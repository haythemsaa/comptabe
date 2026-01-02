<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class AdminSubscriptionController extends Controller
{
    /**
     * Display a listing of all subscriptions.
     */
    public function index(Request $request)
    {
        $query = Subscription::with(['company', 'plan'])->latest();

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan')) {
            $query->where('plan_id', $request->plan);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('company', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%");
            });
        }

        $subscriptions = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Subscription::count(),
            'trialing' => Subscription::where('status', 'trialing')->count(),
            'active' => Subscription::where('status', 'active')->count(),
            'past_due' => Subscription::where('status', 'past_due')->count(),
            'mrr' => Subscription::where('status', 'active')
                ->where('billing_cycle', 'monthly')
                ->sum('amount'),
            'arr' => Subscription::where('status', 'active')
                ->where('billing_cycle', 'yearly')
                ->sum('amount'),
        ];

        $plans = SubscriptionPlan::ordered()->get();

        return view('admin.subscriptions.index', compact('subscriptions', 'stats', 'plans'));
    }

    /**
     * Display subscription details.
     */
    public function show(Subscription $subscription)
    {
        $subscription->load(['company.users', 'plan', 'invoices']);

        $usage = $subscription->company->getCurrentUsage();

        return view('admin.subscriptions.show', compact('subscription', 'usage'));
    }

    /**
     * Show form to change subscription plan.
     */
    public function edit(Subscription $subscription)
    {
        $subscription->load(['company', 'plan']);
        $plans = SubscriptionPlan::active()->ordered()->get();

        return view('admin.subscriptions.edit', compact('subscription', 'plans'));
    }

    /**
     * Update subscription (change plan, status, etc.)
     */
    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'plan_id' => 'required|uuid|exists:subscription_plans,id',
            'status' => 'required|in:trialing,active,past_due,cancelled,suspended,expired',
            'billing_cycle' => 'required|in:monthly,yearly',
            'admin_notes' => 'nullable|string',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        $amount = $validated['billing_cycle'] === 'yearly'
            ? $plan->price_yearly
            : $plan->price_monthly;

        $subscription->update([
            'plan_id' => $validated['plan_id'],
            'status' => $validated['status'],
            'billing_cycle' => $validated['billing_cycle'],
            'amount' => $amount,
            'admin_notes' => $validated['admin_notes'],
        ]);

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('success', 'Abonnement mis à jour.');
    }

    /**
     * Suspend subscription.
     */
    public function suspend(Request $request, Subscription $subscription)
    {
        $reason = $request->input('reason', 'Suspendu par l\'administrateur');
        $subscription->suspend($reason);

        return back()->with('success', 'Abonnement suspendu.');
    }

    /**
     * Reactivate subscription.
     */
    public function reactivate(Subscription $subscription)
    {
        $subscription->reactivate();

        return back()->with('success', 'Abonnement réactivé.');
    }

    /**
     * Extend trial period.
     */
    public function extendTrial(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:90',
        ]);

        $newEndDate = $subscription->trial_ends_at
            ? $subscription->trial_ends_at->addDays($validated['days'])
            : now()->addDays($validated['days']);

        $subscription->update([
            'status' => Subscription::STATUS_TRIALING,
            'trial_ends_at' => $newEndDate,
        ]);

        return back()->with('success', "Période d'essai prolongée de {$validated['days']} jours.");
    }

    /**
     * Create subscription for a company.
     */
    public function create(Company $company)
    {
        $plans = SubscriptionPlan::active()->ordered()->get();

        return view('admin.subscriptions.create', compact('company', 'plans'));
    }

    /**
     * Store new subscription for a company.
     */
    public function store(Request $request, Company $company)
    {
        $validated = $request->validate([
            'plan_id' => 'required|uuid|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
            'start_trial' => 'boolean',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        // Cancel existing subscription if any
        if ($company->subscription) {
            $company->subscription->cancel('Remplacé par nouvel abonnement');
        }

        $amount = $validated['billing_cycle'] === 'yearly'
            ? $plan->price_yearly
            : $plan->price_monthly;

        $subscription = Subscription::create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'status' => $request->boolean('start_trial') ? Subscription::STATUS_TRIALING : Subscription::STATUS_ACTIVE,
            'billing_cycle' => $validated['billing_cycle'],
            'amount' => $amount,
            'trial_ends_at' => $request->boolean('start_trial') ? now()->addDays($plan->trial_days) : null,
            'current_period_start' => $request->boolean('start_trial') ? null : now(),
            'current_period_end' => $request->boolean('start_trial') ? null : ($validated['billing_cycle'] === 'yearly' ? now()->addYear() : now()->addMonth()),
        ]);

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('success', 'Abonnement créé avec succès.');
    }

    /**
     * Generate invoice for subscription.
     */
    public function generateInvoice(Subscription $subscription)
    {
        $invoice = SubscriptionInvoice::createForSubscription($subscription);

        return redirect()->route('admin.subscription-invoices.show', $invoice)
            ->with('success', 'Facture générée.');
    }

    /**
     * Billing / Invoices list.
     */
    public function invoices(Request $request)
    {
        $query = SubscriptionInvoice::with(['company', 'subscription.plan'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('company', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => SubscriptionInvoice::sum('total'),
            'pending' => SubscriptionInvoice::where('status', 'pending')->sum('total'),
            'paid' => SubscriptionInvoice::where('status', 'paid')->sum('total'),
            'overdue' => SubscriptionInvoice::overdue()->sum('total'),
        ];

        return view('admin.subscription-invoices.index', compact('invoices', 'stats'));
    }

    /**
     * Show invoice details.
     */
    public function showInvoice(SubscriptionInvoice $subscriptionInvoice)
    {
        $subscriptionInvoice->load(['company', 'subscription.plan']);

        return view('admin.subscription-invoices.show', compact('subscriptionInvoice'));
    }

    /**
     * Mark invoice as paid.
     */
    public function markInvoicePaid(Request $request, SubscriptionInvoice $subscriptionInvoice)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:bank_transfer,card,cash,other',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        $subscriptionInvoice->markAsPaid(
            $validated['payment_method'],
            $validated['payment_reference']
        );

        return back()->with('success', 'Facture marquée comme payée.');
    }

    /**
     * Companies without subscription (for admin to assign).
     */
    public function unsubscribed()
    {
        $companies = Company::doesntHave('subscription')
            ->orWhereHas('subscription', function ($q) {
                $q->whereIn('status', ['cancelled', 'expired']);
            })
            ->with('users')
            ->latest()
            ->paginate(20);

        $plans = SubscriptionPlan::active()->ordered()->get();

        return view('admin.subscriptions.unsubscribed', compact('companies', 'plans'));
    }

    /**
     * Expiring trials (companies with trial ending soon).
     */
    public function expiringTrials()
    {
        $subscriptions = Subscription::with(['company', 'plan'])
            ->where('status', 'trialing')
            ->where('trial_ends_at', '<=', now()->addDays(7))
            ->where('trial_ends_at', '>', now())
            ->orderBy('trial_ends_at')
            ->paginate(20);

        return view('admin.subscriptions.expiring-trials', compact('subscriptions'));
    }
}
