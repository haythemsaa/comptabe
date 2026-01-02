<?php

namespace App\Services\AI;

use App\Models\JournalEntry;
use App\Models\BankTransaction;
use App\Models\Expense;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccountingValidationService
{
    /**
     * Detect duplicate transactions.
     */
    public function detectDuplicateTransactions(string $companyId, int $windowDays = 7): array
    {
        $duplicates = [];

        // Check bank transactions
        $bankDuplicates = $this->findDuplicateBankTransactions($companyId, $windowDays);
        $duplicates = array_merge($duplicates, $bankDuplicates);

        // Check expenses
        $expenseDuplicates = $this->findDuplicateExpenses($companyId, $windowDays);
        $duplicates = array_merge($duplicates, $expenseDuplicates);

        // Check invoices
        $invoiceDuplicates = $this->findDuplicateInvoices($companyId, $windowDays);
        $duplicates = array_merge($duplicates, $invoiceDuplicates);

        return $duplicates;
    }

    /**
     * Find duplicate bank transactions.
     */
    protected function findDuplicateBankTransactions(string $companyId, int $windowDays): array
    {
        $transactions = BankTransaction::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays($windowDays))
            ->orderBy('transaction_date')
            ->get();

        $duplicates = [];
        $seen = [];

        foreach ($transactions as $transaction) {
            $key = $this->generateTransactionKey($transaction);

            if (isset($seen[$key])) {
                // Found duplicate
                $original = $seen[$key];
                $duplicates[] = [
                    'type' => 'bank_transaction',
                    'original_id' => $original->id,
                    'duplicate_id' => $transaction->id,
                    'original_date' => $original->transaction_date,
                    'duplicate_date' => $transaction->transaction_date,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'similarity' => $this->calculateSimilarity($original, $transaction),
                    'auto_merge_safe' => $this->isSafeToAutoMerge($original, $transaction),
                ];
            } else {
                $seen[$key] = $transaction;
            }
        }

        return $duplicates;
    }

    /**
     * Generate transaction key for duplicate detection.
     */
    protected function generateTransactionKey($transaction): string
    {
        $amount = round($transaction->amount, 2);
        $date = $transaction->transaction_date instanceof Carbon
            ? $transaction->transaction_date->format('Y-m-d')
            : $transaction->transaction_date;
        $description = strtolower(trim($transaction->description ?? ''));

        return md5($amount . '|' . $date . '|' . $description);
    }

    /**
     * Find duplicate expenses.
     */
    protected function findDuplicateExpenses(string $companyId, int $windowDays): array
    {
        $expenses = Expense::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays($windowDays))
            ->with('partner')
            ->get();

        $duplicates = [];
        $groups = $expenses->groupBy(function ($expense) {
            return $expense->partner_id . '_' . round($expense->total_amount, 2) . '_' . $expense->expense_date->format('Y-m-d');
        });

        foreach ($groups as $key => $group) {
            if ($group->count() > 1) {
                $first = $group->first();
                foreach ($group->skip(1) as $duplicate) {
                    $duplicates[] = [
                        'type' => 'expense',
                        'original_id' => $first->id,
                        'duplicate_id' => $duplicate->id,
                        'partner_name' => $first->partner?->name,
                        'amount' => $first->total_amount,
                        'date' => $first->expense_date,
                        'similarity' => 1.0,  // Exact match
                        'auto_merge_safe' => true,
                    ];
                }
            }
        }

        return $duplicates;
    }

    /**
     * Find duplicate invoices.
     */
    protected function findDuplicateInvoices(string $companyId, int $windowDays): array
    {
        $invoices = Invoice::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays($windowDays))
            ->with('partner')
            ->get();

        $duplicates = [];

        // Group by invoice number + partner
        $groups = $invoices->groupBy(function ($invoice) {
            return $invoice->partner_id . '_' . $invoice->invoice_number;
        });

        foreach ($groups as $key => $group) {
            if ($group->count() > 1) {
                $first = $group->first();
                foreach ($group->skip(1) as $duplicate) {
                    $duplicates[] = [
                        'type' => 'invoice',
                        'original_id' => $first->id,
                        'duplicate_id' => $duplicate->id,
                        'invoice_number' => $first->invoice_number,
                        'partner_name' => $first->partner?->name,
                        'amount' => $first->total_amount,
                        'date' => $first->issue_date,
                        'similarity' => 1.0,
                        'auto_merge_safe' => false,  // Invoices should be manually reviewed
                    ];
                }
            }
        }

        return $duplicates;
    }

    /**
     * Calculate similarity between two transactions.
     */
    protected function calculateSimilarity($transaction1, $transaction2): float
    {
        $similarity = 0;

        // Amount match (exact)
        if (abs($transaction1->amount - $transaction2->amount) < 0.01) {
            $similarity += 0.4;
        }

        // Date match (within 1 day)
        $dateDiff = abs($transaction1->transaction_date->diffInDays($transaction2->transaction_date));
        if ($dateDiff == 0) {
            $similarity += 0.3;
        } elseif ($dateDiff == 1) {
            $similarity += 0.2;
        }

        // Description similarity
        $desc1 = strtolower($transaction1->description ?? '');
        $desc2 = strtolower($transaction2->description ?? '');
        similar_text($desc1, $desc2, $percent);
        $similarity += ($percent / 100) * 0.3;

        return $similarity;
    }

    /**
     * Check if it's safe to auto-merge.
     */
    protected function isSafeToAutoMerge($transaction1, $transaction2): bool
    {
        // Don't auto-merge if either is already reconciled
        if ($transaction1->reconciled_at || $transaction2->reconciled_at) {
            return false;
        }

        // Don't auto-merge if there's a time gap > 24 hours
        if (abs($transaction1->created_at->diffInHours($transaction2->created_at)) > 24) {
            return false;
        }

        // Must be exact amount and description match
        if (abs($transaction1->amount - $transaction2->amount) > 0.01) {
            return false;
        }

        if ($transaction1->description !== $transaction2->description) {
            return false;
        }

        return true;
    }

    /**
     * Validate accounting rules for journal entries.
     */
    public function validateJournalEntries(string $companyId): array
    {
        $issues = [];

        // Check for unbalanced entries
        $unbalanced = $this->findUnbalancedEntries($companyId);
        $issues = array_merge($issues, $unbalanced);

        // Check for missing contra accounts
        $missingContra = $this->findMissingContraAccounts($companyId);
        $issues = array_merge($issues, $missingContra);

        // Check for unusual account balances
        $unusualBalances = $this->findUnusualAccountBalances($companyId);
        $issues = array_merge($issues, $unusualBalances);

        return $issues;
    }

    /**
     * Find unbalanced journal entries (debit != credit).
     */
    protected function findUnbalancedEntries(string $companyId): array
    {
        $entries = JournalEntry::where('company_id', $companyId)
            ->with('lines')
            ->get();

        $unbalanced = [];

        foreach ($entries as $entry) {
            $totalDebit = $entry->lines->where('type', 'debit')->sum('amount');
            $totalCredit = $entry->lines->where('type', 'credit')->sum('amount');

            $difference = abs($totalDebit - $totalCredit);

            if ($difference > 0.01) {  // Allow 1 cent rounding difference
                $unbalanced[] = [
                    'type' => 'unbalanced_entry',
                    'severity' => 'critical',
                    'entry_id' => $entry->id,
                    'entry_number' => $entry->entry_number,
                    'date' => $entry->entry_date,
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'difference' => $difference,
                    'description' => "Écriture déséquilibrée : différence de " . number_format($difference, 2) . " €",
                ];
            }
        }

        return $unbalanced;
    }

    /**
     * Find entries with missing contra accounts.
     */
    protected function findMissingContraAccounts(string $companyId): array
    {
        $entries = JournalEntry::where('company_id', $companyId)
            ->with('lines.account')
            ->get();

        $missing = [];

        foreach ($entries as $entry) {
            if ($entry->lines->count() < 2) {
                $missing[] = [
                    'type' => 'missing_contra',
                    'severity' => 'high',
                    'entry_id' => $entry->id,
                    'entry_number' => $entry->entry_number,
                    'date' => $entry->entry_date,
                    'lines_count' => $entry->lines->count(),
                    'description' => "Écriture incomplète : manque de compte(s) de contrepartie",
                ];
            }
        }

        return $missing;
    }

    /**
     * Find accounts with unusual balances.
     */
    protected function findUnusualAccountBalances(string $companyId): array
    {
        $unusual = [];

        // Find accounts that should never have negative balance (assets)
        $assetAccounts = DB::table('chart_of_accounts')
            ->where('company_id', $companyId)
            ->where('account_type', 'asset')
            ->get();

        foreach ($assetAccounts as $account) {
            $balance = $this->calculateAccountBalance($account->id);

            if ($balance < 0) {
                $unusual[] = [
                    'type' => 'unusual_balance',
                    'severity' => 'medium',
                    'account_id' => $account->id,
                    'account_number' => $account->account_number,
                    'account_name' => $account->account_name,
                    'balance' => $balance,
                    'description' => "Compte d'actif avec solde négatif : " . number_format($balance, 2) . " €",
                ];
            }
        }

        // Find liability accounts with positive balance (unusual)
        $liabilityAccounts = DB::table('chart_of_accounts')
            ->where('company_id', $companyId)
            ->where('account_type', 'liability')
            ->get();

        foreach ($liabilityAccounts as $account) {
            $balance = $this->calculateAccountBalance($account->id);

            if ($balance > 0) {
                $unusual[] = [
                    'type' => 'unusual_balance',
                    'severity' => 'low',
                    'account_id' => $account->id,
                    'account_number' => $account->account_number,
                    'account_name' => $account->account_name,
                    'balance' => $balance,
                    'description' => "Compte de passif avec solde positif : " . number_format($balance, 2) . " €",
                ];
            }
        }

        return $unusual;
    }

    /**
     * Calculate account balance.
     */
    protected function calculateAccountBalance(string $accountId): float
    {
        $debits = DB::table('journal_entry_lines')
            ->where('account_id', $accountId)
            ->where('type', 'debit')
            ->sum('amount');

        $credits = DB::table('journal_entry_lines')
            ->where('account_id', $accountId)
            ->where('type', 'credit')
            ->sum('amount');

        return $debits - $credits;
    }

    /**
     * Auto-fix unbalanced entries if possible.
     */
    public function autoFixUnbalancedEntry(string $entryId): bool
    {
        $entry = JournalEntry::with('lines')->find($entryId);

        if (!$entry) {
            return false;
        }

        $totalDebit = $entry->lines->where('type', 'debit')->sum('amount');
        $totalCredit = $entry->lines->where('type', 'credit')->sum('amount');
        $difference = $totalDebit - $totalCredit;

        // Only auto-fix small rounding errors (< 1€)
        if (abs($difference) > 1) {
            return false;
        }

        // Adjust the last line to balance
        $lastLine = $entry->lines->sortByDesc('created_at')->first();

        if (!$lastLine) {
            return false;
        }

        if ($difference > 0) {
            // Need more credit
            if ($lastLine->type === 'credit') {
                $lastLine->amount += abs($difference);
            } else {
                $lastLine->amount -= abs($difference);
            }
        } else {
            // Need more debit
            if ($lastLine->type === 'debit') {
                $lastLine->amount += abs($difference);
            } else {
                $lastLine->amount -= abs($difference);
            }
        }

        $lastLine->save();

        return true;
    }
}
