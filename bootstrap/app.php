<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'tenant' => \App\Http\Middleware\TenantMiddleware::class,
            'superadmin' => \App\Http\Middleware\SuperadminMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'subscription' => \App\Http\Middleware\EnsureActiveSubscription::class,
            'subscription.feature' => \App\Http\Middleware\CheckSubscriptionFeature::class,
            'subscription.limit' => \App\Http\Middleware\CheckSubscriptionLimit::class,
            'module' => \App\Http\Middleware\CheckModuleEnabled::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        // Exclude specific payment webhooks from CSRF verification
        // SECURITY: Only exempt specific webhook endpoints, not all webhooks
        $middleware->validateCsrfTokens(except: [
            'webhooks/mollie',
            'webhooks/stripe',
            'webhooks/peppol/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
