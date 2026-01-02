<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasUuid, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'avatar',
        'is_superadmin',
        'mfa_enabled',
        'mfa_secret',
        'eid_linked',
        'itsme_linked',
        'is_active',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        // Professional fields for Expert-Comptable
        'user_type',
        'professional_title',
        'itaa_number',
        'ire_number',
        'default_firm_id',
    ];

    /**
     * User type labels.
     */
    public const USER_TYPE_LABELS = [
        'individual' => 'Particulier',
        'business' => 'Entreprise',
        'accountant' => 'Expert-Comptable',
        'collaborator' => 'Collaborateur Cabinet',
    ];

    /**
     * Professional title labels.
     */
    public const PROFESSIONAL_TITLE_LABELS = [
        'expert_comptable' => 'Expert-Comptable',
        'conseil_fiscal' => 'Conseil Fiscal',
        'reviseur' => 'Réviseur d\'Entreprises',
        'comptable_agree' => 'Comptable Agréé',
        'collaborateur' => 'Collaborateur',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_superadmin' => 'boolean',
            'mfa_enabled' => 'boolean',
            'eid_linked' => 'boolean',
            'itsme_linked' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if user is a superadmin.
     */
    public function isSuperadmin(): bool
    {
        return (bool) $this->is_superadmin;
    }

    /**
     * Scope for superadmins.
     */
    public function scopeSuperadmins($query)
    {
        return $query->where('is_superadmin', true);
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user's name (alias for full_name).
     */
    public function getNameAttribute(): string
    {
        if (!empty($this->attributes['first_name']) && !empty($this->attributes['last_name'])) {
            return "{$this->attributes['first_name']} {$this->attributes['last_name']}";
        }
        return $this->attributes['name'] ?? '';
    }

    /**
     * Set the user's name (splits into first_name and last_name).
     */
    public function setNameAttribute($value): void
    {
        $parts = explode(' ', $value, 2);
        $this->attributes['first_name'] = $parts[0] ?? '';
        $this->attributes['last_name'] = $parts[1] ?? '';
    }

    /**
     * Get the user's initials.
     */
    public function getInitialsAttribute(): string
    {
        return strtoupper(
            substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1)
        );
    }

    /**
     * Get the current company ID from session.
     */
    public function getCurrentCompanyIdAttribute(): ?string
    {
        return session('current_tenant_id');
    }

    /**
     * Get the current company object.
     */
    public function getCurrentCompanyAttribute(): ?Company
    {
        $tenantId = session('current_tenant_id');
        if (!$tenantId) {
            // Fall back to default company
            return $this->companies()->wherePivot('is_default', true)->first();
        }
        return $this->companies()->find($tenantId);
    }

    /**
     * Get the companies this user belongs to.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->using(CompanyUser::class)
            ->withPivot(['role', 'permissions', 'is_default'])
            ->withTimestamps();
    }

    /**
     * Get the user's default company.
     */
    public function defaultCompany(): ?Company
    {
        return $this->companies()
            ->wherePivot('is_default', true)
            ->first() ?? $this->companies()->first();
    }

    /**
     * Get the invoices created by this user.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    /**
     * Check if user has access to a company.
     */
    public function hasAccessToCompany(string $companyId): bool
    {
        return $this->companies()->where('companies.id', $companyId)->exists();
    }

    /**
     * Get user's role in a company.
     */
    public function getRoleInCompany(string $companyId): ?string
    {
        $company = $this->companies()->where('companies.id', $companyId)->first();
        return $company?->pivot->role;
    }

    /**
     * Check if user is admin or owner in current tenant.
     */
    public function isAdminInCurrentTenant(): bool
    {
        $tenantId = session('current_tenant_id');
        if (!$tenantId) return false;

        $role = $this->getRoleInCompany($tenantId);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Check if user is an accountant.
     */
    public function isAccountant(): bool
    {
        return $this->companies()
            ->wherePivot('role', 'accountant')
            ->exists();
    }

    // ========================================
    // Expert-Comptable / Accounting Firm Methods
    // ========================================

    /**
     * Get the accounting firms this user belongs to.
     */
    public function accountingFirms(): BelongsToMany
    {
        return $this->belongsToMany(AccountingFirm::class, 'accounting_firm_users')
            ->using(AccountingFirmUser::class)
            ->withPivot(['role', 'permissions', 'is_active', 'started_at', 'ended_at'])
            ->withTimestamps();
    }

    /**
     * Get the user's default accounting firm.
     */
    public function defaultFirm(): BelongsTo
    {
        return $this->belongsTo(AccountingFirm::class, 'default_firm_id');
    }

    /**
     * Get the user's current active firm.
     */
    public function currentFirm(): ?AccountingFirm
    {
        return $this->defaultFirm ?? $this->accountingFirms()->wherePivot('is_active', true)->first();
    }

    /**
     * Check if user has access to a firm.
     */
    public function hasAccessToFirm(string $firmId): bool
    {
        return $this->accountingFirms()
            ->where('accounting_firms.id', $firmId)
            ->wherePivot('is_active', true)
            ->exists();
    }

    /**
     * Get user's role in a firm.
     */
    public function getRoleInFirm(string $firmId): ?string
    {
        $firm = $this->accountingFirms()->where('accounting_firms.id', $firmId)->first();
        return $firm?->pivot->role;
    }

    /**
     * Check if user is owner of a firm.
     */
    public function isOwnerOfFirm(string $firmId): bool
    {
        return $this->getRoleInFirm($firmId) === 'cabinet_owner';
    }

    /**
     * Check if user is admin or owner of a firm.
     */
    public function isAdminOfFirm(string $firmId): bool
    {
        $role = $this->getRoleInFirm($firmId);
        return in_array($role, ['cabinet_owner', 'cabinet_admin']);
    }

    /**
     * Check if user is an expert-comptable (professional).
     */
    public function isExpertComptable(): bool
    {
        return $this->user_type === 'accountant' ||
            !empty($this->itaa_number) ||
            !empty($this->ire_number);
    }

    /**
     * Check if user is a member of any cabinet.
     */
    public function isCabinetMember(): bool
    {
        return $this->accountingFirms()->wherePivot('is_active', true)->exists();
    }

    /**
     * Get user type label.
     */
    public function getUserTypeLabelAttribute(): string
    {
        return self::USER_TYPE_LABELS[$this->user_type] ?? $this->user_type ?? 'Non défini';
    }

    /**
     * Get professional title label.
     */
    public function getProfessionalTitleLabelAttribute(): string
    {
        return self::PROFESSIONAL_TITLE_LABELS[$this->professional_title] ?? $this->professional_title ?? '';
    }

    /**
     * Get all client mandates assigned to this user (as manager).
     */
    public function managedMandates(): HasMany
    {
        return $this->hasMany(ClientMandate::class, 'manager_user_id');
    }

    /**
     * Get active mandates assigned to this user.
     */
    public function activeMandates(): HasMany
    {
        return $this->managedMandates()->where('status', 'active');
    }

    /**
     * Get tasks assigned to this user.
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(MandateTask::class, 'assigned_to');
    }

    /**
     * Get pending tasks for this user.
     */
    public function pendingTasks(): HasMany
    {
        return $this->assignedTasks()
            ->whereIn('status', ['pending', 'in_progress', 'review']);
    }

    /**
     * Get the user's onboarding progress.
     */
    public function onboardingProgress(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\OnboardingProgress::class);
    }

    /**
     * Client portal access grants.
     */
    public function clientAccess(): HasMany
    {
        return $this->hasMany(\App\Models\ClientAccess::class);
    }

    /**
     * Check if user has client portal access to a company.
     */
    public function hasClientAccessTo(string $companyId): bool
    {
        return $this->clientAccess()
            ->where('company_id', $companyId)
            ->exists();
    }

    /**
     * Scope for expert-comptable users.
     */
    public function scopeExpertComptable($query)
    {
        return $query->where('user_type', 'accountant');
    }

    /**
     * Scope for cabinet collaborators.
     */
    public function scopeCabinetCollaborators($query)
    {
        return $query->whereHas('accountingFirms', function ($q) {
            $q->wherePivot('is_active', true);
        });
    }
}
