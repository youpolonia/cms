<?php

class SecurityHeadersMiddleware {
    private $next;
    private $headers = [
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: blob:; connect-src 'self';",
        'X-XSS-Protection' => '1; mode=block',
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => "geolocation=(), microphone=(), camera=()"
    ];

    public function __construct(callable $next) {
        $this->next = $next;
    }

    public function __invoke(array $request) {
        $this->setSecurityHeaders();
        $this->validateCsrfToken($request);

        return ($this->next)($request);
    }

    private function setSecurityHeaders(): void {
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
    }

    private function validateCsrfToken(array $request): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $request['headers']['X-CSRF-Token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid CSRF token'
                ]);
                exit;
            }
        }
    }
}
