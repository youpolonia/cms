<?php

namespace CMS\Middleware;

use CMS\Core\TenantRepository;

class TenantIsolationMiddleware
{
    private $tenantRepository;

    public function __construct(TenantRepository $tenantRepository)
    {
        $this->tenantRepository = $tenantRepository;
    }

    public function handle($request)
    {
        $tenantId = $request['headers']['X-Tenant-Context'] ?? null;
        
        if (!$tenantId) {
            return $this->errorResponse('Missing tenant context', 403);
        }

        $tenant = $this->tenantRepository->find($tenantId);
        
        if (!$tenant) {
            return $this->errorResponse('Invalid tenant context', 403);
        }

        // Set tenant context globally
        $GLOBALS['current_tenant'] = $tenant;

        return $request;
    }

    private function errorResponse($message, $code)
    {
        return [
            'error' => [
                'code' => 'TENANT_VIOLATION',
                'message' => $message,
                'status' => $code
            ]
        ];
    }

    // Test helper method
    public static function setCurrentTenantId(int $tenantId): void
    {
        $GLOBALS['current_tenant'] = ['id' => $tenantId];
    }
}
