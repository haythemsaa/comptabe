<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasUuid;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'trial_days',
        'is_active',
        'is_featured',
        'sort_order',
        // Limites
        'max_users',
        'max_invoices_per_month',
        'max_clients',
        'max_products',
        'max_storage_mb',
        // Fonctionnalités
        'feature_peppol',
        'feature_recurring_invoices',
        'feature_credit_notes',
        'feature_quotes',
        'feature_multi_currency',
        'feature_api_access',
        'feature_custom_branding',
        'feature_advanced_reports',
        'feature_priority_support',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'trial_days' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'max_users' => 'integer',
        'max_invoices_per_month' => 'integer',
        'max_clients' => 'integer',
        'max_products' => 'integer',
        'max_storage_mb' => 'integer',
        'feature_peppol' => 'boolean',
        'feature_recurring_invoices' => 'boolean',
        'feature_credit_notes' => 'boolean',
        'feature_quotes' => 'boolean',
        'feature_multi_currency' => 'boolean',
        'feature_api_access' => 'boolean',
        'feature_custom_branding' => 'boolean',
        'feature_advanced_reports' => 'boolean',
        'feature_priority_support' => 'boolean',
    ];

    // Plans par défaut
    public const PLAN_FREE = 'free';
    public const PLAN_STARTER = 'starter';
    public const PLAN_PRO = 'pro';
    public const PLAN_ENTERPRISE = 'enterprise';

    /**
     * Subscriptions using this plan
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    /**
     * Get active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price_monthly');
    }

    /**
     * Get free plan
     */
    public static function getFreePlan(): ?self
    {
        return self::where('slug', self::PLAN_FREE)->first();
    }

    /**
     * Check if plan is free
     */
    public function isFree(): bool
    {
        return $this->price_monthly == 0;
    }

    /**
     * Get yearly discount percentage
     */
    public function getYearlyDiscountAttribute(): float
    {
        if ($this->price_monthly == 0) return 0;

        $yearlyIfMonthly = $this->price_monthly * 12;
        if ($yearlyIfMonthly == 0) return 0;

        return round((($yearlyIfMonthly - $this->price_yearly) / $yearlyIfMonthly) * 100);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->isFree()) {
            return 'Gratuit';
        }
        return number_format($this->price_monthly, 2, ',', ' ') . ' €/mois';
    }

    /**
     * Get limit label (for display)
     */
    public function getLimitLabel(string $limit): string
    {
        $value = $this->$limit ?? 0;
        return $value == -1 ? 'Illimité' : (string) $value;
    }

    /**
     * Check if a limit is unlimited
     */
    public function isUnlimited(string $limit): bool
    {
        return ($this->$limit ?? 0) == -1;
    }

    /**
     * Get all features as array
     */
    public function getFeaturesAttribute(): array
    {
        return [
            'peppol' => $this->feature_peppol,
            'recurring_invoices' => $this->feature_recurring_invoices,
            'credit_notes' => $this->feature_credit_notes,
            'quotes' => $this->feature_quotes,
            'multi_currency' => $this->feature_multi_currency,
            'api_access' => $this->feature_api_access,
            'custom_branding' => $this->feature_custom_branding,
            'advanced_reports' => $this->feature_advanced_reports,
            'priority_support' => $this->feature_priority_support,
        ];
    }

    /**
     * Get features list for display
     */
    public function getFeaturesList(): array
    {
        $features = [];

        $labels = [
            'feature_peppol' => 'Envoi Peppol e-factures',
            'feature_recurring_invoices' => 'Factures récurrentes',
            'feature_credit_notes' => 'Notes de crédit',
            'feature_quotes' => 'Devis',
            'feature_multi_currency' => 'Multi-devises',
            'feature_api_access' => 'Accès API',
            'feature_custom_branding' => 'Branding personnalisé',
            'feature_advanced_reports' => 'Rapports avancés',
            'feature_priority_support' => 'Support prioritaire',
        ];

        foreach ($labels as $key => $label) {
            if ($this->$key) {
                $features[] = $label;
            }
        }

        return $features;
    }
}
