<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Timesheet;
use App\Models\User;

class TimesheetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Timesheet $timesheet): bool
    {
        $company = Company::current();

        if ($timesheet->company_id !== $company->id) {
            return false;
        }

        // User can view their own timesheets
        if ($timesheet->user_id === $user->id) {
            return true;
        }

        // Managers can view all timesheets
        $role = $user->companies()
            ->where('companies.id', $company->id)
            ->first()
            ?->pivot
            ?->role;

        return in_array($role, ['owner', 'admin', 'manager']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Timesheet $timesheet): bool
    {
        $company = Company::current();

        if ($timesheet->company_id !== $company->id) {
            return false;
        }

        // Can only update draft timesheets
        if ($timesheet->status !== 'draft') {
            return false;
        }

        // User can update their own timesheets
        if ($timesheet->user_id === $user->id) {
            return true;
        }

        // Managers can update any draft timesheet
        $role = $user->companies()
            ->where('companies.id', $company->id)
            ->first()
            ?->pivot
            ?->role;

        return in_array($role, ['owner', 'admin', 'manager']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Timesheet $timesheet): bool
    {
        return $this->update($user, $timesheet);
    }

    /**
     * Determine whether the user can approve timesheets.
     */
    public function approve(User $user): bool
    {
        $company = Company::current();

        $role = $user->companies()
            ->where('companies.id', $company->id)
            ->first()
            ?->pivot
            ?->role;

        return in_array($role, ['owner', 'admin', 'manager']);
    }
}
