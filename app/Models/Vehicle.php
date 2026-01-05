<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'asset_id',
        'assigned_user_id',
        'reference',
        'license_plate',
        'vin',
        'brand',
        'model',
        'year',
        'type',
        'fuel_type',
        'ownership',
        'co2_emission',
        'emission_standard',
        'fiscal_horsepower',
        'engine_power_kw',
        'battery_capacity_kwh',
        'catalog_value',
        'options_value',
        'first_registration_date',
        'acquisition_date',
        'disposal_date',
        'odometer_start',
        'odometer_current',
        'status',
        'insurance_company',
        'insurance_policy_number',
        'insurance_expiry_date',
        'technical_inspection_date',
        'metadata',
    ];

    protected $casts = [
        'year' => 'integer',
        'co2_emission' => 'integer',
        'fiscal_horsepower' => 'integer',
        'engine_power_kw' => 'integer',
        'battery_capacity_kwh' => 'integer',
        'catalog_value' => 'decimal:2',
        'options_value' => 'decimal:2',
        'first_registration_date' => 'date',
        'acquisition_date' => 'date',
        'disposal_date' => 'date',
        'odometer_start' => 'integer',
        'odometer_current' => 'integer',
        'insurance_expiry_date' => 'date',
        'technical_inspection_date' => 'date',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vehicle) {
            if (empty($vehicle->reference)) {
                $vehicle->reference = static::generateReference($vehicle->company_id);
            }
        });
    }

    public static function generateReference($companyId): string
    {
        $year = date('Y');
        $count = static::where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('VEH-%s-%04d', $year, $count);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function atnRecords(): HasMany
    {
        return $this->hasMany(VehicleAtn::class)->orderByDesc('year')->orderByDesc('month');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(VehicleContract::class);
    }

    public function activeContract(): HasOne
    {
        return $this->hasOne(VehicleContract::class)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->latest('start_date');
    }

    public function odometerReadings(): HasMany
    {
        return $this->hasMany(VehicleOdometerReading::class)->orderByDesc('reading_date');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(VehicleExpense::class)->orderByDesc('expense_date');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(VehicleReservation::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(VehicleReminder::class);
    }

    public function pendingReminders(): HasMany
    {
        return $this->hasMany(VehicleReminder::class)
            ->whereIn('status', ['pending', 'notified'])
            ->orderBy('due_date');
    }

    // Labels
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'car' => 'Voiture',
            'van' => 'Camionnette',
            'truck' => 'Camion',
            'motorcycle' => 'Moto',
            'electric_bike' => 'Vélo électrique',
            'other' => 'Autre',
            default => $this->type,
        };
    }

    public function getFuelTypeLabelAttribute(): string
    {
        return match ($this->fuel_type) {
            'petrol' => 'Essence',
            'diesel' => 'Diesel',
            'hybrid' => 'Hybride',
            'electric' => 'Électrique',
            'lpg' => 'GPL',
            'cng' => 'GNC',
            'hydrogen' => 'Hydrogène',
            default => $this->fuel_type,
        };
    }

    public function getOwnershipLabelAttribute(): string
    {
        return match ($this->ownership) {
            'owned' => 'Propriété',
            'leased' => 'Leasing',
            'rented' => 'Location',
            'employee_owned' => 'Véhicule employé',
            default => $this->ownership,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Actif',
            'maintenance' => 'En maintenance',
            'disposed' => 'Sorti',
            'sold' => 'Vendu',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'green',
            'maintenance' => 'yellow',
            'disposed', 'sold' => 'red',
            default => 'gray',
        };
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->brand} {$this->model}" . ($this->year ? " ({$this->year})" : '');
    }

    public function getTotalValueAttribute(): float
    {
        return ($this->catalog_value ?? 0) + ($this->options_value ?? 0);
    }

    public function getTotalKmAttribute(): int
    {
        return $this->odometer_current - $this->odometer_start;
    }

    public function getAgeInMonthsAttribute(): int
    {
        if (!$this->first_registration_date) return 0;
        return $this->first_registration_date->diffInMonths(now());
    }

    public function isElectric(): bool
    {
        return $this->fuel_type === 'electric';
    }

    public function isHybrid(): bool
    {
        return $this->fuel_type === 'hybrid';
    }

    // Belgian ATN (Avantage de Toute Nature) calculation
    public function calculateAtn(int $year = null, int $month = null): array
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        // CO2 reference values (Belgium 2024 - need to be updated yearly)
        $co2Reference = $this->getCo2Reference($year);
        $catalogValue = $this->total_value;

        // Calculate CO2 coefficient
        $co2Coefficient = $this->calculateCo2Coefficient($co2Reference);

        // Age reduction (6% per year started, max 30%)
        $ageInYears = $this->age_in_months / 12;
        $ageReduction = min(0.30, floor($ageInYears) * 0.06);
        $adjustedCatalogValue = $catalogValue * (1 - $ageReduction);

        // Minimum catalog value (€ 6,000 for 2024)
        $minCatalogValue = $this->getMinCatalogValue($year);
        $adjustedCatalogValue = max($adjustedCatalogValue, $minCatalogValue);

        // Calculate ATN
        $atnAnnual = $adjustedCatalogValue * $co2Coefficient;

        // Minimum ATN (€ 1,600 for 2024)
        $minAtn = $this->getMinAtn($year);
        $atnAnnual = max($atnAnnual, $minAtn);

        $atnMonthly = $atnAnnual / 12;

        // Employer solidarity contribution (17% for 2024)
        $solidarityContribution = $atnMonthly * 0.17;

        return [
            'catalog_value' => $catalogValue,
            'co2_reference' => $co2Reference,
            'co2_coefficient' => $co2Coefficient,
            'age_reduction' => $ageReduction,
            'adjusted_catalog_value' => $adjustedCatalogValue,
            'atn_annual' => round($atnAnnual, 2),
            'atn_monthly' => round($atnMonthly, 2),
            'solidarity_contribution' => round($solidarityContribution, 2),
        ];
    }

    protected function getCo2Reference(int $year): float
    {
        // CO2 reference values for Belgium (need to be updated yearly)
        $references = [
            2024 => ['petrol' => 78, 'diesel' => 65, 'other' => 78],
            2025 => ['petrol' => 75, 'diesel' => 62, 'other' => 75],
            2026 => ['petrol' => 72, 'diesel' => 59, 'other' => 72],
        ];

        $fuelCategory = in_array($this->fuel_type, ['diesel']) ? 'diesel' : 'petrol';
        return $references[$year][$fuelCategory] ?? $references[2024][$fuelCategory];
    }

    protected function calculateCo2Coefficient(float $co2Reference): float
    {
        $co2 = $this->co2_emission ?? 0;

        // Base coefficient 5.5%
        $coefficient = 0.055;

        // Adjustment based on CO2
        $difference = $co2 - $co2Reference;
        $adjustment = $difference * 0.001; // 0.1% per g/km

        $coefficient += $adjustment;

        // Minimum 4%, maximum 18%
        return max(0.04, min(0.18, $coefficient));
    }

    protected function getMinCatalogValue(int $year): float
    {
        // Minimum catalog values (indexed yearly)
        return match ($year) {
            2024 => 6000,
            2025 => 6200,
            2026 => 6400,
            default => 6000,
        };
    }

    protected function getMinAtn(int $year): float
    {
        // Minimum ATN values (indexed yearly)
        return match ($year) {
            2024 => 1600,
            2025 => 1650,
            2026 => 1700,
            default => 1600,
        };
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_user_id', $userId);
    }
}
