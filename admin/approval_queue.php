<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../api/v1/includes/api_error_handler.php';
require_once __DIR__ . '/../api/v1/includes/tenant_identification.php';
require_once __DIR__ . '/../api/v1/controllers/approvalworkflowcontroller.php';

// Check permissions
if (!has_permission('content_view')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Get pending approvals
$pendingApprovals = ApprovalWorkflowController::getPendingApprovals();

?><!DOCTYPE html>
<html>
<head>
    <title>Approval Queue</title>
    <link rel="stylesheet" href="/admin/css/approval.css">
</head>
<body>
    <div class="approval-queue">
        <h1>Content Approval Queue</h1>
        
        <table class="approval-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Content Title</th>
                    <th>Submitter</th>
                    <th>Submitted At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingApprovals as $approval): ?>
<tr>
                    <td><?= htmlspecialchars($approval['id']) ?></td>
                    <td><?= htmlspecialchars($approval['content_title']) ?></td>
                    <td><?= htmlspecialchars($approval['submitter_name']) ?></td>
                    <td><?= date('Y-m-d H:i', strtotime($approval['created_at'])) ?></td>
                    <td>
                        <button class="approve-btn" data-id="<?= $approval['id'] ?>">Approve</button>
                        <button class="reject-btn" data-id="<?= $approval['id'] ?>">Reject</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<script src="/admin/js/approval_queue.js"></script>
</body>
</html>
