<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTask extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'project_id',
        'parent_task_id',
        'title',
        'description',
        'status',
        'priority',
        'start_date',
        'due_date',
        'completed_at',
        'estimated_hours',
        'actual_hours',
        'progress_percent',
        'assigned_to',
        'created_by',
        'sort_order',
        'is_milestone',
        'checklist',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'date',
        'actual_hours' => 'decimal:2',
        'is_milestone' => 'boolean',
        'checklist' => 'array',
    ];

    const STATUSES = [
        'todo' => ['label' => 'À faire', 'color' => 'secondary'],
        'in_progress' => ['label' => 'En cours', 'color' => 'primary'],
        'review' => ['label' => 'En revue', 'color' => 'info'],
        'done' => ['label' => 'Terminé', 'color' => 'success'],
        'cancelled' => ['label' => 'Annulé', 'color' => 'danger'],
    ];

    const PRIORITIES = [
        'low' => ['label' => 'Basse', 'color' => 'secondary'],
        'medium' => ['label' => 'Moyenne', 'color' => 'info'],
        'high' => ['label' => 'Haute', 'color' => 'warning'],
        'urgent' => ['label' => 'Urgente', 'color' => 'danger'],
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'parent_task_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'parent_task_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class, 'task_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'secondary';
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority]['label'] ?? $this->priority;
    }

    public function getPriorityColorAttribute(): string
    {
        return self::PRIORITIES[$this->priority]['color'] ?? 'secondary';
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'done';
    }

    public function updateActualHours(): void
    {
        $this->actual_hours = $this->timesheets()->sum('hours');
        $this->save();
    }

    public function markAsCompleted(): void
    {
        $this->status = 'done';
        $this->completed_at = now();
        $this->progress_percent = 100;
        $this->save();

        $this->project->updateProgress();
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeRootTasks($query)
    {
        return $query->whereNull('parent_task_id');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('status', '!=', 'done');
    }
}
