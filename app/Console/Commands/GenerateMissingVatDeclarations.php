<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\VatDeclarationService;
use Illuminate\Console\Command;

class GenerateMissingVatDeclarations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vat:generate-missing
                            {--company= : Company ID to process}
                            {--year= : Year to process (defaults to current year)}
                            {--period-type= : Period type (monthly or quarterly)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate missing VAT declarations for companies';

    protected VatDeclarationService $vatService;

    public function __construct(VatDeclarationService $vatService)
    {
        parent::__construct();
        $this->vatService = $vatService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Generating missing VAT declarations...');
        $this->newLine();

        // Parse options
        $year = $this->option('year') ? (int) $this->option('year') : (int) date('Y');
        $periodType = $this->option('period-type');

        // Determine companies to process
        if ($companyId = $this->option('company')) {
            $companies = Company::where('id', $companyId)->get();
        } else {
            $companies = Company::all();
        }

        if ($companies->isEmpty()) {
            $this->error('No companies found.');
            return 1;
        }

        $totalGenerated = 0;
        $totalErrors = 0;

        // Process each company
        foreach ($companies as $company) {
            $this->info("📊 Processing company: {$company->name} ({$company->id})");

            try {
                // Determine period type if not specified
                $companyPeriodType = $periodType ?? ($company->settings['vat_period_type'] ?? 'quarterly');

                $results = $this->vatService->generateMissingDeclarations(
                    $company->id,
                    $companyPeriodType,
                    $year
                );

                $totalGenerated += count($results['generated']);
                $totalErrors += count($results['errors']);

                $this->line("   ✓ Declarations generated: " . count($results['generated']));

                if (!empty($results['generated'])) {
                    foreach ($results['generated'] as $declaration) {
                        $this->line("      - {$declaration->period_type} {$declaration->year}-{$declaration->period}: {$declaration->status}");
                    }
                }

                if (!empty($results['errors'])) {
                    $this->warn("   ⚠ Errors: " . count($results['errors']));

                    foreach ($results['errors'] as $error) {
                        $this->error("      - {$error['period']}: {$error['error']}");
                    }
                }

            } catch (\Exception $e) {
                $this->error("   ✗ Failed: {$e->getMessage()}");
                $totalErrors++;
            }

            $this->newLine();
        }

        // Summary
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("✅ Summary:");
        $this->line("   Companies processed: {$companies->count()}");
        $this->line("   VAT declarations generated: {$totalGenerated}");

        if ($totalErrors > 0) {
            $this->warn("   Errors: {$totalErrors}");
        }

        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return 0;
    }
}
