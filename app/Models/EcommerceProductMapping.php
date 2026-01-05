<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EcommerceProductMapping extends Model
{
    protected $fillable = [
        'connection_id',
        'product_id',
        'external_id',
        'external_sku',
        'external_name',
        'sync_stock',
        'sync_price',
        'last_synced_at',
    ];

    protected $casts = [
        'sync_stock' => 'boolean',
        'sync_price' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function connection(): BelongsTo
    {
        return $this->belongsTo(EcommerceConnection::class, 'connection_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
