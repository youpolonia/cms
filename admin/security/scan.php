<?php
require_once __DIR__ . '/../../includes/security/securityauditor.php';
require_once __DIR__ . '/../../includes/auth/check_auth.php';
require_once __DIR__ . '/../../core/csrf.php';

header('Content-Type: application/json');

// Verify CSRF token
if (!verify_csrf_token()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Check admin permissions
if (!has_permission('security_dashboard')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

try {
    $auditor = new SecurityAuditor();
    $results = $auditor->audit();
    
    // Log the scan
    file_put_contents(
        '../../logs/security_scans.log', 
        date('Y-m-d H:i:s') . " - Scan completed with " . count($results) . " findings\n",
        FILE_APPEND
    );

    echo json_encode([
        'success' => true,
        'count' => count($results),
        'critical' => array_reduce($results, function($carry, $item) {
            return $carry + ($item['severity'] === 'critical' ? 1 : 0);
        }, 0)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Scan failed: ' . $e->getMessage()
    ]);
}
