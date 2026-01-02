<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    /**
     * Determine whether the user can view the report.
     */
    public function view(User $user, Report $report): bool
    {
        // Le rapport appartient à la société de l'utilisateur
        if ($report->company_id !== $user->current_company_id) {
            return false;
        }

        // Le rapport est public ou appartient à l'utilisateur
        return $report->is_public || $report->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the report.
     */
    public function update(User $user, Report $report): bool
    {
        // Seul le propriétaire peut modifier
        return $report->company_id === $user->current_company_id
            && $report->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the report.
     */
    public function delete(User $user, Report $report): bool
    {
        // Seul le propriétaire peut supprimer
        return $report->company_id === $user->current_company_id
            && $report->user_id === $user->id;
    }
}
