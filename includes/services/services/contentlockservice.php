<?php
declare(strict_types=1);

class ContentLockService {
    private static ?\PDO $db = null;
    private static int $defaultLockDuration = 3600; // 1 hour

    public static function initialize(\PDO $dbConnection): void {
        self::$db = $dbConnection;
    }

    public static function acquireLock(
        int $contentId, 
        int $userId,
        ?int $duration = null
    ): bool {
        $duration = $duration ?? self::$defaultLockDuration;
        $expiresAt = time() + $duration;

        try {
            $stmt = self::$db->prepare(
                "INSERT INTO content_locks 
                (content_id, user_id, expires_at) 
                VALUES (:content_id, :user_id, :expires_at)
                ON CONFLICT (content_id) DO UPDATE SET
                    user_id = EXCLUDED.user_id,
                    expires_at = EXCLUDED.expires_at
                WHERE content_locks.expires_at < UNIX_TIMESTAMP()"
            );
            
            return $stmt->execute([
                ':content_id' => $contentId,
                ':user_id' => $userId,
                ':expires_at' => $expiresAt
            ]);
        } catch (\PDOException $e) {
            error_log("Lock acquisition failed: " . $e->getMessage());
            return false;
        }
    }

    public static function releaseLock(int $contentId, int $userId): bool {
        try {
            $stmt = self::$db->prepare(
                "DELETE FROM content_locks 
                WHERE content_id = :content_id 
                AND user_id = :user_id"
            );
            return $stmt->execute([
                ':content_id' => $contentId,
                ':user_id' => $userId
            ]);
        } catch (\PDOException $e) {
            error_log("Lock release failed: " . $e->getMessage());
            return false;
        }
    }

    public static function checkLock(int $contentId): ?array {
        try {
            $stmt = self::$db->prepare(
                "SELECT user_id, expires_at 
                FROM content_locks 
                WHERE content_id = :content_id 
                AND expires_at > UNIX_TIMESTAMP()"
            );
            $stmt->execute([':content_id' => $contentId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Lock check failed: " . $e->getMessage());
            return null;
        }
    }

    public static function forceRelease(int $contentId): bool {
        try {
            $stmt = self::$db->prepare(
                "DELETE FROM content_locks 
                WHERE content_id = :content_id"
            );
            return $stmt->execute([':content_id' => $contentId]);
        } catch (\PDOException $e) {
            error_log("Force release failed: " . $e->getMessage());
            return false;
        }
    }
}
