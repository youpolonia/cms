<?php
/**
 * Abstract base model class for database operations
 * Uses PDO via db.php connection
 */
abstract class DBModel {
    protected static function query(string $sql, array $params = []): PDOStatement {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    protected static function findById(string $table, int $id): ?array {
        $stmt = self::query(
            "SELECT * FROM `$table` WHERE id = :id LIMIT 1",
            ['id' => $id]
        );
        return $stmt->fetch() ?: null;
    }

    protected static function save(string $table, array $data, ?int $id = null): int {
        if ($id === null) {
            // Insert
            $columns = implode(', ', array_keys($data));
            $values = ':' . implode(', :', array_keys($data));
            $sql = "INSERT INTO `$table` ($columns) VALUES ($values)";
        } else {
            // Update
            $set = implode(', ', array_map(fn($col) => "$col = :$col", array_keys($data)));
            $sql = "UPDATE `$table` SET $set WHERE id = :id";
            $data['id'] = $id;
        }

        self::query($sql, $data);
        return $id ?? (int)db()->lastInsertId();
    }
}
