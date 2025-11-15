<?php
/**
 * PriorityQueue Model for Approval Workflow Module
 * 
 * Manages priority queue items for approval workflows
 */
class PriorityQueue {
    private static $table = 'priority_queue';
    private static $db;

    /**
     * Initialize database connection
     */
    public static function init($db) {
        self::$db = $db;
    }

    /**
     * Create new queue item
     */
    public static function create(array $data): int {
        $errors = self::validate($data);
        if (!empty($errors)) {
            throw new InvalidArgumentException('Invalid queue item data: ' . implode(', ', $errors));
        }

        $query = "INSERT INTO " . self::$table . " SET
            item_type = :item_type,
            item_id = :item_id,
            priority = :priority,
            status = :status,
            created_at = NOW()";

        $stmt = self::$db->prepare($query);
        $stmt->execute([
            ':item_type' => $data['item_type'],
            ':item_id' => $data['item_id'],
            ':priority' => $data['priority'] ?? 5,
            ':status' => $data['status'] ?? 'pending'
        ]);

        return self::$db->lastInsertId();
    }

    /**
     * Get queue item by ID
     */
    public static function read(int $id): ?array {
        $query = "SELECT * FROM " . self::$table . " WHERE id = :id";
        $stmt = self::$db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Update queue item
     */
    public static function update(int $id, array $data): bool {
        $errors = self::validate($data);
        if (!empty($errors)) {
            throw new InvalidArgumentException('Invalid queue item data: ' . implode(', ', $errors));
        }

        $query = "UPDATE " . self::$table . " SET
            item_type = :item_type,
            item_id = :item_id,
            priority = :priority,
            status = :status,
            updated_at = NOW()
            WHERE id = :id";

        $stmt = self::$db->prepare($query);
        return $stmt->execute([
            ':item_type' => $data['item_type'],
            ':item_id' => $data['item_id'],
            ':priority' => $data['priority'],
            ':status' => $data['status'],
            ':id' => $id
        ]);
    }

    /**
     * Delete queue item
     */
    public static function delete(int $id): bool {
        $query = "DELETE FROM " . self::$table . " WHERE id = :id";
        $stmt = self::$db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Validate queue item data
     */
    private static function validate(array $data): array {
        $errors = [];
        
        // Validate item_type
        if (empty($data['item_type'])) {
            $errors[] = 'item_type is required';
        } elseif (!is_string($data['item_type'])) {
            $errors[] = 'item_type must be a string';
        }

        // Validate item_id
        if (empty($data['item_id'])) {
            $errors[] = 'item_id is required';
        } elseif (!is_numeric($data['item_id']) || $data['item_id'] <= 0) {
            $errors[] = 'item_id must be a positive integer';
        }

        // Validate priority if provided
        if (isset($data['priority'])) {
            if (!is_numeric($data['priority']) || $data['priority'] < 1 || $data['priority'] > 10) {
                $errors[] = 'priority must be between 1 and 10';
            }
        }

        // Validate status if provided
        if (isset($data['status'])) {
            $validStatuses = ['pending', 'approved', 'rejected', 'processing'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors[] = 'status must be one of: ' . implode(', ', $validStatuses);
            }
        }

        return $errors;
    }
}
