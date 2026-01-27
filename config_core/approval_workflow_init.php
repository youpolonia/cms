<?php
declare(strict_types=1);

/**
 * Initializes and registers the ApprovalWorkflowService
 */

// Get database connection from container
$db = $container->get('database');

// Create and register service
$container->set('approval_workflow', function() use ($db) {
    return new ApprovalWorkflowService($db);
});

// Document integration points
$container->get('documentation')->addServiceIntegration(
    'approval_workflow',
    [
        'depends_on' => ['database', 'content_versioning'],
        'provides' => [
            'content_approval_workflow',
            'approval_history_tracking',
            'pending_approvals_list'
        ],
        'version' => '1.0.0'
    ]
);
