<?php
require_once __DIR__.'/loggerfactory.php';

class AdminAuthGuard {
    private $pdo;
    private $logger;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->logger = LoggerFactory::create('auth');
    }

    public function authenticate(string $username, string $password, string $csrfToken): bool {
        // Verify CSRF token first
        if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            $this->logger->warning('Invalid CSRF token');
            return false;
        }

        // Get user from database
        $stmt = $this->pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $this->logger->warning("Login attempt for non-existent user: $username");
            return false;
        }

        $authResult = $this->checkCredentials($password, $user['password']);
        if (!$authResult) {
            $this->logger->warning("Failed login attempt for user: $username");
        }

        return $authResult;
    }

    public function checkCredentials(string $password, string $hashedPassword): bool {
        return password_verify($password, $hashedPassword);
    }

    // Kept for backward compatibility
    public static function logDebug(string $message): void {
        $logger = LoggerFactory::create('auth');
        $logger->debug($message);
    }
}
