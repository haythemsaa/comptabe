<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class AdminSubscriptionPlanController extends Controller
{
    /**
     * Display a listing of subscription plans.
     */
    public function index()
    {
        $plans = SubscriptionPlan::ordered()->get();

        $stats = [
            'total' => $plans->count(),
            'active' => $plans->where('is_active', true)->count(),
            'subscribers' => \App\Models\Subscription::where('status', '!=', 'cancelled')->count(),
        ];

        return view('admin.subscription-plans.index', compact('plans', 'stats'));
    }

    /**
     * Show the form for creating a new plan.
     */
    public function create()
    {
        return view('admin.subscription-plans.create');
    }

    /**
     * Store a newly created plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:50|unique:subscription_plans,slug',
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'trial_days' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
            // Limits
            'max_users' => 'required|integer|min:-1',
            'max_invoices_per_month' => 'required|integer|min:-1',
            'max_clients' => 'required|integer|min:-1',
            'max_products' => 'required|integer|min:-1',
            'max_storage_mb' => 'required|integer|min:-1',
            // Features
            'feature_peppol' => 'boolean',
            'feature_recurring_invoices' => 'boolean',
            'feature_credit_notes' => 'boolean',
            'feature_quotes' => 'boolean',
            'feature_multi_currency' => 'boolean',
            'feature_api_access' => 'boolean',
            'feature_custom_branding' => 'boolean',
            'feature_advanced_reports' => 'boolean',
            'feature_priority_support' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        // Handle feature checkboxes
        foreach (['peppol', 'recurring_invoices', 'credit_notes', 'quotes', 'multi_currency', 'api_access', 'custom_branding', 'advanced_reports', 'priority_support'] as $feature) {
            $validated["feature_{$feature}"] = $request->boolean("feature_{$feature}");
        }

        SubscriptionPlan::create($validated);

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Plan créé avec succès.');
    }

    /**
     * Show the form for editing a plan.
     */
    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        $subscribersCount = $subscriptionPlan->subscriptions()
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->count();

        return view('admin.subscription-plans.edit', compact('subscriptionPlan', 'subscribersCount'));
    }

    /**
     * Update the specified plan.
     */
    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:50|unique:subscription_plans,slug,' . $subscriptionPlan->id,
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'trial_days' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
            // Limits
            'max_users' => 'required|integer|min:-1',
            'max_invoices_per_month' => 'required|integer|min:-1',
            'max_clients' => 'required|integer|min:-1',
            'max_products' => 'required|integer|min:-1',
            'max_storage_mb' => 'required|integer|min:-1',
            // Features
            'feature_peppol' => 'boolean',
            'feature_recurring_invoices' => 'boolean',
            'feature_credit_notes' => 'boolean',
            'feature_quotes' => 'boolean',
            'feature_multi_currency' => 'boolean',
            'feature_api_access' => 'boolean',
            'feature_custom_branding' => 'boolean',
            'feature_advanced_reports' => 'boolean',
            'feature_priority_support' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        // Handle feature checkboxes
        foreach (['peppol', 'recurring_invoices', 'credit_notes', 'quotes', 'multi_currency', 'api_access', 'custom_branding', 'advanced_reports', 'priority_support'] as $feature) {
            $validated["feature_{$feature}"] = $request->boolean("feature_{$feature}");
        }

        $subscriptionPlan->update($validated);

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Plan mis à jour avec succès.');
    }

    /**
     * Remove the specified plan.
     */
    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        // Check if plan has active subscribers
        $activeSubscribers = $subscriptionPlan->subscriptions()
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->count();

        if ($activeSubscribers > 0) {
            return back()->with('error', "Impossible de supprimer ce plan: {$activeSubscribers} abonnés actifs.");
        }

        $subscriptionPlan->delete();

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Plan supprimé avec succès.');
    }

    /**
     * Toggle plan active status.
     */
    public function toggleActive(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->update(['is_active' => !$subscriptionPlan->is_active]);

        $status = $subscriptionPlan->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Plan {$status}.");
    }
}
