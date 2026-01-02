<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Centralized cache service for the application.
 * Handles tenant-aware caching with consistent TTLs and cache invalidation.
 */
class CacheService
{
    // Cache TTL constants (in seconds)
    public const TTL_SHORT = 60;          // 1 minute - frequently changing data
    public const TTL_MEDIUM = 300;        // 5 minutes - metrics, counts
    public const TTL_LONG = 3600;         // 1 hour - chart data, reports
    public const TTL_DAY = 86400;         // 24 hours - static data
    public const TTL_WEEK = 604800;       // 7 days - rarely changing data

    // Cache prefixes for different data types
    public const PREFIX_DASHBOARD = 'dashboard';
    public const PREFIX_INVOICE = 'invoice';
    public const PREFIX_PARTNER = 'partner';
    public const PREFIX_VAT = 'vat';
    public const PREFIX_BANK = 'bank';
    public const PREFIX_ANALYTICS = 'analytics';
    public const PREFIX_PEPPOL = 'peppol';
    public const PREFIX_SETTINGS = 'settings';

    protected ?int $tenantId = null;

    public function __construct()
    {
        $this->tenantId = session('current_tenant_id');
    }

    /**
     * Set tenant context.
     */
    public function forTenant(int $tenantId): self
    {
        $this->tenantId = $tenantId;
        return $this;
    }

    /**
     * Generate cache key with tenant context.
     */
    public function key(string $prefix, string $key): string
    {
        $tenant = $this->tenantId ?? 'global';
        return "{$prefix}:{$tenant}:{$key}";
    }

    /**
     * Get cached value or compute and store.
     */
    public function remember(string $prefix, string $key, int $ttl, callable $callback)
    {
        $cacheKey = $this->key($prefix, $key);
        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Get cached value or null.
     */
    public function get(string $prefix, string $key)
    {
        return Cache::get($this->key($prefix, $key));
    }

    /**
     * Store value in cache.
     */
    public function put(string $prefix, string $key, $value, int $ttl = null): bool
    {
        return Cache::put($this->key($prefix, $key), $value, $ttl ?? self::TTL_MEDIUM);
    }

    /**
     * Remove value from cache.
     */
    public function forget(string $prefix, string $key): bool
    {
        return Cache::forget($this->key($prefix, $key));
    }

    /**
     * Flush all cache for a specific prefix.
     */
    public function flushPrefix(string $prefix): void
    {
        $pattern = $this->key($prefix, '*');

        // For Redis/Memcached with tagging support
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags([$prefix, "tenant:{$this->tenantId}"])->flush();
        } else {
            // For file/array driver - log warning as we can't efficiently flush by pattern
            Log::warning("Cache flush by pattern not supported for current driver. Pattern: {$pattern}");
        }
    }

    /**
     * Flush all cache for current tenant.
     */
    public function flushTenant(): void
    {
        if (!$this->tenantId) {
            return;
        }

        $prefixes = [
            self::PREFIX_DASHBOARD,
            self::PREFIX_INVOICE,
            self::PREFIX_PARTNER,
            self::PREFIX_VAT,
            self::PREFIX_BANK,
            self::PREFIX_ANALYTICS,
            self::PREFIX_SETTINGS,
        ];

        foreach ($prefixes as $prefix) {
            $this->flushPrefix($prefix);
        }

        Log::info('Tenant cache flushed', ['tenant_id' => $this->tenantId]);
    }

    // =========================================================================
    // Specialized cache methods for common patterns
    // =========================================================================

    /**
     * Get cached dashboard metrics.
     */
    public function dashboardMetrics(callable $callback)
    {
        return $this->remember(self::PREFIX_DASHBOARD, 'metrics', self::TTL_MEDIUM, $callback);
    }

    /**
     * Get cached revenue chart data.
     */
    public function revenueChart(callable $callback)
    {
        return $this->remember(self::PREFIX_DASHBOARD, 'revenue_chart', self::TTL_LONG, $callback);
    }

    /**
     * Get cached invoice statistics.
     */
    public function invoiceStats(string $type, callable $callback)
    {
        return $this->remember(self::PREFIX_INVOICE, "stats:{$type}", self::TTL_MEDIUM, $callback);
    }

    /**
     * Get cached partner list.
     */
    public function partnerList(string $type, callable $callback)
    {
        return $this->remember(self::PREFIX_PARTNER, "list:{$type}", self::TTL_MEDIUM, $callback);
    }

    /**
     * Get cached VAT rates.
     */
    public function vatRates(callable $callback)
    {
        return $this->remember(self::PREFIX_VAT, 'rates', self::TTL_DAY, $callback);
    }

    /**
     * Get cached company settings.
     */
    public function companySettings(callable $callback)
    {
        return $this->remember(self::PREFIX_SETTINGS, 'company', self::TTL_LONG, $callback);
    }

    /**
     * Get cached Peppol directory lookup.
     */
    public function peppolLookup(string $identifier, callable $callback)
    {
        $key = 'lookup:' . md5($identifier);
        return $this->remember(self::PREFIX_PEPPOL, $key, self::TTL_DAY, $callback);
    }

    /**
     * Get cached analytics data.
     */
    public function analytics(string $type, string $period, callable $callback)
    {
        $key = "{$type}:{$period}";
        return $this->remember(self::PREFIX_ANALYTICS, $key, self::TTL_LONG, $callback);
    }

    // =========================================================================
    // Cache invalidation helpers
    // =========================================================================

    /**
     * Invalidate invoice-related caches.
     */
    public function invalidateInvoice(): void
    {
        $this->forget(self::PREFIX_DASHBOARD, 'metrics');
        $this->forget(self::PREFIX_DASHBOARD, 'revenue_chart');
        $this->flushPrefix(self::PREFIX_INVOICE);
        $this->flushPrefix(self::PREFIX_ANALYTICS);
    }

    /**
     * Invalidate partner-related caches.
     */
    public function invalidatePartner(): void
    {
        $this->flushPrefix(self::PREFIX_PARTNER);
    }

    /**
     * Invalidate bank-related caches.
     */
    public function invalidateBank(): void
    {
        $this->forget(self::PREFIX_DASHBOARD, 'metrics');
        $this->flushPrefix(self::PREFIX_BANK);
    }

    /**
     * Invalidate VAT-related caches.
     */
    public function invalidateVat(): void
    {
        $this->flushPrefix(self::PREFIX_VAT);
    }

    /**
     * Invalidate settings cache.
     */
    public function invalidateSettings(): void
    {
        $this->flushPrefix(self::PREFIX_SETTINGS);
    }
}
