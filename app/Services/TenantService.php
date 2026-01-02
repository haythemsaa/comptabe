<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;

class TenantService
{
    /**
     * Get current tenant from session.
     */
    public function current(): ?Company
    {
        $tenantId = session('current_tenant_id');

        if (!$tenantId) {
            return null;
        }

        return Company::find($tenantId);
    }

    /**
     * Get current tenant ID.
     */
    public function currentId(): ?string
    {
        return session('current_tenant_id');
    }

    /**
     * Set current tenant.
     */
    public function setCurrent(Company|string $tenant): void
    {
        $tenantId = $tenant instanceof Company ? $tenant->id : $tenant;
        session(['current_tenant_id' => $tenantId]);
    }

    /**
     * Clear current tenant.
     */
    public function clearCurrent(): void
    {
        session()->forget('current_tenant_id');
    }

    /**
     * Check if user has access to tenant.
     */
    public function userHasAccess(User $user, Company|string $tenant): bool
    {
        $tenantId = $tenant instanceof Company ? $tenant->id : $tenant;
        return $user->companies()->where('companies.id', $tenantId)->exists();
    }

    /**
     * Get user's role in current tenant.
     */
    public function getUserRole(User $user): ?string
    {
        $tenantId = $this->currentId();

        if (!$tenantId) {
            return null;
        }

        return $user->getRoleInCompany($tenantId);
    }

    /**
     * Check if user is admin in current tenant.
     */
    public function isAdmin(User $user): bool
    {
        $role = $this->getUserRole($user);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Check if user is accountant in current tenant.
     */
    public function isAccountant(User $user): bool
    {
        return $this->getUserRole($user) === 'accountant';
    }

    /**
     * Get all tenants for user.
     */
    public function getTenantsForUser(User $user)
    {
        return $user->companies;
    }

    /**
     * Switch tenant for user.
     */
    public function switchTenant(User $user, Company|string $tenant): bool
    {
        if (!$this->userHasAccess($user, $tenant)) {
            return false;
        }

        $this->setCurrent($tenant);
        return true;
    }
}
