<?php
// DEV gate for admin security tools page
if (!defined('DEV_MODE')) { require_once __DIR__ . '/../config.php'; }
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Admin Security Module
 * Handles session security and admin access verification
 */

declare(strict_types=1);

// session boot (admin)
require_once __DIR__ . '/../core/session_boot.php';

if (!defined('ADMIN_SECURITY_INIT')) {
    define('ADMIN_SECURITY_INIT', true);
    
    // Secure session settings
    session_set_cookie_params([
        'lifetime' => 3600,
        'path' => '/admin',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
    session_name('ADMIN_SECURE_SESSION');
}

/**
 * Initialize secure admin session
 */
function initAdminSession(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        cms_session_start('admin');
    }
    
    // Regenerate ID to prevent session fixation
    if (empty($_SESSION['admin_initiated'])) {
        session_regenerate_id(true);
        $_SESSION['admin_initiated'] = true;
    }
}

/**
 * Verify admin access privileges
 * @return bool True if valid admin session, false otherwise
 */
function verifyAdminAccess(): bool {
    initAdminSession();
    
    // Check session validity
    if (empty($_SESSION['admin_id']) || 
        empty($_SESSION['admin_last_activity']) ||
        $_SESSION['ip_address'] !== ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'])) {
        return false;
    }
    
    // Check session timeout (30 minutes)
    if (time() - $_SESSION['admin_last_activity'] > 1800) {
        session_unset();
        session_destroy();
        return false;
    }
    
    // Update last activity time
    $_SESSION['admin_last_activity'] = time();
    
    return true;
}

/**
 * Generate CSRF token for admin forms
 * @return string Generated token
 */
function generateAdminCsrfToken(): string {
    initAdminSession();
    
    if (empty($_SESSION['admin_csrf_token'])) {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['admin_csrf_token'];
}

/**
 * Verify CSRF token for admin forms
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyAdminCsrfToken(string $token): bool {
    initAdminSession();

    return isset($_SESSION['admin_csrf_token']) &&
           hash_equals($_SESSION['admin_csrf_token'], $token);
}

// If accessed directly, show a simple page
if (basename($_SERVER['PHP_SELF']) === 'security.php') {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../core/csrf.php';
    if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
    csrf_boot();
    require_once __DIR__ . '/includes/admin_layout.php';
    admin_render_page_start('Security');
    echo '<p>This module provides admin security functions.</p>';
    echo '<p>Available functions:</p>';
    echo '<ul>';
    echo '<li><code>initAdminSession(): void</code> - Initialize secure admin session</li>';
    echo '<li><code>verifyAdminAccess(): bool</code> - Verify admin access privileges</li>';
    echo '<li><code>generateAdminCsrfToken(): string</code> - Generate CSRF token</li>';
    echo '<li><code>verifyAdminCsrfToken(string $token): bool</code> - Verify CSRF token</li>';
    echo '</ul>';
    admin_render_page_end();
}
