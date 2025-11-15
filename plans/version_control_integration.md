# Version Control System Integration Plan

## Integration Components
1. **Workflow Engine Hooks**
   - Pre-modification version creation
   - Post-modification version tracking
   - Restoration capability for undo operations

2. **Tenant Isolation**
   - Add tenant_id to content_versions table
   - Verify tenant checks in all version operations
   - Update queries to include tenant_id filtering

3. **Database Changes**
   ```sql
   ALTER TABLE content_versions ADD COLUMN tenant_id VARCHAR(36) NOT NULL;
   CREATE INDEX idx_content_versions_tenant ON content_versions(tenant_id, content_id);
   ```

4. **Implementation Steps**
   - [ ] Modify VersionController for workflow integration
   - [ ] Update WorkflowEngine with version hooks
   - [ ] Create database migration
   - [ ] Update content_moderation.php config

## Workflow Integration Points
1. **Before Content Modification**
   ```php
   // In WorkflowEngine::executeActions()
   $versionController->createVersion(
       $contentId, 
       $currentContent, 
       $userId,
       false
   );
   ```

2. **After Content Modification**
   ```php
   // Log version changes in restoration_log
   $versionController->logRestoration(
       $versionId,
       $userId,
       'Workflow action: ' . $action['type']
   );
   ```

## Testing Strategy
1. Unit tests for version creation
2. Integration tests with workflow engine
3. Tenant isolation verification
4. Rollback procedure testing

## Timeline
1. Database changes - Day 1
2. VersionController updates - Day 2
3. WorkflowEngine integration - Day 3
4. Testing and deployment - Day 4