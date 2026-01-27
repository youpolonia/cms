<?php
// core/csrf.php — minimal CSRF utilities for admin forms
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__)); }

if (!function_exists('csrf_boot')) {
    function csrf_boot(string $area = 'admin'): string {
        require_once CMS_ROOT . '/core/session_boot.php';
        cms_session_start($area);
        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['csrf_token'];
    }
}
if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return isset($_SESSION['csrf_token']) && is_string($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
    }
}
if (!function_exists('csrf_field')) {
    function csrf_field(): void {
        $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
        echo '<input type="hidden" name="csrf_token" value="' . $t . '">';
    }
}
if (!function_exists('csrf_validate_or_403')) {
    function csrf_validate_or_403(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $sent   = $_POST['csrf_token'] ?? '';
        $good   = csrf_token();
        if ($method !== 'POST' || !$good || !is_string($sent) || !hash_equals($good, $sent)) {
            http_response_code(403);
            // Return JSON for AJAX requests
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            $wantsJson = isset($_SERVER['HTTP_ACCEPT']) && 
                         strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
            if ($isAjax || $wantsJson) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'error' => 'CSRF verification failed']);
            } else {
                header('Content-Type: text/plain; charset=UTF-8');
                echo 'CSRF verification failed';
            }
            exit;
        }
    }
}
if (!function_exists('csrf_validate')) {
    function csrf_validate(?string $token): bool {
        $good = csrf_token();
        if (!$good || !is_string($token) || $token === '') {
            return false;
        }
        return hash_equals($good, $token);
    }
}
// (no closing PHP tag)
