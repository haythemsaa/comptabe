<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

/**
 * Cache Dashboard Controller
 *
 * Provides cache monitoring, management, and optimization tools.
 * Supports both Database and Redis cache drivers.
 */
class CacheDashboardController extends Controller
{
    /**
     * Display the cache dashboard.
     */
    public function index()
    {
        $cacheDriver = config('cache.default');
        $metrics = $this->getCacheMetrics($cacheDriver);
        $topKeys = $this->getTopCacheKeys();
        $hitRate = $this->calculateHitRate();

        return view('admin.cache.dashboard', compact(
            'cacheDriver',
            'metrics',
            'topKeys',
            'hitRate'
        ));
    }

    /**
     * Get cache metrics based on driver.
     */
    protected function getCacheMetrics(string $driver): array
    {
        if ($driver === 'redis') {
            return $this->getRedisMetrics();
        } elseif ($driver === 'database') {
            return $this->getDatabaseCacheMetrics();
        } else {
            return $this->getGenericCacheMetrics();
        }
    }

    /**
     * Get Redis cache metrics.
     */
    protected function getRedisMetrics(): array
    {
        try {
            $redis = Redis::connection('cache');
            $info = $redis->info();

            // Parse Redis INFO response
            $memory = $info['used_memory_human'] ?? 'N/A';
            $memoryPeak = $info['used_memory_peak_human'] ?? 'N/A';
            $connectedClients = $info['connected_clients'] ?? 0;
            $totalKeys = 0;

            // Count keys by pattern
            $prefix = config('cache.prefix');
            $keys = $redis->keys("{$prefix}*");
            $totalKeys = count($keys);

            // Get hit/miss stats
            $hits = $info['keyspace_hits'] ?? 0;
            $misses = $info['keyspace_misses'] ?? 0;
            $totalCommands = $hits + $misses;
            $hitRatePercent = $totalCommands > 0
                ? round(($hits / $totalCommands) * 100, 2)
                : 0;

            // Get evicted keys
            $evictedKeys = $info['evicted_keys'] ?? 0;

            // Get uptime
            $uptimeSeconds = $info['uptime_in_seconds'] ?? 0;
            $uptime = $this->formatUptime($uptimeSeconds);

            return [
                'type' => 'redis',
                'status' => 'connected',
                'memory_used' => $memory,
                'memory_peak' => $memoryPeak,
                'total_keys' => $totalKeys,
                'connected_clients' => $connectedClients,
                'hit_rate' => $hitRatePercent,
                'hits' => $hits,
                'misses' => $misses,
                'evicted_keys' => $evictedKeys,
                'uptime' => $uptime,
                'version' => $info['redis_version'] ?? 'Unknown',
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'redis',
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get database cache metrics.
     */
    protected function getDatabaseCacheMetrics(): array
    {
        try {
            $cacheTable = config('cache.stores.database.table', 'cache');

            // Total keys
            $totalKeys = DB::table($cacheTable)->count();

            // Expired keys (not yet cleaned)
            $expiredKeys = DB::table($cacheTable)
                ->where('expiration', '<', now()->timestamp)
                ->count();

            // Valid keys
            $validKeys = $totalKeys - $expiredKeys;

            // Table size (approximate)
            $tableSize = DB::select("
                SELECT
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                FROM information_schema.TABLES
                WHERE table_schema = DATABASE()
                AND table_name = ?
            ", [$cacheTable]);

            $sizeInMb = $tableSize[0]->size_mb ?? 0;

            // Get oldest and newest entries
            $stats = DB::table($cacheTable)
                ->selectRaw('MIN(expiration) as oldest, MAX(expiration) as newest')
                ->first();

            return [
                'type' => 'database',
                'status' => 'connected',
                'total_keys' => $totalKeys,
                'valid_keys' => $validKeys,
                'expired_keys' => $expiredKeys,
                'table_size_mb' => $sizeInMb,
                'oldest_expiration' => $stats->oldest ? date('Y-m-d H:i:s', $stats->oldest) : 'N/A',
                'newest_expiration' => $stats->newest ? date('Y-m-d H:i:s', $stats->newest) : 'N/A',
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'database',
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get generic cache metrics (file, array, etc.).
     */
    protected function getGenericCacheMetrics(): array
    {
        return [
            'type' => config('cache.default'),
            'status' => 'active',
            'message' => 'Limited metrics available for this cache driver',
        ];
    }

    /**
     * Get top cache keys by size or access pattern.
     */
    protected function getTopCacheKeys(): array
    {
        $driver = config('cache.default');

        if ($driver === 'database') {
            return $this->getTopDatabaseCacheKeys();
        } elseif ($driver === 'redis') {
            return $this->getTopRedisKeys();
        }

        return [];
    }

    /**
     * Get top cache keys from database.
     */
    protected function getTopDatabaseCacheKeys(): array
    {
        try {
            $cacheTable = config('cache.stores.database.table', 'cache');

            return DB::table($cacheTable)
                ->select('key')
                ->selectRaw('LENGTH(value) as size_bytes')
                ->selectRaw('FROM_UNIXTIME(expiration) as expires_at')
                ->where('expiration', '>', now()->timestamp)
                ->orderByDesc('size_bytes')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'key' => $item->key,
                        'size' => $this->formatBytes($item->size_bytes),
                        'size_bytes' => $item->size_bytes,
                        'expires_at' => $item->expires_at,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get top Redis keys by memory usage.
     */
    protected function getTopRedisKeys(): array
    {
        try {
            $redis = Redis::connection('cache');
            $prefix = config('cache.prefix');
            $keys = $redis->keys("{$prefix}*");

            $keyInfo = [];

            foreach (array_slice($keys, 0, 50) as $key) {
                $ttl = $redis->ttl($key);
                $type = $redis->type($key);

                // Estimate size (debug object not always available)
                $size = strlen($redis->get($key) ?? '');

                $keyInfo[] = [
                    'key' => str_replace($prefix, '', $key),
                    'type' => $type,
                    'size' => $this->formatBytes($size),
                    'size_bytes' => $size,
                    'ttl' => $ttl > 0 ? $this->formatTtl($ttl) : 'No expiry',
                ];
            }

            // Sort by size
            usort($keyInfo, fn($a, $b) => $b['size_bytes'] <=> $a['size_bytes']);

            return array_slice($keyInfo, 0, 10);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Calculate cache hit rate (simplified).
     */
    protected function calculateHitRate(): array
    {
        $driver = config('cache.default');

        if ($driver === 'redis') {
            try {
                $redis = Redis::connection('cache');
                $info = $redis->info();

                $hits = $info['keyspace_hits'] ?? 0;
                $misses = $info['keyspace_misses'] ?? 0;
                $total = $hits + $misses;

                return [
                    'hits' => $hits,
                    'misses' => $misses,
                    'total' => $total,
                    'rate' => $total > 0 ? round(($hits / $total) * 100, 2) : 0,
                ];
            } catch (\Exception $e) {
                return ['error' => $e->getMessage()];
            }
        }

        return [
            'hits' => 'N/A',
            'misses' => 'N/A',
            'total' => 'N/A',
            'rate' => 'N/A',
        ];
    }

    /**
     * Clear all cache.
     */
    public function clear(Request $request)
    {
        try {
            Cache::flush();

            return back()->with('success', 'Cache vidé avec succès');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du vidage du cache: ' . $e->getMessage());
        }
    }

    /**
     * Clear specific cache key.
     */
    public function clearKey(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
        ]);

        try {
            Cache::forget($request->key);

            return back()->with('success', "Clé '{$request->key}' supprimée");
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Warm up cache with frequently accessed data.
     */
    public function warmup(Request $request)
    {
        try {
            Artisan::call('cache:warmup');

            return back()->with('success', 'Cache préchauffé avec succès');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Optimize cache (clear expired, compact, etc.).
     */
    public function optimize(Request $request)
    {
        try {
            $driver = config('cache.default');

            if ($driver === 'database') {
                // Clear expired cache entries
                $cacheTable = config('cache.stores.database.table', 'cache');
                $deleted = DB::table($cacheTable)
                    ->where('expiration', '<', now()->timestamp)
                    ->delete();

                return back()->with('success', "{$deleted} entrée(s) expirée(s) supprimée(s)");
            } elseif ($driver === 'redis') {
                // Redis handles expiration automatically, but we can trigger cleanup
                Artisan::call('cache:clear');
                Artisan::call('cache:warmup');

                return back()->with('success', 'Cache Redis optimisé');
            }

            return back()->with('info', 'Aucune optimisation nécessaire pour ce driver');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Format bytes to human readable.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }

    /**
     * Format TTL to human readable.
     */
    protected function formatTtl(int $seconds): string
    {
        if ($seconds >= 86400) {
            return round($seconds / 86400, 1) . ' jours';
        } elseif ($seconds >= 3600) {
            return round($seconds / 3600, 1) . ' heures';
        } elseif ($seconds >= 60) {
            return round($seconds / 60, 1) . ' minutes';
        }

        return $seconds . ' secondes';
    }

    /**
     * Format uptime to human readable.
     */
    protected function formatUptime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $parts = [];
        if ($days > 0) $parts[] = "{$days}j";
        if ($hours > 0) $parts[] = "{$hours}h";
        if ($minutes > 0) $parts[] = "{$minutes}m";

        return implode(' ', $parts) ?: '0m';
    }
}
