<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'bank_id',
        'bank_name',
        'bic',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'consent_expires_at',
        'last_sync_at',
        'status',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'consent_expires_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' &&
               !$this->consent_expires_at?->isPast();
    }

    public function needsRenewal(): bool
    {
        return $this->consent_expires_at?->diffInDays(now()) < 7;
    }
}
