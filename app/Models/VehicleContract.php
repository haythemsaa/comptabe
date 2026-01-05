<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleContract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'partner_id',
        'contract_number',
        'type',
        'start_date',
        'end_date',
        'duration_months',
        'annual_km_limit',
        'monthly_payment',
        'deposit',
        'residual_value',
        'purchase_option',
        'includes_maintenance',
        'includes_insurance',
        'includes_tyres',
        'includes_fuel_card',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'duration_months' => 'integer',
        'annual_km_limit' => 'integer',
        'monthly_payment' => 'decimal:2',
        'deposit' => 'decimal:2',
        'residual_value' => 'decimal:2',
        'purchase_option' => 'decimal:2',
        'includes_maintenance' => 'boolean',
        'includes_insurance' => 'boolean',
        'includes_tyres' => 'boolean',
        'includes_fuel_card' => 'boolean',
        'metadata' => 'array',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'leasing' => 'Leasing',
            'renting' => 'Location',
            'loan' => 'Crédit',
            default => $this->type,
        };
    }

    public function isActive(): bool
    {
        return $this->start_date->lte(now()) && $this->end_date->gte(now());
    }

    public function getRemainingMonthsAttribute(): int
    {
        if ($this->end_date->lt(now())) return 0;
        return now()->diffInMonths($this->end_date);
    }

    public function getTotalCostAttribute(): float
    {
        return $this->monthly_payment * $this->duration_months + $this->deposit;
    }

    public function getIncludedServicesAttribute(): array
    {
        $services = [];
        if ($this->includes_maintenance) $services[] = 'Entretien';
        if ($this->includes_insurance) $services[] = 'Assurance';
        if ($this->includes_tyres) $services[] = 'Pneus';
        if ($this->includes_fuel_card) $services[] = 'Carte carburant';
        return $services;
    }

    public function scopeActive($query)
    {
        return $query->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now());
    }

    public function scopeExpiringSoon($query, int $days = 90)
    {
        return $query->whereBetween('end_date', [now(), now()->addDays($days)]);
    }
}
