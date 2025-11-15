<?php
/**
 * Emergency Mode Controller
 * Implements system lockdown functionality
 */
class EmergencyModeController {
    private static $isActive = false;
    private static $activationTime = null;
    
    /**
     * Activate emergency mode
     */
    public static function activate(): void {
        if (self::$isActive) {
            return;
        }

        self::$isActive = true;
        self::$activationTime = time();
        
        // Terminate all active sessions
        SessionManager::terminateAllSessions();
        
        // Switch to read-only mode
        SystemConfig::setReadOnly(true);
        
        // Log emergency activation
        SecurityLogger::logEmergencyEvent('EMERGENCY_MODE_ACTIVATED');
        
        // Notify admins
        NotificationService::sendAdminAlert(
            'Emergency mode activated',
            'System has entered emergency lockdown state'
        );
    }
    
    /**
     * Check if emergency mode is active
     */
    public static function isActive(): bool {
        return self::$isActive;
    }
    
    /**
     * Get activation time
     */
    public static function getActivationTime(): ?int {
        return self::$activationTime;
    }
    
    /**
     * Emergency request handler
     */
    public static function handleRequest(): void {
        if (self::isActive()) {
            http_response_code(503);
            header('Retry-After: 3600');
            require_once __DIR__ . '/../views/emergency.php';
            exit;
        }
    }
}
