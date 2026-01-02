<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteLine extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'quote_id',
        'line_number',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'line_total',
        'vat_category',
        'vat_rate',
        'vat_amount',
        'account_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
        ];
    }

    /**
     * Quote relationship.
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * Account relationship.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    /**
     * Calculate line totals.
     */
    public function calculateTotals(): void
    {
        $subtotal = $this->quantity * $this->unit_price;

        // Apply line discount
        if ($this->discount_percent > 0) {
            $this->discount_amount = round($subtotal * $this->discount_percent / 100, 2);
        }

        $this->line_total = $subtotal - $this->discount_amount;
        $this->vat_amount = round($this->line_total * $this->vat_rate / 100, 2);
    }

    /**
     * Get line total including VAT.
     */
    public function getTotalInclVatAttribute(): float
    {
        return $this->line_total + $this->vat_amount;
    }

    /**
     * Boot method to auto-calculate totals.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($line) {
            $line->calculateTotals();
        });
    }
}
