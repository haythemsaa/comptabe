<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'description',
        'depreciation_method',
        'default_useful_life',
        'degressive_rate',
        'accounting_asset_account',
        'accounting_depreciation_account',
        'accounting_expense_account',
        'is_active',
    ];

    protected $casts = [
        'default_useful_life' => 'decimal:2',
        'degressive_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'category_id');
    }

    public function getDepreciationMethodLabelAttribute(): string
    {
        return match ($this->depreciation_method) {
            'linear' => 'Linéaire',
            'degressive' => 'Dégressif',
            'units_of_production' => 'Unités de production',
            default => $this->depreciation_method,
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
