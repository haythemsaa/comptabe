<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SystemError;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminAnalyticsController extends Controller
{
    /**
     * Dashboard analytics principal
     */
    public function index()
    {
        $stats = $this->getGlobalStats();
        $trends = $this->getTrends();
        $recentActivity = $this->getRecentActivity();
        $topCompanies = $this->getTopCompanies();
        $systemHealth = $this->getSystemHealth();

        return view('admin.analytics.index', compact(
            'stats',
            'trends',
            'recentActivity',
            'topCompanies',
            'systemHealth'
        ));
    }

    /**
     * Get global platform statistics
     */
    protected function getGlobalStats(): array
    {
        return Cache::remember('admin.analytics.global_stats', now()->addMinutes(5), function () {
            $now = now();
            $lastMonth = now()->subMonth();

            // Companies stats
            $totalCompanies = Company::count();
            $activeCompanies = Company::where('is_active', true)->count();
            $newCompaniesThisMonth = Company::where('created_at', '>=', $lastMonth)->count();

            // Users stats
            $totalUsers = User::count();
            $activeUsers = User::whereNotNull('last_login_at')
                ->where('last_login_at', '>=', now()->subDays(30))
                ->count();
            $newUsersThisMonth = User::where('created_at', '>=', $lastMonth)->count();

            // Business stats
            $totalInvoices = Invoice::count();
            $invoicesThisMonth = Invoice::where('created_at', '>=', $lastMonth)->count();
            $totalRevenue = Invoice::where('status', 'paid')->sum('total_amount');
            $revenueThisMonth = Invoice::where('status', 'paid')
                ->where('paid_at', '>=', $lastMonth)
                ->sum('total_amount');

            // System health
            $unresolvedErrors = SystemError::unresolved()->count();
            $criticalErrors = SystemError::critical()->unresolved()->count();

            return [
                'companies' => [
                    'total' => $totalCompanies,
                    'active' => $activeCompanies,
                    'new_this_month' => $newCompaniesThisMonth,
                    'growth_rate' => $totalCompanies > 0 ? round(($newCompaniesThisMonth / $totalCompanies) * 100, 2) : 0,
                ],
                'users' => [
                    'total' => $totalUsers,
                    'active' => $activeUsers,
                    'new_this_month' => $newUsersThisMonth,
                    'activity_rate' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 2) : 0,
                ],
                'business' => [
                    'total_invoices' => $totalInvoices,
                    'invoices_this_month' => $invoicesThisMonth,
                    'total_revenue' => $totalRevenue,
                    'revenue_this_month' => $revenueThisMonth,
                    'average_invoice' => $totalInvoices > 0 ? round($totalRevenue / $totalInvoices, 2) : 0,
                ],
                'system' => [
                    'errors_unresolved' => $unresolvedErrors,
                    'errors_critical' => $criticalErrors,
                    'uptime_percentage' => 99.9, // TODO: Calculate actual uptime
                ],
            ];
        });
    }

    /**
     * Get trends data (last 12 months)
     */
    protected function getTrends(): array
    {
        return Cache::remember('admin.analytics.trends', now()->addMinutes(10), function () {
            $months = [];
            $companiesData = [];
            $usersData = [];
            $revenueData = [];
            $invoicesData = [];

            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthStart = $date->copy()->startOfMonth();
                $monthEnd = $date->copy()->endOfMonth();

                $months[] = $date->format('M Y');

                // Companies created
                $companiesData[] = Company::whereBetween('created_at', [$monthStart, $monthEnd])->count();

                // Users created
                $usersData[] = User::whereBetween('created_at', [$monthStart, $monthEnd])->count();

                // Revenue
                $revenueData[] = Invoice::where('status', 'paid')
                    ->whereBetween('paid_at', [$monthStart, $monthEnd])
                    ->sum('total_amount');

                // Invoices
                $invoicesData[] = Invoice::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            }

            return [
                'labels' => $months,
                'companies' => $companiesData,
                'users' => $usersData,
                'revenue' => $revenueData,
                'invoices' => $invoicesData,
            ];
        });
    }

    /**
     * Get recent activity
     */
    protected function getRecentActivity(): array
    {
        return AuditLog::with(['user', 'company'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'description' => $log->description,
                    'user' => $log->user?->full_name,
                    'company' => $log->company?->name,
                    'created_at' => $log->created_at,
                ];
            })
            ->toArray();
    }

    /**
     * Get top companies by various metrics
     */
    protected function getTopCompanies(): array
    {
        return Cache::remember('admin.analytics.top_companies', now()->addMinutes(15), function () {
            // Top by revenue
            $topByRevenue = Company::select('companies.*')
                ->join('invoices', 'companies.id', '=', 'invoices.company_id')
                ->where('invoices.status', 'paid')
                ->selectRaw('companies.*, SUM(invoices.total_amount) as total_revenue')
                ->groupBy('companies.id')
                ->orderByDesc('total_revenue')
                ->limit(10)
                ->get();

            // Top by invoice count
            $topByInvoices = Company::withCount('invoices')
                ->orderByDesc('invoices_count')
                ->limit(10)
                ->get();

            // Most active (by audit logs)
            $topByActivity = Company::select('companies.*')
                ->join('audit_logs', 'companies.id', '=', 'audit_logs.company_id')
                ->where('audit_logs.created_at', '>=', now()->subDays(30))
                ->selectRaw('companies.*, COUNT(audit_logs.id) as activity_count')
                ->groupBy('companies.id')
                ->orderByDesc('activity_count')
                ->limit(10)
                ->get();

            return [
                'by_revenue' => $topByRevenue,
                'by_invoices' => $topByInvoices,
                'by_activity' => $topByActivity,
            ];
        });
    }

    /**
     * Get system health metrics
     */
    protected function getSystemHealth(): array
    {
        return [
            'database' => $this->getDatabaseHealth(),
            'cache' => $this->getCacheHealth(),
            'queue' => $this->getQueueHealth(),
            'storage' => $this->getStorageHealth(),
        ];
    }

    protected function getDatabaseHealth(): array
    {
        try {
            $startTime = microtime(true);
            DB::select('SELECT 1');
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            $dbName = config('database.connections.mysql.database');
            $result = DB::select("
                SELECT
                    table_name,
                    table_rows,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) as size_mb
                FROM information_schema.TABLES
                WHERE table_schema = ?
                ORDER BY (data_length + index_length) DESC
                LIMIT 10
            ", [$dbName]);

            return [
                'status' => 'healthy',
                'response_time' => $responseTime,
                'largest_tables' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function getCacheHealth(): array
    {
        try {
            $key = 'health_check_' . time();
            Cache::put($key, 'test', 10);
            $retrieved = Cache::get($key);
            Cache::forget($key);

            return [
                'status' => $retrieved === 'test' ? 'healthy' : 'degraded',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function getQueueHealth(): array
    {
        try {
            $failedJobs = DB::table('failed_jobs')->count();

            return [
                'status' => $failedJobs > 100 ? 'warning' : 'healthy',
                'failed_jobs' => $failedJobs,
                'driver' => config('queue.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function getStorageHealth(): array
    {
        $path = storage_path();

        if (!function_exists('disk_free_space')) {
            return ['status' => 'unknown'];
        }

        $freeSpace = disk_free_space($path);
        $totalSpace = disk_total_space($path);
        $usedSpace = $totalSpace - $freeSpace;
        $usedPercentage = round(($usedSpace / $totalSpace) * 100, 2);

        return [
            'status' => $usedPercentage > 90 ? 'warning' : 'healthy',
            'free_space' => $this->formatBytes($freeSpace),
            'total_space' => $this->formatBytes($totalSpace),
            'used_percentage' => $usedPercentage,
        ];
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Refresh analytics data (force cache clear)
     */
    public function refresh()
    {
        Cache::forget('admin.analytics.global_stats');
        Cache::forget('admin.analytics.trends');
        Cache::forget('admin.analytics.top_companies');

        return back()->with('success', 'Statistiques rafraîchies.');
    }

    /**
     * Get real-time data for AJAX updates
     */
    public function realtime(Request $request)
    {
        $metric = $request->input('metric', 'all');

        $data = match ($metric) {
            'companies' => ['companies' => $this->getGlobalStats()['companies']],
            'users' => ['users' => $this->getGlobalStats()['users']],
            'business' => ['business' => $this->getGlobalStats()['business']],
            'system' => ['system' => $this->getGlobalStats()['system']],
            'health' => ['health' => $this->getSystemHealth()],
            default => $this->getGlobalStats(),
        };

        return response()->json($data);
    }

    /**
     * Export analytics data
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'csv');

        $stats = $this->getGlobalStats();
        $trends = $this->getTrends();

        if ($format === 'json') {
            return response()->json([
                'stats' => $stats,
                'trends' => $trends,
                'exported_at' => now()->toIso8601String(),
            ]);
        }

        // CSV export
        $csv = "Métriques Globales ComptaBE - " . now()->format('Y-m-d H:i') . "\n\n";

        $csv .= "ENTREPRISES\n";
        $csv .= "Total,Actives,Nouvelles (mois),Taux croissance\n";
        $csv .= implode(',', [
            $stats['companies']['total'],
            $stats['companies']['active'],
            $stats['companies']['new_this_month'],
            $stats['companies']['growth_rate'] . '%',
        ]) . "\n\n";

        $csv .= "UTILISATEURS\n";
        $csv .= "Total,Actifs,Nouveaux (mois),Taux activité\n";
        $csv .= implode(',', [
            $stats['users']['total'],
            $stats['users']['active'],
            $stats['users']['new_this_month'],
            $stats['users']['activity_rate'] . '%',
        ]) . "\n\n";

        $csv .= "BUSINESS\n";
        $csv .= "Total factures,Factures (mois),Revenu total,Revenu (mois),Moyenne facture\n";
        $csv .= implode(',', [
            $stats['business']['total_invoices'],
            $stats['business']['invoices_this_month'],
            $stats['business']['total_revenue'],
            $stats['business']['revenue_this_month'],
            $stats['business']['average_invoice'],
        ]) . "\n\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="analytics-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
