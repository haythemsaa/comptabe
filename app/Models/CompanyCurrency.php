<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyCurrency extends Model
{
    protected $fillable = [
        'company_id',
        'currency_code',
        'is_default',
        'is_active',
        'fixed_rate',
        'rate_type',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'fixed_rate' => 'decimal:8',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function getRate($date = null): float
    {
        if ($this->rate_type === 'fixed' && $this->fixed_rate) {
            return (float) $this->fixed_rate;
        }

        $baseCurrency = $this->company->default_currency ?? 'EUR';

        if ($baseCurrency === $this->currency_code) {
            return 1.0;
        }

        return ExchangeRate::getRate($baseCurrency, $this->currency_code, $date) ?? 1.0;
    }

    public function getRateTypeLabelAttribute(): string
    {
        return match ($this->rate_type) {
            'live' => 'Temps réel',
            'daily' => 'Journalier',
            'fixed' => 'Fixe',
            default => $this->rate_type,
        };
    }

    public static function setDefault(Company $company, string $currencyCode): void
    {
        // Remove default flag from all
        static::where('company_id', $company->id)->update(['is_default' => false]);

        // Set new default
        static::updateOrCreate(
            ['company_id' => $company->id, 'currency_code' => $currencyCode],
            ['is_default' => true, 'is_active' => true]
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
