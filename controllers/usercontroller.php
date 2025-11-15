<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * User Management API Controller
 * 
 * Handles both regular user operations and admin-specific functions
 */
class UserController {
    private UserService $userService;
    private AuthService $authService;

    public function __construct(UserService $userService, AuthService $authService) {
        $this->userService = $userService;
        $this->authService = $authService;
    }

    public function createUser(array $request): array {
        $required = ['username', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($request[$field])) {
                throw new InvalidArgumentException("Missing $field");
            }
        }

        $tenantId = $request['tenant_id'] ?? null;

        return [
            'status' => 'success',
            'data' => $this->userService->createUser(
                $request['username'],
                $request['email'],
                $request['password'],
                $tenantId,
                $request['roles'] ?? []
            )
        ];
    }

    public function getUser(array $request): array {
        if (empty($request['user_id'])) {
            throw new InvalidArgumentException("Missing user_id");
        }

        $tenantId = $request['tenant_id'] ?? null;

        $user = $this->userService->getUser(
            (int)$request['user_id'],
            $tenantId
        );
        if (!$user) {
            throw new RuntimeException("User not found");
        }

        return [
            'status' => 'success',
            'data' => $user
        ];
    }

    public function updateUser(array $request): array {
        require_once __DIR__ . '/../core/csrf.php';
        csrf_validate_or_403();

        if (empty($request['user_id'])) {
            throw new InvalidArgumentException("Missing user_id");
        }

        $tenantId = $request['tenant_id'] ?? null;

        return [
            'status' => 'success',
            'data' => $this->userService->updateUser(
                (int)$request['user_id'],
                $request['username'] ?? null,
                $request['email'] ?? null,
                $request['password'] ?? null,
                $tenantId,
                $request['roles'] ?? null
            )
        ];
    }

    public function deleteUser(array $request): array {
        require_once __DIR__ . '/../core/csrf.php';
        csrf_validate_or_403();

        if (empty($request['user_id'])) {
            throw new InvalidArgumentException("Missing user_id");
        }

        $tenantId = $request['tenant_id'] ?? null;

        return [
            'status' => 'success',
            'data' => $this->userService->deleteUser(
                (int)$request['user_id'],
                $tenantId
            )
        ];
    }

    public function authenticate(array $request): array {
        if (empty($request['username']) || empty($request['password'])) {
            throw new InvalidArgumentException("Missing credentials");
        }

        $token = $this->authService->authenticate(
            $request['username'],
            $request['password']
        );

        if (!$token) {
            throw new RuntimeException("Authentication failed");
        }

        return [
            'status' => 'success',
            'data' => ['token' => $token]
        ];
    }

    public function listUsers(array $request): array {
        $tenantId = $request['tenant_id'] ?? null;
        $limit = isset($request['limit']) ? (int)$request['limit'] : 10;
        $offset = isset($request['offset']) ? (int)$request['offset'] : 0;

        return [
            'status' => 'success',
            'data' => $this->userService->listUsers(
                $tenantId,
                $limit,
                $offset
            )
        ];
    }

    /**
     * Admin-only: Get user details including sensitive info
     */
    public function adminGetUser(int $userId): array {
        $user = $this->userService->getUserWithSensitiveData($userId);
        if (!$user) {
            throw new RuntimeException("User not found");
        }

        return [
            'status' => 'success',
            'data' => $user
        ];
    }

    /**
     * Admin-only: Force password reset for user
     */
    public function adminForcePasswordReset(int $userId): array {
        $success = $this->userService->forcePasswordReset($userId);
        return [
            'status' => $success ? 'success' : 'error',
            'message' => $success ? 'Password reset initiated' : 'Failed to force password reset'
        ];
    }

    /**
     * Admin-only: Deactivate user account
     */
    public function adminDeactivateUser(int $userId): array {
        $success = $this->userService->deactivateUser($userId);
        return [
            'status' => $success ? 'success' : 'error',
            'message' => $success ? 'User deactivated' : 'Failed to deactivate user'
        ];
    }
}
