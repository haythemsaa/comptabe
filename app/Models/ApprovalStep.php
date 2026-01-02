<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_request_id',
        'approval_rule_id',
        'step_number',
        'approver_type',
        'approver_id',
        'approver_role',
        'required_approvals',
        'decision', // null, approved, rejected, changes_requested
        'decided_by',
        'decided_at',
        'comment',
        'delegated_to',
        'delegated_by',
        'delegation_expires_at',
        'is_escalation',
        'escalation_reason',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
        'delegation_expires_at' => 'datetime',
        'is_escalation' => 'boolean',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(ApprovalRule::class, 'approval_rule_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegated_to');
    }

    public function delegator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegated_by');
    }

    public function isPending(): bool
    {
        return is_null($this->decision);
    }

    public function isApproved(): bool
    {
        return $this->decision === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->decision === 'rejected';
    }

    public function hasDelegation(): bool
    {
        return !is_null($this->delegated_to);
    }

    public function isDelegationActive(): bool
    {
        if (!$this->hasDelegation()) return false;

        return !$this->delegation_expires_at || $this->delegation_expires_at->isFuture();
    }

    public function getDecisionLabel(): string
    {
        return match($this->decision) {
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
            'changes_requested' => 'Modifications demandées',
            default => 'En attente',
        };
    }

    public function getDecisionColor(): string
    {
        return match($this->decision) {
            'approved' => 'green',
            'rejected' => 'red',
            'changes_requested' => 'blue',
            default => 'yellow',
        };
    }
}
