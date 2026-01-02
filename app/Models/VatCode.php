<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VatCode extends Model
{
    use HasFactory, HasUuid, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'rate',
        'category',
        'grid_base',
        'grid_vat',
        'account_vat_due_id',
        'account_vat_deductible_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * VAT category labels.
     */
    public const CATEGORY_LABELS = [
        'S' => 'Taux standard',
        'Z' => 'Taux zéro',
        'E' => 'Exonéré',
        'AE' => 'Autoliquidation',
        'K' => 'Intracommunautaire',
        'G' => 'Export',
        'O' => 'Hors champ',
    ];

    /**
     * Get category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORY_LABELS[$this->category] ?? $this->category;
    }

    /**
     * Get display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->code} - {$this->name} ({$this->rate}%)";
    }

    /**
     * Account for VAT due.
     */
    public function accountVatDue(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_vat_due_id');
    }

    /**
     * Account for VAT deductible.
     */
    public function accountVatDeductible(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_vat_deductible_id');
    }

    /**
     * Scope for active codes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
