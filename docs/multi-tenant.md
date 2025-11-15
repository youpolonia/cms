# Multi-Tenant Architecture Documentation

## Overview
The CMS implements multi-tenancy through:
- Database-level isolation with tenant_id columns
- Middleware-based tenant identification
- Resource quotas per tenant
- Strict access controls between tenants

## Database Schema
Key tables for multi-tenancy:
- `tenants` - Stores tenant metadata and quotas
- `tenant_usage` - Tracks resource consumption
- All content tables include `tenant_id` foreign key

```sql
CREATE TABLE tenants (
    id INT PRIMARY KEY,
    name VARCHAR(255),
    status ENUM('active','suspended'),
    cpu_quota INT,
    memory_quota INT,
    storage_quota INT,
    request_quota INT,
    created_at TIMESTAMP
);

CREATE TABLE tenant_usage (
    id INT PRIMARY KEY,
    tenant_id INT REFERENCES tenants(id),
    resource_type ENUM('cpu','memory','storage','requests'),
    usage INT,
    last_updated TIMESTAMP
);
```

## Tenant Isolation Strategy
1. **Database Level**:
   - All content tables include tenant_id column
   - Foreign key constraints enforce data integrity
   - Queries automatically filter by current tenant

2. **Validation Rules**:
   - Tenant IDs must match regex: `[a-zA-Z0-9_-]+`
   - Maximum length: 50 characters
   - Empty values rejected

3. **Cross-Tenant Protection**:
   - All queries include tenant_id filter
   - API endpoints validate tenant context
   - Test coverage for edge cases

## Quota Management
Each tenant has configurable limits for:
- CPU usage
- Memory allocation
- Storage space
- API requests

Quotas are enforced through:
1. Middleware checks
2. Periodic monitoring
3. Automatic suspension when exceeded

## API Endpoints
Key tenant-related endpoints:
- `GET /api/migrations/tenant/status` - Get tenant status
- `POST /api/migrations/tenant/migrate` - Run migrations
- `POST /api/migrations/tenant/rollback` - Rollback migrations

## Testing Procedures
Automated tests verify:
1. Basic tenant scoping
2. Invalid tenant ID handling
3. Cross-tenant access prevention
4. Various tenant ID formats

Test cases include:
```php
// Example test case
function testCrossTenantAccess(PDO $pdo): bool {
    // Setup test data for two tenants
    // Verify tenant1 cannot access tenant2's data
    // Return true if isolation works correctly
}
```

## Monitoring
Tenant dashboards show:
- Current resource usage
- Quota percentages
- Migration status
- Performance metrics