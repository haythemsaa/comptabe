<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CurrencyConversion extends Model
{
    protected $fillable = [
        'company_id',
        'convertible_type',
        'convertible_id',
        'from_currency',
        'to_currency',
        'from_amount',
        'to_amount',
        'exchange_rate',
        'conversion_date',
        'rate_source',
    ];

    protected $casts = [
        'from_amount' => 'decimal:2',
        'to_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:8',
        'conversion_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function convertible(): MorphTo
    {
        return $this->morphTo();
    }

    public function getRateSourceLabelAttribute(): string
    {
        return match ($this->rate_source) {
            'ecb' => 'BCE',
            'manual' => 'Manuel',
            'api' => 'API',
            default => $this->rate_source,
        };
    }

    public static function recordConversion(
        Model $document,
        string $fromCurrency,
        string $toCurrency,
        float $fromAmount,
        float $exchangeRate,
        string $source = 'ecb'
    ): self {
        return static::create([
            'company_id' => $document->company_id,
            'convertible_type' => get_class($document),
            'convertible_id' => $document->id,
            'from_currency' => $fromCurrency,
            'to_currency' => $toCurrency,
            'from_amount' => $fromAmount,
            'to_amount' => $fromAmount * $exchangeRate,
            'exchange_rate' => $exchangeRate,
            'conversion_date' => now(),
            'rate_source' => $source,
        ]);
    }
}
