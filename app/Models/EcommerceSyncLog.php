<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EcommerceSyncLog extends Model
{
    protected $fillable = [
        'connection_id',
        'type',
        'direction',
        'status',
        'items_processed',
        'items_created',
        'items_updated',
        'items_failed',
        'error_message',
        'details',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'items_processed' => 'integer',
        'items_created' => 'integer',
        'items_updated' => 'integer',
        'items_failed' => 'integer',
        'details' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function connection(): BelongsTo
    {
        return $this->belongsTo(EcommerceConnection::class, 'connection_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'orders' => 'Commandes',
            'products' => 'Produits',
            'customers' => 'Clients',
            'stock' => 'Stock',
            'manual' => 'Manuel',
            default => $this->type,
        };
    }

    public function getDirectionLabelAttribute(): string
    {
        return match ($this->direction) {
            'import' => 'Import',
            'export' => 'Export',
            default => $this->direction,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'started' => 'En cours',
            'completed' => 'Terminé',
            'failed' => 'Échec',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'green',
            'started' => 'blue',
            'failed' => 'red',
            default => 'gray',
        };
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        $seconds = $this->started_at->diffInSeconds($this->completed_at);

        if ($seconds < 60) {
            return "{$seconds}s";
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return "{$minutes}m {$remainingSeconds}s";
    }
}
