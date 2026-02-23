<?php
/**
 * BaseModel - Simple base model for Jessie AI-CMS
 * Provides basic CRUD operations with prepared statements
 * 
 * @package JessieCMS
 * @since 2026-02-15
 */

require_once __DIR__ . '/../core/database.php';

abstract class BaseModel
{
    protected static string $table = '';

    /**
     * Get database connection
     */
    protected static function db(): \PDO
    {
        return \core\Database::connection();
    }

    /**
     * Find record by ID
     */
    public static function find(int $id): ?array
    {
        if (empty(static::$table)) {
            throw new \Exception('Table name not defined');
        }

        $sql = "SELECT * FROM `" . static::$table . "` WHERE id = ? LIMIT 1";
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$id]);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find record by column value
     */
    public static function findBy(string $col, mixed $val): ?array
    {
        if (empty(static::$table)) {
            throw new \Exception('Table name not defined');
        }

        $sql = "SELECT * FROM `" . static::$table . "` WHERE `$col` = ? LIMIT 1";
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$val]);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get all records with optional conditions
     */
    public static function all(array $where = [], string $orderBy = 'id DESC', int $limit = 100): array
    {
        if (empty(static::$table)) {
            throw new \Exception('Table name not defined');
        }

        $sql = "SELECT * FROM `" . static::$table . "`";
        $params = [];

        // Build WHERE clause
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                $conditions[] = "`$column` = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // Add ORDER BY
        $sql .= " ORDER BY $orderBy";

        // Add LIMIT
        $sql .= " LIMIT ?";
        $params[] = $limit;

        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Create new record
     */
    public static function create(array $data): int
    {
        if (empty(static::$table)) {
            throw new \Exception('Table name not defined');
        }

        if (empty($data)) {
            throw new \Exception('Data array cannot be empty');
        }

        $columns = array_keys($data);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        
        $sql = "INSERT INTO `" . static::$table . "` (`" . implode('`, `', $columns) . "`) VALUES ($placeholders)";
        $stmt = static::db()->prepare($sql);
        $stmt->execute(array_values($data));
        
        return (int)static::db()->lastInsertId();
    }

    /**
     * Update record by ID
     */
    public static function update(int $id, array $data): bool
    {
        if (empty(static::$table)) {
            throw new \Exception('Table name not defined');
        }

        if (empty($data)) {
            throw new \Exception('Data array cannot be empty');
        }

        $columns = array_keys($data);
        $setPairs = array_map(fn($col) => "`$col` = ?", $columns);
        
        $sql = "UPDATE `" . static::$table . "` SET " . implode(', ', $setPairs) . " WHERE id = ?";
        $params = array_values($data);
        $params[] = $id;
        
        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete record by ID
     */
    public static function delete(int $id): bool
    {
        if (empty(static::$table)) {
            throw new \Exception('Table name not defined');
        }

        $sql = "DELETE FROM `" . static::$table . "` WHERE id = ?";
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Count records with optional conditions
     */
    public static function count(array $where = []): int
    {
        if (empty(static::$table)) {
            throw new \Exception('Table name not defined');
        }

        $sql = "SELECT COUNT(*) as count FROM `" . static::$table . "`";
        $params = [];

        // Build WHERE clause
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                $conditions[] = "`$column` = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)$result['count'];
    }
}