<?php

namespace Includes\Database;

use PDO;
use PDOException;

class Database {
    private static ?PDO $connection = null;
    private static bool $testMode = false;
    private static ?PDO $testConnection = null;

    public static function connect(array $config): void {
        if (self::$connection === null && !self::$testMode) {
            try {
                require_once __DIR__ . '/../../core/database.php';
                self::$connection = \core\Database::connection();
            } catch (PDOException $e) {
                error_log($e->getMessage());
                throw new \RuntimeException("Database connection failed");
            }
        }
    }

    public static function setTestConnection(PDO $connection): void {
        self::$testMode = true;
        self::$testConnection = $connection;
    }

    public static function clearTestConnection(): void {
        self::$testMode = false;
        self::$testConnection = null;
    }

    public static function execute(string $query, array $params = [], ?string $tenantId = null): bool {
        $connection = self::$testMode ? self::$testConnection : self::$connection;
        if ($connection === null) {
            throw new \RuntimeException("Database not connected");
        }

        if ($tenantId !== null) {
            $query = self::addTenantCondition($query, $tenantId);
        }

        $stmt = $connection->prepare($query);
        return $stmt->execute($params);
    }

    public static function query(string $query, array $params = [], ?string $tenantId = null): array {
        $connection = self::$testMode ? self::$testConnection : self::$connection;
        if ($connection === null) {
            throw new \RuntimeException("Database not connected");
        }

        if ($tenantId !== null) {
            $query = self::addTenantCondition($query, $tenantId);
        }

        $stmt = $connection->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private static function addTenantCondition(string $query, string $tenantId): string {
        // Skip if query already has tenant condition
        if (strpos($query, 'tenant_id') !== false) {
            return $query;
        }

        // Simple check for WHERE clause
        $wherePos = stripos($query, 'WHERE');
        if ($wherePos !== false) {
            return substr_replace($query, " AND tenant_id = :tenant_id ", $wherePos + 5, 0);
        }

        // For UPDATE/DELETE without WHERE, add WHERE clause
        if (stripos($query, 'UPDATE') === 0 || stripos($query, 'DELETE') === 0) {
            $query .= " WHERE tenant_id = :tenant_id";
        }

        return $query;
    }

    /**
     * Get last inserted ID
     * @return int
     */
    public static function getLastInsertId(): int {
        $connection = self::$testMode ? self::$testConnection : self::$connection;
        if ($connection === null) {
            throw new \RuntimeException("Database not connected");
        }
        return (int) $connection->lastInsertId();
    }

    /**
     * Get raw PDO connection
     * Note: Tenant filtering will not be applied to queries made directly through this connection
     * @return PDO
     */
    public static function getConnection(): PDO {
        $connection = self::$testMode ? self::$testConnection : self::$connection;
        if ($connection === null) {
            throw new \RuntimeException("Database not connected");
        }
        return $connection;
    }

    /**
     * Get tenant-scoped PDO connection
     * @param string $tenantId Tenant ID to scope queries
     * @return PDO
     */
    /**
     * Get tenant-scoped PDO connection
     * @param string $tenantId Tenant ID to scope queries (must be non-empty)
     * @return PDO
     * @throws \InvalidArgumentException If tenant ID is empty
     */
    public static function getTenantConnection(string $tenantId): PDO {
        if (empty($tenantId)) {
            throw new \InvalidArgumentException("Tenant ID cannot be empty");
        }
        
        $connection = self::getConnection();
        $connection->setAttribute(PDO::ATTR_STATEMENT_CLASS, ['TenantAwarePDOStatement', [$tenantId]]);
        return $connection;
    }
}
