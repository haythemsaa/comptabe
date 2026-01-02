<?php

namespace App\Services\Compliance;

use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Partner;
use App\Models\VatDeclaration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BelgianTaxComplianceService
{
    /**
     * Check VAT compliance for a company
     */
    public function checkVATCompliance(string $companyId): array
    {
        $alerts = [];

        // Check reverse charge situations
        $reverseChargeAlerts = $this->checkReverseCharge($companyId);
        $alerts = array_merge($alerts, $reverseChargeAlerts);

        // Check VAT number validity
        $vatNumberAlerts = $this->checkVATNumbers($companyId);
        $alerts = array_merge($alerts, $vatNumberAlerts);

        // Check VAT threshold
        $thresholdAlerts = $this->checkVATThreshold($companyId);
        $alerts = array_merge($alerts, $thresholdAlerts);

        // Check VAT rate optimization
        $rateAlerts = $this->checkVATRateOptimization($companyId);
        $alerts = array_merge($alerts, $rateAlerts);

        // Check listing obligations
        $listingAlerts = $this->checkListingObligations($companyId);
        $alerts = array_merge($alerts, $listingAlerts);

        return $alerts;
    }

    /**
     * Check for missing reverse charge on intra-EU services
     */
    protected function checkReverseCharge(string $companyId): array
    {
        $alerts = [];

        // Find invoices with EU VAT numbers but no reverse charge applied
        // BUG FIX: Removed contradictory WHERE clause (line 57 was wrong)
        $suspiciousInvoices = Invoice::where('company_id', $companyId)
            ->whereHas('partner', function ($query) {
                $query->whereNotNull('vat_number')
                      ->where('vat_number', 'NOT LIKE', 'BE%'); // Only non-Belgian VAT numbers
            })
            ->where('vat_amount', '>', 0) // VAT was charged (should be 0 for reverse charge)
            ->whereDate('issue_date', '>=', now()->subMonths(3))
            ->get();

        foreach ($suspiciousInvoices as $invoice) {
            $partner = $invoice->partner;
            $vatPrefix = substr($partner->vat_number, 0, 2);

            if (in_array($vatPrefix, $this->getEUCountryCodes()) && $vatPrefix !== 'BE') {
                $alerts[] = [
                    'type' => 'reverse_charge',
                    'severity' => 'high',
                    'title' => 'Reverse Charge Manquant',
                    'message' => "La facture {$invoice->invoice_number} au client EU {$partner->name} ({$partner->vat_number}) devrait utiliser le reverse charge",
                    'reference_id' => $invoice->id,
                    'reference_type' => 'invoice',
                    'action' => 'review_invoice',
                    'impact' => 'Risque de correction TVA et pénalités',
                    'recommendation' => 'Appliquer le reverse charge (TVA 0% avec mention spécifique)',
                ];
            }
        }

        return $alerts;
    }

    /**
     * Validate VAT numbers via VIES
     */
    protected function checkVATNumbers(string $companyId): array
    {
        $alerts = [];

        $partners = Partner::where('company_id', $companyId)
            ->whereNotNull('vat_number')
            ->where('vat_number', '!=', '')
            ->get();

        foreach ($partners as $partner) {
            $cacheKey = "vat_validation_{$partner->vat_number}";

            $validationResult = Cache::remember($cacheKey, 86400, function () use ($partner) {
                return $this->validateVATNumberVIES($partner->vat_number);
            });

            if (!$validationResult['valid']) {
                $alerts[] = [
                    'type' => 'invalid_vat',
                    'severity' => 'medium',
                    'title' => 'Numéro TVA Invalide',
                    'message' => "Le numéro TVA {$partner->vat_number} du partenaire {$partner->name} est invalide selon VIES",
                    'reference_id' => $partner->id,
                    'reference_type' => 'partner',
                    'action' => 'update_vat_number',
                    'impact' => 'Risque de rejet déclaration TVA',
                    'recommendation' => 'Vérifier et corriger le numéro TVA',
                ];
            }
        }

        return $alerts;
    }

    /**
     * Validate VAT number via VIES API
     */
    protected function validateVATNumberVIES(string $vatNumber): array
    {
        try {
            // Remove spaces and special characters
            $vatNumber = strtoupper(preg_replace('/[^A-Z0-9]/', '', $vatNumber));

            $countryCode = substr($vatNumber, 0, 2);
            $vatNumberOnly = substr($vatNumber, 2);

            // VIES SOAP API endpoint
            $response = Http::timeout(5)->post('https://ec.europa.eu/taxation_customs/vies/services/checkVatService', [
                'countryCode' => $countryCode,
                'vatNumber' => $vatNumberOnly,
            ]);

            if ($response->successful()) {
                $valid = str_contains($response->body(), '<valid>true</valid>');

                return [
                    'valid' => $valid,
                    'country_code' => $countryCode,
                    'vat_number' => $vatNumberOnly,
                ];
            }

            return ['valid' => false, 'error' => 'VIES API unavailable'];
        } catch (\Exception $e) {
            Log::warning("VIES validation failed for {$vatNumber}: {$e->getMessage()}");
            return ['valid' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check VAT exemption threshold
     */
    protected function checkVATThreshold(string $companyId): array
    {
        $alerts = [];

        // Belgian VAT exemption threshold: €25,000/year
        $threshold = 25000;

        $currentYearRevenue = Invoice::where('company_id', $companyId)
            ->whereYear('issue_date', now()->year)
            ->sum('total_amount');

        if ($currentYearRevenue > $threshold * 0.8 && $currentYearRevenue < $threshold) {
            $alerts[] = [
                'type' => 'vat_threshold',
                'severity' => 'low',
                'title' => 'Seuil TVA Approché',
                'message' => "Votre CA annuel ({$this->formatCurrency($currentYearRevenue)}) approche le seuil d'exemption TVA (€25,000)",
                'reference_id' => null,
                'reference_type' => null,
                'action' => 'plan_vat_registration',
                'impact' => 'Obligation de s\'assujettir à la TVA si seuil dépassé',
                'recommendation' => 'Prévoir l\'assujettissement TVA et la conformité',
            ];
        } elseif ($currentYearRevenue > $threshold) {
            $alerts[] = [
                'type' => 'vat_threshold',
                'severity' => 'high',
                'title' => 'Seuil TVA Dépassé',
                'message' => "Votre CA annuel ({$this->formatCurrency($currentYearRevenue)}) dépasse le seuil d'exemption TVA (€25,000)",
                'reference_id' => null,
                'reference_type' => null,
                'action' => 'register_vat',
                'impact' => 'Obligation immédiate d\'assujettissement TVA',
                'recommendation' => 'S\'assujettir à la TVA sans délai',
            ];
        }

        return $alerts;
    }

    /**
     * Check for VAT rate optimization opportunities
     */
    protected function checkVATRateOptimization(string $companyId): array
    {
        $alerts = [];

        // Check for potential reduced VAT rate opportunities
        $invoices = Invoice::where('company_id', $companyId)
            ->whereDate('issue_date', '>=', now()->subMonths(6))
            ->get();

        $reducedRateOpportunities = 0;

        foreach ($invoices as $invoice) {
            // Check if description suggests reduced rate eligibility
            if ($this->isEligibleForReducedVATRate($invoice)) {
                $reducedRateOpportunities++;
            }
        }

        if ($reducedRateOpportunities > 0) {
            $alerts[] = [
                'type' => 'vat_optimization',
                'severity' => 'low',
                'title' => 'Optimisation Taux TVA',
                'message' => "{$reducedRateOpportunities} facture(s) pourraient potentiellement bénéficier d'un taux TVA réduit",
                'reference_id' => null,
                'reference_type' => null,
                'action' => 'review_vat_rates',
                'impact' => 'Économie potentielle sur TVA',
                'recommendation' => 'Vérifier l\'éligibilité aux taux réduits (6% ou 12%)',
            ];
        }

        return $alerts;
    }

    /**
     * Check if invoice is eligible for reduced VAT rate
     */
    protected function isEligibleForReducedVATRate($invoice): bool
    {
        $reducedRateKeywords = [
            'restaurant', 'traiteur', 'rénovation', 'travaux', 'construction',
            'logement social', 'démolition', 'transformation', 'aliments',
            'boissons', 'transport', 'hébergement', 'culture', 'sport'
        ];

        $description = strtolower($invoice->description ?? '');

        foreach ($reducedRateKeywords as $keyword) {
            if (str_contains($description, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check listing obligations (clients listing, intra-community listing)
     */
    protected function checkListingObligations(string $companyId): array
    {
        $alerts = [];

        // Check clients listing obligation (annual, if total B2B invoices > €250)
        $currentYearB2BTotal = Invoice::where('company_id', $companyId)
            ->whereYear('issue_date', now()->year)
            ->whereHas('partner', function ($query) {
                $query->whereNotNull('vat_number');
            })
            ->sum('total_amount');

        if ($currentYearB2BTotal > 250) {
            $lastListing = VatDeclaration::where('company_id', $companyId)
                ->where('declaration_type', 'clients_listing')
                ->whereYear('period_end', now()->year)
                ->first();

            if (!$lastListing && now()->month >= 3) {
                $alerts[] = [
                    'type' => 'clients_listing',
                    'severity' => 'high',
                    'title' => 'Listing Clients Obligatoire',
                    'message' => "Le listing annuel des clients (CA B2B: {$this->formatCurrency($currentYearB2BTotal)}) doit être déposé avant le 31 mars",
                    'reference_id' => null,
                    'reference_type' => null,
                    'action' => 'generate_clients_listing',
                    'impact' => 'Pénalité de €50 à €1,250 en cas de retard',
                    'recommendation' => 'Générer et soumettre le listing clients',
                ];
            }
        }

        // Check intra-community listing obligation (monthly/quarterly)
        $this->checkIntraCommunityListing($companyId, $alerts);

        return $alerts;
    }

    /**
     * Check intra-community listing (VIES declaration)
     */
    protected function checkIntraCommunityListing(string $companyId, array &$alerts): void
    {
        $lastQuarter = now()->subQuarter();
        $intraCommunityInvoices = Invoice::where('company_id', $companyId)
            ->whereBetween('issue_date', [$lastQuarter->startOfQuarter(), $lastQuarter->endOfQuarter()])
            ->whereHas('partner', function ($query) {
                $query->whereNotNull('vat_number')
                      ->where('vat_number', 'NOT LIKE', 'BE%');
            })
            ->sum('total_amount');

        if ($intraCommunityInvoices > 0) {
            $lastDeclaration = VatDeclaration::where('company_id', $companyId)
                ->where('declaration_type', 'intracom_listing')
                ->where('period_end', $lastQuarter->endOfQuarter())
                ->first();

            if (!$lastDeclaration) {
                $alerts[] = [
                    'type' => 'intracom_listing',
                    'severity' => 'high',
                    'title' => 'Listing Intracommunautaire Manquant',
                    'message' => "Le listing intracommunautaire pour {$lastQuarter->format('Q Y')} (€{$this->formatCurrency($intraCommunityInvoices)}) n'a pas été déposé",
                    'reference_id' => null,
                    'reference_type' => null,
                    'action' => 'generate_intracom_listing',
                    'impact' => 'Pénalité de €250 à €3,000 + 10% du montant non déclaré',
                    'recommendation' => 'Soumettre le listing avant le 20 du mois suivant le trimestre',
                ];
            }
        }
    }

    /**
     * Get fiscal calendar deadlines for Belgium
     */
    public function getFiscalCalendar(string $companyId, int $year = null): array
    {
        $year = $year ?? now()->year;
        $deadlines = [];

        // VAT declarations (monthly/quarterly)
        for ($month = 1; $month <= 12; $month++) {
            $deadlines[] = [
                'type' => 'vat_declaration',
                'title' => 'Déclaration TVA',
                'period' => Carbon::create($year, $month, 1)->format('F Y'),
                'deadline' => Carbon::create($year, $month, 20)->addMonth(),
                'description' => 'Dépôt déclaration TVA mensuelle',
                'penalty' => '€50 à €2,500 selon retard',
            ];
        }

        // Corporate tax return
        $deadlines[] = [
            'type' => 'corporate_tax',
            'title' => 'Déclaration Impôt Sociétés',
            'period' => ($year - 1) . '',
            'deadline' => Carbon::create($year, 9, 30),
            'description' => 'Dépôt déclaration impôt des sociétés',
            'penalty' => 'Majoration de 10% à 200%',
        ];

        // Annual accounts filing
        $deadlines[] = [
            'type' => 'annual_accounts',
            'title' => 'Dépôt Comptes Annuels',
            'period' => ($year - 1) . '',
            'deadline' => Carbon::create($year, 7, 31),
            'description' => 'Dépôt à la Banque Nationale',
            'penalty' => '€120 à €12,000 selon retard',
        ];

        // Clients listing
        $deadlines[] = [
            'type' => 'clients_listing',
            'title' => 'Listing Annuel Clients',
            'period' => ($year - 1) . '',
            'deadline' => Carbon::create($year, 3, 31),
            'description' => 'Listing clients B2B > €250',
            'penalty' => '€50 à €1,250',
        ];

        // Sort by deadline
        usort($deadlines, function ($a, $b) {
            return $a['deadline']->timestamp - $b['deadline']->timestamp;
        });

        return $deadlines;
    }

    /**
     * Calculate penalties for late filing
     */
    public function calculateLateFilingPenalty(string $declarationType, Carbon $deadline, Carbon $filingDate, float $amount): array
    {
        $daysLate = $deadline->diffInDays($filingDate, false);

        if ($daysLate <= 0) {
            return ['penalty' => 0, 'interest' => 0, 'total' => 0];
        }

        $penalty = 0;
        $interest = 0;

        switch ($declarationType) {
            case 'vat_declaration':
                // Belgian VAT late filing penalty
                if ($daysLate <= 14) {
                    $penalty = 50;
                } elseif ($daysLate <= 30) {
                    $penalty = 250;
                } else {
                    $penalty = 2500;
                }

                // Interest: 7% per year
                $interest = $amount * 0.07 * ($daysLate / 365);
                break;

            case 'corporate_tax':
                // 10% to 200% increase
                $penalty = min($amount * 0.10, $amount * 2.00);
                break;

            case 'annual_accounts':
                // Fixed penalties based on company size
                if ($daysLate <= 30) {
                    $penalty = 120;
                } elseif ($daysLate <= 60) {
                    $penalty = 600;
                } else {
                    $penalty = 12000;
                }
                break;
        }

        return [
            'penalty' => round($penalty, 2),
            'interest' => round($interest, 2),
            'total' => round($penalty + $interest, 2),
            'days_late' => $daysLate,
        ];
    }

    /**
     * Get EU country codes
     */
    protected function getEUCountryCodes(): array
    {
        return [
            'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI',
            'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT',
            'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'
        ];
    }

    /**
     * Format currency
     */
    protected function formatCurrency(float $amount): string
    {
        return number_format($amount, 2, ',', ' ') . ' €';
    }
}
