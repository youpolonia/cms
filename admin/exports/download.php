<?php
/**
 * Secure Download Endpoint for Data Exports
 */

// session boot (admin)
require_once __DIR__ . '/../../core/session_boot.php';

cms_session_start('admin');

// Check token exists and is valid
if (empty($_GET['token']) || !isset($_SESSION['export_token_'.$_GET['token']])) {
    http_response_code(401);
    die('Invalid or expired download token');
}

$exportData = $_SESSION['export_token_'.$_GET['token']];

// Check token expiration
if (time() > $exportData['expires']) {
    unset($_SESSION['export_token_'.$_GET['token']]);
    http_response_code(401);
    die('Download token has expired');
}

// Set appropriate headers based on format
switch ($exportData['format']) {
    case 'json':
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="export_'.date('Y-m-d').'.json"');
        break;
    case 'xml':
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="export_'.date('Y-m-d').'.xml"');
        break;
    default:
        http_response_code(400);
        die('Unsupported export format');
}

// Apply AI enhancements if requested
if (!empty($exportData['enhance'])) {
    try {
        require_once __DIR__.'/../../services/aiexportenhancer.php';
        $exportData['data'] = AIExportEnhancer::enhance(
            $exportData['data'],
            $exportData['enhance']['strategy']
        );
    } catch (Exception $e) {
        error_log('AI enhancement failed: '.$e->getMessage());
        // Continue with original data
    }
}

// Output the data
echo $exportData['data'];

// Clean up
unset($_SESSION['export_token_'.$_GET['token']]);

// Clean expired tokens periodically (1% chance)
if (rand(1, 100) === 1) {
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'export_token_') === 0 && isset($value['expires']) && time() > $value['expires']) {
            unset($_SESSION[$key]);
        }
    }
}
