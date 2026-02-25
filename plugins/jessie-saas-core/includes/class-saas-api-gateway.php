<?php
namespace Plugins\JessieSaasCore;

/**
 * SaaS API Gateway — authenticates API requests, rate limits, tracks usage
 */
class SaasApiGateway {
    private \PDO $pdo;
    private ?array $currentUser = null;
    
    public function __construct() {
        if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
        require_once CMS_ROOT . '/db.php';
        $this->pdo = \core\Database::connection();
    }
    
    /**
     * Authenticate request via API key or session
     * Call at top of every SaaS API endpoint
     */
    public function authenticate(): array {
        // Try API key first (header or query)
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? null;
        
        if ($apiKey) {
            $stmt = $this->pdo->prepare("SELECT * FROM saas_users WHERE api_key = ? AND status = 'active'");
            $stmt->execute([$apiKey]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$user) {
                return $this->errorResponse(401, 'Invalid API key');
            }
            $this->currentUser = $user;
            return ['success' => true, 'user' => $user, 'auth_method' => 'api_key'];
        }
        
        // Try session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (isset($_COOKIE['SAAS_SESSID'])) {
                session_name('SAAS_SESSID');
                session_start();
            }
        }
        
        if (!empty($_SESSION['saas_user_id'])) {
            $stmt = $this->pdo->prepare("SELECT * FROM saas_users WHERE id = ? AND status = 'active'");
            $stmt->execute([$_SESSION['saas_user_id']]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($user) {
                $this->currentUser = $user;
                return ['success' => true, 'user' => $user, 'auth_method' => 'session'];
            }
        }
        
        return $this->errorResponse(401, 'Authentication required. Provide X-API-Key header or login.');
    }
    
    /**
     * Check rate limit for current user
     */
    public function rateLimit(string $service, int $maxPerMinute = 60): array {
        if (!$this->currentUser) return $this->errorResponse(401, 'Not authenticated');
        
        $userId = $this->currentUser['id'];
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM saas_api_usage 
             WHERE user_id = ? AND service = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)"
        );
        $stmt->execute([$userId, $service]);
        $count = (int)$stmt->fetchColumn();
        
        if ($count >= $maxPerMinute) {
            return $this->errorResponse(429, 'Rate limit exceeded. Max ' . $maxPerMinute . ' requests per minute.', [
                'retry_after' => 60,
                'limit' => $maxPerMinute,
                'used' => $count
            ]);
        }
        
        return ['success' => true, 'remaining' => $maxPerMinute - $count];
    }
    
    /**
     * Full gate: authenticate + rate limit + credit check
     */
    public function gate(string $service, string $endpoint, int $creditsNeeded = 1, int $ratePerMinute = 60): array {
        // Auth
        $auth = $this->authenticate();
        if (!$auth['success']) return $auth;
        
        // Rate limit
        $rate = $this->rateLimit($service, $ratePerMinute);
        if (!$rate['success']) return $rate;
        
        // Credit check
        $credits = new SaasCredits();
        $check = $credits->canUse((int)$this->currentUser['id'], $service, $creditsNeeded);
        if (!$check['allowed']) {
            return $this->errorResponse(402, $check['reason'], [
                'credits_used' => $check['used'] ?? 0,
                'credits_limit' => $check['limit'] ?? 0
            ]);
        }
        
        return [
            'success' => true,
            'user_id' => (int)$this->currentUser['id'],
            'user' => $this->currentUser,
            'credits' => $check
        ];
    }
    
    /**
     * Record usage after successful API call
     */
    public function recordUsage(string $service, string $endpoint, int $credits = 1, array $meta = []): void {
        if (!$this->currentUser) return;
        $creditsService = new SaasCredits();
        $creditsService->consume((int)$this->currentUser['id'], $service, $endpoint, $credits, array_merge($meta, [
            'api_key' => $this->currentUser['api_key'] ?? null
        ]));
    }
    
    public function getCurrentUser(): ?array { return $this->currentUser; }
    public function getUserId(): ?int { return $this->currentUser ? (int)$this->currentUser['id'] : null; }
    
    /**
     * Send JSON error and exit
     */
    private function errorResponse(int $code, string $message, array $extra = []): array {
        return array_merge(['success' => false, 'error' => $message, 'code' => $code], $extra);
    }
    
    /**
     * Send JSON error response and exit (for direct use in endpoints)
     */
    public static function sendError(int $code, string $message, array $extra = []): never {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(['success' => false, 'error' => $message], $extra), JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Send JSON success response and exit
     */
    public static function sendJson(array $data, int $code = 200): never {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(['success' => true], $data), JSON_UNESCAPED_UNICODE);
        exit;
    }
}
