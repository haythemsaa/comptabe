<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleReservation extends Model
{
    protected $fillable = [
        'vehicle_id',
        'user_id',
        'start_datetime',
        'end_datetime',
        'purpose',
        'destination',
        'expected_km',
        'start_odometer',
        'end_odometer',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'expected_km' => 'integer',
        'start_odometer' => 'integer',
        'end_odometer' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'En attente',
            'approved' => 'Approuvée',
            'rejected' => 'Refusée',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'approved', 'completed' => 'green',
            'pending' => 'yellow',
            'rejected', 'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function getDurationInHoursAttribute(): float
    {
        return $this->start_datetime->diffInHours($this->end_datetime);
    }

    public function getActualKmAttribute(): ?int
    {
        if ($this->start_odometer && $this->end_odometer) {
            return $this->end_odometer - $this->start_odometer;
        }
        return null;
    }

    public function isOngoing(): bool
    {
        return $this->status === 'approved'
            && $this->start_datetime->lte(now())
            && $this->end_datetime->gte(now());
    }

    public function overlaps($startDatetime, $endDatetime, $excludeId = null): bool
    {
        $query = static::where('vehicle_id', $this->vehicle_id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($startDatetime, $endDatetime) {
                $q->whereBetween('start_datetime', [$startDatetime, $endDatetime])
                    ->orWhereBetween('end_datetime', [$startDatetime, $endDatetime])
                    ->orWhere(function ($q2) use ($startDatetime, $endDatetime) {
                        $q2->where('start_datetime', '<=', $startDatetime)
                            ->where('end_datetime', '>=', $endDatetime);
                    });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'approved')
            ->where('start_datetime', '>', now())
            ->orderBy('start_datetime');
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', 'approved')
            ->where('start_datetime', '<=', now())
            ->where('end_datetime', '>=', now());
    }
}
