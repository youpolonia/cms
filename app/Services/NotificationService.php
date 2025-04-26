<?php

namespace App\Services;

use App\Models\OperationNotification;
use App\Models\User;

class NotificationService
{
    public function createOperationNotification(
        User $user,
        string $type,
        string $initialMessage,
        array $metadata = null
    ): OperationNotification {
        return OperationNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'status' => 'pending',
            'message' => $initialMessage,
            'metadata' => $metadata
        ]);
    }

    public function getPendingNotifications(User $user, int $limit = 5)
    {
        return OperationNotification::where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getRecentNotifications(User $user, int $limit = 10)
    {
        return OperationNotification::where('user_id', $user->id)
            ->where('status', '!=', 'pending')
            ->latest()
            ->limit($limit)
            ->get();
    }
}