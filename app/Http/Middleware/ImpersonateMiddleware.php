<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ImpersonateMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si un superadmin est en train d'impersonate un utilisateur
        if (session()->has('impersonate_id')) {
            $impersonateId = session('impersonate_id');

            // Vérifier que l'utilisateur existe toujours
            $user = \App\Models\User::find($impersonateId);

            if ($user) {
                Auth::onceUsingId($impersonateId);
            } else {
                // L'utilisateur n'existe plus, arrêter l'impersonate
                session()->forget('impersonate_id');
                session()->forget('impersonate_by');
            }
        }

        return $next($request);
    }
}
