<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\PaymentReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:send-reminders
                            {--dry-run : Show what would be sent without actually sending}
                            {--force : Create reminders even if not overdue}
                            {--days=7 : Minimum days overdue before first reminder}
                            {--level= : Only process specific reminder level (1, 2, or 3)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and send payment reminders for overdue invoices';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $minDays = (int) $this->option('days');
        $level = $this->option('level') ? (int) $this->option('level') : null;

        $this->info('Starting payment reminder process...');
        $this->newLine();

        // Get all overdue invoices
        $query = Invoice::sales()
            ->whereIn('status', ['sent', 'overdue'])
            ->where('due_date', '<', now()->subDays($minDays))
            ->whereColumn('amount_paid', '<', 'total_incl_vat')
            ->with(['partner', 'reminders']);

        if (!$force) {
            $query->where('due_date', '<', now());
        }

        $invoices = $query->get();

        if ($invoices->isEmpty()) {
            $this->info('No overdue invoices found that require reminders.');
            return Command::SUCCESS;
        }

        $this->info("Found {$invoices->count()} overdue invoice(s) to process.");
        $this->newLine();

        $created = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($invoices as $invoice) {
            $lastReminder = $invoice->reminders()->latest()->first();
            $currentLevel = $lastReminder ? $lastReminder->reminder_level : 0;
            $nextLevel = $currentLevel + 1;

            // Skip if specific level requested and doesn't match
            if ($level && $nextLevel !== $level) {
                $skipped++;
                continue;
            }

            // Skip if already at level 3
            if ($currentLevel >= 3) {
                $this->line("Skipping: {$invoice->invoice_number} (already at level 3)");
                $skipped++;
                continue;
            }

            // Check if enough time has passed since last reminder
            if ($lastReminder && !$this->shouldSendNextReminder($lastReminder)) {
                $this->line("Skipping: {$invoice->invoice_number} (not enough time since last reminder)");
                $skipped++;
                continue;
            }

            $daysOverdue = now()->diffInDays($invoice->due_date);
            $amountDue = $invoice->total_incl_vat - $invoice->amount_paid;

            $this->line("Processing: {$invoice->invoice_number} ({$invoice->partner->name})");
            $this->line("  Days overdue: {$daysOverdue}, Amount due: " . number_format($amountDue, 2) . " EUR");
            $this->line("  Creating reminder level {$nextLevel}");

            if ($dryRun) {
                $this->info("  [DRY RUN] Would create reminder level {$nextLevel}");
                $created++;
                continue;
            }

            try {
                $reminder = PaymentReminder::createForInvoice($invoice, $nextLevel);

                $this->info("  Created reminder: Level {$reminder->reminder_level}");
                $this->line("    - Amount due: " . number_format($reminder->amount_due, 2) . " EUR");
                $this->line("    - Late fee: " . number_format($reminder->late_fee, 2) . " EUR");
                $this->line("    - Interest: " . number_format($reminder->interest_amount, 2) . " EUR");
                $this->line("    - Total: " . number_format($reminder->total_amount, 2) . " EUR");
                $created++;

                // Update invoice status to overdue if not already
                if ($invoice->status !== 'overdue') {
                    $invoice->update(['status' => 'overdue']);
                }

                // Log the reminder creation
                Log::info("Payment reminder created", [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'reminder_id' => $reminder->id,
                    'reminder_level' => $reminder->reminder_level,
                    'amount_due' => $reminder->amount_due,
                    'late_fee' => $reminder->late_fee,
                    'interest_amount' => $reminder->interest_amount,
                    'total_amount' => $reminder->total_amount,
                ]);

            } catch (\Exception $e) {
                $this->error("  Failed: {$e->getMessage()}");
                $failed++;

                Log::error("Failed to create payment reminder", [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Reminder process complete:");
        $this->line("  - Created: {$created}");
        $this->line("  - Skipped: {$skipped}");

        if ($failed > 0) {
            $this->error("  - Failed: {$failed}");
        }

        // Show summary of pending reminders
        $this->newLine();
        $this->showPendingReminders();

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Check if enough time has passed to send the next reminder level.
     */
    protected function shouldSendNextReminder(PaymentReminder $lastReminder): bool
    {
        $daysSinceLastReminder = now()->diffInDays($lastReminder->reminder_date);

        // Wait periods between reminder levels
        $waitPeriods = [
            1 => 7,  // Wait 7 days after level 1
            2 => 5,  // Wait 5 days after level 2
            3 => 0,  // No more reminders after level 3
        ];

        $waitDays = $waitPeriods[$lastReminder->reminder_level] ?? 7;

        return $daysSinceLastReminder >= $waitDays;
    }

    /**
     * Show summary of pending reminders.
     */
    protected function showPendingReminders(): void
    {
        $pending = PaymentReminder::pending()
            ->with(['invoice.partner'])
            ->orderBy('reminder_level')
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No pending reminders to send.');
            return;
        }

        $this->info("Pending reminders ({$pending->count()}):");
        $this->newLine();

        $headers = ['Invoice', 'Client', 'Level', 'Amount', 'Method'];
        $rows = [];

        foreach ($pending as $reminder) {
            $rows[] = [
                $reminder->invoice->invoice_number,
                $reminder->invoice->partner->name,
                $reminder->reminder_level,
                number_format($reminder->total_amount, 2) . ' EUR',
                $reminder->send_method,
            ];
        }

        $this->table($headers, $rows);
    }
}
