<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
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
    public function view(User $user, Project $project): bool
    {
        $company = Company::current();

        return $project->company_id === $company->id;
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
    public function update(User $user, Project $project): bool
    {
        $company = Company::current();

        if ($project->company_id !== $company->id) {
            return false;
        }

        // Only owner, admin, manager or project manager can update
        $role = $user->companies()
            ->where('companies.id', $company->id)
            ->first()
            ?->pivot
            ?->role;

        if (in_array($role, ['owner', 'admin', 'manager'])) {
            return true;
        }

        // Project manager can update their own projects
        return $project->manager_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }
}
