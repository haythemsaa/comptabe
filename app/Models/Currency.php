<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'decimal_places',
        'decimal_separator',
        'thousands_separator',
        'symbol_before',
        'is_active',
    ];

    protected $casts = [
        'decimal_places' => 'integer',
        'symbol_before' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function exchangeRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'target_currency', 'code');
    }

    public function companySettings(): HasMany
    {
        return $this->hasMany(CompanyCurrency::class, 'currency_code', 'code');
    }

    public function format(float $amount): string
    {
        $formatted = number_format(
            $amount,
            $this->decimal_places,
            $this->decimal_separator,
            $this->thousands_separator
        );

        return $this->symbol_before
            ? "{$this->symbol}{$formatted}"
            : "{$formatted} {$this->symbol}";
    }

    public function getLatestRate(string $baseCurrency = 'EUR'): ?float
    {
        return ExchangeRate::where('base_currency', $baseCurrency)
            ->where('target_currency', $this->code)
            ->orderByDesc('rate_date')
            ->value('rate');
    }

    public function getRateForDate(string $baseCurrency, $date): ?float
    {
        return ExchangeRate::where('base_currency', $baseCurrency)
            ->where('target_currency', $this->code)
            ->whereDate('rate_date', '<=', $date)
            ->orderByDesc('rate_date')
            ->value('rate');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getCommonCurrencies(): array
    {
        return [
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'decimal_places' => 2],
            ['code' => 'USD', 'name' => 'Dollar américain', 'symbol' => '$', 'decimal_places' => 2, 'symbol_before' => true],
            ['code' => 'GBP', 'name' => 'Livre sterling', 'symbol' => '£', 'decimal_places' => 2, 'symbol_before' => true],
            ['code' => 'CHF', 'name' => 'Franc suisse', 'symbol' => 'CHF', 'decimal_places' => 2],
            ['code' => 'CAD', 'name' => 'Dollar canadien', 'symbol' => 'CA$', 'decimal_places' => 2, 'symbol_before' => true],
            ['code' => 'AUD', 'name' => 'Dollar australien', 'symbol' => 'A$', 'decimal_places' => 2, 'symbol_before' => true],
            ['code' => 'JPY', 'name' => 'Yen japonais', 'symbol' => '¥', 'decimal_places' => 0, 'symbol_before' => true],
            ['code' => 'CNY', 'name' => 'Yuan chinois', 'symbol' => '¥', 'decimal_places' => 2, 'symbol_before' => true],
            ['code' => 'SEK', 'name' => 'Couronne suédoise', 'symbol' => 'kr', 'decimal_places' => 2],
            ['code' => 'NOK', 'name' => 'Couronne norvégienne', 'symbol' => 'kr', 'decimal_places' => 2],
            ['code' => 'DKK', 'name' => 'Couronne danoise', 'symbol' => 'kr', 'decimal_places' => 2],
            ['code' => 'PLN', 'name' => 'Zloty polonais', 'symbol' => 'zł', 'decimal_places' => 2],
            ['code' => 'CZK', 'name' => 'Couronne tchèque', 'symbol' => 'Kč', 'decimal_places' => 2],
            ['code' => 'MAD', 'name' => 'Dirham marocain', 'symbol' => 'DH', 'decimal_places' => 2],
            ['code' => 'TND', 'name' => 'Dinar tunisien', 'symbol' => 'DT', 'decimal_places' => 3],
        ];
    }
}
