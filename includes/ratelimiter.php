<?php
/**
 * Rate Limiter for login attempts and API requests
 */
class RateLimiter {
    const LOGIN_ATTEMPTS_KEY = 'login_attempts';
    const MAX_ATTEMPTS = 5;
    const LOCKOUT_TIME = 300; // 5 minutes in seconds

    /**
     * Check if login attempts exceed limit
     */
    public static function isLoginAllowed(string $ip): bool {
        if (!isset($_SESSION[self::LOGIN_ATTEMPTS_KEY][$ip])) {
            return true;
        }

        $attempts = $_SESSION[self::LOGIN_ATTEMPTS_KEY][$ip];
        return $attempts['count'] < self::MAX_ATTEMPTS || 
               (time() - $attempts['last_attempt']) > self::LOCKOUT_TIME;
    }

    /**
     * Record a failed login attempt
     */
    public static function recordFailedAttempt(string $ip): void {
        if (!isset($_SESSION[self::LOGIN_ATTEMPTS_KEY][$ip])) {
            $_SESSION[self::LOGIN_ATTEMPTS_KEY][$ip] = [
                'count' => 0,
                'last_attempt' => time()
            ];
        }

        $_SESSION[self::LOGIN_ATTEMPTS_KEY][$ip]['count']++;
        $_SESSION[self::LOGIN_ATTEMPTS_KEY][$ip]['last_attempt'] = time();
    }

    /**
     * Reset attempts for IP
     */
    public static function resetAttempts(string $ip): void {
        unset($_SESSION[self::LOGIN_ATTEMPTS_KEY][$ip]);
    }

    /**
     * Get remaining attempts
     */
    public static function getRemainingAttempts(string $ip): int {
        if (!isset($_SESSION[self::LOGIN_ATTEMPTS_KEY][$ip])) {
            return self::MAX_ATTEMPTS;
        }
        return max(0, self::MAX_ATTEMPTS - $_SESSION[self::LOGIN_ATTEMPTS_KEY][$ip]['count']);
    }
}
