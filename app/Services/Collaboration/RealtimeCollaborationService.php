<?php

namespace App\Services\Collaboration;

use App\Models\User;
use App\Models\Invoice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class RealtimeCollaborationService
{
    /**
     * Register user presence on a document
     */
    public function registerPresence(string $userId, string $documentType, string $documentId): void
    {
        $key = $this->getPresenceKey($documentType, $documentId);

        $presenceData = [
            'user_id' => $userId,
            'timestamp' => now()->timestamp,
            'document_type' => $documentType,
            'document_id' => $documentId,
        ];

        // Store in Redis with 30 second TTL
        Redis::setex(
            "{$key}:{$userId}",
            30,
            json_encode($presenceData)
        );

        // Broadcast presence update via WebSocket
        $this->broadcastPresenceUpdate($documentType, $documentId);
    }

    /**
     * Remove user presence from document
     */
    public function removePresence(string $userId, string $documentType, string $documentId): void
    {
        $key = $this->getPresenceKey($documentType, $documentId);

        Redis::del("{$key}:{$userId}");

        $this->broadcastPresenceUpdate($documentType, $documentId);
    }

    /**
     * Get all users currently viewing a document
     */
    public function getActiveUsers(string $documentType, string $documentId): array
    {
        $key = $this->getPresenceKey($documentType, $documentId);
        $pattern = "{$key}:*";

        $keys = Redis::keys($pattern);
        $activeUsers = [];

        foreach ($keys as $redisKey) {
            $data = Redis::get($redisKey);
            if ($data) {
                $presence = json_decode($data, true);

                // Verify timestamp is recent (within 30 seconds)
                if (now()->timestamp - $presence['timestamp'] <= 30) {
                    $user = User::find($presence['user_id']);
                    if ($user) {
                        $activeUsers[] = [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'avatar' => $user->avatar_url ?? null,
                            'last_seen' => Carbon::createFromTimestamp($presence['timestamp']),
                        ];
                    }
                }
            }
        }

        return $activeUsers;
    }

    /**
     * Acquire lock for editing a document
     */
    public function acquireEditLock(string $userId, string $documentType, string $documentId): bool
    {
        $lockKey = $this->getLockKey($documentType, $documentId);

        $lockData = [
            'user_id' => $userId,
            'acquired_at' => now()->timestamp,
        ];

        // Try to acquire lock (5 minute TTL)
        $acquired = Redis::set(
            $lockKey,
            json_encode($lockData),
            'EX', 300, // 5 minutes
            'NX' // Only set if not exists
        );

        if ($acquired) {
            $this->broadcastLockUpdate($documentType, $documentId, $userId, 'acquired');
            return true;
        }

        return false;
    }

    /**
     * Release edit lock
     */
    public function releaseEditLock(string $userId, string $documentType, string $documentId): bool
    {
        $lockKey = $this->getLockKey($documentType, $documentId);

        $currentLock = Redis::get($lockKey);

        if ($currentLock) {
            $lockData = json_decode($currentLock, true);

            // Only allow lock owner to release
            if ($lockData['user_id'] === $userId) {
                Redis::del($lockKey);
                $this->broadcastLockUpdate($documentType, $documentId, $userId, 'released');
                return true;
            }
        }

        return false;
    }

    /**
     * Get current lock holder
     */
    public function getLockHolder(string $documentType, string $documentId): ?array
    {
        $lockKey = $this->getLockKey($documentType, $documentId);

        $lockData = Redis::get($lockKey);

        if ($lockData) {
            $lock = json_decode($lockData, true);
            $user = User::find($lock['user_id']);

            if ($user) {
                return [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'acquired_at' => Carbon::createFromTimestamp($lock['acquired_at']),
                    'expires_at' => Carbon::createFromTimestamp($lock['acquired_at'])->addMinutes(5),
                ];
            }
        }

        return null;
    }

    /**
     * Record a change/edit operation
     */
    public function recordChange(
        string $userId,
        string $documentType,
        string $documentId,
        string $field,
        $oldValue,
        $newValue
    ): void {
        $changeKey = $this->getChangesKey($documentType, $documentId);

        $change = [
            'id' => uniqid('change_', true),
            'user_id' => $userId,
            'field' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'timestamp' => now()->timestamp,
        ];

        // Add to sorted set (score = timestamp)
        Redis::zadd($changeKey, $change['timestamp'], json_encode($change));

        // Keep only last 100 changes
        Redis::zremrangebyrank($changeKey, 0, -101);

        // Set expiry for 24 hours
        Redis::expire($changeKey, 86400);

        // Broadcast change to other users
        $this->broadcastChange($documentType, $documentId, $change);
    }

    /**
     * Get recent changes for a document
     */
    public function getRecentChanges(string $documentType, string $documentId, int $limit = 50): array
    {
        $changeKey = $this->getChangesKey($documentType, $documentId);

        // Get latest changes (sorted by timestamp descending)
        $changes = Redis::zrevrange($changeKey, 0, $limit - 1);

        $result = [];
        foreach ($changes as $changeJson) {
            $change = json_decode($changeJson, true);

            $user = User::find($change['user_id']);

            $result[] = [
                'id' => $change['id'],
                'user' => [
                    'id' => $user->id ?? null,
                    'name' => $user->name ?? 'Unknown',
                ],
                'field' => $change['field'],
                'old_value' => $change['old_value'],
                'new_value' => $change['new_value'],
                'timestamp' => Carbon::createFromTimestamp($change['timestamp']),
            ];
        }

        return $result;
    }

    /**
     * Detect and resolve conflicts
     */
    public function detectConflict(
        string $documentType,
        string $documentId,
        string $field,
        $baseValue,
        $newValue
    ): ?array {
        $changes = $this->getRecentChanges($documentType, $documentId, 10);

        // Check if field was modified by someone else recently
        foreach ($changes as $change) {
            if ($change['field'] === $field && $change['new_value'] !== $baseValue) {
                return [
                    'conflict' => true,
                    'field' => $field,
                    'base_value' => $baseValue,
                    'current_value' => $change['new_value'],
                    'your_value' => $newValue,
                    'conflicting_user' => $change['user'],
                    'conflicting_change_time' => $change['timestamp'],
                    'resolution_strategy' => $this->suggestResolutionStrategy($change, $newValue),
                ];
            }
        }

        return null;
    }

    /**
     * Suggest conflict resolution strategy
     */
    protected function suggestResolutionStrategy($conflictingChange, $newValue): string
    {
        // Last write wins by default
        if ($conflictingChange['timestamp']->isAfter(now()->subMinutes(1))) {
            return 'manual'; // Recent conflict, require manual resolution
        }

        return 'last_write_wins';
    }

    /**
     * Broadcast presence update via WebSocket
     */
    protected function broadcastPresenceUpdate(string $documentType, string $documentId): void
    {
        $activeUsers = $this->getActiveUsers($documentType, $documentId);

        // Use Laravel Echo / Pusher / Soketi
        event(new \App\Events\PresenceUpdated(
            $documentType,
            $documentId,
            $activeUsers
        ));
    }

    /**
     * Broadcast lock update
     */
    protected function broadcastLockUpdate(
        string $documentType,
        string $documentId,
        string $userId,
        string $action
    ): void {
        event(new \App\Events\LockUpdated(
            $documentType,
            $documentId,
            $userId,
            $action
        ));
    }

    /**
     * Broadcast change to other users
     */
    protected function broadcastChange(string $documentType, string $documentId, array $change): void
    {
        event(new \App\Events\DocumentChanged(
            $documentType,
            $documentId,
            $change
        ));
    }

    /**
     * Get Redis key for presence
     */
    protected function getPresenceKey(string $documentType, string $documentId): string
    {
        return "presence:{$documentType}:{$documentId}";
    }

    /**
     * Get Redis key for edit lock
     */
    protected function getLockKey(string $documentType, string $documentId): string
    {
        return "lock:{$documentType}:{$documentId}";
    }

    /**
     * Get Redis key for changes
     */
    protected function getChangesKey(string $documentType, string $documentId): string
    {
        return "changes:{$documentType}:{$documentId}";
    }

    /**
     * Cleanup stale presence data (called by scheduler)
     */
    public function cleanupStalePresence(): void
    {
        // This would be called by a scheduled job to clean up expired presence data
        $pattern = "presence:*";
        $keys = Redis::keys($pattern);

        $cleaned = 0;
        foreach ($keys as $key) {
            $ttl = Redis::ttl($key);
            if ($ttl === -1 || $ttl === -2) {
                Redis::del($key);
                $cleaned++;
            }
        }

        \Log::info("Cleaned up {$cleaned} stale presence keys");
    }

    /**
     * Get collaboration statistics for dashboard
     */
    public function getCollaborationStats(string $companyId): array
    {
        // This would aggregate collaboration metrics
        return [
            'active_collaborations' => 0, // Count active collaborative sessions
            'total_changes_today' => 0, // Count changes made today
            'most_collaborative_documents' => [], // Top documents by collaboration
            'active_users' => 0, // Currently active users
        ];
    }
}
