<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InvitationToken extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'invited_by',
        'company_id',
        'email',
        'token',
        'role',
        'expires_at',
        'accepted_at',
        'accepted_from_ip',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Invited by relationship
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Company relationship
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Generate a new invitation token
     */
    public static function generate(User $user, User $invitedBy = null, Company $company = null, string $role = 'user', int $validHours = 72): self
    {
        return self::create([
            'user_id' => $user->id,
            'invited_by' => $invitedBy?->id,
            'company_id' => $company?->id,
            'email' => $user->email,
            'token' => Str::random(64),
            'role' => $role,
            'expires_at' => now()->addHours($validHours),
        ]);
    }

    /**
     * Find valid token
     */
    public static function findValid(string $token): ?self
    {
        return self::where('token', $token)
            ->where('expires_at', '>', now())
            ->whereNull('accepted_at')
            ->first();
    }

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if token is accepted
     */
    public function isAccepted(): bool
    {
        return !is_null($this->accepted_at);
    }

    /**
     * Check if token is valid
     */
    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isAccepted();
    }

    /**
     * Accept the invitation
     */
    public function accept(string $ip = null): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        return $this->update([
            'accepted_at' => now(),
            'accepted_from_ip' => $ip ?? request()->ip(),
        ]);
    }

    /**
     * Get the invitation URL
     */
    public function getUrlAttribute(): string
    {
        return route('invitation.accept', ['token' => $this->token]);
    }

    /**
     * Scope: Pending invitations
     */
    public function scopePending($query)
    {
        return $query->whereNull('accepted_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope: Accepted invitations
     */
    public function scopeAccepted($query)
    {
        return $query->whereNotNull('accepted_at');
    }

    /**
     * Scope: Expired invitations
     */
    public function scopeExpired($query)
    {
        return $query->whereNull('accepted_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Clean up expired tokens
     */
    public static function cleanupExpired(): int
    {
        return self::expired()
            ->where('created_at', '<', now()->subDays(30))
            ->delete();
    }
}
