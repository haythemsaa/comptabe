<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ExchangeRateDifference extends Model
{
    protected $fillable = [
        'company_id',
        'documentable_type',
        'documentable_id',
        'transaction_date',
        'settlement_date',
        'currency',
        'original_rate',
        'settlement_rate',
        'original_amount',
        'difference_amount',
        'type',
        'is_gain',
        'accounting_entry_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'settlement_date' => 'date',
        'original_rate' => 'decimal:8',
        'settlement_rate' => 'decimal:8',
        'original_amount' => 'decimal:2',
        'difference_amount' => 'decimal:2',
        'is_gain' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'realized' => 'Réalisé',
            'unrealized' => 'Non réalisé',
            default => $this->type,
        };
    }

    public function getResultLabelAttribute(): string
    {
        return $this->is_gain ? 'Gain de change' : 'Perte de change';
    }

    public function getResultColorAttribute(): string
    {
        return $this->is_gain ? 'green' : 'red';
    }

    public static function calculateForInvoice(Invoice $invoice, float $settlementRate): self
    {
        $originalRate = $invoice->exchange_rate;
        $originalAmountBase = $invoice->total_amount / $originalRate;
        $settlementAmountBase = $invoice->total_amount / $settlementRate;
        $difference = $settlementAmountBase - $originalAmountBase;

        return static::create([
            'company_id' => $invoice->company_id,
            'documentable_type' => Invoice::class,
            'documentable_id' => $invoice->id,
            'transaction_date' => $invoice->issue_date,
            'settlement_date' => now(),
            'currency' => $invoice->currency,
            'original_rate' => $originalRate,
            'settlement_rate' => $settlementRate,
            'original_amount' => $invoice->total_amount,
            'difference_amount' => abs($difference),
            'type' => 'realized',
            'is_gain' => $difference > 0,
        ]);
    }

    public function scopeGains($query)
    {
        return $query->where('is_gain', true);
    }

    public function scopeLosses($query)
    {
        return $query->where('is_gain', false);
    }

    public function scopeRealized($query)
    {
        return $query->where('type', 'realized');
    }

    public function scopeUnrealized($query)
    {
        return $query->where('type', 'unrealized');
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('settlement_date', [$startDate, $endDate]);
    }
}
