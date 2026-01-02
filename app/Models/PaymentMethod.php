<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'company_id',
        'provider',
        'provider_method_id',
        'type',
        'last_four',
        'brand',
        'bank_name',
        'holder_name',
        'exp_month',
        'exp_year',
        'is_default',
        'is_verified',
        'verified_at',
        'expires_at',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
        'exp_month' => 'integer',
        'exp_year' => 'integer',
    ];

    /**
     * Company relationship
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Payment transactions relationship
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Check if payment method is expired
     */
    public function isExpired(): bool
    {
        if (!$this->exp_month || !$this->exp_year) {
            return false;
        }

        $expirationDate = now()->setYear($this->exp_year)->setMonth($this->exp_month)->endOfMonth();

        return now()->isAfter($expirationDate);
    }

    /**
     * Get formatted expiry date
     */
    public function getFormattedExpiryAttribute(): ?string
    {
        if (!$this->exp_month || !$this->exp_year) {
            return null;
        }

        return sprintf('%02d/%d', $this->exp_month, $this->exp_year);
    }

    /**
     * Get display name for payment method
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->type === 'card' && $this->brand && $this->last_four) {
            return ucfirst($this->brand) . ' •••• ' . $this->last_four;
        }

        if ($this->type === 'sepa_debit' && $this->last_four) {
            return 'SEPA •••• ' . $this->last_four;
        }

        if ($this->bank_name) {
            return $this->bank_name;
        }

        return ucfirst(str_replace('_', ' ', $this->type));
    }

    /**
     * Set as default payment method (only one can be default)
     */
    public function setAsDefault(): bool
    {
        // Remove default from all other methods
        $this->company->paymentMethods()->update(['is_default' => false]);

        return $this->update(['is_default' => true]);
    }

    /**
     * Scope: Get default payment method
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope: Get verified methods
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope: Get non-expired methods
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }
}
