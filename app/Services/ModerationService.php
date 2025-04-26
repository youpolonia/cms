<?php

namespace App\Services;

use App\Models\ModerationQueue;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ContentModerated;

class ModerationService
{
    public function moderateContent(int $contentId, string $status, ?User $moderator, ?string $reason = null): ModerationQueue
    {
        $moderation = ModerationQueue::where('content_id', $contentId)
            ->where('status', 'pending')
            ->firstOrFail();

        $moderation->update([
            'status' => $status,
            'moderator_id' => $moderator?->id,
            'moderated_at' => now(),
            'rejection_reason' => $status === 'rejected' ? $reason : null,
            'moderation_result' => [
                'action' => $status,
                'moderator' => $moderator?->name,
                'timestamp' => now()->toDateTimeString()
            ]
        ]);

        // Notify content creator
        if ($moderation->user) {
            Notification::send($moderation->user, new ContentModerated($moderation));
        }

        return $moderation;
    }

    public function getPendingItems(int $limit = 10)
    {
        return ModerationQueue::with(['content', 'user'])
            ->where('status', 'pending')
            ->latest()
            ->limit($limit)
            ->get();
    }
}