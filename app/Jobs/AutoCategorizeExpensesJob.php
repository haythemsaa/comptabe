<?php

namespace App\Jobs;

use App\Models\Expense;
use App\Models\Company;
use App\Services\AI\IntelligentCategorizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoCategorizeExpensesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public string $companyId,
        public ?array $expenseIds = null  // If null, process all uncategorized
    ) {}

    /**
     * Execute the job.
     */
    public function handle(IntelligentCategorizationService $categorizationService): void
    {
        Log::info('Starting auto-categorization job', [
            'company_id' => $this->companyId,
            'expense_ids' => $this->expenseIds ? count($this->expenseIds) : 'all uncategorized',
        ]);

        $query = Expense::where('company_id', $this->companyId)
            ->whereNull('category');

        if ($this->expenseIds) {
            $query->whereIn('id', $this->expenseIds);
        }

        $expenses = $query->get();

        $categorized = 0;
        $highConfidence = 0;
        $lowConfidence = 0;

        foreach ($expenses as $expense) {
            try {
                $result = $categorizationService->categorize(
                    $expense->description ?? $expense->partner?->name ?? '',
                    [
                        'amount' => $expense->total_amount,
                        'partner' => $expense->partner?->name,
                        'date' => $expense->expense_date,
                    ]
                );

                // Only auto-apply if confidence is high enough (>= 75%)
                if ($result['confidence'] >= 0.75) {
                    $this->applyCategorization($expense, $result);
                    $categorized++;
                    $highConfidence++;

                    Log::debug('Expense auto-categorized', [
                        'expense_id' => $expense->id,
                        'category' => $result['category'],
                        'confidence' => $result['confidence'],
                    ]);
                } else {
                    // Store suggestion for manual review
                    $expense->update([
                        'ai_suggestions' => [
                            'category' => $result['category'],
                            'confidence' => $result['confidence'],
                            'alternatives' => $result['alternatives'],
                            'suggested_at' => now()->toIso8601String(),
                        ],
                    ]);
                    $lowConfidence++;

                    Log::debug('Expense categorization suggestion stored', [
                        'expense_id' => $expense->id,
                        'category' => $result['category'],
                        'confidence' => $result['confidence'],
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to categorize expense', [
                    'expense_id' => $expense->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Auto-categorization job completed', [
            'company_id' => $this->companyId,
            'total_processed' => $expenses->count(),
            'auto_categorized' => $categorized,
            'high_confidence' => $highConfidence,
            'low_confidence' => $lowConfidence,
        ]);

        // Send notification to company owner/admin
        $this->notifyCompletion($categorized, $lowConfidence);
    }

    /**
     * Apply categorization to expense.
     */
    protected function applyCategorization(Expense $expense, array $result): void
    {
        $updateData = [
            'category' => $result['category'],
        ];

        if (isset($result['subcategory'])) {
            $updateData['subcategory'] = $result['subcategory'];
        }

        if (isset($result['account']['id'])) {
            $updateData['account_id'] = $result['account']['id'];
        }

        if (isset($result['vat_code']['id'])) {
            $updateData['vat_code_id'] = $result['vat_code']['id'];
        }

        // Store AI metadata
        $updateData['ai_categorized'] = true;
        $updateData['ai_confidence'] = $result['confidence'];
        $updateData['ai_categorized_at'] = now();

        $expense->update($updateData);
    }

    /**
     * Notify completion to relevant users.
     */
    protected function notifyCompletion(int $categorized, int $needsReview): void
    {
        if ($categorized == 0 && $needsReview == 0) {
            return;
        }

        $company = Company::find($this->companyId);
        if (!$company) {
            return;
        }

        $owners = $company->users()
            ->wherePivot('role', 'owner')
            ->orWherePivot('role', 'admin')
            ->get();

        foreach ($owners as $owner) {
            // TODO: Send notification
            // $owner->notify(new ExpensesCategorizedNotification($categorized, $needsReview));
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Auto-categorization job failed permanently', [
            'company_id' => $this->companyId,
            'error' => $exception->getMessage(),
        ]);
    }
}
