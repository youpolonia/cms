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

// Get approval stats
$pending = ApprovalWorkflowController::getPendingCount();
$approved = ApprovalWorkflowController::getApprovedCount();
$rejected = ApprovalWorkflowController::getRejectedCount();

?><!DOCTYPE html>
<html>
<head>
    <title>Approval Dashboard</title>
    <link rel="stylesheet" href="/admin/css/approval.css">
</head>
<body>
    <div class="approval-dashboard">
        <h1>Content Approval Dashboard</h1>
        
        <div class="stats-container">
            <div class="stat-card pending">
                <h3>Pending</h3>
                <div class="stat-value"><?= $pending ?></div>
            </div>
            
            <div class="stat-card approved">
                <h3>Approved</h3>
                <div class="stat-value"><?= $approved ?></div>
            </div>
            
            <div class="stat-card rejected">
                <h3>Rejected</h3>
                <div class="stat-value"><?= $rejected ?></div>
            </div>
        </div>
    </div>

    <script src="/admin/js/approval_dashboard.js"></script>
</body>
</html>
