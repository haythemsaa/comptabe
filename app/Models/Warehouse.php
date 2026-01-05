<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;

class Warehouse extends Model
{
    use HasUuid, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'address',
        'city',
        'postal_code',
        'country_code',
        'phone',
        'email',
        'manager_id',
        'is_default',
        'is_active',
        'allow_negative_stock',
        'settings',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'allow_negative_stock' => 'boolean',
        'settings' => 'array',
        'sort_order' => 'integer',
    ];

    // Relations
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function productStocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function inventorySessions(): HasMany
    {
        return $this->hasMany(InventorySession::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Helpers
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->postal_code . ' ' . $this->city,
            $this->country_code !== 'BE' ? $this->country_code : null,
        ]);
        return implode(', ', $parts);
    }

    public function getTotalProductsCount(): int
    {
        return $this->productStocks()->where('quantity', '>', 0)->count();
    }

    public function getTotalStockValue(): float
    {
        return $this->productStocks()
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->selectRaw('SUM(product_stocks.quantity * products.cost_price) as total')
            ->value('total') ?? 0;
    }

    public function getAvailableQuantity(string $productId): float
    {
        $stock = $this->productStocks()->where('product_id', $productId)->first();
        if (!$stock) return 0;
        return max(0, $stock->quantity - $stock->reserved_quantity);
    }

    public function hasStock(string $productId, float $quantity = 1): bool
    {
        return $this->getAvailableQuantity($productId) >= $quantity;
    }

    public function setAsDefault(): void
    {
        // Remove default from others
        static::where('company_id', $this->company_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    // Stats
    public static function getStats(string $companyId): array
    {
        $warehouses = static::where('company_id', $companyId)->get();

        return [
            'total' => $warehouses->count(),
            'active' => $warehouses->where('is_active', true)->count(),
            'total_products' => ProductStock::where('company_id', $companyId)
                ->where('quantity', '>', 0)
                ->distinct('product_id')
                ->count('product_id'),
            'total_value' => ProductStock::where('company_id', $companyId)
                ->join('products', 'product_stocks.product_id', '=', 'products.id')
                ->selectRaw('SUM(product_stocks.quantity * products.cost_price) as total')
                ->value('total') ?? 0,
        ];
    }
}
