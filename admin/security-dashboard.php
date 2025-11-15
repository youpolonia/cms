<?php
require_once __DIR__ . '/../includes/security/SecurityAuditor.php';
require_once __DIR__ . '/components/securitydashboard.php';

header('Content-Type: text/html; charset=utf-8');

?><!DOCTYPE html>
<html>
<head>
    <title>Security Dashboard</title>
    <link rel="stylesheet" href="assets/css/security-dashboard.css">
</head>
<body>
<?php

$dashboard = new SecurityDashboard([
    'scan_interval' => 3600, // Hourly scans
    'alert_threshold' => 'critical'
]);

// Handle n8n webhook requests
if (isset($_GET['n8n_webhook'])) {
    $dashboard->scheduleScans();
    exit;
}

echo $dashboard->renderDashboard();

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'init_scan':
            $response = Security\SecurityAuditorController::handleRequest([
                'action' => 'start_scan',
                'scan_type' => $_GET['scan_type'] ?? ''
            ]);
            break;
        case 'get_results':
            $response = Security\SecurityAuditorController::handleRequest([
                'action' => 'get_results',
                'scan_id' => $_GET['scan_id'] ?? ''
            ]);
            break;
        case 'list_scans':
            $response = Security\SecurityAuditorController::handleRequest([
                'action' => 'list_scans'
            ]);
            break;
        case 'list_versions':
            $contentId = filter_input(INPUT_GET, 'content_id', FILTER_VALIDATE_INT);
            if (!$contentId) {
                throw new InvalidArgumentException('Invalid content ID');
            }
            $response = [
                'status' => 'success',
                'versions' => ContentVersioningSystem::getInstance()->listVersions($contentId)
            ];
            break;
        default:
            $response = ['status' => 'success'];
    }
} catch (\Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
