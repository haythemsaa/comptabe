<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientMandate extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'accounting_firm_id',
        'company_id',
        'mandate_type',
        'status',
        'start_date',
        'end_date',
        'manager_user_id',
        'assigned_users',
        'services',
        'billing_type',
        'hourly_rate',
        'monthly_fee',
        'annual_fee',
        'client_can_view',
        'client_can_edit',
        'client_can_validate',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'assigned_users' => 'array',
            'services' => 'array',
            'hourly_rate' => 'decimal:2',
            'monthly_fee' => 'decimal:2',
            'annual_fee' => 'decimal:2',
            'client_can_view' => 'boolean',
            'client_can_edit' => 'boolean',
            'client_can_validate' => 'boolean',
        ];
    }

    /**
     * Mandate type labels.
     */
    public const TYPE_LABELS = [
        'full' => 'Mandat complet',
        'bookkeeping' => 'Tenue comptable',
        'tax' => 'Missions fiscales',
        'payroll' => 'Gestion sociale',
        'advisory' => 'Conseil',
        'audit' => 'Révision',
    ];

    /**
     * Status labels.
     */
    public const STATUS_LABELS = [
        'pending' => 'En attente',
        'active' => 'Actif',
        'suspended' => 'Suspendu',
        'terminated' => 'Terminé',
    ];

    /**
     * Status colors.
     */
    public const STATUS_COLORS = [
        'pending' => 'warning',
        'active' => 'success',
        'suspended' => 'danger',
        'terminated' => 'secondary',
    ];

    /**
     * Default services.
     */
    public const DEFAULT_SERVICES = [
        'bookkeeping' => true,
        'vat_declarations' => true,
        'annual_accounts' => true,
        'tax_returns' => true,
        'payroll' => false,
        'social_declarations' => false,
        'advisory' => false,
    ];

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->mandate_type] ?? $this->mandate_type;
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
     * Check if mandate is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if a service is included.
     */
    public function hasService(string $service): bool
    {
        return $this->services[$service] ?? false;
    }

    /**
     * Accounting firm.
     */
    public function accountingFirm(): BelongsTo
    {
        return $this->belongsTo(AccountingFirm::class);
    }

    /**
     * Client company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Manager (assigned accountant).
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    /**
     * Tasks.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(MandateTask::class);
    }

    /**
     * Documents.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(MandateDocument::class);
    }

    /**
     * Activities.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(MandateActivity::class);
    }

    /**
     * Communications.
     */
    public function communications(): HasMany
    {
        return $this->hasMany(MandateCommunication::class);
    }

    /**
     * Get pending tasks count.
     */
    public function getPendingTasksCountAttribute(): int
    {
        return $this->tasks()->whereIn('status', ['pending', 'in_progress'])->count();
    }

    /**
     * Get overdue tasks count.
     */
    public function getOverdueTasksCountAttribute(): int
    {
        return $this->tasks()
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();
    }

    /**
     * Get unread messages count.
     */
    public function getUnreadMessagesCountAttribute(): int
    {
        return $this->communications()
            ->where('is_read', false)
            ->where('sender_type', 'client')
            ->count();
    }

    /**
     * Log activity.
     */
    public function logActivity(string $type, string $description = null, array $metadata = []): MandateActivity
    {
        return $this->activities()->create([
            'user_id' => auth()->id(),
            'activity_type' => $type,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Scope for active mandates.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for mandates assigned to a user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('manager_user_id', $userId)
                ->orWhereJsonContains('assigned_users', ['user_id' => $userId]);
        });
    }
}
