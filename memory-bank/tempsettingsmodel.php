<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/cache.php';
require_once __DIR__ . '/../core/database.php';

class TempSettingsModel {
    private static $possibleTables = ['system_settings', 'cms_settings', 'settings', 'site_settings'];
    
    public static function getSettings() {
        return self::getAll();
    }
    
    public static function getAll() {
        $cacheKey = 'system_settings';
        $cached = Cache::get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $pdo = \core\Database::connection();
        $settings = [];
        
        foreach (self::$possibleTables as $table) {
            try {
                $stmt = $pdo->query("SELECT * FROM " . $table . " LIMIT 1");
                $settings = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
                if (!empty($settings)) {
                    echo "Found settings in table: " . $table . "\n";
                    break;
                }
            } catch (PDOException $e) {
                continue;
            }
        }
        
        Cache::set($cacheKey, $settings, 3600);
        return $settings;
    }
}