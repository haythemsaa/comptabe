<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetLog extends Model
{
    protected $fillable = [
        'asset_id',
        'event',
        'description',
        'old_values',
        'new_values',
        'user_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getEventLabelAttribute(): string
    {
        return match ($this->event) {
            'created' => 'Créé',
            'activated' => 'Mis en service',
            'depreciation_posted' => 'Amortissement comptabilisé',
            'revalued' => 'Réévalué',
            'impaired' => 'Déprécié',
            'disposed' => 'Sorti',
            'sold' => 'Vendu',
            'transferred' => 'Transféré',
            'modified' => 'Modifié',
            default => $this->event,
        };
    }

    public function getEventColorAttribute(): string
    {
        return match ($this->event) {
            'activated' => 'green',
            'depreciation_posted' => 'blue',
            'disposed', 'sold' => 'red',
            'revalued', 'impaired' => 'yellow',
            default => 'gray',
        };
    }
}
