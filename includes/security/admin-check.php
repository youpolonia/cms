<?php
/**
 * Admin Access Check
 * Verifies admin session and permissions
 */
declare(strict_types=1);

if (!defined('CMS_ADMIN')) {
    die('Direct access not permitted');
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/session_boot.php';

class AdminCheck {
    public static function verify(): void {
        cms_session_start('admin');
        
        if (empty($_SESSION['admin_logged_in'])) {
            header('Location: /admin/login?redirect='.urlencode($_SERVER['REQUEST_URI']));
            exit;
        }

        // Check CSRF token for POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'], $token)) {
                http_response_code(403);
                die('Invalid CSRF token');
            }
        }

        // Check last activity timeout (30 minutes)
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > 1800)) {
            session_unset();
            session_destroy();
            header('Location: /admin/login?timeout=1');
            exit;
        }

        $_SESSION['last_activity'] = time();
    }
}
