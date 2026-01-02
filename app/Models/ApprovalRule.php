<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_workflow_id',
        'name',
        'step_order',
        'approver_type', // user, role, department, manager
        'approver_id',
        'approver_role',
        'required_approvals',
        'allow_delegation',
        'auto_approve_if_requester',
        'conditions',
    ];

    protected $casts = [
        'required_approvals' => 'integer',
        'allow_delegation' => 'boolean',
        'auto_approve_if_requester' => 'boolean',
        'conditions' => 'array',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'approval_workflow_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function getApproverLabel(): string
    {
        return match($this->approver_type) {
            'user' => $this->approver?->name ?? 'Utilisateur inconnu',
            'role' => 'Rôle: ' . $this->approver_role,
            'department' => 'Département #' . $this->approver_id,
            'manager' => 'Manager direct',
            default => $this->approver_type,
        };
    }
}
