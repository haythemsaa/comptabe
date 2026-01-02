<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Partner;
use App\Models\BankTransaction;
use App\Models\VatDeclaration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Cache TTL constants (in seconds).
     */
    private const CACHE_TTL_SHORT = 60;       // 1 minute for frequently changing data
    private const CACHE_TTL_MEDIUM = 300;     // 5 minutes for metrics
    private const CACHE_TTL_LONG = 3600;      // 1 hour for chart data

    /**
     * Get cache key with tenant context.
     */
    private function cacheKey(string $key): string
    {
        $tenantId = session('current_tenant_id', 'global');
        return "dashboard:{$tenantId}:{$key}";
    }

    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = auth()->user();

        // Key metrics - cached for 5 minutes
        $metrics = Cache::remember($this->cacheKey('metrics'), self::CACHE_TTL_MEDIUM, function () {
            $currentMonth = now()->startOfMonth();
            $lastMonth = now()->subMonth()->startOfMonth();

            // Current month revenue
            $currentRevenue = Invoice::sales()
                ->whereNotIn('status', ['draft', 'cancelled'])
                ->whereBetween('invoice_date', [$currentMonth, now()])
                ->sum('total_excl_vat');

            // Last month revenue
            $lastRevenue = Invoice::sales()
                ->whereNotIn('status', ['draft', 'cancelled'])
                ->whereBetween('invoice_date', [$lastMonth, $currentMonth->copy()->subSecond()])
                ->sum('total_excl_vat');

            // Calculate growth
            $revenueGrowth = $lastRevenue > 0
                ? round((($currentRevenue - $lastRevenue) / $lastRevenue) * 100, 1)
                : 0;

            return [
                'receivables' => Invoice::sales()->unpaid()->sum('amount_due'),
                'payables' => Invoice::purchases()->unpaid()->sum('amount_due'),
                'bank_balance' => $this->getTotalBankBalance(),
                'overdue_receivables' => Invoice::sales()->overdue()->sum('amount_due'),
                'current_revenue' => $currentRevenue,
                'last_revenue' => $lastRevenue,
                'revenue_growth' => $revenueGrowth,
                'total_invoices_current_month' => Invoice::sales()
                    ->whereNotIn('status', ['draft', 'cancelled'])
                    ->whereBetween('invoice_date', [$currentMonth, now()])
                    ->count(),
                'average_invoice_value' => Invoice::sales()
                    ->whereNotIn('status', ['draft', 'cancelled'])
                    ->whereBetween('invoice_date', [$currentMonth, now()])
                    ->avg('total_excl_vat') ?? 0,
            ];
        });

        // Recent sales invoices
        $recentSalesInvoices = Invoice::sales()
            ->with('partner')
            ->latest('invoice_date')
            ->limit(5)
            ->get();

        // Overdue invoices
        $overdueInvoices = Invoice::sales()
            ->overdue()
            ->with('partner')
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        // Pending bank transactions
        $pendingTransactions = BankTransaction::pending()
            ->with('bankAccount')
            ->latest('transaction_date')
            ->limit(5)
            ->get();

        // Upcoming VAT declaration
        $upcomingVatDeclaration = VatDeclaration::draft()
            ->orderBy('period_year', 'desc')
            ->orderBy('period_number', 'desc')
            ->first();

        // Action items
        $actionItems = $this->getActionItems();

        // Monthly revenue chart data (last 12 months) - cached for 1 hour
        $revenueChartData = Cache::remember($this->cacheKey('revenue_chart'), self::CACHE_TTL_LONG, function () {
            return $this->getRevenueChartData();
        });

        // Cash flow forecast (next 30 days) - cached for 5 minutes
        $cashFlowForecast = Cache::remember($this->cacheKey('cash_flow'), self::CACHE_TTL_MEDIUM, function () {
            return $this->getCashFlowForecast();
        });

        // Top clients - cached for 1 hour
        $topClients = Cache::remember($this->cacheKey('top_clients'), self::CACHE_TTL_LONG, function () {
            return $this->getTopClients();
        });

        // Expense breakdown - cached for 1 hour
        $expenseBreakdown = Cache::remember($this->cacheKey('expense_breakdown'), self::CACHE_TTL_LONG, function () {
            return $this->getExpenseBreakdown();
        });

        return view('dashboard.index', compact(
            'metrics',
            'recentSalesInvoices',
            'overdueInvoices',
            'pendingTransactions',
            'upcomingVatDeclaration',
            'actionItems',
            'revenueChartData',
            'cashFlowForecast',
            'topClients',
            'expenseBreakdown'
        ));
    }

    /**
     * Get total bank balance.
     */
    protected function getTotalBankBalance(): float
    {
        return \App\Models\BankAccount::active()
            ->with(['statements' => fn($q) => $q->latest('statement_date')->limit(1)])
            ->get()
            ->sum(fn($account) => $account->statements->first()?->closing_balance ?? 0);
    }

    /**
     * Get action items for dashboard.
     */
    protected function getActionItems(): array
    {
        $items = [];

        // Draft invoices to send
        $draftCount = Invoice::sales()->where('status', 'draft')->count();
        if ($draftCount > 0) {
            $items[] = [
                'type' => 'warning',
                'icon' => 'document',
                'message' => "{$draftCount} facture(s) en brouillon à envoyer",
                'route' => route('invoices.index', ['status' => 'draft']),
            ];
        }

        // Pending Peppol invoices to process
        $peppolPending = Invoice::where('type', 'in')
            ->where('peppol_status', 'received')
            ->where('is_booked', false)
            ->count();
        if ($peppolPending > 0) {
            $items[] = [
                'type' => 'info',
                'icon' => 'inbox',
                'message' => "{$peppolPending} facture(s) Peppol à traiter",
                'route' => route('purchases.index', ['peppol' => 'pending']),
            ];
        }

        // Bank reconciliation pending
        $reconPending = BankTransaction::pending()->count();
        if ($reconPending > 0) {
            $items[] = [
                'type' => 'primary',
                'icon' => 'bank',
                'message' => "{$reconPending} transaction(s) bancaire(s) à réconcilier",
                'route' => route('bank.reconciliation'),
            ];
        }

        // Overdue invoices
        $overdueCount = Invoice::sales()->overdue()->count();
        if ($overdueCount > 0) {
            $items[] = [
                'type' => 'danger',
                'icon' => 'alert',
                'message' => "{$overdueCount} facture(s) en retard de paiement",
                'route' => route('invoices.index', ['status' => 'overdue']),
            ];
        }

        return $items;
    }

    /**
     * Get revenue chart data for last 12 months.
     * Optimized: Single query instead of 24 separate queries.
     */
    protected function getRevenueChartData(): array
    {
        $startDate = now()->subMonths(11)->startOfMonth();
        $endDate = now()->endOfMonth();

        // Get all data in one query grouped by month and type
        $data = Invoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->selectRaw("
                DATE_FORMAT(invoice_date, '%Y-%m') as month,
                type,
                SUM(total_excl_vat) as total
            ")
            ->groupBy('month', 'type')
            ->get()
            ->groupBy('month');

        // Build arrays for chart
        $months = collect();
        $revenue = collect();
        $expenses = collect();

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');
            $months->push($date->translatedFormat('M Y'));

            $monthData = $data->get($key, collect());
            $revenue->push($monthData->where('type', 'out')->sum('total') ?? 0);
            $expenses->push($monthData->where('type', 'in')->sum('total') ?? 0);
        }

        return [
            'labels' => $months->toArray(),
            'revenue' => $revenue->toArray(),
            'expenses' => $expenses->toArray(),
        ];
    }

    /**
     * Get cash flow forecast for next 30 days.
     */
    protected function getCashFlowForecast(): array
    {
        $currentBalance = $this->getTotalBankBalance();
        $forecastDays = 30;

        // Get upcoming receivables (sales invoices) grouped by due date
        $upcomingReceivables = Invoice::sales()
            ->unpaid()
            ->whereBetween('due_date', [now(), now()->addDays($forecastDays)])
            ->selectRaw('DATE(due_date) as due_day, SUM(amount_due) as total')
            ->groupBy('due_day')
            ->pluck('total', 'due_day')
            ->toArray();

        // Get upcoming payables (purchase invoices) grouped by due date
        $upcomingPayables = Invoice::purchases()
            ->unpaid()
            ->whereBetween('due_date', [now(), now()->addDays($forecastDays)])
            ->selectRaw('DATE(due_date) as due_day, SUM(amount_due) as total')
            ->groupBy('due_day')
            ->pluck('total', 'due_day')
            ->toArray();

        // Build forecast data
        $labels = [];
        $balances = [];
        $inflows = [];
        $outflows = [];
        $runningBalance = $currentBalance;

        // Weekly intervals (5 data points)
        $intervals = [0, 7, 14, 21, 30];

        foreach ($intervals as $i => $dayOffset) {
            $date = now()->addDays($dayOffset);
            $labels[] = $dayOffset === 0 ? 'Aujourd\'hui' : $date->translatedFormat('d M');

            // Calculate cumulative in/out flows up to this point
            $periodInflow = 0;
            $periodOutflow = 0;

            $startDay = $i === 0 ? 0 : $intervals[$i - 1] + 1;
            for ($d = $startDay; $d <= $dayOffset; $d++) {
                $checkDate = now()->addDays($d)->format('Y-m-d');
                $periodInflow += $upcomingReceivables[$checkDate] ?? 0;
                $periodOutflow += $upcomingPayables[$checkDate] ?? 0;
            }

            $runningBalance += $periodInflow - $periodOutflow;

            $balances[] = round($runningBalance, 2);
            $inflows[] = round($periodInflow, 2);
            $outflows[] = round($periodOutflow, 2);
        }

        // Calculate totals
        $totalInflow = array_sum($inflows);
        $totalOutflow = array_sum($outflows);
        $projectedBalance = $currentBalance + $totalInflow - $totalOutflow;

        // Calculate trend (positive if increasing)
        $trend = $projectedBalance > $currentBalance ? 'up' : ($projectedBalance < $currentBalance ? 'down' : 'stable');
        $trendPercent = $currentBalance > 0
            ? round((($projectedBalance - $currentBalance) / $currentBalance) * 100, 1)
            : 0;

        return [
            'labels' => $labels,
            'balances' => $balances,
            'inflows' => $inflows,
            'outflows' => $outflows,
            'summary' => [
                'current_balance' => $currentBalance,
                'total_inflow' => $totalInflow,
                'total_outflow' => $totalOutflow,
                'projected_balance' => $projectedBalance,
                'trend' => $trend,
                'trend_percent' => $trendPercent,
            ],
        ];
    }

    /**
     * Get top clients by revenue (last 12 months).
     */
    protected function getTopClients(): array
    {
        $startDate = now()->subMonths(11)->startOfMonth();

        return Invoice::sales()
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->where('invoice_date', '>=', $startDate)
            ->with('partner:id,name')
            ->selectRaw('partner_id, SUM(total_excl_vat) as total_revenue, COUNT(*) as invoice_count')
            ->groupBy('partner_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->partner->name ?? 'Client inconnu',
                    'revenue' => $item->total_revenue,
                    'invoice_count' => $item->invoice_count,
                ];
            })
            ->toArray();
    }

    /**
     * Get expense breakdown by category (last 12 months).
     */
    protected function getExpenseBreakdown(): array
    {
        try {
            $startDate = now()->subMonths(11)->startOfMonth();

            // Get expense by category from invoice lines
            $expenses = \DB::table('invoices')
                ->join('invoice_lines', 'invoices.id', '=', 'invoice_lines.invoice_id')
                ->leftJoin('accounts', 'invoice_lines.account_id', '=', 'accounts.id')
                ->where('invoices.type', 'in')
                ->whereNotIn('invoices.status', ['draft', 'cancelled'])
                ->where('invoices.invoice_date', '>=', $startDate)
                ->selectRaw('
                    COALESCE(accounts.code, "Autres") as category,
                    SUM(invoice_lines.line_amount) as total
                ')
                ->groupBy('category')
                ->orderByDesc('total')
                ->limit(6)
                ->get();

            // Map account codes to readable names (PCMN)
            $categoryNames = [
                '60' => 'Achats',
                '61' => 'Services et biens divers',
                '62' => 'Rémunérations et charges sociales',
                '63' => 'Amortissements',
                '64' => 'Autres charges d\'exploitation',
                '65' => 'Charges financières',
            ];

            return $expenses->map(function ($item) use ($categoryNames) {
                $code = substr($item->category, 0, 2);
                return [
                    'category' => $categoryNames[$code] ?? 'Autres',
                    'total' => $item->total,
                ];
            })->toArray();
        } catch (\Exception $e) {
            // Return empty array if accounts table doesn't exist yet
            return [];
        }
    }
}
