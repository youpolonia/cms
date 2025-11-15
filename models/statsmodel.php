<?php
require_once __DIR__ . '/../core/database.php';

require_once __DIR__ . '/../core/cache.php';

class StatsModel {
    private static $cacheKey = 'dashboard_stats';
    
    public static function getStats() {
        $cached = Cache::get(self::$cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }
        
        // Generate fresh stats
        $stats = [
            'total_content' => self::getContentCount(),
            'active_users' => self::getActiveUserCount(),
            'storage_usage' => self::getStorageUsage(),
            'last_updated' => time()
        ];
        
        Cache::set(self::$cacheKey, $stats, 60); // Cache for 60 seconds
        return $stats;
    }
    
    private static function getContentCount() {
        $pdo = \core\Database::connection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM content");
        return (int)$stmt->fetchColumn();
    }
    
    private static function getActiveUserCount() {
        require_once __DIR__ . '/../core/database.php';
        $pdo = \core\Database::connection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE last_active > DATE_SUB(NOW(), INTERVAL 30 DAY)");
        return (int)$stmt->fetchColumn();
    }
    
    private static function getStorageUsage() {
        $path = __DIR__ . '/../../uploads';
        $size = 0;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
            $size += $file->getSize();
        }
        return round($size / (1024 * 1024), 2); // MB
    }
}
