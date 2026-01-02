<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleEnabled
{
    /**
     * Handle an incoming request.
     *
     * Check if the specified module is enabled for the current company.
     * Usage: Route::middleware('module:crm') or Route::middleware('module:invoices,quotes')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$modules  One or more module codes to check (any must be enabled)
     */
    public function handle(Request $request, Closure $next, string ...$modules): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $company = $user->currentCompany;

        if (!$company) {
            return redirect()->route('tenant.select')
                ->with('error', 'Veuillez sélectionner une entreprise.');
        }

        // Check if any of the specified modules is enabled
        $hasAccess = false;
        foreach ($modules as $moduleCode) {
            if ($company->hasModule($moduleCode)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            // Store intended URL for after activation
            session(['module_redirect_after' => $request->fullUrl()]);

            $moduleNames = implode(', ', $modules);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Module non activé',
                    'message' => "Le module requis ($moduleNames) n'est pas activé pour votre entreprise.",
                    'redirect' => route('modules.marketplace')
                ], 403);
            }

            return redirect()->route('modules.marketplace')
                ->with('error', "Le module requis ($moduleNames) n'est pas activé. Demandez son activation ci-dessous.");
        }

        return $next($request);
    }
}
