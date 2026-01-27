<?php
/**
 * Tenant Isolation Middleware
 * 
 * Validates and sets tenant context for API requests
 */
class TenantMiddleware {
    public function __invoke(array $request, PDO $pdo, callable $next): array {
        $tenantId = $request['headers']['X-Tenant-Context'] ?? null;
        
        if (!$tenantId || !$this->validateTenant($pdo, $tenantId)) {
            return [
                'status' => 403,
                'body' => ['error' => 'Invalid tenant context']
            ];
        }

        $request['tenant_id'] = $tenantId;
        return $next($request);
    }

    private function validateTenant(PDO $pdo, string $tenantId): bool {
        $stmt = $pdo->prepare("SELECT 1 FROM tenants WHERE id = ?");
        $stmt->execute([$tenantId]);
        return (bool)$stmt->fetchColumn();
    }
}
