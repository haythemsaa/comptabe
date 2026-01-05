<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EcommerceConnection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'platform',
        'store_url',
        'api_key',
        'api_secret',
        'access_token',
        'webhook_secret',
        'is_active',
        'auto_sync_orders',
        'auto_sync_products',
        'auto_sync_customers',
        'auto_create_invoices',
        'sync_interval_minutes',
        'last_sync_at',
        'settings',
        'field_mappings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_sync_orders' => 'boolean',
        'auto_sync_products' => 'boolean',
        'auto_sync_customers' => 'boolean',
        'auto_create_invoices' => 'boolean',
        'sync_interval_minutes' => 'integer',
        'last_sync_at' => 'datetime',
        'settings' => 'array',
        'field_mappings' => 'array',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
        'access_token',
        'webhook_secret',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(EcommerceOrder::class, 'connection_id');
    }

    public function productMappings(): HasMany
    {
        return $this->hasMany(EcommerceProductMapping::class, 'connection_id');
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(EcommerceSyncLog::class, 'connection_id');
    }

    public function getPlatformLabelAttribute(): string
    {
        return match ($this->platform) {
            'woocommerce' => 'WooCommerce',
            'shopify' => 'Shopify',
            'prestashop' => 'PrestaShop',
            'magento' => 'Magento',
            'custom' => 'API Personnalisée',
            default => ucfirst($this->platform),
        };
    }

    public function getPlatformIconAttribute(): string
    {
        return match ($this->platform) {
            'woocommerce' => 'fab fa-wordpress',
            'shopify' => 'fab fa-shopify',
            'prestashop' => 'fas fa-shopping-cart',
            'magento' => 'fab fa-magento',
            default => 'fas fa-plug',
        };
    }

    public function needsSync(): bool
    {
        if (!$this->is_active) return false;
        if (!$this->last_sync_at) return true;

        return $this->last_sync_at->addMinutes($this->sync_interval_minutes)->lt(now());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNeedsSync($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('last_sync_at')
                    ->orWhereRaw('last_sync_at < DATE_SUB(NOW(), INTERVAL sync_interval_minutes MINUTE)');
            });
    }
}
