# WorkflowService Deployment Checklist

## Pre-Deployment Verification

1. [ ] Run all tests via PHPUnit
2. [ ] Verify test coverage meets requirements:
   - State transitions (100%)
   - Error cases (100%)
   - Schema handling (100%)
   - Tenant isolation (100%)

## Configuration Changes

1. [ ] Disable debug mode:
   ```php
   const DEBUG_MODE = false;
   ```
2. [ ] Set production audit logging:
   ```php
   const AUDIT_VERBOSITY = [
       'transitions' => true,
       'errors' => true,
       'debug' => false
   ];
   ```

## Post-Deployment Tests

1. [ ] Verify single workflow transition:
   ```bash
   curl -X POST https://example.com/api/workflow/transition \
     -d 'instance_id=test123&action=approve'
   ```
2. [ ] Verify batch transition:
   ```bash
   curl -X POST https://example.com/api/workflow/batch_transition \
     -d 'instance_ids[]=test1&instance_ids[]=test2&action=reject'
   ```

## Emergency Rollback Steps

1. [ ] Restore previous version of:
   - `includes/Services/WorkflowService.php`
   - Database migrations
   - API endpoints
2. [ ] Clear workflow caches:
   ```php
   // Run via admin interface
   WorkflowCache::clearAll();