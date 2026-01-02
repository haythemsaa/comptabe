<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment provider that will be used
    | when no specific provider is requested.
    |
    | Supported: "mollie", "stripe"
    |
    */

    'default_provider' => env('PAYMENT_PROVIDER', 'mollie'),

    /*
    |--------------------------------------------------------------------------
    | Payment Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure each payment provider's credentials and settings.
    |
    */

    'providers' => [

        'mollie' => [
            'api_key' => env('MOLLIE_API_KEY'),
            'enabled' => env('MOLLIE_ENABLED', true),
            'webhook_secret' => env('MOLLIE_WEBHOOK_SECRET'),
            'test_mode' => env('MOLLIE_TEST_MODE', false),
        ],

        'stripe' => [
            'api_key' => env('STRIPE_SECRET_KEY'),
            'public_key' => env('STRIPE_PUBLIC_KEY'),
            'enabled' => env('STRIPE_ENABLED', true),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'test_mode' => env('STRIPE_TEST_MODE', false),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    |
    | Define your subscription plans here. These will be used to create
    | payments and subscriptions with providers.
    |
    */

    'plans' => [
        'free' => [
            'name' => 'Gratuit',
            'price' => 0,
            'currency' => 'EUR',
            'interval' => 'monthly',
            'features' => [
                'max_invoices' => 10,
                'max_users' => 1,
                'peppol_quota' => 10,
            ],
        ],
        'starter' => [
            'name' => 'Starter',
            'price' => 29,
            'currency' => 'EUR',
            'interval' => 'monthly',
            'features' => [
                'max_invoices' => 100,
                'max_users' => 3,
                'peppol_quota' => 50,
            ],
            // Provider-specific plan IDs
            'mollie_plan_id' => env('MOLLIE_PLAN_STARTER'),
            'stripe_plan_id' => env('STRIPE_PLAN_STARTER'),
        ],
        'professional' => [
            'name' => 'Professional',
            'price' => 79,
            'currency' => 'EUR',
            'interval' => 'monthly',
            'features' => [
                'max_invoices' => 500,
                'max_users' => 10,
                'peppol_quota' => 200,
            ],
            'mollie_plan_id' => env('MOLLIE_PLAN_PRO'),
            'stripe_plan_id' => env('STRIPE_PLAN_PRO'),
        ],
        'business' => [
            'name' => 'Business',
            'price' => 149,
            'currency' => 'EUR',
            'interval' => 'monthly',
            'features' => [
                'max_invoices' => -1, // Unlimited
                'max_users' => -1,
                'peppol_quota' => 500,
            ],
            'mollie_plan_id' => env('MOLLIE_PLAN_BUSINESS'),
            'stripe_plan_id' => env('STRIPE_PLAN_BUSINESS'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment URLs
    |--------------------------------------------------------------------------
    |
    | URLs for payment success, cancel, and webhook callbacks.
    |
    */

    'urls' => [
        'success' => env('APP_URL') . '/subscription/payment/success',
        'cancel' => env('APP_URL') . '/subscription/payment/cancel',
        'webhook_mollie' => env('APP_URL') . '/webhooks/mollie',
        'webhook_stripe' => env('APP_URL') . '/webhooks/stripe',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    */

    'currency' => env('PAYMENT_CURRENCY', 'EUR'),
    'locale' => env('PAYMENT_LOCALE', 'fr_BE'),

    // VAT settings for Belgium
    'vat' => [
        'enabled' => env('PAYMENT_VAT_ENABLED', true),
        'rate' => env('PAYMENT_VAT_RATE', 21), // 21% for Belgium
        'include_in_price' => true, // Prices include VAT
    ],

];
