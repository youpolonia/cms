<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session

require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');

// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../api/v1/includes/api_error_handler.php';
require_once __DIR__ . '/../api/v1/includes/tenant_identification.php';
require_once __DIR__ . '/../api/v1/controllers/approvalworkflowcontroller.php';

// Check permissions
if (!has_permission('content_view')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Get approval history
$history = ApprovalWorkflowController::getApprovalHistory();

?><!DOCTYPE html>
<html>
<head>
    <title>Approval History</title>
    <link rel="stylesheet" href="/admin/css/approval.css">
</head>
<body>
    <div class="approval-history">
        <h1>Content Approval History</h1>
        
        <div class="filters">
            <select id="status-filter">
                <option value="all">All Statuses</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <input type="date" id="date-filter">
        </div>

        <table class="history-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Content</th>
                    <th>Status</th>
                    <th>Submitter</th>
                    <th>Approver</th>
                    <th>Date</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $item): ?>
<tr class="status-<?= $item['status'] ?>">
                    <td><?= htmlspecialchars($item['id']) ?></td>
                    <td><?= htmlspecialchars($item['content_title']) ?></td>
                    <td><?= ucfirst($item['status']) ?></td>
                    <td><?= htmlspecialchars($item['submitter_name']) ?></td>
                    <td><?= htmlspecialchars($item['approver_name'] ?? 'N/A') ?></td>
                    <td><?= date('Y-m-d H:i', strtotime($item['updated_at'])) ?></td>
                    <td><?= htmlspecialchars($item['notes']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<script src="/admin/js/approval_history.js"></script>
</body>
</html>
