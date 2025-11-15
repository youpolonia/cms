# Database Migration Troubleshooting Guide

## Common Issues
1. **Pending Migrations Stuck**
   - Symptom: Migrations show as pending but won't execute
   - Solution: Verify database state matches migration expectations

2. **Duplicate Index Errors**  
   - Symptom: "Index already exists" errors on migration
   - Solution: Check `SHOW INDEX` output before creating

## Emergency Recovery

```sql
-- Check current migration state
SELECT * FROM migrations ORDER BY batch DESC, migration DESC LIMIT 10;

-- Manually complete a migration
INSERT INTO migrations (migration, batch) 
VALUES ('migration_name', next_batch_number);

-- Rollback a problematic migration
DELETE FROM migrations WHERE migration = 'failed_migration';
```

## Best Practices Checklist
- [ ] Verify table/column existence before modification
- [ ] Use transactions for complex migrations
- [ ] Test migrations in identical environments
- [ ] Document schema dependencies between migrations