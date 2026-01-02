<?php

namespace App\Http\Middleware;

use App\Models\ClientAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientPortalAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Get company from route or session
        $companyId = $request->route('company') ?? session('client_portal_company_id');

        if (!$companyId) {
            abort(403, 'Aucune entreprise sélectionnée');
        }

        // Check if user has access to this company
        $access = ClientAccess::where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->first();

        if (!$access) {
            // Check if user is owner/admin of company (internal access)
            if (!$user->hasAccessToCompany($companyId)) {
                abort(403, 'Accès non autorisé à cette entreprise');
            }
        } else {
            // Record access
            $access->recordAccess();

            // Store access in request for use in controllers
            $request->merge(['client_access' => $access]);

            // Check specific permission if provided
            if ($permission && !$access->hasPermission($permission)) {
                abort(403, 'Permission insuffisante: ' . $permission);
            }
        }

        return $next($request);
    }
}
