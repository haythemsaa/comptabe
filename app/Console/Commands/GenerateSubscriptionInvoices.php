<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Notifications\PaymentDueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateSubscriptionInvoices extends Command
{
    protected $signature = 'subscriptions:generate-invoices {--force : Force generation even if not due}';

    protected $description = 'Generate subscription invoices for active subscriptions due for renewal';

    public function handle(): int
    {
        $this->info('Starting subscription invoice generation...');

        $force = $this->option('force');

        // Get all active subscriptions that need invoicing
        $subscriptions = Subscription::with(['company.users', 'plan'])
            ->where('status', 'active')
            ->where(function ($query) use ($force) {
                if ($force) {
                    // Force mode: include all active
                    return;
                }
                // Normal mode: only those due within the next 7 days
                $query->where('current_period_end', '<=', now()->addDays(7));
            })
            ->where('cancel_at_period_end', false)
            ->get();

        $count = 0;

        foreach ($subscriptions as $subscription) {
            // Check if invoice already exists for this period
            $existingInvoice = SubscriptionInvoice::where('subscription_id', $subscription->id)
                ->where('period_start', $subscription->current_period_start)
                ->first();

            if ($existingInvoice && !$force) {
                $this->line("  - Skipping {$subscription->company->name} (invoice already exists)");
                continue;
            }

            try {
                DB::beginTransaction();

                // Generate invoice
                $invoice = $this->generateInvoice($subscription);

                // Update subscription period
                $this->renewSubscriptionPeriod($subscription);

                DB::commit();

                // Send notification
                $this->notifyCompanyOwners($subscription->company, new PaymentDueNotification($invoice));

                $this->line("  - Generated invoice {$invoice->invoice_number} for {$subscription->company->name}");
                $count++;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("  - Error generating invoice for {$subscription->company->name}: {$e->getMessage()}");
                Log::error("Failed to generate subscription invoice", [
                    'subscription_id' => $subscription->id,
                    'company' => $subscription->company->name,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Generated {$count} invoices.");

        return Command::SUCCESS;
    }

    protected function generateInvoice(Subscription $subscription): SubscriptionInvoice
    {
        $plan = $subscription->plan;
        $company = $subscription->company;

        // Calculate amounts
        $subtotal = $subscription->amount;
        $vatRate = 21; // Belgian VAT rate
        $vatAmount = round($subtotal * ($vatRate / 100), 2);
        $total = $subtotal + $vatAmount;

        // Generate invoice number
        $year = now()->format('Y');
        $lastInvoice = SubscriptionInvoice::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $sequence = $lastInvoice
            ? (int) substr($lastInvoice->invoice_number, -5) + 1
            : 1;

        $invoiceNumber = "SUB-{$year}-" . str_pad($sequence, 5, '0', STR_PAD_LEFT);

        // Calculate period
        $periodStart = $subscription->current_period_end ?? now();
        $periodEnd = $subscription->billing_cycle === 'yearly'
            ? $periodStart->copy()->addYear()
            : $periodStart->copy()->addMonth();

        return SubscriptionInvoice::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => $invoiceNumber,
            'status' => 'pending',
            'subtotal' => $subtotal,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'total' => $total,
            'currency' => 'EUR',
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'due_date' => now()->addDays(14),
            'billing_details' => [
                'company_name' => $company->name,
                'vat_number' => $company->vat_number,
                'address' => $company->full_address,
                'plan_name' => $plan->name,
                'billing_cycle' => $subscription->billing_cycle,
            ],
        ]);
    }

    protected function renewSubscriptionPeriod(Subscription $subscription): void
    {
        $newPeriodStart = $subscription->current_period_end ?? now();
        $newPeriodEnd = $subscription->billing_cycle === 'yearly'
            ? $newPeriodStart->copy()->addYear()
            : $newPeriodStart->copy()->addMonth();

        $subscription->update([
            'current_period_start' => $newPeriodStart,
            'current_period_end' => $newPeriodEnd,
        ]);
    }

    protected function notifyCompanyOwners($company, $notification): void
    {
        $users = $company->users()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->get();

        foreach ($users as $user) {
            $user->notify($notification);
        }
    }
}
