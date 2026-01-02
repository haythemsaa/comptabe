<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasUuid, HasTenant, SoftDeletes;

    protected $fillable = [
        'company_id',
        'product_type_id',
        'category_id',
        'code',
        'sku',
        'barcode',
        'manufacturer',
        'brand',
        'name',
        'description',
        'type',
        'unit_price',
        'cost_price',
        'compare_price',
        'min_price',
        'currency',
        'unit',
        'vat_rate',
        'category',
        'track_inventory',
        'stock_quantity',
        'low_stock_threshold',
        'stock_status',
        'weight',
        'length',
        'width',
        'height',
        'duration_minutes',
        'requires_scheduling',
        'image_path',
        'gallery',
        'documents',
        'custom_fields',
        'meta_title',
        'meta_description',
        'tags',
        'min_quantity',
        'max_quantity',
        'quantity_increment',
        'last_sold_at',
        'total_sold',
        'sort_order',
        'is_active',
        'accounting_code',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'min_price' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'weight' => 'decimal:3',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'is_active' => 'boolean',
            'track_inventory' => 'boolean',
            'requires_scheduling' => 'boolean',
            'sort_order' => 'integer',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'duration_minutes' => 'integer',
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'quantity_increment' => 'integer',
            'total_sold' => 'integer',
            'gallery' => 'array',
            'documents' => 'array',
            'custom_fields' => 'array',
            'tags' => 'array',
            'last_sold_at' => 'datetime',
        ];
    }

    /**
     * Type options.
     */
    public const TYPES = [
        'product' => 'Produit',
        'service' => 'Service',
    ];

    /**
     * Common units.
     */
    public const UNITS = [
        'unité' => 'Unité',
        'pièce' => 'Pièce',
        'heure' => 'Heure',
        'jour' => 'Jour',
        'mois' => 'Mois',
        'année' => 'Année',
        'kg' => 'Kilogramme',
        'g' => 'Gramme',
        'litre' => 'Litre',
        'ml' => 'Millilitre',
        'mètre' => 'Mètre',
        'cm' => 'Centimètre',
        'm²' => 'Mètre carré',
        'm³' => 'Mètre cube',
        'forfait' => 'Forfait',
        'lot' => 'Lot',
        'boîte' => 'Boîte',
        'paquet' => 'Paquet',
        'carton' => 'Carton',
        'palette' => 'Palette',
    ];

    /**
     * Common VAT rates in Belgium.
     */
    public const VAT_RATES = [
        '21.00' => '21%',
        '12.00' => '12%',
        '6.00' => '6%',
        '0.00' => '0% (Exonéré)',
    ];

    /**
     * Stock statuses.
     */
    public const STOCK_STATUSES = [
        'in_stock' => 'En stock',
        'out_of_stock' => 'Rupture de stock',
        'low_stock' => 'Stock faible',
        'backorder' => 'En commande',
        'preorder' => 'Précommande',
    ];

    /**
     * Company relationship.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Product type relationship.
     */
    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    /**
     * Category relationship.
     */
    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Variants relationship.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    /**
     * Get custom field definitions for this product.
     */
    public function getCustomFieldDefinitions()
    {
        return ProductCustomField::where('company_id', $this->company_id)
            ->forType($this->product_type_id)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->unit_price, 2, ',', ' ') . ' €';
    }

    /**
     * Get formatted cost price.
     */
    public function getFormattedCostPriceAttribute(): ?string
    {
        if ($this->cost_price === null) {
            return null;
        }
        return number_format($this->cost_price, 2, ',', ' ') . ' €';
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Get price with VAT.
     */
    public function getPriceWithVatAttribute(): float
    {
        return round($this->unit_price * (1 + $this->vat_rate / 100), 2);
    }

    /**
     * Get profit margin.
     */
    public function getProfitMarginAttribute(): ?float
    {
        if (!$this->cost_price || $this->cost_price == 0) {
            return null;
        }
        return round((($this->unit_price - $this->cost_price) / $this->cost_price) * 100, 2);
    }

    /**
     * Get profit margin percentage formatted.
     */
    public function getFormattedMarginAttribute(): ?string
    {
        $margin = $this->profit_margin;
        if ($margin === null) {
            return null;
        }
        return number_format($margin, 1, ',', '') . '%';
    }

    /**
     * Get stock status label.
     */
    public function getStockStatusLabelAttribute(): string
    {
        return self::STOCK_STATUSES[$this->stock_status] ?? $this->stock_status;
    }

    /**
     * Check if product is in stock.
     */
    public function getIsInStockAttribute(): bool
    {
        if (!$this->track_inventory) {
            return true;
        }
        return $this->stock_quantity > 0;
    }

    /**
     * Check if stock is low.
     */
    public function getIsLowStockAttribute(): bool
    {
        if (!$this->track_inventory) {
            return false;
        }
        $threshold = $this->low_stock_threshold ?? 5;
        return $this->stock_quantity <= $threshold && $this->stock_quantity > 0;
    }

    /**
     * Check if product has variants.
     */
    public function getHasVariantsAttribute(): bool
    {
        return $this->variants()->exists();
    }

    /**
     * Get custom field value.
     */
    public function getCustomField(string $slug, $default = null)
    {
        return $this->custom_fields[$slug] ?? $default;
    }

    /**
     * Set custom field value.
     */
    public function setCustomField(string $slug, $value): void
    {
        $customFields = $this->custom_fields ?? [];
        $customFields[$slug] = $value;
        $this->custom_fields = $customFields;
    }

    /**
     * Get formatted custom field value.
     */
    public function getFormattedCustomField(string $slug): ?string
    {
        $value = $this->getCustomField($slug);
        if ($value === null) {
            return null;
        }

        $fieldDef = $this->getCustomFieldDefinitions()->firstWhere('slug', $slug);
        if (!$fieldDef) {
            return (string) $value;
        }

        return $fieldDef->formatValue($value);
    }

    /**
     * Scope active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by product type (advanced).
     */
    public function scopeOfProductType($query, string $productTypeId)
    {
        return $query->where('product_type_id', $productTypeId);
    }

    /**
     * Scope products only.
     */
    public function scopeProducts($query)
    {
        return $query->where('type', 'product');
    }

    /**
     * Scope services only.
     */
    public function scopeServices($query)
    {
        return $query->where('type', 'service');
    }

    /**
     * Scope by category.
     */
    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by category ID.
     */
    public function scopeInCategoryId($query, string $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope in stock.
     */
    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('track_inventory', false)
              ->orWhere('stock_quantity', '>', 0);
        });
    }

    /**
     * Scope low stock.
     */
    public function scopeLowStock($query)
    {
        return $query->where('track_inventory', true)
            ->whereRaw('stock_quantity <= COALESCE(low_stock_threshold, 5)')
            ->where('stock_quantity', '>', 0);
    }

    /**
     * Scope out of stock.
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('track_inventory', true)
            ->where('stock_quantity', '<=', 0);
    }

    /**
     * Scope search.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('brand', 'like', "%{$search}%")
                ->orWhere('manufacturer', 'like', "%{$search}%");
        });
    }

    /**
     * Scope search in custom fields.
     */
    public function scopeSearchCustomFields($query, string $search)
    {
        // Get searchable custom fields
        $searchableFields = ProductCustomField::where('company_id', session('current_tenant_id'))
            ->searchable()
            ->pluck('slug');

        if ($searchableFields->isEmpty()) {
            return $query;
        }

        return $query->where(function ($q) use ($search, $searchableFields) {
            foreach ($searchableFields as $slug) {
                $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_fields, '$.{$slug}')) LIKE ?", ["%{$search}%"]);
            }
        });
    }

    /**
     * Scope with tags.
     */
    public function scopeWithTags($query, array $tags)
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('tags', $tag);
            }
        });
    }

    /**
     * Get ordered by sort_order then name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Adjust stock quantity.
     */
    public function adjustStock(int $quantity, string $reason = null): void
    {
        if (!$this->track_inventory) {
            return;
        }

        $this->increment('stock_quantity', $quantity);
        $this->updateStockStatus();

        // Could log stock movements here
    }

    /**
     * Update stock status based on quantity.
     */
    public function updateStockStatus(): void
    {
        if (!$this->track_inventory) {
            $this->update(['stock_status' => 'in_stock']);
            return;
        }

        if ($this->stock_quantity <= 0) {
            $status = 'out_of_stock';
        } elseif ($this->is_low_stock) {
            $status = 'low_stock';
        } else {
            $status = 'in_stock';
        }

        $this->update(['stock_status' => $status]);
    }

    /**
     * Record a sale.
     */
    public function recordSale(int $quantity = 1): void
    {
        $this->increment('total_sold', $quantity);
        $this->update(['last_sold_at' => now()]);

        if ($this->track_inventory) {
            $this->adjustStock(-$quantity, 'Sale');
        }
    }

    /**
     * Convert to invoice line data.
     */
    public function toInvoiceLine(float $quantity = 1): array
    {
        return [
            'product_id' => $this->id,
            'description' => $this->name,
            'quantity' => $quantity,
            'unit' => $this->unit,
            'unit_price' => $this->unit_price,
            'vat_rate' => $this->vat_rate,
            'total_excl_vat' => round($this->unit_price * $quantity, 2),
            'vat_amount' => round($this->unit_price * $quantity * $this->vat_rate / 100, 2),
            'total_incl_vat' => round($this->unit_price * $quantity * (1 + $this->vat_rate / 100), 2),
        ];
    }

    /**
     * Duplicate product with optional custom fields.
     */
    public function duplicate(): Product
    {
        $newProduct = $this->replicate([
            'sku',
            'barcode',
            'last_sold_at',
            'total_sold',
        ]);

        $newProduct->name = $this->name . ' (copie)';
        $newProduct->code = null;
        $newProduct->sku = null;
        $newProduct->barcode = null;
        $newProduct->save();

        // Duplicate variants
        foreach ($this->variants as $variant) {
            $newVariant = $variant->replicate(['sku', 'barcode']);
            $newVariant->product_id = $newProduct->id;
            $newVariant->sku = null;
            $newVariant->barcode = null;
            $newVariant->save();
        }

        return $newProduct;
    }

    /**
     * Get price range for products with variants.
     */
    public function getPriceRangeAttribute(): ?array
    {
        if (!$this->has_variants) {
            return null;
        }

        $variants = $this->variants()->active()->get();
        if ($variants->isEmpty()) {
            return null;
        }

        $min = $variants->min('unit_price');
        $max = $variants->max('unit_price');

        return [
            'min' => $min,
            'max' => $max,
            'formatted' => $min == $max
                ? number_format($min, 2, ',', ' ') . ' €'
                : number_format($min, 2, ',', ' ') . ' - ' . number_format($max, 2, ',', ' ') . ' €',
        ];
    }

    /**
     * Get total stock across all variants.
     */
    public function getTotalVariantStockAttribute(): int
    {
        if (!$this->has_variants) {
            return $this->stock_quantity;
        }

        return $this->variants()->sum('stock_quantity');
    }

    /**
     * Get image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return asset('storage/' . $this->image_path);
    }

    /**
     * Get gallery URLs.
     */
    public function getGalleryUrlsAttribute(): array
    {
        if (empty($this->gallery)) {
            return [];
        }

        return array_map(fn($path) => asset('storage/' . $path), $this->gallery);
    }
}
