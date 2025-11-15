<?php
class AdminAuthMiddleware {
    private AuthService $authService;

    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }

    public function handle(array $request, callable $next): array {
        // Check if user is authenticated
        if (empty($_SESSION['admin_user_id'])) {
            throw new RuntimeException("Unauthorized", 401);
        }

        // Verify admin privileges
        $user = $this->authService->getUserWithRoles($_SESSION['admin_user_id']);
        if (!$user || !$this->hasAdminRole($user['roles'])) {
            throw new RuntimeException("Forbidden - Admin access required", 403);
        }

        // Add user to request context
        $request['admin_user'] = $user;

        return $next($request);
    }

    private function hasAdminRole(array $roles): bool {
        foreach ($roles as $role) {
            if ($role['name'] === 'admin') {
                return true;
            }
        }
        return false;
    }
}
