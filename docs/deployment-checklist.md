# Comprehensive Deployment Checklist

## Phase 11 Specific Verification
1. [ ] Content Federation API:
   - Verify `/federation/share` endpoint
   - Test cross-tenant content sharing
   - Validate conflict resolution workflows
   - Check rate limiting headers

2. [ ] Tenant Isolation Enhancements:
   - Test `/api/test/cross-site-relations`
   - Verify migration 0004_cross_site_relations
   - Validate tenant-aware query builder

## Pre-Deployment Verification
1. [ ] Framework-Free Compliance:
   - No Laravel patterns detected
   - No CLI dependencies
   - Pure PHP 8.1+ implementation
   - FTP-deployable structure

2. [ ] Migration Validation:
   - Class naming follows `Migration_[4-digit-id]_[Name]`
   - Static methods only (no instantiation)
   - PDO parameter named `$pdo` consistently
   - Web endpoints exist:
     - `/migrate/[id]`
     - `/rollback/[id]`

## Database Deployment
1. [ ] Execute migrations in sequence by ID
2. [ ] Verify foreign key relationships:
   ```sql
   SELECT TABLE_NAME, CONSTRAINT_NAME
   FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
   WHERE CONSTRAINT_TYPE = 'FOREIGN KEY'
   ```
3. [ ] Test rollback procedures for each migration

## Tenant Isolation Testing
1. [ ] Verify test endpoints:
   - `/api/test/tenant-isolation`
   - `/api/test/cross-site-relations`
2. [ ] Execute test queries:
   - Basic tenant isolation
   - SQL injection test
   - Cross-tenant access test
   - Boundary test
   - Content federation scenarios

## File Deployment
1. [ ] Synchronize multisite assets
2. [ ] Verify asset paths in tenant context
3. [ ] Set file permissions:
   - 644 for files
   - 755 for directories

## Post-Deployment Verification
1. [ ] Test all modified API endpoints including:
   - Content Federation API
   - Tenant isolation endpoints
2. [ ] Verify tenant context propagation
3. [ ] Run smoke tests on critical paths

## Rollback Procedures
1. [ ] Database:
   - Execute in reverse dependency order
   - Remove foreign keys before tables
   - Use `IF EXISTS` clauses
2. [ ] Files:
   - Restore previous versions
   - Verify permissions