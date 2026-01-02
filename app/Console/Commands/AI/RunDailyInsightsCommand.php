<?php

namespace App\Console\Commands\AI;

use Illuminate\Console\Command;
use App\Jobs\DailyInsightsJob;
use App\Models\Company;

class RunDailyInsightsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:daily-insights
                            {--company= : Specific company ID to process}
                            {--force : Force execution even if already run today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send daily business insights to active companies';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting daily insights generation...');

        if ($this->option('company')) {
            $this->processSingleCompany($this->option('company'));
        } else {
            $this->processAllCompanies();
        }

        $this->info('Daily insights generation completed!');
        return Command::SUCCESS;
    }

    /**
     * Process a single company
     */
    protected function processSingleCompany(string $companyId): void
    {
        $company = Company::find($companyId);

        if (!$company) {
            $this->error("Company {$companyId} not found!");
            return;
        }

        $this->info("Processing company: {$company->name}");

        DailyInsightsJob::dispatch($companyId, $this->option('force'));

        $this->info("✓ Insights job dispatched for {$company->name}");
    }

    /**
     * Process all active companies
     */
    protected function processAllCompanies(): void
    {
        $companies = Company::whereHas('subscription', function ($query) {
            $query->where('status', 'active')
                  ->orWhere('status', 'trial');
        })->get();

        $this->info("Found {$companies->count()} active companies to process");

        $bar = $this->output->createProgressBar($companies->count());
        $bar->start();

        foreach ($companies as $company) {
            DailyInsightsJob::dispatch($company->id, $this->option('force'));
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("✓ Dispatched {$companies->count()} insights jobs");
    }
}
