<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'icon',
        'version',
        'is_core',
        'is_premium',
        'monthly_price',
        'sort_order',
        'dependencies',
        'routes',
        'permissions',
        'is_active',
    ];

    protected $casts = [
        'is_core' => 'boolean',
        'is_premium' => 'boolean',
        'is_active' => 'boolean',
        'monthly_price' => 'decimal:2',
        'dependencies' => 'array',
        'routes' => 'array',
        'permissions' => 'array',
    ];

    /**
     * Companies that have this module enabled
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_modules')
            ->withPivot(['is_enabled', 'is_visible', 'enabled_at', 'disabled_at', 'enabled_by', 'status', 'trial_ends_at'])
            ->withTimestamps();
    }

    /**
     * Module requests
     */
    public function requests(): HasMany
    {
        return $this->hasMany(ModuleRequest::class);
    }

    /**
     * Get companies with module enabled
     */
    public function enabledCompanies(): BelongsToMany
    {
        return $this->companies()->wherePivot('is_enabled', true);
    }

    /**
     * Check if module has dependencies
     */
    public function hasDependencies(): bool
    {
        return !empty($this->dependencies);
    }

    /**
     * Get dependent modules
     */
    public function getDependentModules(): array
    {
        if (!$this->hasDependencies()) {
            return [];
        }

        return static::whereIn('code', $this->dependencies)->get()->toArray();
    }

    /**
     * Check if company has this module
     */
    public function isEnabledForCompany(string $companyId): bool
    {
        return $this->companies()
            ->where('company_id', $companyId)
            ->wherePivot('is_enabled', true)
            ->exists();
    }

    /**
     * Scope: Active modules only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Core modules
     */
    public function scopeCore($query)
    {
        return $query->where('is_core', true);
    }

    /**
     * Scope: Premium modules
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Scope: By category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get module icon HTML
     */
    public function getIconHtmlAttribute(): string
    {
        if (empty($this->icon)) {
            return '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>';
        }

        // If it's already a complete SVG tag
        if (str_starts_with($this->icon, '<svg')) {
            return $this->icon;
        }

        // If it's an SVG path (starts with M for moveto command)
        if (str_starts_with($this->icon, 'M')) {
            return '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="' . e($this->icon) . '"></path></svg>';
        }

        // If it's a class name (e.g., heroicon)
        return sprintf('<i class="%s"></i>', e($this->icon));
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->monthly_price == 0) {
            return 'Gratuit';
        }

        return number_format($this->monthly_price, 2, ',', ' ') . ' €/mois';
    }

    /**
     * Get category badge color
     */
    public function getCategoryColorAttribute(): string
    {
        return match($this->category) {
            'sales' => 'bg-blue-100 text-blue-800',
            'inventory' => 'bg-green-100 text-green-800',
            'hr' => 'bg-purple-100 text-purple-800',
            'finance' => 'bg-yellow-100 text-yellow-800',
            'productivity' => 'bg-pink-100 text-pink-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'sales' => 'Ventes & CRM',
            'inventory' => 'Stock & Inventaire',
            'hr' => 'RH & Paie',
            'finance' => 'Finance & Comptabilité',
            'productivity' => 'Productivité',
            default => $this->category,
        };
    }
}
