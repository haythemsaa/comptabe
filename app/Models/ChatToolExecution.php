<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatToolExecution extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'message_id',
        'tool_name',
        'tool_input',
        'tool_output',
        'status',
        'error_message',
        'requires_confirmation',
        'confirmed',
        'executed_at',
    ];

    protected $casts = [
        'tool_input' => 'array',
        'tool_output' => 'array',
        'requires_confirmation' => 'boolean',
        'confirmed' => 'boolean',
        'executed_at' => 'datetime',
    ];

    /**
     * Get the message this execution belongs to.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    /**
     * Check if execution is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if execution was successful.
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if execution failed.
     */
    public function isError(): bool
    {
        return $this->status === 'error';
    }

    /**
     * Mark execution as successful.
     */
    public function markAsSuccess(array $output): void
    {
        $this->update([
            'status' => 'success',
            'tool_output' => $output,
            'executed_at' => now(),
        ]);
    }

    /**
     * Mark execution as failed.
     */
    public function markAsError(string $errorMessage): void
    {
        $this->update([
            'status' => 'error',
            'error_message' => $errorMessage,
            'executed_at' => now(),
        ]);
    }

    /**
     * Confirm the execution (for dangerous operations).
     */
    public function confirm(): void
    {
        $this->update(['confirmed' => true]);
    }

    /**
     * Check if awaiting confirmation.
     */
    public function isAwaitingConfirmation(): bool
    {
        return $this->requires_confirmation && $this->confirmed !== true;
    }

    /**
     * Scope to filter pending executions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter successful executions.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope to filter failed executions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'error');
    }
}
