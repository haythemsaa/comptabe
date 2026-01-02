<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | Choose your AI provider:
    | - 'ollama': FREE local LLM (requires Ollama installed)
    | - 'claude': Anthropic Claude API (paid, requires API key)
    |
    | Ollama is recommended for development and cost-free production use.
    |
    */

    'default_provider' => env('AI_PROVIDER', 'ollama'),

    /*
    |--------------------------------------------------------------------------
    | Ollama Configuration (FREE)
    |--------------------------------------------------------------------------
    |
    | Ollama runs LLMs locally on your machine - completely free!
    |
    | Installation:
    | 1. Download from https://ollama.com/download
    | 2. Install and start Ollama
    | 3. Pull a model: ollama pull llama3.1
    |
    | Recommended models:
    | - llama3.1 (8B) - Best balance of speed/quality
    | - mistral (7B) - Fast and efficient
    | - phi3 (3.8B) - Very fast for simple tasks
    |
    */

    'ollama' => [
        'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
        'model' => env('OLLAMA_MODEL', 'llama3.1'),
        'max_tokens' => env('OLLAMA_MAX_TOKENS', 4096),
        'temperature' => env('OLLAMA_TEMPERATURE', 0.7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Claude AI Configuration (PAID)
    |--------------------------------------------------------------------------
    |
    | Configuration for Claude API integration
    | Cost: ~$3 input / $15 output per million tokens
    |
    */

    'claude' => [
        'api_key' => env('CLAUDE_API_KEY'),
        'model' => env('CLAUDE_MODEL', 'claude-3-5-sonnet-20241022'),
        'base_url' => env('CLAUDE_API_URL', 'https://api.anthropic.com/v1'),
        'api_version' => '2023-06-01',
        'max_tokens' => env('CLAUDE_MAX_TOKENS', 4096),
        'temperature' => env('CLAUDE_TEMPERATURE', 0.7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Chat Configuration
    |--------------------------------------------------------------------------
    */

    'chat' => [
        // Maximum conversations to keep per user
        'max_conversations_per_user' => 50,

        // Maximum messages per conversation
        'max_messages_per_conversation' => 500,

        // Auto-archive conversations older than X days
        'auto_archive_days' => 30,

        // Number of recent messages to send to Claude for context
        'context_window_messages' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Tracking
    |--------------------------------------------------------------------------
    |
    | Pricing for Claude Sonnet (as of Dec 2025)
    |
    */

    'costs' => [
        'input_per_million' => 3.00,  // $3 per million input tokens
        'output_per_million' => 15.00, // $15 per million output tokens
    ],

    /*
    |--------------------------------------------------------------------------
    | Tool Permissions
    |--------------------------------------------------------------------------
    |
    | Define which tools are available for each context
    |
    */

    'tools' => [
        // Tools available for tenant users (implemented)
        'tenant' => [
            // Basic operations
            'read_invoices',                // ✅ Implemented
            'create_invoice',               // ✅ Implemented
            'create_quote',                 // ✅ Implemented
            'search_partners',              // ✅ Implemented
            'create_partner',               // ✅ Implemented
            'record_payment',               // ✅ Implemented
            'invite_user',                  // ✅ Implemented

            // Advanced operations
            'send_invoice_email',           // ✅ Implemented
            'convert_quote_to_invoice',     // ✅ Implemented
            'generate_vat_declaration',     // ✅ Implemented
            'send_via_peppol',              // ✅ Implemented

            // Management & Export operations
            'update_invoice',               // ✅ Implemented
            'delete_invoice',               // ✅ Implemented
            'reconcile_bank_transaction',   // ✅ Implemented
            'create_expense',               // ✅ Implemented
            'export_accounting_data',       // ✅ Implemented

            // Payroll operations
            'create_employee',              // ✅ Implemented
            'generate_payslip',             // ✅ Implemented

            // Invoice automation
            'create_invoice_template',      // ✅ Implemented
            'create_recurring_invoice',     // ✅ Implemented
            'configure_invoice_reminders',  // ✅ Implemented

            // Future tools:
            // 'generate_custom_report',
            // 'view_dashboard_stats',
            // 'manage_subscriptions',
            // 'batch_operations',
            // 'create_employment_contract',
            // 'generate_dimona',
            // 'generate_dmfa',
        ],

        // Tools for accounting firms / fiduciaries (implemented)
        'firm' => [
            'get_all_clients_data',         // ✅ Implemented - Overview of all clients
            'bulk_export_accounting',       // ✅ Implemented - Bulk export for multiple clients
            'generate_multi_client_report', // ✅ Implemented - Comparative reports
            'assign_mandate_task',          // ✅ Implemented - Task assignment
            'get_client_health_score',      // ✅ Implemented - Client health scoring

            // Future firm tools:
            // 'bulk_vat_declaration',
            // 'time_tracking',
            // 'client_billing',
            // 'team_performance',
        ],

        // Additional tools for superadmins (implemented)
        'superadmin' => [
            'create_demo_account',          // ✅ Implemented

            // Future tools:
            // 'manage_user',
            // 'suspend_user',
            // 'view_all_companies',
            // 'get_platform_statistics',
            // 'impersonate_user',
            // 'modify_subscription',
            // 'view_audit_logs',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | System Prompts
    |--------------------------------------------------------------------------
    */

    'system_prompts' => [
        'tenant' => "Vous êtes un assistant AI pour ComptaBE, une plateforme comptable belge. Vous aidez les utilisateurs avec leurs factures, déclarations TVA, comptabilité et gestion d'entreprise. Répondez en français de manière professionnelle et concise. Utilisez les outils disponibles pour exécuter les actions demandées.",

        'superadmin' => "Vous êtes un assistant AI administrateur pour ComptaBE. Vous avez accès à toutes les fonctionnalités de gestion de la plateforme, incluant la gestion des utilisateurs, entreprises, abonnements et statistiques globales. Soyez précis et professionnel.",
    ],
];
