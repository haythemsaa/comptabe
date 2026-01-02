<?php

namespace App\Services\AI;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\BankTransaction;
use App\Models\Partner;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BusinessIntelligenceService
{
    /**
     * Get comprehensive business intelligence dashboard data.
     */
    public function getDashboardData(string $companyId): array
    {
        return Cache::remember("bi_dashboard_{$companyId}", 300, function () use ($companyId) {
            return [
                'health_score' => $this->calculateHealthScore($companyId),
                'insights' => $this->generateInsights($companyId),
                'anomalies' => $this->detectAnomalies($companyId),
                'predictions' => $this->generatePredictions($companyId),
                'kpis' => $this->calculateKPIs($companyId),
                'trends' => $this->analyzeTrends($companyId),
            ];
        });
    }

    /**
     * Calculate overall financial health score (0-100).
     */
    public function calculateHealthScore(string $companyId): array
    {
        $liquidityScore = $this->calculateLiquidityScore($companyId);
        $profitabilityScore = $this->calculateProfitabilityScore($companyId);
        $growthScore = $this->calculateGrowthScore($companyId);
        $debtScore = $this->calculateDebtScore($companyId);

        // Weighted average
        $overallScore = (
            $liquidityScore * 0.30 +
            $profitabilityScore * 0.30 +
            $growthScore * 0.25 +
            $debtScore * 0.15
        );

        // Calculate trend (compare with last month)
        $lastMonthScore = Cache::get("health_score_{$companyId}_last_month", $overallScore);
        $trend = $overallScore - $lastMonthScore;

        // Store current as last month for next calculation
        Cache::put("health_score_{$companyId}_last_month", $overallScore, now()->addMonth());

        return [
            'overall' => round($overallScore, 1),
            'trend' => round($trend, 1),
            'components' => [
                'liquidity' => [
                    'score' => round($liquidityScore, 1),
                    'label' => 'Liquidité',
                    'description' => 'Capacité à honorer les obligations court terme',
                ],
                'profitability' => [
                    'score' => round($profitabilityScore, 1),
                    'label' => 'Rentabilité',
                    'description' => 'Génération de profit par rapport aux coûts',
                ],
                'growth' => [
                    'score' => round($growthScore, 1),
                    'label' => 'Croissance',
                    'description' => 'Évolution du chiffre d\'affaires',
                ],
                'debt' => [
                    'score' => round($debtScore, 1),
                    'label' => 'Endettement',
                    'description' => 'Niveau d\'endettement sain',
                ],
            ],
            'rating' => $this->getHealthRating($overallScore),
            'next_update' => now()->addHours(6)->toIso8601String(),
        ];
    }

    /**
     * Calculate liquidity score based on cash flow and receivables.
     */
    protected function calculateLiquidityScore(string $companyId): float
    {
        $currentBalance = BankTransaction::where('company_id', $companyId)
            ->sum('amount');

        $receivables = Invoice::where('company_id', $companyId)
            ->where('status', 'sent')
            ->where('due_date', '>=', now())
            ->sum('total_amount');

        $payables = Expense::where('company_id', $companyId)
            ->where('status', 'pending')
            ->sum('total_amount');

        $currentRatio = $payables > 0
            ? ($currentBalance + $receivables) / $payables
            : 100;

        // Current ratio scoring
        if ($currentRatio >= 2) return 100;
        if ($currentRatio >= 1.5) return 85;
        if ($currentRatio >= 1) return 70;
        if ($currentRatio >= 0.75) return 50;
        if ($currentRatio >= 0.5) return 30;
        return 10;
    }

    /**
     * Calculate profitability score.
     */
    protected function calculateProfitabilityScore(string $companyId): float
    {
        $period = now()->subMonths(3);

        $revenue = Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->where('issue_date', '>=', $period)
            ->sum('total_amount');

        $expenses = Expense::where('company_id', $companyId)
            ->where('status', 'paid')
            ->where('expense_date', '>=', $period)
            ->sum('total_amount');

        if ($revenue == 0) return 50;

        $profitMargin = (($revenue - $expenses) / $revenue) * 100;

        // Profit margin scoring
        if ($profitMargin >= 30) return 100;
        if ($profitMargin >= 20) return 90;
        if ($profitMargin >= 15) return 80;
        if ($profitMargin >= 10) return 70;
        if ($profitMargin >= 5) return 60;
        if ($profitMargin >= 0) return 50;
        if ($profitMargin >= -10) return 30;
        return 10;
    }

    /**
     * Calculate growth score.
     */
    protected function calculateGrowthScore(string $companyId): float
    {
        $currentPeriod = Invoice::where('company_id', $companyId)
            ->where('issue_date', '>=', now()->subMonths(3))
            ->sum('total_amount');

        $previousPeriod = Invoice::where('company_id', $companyId)
            ->whereBetween('issue_date', [now()->subMonths(6), now()->subMonths(3)])
            ->sum('total_amount');

        if ($previousPeriod == 0) return 50;

        $growthRate = (($currentPeriod - $previousPeriod) / $previousPeriod) * 100;

        // Growth rate scoring
        if ($growthRate >= 30) return 100;
        if ($growthRate >= 20) return 90;
        if ($growthRate >= 10) return 80;
        if ($growthRate >= 5) return 70;
        if ($growthRate >= 0) return 60;
        if ($growthRate >= -5) return 50;
        if ($growthRate >= -10) return 30;
        return 10;
    }

    /**
     * Calculate debt score (inverse - lower debt = higher score).
     */
    protected function calculateDebtScore(string $companyId): float
    {
        $totalAssets = BankTransaction::where('company_id', $companyId)
            ->where('amount', '>', 0)
            ->sum('amount');

        $totalLiabilities = Expense::where('company_id', $companyId)
            ->where('status', 'pending')
            ->sum('total_amount');

        if ($totalAssets == 0) return 50;

        $debtRatio = ($totalLiabilities / $totalAssets) * 100;

        // Lower debt ratio = higher score
        if ($debtRatio <= 20) return 100;
        if ($debtRatio <= 40) return 85;
        if ($debtRatio <= 60) return 70;
        if ($debtRatio <= 80) return 50;
        if ($debtRatio <= 100) return 30;
        return 10;
    }

    /**
     * Get health rating based on score.
     */
    protected function getHealthRating(float $score): array
    {
        if ($score >= 85) {
            return ['label' => 'Excellent', 'color' => 'success', 'icon' => 'trending-up'];
        } elseif ($score >= 70) {
            return ['label' => 'Bon', 'color' => 'success', 'icon' => 'check-circle'];
        } elseif ($score >= 55) {
            return ['label' => 'Moyen', 'color' => 'warning', 'icon' => 'alert-circle'];
        } elseif ($score >= 40) {
            return ['label' => 'Préoccupant', 'color' => 'warning', 'icon' => 'alert-triangle'];
        } else {
            return ['label' => 'Critique', 'color' => 'danger', 'icon' => 'alert-octagon'];
        }
    }

    /**
     * Generate AI-powered business insights.
     */
    public function generateInsights(string $companyId): array
    {
        $insights = [];

        // Insight 1: Payment delays analysis
        $paymentDelays = $this->analyzePaymentDelays($companyId);
        if ($paymentDelays) {
            $insights[] = $paymentDelays;
        }

        // Insight 2: Top revenue sources
        $topRevenue = $this->analyzeTopRevenueSources($companyId);
        if ($topRevenue) {
            $insights[] = $topRevenue;
        }

        // Insight 3: Cost optimization opportunities
        $costOptimization = $this->analyzeCostOptimization($companyId);
        if ($costOptimization) {
            $insights[] = $costOptimization;
        }

        // Insight 4: VAT optimization
        $vatOptimization = $this->analyzeVATOptimization($companyId);
        if ($vatOptimization) {
            $insights[] = $vatOptimization;
        }

        // Insight 5: Cash flow prediction
        $cashFlowPrediction = $this->analyzeCashFlowPrediction($companyId);
        if ($cashFlowPrediction) {
            $insights[] = $cashFlowPrediction;
        }

        return array_slice($insights, 0, 5); // Top 5 insights
    }

    /**
     * Analyze payment delays by customer.
     */
    protected function analyzePaymentDelays(string $companyId): ?array
    {
        $delayedInvoices = Invoice::where('company_id', $companyId)
            ->where('status', 'sent')
            ->where('due_date', '<', now())
            ->with('partner')
            ->get();

        if ($delayedInvoices->isEmpty()) {
            return null;
        }

        $partnerDelays = $delayedInvoices->groupBy('partner_id')->map(function ($invoices) {
            $avgDelay = $invoices->avg(fn($inv) => now()->diffInDays($inv->due_date));
            return [
                'partner' => $invoices->first()->partner?->name ?? 'Inconnu',
                'count' => $invoices->count(),
                'avg_delay' => round($avgDelay),
                'total_amount' => $invoices->sum('total_amount'),
            ];
        })->sortByDesc('avg_delay')->first();

        if (!$partnerDelays || $partnerDelays['avg_delay'] < 15) {
            return null;
        }

        return [
            'type' => 'payment_delay',
            'severity' => $partnerDelays['avg_delay'] > 45 ? 'high' : 'medium',
            'title' => 'Retards de paiement significatifs',
            'description' => "Vos factures à {$partnerDelays['partner']} sont payées avec {$partnerDelays['avg_delay']} jours de retard en moyenne.",
            'impact' => "Impact trésorerie: " . number_format($partnerDelays['total_amount'], 2) . " € bloqués",
            'recommendation' => "Réviser les conditions de paiement ou mettre en place des relances automatiques.",
            'action_url' => route('partners.show', ['partner' => $delayedInvoices->first()->partner_id]),
            'action_text' => 'Voir le client',
        ];
    }

    /**
     * Analyze top revenue sources.
     */
    protected function analyzeTopRevenueSources(string $companyId): ?array
    {
        $topPartner = Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->where('issue_date', '>=', now()->subMonths(6))
            ->select('partner_id', DB::raw('SUM(total_amount) as total'))
            ->groupBy('partner_id')
            ->orderByDesc('total')
            ->with('partner')
            ->first();

        if (!$topPartner) {
            return null;
        }

        $totalRevenue = Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->where('issue_date', '>=', now()->subMonths(6))
            ->sum('total_amount');

        $percentage = $totalRevenue > 0 ? ($topPartner->total / $totalRevenue) * 100 : 0;

        return [
            'type' => 'revenue_concentration',
            'severity' => $percentage > 40 ? 'medium' : 'low',
            'title' => 'Concentration du chiffre d\'affaires',
            'description' => "{$topPartner->partner?->name} représente " . round($percentage, 1) . "% de votre CA (6 derniers mois).",
            'impact' => "Dépendance élevée : diversification recommandée",
            'recommendation' => $percentage > 40
                ? "Diversifier votre portefeuille clients pour réduire le risque de dépendance."
                : "Bon équilibre du portefeuille clients.",
            'action_url' => route('partners.index'),
            'action_text' => 'Voir tous les clients',
        ];
    }

    /**
     * Analyze cost optimization opportunities.
     */
    protected function analyzeCostOptimization(string $companyId): ?array
    {
        $expenses = Expense::where('company_id', $companyId)
            ->where('expense_date', '>=', now()->subMonths(3))
            ->select('category', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        if ($expenses->isEmpty()) {
            return null;
        }

        $topCategory = $expenses->first();
        $totalExpenses = $expenses->sum('total');
        $percentage = ($topCategory->total / $totalExpenses) * 100;

        return [
            'type' => 'cost_optimization',
            'severity' => 'low',
            'title' => 'Opportunité d\'optimisation des coûts',
            'description' => "Catégorie '{$topCategory->category}' : " . number_format($topCategory->total, 2) . " € (" . round($percentage, 1) . "% des dépenses).",
            'impact' => "Économies potentielles de 10-15% via optimisation",
            'recommendation' => "Analyser les fournisseurs de cette catégorie pour négocier de meilleures conditions.",
            'action_url' => route('expenses.index', ['category' => $topCategory->category]),
            'action_text' => 'Voir les dépenses',
        ];
    }

    /**
     * Analyze VAT optimization opportunities.
     */
    protected function analyzeVATOptimization(string $companyId): ?array
    {
        $euExpenses = Expense::where('company_id', $companyId)
            ->where('expense_date', '>=', now()->subMonths(3))
            ->whereHas('partner', function ($q) {
                $q->where('country', '!=', 'BE')
                  ->whereIn('country', ['FR', 'DE', 'NL', 'LU', 'IT', 'ES', 'PT']);
            })
            ->where('vat_rate', '>', 0)
            ->sum('vat_amount');

        if ($euExpenses > 100) {
            return [
                'type' => 'vat_optimization',
                'severity' => 'medium',
                'title' => 'Optimisation TVA possible',
                'description' => "Vous avez payé " . number_format($euExpenses, 2) . " € de TVA sur des services intra-UE.",
                'impact' => "Économie potentielle via autoliquidation (reverse charge)",
                'recommendation' => "Appliquer le mécanisme d'autoliquidation pour les prestations de services B2B intra-UE.",
                'action_url' => route('expenses.index'),
                'action_text' => 'Voir les dépenses concernées',
            ];
        }

        return null;
    }

    /**
     * Analyze cash flow prediction.
     */
    protected function analyzeCashFlowPrediction(string $companyId): ?array
    {
        $currentBalance = BankTransaction::where('company_id', $companyId)->sum('amount');

        $expectedInflows = Invoice::where('company_id', $companyId)
            ->where('status', 'sent')
            ->where('due_date', '<=', now()->addDays(30))
            ->sum('total_amount');

        $expectedOutflows = Expense::where('company_id', $companyId)
            ->where('status', 'pending')
            ->where('due_date', '<=', now()->addDays(30))
            ->sum('total_amount');

        $projectedBalance = $currentBalance + $expectedInflows - $expectedOutflows;

        if ($projectedBalance < 0) {
            return [
                'type' => 'cash_flow_alert',
                'severity' => 'high',
                'title' => 'Alerte trésorerie négative prévue',
                'description' => "Solde projeté dans 30j : " . number_format($projectedBalance, 2) . " €",
                'impact' => "Risque de difficultés de paiement",
                'recommendation' => "Accélérer les encaissements ou reporter certaines dépenses non urgentes.",
                'action_url' => route('dashboard'),
                'action_text' => 'Voir le plan de trésorerie',
            ];
        }

        return null;
    }

    /**
     * Detect anomalies in transactions and data.
     */
    public function detectAnomalies(string $companyId): array
    {
        $anomalies = [];

        // Detect unusual amounts
        $anomalies = array_merge($anomalies, $this->detectUnusualAmounts($companyId));

        // Detect duplicate transactions
        $anomalies = array_merge($anomalies, $this->detectDuplicateTransactions($companyId));

        // Detect VAT discrepancies
        $anomalies = array_merge($anomalies, $this->detectVATDiscrepancies($companyId));

        // Detect missing documents
        $anomalies = array_merge($anomalies, $this->detectMissingDocuments($companyId));

        return array_slice($anomalies, 0, 10); // Top 10 anomalies
    }

    /**
     * Detect unusual transaction amounts.
     */
    protected function detectUnusualAmounts(string $companyId): array
    {
        $expenses = Expense::where('company_id', $companyId)
            ->where('expense_date', '>=', now()->subMonths(1))
            ->get();

        if ($expenses->count() < 10) {
            return [];
        }

        $mean = $expenses->avg('total_amount');
        $stdDev = $this->calculateStdDev($expenses->pluck('total_amount')->toArray());

        $threshold = $mean + (3 * $stdDev); // 3 standard deviations

        $unusual = $expenses->filter(fn($exp) => $exp->total_amount > $threshold);

        return $unusual->map(function ($expense) use ($mean) {
            return [
                'type' => 'unusual_amount',
                'severity' => 'medium',
                'title' => 'Montant inhabituel détecté',
                'description' => "Dépense de " . number_format($expense->total_amount, 2) . " € chez {$expense->partner?->name}",
                'details' => "Montant " . round(($expense->total_amount / $mean - 1) * 100) . "% supérieur à la moyenne",
                'entity_type' => 'expense',
                'entity_id' => $expense->id,
                'date' => $expense->expense_date->toDateString(),
            ];
        })->toArray();
    }

    /**
     * Detect potential duplicate transactions.
     */
    protected function detectDuplicateTransactions(string $companyId): array
    {
        $duplicates = Expense::where('company_id', $companyId)
            ->where('expense_date', '>=', now()->subDays(7))
            ->select('partner_id', 'total_amount', 'expense_date', DB::raw('COUNT(*) as count'))
            ->groupBy('partner_id', 'total_amount', 'expense_date')
            ->having('count', '>', 1)
            ->get();

        return $duplicates->map(function ($dup) {
            return [
                'type' => 'duplicate_transaction',
                'severity' => 'high',
                'title' => 'Transaction potentiellement en double',
                'description' => "Montant identique (" . number_format($dup->total_amount, 2) . " €) détecté {$dup->count} fois",
                'details' => "Vérifier s'il ne s'agit pas d'une erreur de saisie",
                'entity_type' => 'expense',
                'date' => $dup->expense_date,
            ];
        })->toArray();
    }

    /**
     * Detect VAT calculation discrepancies.
     */
    protected function detectVATDiscrepancies(string $companyId): array
    {
        $invoices = Invoice::where('company_id', $companyId)
            ->where('issue_date', '>=', now()->subMonth())
            ->get();

        $discrepancies = $invoices->filter(function ($invoice) {
            $expectedVAT = ($invoice->subtotal * $invoice->vat_rate) / 100;
            $diff = abs($expectedVAT - $invoice->vat_amount);
            return $diff > 0.10; // More than 10 cents difference
        });

        return $discrepancies->map(function ($invoice) {
            $expectedVAT = ($invoice->subtotal * $invoice->vat_rate) / 100;
            $diff = $invoice->vat_amount - $expectedVAT;

            return [
                'type' => 'vat_discrepancy',
                'severity' => abs($diff) > 10 ? 'high' : 'low',
                'title' => 'Incohérence TVA détectée',
                'description' => "Facture {$invoice->invoice_number} : différence de " . number_format(abs($diff), 2) . " €",
                'details' => "TVA calculée: " . number_format($expectedVAT, 2) . " € vs enregistrée: " . number_format($invoice->vat_amount, 2) . " €",
                'entity_type' => 'invoice',
                'entity_id' => $invoice->id,
                'date' => $invoice->issue_date->toDateString(),
            ];
        })->toArray();
    }

    /**
     * Detect missing supporting documents.
     */
    protected function detectMissingDocuments(string $companyId): array
    {
        $expensesWithoutDocs = Expense::where('company_id', $companyId)
            ->where('expense_date', '>=', now()->subMonths(3))
            ->where('total_amount', '>', 100)
            ->whereDoesntHave('documents')
            ->get();

        return $expensesWithoutDocs->take(5)->map(function ($expense) {
            return [
                'type' => 'missing_document',
                'severity' => $expense->total_amount > 1000 ? 'high' : 'medium',
                'title' => 'Justificatif manquant',
                'description' => "Dépense de " . number_format($expense->total_amount, 2) . " € sans document",
                'details' => "Pièce justificative requise pour conformité fiscale",
                'entity_type' => 'expense',
                'entity_id' => $expense->id,
                'date' => $expense->expense_date->toDateString(),
            ];
        })->toArray();
    }

    /**
     * Generate business predictions.
     */
    public function generatePredictions(string $companyId): array
    {
        return [
            'revenue' => $this->predictRevenue($companyId),
            'cash_flow' => $this->predictCashFlow($companyId),
            'expenses' => $this->predictExpenses($companyId),
        ];
    }

    /**
     * Predict revenue for next 3/6/12 months.
     */
    protected function predictRevenue(string $companyId): array
    {
        $historicalRevenue = Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->where('issue_date', '>=', now()->subMonths(12))
            ->selectRaw('MONTH(issue_date) as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        if (empty($historicalRevenue)) {
            return [
                '3_months' => 0,
                '6_months' => 0,
                '12_months' => 0,
                'confidence' => 0,
            ];
        }

        $avgMonthly = array_sum($historicalRevenue) / count($historicalRevenue);

        // Simple linear regression for trend
        $trend = $this->calculateTrend(array_values($historicalRevenue));

        return [
            '3_months' => round($avgMonthly * 3 * (1 + $trend), 2),
            '6_months' => round($avgMonthly * 6 * (1 + $trend), 2),
            '12_months' => round($avgMonthly * 12 * (1 + $trend), 2),
            'confidence' => count($historicalRevenue) >= 6 ? 0.75 : 0.50,
            'trend' => $trend > 0 ? 'up' : ($trend < 0 ? 'down' : 'stable'),
        ];
    }

    /**
     * Predict cash flow scenarios.
     */
    protected function predictCashFlow(string $companyId): array
    {
        $currentBalance = BankTransaction::where('company_id', $companyId)->sum('amount');

        $avgMonthlyInflow = Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->where('payment_date', '>=', now()->subMonths(6))
            ->avg(DB::raw('total_amount'));

        $avgMonthlyOutflow = Expense::where('company_id', $companyId)
            ->where('status', 'paid')
            ->where('payment_date', '>=', now()->subMonths(6))
            ->avg(DB::raw('total_amount'));

        $optimistic = $currentBalance + (($avgMonthlyInflow * 1.2 - $avgMonthlyOutflow * 0.8) * 3);
        $realistic = $currentBalance + (($avgMonthlyInflow - $avgMonthlyOutflow) * 3);
        $pessimistic = $currentBalance + (($avgMonthlyInflow * 0.8 - $avgMonthlyOutflow * 1.2) * 3);

        return [
            'current' => round($currentBalance, 2),
            'scenarios' => [
                'optimistic' => round($optimistic, 2),
                'realistic' => round($realistic, 2),
                'pessimistic' => round($pessimistic, 2),
            ],
            'horizon' => '3_months',
            'recommendation' => $pessimistic < 0
                ? 'Attention : scénario pessimiste négatif. Prévoir une marge de sécurité.'
                : 'Trésorerie saine dans tous les scénarios.',
        ];
    }

    /**
     * Predict expenses.
     */
    protected function predictExpenses(string $companyId): array
    {
        $avgMonthly = Expense::where('company_id', $companyId)
            ->where('expense_date', '>=', now()->subMonths(6))
            ->selectRaw('AVG(total_amount) as avg')
            ->value('avg') ?? 0;

        return [
            'next_month' => round($avgMonthly, 2),
            'next_quarter' => round($avgMonthly * 3, 2),
            'confidence' => 0.70,
        ];
    }

    /**
     * Calculate KPIs.
     */
    public function calculateKPIs(string $companyId): array
    {
        $period = now()->subMonths(3);

        return [
            'total_revenue' => Invoice::where('company_id', $companyId)
                ->where('status', 'paid')
                ->where('issue_date', '>=', $period)
                ->sum('total_amount'),
            'total_expenses' => Expense::where('company_id', $companyId)
                ->where('status', 'paid')
                ->where('expense_date', '>=', $period)
                ->sum('total_amount'),
            'outstanding_invoices' => Invoice::where('company_id', $companyId)
                ->where('status', 'sent')
                ->sum('total_amount'),
            'overdue_invoices' => Invoice::where('company_id', $companyId)
                ->where('status', 'sent')
                ->where('due_date', '<', now())
                ->sum('total_amount'),
            'avg_payment_delay' => Invoice::where('company_id', $companyId)
                ->where('status', 'paid')
                ->whereNotNull('payment_date')
                ->where('payment_date', '>=', $period)
                ->selectRaw('AVG(DATEDIFF(payment_date, due_date)) as avg_delay')
                ->value('avg_delay') ?? 0,
        ];
    }

    /**
     * Analyze trends.
     */
    public function analyzeTrends(string $companyId): array
    {
        $months = 12;
        $revenue = [];
        $expenses = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');

            $revenue[$monthKey] = Invoice::where('company_id', $companyId)
                ->where('status', 'paid')
                ->whereYear('issue_date', $date->year)
                ->whereMonth('issue_date', $date->month)
                ->sum('total_amount');

            $expenses[$monthKey] = Expense::where('company_id', $companyId)
                ->where('status', 'paid')
                ->whereYear('expense_date', $date->year)
                ->whereMonth('expense_date', $date->month)
                ->sum('total_amount');
        }

        return [
            'labels' => array_keys($revenue),
            'revenue' => array_values($revenue),
            'expenses' => array_values($expenses),
            'profit' => array_map(fn($r, $e) => $r - $e, array_values($revenue), array_values($expenses)),
        ];
    }

    /**
     * Calculate standard deviation.
     */
    protected function calculateStdDev(array $values): float
    {
        if (empty($values)) return 0;

        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / count($values);

        return sqrt($variance);
    }

    /**
     * Calculate trend coefficient.
     */
    protected function calculateTrend(array $values): float
    {
        if (count($values) < 2) return 0;

        $n = count($values);
        $x = range(1, $n);
        $y = $values;

        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = array_sum(array_map(fn($xi, $yi) => $xi * $yi, $x, $y));
        $sumX2 = array_sum(array_map(fn($xi) => $xi * $xi, $x));

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $mean = $sumY / $n;

        return $mean != 0 ? $slope / $mean : 0;
    }
}
