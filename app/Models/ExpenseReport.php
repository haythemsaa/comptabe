<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;

class ExpenseReport extends Model
{
    use HasUuid, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'reference',
        'title',
        'description',
        'user_id',
        'period_start',
        'period_end',
        'status',
        'total_amount',
        'total_vat',
        'approved_amount',
        'paid_amount',
        'currency',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
        'payment_method',
        'payment_reference',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_amount' => 'decimal:2',
        'total_vat' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public const STATUSES = [
        'draft' => ['label' => 'Brouillon', 'color' => 'secondary', 'icon' => 'file'],
        'submitted' => ['label' => 'Soumis', 'color' => 'primary', 'icon' => 'send'],
        'under_review' => ['label' => 'En révision', 'color' => 'warning', 'icon' => 'eye'],
        'approved' => ['label' => 'Approuvé', 'color' => 'success', 'icon' => 'check'],
        'rejected' => ['label' => 'Rejeté', 'color' => 'danger', 'icon' => 'x'],
        'paid' => ['label' => 'Remboursé', 'color' => 'info', 'icon' => 'cash'],
        'cancelled' => ['label' => 'Annulé', 'color' => 'secondary', 'icon' => 'ban'],
    ];

    // Relations
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(EmployeeExpense::class);
    }

    // Scopes
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['submitted', 'under_review']);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAwaitingApproval($query)
    {
        return $query->whereIn('status', ['submitted', 'under_review']);
    }

    public function scopeAwaitingPayment($query)
    {
        return $query->where('status', 'approved');
    }

    // Helpers
    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status]['label'] ?? $this->status;
    }

    public function getStatusColor(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'secondary';
    }

    public function getStatusIcon(): string
    {
        return self::STATUSES[$this->status]['icon'] ?? 'file';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'rejected']);
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === 'draft' && $this->expenses()->count() > 0;
    }

    public function canBeApproved(): bool
    {
        return in_array($this->status, ['submitted', 'under_review']);
    }

    public function canBePaid(): bool
    {
        return $this->status === 'approved';
    }

    public function getExpensesCount(): int
    {
        return $this->expenses()->count();
    }

    public function getPendingAmount(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    // Calculate totals from expenses
    public function recalculateTotals(): void
    {
        $expenses = $this->expenses;
        $this->total_amount = $expenses->sum('amount');
        $this->total_vat = $expenses->sum('vat_amount');
        $this->save();
    }

    // Actions
    public function submit(): void
    {
        if (!$this->canBeSubmitted()) return;

        // Mark all draft expenses as pending
        $this->expenses()->where('status', 'draft')->update(['status' => 'pending']);

        $this->recalculateTotals();
        $this->status = 'submitted';
        $this->save();
    }

    public function startReview(): void
    {
        if ($this->status !== 'submitted') return;
        $this->status = 'under_review';
        $this->save();
    }

    public function approve(?User $user = null, ?float $approvedAmount = null): void
    {
        if (!$this->canBeApproved()) return;

        // Approve all pending expenses
        $this->expenses()->where('status', 'pending')->update(['status' => 'approved']);

        $this->status = 'approved';
        $this->approved_at = now();
        $this->approved_by = $user?->id ?? auth()->id();
        $this->approved_amount = $approvedAmount ?? $this->total_amount;
        $this->save();
    }

    public function reject(string $reason, ?User $user = null): void
    {
        if (!$this->canBeApproved()) return;

        // Reject all pending expenses
        $this->expenses()->where('status', 'pending')->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        $this->status = 'rejected';
        $this->rejection_reason = $reason;
        $this->save();
    }

    public function markPaid(?User $user = null, ?string $paymentMethod = null, ?string $paymentRef = null): void
    {
        if (!$this->canBePaid()) return;

        // Mark all approved expenses as reimbursed
        $this->expenses()->where('status', 'approved')->update(['status' => 'reimbursed']);

        $this->status = 'paid';
        $this->paid_at = now();
        $this->paid_by = $user?->id ?? auth()->id();
        $this->paid_amount = $this->approved_amount ?? $this->total_amount;
        $this->payment_method = $paymentMethod;
        $this->payment_reference = $paymentRef;
        $this->save();
    }

    public function cancel(): void
    {
        if (in_array($this->status, ['paid', 'cancelled'])) return;

        // Reset expenses to draft
        $this->expenses()->update(['status' => 'draft', 'rejection_reason' => null]);

        $this->status = 'cancelled';
        $this->save();
    }

    // Reference generation
    public static function generateReference(string $companyId): string
    {
        $year = date('Y');
        $month = date('m');
        $prefix = 'NDF';

        $lastRef = static::where('company_id', $companyId)
            ->where('reference', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderByDesc('reference')
            ->value('reference');

        if ($lastRef) {
            $lastNumber = (int) substr($lastRef, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $nextNumber);
    }
}
