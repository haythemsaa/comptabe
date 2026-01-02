<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'journal_entry_id',
        'line_number',
        'account_id',
        'partner_id',
        'description',
        'debit',
        'credit',
        'vat_code',
        'vat_amount',
        'vat_base',
        'analytic_account_id',
        'reconciliation_id',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'vat_base' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    /**
     * Journal entry.
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Account.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    /**
     * Partner.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Get the amount (debit - credit).
     */
    public function getAmountAttribute(): float
    {
        return $this->debit - $this->credit;
    }

    /**
     * Check if this is a debit line.
     */
    public function isDebit(): bool
    {
        return $this->debit > 0;
    }

    /**
     * Check if this is a credit line.
     */
    public function isCredit(): bool
    {
        return $this->credit > 0;
    }
}
