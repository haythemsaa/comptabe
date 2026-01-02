<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use App\Models\Invoice;
use App\Models\BankAccount;
use App\Models\VatDeclaration;
use App\Notifications\InvoiceOverdueNotification;
use App\Notifications\LowCashFlowNotification;
use App\Notifications\BankReconciliationPendingNotification;
use App\Notifications\VatDeclarationDueNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Run all notification checks for a company
     */
    public function runAllChecks(Company $company): array
    {
        $results = [];

        try {
            $results['invoices_overdue'] = $this->checkInvoiceOverdue($company);
            $results['low_cash_flow'] = $this->checkLowCashFlow($company);
            $results['bank_reconciliation'] = $this->checkBankReconciliation($company);
            $results['vat_declarations'] = $this->checkVatDeclarations($company);
        } catch (\Exception $e) {
            Log::error('Error running notification checks', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $results;
    }

    /**
     * Check for overdue invoices and send notifications
     */
    public function checkInvoiceOverdue(Company $company): bool
    {
        // Get overdue invoices (sent, not paid, past due date)
        $overdueInvoices = Invoice::where('company_id', $company->id)
            ->where('status', 'sent')
            ->whereDate('due_date', '<', now())
            ->with('partner')
            ->get();

        if ($overdueInvoices->isEmpty()) {
            return false;
        }

        $overdueCount = $overdueInvoices->count();
        $totalAmount = $overdueInvoices->sum('total_amount');

        // Calculate average days overdue
        $totalDaysOverdue = $overdueInvoices->sum(function ($invoice) {
            return now()->diffInDays($invoice->due_date);
        });
        $avgDaysOverdue = round($totalDaysOverdue / $overdueCount);

        // Get oldest invoice
        $oldestInvoice = $overdueInvoices->sortBy('due_date')->first();

        // Send notification to company admins
        $admins = $company->users()
            ->wherePivot('role', 'admin')
            ->orWherePivot('role', 'owner')
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new InvoiceOverdueNotification(
                $overdueCount,
                $totalAmount,
                $avgDaysOverdue,
                $oldestInvoice
            ));
        }

        Log::info('Invoice overdue notification sent', [
            'company_id' => $company->id,
            'overdue_count' => $overdueCount,
            'total_amount' => $totalAmount,
        ]);

        return true;
    }

    /**
     * Check cash flow projections and send alerts if low
     */
    public function checkLowCashFlow(Company $company): bool
    {
        // Get current bank balance
        $currentBalance = BankAccount::where('company_id', $company->id)
            ->sum('current_balance');

        // Get upcoming receivables (invoices due in next 30 days)
        $upcomingReceivables = Invoice::where('company_id', $company->id)
            ->where('type', 'sale')
            ->where('status', 'sent')
            ->whereBetween('due_date', [now(), now()->addDays(30)])
            ->sum('total_amount');

        // Get upcoming payables (bills due in next 30 days)
        $upcomingPayables = Invoice::where('company_id', $company->id)
            ->where('type', 'purchase')
            ->where('status', 'sent')
            ->whereBetween('due_date', [now(), now()->addDays(30)])
            ->sum('total_amount');

        // Calculate projected balance
        $projectedBalance = $currentBalance + $upcomingReceivables - $upcomingPayables;

        // Check if projected balance is negative
        if ($projectedBalance >= 0) {
            return false;
        }

        // Calculate days until negative (simplified projection)
        $dailyBurnRate = $upcomingPayables / 30; // Average daily expenses
        $daysUntilNegative = $dailyBurnRate > 0
            ? max(1, (int) ($currentBalance / $dailyBurnRate))
            : 30;

        // Only send notification if it's becoming critical (within 30 days)
        if ($daysUntilNegative > 30) {
            return false;
        }

        // Send notification to company admins
        $admins = $company->users()
            ->wherePivot('role', 'admin')
            ->orWherePivot('role', 'owner')
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new LowCashFlowNotification(
                $currentBalance,
                $projectedBalance,
                $daysUntilNegative,
                $upcomingPayables,
                $upcomingReceivables
            ));
        }

        Log::warning('Low cash flow notification sent', [
            'company_id' => $company->id,
            'current_balance' => $currentBalance,
            'projected_balance' => $projectedBalance,
            'days_until_negative' => $daysUntilNegative,
        ]);

        return true;
    }

    /**
     * Check for pending bank reconciliations
     */
    public function checkBankReconciliation(Company $company): bool
    {
        $bankAccounts = BankAccount::where('company_id', $company->id)
            ->where('is_active', true)
            ->get();

        $notificationsSent = false;

        foreach ($bankAccounts as $account) {
            // Get unreconciled transactions count
            $pendingCount = DB::table('bank_transactions')
                ->where('bank_account_id', $account->id)
                ->whereNull('reconciled_at')
                ->count();

            if ($pendingCount === 0) {
                continue;
            }

            // Get total unreconciled amount
            $totalUnreconciled = DB::table('bank_transactions')
                ->where('bank_account_id', $account->id)
                ->whereNull('reconciled_at')
                ->sum('amount');

            // Get days since last reconciliation
            $lastReconciliation = DB::table('bank_transactions')
                ->where('bank_account_id', $account->id)
                ->whereNotNull('reconciled_at')
                ->max('reconciled_at');

            $daysSinceLast = $lastReconciliation
                ? Carbon::parse($lastReconciliation)->diffInDays(now())
                : 90; // Default to 90 if never reconciled

            // Only send notification if more than 14 days since last reconciliation
            if ($daysSinceLast < 14) {
                continue;
            }

            // Send notification to company admins
            $admins = $company->users()
                ->wherePivot('role', 'admin')
                ->orWherePivot('role', 'owner')
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new BankReconciliationPendingNotification(
                    $pendingCount,
                    $totalUnreconciled,
                    $daysSinceLast,
                    $account->name,
                    $account->id
                ));
            }

            $notificationsSent = true;

            Log::info('Bank reconciliation notification sent', [
                'company_id' => $company->id,
                'bank_account_id' => $account->id,
                'pending_count' => $pendingCount,
                'days_since_last' => $daysSinceLast,
            ]);
        }

        return $notificationsSent;
    }

    /**
     * Check for upcoming VAT declaration deadlines
     */
    public function checkVatDeclarations(Company $company): bool
    {
        // Get company VAT periodicity from settings
        $periodicity = $company->settings['vat_periodicity'] ?? 'monthly';

        // Calculate current period and due date
        $currentDate = now();

        if ($periodicity === 'monthly') {
            $period = $currentDate->copy()->subMonth()->format('F Y');
            // Monthly VAT is due on the 20th of the following month
            $dueDate = $currentDate->copy()->day(20);

            // If we're past the 20th, it's overdue
            if ($currentDate->day > 20) {
                $isOverdue = true;
                $daysUntilDue = $currentDate->diffInDays($dueDate);
            } else {
                $isOverdue = false;
                $daysUntilDue = $currentDate->diffInDays($dueDate);
            }
        } else {
            // Quarterly
            $quarter = $currentDate->copy()->subMonths(3)->quarter;
            $year = $currentDate->copy()->subMonths(3)->year;
            $period = "Q{$quarter} {$year}";

            // Quarterly VAT is due on the 20th of the month following the quarter
            $quarterEndMonth = $quarter * 3;
            $dueDate = Carbon::create($year, $quarterEndMonth, 1)->addMonth()->day(20);

            $isOverdue = $currentDate->isAfter($dueDate);
            $daysUntilDue = $isOverdue
                ? $currentDate->diffInDays($dueDate)
                : $currentDate->diffInDays($dueDate);
        }

        // Only send notification if within 7 days or overdue
        if (!$isOverdue && $daysUntilDue > 7) {
            return false;
        }

        // Estimate VAT amount
        $estimatedVatAmount = $this->estimateVatAmount($company, $periodicity);

        // Send notification to company admins
        $admins = $company->users()
            ->wherePivot('role', 'admin')
            ->orWherePivot('role', 'owner')
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new VatDeclarationDueNotification(
                $period,
                $periodicity === 'monthly' ? 'Mensuelle' : 'Trimestrielle',
                $dueDate->format('d/m/Y'),
                $daysUntilDue,
                $estimatedVatAmount,
                $isOverdue
            ));
        }

        Log::info('VAT declaration notification sent', [
            'company_id' => $company->id,
            'period' => $period,
            'is_overdue' => $isOverdue,
            'days_until_due' => $daysUntilDue,
        ]);

        return true;
    }

    /**
     * Estimate VAT amount for the period
     */
    private function estimateVatAmount(Company $company, string $periodicity): ?float
    {
        $startDate = $periodicity === 'monthly'
            ? now()->subMonth()->startOfMonth()
            : now()->subMonths(3)->startOfQuarter();

        $endDate = $periodicity === 'monthly'
            ? now()->subMonth()->endOfMonth()
            : now()->subMonths(3)->endOfQuarter();

        // Get VAT collected (sales)
        $vatCollected = Invoice::where('company_id', $company->id)
            ->where('type', 'sale')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('vat_amount');

        // Get VAT paid (purchases)
        $vatPaid = Invoice::where('company_id', $company->id)
            ->where('type', 'purchase')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('vat_amount');

        return $vatCollected - $vatPaid;
    }

    /**
     * Get notification statistics for a company
     */
    public function getStatistics(Company $company): array
    {
        $user = $company->users()->first();

        if (!$user) {
            return [];
        }

        return [
            'total_notifications' => $user->notifications()->count(),
            'unread_notifications' => $user->unreadNotifications()->count(),
            'by_type' => DB::table('notifications')
                ->where('notifiable_id', $user->id)
                ->where('notifiable_type', User::class)
                ->select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type')
                ->toArray(),
            'recent_critical' => $user->notifications()
                ->whereJsonContains('data->severity', 'critical')
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
        ];
    }
}
