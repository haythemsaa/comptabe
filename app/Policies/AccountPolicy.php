<?php

namespace App\Policies;

use App\Models\ChartOfAccount;
use App\Models\User;

class AccountPolicy
{
    /**
     * Determine whether the user can view any accounts.
     */
    public function viewAny(User $user): bool
    {
        return $user->current_company_id !== null;
    }

    /**
     * Determine whether the user can view the account.
     */
    public function view(User $user, ChartOfAccount $account): bool
    {
        return $account->company_id === $user->current_company_id;
    }

    /**
     * Determine whether the user can create accounts.
     * Only accountants and admins can create accounts in the chart of accounts.
     */
    public function create(User $user): bool
    {
        if (!$user->current_company_id) {
            return false;
        }

        $role = $user->getRoleInCompany($user->current_company_id);
        return in_array($role, ['owner', 'admin', 'accountant']);
    }

    /**
     * Determine whether the user can update the account.
     * Only accountants and admins can modify the chart of accounts.
     */
    public function update(User $user, ChartOfAccount $account): bool
    {
        if ($account->company_id !== $user->current_company_id) {
            return false;
        }

        $role = $user->getRoleInCompany($user->current_company_id);
        return in_array($role, ['owner', 'admin', 'accountant']);
    }

    /**
     * Determine whether the user can delete the account.
     * Only owners and admins can delete accounts.
     */
    public function delete(User $user, ChartOfAccount $account): bool
    {
        if ($account->company_id !== $user->current_company_id) {
            return false;
        }

        // Cannot delete accounts with existing transactions
        if ($account->journal_entries_count > 0) {
            return false;
        }

        // Only owners and admins can delete
        return $user->isAdminInCurrentTenant();
    }

    /**
     * Determine whether the user can archive the account.
     * Only owners can archive accounts (soft delete equivalent).
     */
    public function archive(User $user, ChartOfAccount $account): bool
    {
        if ($account->company_id !== $user->current_company_id) {
            return false;
        }

        $role = $user->getRoleInCompany($user->current_company_id);
        return $role === 'owner';
    }

    /**
     * Determine whether the user can restore an archived account.
     */
    public function restore(User $user, ChartOfAccount $account): bool
    {
        if ($account->company_id !== $user->current_company_id) {
            return false;
        }

        $role = $user->getRoleInCompany($user->current_company_id);
        return in_array($role, ['owner', 'admin', 'accountant']);
    }

    /**
     * Determine whether the user can view account history/audit log.
     */
    public function viewHistory(User $user, ChartOfAccount $account): bool
    {
        if ($account->company_id !== $user->current_company_id) {
            return false;
        }

        $role = $user->getRoleInCompany($user->current_company_id);
        return in_array($role, ['owner', 'admin', 'accountant']);
    }

    /**
     * Determine whether the user can reconcile the account.
     * Only accountants and above can reconcile.
     */
    public function reconcile(User $user, ChartOfAccount $account): bool
    {
        if ($account->company_id !== $user->current_company_id) {
            return false;
        }

        $role = $user->getRoleInCompany($user->current_company_id);
        return in_array($role, ['owner', 'admin', 'accountant']);
    }
}
