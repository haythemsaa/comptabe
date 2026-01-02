<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stratégie de Scaling Automatique Peppol
    |--------------------------------------------------------------------------
    |
    | Ce fichier définit les plans providers et les seuils de scaling.
    | Le système calcule automatiquement le meilleur plan selon le volume.
    |
    */

    'auto_scaling_enabled' => env('PEPPOL_AUTO_SCALING', true),

    /*
    |--------------------------------------------------------------------------
    | Plans Providers (Coûts réels)
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'recommand' => [
            'name' => 'Recommand.eu',
            'url' => 'https://api.recommand.eu/v1',
            'plans' => [
                'free' => [
                    'name' => 'Free',
                    'monthly_cost' => 0,
                    'included_documents' => 25,
                    'overage_cost' => 0.30, // €0.30 per additional doc
                    'max_documents' => 100, // limite soft
                ],
                'starter' => [
                    'name' => 'Starter',
                    'monthly_cost' => 29,
                    'included_documents' => 200,
                    'overage_cost' => 0.20,
                    'max_documents' => 500,
                ],
                'professional' => [
                    'name' => 'Professional',
                    'monthly_cost' => 99,
                    'included_documents' => 1000,
                    'overage_cost' => 0.10,
                    'max_documents' => 5000,
                ],
                'enterprise' => [
                    'name' => 'Enterprise',
                    'monthly_cost' => 299, // estimation
                    'included_documents' => 10000,
                    'overage_cost' => 0.05,
                    'max_documents' => null, // illimité
                ],
            ],
        ],

        'digiteal' => [
            'name' => 'Digiteal',
            'url' => 'https://api.digiteal.eu/peppol',
            'plans' => [
                'starter' => [
                    'name' => 'Starter',
                    'monthly_cost' => 25,
                    'included_documents' => 100,
                    'overage_cost' => 0.25,
                    'max_documents' => 500,
                ],
                'business' => [
                    'name' => 'Business',
                    'monthly_cost' => 89,
                    'included_documents' => 1000,
                    'overage_cost' => 0.12,
                    'max_documents' => 5000,
                ],
                'enterprise' => [
                    'name' => 'Enterprise',
                    'monthly_cost' => 249,
                    'included_documents' => 10000,
                    'overage_cost' => 0.08,
                    'max_documents' => null,
                ],
            ],
        ],

        'peppol_box' => [
            'name' => 'Peppol Box',
            'url' => 'https://api.peppol-box.be',
            'plans' => [
                'basic' => [
                    'name' => 'Basic',
                    'monthly_cost' => 5,
                    'included_documents' => 50,
                    'overage_cost' => 0.15,
                    'max_documents' => 200,
                ],
                'standard' => [
                    'name' => 'Standard',
                    'monthly_cost' => 49,
                    'included_documents' => 500,
                    'overage_cost' => 0.10,
                    'max_documents' => 2000,
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Seuils de Recommandation Automatique
    |--------------------------------------------------------------------------
    | Le système recommande un upgrade quand le volume dépasse ces seuils
    */
    'scaling_thresholds' => [
        'recommand' => [
            // Si volume > 20 docs/mois ET plan = free => recommander starter
            20 => 'starter',
            // Si volume > 150 docs/mois ET plan = starter => recommander professional
            150 => 'professional',
            // Si volume > 800 docs/mois ET plan = professional => recommander enterprise
            800 => 'enterprise',
        ],
        'digiteal' => [
            80 => 'business',
            800 => 'enterprise',
        ],
        'peppol_box' => [
            40 => 'standard',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Plans Clients (Ce que VOUS facturez à vos clients)
    |--------------------------------------------------------------------------
    */
    'tenant_plans' => [
        'free' => [
            'name' => 'Gratuit',
            'monthly_quota' => 10,
            'price' => 0,
            'features' => [
                '10 factures Peppol/mois',
                'Support email',
            ],
        ],
        'starter' => [
            'name' => 'Starter',
            'monthly_quota' => 50,
            'price' => 15,
            'features' => [
                '50 factures Peppol/mois',
                'Support email',
                'Historique 6 mois',
            ],
        ],
        'pro' => [
            'name' => 'Pro',
            'monthly_quota' => 150,
            'price' => 49,
            'features' => [
                '150 factures Peppol/mois',
                'Support prioritaire',
                'Historique illimité',
                'API access',
            ],
        ],
        'business' => [
            'name' => 'Business',
            'monthly_quota' => 500,
            'price' => 149,
            'features' => [
                '500 factures Peppol/mois',
                'Support dédié',
                'Historique illimité',
                'API access',
                'SLA 99.9%',
            ],
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'monthly_quota' => null, // illimité
            'price' => 299,
            'features' => [
                'Factures illimitées',
                'Account manager dédié',
                'Support 24/7',
                'API access',
                'SLA 99.99%',
                'Custom integration',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Calcul Automatique du Meilleur Plan Provider
    |--------------------------------------------------------------------------
    | Basé sur le volume total de tous vos tenants
    */
    'cost_optimizer' => [
        'enabled' => true,
        'check_frequency' => 'monthly', // daily, weekly, monthly
        'notification_email' => env('PEPPOL_ADMIN_EMAIL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Démarrage Recommandé
    |--------------------------------------------------------------------------
    */
    'recommended_start' => [
        'provider' => 'recommand',
        'plan' => 'free',
        'reason' => 'Gratuit jusqu\'à 25 documents/mois. Idéal pour démarrer.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Stratégie de Croissance
    |--------------------------------------------------------------------------
    */
    'growth_strategy' => [
        // 0-50 factures/mois total
        [
            'volume_min' => 0,
            'volume_max' => 50,
            'recommended_provider' => 'recommand',
            'recommended_plan' => 'free',
            'estimated_cost' => 0, // 25 gratuits + 25*€0.30 = €7.50
            'note' => 'Plan FREE suffisant',
        ],

        // 51-200 factures/mois total
        [
            'volume_min' => 51,
            'volume_max' => 200,
            'recommended_provider' => 'recommand',
            'recommended_plan' => 'starter',
            'estimated_cost' => 29,
            'note' => 'Passer au Starter (€29/mois)',
        ],

        // 201-1000 factures/mois total
        [
            'volume_min' => 201,
            'volume_max' => 1000,
            'recommended_provider' => 'recommand',
            'recommended_plan' => 'professional',
            'estimated_cost' => 99,
            'note' => 'Passer au Professional (€99/mois)',
        ],

        // 1001+ factures/mois total
        [
            'volume_min' => 1001,
            'volume_max' => null,
            'recommended_provider' => 'recommand',
            'recommended_plan' => 'enterprise',
            'estimated_cost' => 299,
            'note' => 'Passer à Enterprise ou négocier tarif custom',
        ],
    ],
];
