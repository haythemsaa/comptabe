<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * SECURITY: Ensures multi-tenant data isolation by:
     * 1. Checking user authentication
     * 2. Validating user has access to current tenant
     * 3. Filtering all queries by company_id
     */
    public function apply(Builder $builder, Model $model): void
    {
        // SECURITY: Only apply tenant filtering for authenticated users
        if (!auth()->check()) {
            return;
        }

        $tenantId = session('current_tenant_id');

        // If no tenant selected, don't filter (will be handled by middleware)
        if (!$tenantId) {
            return;
        }

        // SECURITY: Verify user has access to this tenant
        $user = auth()->user();

        // Allow superadmin to bypass tenant scope for admin operations
        if ($user->is_superadmin ?? false) {
            return;
        }

        // Verify user has access to the current tenant
        if (!$user->hasAccessToCompany($tenantId)) {
            // SECURITY: User trying to access unauthorized tenant
            // Force logout and throw exception
            \Log::warning('Unauthorized tenant access attempt', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'attempted_tenant' => $tenantId,
                'user_ip' => request()->ip(),
            ]);

            // Clear malicious session
            session()->forget('current_tenant_id');

            throw new \Illuminate\Auth\Access\AuthorizationException(
                'Unauthorized access to company data.'
            );
        }

        // Apply tenant filter
        $builder->where($model->getTable() . '.company_id', $tenantId);
    }
}
