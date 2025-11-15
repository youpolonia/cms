<?php
require_once __DIR__.'/loggerfactory.php';

class AuthGuard {
    private $pdo;
    private $fallbackCredentials;
    private $logger;

    public function __construct(PDO $pdo, array $fallbackCredentials = []) {
        $this->pdo = $pdo;
        $this->fallbackCredentials = $fallbackCredentials;
        $this->logger = LoggerFactory::create('auth');
    }

    public function authenticate(string $username, string $password, string $csrfToken): bool {
        // Verify CSRF token first
        if (empty($csrfToken) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            $this->logger->error("CSRF validation failed for $username");
            return false;
        }

        // Check database credentials
        $valid = false;
        try {
            $stmt = $this->pdo->prepare("SELECT password_hash FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $valid = true;
                $this->logger->info("Database authentication successful for $username");
            }
        } catch (PDOException $e) {
            $this->logger->error("Database error: " . $e->getMessage());
        }

        // Fallback to config file credentials if database auth failed
        if (!$valid && !empty($this->fallbackCredentials)) {
            $valid = $this->checkFallbackCredentials($username, $password);
            $this->logger->info("Fallback auth result for $username: " . ($valid ? 'success' : 'failed'));
        }

        return $valid;
    }

    private function checkFallbackCredentials(string $username, string $password): bool {
        return isset($this->fallbackCredentials[$username]) &&
               password_verify($password, $this->fallbackCredentials[$username]);
    }

    private function logDebug(string $message): void {
        $this->logger->debug($message);
    }
}
