<?php

namespace Includes\Controllers\Auth;

use Core\Cache\SessionCacheAdapter;
use Core\Cache\CacheFactory;
use Core\Security\CSRFToken;
use Core\Logger;

class AdminAuthController extends BaseAuthController
{
    protected function preAuthChecks(): bool
    {
        // Validate CSRF token
        if (!CSRFToken::validate($_POST['csrf_token'] ?? '')) {
            header('Location: /admin/login?error=csrf');
            return false;
        }

        // Check rate limiting
        if ($this->isRateLimited($_SERVER['REMOTE_ADDR'])) {
            header('Location: /admin/login?error=rate_limited');
            return false;
        }

        // Validate input
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            header('Location: /admin/login?error=invalid_credentials');
            return false;
        }

        return true;
    }

    protected function handleSuccessfulAuth(array $user): void
    {
        $cache = new SessionCacheAdapter(
            CacheFactory::make(),
            session_id()
        );
        
        $cache->set('user_id', $user['id']);
        $cache->set('authenticated', true);
        session_regenerate_id(true);

        // Log successful login
        Logger::securityLog("User {$user['username']} logged in successfully", [
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        header('Location: /admin/dashboard');
        exit;
    }

    protected string $username;

    public function __construct()
    {
        parent::__construct();
        $this->username = $_POST['username'] ?? '';
    }

    protected function handleFailedAuth(): void
    {
        // Log failed attempt
        Logger::securityLog("Failed login attempt for {$this->username}", [
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);

        header('Location: /admin/login?error=invalid_credentials');
        exit;
    }

    private function isRateLimited(string $ip): bool
    {
        // Implement rate limiting logic here
        return false;
    }
}
