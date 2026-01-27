<?php
namespace core;

require_once __DIR__ . '/../config.php';

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * Database abstraction layer with connection handling, query builder,
 * prepared statements, and migration support
 */
class Database {
    private static ?self $instance = null;
    private static ?PDO $connection = null;
    private static array $config = [];

    private static function initConfig(): void {
        if (empty(self::$config)) {
            self::$config = [
                'host' => defined('DB_HOST') ? DB_HOST : '',
                'dbname' => defined('DB_NAME') ? DB_NAME : '',
                'charset' => 'utf8mb4',
                'user' => defined('DB_USER') ? DB_USER : '',
                'pass' => defined('DB_PASSWORD') ? DB_PASSWORD : (defined('DB_PASS') ? DB_PASS : ''),
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => false
                ]
            ];
        }
    }

    /**
     * Get singleton instance of Database
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function connect(): PDO {
        self::initConfig();
        if (self::$connection === null) {
            // PDO constructor requires non-empty host, user, and dbname
            if (empty(self::$config['host']) || empty(self::$config['user']) || empty(self::$config['dbname'])) {
                throw new RuntimeException("Database credentials (host, user, dbname) are not configured.");
            }
            
            try {
                // Fix socket issue: use 127.0.0.1 instead of localhost for TCP connection
                $host = self::$config['host'];
                if ($host === 'localhost') {
                    $host = '127.0.0.1';
                }
                
                $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',
                    $host,
                    self::$config['dbname'],
                    self::$config['charset']
                );
                
                self::$connection = new PDO(
                    $dsn,
                    self::$config['user'],
                    self::$config['pass'],
                    self::$config['options']
                );
            } catch (PDOException $e) {
                throw new RuntimeException("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }

    /**
     * Alias for connect(), used by legacy and refactored code.
     */
    public static function connection(): PDO {
        return self::connect();
    }

    /**
     * Get the PDO connection instance (for singleton usage)
     */
    public function getConnection(): PDO {
        return self::connect();
    }

    // Basic query execution with enhanced prepared statement support
    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::connect()->prepare($sql);
        
        foreach ($params as $key => $value) {
            $type = match(true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            };
            $stmt->bindValue(is_int($key) ? $key + 1 : $key, $value, $type);
        }
        
        $stmt->execute();
        return $stmt;
    }

    // Prepared statement helper
    public static function prepare(string $sql): PDOStatement {
        return self::connect()->prepare($sql);
    }

    // Query builder methods
    public static function table(string $table): QueryBuilder {
        return new QueryBuilder($table);
    }

    public static function select(string $table, array $columns = ['*']): PDOStatement {
        $columns = implode(', ', $columns);
        return self::query("SELECT $columns FROM `$table`");
    }

    public static function insert(string $table, array $data): int {
        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO `$table` ($columns) VALUES ($values)";
        self::query($sql, $data);
        return (int)self::connect()->lastInsertId();
    }

    public static function update(string $table, array $data, array $where): int {
        $set = implode(', ', array_map(fn($col) => "$col = :$col", array_keys($data)));
        $whereClause = implode(' AND ', array_map(fn($col) => "$col = :where_$col", array_keys($where)));
        
        $params = $data;
        foreach ($where as $key => $value) {
            $params["where_$key"] = $value;
        }
        
        $sql = "UPDATE `$table` SET $set WHERE $whereClause";
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    public static function delete(string $table, array $where): int {
        $whereClause = implode(' AND ', array_map(fn($col) => "$col = :$col", array_keys($where)));
        $sql = "DELETE FROM `$table` WHERE $whereClause";
        $stmt = self::query($sql, $where);
        return $stmt->rowCount();
    }

    // Migration system will be implemented separately
}
