<?php
namespace models;

require_once __DIR__ . '/../config.php';

use Database;

class WorkflowModel {
    private static $table = 'workflows';

    public static function create(array $data): int {
        $db = \core\Database::connection();
        return $db->insert(self::$table, [
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'status' => $data['status'] ?? 'draft',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public static function update(int $id, array $data): bool {
        $db = \core\Database::connection();
        return $db->update(self::$table, [
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'status' => $data['status'] ?? 'draft',
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);
    }

    public static function get(int $id): ?array {
        $db = \core\Database::connection();
        return $db->query("SELECT * FROM ".self::$table." WHERE id = ?", [$id])->fetch();
    }

    public static function getAll(): array {
        $db = \core\Database::connection();
        return $db->query("SELECT * FROM ".self::$table)->fetchAll();
    }
}
