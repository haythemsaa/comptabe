<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountingFirmUser extends Pivot
{
    use HasUuid;

    protected $table = 'accounting_firm_users';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'accounting_firm_id',
        'user_id',
        'role',
        'employee_number',
        'job_title',
        'department',
        'permissions',
        'can_access_all_clients',
        'is_default',
        'is_active',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'can_access_all_clients' => 'boolean',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'joined_at' => 'datetime',
        ];
    }

    /**
     * Role labels.
     */
    public const ROLE_LABELS = [
        'cabinet_owner' => 'Propriétaire',
        'cabinet_admin' => 'Administrateur',
        'cabinet_manager' => 'Chef de mission',
        'cabinet_accountant' => 'Collaborateur comptable',
        'cabinet_assistant' => 'Assistant',
    ];

    /**
     * Role colors for badges.
     */
    public const ROLE_COLORS = [
        'cabinet_owner' => 'primary',
        'cabinet_admin' => 'warning',
        'cabinet_manager' => 'success',
        'cabinet_accountant' => 'secondary',
        'cabinet_assistant' => 'secondary',
    ];

    /**
     * Get role label.
     */
    public function getRoleLabelAttribute(): string
    {
        return self::ROLE_LABELS[$this->role] ?? $this->role;
    }

    /**
     * Get role color.
     */
    public function getRoleColorAttribute(): string
    {
        return self::ROLE_COLORS[$this->role] ?? 'secondary';
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string|array $roles): bool
    {
        $roles = (array) $roles;
        return in_array($this->role, $roles);
    }

    /**
     * Check if user is owner or admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(['cabinet_owner', 'cabinet_admin']);
    }

    /**
     * Check if user can manage other users.
     */
    public function canManageUsers(): bool
    {
        return $this->hasRole(['cabinet_owner', 'cabinet_admin']);
    }

    /**
     * Check if user can manage clients.
     */
    public function canManageClients(): bool
    {
        return $this->hasRole(['cabinet_owner', 'cabinet_admin', 'cabinet_manager']);
    }

    /**
     * Accounting firm.
     */
    public function accountingFirm(): BelongsTo
    {
        return $this->belongsTo(AccountingFirm::class);
    }

    /**
     * User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
