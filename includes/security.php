<?php
declare(strict_types=1);

/**
 * Security utilities for the CMS admin interface
 * Includes session validation, CSRF protection, and permission checks
 */

// Session configuration constants
define('SESSION_TIMEOUT', 3600); // 1 hour session timeout
define('CSRF_TOKEN_LENGTH', 32);

/**
 * Verify CSRF token from POST request
 * @throws RuntimeException if token is invalid
 */
/**
 * Generate and store a new CSRF token
 * @return string The generated token
 */
function generate_csrf_token(): string {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH / 2));
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(): void {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new RuntimeException('Invalid CSRF token');
    }
}

/**
 * Check if user has specific permission
 * @param string $permission Permission key to check
 * @throws RuntimeException if permission is missing
 */
function check_permission(string $permission): void {
    if (!has_permission($permission)) {
        throw new RuntimeException('Insufficient permissions');
    }
}

/**
 * Check permission existence
 * @param string $permission Permission key to verify
 * @return bool True if permission exists
 */
function has_permission(string $permission): bool {
    return in_array($permission, $_SESSION['permissions'] ?? [], true);
}

require_once __DIR__ . '/../core/loggerfactory.php';

/**
 * Log security-related actions
 * @param string $action Action description
 * @param array $data Additional context data
 */
function audit_log(string $action, array $data = []): void {
    global $db;
    
    $userId = $_SESSION['user_id'] ?? 0;
    $ip = $_SERVER['REMOTE_ADDR'];
    $dataJson = json_encode($data);
    
    // Maintain database logging
    $db->query("INSERT INTO audit_log
               (user_id, action, ip_address, data)
               VALUES ($userId, '$action', '$ip', '$dataJson')");
               
    // Add PSR-3 compliant logging
    LoggerFactory::create()->warning("Audit: {$action}", [
        'user_id' => $userId,
        'ip' => $ip,
        'data' => $data
    ]);
}

/**
 * Sanitize user input
 * @param string $input Raw user input
 * @return string Sanitized output
 */
function sanitize_input(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate admin session and access rights
 * Redirects to login with session_expired parameter if invalid
 */
function verifyAdminAccess(): void {
    // Check if ADMIN_PROTECTED is defined and true
    if (defined('ADMIN_PROTECTED') && ADMIN_PROTECTED === true) {
        // Allow bypass if in DEV_MODE and page explicitly allows it
        if (defined('DEV_MODE') && DEV_MODE === true &&
            defined('ADMIN_DEV_BYPASS') && ADMIN_DEV_BYPASS === true) {
            return;
        }
        
        // Otherwise enforce normal access checks
        if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], '/admin/') === false) {
            header('Location: /admin/login.php?error=direct_access');
            exit;
        }
    }

    // First check if session exists at all
    if (empty($_SESSION)) {
        $logPath = __DIR__.'/../../logs/auth_debug.log';
        if (is_dir(dirname($logPath)) && is_writable(dirname($logPath))) {
            file_put_contents($logPath,
                date('Y-m-d H:i:s')." - verifyAdminAccess: Missing session, redirecting to login\n".
                "Cookies: ".print_r($_COOKIE, true)."\n\n",
                FILE_APPEND);
        }
        
        if (!headers_sent()) {
            header('Location: /admin/login.php?error=session_expired');
            exit;
        }
        return;
    }

    // Simple session validation fallback
    $simpleValid = !empty($_SESSION['admin_logged_in']) &&
                  ($_SESSION['last_activity'] ?? 0) > (time() - SESSION_TIMEOUT);
    
    // Advanced validation if available
    $advancedValid = false;
    if (class_exists('\Core\AuthController') && method_exists('\Core\AuthController', 'validateSession')) {
        $advancedValid = \Core\AuthController::validateSession($_SESSION);
    }
    
    if (!$simpleValid && !$advancedValid) {
        $logPath = __DIR__.'/../../logs/auth_debug.log';
        if (is_dir(dirname($logPath)) && is_writable(dirname($logPath))) {
            file_put_contents($logPath,
                date('Y-m-d H:i:s')." - verifyAdminAccess redirecting to login\n".
                "Session: ".print_r($_SESSION, true)."\n".
                "Cookies: ".print_r($_COOKIE, true)."\n".
                "Validation: Simple=".($simpleValid?'Y':'N')." Advanced=".($advancedValid?'Y':'N')."\n\n",
                FILE_APPEND);
        }
        
        if (!headers_sent()) {
            header('Location: /admin/login.php?error=session_expired');
            exit;
        }
    }
    
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
}
