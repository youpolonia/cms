<?php
class RateLimiter {
    private static $limits = [
        'user_update' => [
            'limit' => 5,
            'window' => 60 // seconds
        ]
    ];

    public static function check(string $action): bool {
        if (!isset(self::$limits[$action])) {
            return true;
        }

        $key = "rate_limit_{$action}_" . ($_SESSION['user_id'] ?? session_id());
        $now = time();

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 1,
                'start' => $now
            ];
            return true;
        }

        $data = $_SESSION[$key];
        $limit = self::$limits[$action];

        if ($now - $data['start'] > $limit['window']) {
            $_SESSION[$key] = [
                'count' => 1,
                'start' => $now
            ];
            return true;
        }

        if ($data['count'] >= $limit['limit']) {
            return false;
        }

        $_SESSION[$key]['count']++;
        return true;
    }
}
