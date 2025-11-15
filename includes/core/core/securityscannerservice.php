<?php
declare(strict_types=1);

class SecurityScannerService {
    /**
     * Scans content for potential security issues
     */
    public static function scanContent(string $content): array {
        $issues = [];
        
        // Check for XSS vulnerabilities
        if (preg_match('/
<script[^>]*>.*?<\/script>/is',
 $content)) {
            $issues[] = 'Potential XSS vulnerability detected';
        }

        // Check for SQL injection patterns
        if (preg_match('/(union.*select|insert.+into|update.+set|delete.+from)/is', $content)) {
            $issues[] = 'Potential SQL injection pattern detected';
        }

        // Check for file inclusion patterns
        if (preg_match('/(\.\.\/|\.\.\\\)/', $content)) {
            $issues[] = 'Potential directory traversal attempt detected';
        }

        return $issues;
    }

    /**
     * Validates API request headers
     */
    public static function validateHeaders(array $headers): array {
        $issues = [];
        
        if (empty($headers['Content-Type'])) {
            $issues[] = 'Missing Content-Type header';
        }

        if (empty($headers['X-Request-ID'])) {
            $issues[] = 'Missing X-Request-ID header';
        }

        // Check for secure headers
        if (empty($headers['X-Content-Type-Options'])) {
            $issues[] = 'Missing X-Content-Type-Options header';
        }

        if (empty($headers['X-Frame-Options'])) {
            $issues[] = 'Missing X-Frame-Options header';
        }

        return $issues;
    }
}
