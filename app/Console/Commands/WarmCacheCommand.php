<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Partner;
use App\Services\CacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class WarmCacheCommand extends Command
{
    protected $signature = 'cache:warm
                            {--company= : Specific company ID to warm cache for}
                            {--all : Warm cache for all companies}';

    protected $description = 'Warm up frequently used caches for better performance';

    public function handle(): int
    {
        $this->info('Starting cache warming...');

        if ($this->option('all')) {
            $companies = Company::all();
        } elseif ($this->option('company')) {
            $companies = Company::where('id', $this->option('company'))->get();
        } else {
            $companies = Company::active()->get();
        }

        $bar = $this->output->createProgressBar($companies->count());
        $bar->start();

        foreach ($companies as $company) {
            $this->warmCompanyCache($company);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Cache warming completed successfully!');

        return Command::SUCCESS;
    }

    protected function warmCompanyCache(Company $company): void
    {
        $cacheService = (new CacheService())->forTenant($company->id);

        // Dashboard metrics
        $cacheService->dashboardMetrics(function () use ($company) {
            return [
                'receivables' => Invoice::where('company_id', $company->id)
                    ->where('type', 'out')
                    ->where('status', '!=', 'paid')
                    ->sum('amount_due'),
                'payables' => Invoice::where('company_id', $company->id)
                    ->where('type', 'in')
                    ->where('status', '!=', 'paid')
                    ->sum('amount_due'),
            ];
        });

        // Partner counts
        $cacheService->partnerList('counts', function () use ($company) {
            return [
                'customers' => Partner::where('company_id', $company->id)
                    ->where('type', 'customer')
                    ->count(),
                'suppliers' => Partner::where('company_id', $company->id)
                    ->where('type', 'supplier')
                    ->count(),
            ];
        });

        // Invoice statistics by status
        $cacheService->invoiceStats('by_status', function () use ($company) {
            return Invoice::where('company_id', $company->id)
                ->selectRaw('status, COUNT(*) as count, SUM(total_incl_vat) as total')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
        });

        // VAT rates (rarely change)
        $cacheService->vatRates(function () {
            return [
                'BE' => [
                    21 => 'Taux normal (21%)',
                    12 => 'Taux réduit (12%)',
                    6 => 'Taux réduit (6%)',
                    0 => 'Exonéré (0%)',
                ],
            ];
        });
    }
}
