<?php

declare(strict_types=1);

namespace Api\Middleware;

use InvalidArgumentException;
use Api\Utilities\TenantValidator;
use Api\Utilities\SessionManager;

class TenantSecurityMiddleware
{
    private const CSRF_TOKEN_NAME = 'tenant_csrf_token';

    public function __invoke($request, $response, $next)
    {
        // 1. Validate tenant ID from request/session
        $tenantId = $this->validateTenantId($request);

        // 2. Verify CSRF token for non-GET requests
        if ($request->getMethod() !== 'GET') {
            $this->validateCsrfToken($request, $tenantId);
        }

        // 3. Validate session integrity
        $this->validateSession($request, $tenantId);

        // Generate new CSRF token for next request
        $this->generateCsrfToken($tenantId);

        return $next($request, $response);
    }

    private function validateTenantId($request): string
    {
        $tenantId = $request->getAttribute('tenant_id') 
            ?? $request->getParsedBody()['tenant_id'] 
            ?? $_SESSION['tenant_id'] 
            ?? null;

        if (!$tenantId) {
            throw new InvalidArgumentException('Tenant ID not provided');
        }

        return TenantValidator::validateId($tenantId);
    }

    private function validateCsrfToken($request, string $tenantId): void
    {
        $token = $request->getParsedBody()[self::CSRF_TOKEN_NAME] 
            ?? $request->getHeaderLine('X-CSRF-Token');

        if (!$token || !hash_equals(
            $_SESSION[self::CSRF_TOKEN_NAME][$tenantId] ?? '',
            $token
        )) {
            throw new InvalidArgumentException('Invalid CSRF token');
        }
    }

    private function generateCsrfToken(string $tenantId): void
    {
        $_SESSION[self::CSRF_TOKEN_NAME][$tenantId] = bin2hex(random_bytes(32));
    }

    private function validateSession($request, string $tenantId): void
    {
        if (!SessionManager::validate($request, $tenantId)) {
            throw new InvalidArgumentException('Session validation failed');
        }
    }
}
