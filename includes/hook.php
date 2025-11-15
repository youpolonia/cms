<?php
/**
 * Hook Model
 */
class Hook {
    private static $table = 'system_hooks';

    public $id;
    public $name;
    public $description;
    public $created_at;
    public $updated_at;

    /**
     * Get all hooks from database
     */
    public static function getAll(): array {
        global $db;
        $query = "SELECT * FROM " . self::$table;
        $result = $db->query($query);
        
        $hooks = [];
        while ($row = $result->fetch_assoc()) {
            $hook = new self();
            $hook->id = $row['id'];
            $hook->name = $row['name'];
            $hook->description = $row['description'];
            $hook->created_at = $row['created_at'];
            $hook->updated_at = $row['updated_at'];
            $hooks[] = $hook;
        }
        
        return $hooks;
    }

    /**
     * Create a new hook
     */
    public static function create(array $data): bool {
        global $db;
        $query = "INSERT INTO " . self::$table . " (name, description) VALUES (?, ?)";
        $stmt = $db->prepare($query);
        $stmt->bind_param('ss', $data['name'], $data['description']);
        return $stmt->execute();
    }

    /**
     * Update a hook
     */
    public static function update(int $id, array $data): bool {
        global $db;
        $query = "UPDATE " . self::$table . " SET name = ?, description = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param('ssi', $data['name'], $data['description'], $id);
        return $stmt->execute();
    }

    /**
     * Delete a hook
     */
    public static function delete(int $id): bool {
        global $db;
        $query = "DELETE FROM " . self::$table . " WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
