<?php

namespace App\Policies;

use App\Models\Partner;
use App\Models\User;

class PartnerPolicy
{
    /**
     * Determine whether the user can view any partners.
     */
    public function viewAny(User $user): bool
    {
        return $user->current_company_id !== null;
    }

    /**
     * Determine whether the user can view the partner.
     */
    public function view(User $user, Partner $partner): bool
    {
        return $partner->company_id === $user->current_company_id;
    }

    /**
     * Determine whether the user can create partners.
     */
    public function create(User $user): bool
    {
        return $user->current_company_id !== null;
    }

    /**
     * Determine whether the user can update the partner.
     */
    public function update(User $user, Partner $partner): bool
    {
        return $partner->company_id === $user->current_company_id;
    }

    /**
     * Determine whether the user can delete the partner.
     */
    public function delete(User $user, Partner $partner): bool
    {
        if ($partner->company_id !== $user->current_company_id) {
            return false;
        }

        // Only admins/owners can delete partners
        return $user->isAdminInCurrentTenant();
    }

    /**
     * Determine whether the user can merge partners.
     */
    public function merge(User $user, Partner $partner): bool
    {
        if ($partner->company_id !== $user->current_company_id) {
            return false;
        }

        // Only admins can merge partners
        return $user->isAdminInCurrentTenant();
    }

    /**
     * Determine whether the user can verify Peppol capability.
     */
    public function verifyPeppol(User $user, Partner $partner): bool
    {
        return $partner->company_id === $user->current_company_id;
    }
}
