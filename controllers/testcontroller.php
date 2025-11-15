<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

declare(strict_types=1);

class TestController {
    public static function testContentVersions(): array {
        try {
            $db = \core\Database::connection();
            $stmt = $db->query("SELECT * FROM content_versions LIMIT 5");
            return [
                'success' => true,
                'versions' => $stmt->fetchAll(\PDO::FETCH_ASSOC)
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public static function testContentStates(): array {
        try {
            $db = \core\Database::connection();
            $stmt = $db->query("SELECT * FROM content_items LIMIT 5");
            return [
                'success' => true,
                'content_items' => $stmt->fetchAll(\PDO::FETCH_ASSOC)
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public static function testMigrationRollback(array $data): array {
        try {
            // Verify migration exists
            $migration = $data['migration'] ?? '';
            if (!preg_match('/^\d{4}_\w+\.php$/', $migration)) {
                throw new \InvalidArgumentException("Invalid migration format");
            }

            // Execute rollback (simulated)
            return [
                'success' => true,
                'message' => "Rollback simulated for $migration",
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
