<?php
// Admin AI Access Control
require_once __DIR__ . '/../../admin/includes/auth.php';

if (!checkAdminAccess('ai_operations')) {
    logSecurityEvent('Unauthorized AI access attempt', $_SERVER['REMOTE_ADDR']);
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Log all AI operations
logSecurityEvent('AI directory accessed', $_SERVER['REMOTE_ADDR'], [
    'user' => $_SESSION['user_id'] ?? 'unknown',
    'path' => $_SERVER['REQUEST_URI']
]);

// Forward to Vue component
header('Location: TranslationInterface.vue');
exit;
