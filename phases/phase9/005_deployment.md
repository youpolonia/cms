# Phase 7 Tenant-Aware Features Deployment Guide

## Database Migration Steps

1. **Prerequisites**:
   - Database backup completed
   - Maintenance mode enabled
   - Verify `tenants` table exists

2. **Execute Migrations**:
```php
// Run via MigrationRunner
$runner = new MigrationRunner($pdo, __DIR__.'/database/migrations');
$runner->runMigration('Migration_0001_AddTenantAwareness.php');
$runner->runMigration('Migration_0001_StatusTransitions.php');
```

3. **Verification**:
   - Check `migrations` table for successful entries
   - Verify schema changes:
     - `tenant_id` column added to `users` and `content`
     - `status_transitions` table created
   - Run test queries with tenant context

## Configuration Requirements

1. **Middleware Setup**:
```php
// Add to application middleware stack
$app->addMiddleware(new TenantIsolationMiddleware());
```

2. **Required Environment Variables**:
```
TENANT_HEADER=X-Tenant-ID
TENANT_COOKIE=tenant_id
```

## Verification Procedures

1. **Test Endpoints**:
   - `/api/tenant/verify` - Verify tenant context
   - `/api/content/tenant-scoped` - Test tenant-scoped queries
   - `/api/status/transitions` - Test status transitions

2. **Validation Checks**:
   - Verify tenant isolation in API responses
   - Confirm audit logs include tenant context
   - Test cross-tenant content sharing

## Rollback Instructions

1. **Single Migration Rollback**:
```php
$runner->rollbackMigration('Migration_0001_AddTenantAwareness.php');
```

2. **Full Rollback**:
```php
$runner->rollbackBatch(7); // Batch number for Phase 7
```

3. **Post-Rollback**:
   - Verify schema changes reverted
   - Check application functionality
   - Restore from backup if needed

## Post-Deployment Tasks

1. Update documentation:
   - API documentation with tenant context requirements
   - Developer guide for tenant-aware features

2. Monitor:
   - Tenant isolation logs
   - Performance metrics
   - Error rates