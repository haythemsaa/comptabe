<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasUuid;

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'name',
        'attributes',
        'unit_price',
        'cost_price',
        'stock_quantity',
        'image_path',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'unit_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Product relationship.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->unit_price, 2, ',', ' ') . ' €';
    }

    /**
     * Get attribute values as string.
     */
    public function getAttributeStringAttribute(): string
    {
        if (empty($this->attributes)) {
            return '';
        }

        return collect($this->attributes)
            ->map(fn($value, $key) => "{$key}: {$value}")
            ->implode(', ');
    }

    /**
     * Get full variant name (product name + attributes).
     */
    public function getFullNameAttribute(): string
    {
        $productName = $this->product->name ?? '';
        $attrs = $this->attribute_string;

        return $attrs ? "{$productName} ({$attrs})" : $productName;
    }

    /**
     * Check if in stock.
     */
    public function getIsInStockAttribute(): bool
    {
        if (!$this->product->track_inventory) {
            return true;
        }

        return $this->stock_quantity > 0;
    }

    /**
     * Get stock status.
     */
    public function getStockStatusAttribute(): string
    {
        if (!$this->product->track_inventory) {
            return 'in_stock';
        }

        if ($this->stock_quantity <= 0) {
            return 'out_of_stock';
        }

        $threshold = $this->product->low_stock_threshold ?? 5;
        if ($this->stock_quantity <= $threshold) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * Scope active variants.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope in stock.
     */
    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->whereHas('product', function ($pq) {
                $pq->where('track_inventory', false);
            })->orWhere('stock_quantity', '>', 0);
        });
    }

    /**
     * Adjust stock quantity.
     */
    public function adjustStock(int $quantity, string $reason = null): void
    {
        $this->increment('stock_quantity', $quantity);

        // Could log stock movements here
    }

    /**
     * Convert to invoice line data.
     */
    public function toInvoiceLine(float $quantity = 1): array
    {
        return [
            'product_id' => $this->product_id,
            'variant_id' => $this->id,
            'description' => $this->full_name,
            'quantity' => $quantity,
            'unit' => $this->product->unit,
            'unit_price' => $this->unit_price,
            'vat_rate' => $this->product->vat_rate,
            'total_excl_vat' => round($this->unit_price * $quantity, 2),
            'vat_amount' => round($this->unit_price * $quantity * $this->product->vat_rate / 100, 2),
            'total_incl_vat' => round($this->unit_price * $quantity * (1 + $this->product->vat_rate / 100), 2),
        ];
    }
}
