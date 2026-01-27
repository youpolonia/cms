<?php
require_once __DIR__ . '/../core/database.php';

require_once __DIR__ . '/../core/cache.php';

class SettingsModel {
    private static $table = 'system_settings';
    
    public static function getSettings($tenant_id = null) {
        return self::getAll($tenant_id);
    }
    
    public static function getAll($tenant_id = null) {
        $cacheKey = 'system_settings' . ($tenant_id ? '_tenant_' . $tenant_id : '');
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        try {
            $pdo = \core\Database::connection();
            $sql = "SELECT * FROM " . self::$table;
            $params = [];

            if ($tenant_id !== null) {
                $sql .= " WHERE tenant_id = :tenant_id";
                $params[':tenant_id'] = $tenant_id;
            }
            $sql .= " LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $settings = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            Cache::set($cacheKey, $settings, 3600);
            return $settings;
        } catch (\Throwable $e) {
            error_log('DB error in SettingsModel::getAll: ' . $e->getMessage());
            return [];
        }
    }
    
    public static function saveSettings($settings, $tenant_id = null) {
        $cacheKey = 'system_settings' . ($tenant_id ? '_tenant_' . $tenant_id : '');
        require_once __DIR__ . '/../core/database.php';

        // Clear cache before saving
        Cache::clear($cacheKey);

        // Validate required fields
        if (empty($settings['site_title'])) {
            throw new InvalidArgumentException('Site title is required');
        }

        try {
            $pdo = \core\Database::connection();

            // Get current settings to determine insert vs update
            $current = self::getSettings($tenant_id);

            if (empty($current)) {
                $stmt = $pdo->prepare("INSERT INTO " . self::$table . "
                    (site_title, site_description, timezone, maintenance_mode, tenant_id)
                    VALUES (:title, :desc, :tz, :maintenance, :tenant_id)");
            } else {
                $stmt = $pdo->prepare("UPDATE " . self::$table . " SET
                    site_title = :title,
                    site_description = :desc,
                    timezone = :tz,
                    maintenance_mode = :maintenance
                    WHERE " . ($tenant_id ? "tenant_id = :tenant_id" : "tenant_id IS NULL"));
            }

            $params = [
                ':title' => $settings['site_title'],
                ':desc' => $settings['site_description'] ?? '',
                ':tz' => $settings['timezone'] ?? 'UTC',
                ':maintenance' => $settings['maintenance_mode'] ?? 0
            ];

            if ($tenant_id !== null) {
                $params[':tenant_id'] = $tenant_id;
            }

            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('DB error in SettingsModel::saveSettings: ' . $e->getMessage());
            return false;
        }
    }

    public static function getActiveTheme($tenant_id = null): string
    {
        $settings = self::getSettings($tenant_id);
        return $settings['active_theme'] ?? 'default';
    }

    public static function setActiveTheme(string $theme, $tenant_id = null): bool
    {
        $cacheKey = 'system_settings' . ($tenant_id ? '_tenant_' . $tenant_id : '');
        Cache::clear($cacheKey);

        try {
            $pdo = \core\Database::connection();

            if ($tenant_id !== null) {
                $stmt = $pdo->prepare("UPDATE " . self::$table . " SET active_theme = :theme WHERE tenant_id = :tenant_id");
                $stmt->execute([':theme' => $theme, ':tenant_id' => $tenant_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE " . self::$table . " SET active_theme = :theme WHERE tenant_id IS NULL");
                $stmt->execute([':theme' => $theme]);
            }

            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('DB error in SettingsModel::setActiveTheme: ' . $e->getMessage());
            return false;
        }
    }
}
