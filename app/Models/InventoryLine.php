<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLine extends Model
{
    use HasUuid;

    protected $fillable = [
        'inventory_session_id',
        'product_id',
        'location',
        'expected_quantity',
        'counted_quantity',
        'difference',
        'unit_cost',
        'value_difference',
        'status',
        'counted_by',
        'counted_at',
        'notes',
    ];

    protected $casts = [
        'expected_quantity' => 'decimal:4',
        'counted_quantity' => 'decimal:4',
        'difference' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'value_difference' => 'decimal:2',
        'counted_at' => 'datetime',
    ];

    public const STATUSES = [
        'pending' => ['label' => 'En attente', 'color' => 'secondary'],
        'counted' => ['label' => 'Compté', 'color' => 'primary'],
        'verified' => ['label' => 'Vérifié', 'color' => 'info'],
        'adjusted' => ['label' => 'Ajusté', 'color' => 'success'],
    ];

    // Relations
    public function inventorySession(): BelongsTo
    {
        return $this->belongsTo(InventorySession::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function countedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCounted($query)
    {
        return $query->whereIn('status', ['counted', 'verified', 'adjusted']);
    }

    public function scopeWithDiscrepancy($query)
    {
        return $query->where('difference', '!=', 0)->whereNotNull('difference');
    }

    // Helpers
    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status]['label'] ?? $this->status;
    }

    public function getStatusColor(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'secondary';
    }

    public function hasDiscrepancy(): bool
    {
        return $this->difference !== null && $this->difference != 0;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCounted(): bool
    {
        return in_array($this->status, ['counted', 'verified', 'adjusted']);
    }

    // Actions
    public function recordCount(float $quantity, ?User $user = null, ?string $notes = null): void
    {
        $this->counted_quantity = $quantity;
        $this->difference = $quantity - $this->expected_quantity;
        $this->value_difference = $this->difference * ($this->unit_cost ?? 0);
        $this->status = 'counted';
        $this->counted_by = $user?->id ?? auth()->id();
        $this->counted_at = now();
        $this->notes = $notes;
        $this->save();

        // Update session stats
        $this->inventorySession->updateStats();
    }

    public function verify(): void
    {
        if ($this->status !== 'counted') return;
        $this->status = 'verified';
        $this->save();
    }
}
