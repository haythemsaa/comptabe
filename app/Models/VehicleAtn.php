<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleAtn extends Model
{
    protected $table = 'vehicle_atn';

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'year',
        'month',
        'catalog_value',
        'co2_reference',
        'co2_coefficient',
        'minimum_coefficient',
        'age_coefficient',
        'atn_amount',
        'atn_annual',
        'employer_solidarity_contribution',
        'is_calculated',
        'calculation_details',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'catalog_value' => 'decimal:2',
        'co2_reference' => 'decimal:2',
        'co2_coefficient' => 'decimal:4',
        'minimum_coefficient' => 'decimal:4',
        'age_coefficient' => 'decimal:4',
        'atn_amount' => 'decimal:2',
        'atn_annual' => 'decimal:2',
        'employer_solidarity_contribution' => 'decimal:2',
        'is_calculated' => 'boolean',
        'calculation_details' => 'array',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPeriodLabelAttribute(): string
    {
        $months = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];

        return ($months[$this->month] ?? $this->month) . ' ' . $this->year;
    }

    public static function calculateForVehicle(Vehicle $vehicle, int $year, int $month): self
    {
        $atnData = $vehicle->calculateAtn($year, $month);

        return static::updateOrCreate(
            [
                'vehicle_id' => $vehicle->id,
                'year' => $year,
                'month' => $month,
            ],
            [
                'user_id' => $vehicle->assigned_user_id,
                'catalog_value' => $atnData['catalog_value'],
                'co2_reference' => $atnData['co2_reference'],
                'co2_coefficient' => $atnData['co2_coefficient'],
                'minimum_coefficient' => 0.04,
                'age_coefficient' => $atnData['age_reduction'],
                'atn_amount' => $atnData['atn_monthly'],
                'atn_annual' => $atnData['atn_annual'],
                'employer_solidarity_contribution' => $atnData['solidarity_contribution'],
                'is_calculated' => true,
                'calculation_details' => $atnData,
            ]
        );
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
