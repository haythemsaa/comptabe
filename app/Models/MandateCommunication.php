<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MandateCommunication extends Model
{
    use HasFactory, HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'client_mandate_id',
        'sender_id',
        'sender_type',
        'subject',
        'message',
        'attachments',
        'is_read',
        'read_at',
        'parent_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Sender type labels.
     */
    public const SENDER_TYPE_LABELS = [
        'cabinet' => 'Cabinet',
        'client' => 'Client',
    ];

    /**
     * Check if message is from cabinet.
     */
    public function isFromCabinet(): bool
    {
        return $this->sender_type === 'cabinet';
    }

    /**
     * Check if message is from client.
     */
    public function isFromClient(): bool
    {
        return $this->sender_type === 'client';
    }

    /**
     * Check if message is a reply.
     */
    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * Mark as read.
     */
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Client mandate.
     */
    public function clientMandate(): BelongsTo
    {
        return $this->belongsTo(ClientMandate::class);
    }

    /**
     * Sender.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Parent message (for replies).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MandateCommunication::class, 'parent_id');
    }

    /**
     * Replies.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(MandateCommunication::class, 'parent_id')
            ->orderBy('created_at');
    }

    /**
     * Scope for unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for messages from cabinet.
     */
    public function scopeFromCabinet($query)
    {
        return $query->where('sender_type', 'cabinet');
    }

    /**
     * Scope for messages from client.
     */
    public function scopeFromClient($query)
    {
        return $query->where('sender_type', 'client');
    }

    /**
     * Scope for root messages (not replies).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
