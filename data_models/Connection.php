<?php
namespace Includes\Database;

use PDO;
use PDOException;
use Ramsey\Uuid\Uuid;

class Connection {
    private static $instances = [];
    private $pdo;
    private $tenantId;

    /**
     * Get database connection instance for a tenant
     * @param string $tenantId UUID string
     * @param array $config Database configuration
     * @return Connection
     */
    public static function getInstance(string $tenantId, array $config): self {
        if (!isset(self::$instances[$tenantId])) {
            if (!Uuid::isValid($tenantId)) {
                throw new \InvalidArgumentException('Invalid tenant UUID');
            }
            self::$instances[$tenantId] = new self($tenantId, $config);
        }
        return self::$instances[$tenantId];
    }

    private function __construct(string $tenantId, array $config) {
        $this->tenantId = $tenantId;

        try {
            $this->pdo = \core\Database::connection();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            throw new \RuntimeException("Connection failed");
        }
    }

    /**
     * Execute a query with tenant scope
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|int Query results or affected row count
     */
    public function query(string $sql, array $params = []) {
        // For SELECT queries, automatically add tenant scope if not present
        if (str_starts_with(strtoupper(trim($sql)), 'SELECT') && 
            !str_contains(strtoupper($sql), 'TENANT_ID')) {
            $sql = $this->scopeQuery($sql);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        if (str_starts_with(strtoupper(trim($sql)), 'SELECT')) {
            return $stmt->fetchAll();
        }
        return $stmt->rowCount();
    }

    /**
     * Add tenant scope to a query
     * @param string $sql Original SQL query
     * @return string Scoped SQL query
     */
    private function scopeQuery(string $sql): string {
        $wherePos = stripos($sql, 'WHERE');
        if ($wherePos === false) {
            return $sql . " WHERE tenant_id = :tenant_id";
        }
        return substr($sql, 0, $wherePos + 5) . " tenant_id = :tenant_id AND " . substr($sql, $wherePos + 5);
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit(): bool {
        return $this->pdo->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollBack(): bool {
        return $this->pdo->rollBack();
    }

    /**
     * Get the last inserted ID
     */
    public function lastInsertId(): string {
        return $this->pdo->lastInsertId();
    }
}
