<?php

namespace App\Services;

use App\Models\Company;
use App\Models\PeppolUsage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PeppolPlanOptimizer
{
    /**
     * Calculer le volume total de tous les tenants pour le mois en cours
     */
    public function getTotalMonthlyVolume(): int
    {
        return PeppolUsage::where('month', now()->month)
            ->where('year', now()->year)
            ->where('status', 'success')
            ->where('counted_in_quota', true)
            ->count();
    }

    /**
     * Obtenir le plan provider actuel
     */
    public function getCurrentProviderPlan(): array
    {
        return [
            'provider' => $this->getSetting('peppol_global_provider', 'recommand'),
            'plan' => $this->getSetting('peppol_global_plan', 'free'),
        ];
    }

    /**
     * Calculer le coût avec le plan actuel
     */
    public function calculateCurrentCost(int $volume): float
    {
        $current = $this->getCurrentProviderPlan();
        $plans = config('peppol_plans.providers');

        $provider = $plans[$current['provider']] ?? null;
        if (!$provider) {
            return 0;
        }

        $plan = $provider['plans'][$current['plan']] ?? null;
        if (!$plan) {
            return 0;
        }

        $monthlyCost = $plan['monthly_cost'];
        $included = $plan['included_documents'];
        $overage = max(0, $volume - $included);
        $overageCost = $overage * $plan['overage_cost'];

        return $monthlyCost + $overageCost;
    }

    /**
     * Trouver le plan optimal pour un volume donné
     */
    public function findOptimalPlan(int $projectedVolume): array
    {
        $providers = config('peppol_plans.providers');
        $bestOption = null;
        $lowestCost = PHP_FLOAT_MAX;

        foreach ($providers as $providerKey => $provider) {
            foreach ($provider['plans'] as $planKey => $plan) {
                $cost = $this->calculatePlanCost($plan, $projectedVolume);

                // Vérifier si le plan peut gérer ce volume
                if ($plan['max_documents'] !== null && $projectedVolume > $plan['max_documents']) {
                    continue;
                }

                if ($cost < $lowestCost) {
                    $lowestCost = $cost;
                    $bestOption = [
                        'provider' => $providerKey,
                        'provider_name' => $provider['name'],
                        'plan' => $planKey,
                        'plan_name' => $plan['name'],
                        'monthly_cost' => $plan['monthly_cost'],
                        'included_documents' => $plan['included_documents'],
                        'total_cost' => $cost,
                        'documents_volume' => $projectedVolume,
                    ];
                }
            }
        }

        return $bestOption ?? [
            'provider' => 'recommand',
            'plan' => 'free',
            'total_cost' => 0,
        ];
    }

    /**
     * Calculer le coût d'un plan pour un volume donné
     */
    protected function calculatePlanCost(array $plan, int $volume): float
    {
        $monthlyCost = $plan['monthly_cost'];
        $included = $plan['included_documents'];
        $overage = max(0, $volume - $included);
        $overageCost = $overage * $plan['overage_cost'];

        return $monthlyCost + $overageCost;
    }

    /**
     * Obtenir une recommandation de plan basée sur le volume actuel et projeté
     */
    public function getRecommendation(): array
    {
        $currentVolume = $this->getTotalMonthlyVolume();
        $projectedVolume = $this->projectNextMonthVolume();
        $current = $this->getCurrentProviderPlan();
        $optimal = $this->findOptimalPlan($projectedVolume);

        $currentCost = $this->calculateCurrentCost($currentVolume);
        $optimalCost = $optimal['total_cost'];
        $savings = $currentCost - $optimalCost;

        $shouldUpgrade = false;
        $shouldDowngrade = false;
        $reason = '';

        if ($optimal['provider'] !== $current['provider'] || $optimal['plan'] !== $current['plan']) {
            if ($optimalCost < $currentCost) {
                $shouldDowngrade = true;
                $reason = "Vous pourriez économiser €" . number_format($savings, 2) . "/mois";
            } else {
                $shouldUpgrade = true;
                $reason = "Volume en croissance, optimisez vos coûts";
            }
        }

        // Calculer les revenus estimés de vos tenants
        $tenantRevenue = $this->calculateTenantRevenue();
        $margin = $tenantRevenue - $optimalCost;
        $marginPercent = $tenantRevenue > 0 ? ($margin / $tenantRevenue) * 100 : 0;

        return [
            'current' => [
                'provider' => $current['provider'],
                'plan' => $current['plan'],
                'volume' => $currentVolume,
                'cost' => $currentCost,
            ],
            'optimal' => $optimal,
            'should_upgrade' => $shouldUpgrade,
            'should_downgrade' => $shouldDowngrade,
            'reason' => $reason,
            'savings' => $savings,
            'projected_volume' => $projectedVolume,
            'revenue' => [
                'tenant_revenue' => $tenantRevenue,
                'provider_cost' => $optimalCost,
                'margin' => $margin,
                'margin_percent' => round($marginPercent, 1),
            ],
        ];
    }

    /**
     * Projeter le volume du mois prochain basé sur la croissance
     */
    protected function projectNextMonthVolume(): int
    {
        $currentMonth = $this->getTotalMonthlyVolume();

        // Obtenir le volume du mois dernier
        $lastMonth = PeppolUsage::where('month', now()->subMonth()->month)
            ->where('year', now()->subMonth()->year)
            ->where('status', 'success')
            ->where('counted_in_quota', true)
            ->count();

        if ($lastMonth === 0) {
            // Pas de données historiques, projeter +20% de croissance par défaut
            return (int) ceil($currentMonth * 1.2);
        }

        // Calculer le taux de croissance
        $growthRate = $lastMonth > 0 ? ($currentMonth - $lastMonth) / $lastMonth : 0.2;

        // Limiter la croissance projetée entre -50% et +100%
        $growthRate = max(-0.5, min(1.0, $growthRate));

        // Projeter le mois prochain
        return (int) ceil($currentMonth * (1 + $growthRate));
    }

    /**
     * Calculer les revenus générés par vos tenants
     */
    protected function calculateTenantRevenue(): float
    {
        $tenantPlans = config('peppol_plans.tenant_plans');
        $revenue = 0;

        foreach ($tenantPlans as $planKey => $plan) {
            $count = Company::where('peppol_plan', $planKey)->count();
            $revenue += $count * $plan['price'];
        }

        return $revenue;
    }

    /**
     * Obtenir les statistiques complètes
     */
    public function getStats(): array
    {
        $totalCompanies = Company::count();
        $activeCompanies = Company::where('peppol_usage_current_month', '>', 0)->count();
        $totalVolume = $this->getTotalMonthlyVolume();

        $planDistribution = Company::select('peppol_plan', DB::raw('count(*) as count'))
            ->groupBy('peppol_plan')
            ->get()
            ->pluck('count', 'peppol_plan')
            ->toArray();

        return [
            'total_companies' => $totalCompanies,
            'active_companies' => $activeCompanies,
            'total_volume' => $totalVolume,
            'avg_volume_per_company' => $activeCompanies > 0 ? round($totalVolume / $activeCompanies, 1) : 0,
            'plan_distribution' => $planDistribution,
        ];
    }

    /**
     * Vérifier si un upgrade est nécessaire
     */
    public function needsUpgrade(): bool
    {
        $recommendation = Cache::remember('peppol_recommendation', 3600, function () {
            return $this->getRecommendation();
        });

        return $recommendation['should_upgrade'];
    }

    /**
     * Obtenir un setting système
     */
    protected function getSetting(string $key, $default = null)
    {
        $setting = DB::table('system_settings')->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Définir un setting système
     */
    public function setSetting(string $key, $value): void
    {
        $type = is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : 'string');

        DB::table('system_settings')->updateOrInsert(
            ['key' => $key],
            [
                'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                'type' => $type,
                'updated_at' => now(),
            ]
        );

        Cache::forget('peppol_recommendation');
    }

    /**
     * Obtenir l'historique des coûts
     */
    public function getCostHistory(int $months = 6): array
    {
        $history = [];
        $startDate = now()->subMonths($months);

        for ($i = 0; $i < $months; $i++) {
            $date = $startDate->copy()->addMonths($i);
            $volume = PeppolUsage::where('month', $date->month)
                ->where('year', $date->year)
                ->where('status', 'success')
                ->count();

            $cost = PeppolUsage::where('month', $date->month)
                ->where('year', $date->year)
                ->where('status', 'success')
                ->sum('cost');

            $history[] = [
                'month' => $date->format('Y-m'),
                'volume' => $volume,
                'cost' => (float) $cost,
            ];
        }

        return $history;
    }
}
