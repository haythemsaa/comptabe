<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    use HasFactory, HasUuid, BelongsToTenant;

    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'company_id',
        'account_number',
        'name',
        'type',
        'parent_id',
        'vat_code',
        'is_active',
        'is_system',
        'allow_direct_posting',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_system' => 'boolean',
            'allow_direct_posting' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Account type colors.
     */
    public const TYPE_COLORS = [
        'asset' => 'primary',
        'liability' => 'warning',
        'equity' => 'success',
        'revenue' => 'success',
        'expense' => 'danger',
    ];

    /**
     * Account type labels.
     */
    public const TYPE_LABELS = [
        'asset' => 'Actif',
        'liability' => 'Passif',
        'equity' => 'Capitaux propres',
        'revenue' => 'Produits',
        'expense' => 'Charges',
    ];

    /**
     * Get display name with number.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->account_number} - {$this->name}";
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    /**
     * Get type color.
     */
    public function getTypeColorAttribute(): string
    {
        return self::TYPE_COLORS[$this->type] ?? 'secondary';
    }

    /**
     * Check if this is a group account (has children or doesn't allow direct posting).
     */
    public function getIsGroupAttribute(): bool
    {
        return !$this->allow_direct_posting || strlen($this->account_number) <= 2;
    }

    /**
     * Alias for account_number.
     */
    public function getCodeAttribute(): string
    {
        return $this->account_number;
    }

    /**
     * Parent account.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    /**
     * Child accounts.
     */
    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    /**
     * Journal entry lines.
     */
    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    /**
     * Get balance for this account.
     */
    public function getBalance(?string $startDate = null, ?string $endDate = null): float
    {
        $query = $this->journalEntryLines()
            ->whereHas('journalEntry', function ($q) {
                $q->where('status', 'posted');
            });

        if ($startDate) {
            $query->whereHas('journalEntry', fn($q) => $q->where('entry_date', '>=', $startDate));
        }

        if ($endDate) {
            $query->whereHas('journalEntry', fn($q) => $q->where('entry_date', '<=', $endDate));
        }

        $totals = $query->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')->first();

        // Debit balance for assets/expenses, credit balance for liabilities/equity/revenue
        $balance = ($totals->total_debit ?? 0) - ($totals->total_credit ?? 0);

        if (in_array($this->type, ['liability', 'equity', 'revenue'])) {
            $balance = -$balance;
        }

        return $balance;
    }

    /**
     * Scope for active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for postable accounts.
     */
    public function scopePostable($query)
    {
        return $query->where('allow_direct_posting', true);
    }

    /**
     * Scope by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for root accounts (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
