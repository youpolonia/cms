<?php
namespace api\v1\Controllers;

require_once __DIR__ . '/../../../config.php';

use Database;
use ApprovalService;

class ApprovalWorkflowController
{
    public static function submitForApproval($request)
    {
        $db = \core\Database::connection();
        $approvalId = $db->insert('content_approvals', [
            'content_id' => $request['content_id'],
            'submitter_id' => $request['user_id'],
            'status' => 'pending',
            'notes' => $request['notes'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'approval_id' => $approvalId
        ];
    }

    public static function getApprovalStatus($request)
    {
        $db = \core\Database::connection();
        $approval = $db->selectOne('content_approvals', [
            'id' => $request['approval_id']
        ]);

        return [
            'success' => true,
            'approval' => $approval
        ];
    }

    public static function approveContent($request)
    {
        $db = \core\Database::connection();
        $db->update('content_approvals', [
            'status' => 'approved',
            'approver_id' => $request['user_id'],
            'approved_at' => date('Y-m-d H:i:s'),
            'approval_notes' => $request['notes'] ?? ''
        ], ['id' => $request['approval_id']]);

        ApprovalService::publishContent($request['content_id']);

        return ['success' => true];
    }

    public static function rejectContent($request)
    {
        $db = \core\Database::connection();
        $db->update('content_approvals', [
            'status' => 'rejected',
            'approver_id' => $request['user_id'],
            'rejected_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $request['reason'] ?? ''
        ], ['id' => $request['approval_id']]);

        return ['success' => true];
    }

    public static function getApprovalHistory($request)
    {
        $db = \core\Database::connection();
        $history = $db->select('content_approvals', [
            'content_id' => $request['content_id']
        ], 'created_at DESC');

        return [
            'success' => true,
            'history' => $history
        ];
    }
}
