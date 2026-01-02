<?php

namespace App\Policies;

use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\User;

class ApprovalPolicy
{
    /**
     * Determine whether the user can view any approval workflows.
     */
    public function viewAnyWorkflows(User $user): bool
    {
        return $user->current_company_id !== null;
    }

    /**
     * Determine whether the user can view the workflow.
     */
    public function viewWorkflow(User $user, ApprovalWorkflow $workflow): bool
    {
        return $workflow->company_id === $user->current_company_id;
    }

    /**
     * Determine whether the user can create workflows.
     * Only admins and owners can create approval workflows.
     */
    public function createWorkflow(User $user): bool
    {
        if (!$user->current_company_id) {
            return false;
        }

        return $user->isAdminInCurrentTenant();
    }

    /**
     * Determine whether the user can update the workflow.
     * Only admins and owners can update workflows.
     */
    public function updateWorkflow(User $user, ApprovalWorkflow $workflow): bool
    {
        if ($workflow->company_id !== $user->current_company_id) {
            return false;
        }

        return $user->isAdminInCurrentTenant();
    }

    /**
     * Determine whether the user can delete the workflow.
     * Only owners can delete workflows (dangerous operation).
     */
    public function deleteWorkflow(User $user, ApprovalWorkflow $workflow): bool
    {
        if ($workflow->company_id !== $user->current_company_id) {
            return false;
        }

        // Cannot delete if there are active approval requests
        if ($workflow->approval_requests()->whereIn('status', ['pending', 'in_progress'])->exists()) {
            return false;
        }

        $role = $user->getRoleInCompany($user->current_company_id);
        return $role === 'owner';
    }

    /**
     * Determine whether the user can activate/deactivate the workflow.
     */
    public function toggleWorkflow(User $user, ApprovalWorkflow $workflow): bool
    {
        if ($workflow->company_id !== $user->current_company_id) {
            return false;
        }

        return $user->isAdminInCurrentTenant();
    }

    // ========================================
    // Approval Request Policies
    // ========================================

    /**
     * Determine whether the user can view any approval requests.
     */
    public function viewAnyRequests(User $user): bool
    {
        return $user->current_company_id !== null;
    }

    /**
     * Determine whether the user can view the approval request.
     */
    public function viewRequest(User $user, ApprovalRequest $request): bool
    {
        if ($request->company_id !== $user->current_company_id) {
            return false;
        }

        // User can view if:
        // 1. They created the request
        // 2. They are an approver at any step
        // 3. They are admin/owner
        return $request->requester_id === $user->id
            || $this->isApproverForRequest($user, $request)
            || $user->isAdminInCurrentTenant();
    }

    /**
     * Determine whether the user can create an approval request.
     */
    public function createRequest(User $user): bool
    {
        return $user->current_company_id !== null;
    }

    /**
     * Determine whether the user can approve the request.
     */
    public function approve(User $user, ApprovalRequest $request): bool
    {
        if ($request->company_id !== $user->current_company_id) {
            return false;
        }

        // Cannot approve own request (unless admin override)
        if ($request->requester_id === $user->id && !$user->isAdminInCurrentTenant()) {
            return false;
        }

        // Can only approve if status is pending or in_progress
        if (!in_array($request->status, ['pending', 'in_progress'])) {
            return false;
        }

        // Check if user is an approver for the current step
        return $this->isApproverForCurrentStep($user, $request);
    }

    /**
     * Determine whether the user can reject the request.
     */
    public function reject(User $user, ApprovalRequest $request): bool
    {
        // Same logic as approve
        return $this->approve($user, $request);
    }

    /**
     * Determine whether the user can request changes.
     */
    public function requestChanges(User $user, ApprovalRequest $request): bool
    {
        // Same logic as approve
        return $this->approve($user, $request);
    }

    /**
     * Determine whether the user can delegate the approval.
     */
    public function delegate(User $user, ApprovalRequest $request): bool
    {
        if ($request->company_id !== $user->current_company_id) {
            return false;
        }

        // User must be the assigned approver for current step
        return $this->isApproverForCurrentStep($user, $request);
    }

    /**
     * Determine whether the user can cancel the request.
     */
    public function cancel(User $user, ApprovalRequest $request): bool
    {
        if ($request->company_id !== $user->current_company_id) {
            return false;
        }

        // Can cancel if:
        // 1. User is the requester and request is still pending
        // 2. User is admin/owner
        return ($request->requester_id === $user->id && $request->status === 'pending')
            || $user->isAdminInCurrentTenant();
    }

    /**
     * Determine whether the user can escalate the request.
     * Only admins can manually escalate.
     */
    public function escalate(User $user, ApprovalRequest $request): bool
    {
        if ($request->company_id !== $user->current_company_id) {
            return false;
        }

        return $user->isAdminInCurrentTenant();
    }

    /**
     * Determine whether the user can view the request history/audit trail.
     */
    public function viewHistory(User $user, ApprovalRequest $request): bool
    {
        // Same as viewRequest
        return $this->viewRequest($user, $request);
    }

    /**
     * Determine whether the user can add comments to the request.
     */
    public function comment(User $user, ApprovalRequest $request): bool
    {
        // Anyone who can view the request can comment
        return $this->viewRequest($user, $request);
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Check if user is an approver at any step of the request.
     */
    protected function isApproverForRequest(User $user, ApprovalRequest $request): bool
    {
        $workflow = $request->workflow;

        foreach ($workflow->rules as $rule) {
            if ($rule->approver_type === 'user' && $rule->approver_id === $user->id) {
                return true;
            }

            if ($rule->approver_type === 'role') {
                $userRole = $user->getRoleInCompany($user->current_company_id);
                if ($userRole === $rule->approver_role) {
                    return true;
                }
            }

            if ($rule->approver_type === 'manager') {
                // Check if user is the requester's manager (if that relationship exists)
                // This would need to be implemented based on your org structure
                // For now, treat as admin-only
                if ($user->isAdminInCurrentTenant()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if user is an approver for the current step of the request.
     */
    protected function isApproverForCurrentStep(User $user, ApprovalRequest $request): bool
    {
        $currentStepOrder = $request->current_step;
        $workflow = $request->workflow;

        $currentRule = $workflow->rules()
            ->where('step_order', $currentStepOrder)
            ->first();

        if (!$currentRule) {
            return false;
        }

        // Check based on approver type
        if ($currentRule->approver_type === 'user') {
            return $currentRule->approver_id === $user->id;
        }

        if ($currentRule->approver_type === 'role') {
            $userRole = $user->getRoleInCompany($user->current_company_id);
            return $userRole === $currentRule->approver_role;
        }

        if ($currentRule->approver_type === 'manager') {
            // Check if user is the requester's manager
            // For now, allow admins to act as managers
            return $user->isAdminInCurrentTenant();
        }

        return false;
    }
}
