<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AccountingFirm extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'legal_form',
        'itaa_number',
        'ire_number',
        'vat_number',
        'enterprise_number',
        'street',
        'house_number',
        'box',
        'postal_code',
        'city',
        'country_code',
        'email',
        'phone',
        'website',
        'logo_path',
        'primary_color',
        'peppol_id',
        'peppol_provider',
        'peppol_api_key',
        'peppol_api_secret',
        'peppol_test_mode',
        'subscription_plan_id',
        'subscription_status',
        'trial_ends_at',
        'max_clients',
        'max_users',
        'settings',
        'features',
    ];

    protected function casts(): array
    {
        return [
            'peppol_test_mode' => 'boolean',
            'peppol_api_secret' => 'encrypted',
            'trial_ends_at' => 'datetime',
            'settings' => 'array',
            'features' => 'array',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($firm) {
            if (empty($firm->slug)) {
                $firm->slug = Str::slug($firm->name);
            }
        });
    }

    /**
     * Subscription status labels.
     */
    public const STATUS_LABELS = [
        'trial' => 'Essai',
        'active' => 'Actif',
        'past_due' => 'En retard',
        'cancelled' => 'Annulé',
        'suspended' => 'Suspendu',
    ];

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->subscription_status] ?? $this->subscription_status;
    }

    /**
     * Get the current authenticated firm.
     */
    public static function current(): ?self
    {
        $firmId = session('current_firm_id');

        // Fallback to the authenticated user's default firm
        if (!$firmId && auth()->check()) {
            $user = auth()->user();
            $firmId = $user->default_firm_id;

            // Set the session for future requests
            if ($firmId) {
                session(['current_firm_id' => $firmId]);
            }
        }

        if (!$firmId) {
            return null;
        }

        return static::find($firmId);
    }

    /**
     * Get formatted VAT number.
     */
    public function getFormattedVatNumberAttribute(): string
    {
        $vat = preg_replace('/[^0-9]/', '', $this->vat_number);
        if (strlen($vat) === 10) {
            return 'BE ' . substr($vat, 0, 4) . '.' . substr($vat, 4, 3) . '.' . substr($vat, 7);
        }
        return $this->vat_number;
    }

    /**
     * Get full address.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street . ' ' . $this->house_number . ($this->box ? ' bte ' . $this->box : ''),
            $this->postal_code . ' ' . $this->city,
            $this->country_code !== 'BE' ? $this->country_code : null,
        ]);
        return implode(', ', $parts);
    }

    /**
     * Generate Peppol ID from VAT number.
     */
    public function generatePeppolId(): string
    {
        $vat = preg_replace('/[^0-9]/', '', $this->vat_number);
        return '0208:' . $vat;
    }

    /**
     * Check if subscription is active.
     */
    public function hasActiveSubscription(): bool
    {
        return in_array($this->subscription_status, ['active', 'trial']);
    }

    /**
     * Check if on trial.
     */
    public function onTrial(): bool
    {
        return $this->subscription_status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if can add more clients.
     */
    public function canAddClient(): bool
    {
        if ($this->max_clients === -1) return true;
        return $this->clientMandates()->active()->count() < $this->max_clients;
    }

    /**
     * Check if can add more users.
     */
    public function canAddUser(): bool
    {
        if ($this->max_users === -1) return true;
        return $this->users()->count() < $this->max_users;
    }

    /**
     * Users (collaborators) of this firm.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'accounting_firm_users')
            ->using(AccountingFirmUser::class)
            ->withPivot([
                'role',
                'employee_number',
                'job_title',
                'department',
                'permissions',
                'can_access_all_clients',
                'is_default',
                'is_active',
                'joined_at',
            ])
            ->withTimestamps();
    }

    /**
     * Get firm owners.
     */
    public function owners(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'cabinet_owner');
    }

    /**
     * Get firm admins.
     */
    public function admins(): BelongsToMany
    {
        return $this->users()->wherePivotIn('role', ['cabinet_owner', 'cabinet_admin']);
    }

    /**
     * Client mandates.
     */
    public function clientMandates(): HasMany
    {
        return $this->hasMany(ClientMandate::class);
    }

    /**
     * Get active client mandates.
     */
    public function activeClientMandates(): HasMany
    {
        return $this->clientMandates()->where('status', 'active');
    }

    /**
     * Client companies (through mandates).
     */
    public function clientCompanies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'client_mandates')
            ->withPivot([
                'mandate_type',
                'status',
                'start_date',
                'end_date',
                'manager_user_id',
                'services',
            ])
            ->withTimestamps();
    }

    /**
     * Subscription plan.
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * Get statistics.
     */
    public function getStats(): array
    {
        return [
            'total_clients' => $this->clientMandates()->count(),
            'active_clients' => $this->clientMandates()->where('status', 'active')->count(),
            'total_users' => $this->users()->count(),
            'pending_tasks' => MandateTask::whereHas('clientMandate', fn($q) => $q->where('accounting_firm_id', $this->id))
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'overdue_tasks' => MandateTask::whereHas('clientMandate', fn($q) => $q->where('accounting_firm_id', $this->id))
                ->where('due_date', '<', now())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
        ];
    }

    /**
     * Scope for active firms.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('subscription_status', ['active', 'trial']);
    }
}
