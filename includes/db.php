<?php
require_once __DIR__ . '/../core/bootstrap.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Basic DB helper class for FTP-only CMS
 * Provides static methods for database operations
 */
class DB {
    private static $connection = null;

    /**
     * Get PDO connection (singleton pattern)
     */
    private static function getConnection(): PDO {
        if (self::$connection === null) {
            self::$connection = \core\Database::connection();
        }
        return self::$connection;
    }

    /**
     * Execute SELECT query and return all results
     */
    public static function select(string $query, array $params = []): array {
        $stmt = self::getConnection()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute INSERT query and return last insert ID
     */
    public static function insert(string $query, array $params = []): int {
        $conn = self::getConnection();
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        return $conn->lastInsertId();
    }

    /**
     * Execute UPDATE/DELETE query and return affected row count
     */
    public static function execute(string $query, array $params = []): int {
        $stmt = self::getConnection()->prepare($query);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Execute raw query (for complex operations)
     */
    public static function query(string $query, array $params = []): PDOStatement {
        $stmt = self::getConnection()->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
}
