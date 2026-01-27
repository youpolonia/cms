<?php
require_once __DIR__ . '/../../core/database.php';

/**
 * SystemSettings Model
 * Handles database operations for system settings
 */
class SystemSettings {
    /**
     * Get all settings from database
     * @return array Associative array of settings
     */
    public static function getAll(): array {
        $pdo = \core\Database::connection();
        $stmt = $pdo->query("SELECT * FROM system_settings LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Update all settings
     * @param array $settings Associative array of settings to update
     * @return bool True on success
     */
    public static function updateAll(array $settings): bool {
        require_once __DIR__ . '/../../core/database.php';
        $pdo = \core\Database::connection();
        
        // Prepare update statement for each setting
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("
                UPDATE system_settings 
                SET $key = :value 
                WHERE id = 1
            ");
            $stmt->bindValue(':value', $value);
            if (!$stmt->execute()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get single setting value
     * @param string $key Setting name
     * @return mixed|null Setting value or null if not found
     */
    public static function get(string $key) {
        $settings = self::getAll();
        return $settings[$key] ?? null;
    }
}
