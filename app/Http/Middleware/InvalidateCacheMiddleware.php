<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cache Invalidation Middleware
 *
 * Automatically invalidates relevant cache keys when data is modified.
 * Runs after the response to avoid impacting request performance.
 */
class InvalidateCacheMiddleware
{
    /**
     * Routes that should trigger cache invalidation.
     * Format: 'route_pattern' => ['cache_keys_to_invalidate']
     */
    protected array $invalidationRules = [
        // Invoice operations
        'invoices.store' => ['dashboard:*:metrics', 'dashboard:*:revenue_chart', 'dashboard:*:top_clients'],
        'invoices.update' => ['dashboard:*:metrics', 'dashboard:*:revenue_chart'],
        'invoices.destroy' => ['dashboard:*:metrics', 'dashboard:*:revenue_chart'],
        'invoices.mark-paid' => ['dashboard:*:metrics', 'dashboard:*:cash_flow'],

        // Partner operations
        'partners.store' => ['partners:*:active'],
        'partners.update' => ['partners:*:active'],
        'partners.destroy' => ['partners:*:active', 'dashboard:*:top_clients'],

        // Bank operations
        'bank.transactions.reconcile' => ['dashboard:*:metrics', 'dashboard:*:cash_flow'],
        'bank.statements.import' => ['dashboard:*:metrics'],

        // Account operations
        'accounts.store' => ['accounts:*:all'],
        'accounts.update' => ['accounts:*:all'],
        'accounts.destroy' => ['accounts:*:all'],

        // VAT operations
        'vat.declarations.submit' => ['dashboard:*:metrics'],
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only invalidate on successful POST, PUT, PATCH, DELETE requests
        if ($this->shouldInvalidateCache($request, $response)) {
            $this->invalidateCache($request);
        }

        return $response;
    }

    /**
     * Determine if cache should be invalidated.
     */
    protected function shouldInvalidateCache(Request $request, Response $response): bool
    {
        // Only invalidate on write operations
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return false;
        }

        // Only invalidate on successful responses
        if ($response->getStatusCode() >= 400) {
            return false;
        }

        // Check if route matches any invalidation rules
        $routeName = $request->route()?->getName();
        if (!$routeName) {
            return false;
        }

        foreach ($this->invalidationRules as $pattern => $keys) {
            if ($this->matchesPattern($routeName, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Invalidate cache based on the request.
     */
    protected function invalidateCache(Request $request): void
    {
        $routeName = $request->route()?->getName();
        $tenantId = session('current_tenant_id', auth()->user()?->current_company_id);

        foreach ($this->invalidationRules as $pattern => $cachePatterns) {
            if ($this->matchesPattern($routeName, $pattern)) {
                foreach ($cachePatterns as $cachePattern) {
                    $this->invalidateCachePattern($cachePattern, $tenantId);
                }
            }
        }
    }

    /**
     * Invalidate cache keys matching a pattern.
     */
    protected function invalidateCachePattern(string $pattern, ?string $tenantId): void
    {
        // Replace wildcard with tenant ID if available
        if ($tenantId && str_contains($pattern, '*')) {
            $pattern = str_replace('*', $tenantId, $pattern);
        }

        // If pattern contains wildcards, we need to find all matching keys
        if (str_contains($pattern, '*')) {
            $this->invalidateWildcardPattern($pattern);
        } else {
            // Direct key invalidation
            Cache::forget($pattern);
        }
    }

    /**
     * Invalidate all keys matching a wildcard pattern.
     */
    protected function invalidateWildcardPattern(string $pattern): void
    {
        $cacheDriver = config('cache.default');

        if ($cacheDriver === 'redis') {
            $this->invalidateRedisPattern($pattern);
        } elseif ($cacheDriver === 'database') {
            $this->invalidateDatabasePattern($pattern);
        }
        // For other drivers (file, array), we can't easily match patterns
    }

    /**
     * Invalidate Redis keys matching pattern.
     */
    protected function invalidateRedisPattern(string $pattern): void
    {
        try {
            $redis = \Illuminate\Support\Facades\Redis::connection('cache');
            $prefix = config('cache.prefix');
            $fullPattern = $prefix . str_replace('*', '*', $pattern);

            $keys = $redis->keys($fullPattern);

            if (!empty($keys)) {
                foreach ($keys as $key) {
                    $redis->del($key);
                }
            }
        } catch (\Exception $e) {
            // Silently fail - cache invalidation shouldn't break the request
            \Illuminate\Support\Facades\Log::warning('Cache invalidation failed', [
                'pattern' => $pattern,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Invalidate database cache keys matching pattern.
     */
    protected function invalidateDatabasePattern(string $pattern): void
    {
        try {
            $cacheTable = config('cache.stores.database.table', 'cache');

            // Convert pattern to SQL LIKE pattern
            $likePattern = str_replace('*', '%', $pattern);

            \Illuminate\Support\Facades\DB::table($cacheTable)
                ->where('key', 'LIKE', $likePattern)
                ->delete();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Cache invalidation failed', [
                'pattern' => $pattern,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if route name matches pattern.
     */
    protected function matchesPattern(string $routeName, string $pattern): bool
    {
        // Exact match
        if ($routeName === $pattern) {
            return true;
        }

        // Wildcard match
        if (str_contains($pattern, '*')) {
            $regex = '/^' . str_replace('*', '.*', preg_quote($pattern, '/')) . '$/';
            return (bool) preg_match($regex, $routeName);
        }

        return false;
    }
}
