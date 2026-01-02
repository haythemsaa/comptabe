<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;
use App\Notifications\ModuleRequestApprovedNotification;
use App\Notifications\ModuleRequestRejectedNotification;

class ModuleRequest extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'module_id',
        'status',
        'message',
        'admin_message',
        'admin_response',
        'requested_by',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function getAdminMessageAttribute()
    {
        return $this->admin_response;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approve(User $admin, string $response = null, int $trialDays = 30): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'admin_response' => $response,
        ]);

        // Enable module for company
        $this->company->modules()->syncWithoutDetaching([
            $this->module_id => [
                'is_enabled' => true,
                'is_visible' => true,
                'enabled_at' => now(),
                'enabled_by' => $admin->id,
                'status' => 'trial',
                'trial_ends_at' => now()->addDays($trialDays),
            ]
        ]);

        // Notify the requester
        $this->requestedBy->notify(new ModuleRequestApprovedNotification($this));
    }

    public function reject(User $admin, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'admin_response' => $reason,
        ]);

        // Notify the requester
        $this->requestedBy->notify(new ModuleRequestRejectedNotification($this));
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
