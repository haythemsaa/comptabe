<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use App\Notifications\PaymentDueNotification;
use App\Notifications\SubscriptionExpiredNotification;
use App\Notifications\TrialEndingNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendSubscriptionNotifications extends Command
{
    protected $signature = 'subscriptions:send-notifications';

    protected $description = 'Send subscription-related notifications (trial ending, payment due, etc.)';

    public function handle(): int
    {
        $this->info('Starting subscription notifications...');

        $this->sendTrialEndingNotifications();
        $this->sendPaymentReminders();
        $this->checkExpiredSubscriptions();

        $this->info('Subscription notifications completed.');

        return Command::SUCCESS;
    }

    protected function sendTrialEndingNotifications(): void
    {
        $this->info('Checking for expiring trials...');

        // Send notifications 7 days, 3 days, and 1 day before trial ends
        $notificationDays = [7, 3, 1];

        foreach ($notificationDays as $days) {
            $subscriptions = Subscription::with(['company.users', 'plan'])
                ->where('status', 'trialing')
                ->whereDate('trial_ends_at', now()->addDays($days)->toDateString())
                ->get();

            foreach ($subscriptions as $subscription) {
                $this->notifyCompanyOwners($subscription->company, new TrialEndingNotification($subscription, $days));
                $this->line("  - Trial ending in {$days} days: {$subscription->company->name}");
            }
        }

        // Check for expired trials that need to be converted or expired
        $expiredTrials = Subscription::with(['company.users', 'plan'])
            ->where('status', 'trialing')
            ->where('trial_ends_at', '<', now())
            ->get();

        foreach ($expiredTrials as $subscription) {
            // Update status to expired
            $subscription->update(['status' => 'expired']);

            $this->notifyCompanyOwners($subscription->company, new SubscriptionExpiredNotification($subscription, 'expired'));
            $this->line("  - Trial expired: {$subscription->company->name}");

            Log::info("Trial expired for company: {$subscription->company->name}");
        }

        $this->info("  Found {$expiredTrials->count()} expired trials.");
    }

    protected function sendPaymentReminders(): void
    {
        $this->info('Checking for payment reminders...');

        // Send reminder 7 days before due date
        $upcomingInvoices = SubscriptionInvoice::with(['company.users', 'subscription'])
            ->where('status', 'pending')
            ->whereDate('due_date', now()->addDays(7)->toDateString())
            ->get();

        foreach ($upcomingInvoices as $invoice) {
            $this->notifyCompanyOwners($invoice->company, new PaymentDueNotification($invoice, true));
            $this->line("  - Payment reminder (7 days): {$invoice->invoice_number}");
        }

        // Send reminder 3 days before due date
        $urgentInvoices = SubscriptionInvoice::with(['company.users', 'subscription'])
            ->where('status', 'pending')
            ->whereDate('due_date', now()->addDays(3)->toDateString())
            ->get();

        foreach ($urgentInvoices as $invoice) {
            $this->notifyCompanyOwners($invoice->company, new PaymentDueNotification($invoice, true));
            $this->line("  - Payment reminder (3 days): {$invoice->invoice_number}");
        }

        // Check for overdue invoices
        $overdueInvoices = SubscriptionInvoice::with(['company.users', 'subscription.plan'])
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->whereDoesntHave('company', function ($q) {
                // Don't send if subscription already suspended
                $q->whereHas('subscription', function ($sq) {
                    $sq->where('status', 'suspended');
                });
            })
            ->get();

        foreach ($overdueInvoices as $invoice) {
            // Update invoice status to overdue
            if ($invoice->status !== 'overdue') {
                $invoice->update(['status' => 'overdue']);
            }

            // Check how many days overdue
            $daysOverdue = $invoice->due_date->diffInDays(now());

            // Send reminder every 3 days for the first 2 weeks
            if ($daysOverdue % 3 === 0 && $daysOverdue <= 14) {
                $this->notifyCompanyOwners($invoice->company, new PaymentDueNotification($invoice, true));
                $this->line("  - Overdue payment reminder ({$daysOverdue} days): {$invoice->invoice_number}");
            }

            // After 14 days, suspend the subscription
            if ($daysOverdue >= 14 && $invoice->subscription) {
                $invoice->subscription->update(['status' => 'suspended']);
                $this->notifyCompanyOwners($invoice->company, new SubscriptionExpiredNotification($invoice->subscription, 'payment_failed'));
                $this->line("  - Subscription suspended due to non-payment: {$invoice->company->name}");

                Log::warning("Subscription suspended due to non-payment: {$invoice->company->name}");
            }
        }

        $this->info("  Found {$overdueInvoices->count()} overdue invoices.");
    }

    protected function checkExpiredSubscriptions(): void
    {
        $this->info('Checking for expired subscriptions...');

        // Check for subscriptions that have passed their period end
        $expiredSubscriptions = Subscription::with(['company.users', 'plan'])
            ->where('status', 'active')
            ->where('current_period_end', '<', now())
            ->where('cancel_at_period_end', true)
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            $subscription->update(['status' => 'cancelled']);
            $this->notifyCompanyOwners($subscription->company, new SubscriptionExpiredNotification($subscription, 'cancelled'));
            $this->line("  - Subscription cancelled at period end: {$subscription->company->name}");

            Log::info("Subscription cancelled at period end: {$subscription->company->name}");
        }

        $this->info("  Found {$expiredSubscriptions->count()} expired subscriptions.");
    }

    protected function notifyCompanyOwners($company, $notification): void
    {
        // Get all owners and admins of the company
        $users = $company->users()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->get();

        foreach ($users as $user) {
            $user->notify($notification);
        }

        // Also notify superadmins if it's a critical notification
        if ($notification instanceof SubscriptionExpiredNotification && $notification->reason === 'payment_failed') {
            $superadmins = User::where('is_superadmin', true)->get();
            foreach ($superadmins as $admin) {
                $admin->notify($notification);
            }
        }
    }
}
