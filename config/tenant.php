<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenant Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour le système multi-tenant basé sur les entreprises.
    | Chaque entreprise (Company) est un tenant isolé.
    |
    */

    // Modèle utilisé comme tenant
    'model' => \App\Models\Company::class,

    // Clé de session pour stocker le tenant actif
    'session_key' => 'current_tenant_id',

    // Colonne utilisée pour l'isolation des données
    'foreign_key' => 'company_id',

    // Modèles qui doivent être filtrés par tenant
    'tenant_models' => [
        \App\Models\Partner::class,
        \App\Models\Invoice::class,
        \App\Models\InvoiceLine::class,
        \App\Models\JournalEntry::class,
        \App\Models\JournalEntryLine::class,
        \App\Models\BankAccount::class,
        \App\Models\BankStatement::class,
        \App\Models\BankTransaction::class,
        \App\Models\VatDeclaration::class,
        \App\Models\ChartOfAccount::class,
        \App\Models\Journal::class,
        \App\Models\FiscalYear::class,
        \App\Models\VatCode::class,
    ],

    // Routes exclues du middleware tenant
    'excluded_routes' => [
        'login',
        'logout',
        'register',
        'password.*',
        'verification.*',
        'tenant.switch',
        'tenant.select',
    ],

];
