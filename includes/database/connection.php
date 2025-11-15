<?php
/**
 * Secure database connection handler with SQL injection protection
 */
class Connection {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            require_once __DIR__ . '/../../core/database.php';
            self::$instance = \core\Database::connection();
        }
        return self::$instance;
    }

    /**
     * Execute a prepared statement with parameters
     */
    public static function execute(string $sql, array $params = []): PDOStatement {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Quote identifier for safe use in SQL
     */
    public static function quoteIdentifier(string $identifier): string {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
