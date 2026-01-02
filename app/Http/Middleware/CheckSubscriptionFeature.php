<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionFeature
{
    /**
     * Handle an incoming request.
     * Checks if the current plan includes a specific feature.
     *
     * @param string $feature The feature to check (peppol, recurring_invoices, quotes, api_access, etc.)
     */
    public function handle(Request $request, Closure $next, string $feature): Response
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
            return redirect()->route('tenant.select');
        }

        // Check if the feature is available in the current plan
        if (!$company->hasFeature($feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'feature_not_available',
                    'message' => 'Cette fonctionnalité n\'est pas disponible dans votre plan actuel.',
                    'feature' => $feature,
                    'upgrade_url' => route('subscription.upgrade'),
                ], 403);
            }

            return redirect()->route('subscription.upgrade')
                ->with('feature_required', $feature)
                ->with('error', 'Cette fonctionnalité nécessite un plan supérieur.');
        }

        return $next($request);
    }
}
