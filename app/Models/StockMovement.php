<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;

class StockMovement extends Model
{
    use HasUuid, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'reference',
        'product_id',
        'warehouse_id',
        'destination_warehouse_id',
        'type',
        'reason',
        'quantity',
        'quantity_before',
        'quantity_after',
        'unit_cost',
        'total_cost',
        'batch_number',
        'expiry_date',
        'serial_number',
        'source_type',
        'source_id',
        'notes',
        'created_by',
        'validated_by',
        'validated_at',
        'status',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'quantity_before' => 'decimal:4',
        'quantity_after' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'expiry_date' => 'date',
        'validated_at' => 'datetime',
    ];

    // Types avec labels
    public const TYPES = [
        'in' => ['label' => 'Entrée', 'color' => 'success', 'icon' => 'arrow-down'],
        'out' => ['label' => 'Sortie', 'color' => 'danger', 'icon' => 'arrow-up'],
        'transfer' => ['label' => 'Transfert', 'color' => 'info', 'icon' => 'repeat'],
        'adjustment' => ['label' => 'Ajustement', 'color' => 'warning', 'icon' => 'edit'],
        'production' => ['label' => 'Production', 'color' => 'primary', 'icon' => 'tool'],
        'consumption' => ['label' => 'Consommation', 'color' => 'secondary', 'icon' => 'package'],
    ];

    public const REASONS = [
        'purchase' => 'Achat fournisseur',
        'sale' => 'Vente client',
        'return_in' => 'Retour client',
        'return_out' => 'Retour fournisseur',
        'transfer' => 'Transfert',
        'inventory' => 'Inventaire',
        'production' => 'Production',
        'consumption' => 'Consommation',
        'damage' => 'Casse/Dommage',
        'theft' => 'Vol',
        'expired' => 'Périmé',
        'sample' => 'Échantillon',
        'gift' => 'Don/Cadeau',
        'other' => 'Autre',
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

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeReason($query, string $reason)
    {
        return $query->where('reason', $reason);
    }

    public function scopeValidated($query)
    {
        return $query->where('status', 'validated');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeIncoming($query)
    {
        return $query->whereIn('type', ['in', 'production']);
    }

    public function scopeOutgoing($query)
    {
        return $query->whereIn('type', ['out', 'consumption']);
    }

    public function scopeForProduct($query, string $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForWarehouse($query, string $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopePeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
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
        return self::TYPES[$this->type]['icon'] ?? 'circle';
    }

    public function getReasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }

    public function isIncoming(): bool
    {
        return in_array($this->type, ['in', 'production']);
    }

    public function isOutgoing(): bool
    {
        return in_array($this->type, ['out', 'consumption']);
    }

    public function isTransfer(): bool
    {
        return $this->type === 'transfer';
    }

    public function isValidated(): bool
    {
        return $this->status === 'validated';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    // Actions
    public function validate(?User $user = null): void
    {
        if ($this->status !== 'draft') return;

        // Apply stock movement
        $stock = ProductStock::getOrCreate(
            $this->company_id,
            $this->product_id,
            $this->warehouse_id
        );

        $quantityChange = $this->isIncoming() ? $this->quantity : -$this->quantity;
        $this->quantity_before = $stock->quantity;
        $stock->quantity += $quantityChange;
        $stock->save();
        $this->quantity_after = $stock->quantity;

        // Handle transfers
        if ($this->isTransfer() && $this->destination_warehouse_id) {
            $destStock = ProductStock::getOrCreate(
                $this->company_id,
                $this->product_id,
                $this->destination_warehouse_id
            );
            $destStock->quantity += $this->quantity;
            $destStock->save();
        }

        $this->status = 'validated';
        $this->validated_by = $user?->id ?? auth()->id();
        $this->validated_at = now();
        $this->save();
    }

    public function cancel(?User $user = null): void
    {
        if ($this->status !== 'validated') return;

        // Reverse stock movement
        $stock = ProductStock::getOrCreate(
            $this->company_id,
            $this->product_id,
            $this->warehouse_id
        );

        $quantityChange = $this->isIncoming() ? -$this->quantity : $this->quantity;
        $stock->quantity += $quantityChange;
        $stock->save();

        // Handle transfers
        if ($this->isTransfer() && $this->destination_warehouse_id) {
            $destStock = ProductStock::getOrCreate(
                $this->company_id,
                $this->product_id,
                $this->destination_warehouse_id
            );
            $destStock->quantity -= $this->quantity;
            $destStock->save();
        }

        $this->status = 'cancelled';
        $this->save();
    }

    // Reference generation
    public static function generateReference(string $companyId): string
    {
        $year = date('Y');
        $prefix = 'MVT';

        $lastRef = static::where('company_id', $companyId)
            ->where('reference', 'like', "{$prefix}-{$year}-%")
            ->orderByDesc('reference')
            ->value('reference');

        if ($lastRef) {
            $lastNumber = (int) substr($lastRef, -5);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s-%05d', $prefix, $year, $nextNumber);
    }

    // Stats
    public static function getStats(string $companyId, ?string $warehouseId = null, ?array $period = null): array
    {
        $query = static::where('company_id', $companyId)->validated();

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($period) {
            $query->whereBetween('created_at', $period);
        }

        $movements = $query->get();

        return [
            'total' => $movements->count(),
            'incoming' => $movements->where('type', 'in')->count(),
            'outgoing' => $movements->where('type', 'out')->count(),
            'transfers' => $movements->where('type', 'transfer')->count(),
            'adjustments' => $movements->where('type', 'adjustment')->count(),
            'total_in_value' => $movements->whereIn('type', ['in', 'production'])->sum('total_cost'),
            'total_out_value' => $movements->whereIn('type', ['out', 'consumption'])->sum('total_cost'),
        ];
    }
}
