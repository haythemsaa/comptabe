<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToTenant;

class StockAlert extends Model
{
    use HasUuid, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'product_id',
        'warehouse_id',
        'type',
        'current_quantity',
        'threshold_quantity',
        'expiry_date',
        'is_read',
        'is_resolved',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'current_quantity' => 'decimal:4',
        'threshold_quantity' => 'decimal:4',
        'expiry_date' => 'date',
        'is_read' => 'boolean',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public const TYPES = [
        'low_stock' => ['label' => 'Stock faible', 'color' => 'warning', 'icon' => 'alert-triangle'],
        'out_of_stock' => ['label' => 'Rupture de stock', 'color' => 'danger', 'icon' => 'x-circle'],
        'overstock' => ['label' => 'Sur-stockage', 'color' => 'info', 'icon' => 'package'],
        'expiring_soon' => ['label' => 'Péremption proche', 'color' => 'warning', 'icon' => 'clock'],
        'expired' => ['label' => 'Périmé', 'color' => 'danger', 'icon' => 'alert-octagon'],
        'reorder_point' => ['label' => 'Point de commande', 'color' => 'primary', 'icon' => 'shopping-cart'],
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

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Scopes
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCritical($query)
    {
        return $query->whereIn('type', ['out_of_stock', 'expired']);
    }

    // Helpers
    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type]['label'] ?? $this->type;
    }

    public function getTypeColor(): string
    {
        return self::TYPES[$this->type]['color'] ?? 'secondary';
    }

    public function getTypeIcon(): string
    {
        return self::TYPES[$this->type]['icon'] ?? 'alert-circle';
    }

    public function isCritical(): bool
    {
        return in_array($this->type, ['out_of_stock', 'expired']);
    }

    // Actions
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    public function resolve(?User $user = null): void
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $user?->id ?? auth()->id(),
        ]);
    }

    // Static helpers
    public static function checkAndCreate(string $companyId, string $productId, ?string $warehouseId = null): void
    {
        $query = ProductStock::where('company_id', $companyId)
            ->where('product_id', $productId);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $stocks = $query->get();

        foreach ($stocks as $stock) {
            // Check for out of stock
            if ($stock->quantity <= 0) {
                static::createIfNotExists($companyId, $productId, $stock->warehouse_id, 'out_of_stock', $stock->quantity, 0);
            }
            // Check for low stock
            elseif ($stock->isLowStock()) {
                static::createIfNotExists($companyId, $productId, $stock->warehouse_id, 'low_stock', $stock->quantity, $stock->min_quantity);
            }
            // Check for overstock
            elseif ($stock->isOverstock()) {
                static::createIfNotExists($companyId, $productId, $stock->warehouse_id, 'overstock', $stock->quantity, $stock->max_quantity);
            }
        }
    }

    protected static function createIfNotExists(
        string $companyId,
        string $productId,
        string $warehouseId,
        string $type,
        float $currentQty,
        float $thresholdQty
    ): void {
        $exists = static::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('type', $type)
            ->where('is_resolved', false)
            ->exists();

        if (!$exists) {
            static::create([
                'company_id' => $companyId,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'type' => $type,
                'current_quantity' => $currentQty,
                'threshold_quantity' => $thresholdQty,
            ]);
        }
    }

    public static function getUnresolvedCount(string $companyId): int
    {
        return static::where('company_id', $companyId)->unresolved()->count();
    }

    public static function getCriticalCount(string $companyId): int
    {
        return static::where('company_id', $companyId)->unresolved()->critical()->count();
    }
}
