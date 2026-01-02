<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'company_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Action types.
     */
    public const ACTIONS = [
        'create' => 'Création',
        'update' => 'Modification',
        'delete' => 'Suppression',
        'login' => 'Connexion',
        'logout' => 'Déconnexion',
        'login_failed' => 'Échec connexion',
        'password_reset' => 'Réinitialisation mot de passe',
        'impersonate' => 'Impersonation',
        'export' => 'Export',
        'import' => 'Import',
        'validate' => 'Validation',
        'send' => 'Envoi',
        'suspend' => 'Suspension',
        'activate' => 'Activation',
    ];

    /**
     * User relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Company relationship.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Auditable model relationship.
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo('model');
    }

    /**
     * Get action label.
     */
    public function getActionLabelAttribute(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
    }

    /**
     * Get color for action badge.
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'create' => 'success',
            'update' => 'primary',
            'delete' => 'danger',
            'login' => 'info',
            'logout' => 'secondary',
            'login_failed' => 'danger',
            'suspend' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get short model name.
     */
    public function getModelNameAttribute(): string
    {
        if (!$this->model_type) return '-';
        return class_basename($this->model_type);
    }

    /**
     * Scope for specific user.
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific company.
     */
    public function scopeForCompany($query, string $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope for specific action.
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Create a log entry.
     */
    public static function log(
        string $action,
        string $description,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null
    ): self {
        return static::create([
            'user_id' => auth()->id(),
            'company_id' => session('current_tenant_id'),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
