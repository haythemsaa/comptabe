<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModulesSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            // ========== CORE MODULES (Always enabled, free) ==========
            [
                'code' => 'accounting',
                'name' => 'Comptabilité Générale',
                'description' => 'Plan comptable, écritures, journaux, balance, grand livre',
                'category' => 'finance',
                'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                'is_core' => true,
                'is_premium' => false,
                'monthly_price' => 0,
                'sort_order' => 1,
            ],
            [
                'code' => 'invoices',
                'name' => 'Facturation Client',
                'description' => 'Factures, notes de crédit, paiements, relances automatiques',
                'category' => 'sales',
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'is_core' => true,
                'is_premium' => false,
                'monthly_price' => 0,
                'sort_order' => 2,
            ],
            [
                'code' => 'partners',
                'name' => 'Clients & Fournisseurs',
                'description' => 'Gestion contacts, clients, fournisseurs, historique',
                'category' => 'sales',
                'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                'is_core' => true,
                'is_premium' => false,
                'monthly_price' => 0,
                'sort_order' => 3,
            ],

            // ========== SALES & CRM ==========
            [
                'code' => 'crm',
                'name' => 'CRM & Pipeline Commercial',
                'description' => 'Opportunités, pipeline Kanban, prévisions CA, activités commerciales',
                'category' => 'sales',
                'icon' => 'M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
                'is_core' => false,
                'is_premium' => false,
                'monthly_price' => 19.99,
                'sort_order' => 10,
                'dependencies' => ['partners'],
            ],
            [
                'code' => 'quotes',
                'name' => 'Devis & Propositions',
                'description' => 'Devis, conversion en facture, versions multiples, validité',
                'category' => 'sales',
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'is_core' => false,
                'is_premium' => false,
                'monthly_price' => 0,
                'sort_order' => 11,
                'dependencies' => ['partners'],
            ],
            [
                'code' => 'recurring_invoices',
                'name' => 'Facturation Récurrente',
                'description' => 'Abonnements, factures récurrentes automatiques, contrats',
                'category' => 'sales',
                'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                'is_core' => false,
                'is_premium' => false,
                'monthly_price' => 9.99,
                'sort_order' => 12,
                'dependencies' => ['invoices'],
            ],

            // ========== INVENTORY & PRODUCTS ==========
            [
                'code' => 'stock',
                'name' => 'Gestion de Stock',
                'description' => 'Inventaire, mouvements, alertes stock faible, multi-entrepôts, valorisation',
                'category' => 'inventory',
                'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                'is_core' => false,
                'is_premium' => false,
                'monthly_price' => 24.99,
                'sort_order' => 20,
            ],
            [
                'code' => 'products',
                'name' => 'Produits & Services',
                'description' => 'Catalogue produits, variantes, tarifs, catégories',
                'category' => 'inventory',
                'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
                'is_core' => false,
                'is_premium' => false,
                'monthly_price' => 0,
                'sort_order' => 21,
            ],

            // ========== HR & PAYROLL ==========
            [
                'code' => 'payroll',
                'name' => 'Paie & RH',
                'description' => 'Employés, bulletins paie, ONSS, déclarations sociales (BE/FR/TN)',
                'category' => 'hr',
                'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                'is_core' => false,
                'is_premium' => false,
                'monthly_price' => 29.99,
                'sort_order' => 30,
            ],
            [
                'code' => 'expenses',
                'name' => 'Notes de Frais',
                'description' => 'Saisie frais, validation workflow, scan reçus OCR, remboursements',
                'category' => 'hr',
                'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                'is_core' => false,
                'is_premium' => false,
                'monthly_price' => 14.99,
                'sort_order' => 31,
            ],
            [
                'code' => 'leaves',
                'name' => 'Gestion des Congés',
                'description' => 'Demandes congés, validations, soldes, calendrier équipe',
                'category' => 'hr',
                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                'is_core' => false,
                'is_premium' => false,
                'monthly_price' => 9.99,
                'sort_order' => 32,
                'dependencies' => ['payroll'],
            ],

            // ========== PRODUCTIVITY ==========
            [
                'code' => 'projects',
                'name' => 'Gestion de Projets',
                'description' => 'Projets, tâches Kanban, Gantt, allocation ressources, budgets',
                'category' => 'productivity',
                'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                'is_core' => false,
                'is_premium' => false,
                'monthly_price' => 34.99,
                'sort_order' => 40,
            ],
            [
                'code' => 'timesheet',
                'name' => 'Feuilles de Temps',
                'description' => 'Timesheet hebdomadaire, imputation projets, facturation temps',
                'category' => 'productivity',
                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                'is_core' => false,
                'is_premium' => false,
                'monthly_price' => 14.99,
                'sort_order' => 41,
                'dependencies' => ['projects'],
            ],

            // ========== FINANCE ==========
            [
                'code' => 'bank',
                'name' => 'Trésorerie & Banque',
                'description' => 'Comptes bancaires, rapprochement automatique, prévisions trésorerie',
                'category' => 'finance',
                'icon' => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3',
                'is_core' => false,
                'is_premium' => false,
                'monthly_price' => 0,
                'sort_order' => 50,
                'dependencies' => ['accounting'],
            ],
            [
                'code' => 'vat',
                'name' => 'TVA & Taxes',
                'description' => 'Déclarations TVA, Intervat, listing intracommunautaire',
                'category' => 'finance',
                'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                'is_core' => false,
                'is_premium' => false,
                'monthly_price' => 0,
                'sort_order' => 51,
                'dependencies' => ['accounting'],
            ],
            [
                'code' => 'reports',
                'name' => 'Rapports & Analytics',
                'description' => 'Tableau de bord BI, rapports personnalisés, exports Excel/PDF',
                'category' => 'finance',
                'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                'is_core' => false,
                'is_premium' => false,
                'monthly_price' => 19.99,
                'sort_order' => 52,
            ],

            // ========== PREMIUM MODULES ==========
            [
                'code' => 'ai',
                'name' => 'Intelligence Artificielle',
                'description' => 'Chat IA, OCR factures, catégorisation auto, prédictions trésorerie',
                'category' => 'productivity',
                'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
                'is_core' => false,
                'is_premium' => true,
                'monthly_price' => 49.99,
                'sort_order' => 60,
            ],
            [
                'code' => 'peppol',
                'name' => 'Peppol E-Invoicing',
                'description' => 'Facturation électronique Peppol, envoi/réception UBL, conformité',
                'category' => 'sales',
                'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                'is_core' => false,
                'is_premium' => true,
                'monthly_price' => 29.99,
                'sort_order' => 61,
                'dependencies' => ['invoices'],
            ],
            [
                'code' => 'open_banking',
                'name' => 'Open Banking (PSD2)',
                'description' => 'Connexion directe banques, import transactions temps réel',
                'category' => 'finance',
                'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                'is_core' => false,
                'is_premium' => true,
                'monthly_price' => 19.99,
                'sort_order' => 62,
                'dependencies' => ['bank'],
            ],
            [
                'code' => 'accounting_firm',
                'name' => 'Multi-Clients Fiduciaire',
                'description' => 'Gestion cabinet comptable, mandats clients, portail client',
                'category' => 'productivity',
                'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                'is_core' => false,
                'is_premium' => true,
                'monthly_price' => 99.99,
                'sort_order' => 63,
            ],

            // ========== COLLABORATION ==========
            [
                'code' => 'documents',
                'name' => 'GED & Archivage',
                'description' => 'Gestion documentaire, OCR, tags, recherche plein texte',
                'category' => 'productivity',
                'icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                'is_core' => false,
                'is_premium' => false,
                'monthly_price' => 9.99,
                'sort_order' => 70,
            ],
            [
                'code' => 'approvals',
                'name' => 'Workflows d\'Approbation',
                'description' => 'Validation multi-niveaux, délais, escalade automatique',
                'category' => 'productivity',
                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'is_core' => false,
                'is_premium' => false,
                'monthly_price' => 14.99,
                'sort_order' => 71,
            ],
        ];

        foreach ($modules as $moduleData) {
            Module::updateOrCreate(
                ['code' => $moduleData['code']],
                $moduleData
            );
        }

        $this->command->info('✅ ' . count($modules) . ' modules created/updated successfully!');
    }
}
