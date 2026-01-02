<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'type',
        'description',
        'config',
        'filters',
        'schedule',
        'last_generated_at',
        'is_favorite',
        'is_public',
    ];

    protected $casts = [
        'config' => 'array',
        'filters' => 'array',
        'schedule' => 'array',
        'last_generated_at' => 'datetime',
        'is_favorite' => 'boolean',
        'is_public' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function executions(): HasMany
    {
        return $this->hasMany(ReportExecution::class);
    }

    /**
     * Scope: rapports de l'utilisateur
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere('is_public', true);
        });
    }

    /**
     * Scope: rapports planifiés
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('schedule');
    }

    /**
     * Scope: favoris
     */
    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    /**
     * Vérifier si le rapport doit être généré selon son planning
     */
    public function shouldRun(): bool
    {
        if (!$this->schedule) {
            return false;
        }

        $frequency = $this->schedule['frequency'] ?? null;
        $lastRun = $this->last_generated_at;

        if (!$lastRun) {
            return true;
        }

        return match($frequency) {
            'daily' => $lastRun->diffInDays(now()) >= 1,
            'weekly' => $lastRun->diffInWeeks(now()) >= 1,
            'monthly' => $lastRun->diffInMonths(now()) >= 1,
            'quarterly' => $lastRun->diffInMonths(now()) >= 3,
            default => false,
        };
    }

    /**
     * Obtenir la prochaine date d'exécution
     */
    public function getNextRunAttribute(): ?\Carbon\Carbon
    {
        if (!$this->schedule) {
            return null;
        }

        $frequency = $this->schedule['frequency'] ?? null;
        $lastRun = $this->last_generated_at ?? now();

        return match($frequency) {
            'daily' => $lastRun->copy()->addDay(),
            'weekly' => $lastRun->copy()->addWeek(),
            'monthly' => $lastRun->copy()->addMonth(),
            'quarterly' => $lastRun->copy()->addMonths(3),
            default => null,
        };
    }
}
