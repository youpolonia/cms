<?php
namespace Includes\Auth;

use Includes\Core\BaseController;
use Includes\Core\Request;
use Includes\Core\Response;
require_once __DIR__ . '/../includes/auth/jwt.php';
use Includes\Auth\JWT;
use PDO;

/**
 * WorkerAuthController - Handles worker authentication using JWT
 */
class WorkerAuthController extends BaseController {
    private $db;
    private static $instance;

    public function __construct($db) {
        parent::__construct();
        $this->db = $db;
    }

    public function getDependencies() {
        return ['db' => $this->db];
    }

    public function login($username, $password) {
        // Validate input
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Username and password are required'];
        }

        // Get worker from database
        $stmt = $this->db->prepare("SELECT id, username, password, role FROM workers WHERE username = ?");
        $stmt->execute([$username]);
        $worker = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$worker || !password_verify($password, $worker['password'])) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        // Generate JWT token
        $token = JWT::generateToken([
            'worker_id' => $worker['id'],
            'username' => $worker['username'],
            'role' => $worker['role'],
            'is_worker' => true
        ]);

        return [
            'success' => true,
            'message' => 'Login successful',
            'role' => $worker['role'],
            'token' => $token
        ];
    }

    public function logout() {
        // JWT tokens are stateless - no server-side cleanup needed
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    public function isLoggedIn(Request $request): bool {
        $token = $this->getTokenFromRequest($request);
        if (!$token) return false;

        try {
            $payload = JWT::validateToken($token);
            return !empty($payload['is_worker']);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getUserId(Request $request): ?int {
        $token = $this->getTokenFromRequest($request);
        if (!$token) return null;

        try {
            $payload = JWT::validateToken($token);
            return $payload['worker_id'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function hasRole(Request $request, string $requiredRole): bool {
        $token = $this->getTokenFromRequest($request);
        if (!$token) return false;

        try {
            $payload = JWT::validateToken($token);
            return ($payload['role'] ?? null) === $requiredRole;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getCsrfToken(): string {
        // Using JWT as CSRF protection
        return JWT::generateToken([
            'csrf' => true,
            'random' => bin2hex(random_bytes(16))
        ]);
    }

    public function validateCsrfToken(string $token): bool {
        try {
            $payload = JWT::validateToken($token);
            return !empty($payload['csrf']);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getTokenFromRequest(Request $request): ?string {
        $authHeader = $request->getHeader('Authorization');
        if (empty($authHeader)) return null;

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Static facade methods for backward compatibility
     */
    public static function validateApiRequest(): bool {
        try {
            $instance = self::getInstance();
            $request = new Request($_SERVER);
            return $instance->isLoggedIn($request);
        } catch (\Exception $e) {
            error_log("WorkerAuth validation error: " . $e->getMessage());
            return false;
        }
    }

    public static function getCurrentUserId(): ?int {
        try {
            $instance = self::getInstance();
            $request = new Request($_SERVER);
            return $instance->getUserId($request);
        } catch (\Exception $e) {
            error_log("WorkerAuth user ID error: " . $e->getMessage());
            return null;
        }
    }

    private static function getInstance(): self {
        if (!self::$instance) {
            $db = \core\Database::connection();
            self::$instance = new self($db);
        }
        return self::$instance;
    }
}
