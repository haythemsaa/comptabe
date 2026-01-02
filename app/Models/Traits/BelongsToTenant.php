<?php

namespace App\Models\Traits;

use App\Models\Company;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Boot the trait.
     */
    protected static function bootBelongsToTenant(): void
    {
        // Add global scope to filter by tenant
        static::addGlobalScope(new TenantScope);

        // Automatically set tenant_id on creating
        static::creating(function ($model) {
            if (empty($model->company_id) && $tenantId = session('current_tenant_id')) {
                $model->company_id = $tenantId;
            }
        });
    }

    /**
     * Get the company that owns this model.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope a query to a specific tenant.
     */
    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('company_id', $tenantId);
    }

    /**
     * Remove tenant scope for this query.
     */
    public function scopeWithoutTenant($query)
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
