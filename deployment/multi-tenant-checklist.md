# Multi-Tenant Deployment Checklist

## Pre-Deployment
- [ ] Verify all tenant-aware migrations are complete
- [ ] Test tenant isolation in staging environment
- [ ] Validate database connection pooling settings
- [ ] Confirm backup procedures are in place

## Deployment Steps
1. Run tenant initialization script: `php scripts/tenant_init.php`
2. Deploy codebase to all web servers
3. Verify shared storage mounts
4. Test tenant-specific configurations
5. Validate cross-tenant data isolation

## Post-Deployment
- [ ] Monitor resource usage per tenant
- [ ] Verify tenant-specific cron jobs
- [ ] Test tenant onboarding process
- [ ] Document any tenant-specific customizations

## Rollback Procedure
1. Restore database from backup
2. Revert code deployment
3. Disable new tenant onboarding
4. Notify affected tenants