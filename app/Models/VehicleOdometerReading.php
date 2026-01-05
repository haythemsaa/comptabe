<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleOdometerReading extends Model
{
    protected $fillable = [
        'vehicle_id',
        'user_id',
        'reading_date',
        'odometer_value',
        'notes',
        'photo_path',
    ];

    protected $casts = [
        'reading_date' => 'date',
        'odometer_value' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($reading) {
            // Update vehicle's current odometer
            $reading->vehicle->update([
                'odometer_current' => $reading->odometer_value,
            ]);
        });
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getKmSinceLastAttribute(): ?int
    {
        $previous = static::where('vehicle_id', $this->vehicle_id)
            ->where('reading_date', '<', $this->reading_date)
            ->orderByDesc('reading_date')
            ->first();

        return $previous ? ($this->odometer_value - $previous->odometer_value) : null;
    }
}
