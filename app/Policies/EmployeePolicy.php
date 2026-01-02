<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    /**
     * Determine whether the user can view any employees.
     */
    public function viewAny(User $user): bool
    {
        return $user->current_company_id !== null;
    }

    /**
     * Determine whether the user can view the employee.
     */
    public function view(User $user, Employee $employee): bool
    {
        // Allow if employee belongs to user's current company
        if ($employee->company_id === $user->current_company_id) {
            return true;
        }

        // Also allow if user has access to the employee's company
        return $user->hasAccessToCompany($employee->company_id);
    }

    /**
     * Determine whether the user can create employees.
     */
    public function create(User $user): bool
    {
        // Only admins can create employees
        if ($user->current_company_id === null) {
            return false;
        }

        $role = $user->getRoleInCompany($user->current_company_id);
        return in_array($role, ['owner', 'admin', 'accountant']);
    }

    /**
     * Determine whether the user can update the employee.
     */
    public function update(User $user, Employee $employee): bool
    {
        if ($employee->company_id !== $user->current_company_id) {
            return false;
        }

        // Only admins can update employees
        $role = $user->getRoleInCompany($user->current_company_id);
        return in_array($role, ['owner', 'admin', 'accountant']);
    }

    /**
     * Determine whether the user can delete the employee.
     */
    public function delete(User $user, Employee $employee): bool
    {
        if ($employee->company_id !== $user->current_company_id) {
            return false;
        }

        // Only owners/admins can delete employees
        $role = $user->getRoleInCompany($user->current_company_id);
        return in_array($role, ['owner', 'admin']);
    }
}
