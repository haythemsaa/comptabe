<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'workflow_id',
        'approvable_type',
        'approvable_id',
        'requester_id',
        'amount',
        'notes',
        'status', // pending, approved, rejected, cancelled, expired, changes_requested
        'current_step',
        'escalation_count',
        'expires_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class)->orderBy('step_number');
    }

    public function currentStepDetails(): ApprovalStep
    {
        return $this->steps()->where('step_number', $this->current_step)->first();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
            'cancelled' => 'Annulé',
            'expired' => 'Expiré',
            'changes_requested' => 'Modifications demandées',
            default => $this->status,
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            'cancelled' => 'gray',
            'expired' => 'orange',
            'changes_requested' => 'blue',
            default => 'gray',
        };
    }

    public function getProgressPercentage(): int
    {
        $totalSteps = $this->steps()->distinct('step_number')->count('step_number');
        if ($totalSteps === 0) return 0;

        if ($this->status === 'approved') return 100;

        return intval(($this->current_step - 1) / $totalSteps * 100);
    }
}
