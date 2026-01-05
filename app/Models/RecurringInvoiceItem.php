<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringInvoiceItem extends Model
{
    protected $fillable = [
        'recurring_invoice_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'vat_rate',
        'discount_percent',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function recurringInvoice(): BelongsTo
    {
        return $this->belongsTo(RecurringInvoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    public function getDiscountAmountAttribute(): float
    {
        return $this->subtotal * ($this->discount_percent / 100);
    }

    public function getNetAmountAttribute(): float
    {
        return $this->subtotal - $this->discount_amount;
    }

    public function getVatAmountAttribute(): float
    {
        return $this->net_amount * ($this->vat_rate / 100);
    }

    public function getTotalAttribute(): float
    {
        return $this->net_amount + $this->vat_amount;
    }
}
