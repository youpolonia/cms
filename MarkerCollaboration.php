<?php
declare(strict_types=1);

/**
 * Marker Collaboration System
 * Handles real-time collaboration features for content markers
 */
class MarkerCollaboration {
    private static array $activeSessions = [];
    private static array $collaborators = [];

    /**
     * Initialize collaboration session
     */
    public static function initSession(string $markerId, string $userId): bool {
        if (!isset(self::$activeSessions[$markerId])) {
            self::$activeSessions[$markerId] = [
                'created_at' => time(),
                'modified_at' => time(),
                'lock_owner' => $userId
            ];
            return true;
        }
        return false;
    }

    /**
     * Add collaborator to marker session
     */
    public static function addCollaborator(string $markerId, string $userId): bool {
        if (!isset(self::$collaborators[$markerId])) {
            self::$collaborators[$markerId] = [];
        }

        if (!in_array($userId, self::$collaborators[$markerId])) {
            self::$collaborators[$markerId][] = $userId;
            return true;
        }
        return false;
    }

    /**
     * Get active collaborators for marker
     */
    public static function getCollaborators(string $markerId): array {
        return self::$collaborators[$markerId] ?? [];
    }

    /**
     * Release collaboration lock
     */
    public static function releaseLock(string $markerId, string $userId): bool {
        if (isset(self::$activeSessions[$markerId]) && 
            self::$activeSessions[$markerId]['lock_owner'] === $userId) {
            unset(self::$activeSessions[$markerId]);
            return true;
        }
        return false;
    }
}
