# Phase 9 Tenant Isolation Deployment Checklist

## Prerequisites
- [ ] Database backup completed
- [ ] Maintenance mode enabled
- [ ] Verify `tenants` table exists with sample data
- [ ] Confirm MigrationRunner is operational

## Database Migration Steps
1. Execute tenant isolation migrations:
```php
$runner->runMigration('Migration_0001_TenantIsolation.php');
$runner->runMigration('Migration_0002_AddTenantColumns.php');
$runner->runMigration('Migration_0004_CrossSiteRelations.php');
```

2. Verify schema changes:
- `tenant_id` column added to all tenant-specific tables
- Indexes created on tenant_id columns
- Foreign key constraints updated

## Configuration Requirements
1. Middleware Setup:
```php
$app->addMiddleware(new TenantQueryFilter());
```

2. Environment Variables:
```
TENANT_HEADER=X-Tenant-ID
TENANT_COOKIE=tenant_id  
TENANT_STRICT_MODE=true
```

## Verification Procedures
1. Test Endpoints:
- `/api/tenant/verify-isolation`
- `/api/content/tenant-scoped`
- `/api/relations/cross-tenant`

2. Validation Checks:
- Verify tenant isolation in all queries
- Confirm cross-tenant access blocked
- Test audit logging includes tenant context

## Rollback Instructions
1. Single Migration Rollback:
```php 
$runner->rollbackMigration('Migration_0004_CrossSiteRelations.php');
```

2. Full Rollback:
```php
$runner->rollbackBatch(9); // Phase 9 batch
```

3. Post-Rollback:
- Verify schema changes reverted
- Check application functionality
- Restore from backup if needed

## Post-Deployment Tasks
1. Update Documentation:
- API documentation with tenant context
- Developer guide for tenant-aware queries
- Admin guide for tenant management

2. Monitoring:
- Tenant isolation violation logs
- Query performance metrics
- Error rates by tenant