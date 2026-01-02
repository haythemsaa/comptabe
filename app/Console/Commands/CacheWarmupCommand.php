<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Partner;
use App\Models\Account;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Cache Warmup Command
 *
 * Preloads frequently accessed data into cache to improve performance.
 * Should be run after cache clear or on deployment.
 *
 * Usage: php artisan cache:warmup
 */
class CacheWarmupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warmup
                            {--company= : Warm cache for specific company only}
                            {--force : Force refresh existing cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Préchauffe le cache avec les données fréquemment utilisées';

    /**
     * Cache TTL constants (in seconds).
     */
    private const CACHE_TTL_SHORT = 300;     // 5 minutes
    private const CACHE_TTL_MEDIUM = 1800;   // 30 minutes
    private const CACHE_TTL_LONG = 3600;     // 1 hour
    private const CACHE_TTL_VERY_LONG = 86400; // 24 hours

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔥 Préchauffage du cache...');
        $this->newLine();

        $companyId = $this->option('company');
        $force = $this->option('force');

        // Get companies to warm up
        $companies = $companyId
            ? Company::where('id', $companyId)->get()
            : Company::all();

        if ($companies->isEmpty()) {
            $this->error('❌ Aucune company trouvée');
            return Command::FAILURE;
        }

        $this->info("📊 Préchauffage pour {$companies->count()} company(ies)");
        $this->newLine();

        $itemsCached = 0;

        foreach ($companies as $company) {
            $this->line("🏢 Company: {$company->name}");

            // Set tenant context
            $tenantId = $company->id;

            // 1. Warm up dashboard metrics
            $itemsCached += $this->warmupDashboardMetrics($tenantId, $force);

            // 2. Warm up chart data
            $itemsCached += $this->warmupChartData($tenantId, $force);

            // 3. Warm up partners list
            $itemsCached += $this->warmupPartners($tenantId, $force);

            // 4. Warm up accounts (chart of accounts)
            $itemsCached += $this->warmupAccounts($tenantId, $force);

            // 5. Warm up VAT rates
            $itemsCached += $this->warmupVatRates($tenantId, $force);

            $this->newLine();
        }

        // 6. Warm up global cache (non-tenant specific)
        $itemsCached += $this->warmupGlobalData($force);

        $this->newLine();
        $this->info("✅ Cache préchauffé avec succès!");
        $this->info("   {$itemsCached} élément(s) mis en cache");

        return Command::SUCCESS;
    }

    /**
     * Warm up dashboard metrics for a company.
     */
    protected function warmupDashboardMetrics(string $tenantId, bool $force): int
    {
        $key = "dashboard:{$tenantId}:metrics";

        if (!$force && Cache::has($key)) {
            $this->line("   ⏭️  Dashboard metrics (déjà en cache)");
            return 0;
        }

        try {
            Cache::remember($key, self::CACHE_TTL_MEDIUM, function () {
                $currentMonth = now()->startOfMonth();
                $lastMonth = now()->subMonth()->startOfMonth();

                return [
                    'receivables' => Invoice::sales()->unpaid()->sum('amount_due'),
                    'payables' => Invoice::purchases()->unpaid()->sum('amount_due'),
                    'overdue_receivables' => Invoice::sales()->overdue()->sum('amount_due'),
                    'current_revenue' => Invoice::sales()
                        ->whereNotIn('status', ['draft', 'cancelled'])
                        ->whereBetween('invoice_date', [$currentMonth, now()])
                        ->sum('total_excl_vat'),
                ];
            });

            $this->line("   ✅ Dashboard metrics");
            return 1;
        } catch (\Exception $e) {
            $this->warn("   ⚠️  Dashboard metrics failed: {$e->getMessage()}");
            return 0;
        }
    }

    /**
     * Warm up chart data for a company.
     */
    protected function warmupChartData(string $tenantId, bool $force): int
    {
        $keys = [
            "dashboard:{$tenantId}:revenue_chart",
            "dashboard:{$tenantId}:cash_flow",
            "dashboard:{$tenantId}:top_clients",
            "dashboard:{$tenantId}:expense_breakdown",
        ];

        $cached = 0;

        foreach ($keys as $key) {
            if (!$force && Cache::has($key)) {
                continue;
            }

            try {
                if (str_contains($key, 'revenue_chart')) {
                    Cache::remember($key, self::CACHE_TTL_LONG, function () {
                        return $this->getRevenueChartData();
                    });
                } elseif (str_contains($key, 'cash_flow')) {
                    Cache::remember($key, self::CACHE_TTL_MEDIUM, function () {
                        return ['labels' => [], 'balances' => []]; // Simplified
                    });
                } elseif (str_contains($key, 'top_clients')) {
                    Cache::remember($key, self::CACHE_TTL_LONG, function () {
                        return $this->getTopClients();
                    });
                } elseif (str_contains($key, 'expense_breakdown')) {
                    Cache::remember($key, self::CACHE_TTL_LONG, function () {
                        return [];
                    });
                }

                $cached++;
            } catch (\Exception $e) {
                // Ignore errors
            }
        }

        if ($cached > 0) {
            $this->line("   ✅ Chart data ({$cached} charts)");
        }

        return $cached;
    }

    /**
     * Warm up partners list.
     */
    protected function warmupPartners(string $tenantId, bool $force): int
    {
        $key = "partners:{$tenantId}:active";

        if (!$force && Cache::has($key)) {
            return 0;
        }

        try {
            Cache::remember($key, self::CACHE_TTL_LONG, function () {
                return Partner::active()
                    ->select('id', 'name', 'vat_number', 'email', 'partner_type')
                    ->get()
                    ->toArray();
            });

            $this->line("   ✅ Partners list");
            return 1;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Warm up chart of accounts.
     */
    protected function warmupAccounts(string $tenantId, bool $force): int
    {
        $key = "accounts:{$tenantId}:all";

        if (!$force && Cache::has($key)) {
            return 0;
        }

        try {
            Cache::remember($key, self::CACHE_TTL_VERY_LONG, function () {
                return Account::active()
                    ->orderBy('code')
                    ->select('id', 'code', 'name', 'account_type', 'parent_account_id')
                    ->get()
                    ->toArray();
            });

            $this->line("   ✅ Chart of accounts");
            return 1;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Warm up VAT rates.
     */
    protected function warmupVatRates(string $tenantId, bool $force): int
    {
        $key = "vat_rates:{$tenantId}";

        if (!$force && Cache::has($key)) {
            return 0;
        }

        try {
            Cache::remember($key, self::CACHE_TTL_VERY_LONG, function () use ($tenantId) {
                $company = Company::find($tenantId);

                // Belgian VAT rates
                if ($company?->country_code === 'BE') {
                    return [
                        ['code' => '21', 'rate' => 21.00, 'label' => 'TVA 21%'],
                        ['code' => '12', 'rate' => 12.00, 'label' => 'TVA 12%'],
                        ['code' => '6', 'rate' => 6.00, 'label' => 'TVA 6%'],
                        ['code' => '0', 'rate' => 0.00, 'label' => 'TVA 0%'],
                    ];
                }

                // Tunisian VAT rates
                if ($company?->country_code === 'TN') {
                    return [
                        ['code' => '19', 'rate' => 19.00, 'label' => 'TVA 19%'],
                        ['code' => '13', 'rate' => 13.00, 'label' => 'TVA 13%'],
                        ['code' => '7', 'rate' => 7.00, 'label' => 'TVA 7%'],
                        ['code' => '0', 'rate' => 0.00, 'label' => 'TVA 0%'],
                    ];
                }

                return [];
            });

            $this->line("   ✅ VAT rates");
            return 1;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Warm up global non-tenant data.
     */
    protected function warmupGlobalData(bool $force): int
    {
        $this->line("🌍 Global data");

        $cached = 0;

        // System settings
        $key = 'system:settings';
        if ($force || !Cache::has($key)) {
            Cache::remember($key, self::CACHE_TTL_VERY_LONG, function () {
                return [
                    'app_name' => config('app.name'),
                    'app_version' => config('app.version', '1.0.0'),
                    'maintenance_mode' => app()->isDownForMaintenance(),
                ];
            });
            $cached++;
        }

        if ($cached > 0) {
            $this->line("   ✅ System settings");
        }

        return $cached;
    }

    /**
     * Get revenue chart data (simplified).
     */
    protected function getRevenueChartData(): array
    {
        $startDate = now()->subMonths(11)->startOfMonth();
        $endDate = now()->endOfMonth();

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

        $months = collect();
        $revenue = collect();

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');
            $months->push($date->translatedFormat('M Y'));

            $monthData = $data->get($key, collect());
            $revenue->push($monthData->where('type', 'out')->sum('total') ?? 0);
        }

        return [
            'labels' => $months->toArray(),
            'revenue' => $revenue->toArray(),
        ];
    }

    /**
     * Get top clients (simplified).
     */
    protected function getTopClients(): array
    {
        $startDate = now()->subMonths(11)->startOfMonth();

        return Invoice::sales()
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->where('invoice_date', '>=', $startDate)
            ->with('partner:id,name')
            ->selectRaw('partner_id, SUM(total_excl_vat) as total_revenue')
            ->groupBy('partner_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'name' => $item->partner->name ?? 'Unknown',
                'revenue' => $item->total_revenue,
            ])
            ->toArray();
    }
}
