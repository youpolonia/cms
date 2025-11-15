<?php
declare(strict_types=1);

namespace Includes\Middleware;

use Core\TenantContext;
use Services\TenantQuotaService;
use Exception;
use PDO;

class TenantIsolationMiddleware
{
    private ?PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        require_once __DIR__ . '/../../core/database.php';
        $this->pdo = $pdo ?: \core\Database::connection();
    }

    public function handle(array $request): array
    {
        try {
            $tenantId = $this->extractTenantId($request);
            $this->validateTenant($tenantId);
            
            // Store in all contexts
            TenantContext::setCurrent($tenantId);
            $request['tenant_id'] = $tenantId;
            
            // Initialize tenant-aware query builder
            require_once __DIR__.'/../../database/migrations/Migration_0005_TenantAwareQueryBuilder.php';
            TenantAwareQueryBuilder::setTenantId($tenantId);
            
            // Validate tenant schema exists
            $this->validateTenantSchema($tenantId);
            
            // Check quota (non-blocking)
            $this->checkQuota($tenantId);

            return $request;
        } catch (Exception $e) {
            error_log("TenantIsolationMiddleware failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function extractTenantId(array $request): string
    {
        // 1. Check subdomain
        $hostParts = explode('.', $request['headers']['host'] ?? '');
        if (count($hostParts) > 2) {
            $tenantId = $hostParts[0];
            if ($this->validateTenantFormat($tenantId)) {
                return $tenantId;
            }
        }

        // 2. Check X-Tenant-ID header
        if (!empty($request['headers']['x-tenant-id'])) {
            $tenantId = $request['headers']['x-tenant-id'];
            if ($this->validateTenantFormat($tenantId)) {
                return $tenantId;
            }
        }

        // 3. Check X-Tenant-Key header (legacy)
        if (!empty($request['headers']['x-tenant-key'])) {
            $tenantId = $request['headers']['x-tenant-key'];
            if ($this->validateTenantFormat($tenantId)) {
                return $tenantId;
            }
        }

        // 4. For home page and public routes, use default tenant
        $path = $request['path'] ?? $_SERVER['REQUEST_URI'] ?? '/';
        if ($path === '/' || strpos($path, '/login') === 0 || strpos($path, '/assets/') === 0 ||
            strpos($path, '/css/') === 0 || strpos($path, '/js/') === 0 ||
            strpos($path, '/images/') === 0 || $path === '/favicon.ico') {
            return 'default';
        }

        throw new Exception('No valid tenant identifier found');
    }

    private function validateTenant(string $tenantId): void
    {
        if (!$this->validateTenantFormat($tenantId)) {
            throw new Exception('Invalid tenant identifier format');
        }

        if (!$this->validateTenantExists($tenantId)) {
            throw new Exception('Tenant does not exist');
        }
    }

    private function validateTenantFormat(string $tenantId): bool
    {
        return preg_match('/^[a-z0-9\-]{3,32}$/i', $tenantId);
    }

    private function validateTenantExists(string $tenantId): bool
    {
        try {
            $db = $this->pdo;
            $stmt = $db->prepare('SELECT id FROM tenants WHERE id = :id AND status = "active"');
            $stmt->execute(['id' => $tenantId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }

    private function validateTenantSchema(string $tenantId): void
    {
        try {
            $db = $this->pdo;
            $stmt = $db->prepare('SELECT 1 FROM information_schema.tables WHERE table_schema = :tenantId LIMIT 1');
            $stmt->execute(['tenantId' => $tenantId]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception("Tenant schema does not exist");
            }
        } catch (PDOException $e) {
            error_log('Schema validation error: ' . $e->getMessage());
            throw new Exception("Failed to validate tenant schema");
        }
    }

    private function checkQuota(string $tenantId): void
    {
        try {
            TenantQuotaService::checkQuota($tenantId);
        } catch (Exception $e) {
            error_log("Tenant quota check failed for {$tenantId}: " . $e->getMessage());
        }
    }

    private function isPublicRoute(array $request): bool
    {
        // Get the path from the request
        $path = $request['path'] ?? $_SERVER['REQUEST_URI'] ?? '/';
        
        // List of public routes that don't require tenant isolation
        $publicRoutes = [
            '/',                // Home page
            '/login',           // Login page
            '/register',        // Registration page
            '/assets/',         // Static assets
            '/css/',            // CSS files
            '/js/',            // JavaScript files
            '/images/',         // Image files
            '/favicon.ico',     // Favicon
            '/robots.txt',      // Robots file
            '/sitemap.xml',     // Sitemap
        ];
        
        // Check if the path matches any public route
        foreach ($publicRoutes as $publicRoute) {
            if ($publicRoute === '/' && $path === '/') {
                return true;
            }
            if ($publicRoute !== '/' && strpos($path, $publicRoute) === 0) {
                return true;
            }
        }
        
        return false;
    }
}
