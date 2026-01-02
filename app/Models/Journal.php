<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journal extends Model
{
    use HasFactory, HasUuid, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'default_account_id',
        'bank_account_id',
        'next_number',
        'number_prefix',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'next_number' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Journal type labels.
     */
    public const TYPE_LABELS = [
        'purchases' => 'Achats',
        'sales' => 'Ventes',
        'bank' => 'Banque',
        'cash' => 'Caisse',
        'misc' => 'Opérations diverses',
        'opening' => 'Ouverture',
        'closing' => 'Clôture',
    ];

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    /**
     * Default account.
     */
    public function defaultAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_account_id');
    }

    /**
     * Bank account.
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Journal entries.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Generate next entry number.
     */
    public function generateEntryNumber(): string
    {
        $prefix = $this->number_prefix ?? $this->code;
        $number = $this->next_number;

        $this->increment('next_number');

        return $prefix . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Scope for active journals.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
