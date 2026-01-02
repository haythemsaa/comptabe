<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatConversation extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'title',
        'context_type',
        'metadata',
        'is_archived',
        'last_message_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_archived' => 'boolean',
        'last_message_at' => 'datetime',
    ];

    /**
     * Get the user who owns the conversation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company context (null for superadmin).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get all messages in the conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id')->orderBy('created_at');
    }

    /**
     * Get recent messages (for context window).
     */
    public function recentMessages(int $limit = 20): HasMany
    {
        return $this->messages()->latest()->limit($limit);
    }

    /**
     * Check if conversation is for superadmin context.
     */
    public function isSuperadminContext(): bool
    {
        return $this->context_type === 'superadmin';
    }

    /**
     * Archive the conversation.
     */
    public function archive(): void
    {
        $this->update(['is_archived' => true]);
    }

    /**
     * Unarchive the conversation.
     */
    public function unarchive(): void
    {
        $this->update(['is_archived' => false]);
    }

    /**
     * Update last message timestamp.
     */
    public function touchLastMessage(): void
    {
        $this->update(['last_message_at' => now()]);
    }

    /**
     * Generate title from first message if not set.
     */
    public function generateTitle(string $firstMessage): void
    {
        if (!$this->title) {
            $this->update([
                'title' => \Illuminate\Support\Str::limit($firstMessage, 50),
            ]);
        }
    }

    /**
     * Scope to filter by context type.
     */
    public function scopeOfContextType($query, string $type)
    {
        return $query->where('context_type', $type);
    }

    /**
     * Scope to filter active (non-archived) conversations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope to filter archived conversations.
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }
}
