<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'partner_id',
        'reference',
        'name',
        'description',
        'status',
        'priority',
        'start_date',
        'end_date',
        'actual_start_date',
        'actual_end_date',
        'budget',
        'actual_cost',
        'billing_type',
        'hourly_rate',
        'estimated_hours',
        'progress_percent',
        'manager_id',
        'color',
        'tags',
        'is_template',
        'template_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'tags' => 'array',
        'is_template' => 'boolean',
    ];

    const STATUSES = [
        'draft' => ['label' => 'Brouillon', 'color' => 'secondary'],
        'planning' => ['label' => 'Planification', 'color' => 'info'],
        'in_progress' => ['label' => 'En cours', 'color' => 'primary'],
        'on_hold' => ['label' => 'En pause', 'color' => 'warning'],
        'completed' => ['label' => 'Terminé', 'color' => 'success'],
        'cancelled' => ['label' => 'Annulé', 'color' => 'danger'],
    ];

    const PRIORITIES = [
        'low' => ['label' => 'Basse', 'color' => 'secondary'],
        'medium' => ['label' => 'Moyenne', 'color' => 'info'],
        'high' => ['label' => 'Haute', 'color' => 'warning'],
        'urgent' => ['label' => 'Urgente', 'color' => 'danger'],
    ];

    const BILLING_TYPES = [
        'fixed_price' => 'Prix fixe',
        'time_materials' => 'Temps et matériaux',
        'milestone' => 'Par jalons',
        'not_billable' => 'Non facturable',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function rootTasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class)->whereNull('parent_task_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot(['role', 'hourly_rate'])
            ->withTimestamps();
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'template_id');
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

    public function getBillingTypeLabelAttribute(): string
    {
        return self::BILLING_TYPES[$this->billing_type] ?? $this->billing_type;
    }

    public function getTotalHoursAttribute(): float
    {
        return $this->timesheets()->sum('hours');
    }

    public function getBillableHoursAttribute(): float
    {
        return $this->timesheets()->where('billable', true)->sum('hours');
    }

    public function getCompletedTasksCountAttribute(): int
    {
        return $this->tasks()->where('status', 'done')->count();
    }

    public function getTotalTasksCountAttribute(): int
    {
        return $this->tasks()->count();
    }

    public function updateProgress(): void
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) {
            $this->progress_percent = 0;
        } else {
            $completedTasks = $this->tasks()->where('status', 'done')->count();
            $this->progress_percent = round(($completedTasks / $totalTasks) * 100);
        }
        $this->save();
    }

    public function updateActualCost(): void
    {
        $this->actual_cost = $this->timesheets()
            ->whereNotNull('amount')
            ->sum('amount');
        $this->save();
    }

    public static function generateReference(Company $company): string
    {
        $year = now()->year;
        $count = self::where('company_id', $company->id)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('PRJ-%d-%04d', $year, $count);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['planning', 'in_progress']);
    }

    public function scopeNotTemplate($query)
    {
        return $query->where('is_template', false);
    }
}
