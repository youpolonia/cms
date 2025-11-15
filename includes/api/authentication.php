<?php
/**
 * API Authentication
 */

declare(strict_types=1);

namespace CMS\API;

use App\Models\Role;
use includes\auth\RoleManager;
require_once __DIR__ . '/../auth/RoleManager.php';
class Authentication
{
    // Worker-specific permissions
    public const PERM_WORKER_REGISTER = 'worker_register';
    public const PERM_WORKER_HEARTBEAT = 'worker_heartbeat';
    public const PERM_WORKER_VIEW_METRICS = 'worker_view_metrics';
    public const PERM_WORKER_MANAGE_SCALING = 'worker_manage_scaling';
    public const PERM_WORKER_VIEW = 'worker_view';

    private const TOKEN_HEADER = 'X-API-Token';
    private const TOKEN_PREFIX = 'Bearer ';


    public function authenticate(): void
    {
        $token = $this->getTokenFromHeader();

        if (!$token) {
            throw new \RuntimeException('Authentication token required', 401);
        }

        if (!$this->validateToken($token)) {
            throw new \RuntimeException('Invalid authentication token', 403);
        }
    }

    public function checkPermission(string $permission, ?int $testUserId = null): bool
    {
        // In test mode, allow direct user ID passing
        if (php_sapi_name() === 'cli' && $testUserId !== null) {
            $userId = $testUserId;
        } else {
            $token = $this->getTokenFromHeader();
            if (!$token || !$this->validateToken($token)) {
                return false;
            }
            $userId = $this->getUserIdFromToken($token);
        }
        $cacheKey = "perm_{$userId}_{$permission}";

        // Check cache first
        if (function_exists('apcu_exists') && function_exists('apcu_fetch')) {
            if (apcu_exists($cacheKey)) {
                $cachedResult = apcu_fetch($cacheKey);
                if ($cachedResult !== false) {
                    return (bool)$cachedResult;
                }
            }
        }

        $roleManager = new RoleManager();
        $role = $roleManager->getUserRole($userId);
        $result = false;
        
        // Special case for worker permissions - allow if either:
        // 1. User has the permission directly
        // 2. Token is a valid worker token with matching permission
        if (strpos($permission, 'worker_') === 0) {
            $result = $role && ($role->hasPermission($permission) || $this->validateWorkerToken($token, $permission));
        } else {
            $result = $role && $role->hasPermission($permission);
        }

        // Store in cache if available
        if (function_exists('apcu_store')) {
            apcu_store($cacheKey, $result, 300); // 5 minute TTL
        }

        return $result;
    }

    private function validateWorkerToken(string $token, string $requiredPermission): bool
    {
        $cacheKey = "worker_perm_{$token}_{$requiredPermission}";

        // Check cache first
        if (function_exists('apcu_exists') && function_exists('apcu_fetch')) {
            if (apcu_exists($cacheKey)) {
                $cachedResult = apcu_fetch($cacheKey);
                if ($cachedResult !== false) {
                    return (bool)$cachedResult;
                }
            }
        }

        // Worker tokens are stored in worker_tokens table
        $stmt = $this->db->prepare(
            "SELECT w.capabilities
             FROM worker_tokens wt
             JOIN workers w ON wt.worker_id = w.worker_id
             WHERE wt.token = :token
             AND wt.expires_at > NOW()"
        );
        
        $stmt->execute([':token' => $token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return false;
        }
        
        $capabilities = json_decode($result['capabilities'], true);
        $result = in_array($requiredPermission, $capabilities);

        // Store in cache if available
        if (function_exists('apcu_store')) {
            apcu_store($cacheKey, $result, 300); // 5 minute TTL
        }

        return $result;
    }

    public function requirePermission(string $permission): void
    {
        if (!$this->checkPermission($permission)) {
            throw new \RuntimeException('Permission denied', 403);
        }
    }

    private function getTokenFromHeader(): ?string
    {
        if (php_sapi_name() === 'cli') {
            return null;
        }
        
        if (!function_exists('getallheaders')) {
            // Fallback for non-Apache servers
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        } else {
            $headers = getallheaders();
        }
        $authHeader = $headers[self::TOKEN_HEADER] ?? '';

        if (strpos($authHeader, self::TOKEN_PREFIX) === 0) {
            return substr($authHeader, strlen(self::TOKEN_PREFIX));
        }

        return null;
    }

    private function validateToken(string $token): bool
    {
        // In a real implementation, this would validate against stored tokens
        // For now, we'll just validate the format
        return preg_match('/^[a-f0-9]{64}$/', $token) === 1;
    }

    private function getUserIdFromToken(string $token): ?int
    {
        // TODO: Implement token to user ID resolution
        return 1; // Placeholder
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Invalidate permission cache for a user
     */
    public static function invalidateUserPermissions(int $userId): void
    {
        if (function_exists('apcu_delete') && function_exists('apcu_iterator')) {
            try {
                $iterator = new APCuIterator('/^perm_' . $userId . '_/');
                if ($iterator) {
                    foreach ($iterator as $key => $value) {
                        apcu_delete($key);
                    }
                }
            } catch (\Exception $e) {
                // Log error if needed
            }
        }
    }

    /**
     * Invalidate worker token cache
     */
    public static function invalidateWorkerToken(string $token): void
    {
        if (function_exists('apcu_delete') && function_exists('apcu_iterator')) {
            try {
                $iterator = new APCuIterator('/^worker_perm_' . preg_quote($token, '/') . '_/');
                if ($iterator) {
                    foreach ($iterator as $key => $value) {
                        apcu_delete($key);
                    }
                }
            } catch (\Exception $e) {
                // Log error if needed
            }
        }
    }
}
