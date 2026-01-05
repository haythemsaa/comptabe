<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;

class InventorySession extends Model
{
    use HasUuid, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'reference',
        'name',
        'description',
        'warehouse_id',
        'type',
        'status',
        'scheduled_date',
        'started_at',
        'completed_at',
        'validated_at',
        'created_by',
        'validated_by',
        'filters',
        'total_products',
        'counted_products',
        'discrepancies',
        'total_value_difference',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'validated_at' => 'datetime',
        'filters' => 'array',
        'total_products' => 'integer',
        'counted_products' => 'integer',
        'discrepancies' => 'integer',
        'total_value_difference' => 'decimal:2',
    ];

    public const TYPES = [
        'full' => ['label' => 'Inventaire complet', 'color' => 'primary'],
        'partial' => ['label' => 'Inventaire partiel', 'color' => 'info'],
        'cycle' => ['label' => 'Comptage cyclique', 'color' => 'success'],
        'spot' => ['label' => 'Vérification ponctuelle', 'color' => 'warning'],
    ];

    public const STATUSES = [
        'draft' => ['label' => 'Brouillon', 'color' => 'secondary'],
        'in_progress' => ['label' => 'En cours', 'color' => 'primary'],
        'review' => ['label' => 'En révision', 'color' => 'warning'],
        'validated' => ['label' => 'Validé', 'color' => 'success'],
        'cancelled' => ['label' => 'Annulé', 'color' => 'danger'],
    ];

    // Relations
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InventoryLine::class);
    }

    // Scopes
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeValidated($query)
    {
        return $query->where('status', 'validated');
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

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status]['label'] ?? $this->status;
    }

    public function getStatusColor(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'secondary';
    }

    public function getProgressPercentage(): int
    {
        if ($this->total_products === 0) return 0;
        return (int) round(($this->counted_products / $this->total_products) * 100);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isValidated(): bool
    {
        return $this->status === 'validated';
    }

    public function canBeStarted(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeValidated(): bool
    {
        return in_array($this->status, ['in_progress', 'review']);
    }

    // Actions
    public function start(): void
    {
        if (!$this->canBeStarted()) return;

        $this->status = 'in_progress';
        $this->started_at = now();
        $this->save();
    }

    public function generateLines(): void
    {
        // Get products in warehouse
        $productStocks = ProductStock::where('warehouse_id', $this->warehouse_id)
            ->with('product')
            ->get();

        foreach ($productStocks as $stock) {
            InventoryLine::create([
                'inventory_session_id' => $this->id,
                'product_id' => $stock->product_id,
                'location' => $stock->location,
                'expected_quantity' => $stock->quantity,
                'unit_cost' => $stock->product->cost_price ?? 0,
                'status' => 'pending',
            ]);
        }

        $this->total_products = $productStocks->count();
        $this->save();
    }

    public function updateStats(): void
    {
        $lines = $this->lines;
        $this->counted_products = $lines->where('status', '!=', 'pending')->count();
        $this->discrepancies = $lines->where('difference', '!=', 0)->whereNotNull('difference')->count();
        $this->total_value_difference = $lines->sum('value_difference') ?? 0;
        $this->save();
    }

    public function validate(?User $user = null): void
    {
        if (!$this->canBeValidated()) return;

        // Create stock movements for discrepancies
        foreach ($this->lines()->where('difference', '!=', 0)->whereNotNull('difference')->get() as $line) {
            $stock = ProductStock::getOrCreate(
                $this->company_id,
                $line->product_id,
                $this->warehouse_id
            );

            StockMovement::create([
                'company_id' => $this->company_id,
                'reference' => StockMovement::generateReference($this->company_id),
                'product_id' => $line->product_id,
                'warehouse_id' => $this->warehouse_id,
                'type' => 'adjustment',
                'reason' => 'inventory',
                'quantity' => abs($line->difference),
                'quantity_before' => $stock->quantity,
                'quantity_after' => $line->counted_quantity,
                'unit_cost' => $line->unit_cost,
                'total_cost' => abs($line->difference) * $line->unit_cost,
                'source_type' => InventorySession::class,
                'source_id' => $this->id,
                'notes' => "Ajustement inventaire {$this->reference}",
                'created_by' => $user?->id ?? auth()->id(),
                'validated_by' => $user?->id ?? auth()->id(),
                'validated_at' => now(),
                'status' => 'validated',
            ]);

            // Update stock
            $stock->quantity = $line->counted_quantity;
            $stock->last_counted_at = now();
            $stock->last_counted_quantity = $line->counted_quantity;
            $stock->save();

            $line->status = 'adjusted';
            $line->save();
        }

        $this->status = 'validated';
        $this->validated_at = now();
        $this->validated_by = $user?->id ?? auth()->id();
        $this->completed_at = now();
        $this->save();
    }

    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->save();
    }

    // Reference generation
    public static function generateReference(string $companyId): string
    {
        $year = date('Y');
        $prefix = 'INV';

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
}
