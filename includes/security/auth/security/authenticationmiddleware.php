<?php
/**
 * Authentication Middleware
 * Handles session-based authentication and CSRF protection
 */

use Includes\Auth\AuthService;

class AuthenticationMiddleware {
    private $authService;
    private $csrfTokenName = 'csrf_token';

    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }

    public function handle($request, $next) {
        // Verify CSRF token for non-GET requests
        if ($request['method'] !== 'GET') {
            $token = $request['headers']['X-CSRF-TOKEN'] ?? $request['post'][$this->csrfTokenName] ?? null;
            if (!\Includes\Security\CSRF::validate($token)) {
                throw new Exception('Invalid CSRF token');
            }
        }

        // Check if user is authenticated
        if (!$this->authService->isAuthenticated()) {
            return ApiResponse::unauthorized();
        }

        return $next($request);
    }

    public function isAuthenticated(): bool {
        return $this->authService->isAuthenticated();
    }

    public function login($userId): void {
        $this->authService->login($userId);
    }

    public function logout(): void {
        $this->authService->logout();
    }

    public function generateCsrfToken(): string {
        return $this->authService->generateCsrfToken();
    }

    public function getCsrfToken(): string {
        return $this->authService->getCsrfToken();
    }
}
