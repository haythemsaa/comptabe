<?php

namespace App\Http\Controllers\Firm;

use App\Http\Controllers\Controller;
use App\Models\ClientMandate;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FirmDashboardController extends Controller
{
    /**
     * Show the main firm dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Check if user is a firm member
        if (!$user->isCabinetMember()) {
            return redirect()->route('dashboard')
                ->with('error', 'Vous devez être membre d\'un cabinet comptable pour accéder à cette page.');
        }

        $firm = $user->currentFirm();

        if (!$firm) {
            return redirect()->route('dashboard')
                ->with('error', 'Aucun cabinet comptable trouvé.');
        }

        // Get period filter (default: current month)
        $period = $request->input('period', 'current_month');
        $dateRange = $this->getDateRangeForPeriod($period);

        // Get all active client mandates
        $mandates = ClientMandate::where('accounting_firm_id', $firm->id)
            ->where('status', 'active')
            ->with(['company', 'manager'])
            ->get();

        // Calculate portfolio metrics
        $portfolioMetrics = $this->calculatePortfolioMetrics($mandates, $dateRange);

        // Get client health scores
        $clientsWithScores = $this->calculateClientHealthScores($mandates);

        // Get recent activities
        $recentActivities = $this->getRecentActivities($firm, 10);

        // Get upcoming tasks
        $upcomingTasks = $this->getUpcomingTasks($firm, 10);

        return view('firm.dashboard.index', [
            'firm' => $firm,
            'mandates' => $mandates,
            'portfolioMetrics' => $portfolioMetrics,
            'clientsWithScores' => $clientsWithScores,
            'recentActivities' => $recentActivities,
            'upcomingTasks' => $upcomingTasks,
            'period' => $period,
            'dateRange' => $dateRange,
        ]);
    }

    /**
     * Show detailed client list.
     */
    public function clients(Request $request)
    {
        $user = $request->user();
        $firm = $user->currentFirm();

        if (!$firm) {
            abort(403);
        }

        // Get filters
        $statusFilter = $request->input('status', 'active');
        $sortBy = $request->input('sort', 'name');
        $search = $request->input('search');

        // Build query
        $query = ClientMandate::where('accounting_firm_id', $firm->id)
            ->with(['company', 'manager']);

        // Apply status filter
        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        // Apply search
        if ($search) {
            $query->whereHas('company', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%");
            });
        }

        $mandates = $query->get();

        // Calculate metrics for each client
        $period = $request->input('period', 'current_month');
        $dateRange = $this->getDateRangeForPeriod($period);

        $clientsData = $mandates->map(function ($mandate) use ($dateRange) {
            return [
                'mandate' => $mandate,
                'company' => $mandate->company,
                'metrics' => $this->calculateClientMetrics($mandate->company, $dateRange),
                'health_score' => $this->calculateSingleClientHealthScore($mandate),
            ];
        });

        // Sort clients
        $clientsData = $this->sortClients($clientsData, $sortBy);

        return view('firm.dashboard.clients', [
            'firm' => $firm,
            'clientsData' => $clientsData,
            'statusFilter' => $statusFilter,
            'sortBy' => $sortBy,
            'search' => $search,
            'period' => $period,
        ]);
    }

    /**
     * Calculate portfolio-wide metrics.
     */
    protected function calculatePortfolioMetrics($mandates, array $dateRange): array
    {
        $totalRevenue = 0;
        $totalExpenses = 0;
        $totalVatCollected = 0;
        $totalVatPaid = 0;
        $totalOutstanding = 0;
        $totalInvoices = 0;

        foreach ($mandates as $mandate) {
            $company = $mandate->company;

            // Sales
            $sales = Invoice::where('company_id', $company->id)
                ->where('type', 'sale')
                ->whereBetween('invoice_date', [$dateRange['from'], $dateRange['to']])
                ->whereIn('status', ['validated', 'sent', 'paid'])
                ->get();

            // Purchases
            $purchases = Invoice::where('company_id', $company->id)
                ->where('type', 'purchase')
                ->whereBetween('invoice_date', [$dateRange['from'], $dateRange['to']])
                ->whereIn('status', ['validated', 'sent', 'paid'])
                ->get();

            // Outstanding
            $outstanding = Invoice::where('company_id', $company->id)
                ->where('type', 'sale')
                ->whereIn('status', ['sent', 'partial'])
                ->where('due_date', '<', now())
                ->sum('amount_due');

            $totalRevenue += $sales->sum('total_incl_vat');
            $totalExpenses += $purchases->sum('total_incl_vat');
            $totalVatCollected += $sales->sum('total_vat');
            $totalVatPaid += $purchases->sum('total_vat');
            $totalOutstanding += $outstanding;
            $totalInvoices += $sales->count();
        }

        return [
            'total_clients' => $mandates->count(),
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'total_margin' => $totalRevenue - $totalExpenses,
            'total_vat_collected' => $totalVatCollected,
            'total_vat_paid' => $totalVatPaid,
            'net_vat_balance' => $totalVatCollected - $totalVatPaid,
            'total_outstanding' => $totalOutstanding,
            'total_invoices' => $totalInvoices,
            'average_revenue_per_client' => $mandates->count() > 0 ? $totalRevenue / $mandates->count() : 0,
        ];
    }

    /**
     * Calculate client metrics.
     */
    protected function calculateClientMetrics($company, array $dateRange): array
    {
        $sales = Invoice::where('company_id', $company->id)
            ->where('type', 'sale')
            ->whereBetween('invoice_date', [$dateRange['from'], $dateRange['to']])
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->get();

        $purchases = Invoice::where('company_id', $company->id)
            ->where('type', 'purchase')
            ->whereBetween('invoice_date', [$dateRange['from'], $dateRange['to']])
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->get();

        $outstanding = Invoice::where('company_id', $company->id)
            ->where('type', 'sale')
            ->whereIn('status', ['sent', 'partial'])
            ->where('due_date', '<', now())
            ->get();

        return [
            'revenue' => (float) $sales->sum('total_incl_vat'),
            'expenses' => (float) $purchases->sum('total_excl_vat'),
            'margin' => (float) ($sales->sum('total_excl_vat') - $purchases->sum('total_excl_vat')),
            'vat_collected' => (float) $sales->sum('total_vat'),
            'vat_paid' => (float) $purchases->sum('total_vat'),
            'vat_balance' => (float) ($sales->sum('total_vat') - $purchases->sum('total_vat')),
            'invoices_count' => $sales->count(),
            'outstanding_count' => $outstanding->count(),
            'outstanding_amount' => (float) $outstanding->sum('amount_due'),
        ];
    }

    /**
     * Calculate health scores for all clients.
     */
    protected function calculateClientHealthScores($mandates): array
    {
        return $mandates->map(function ($mandate) {
            $score = $this->calculateSingleClientHealthScore($mandate);

            return [
                'mandate' => $mandate,
                'company' => $mandate->company,
                'score' => $score['overall'],
                'status' => $score['status'],
                'color' => $score['color'],
            ];
        })->sortByDesc('score')->values()->toArray();
    }

    /**
     * Calculate health score for a single client (simplified).
     */
    protected function calculateSingleClientHealthScore($mandate): array
    {
        $company = $mandate->company;
        $score = 100;

        // Check VAT number
        if (empty($company->vat_number)) {
            $score -= 30;
        }

        // Check activity (last 3 months)
        $recentInvoices = Invoice::where('company_id', $company->id)
            ->where('invoice_date', '>=', now()->subMonths(3))
            ->count();

        if ($recentInvoices === 0) {
            $score -= 40;
        } elseif ($recentInvoices < 3) {
            $score -= 20;
        }

        // Check overdue invoices
        $overdueCount = Invoice::where('company_id', $company->id)
            ->where('type', 'sale')
            ->whereIn('status', ['sent', 'partial'])
            ->where('due_date', '<', now()->subDays(90))
            ->count();

        if ($overdueCount > 0) {
            $score -= 25;
        }

        $score = max(0, $score);

        return [
            'overall' => $score,
            'status' => $this->getHealthStatus($score)['label'],
            'color' => $this->getHealthStatus($score)['color'],
        ];
    }

    /**
     * Get health status label and color.
     */
    protected function getHealthStatus(int $score): array
    {
        if ($score >= 80) {
            return ['label' => 'Excellent', 'color' => 'green'];
        } elseif ($score >= 60) {
            return ['label' => 'Bon', 'color' => 'blue'];
        } elseif ($score >= 40) {
            return ['label' => 'Moyen', 'color' => 'yellow'];
        } elseif ($score >= 20) {
            return ['label' => 'Faible', 'color' => 'orange'];
        }

        return ['label' => 'Critique', 'color' => 'red'];
    }

    /**
     * Get recent activities.
     */
    protected function getRecentActivities($firm, int $limit = 10): array
    {
        $activities = DB::table('mandate_activities')
            ->join('client_mandates', 'mandate_activities.client_mandate_id', '=', 'client_mandates.id')
            ->join('companies', 'client_mandates.company_id', '=', 'companies.id')
            ->join('users', 'mandate_activities.user_id', '=', 'users.id')
            ->where('client_mandates.accounting_firm_id', $firm->id)
            ->select([
                'mandate_activities.type',
                'mandate_activities.description',
                'mandate_activities.created_at',
                'companies.name as company_name',
                'users.name as user_name',
            ])
            ->orderBy('mandate_activities.created_at', 'desc')
            ->limit($limit)
            ->get();

        return $activities->toArray();
    }

    /**
     * Get upcoming tasks.
     */
    protected function getUpcomingTasks($firm, int $limit = 10): array
    {
        $tasks = DB::table('mandate_tasks')
            ->join('client_mandates', 'mandate_tasks.client_mandate_id', '=', 'client_mandates.id')
            ->join('companies', 'client_mandates.company_id', '=', 'companies.id')
            ->leftJoin('users', 'mandate_tasks.assigned_to', '=', 'users.id')
            ->where('client_mandates.accounting_firm_id', $firm->id)
            ->where('mandate_tasks.status', '!=', 'completed')
            ->select([
                'mandate_tasks.id',
                'mandate_tasks.title',
                'mandate_tasks.task_type',
                'mandate_tasks.priority',
                'mandate_tasks.due_date',
                'mandate_tasks.status',
                'companies.name as company_name',
                'users.name as assigned_to_name',
            ])
            ->orderBy('mandate_tasks.due_date', 'asc')
            ->limit($limit)
            ->get();

        return $tasks->toArray();
    }

    /**
     * Sort clients by specified field.
     */
    protected function sortClients($clientsData, string $sortBy)
    {
        return match ($sortBy) {
            'name' => $clientsData->sortBy('company.name'),
            'revenue' => $clientsData->sortByDesc('metrics.revenue'),
            'health' => $clientsData->sortByDesc('health_score.score'),
            'outstanding' => $clientsData->sortByDesc('metrics.outstanding_amount'),
            default => $clientsData->sortBy('company.name'),
        };
    }

    /**
     * Get date range for period.
     */
    protected function getDateRangeForPeriod(string $period): array
    {
        return match ($period) {
            'current_month' => ['from' => now()->startOfMonth(), 'to' => now()->endOfMonth()],
            'current_quarter' => ['from' => now()->startOfQuarter(), 'to' => now()->endOfQuarter()],
            'current_year' => ['from' => now()->startOfYear(), 'to' => now()->endOfYear()],
            'last_month' => ['from' => now()->subMonth()->startOfMonth(), 'to' => now()->subMonth()->endOfMonth()],
            'last_quarter' => ['from' => now()->subQuarter()->startOfQuarter(), 'to' => now()->subQuarter()->endOfQuarter()],
            'last_year' => ['from' => now()->subYear()->startOfYear(), 'to' => now()->subYear()->endOfYear()],
            default => ['from' => now()->startOfMonth(), 'to' => now()->endOfMonth()],
        };
    }

    /*
    |--------------------------------------------------------------------------
    | API Endpoints for AJAX Data Fetching
    |--------------------------------------------------------------------------
    */

    /**
     * API: Get all clients with metrics (for dynamic dashboard)
     */
    public function apiGetClients(Request $request)
    {
        $user = $request->user();

        if (!$user->isCabinetMember()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $firm = $user->currentFirm();

        if (!$firm) {
            return response()->json(['error' => 'No firm found'], 404);
        }

        $request->validate([
            'status' => 'nullable|in:all,active,pending,suspended',
            'sort_by' => 'nullable|in:name,revenue,health,outstanding',
            'period' => 'nullable|in:current_month,current_quarter,current_year,last_month,last_quarter,last_year',
            'search' => 'nullable|string|max:100',
        ]);

        $statusFilter = $request->get('status', 'active');
        $sortBy = $request->get('sort_by', 'name');
        $period = $request->get('period', 'current_month');
        $search = $request->get('search');

        $dateRange = $this->getDateRangeForPeriod($period);

        // Build query
        $query = ClientMandate::where('accounting_firm_id', $firm->id)
            ->with(['company', 'manager']);

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($search) {
            $query->whereHas('company', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%");
            });
        }

        $mandates = $query->get();

        // Process each client
        $clientsData = $mandates->map(function ($mandate) use ($dateRange) {
            $company = $mandate->company;
            $metrics = $this->calculateClientMetrics($company, $dateRange);
            $healthScore = $this->calculateSingleClientHealthScore($mandate);
            $alerts = $this->getClientAlertsForApi($company, $metrics);

            return [
                'id' => $company->id,
                'name' => $company->name,
                'vat_number' => $company->vat_number,
                'mandate' => [
                    'id' => $mandate->id,
                    'type' => $mandate->mandate_type,
                    'status' => $mandate->status,
                    'manager' => $mandate->manager?->name ?? 'Non assigné',
                ],
                'metrics' => $metrics,
                'health_score' => $healthScore,
                'alerts' => $alerts,
            ];
        });

        // Sort
        $clientsData = $this->sortClients($clientsData, $sortBy);

        // Summary
        $summary = $this->calculatePortfolioSummaryFromClients($clientsData);

        return response()->json([
            'success' => true,
            'data' => [
                'clients' => $clientsData->values(),
                'summary' => $summary,
                'total_count' => $clientsData->count(),
            ],
        ]);
    }

    /**
     * API: Get portfolio statistics
     */
    public function apiGetStatistics(Request $request)
    {
        $user = $request->user();

        if (!$user->isCabinetMember()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $firm = $user->currentFirm();

        if (!$firm) {
            return response()->json(['error' => 'No firm found'], 404);
        }

        $period = $request->get('period', 'current_month');
        $dateRange = $this->getDateRangeForPeriod($period);

        $mandates = ClientMandate::where('accounting_firm_id', $firm->id)
            ->with('company')
            ->get();

        $portfolioMetrics = $this->calculatePortfolioMetrics($mandates, $dateRange);

        // Health distribution
        $healthDistribution = [
            'excellent' => 0,
            'good' => 0,
            'warning' => 0,
            'critical' => 0,
        ];

        $clientsWithAlerts = 0;

        foreach ($mandates as $mandate) {
            $score = $this->calculateSingleClientHealthScore($mandate);
            $scoreValue = $score['overall'];

            if ($scoreValue >= 80) {
                $healthDistribution['excellent']++;
            } elseif ($scoreValue >= 60) {
                $healthDistribution['good']++;
            } elseif ($scoreValue >= 40) {
                $healthDistribution['warning']++;
            } else {
                $healthDistribution['critical']++;
            }

            // Check if client has alerts
            $metrics = $this->calculateClientMetrics($mandate->company, $dateRange);
            $alerts = $this->getClientAlertsForApi($mandate->company, $metrics);
            if (!empty($alerts)) {
                $clientsWithAlerts++;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'portfolio_metrics' => $portfolioMetrics,
                'health_distribution' => $healthDistribution,
                'clients_with_alerts' => $clientsWithAlerts,
            ],
        ]);
    }

    /**
     * Get alerts for a client (API version)
     */
    protected function getClientAlertsForApi($company, array $metrics): array
    {
        $alerts = [];

        // Overdue invoices
        if ($metrics['outstanding_count'] > 0) {
            $alerts[] = [
                'type' => 'overdue_invoices',
                'severity' => $metrics['outstanding_count'] > 5 ? 'critical' : 'warning',
                'message' => "{$metrics['outstanding_count']} facture(s) en retard",
                'count' => $metrics['outstanding_count'],
            ];
        }

        // High outstanding amount
        if ($metrics['outstanding_amount'] > 10000) {
            $alerts[] = [
                'type' => 'high_outstanding',
                'severity' => 'warning',
                'message' => 'Créances élevées',
                'amount' => $metrics['outstanding_amount'],
            ];
        }

        // Low activity
        $recentInvoices = Invoice::where('company_id', $company->id)
            ->where('invoice_date', '>=', now()->subMonths(2))
            ->count();

        if ($recentInvoices === 0) {
            $alerts[] = [
                'type' => 'no_activity',
                'severity' => 'info',
                'message' => 'Aucune activité récente',
            ];
        }

        return $alerts;
    }

    /**
     * Calculate portfolio summary from processed clients data
     */
    protected function calculatePortfolioSummaryFromClients($clientsData): array
    {
        $totalRevenue = 0;
        $totalExpenses = 0;
        $totalVatBalance = 0;
        $totalOutstanding = 0;
        $totalInvoices = 0;

        foreach ($clientsData as $client) {
            $totalRevenue += $client['metrics']['revenue'] ?? 0;
            $totalExpenses += $client['metrics']['expenses'] ?? 0;
            $totalVatBalance += $client['metrics']['vat_balance'] ?? 0;
            $totalOutstanding += $client['metrics']['outstanding_amount'] ?? 0;
            $totalInvoices += $client['metrics']['invoices_count'] ?? 0;
        }

        return [
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'total_margin' => $totalRevenue - $totalExpenses,
            'total_vat_balance' => $totalVatBalance,
            'total_outstanding' => $totalOutstanding,
            'total_invoices' => $totalInvoices,
            'average_per_client' => $clientsData->count() > 0 ? $totalRevenue / $clientsData->count() : 0,
        ];
    }
}
