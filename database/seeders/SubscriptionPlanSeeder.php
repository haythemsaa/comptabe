<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Gratuit',
                'slug' => 'free',
                'description' => 'Idéal pour démarrer et découvrir ComptaBE',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'trial_days' => 0,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 0,
                // Limites
                'max_users' => 1,
                'max_invoices_per_month' => 10,
                'max_clients' => 10,
                'max_products' => 20,
                'max_storage_mb' => 100,
                // Features
                'feature_peppol' => true, // Envoi Peppol uniquement
                'feature_recurring_invoices' => false,
                'feature_credit_notes' => false,
                'feature_quotes' => false,
                'feature_multi_currency' => false,
                'feature_api_access' => false,
                'feature_custom_branding' => false,
                'feature_advanced_reports' => false,
                'feature_priority_support' => false,
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Parfait pour indépendants et micro-entreprises',
                'price_monthly' => 9.00,
                'price_yearly' => 90.00, // ~17% discount
                'trial_days' => 14,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
                // Limites
                'max_users' => 1,
                'max_invoices_per_month' => 50,
                'max_clients' => 50,
                'max_products' => 100,
                'max_storage_mb' => 500,
                // Features
                'feature_peppol' => true, // Envoi + Réception Peppol
                'feature_recurring_invoices' => false,
                'feature_credit_notes' => true,
                'feature_quotes' => true,
                'feature_multi_currency' => false,
                'feature_api_access' => false,
                'feature_custom_branding' => false,
                'feature_advanced_reports' => false,
                'feature_priority_support' => false,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Pour PME avec besoins comptables complets',
                'price_monthly' => 29.00,
                'price_yearly' => 290.00, // ~17% discount
                'trial_days' => 14,
                'is_active' => true,
                'is_featured' => true, // Plan recommandé
                'sort_order' => 2,
                // Limites
                'max_users' => 5,
                'max_invoices_per_month' => -1, // Illimité
                'max_clients' => -1, // Illimité
                'max_products' => -1, // Illimité
                'max_storage_mb' => 2000, // 2GB
                // Features
                'feature_peppol' => true,
                'feature_recurring_invoices' => true,
                'feature_credit_notes' => true,
                'feature_quotes' => true,
                'feature_multi_currency' => true,
                'feature_api_access' => true,
                'feature_custom_branding' => false,
                'feature_advanced_reports' => true,
                'feature_priority_support' => false,
            ],
            [
                'name' => 'Cabinet',
                'slug' => 'cabinet',
                'description' => 'Solution SaaS pour cabinets comptables multi-clients',
                'price_monthly' => 99.00,
                'price_yearly' => 990.00, // ~17% discount
                'trial_days' => 30,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
                // Limites
                'max_users' => -1, // Illimité
                'max_invoices_per_month' => -1, // Illimité
                'max_clients' => -1, // Illimité
                'max_products' => -1, // Illimité
                'max_storage_mb' => 10000, // 10GB
                // Features
                'feature_peppol' => true,
                'feature_recurring_invoices' => true,
                'feature_credit_notes' => true,
                'feature_quotes' => true,
                'feature_multi_currency' => true,
                'feature_api_access' => true,
                'feature_custom_branding' => true,
                'feature_advanced_reports' => true,
                'feature_priority_support' => true,
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $planData['slug']],
                array_merge($planData, ['id' => Str::uuid()])
            );
        }
    }
}
