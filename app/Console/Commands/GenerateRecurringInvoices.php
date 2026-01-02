<?php

namespace App\Console\Commands;

use App\Models\RecurringInvoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateRecurringInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-recurring
                            {--dry-run : Show what would be generated without actually generating}
                            {--force : Generate even if not due today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices from active recurring invoice templates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('Starting recurring invoice generation...');
        $this->newLine();

        // Get all active recurring invoices that are due
        $query = RecurringInvoice::active();

        if (!$force) {
            $query->due();
        }

        $recurringInvoices = $query->get();

        if ($recurringInvoices->isEmpty()) {
            $this->info('No recurring invoices are due for generation.');
            return Command::SUCCESS;
        }

        $this->info("Found {$recurringInvoices->count()} recurring invoice(s) to process.");
        $this->newLine();

        $generated = 0;
        $failed = 0;

        foreach ($recurringInvoices as $recurring) {
            $this->line("Processing: {$recurring->name} ({$recurring->partner->name})");

            if ($dryRun) {
                $this->info("  [DRY RUN] Would generate invoice for {$recurring->total_incl_vat} EUR");
                $generated++;
                continue;
            }

            try {
                $invoice = $recurring->generateInvoice();

                $this->info("  Generated invoice: {$invoice->invoice_number}");
                $generated++;

                // Log the generation
                Log::info("Generated recurring invoice", [
                    'recurring_invoice_id' => $recurring->id,
                    'recurring_invoice_name' => $recurring->name,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $invoice->total_incl_vat,
                ]);

            } catch (\Exception $e) {
                $this->error("  Failed: {$e->getMessage()}");
                $failed++;

                // Log the error
                Log::error("Failed to generate recurring invoice", [
                    'recurring_invoice_id' => $recurring->id,
                    'recurring_invoice_name' => $recurring->name,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Generation complete:");
        $this->line("  - Generated: {$generated}");

        if ($failed > 0) {
            $this->error("  - Failed: {$failed}");
        }

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
