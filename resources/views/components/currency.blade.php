@props([
    'amount',
    'currency' => 'EUR',
    'colored' => false,
    'showSign' => false,
])

@php
    $formatted = number_format(abs($amount), 2, ',', ' ');
    $symbol = match($currency) {
        'EUR' => '€',
        'USD' => '$',
        'GBP' => '£',
        default => $currency,
    };

    $colorClass = '';
    if ($colored) {
        if ($amount > 0) {
            $colorClass = 'text-success-600';
        } elseif ($amount < 0) {
            $colorClass = 'text-danger-600';
        }
    }

    $sign = '';
    if ($showSign && $amount != 0) {
        $sign = $amount > 0 ? '+' : '-';
    } elseif ($amount < 0) {
        $sign = '-';
    }
@endphp

<span {{ $attributes->merge(['class' => 'font-mono ' . $colorClass]) }}>
    {{ $sign }}{{ $formatted }} {{ $symbol }}
</span>
