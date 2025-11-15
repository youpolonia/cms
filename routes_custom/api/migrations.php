<?php
/**
 * Migration API Endpoints
 * 
 * Provides web-accessible endpoints for:
 * - Running tenant migrations
 * - Rolling back migrations
 * - Testing migrations
 * 
 * Security:
 * - Requires admin authentication
 * - Rate limited (10 requests/minute)
 */

declare(strict_types=1);

use Core\Auth\AdminAuth;
use Core\RateLimiter;
use Core\Validation\MigrationValidator;
use Database\Migrations\Phase12TenantStructure;

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

// Apply rate limiting (10 requests/minute)
RateLimiter::apply('migrations', 10, 60);

// Verify admin authentication
AdminAuth::verify();

// Validate request parameters
$validator = new MigrationValidator();
$validator->validate($_POST);

// Migration endpoints
switch ($_SERVER['REQUEST_URI']) {
    case '/api/migrations/tenant/migrate':
        $result = Phase12TenantStructure::migrate($pdo);
        echo json_encode(['success' => $result]);
        break;
        
    case '/api/migrations/tenant/rollback':
        $result = Phase12TenantStructure::rollback($pdo);
        echo json_encode(['success' => $result]);
        break;
        
    case '/api/migrations/tenant/test':
        $result = Phase12TenantStructure::test($pdo);
        echo json_encode(['success' => $result]);
        break;
        
    case '/api/v1/migrations/phase14/verify':
        $result = Phase14VersionControlMigration::verify($pdo);
        echo json_encode($result);
        break;
        
    case '/api/v1/migrations/phase14/test-insert':
        $result = Phase14VersionControlMigration::testInsert($pdo);
        echo json_encode($result);
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
}
