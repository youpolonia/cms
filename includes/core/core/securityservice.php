<?php

namespace Core;

class SecurityService
{
    private array $securityHeaders = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Strict-Transport-Security' => 'max-age=63072000; includeSubDomains; preload'
    ];

    private ?string $cspPolicy = null;

    public function __construct(array $config = [])
    {
        if (isset($config['csp'])) {
            $this->setCspPolicy($config['csp']);
        }
    }

    public function setCspPolicy(string $policy): void
    {
        $this->cspPolicy = $policy;
        $this->securityHeaders['Content-Security-Policy'] = $policy;
    }

    public function getSecurityHeaders(): array
    {
        return $this->securityHeaders;
    }

    public function applySecurityHeaders(Response $response): Response
    {
        $newResponse = $response;
        foreach ($this->getSecurityHeaders() as $name => $value) {
            $newResponse = $newResponse->withHeader($name, $value);
        }
        return $newResponse;
    }

    public static function createDefault(): self
    {
        return new self([
            'csp' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;"
        ]);
    }
    private const CSRF_TOKEN_LIFETIME = 3600; // 1 hour
    private const CSRF_SESSION_KEY = 'csrf_tokens';

    public function generateCSRFToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new \RuntimeException('Session must be active to generate CSRF token');
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = time() + self::CSRF_TOKEN_LIFETIME;

        $_SESSION[self::CSRF_SESSION_KEY][$token] = [
            'expires' => $expiresAt,
            'used' => false
        ];

        return $token;
    }

    public function validateCSRFToken(string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        if (!isset($_SESSION[self::CSRF_SESSION_KEY][$token])) {
            return false;
        }

        $tokenData = $_SESSION[self::CSRF_SESSION_KEY][$token];

        // Check expiration
        if (time() > $tokenData['expires']) {
            unset($_SESSION[self::CSRF_SESSION_KEY][$token]);
            return false;
        }

        // Mark token as used
        $_SESSION[self::CSRF_SESSION_KEY][$token]['used'] = true;
        return true;
    }

    public function getCSRFToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new \RuntimeException('Session must be active to get CSRF token');
        }

        // Clean up expired tokens
        $this->cleanupExpiredTokens();

        // Generate new token if none exists
        if (empty($_SESSION[self::CSRF_SESSION_KEY])) {
            return $this->generateCSRFToken();
        }

        // Return first unused token
        foreach ($_SESSION[self::CSRF_SESSION_KEY] as $token => $data) {
            if (!$data['used']) {
                return $token;
            }
        }

        // All tokens used, generate new one
        return $this->generateCSRFToken();
    }

    private function cleanupExpiredTokens(): void
    {
        $now = time();
        foreach ($_SESSION[self::CSRF_SESSION_KEY] as $token => $data) {
            if ($now > $data['expires']) {
                unset($_SESSION[self::CSRF_SESSION_KEY][$token]);
            }
        }
    }
}
