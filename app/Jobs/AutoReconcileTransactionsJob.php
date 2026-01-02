<?php

namespace App\Jobs;

use App\Models\BankTransaction;
use App\Models\Company;
use App\Services\AI\SmartReconciliationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AutoReconcileTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600;  // 10 minutes for large batches

    public function __construct(
        public string $companyId,
        public ?string $bankAccountId = null,  // If null, process all accounts
        public float $confidenceThreshold = 0.95  // Only auto-reconcile if >= 95% confidence
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SmartReconciliationService $reconciliationService): void
    {
        Log::info('Starting auto-reconciliation job', [
            'company_id' => $this->companyId,
            'bank_account_id' => $this->bankAccountId,
            'confidence_threshold' => $this->confidenceThreshold,
        ]);

        $query = BankTransaction::where('company_id', $this->companyId)
            ->whereNull('reconciled_at')
            ->whereNull('reconciled_invoice_id')
            ->whereNull('reconciled_expense_id');

        if ($this->bankAccountId) {
            $query->where('bank_account_id', $this->bankAccountId);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        $reconciled = 0;
        $suggestions = 0;
        $noMatch = 0;

        foreach ($transactions as $transaction) {
            try {
                $matches = $reconciliationService->findMatches($transaction, [
                    'include_invoices' => true,
                    'include_expenses' => true,
                    'max_results' => 5,
                ]);

                if (empty($matches)) {
                    $noMatch++;
                    continue;
                }

                $bestMatch = $matches[0];

                // Auto-reconcile if confidence is very high
                if ($bestMatch['confidence'] >= $this->confidenceThreshold) {
                    $this->performReconciliation($transaction, $bestMatch);
                    $reconciled++;

                    Log::debug('Transaction auto-reconciled', [
                        'transaction_id' => $transaction->id,
                        'match_type' => $bestMatch['type'],
                        'match_id' => $bestMatch['id'],
                        'confidence' => $bestMatch['confidence'],
                    ]);
                } else {
                    // Store suggestions for manual review
                    $transaction->update([
                        'ai_reconciliation_suggestions' => array_slice($matches, 0, 3),
                        'suggested_at' => now(),
                    ]);
                    $suggestions++;

                    Log::debug('Reconciliation suggestions stored', [
                        'transaction_id' => $transaction->id,
                        'suggestions_count' => count($matches),
                        'best_confidence' => $bestMatch['confidence'],
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to reconcile transaction', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Auto-reconciliation job completed', [
            'company_id' => $this->companyId,
            'total_processed' => $transactions->count(),
            'auto_reconciled' => $reconciled,
            'suggestions_stored' => $suggestions,
            'no_match' => $noMatch,
        ]);

        // Send notification
        $this->notifyCompletion($reconciled, $suggestions);
    }

    /**
     * Perform reconciliation.
     */
    protected function performReconciliation(BankTransaction $transaction, array $match): void
    {
        DB::transaction(function () use ($transaction, $match) {
            $updateData = [
                'reconciled_at' => now(),
                'reconciled_by' => null,  // Auto-reconciled by AI
                'ai_reconciled' => true,
                'ai_confidence' => $match['confidence'],
            ];

            if ($match['type'] === 'invoice') {
                $updateData['reconciled_invoice_id'] = $match['id'];

                // Update invoice payment status
                $invoice = \App\Models\Invoice::find($match['id']);
                if ($invoice && $invoice->status !== 'paid') {
                    $invoice->update([
                        'status' => 'paid',
                        'payment_date' => $transaction->transaction_date,
                        'payment_method' => 'bank_transfer',
                    ]);
                }
            } elseif ($match['type'] === 'expense') {
                $updateData['reconciled_expense_id'] = $match['id'];

                // Update expense payment status
                $expense = \App\Models\Expense::find($match['id']);
                if ($expense && $expense->status !== 'paid') {
                    $expense->update([
                        'status' => 'paid',
                        'payment_date' => $transaction->transaction_date,
                        'payment_method' => 'bank_transfer',
                    ]);
                }
            }

            $transaction->update($updateData);
        });
    }

    /**
     * Notify completion to relevant users.
     */
    protected function notifyCompletion(int $reconciled, int $suggestions): void
    {
        if ($reconciled == 0 && $suggestions == 0) {
            return;
        }

        $company = Company::find($this->companyId);
        if (!$company) {
            return;
        }

        $accountants = $company->users()
            ->wherePivot('role', 'accountant')
            ->orWherePivot('role', 'owner')
            ->orWherePivot('role', 'admin')
            ->get();

        foreach ($accountants as $accountant) {
            // TODO: Send notification
            // $accountant->notify(new TransactionsReconciledNotification($reconciled, $suggestions));
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Auto-reconciliation job failed permanently', [
            'company_id' => $this->companyId,
            'error' => $exception->getMessage(),
        ]);
    }
}
