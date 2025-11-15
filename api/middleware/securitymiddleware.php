<?php
namespace Api\Middleware;

class SecurityMiddleware
{
    public function handle()
    {
        // CSRF protection for non-GET requests
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
            if (!\Core\Security\CSRFToken::validate($csrfToken)) {
                header('HTTP/1.1 403 Forbidden');
                exit;
            }
        }

        // XSS protection - sanitize all input
        array_walk_recursive($_GET, [$this, 'sanitizeInput']);
        array_walk_recursive($_POST, [$this, 'sanitizeInput']);
        array_walk_recursive($_COOKIE, [$this, 'sanitizeInput']);

        // Security headers
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }

    protected function sanitizeInput(&$value)
    {
        if (is_string($value)) {
            $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
}
