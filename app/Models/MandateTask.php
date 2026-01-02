<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MandateTask extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'client_mandate_id',
        'title',
        'description',
        'task_type',
        'fiscal_year',
        'period',
        'due_date',
        'reminder_date',
        'assigned_to',
        'status',
        'priority',
        'estimated_hours',
        'actual_hours',
        'is_billable',
        'billed_at',
        'completed_at',
        'completed_by',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'reminder_date' => 'date',
            'estimated_hours' => 'decimal:2',
            'actual_hours' => 'decimal:2',
            'is_billable' => 'boolean',
            'billed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Task type labels.
     */
    public const TYPE_LABELS = [
        'vat_declaration' => 'Déclaration TVA',
        'annual_accounts' => 'Comptes annuels',
        'tax_return' => 'Déclaration fiscale',
        'bookkeeping' => 'Tenue comptable',
        'payroll' => 'Gestion sociale',
        'meeting' => 'Réunion',
        'review' => 'Révision',
        'other' => 'Autre',
    ];

    /**
     * Status labels.
     */
    public const STATUS_LABELS = [
        'pending' => 'À faire',
        'in_progress' => 'En cours',
        'review' => 'À réviser',
        'completed' => 'Terminé',
        'cancelled' => 'Annulé',
    ];

    /**
     * Status colors.
     */
    public const STATUS_COLORS = [
        'pending' => 'secondary',
        'in_progress' => 'primary',
        'review' => 'warning',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];

    /**
     * Priority labels.
     */
    public const PRIORITY_LABELS = [
        'low' => 'Basse',
        'normal' => 'Normale',
        'high' => 'Haute',
        'urgent' => 'Urgente',
    ];

    /**
     * Priority colors.
     */
    public const PRIORITY_COLORS = [
        'low' => 'secondary',
        'normal' => 'primary',
        'high' => 'warning',
        'urgent' => 'danger',
    ];

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->task_type] ?? $this->task_type;
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Get status color.
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    /**
     * Get priority label.
     */
    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITY_LABELS[$this->priority] ?? $this->priority;
    }

    /**
     * Get priority color.
     */
    public function getPriorityColorAttribute(): string
    {
        return self::PRIORITY_COLORS[$this->priority] ?? 'secondary';
    }

    /**
     * Check if task is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Check if task is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get days until due (negative if overdue).
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_date) return null;
        return now()->startOfDay()->diffInDays($this->due_date, false);
    }

    /**
     * Mark as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => auth()->id(),
        ]);

        // Log activity
        $this->clientMandate->logActivity('task_completed', "Tâche terminée: {$this->title}");
    }

    /**
     * Client mandate.
     */
    public function clientMandate(): BelongsTo
    {
        return $this->belongsTo(ClientMandate::class);
    }

    /**
     * Assigned user.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * User who completed the task.
     */
    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Scope for pending tasks.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress', 'review']);
    }

    /**
     * Scope for overdue tasks.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope for tasks due this week.
     */
    public function scopeDueThisWeek($query)
    {
        return $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope for tasks assigned to a user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }
}
