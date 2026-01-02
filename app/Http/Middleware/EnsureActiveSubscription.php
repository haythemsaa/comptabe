<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    /**
     * Handle an incoming request.
     * Ensures the current tenant has an active subscription.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $tenantId = session('current_tenant_id');

        if (!$tenantId) {
            return redirect()->route('tenant.select');
        }

        $company = $user->companies()->find($tenantId);

        if (!$company) {
            return redirect()->route('tenant.select')
                ->with('error', 'Entreprise non trouvée.');
        }

        // Check if company has an active subscription
        if (!$company->hasActiveSubscription()) {
            // If subscription exists but is expired/suspended
            if ($company->subscription) {
                $status = $company->subscription->status;

                if ($status === 'suspended') {
                    return redirect()->route('subscription.suspended');
                }

                if ($status === 'expired' || $status === 'cancelled') {
                    return redirect()->route('subscription.expired');
                }

                if ($status === 'past_due') {
                    // Allow access but show warning
                    session()->flash('subscription_warning', 'Votre paiement est en retard. Veuillez régulariser votre situation.');
                    return $next($request);
                }
            }

            // No subscription at all
            return redirect()->route('subscription.required');
        }

        // Share subscription info with views
        view()->share('currentSubscription', $company->subscription);
        view()->share('currentPlan', $company->plan);

        return $next($request);
    }
}
