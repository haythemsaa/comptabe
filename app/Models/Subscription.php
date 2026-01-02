<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasUuid;

    protected $fillable = [
        'company_id',
        'plan_id',
        'status',
        'billing_cycle',
        'amount',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'cancelled_at',
        'suspended_at',
        'cancellation_reason',
        'admin_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'trial_ends_at' => 'date',
        'current_period_start' => 'date',
        'current_period_end' => 'date',
        'cancelled_at' => 'date',
        'suspended_at' => 'date',
    ];

    // Status constants
    public const STATUS_TRIALING = 'trialing';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_EXPIRED = 'expired';

    public const STATUSES = [
        self::STATUS_TRIALING => 'En essai',
        self::STATUS_ACTIVE => 'Actif',
        self::STATUS_PAST_DUE => 'Impayé',
        self::STATUS_CANCELLED => 'Annulé',
        self::STATUS_SUSPENDED => 'Suspendu',
        self::STATUS_EXPIRED => 'Expiré',
    ];

    public const STATUS_COLORS = [
        self::STATUS_TRIALING => 'info',
        self::STATUS_ACTIVE => 'success',
        self::STATUS_PAST_DUE => 'warning',
        self::STATUS_CANCELLED => 'secondary',
        self::STATUS_SUSPENDED => 'danger',
        self::STATUS_EXPIRED => 'secondary',
    ];

    /**
     * The company this subscription belongs to
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The plan for this subscription
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /**
     * Invoices for this subscription
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    /**
     * Check if subscription is active (can use the app)
     */
    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_TRIALING,
            self::STATUS_ACTIVE,
            self::STATUS_PAST_DUE, // Grace period
        ]);
    }

    /**
     * Check if subscription is in trial
     */
    public function onTrial(): bool
    {
        return $this->status === self::STATUS_TRIALING
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if trial has ended
     */
    public function trialEnded(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    /**
     * Get days remaining in trial
     */
    public function getTrialDaysRemainingAttribute(): int
    {
        if (!$this->trial_ends_at || $this->trial_ends_at->isPast()) {
            return 0;
        }
        return now()->diffInDays($this->trial_ends_at);
    }

    /**
     * Get days until period end
     */
    public function getDaysUntilRenewalAttribute(): ?int
    {
        if (!$this->current_period_end) {
            return null;
        }
        return now()->diffInDays($this->current_period_end, false);
    }

    /**
     * Check if subscription needs renewal
     */
    public function needsRenewal(): bool
    {
        return $this->current_period_end && $this->current_period_end->isPast();
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    /**
     * Start a new trial
     */
    public function startTrial(int $days = null): self
    {
        $days = $days ?? $this->plan->trial_days ?? 14;

        $this->update([
            'status' => self::STATUS_TRIALING,
            'trial_ends_at' => now()->addDays($days),
        ]);

        return $this;
    }

    /**
     * Activate subscription (after payment)
     */
    public function activate(string $cycle = 'monthly'): self
    {
        $amount = $cycle === 'yearly'
            ? $this->plan->price_yearly
            : $this->plan->price_monthly;

        $periodEnd = $cycle === 'yearly'
            ? now()->addYear()
            : now()->addMonth();

        $this->update([
            'status' => self::STATUS_ACTIVE,
            'billing_cycle' => $cycle,
            'amount' => $amount,
            'current_period_start' => now(),
            'current_period_end' => $periodEnd,
            'trial_ends_at' => null,
        ]);

        return $this;
    }

    /**
     * Suspend subscription
     */
    public function suspend(string $reason = null): self
    {
        $this->update([
            'status' => self::STATUS_SUSPENDED,
            'suspended_at' => now(),
            'admin_notes' => $reason ? ($this->admin_notes . "\n" . now()->format('d/m/Y') . ": Suspendu - " . $reason) : $this->admin_notes,
        ]);

        return $this;
    }

    /**
     * Reactivate suspended subscription
     */
    public function reactivate(): self
    {
        if ($this->current_period_end && $this->current_period_end->isFuture()) {
            $this->update([
                'status' => self::STATUS_ACTIVE,
                'suspended_at' => null,
            ]);
        } else {
            // Need to renew
            $this->update([
                'status' => self::STATUS_PAST_DUE,
                'suspended_at' => null,
            ]);
        }

        return $this;
    }

    /**
     * Cancel subscription
     */
    public function cancel(string $reason = null): self
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return $this;
    }

    /**
     * Mark as past due
     */
    public function markAsPastDue(): self
    {
        $this->update([
            'status' => self::STATUS_PAST_DUE,
        ]);

        return $this;
    }

    /**
     * Change plan
     */
    public function changePlan(SubscriptionPlan $newPlan): self
    {
        $amount = $this->billing_cycle === 'yearly'
            ? $newPlan->price_yearly
            : $newPlan->price_monthly;

        $this->update([
            'plan_id' => $newPlan->id,
            'amount' => $amount,
        ]);

        return $this;
    }

    /**
     * Renew subscription for another period
     */
    public function renew(): self
    {
        $periodEnd = $this->billing_cycle === 'yearly'
            ? now()->addYear()
            : now()->addMonth();

        $this->update([
            'status' => self::STATUS_ACTIVE,
            'current_period_start' => now(),
            'current_period_end' => $periodEnd,
        ]);

        return $this;
    }

    /**
     * Create subscription for company
     */
    public static function createForCompany(Company $company, SubscriptionPlan $plan): self
    {
        return self::create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'status' => self::STATUS_TRIALING,
            'billing_cycle' => 'monthly',
            'amount' => $plan->price_monthly,
            'trial_ends_at' => now()->addDays($plan->trial_days),
        ]);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_TRIALING,
            self::STATUS_ACTIVE,
            self::STATUS_PAST_DUE,
        ]);
    }

    public function scopeTrialing($query)
    {
        return $query->where('status', self::STATUS_TRIALING);
    }

    public function scopePastDue($query)
    {
        return $query->where('status', self::STATUS_PAST_DUE);
    }

    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->where('status', self::STATUS_TRIALING)
            ->where('trial_ends_at', '<=', now()->addDays($days))
            ->where('trial_ends_at', '>', now());
    }
}
