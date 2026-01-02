<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringInvoiceLine extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'recurring_invoice_id',
        'line_number',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_percent',
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
            'line_total' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
        ];
    }

    /**
     * Recurring invoice relationship.
     */
    public function recurringInvoice(): BelongsTo
    {
        return $this->belongsTo(RecurringInvoice::class);
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

        if ($this->discount_percent > 0) {
            $subtotal = $subtotal * (1 - $this->discount_percent / 100);
        }

        $this->line_total = round($subtotal, 2);
        $this->vat_amount = round($this->line_total * $this->vat_rate / 100, 2);
    }

    /**
     * Boot method.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($line) {
            $line->calculateTotals();
        });
    }
}
