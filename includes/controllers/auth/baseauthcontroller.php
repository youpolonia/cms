<?php

namespace Includes\Controllers\Auth;

use Includes\Auth\AuthService;
use Core\Security\CSRFToken;
use Core\Logger;

abstract class BaseAuthController
{
    protected AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    protected function validateCsrfToken(string $token): bool
    {
        return CSRFToken::validate($token);
    }

    protected function checkRateLimit(string $ip): void
    {
        // Implementation depends on your rate limiting system
    }

    protected function authenticateUser(string $username, string $password): ?array
    {
        return $this->authService->authenticate($username, $password);
    }

    protected function logAuthAttempt(string $username, bool $success): void
    {
        Logger::securityLog(
            $success 
                ? "Successful login for {$username}"
                : "Failed login attempt for {$username}",
            [
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]
        );
    }

    abstract protected function handleSuccessfulAuth(array $user): void;
    abstract protected function handleFailedAuth(): void;
}
