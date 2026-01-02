<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionLimit
{
    /**
     * Handle an incoming request.
     * Checks if the user can create a new resource based on plan limits.
     *
     * @param string $resource The resource type to check (invoices, clients, products, users)
     */
    public function handle(Request $request, Closure $next, string $resource): Response
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

        // Check if the resource limit is reached
        if (!$company->canCreate($resource)) {
            $plan = $company->plan;
            $limitName = $this->getLimitDisplayName($resource);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'limit_reached',
                    'message' => "Vous avez atteint la limite de {$limitName} pour votre plan.",
                    'resource' => $resource,
                    'upgrade_url' => route('subscription.upgrade'),
                ], 403);
            }

            return redirect()->back()
                ->with('limit_reached', $resource)
                ->with('error', "Vous avez atteint la limite de {$limitName} pour votre plan. Passez à un plan supérieur pour continuer.");
        }

        return $next($request);
    }

    /**
     * Get human-readable name for the resource limit.
     */
    private function getLimitDisplayName(string $resource): string
    {
        return match ($resource) {
            'invoices' => 'factures ce mois',
            'clients' => 'clients',
            'products' => 'produits',
            'users' => 'utilisateurs',
            default => $resource,
        };
    }
}
