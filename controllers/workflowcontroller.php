<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Workflow Controller for API Endpoints
 */
class WorkflowController {
    // Existing methods (transition, getState, getHistory) remain unchanged

    public function listVersions($request) {
        try {
            $contentId = $request['content_id'] ?? null;
            $tenantId = $request['tenant_id'] ?? null;
            
            if (!$contentId || !$tenantId) {
                throw new Exception("Content ID and Tenant ID required");
            }

            $versionControl = VersionControl::getInstance();
            return $versionControl->getVersions($contentId, $tenantId);
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function approveVersion($request) {
        try {
            $versionId = $request['version_id'] ?? null;
            $userId = $request['user_id'] ?? null;
            $tenantId = $request['tenant_id'] ?? null;
            $comment = $request['comment'] ?? '';

            if (!$versionId || !$userId || !$tenantId) {
                throw new Exception("Version ID, User ID and Tenant ID required");
            }

            $approval = ContentApproval::getInstance();
            $result = $approval->approveVersion($versionId, $userId, $comment, $tenantId);

            return [
                'success' => true,
                'new_state' => $result['state'],
                'approval_id' => $result['approval_id']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function rollbackVersion($request) {
        try {
            $versionId = $request['version_id'] ?? null;
            $userId = $request['user_id'] ?? null;
            $tenantId = $request['tenant_id'] ?? null;
            $reason = $request['reason'] ?? '';

            if (!$versionId || !$userId || !$tenantId) {
                throw new Exception("Version ID, User ID and Tenant ID required");
            }

            $rollback = VersionRollback::getInstance();
            $result = $rollback->execute($versionId, $userId, $reason, $tenantId);

            return [
                'success' => true,
                'rollback_to' => $result['rollback_to'],
                'current_version' => $result['current_version']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getWorkflowDefinitions($request) {
        $tenantId = $request['tenant_id'] ?? null;
        $type = $request['type'] ?? null;

        $workflow = WorkflowEngine::getInstance();
        return $workflow->getDefinitions($tenantId, $type);
    }

    public function createWorkflowDefinition($request) {
        try {
            $definition = $request['definition'] ?? null;
            $tenantId = $request['tenant_id'] ?? null;
            $userId = $request['user_id'] ?? null;

            if (!$definition || !$tenantId || !$userId) {
                throw new Exception("Definition, Tenant ID and User ID required");
            }

            $workflow = WorkflowEngine::getInstance();
            $id = $workflow->createDefinition($definition, $tenantId, $userId);

            return [
                'success' => true,
                'workflow_id' => $id
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
