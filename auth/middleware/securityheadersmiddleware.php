<?php
declare(strict_types=1);

class SecurityHeadersMiddleware {
    private array $headers = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Strict-Transport-Security' => 'max-age=63072000; includeSubDomains; preload',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; form-action 'self'"
    ];

    public function __invoke(): void {
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
    }

    public function updateHeader(string $name, string $value): void {
        $this->headers[$name] = $value;
    }
}
