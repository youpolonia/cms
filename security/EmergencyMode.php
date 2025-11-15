<?php
/**
 * Emergency Mode Handler
 * 
 * Implements system-wide emergency lockout and maintenance capabilities
 * 
 * Features:
 * - System-wide lockout capability
 * - Maintenance mode handling
 * - Admin notification system
 * - Status checking
 * 
 * @package Security
 * @version 1.0.0
 */
class EmergencyMode {
    private static $lockFile = __DIR__ . '/../storage/emergency.lock';
    private static $configFile = __DIR__ . '/../config/emergency.php';
    
    /**
     * Activate emergency mode
     * 
     * @param string $reason Reason for activation
     * @param string $adminMessage Optional message for admin dashboard
     * @return bool True if activated successfully
     */
    public static function activate(string $reason, string $adminMessage = ''): bool {
        $config = [
            'activated_at' => time(),
            'reason' => $reason,
            'admin_message' => $adminMessage,
            'activated_by' => self::getCurrentUser()
        ];
        
        // Create lock file
        if (file_put_contents(self::$lockFile, '') === false) {
            return false;
        }
        
        // Save config
        return file_put_contents(
            self::$configFile,
            "<?php return " . var_export($config, true) . ";"
        ) !== false;
    }
    
    /**
     * Deactivate emergency mode
     * 
     * @return bool True if deactivated successfully
     */
    public static function deactivate(): bool {
        $success = true;
        
        if (file_exists(self::$lockFile)) {
            $success = unlink(self::$lockFile);
        }
        
        if (file_exists(self::$configFile)) {
            $success = $success && unlink(self::$configFile);
        }
        
        return $success;
    }
    
    /**
     * Check if emergency mode is active
     * 
     * @return bool True if emergency mode is active
     */
    public static function isActive(): bool {
        return file_exists(self::$lockFile);
    }
    
    /**
     * Get emergency mode configuration
     * 
     * @return array|null Configuration array or null if not active
     */
    public static function getConfig(): ?array {
        if (!self::isActive()) {
            return null;
        }

        $path = realpath(self::$configFile);
        $base = realpath(dirname(__DIR__) . '/../config');
        if ($path !== false && $base !== false && str_starts_with($path, $base . DIRECTORY_SEPARATOR) && is_file($path)) {
            return require_once $path;
        }

        return null;
    }
    
    private static function getCurrentUser(): string {
        return $_SERVER['REMOTE_ADDR'] ?? 'system';
    }
}
