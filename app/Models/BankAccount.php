<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, HasUuid, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'iban',
        'bic',
        'bank_name',
        'account_id',
        'journal_id',
        'coda_enabled',
        'coda_contract_number',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'coda_enabled' => 'boolean',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get formatted IBAN.
     */
    public function getFormattedIbanAttribute(): string
    {
        $iban = strtoupper(preg_replace('/\s+/', '', $this->iban));
        return implode(' ', str_split($iban, 4));
    }

    /**
     * Account relationship.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    /**
     * Journal relationship.
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Bank statements.
     */
    public function statements(): HasMany
    {
        return $this->hasMany(BankStatement::class);
    }

    /**
     * Bank transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    /**
     * Get current balance.
     */
    public function getCurrentBalanceAttribute(): ?float
    {
        $lastStatement = $this->statements()
            ->orderBy('statement_date', 'desc')
            ->first();

        return $lastStatement?->closing_balance;
    }

    /**
     * Scope for active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
