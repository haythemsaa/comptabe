<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankStatement extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'bank_account_id',
        'statement_number',
        'statement_date',
        'period_start',
        'period_end',
        'opening_balance',
        'closing_balance',
        'total_debit',
        'total_credit',
        'source',
        'original_file_path',
        'original_filename',
        'is_processed',
        'processed_at',
        'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'statement_date' => 'date',
            'period_start' => 'date',
            'period_end' => 'date',
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'total_debit' => 'decimal:2',
            'total_credit' => 'decimal:2',
            'is_processed' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Bank account.
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    /**
     * Processed by user.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get transaction count.
     */
    public function getTransactionCountAttribute(): int
    {
        return $this->transactions()->count();
    }

    /**
     * Get unreconciled transaction count.
     */
    public function getUnreconciledCountAttribute(): int
    {
        return $this->transactions()
            ->where('reconciliation_status', 'pending')
            ->count();
    }
}
