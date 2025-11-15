<?php
namespace Core;

class AuthController {
    const IDLE_TIMEOUT = 1800; // 30 minutes in seconds

    /**
     * Generate session fingerprint
     * @return string SHA256 hash of user agent + IP
     */
    public static function generateFingerprint(): string {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        return hash('sha256', $userAgent . $ip);
    }

    /**
     * Validate current session fingerprint
     * @param string $storedFingerprint
     * @return bool True if valid, false otherwise
     */
    public static function validateFingerprint(string $storedFingerprint): bool {
        $currentFingerprint = self::generateFingerprint();
        return hash_equals($storedFingerprint, $currentFingerprint);
    }

    /**
     * Validate session including fingerprint and idle timeout
     * @param array $sessionData
     * @return bool True if valid session, false otherwise
     */
    public static function validateSession(array $sessionData): bool {
        if (empty($sessionData['fingerprint'])) {
            return false;
        }
        
        if (!self::validateFingerprint($sessionData['fingerprint'])) {
            return false;
        }

        // Check idle timeout
        if (isset($sessionData['last_activity']) &&
            (time() - $sessionData['last_activity'] > self::IDLE_TIMEOUT)) {
            return false;
        }

        return true;
    }

    /**
     * Update last activity timestamp in session
     * @param array &$sessionData Reference to session data array
     */
    public static function updateLastActivity(array &$sessionData): void {
        $sessionData['last_activity'] = time();
    }

    /**
     * Check if session is idle
     * @param array $sessionData
     * @return bool True if idle (timed out), false otherwise
     */
    public static function isSessionIdle(array $sessionData): bool {
        return isset($sessionData['last_activity']) &&
               (time() - $sessionData['last_activity'] > self::IDLE_TIMEOUT);
    }
}
