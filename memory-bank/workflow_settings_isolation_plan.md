# Workflow & Settings Table Isolation Plan

## Identified Tables Requiring Tenant Isolation

### Workflow Tables (Shared Across Tenants)
1. `workflow_definitions`
2. `workflow_instances` 
3. `workflow_history`
4. `workflow_actions`
5. `workflow_events`
6. `workflow_monitoring`
7. `workflow_notifications`
8. `workflow_webhooks`
9. `workflow_variables`
10. `workflow_conditions`
11. `workflow_triggers`
12. `workflow_transitions`
13. `workflow_variable_history`

### Settings Tables (Site-Specific)
1. `settings`
2. `application_settings`

## Isolation Strategy

### Workflow Tables
- Add `tenant_id` column (UUID) to all tables
- Modify foreign keys to include tenant_id in relationships
- Update indexes to include tenant_id
- Add tenant_id to all queries

### Settings Tables
- Add both `site_id` and `tenant_id` columns
- `site_id` for site-specific settings
- `tenant_id` for shared configuration
- Create composite indexes on (tenant_id, site_id)

## Implementation Steps

1. Create migration for each table group
2. Add tenant_id columns with proper constraints
3. Update foreign key relationships
4. Modify indexes
5. Update all queries to include tenant context
6. Create test endpoints for verification

## Migration Priority Order
1. Core workflow tables (definitions, instances, history)
2. Supporting workflow tables (actions, events, monitoring)
3. Settings tables
4. Remaining workflow tables

## Verification Checklist
- [ ] All tables have tenant_id column
- [ ] Foreign keys updated
- [ ] Indexes optimized
- [ ] Queries include tenant context
- [ ] Test endpoints created