<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyModule extends Model
{
    protected $fillable = [
        'company_id',
        'module_id',
        'is_enabled',
        'is_visible',
        'enabled_at',
        'disabled_at',
        'enabled_by',
        'status',
        'trial_ends_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_visible' => 'boolean',
        'enabled_at' => 'datetime',
        'disabled_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function enabledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enabled_by');
    }

    public function isTrialActive(): bool
    {
        if ($this->status !== 'trial') {
            return false;
        }
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isActive(): bool
    {
        return $this->is_enabled && in_array($this->status, ['active', 'trial']);
    }

    public function enable(User $user = null): void
    {
        $this->update([
            'is_enabled' => true,
            'enabled_at' => now(),
            'disabled_at' => null,
            'enabled_by' => $user?->id,
        ]);
    }

    public function disable(): void
    {
        $this->update([
            'is_enabled' => false,
            'disabled_at' => now(),
        ]);
    }
}
