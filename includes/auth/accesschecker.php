<?php
// Minimal includes/auth access checker shim
if (!function_exists('auth_require_admin')) {
    function auth_require_admin(): void {
        if (empty($_SESSION['is_admin'])) { http_response_code(403); exit; }
    }
}
