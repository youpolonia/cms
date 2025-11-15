<?php
require_once __DIR__ . '/../../core/database.php';

/**
 * Auth Middleware
 * 
 * Handles authorization checks and security protections
 */
class AuthMiddleware {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function __invoke(array $request, callable $next): array {
        // CSRF protection for session endpoints
        if ($this->isSessionEndpoint($request)) {
            if (!$this->validateCsrfToken($request)) {
                return [
                    'status' => 403,
                    'body' => ['error' => 'Invalid CSRF token']
                ];
            }
        }

        // Check account lockout status
        if ($this->isAccountLocked($request['user_id'] ?? null)) {
            return [
                'status' => 403,
                'body' => ['error' => 'Account temporarily locked']
            ];
        }

        return $next($request);
    }

    private function isSessionEndpoint(array $request): bool {
        $path = $request['path'] ?? '';
        return strpos($path, '/session/') === 0;
    }

    private function validateCsrfToken(array $request): bool {
        $token = $request['headers']['X-CSRF-Token'] ?? '';
        $cache = new \Core\Cache\SessionCacheAdapter(
            \Core\Cache\CacheFactory::make(),
            session_id()
        );
        $sessionToken = $cache->get(session_id(), 'csrf_token') ?? '';
        return hash_equals($sessionToken, $token);
    }

    private function isAccountLocked(?int $userId): bool {
        if (!$userId) return false;
        
        $stmt = $this->pdo->prepare("
            SELECT lockout_until 
            FROM user_security 
            WHERE user_id = ? AND lockout_until > NOW()
        ");
        $stmt->execute([$userId]);
        return (bool)$stmt->fetchColumn();
    }

    public static function recordFailedAttempt(int $userId): void {
        $pdo = \core\Database::connection();
        $pdo->prepare("
            INSERT INTO user_security (user_id, failed_attempts, last_attempt)
            VALUES (?, 1, NOW())
            ON DUPLICATE KEY UPDATE
                failed_attempts = IF(lockout_until IS NULL OR lockout_until < NOW(), 
                    failed_attempts + 1, 
                    failed_attempts),
                last_attempt = NOW(),
                lockout_until = IF(failed_attempts >= ?, 
                    DATE_ADD(NOW(), INTERVAL ? SECOND), 
                    lockout_until)
        ")->execute([
            $userId,
            $GLOBALS['config']['auth']['lockout']['max_attempts'],
            $GLOBALS['config']['auth']['lockout']['lockout_time']
        ]);
    }
}
