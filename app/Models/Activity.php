<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;

class Activity extends Model
{
    use HasFactory, HasUuid, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'related_type',
        'related_id',
        'type',
        'subject',
        'description',
        'due_date',
        'duration',
        'is_completed',
        'completed_at',
        'assigned_to',
        'created_by',
        'priority',
        'metadata',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'duration' => 'integer',
        'metadata' => 'array',
        'is_completed' => 'boolean',
    ];

    // Types avec labels et icônes
    public const TYPES = [
        'call' => ['label' => 'Appel', 'icon' => 'phone', 'color' => 'primary'],
        'email' => ['label' => 'Email', 'icon' => 'mail', 'color' => 'info'],
        'meeting' => ['label' => 'Réunion', 'icon' => 'users', 'color' => 'success'],
        'note' => ['label' => 'Note', 'icon' => 'file-text', 'color' => 'secondary'],
        'task' => ['label' => 'Tâche', 'icon' => 'check-square', 'color' => 'warning'],
        'demo' => ['label' => 'Démo', 'icon' => 'monitor', 'color' => 'purple'],
        'follow_up' => ['label' => 'Relance', 'icon' => 'refresh-cw', 'color' => 'danger'],
    ];

    public const PRIORITIES = [
        'low' => ['label' => 'Basse', 'color' => 'secondary'],
        'medium' => ['label' => 'Moyenne', 'color' => 'info'],
        'high' => ['label' => 'Haute', 'color' => 'warning'],
        'urgent' => ['label' => 'Urgente', 'color' => 'danger'],
    ];

    // Relations
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_completed', false)
                     ->whereNotNull('due_date')
                     ->where('due_date', '<', now());
    }

    public function scopeDueToday($query)
    {
        return $query->where('is_completed', false)
                     ->whereDate('due_date', today());
    }

    public function scopeDueThisWeek($query)
    {
        return $query->where('is_completed', false)
                     ->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeForRelated($query, string $type, int $id)
    {
        return $query->where('related_type', $type)
                     ->where('related_id', $id);
    }

    // Helpers
    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type]['label'] ?? $this->type;
    }

    public function getTypeIcon(): string
    {
        return self::TYPES[$this->type]['icon'] ?? 'circle';
    }

    public function getTypeColor(): string
    {
        return self::TYPES[$this->type]['color'] ?? 'secondary';
    }

    public function getPriorityLabel(): string
    {
        return self::PRIORITIES[$this->priority]['label'] ?? $this->priority;
    }

    public function getPriorityColor(): string
    {
        return self::PRIORITIES[$this->priority]['color'] ?? 'secondary';
    }

    public function isCompleted(): bool
    {
        return $this->is_completed;
    }

    public function isPending(): bool
    {
        return !$this->is_completed;
    }

    public function isOverdue(): bool
    {
        return $this->isPending() &&
               $this->due_date &&
               $this->due_date->isPast();
    }

    public function isDueToday(): bool
    {
        return $this->isPending() &&
               $this->due_date &&
               $this->due_date->isToday();
    }

    public function getFormattedDuration(): ?string
    {
        if (!$this->duration) {
            return null;
        }

        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;

        if ($hours > 0) {
            return "{$hours}h" . ($minutes > 0 ? " {$minutes}min" : '');
        }
        return "{$minutes}min";
    }

    // Actions
    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function markAsPending(): void
    {
        $this->update([
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }

    // Stats
    public static function getStats(string $companyId, ?string $userId = null): array
    {
        $query = self::where('company_id', $companyId);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        $pending = (clone $query)->pending()->count();
        $overdue = (clone $query)->overdue()->count();
        $dueToday = (clone $query)->dueToday()->count();
        $completedToday = (clone $query)->completed()
            ->whereDate('completed_at', today())
            ->count();

        return [
            'pending' => $pending,
            'overdue' => $overdue,
            'due_today' => $dueToday,
            'completed_today' => $completedToday,
        ];
    }
}
