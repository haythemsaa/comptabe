<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported Countries Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for each supported country including tax rates, payroll
    | settings, formats, and regulatory requirements.
    |
    */

    'BE' => [
        'name' => 'Belgique',
        'name_en' => 'Belgium',
        'currency' => 'EUR',
        'currency_symbol' => '€',
        'decimal_places' => 2,
        'locale' => 'fr_BE',

        // VAT Configuration
        'vat' => [
            'rates' => [21, 12, 6, 0],
            'default_rate' => 21,
            'number_format' => 'BE0999999999', // BE + 10 digits
            'number_validation' => '/^BE0\d{9}$/',
        ],

        // Payroll Configuration
        'payroll' => [
            'enabled' => true,
            'social_security' => [
                'employee_rate' => 13.07,
                'employer_rate' => 25.00,
                'organization' => 'ONSS',
            ],
            'income_tax' => [
                'type' => 'progressive',
                'name' => 'Précompte professionnel',
            ],
            'min_wage' => 1954.99,
            'standard_working_hours' => 38,
            'annual_leave_days' => 20,
            'features' => [
                'paritair_committees' => true,
                'meal_vouchers' => true,
                'eco_vouchers' => true,
                'company_car' => true,
                '13th_month' => true,
            ],
        ],

        // Number and Date Formats
        'formats' => [
            'date' => 'd/m/Y',
            'datetime' => 'd/m/Y H:i',
            'number' => [
                'decimal' => ',',
                'thousands' => '.',
            ],
        ],

        // Banking
        'banking' => [
            'account_format' => 'IBAN',
            'account_validation' => 'iban',
            'account_example' => 'BE68 5390 0754 7034',
            'bic_required' => true,
            'psd2_enabled' => true,
        ],

        // Regulatory Requirements
        'regulations' => [
            'peppol' => [
                'enabled' => true,
                'mandatory_b2g' => true,
            ],
            'e_reporting' => [
                'enabled' => false,
                'mandatory_from' => '2028-01-01',
            ],
            'intervat' => [
                'enabled' => true,
                'format' => 'XML',
            ],
        ],

        // Company Types
        'company_types' => [
            'SPRL' => 'Société Privée à Responsabilité Limitée',
            'SA' => 'Société Anonyme',
            'SNC' => 'Société en Nom Collectif',
            'SCS' => 'Société en Commandite Simple',
            'SCRL' => 'Société Coopérative à Responsabilité Limitée',
            'ASBL' => 'Association Sans But Lucratif',
        ],
    ],

    'TN' => [
        'name' => 'Tunisie',
        'name_en' => 'Tunisia',
        'currency' => 'TND',
        'currency_symbol' => 'د.ت',
        'decimal_places' => 3, // Important: TND uses 3 decimal places (millimes)
        'locale' => 'fr_TN',

        // VAT Configuration
        'vat' => [
            'rates' => [19, 13, 7, 0],
            'default_rate' => 19,
            'number_format' => '1234567/A/M/000',
            'number_validation' => '/^\d{7}\/[A-Z]\/[A-Z]\/\d{3}$/',
        ],

        // Payroll Configuration
        'payroll' => [
            'enabled' => true,
            'social_security' => [
                'employee_rate' => 9.18, // CNSS
                'employer_rate' => 16.57, // CNSS
                'organization' => 'CNSS',
            ],
            'income_tax' => [
                'type' => 'progressive',
                'name' => 'IRPP',
                'brackets' => [
                    ['min' => 0, 'max' => 5000, 'rate' => 0],
                    ['min' => 5000, 'max' => 20000, 'rate' => 26],
                    ['min' => 20000, 'max' => 30000, 'rate' => 28],
                    ['min' => 30000, 'max' => 50000, 'rate' => 32],
                    ['min' => 50000, 'max' => null, 'rate' => 35],
                ],
            ],
            'min_wage' => 460.00, // SMIG 2024
            'standard_working_hours' => 48,
            'annual_leave_days' => 12, // 1 day per month worked
            'features' => [
                'paritair_committees' => false,
                'meal_vouchers' => false,
                'eco_vouchers' => false,
                'company_car' => false,
                '13th_month' => false,
            ],
        ],

        // Number and Date Formats
        'formats' => [
            'date' => 'd/m/Y',
            'datetime' => 'd/m/Y H:i',
            'number' => [
                'decimal' => ',',
                'thousands' => ' ',
            ],
        ],

        // Banking
        'banking' => [
            'account_format' => 'RIB',
            'account_validation' => 'regex:/^\d{20}$/',
            'account_example' => '12345678901234567890',
            'bic_required' => false,
            'psd2_enabled' => false,
        ],

        // Regulatory Requirements
        'regulations' => [
            'peppol' => [
                'enabled' => false,
                'mandatory_b2g' => false,
            ],
            'e_reporting' => [
                'enabled' => false,
            ],
            'cnudst' => [
                'enabled' => true, // Centre National Universitaire de Documentation Scientifique et Technique
                'vat_declaration' => 'quarterly',
            ],
        ],

        // Company Types
        'company_types' => [
            'SARL' => 'Société À Responsabilité Limitée',
            'SUARL' => 'Société Unipersonnelle À Responsabilité Limitée',
            'SA' => 'Société Anonyme',
            'SNC' => 'Société en Nom Collectif',
            'SCS' => 'Société en Commandite Simple',
            'ASSOCIATION' => 'Association',
        ],

        // Tunisia-specific fields
        'required_fields' => [
            'employee' => ['cin', 'cnss_number'],
            'company' => ['matricule_fiscal', 'cnss_employer_number'],
        ],
    ],

    'FR' => [
        'name' => 'France',
        'name_en' => 'France',
        'currency' => 'EUR',
        'currency_symbol' => '€',
        'decimal_places' => 2,
        'locale' => 'fr_FR',

        // VAT Configuration
        'vat' => [
            'rates' => [20, 10, 5.5, 2.1, 0],
            'default_rate' => 20,
            'number_format' => 'FR12345678901',
            'number_validation' => '/^FR[A-Z0-9]{2}\d{9}$/',
        ],

        // Payroll Configuration
        'payroll' => [
            'enabled' => true,
            'social_security' => [
                'employee_rate' => 22.00, // Total approximatif
                'employer_rate' => 42.00, // Total approximatif
                'organization' => 'URSSAF',
                'components' => [
                    // Cotisations employé
                    'securite_sociale' => ['employee' => 0.40, 'employer' => 7.00, 'ceiling' => 'PSS'], // Maladie
                    'vieillesse_plafonnee' => ['employee' => 6.90, 'employer' => 8.55, 'ceiling' => 'PSS'],
                    'vieillesse_deplafonnee' => ['employee' => 0.40, 'employer' => 1.90, 'ceiling' => null],
                    'allocations_familiales' => ['employee' => 0, 'employer' => 3.45, 'ceiling' => null],
                    'chomage' => ['employee' => 0, 'employer' => 4.05, 'ceiling' => '4*PSS'],
                    'agff_t1' => ['employee' => 0.80, 'employer' => 1.20, 'ceiling' => 'PSS'],
                    'agff_t2' => ['employee' => 0.90, 'employer' => 1.30, 'ceiling' => '4*PSS'],
                    'csg_crds' => ['employee' => 9.70, 'employer' => 0, 'ceiling' => null], // CSG/CRDS
                    'retraite_complementaire' => ['employee' => 3.15, 'employer' => 4.72, 'ceiling' => 'PSS'],
                    'formation' => ['employee' => 0, 'employer' => 0.55, 'ceiling' => null],
                    'transport' => ['employee' => 0, 'employer' => 1.60, 'ceiling' => null], // Variable selon région
                ],
                'pss_monthly' => 3666, // Plafond Sécurité Sociale mensuel 2024
            ],
            'income_tax' => [
                'type' => 'progressive',
                'name' => 'Impôt sur le revenu',
                'prelevement_source' => true, // Prélèvement à la source
                'brackets' => [
                    // Barème 2024 pour 1 part fiscale
                    ['min' => 0, 'max' => 11294, 'rate' => 0],
                    ['min' => 11294, 'max' => 28797, 'rate' => 11],
                    ['min' => 28797, 'max' => 82341, 'rate' => 30],
                    ['min' => 82341, 'max' => 177106, 'rate' => 41],
                    ['min' => 177106, 'max' => null, 'rate' => 45],
                ],
                'abattement' => 10, // 10% abattement forfaitaire
            ],
            'min_wage' => 1766.92, // SMIC mensuel brut 2024 (151.67h * 11.65€)
            'standard_working_hours' => 35,
            'annual_leave_days' => 25,
            'features' => [
                'paritair_committees' => false,
                'convention_collective' => true,
                'meal_vouchers' => true,
                'tickets_restaurant' => true,
                'eco_vouchers' => false,
                'company_car' => true,
                '13th_month' => true,
                'mutuelle_obligatoire' => true,
                'prevoyance' => true,
            ],
        ],

        // Number and Date Formats
        'formats' => [
            'date' => 'd/m/Y',
            'datetime' => 'd/m/Y H:i',
            'number' => [
                'decimal' => ',',
                'thousands' => ' ',
            ],
        ],

        // Banking
        'banking' => [
            'account_format' => 'IBAN',
            'account_validation' => 'iban',
            'account_example' => 'FR14 2004 1010 0505 0001 3M02 606',
            'bic_required' => true,
            'psd2_enabled' => true,
        ],

        // Regulatory Requirements
        'regulations' => [
            'chorus_pro' => [
                'enabled' => true,
                'mandatory_b2g' => true,
            ],
            'fec' => [
                'enabled' => true, // Fichier des Écritures Comptables
                'format' => 'TXT',
            ],
        ],

        // Company Types
        'company_types' => [
            'SARL' => 'Société À Responsabilité Limitée',
            'EURL' => 'Entreprise Unipersonnelle À Responsabilité Limitée',
            'SA' => 'Société Anonyme',
            'SAS' => 'Société par Actions Simplifiée',
            'SASU' => 'Société par Actions Simplifiée Unipersonnelle',
            'SNC' => 'Société en Nom Collectif',
            'ASSOCIATION' => 'Association Loi 1901',
        ],
    ],
];
