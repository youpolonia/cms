<?php
/**
 * Database Schema Auditor
 * Temporary utility for tenant isolation audit
 */

require_once __DIR__ . '/../config.php';
if (!defined('DB_PORT')) {
    $envPort = getenv('DB_PORT');
    define('DB_PORT', ($envPort !== false && $envPort !== '') ? (int)$envPort : 3306);
}
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_HOSTNAME')) {
    define('DB_HOSTNAME', DB_HOST);
}
class DBSchemaAuditor {
    private static $connection;

    public static function getConnection() {
        if (!self::$connection) {
            self::$connection = \core\Database::connection();
        }
        return self::$connection;
    }
    
    public static function getTables(): array {
        $conn = self::getConnection();
        $result = $conn->query("SHOW TABLES");
        $tables = [];
        while ($row = $result->fetch(\PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        return $tables;
    }
    
    public static function getTableStructure(string $tableName): array {
        $conn = self::getConnection();
        $structure = [
            'columns' => [],
            'indexes' => [],
            'foreign_keys' => []
        ];
        
        // Get columns
        $columns = $conn->query("SHOW COLUMNS FROM `$tableName`");
        while ($column = $columns->fetch(\PDO::FETCH_ASSOC)) {
            $structure['columns'][] = $column;
        }

        // Get indexes
        $indexes = $conn->query("SHOW INDEX FROM `$tableName`");
        while ($index = $indexes->fetch(\PDO::FETCH_ASSOC)) {
            $structure['indexes'][] = $index;
        }
        
        // Get foreign keys (MySQL 5.6+)
        $fks = $conn->query("
            SELECT
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME,
                UPDATE_RULE,
                DELETE_RULE
            FROM
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE
                TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = '$tableName'
                AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        while ($fk = $fks->fetch(\PDO::FETCH_ASSOC)) {
            $structure['foreign_keys'][] = $fk;
        }
        
        return $structure;
    }
    
    public static function closeConnection() {
        if (self::$connection) {
            self::$connection = null;
        }
    }
}
