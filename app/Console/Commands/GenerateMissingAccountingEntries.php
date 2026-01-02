<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\Accounting\AccountingEntryService;
use Illuminate\Console\Command;

class GenerateMissingAccountingEntries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounting:generate-missing
                            {--company= : Company ID to process}
                            {--from= : Start date (Y-m-d)}
                            {--to= : End date (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate missing accounting entries for invoices and payments';

    protected AccountingEntryService $accountingService;

    public function __construct(AccountingEntryService $accountingService)
    {
        parent::__construct();
        $this->accountingService = $accountingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Generating missing accounting entries...');
        $this->newLine();

        // Parse dates
        $from = $this->option('from') ? new \DateTime($this->option('from')) : null;
        $to = $this->option('to') ? new \DateTime($this->option('to')) : null;

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

        $totalInvoices = 0;
        $totalPayments = 0;
        $totalErrors = 0;

        // Process each company
        foreach ($companies as $company) {
            $this->info("📊 Processing company: {$company->name} ({$company->id})");

            try {
                $results = $this->accountingService->generateMissingEntries(
                    $company->id,
                    $from,
                    $to
                );

                $totalInvoices += $results['invoices_processed'];
                $totalPayments += $results['payments_processed'];
                $totalErrors += count($results['errors']);

                $this->line("   ✓ Invoices: {$results['invoices_processed']}");
                $this->line("   ✓ Payments: {$results['payments_processed']}");

                if (!empty($results['errors'])) {
                    $this->warn("   ⚠ Errors: " . count($results['errors']));

                    foreach ($results['errors'] as $error) {
                        $this->error("      - {$error['type']} #{$error['id']}: {$error['error']}");
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
        $this->line("   Invoice entries generated: {$totalInvoices}");
        $this->line("   Payment entries generated: {$totalPayments}");

        if ($totalErrors > 0) {
            $this->warn("   Errors: {$totalErrors}");
        }

        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return 0;
    }
}
