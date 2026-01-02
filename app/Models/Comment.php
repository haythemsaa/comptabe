<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasUuid;
use App\Notifications\UserMentionedInComment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, HasUuid, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'user_id',
        'company_id',
        'content',
        'mentions',
        'parent_id',
        'is_resolved',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'mentions' => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (Comment $comment) {
            // Notify mentioned users
            if (!empty($comment->mentions)) {
                $users = User::whereIn('id', $comment->mentions)->get();

                foreach ($users as $user) {
                    $user->notify(new UserMentionedInComment($comment));
                }
            }
        });
    }

    /**
     * Commentable (polymorphic).
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * User who created the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Parent comment (for replies).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * User who resolved the comment.
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Mark comment as resolved.
     */
    public function resolve(User $user = null): void
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $user?->id ?? auth()->id(),
        ]);
    }

    /**
     * Mark comment as unresolved.
     */
    public function unresolve(): void
    {
        $this->update([
            'is_resolved' => false,
            'resolved_at' => null,
            'resolved_by' => null,
        ]);
    }

    /**
     * Parse content and extract mentions.
     */
    public static function extractMentions(string $content): array
    {
        preg_match_all('/@(\w+)/', $content, $matches);

        if (empty($matches[1])) {
            return [];
        }

        // Find users by username or email
        return User::where(function ($query) use ($matches) {
            foreach ($matches[1] as $username) {
                $query->orWhere('name', 'LIKE', "%{$username}%")
                      ->orWhere('email', 'LIKE', "%{$username}%");
            }
        })->pluck('id')->toArray();
    }
}
