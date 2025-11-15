# Production Deployment Checklist

## Database Migrations
- [ ] Run migration: `2025_05_03_231601_update_ai_prompts_table.php`
  - Adds content_id foreign key to ai_prompts table
  - Adds prompt_type field for categorization  
  - Adds last_used_at timestamp with index
  - All fields are nullable for backward compatibility

## Verification Steps
1. Confirm migration runs successfully in staging
2. Verify no existing functionality is broken
3. Check all new fields are properly nullable
4. Test rollback procedure

## Deployment Instructions
1. Schedule during low-traffic period
2. Run: `php artisan migrate`
3. Monitor for any errors
4. Verify successful completion in logs

## Rollback Plan
If issues occur:
1. Run: `php artisan migrate:rollback --step=1`
2. Verify all changes are reverted
3. Check application functionality