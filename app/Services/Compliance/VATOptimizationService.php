<?php

namespace App\Services\Compliance;

use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VATOptimizationService
{
    /**
     * Analyze VAT optimization opportunities
     */
    public function analyzeOptimizations(string $companyId): array
    {
        $optimizations = [];

        // Check regime optimization
        $regimeOptimization = $this->analyzeVATRegime($companyId);
        if ($regimeOptimization) {
            $optimizations[] = $regimeOptimization;
        }

        // Check deductible VAT opportunities
        $deductibleOpportunities = $this->analyzeDeductibleVAT($companyId);
        $optimizations = array_merge($optimizations, $deductibleOpportunities);

        // Check cash vs accrual accounting
        $accountingMethodOptimization = $this->analyzeAccountingMethod($companyId);
        if ($accountingMethodOptimization) {
            $optimizations[] = $accountingMethodOptimization;
        }

        // Check reverse charge opportunities
        $reverseChargeOpportunities = $this->analyzeReverseChargeOpportunities($companyId);
        $optimizations = array_merge($optimizations, $reverseChargeOpportunities);

        return $optimizations;
    }

    /**
     * Analyze optimal VAT regime (monthly vs quarterly)
     */
    protected function analyzeVATRegime(string $companyId): ?array
    {
        // Calculate average monthly VAT to pay
        $last12Months = Invoice::where('company_id', $companyId)
            ->whereDate('issue_date', '>=', now()->subYear())
            ->selectRaw('MONTH(issue_date) as month, SUM(vat_amount) as total_vat')
            ->groupBy('month')
            ->get();

        $avgMonthlyVAT = $last12Months->avg('total_vat');

        // Calculate average deductible VAT
        $avgMonthlyDeductibleVAT = Expense::where('company_id', $companyId)
            ->whereDate('expense_date', '>=', now()->subYear())
            ->avg(DB::raw('COALESCE(vat_amount, 0)'));

        $netVAT = $avgMonthlyVAT - $avgMonthlyDeductibleVAT;

        // If usually in VAT credit, quarterly is better (cash flow)
        if ($netVAT < 0) {
            return [
                'type' => 'vat_regime',
                'title' => 'Optimisation Régime TVA',
                'description' => 'Passage au régime trimestriel recommandé',
                'current_situation' => 'Régime mensuel avec crédit TVA récurrent',
                'recommendation' => 'Passer au régime trimestriel pour améliorer la trésorerie',
                'estimated_benefit' => abs($netVAT) * 2,
                'benefit_type' => 'cash_flow',
                'complexity' => 'low',
                'action_required' => 'Demande au SPF Finances',
            ];
        }

        // If usually paying VAT, monthly is better (smaller amounts)
        if ($netVAT > 1000) {
            return [
                'type' => 'vat_regime',
                'title' => 'Régime TVA Optimal',
                'description' => 'Régime mensuel adapté à votre situation',
                'current_situation' => 'TVA à payer mensuelle significative',
                'recommendation' => 'Le régime mensuel actuel est optimal pour votre trésorerie',
                'estimated_benefit' => 0,
                'benefit_type' => 'information',
                'complexity' => 'none',
                'action_required' => 'Aucune',
            ];
        }

        return null;
    }

    /**
     * Analyze deductible VAT opportunities
     */
    protected function analyzeDeductibleVAT(string $companyId): array
    {
        $opportunities = [];

        // Check for expenses without VAT deduction
        $expensesWithoutVAT = Expense::where('company_id', $companyId)
            ->whereDate('expense_date', '>=', now()->subMonths(6))
            ->where('amount', '>', 0)
            ->where(function ($query) {
                $query->whereNull('vat_amount')
                      ->orWhere('vat_amount', 0);
            })
            ->get();

        $potentialDeductibleAmount = 0;

        foreach ($expensesWithoutVAT as $expense) {
            // Check if expense is potentially eligible for VAT deduction
            if ($this->isPotentiallyVATDeductible($expense)) {
                // Estimate VAT at 21% (standard rate)
                $potentialVAT = $expense->amount * 0.21 / 1.21;
                $potentialDeductibleAmount += $potentialVAT;
            }
        }

        if ($potentialDeductibleAmount > 100) {
            $opportunities[] = [
                'type' => 'deductible_vat',
                'title' => 'TVA Déductible Manquante',
                'description' => "{$expensesWithoutVAT->count()} dépenses sans TVA déductible identifiée",
                'current_situation' => 'Dépenses potentiellement sans récupération TVA',
                'recommendation' => 'Vérifier et comptabiliser la TVA déductible sur ces dépenses',
                'estimated_benefit' => round($potentialDeductibleAmount, 2),
                'benefit_type' => 'tax_savings',
                'complexity' => 'low',
                'action_required' => 'Révision comptable des dépenses',
            ];
        }

        // Check for vehicles with mixed use
        $this->checkVehicleVATDeduction($companyId, $opportunities);

        return $opportunities;
    }

    /**
     * Check if expense is potentially VAT deductible
     */
    protected function isPotentiallyVATDeductible($expense): bool
    {
        $deductibleKeywords = [
            'fourniture', 'matériel', 'équipement', 'logiciel', 'abonnement',
            'formation', 'publicité', 'marketing', 'bureau', 'loyer',
            'électricité', 'internet', 'téléphone', 'assurance', 'maintenance'
        ];

        $description = strtolower($expense->description ?? '');
        $category = strtolower($expense->category ?? '');

        foreach ($deductibleKeywords as $keyword) {
            if (str_contains($description, $keyword) || str_contains($category, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check vehicle VAT deduction optimization
     */
    protected function checkVehicleVATDeduction(string $companyId, array &$opportunities): void
    {
        $vehicleExpenses = Expense::where('company_id', $companyId)
            ->whereDate('expense_date', '>=', now()->subYear())
            ->where(function ($query) {
                $query->where('category', 'LIKE', '%véhicule%')
                      ->orWhere('category', 'LIKE', '%transport%')
                      ->orWhere('description', 'LIKE', '%carburant%')
                      ->orWhere('description', 'LIKE', '%essence%')
                      ->orWhere('description', 'LIKE', '%diesel%');
            })
            ->sum('amount');

        if ($vehicleExpenses > 5000) {
            $opportunities[] = [
                'type' => 'vehicle_vat',
                'title' => 'Optimisation TVA Véhicules',
                'description' => 'Dépenses véhicules significatives détectées',
                'current_situation' => "€{$this->formatAmount($vehicleExpenses)} de dépenses véhicules annuelles",
                'recommendation' => 'Vérifier les règles de déduction TVA pour véhicules (carburant: 50% déductible si usage mixte)',
                'estimated_benefit' => round($vehicleExpenses * 0.21 * 0.50 / 1.21, 2),
                'benefit_type' => 'tax_optimization',
                'complexity' => 'medium',
                'action_required' => 'Documenter l\'usage professionnel vs privé',
            ];
        }
    }

    /**
     * Analyze cash vs accrual accounting method
     */
    protected function analyzeAccountingMethod(string $companyId): ?array
    {
        // Cash accounting ("régime de caisse") allowed if turnover < €2.5M

        $company = Company::find($companyId);
        $annualTurnover = Invoice::where('company_id', $companyId)
            ->whereYear('issue_date', now()->year)
            ->sum('total_amount');

        if ($annualTurnover < 2500000) {
            // Calculate cash flow impact of switching to cash accounting
            $unpaidInvoices = Invoice::where('company_id', $companyId)
                ->where('status', 'sent')
                ->whereNull('payment_date')
                ->sum('vat_amount');

            if ($unpaidInvoices > 1000) {
                return [
                    'type' => 'accounting_method',
                    'title' => 'Régime de Caisse',
                    'description' => 'Passage au régime de caisse possible',
                    'current_situation' => "€{$this->formatAmount($unpaidInvoices)} de TVA due sur factures impayées",
                    'recommendation' => 'Passer au régime de caisse pour ne payer la TVA qu\'à l\'encaissement',
                    'estimated_benefit' => round($unpaidInvoices, 2),
                    'benefit_type' => 'cash_flow',
                    'complexity' => 'medium',
                    'action_required' => 'Demande au SPF Finances avec justification CA < €2.5M',
                ];
            }
        }

        return null;
    }

    /**
     * Analyze reverse charge opportunities
     */
    protected function analyzeReverseChargeOpportunities(string $companyId): array
    {
        $opportunities = [];

        // Check for potential reverse charge on services from abroad
        $foreignServiceExpenses = Expense::where('company_id', $companyId)
            ->whereDate('expense_date', '>=', now()->subMonths(3))
            ->where(function ($query) {
                $query->where('description', 'LIKE', '%consulting%')
                      ->orWhere('description', 'LIKE', '%software%')
                      ->orWhere('description', 'LIKE', '%licence%')
                      ->orWhere('description', 'LIKE', '%cloud%')
                      ->orWhere('description', 'LIKE', '%SaaS%');
            })
            ->where('vat_amount', '>', 0)
            ->get();

        $potentialReverseCharge = 0;

        foreach ($foreignServiceExpenses as $expense) {
            $potentialReverseCharge += $expense->vat_amount;
        }

        if ($potentialReverseCharge > 100) {
            $opportunities[] = [
                'type' => 'reverse_charge',
                'title' => 'Reverse Charge sur Services',
                'description' => 'Services étrangers potentiellement soumis au reverse charge',
                'current_situation' => "€{$this->formatAmount($potentialReverseCharge)} de TVA sur services étrangers",
                'recommendation' => 'Vérifier l\'application du reverse charge pour optimiser trésorerie',
                'estimated_benefit' => 0,
                'benefit_type' => 'compliance',
                'complexity' => 'low',
                'action_required' => 'Révision des factures fournisseurs étrangers',
            ];
        }

        return $opportunities;
    }

    /**
     * Simulate VAT regime change impact
     */
    public function simulateRegimeChange(string $companyId, string $newRegime): array
    {
        // Get historical data
        $last12Months = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            $vatToCollect = Invoice::where('company_id', $companyId)
                ->whereYear('issue_date', $month->year)
                ->whereMonth('issue_date', $month->month)
                ->sum('vat_amount');

            $vatDeductible = Expense::where('company_id', $companyId)
                ->whereYear('expense_date', $month->year)
                ->whereMonth('expense_date', $month->month)
                ->sum(DB::raw('COALESCE(vat_amount, 0)'));

            $last12Months[] = [
                'month' => $month->format('Y-m'),
                'vat_to_collect' => $vatToCollect,
                'vat_deductible' => $vatDeductible,
                'net_vat' => $vatToCollect - $vatDeductible,
            ];
        }

        // Simulate monthly regime
        $monthlyRegime = [
            'total_vat_paid' => 0,
            'total_vat_credit' => 0,
            'payment_dates' => [],
        ];

        foreach ($last12Months as $monthData) {
            $netVAT = $monthData['net_vat'];
            $paymentDate = Carbon::parse($monthData['month'])->addMonth()->day(20);

            if ($netVAT > 0) {
                $monthlyRegime['total_vat_paid'] += $netVAT;
                $monthlyRegime['payment_dates'][] = [
                    'date' => $paymentDate,
                    'amount' => $netVAT,
                ];
            } else {
                $monthlyRegime['total_vat_credit'] += abs($netVAT);
            }
        }

        // Simulate quarterly regime
        $quarterlyRegime = [
            'total_vat_paid' => 0,
            'total_vat_credit' => 0,
            'payment_dates' => [],
        ];

        for ($quarter = 0; $quarter < 4; $quarter++) {
            $quarterMonths = array_slice($last12Months, $quarter * 3, 3);
            $quarterNetVAT = array_sum(array_column($quarterMonths, 'net_vat'));
            $quarterEndDate = Carbon::parse($quarterMonths[2]['month'])->endOfMonth();
            $paymentDate = $quarterEndDate->copy()->addMonth()->day(20);

            if ($quarterNetVAT > 0) {
                $quarterlyRegime['total_vat_paid'] += $quarterNetVAT;
                $quarterlyRegime['payment_dates'][] = [
                    'date' => $paymentDate,
                    'amount' => $quarterNetVAT,
                ];
            } else {
                $quarterlyRegime['total_vat_credit'] += abs($quarterNetVAT);
            }
        }

        // Calculate cash flow impact
        $cashFlowDifference = $this->calculateCashFlowImpact($monthlyRegime, $quarterlyRegime);

        return [
            'current_regime' => 'monthly',
            'simulated_regime' => $newRegime,
            'monthly_regime' => $monthlyRegime,
            'quarterly_regime' => $quarterlyRegime,
            'cash_flow_impact' => $cashFlowDifference,
            'recommendation' => $cashFlowDifference > 0 ? 'quarterly' : 'monthly',
        ];
    }

    /**
     * Calculate cash flow impact between regimes
     */
    protected function calculateCashFlowImpact(array $monthlyRegime, array $quarterlyRegime): float
    {
        // Simplified calculation: average time value of money
        // Quarterly regime delays payments by ~1.5 months on average
        $avgDelay = 1.5;
        $avgMonthlyPayment = $monthlyRegime['total_vat_paid'] / 12;

        // Time value = opportunity cost of capital (assume 5% annual)
        $opportunityCost = 0.05 / 12;

        $cashFlowBenefit = $avgMonthlyPayment * $avgDelay * $opportunityCost * 12;

        return round($cashFlowBenefit, 2);
    }

    /**
     * Format amount
     */
    protected function formatAmount(float $amount): string
    {
        return number_format($amount, 2, ',', ' ');
    }
}
