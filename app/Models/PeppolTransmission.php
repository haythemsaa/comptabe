<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeppolTransmission extends Model
{
    use HasFactory, HasUuid, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'invoice_id',
        'direction',
        'sender_id',
        'receiver_id',
        'document_type',
        'message_id',
        'status',
        'error_message',
        'sent_at',
        'delivered_at',
        'mdn_received_at',
        'request_payload',
        'response_payload',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'mdn_received_at' => 'datetime',
        ];
    }

    /**
     * Status labels.
     */
    public const STATUS_LABELS = [
        'pending' => 'En attente',
        'sent' => 'Envoyé',
        'delivered' => 'Délivré',
        'failed' => 'Échoué',
    ];

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Invoice.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Check if transmission is outbound.
     */
    public function isOutbound(): bool
    {
        return $this->direction === 'outbound';
    }

    /**
     * Check if transmission is inbound.
     */
    public function isInbound(): bool
    {
        return $this->direction === 'inbound';
    }

    /**
     * Check if transmission was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Scope for outbound transmissions.
     */
    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    /**
     * Scope for inbound transmissions.
     */
    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    /**
     * Scope for failed transmissions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
