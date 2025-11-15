<?php
/**
 * Security headers manager
 */
class SecurityHeaders {
    private const DEFAULT_HEADERS = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Strict-Transport-Security' => 'max-age=63072000; includeSubDomains; preload',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; media-src 'self'; object-src 'none'; frame-src 'none'"
    ];

    public static function apply(): void {
        foreach (self::DEFAULT_HEADERS as $header => $value) {
            header("$header: $value");
        }
        EmergencyLogger::log('Security headers applied', $_SERVER['REMOTE_ADDR'] ?? '');
    }

    public static function getHeaders(): array {
        return self::DEFAULT_HEADERS;
    }
}
