# Phase 11: Tenant Validation Implementation Plan

## Overview
Implement missing tenant validation system referenced in decisionLog.md but not found in codebase.

## Components

### 1. Core Validator
Location: `includes/Tenant/Validator.php`
```php
class TenantValidator {
    public static function validateAccess(int $tenantId, int $userId): bool {
        // Implementation checking user has access to tenant
    }
    
    public static function validateResourceOwnership(int $tenantId, string $table, int $resourceId): bool {
        // Implementation verifying resource belongs to tenant
    }
}
```

### 2. Database Schema Updates
- Add `tenant_id` column to all tenant-isolated tables
- Create composite indexes on (tenant_id, id) for all tenant-isolated tables
- Add foreign key constraints with ON DELETE CASCADE

### 3. Test Suite
Location: `admin/testing/TenantValidationTest.php`
```php
class TenantValidationTest {
    public function testUserAccessValidation() {
        // Test cases for validateAccess()
    }
    
    public function testResourceOwnershipValidation() {
        // Test cases for validateResourceOwnership()
    }
}
```

### 4. Integration Points
- API endpoints
- Admin panel controllers
- Data access layer

## Implementation Steps
1. Create Validator class
2. Implement database migrations
3. Create test cases
4. Integrate validation checks
5. Document usage patterns

## Estimated Effort
- Core implementation: 2 days
- Testing: 1 day
- Integration: 1 day