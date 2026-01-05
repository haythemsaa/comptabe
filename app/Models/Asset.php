<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Asset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'category_id',
        'partner_id',
        'invoice_id',
        'reference',
        'name',
        'description',
        'serial_number',
        'location',
        'acquisition_date',
        'service_date',
        'disposal_date',
        'acquisition_cost',
        'residual_value',
        'current_value',
        'accumulated_depreciation',
        'depreciation_method',
        'useful_life',
        'degressive_rate',
        'total_units',
        'units_produced',
        'status',
        'disposal_amount',
        'disposal_notes',
        'metadata',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'service_date' => 'date',
        'disposal_date' => 'date',
        'acquisition_cost' => 'decimal:2',
        'residual_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'useful_life' => 'decimal:2',
        'degressive_rate' => 'decimal:2',
        'total_units' => 'integer',
        'units_produced' => 'integer',
        'disposal_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($asset) {
            if (empty($asset->reference)) {
                $asset->reference = static::generateReference($asset->company_id);
            }
            if (empty($asset->current_value)) {
                $asset->current_value = $asset->acquisition_cost;
            }
        });
    }

    public static function generateReference($companyId): string
    {
        $year = date('Y');
        $count = static::where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('IMM-%s-%04d', $year, $count);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function depreciations(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class)->orderBy('period_start');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AssetLog::class)->orderByDesc('created_at');
    }

    public function vehicle(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Vehicle::class);
    }

    public function getDepreciableAmountAttribute(): float
    {
        return $this->acquisition_cost - $this->residual_value;
    }

    public function getAnnualDepreciationAttribute(): float
    {
        if ($this->depreciation_method === 'linear') {
            return $this->useful_life > 0 ? $this->depreciable_amount / $this->useful_life : 0;
        }

        if ($this->depreciation_method === 'degressive') {
            $rate = $this->degressive_rate ?? $this->calculateDegressiveRate();
            return $this->current_value * ($rate / 100);
        }

        return 0;
    }

    public function getMonthlyDepreciationAttribute(): float
    {
        return $this->annual_depreciation / 12;
    }

    public function getDepreciationRateAttribute(): float
    {
        return $this->useful_life > 0 ? 100 / $this->useful_life : 0;
    }

    public function getRemainingValueAttribute(): float
    {
        return max(0, $this->current_value - $this->residual_value);
    }

    public function getDepreciationPercentAttribute(): float
    {
        if ($this->depreciable_amount <= 0) return 100;
        return min(100, ($this->accumulated_depreciation / $this->depreciable_amount) * 100);
    }

    public function getAgeInMonthsAttribute(): int
    {
        return $this->service_date->diffInMonths(now());
    }

    public function getRemainingMonthsAttribute(): int
    {
        $totalMonths = $this->useful_life * 12;
        return max(0, $totalMonths - $this->age_in_months);
    }

    public function calculateDegressiveRate(): float
    {
        // Coefficients dégressifs belges selon durée d'amortissement
        $rate = $this->depreciation_rate;

        if ($this->useful_life <= 4) {
            return $rate * 1.5;
        } elseif ($this->useful_life <= 6) {
            return $rate * 2;
        } else {
            return $rate * 2.5;
        }
    }

    public function generateDepreciationSchedule(): array
    {
        $schedule = [];
        $currentValue = $this->acquisition_cost;
        $accumulated = 0;
        $startDate = $this->service_date->copy();

        for ($year = 1; $year <= ceil($this->useful_life); $year++) {
            $periodStart = $startDate->copy();
            $periodEnd = $periodStart->copy()->addYear()->subDay();

            // Calcul au prorata pour la première année
            $monthsInYear = 12;
            if ($year === 1 && $this->service_date->month > 1) {
                $monthsInYear = 13 - $this->service_date->month;
            }

            $depreciation = $this->calculateYearDepreciation($currentValue, $year, $monthsInYear);

            // Ne pas dépasser la valeur résiduelle
            $maxDepreciation = $currentValue - $this->residual_value;
            $depreciation = min($depreciation, $maxDepreciation);

            if ($depreciation <= 0) break;

            $accumulated += $depreciation;
            $currentValue -= $depreciation;

            $schedule[] = [
                'year_number' => $year,
                'period_start' => $periodStart->format('Y-m-d'),
                'period_end' => $periodEnd->format('Y-m-d'),
                'depreciation_amount' => round($depreciation, 2),
                'accumulated_depreciation' => round($accumulated, 2),
                'book_value' => round($currentValue, 2),
            ];

            $startDate->addYear();

            if ($currentValue <= $this->residual_value) break;
        }

        return $schedule;
    }

    protected function calculateYearDepreciation(float $currentValue, int $year, int $months = 12): float
    {
        $factor = $months / 12;

        if ($this->depreciation_method === 'linear') {
            return ($this->annual_depreciation) * $factor;
        }

        if ($this->depreciation_method === 'degressive') {
            $rate = $this->degressive_rate ?? $this->calculateDegressiveRate();
            $degressiveAmount = $currentValue * ($rate / 100) * $factor;
            $linearAmount = $this->annual_depreciation * $factor;

            // Basculer au linéaire quand c'est plus avantageux
            return max($degressiveAmount, $linearAmount);
        }

        return 0;
    }

    public function activate(): void
    {
        $this->update(['status' => 'active']);
        $this->log('activated', 'Immobilisation mise en service');
    }

    public function dispose(float $amount = null, string $notes = null): void
    {
        $this->update([
            'status' => 'disposed',
            'disposal_date' => now(),
            'disposal_amount' => $amount,
            'disposal_notes' => $notes,
        ]);
        $this->log('disposed', $notes ?? 'Immobilisation sortie');
    }

    public function sell(float $amount, string $notes = null): void
    {
        $this->update([
            'status' => 'sold',
            'disposal_date' => now(),
            'disposal_amount' => $amount,
            'disposal_notes' => $notes,
        ]);
        $this->log('sold', "Vendu pour {$amount} €");
    }

    public function log(string $event, string $description = null, array $oldValues = [], array $newValues = []): AssetLog
    {
        return $this->logs()->create([
            'event' => $event,
            'description' => $description,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'user_id' => auth()->id(),
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFullyDepreciated($query)
    {
        return $query->where('status', 'fully_depreciated');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
