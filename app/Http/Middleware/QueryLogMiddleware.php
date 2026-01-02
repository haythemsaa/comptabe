<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to log slow queries and detect N+1 issues.
 * Only active in development environment.
 */
class QueryLogMiddleware
{
    protected const SLOW_QUERY_THRESHOLD_MS = 100; // Log queries slower than 100ms
    protected const N_PLUS_ONE_THRESHOLD = 10;      // Warn if same query pattern runs > 10 times

    public function handle(Request $request, Closure $next): Response
    {
        // Only enable in development
        if (!config('app.debug') || !config('app.query_logging', false)) {
            return $next($request);
        }

        DB::enableQueryLog();

        $response = $next($request);

        $this->analyzeQueries($request);

        return $response;
    }

    protected function analyzeQueries(Request $request): void
    {
        $queries = DB::getQueryLog();

        if (empty($queries)) {
            return;
        }

        $slowQueries = [];
        $queryPatterns = [];

        foreach ($queries as $query) {
            $time = $query['time'];
            $sql = $query['query'];

            // Track slow queries
            if ($time > self::SLOW_QUERY_THRESHOLD_MS) {
                $slowQueries[] = [
                    'sql' => $sql,
                    'time' => $time,
                    'bindings' => $query['bindings'],
                ];
            }

            // Track query patterns for N+1 detection
            $pattern = $this->getQueryPattern($sql);
            $queryPatterns[$pattern] = ($queryPatterns[$pattern] ?? 0) + 1;
        }

        // Log slow queries
        if (!empty($slowQueries)) {
            Log::channel('queries')->warning('Slow queries detected', [
                'route' => $request->path(),
                'method' => $request->method(),
                'slow_queries' => $slowQueries,
            ]);
        }

        // Log potential N+1 issues
        $nPlusOnePatterns = array_filter($queryPatterns, fn($count) => $count > self::N_PLUS_ONE_THRESHOLD);

        if (!empty($nPlusOnePatterns)) {
            Log::channel('queries')->warning('Potential N+1 queries detected', [
                'route' => $request->path(),
                'method' => $request->method(),
                'patterns' => $nPlusOnePatterns,
            ]);
        }

        // Log query summary in debug
        if (config('app.query_summary', false)) {
            Log::channel('queries')->debug('Query summary', [
                'route' => $request->path(),
                'total_queries' => count($queries),
                'total_time' => array_sum(array_column($queries, 'time')),
            ]);
        }

        DB::flushQueryLog();
    }

    /**
     * Extract query pattern for N+1 detection.
     * Normalizes values in WHERE clauses.
     */
    protected function getQueryPattern(string $sql): string
    {
        // Remove specific values from WHERE clauses
        $pattern = preg_replace('/=\s*\?/', '= ?', $sql);
        $pattern = preg_replace('/in\s*\([^)]+\)/i', 'in (?)', $pattern);
        $pattern = preg_replace('/between\s+\?\s+and\s+\?/i', 'between ? and ?', $pattern);
        $pattern = preg_replace('/limit\s+\d+/i', 'limit ?', $pattern);
        $pattern = preg_replace('/offset\s+\d+/i', 'offset ?', $pattern);

        return $pattern;
    }
}
