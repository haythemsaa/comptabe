<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Get current tenant from session
        $tenantId = session('current_tenant_id');

        // If no tenant selected, try to get user's default company
        if (!$tenantId) {
            $defaultCompany = $user->defaultCompany();

            if ($defaultCompany) {
                session(['current_tenant_id' => $defaultCompany->id]);
                $tenantId = $defaultCompany->id;
            } else {
                // User has no companies, redirect to company creation
                return redirect()->route('companies.create')
                    ->with('warning', 'Veuillez d\'abord créer ou rejoindre une entreprise.');
            }
        }

        // Verify user has access to this tenant
        if (!$user->hasAccessToCompany($tenantId)) {
            session()->forget('current_tenant_id');
            return redirect()->route('tenant.select')
                ->with('error', 'Vous n\'avez pas accès à cette entreprise.');
        }

        // Share current tenant with views
        $currentTenant = $user->companies()->find($tenantId);
        view()->share('currentTenant', $currentTenant);
        view()->share('userRole', $user->getRoleInCompany($tenantId));

        return $next($request);
    }
}
