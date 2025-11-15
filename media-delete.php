<?php
// Media deletion handler - Secure file deletion with admin verification
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/services/auditlogger.php';

// Verify admin permissions
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = 'Unauthorized: Admin access required';
    header('Location: /admin/media-manager.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token'])) {
        log_action('CSRF token missing', 'security');
        $_SESSION['error'] = 'Invalid request';
        header('Location: /admin/media-manager.php');
        exit;
    }

    // Verify CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        log_action('CSRF token mismatch', 'security');
        $_SESSION['error'] = 'Invalid request';
        header('Location: /admin/media-manager.php');
        exit;
    }

    // Validate and sanitize file path
    if (empty($_POST['file_path'])) {
        $_SESSION['error'] = 'No file specified';
        header('Location: /admin/media-manager.php');
        exit;
    }

    // Sanitize and validate file path
    $basePath = realpath(__DIR__ . '/media') . DIRECTORY_SEPARATOR;
    $filePath = realpath($basePath . $_POST['file_path']);

    // Security checks
    if ($filePath === false || strpos($filePath, $basePath) !== 0) {
        log_action('Invalid file path attempt: ' . $_POST['file_path'], 'security');
        $_SESSION['error'] = 'Invalid file path';
        header('Location: /admin/media-manager.php');
        exit;
    }

    // Verify file exists and is readable
    if (!is_file($filePath) || !is_readable($filePath)) {
        $_SESSION['error'] = 'File not found or inaccessible';
        header('Location: /admin/media-manager.php');
        exit;
    }

    try {
        // Attempt file deletion
        if (unlink($filePath)) {
            // Log successful deletion
            $logMessage = sprintf(
                "Media deleted by %s: %s (%s bytes)",
                $_SESSION['user_id'],
                basename($filePath),
                filesize($filePath)
            );
            log_action($logMessage, 'media-delete');

            $_SESSION['success'] = 'File deleted successfully';
            header('Location: /admin/media-manager.php');
            exit;
        } else {
            throw new Exception('Failed to delete file');
        }
    } catch (Exception $e) {
        // Log deletion failure
        $logMessage = sprintf(
            "Failed to delete media by %s: %s - %s",
            $_SESSION['user_id'],
            basename($filePath),
            $e->getMessage()
        );
        log_action($logMessage, 'media-error');

        $_SESSION['error'] = 'Failed to delete file: ' . $e->getMessage();
        header('Location: /admin/media-manager.php');
        exit;
    }
} else {
    // Invalid request method
    $_SESSION['error'] = 'Invalid request method';
    header('Location: /admin/media-manager.php');
    exit;
}

/**
 * Logs actions to the media-actions log file
 */
function log_action(string $message, string $category = 'info'): void {
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/media-actions.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = sprintf(
        "[%s] [%s] %s\n",
        $timestamp,
        strtoupper($category),
        $message
    );

    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}
