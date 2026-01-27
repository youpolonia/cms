<?php
/**
 * Analytics Tracking Service
 * Framework-free implementation for Phase 11
 */
class TrackingService {
    private static $storagePath = __DIR__ . '/../storage/analytics/';
    
    public static function trackView(string $pageUrl, ?string $referrer = null): bool {
        $data = [
            'timestamp' => time(),
            'page_url' => $pageUrl,
            'referrer' => $referrer,
            'session_id' => self::getSessionId()
        ];
        
        return self::storeData($data);
    }
    
    private static function storeData(array $data): bool {
        $date = date('Y-m-d');
        $filename = self::$storagePath . $date . '.log';
        
        if (!file_exists(self::$storagePath)) {
            mkdir(self::$storagePath, 0755, true);
        }
        
        return file_put_contents($filename, json_encode($data) . PHP_EOL, FILE_APPEND) !== false;
    }
    
    private static function getSessionId(): string {
        if (empty($_COOKIE['analytics_sid'])) {
            $sid = bin2hex(random_bytes(16));
            setcookie('analytics_sid', $sid, time() + 86400 * 30, '/');
            return $sid;
        }
        return $_COOKIE['analytics_sid'];
    }
}
