<?php
declare(strict_types=1);

namespace Api\Gateway\Middleware;

use Includes\Auth\Services\EmergencyLogger;

class AuthMiddleware
{
    private static ?EmergencyLogger $logger = null;

    public static function setLogger(EmergencyLogger $logger): void
    {
        self::$logger = $logger;
    }

    public static function handle(array $request, callable $next): array
    {
        // Skip auth for excluded routes
        if (self::shouldSkipAuth($request['path'])) {
            return $next($request);
        }

        // Validate JWT token
        $token = self::getBearerToken($request['headers']);
        if (!$token) {
            self::logAuthFailure('Missing token', $request);
            return self::unauthorizedResponse();
        }

        try {
            $isValid = self::validateJWT($token);
            if (!$isValid) {
                self::logAuthFailure('Invalid token', $request);
                return self::unauthorizedResponse();
            }

            self::logAuthSuccess($request);
            return $next($request);
        } catch (\Exception $e) {
            self::logAuthFailure('Token validation error: ' . $e->getMessage(), $request);
            return self::unauthorizedResponse();
        }
    }

    private static function shouldSkipAuth(string $path): bool
    {
        $config = require_once __DIR__ . '/../../config.php';
        // Check both possible config structures for backward compatibility
        $excludePaths = $config['security']['auth_middleware']['exclude'] ??
                       $config['security']['auth_middleware_exclude'] ?? [];
        return in_array($path, $excludePaths);
    }

    private static function getBearerToken(array $headers): ?string
    {
        $authHeader = $headers['Authorization'] ?? '';
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private static function validateJWT(string $token): bool
    {
        $config = require_once __DIR__ . '/../../config.php';
        // Check both possible config structures for backward compatibility
        $secret = $config['security']['jwt']['secret'] ??
                 $config['security']['jwt_secret'] ?? '';

        if (empty($secret)) {
            throw new \RuntimeException('JWT secret not configured');
        }

        // Simple validation - in production use firebase/php-jwt
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        return hash_equals(
            $parts[2],
            hash_hmac('sha256', "$parts[0].$parts[1]", $secret)
        );
    }

    private static function unauthorizedResponse(): array
    {
        return [
            'status' => 401,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode(['error' => 'Unauthorized'])
        ];
    }

    private static function logAuthSuccess(array $request): void
    {
        if (self::$logger) {
            self::$logger->log('INFO', 'API authentication success', [
                'path' => $request['path'],
                'method' => $request['method']
            ]);
        }
    }

    private static function logAuthFailure(string $reason, array $request): void
    {
        if (self::$logger) {
            self::$logger->log('WARNING', 'API authentication failed', [
                'reason' => $reason,
                'path' => $request['path'],
                'method' => $request['method'],
                'ip' => $request['headers']['X-Forwarded-For'] ?? $request['headers']['Client-Ip'] ?? 'unknown'
            ]);
        }
    }
}
