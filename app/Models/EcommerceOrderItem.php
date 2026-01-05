<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EcommerceOrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'external_product_id',
        'sku',
        'name',
        'quantity',
        'unit_price',
        'tax_amount',
        'discount_amount',
        'total',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(EcommerceOrder::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    public function getNetAmountAttribute(): float
    {
        return $this->subtotal - $this->discount_amount;
    }
}
