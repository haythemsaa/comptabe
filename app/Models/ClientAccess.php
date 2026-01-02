<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAccess extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'client_access';

    protected $fillable = [
        'user_id',
        'company_id',
        'access_level',
        'permissions',
        'last_access_at',
        'ip_address',
    ];

    protected $casts = [
        'permissions' => 'array',
        'last_access_at' => 'datetime',
    ];

    /**
     * Access levels
     */
    const ACCESS_LEVELS = [
        'view_only' => 'Lecture seule',
        'upload_documents' => 'Upload de documents',
        'full_client' => 'Accès client complet',
    ];

    /**
     * Default permissions par niveau
     */
    const DEFAULT_PERMISSIONS = [
        'view_only' => [
            'view_invoices' => true,
            'download_invoices' => true,
            'view_payments' => true,
            'view_balance' => false,
            'upload_documents' => false,
            'comment' => false,
        ],
        'upload_documents' => [
            'view_invoices' => true,
            'download_invoices' => true,
            'view_payments' => true,
            'view_balance' => false,
            'upload_documents' => true,
            'comment' => true,
        ],
        'full_client' => [
            'view_invoices' => true,
            'download_invoices' => true,
            'view_payments' => true,
            'view_balance' => true,
            'upload_documents' => true,
            'comment' => true,
            'view_reports' => true,
        ],
    ];

    /**
     * User with access.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Company being accessed.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if user has specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? self::DEFAULT_PERMISSIONS[$this->access_level] ?? [];

        return $permissions[$permission] ?? false;
    }

    /**
     * Record access.
     */
    public function recordAccess(string $ipAddress = null): void
    {
        $this->update([
            'last_access_at' => now(),
            'ip_address' => $ipAddress ?? request()->ip(),
        ]);
    }

    /**
     * Get access level label.
     */
    public function getAccessLevelLabelAttribute(): string
    {
        return self::ACCESS_LEVELS[$this->access_level] ?? $this->access_level;
    }
}
