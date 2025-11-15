<?php
require_once __DIR__ . '/../../config.php';

/**
 * DatabaseConnection - PDO wrapper with query builder
 * 
 * Provides secure database access with advanced query building capabilities
 * Designed for shared hosting environments with no external dependencies
 */

namespace CMS\Database;

use PDO;
use PDOException;
use CMS\ErrorHandler;

class DatabaseConnection {
    private $pdo;
    private $errorHandler;
    private $config;

    /**
     * Constructor - establishes database connection
     * 
     * @param array $config Database configuration
     * @param ErrorHandler $errorHandler Error handler instance
     */
    public function __construct(array $config, ErrorHandler $errorHandler) {
        $this->config = $this->validateConfig($config);
        $this->errorHandler = $errorHandler;
        $this->connect();
    }

    /**
     * Validate database configuration
     */
    private function validateConfig(array $config): array {
        $required = ['host', 'username', 'password', 'database'];
        foreach ($required as $key) {
            if (!isset($config[$key])) {
                throw new \InvalidArgumentException("Missing required database config: $key");
            }
        }

        return array_merge([
            'port' => 3306,
            'charset' => 'utf8mb4',
            'options' => []
        ], $config);
    }

    /**
     * Establish database connection
     */
    private function connect(): void {
        try {
            $this->pdo = \core\Database::connection();
        } catch (PDOException $e) {
            $this->errorHandler->logDatabaseError($e);
            throw new \RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Execute raw SQL query
     */
    public function query(string $sql, array $params = []): \PDOStatement {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->errorHandler->logDatabaseError($e);
            throw $e;
        }
    }

    /**
     * Query builder - SELECT
     */
    public function select(string $table, array $columns = ['*'], array $conditions = [], array $options = []): \PDOStatement {
        $columns = empty($columns) ? ['*'] : $columns;
        $sql = "SELECT " . implode(', ', $columns) . " FROM $table";

        if (!empty($conditions)) {
            $where = [];
            $params = [];
            foreach ($conditions as $field => $value) {
                $where[] = "$field = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        // Handle options (ORDER BY, LIMIT, etc)
        if (isset($options['order'])) {
            $sql .= " ORDER BY " . $options['order'];
        }
        if (isset($options['limit'])) {
            $sql .= " LIMIT " . (int)$options['limit'];
        }
        if (isset($options['offset'])) {
            $sql .= " OFFSET " . (int)$options['offset'];
        }

        return $this->query($sql, $params ?? []);
    }

    /**
     * Query builder - INSERT
     */
    public function insert(string $table, array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->query($sql, array_values($data));
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Query builder - UPDATE
     */
    public function update(string $table, array $data, array $conditions): int {
        $set = [];
        $params = [];
        foreach ($data as $field => $value) {
            $set[] = "$field = ?";
            $params[] = $value;
        }

        $where = [];
        foreach ($conditions as $field => $value) {
            $where[] = "$field = ?";
            $params[] = $value;
        }

        $sql = "UPDATE $table SET " . implode(', ', $set) . " WHERE " . implode(' AND ', $where);
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Query builder - DELETE
     */
    public function delete(string $table, array $conditions): int {
        $where = [];
        $params = [];
        foreach ($conditions as $field => $value) {
            $where[] = "$field = ?";
            $params[] = $value;
        }

        $sql = "DELETE FROM $table WHERE " . implode(' AND ', $where);
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): bool {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool {
        return $this->pdo->rollback();
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId(): int {
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Get PDO instance (for advanced operations)
     */
    public function getPdo(): \PDO {
        return $this->pdo;
    }
}
