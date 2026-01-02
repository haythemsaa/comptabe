<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasFactory, HasUuid, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'company_id',
        'journal_id',
        'fiscal_year_id',
        'entry_number',
        'entry_date',
        'accounting_date',
        'reference',
        'description',
        'source_type',
        'source_id',
        'status',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'accounting_date' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    /**
     * Status labels.
     */
    public const STATUS_LABELS = [
        'draft' => 'Brouillon',
        'posted' => 'Comptabilisé',
        'reversed' => 'Extourné',
    ];

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Get total amount (sum of debits).
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->lines()->sum('debit');
    }

    /**
     * Journal.
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Fiscal year.
     */
    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    /**
     * Entry lines.
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class)->orderBy('line_number');
    }

    /**
     * Creator.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Posted by.
     */
    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Check if entry is balanced.
     */
    public function isBalanced(): bool
    {
        $totals = $this->lines()
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        return abs(($totals->total_debit ?? 0) - ($totals->total_credit ?? 0)) < 0.01;
    }

    /**
     * Get total debit.
     */
    public function getTotalDebitAttribute(): float
    {
        return $this->lines->sum('debit');
    }

    /**
     * Get total credit.
     */
    public function getTotalCreditAttribute(): float
    {
        return $this->lines->sum('credit');
    }

    /**
     * Post the entry.
     */
    public function post(?User $user = null): bool
    {
        if (!$this->isBalanced()) {
            return false;
        }

        $this->status = 'posted';
        $this->posted_at = now();
        $this->posted_by = $user?->id;

        return $this->save();
    }

    /**
     * Scope for posted entries.
     */
    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    /**
     * Scope for draft entries.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Generate the next entry number.
     */
    public static function generateEntryNumber(?string $journalId = null): string
    {
        $year = now()->format('Y');
        $prefix = 'JE';

        if ($journalId) {
            $journal = Journal::find($journalId);
            if ($journal) {
                $prefix = strtoupper(substr($journal->code ?? $journal->name, 0, 3));
            }
        }

        $lastEntry = self::where('entry_number', 'like', "{$prefix}-{$year}-%")
            ->orderBy('entry_number', 'desc')
            ->first();

        if ($lastEntry) {
            $lastNumber = (int) substr($lastEntry->entry_number, -5);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s-%05d', $prefix, $year, $nextNumber);
    }
}
