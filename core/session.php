<?php
/**
 * Secure Session Handler
 * 
 * Features:
 * - Secure session initialization
 * - HttpOnly flag
 * - SameSite=Strict policy
 * - Secure flag when HTTPS detected
 * - Proper session path settings
 * - Session regeneration
 */

class SecureSession {
    /**
     * Initialize secure session settings
     */
    public static function start(): void {
        // Set session cookie parameters
        $cookieParams = session_get_cookie_params();
        
        session_set_cookie_params([
            'lifetime' => $cookieParams['lifetime'],
            'path' => '/',
            'domain' => $cookieParams['domain'],
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        // Set session save path
        $sessionPath = __DIR__ . '/../storage/sessions';
        if (!is_dir($sessionPath)) {
            mkdir($sessionPath, 0700, true);
        }
        session_save_path($sessionPath);

        // Start session with strict mode
        ini_set('session.use_strict_mode', 1);
        session_start();

        // Regenerate session ID if needed
        if (!isset($_SESSION['__initiated'])) {
            session_regenerate_id(true);
            $_SESSION['__initiated'] = true;
        }
    }

    /**
     * Check if HTTPS is being used
     */
    private static function isHttps(): bool {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    /**
     * Regenerate session ID
     */
    public static function regenerate(): void {
        session_regenerate_id(true);
    }

    /**
     * Destroy session
     */
    public static function destroy(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }
}
