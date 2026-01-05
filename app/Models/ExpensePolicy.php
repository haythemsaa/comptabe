<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToTenant;

class ExpensePolicy extends Model
{
    use HasUuid, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'category_id',
        'daily_limit',
        'monthly_limit',
        'per_expense_limit',
        'requires_pre_approval',
        'pre_approval_threshold',
        'auto_approve_below',
        'auto_approve_threshold',
        'allowed_payment_methods',
        'receipt_required',
        'receipt_required_threshold',
        'is_active',
    ];

    protected $casts = [
        'daily_limit' => 'decimal:2',
        'monthly_limit' => 'decimal:2',
        'per_expense_limit' => 'decimal:2',
        'pre_approval_threshold' => 'decimal:2',
        'auto_approve_threshold' => 'decimal:2',
        'receipt_required_threshold' => 'decimal:2',
        'requires_pre_approval' => 'boolean',
        'auto_approve_below' => 'boolean',
        'receipt_required' => 'boolean',
        'is_active' => 'boolean',
        'allowed_payment_methods' => 'array',
    ];

    // Relations
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCategory($query, ?string $categoryId)
    {
        if ($categoryId) {
            return $query->where(function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                    ->orWhereNull('category_id');
            });
        }
        return $query->whereNull('category_id');
    }

    // Check expense against policy
    public function checkExpense(EmployeeExpense $expense): array
    {
        $violations = [];

        // Check per-expense limit
        if ($this->per_expense_limit && $expense->amount > $this->per_expense_limit) {
            $violations[] = [
                'type' => 'per_expense_limit',
                'message' => "Le montant dépasse la limite par dépense ({$this->per_expense_limit}€)",
                'limit' => $this->per_expense_limit,
                'actual' => $expense->amount,
            ];
        }

        // Check if receipt is required
        if ($this->receipt_required && $expense->amount >= $this->receipt_required_threshold && !$expense->has_receipt) {
            $violations[] = [
                'type' => 'receipt_required',
                'message' => "Un justificatif est requis pour les dépenses >= {$this->receipt_required_threshold}€",
            ];
        }

        // Check payment method
        if ($this->allowed_payment_methods && !in_array($expense->payment_method, $this->allowed_payment_methods)) {
            $violations[] = [
                'type' => 'payment_method',
                'message' => "Mode de paiement non autorisé",
            ];
        }

        // Check if pre-approval required
        if ($this->requires_pre_approval && $expense->amount >= $this->pre_approval_threshold) {
            $violations[] = [
                'type' => 'pre_approval',
                'message' => "Pré-approbation requise pour les dépenses >= {$this->pre_approval_threshold}€",
            ];
        }

        return $violations;
    }

    // Check daily spending
    public function checkDailyLimit(string $userId, string $date, float $newAmount = 0): array
    {
        if (!$this->daily_limit) return [];

        $dailyTotal = EmployeeExpense::where('user_id', $userId)
            ->whereDate('expense_date', $date)
            ->when($this->category_id, fn($q) => $q->where('category_id', $this->category_id))
            ->sum('amount');

        $total = $dailyTotal + $newAmount;

        if ($total > $this->daily_limit) {
            return [[
                'type' => 'daily_limit',
                'message' => "Limite journalière dépassée ({$this->daily_limit}€)",
                'limit' => $this->daily_limit,
                'current' => $dailyTotal,
                'new_total' => $total,
            ]];
        }

        return [];
    }

    // Check monthly spending
    public function checkMonthlyLimit(string $userId, int $year, int $month, float $newAmount = 0): array
    {
        if (!$this->monthly_limit) return [];

        $monthlyTotal = EmployeeExpense::where('user_id', $userId)
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->when($this->category_id, fn($q) => $q->where('category_id', $this->category_id))
            ->sum('amount');

        $total = $monthlyTotal + $newAmount;

        if ($total > $this->monthly_limit) {
            return [[
                'type' => 'monthly_limit',
                'message' => "Limite mensuelle dépassée ({$this->monthly_limit}€)",
                'limit' => $this->monthly_limit,
                'current' => $monthlyTotal,
                'new_total' => $total,
            ]];
        }

        return [];
    }

    // Check if expense can be auto-approved
    public function canAutoApprove(EmployeeExpense $expense): bool
    {
        if (!$this->auto_approve_below) return false;
        if ($expense->amount >= $this->auto_approve_threshold) return false;

        // Check for violations
        $violations = $this->checkExpense($expense);
        return empty($violations);
    }

    // Get applicable policy for expense
    public static function getApplicablePolicy(string $companyId, ?string $categoryId): ?self
    {
        // First try to find category-specific policy
        if ($categoryId) {
            $policy = static::where('company_id', $companyId)
                ->where('category_id', $categoryId)
                ->active()
                ->first();

            if ($policy) return $policy;
        }

        // Fall back to default policy (no category)
        return static::where('company_id', $companyId)
            ->whereNull('category_id')
            ->active()
            ->first();
    }
}
