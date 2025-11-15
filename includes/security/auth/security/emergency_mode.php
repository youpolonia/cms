<?php
/**
 * Emergency Mode System
 * Provides functions to enable/disable emergency lockdown mode
 */

require_once __DIR__ . '/securitylogger.php';

define('EMERGENCY_FLAG_PATH', __DIR__ . '/../../storage/emergency.flag');

/**
 * Check if emergency mode is active
 * @return bool True if emergency mode is active
 */
function isEmergencyModeActive(): bool {
    return file_exists(EMERGENCY_FLAG_PATH);
}

/**
 * Enable emergency mode
 * @return bool True on success
 */
function enableEmergencyMode(): bool {
    if (!is_writable(dirname(EMERGENCY_FLAG_PATH))) {
        SecurityLogger::log(
            'EMERGENCY_MODE_ERROR',
            'Cannot write to storage directory',
            ['path' => dirname(EMERGENCY_FLAG_PATH)]
        );
        return false;
    }
    
    $result = file_put_contents(EMERGENCY_FLAG_PATH, time());
    return $result !== false;
}

/**
 * Disable emergency mode
 * @return bool True on success
 */
function disableEmergencyMode(): bool {
    if (file_exists(EMERGENCY_FLAG_PATH)) {
        return unlink(EMERGENCY_FLAG_PATH);
    }
    return true;
}

/**
 * Get emergency mode activation time
 * @return int|null Unix timestamp or null if not active
 */
function getEmergencyModeActivationTime(): ?int {
    if (!isEmergencyModeActive()) {
        return null;
    }
    return (int)file_get_contents(EMERGENCY_FLAG_PATH);
}
