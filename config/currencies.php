<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Available Currencies
    |--------------------------------------------------------------------------
    |
    | List of currencies available for invoicing.
    | Users can choose any currency regardless of their company's default.
    |
    */

    'available' => [
        'EUR' => [
            'name' => 'Euro',
            'symbol' => '€',
            'decimal_places' => 2,
            'locale' => 'fr-BE',
        ],
        'TND' => [
            'name' => 'Dinar Tunisien',
            'symbol' => 'د.ت',
            'decimal_places' => 3,
            'locale' => 'fr-TN',
        ],
        'USD' => [
            'name' => 'US Dollar',
            'symbol' => '$',
            'decimal_places' => 2,
            'locale' => 'en-US',
        ],
        'GBP' => [
            'name' => 'British Pound',
            'symbol' => '£',
            'decimal_places' => 2,
            'locale' => 'en-GB',
        ],
        'CHF' => [
            'name' => 'Swiss Franc',
            'symbol' => 'CHF',
            'decimal_places' => 2,
            'locale' => 'fr-CH',
        ],
        'MAD' => [
            'name' => 'Dirham Marocain',
            'symbol' => 'MAD',
            'decimal_places' => 2,
            'locale' => 'fr-MA',
        ],
        'CAD' => [
            'name' => 'Canadian Dollar',
            'symbol' => 'CA$',
            'decimal_places' => 2,
            'locale' => 'en-CA',
        ],
        'DZD' => [
            'name' => 'Dinar Algérien',
            'symbol' => 'DA',
            'decimal_places' => 2,
            'locale' => 'fr-DZ',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Popular Currencies (for quick selection)
    |--------------------------------------------------------------------------
    */

    'popular' => ['EUR', 'USD', 'TND', 'GBP'],

    /*
    |--------------------------------------------------------------------------
    | Exchange Rate API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure external API for real-time exchange rates
    | Example: https://exchangerate-api.com or https://fixer.io
    |
    */

    'exchange_api' => [
        'enabled' => env('EXCHANGE_RATE_API_ENABLED', false),
        'provider' => env('EXCHANGE_RATE_API_PROVIDER', 'exchangerate-api'),
        'api_key' => env('EXCHANGE_RATE_API_KEY', ''),
        'base_url' => env('EXCHANGE_RATE_API_URL', 'https://api.exchangerate-api.com/v4/latest/'),
    ],
];
