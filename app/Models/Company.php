<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'name',
        'legal_form',
        'vat_number',
        'mollie_customer_id',
        'stripe_customer_id',
        'enterprise_number',
        'street',
        'house_number',
        'box',
        'postal_code',
        'city',
        'country_code',
        // Tunisia fields
        'matricule_fiscal',
        'cnss_employer_number',
        // France fields
        'siret',
        'siren',
        'ape_code',
        'urssaf_number',
        'convention_collective',
        'email',
        'phone',
        'website',
        'peppol_id',
        'peppol_provider',
        'peppol_participant_id',
        'peppol_company_id',
        'peppol_api_key',
        'peppol_api_secret',
        'peppol_webhook_secret',
        'peppol_test_mode',
        'peppol_connected_at',
        'peppol_settings',
        'peppol_registered',
        'peppol_registered_at',
        // Peppol quota system
        'peppol_plan',
        'peppol_quota_monthly',
        'peppol_usage_current_month',
        'peppol_usage_last_reset',
        'peppol_overage_allowed',
        'peppol_overage_cost',
        'default_iban',
        'default_bic',
        'fiscal_year_start_month',
        'vat_regime',
        'vat_periodicity',
        'logo_path',
        // Invoice template settings
        'invoice_template',
        'invoice_primary_color',
        'invoice_secondary_color',
        'invoice_template_settings',
        'settings',
        // Firm management fields
        'company_type',
        'managed_by_firm_id',
        'accepts_firm_management',
        'firm_access_level',
    ];

    /**
     * Company type labels.
     */
    public const COMPANY_TYPE_LABELS = [
        'standalone' => 'Indépendante',
        'firm_client' => 'Client Cabinet',
        'accounting_firm' => 'Cabinet Comptable',
    ];

    /**
     * Firm access level labels.
     */
    public const FIRM_ACCESS_LEVEL_LABELS = [
        'full' => 'Accès complet',
        'limited' => 'Accès limité',
        'readonly' => 'Lecture seule',
    ];

    protected function casts(): array
    {
        return [
            'peppol_registered' => 'boolean',
            'peppol_registered_at' => 'datetime',
            'peppol_test_mode' => 'boolean',
            'peppol_connected_at' => 'datetime',
            'peppol_settings' => 'array',
            'peppol_api_secret' => 'encrypted',
            'peppol_quota_monthly' => 'integer',
            'peppol_usage_current_month' => 'integer',
            'peppol_usage_last_reset' => 'datetime',
            'peppol_overage_allowed' => 'boolean',
            'peppol_overage_cost' => 'decimal:2',
            'fiscal_year_start_month' => 'integer',
            'settings' => 'array',
            'accepts_firm_management' => 'boolean',
            // SECURITY: Encrypt sensitive banking data
            'default_iban' => 'encrypted',
            'default_bic' => 'encrypted',
            // Invoice template settings
            'invoice_template_settings' => 'array',
        ];
    }

    /**
     * Get the current tenant company.
     */
    public static function current(): ?self
    {
        $tenantId = session('current_tenant_id');

        if (!$tenantId) {
            return null;
        }

        return static::find($tenantId);
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
     * Get IBAN (alias for default_iban).
     */
    public function getIbanAttribute(): ?string
    {
        return $this->default_iban;
    }

    /**
     * Get formatted IBAN.
     */
    public function getFormattedIbanAttribute(): ?string
    {
        if (!$this->default_iban) return null;
        $clean = preg_replace('/\s+/', '', $this->default_iban);
        return trim(chunk_split($clean, 4, ' '));
    }

    /**
     * Get BIC (alias for default_bic).
     */
    public function getBicAttribute(): ?string
    {
        return $this->default_bic;
    }

    /**
     * Get the full address.
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
     * Get country configuration.
     */
    public function getCountryConfig(): array
    {
        $countryCode = $this->country_code ?? 'BE';
        return config("countries.{$countryCode}", config('countries.BE'));
    }

    /**
     * Get company currency based on country.
     */
    public function getCurrency(): string
    {
        return $this->getCountryConfig()['currency'] ?? 'EUR';
    }

    /**
     * Get currency symbol.
     */
    public function getCurrencySymbol(): string
    {
        return $this->getCountryConfig()['currency_symbol'] ?? '€';
    }

    /**
     * Get decimal places for currency.
     */
    public function getDecimalPlaces(): int
    {
        return $this->getCountryConfig()['decimal_places'] ?? 2;
    }

    /**
     * Get VAT rates for this country.
     */
    public function getVatRates(): array
    {
        return $this->getCountryConfig()['vat']['rates'] ?? [21, 12, 6, 0];
    }

    /**
     * Get default VAT rate.
     */
    public function getDefaultVatRate(): int
    {
        return $this->getCountryConfig()['vat']['default_rate'] ?? 21;
    }

    /**
     * Get social security organization name.
     */
    public function getSocialSecurityOrg(): string
    {
        return $this->getCountryConfig()['payroll']['social_security']['organization'] ?? 'ONSS';
    }

    /**
     * Check if this is a Tunisian company.
     */
    public function isTunisia(): bool
    {
        return $this->country_code === 'TN';
    }

    /**
     * Check if this is a Belgian company.
     */
    public function isBelgium(): bool
    {
        return $this->country_code === 'BE';
    }

    /**
     * Check if this is a French company.
     */
    public function isFrance(): bool
    {
        return $this->country_code === 'FR';
    }

    /**
     * Users belonging to this company.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(CompanyUser::class)
            ->withPivot(['role', 'permissions', 'is_default'])
            ->withTimestamps();
    }

    /**
     * Get company owners.
     */
    public function owners(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'owner');
    }

    /**
     * Get company accountants.
     */
    public function accountants(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'accountant');
    }

    /**
     * Partners (customers/suppliers).
     */
    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class);
    }

    /**
     * Invoices.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Sales invoices.
     */
    public function salesInvoices(): HasMany
    {
        return $this->invoices()->where('type', 'out');
    }

    /**
     * Purchase invoices.
     */
    public function purchaseInvoices(): HasMany
    {
        return $this->invoices()->where('type', 'in');
    }

    /**
     * Chart of accounts.
     */
    public function chartOfAccounts(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class);
    }

    /**
     * Journals.
     */
    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class);
    }

    /**
     * Journal entries.
     */
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Bank accounts.
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    /**
     * Fiscal years.
     */
    public function fiscalYears(): HasMany
    {
        return $this->hasMany(FiscalYear::class);
    }

    /**
     * Current fiscal year.
     */
    public function currentFiscalYear(): ?FiscalYear
    {
        return $this->fiscalYears()
            ->where('status', 'open')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
    }

    /**
     * VAT declarations.
     */
    public function vatDeclarations(): HasMany
    {
        return $this->hasMany(VatDeclaration::class);
    }

    /**
     * VAT codes.
     */
    public function vatCodes(): HasMany
    {
        return $this->hasMany(VatCode::class);
    }

    /**
     * Products/Services catalog.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Quotes.
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * Credit notes.
     */
    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    /**
     * Recurring invoices.
     */
    public function recurringInvoices(): HasMany
    {
        return $this->hasMany(RecurringInvoice::class);
    }

    /**
     * Clients (customer partners).
     */
    public function clients(): HasMany
    {
        return $this->partners()->where('type', 'customer');
    }

    /**
     * Suppliers (supplier partners).
     */
    public function suppliers(): HasMany
    {
        return $this->partners()->where('type', 'supplier');
    }

    /**
     * Active subscription.
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    /**
     * All subscriptions history.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Subscription invoices.
     */
    public function subscriptionInvoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    /**
     * Usage records.
     */
    public function usageRecords(): HasMany
    {
        return $this->hasMany(SubscriptionUsage::class);
    }

    /**
     * Payment methods.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Default payment method.
     */
    public function defaultPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethods()->where('is_default', true)->first();
    }

    /**
     * Payment transactions.
     */
    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Modules enabled for this company.
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'company_modules')
            ->withPivot(['is_enabled', 'is_visible', 'enabled_at', 'disabled_at', 'enabled_by', 'status', 'trial_ends_at'])
            ->withTimestamps();
    }

    /**
     * Get only enabled modules.
     */
    public function enabledModules(): BelongsToMany
    {
        return $this->modules()->wherePivot('is_enabled', true);
    }

    /**
     * Get only visible modules (for UI).
     */
    public function visibleModules(): BelongsToMany
    {
        return $this->modules()->wherePivot('is_visible', true)->wherePivot('is_enabled', true);
    }

    /**
     * Check if company has module enabled.
     */
    public function hasModule(string $moduleCode): bool
    {
        return $this->enabledModules()->where('code', $moduleCode)->exists();
    }

    /**
     * Module requests from this company.
     */
    public function moduleRequests(): HasMany
    {
        return $this->hasMany(ModuleRequest::class);
    }

    /**
     * Get current subscription plan.
     */
    public function getPlanAttribute(): ?SubscriptionPlan
    {
        return $this->subscription?->plan;
    }

    /**
     * Check if subscription is active.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription && $this->subscription->isActive();
    }

    /**
     * Check if on trial.
     */
    public function onTrial(): bool
    {
        return $this->subscription && $this->subscription->onTrial();
    }

    /**
     * Check if a feature is enabled.
     */
    public function hasFeature(string $feature): bool
    {
        $plan = $this->plan;
        if (!$plan) return false;

        $featureKey = "feature_{$feature}";
        return $plan->$featureKey ?? false;
    }

    /**
     * Check if can create more of a resource.
     */
    public function canCreate(string $resource): bool
    {
        $plan = $this->plan;
        if (!$plan) return true; // No plan = no restrictions (or handle differently)

        $usage = $this->getCurrentUsage();

        // Map resource to limit key and usage key
        $mapping = [
            'invoices' => ['limit' => 'max_invoices_per_month', 'usage' => 'invoices'],
            'clients' => ['limit' => 'max_clients', 'usage' => 'clients'],
            'products' => ['limit' => 'max_products', 'usage' => 'products'],
            'users' => ['limit' => 'max_users', 'usage' => 'users'],
        ];

        if (!isset($mapping[$resource])) {
            return true;
        }

        $limitKey = $mapping[$resource]['limit'];
        $usageKey = $mapping[$resource]['usage'];
        $limit = $plan->$limitKey ?? 0;

        // -1 means unlimited
        if ($limit === -1) {
            return true;
        }

        return $usage[$usageKey] < $limit;
    }

    /**
     * Get usage for current period (returns array for easier view use).
     */
    public function getCurrentUsage(): array
    {
        $usageModel = SubscriptionUsage::getCurrentUsage($this);

        // Also calculate real-time counts
        $invoicesThisMonth = $this->invoices()
            ->where('type', 'out')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            'invoices' => max($usageModel->invoices_created, $invoicesThisMonth),
            'clients' => $this->partners()->customers()->count(),
            'products' => $this->products()->count(),
            'users' => $this->users()->count(),
            'storage_mb' => $usageModel->storage_used_mb,
        ];
    }

    /**
     * Get usage record model.
     */
    public function getUsageRecord(): SubscriptionUsage
    {
        return SubscriptionUsage::getCurrentUsage($this);
    }

    /**
     * Get remaining quota for a resource.
     */
    public function getRemainingQuota(string $resource): int|string
    {
        $plan = $this->plan;
        if (!$plan) return 0;

        $limitKey = "max_{$resource}";
        $limit = $plan->$limitKey ?? 0;

        if ($limit === -1) return 'Illimité';

        $usage = $this->getCurrentUsage();
        $current = match ($resource) {
            'invoices_per_month', 'invoices' => $usage['invoices'],
            'clients' => $usage['clients'],
            'products' => $usage['products'],
            'users' => $usage['users'],
            'storage_mb' => $usage['storage_mb'],
            default => 0,
        };

        return max(0, $limit - $current);
    }

    /**
     * Increment invoice usage when creating a new invoice.
     */
    public function incrementInvoiceUsage(): void
    {
        $usageRecord = $this->getUsageRecord();
        $usageRecord->incrementInvoices();
    }

    /**
     * Refresh usage counts (call after creating/deleting resources).
     */
    public function refreshUsageCounts(): void
    {
        $usageRecord = $this->getUsageRecord();
        $usageRecord->updateCounts();
    }

    // ========================================
    // Expert-Comptable / Accounting Firm Methods
    // ========================================

    /**
     * Get the accounting firm managing this company.
     */
    public function managedByFirm(): BelongsTo
    {
        return $this->belongsTo(AccountingFirm::class, 'managed_by_firm_id');
    }

    /**
     * Get the active mandate with the managing firm.
     */
    public function mandate(): HasOne
    {
        return $this->hasOne(ClientMandate::class)
            ->where('status', 'active')
            ->latestOfMany();
    }

    /**
     * Get all mandates (history).
     */
    public function mandates(): HasMany
    {
        return $this->hasMany(ClientMandate::class);
    }

    /**
     * Check if company is managed by an accounting firm.
     */
    public function isManagedByFirm(): bool
    {
        return $this->managed_by_firm_id !== null;
    }

    /**
     * Check if company accepts firm management.
     */
    public function acceptsFirmManagement(): bool
    {
        return (bool) $this->accepts_firm_management;
    }

    /**
     * Check if a specific firm can access this company.
     */
    public function canBeAccessedByFirm(string $firmId): bool
    {
        return $this->managed_by_firm_id === $firmId &&
            $this->mandates()->where('accounting_firm_id', $firmId)->where('status', 'active')->exists();
    }

    /**
     * Check if firm has full access.
     */
    public function hasFirmFullAccess(): bool
    {
        return $this->firm_access_level === 'full';
    }

    /**
     * Check if firm has edit access (full or limited).
     */
    public function hasFirmEditAccess(): bool
    {
        return in_array($this->firm_access_level, ['full', 'limited']);
    }

    /**
     * Get the company type label.
     */
    public function getCompanyTypeLabelAttribute(): string
    {
        return self::COMPANY_TYPE_LABELS[$this->company_type] ?? $this->company_type ?? 'Indépendante';
    }

    /**
     * Get the firm access level label.
     */
    public function getFirmAccessLevelLabelAttribute(): string
    {
        return self::FIRM_ACCESS_LEVEL_LABELS[$this->firm_access_level] ?? $this->firm_access_level ?? '';
    }

    /**
     * Assign company to an accounting firm.
     */
    public function assignToFirm(AccountingFirm $firm, string $accessLevel = 'full'): void
    {
        $this->update([
            'company_type' => 'firm_client',
            'managed_by_firm_id' => $firm->id,
            'accepts_firm_management' => true,
            'firm_access_level' => $accessLevel,
        ]);
    }

    /**
     * Remove company from firm management.
     */
    public function removeFromFirm(): void
    {
        // End active mandate
        $this->mandate?->update(['status' => 'terminated', 'end_date' => now()]);

        $this->update([
            'company_type' => 'standalone',
            'managed_by_firm_id' => null,
            'accepts_firm_management' => false,
            'firm_access_level' => null,
        ]);
    }

    /**
     * Scope for companies managed by a firm.
     */
    public function scopeManagedBy($query, $firmId)
    {
        return $query->where('managed_by_firm_id', $firmId);
    }

    /**
     * Scope for firm clients.
     */
    public function scopeFirmClients($query)
    {
        return $query->where('company_type', 'firm_client');
    }

    /**
     * Scope for standalone companies.
     */
    public function scopeStandalone($query)
    {
        return $query->where('company_type', 'standalone')
            ->orWhereNull('company_type');
    }

    /**
     * Peppol usage tracking.
     */
    public function peppolUsage(): HasMany
    {
        return $this->hasMany(PeppolUsage::class);
    }

    /**
     * Check if company has available Peppol quota.
     */
    public function hasPeppolQuota(): bool
    {
        // Enterprise plan = unlimited
        if ($this->peppol_plan === 'enterprise') {
            return true;
        }

        // Check if within quota
        if ($this->peppol_usage_current_month < $this->peppol_quota_monthly) {
            return true;
        }

        // Check if overage allowed
        return $this->peppol_overage_allowed;
    }

    /**
     * Get remaining Peppol quota for current month.
     */
    public function getRemainingPeppolQuota(): int
    {
        if ($this->peppol_plan === 'enterprise') {
            return PHP_INT_MAX;
        }

        return max(0, $this->peppol_quota_monthly - $this->peppol_usage_current_month);
    }

    /**
     * Get quota usage percentage.
     */
    public function getPeppolQuotaPercentage(): float
    {
        if ($this->peppol_plan === 'enterprise' || $this->peppol_quota_monthly === 0) {
            return 0;
        }

        return min(100, ($this->peppol_usage_current_month / $this->peppol_quota_monthly) * 100);
    }

    /**
     * Increment Peppol usage counter.
     */
    public function incrementPeppolUsage(): void
    {
        $this->increment('peppol_usage_current_month');
    }

    /**
     * Reset Peppol usage for new month.
     */
    public function resetPeppolUsage(): void
    {
        $this->update([
            'peppol_usage_current_month' => 0,
            'peppol_usage_last_reset' => now(),
        ]);
    }

    /**
     * Get Peppol plan details.
     */
    public function getPeppolPlanDetails(): ?array
    {
        $plans = config('peppol_plans.tenant_plans');
        return $plans[$this->peppol_plan] ?? null;
    }

    /**
     * Check if Peppol is enabled for this company.
     */
    public function isPeppolEnabled(): bool
    {
        return !empty($this->peppol_participant_id)
            && $this->peppol_quota_monthly > 0;
    }
}
