# Phase 9 Database Schema - Tenant Isolation & Status Tracking

## Proposed Changes:
1. Add `tenant_id` column to all tenant-specific tables:
   ```sql
   ALTER TABLE table_name ADD COLUMN tenant_id VARCHAR(36) NOT NULL;
   CREATE INDEX idx_table_name_tenant_id ON table_name(tenant_id);
   ```

2. Create `status_transitions` table:
   ```sql
   CREATE TABLE IF NOT EXISTS status_transitions (
       id INT AUTO_INCREMENT PRIMARY KEY,
       tenant_id VARCHAR(36) NOT NULL,
       entity_type VARCHAR(255) NOT NULL,
       entity_id BIGINT NOT NULL,
       from_status VARCHAR(255) NOT NULL,
       to_status VARCHAR(255) NOT NULL,
       transition_time DATETIME DEFAULT CURRENT_TIMESTAMP,
       reason TEXT,
       FOREIGN KEY (tenant_id) REFERENCES tenants(id)
   );
   ```

3. Tenant isolation rules:
   - All queries must filter by tenant_id
   - Foreign keys must include tenant_id
   - Unique constraints must include tenant_id

## Migration Approach:
1. Create new migration file:
   ```php
   // File: includes/Database/Migrations/2025053001_tenant_isolation.php
   class TenantIsolationMigration {
       public static function up($db) {
           // Schema changes here
       }
       
       public static function down($db) {
           // Rollback procedures
       }
   }
   ```

2. Web test endpoint:
   ```php
   // File: tests/migrations/tenant_isolation_test.php
   // Test migration application and rollback