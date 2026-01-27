<?php
require_once __DIR__ . '/logrotator.php';

header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? '';
    $logFile = $_GET['file'] ?? '';

    switch ($action) {
        case 'rotate':
            if (empty($logFile)) {
                throw new Exception('Log file parameter required');
            }
            $result = LogRotator::rotateIfNeeded($logFile);
            echo json_encode(['success' => $result]);
            break;

        case 'status':
            if (empty($logFile)) {
                throw new Exception('Log file parameter required');
            }
            $size = file_exists($logFile) ? filesize($logFile) : 0;
            $needsRotation = $size >= LogRotator::MAX_SIZE;
            echo json_encode([
                'size' => $size,
                'needs_rotation' => $needsRotation,
                'max_size' => LogRotator::MAX_SIZE
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
