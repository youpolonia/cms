<?php
namespace CMS\Realtime;

class PresenceTracker {
    private $db;
    private $activeUsers = [];

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function userJoined(string $userId, string $documentId): void {
        $this->activeUsers[$documentId][$userId] = time();
        
        $stmt = $this->db->prepare("
            INSERT INTO presence_tracking 
            (user_id, document_id, last_active) 
            VALUES (:user_id, :document_id, NOW())
            ON DUPLICATE KEY UPDATE last_active = NOW()
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':document_id' => $documentId
        ]);
    }

    public function userLeft(string $userId, string $documentId): void {
        unset($this->activeUsers[$documentId][$userId]);
        
        $stmt = $this->db->prepare("
            UPDATE presence_tracking 
            SET last_active = NULL 
            WHERE user_id = :user_id AND document_id = :document_id
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':document_id' => $documentId
        ]);
    }

    public function getActiveUsers(string $documentId): array {
        // First check in-memory cache
        if (isset($this->activeUsers[$documentId])) {
            return array_keys($this->activeUsers[$documentId]);
        }

        // Fallback to database
        $stmt = $this->db->prepare("
            SELECT user_id FROM presence_tracking 
            WHERE document_id = :document_id 
            AND last_active > DATE_SUB(NOW(), INTERVAL 2 MINUTE)
        ");
        $stmt->execute([':document_id' => $documentId]);
        
        return array_column($stmt->fetchAll(), 'user_id');
    }

    public function cleanupInactive(): void {
        $stmt = $this->db->prepare("
            DELETE FROM presence_tracking 
            WHERE last_active IS NULL 
            OR last_active < DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute();
    }
}
