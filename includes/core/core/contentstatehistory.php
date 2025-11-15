<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/database.php';

use PDO;

class ContentStateHistory {
    public static function logTransition(
        int $contentId,
        ?int $fromStateId,
        int $toStateId,
        int $userId,
        string $notes = ''
    ): bool {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare(
                "INSERT INTO content_state_history 
                (content_id, from_state_id, to_state_id, changed_by, notes) 
                VALUES (?, ?, ?, ?, ?)"
            );
            return $stmt->execute([
                $contentId,
                $fromStateId,
                $toStateId,
                $userId,
                $notes
            ]);
        } catch (PDOException $e) {
            error_log("State transition logging failed: " . $e->getMessage());
            return false;
        }
    }

    public static function getHistory(int $contentId, int $limit = 50): array {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare(
                "SELECT h.*, u.username, 
                fs.name as from_state_name, ts.name as to_state_name
                FROM content_state_history h
                LEFT JOIN users u ON h.changed_by = u.id
                LEFT JOIN content_states fs ON h.from_state_id = fs.id
                LEFT JOIN content_states ts ON h.to_state_id = ts.id
                WHERE h.content_id = ?
                ORDER BY h.changed_at DESC
                LIMIT ?"
            );
            $stmt->execute([$contentId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Failed to fetch state history: " . $e->getMessage());
            return [];
        }
    }
}
