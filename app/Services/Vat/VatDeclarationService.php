<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\VatDeclaration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service de génération des déclarations TVA belges
 *
 * Génère automatiquement les grilles 54-72 selon le régime belge
 * et exporte au format XML Intervat
 */
class VatDeclarationService
{
    /**
     * Grilles TVA belges (simplifié - les principales)
     */
    const GRIDS = [
        // Opérations sorties (ventes)
        '00' => 'Chiffre d\'affaires (HTVA) grille 0%',
        '01' => 'Opérations taxables à 6%',
        '02' => 'Opérations taxables à 12%',
        '03' => 'Opérations taxables à 21%',
        '44' => 'Services intracommunautaires',
        '45' => 'Livraisons intracommunautaires',
        '46' => 'Livraisons avec TVA à l\'acquéreur',
        '47' => 'Autres opérations exemptées',
        '48' => 'Opérations exemptées - exportations hors UE',
        '49' => 'Opérations exemptées - livraisons UE',

        // TVA due (grilles 54-55-56)
        '54' => 'TVA due sur opérations 6%',
        '55' => 'TVA due sur opérations 12%',
        '56' => 'TVA due sur opérations 21%',
        '57' => 'TVA due sur importations avec report',
        '61' => 'TVA due sur acquisitions IC',
        '63' => 'TVA due sur autres opérations',

        // Opérations entrées (achats)
        '81' => 'Achats marchandises/services (HTVA)',
        '82' => 'Achats biens d\'investissement (HTVA)',
        '83' => 'Acquisitions intracommunautaires',
        '84' => 'Achats avec TVA déductible',
        '85' => 'Opérations diverses',
        '86' => 'Biens mixtes',
        '87' => 'Importations avec report de perception',

        // TVA déductible
        '59' => 'TVA déductible',

        // Soldes
        '71' => 'TVA à payer (solde positif)',
        '72' => 'TVA à récupérer (solde négatif)',
    ];

    /**
     * Taux TVA belges standards
     */
    const VAT_RATES = [
        0 => '00',   // Exempté
        6 => '01',   // Taux réduit
        12 => '02',  // Taux intermédiaire
        21 => '03',  // Taux normal
    ];

    /**
     * Génère une déclaration TVA pour une période
     *
     * @param string $companyId
     * @param string $periodType 'monthly' ou 'quarterly'
     * @param int $year
     * @param int $periodNumber (1-12 pour monthly, 1-4 pour quarterly)
     * @return VatDeclaration
     */
    public function generateDeclaration(
        string $companyId,
        string $periodType,
        int $year,
        int $periodNumber
    ): VatDeclaration {

        // Vérifier si déclaration existe déjà
        $existing = VatDeclaration::where('company_id', $companyId)
            ->where('period_type', $periodType)
            ->where('period_year', $year)
            ->where('period_number', $periodNumber)
            ->first();

        if ($existing && $existing->status !== 'draft') {
            throw new \Exception('Une déclaration non-draft existe déjà pour cette période');
        }

        DB::beginTransaction();

        try {
            // Calculer les dates de période
            $periodStart = $this->getPeriodStart($periodType, $year, $periodNumber);
            $periodEnd = $this->getPeriodEnd($periodType, $year, $periodNumber);

            // Calculer les grilles TVA
            $grids = $this->calculateGrids($companyId, $periodStart, $periodEnd);

            // Calculer totaux
            $totalVatDue = ($grids['54'] ?? 0) + ($grids['55'] ?? 0) + ($grids['56'] ?? 0)
                         + ($grids['57'] ?? 0) + ($grids['61'] ?? 0) + ($grids['63'] ?? 0);

            $totalVatDeductible = $grids['59'] ?? 0;

            $balance = $totalVatDue - $totalVatDeductible;

            // Créer ou mettre à jour déclaration
            $declaration = VatDeclaration::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'period_type' => $periodType,
                    'period_year' => $year,
                    'period_number' => $periodNumber,
                ],
                [
                    'status' => 'draft',
                    'grid_values' => $grids,
                    'total_operations' => array_sum($grids),
                    'total_vat_due' => $totalVatDue,
                    'total_vat_deductible' => $totalVatDeductible,
                    'balance' => $balance,
                ]
            );

            DB::commit();

            Log::info('VAT declaration generated', [
                'declaration_id' => $declaration->id,
                'period' => $declaration->period_name,
                'balance' => $balance,
            ]);

            return $declaration;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to generate VAT declaration', [
                'company_id' => $companyId,
                'period' => "{$periodType} {$year}/{$periodNumber}",
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Calcule toutes les grilles TVA pour une période
     *
     * @param string $companyId
     * @param Carbon $periodStart
     * @param Carbon $periodEnd
     * @return array
     */
    protected function calculateGrids(string $companyId, Carbon $periodStart, Carbon $periodEnd): array
    {
        $grids = [];

        // Récupérer toutes les factures de la période
        $salesInvoices = Invoice::where('company_id', $companyId)
            ->where('type', 'sales')
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->whereBetween('invoice_date', [$periodStart, $periodEnd])
            ->with('lines')
            ->get();

        $purchaseInvoices = Invoice::where('company_id', $companyId)
            ->where('type', 'purchase')
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->whereBetween('invoice_date', [$periodStart, $periodEnd])
            ->with('lines')
            ->get();

        // === VENTES (opérations sorties) ===

        $vat6Base = 0;
        $vat12Base = 0;
        $vat21Base = 0;
        $vat0Base = 0;

        foreach ($salesInvoices as $invoice) {
            foreach ($invoice->lines as $line) {
                $rate = (int) $line->vat_rate;

                switch ($rate) {
                    case 0:
                        $vat0Base += $line->subtotal;
                        break;
                    case 6:
                        $vat6Base += $line->subtotal;
                        break;
                    case 12:
                        $vat12Base += $line->subtotal;
                        break;
                    case 21:
                        $vat21Base += $line->subtotal;
                        break;
                }
            }
        }

        // Grilles ventes
        $grids['00'] = round($vat0Base, 2);
        $grids['01'] = round($vat6Base, 2);
        $grids['02'] = round($vat12Base, 2);
        $grids['03'] = round($vat21Base, 2);

        // TVA due (grilles 54-56)
        $grids['54'] = round($vat6Base * 0.06, 2);
        $grids['55'] = round($vat12Base * 0.12, 2);
        $grids['56'] = round($vat21Base * 0.21, 2);

        // === ACHATS (opérations entrées) ===

        $purchaseBase = 0;
        $vatDeductible = 0;

        foreach ($purchaseInvoices as $invoice) {
            foreach ($invoice->lines as $line) {
                $purchaseBase += $line->subtotal;
                $vatDeductible += $line->vat_amount;
            }
        }

        $grids['81'] = round($purchaseBase, 2);  // Total achats HTVA
        $grids['59'] = round($vatDeductible, 2); // TVA déductible

        // === SOLDES (grilles 71-72) ===

        $totalVatDue = ($grids['54'] ?? 0) + ($grids['55'] ?? 0) + ($grids['56'] ?? 0);
        $balance = $totalVatDue - $vatDeductible;

        if ($balance > 0) {
            $grids['71'] = round($balance, 2); // À payer
        } else {
            $grids['72'] = round(abs($balance), 2); // À récupérer
        }

        return $grids;
    }

    /**
     * Valide une déclaration
     */
    public function validateDeclaration(VatDeclaration $declaration): bool
    {
        if ($declaration->status !== 'draft') {
            throw new \Exception('Seules les déclarations en brouillon peuvent être validées');
        }

        // Vérifications
        if (empty($declaration->grid_values)) {
            throw new \Exception('Les grilles TVA sont vides');
        }

        $declaration->update([
            'status' => 'validated',
            'validated_at' => now(),
            'validated_by' => auth()->id(),
        ]);

        Log::info('VAT declaration validated', [
            'declaration_id' => $declaration->id,
            'period' => $declaration->period_name,
        ]);

        return true;
    }

    /**
     * Génère le XML Intervat pour soumission
     *
     * @param VatDeclaration $declaration
     * @return string XML content
     */
    public function generateIntervatXml(VatDeclaration $declaration): string
    {
        if ($declaration->status === 'draft') {
            throw new \Exception('La déclaration doit être validée avant export XML');
        }

        $company = Company::findOrFail($declaration->company_id);

        // Générer XML selon format Intervat
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><VATConsignment></VATConsignment>');

        // Representative (accountant or company)
        $representative = $xml->addChild('Representative');
        $representative->addChild('RepresentativeID', $company->vat_number ?? '');
        $representative->addChild('Name', $company->name);

        // Declarant
        $declarant = $xml->addChild('Declarant');
        $declarant->addChild('VATNumber', str_replace(['BE', ' '], '', $company->vat_number ?? ''));
        $declarant->addChild('Name', $company->name);
        $declarant->addChild('Street', $company->address ?? '');
        $declarant->addChild('PostCode', $company->postal_code ?? '');
        $declarant->addChild('City', $company->city ?? '');
        $declarant->addChild('CountryCode', 'BE');

        // Declaration
        $vatDeclaration = $xml->addChild('VATDeclaration');
        $vatDeclaration->addChild('SequenceNumber', '1');

        // Period
        $period = $vatDeclaration->addChild('Period');
        $period->addChild('Period', $declaration->period_type === 'monthly' ? 'M' : 'T');
        $period->addChild('Year', $declaration->period_year);
        $period->addChild('Month', str_pad($declaration->period_number, 2, '0', STR_PAD_LEFT));

        // Data (grilles)
        $data = $vatDeclaration->addChild('Data');

        foreach ($declaration->grid_values as $grid => $amount) {
            if ($amount != 0) {
                $item = $data->addChild('Amount');
                $item->addChild('GridNumber', $grid);
                $item->addChild('Amount', number_format($amount, 2, '.', ''));
            }
        }

        // Format XML
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }

    /**
     * Soumet la déclaration à Intervat (simulation)
     *
     * @param VatDeclaration $declaration
     * @return array
     */
    public function submitToIntervat(VatDeclaration $declaration): array
    {
        if ($declaration->status !== 'validated') {
            throw new \Exception('La déclaration doit être validée avant soumission');
        }

        // Générer XML
        $xml = $this->generateIntervatXml($declaration);

        try {
            // TODO: Intégration réelle avec API Intervat
            // Pour l'instant, simulation

            $submissionRef = 'IV-' . date('Ymd-His') . '-' . substr($declaration->id, 0, 8);

            $declaration->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'submission_reference' => $submissionRef,
                'intervat_response' => [
                    'success' => true,
                    'reference' => $submissionRef,
                    'submitted_at' => now()->toIso8601String(),
                    'message' => 'Déclaration soumise avec succès (simulation)',
                ],
            ]);

            Log::info('VAT declaration submitted to Intervat', [
                'declaration_id' => $declaration->id,
                'reference' => $submissionRef,
            ]);

            return [
                'success' => true,
                'reference' => $submissionRef,
                'message' => 'Déclaration soumise avec succès',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to submit VAT declaration', [
                'declaration_id' => $declaration->id,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Erreur lors de la soumission: ' . $e->getMessage());
        }
    }

    /**
     * Obtient la date de début de période
     */
    protected function getPeriodStart(string $periodType, int $year, int $periodNumber): Carbon
    {
        if ($periodType === 'monthly') {
            return Carbon::create($year, $periodNumber, 1)->startOfDay();
        }

        // Quarterly: T1=Jan, T2=Apr, T3=Jul, T4=Oct
        $month = (($periodNumber - 1) * 3) + 1;
        return Carbon::create($year, $month, 1)->startOfDay();
    }

    /**
     * Obtient la date de fin de période
     */
    protected function getPeriodEnd(string $periodType, int $year, int $periodNumber): Carbon
    {
        if ($periodType === 'monthly') {
            return Carbon::create($year, $periodNumber, 1)->endOfMonth()->endOfDay();
        }

        // Quarterly: end of T1=Mar, T2=Jun, T3=Sep, T4=Dec
        $month = $periodNumber * 3;
        return Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
    }

    /**
     * Génère toutes les déclarations manquantes pour une année
     */
    public function generateMissingDeclarations(string $companyId, int $year, string $periodType = 'monthly'): array
    {
        $results = [
            'generated' => 0,
            'skipped' => 0,
            'declarations' => [],
        ];

        $periods = $periodType === 'monthly' ? 12 : 4;

        for ($i = 1; $i <= $periods; $i++) {
            try {
                $declaration = $this->generateDeclaration($companyId, $periodType, $year, $i);
                $results['generated']++;
                $results['declarations'][] = $declaration;
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'existe déjà')) {
                    $results['skipped']++;
                } else {
                    throw $e;
                }
            }
        }

        return $results;
    }

    /**
     * Wrapper pour compatibilité controller : génère depuis format période "YYYY-MM" ou "YYYY-QX"
     *
     * @param string $period Format: "2025-01" (mensuel) ou "2025-Q1" (trimestriel)
     * @return VatDeclaration
     */
    public function generate(string $period): VatDeclaration
    {
        // Parse le format période
        if (preg_match('/^(\d{4})-Q([1-4])$/', $period, $matches)) {
            // Trimestriel: 2025-Q1
            $year = (int) $matches[1];
            $quarter = (int) $matches[2];
            return $this->generateDeclaration(auth()->user()->current_company_id, 'quarterly', $year, $quarter);

        } elseif (preg_match('/^(\d{4})-(\d{2})$/', $period, $matches)) {
            // Mensuel: 2025-01
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            return $this->generateDeclaration(auth()->user()->current_company_id, 'monthly', $year, $month);

        } else {
            throw new \InvalidArgumentException('Format période invalide. Utilisez YYYY-MM ou YYYY-QX');
        }
    }

    /**
     * Wrapper pour compatibilité controller : soumet une déclaration
     *
     * @param VatDeclaration $declaration
     * @return array
     */
    public function submit(VatDeclaration $declaration): array
    {
        // Valider d'abord si nécessaire
        if ($declaration->status === 'draft') {
            $this->validateDeclaration($declaration);
        }

        // Soumettre
        return $this->submitToIntervat($declaration);
    }

    /**
     * Obtient les statistiques TVA pour une année
     *
     * @param int $year
     * @return array
     */
    public function getStats(int $year): array
    {
        $companyId = auth()->user()->current_company_id;

        $declarations = VatDeclaration::where('company_id', $companyId)
            ->where('period_year', $year)
            ->get();

        $totalVatDue = $declarations->sum('total_vat_due');
        $totalVatDeductible = $declarations->sum('total_vat_deductible');
        $totalBalance = $declarations->sum('balance');

        $countDraft = $declarations->where('status', 'draft')->count();
        $countValidated = $declarations->where('status', 'validated')->count();
        $countSubmitted = $declarations->whereIn('status', ['submitted', 'accepted'])->count();

        return [
            'year' => $year,
            'total_declarations' => $declarations->count(),
            'draft' => $countDraft,
            'validated' => $countValidated,
            'submitted' => $countSubmitted,
            'total_vat_due' => round($totalVatDue, 2),
            'total_vat_deductible' => round($totalVatDeductible, 2),
            'total_balance' => round($totalBalance, 2),
            'average_balance' => $declarations->count() > 0 ? round($totalBalance / $declarations->count(), 2) : 0,
        ];
    }

    /**
     * Exporte une déclaration en PDF
     *
     * @param VatDeclaration $declaration
     * @return string Binary PDF content
     */
    public function exportPDF(VatDeclaration $declaration): string
    {
        $company = Company::findOrFail($declaration->company_id);

        // Grid descriptions for the PDF
        $gridDescriptions = self::GRIDS;

        // Generate PDF using DomPDF with Blade template
        $pdf = \PDF::loadView('pdf.vat-declaration', [
            'declaration' => $declaration,
            'company' => $company,
            'gridDescriptions' => $gridDescriptions,
        ]);

        // Set PDF options
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);

        // Return PDF binary content
        return $pdf->output();
    }
}
