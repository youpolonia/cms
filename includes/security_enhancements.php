<?php
/**
 * Security Enhancements for CMS
 * Includes security headers and rate limiting functions
 */

/**
 * Apply security headers to response
 */
function applySecurityHeaders(): void {
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Content-Security-Policy: default-src 'self'");
}

/**
 * Check login rate limiting
 * @param string $ip Client IP address
 */
function checkLoginRateLimit(string $ip): void {
    $cacheDir = __DIR__ . '/../storage/cache/rate_limits/';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    $cacheFile = $cacheDir . md5($ip) . '.cache';
    $now = time();
    $window = 300; // 5 minute window
    $maxAttempts = 5;

    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if ($data['timestamp'] > $now - $window) {
            if ($data['attempts'] >= $maxAttempts) {
                header('HTTP/1.1 429 Too Many Requests');
                header('Retry-After: ' . ($window - ($now - $data['timestamp'])));
                exit;
            }
            $data['attempts']++;
        } else {
            $data = ['attempts' => 1, 'timestamp' => $now];
        }
    } else {
        $data = ['attempts' => 1, 'timestamp' => $now];
    }

    file_put_contents($cacheFile, json_encode($data));
}
