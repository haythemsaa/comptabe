<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'invoice_id',
        'line_number',
        'product_code',
        'description',
        'quantity',
        'unit_code',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'line_amount',
        'vat_category',
        'vat_rate',
        'vat_amount',
        'account_id',
        'analytic_account_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'line_amount' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
        ];
    }

    /**
     * Invoice relationship.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Account relationship.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    /**
     * Calculate line amount.
     */
    public function calculateAmount(): void
    {
        $subtotal = $this->quantity * $this->unit_price;

        if ($this->discount_percent > 0) {
            $this->discount_amount = round($subtotal * ($this->discount_percent / 100), 2);
        }

        $this->line_amount = round($subtotal - $this->discount_amount, 2);

        // Belgian 2026 rule: VAT rounded at total level, not per line
        // But we store the non-rounded VAT per line for calculation
        $this->vat_amount = round($this->line_amount * ($this->vat_rate / 100), 2);
    }

    /**
     * Get gross amount before discount.
     */
    public function getGrossAmountAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Get total including VAT.
     */
    public function getTotalInclVatAttribute(): float
    {
        return $this->line_amount + $this->vat_amount;
    }

    /**
     * Alias for line_amount (total excluding VAT).
     */
    public function getTotalExclVatAttribute(): float
    {
        return $this->line_amount ?? 0;
    }

    /**
     * Boot model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($line) {
            $line->calculateAmount();
        });

        static::saved(function ($line) {
            // Recalculate invoice totals
            $line->invoice->calculateTotals();
            $line->invoice->save();
        });

        static::deleted(function ($line) {
            // Recalculate invoice totals
            $line->invoice->calculateTotals();
            $line->invoice->save();
        });
    }
}
