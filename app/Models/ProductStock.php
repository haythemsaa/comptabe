<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToTenant;

class ProductStock extends Model
{
    use HasUuid, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'product_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'incoming_quantity',
        'location',
        'min_quantity',
        'max_quantity',
        'reorder_quantity',
        'last_counted_at',
        'last_counted_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'reserved_quantity' => 'decimal:4',
        'incoming_quantity' => 'decimal:4',
        'min_quantity' => 'decimal:4',
        'max_quantity' => 'decimal:4',
        'reorder_quantity' => 'decimal:4',
        'last_counted_at' => 'date',
        'last_counted_quantity' => 'decimal:4',
    ];

    // Relations
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    // Calculated attributes
    public function getAvailableQuantityAttribute(): float
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    public function getExpectedQuantityAttribute(): float
    {
        return $this->quantity + $this->incoming_quantity;
    }

    public function getStockValueAttribute(): float
    {
        return $this->quantity * ($this->product->cost_price ?? 0);
    }

    // Status checks
    public function isLowStock(): bool
    {
        return $this->quantity > 0 && $this->quantity <= $this->min_quantity;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    public function isOverstock(): bool
    {
        return $this->max_quantity && $this->quantity > $this->max_quantity;
    }

    public function needsReorder(): bool
    {
        return $this->quantity <= $this->min_quantity;
    }

    public function getStatusAttribute(): string
    {
        if ($this->isOutOfStock()) return 'out_of_stock';
        if ($this->isLowStock()) return 'low_stock';
        if ($this->isOverstock()) return 'overstock';
        return 'in_stock';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'out_of_stock' => 'Rupture',
            'low_stock' => 'Stock faible',
            'overstock' => 'Sur-stockage',
            default => 'En stock',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'out_of_stock' => 'danger',
            'low_stock' => 'warning',
            'overstock' => 'info',
            default => 'success',
        };
    }

    // Actions
    public function adjustQuantity(float $delta, string $reason = 'adjustment'): StockMovement
    {
        $type = $delta >= 0 ? 'in' : 'out';
        $quantityBefore = $this->quantity;
        $this->quantity += $delta;
        $this->save();

        return StockMovement::create([
            'company_id' => $this->company_id,
            'reference' => StockMovement::generateReference($this->company_id),
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'type' => 'adjustment',
            'reason' => $reason,
            'quantity' => abs($delta),
            'quantity_before' => $quantityBefore,
            'quantity_after' => $this->quantity,
            'unit_cost' => $this->product->cost_price,
            'total_cost' => abs($delta) * ($this->product->cost_price ?? 0),
            'created_by' => auth()->id(),
            'status' => 'validated',
        ]);
    }

    public function reserve(float $quantity): bool
    {
        if ($this->available_quantity < $quantity) {
            return false;
        }

        $this->reserved_quantity += $quantity;
        $this->save();
        return true;
    }

    public function releaseReservation(float $quantity): void
    {
        $this->reserved_quantity = max(0, $this->reserved_quantity - $quantity);
        $this->save();
    }

    // Static helpers
    public static function getOrCreate(string $companyId, string $productId, string $warehouseId): self
    {
        return static::firstOrCreate(
            [
                'company_id' => $companyId,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'quantity' => 0,
                'reserved_quantity' => 0,
                'incoming_quantity' => 0,
            ]
        );
    }

    public static function getTotalQuantity(string $companyId, string $productId): float
    {
        return static::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->sum('quantity');
    }

    public static function getTotalAvailable(string $companyId, string $productId): float
    {
        $totals = static::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->selectRaw('SUM(quantity) as qty, SUM(reserved_quantity) as reserved')
            ->first();

        return max(0, ($totals->qty ?? 0) - ($totals->reserved ?? 0));
    }
}
