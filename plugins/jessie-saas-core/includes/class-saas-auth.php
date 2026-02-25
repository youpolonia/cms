<?php
namespace Plugins\JessieSaasCore;

/**
 * SaaS Authentication — register, login, sessions, password reset, API keys
 */
class SaasAuth {
    private \PDO $pdo;
    
    public function __construct() {
        if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
        require_once CMS_ROOT . '/db.php';
        $this->pdo = \core\Database::connection();
    }
    
    // ── Registration ──
    public function register(string $email, string $password, string $name = '', string $company = ''): array {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Invalid email address'];
        }
        if (strlen($password) < 8) {
            return ['success' => false, 'error' => 'Password must be at least 8 characters'];
        }
        // Check duplicate
        $stmt = $this->pdo->prepare("SELECT id FROM saas_users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Email already registered'];
        }
        
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $apiKey = bin2hex(random_bytes(32));
        $apiSecret = bin2hex(random_bytes(64));
        $verifyToken = bin2hex(random_bytes(32));
        
        $stmt = $this->pdo->prepare(
            "INSERT INTO saas_users (email, password_hash, name, company, api_key, api_secret, verification_token, credits_remaining, credits_monthly)
             VALUES (?, ?, ?, ?, ?, ?, ?, 100, 100)"
        );
        $stmt->execute([$email, $hash, trim($name), trim($company), $apiKey, $apiSecret, $verifyToken]);
        $userId = (int)$this->pdo->lastInsertId();
        
        // Create free subscription for all services
        $this->createFreeSubscriptions($userId);
        
        return [
            'success' => true,
            'user_id' => $userId,
            'api_key' => $apiKey,
            'verification_token' => $verifyToken
        ];
    }
    
    // ── Login ──
    public function login(string $email, string $password): array {
        $email = strtolower(trim($email));
        $stmt = $this->pdo->prepare("SELECT * FROM saas_users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Invalid email or password'];
        }
        
        // Update last login
        $this->pdo->prepare("UPDATE saas_users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
        
        // Start session
        $this->startSession($user);
        
        unset($user['password_hash'], $user['api_secret'], $user['verification_token'], $user['reset_token'], $user['reset_expires']);
        return ['success' => true, 'user' => $user];
    }
    
    // ── Session Management ──
    public function startSession(array $user): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_name('SAAS_SESSID');
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION['saas_user_id'] = (int)$user['id'];
        $_SESSION['saas_email'] = $user['email'];
        $_SESSION['saas_name'] = $user['name'] ?? '';
        $_SESSION['saas_plan'] = $user['plan'] ?? 'free';
        $_SESSION['saas_login_time'] = time();
    }
    
    public static function isLoggedIn(): bool {
        return isset($_SESSION['saas_user_id']);
    }
    
    public static function getUserId(): ?int {
        return $_SESSION['saas_user_id'] ?? null;
    }
    
    public static function requireAuth(): void {
        if (!self::isLoggedIn()) {
            if (self::isApiRequest()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                exit;
            }
            header('Location: /saas/login', true, 303);
            exit;
        }
    }
    
    private static function isApiRequest(): bool {
        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xhr = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_contains($ct, 'application/json') || str_contains($accept, 'application/json') || strtolower($xhr) === 'xmlhttprequest';
    }
    
    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
    
    // ── API Key Auth ──
    public function authenticateApiKey(string $apiKey): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM saas_users WHERE api_key = ? AND status = 'active'");
        $stmt->execute([$apiKey]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$user) return null;
        unset($user['password_hash'], $user['api_secret'], $user['verification_token'], $user['reset_token']);
        return $user;
    }
    
    public function regenerateApiKey(int $userId): ?string {
        $newKey = bin2hex(random_bytes(32));
        $stmt = $this->pdo->prepare("UPDATE saas_users SET api_key = ? WHERE id = ?");
        $stmt->execute([$newKey, $userId]);
        return $newKey;
    }
    
    // ── Password Reset ──
    public function requestPasswordReset(string $email): array {
        $email = strtolower(trim($email));
        $stmt = $this->pdo->prepare("SELECT id FROM saas_users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$user) {
            return ['success' => true]; // Don't reveal if email exists
        }
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $this->pdo->prepare("UPDATE saas_users SET reset_token = ?, reset_expires = ? WHERE id = ?")->execute([$token, $expires, $user['id']]);
        return ['success' => true, 'token' => $token, 'user_id' => $user['id']];
    }
    
    public function resetPassword(string $token, string $newPassword): array {
        if (strlen($newPassword) < 8) {
            return ['success' => false, 'error' => 'Password must be at least 8 characters'];
        }
        $stmt = $this->pdo->prepare("SELECT id FROM saas_users WHERE reset_token = ? AND reset_expires > NOW() AND status = 'active'");
        $stmt->execute([$token]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$user) {
            return ['success' => false, 'error' => 'Invalid or expired reset token'];
        }
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->pdo->prepare("UPDATE saas_users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?")->execute([$hash, $user['id']]);
        return ['success' => true];
    }
    
    // ── Profile ──
    public function getUser(int $userId): ?array {
        $stmt = $this->pdo->prepare("SELECT id, email, name, company, avatar, plan, credits_remaining, credits_monthly, api_key, timezone, language, status, last_login, created_at FROM saas_users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    public function updateProfile(int $userId, array $data): bool {
        $allowed = ['name', 'company', 'avatar', 'timezone', 'language'];
        $sets = [];
        $params = [];
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $sets[] = "`$field` = ?";
                $params[] = $data[$field];
            }
        }
        if (empty($sets)) return false;
        $params[] = $userId;
        return $this->pdo->prepare("UPDATE saas_users SET " . implode(', ', $sets) . " WHERE id = ?")->execute($params);
    }
    
    // ── Free subscriptions ──
    private function createFreeSubscriptions(int $userId): void {
        $services = ['seowriter', 'copywriter', 'imagestudio', 'emailcreator', 'socialmanager', 'chatbot', 'blogwriter', 'landingpage', 'bizplan', 'sitebuilder'];
        $stmt = $this->pdo->prepare("SELECT id FROM saas_plans WHERE service = ? AND slug LIKE '%free%' LIMIT 1");
        $sub = $this->pdo->prepare(
            "INSERT IGNORE INTO saas_subscriptions (user_id, plan_id, service, billing_cycle, credits_limit, status)
             VALUES (?, ?, ?, 'free', ?, 'active')"
        );
        foreach ($services as $svc) {
            $stmt->execute([$svc]);
            $plan = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($plan) {
                $sub->execute([$userId, $plan['id'], $svc, 0]);
            }
        }
    }
}
