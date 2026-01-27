<?php
require_once __DIR__ . '/../../config.php';

require_once __DIR__ . '/../core/csrf.php';
// admin/core/session.php - Admin session management

csrf_boot('admin');

require_once __DIR__ . '/../../includes/session_config.php';
require_once __DIR__ . '/../includes/security.php'; // Admin-specific security first
require_once __DIR__ . '/../../includes/security.php'; // Then root security

class AdminSession {
    /**
     * Initialize admin session with security checks
     */
    public static function start(): void {
        // Start session first
        require_once __DIR__ . '/../../core/session_boot.php';
        cms_session_start('admin');

        // --- BEGIN: Safe, optional heartbeat loader ---
        $CMS_ROOT = dirname(__DIR__, 2);            // /cms
        $HB_CANDIDATE = $CMS_ROOT . '/config/heartbeat.php';

        if (is_file($HB_CANDIDATE)) {
            $config = require_once __DIR__ . '/../../config/heartbeat.php';
            if ($config['maintenance']['suppress_all'] ||
                ($config['maintenance']['suppress_until'] &&
                 time() < strtotime($config['maintenance']['suppress_until']))) {
                header('Location: /maintenance.php');
                exit;
            }
        } else {
            // Do not hard-fatal if heartbeat is absent; mark and continue
            if (!defined('ADMIN_HEARTBEAT_MISSING')) {
                define('ADMIN_HEARTBEAT_MISSING', true);
                define('ADMIN_HEARTBEAT_EXPECTED', $HB_CANDIDATE);
            }
            // continue without heartbeat
        }
        // --- END: Safe, optional heartbeat loader ---
        
        // Verify admin access and validate session
        verifyAdminAccess();
    }

    /**
     * Regenerate session ID for security
     */
    public static function regenerate(): void {
        session_regenerate_id(true);
        
        // Update session in database
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("
                UPDATE sessions 
                SET session_id = ?, expires_at = DATE_ADD(NOW(), INTERVAL 30 MINUTE)
                WHERE session_id = ?
            ");
            $stmt->execute([session_id(), $_SESSION['old_session_id']]);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    /**
     * Destroy session and clear all data
     */
    public static function destroy(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_validate_or_403(); }
        // Remove from database
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("DELETE FROM sessions WHERE session_id = ?");
            $stmt->execute([session_id()]);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }

        // Clear session data
        $_SESSION = [];
        session_destroy();
    }

    /**
     * Check if admin is logged in
     */
    public static function isLoggedIn(): bool {
        return !empty($_SESSION['admin_logged_in']);
    }

    /**
     * Get current CSRF token
     */
    public static function getCsrfToken(): string {
        return getCsrfToken();
    }
}

// Auto-start session for admin pages
if (php_sapi_name() !== 'cli') {
    AdminSession::start();
}
