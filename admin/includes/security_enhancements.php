<?php
/**
 * Security Enhancements
 */

require_once __DIR__ . '/../../core/database.php';

// Rate limiting for failed login attempts
function checkLoginRateLimit(string $ip): void {
    $db = \core\Database::connection();
    $stmt = $db->prepare("
        SELECT COUNT(*) as attempts
        FROM login_attempts
        WHERE ip = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $stmt->execute([$ip]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['attempts'] > 5) {
        http_response_code(429);
        die('Too many login attempts. Please try again later.');
    }
}

// Password policy validation
function validatePasswordPolicy(string $password): bool {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/', $password);
}

// Security headers middleware
function applySecurityHeaders(): void {
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: blob:; connect-src 'self';");
}
