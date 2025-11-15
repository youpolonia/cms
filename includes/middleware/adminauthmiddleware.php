<?php
declare(strict_types=1);

namespace Includes\Middleware;

use Includes\Auth\Auth;

class AdminAuthMiddleware extends AuthenticateMiddleware
{
    public function handle($request, $next)
    {
        // First check regular authentication
        if (!$this->auth->check() || $this->auth->isSessionExpired()) {
            $this->logAccessAttempt(false, 'not authenticated');
            header('Location: /login');
            exit;
        }

        // Then verify admin role
        $user = $this->auth->user();
        
        if (!isset($user['role'])) {
            $this->logAccessAttempt(false, 'no role assigned');
            header('Location: /login');
            exit;
        }

        if ($user['role'] !== 'admin') {
            $this->logAccessAttempt(false, 'insufficient privileges');
            header('HTTP/1.1 403 Forbidden');
            echo 'Access denied - admin privileges required';
            exit;
        }

        $this->logAccessAttempt(true);
        return $next($request);
    }

    protected function logAccessAttempt(bool $success, string $reason = ''): void
    {
        $user = $this->auth->user();
        $userId = $user['id'] ?? 'guest';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $timestamp = date('Y-m-d H:i:s');

        $logEntry = sprintf(
            "[%s] %s access %s from IP %s (User ID: %s) %s\n",
            $timestamp,
            $success ? 'SUCCESS' : 'DENIED',
            $_SERVER['REQUEST_URI'] ?? '',
            $ip,
            $userId,
            $reason ? "Reason: $reason" : ''
        );

        file_put_contents(
            __DIR__.'/../../storage/logs/admin_access.log',
            $logEntry,
            FILE_APPEND
        );
    }
}
