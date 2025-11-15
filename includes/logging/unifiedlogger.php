<?php

namespace Includes\Logging;

require_once __DIR__ . '/../../core/database.php';

use PDO;

class UnifiedLogger {
    private const LOG_TABLE = 'system_logs';
    private const LOG_TYPES = [
        'ERROR', 
        'WARNING', 
        'INFO', 
        'DEBUG',
        'AUDIT'
    ];

    public static function log(
        string $type,
        string $message,
        ?string $source = null,
        ?array $metadata = null,
        ?int $userId = null
    ): bool {
        if (!in_array($type, self::LOG_TYPES)) {
            throw new \InvalidArgumentException("Invalid log type: $type");
        }

        $db = \core\Database::connection();
        $stmt = $db->prepare("
            INSERT INTO " . self::LOG_TABLE . " 
            (log_type, message, source, metadata, user_id, created_at)
            VALUES (:type, :message, :source, :metadata, :userId, NOW())
        ");

        return $stmt->execute([
            ':type' => $type,
            ':message' => $message,
            ':source' => $source,
            ':metadata' => $metadata ? json_encode($metadata) : null,
            ':userId' => $userId
        ]);
    }

    public static function error(string $message, ?string $source = null, ?array $metadata = null, ?int $userId = null): bool {
        return self::log('ERROR', $message, $source, $metadata, $userId);
    }

    public static function warning(string $message, ?string $source = null, ?array $metadata = null, ?int $userId = null): bool {
        return self::log('WARNING', $message, $source, $metadata, $userId);
    }

    public static function info(string $message, ?string $source = null, ?array $metadata = null, ?int $userId = null): bool {
        return self::log('INFO', $message, $source, $metadata, $userId);
    }

    public static function debug(string $message, ?string $source = null, ?array $metadata = null, ?int $userId = null): bool {
        return self::log('DEBUG', $message, $source, $metadata, $userId);
    }

    public static function audit(string $message, ?string $source = null, ?array $metadata = null, ?int $userId = null): bool {
        return self::log('AUDIT', $message, $source, $metadata, $userId);
    }

    public static function getRecentLogs(int $limit = 100, ?string $type = null): array {
        $db = \core\Database::connection();
        $query = "SELECT * FROM " . self::LOG_TABLE;
        $params = [];

        if ($type) {
            $query .= " WHERE log_type = :type";
            $params[':type'] = $type;
        }

        $query .= " ORDER BY created_at DESC LIMIT :limit";
        $params[':limit'] = $limit;

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
