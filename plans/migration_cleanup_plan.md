# Migration Cleanup Implementation Plan

## Phase 1: Method Renaming
1. Create script to:
   - Rename up() → migrate()
   - Rename down() → rollback()
2. Update all 115 affected files
3. Verify:
   - No broken references
   - Web interface still works

## Phase 2: DB Call Replacement
1. Replace all DB:: calls with direct PDO
2. Replace Schema:: calls with raw SQL
3. Verify:
   - All migrations execute successfully
   - Rollbacks work correctly

## Phase 3: Documentation Update
1. Update README.md with new standards
2. Add examples to StandardizedMigrationTemplate.php
3. Verify:
   - Documentation is clear
   - New migrations follow pattern

## Phase 4: Final Verification
1. Test all migrations end-to-end
2. Check web interface functionality
3. Confirm no framework patterns remain

## Rollback Plan
1. Create backup of all modified files
2. Document rollback procedure
3. Test rollback before full deployment