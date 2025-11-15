<?php
require_once __DIR__ . '/../../includes/security/securityauditor.php';
require_once __DIR__ . '/../../includes/auth/check_auth.php';
require_once __DIR__ . '/../../core/csrf.php';

// Verify CSRF token from GET parameter
if (!verify_csrf_token($_GET['token'] ?? '')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Invalid CSRF token');
}

// Check admin permissions
if (!has_permission('security_dashboard')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

try {
    $auditor = new SecurityAuditor();
    $auditor->audit(); // Run scan first
    $report = $auditor->generateReport();

    // Set headers for download
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="security_report_' . date('Y-m-d') . '.txt"');
    header('Content-Length: ' . strlen($report));
    
    echo $report;
} catch (\Throwable $e) {
    header('HTTP/1.0 500 Internal Server Error');
    error_log($e->getMessage());
    echo 'Failed to generate report';
}
