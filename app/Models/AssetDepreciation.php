<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDepreciation extends Model
{
    protected $fillable = [
        'asset_id',
        'period_start',
        'period_end',
        'year_number',
        'depreciation_amount',
        'accumulated_depreciation',
        'book_value',
        'status',
        'accounting_entry_id',
        'posted_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'year_number' => 'integer',
        'depreciation_amount' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'book_value' => 'decimal:2',
        'posted_at' => 'date',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function isPlanned(): bool
    {
        return $this->status === 'planned';
    }

    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    public function canBePosted(): bool
    {
        return $this->isPlanned() && $this->period_end->lte(now());
    }

    public function post(): void
    {
        $this->update([
            'status' => 'posted',
            'posted_at' => now(),
        ]);

        // Mettre à jour les valeurs de l'immobilisation
        $this->asset->update([
            'accumulated_depreciation' => $this->accumulated_depreciation,
            'current_value' => $this->book_value,
        ]);

        if ($this->book_value <= $this->asset->residual_value) {
            $this->asset->update(['status' => 'fully_depreciated']);
        }

        $this->asset->log('depreciation_posted', "Amortissement année {$this->year_number} comptabilisé: {$this->depreciation_amount} €");
    }

    public function scopePlanned($query)
    {
        return $query->where('status', 'planned');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeDueForPosting($query)
    {
        return $query->where('status', 'planned')
            ->whereDate('period_end', '<=', now());
    }
}
