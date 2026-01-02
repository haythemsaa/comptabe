<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Expense;
use App\Models\BankTransaction;
use App\Models\Partner;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Dashboard analytique principal
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->current_company_id;
        $period = $request->get('period', 'year');
        $year = $request->get('year', now()->year);

        $dateRange = $this->getDateRange($period, $year);

        // KPIs principaux
        $kpis = $this->calculateKPIs($companyId, $dateRange);

        // Évolution du CA
        $revenueEvolution = $this->getRevenueEvolution($companyId, $dateRange);

        // Top clients
        $topClients = $this->getTopClients($companyId, $dateRange);

        // Top fournisseurs
        $topSuppliers = $this->getTopSuppliers($companyId, $dateRange);

        // Répartition des dépenses
        $expenseBreakdown = $this->getExpenseBreakdown($companyId, $dateRange);

        // Cash flow
        $cashFlow = $this->getCashFlow($companyId, $dateRange);

        // Aging report (échéances)
        $aging = $this->getAgingReport($companyId);

        // Comparaison périodes
        $comparison = $this->getPeriodComparison($companyId, $dateRange);

        return view('analytics.index', compact(
            'kpis',
            'revenueEvolution',
            'topClients',
            'topSuppliers',
            'expenseBreakdown',
            'cashFlow',
            'aging',
            'comparison',
            'period',
            'year'
        ));
    }

    /**
     * Rapport détaillé revenus
     */
    public function revenue(Request $request)
    {
        $companyId = Auth::user()->current_company_id;
        $year = $request->get('year', now()->year);

        // Revenus mensuels
        $monthlyRevenue = Invoice::where('company_id', $companyId)
            ->where('type', 'sale')
            ->whereYear('issue_date', $year)
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->selectRaw('MONTH(issue_date) as month, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        // Revenus par client
        $revenueByClient = Invoice::where('company_id', $companyId)
            ->where('type', 'sale')
            ->whereYear('issue_date', $year)
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->with('partner')
            ->selectRaw('partner_id, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('partner_id')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        // Revenus par catégorie/produit
        $revenueByCategory = $this->getRevenueByCategory($companyId, $year);

        // Prévisions
        $forecast = $this->getRevenueForecast($companyId);

        return view('analytics.revenue', compact(
            'monthlyRevenue',
            'revenueByClient',
            'revenueByCategory',
            'forecast',
            'year'
        ));
    }

    /**
     * Rapport détaillé dépenses
     */
    public function expenses(Request $request)
    {
        $companyId = Auth::user()->current_company_id;
        $year = $request->get('year', now()->year);

        // Dépenses mensuelles
        $monthlyExpenses = Invoice::where('company_id', $companyId)
            ->where('type', 'purchase')
            ->whereYear('issue_date', $year)
            ->selectRaw('MONTH(issue_date) as month, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        // Dépenses par fournisseur
        $expensesBySupplier = Invoice::where('company_id', $companyId)
            ->where('type', 'purchase')
            ->whereYear('issue_date', $year)
            ->with('partner')
            ->selectRaw('partner_id, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('partner_id')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        // Dépenses par catégorie
        $expensesByCategory = Expense::where('company_id', $companyId)
            ->whereYear('date', $year)
            ->whereNotNull('category')
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // Tendance des dépenses
        $trend = $this->getExpenseTrend($companyId, $year);

        return view('analytics.expenses', compact(
            'monthlyExpenses',
            'expensesBySupplier',
            'expensesByCategory',
            'trend',
            'year'
        ));
    }

    /**
     * Rapport de rentabilité
     */
    public function profitability(Request $request)
    {
        $companyId = Auth::user()->current_company_id;
        $year = $request->get('year', now()->year);

        // Marge mensuelle
        $monthlyProfitability = [];
        for ($month = 1; $month <= 12; $month++) {
            $revenue = Invoice::where('company_id', $companyId)
                ->where('type', 'sale')
                ->whereYear('issue_date', $year)
                ->whereMonth('issue_date', $month)
                ->whereIn('status', ['validated', 'sent', 'paid'])
                ->sum('total_amount');

            $expenses = Invoice::where('company_id', $companyId)
                ->where('type', 'purchase')
                ->whereYear('issue_date', $year)
                ->whereMonth('issue_date', $month)
                ->sum('total_amount');

            $monthlyProfitability[$month] = [
                'revenue' => $revenue,
                'expenses' => $expenses,
                'profit' => $revenue - $expenses,
                'margin' => $revenue > 0 ? (($revenue - $expenses) / $revenue) * 100 : 0,
            ];
        }

        // Rentabilité par client
        $clientProfitability = $this->getClientProfitability($companyId, $year);

        // KPIs de rentabilité
        $kpis = $this->getProfitabilityKPIs($companyId, $year);

        return view('analytics.profitability', compact(
            'monthlyProfitability',
            'clientProfitability',
            'kpis',
            'year'
        ));
    }

    /**
     * API pour graphiques dynamiques
     */
    public function chartData(Request $request)
    {
        $companyId = Auth::user()->current_company_id;
        $type = $request->get('type');
        $period = $request->get('period', 'year');
        $year = $request->get('year', now()->year);

        $dateRange = $this->getDateRange($period, $year);

        return response()->json(match($type) {
            'revenue' => $this->getRevenueEvolution($companyId, $dateRange),
            'expenses' => $this->getExpenseEvolution($companyId, $dateRange),
            'cashflow' => $this->getCashFlow($companyId, $dateRange),
            'profitability' => $this->getProfitabilityEvolution($companyId, $dateRange),
            default => [],
        });
    }

    /**
     * Export des données analytiques
     */
    public function export(Request $request)
    {
        $companyId = Auth::user()->current_company_id;
        $type = $request->get('type', 'summary');
        $format = $request->get('format', 'csv');
        $year = $request->get('year', now()->year);

        $data = match($type) {
            'revenue' => $this->exportRevenueData($companyId, $year),
            'expenses' => $this->exportExpenseData($companyId, $year),
            'profitability' => $this->exportProfitabilityData($companyId, $year),
            default => $this->exportSummaryData($companyId, $year),
        };

        if ($format === 'json') {
            return response()->json($data);
        }

        // Export CSV
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"analytics_{$type}_{$year}.csv\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            if (!empty($data)) {
                fputcsv($file, array_keys($data[0]), ';');
                foreach ($data as $row) {
                    fputcsv($file, $row, ';');
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ===== MÉTHODES PRIVÉES =====

    protected function getDateRange(string $period, int $year): array
    {
        return match($period) {
            'month' => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth(),
            ],
            'quarter' => [
                'start' => now()->startOfQuarter(),
                'end' => now()->endOfQuarter(),
            ],
            'year' => [
                'start' => Carbon::createFromDate($year, 1, 1)->startOfDay(),
                'end' => Carbon::createFromDate($year, 12, 31)->endOfDay(),
            ],
            default => [
                'start' => now()->subYear(),
                'end' => now(),
            ],
        };
    }

    protected function calculateKPIs(int $companyId, array $dateRange): array
    {
        // Chiffre d'affaires
        $revenue = Invoice::where('company_id', $companyId)
            ->where('type', 'sale')
            ->whereBetween('issue_date', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->sum('total_amount');

        // Dépenses
        $expenses = Invoice::where('company_id', $companyId)
            ->where('type', 'purchase')
            ->whereBetween('issue_date', [$dateRange['start'], $dateRange['end']])
            ->sum('total_amount');

        // Factures impayées
        $unpaidInvoices = Invoice::where('company_id', $companyId)
            ->where('type', 'sale')
            ->whereIn('status', ['validated', 'sent'])
            ->sum('total_amount');

        // Nombre de clients actifs
        $activeClients = Invoice::where('company_id', $companyId)
            ->where('type', 'sale')
            ->whereBetween('issue_date', [$dateRange['start'], $dateRange['end']])
            ->distinct('partner_id')
            ->count('partner_id');

        // Délai moyen de paiement
        $avgPaymentDelay = Invoice::where('company_id', $companyId)
            ->where('type', 'sale')
            ->where('status', 'paid')
            ->whereBetween('issue_date', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('paid_at')
            ->selectRaw('AVG(DATEDIFF(paid_at, issue_date)) as avg_days')
            ->first()
            ->avg_days ?? 0;

        // Marge brute
        $grossMargin = $revenue > 0 ? (($revenue - $expenses) / $revenue) * 100 : 0;

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'profit' => $revenue - $expenses,
            'gross_margin' => round($grossMargin, 1),
            'unpaid_invoices' => $unpaidInvoices,
            'active_clients' => $activeClients,
            'avg_payment_delay' => round($avgPaymentDelay, 0),
            'invoices_count' => Invoice::where('company_id', $companyId)
                ->where('type', 'sale')
                ->whereBetween('issue_date', [$dateRange['start'], $dateRange['end']])
                ->count(),
        ];
    }

    protected function getRevenueEvolution(int $companyId, array $dateRange): array
    {
        $revenue = Invoice::where('company_id', $companyId)
            ->where('type', 'sale')
            ->whereBetween('issue_date', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->selectRaw('DATE_FORMAT(issue_date, "%Y-%m") as period, SUM(total_amount) as total')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $revenue->map(fn($r) => [
            'period' => $r->period,
            'total' => $r->total,
        ])->toArray();
    }

    protected function getExpenseEvolution(int $companyId, array $dateRange): array
    {
        $expenses = Invoice::where('company_id', $companyId)
            ->where('type', 'purchase')
            ->whereBetween('issue_date', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('DATE_FORMAT(issue_date, "%Y-%m") as period, SUM(total_amount) as total')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $expenses->map(fn($e) => [
            'period' => $e->period,
            'total' => $e->total,
        ])->toArray();
    }

    protected function getTopClients(int $companyId, array $dateRange): array
    {
        return Invoice::where('company_id', $companyId)
            ->where('type', 'sale')
            ->whereBetween('issue_date', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->with('partner:id,name')
            ->selectRaw('partner_id, SUM(total_amount) as total, COUNT(*) as invoice_count')
            ->groupBy('partner_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($i) => [
                'partner' => $i->partner?->name ?? 'Inconnu',
                'partner_id' => $i->partner_id,
                'total' => $i->total,
                'invoice_count' => $i->invoice_count,
            ])
            ->toArray();
    }

    protected function getTopSuppliers(int $companyId, array $dateRange): array
    {
        return Invoice::where('company_id', $companyId)
            ->where('type', 'purchase')
            ->whereBetween('issue_date', [$dateRange['start'], $dateRange['end']])
            ->with('partner:id,name')
            ->selectRaw('partner_id, SUM(total_amount) as total, COUNT(*) as invoice_count')
            ->groupBy('partner_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($i) => [
                'partner' => $i->partner?->name ?? 'Inconnu',
                'partner_id' => $i->partner_id,
                'total' => $i->total,
                'invoice_count' => $i->invoice_count,
            ])
            ->toArray();
    }

    protected function getExpenseBreakdown(int $companyId, array $dateRange): array
    {
        return Expense::where('company_id', $companyId)
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('category')
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn($e) => [
                'category' => $e->category,
                'total' => $e->total,
            ])
            ->toArray();
    }

    protected function getCashFlow(int $companyId, array $dateRange): array
    {
        $inflows = BankTransaction::where('company_id', $companyId)
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->where('amount', '>', 0)
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as period, SUM(amount) as total')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $outflows = BankTransaction::where('company_id', $companyId)
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->where('amount', '<', 0)
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as period, SUM(ABS(amount)) as total')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $periods = $inflows->keys()->merge($outflows->keys())->unique()->sort();

        return $periods->map(fn($period) => [
            'period' => $period,
            'inflows' => $inflows->get($period)?->total ?? 0,
            'outflows' => $outflows->get($period)?->total ?? 0,
            'net' => ($inflows->get($period)?->total ?? 0) - ($outflows->get($period)?->total ?? 0),
        ])->values()->toArray();
    }

    protected function getAgingReport(int $companyId): array
    {
        $today = now();

        $aging = [
            'current' => ['count' => 0, 'amount' => 0],
            '1_30' => ['count' => 0, 'amount' => 0],
            '31_60' => ['count' => 0, 'amount' => 0],
            '61_90' => ['count' => 0, 'amount' => 0],
            'over_90' => ['count' => 0, 'amount' => 0],
        ];

        $unpaidInvoices = Invoice::where('company_id', $companyId)
            ->where('type', 'sale')
            ->whereIn('status', ['validated', 'sent'])
            ->whereNotNull('due_date')
            ->get();

        foreach ($unpaidInvoices as $invoice) {
            $daysOverdue = $today->diffInDays($invoice->due_date, false);

            if ($daysOverdue <= 0) {
                $aging['current']['count']++;
                $aging['current']['amount'] += $invoice->total_amount;
            } elseif ($daysOverdue <= 30) {
                $aging['1_30']['count']++;
                $aging['1_30']['amount'] += $invoice->total_amount;
            } elseif ($daysOverdue <= 60) {
                $aging['31_60']['count']++;
                $aging['31_60']['amount'] += $invoice->total_amount;
            } elseif ($daysOverdue <= 90) {
                $aging['61_90']['count']++;
                $aging['61_90']['amount'] += $invoice->total_amount;
            } else {
                $aging['over_90']['count']++;
                $aging['over_90']['amount'] += $invoice->total_amount;
            }
        }

        return $aging;
    }

    protected function getPeriodComparison(int $companyId, array $dateRange): array
    {
        $periodLength = $dateRange['start']->diffInDays($dateRange['end']);
        $previousStart = $dateRange['start']->copy()->subDays($periodLength);
        $previousEnd = $dateRange['start']->copy()->subDay();

        $currentRevenue = Invoice::where('company_id', $companyId)
            ->where('type', 'sale')
            ->whereBetween('issue_date', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->sum('total_amount');

        $previousRevenue = Invoice::where('company_id', $companyId)
            ->where('type', 'sale')
            ->whereBetween('issue_date', [$previousStart, $previousEnd])
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->sum('total_amount');

        $currentExpenses = Invoice::where('company_id', $companyId)
            ->where('type', 'purchase')
            ->whereBetween('issue_date', [$dateRange['start'], $dateRange['end']])
            ->sum('total_amount');

        $previousExpenses = Invoice::where('company_id', $companyId)
            ->where('type', 'purchase')
            ->whereBetween('issue_date', [$previousStart, $previousEnd])
            ->sum('total_amount');

        return [
            'revenue' => [
                'current' => $currentRevenue,
                'previous' => $previousRevenue,
                'change' => $previousRevenue > 0
                    ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100
                    : 0,
            ],
            'expenses' => [
                'current' => $currentExpenses,
                'previous' => $previousExpenses,
                'change' => $previousExpenses > 0
                    ? (($currentExpenses - $previousExpenses) / $previousExpenses) * 100
                    : 0,
            ],
            'profit' => [
                'current' => $currentRevenue - $currentExpenses,
                'previous' => $previousRevenue - $previousExpenses,
            ],
        ];
    }

    protected function getProfitabilityEvolution(int $companyId, array $dateRange): array
    {
        $revenue = $this->getRevenueEvolution($companyId, $dateRange);
        $expenses = $this->getExpenseEvolution($companyId, $dateRange);

        $revenueByPeriod = collect($revenue)->keyBy('period');
        $expensesByPeriod = collect($expenses)->keyBy('period');

        $periods = $revenueByPeriod->keys()->merge($expensesByPeriod->keys())->unique()->sort();

        return $periods->map(function($period) use ($revenueByPeriod, $expensesByPeriod) {
            $rev = $revenueByPeriod->get($period)['total'] ?? 0;
            $exp = $expensesByPeriod->get($period)['total'] ?? 0;
            return [
                'period' => $period,
                'profit' => $rev - $exp,
                'margin' => $rev > 0 ? (($rev - $exp) / $rev) * 100 : 0,
            ];
        })->values()->toArray();
    }

    protected function getRevenueByCategory(int $companyId, int $year): array
    {
        // Simplified - would need invoice lines with categories
        return [];
    }

    protected function getRevenueForecast(int $companyId): array
    {
        // Basic linear regression forecast
        $lastYearData = Invoice::where('company_id', $companyId)
            ->where('type', 'sale')
            ->whereYear('issue_date', now()->year)
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->selectRaw('MONTH(issue_date) as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        $forecast = [];
        $currentMonth = now()->month;

        // Simple average growth forecast
        if (count($lastYearData) >= 3) {
            $values = array_values($lastYearData);
            $avgGrowth = 0;
            for ($i = 1; $i < count($values); $i++) {
                if ($values[$i - 1] > 0) {
                    $avgGrowth += ($values[$i] - $values[$i - 1]) / $values[$i - 1];
                }
            }
            $avgGrowth = count($values) > 1 ? $avgGrowth / (count($values) - 1) : 0;

            $lastValue = end($values);
            for ($month = $currentMonth + 1; $month <= 12; $month++) {
                $lastValue = $lastValue * (1 + $avgGrowth);
                $forecast[$month] = max(0, $lastValue);
            }
        }

        return $forecast;
    }

    protected function getExpenseTrend(int $companyId, int $year): array
    {
        return Invoice::where('company_id', $companyId)
            ->where('type', 'purchase')
            ->whereYear('issue_date', $year)
            ->selectRaw('MONTH(issue_date) as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($e) => [
                'month' => $e->month,
                'total' => $e->total,
            ])
            ->toArray();
    }

    protected function getClientProfitability(int $companyId, int $year): array
    {
        return Invoice::where('company_id', $companyId)
            ->where('type', 'sale')
            ->whereYear('issue_date', $year)
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->with('partner:id,name')
            ->selectRaw('partner_id, SUM(total_amount) as revenue')
            ->groupBy('partner_id')
            ->orderByDesc('revenue')
            ->limit(20)
            ->get()
            ->map(fn($i) => [
                'partner' => $i->partner?->name ?? 'Inconnu',
                'revenue' => $i->revenue,
                // Would need cost tracking per client for real profitability
                'estimated_profit' => $i->revenue * 0.3, // Assumed 30% margin
            ])
            ->toArray();
    }

    protected function getProfitabilityKPIs(int $companyId, int $year): array
    {
        $revenue = Invoice::where('company_id', $companyId)
            ->where('type', 'sale')
            ->whereYear('issue_date', $year)
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->sum('total_amount');

        $expenses = Invoice::where('company_id', $companyId)
            ->where('type', 'purchase')
            ->whereYear('issue_date', $year)
            ->sum('total_amount');

        return [
            'gross_profit' => $revenue - $expenses,
            'gross_margin' => $revenue > 0 ? (($revenue - $expenses) / $revenue) * 100 : 0,
            'revenue_growth' => 0, // Would compare to previous year
            'expense_ratio' => $revenue > 0 ? ($expenses / $revenue) * 100 : 0,
        ];
    }

    protected function exportSummaryData(int $companyId, int $year): array
    {
        $data = [];
        for ($month = 1; $month <= 12; $month++) {
            $revenue = Invoice::where('company_id', $companyId)
                ->where('type', 'sale')
                ->whereYear('issue_date', $year)
                ->whereMonth('issue_date', $month)
                ->whereIn('status', ['validated', 'sent', 'paid'])
                ->sum('total_amount');

            $expenses = Invoice::where('company_id', $companyId)
                ->where('type', 'purchase')
                ->whereYear('issue_date', $year)
                ->whereMonth('issue_date', $month)
                ->sum('total_amount');

            $data[] = [
                'Mois' => Carbon::createFromDate($year, $month, 1)->format('F Y'),
                'Revenus' => $revenue,
                'Dépenses' => $expenses,
                'Profit' => $revenue - $expenses,
                'Marge %' => $revenue > 0 ? round((($revenue - $expenses) / $revenue) * 100, 1) : 0,
            ];
        }

        return $data;
    }

    protected function exportRevenueData(int $companyId, int $year): array
    {
        return Invoice::where('company_id', $companyId)
            ->where('type', 'sale')
            ->whereYear('issue_date', $year)
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->with('partner:id,name')
            ->orderBy('issue_date')
            ->get()
            ->map(fn($i) => [
                'Date' => $i->issue_date->format('d/m/Y'),
                'Numéro' => $i->number,
                'Client' => $i->partner?->name ?? 'Inconnu',
                'HT' => $i->subtotal,
                'TVA' => $i->tax_amount,
                'TTC' => $i->total_amount,
                'Statut' => $i->status,
            ])
            ->toArray();
    }

    protected function exportExpenseData(int $companyId, int $year): array
    {
        return Invoice::where('company_id', $companyId)
            ->where('type', 'purchase')
            ->whereYear('issue_date', $year)
            ->with('partner:id,name')
            ->orderBy('issue_date')
            ->get()
            ->map(fn($i) => [
                'Date' => $i->issue_date->format('d/m/Y'),
                'Numéro' => $i->number,
                'Fournisseur' => $i->partner?->name ?? 'Inconnu',
                'HT' => $i->subtotal,
                'TVA' => $i->tax_amount,
                'TTC' => $i->total_amount,
            ])
            ->toArray();
    }

    protected function exportProfitabilityData(int $companyId, int $year): array
    {
        return $this->exportSummaryData($companyId, $year);
    }
}
