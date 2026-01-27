<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/session_boot.php';
cms_session_start('admin');

// RBAC: Require admin access
require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();
require_once __DIR__ . '/../../core/csrf.php';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { csrf_validate_or_403(); }
require_once __DIR__ . '/../../core/extensions_state.php';
require_once __DIR__ . '/../includes/flashmessage.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$slug = trim((string)($_POST['slug'] ?? ''));

// Validate slug
if (!preg_match('/^[a-z0-9_\-]{3,64}$/', $slug)) {
    if (class_exists('FlashMessage')) {
        FlashMessage::add('Invalid extension slug', FlashMessage::TYPE_ERROR);
    } else {
        $_SESSION['flash_messages'][] = ['type' => 'error', 'text' => 'Invalid extension slug'];
    }
    header('Location: index.php', true, 303);
    exit;
}

// Resolve paths
$EXT_DIR = realpath(__DIR__ . '/../../extensions');
if ($EXT_DIR === false) {
    if (class_exists('FlashMessage')) {
        FlashMessage::add('Extensions directory not found', FlashMessage::TYPE_ERROR);
    } else {
        $_SESSION['flash_messages'][] = ['type' => 'error', 'text' => 'Extensions directory not found'];
    }
    header('Location: index.php', true, 303);
    exit;
}

$target = $EXT_DIR . '/' . $slug;
$targetReal = realpath($target);

if ($targetReal === false || strpos($targetReal . '/', $EXT_DIR . '/') !== 0) {
    if (class_exists('FlashMessage')) {
        FlashMessage::add('Extension not found or invalid path', FlashMessage::TYPE_ERROR);
    } else {
        $_SESSION['flash_messages'][] = ['type' => 'error', 'text' => 'Extension not found or invalid path'];
    }
    header('Location: index.php', true, 303);
    exit;
}

// Safe recursive delete function
function safe_recursive_delete(string $path): bool {
    if (!file_exists($path)) {
        return true;
    }
    
    // Handle symlinks - remove link only, do not traverse
    if (is_link($path)) {
        return @unlink($path);
    }
    
    if (is_file($path)) {
        return @unlink($path);
    }
    
    if (is_dir($path)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            $filePath = $file->getPathname();
            
            // Handle symlinks - remove link only, do not traverse
            if (is_link($filePath)) {
                if (!@unlink($filePath)) {
                    return false;
                }
            } elseif ($file->isDir()) {
                if (!@rmdir($filePath)) {
                    return false;
                }
            } else {
                if (!@unlink($filePath)) {
                    return false;
                }
            }
        }
        
        return @rmdir($path);
    }
    
    return false;
}

// Attempt deletion
$deleteSuccess = safe_recursive_delete($targetReal);

if (!$deleteSuccess) {
    if (class_exists('FlashMessage')) {
        FlashMessage::add("Failed to delete extension: $slug", FlashMessage::TYPE_ERROR);
    } else {
        $_SESSION['flash_messages'][] = ['type' => 'error', 'text' => "Failed to delete extension: $slug"];
    }
    
    // Log failure
    $logDir = __DIR__ . '/../../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/extensions.log';
    
    $logEntry = json_encode([
        'ts' => gmdate('c'),
        'event' => 'extension_uninstall_failed',
        'slug' => $slug,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'user' => $_SESSION['admin_username'] ?? $_SESSION['user']['username'] ?? 'admin',
        'error' => 'delete_failed'
    ], JSON_UNESCAPED_SLASHES);
    
    @file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
    
    header('Location: index.php', true, 303);
    exit;
}

// Update state via centralized API - remove extension from state
$state = ext_state_load();
unset($state[$slug]);
$stateSuccess = ext_state_save($state);

if (!$stateSuccess) {
    if (class_exists('FlashMessage')) {
        FlashMessage::add('Failed to update extension state', FlashMessage::TYPE_ERROR);
    } else {
        $_SESSION['flash_messages'][] = ['type' => 'error', 'text' => 'Failed to update extension state'];
    }
    header('Location: index.php', true, 303);
    exit;
}

// Log success
$logDir = __DIR__ . '/../../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/extensions.log';

$logEntry = json_encode([
    'ts' => gmdate('c'),
    'event' => 'extension_uninstall_ok',
    'slug' => $slug,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
    'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'user' => $_SESSION['admin_username'] ?? $_SESSION['user']['username'] ?? 'admin'
], JSON_UNESCAPED_SLASHES);

@file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);

// Success flash message
if (class_exists('FlashMessage')) {
    FlashMessage::add("Uninstalled: $slug", FlashMessage::TYPE_SUCCESS);
} else {
    $_SESSION['flash_messages'][] = ['type' => 'success', 'text' => "Uninstalled: $slug"];
}

header('Location: index.php', true, 303);
exit;
