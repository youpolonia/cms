<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/session_boot.php';
cms_session_start('admin');

// RBAC: Require admin access
require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();
require_once __DIR__ . '/../../core/csrf.php';
csrf_validate_or_403();
require_once __DIR__ . '/../includes/flashmessage.php';
require_once __DIR__ . '/../../core/extensions_integrity.php';

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

// Check extension exists
$root = defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__, 2);
$extDir = $root . '/extensions/' . $slug;
if (!is_dir($extDir)) {
    if (class_exists('FlashMessage')) {
        FlashMessage::add('Extension not found', FlashMessage::TYPE_ERROR);
    } else {
        $_SESSION['flash_messages'][] = ['type' => 'error', 'text' => 'Extension not found'];
    }
    header('Location: index.php', true, 303);
    exit;
}

// Handle actions
if ($action === 'build') {
    $result = ext_integrity_build($slug);
    
    if ($result['ok']) {
        $fileCount = count($result['files']);
        if (class_exists('FlashMessage')) {
            FlashMessage::add("Integrity baseline built for '$slug' ($fileCount files)", FlashMessage::TYPE_SUCCESS);
        } else {
            $_SESSION['flash_messages'][] = ['type' => 'success', 'text' => "Integrity baseline built for '$slug' ($fileCount files)"];
        }
    } else {
        $error = $result['error'] ?? 'unknown_error';
        if (class_exists('FlashMessage')) {
            FlashMessage::add("Failed to build baseline for '$slug': $error", FlashMessage::TYPE_ERROR);
        } else {
            $_SESSION['flash_messages'][] = ['type' => 'error', 'text' => "Failed to build baseline for '$slug': $error"];
        }
    }
    
} elseif ($action === 'check') {
    $result = ext_integrity_check($slug);
    
    if ($result['ok']) {
        if (class_exists('FlashMessage')) {
            FlashMessage::add("Integrity check passed for '$slug'", FlashMessage::TYPE_SUCCESS);
        } else {
            $_SESSION['flash_messages'][] = ['type' => 'success', 'text' => "Integrity check passed for '$slug'"];
        }
    } else {
        $issues = [];
        if (isset($result['mismatch']) && !empty($result['mismatch'])) {
            $issues[] = count($result['mismatch']) . ' modified';
        }
        if (isset($result['missing']) && !empty($result['missing'])) {
            $issues[] = count($result['missing']) . ' missing';
        }
        if (isset($result['extra']) && !empty($result['extra'])) {
            $issues[] = count($result['extra']) . ' extra';
        }
        
        if (!empty($issues)) {
            $summary = implode(', ', $issues);
            if (class_exists('FlashMessage')) {
                FlashMessage::add("Integrity check failed for '$slug': $summary", FlashMessage::TYPE_ERROR);
            } else {
                $_SESSION['flash_messages'][] = ['type' => 'error', 'text' => "Integrity check failed for '$slug': $summary"];
            }
        } else {
            $error = $result['error'] ?? 'unknown_error';
            if (class_exists('FlashMessage')) {
                FlashMessage::add("Integrity check failed for '$slug': $error", FlashMessage::TYPE_ERROR);
            } else {
                $_SESSION['flash_messages'][] = ['type' => 'error', 'text' => "Integrity check failed for '$slug': $error"];
            }
        }
    }
    
} else {
    if (class_exists('FlashMessage')) {
        FlashMessage::add('Invalid action', FlashMessage::TYPE_ERROR);
    } else {
        $_SESSION['flash_messages'][] = ['type' => 'error', 'text' => 'Invalid action'];
    }
}

header('Location: index.php', true, 303);
exit;
