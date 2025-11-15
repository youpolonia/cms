<?php
/**
 * Enhanced GDPR-compliant rate limiter
 */
class RateLimiter {
    private static $storagePath = __DIR__ . '/../storage/rate_limits/';
    private static $logPath = __DIR__ . '/../storage/logs/rate_limit_logs/';
    private static $defaultLimit = 60; // Requests per hour (reduced from 100)
    private static $defaultWindow = 3600; // Seconds
    private static $ipLimit = 30; // Per-IP limit
    private static $burstProtection = 10; // Max requests in 1 minute

    public static function check($key, $limit = null, $window = null) {
        $limit = $limit ?? self::$defaultLimit;
        $window = $window ?? self::$defaultWindow;
        
        if (!file_exists(self::$storagePath)) {
            mkdir(self::$storagePath, 0755, true);
        }
        if (!file_exists(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $now = time();
        
        // Check IP-based limits first
        if (!self::checkIpLimit($ip)) {
            self::logRateLimit('ip_limit_exceeded', $ip, $key);
            return false;
        }

        // Check burst protection
        if (!self::checkBurstProtection($ip, $key)) {
            self::logRateLimit('burst_limit_exceeded', $ip, $key);
            return false;
        }

        $file = self::$storagePath . md5($key) . '.json';
        
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            // Sliding window implementation
            $elapsed = $now - $data['timestamp'];
            $remainingWindow = $window - $elapsed;
            $count = $data['count'] * ($remainingWindow / $window);
            
            if ($count >= $limit) {
                self::logRateLimit('global_limit_exceeded', $ip, $key);
                return false;
            }
            $data['count']++;
            $data['timestamp'] = $now;
        } else {
            $data = ['count' => 1, 'timestamp' => $now];
        }

        file_put_contents($file, json_encode($data));
        return true;
    }

    private static function checkIpLimit($ip) {
        $ipFile = self::$storagePath . 'ip_' . md5($ip) . '.json';
        $now = time();
        
        if (file_exists($ipFile)) {
            $data = json_decode(file_get_contents($ipFile), true);
            if ($data['timestamp'] + self::$defaultWindow > $now) {
                if ($data['count'] >= self::$ipLimit) {
                    return false;
                }
                $data['count']++;
            } else {
                $data = ['count' => 1, 'timestamp' => $now];
            }
        } else {
            $data = ['count' => 1, 'timestamp' => $now];
        }

        file_put_contents($ipFile, json_encode($data));
        return true;
    }

    private static function checkBurstProtection($ip, $key) {
        $burstFile = self::$storagePath . 'burst_' . md5($ip . $key) . '.json';
        $now = time();
        $burstWindow = 60; // 1 minute
        
        if (file_exists($burstFile)) {
            $data = json_decode(file_get_contents($burstFile), true);
            if ($data['timestamp'] + $burstWindow > $now) {
                if ($data['count'] >= self::$burstProtection) {
                    return false;
                }
                $data['count']++;
            } else {
                $data = ['count' => 1, 'timestamp' => $now];
            }
        } else {
            $data = ['count' => 1, 'timestamp' => $now];
        }

        file_put_contents($burstFile, json_encode($data));
        return true;
    }

    private static function logRateLimit($type, $ip, $key) {
        $logFile = self::$logPath . date('Y-m-d') . '.log';
        $logEntry = sprintf(
            "[%s] %s: ip=%s key=%s\n",
            date('c'),
            $type,
            $ip,
            $key
        );
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    public static function getRemaining($key) {
        $file = self::$storagePath . md5($key) . '.json';
        if (!file_exists($file)) {
            return self::$defaultLimit;
        }
        
        $data = json_decode(file_get_contents($file), true);
        $now = time();
        $elapsed = $now - $data['timestamp'];
        $remainingWindow = self::$defaultWindow - $elapsed;
        $count = $data['count'] * ($remainingWindow / self::$defaultWindow);
        
        return max(0, floor(self::$defaultLimit - $count));
    }
}
