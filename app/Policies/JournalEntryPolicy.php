<?php

namespace App\Policies;

use App\Models\JournalEntry;
use App\Models\User;

class JournalEntryPolicy
{
    /**
     * Determine whether the user can view any journal entries.
     */
    public function viewAny(User $user): bool
    {
        return $user->current_company_id !== null;
    }

    /**
     * Determine whether the user can view the journal entry.
     */
    public function view(User $user, JournalEntry $entry): bool
    {
        return $entry->company_id === $user->current_company_id;
    }

    /**
     * Determine whether the user can create journal entries.
     */
    public function create(User $user): bool
    {
        if (!$user->current_company_id) {
            return false;
        }

        // Only admins or accountants can create journal entries
        $role = $user->getRoleInCompany($user->current_company_id);
        return in_array($role, ['owner', 'admin', 'accountant']);
    }

    /**
     * Determine whether the user can update the journal entry.
     */
    public function update(User $user, JournalEntry $entry): bool
    {
        if ($entry->company_id !== $user->current_company_id) {
            return false;
        }

        // Only draft entries can be updated
        if ($entry->status !== 'draft') {
            return false;
        }

        // Only admins or accountants can update
        $role = $user->getRoleInCompany($user->current_company_id);
        return in_array($role, ['owner', 'admin', 'accountant']);
    }

    /**
     * Determine whether the user can delete the journal entry.
     */
    public function delete(User $user, JournalEntry $entry): bool
    {
        if ($entry->company_id !== $user->current_company_id) {
            return false;
        }

        // Only draft entries can be deleted
        if ($entry->status !== 'draft') {
            return false;
        }

        // Only admins can delete
        return $user->isAdminInCurrentTenant();
    }

    /**
     * Determine whether the user can post the journal entry.
     */
    public function post(User $user, JournalEntry $entry): bool
    {
        if ($entry->company_id !== $user->current_company_id) {
            return false;
        }

        // Only draft entries can be posted
        if ($entry->status !== 'draft') {
            return false;
        }

        // Only admins or accountants can post
        $role = $user->getRoleInCompany($user->current_company_id);
        return in_array($role, ['owner', 'admin', 'accountant']);
    }

    /**
     * Determine whether the user can reverse the journal entry.
     */
    public function reverse(User $user, JournalEntry $entry): bool
    {
        if ($entry->company_id !== $user->current_company_id) {
            return false;
        }

        // Only posted entries can be reversed
        if ($entry->status !== 'posted') {
            return false;
        }

        // Only admins can reverse
        return $user->isAdminInCurrentTenant();
    }
}
