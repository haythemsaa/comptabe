<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\Payment\PaymentProviderFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Show subscription status page (when subscription is required).
     */
    public function required()
    {
        $plans = SubscriptionPlan::active()->ordered()->get();

        return view('subscription.required', compact('plans'));
    }

    /**
     * Show suspended subscription page.
     */
    public function suspended()
    {
        $company = auth()->user()->companies()->find(session('current_tenant_id'));
        $subscription = $company?->subscription;

        return view('subscription.suspended', compact('subscription'));
    }

    /**
     * Show expired subscription page.
     */
    public function expired()
    {
        $company = auth()->user()->companies()->find(session('current_tenant_id'));
        $subscription = $company?->subscription;
        $plans = SubscriptionPlan::active()->ordered()->get();

        return view('subscription.expired', compact('subscription', 'plans'));
    }

    /**
     * Show upgrade page.
     */
    public function upgrade(Request $request)
    {
        $company = auth()->user()->companies()->find(session('current_tenant_id'));
        $currentPlan = $company?->plan;
        $subscription = $company?->subscription;

        $plans = SubscriptionPlan::active()
            ->ordered()
            ->get();

        $featureRequired = $request->session()->get('feature_required');

        return view('subscription.upgrade', compact('plans', 'currentPlan', 'subscription', 'featureRequired'));
    }

    /**
     * Show current subscription details.
     */
    public function show()
    {
        $company = auth()->user()->companies()->find(session('current_tenant_id'));
        $subscription = $company?->subscription;
        $plan = $company?->plan;
        $usage = $company?->getCurrentUsage();
        $invoices = $subscription?->invoices()->latest()->take(10)->get() ?? collect();

        return view('subscription.show', compact('subscription', 'plan', 'usage', 'invoices', 'company'));
    }

    /**
     * Start a subscription (select plan).
     */
    public function selectPlan(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|uuid|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $company = auth()->user()->companies()->find(session('current_tenant_id'));

        if (!$company) {
            return redirect()->route('tenant.select')
                ->with('error', 'Veuillez d\'abord sélectionner une entreprise.');
        }

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        // If free plan, create subscription directly
        if ($plan->isFree()) {
            $subscription = Subscription::createForCompany($company, $plan);
            $subscription->activate($validated['billing_cycle']);

            return redirect()->route('dashboard')
                ->with('success', 'Votre abonnement gratuit est activé.');
        }

        // For paid plans, redirect to payment
        session([
            'pending_subscription' => [
                'plan_id' => $plan->id,
                'billing_cycle' => $validated['billing_cycle'],
            ],
        ]);

        return redirect()->route('subscription.payment');
    }

    /**
     * Show payment page.
     */
    public function payment()
    {
        $pendingSubscription = session('pending_subscription');

        if (!$pendingSubscription) {
            return redirect()->route('subscription.upgrade');
        }

        $plan = SubscriptionPlan::findOrFail($pendingSubscription['plan_id']);
        $billingCycle = $pendingSubscription['billing_cycle'];
        $amount = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

        return view('subscription.payment', compact('plan', 'billingCycle', 'amount'));
    }

    /**
     * Process payment with selected provider (Mollie or Stripe).
     */
    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'payment_provider' => 'required|in:mollie,stripe',
            'payment_type' => 'required|in:onetime,recurring',
        ]);

        $pendingSubscription = session('pending_subscription');

        if (!$pendingSubscription) {
            return redirect()->route('subscription.upgrade')
                ->with('error', 'Session expirée. Veuillez recommencer.');
        }

        $company = auth()->user()->companies()->find(session('current_tenant_id'));
        $plan = SubscriptionPlan::findOrFail($pendingSubscription['plan_id']);
        $billingCycle = $pendingSubscription['billing_cycle'];

        // Calculate amount
        $amount = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

        // Create or update subscription
        if ($company->subscription && $company->subscription->status !== 'cancelled') {
            $subscription = $company->subscription;
            $subscription->update([
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'amount' => $amount,
                'payment_provider' => $validated['payment_provider'],
            ]);
        } else {
            $subscription = Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'status' => 'trialing',
                'billing_cycle' => $billingCycle,
                'amount' => $amount,
                'payment_provider' => $validated['payment_provider'],
            ]);
        }

        try {
            $provider = PaymentProviderFactory::make($validated['payment_provider']);

            if ($validated['payment_type'] === 'recurring') {
                // Create recurring subscription
                $result = $provider->createSubscription($company, $plan->slug, [
                    'success_url' => route('subscription.success', ['subscription_id' => $subscription->id]),
                    'cancel_url' => route('subscription.cancel-payment'),
                ]);

                // Update subscription with provider IDs
                $subscription->update([
                    'provider_subscription_id' => $result['subscription_id'],
                    'provider_customer_id' => $result['customer_id'] ?? null,
                ]);

                // For Stripe, might need client_secret for card setup
                if ($validated['payment_provider'] === 'stripe' && isset($result['client_secret'])) {
                    return view('subscription.stripe-setup', [
                        'clientSecret' => $result['client_secret'],
                        'subscription' => $subscription,
                    ]);
                }

                session()->forget('pending_subscription');

                return redirect()->route('subscription.show')
                    ->with('success', 'Votre abonnement récurrent a été créé avec succès.');

            } else {
                // One-time payment
                $result = $provider->createPayment($subscription, [
                    'success_url' => route('subscription.success', ['subscription_id' => $subscription->id]),
                    'cancel_url' => route('subscription.cancel-payment'),
                    'description' => "Abonnement {$plan->name} - {$billingCycle}",
                ]);

                // Redirect to checkout
                return redirect($result['checkout_url']);
            }

        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'provider' => $validated['payment_provider'],
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('subscription.payment')
                ->with('error', 'Une erreur est survenue lors du traitement du paiement. Veuillez réessayer.');
        }
    }

    /**
     * Payment success callback
     */
    public function success(Request $request)
    {
        $subscriptionId = $request->get('subscription_id');
        $subscription = Subscription::find($subscriptionId);

        if (!$subscription) {
            return redirect()->route('dashboard')
                ->with('info', 'Paiement traité avec succès.');
        }

        // Verify payment status with provider
        if ($subscription->payment_provider && $request->has('session_id')) {
            try {
                $provider = PaymentProviderFactory::make($subscription->payment_provider);
                $status = $provider->getPaymentStatus($request->get('session_id'));

                if ($status['status'] === 'paid' || $status['status'] === 'active') {
                    $subscription->update(['status' => 'active']);
                }
            } catch (\Exception $e) {
                Log::error('Payment verification failed', [
                    'subscription_id' => $subscriptionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        session()->forget('pending_subscription');

        return view('subscription.success', compact('subscription'));
    }

    /**
     * Payment cancelled callback
     */
    public function cancelPayment()
    {
        return view('subscription.cancel-payment');
    }

    /**
     * Start free trial.
     */
    public function startTrial(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|uuid|exists:subscription_plans,id',
        ]);

        $company = auth()->user()->companies()->find(session('current_tenant_id'));

        if (!$company) {
            return redirect()->route('tenant.select');
        }

        // Check if already had a trial
        if ($company->subscription && $company->subscription->trial_ends_at) {
            return redirect()->route('subscription.upgrade')
                ->with('error', 'Vous avez déjà utilisé votre période d\'essai gratuite.');
        }

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        $subscription = Subscription::createForCompany($company, $plan);
        $subscription->startTrial();

        return redirect()->route('dashboard')
            ->with('success', 'Votre période d\'essai de ' . $plan->trial_days . ' jours a commencé.');
    }

    /**
     * Cancel subscription.
     */
    public function cancel(Request $request)
    {
        $company = auth()->user()->companies()->find(session('current_tenant_id'));

        if (!$company || !$company->subscription) {
            return redirect()->route('subscription.show')
                ->with('error', 'Aucun abonnement à annuler.');
        }

        $subscription = $company->subscription;
        $reason = $request->input('reason', 'Annulé par le client');

        // If subscription has provider (recurring), cancel with provider
        if ($subscription->payment_provider && $subscription->provider_subscription_id) {
            try {
                $provider = PaymentProviderFactory::make($subscription->payment_provider);
                $provider->cancelSubscription($subscription->provider_subscription_id);

                Log::info('Subscription cancelled with provider', [
                    'provider' => $subscription->payment_provider,
                    'subscription_id' => $subscription->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Provider cancellation failed', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);

                return redirect()->route('subscription.show')
                    ->with('error', 'Une erreur est survenue lors de l\'annulation. Veuillez contacter le support.');
            }
        }

        // Cancel locally
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return redirect()->route('subscription.show')
            ->with('success', 'Votre abonnement a été annulé. Vous conservez l\'accès jusqu\'à la fin de la période payée.');
    }
}
