<?php

require_once __DIR__ . '/../config.php';

class ConflictResolutionService {
    /**
     * Record a content conflict
     * @param string $contentId
     * @param string $localVersion
     * @param string $remoteVersion
     * @return int|false The conflict ID or false on failure
     */
    public static function recordConflict(
        string $contentId,
        string $localVersion,
        string $remoteVersion
    ) {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("
                INSERT INTO federation_conflicts
                (content_id, resolution_type, created_at)
                VALUES (?, 'manual', NOW())
            ");
            
            if ($stmt->execute([$contentId])) {
                return $db->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("Conflict recording failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Resolve a content conflict
     * @param int $conflictId
     * @param string $resolutionType
     * @param string|null $winningVersion
     * @param array|null $mergedContent
     * @param string $resolvedBy
     * @return bool
     */
    public static function resolveConflict(
        int $conflictId,
        string $resolutionType,
        ?string $winningVersion,
        ?array $mergedContent,
        string $resolvedBy
    ): bool {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("
                UPDATE federation_conflicts SET
                resolution_type = ?,
                winning_version = ?,
                merged_content = ?,
                resolved_by = ?,
                resolved_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([
                $resolutionType,
                $winningVersion,
                $mergedContent ? json_encode($mergedContent) : null,
                $resolvedBy,
                $conflictId
            ]);
        } catch (Exception $e) {
            error_log("Conflict resolution failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unresolved conflicts
     * @param int $limit
     * @return array
     */
    public static function getUnresolvedConflicts(int $limit = 100): array {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("
                SELECT * FROM federation_conflicts
                WHERE resolved_at IS NULL
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Failed to fetch conflicts: " . $e->getMessage());
            return [];
        }
    }
}
