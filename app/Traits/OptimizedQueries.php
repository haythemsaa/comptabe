<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Trait for optimized query patterns.
 * Use this trait in models that benefit from query optimization.
 */
trait OptimizedQueries
{
    /**
     * Scope to select only essential columns for list views.
     * Override in child model to customize columns.
     */
    public function scopeSelectEssential(Builder $query): Builder
    {
        $essentialColumns = $this->essentialColumns ?? ['id', 'name', 'created_at'];
        return $query->select($essentialColumns);
    }

    /**
     * Scope to eager load common relationships.
     * Override in child model to customize relationships.
     */
    public function scopeWithCommon(Builder $query): Builder
    {
        $commonRelations = $this->commonRelations ?? [];
        return $query->with($commonRelations);
    }

    /**
     * Scope for efficient pagination with count optimization.
     */
    public function scopeEfficientPaginate(Builder $query, int $perPage = 15, array $columns = ['*'])
    {
        // For large datasets, use cursor pagination for better performance
        if ($this->useCursorPagination ?? false) {
            return $query->cursorPaginate($perPage, $columns);
        }

        return $query->paginate($perPage, $columns);
    }

    /**
     * Scope to filter by date range efficiently.
     */
    public function scopeDateRange(Builder $query, string $column, $startDate, $endDate): Builder
    {
        if ($startDate && $endDate) {
            return $query->whereBetween($column, [$startDate, $endDate]);
        } elseif ($startDate) {
            return $query->where($column, '>=', $startDate);
        } elseif ($endDate) {
            return $query->where($column, '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope for full-text search (MySQL).
     * Requires FULLTEXT index on the column.
     */
    public function scopeFullTextSearch(Builder $query, string $column, string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        // Validate column name against table columns to prevent SQL injection
        if (!in_array($column, $this->getFillable(), true) && $column !== 'name' && $column !== 'description') {
            throw new \InvalidArgumentException('Invalid column name for full-text search.');
        }

        // Escape special characters
        $search = preg_replace('/[^\p{L}\p{N}\s]/u', '', $search);

        if (config('database.default') === 'mysql') {
            // Use backticks to safely quote the column name
            $quotedColumn = '`' . str_replace('`', '``', $column) . '`';
            return $query->whereRaw(
                "MATCH({$quotedColumn}) AGAINST(? IN BOOLEAN MODE)",
                ["+{$search}*"]
            );
        }

        // Fallback for other databases
        return $query->where($column, 'LIKE', "%{$search}%");
    }

    /**
     * Get aggregated count by a column, cached.
     */
    public static function getCachedCountBy(string $column, int $ttl = 300): array
    {
        $cacheKey = static::class . ':count_by:' . $column;

        return cache()->remember($cacheKey, $ttl, function () use ($column) {
            return static::query()
                ->select($column, DB::raw('COUNT(*) as count'))
                ->groupBy($column)
                ->pluck('count', $column)
                ->toArray();
        });
    }

    /**
     * Get sum by column, cached.
     * Column names are validated against fillable attributes to prevent SQL injection.
     */
    public static function getCachedSumBy(string $sumColumn, string $groupColumn, int $ttl = 300): array
    {
        // Create instance to access fillable columns
        $instance = new static();
        $fillable = $instance->getFillable();

        // Validate column names against fillable to prevent SQL injection
        if (!in_array($sumColumn, $fillable, true) || !in_array($groupColumn, $fillable, true)) {
            throw new \InvalidArgumentException('Invalid column name for aggregation query.');
        }

        $cacheKey = static::class . ':sum_by:' . $sumColumn . ':' . $groupColumn;

        // Quote column names safely
        $quotedSumColumn = '`' . str_replace('`', '``', $sumColumn) . '`';

        return cache()->remember($cacheKey, $ttl, function () use ($quotedSumColumn, $groupColumn) {
            return static::query()
                ->select($groupColumn, DB::raw("SUM({$quotedSumColumn}) as total"))
                ->groupBy($groupColumn)
                ->pluck('total', $groupColumn)
                ->toArray();
        });
    }

    /**
     * Chunk with memory optimization for large exports.
     */
    public static function chunkOptimized(int $chunkSize, callable $callback): bool
    {
        // Disable query log for memory optimization
        DB::disableQueryLog();

        // Use lazy collection for memory efficiency
        return static::query()
            ->lazyById($chunkSize)
            ->each(function ($model) use ($callback) {
                $callback($model);

                // Clear model reference to free memory
                unset($model);
            });
    }

    /**
     * Get records updated since a specific time, for sync operations.
     */
    public function scopeUpdatedSince(Builder $query, $timestamp): Builder
    {
        return $query->where('updated_at', '>', $timestamp);
    }

    /**
     * Scope for tenant context (multi-tenant).
     */
    public function scopeForCurrentTenant(Builder $query): Builder
    {
        $tenantId = session('current_tenant_id');

        if ($tenantId && in_array('company_id', $this->getFillable())) {
            return $query->where('company_id', $tenantId);
        }

        return $query;
    }

    /**
     * Get indexed columns for this model.
     * Useful for building efficient queries.
     */
    public static function getIndexedColumns(): array
    {
        return static::$indexedColumns ?? [];
    }

    /**
     * Check if a query would benefit from an index.
     */
    public static function hasIndexFor(string $column): bool
    {
        return in_array($column, static::getIndexedColumns());
    }
}
