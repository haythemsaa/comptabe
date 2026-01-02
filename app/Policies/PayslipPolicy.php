<?php

namespace App\Policies;

use App\Models\Payslip;
use App\Models\User;

class PayslipPolicy
{
    /**
     * Determine whether the user can view any payslips.
     */
    public function viewAny(User $user): bool
    {
        return $user->current_company_id !== null;
    }

    /**
     * Determine whether the user can view the payslip.
     */
    public function view(User $user, Payslip $payslip): bool
    {
        // Allow if payslip belongs to user's current company
        if ($payslip->company_id === $user->current_company_id) {
            return true;
        }

        // Also allow if user has access to the payslip's company
        return $user->hasAccessToCompany($payslip->company_id);
    }

    /**
     * Determine whether the user can create payslips.
     */
    public function create(User $user): bool
    {
        // Only admins and accountants can create payslips
        if ($user->current_company_id === null) {
            return false;
        }

        $role = $user->getRoleInCompany($user->current_company_id);
        return in_array($role, ['owner', 'admin', 'accountant']);
    }

    /**
     * Determine whether the user can update the payslip.
     */
    public function update(User $user, Payslip $payslip): bool
    {
        if ($payslip->company_id !== $user->current_company_id) {
            return false;
        }

        // Only admins and accountants can update payslips
        $role = $user->getRoleInCompany($user->current_company_id);
        if (!in_array($role, ['owner', 'admin', 'accountant'])) {
            return false;
        }

        // Only draft payslips can be edited
        return $payslip->status === 'draft';
    }

    /**
     * Determine whether the user can delete the payslip.
     */
    public function delete(User $user, Payslip $payslip): bool
    {
        if ($payslip->company_id !== $user->current_company_id) {
            return false;
        }

        // Only owners/admins can delete
        $role = $user->getRoleInCompany($user->current_company_id);
        if (!in_array($role, ['owner', 'admin'])) {
            return false;
        }

        // Only draft or cancelled payslips can be deleted
        return in_array($payslip->status, ['draft', 'cancelled']);
    }

    /**
     * Determine whether the user can validate the payslip.
     */
    public function validate(User $user, Payslip $payslip): bool
    {
        if ($payslip->company_id !== $user->current_company_id) {
            return false;
        }

        // Only admins and accountants can validate payslips
        $role = $user->getRoleInCompany($user->current_company_id);
        if (!in_array($role, ['owner', 'admin', 'accountant'])) {
            return false;
        }

        return $payslip->status === 'draft';
    }

    /**
     * Determine whether the user can mark the payslip as paid.
     */
    public function markAsPaid(User $user, Payslip $payslip): bool
    {
        if ($payslip->company_id !== $user->current_company_id) {
            return false;
        }

        // Only admins and accountants can mark as paid
        $role = $user->getRoleInCompany($user->current_company_id);
        if (!in_array($role, ['owner', 'admin', 'accountant'])) {
            return false;
        }

        return $payslip->status === 'validated';
    }

    /**
     * Determine whether the user can download the payslip.
     */
    public function download(User $user, Payslip $payslip): bool
    {
        return $payslip->company_id === $user->current_company_id;
    }
}
