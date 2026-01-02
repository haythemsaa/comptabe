<?php

namespace App\Services;

use App\Models\VatDeclaration;
use App\Models\Invoice;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VatDeclarationService
{
    /**
     * Génération automatique déclaration TVA
     * Conforme grilles Intervat belges
     *
     * @param string $period Format: YYYY-QX (ex: 2025-Q1) ou YYYY-MM (ex: 2025-01)
     * @return VatDeclaration
     */
    public function generate(string $period): VatDeclaration
    {
        [$startDate, $endDate, $periodType] = $this->parsePeriod($period);

        // Calculer toutes les grilles
        $sales = $this->calculateSalesVat($startDate, $endDate);
        $purchases = $this->calculatePurchaseVat($startDate, $endDate);
        $intracom = $this->calculateIntracomVat($startDate, $endDate);

        // Créer déclaration
        $declaration = VatDeclaration::create([
            'company_id' => Company::current()->id,
            'period' => $period,
            'period_type' => $periodType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'draft',

            // GRILLES VENTES (opérations sortantes)
            'grid_00' => $sales['base_total'],
            'grid_01' => $sales['base_6'],
            'grid_02' => $sales['base_12'],
            'grid_03' => $sales['base_21'],
            'grid_44' => $sales['services'], // Services avec autoliquidation
            'grid_45' => $sales['exports'], // Exportations hors UE
            'grid_46' => $sales['exempt'], // Opérations exemptées
            'grid_47' => $sales['other_exempt'], // Autres exemptées
            'grid_48' => $sales['note_credit'], // Notes de crédit
            'grid_49' => $sales['note_credit_refund'], // Notes de crédit remboursées

            // TVA COLLECTÉE
            'grid_54' => $sales['vat_21'],
            'grid_55' => $sales['vat_12'],
            'grid_56' => $sales['vat_6'],
            'grid_57' => $sales['vat_revisions'], // Révisions TVA
            'grid_61' => $sales['vat_various'], // TVA diverses opérations
            'grid_63' => $sales['vat_total'], // Total TVA due

            // GRILLES ACHATS (opérations entrantes)
            'grid_81' => $purchases['goods'],
            'grid_82' => $purchases['services'],
            'grid_83' => $purchases['investments'],
            'grid_84' => $purchases['note_credit_received'],
            'grid_85' => $purchases['other_purchases'],

            // TVA DÉDUCTIBLE
            'grid_59' => $purchases['vat_deductible'],
            'grid_62' => $purchases['vat_diverse'], // TVA diverses

            // GRILLES INTRACOMMUNAUTAIRES
            'grid_86' => $intracom['acquisitions_goods'], // Acquisitions biens intra-UE
            'grid_87' => $intracom['acquisitions_services'], // Acquisitions services intra-UE
            'grid_88' => $intracom['livraisons'], // Livraisons intra-UE

            // TVA INTRACOMMUNAUTAIRE
            'grid_55_intra' => $intracom['vat_goods'], // TVA sur acquisitions biens
            'grid_56_intra' => $intracom['vat_services'], // TVA sur acquisitions services

            // SOLDE
            'grid_71' => $sales['vat_total'] - $purchases['vat_deductible'], // TVA à payer/récupérer
            'grid_72' => 0, // Montants reportés période précédente (à implémenter)

            // Métadonnées
            'invoice_count_sales' => $sales['count'],
            'invoice_count_purchases' => $purchases['count'],
            'total_vat_collected' => $sales['vat_total'],
            'total_vat_deductible' => $purchases['vat_deductible'],
            'generated_at' => now(),
            'generated_by' => auth()->id(),
        ]);

        // Générer XML Intervat
        $declaration->xml_content = $this->generateIntervatXML($declaration);
        $declaration->save();

        return $declaration;
    }

    /**
     * Parser la période (trimestriel ou mensuel)
     */
    private function parsePeriod(string $period): array
    {
        if (preg_match('/^(\d{4})-Q([1-4])$/', $period, $matches)) {
            // Période trimestrielle
            $year = (int) $matches[1];
            $quarter = (int) $matches[2];

            $startMonth = ($quarter - 1) * 3 + 1;
            $startDate = Carbon::create($year, $startMonth, 1)->startOfDay();
            $endDate = $startDate->copy()->addMonths(3)->subDay()->endOfDay();

            return [$startDate, $endDate, 'quarterly'];
        } elseif (preg_match('/^(\d{4})-(\d{2})$/', $period, $matches)) {
            // Période mensuelle
            $year = (int) $matches[1];
            $month = (int) $matches[2];

            $startDate = Carbon::create($year, $month, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();

            return [$startDate, $endDate, 'monthly'];
        }

        throw new \InvalidArgumentException("Format période invalide. Utiliser YYYY-QX ou YYYY-MM");
    }

    /**
     * Calcul TVA ventes (opérations sortantes)
     */
    private function calculateSalesVat(Carbon $startDate, Carbon $endDate): array
    {
        $invoices = Invoice::query()
            ->where('company_id', Company::current()->id)
            ->where('type', 'out') // Factures de vente
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->whereIn('status', ['validated', 'sent', 'paid', 'partially_paid'])
            ->with(['lines.vatCode', 'partner'])
            ->get();

        $stats = [
            'count' => $invoices->count(),
            'base_6' => 0,
            'base_12' => 0,
            'base_21' => 0,
            'base_0' => 0,
            'base_total' => 0,
            'vat_6' => 0,
            'vat_12' => 0,
            'vat_21' => 0,
            'vat_total' => 0,
            'services' => 0, // Services autoliquidation
            'exports' => 0, // Exportations hors UE
            'exempt' => 0, // Exemptées article 44
            'other_exempt' => 0,
            'note_credit' => 0,
            'note_credit_refund' => 0,
            'vat_revisions' => 0,
            'vat_various' => 0,
        ];

        foreach ($invoices as $invoice) {
            $isIntraEU = $invoice->partner && $invoice->partner->is_eu_company && $invoice->partner->country_code !== 'BE';
            $isExport = $invoice->partner && !$invoice->partner->is_eu_company && $invoice->partner->country_code !== 'BE';

            foreach ($invoice->lines as $line) {
                $rate = $line->vat_rate ?? 21;
                $base = $line->total_excl_vat;
                $vat = $line->vat_amount;

                // Déterminer la grille selon le type d'opération
                if ($isExport) {
                    // Exportation hors UE (grille 45)
                    $stats['exports'] += $base;
                } elseif ($isIntraEU) {
                    // Livraison intra-UE (traité dans calculateIntracomVat)
                    continue;
                } else {
                    // Ventes nationales
                    match($rate) {
                        21 => [
                            $stats['base_21'] += $base,
                            $stats['vat_21'] += $vat,
                        ],
                        12 => [
                            $stats['base_12'] += $base,
                            $stats['vat_12'] += $vat,
                        ],
                        6 => [
                            $stats['base_6'] += $base,
                            $stats['vat_6'] += $vat,
                        ],
                        0 => $stats['base_0'] += $base,
                        default => null,
                    };
                }
            }

            // Notes de crédit (factures négatives)
            if ($invoice->document_type === 'credit_note') {
                $stats['note_credit'] += abs($invoice->total_excl_vat);
            }
        }

        $stats['base_total'] = $stats['base_21'] + $stats['base_12'] + $stats['base_6'] + $stats['base_0'];
        $stats['vat_total'] = $stats['vat_21'] + $stats['vat_12'] + $stats['vat_6'];

        return $stats;
    }

    /**
     * Calcul TVA achats (opérations entrantes)
     */
    private function calculatePurchaseVat(Carbon $startDate, Carbon $endDate): array
    {
        $expenses = Invoice::query()
            ->where('company_id', Company::current()->id)
            ->where('type', 'in') // Factures d'achat
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->whereIn('status', ['validated', 'paid', 'partially_paid'])
            ->with(['lines.account', 'lines.vatCode'])
            ->get();

        $stats = [
            'count' => $expenses->count(),
            'goods' => 0, // Biens et marchandises
            'services' => 0, // Services et biens divers
            'investments' => 0, // Biens d'investissement
            'other_purchases' => 0,
            'note_credit_received' => 0,
            'vat_deductible' => 0,
            'vat_diverse' => 0,
        ];

        foreach ($expenses as $expense) {
            foreach ($expense->lines as $line) {
                $base = $line->total_excl_vat;
                $vat = $line->vat_amount;
                $accountCode = $line->account_code ?? '';

                // Catégoriser selon le compte comptable (PCMN)
                if ($accountCode >= 600000 && $accountCode < 610000) {
                    // Grille 81: Achats marchandises, matières premières
                    $stats['goods'] += $base;
                } elseif ($accountCode >= 610000 && $accountCode < 620000) {
                    // Grille 82: Services et biens divers
                    $stats['services'] += $base;
                } elseif ($accountCode >= 200000 && $accountCode < 300000) {
                    // Grille 83: Biens d'investissement (actifs immobilisés)
                    $stats['investments'] += $base;
                } else {
                    $stats['other_purchases'] += $base;
                }

                // TVA déductible
                if ($line->vat_rate > 0) {
                    $stats['vat_deductible'] += $vat;
                }
            }

            // Notes de crédit reçues
            if ($expense->document_type === 'credit_note') {
                $stats['note_credit_received'] += abs($expense->total_excl_vat);
            }
        }

        return $stats;
    }

    /**
     * Calcul opérations intracommunautaires
     */
    private function calculateIntracomVat(Carbon $startDate, Carbon $endDate): array
    {
        $stats = [
            'acquisitions_goods' => 0, // Grille 86: Acquisitions biens intra-UE
            'acquisitions_services' => 0, // Grille 87: Acquisitions services intra-UE
            'livraisons' => 0, // Grille 88: Livraisons intra-UE
            'vat_goods' => 0, // TVA autoliquidée sur biens
            'vat_services' => 0, // TVA autoliquidée sur services
        ];

        // Acquisitions intra-UE (achats)
        $acquisitions = Invoice::query()
            ->where('company_id', Company::current()->id)
            ->where('type', 'in')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->whereHas('partner', function ($q) {
                $q->where('is_eu_company', true)
                  ->where('country_code', '!=', 'BE');
            })
            ->with('lines')
            ->get();

        foreach ($acquisitions as $acquisition) {
            $isService = $acquisition->lines->contains(function ($line) {
                return $line->account_code >= 610000 && $line->account_code < 620000;
            });

            $base = $acquisition->total_excl_vat;

            if ($isService) {
                $stats['acquisitions_services'] += $base;
                $stats['vat_services'] += $base * 0.21; // Autoliquidation 21%
            } else {
                $stats['acquisitions_goods'] += $base;
                $stats['vat_goods'] += $base * 0.21; // Autoliquidation 21%
            }
        }

        // Livraisons intra-UE (ventes)
        $livraisons = Invoice::query()
            ->where('company_id', Company::current()->id)
            ->where('type', 'out')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->whereHas('partner', function ($q) {
                $q->where('is_eu_company', true)
                  ->where('country_code', '!=', 'BE');
            })
            ->sum('total_excl_vat');

        $stats['livraisons'] = $livraisons;

        return $stats;
    }

    /**
     * Générer XML Intervat pour soumission fiscale
     */
    private function generateIntervatXML(VatDeclaration $declaration): string
    {
        $company = Company::current();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><VATConsignment></VATConsignment>');

        // En-tête
        $xml->addAttribute('xmlns', 'http://www.minfin.fgov.be/VATConsignment');
        $xml->addAttribute('VATDeclarationsNbr', '1');

        // Représentant (comptable ou entreprise)
        $representative = $xml->addChild('Representative');
        $representative->addChild('RepresentativeID', $company->vat_number);
        $representative->addChild('Name', htmlspecialchars($company->name));
        $representative->addChild('Street', htmlspecialchars($company->address ?? ''));
        $representative->addChild('PostCode', $company->postal_code ?? '');
        $representative->addChild('City', htmlspecialchars($company->city ?? ''));
        $representative->addChild('CountryCode', 'BE');
        $representative->addChild('EmailAddress', $company->email ?? '');
        $representative->addChild('Phone', $company->phone ?? '');

        // Déclaration
        $vatDeclaration = $xml->addChild('VATDeclaration');
        $vatDeclaration->addAttribute('SequenceNumber', '1');
        $vatDeclaration->addAttribute('DeclarantReference', $declaration->id);

        // Période
        $period = $vatDeclaration->addChild('Period');
        $period->addChild('Year', $declaration->start_date->year);
        $period->addChild('Month', $declaration->period_type === 'monthly' ? $declaration->start_date->month : null);
        $period->addChild('Quarter', $declaration->period_type === 'quarterly' ? ceil($declaration->start_date->month / 3) : null);

        // Grilles
        $this->addGridsToXML($vatDeclaration, $declaration);

        // Signature (si certificat disponible)
        // TODO: Implémenter signature électronique avec eID

        return $xml->asXML();
    }

    /**
     * Ajouter grilles au XML
     */
    private function addGridsToXML(\SimpleXMLElement $vatDeclaration, VatDeclaration $declaration): void
    {
        $grids = [
            // Opérations sortantes
            '00' => $declaration->grid_00,
            '01' => $declaration->grid_01,
            '02' => $declaration->grid_02,
            '03' => $declaration->grid_03,
            '44' => $declaration->grid_44,
            '45' => $declaration->grid_45,
            '46' => $declaration->grid_46,
            '47' => $declaration->grid_47,
            '48' => $declaration->grid_48,
            '49' => $declaration->grid_49,

            // TVA collectée
            '54' => $declaration->grid_54,
            '55' => $declaration->grid_55,
            '56' => $declaration->grid_56,
            '57' => $declaration->grid_57,
            '61' => $declaration->grid_61,
            '63' => $declaration->grid_63,

            // Opérations entrantes
            '81' => $declaration->grid_81,
            '82' => $declaration->grid_82,
            '83' => $declaration->grid_83,
            '84' => $declaration->grid_84,
            '85' => $declaration->grid_85,

            // TVA déductible
            '59' => $declaration->grid_59,
            '62' => $declaration->grid_62,

            // Intracommunautaire
            '86' => $declaration->grid_86,
            '87' => $declaration->grid_87,
            '88' => $declaration->grid_88,

            // Solde
            '71' => $declaration->grid_71,
            '72' => $declaration->grid_72,
        ];

        foreach ($grids as $number => $amount) {
            if ($amount && abs($amount) >= 0.01) {
                $data = $vatDeclaration->addChild('Data');
                $data->addChild('GridNumber', $number);
                $data->addChild('Amount', number_format($amount, 2, '.', ''));
            }
        }
    }

    /**
     * Valider déclaration avant envoi
     */
    public function validate(VatDeclaration $declaration): array
    {
        $errors = [];

        // 1. Cohérence totaux
        $expectedVatCollected = ($declaration->grid_54 ?? 0) + ($declaration->grid_55 ?? 0) + ($declaration->grid_56 ?? 0);
        if (abs($expectedVatCollected - ($declaration->grid_63 ?? 0)) > 0.01) {
            $errors[] = "Incohérence grille 63 (TVA totale)";
        }

        $expectedBalance = ($declaration->grid_63 ?? 0) - ($declaration->grid_59 ?? 0);
        if (abs($expectedBalance - ($declaration->grid_71 ?? 0)) > 0.01) {
            $errors[] = "Incohérence grille 71 (solde)";
        }

        // 2. Grilles obligatoires
        if (!$declaration->grid_00) {
            $errors[] = "Grille 00 (chiffre d'affaires) obligatoire";
        }

        // 3. Numéro TVA valide
        $company = $declaration->company;
        if (!$company->vat_number || !$this->validateVATNumber($company->vat_number)) {
            $errors[] = "Numéro TVA entreprise invalide";
        }

        // 4. Période complète
        if ($declaration->start_date->isFuture()) {
            $errors[] = "La période ne peut pas être dans le futur";
        }

        return $errors;
    }

    /**
     * Valider numéro TVA belge
     */
    private function validateVATNumber(string $vat): bool
    {
        // Format belge: BE0123456789 ou BE 0123 456 789
        $vat = preg_replace('/[^0-9]/', '', $vat);

        if (strlen($vat) !== 10) {
            return false;
        }

        // Validation modulo 97
        $base = (int) substr($vat, 0, 8);
        $check = (int) substr($vat, 8, 2);

        return (97 - ($base % 97)) === $check;
    }

    /**
     * Soumettre à Intervat (production)
     */
    public function submit(VatDeclaration $declaration): array
    {
        // Valider d'abord
        $errors = $this->validate($declaration);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // TODO: Intégration réelle avec Intervat/Biztax
        // Pour l'instant, simulation

        if (config('app.env') === 'production') {
            // Appel API Intervat réel
            // Nécessite certificat eID ou token Isabel
            Log::info('Soumission Intervat production', ['declaration_id' => $declaration->id]);

            // Placeholder
            return [
                'success' => false,
                'message' => 'Intégration Intervat en cours de développement',
            ];
        }

        // Mode test: simuler succès
        $declaration->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'submission_reference' => 'TEST-' . now()->format('YmdHis'),
        ]);

        return [
            'success' => true,
            'reference' => $declaration->submission_reference,
            'message' => 'Déclaration soumise avec succès (mode test)',
        ];
    }

    /**
     * Exporter en PDF
     */
    public function exportPDF(VatDeclaration $declaration): string
    {
        // TODO: Générer PDF avec toutes les grilles
        // Utiliser barryvdh/laravel-dompdf

        return '';
    }

    /**
     * Obtenir statistiques déclarations
     */
    public function getStats(int $year): array
    {
        $declarations = VatDeclaration::query()
            ->where('company_id', Company::current()->id)
            ->whereYear('start_date', $year)
            ->get();

        return [
            'total_declarations' => $declarations->count(),
            'submitted' => $declarations->where('status', 'submitted')->count(),
            'draft' => $declarations->where('status', 'draft')->count(),
            'total_vat_paid' => $declarations->where('grid_71', '>', 0)->sum('grid_71'),
            'total_vat_refund' => abs($declarations->where('grid_71', '<', 0)->sum('grid_71')),
            'average_vat' => $declarations->avg('grid_71'),
        ];
    }
}
