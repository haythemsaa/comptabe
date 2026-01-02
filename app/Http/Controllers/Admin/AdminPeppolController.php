<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\PeppolUsage;
use App\Services\PeppolPlanOptimizer;
use App\Services\PeppolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminPeppolController extends Controller
{
    protected PeppolPlanOptimizer $optimizer;

    public function __construct(PeppolPlanOptimizer $optimizer)
    {
        $this->optimizer = $optimizer;
    }

    /**
     * Dashboard principal Peppol
     */
    public function dashboard()
    {
        $recommendation = $this->optimizer->getRecommendation();
        $stats = $this->optimizer->getStats();
        $costHistory = $this->optimizer->getCostHistory(6);

        // Top 10 entreprises utilisatrices
        $topUsers = Company::withCount([
            'peppolUsage' => function ($query) {
                $query->where('month', now()->month)
                    ->where('year', now()->year)
                    ->where('status', 'success');
            }
        ])
        ->where('peppol_usage_current_month', '>', 0)
        ->orderBy('peppol_usage_current_month', 'desc')
        ->take(10)
        ->get();

        // Statistiques du mois en cours
        $currentMonth = [
            'sends' => PeppolUsage::currentMonth()->where('action', 'send')->successful()->count(),
            'receives' => PeppolUsage::currentMonth()->where('action', 'receive')->successful()->count(),
            'failed' => PeppolUsage::currentMonth()->failed()->count(),
            'total_cost' => PeppolUsage::currentMonth()->sum('cost'),
        ];

        return view('admin.peppol.dashboard', compact(
            'recommendation',
            'stats',
            'costHistory',
            'topUsers',
            'currentMonth'
        ));
    }

    /**
     * Paramètres globaux Peppol
     */
    public function settings()
    {
        $settings = [
            'provider' => $this->getSetting('peppol_global_provider', 'recommand'),
            'plan' => $this->getSetting('peppol_global_plan', 'free'),
            'api_key' => $this->getSetting('peppol_global_api_key', ''),
            'api_secret' => $this->getSetting('peppol_global_api_secret', ''),
            'test_mode' => (bool) $this->getSetting('peppol_global_test_mode', true),
            'enabled' => (bool) $this->getSetting('peppol_enabled', true),
        ];

        $providers = config('peppol_plans.providers');
        $currentProvider = $providers[$settings['provider']] ?? null;
        $availablePlans = $currentProvider ? $currentProvider['plans'] : [];

        return view('admin.peppol.settings', compact('settings', 'providers', 'availablePlans'));
    }

    /**
     * Mise à jour des paramètres globaux
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|in:recommand,digiteal,peppol_box',
            'plan' => 'required|string',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'test_mode' => 'boolean',
            'enabled' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            $this->setSetting("peppol_global_$key", $value);
        }

        // Si enabled change, mettre à jour aussi
        if (isset($validated['enabled'])) {
            $this->setSetting('peppol_enabled', $validated['enabled']);
        }

        AuditLog::log('update', 'Configuration Peppol globale mise à jour', null, null, $validated);

        return redirect()
            ->route('admin.peppol.settings')
            ->with('success', 'Configuration Peppol mise à jour avec succès.');
    }

    /**
     * Test de connexion à l'API Peppol
     */
    public function testConnection()
    {
        try {
            $peppolService = app(PeppolService::class);
            $result = $peppolService->testConnection();

            if ($result['success']) {
                return back()->with('success', 'Connexion réussie ! API Peppol opérationnelle.');
            } else {
                return back()->with('error', 'Échec de connexion : ' . ($result['error'] ?? 'Erreur inconnue'));
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Gestion des quotas par entreprise
     */
    public function quotas(Request $request)
    {
        $search = $request->input('search');
        $planFilter = $request->input('plan');

        $companies = Company::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('vat_number', 'like', "%{$search}%");
                });
            })
            ->when($planFilter, function ($query, $plan) {
                $query->where('peppol_plan', $plan);
            })
            ->withCount([
                'peppolUsage' => function ($query) {
                    $query->where('month', now()->month)
                        ->where('year', now()->year)
                        ->where('status', 'success');
                }
            ])
            ->orderBy('peppol_usage_current_month', 'desc')
            ->paginate(50);

        $planStats = Company::select('peppol_plan', DB::raw('count(*) as count'))
            ->groupBy('peppol_plan')
            ->get()
            ->pluck('count', 'peppol_plan')
            ->toArray();

        $tenantPlans = config('peppol_plans.tenant_plans');

        return view('admin.peppol.quotas', compact('companies', 'planStats', 'tenantPlans', 'search', 'planFilter'));
    }

    /**
     * Mettre à jour le quota d'une entreprise
     */
    public function updateQuota(Request $request, Company $company)
    {
        $validated = $request->validate([
            'peppol_plan' => 'required|in:free,starter,pro,business,enterprise',
            'peppol_quota_monthly' => 'required|integer|min:0',
            'peppol_overage_allowed' => 'boolean',
            'peppol_overage_cost' => 'nullable|numeric|min:0',
        ]);

        $company->update($validated);

        AuditLog::log('update', 'Quota Peppol mis à jour pour ' . $company->name, 'Company', $company->id, $validated);

        return back()->with('success', "Quota mis à jour pour {$company->name}");
    }

    /**
     * Optimiser automatiquement le plan provider
     */
    public function optimize()
    {
        $currentVolume = $this->optimizer->getTotalMonthlyVolume();
        $projectedVolume = $this->optimizer->projectNextMonthVolume();
        $optimal = $this->optimizer->findOptimalPlan($projectedVolume);

        return view('admin.peppol.optimize', compact('currentVolume', 'projectedVolume', 'optimal'));
    }

    /**
     * Appliquer le plan optimal
     */
    public function applyOptimalPlan()
    {
        $projectedVolume = $this->optimizer->projectNextMonthVolume();
        $optimal = $this->optimizer->findOptimalPlan($projectedVolume);

        $this->setSetting('peppol_global_provider', $optimal['provider']);
        $this->setSetting('peppol_global_plan', $optimal['plan']);

        AuditLog::log('update', 'Plan Peppol optimisé automatiquement', null, null, [
            'provider' => $optimal['provider'],
            'plan' => $optimal['plan'],
            'projected_volume' => $projectedVolume,
            'estimated_cost' => $optimal['total_cost'],
        ]);

        return redirect()
            ->route('admin.peppol.dashboard')
            ->with('success', "Plan optimisé : {$optimal['provider_name']} {$optimal['plan_name']}");
    }

    /**
     * Historique d'usage détaillé
     */
    public function usage(Request $request)
    {
        $dateFrom = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('to', now()->endOfMonth()->format('Y-m-d'));
        $status = $request->input('status');
        $action = $request->input('action');

        $usage = PeppolUsage::with(['company', 'invoice'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($action, function ($query, $action) {
                $query->where('action', $action);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(100);

        $summary = [
            'total' => PeppolUsage::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'successful' => PeppolUsage::whereBetween('created_at', [$dateFrom, $dateTo])->successful()->count(),
            'failed' => PeppolUsage::whereBetween('created_at', [$dateFrom, $dateTo])->failed()->count(),
            'total_cost' => PeppolUsage::whereBetween('created_at', [$dateFrom, $dateTo])->sum('cost'),
        ];

        return view('admin.peppol.usage', compact('usage', 'summary', 'dateFrom', 'dateTo', 'status', 'action'));
    }

    /**
     * Réinitialiser les quotas manuellement
     */
    public function resetQuotas()
    {
        $count = 0;
        Company::chunk(100, function ($companies) use (&$count) {
            foreach ($companies as $company) {
                $company->update([
                    'peppol_usage_current_month' => 0,
                    'peppol_usage_last_reset' => now(),
                ]);
                $count++;
            }
        });

        AuditLog::log('update', "Quotas Peppol réinitialisés pour {$count} entreprises");

        return back()->with('success', "Quotas réinitialisés pour {$count} entreprises");
    }

    /**
     * Obtenir un paramètre système
     */
    protected function getSetting(string $key, $default = null)
    {
        return Cache::remember("system_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = DB::table('system_settings')->where('key', $key)->first();
            if (!$setting) {
                return $default;
            }
            return $this->castValue($setting->value, $setting->type);
        });
    }

    /**
     * Définir un paramètre système
     */
    protected function setSetting(string $key, $value): void
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

        Cache::forget("system_setting_{$key}");
    }

    /**
     * Caster une valeur selon son type
     */
    protected function castValue($value, string $type)
    {
        return match($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }
}
