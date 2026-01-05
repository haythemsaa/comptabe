<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleReminder extends Model
{
    protected $fillable = [
        'vehicle_id',
        'type',
        'due_date',
        'reminder_days_before',
        'is_recurring',
        'recurrence_months',
        'status',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'reminder_days_before' => 'integer',
        'is_recurring' => 'boolean',
        'recurrence_months' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'insurance' => 'Assurance',
            'technical_inspection' => 'Contrôle technique',
            'maintenance' => 'Entretien',
            'oil_change' => 'Vidange',
            'tyre_change' => 'Changement de pneus',
            'registration' => 'Immatriculation',
            'other' => 'Autre',
            default => $this->type,
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'insurance' => 'fa-shield-alt',
            'technical_inspection' => 'fa-clipboard-check',
            'maintenance' => 'fa-wrench',
            'oil_change' => 'fa-oil-can',
            'tyre_change' => 'fa-tire',
            'registration' => 'fa-id-card',
            default => 'fa-bell',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'En attente',
            'notified' => 'Notifié',
            'completed' => 'Terminé',
            'overdue' => 'En retard',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'green',
            'pending' => 'blue',
            'notified' => 'yellow',
            'overdue' => 'red',
            default => 'gray',
        };
    }

    public function getDaysUntilDueAttribute(): int
    {
        return now()->diffInDays($this->due_date, false);
    }

    public function isOverdue(): bool
    {
        return $this->due_date->lt(now()) && $this->status !== 'completed';
    }

    public function shouldNotify(): bool
    {
        if ($this->status !== 'pending') return false;
        $notifyDate = $this->due_date->copy()->subDays($this->reminder_days_before);
        return now()->gte($notifyDate);
    }

    public function complete(): void
    {
        $this->update(['status' => 'completed']);

        if ($this->is_recurring && $this->recurrence_months) {
            static::create([
                'vehicle_id' => $this->vehicle_id,
                'type' => $this->type,
                'due_date' => $this->due_date->copy()->addMonths($this->recurrence_months),
                'reminder_days_before' => $this->reminder_days_before,
                'is_recurring' => true,
                'recurrence_months' => $this->recurrence_months,
                'status' => 'pending',
                'notes' => $this->notes,
            ]);
        }
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDueSoon($query, int $days = 30)
    {
        return $query->whereIn('status', ['pending', 'notified'])
            ->whereBetween('due_date', [now(), now()->addDays($days)]);
    }

    public function scopeOverdue($query)
    {
        return $query->whereIn('status', ['pending', 'notified'])
            ->where('due_date', '<', now());
    }
}
