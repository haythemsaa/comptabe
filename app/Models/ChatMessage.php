<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatMessage extends Model
{
    use HasFactory, HasUuid;

    public $timestamps = false; // Only use created_at

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'tool_calls',
        'tool_results',
        'input_tokens',
        'output_tokens',
        'cost',
        'created_at',
    ];

    protected $casts = [
        'tool_calls' => 'array',
        'tool_results' => 'array',
        'created_at' => 'datetime',
        'cost' => 'decimal:6',
    ];

    /**
     * Get the conversation this message belongs to.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    /**
     * Get tool executions for this message.
     */
    public function toolExecutions(): HasMany
    {
        return $this->hasMany(ChatToolExecution::class, 'message_id');
    }

    /**
     * Check if message is from user.
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if message is from assistant.
     */
    public function isAssistant(): bool
    {
        return $this->role === 'assistant';
    }

    /**
     * Check if message has tool calls.
     */
    public function hasToolCalls(): bool
    {
        return !empty($this->tool_calls);
    }

    /**
     * Calculate and set cost based on tokens.
     */
    public function calculateCost(): void
    {
        if ($this->input_tokens && $this->output_tokens) {
            $inputCost = ($this->input_tokens / 1_000_000) * config('ai.costs.input_per_million');
            $outputCost = ($this->output_tokens / 1_000_000) * config('ai.costs.output_per_million');
            $this->cost = $inputCost + $outputCost;
        }
    }

    /**
     * Boot method to auto-calculate cost and update conversation timestamp.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($message) {
            $message->created_at = now();
            $message->calculateCost();
        });

        static::created(function ($message) {
            // Update conversation's last_message_at
            $message->conversation->touchLastMessage();

            // Generate title from first user message
            if ($message->isUser() && $message->conversation->messages()->count() === 1) {
                $message->conversation->generateTitle($message->content);
            }
        });
    }
}
