# Phase 11 Installation Procedure

## Pre-requisites
- PHP 8.1+
- MySQL 5.7+
- FTP access to server
- Backup of current system

## Deployment Steps

1. **Upload Package**
   - Extract phase11_package_*.zip to temporary directory
   - FTP files to their respective locations:
     - Migrations → database/migrations/
     - API tests → public/api/test/
     - Checklist → deployment/phase11/

2. **Run Migrations**
   - Execute migrations in order:
     1. Migration_0001_TenantIsolation
     2. 0002_add_tenant_columns
     3. 0003_complete_tenant_scope
     4. 0004_cross_site_relations
     5. 0005_tenant_aware_query_builder

3. **Verify Deployment**
   - Run tenant scoping test: /public/api/test/tenant-scoping.php
   - Run cross-site relations test: /public/api/test/cross-site-relations.php
   - Check all items in deployment/phase11/checklist.md

## Rollback Procedure
1. Delete newly added migration files
2. Restore previous versions of modified files
3. Run `DROP TABLE` for any new tables created by migrations