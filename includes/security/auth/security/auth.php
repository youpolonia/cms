<?php
namespace Includes\Auth;

require_once __DIR__ . '/csrftoken.php';
require_once __DIR__ . '/validator.php';
require_once __DIR__ . '/../core/database.php';

class Auth {
    private $passwordAlgo = PASSWORD_ARGON2ID;
    private $passwordOptions = [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 2
    ];
    private $pepper = 'CMS_SECURE_PEPPER';

    public function __construct() {
        $config = require_once __DIR__ . '/../../config/security.php';
        $timeout = $config['session_timeout'] ?? 900;

        require_once __DIR__ . '/../../../../config.php';
        require_once __DIR__ . '/../../../../core/session_boot.php';
        cms_session_start('public');
        \Includes\Auth\CSRFToken::generate();
        
        // Initialize last activity time
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        }
    }

    public function hashPassword(string $password): string {
        $peppered = hash_hmac('sha256', $password, $this->pepper);
        return password_hash($peppered, $this->passwordAlgo, $this->passwordOptions);
    }

    private function verifySessionIp(): bool {
        $config = require_once __DIR__ . '/../../config/security.php';
        if (!$config['ip_binding']['enabled']) {
            return true;
        }

        if (!isset($_SESSION['login_ip'])) {
            return false;
        }

        $currentIp = $this->getClientIp();
        $strict = $config['ip_binding']['strict_mode'];

        if ($strict) {
            return $_SESSION['login_ip'] === $currentIp;
        }
        return $_SESSION['login_ip'] === $currentIp ||
               $_SERVER['HTTP_X_FORWARDED_FOR'] === $currentIp;
    }

    public function login($userId, bool $remember = false) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['last_activity'] = time();
        $_SESSION['login_ip'] = $this->getClientIp();
        
        // Clear any failed attempts for this user/IP
        $ip = $this->getClientIp();
        $this->clearAttempts($ip, $userId);
        
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $hashedToken = hash('sha256', $token);
            
            // Store hashed token in database
            $db = new \Includes\Database();
            $db->query(
                "INSERT INTO remember_tokens (user_id, token_hash, expires_at)
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))",
                [$userId, $hashedToken]
            );
            
            // Set secure cookie
            setcookie(
                'remember_token',
                $token,
                [
                    'expires' => time() + 60 * 60 * 24 * 30,
                    'path' => '/',
                    'domain' => $_SERVER['HTTP_HOST'],
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );
        }
    }

    public function isSessionValid(): bool {
        if (!isset($_SESSION['last_activity'])) {
            return false;
        }

        $config = require_once __DIR__ . '/../../config/security.php';
        $timeout = $config['session_timeout'] ?? 900;
        $expired = (time() - $_SESSION['last_activity']) > $timeout;
        
        return !$expired && $this->verifySessionIp();
    }

    public function logout(): bool {
        // Ensure logs directory exists
        $logDir = __DIR__ . '/../../logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Log the logout event
        $userId = $_SESSION['user_id'] ?? 'unknown';
        $logMessage = sprintf(
            "[%s] User %s logged out from IP %s\n",
            date('Y-m-d H:i:s'),
            $userId,
            $this->getClientIp()
        );
        file_put_contents($logDir . '/security.log', $logMessage, FILE_APPEND);

        // Clear remember token if exists
        if (isset($_SESSION['remember_token'])) {
            $this->invalidateRememberToken($_SESSION['remember_token']);
        }

        // Destroy session and clear cookies
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        return session_destroy();
    }

    public function renewSession(): void {
        $_SESSION['last_activity'] = time();
    }

    private function getClientIp(): string {
        return $_SERVER['HTTP_CLIENT_IP'] ??
               $_SERVER['HTTP_X_FORWARDED_FOR'] ??
               $_SERVER['REMOTE_ADDR'] ?? '';
    }

    private function isIpBanned(string $ip): bool {
        $db = new \Includes\Database();
        $result = $db->query(
            "SELECT COUNT(*) FROM login_attempts
             WHERE ip = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)",
            [$ip, $this->getBanDuration()]
        );
        return $result->fetchColumn() >= $this->getMaxAttempts();
    }

    private function recordFailedAttempt(string $ip, string $username): void {
        $db = new \Includes\Database();
        $db->query(
            "INSERT INTO login_attempts (ip, username, created_at)
             VALUES (?, ?, NOW())",
            [$ip, $username]
        );
    }

    private function clearAttempts(string $ip, string $username): void {
        $db = new \Includes\Database();
        $db->query(
            "DELETE FROM login_attempts WHERE ip = ? OR username = ?",
            [$ip, $username]
        );
    }

    private function getMaxAttempts(): int {
        $config = require_once __DIR__ . '/../../config/security.php';
        return $config['brute_force']['max_attempts'] ?? 5;
    }

    private function getTimeWindow(): int {
        $config = require_once __DIR__ . '/../../config/security.php';
        return $config['brute_force']['time_window'] ?? 600;
    }

    private function getBanDuration(): int {
        $config = require_once __DIR__ . '/../../config/security.php';
        return $config['brute_force']['ban_duration'] ?? 1800;
    }

    public function attempt($username, $password, $isAdmin = false, $csrfToken = null) {
        $ip = $this->getClientIp();
        
        // Check if IP is banned
        if ($this->isIpBanned($ip)) {
            error_log("Login blocked for banned IP: $ip");
            return false;
        }

        // Validate CSRF token if provided
        if ($csrfToken && !\Includes\Auth\CSRFToken::validate($csrfToken)) {
            throw new \Exception('Invalid CSRF token');
        }

        // Validate inputs
        $username = \Includes\Auth\Validator::sanitizeInput($username);
        if (!\Includes\Auth\Validator::validateUsername($username)) {
            throw new \Exception('Invalid username format');
        }

        if (!\Includes\Auth\Validator::validatePassword($password)) {
            throw new \Exception('Invalid password format');
        }

        // Get user from database
        $db = new \Includes\Database();
        $user = $db->query("SELECT * FROM users WHERE username = ?", [$username])->fetch();
        
        if (!$user) {
            throw new \Exception('User not found');
        }
        
        // Verify password against stored hash with pepper
        $peppered = hash_hmac('sha256', $password, $this->pepper);
        if (!password_verify($peppered, $user['password_hash'])) {
            throw new \Exception('Invalid credentials');
        }

        // Check if password needs rehashing
        if (password_needs_rehash($user['password_hash'], $this->passwordAlgo, $this->passwordOptions)) {
            $db->query(
                "UPDATE users SET password_hash = ? WHERE id = ?",
                [$this->hashPassword($password), $user['id']]
            );
        }

        return $user;
    }

    private function invalidateRememberToken(string $token): void {
        $hashedToken = hash('sha256', $token);
        $db = new \Includes\Database();
        $db->query(
            "DELETE FROM remember_tokens WHERE token_hash = ?",
            [$hashedToken]
        );
        setcookie('remember_token', '', time() - 3600, '/');
    }
}
