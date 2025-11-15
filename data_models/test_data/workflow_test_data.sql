-- Workflow Test Data
-- Tenant: test_tenant_1

-- Approval Levels
INSERT INTO approval_levels (id, name, required_approvals, tenant_id) VALUES
('level_1', 'First Review', 1, 'test_tenant_1'),
('level_2', 'Manager Approval', 1, 'test_tenant_1'),
('level_3', 'Final Approval', 1, 'test_tenant_1');

-- Workflow Instances
INSERT INTO approval_instances (id, content_id, current_level, status, tenant_id) VALUES
('instance_1', 'content_1', 'level_1', 'pending', 'test_tenant_1'),
('instance_2', 'content_2', 'level_2', 'approved', 'test_tenant_1'),
('instance_3', 'content_3', 'level_1', 'rejected', 'test_tenant_1');

-- Workflow History
INSERT INTO workflow_history (instance_id, old_level, new_level, old_status, new_status, tenant_id) VALUES
('instance_2', 'level_1', 'level_2', 'pending', 'approved', 'test_tenant_1'),
('instance_3', 'level_1', 'level_1', 'pending', 'rejected', 'test_tenant_1');