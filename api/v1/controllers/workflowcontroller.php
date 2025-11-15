<?php
namespace api\v1\Controllers;

require_once __DIR__ . '/../../../config.php';

use Database;
use WorkflowEngine;

class WorkflowController
{
    public static function createWorkflow($request)
    {
        $db = \core\Database::connection();
        $workflowId = $db->insert('workflows', [
            'name' => $request['name'],
            'description' => $request['description'] ?? '',
            'status' => 'draft',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'workflow_id' => $workflowId
        ];
    }

    public static function updateWorkflow($request)
    {
        $db = \core\Database::connection();
        $db->update('workflows', [
            'name' => $request['name'],
            'description' => $request['description'] ?? '',
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $request['workflow_id']]);

        return ['success' => true];
    }

    public static function executeWorkflow($request)
    {
        $engine = WorkflowEngine::getInstance();
        $output = $engine->executeWorkflow($request['workflow_id'], $request['input_data'] ?? []);

        return [
            'success' => true,
            'output' => $output
        ];
    }

    public static function getWorkflowStatus($request)
    {
        $engine = WorkflowEngine::getInstance();
        $status = $engine->getWorkflowStatus($request['workflow_id']);

        return [
            'success' => true,
            'status' => $status
        ];
    }

    public static function listWorkflows($request)
    {
        $db = \core\Database::connection();
        $workflows = $db->query("SELECT * FROM workflows")->fetchAll();

        return [
            'success' => true,
            'workflows' => $workflows
        ];
    }

    public static function recordStatusTransition($request)
    {
        $db = \core\Database::connection();
        
        // Validate required fields
        if (empty($request['entity_type']) || empty($request['entity_id']) || 
            empty($request['from_status']) || empty($request['to_status'])) {
            return [
                'success' => false,
                'error' => 'Missing required fields',
                'code' => 400
            ];
        }

        try {
            $db->beginTransaction();
            
            $transitionId = $db->insert('status_transitions', [
                'entity_type' => $request['entity_type'],
                'entity_id' => $request['entity_id'],
                'from_status' => $request['from_status'],
                'to_status' => $request['to_status'],
                'reason' => $request['reason'] ?? '',
                'transition_time' => date('Y-m-d H:i:s')
            ]);

            $db->commit();

            return [
                'success' => true,
                'transition_id' => $transitionId,
                'timestamp' => date('c')
            ];
        } catch (\Exception $e) {
            $db->rollBack();
            return [
                'success' => false,
                'error' => 'Failed to record status transition',
                'code' => 500
            ];
        }
    }

    public static function getStatusHistory($request)
    {
        $db = \core\Database::connection();
        
        // Validate required fields
        if (empty($request['entity_type']) || empty($request['entity_id'])) {
            return [
                'success' => false,
                'error' => 'Missing required fields: entity_type and entity_id',
                'code' => 400
            ];
        }

        $limit = isset($request['limit']) ? (int)$request['limit'] : 10;
        $limit = min($limit, 100); // Cap at 100 records

        $transitions = $db->query(
            "SELECT * FROM status_transitions 
             WHERE entity_type = ? AND entity_id = ? 
             ORDER BY transition_time DESC 
             LIMIT ?",
            [$request['entity_type'], $request['entity_id'], $limit]
        )->fetchAll();

        return [
            'success' => true,
            'transitions' => $transitions
        ];
    }
}
