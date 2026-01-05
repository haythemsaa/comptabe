<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timesheet extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'company_id',
        'user_id',
        'project_id',
        'task_id',
        'date',
        'hours',
        'description',
        'billable',
        'hourly_rate',
        'amount',
        'invoiced',
        'invoice_id',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'billable' => 'boolean',
        'invoiced' => 'boolean',
        'approved_at' => 'datetime',
    ];

    const STATUSES = [
        'draft' => ['label' => 'Brouillon', 'color' => 'secondary'],
        'submitted' => ['label' => 'Soumis', 'color' => 'info'],
        'approved' => ['label' => 'Approuvé', 'color' => 'success'],
        'rejected' => ['label' => 'Rejeté', 'color' => 'danger'],
    ];

    protected static function booted(): void
    {
        static::saving(function (Timesheet $timesheet) {
            // Calculate amount if hourly rate is set
            if ($timesheet->hourly_rate && $timesheet->hours) {
                $timesheet->amount = $timesheet->hours * $timesheet->hourly_rate;
            }
        });

        static::saved(function (Timesheet $timesheet) {
            // Update task actual hours
            if ($timesheet->task_id) {
                $timesheet->task->updateActualHours();
            }
            // Update project actual cost
            if ($timesheet->project_id) {
                $timesheet->project->updateActualCost();
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'secondary';
    }

    public function submit(): void
    {
        $this->status = 'submitted';
        $this->save();
    }

    public function approve(User $approver): void
    {
        $this->status = 'approved';
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->save();
    }

    public function reject(): void
    {
        $this->status = 'rejected';
        $this->save();
    }

    public function markAsInvoiced(Invoice $invoice): void
    {
        $this->invoiced = true;
        $this->invoice_id = $invoice->id;
        $this->save();
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeBillable($query)
    {
        return $query->where('billable', true);
    }

    public function scopeNotInvoiced($query)
    {
        return $query->where('invoiced', false);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForWeek($query, $date)
    {
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        return $query->whereBetween('date', [$startOfWeek, $endOfWeek]);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }
}
