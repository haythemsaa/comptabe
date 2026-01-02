<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'webhook_id',
        'delivery_id',
        'event',
        'payload',
        'request_headers',
        'response_status',
        'response_headers',
        'response_body',
        'response_time_ms',
        'success',
        'error_message',
        'delivered_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'request_headers' => 'array',
        'response_headers' => 'array',
        'success' => 'boolean',
        'delivered_at' => 'datetime',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }
}
