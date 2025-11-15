<?php
/**
 * Admin Authentication Middleware
 * Provides session validation and admin privilege checking
 */

namespace Core\Middleware;

use Core\Auth;
use Core\Response;
use Core\Router;

class AdminAuth
{
    /**
     * Log authentication errors
     * @param string $message Error message
     * @param array $context Additional context (optional)
     */
    private static function logError(string $message, array $context = []): void
    {
        $logEntry = sprintf(
            "[%s] %s - %s\n",
            date('Y-m-d H:i:s'),
            $message,
            json_encode($context, JSON_PARTIAL_OUTPUT_ON_ERROR)
        );
        
        file_put_contents(
            dirname(__DIR__, 3) . '/logs/auth_errors.log',
            $logEntry,
            FILE_APPEND
        );
    }

    /**
     * Static check for admin authentication status
     * @return bool True if authenticated admin, false otherwise
     */
    public static function check(): bool 
    {
        if (!Auth::check()) {
            self::logError('Auth::check() failed - no active session');
            return false;
        }

        $user = Auth::user();
        if (!$user instanceof \Core\User) {
            self::logError('Invalid user object', [
                'user_type' => gettype($user)
            ]);
            return false;
        }

        return $user->hasRole('admin');
    }

    /**
     * Handle the incoming request
     *
     * @param mixed $request The incoming request data
     * @param callable $next The next middleware in the stack
     * @return mixed Response from next middleware or redirect/error
     */
    public function handle($request, callable $next)
    {
        if (!Auth::check()) {
            return Router::redirect('/admin/login');
        }

        if (!self::check()) {
            return Response::abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
