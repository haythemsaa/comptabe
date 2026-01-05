<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ExchangeRate extends Model
{
    protected $fillable = [
        'base_currency',
        'target_currency',
        'rate_date',
        'rate',
        'source',
    ];

    protected $casts = [
        'rate_date' => 'date',
        'rate' => 'decimal:8',
    ];

    public static function getRate(string $from, string $to, $date = null): ?float
    {
        if ($from === $to) return 1.0;

        $date = $date ? Carbon::parse($date) : now();

        // Try to find exact date
        $rate = static::where('base_currency', $from)
            ->where('target_currency', $to)
            ->whereDate('rate_date', $date)
            ->value('rate');

        if ($rate) return (float) $rate;

        // Try inverse
        $inverseRate = static::where('base_currency', $to)
            ->where('target_currency', $from)
            ->whereDate('rate_date', $date)
            ->value('rate');

        if ($inverseRate) return 1 / (float) $inverseRate;

        // Get latest available rate before date
        $rate = static::where('base_currency', $from)
            ->where('target_currency', $to)
            ->whereDate('rate_date', '<=', $date)
            ->orderByDesc('rate_date')
            ->value('rate');

        return $rate ? (float) $rate : null;
    }

    public static function convert(float $amount, string $from, string $to, $date = null): ?float
    {
        $rate = static::getRate($from, $to, $date);
        return $rate ? $amount * $rate : null;
    }

    public static function fetchFromEcb(): array
    {
        $imported = [];

        try {
            $response = Http::get('https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');

            if ($response->successful()) {
                $xml = simplexml_load_string($response->body());
                $date = (string) $xml->Cube->Cube['time'];

                foreach ($xml->Cube->Cube->Cube as $rate) {
                    $currency = (string) $rate['currency'];
                    $rateValue = (float) $rate['rate'];

                    static::updateOrCreate(
                        [
                            'base_currency' => 'EUR',
                            'target_currency' => $currency,
                            'rate_date' => $date,
                        ],
                        [
                            'rate' => $rateValue,
                            'source' => 'ecb',
                        ]
                    );

                    $imported[] = $currency;
                }
            }
        } catch (\Exception $e) {
            \Log::error('ECB exchange rate fetch failed: ' . $e->getMessage());
        }

        return $imported;
    }

    public static function setManualRate(string $from, string $to, float $rate, $date = null): self
    {
        return static::updateOrCreate(
            [
                'base_currency' => $from,
                'target_currency' => $to,
                'rate_date' => $date ?? now()->toDateString(),
            ],
            [
                'rate' => $rate,
                'source' => 'manual',
            ]
        );
    }

    public function scopeForCurrency($query, string $currency)
    {
        return $query->where('target_currency', $currency);
    }

    public function scopeFromEcb($query)
    {
        return $query->where('source', 'ecb');
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('rate_date');
    }
}
