<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringTransaction extends Model
{
    use HasFactory, HasUuid, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'partner_id',
        'type',
        'description',
        'notes',
        'amount',
        'currency',
        'frequency',
        'interval',
        'start_date',
        'end_date',
        'next_occurrence_date',
        'category',
        'account_code',
        'vat_code',
        'is_active',
        'auto_create',
        'occurrences_count',
        'last_executed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'interval' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'next_occurrence_date' => 'date',
            'is_active' => 'boolean',
            'auto_create' => 'boolean',
            'occurrences_count' => 'integer',
            'last_executed_at' => 'datetime',
        ];
    }

    public const TYPES = [
        'income' => 'Revenu',
        'expense' => 'Dépense',
    ];

    public const FREQUENCIES = [
        'daily' => 'Quotidien',
        'weekly' => 'Hebdomadaire',
        'monthly' => 'Mensuel',
        'quarterly' => 'Trimestriel',
        'yearly' => 'Annuel',
    ];

    /**
     * Company relationship.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Partner relationship.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Get frequency label.
     */
    public function getFrequencyLabelAttribute(): string
    {
        return self::FREQUENCIES[$this->frequency] ?? $this->frequency;
    }

    /**
     * Check if transaction is due.
     */
    public function isDue(): bool
    {
        return $this->is_active
            && $this->next_occurrence_date
            && $this->next_occurrence_date->isPast();
    }

    /**
     * Check if transaction has ended.
     */
    public function hasEnded(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    /**
     * Calculate next occurrence date.
     */
    public function calculateNextDate(): ?Carbon
    {
        if (!$this->next_occurrence_date) {
            return null;
        }

        $next = $this->next_occurrence_date->copy();

        return match ($this->frequency) {
            'daily' => $next->addDays($this->interval),
            'weekly' => $next->addWeeks($this->interval),
            'monthly' => $next->addMonths($this->interval),
            'quarterly' => $next->addMonths($this->interval * 3),
            'yearly' => $next->addYears($this->interval),
            default => $next->addMonths($this->interval),
        };
    }

    /**
     * Mark as executed and update next occurrence.
     */
    public function markExecuted(): void
    {
        $this->increment('occurrences_count');
        $this->last_executed_at = now();
        $this->next_occurrence_date = $this->calculateNextDate();

        // Deactivate if end date passed
        if ($this->hasEnded()) {
            $this->is_active = false;
        }

        $this->save();
    }

    /**
     * Scope for active transactions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for income transactions.
     */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope for expense transactions.
     */
    public function scopeExpenses($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope for due transactions.
     */
    public function scopeDue($query)
    {
        return $query->active()
            ->where('next_occurrence_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope for upcoming transactions.
     */
    public function scopeUpcoming($query, int $days = 30)
    {
        return $query->active()
            ->whereBetween('next_occurrence_date', [now(), now()->addDays($days)]);
    }
}
