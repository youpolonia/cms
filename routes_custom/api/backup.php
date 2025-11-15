<?php
/**
 * Backup API Endpoint
 * Accessible via: /api/backup/create
 */

require_once __DIR__ . '/../../includes/backup.php';

header('Content-Type: application/json');

try {
    // Authenticate request (basic example)
    if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] !== 'backup' || $_SERVER['PHP_AUTH_PW'] !== 'secure_password') {
        throw new Exception('Unauthorized', 401);
    }

    $backupPath = BackupManager::createBackup();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Backup created successfully',
        'path' => $backupPath,
        'timestamp' => date('c')
    ]);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
}
