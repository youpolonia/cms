<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/session_boot.php';
cms_session_start('admin');

// RBAC: Require admin access
require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/../../core/extensions_state.php';
require_once __DIR__ . '/../includes/flashmessage.php';
csrf_validate_or_403();
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$slug = trim((string)($_POST['slug'] ?? ''));
$action = trim((string)($_POST['action'] ?? ''));

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

// Ensure extension directory exists
$extDir = CMS_ROOT . '/extensions/' . $slug;
if (!is_dir($extDir)) {
    if (class_exists('FlashMessage')) {
        FlashMessage::add('Extension not found', FlashMessage::TYPE_ERROR);
    } else {
        $_SESSION['flash_messages'][] = ['type' => 'error', 'text' => 'Extension not found'];
    }
    header('Location: index.php', true, 303);
    exit;
}

// Validate action
if (!in_array($action, ['enable', 'disable'], true)) {
    if (class_exists('FlashMessage')) {
        FlashMessage::add('Invalid action', FlashMessage::TYPE_ERROR);
    } else {
        $_SESSION['flash_messages'][] = ['type' => 'error', 'text' => 'Invalid action'];
    }
    header('Location: index.php', true, 303);
    exit;
}

// Update state via centralized API
$success = ext_set_enabled($slug, $action === 'enable');
if (!$success) {
    if (class_exists('FlashMessage')) {
        FlashMessage::add('Failed to update extension state', FlashMessage::TYPE_ERROR);
    } else {
        $_SESSION['flash_messages'][] = ['type' => 'error', 'text' => 'Failed to update extension state'];
    }
    header('Location: index.php', true, 303);
    exit;
}

// Log event
$logDir = CMS_ROOT . '/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/extensions.log';
$eventType = $action === 'enable' ? 'extension_enable' : 'extension_disable';

$logEntry = json_encode([
    'timestamp' => gmdate('c'),
    'event' => $eventType,
    'slug' => $slug,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
    'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'user' => $_SESSION['admin_username'] ?? $_SESSION['user']['username'] ?? 'admin'
], JSON_UNESCAPED_SLASHES);

@file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);

// Set flash message
$message = $action === 'enable' ? "Extension '{$slug}' enabled" : "Extension '{$slug}' disabled";
if (class_exists('FlashMessage')) {
    FlashMessage::add($message, FlashMessage::TYPE_SUCCESS);
} else {
    $_SESSION['flash_messages'][] = ['type' => 'success', 'text' => $message];
}

// Redirect back
header('Location: index.php', true, 303);
exit;
