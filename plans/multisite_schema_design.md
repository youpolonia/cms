# Multisite Schema Design Proposal

## Schema Changes

### Core Tables Requiring Tenant Isolation

1. **Users & Authentication**
   - `users` (add tenant_id)
   - `user_roles` (add tenant_id)
   - `user_permissions` (add tenant_id)
   - `sessions` (add tenant_id)

2. **Content Management**
   - `content` (add tenant_id)
   - `content_types` (add tenant_id)
   - `content_fields` (add tenant_id)
   - `content_versions` (add tenant_id)

3. **Workflow Tables** (from existing plan)
   - All 13 workflow tables (add tenant_id)

4. **Settings Tables** (from existing plan)
   - `settings` (add tenant_id, site_id)
   - `application_settings` (add tenant_id, site_id)

### Column Specifications
```sql
tenant_id CHAR(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000' COMMENT 'UUID for tenant isolation',
```

### Indexing Strategy
1. Composite indexes on all tenant_id + primary key combinations
2. Additional indexes for common query patterns:
   - `(tenant_id, user_id)`
   - `(tenant_id, content_id)`
   - `(tenant_id, workflow_id)`

### Foreign Key Relationships
```sql
ALTER TABLE content
ADD CONSTRAINT fk_content_tenant
FOREIGN KEY (tenant_id) REFERENCES tenants(id)
ON DELETE CASCADE;
```

## Versioning & Rollback
1. Create backup tables with `_backup` suffix before migrations
2. Use transaction blocks for all schema changes
3. Implement versioned migration files (YYYYMMDD_description.php)

## Test Data Template
```json
{
  "tenant": {
    "id": "11111111-1111-1111-1111-111111111111",
    "name": "Test Tenant"
  },
  "users": [
    {
      "id": 1,
      "tenant_id": "11111111-1111-1111-1111-111111111111",
      "username": "admin"
    }
  ]
}
```

## Implementation Plan
1. Phase 1: Core tables (users, content)
2. Phase 2: Workflow tables
3. Phase 3: Settings tables
4. Phase 4: Remaining tables