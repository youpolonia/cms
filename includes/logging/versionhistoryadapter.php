<?php

namespace Includes\Logging;

require_once __DIR__ . '/../../core/database.php';

use Includes\Version\SemanticVersionComparator;
use PDO;

class VersionHistoryAdapter {
    private const HISTORY_TABLE = 'version_history';

    public static function logVersionChange(
        string $component,
        string $oldVersion,
        string $newVersion,
        ?string $changeType = null,
        ?array $metadata = null,
        ?int $userId = null
    ): bool {
        $comparison = SemanticVersionComparator::compare($oldVersion, $newVersion);
        
        $db = \core\Database::connection();
        $stmt = $db->prepare("
            INSERT INTO " . self::HISTORY_TABLE . " 
            (component, old_version, new_version, change_type, comparison_result, metadata, user_id, created_at)
            VALUES (:component, :oldVersion, :newVersion, :changeType, :comparison, :metadata, :userId, NOW())
        ");

        return $stmt->execute([
            ':component' => $component,
            ':oldVersion' => $oldVersion,
            ':newVersion' => $newVersion,
            ':changeType' => $changeType,
            ':comparison' => $comparison,
            ':metadata' => $metadata ? json_encode($metadata) : null,
            ':userId' => $userId
        ]);
    }

    public static function getVersionHistory(
        string $component,
        int $limit = 100,
        ?string $changeType = null
    ): array {
        $db = \core\Database::connection();
        $query = "SELECT * FROM " . self::HISTORY_TABLE . " WHERE component = :component";
        $params = [':component' => $component];

        if ($changeType) {
            $query .= " AND change_type = :changeType";
            $params[':changeType'] = $changeType;
        }

        $query .= " ORDER BY created_at DESC LIMIT :limit";
        $params[':limit'] = $limit;

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getLatestVersion(string $component): ?string {
        $db = \core\Database::connection();
        $stmt = $db->prepare("
            SELECT new_version 
            FROM " . self::HISTORY_TABLE . " 
            WHERE component = :component 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([':component' => $component]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['new_version'] : null;
    }
}
