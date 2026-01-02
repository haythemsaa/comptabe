<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from session, user preference, or default
        $locale = session('locale', config('app.locale', 'fr'));

        // Validate locale
        if (!in_array($locale, ['fr', 'nl', 'en'])) {
            $locale = 'fr';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
