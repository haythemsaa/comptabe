<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Peppol Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'intégration Peppol (facturation électronique B2B)
    | obligatoire en Belgique à partir du 1er janvier 2026.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Peppol Provider
    |--------------------------------------------------------------------------
    |
    | The Peppol Access Point provider to use. Available providers:
    | - recommand: Recommand.eu (Open Source)
    | - digiteal: Digiteal (Belgium-based)
    | - b2brouter: B2Brouter (Enterprise)
    | - custom: Custom provider configuration
    |
    */

    'provider' => env('PEPPOL_PROVIDER', 'recommand'),

    /*
    |--------------------------------------------------------------------------
    | Peppol Providers Configuration
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'recommand' => [
            'name' => 'Recommand.eu',
            'description' => 'Open-source Peppol Access Point with developer-friendly API',
            'api_url' => env('PEPPOL_RECOMMAND_API_URL', 'https://api.recommand.eu/v1'),
            'api_key' => env('PEPPOL_RECOMMAND_API_KEY'),
            'api_secret' => env('PEPPOL_RECOMMAND_API_SECRET'),
            'playground_url' => 'https://playground.recommand.eu',
            'docs_url' => 'https://docs.recommand.eu',
            'features' => [
                'send_invoice' => true,
                'receive_invoice' => true,
                'participant_lookup' => true,
                'status_tracking' => true,
                'webhooks' => true,
            ],
        ],

        'digiteal' => [
            'name' => 'Digiteal',
            'description' => 'Belgian Peppol Access Point - ISO 27001 certified',
            'api_url' => env('PEPPOL_DIGITEAL_API_URL', 'https://api.digiteal.eu/peppol'),
            'api_key' => env('PEPPOL_DIGITEAL_API_KEY'),
            'client_id' => env('PEPPOL_DIGITEAL_CLIENT_ID'),
            'client_secret' => env('PEPPOL_DIGITEAL_CLIENT_SECRET'),
            'docs_url' => 'https://doc.digiteal.eu',
            'features' => [
                'send_invoice' => true,
                'receive_invoice' => true,
                'participant_lookup' => true,
                'status_tracking' => true,
                'webhooks' => true,
                'format_conversion' => true,
            ],
        ],

        'b2brouter' => [
            'name' => 'B2Brouter',
            'description' => 'Enterprise Peppol Access Point with advanced features',
            'api_url' => env('PEPPOL_B2BROUTER_API_URL', 'https://api.b2brouter.net'),
            'api_key' => env('PEPPOL_B2BROUTER_API_KEY'),
            'features' => [
                'send_invoice' => true,
                'receive_invoice' => true,
                'participant_lookup' => true,
                'status_tracking' => true,
                'webhooks' => true,
                'format_conversion' => true,
                'batch_processing' => true,
            ],
        ],

        'custom' => [
            'name' => 'Custom Provider',
            'description' => 'Custom Peppol Access Point configuration',
            'api_url' => env('PEPPOL_CUSTOM_API_URL'),
            'api_key' => env('PEPPOL_CUSTOM_API_KEY'),
        ],
    ],

    // Access Point Provider (Legacy - for backwards compatibility)
    'access_point' => [
        'url' => env('PEPPOL_ACCESS_POINT_URL'),
        'api_key' => env('PEPPOL_API_KEY'),
        'timeout' => 30,
    ],

    // Participant ID de l'entreprise (format: 0208:BE0123456789)
    'participant_id' => env('PEPPOL_PARTICIPANT_ID'),

    // Schémas d'identification Peppol
    'schemes' => [
        'be_enterprise' => '0208', // Numéro d'entreprise belge
        'be_vat' => '9925',        // Numéro TVA belge
        'gln' => '0088',           // Global Location Number
    ],

    // Default scheme for Belgium
    'scheme' => env('PEPPOL_SCHEME', '0208'),

    // Document types supportés
    'document_types' => [
        'invoice' => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2::Invoice##urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0::2.1',
        'credit_note' => 'urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2::CreditNote##urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0::2.1',
    ],

    // Process IDs
    'process_ids' => [
        'billing' => 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0',
    ],

    // Customization ID (Peppol BIS 3.0)
    'customization_id' => 'urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0',

    // Profile ID
    'profile_id' => 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0',

    // Document Format
    'document_format' => env('PEPPOL_DOCUMENT_FORMAT', 'ubl'),

    // Webhook Configuration
    'webhook' => [
        'enabled' => env('PEPPOL_WEBHOOK_ENABLED', false),
        'secret' => env('PEPPOL_WEBHOOK_SECRET'),
        'url' => env('APP_URL') . '/webhooks/peppol',
    ],

    // Auto-Process Incoming Invoices
    'auto_process_incoming' => env('PEPPOL_AUTO_PROCESS_INCOMING', false),

    // Testing Mode
    'testing' => env('PEPPOL_TESTING', false),

    // Timeout
    'timeout' => env('PEPPOL_TIMEOUT', 30),

    // Codes TVA Belgique
    'vat_categories' => [
        'S' => 'Standard rate',
        'Z' => 'Zero rated',
        'E' => 'Exempt',
        'AE' => 'Reverse charge',
        'K' => 'Intra-community',
        'G' => 'Export',
        'O' => 'Outside scope',
        'L' => 'Canary Islands',
        'M' => 'Ceuta and Melilla',
    ],

    // Taux TVA Belgique
    'vat_rates' => [
        'standard' => 21.00,
        'reduced_1' => 12.00,
        'reduced_2' => 6.00,
        'zero' => 0.00,
    ],

];
