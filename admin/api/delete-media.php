<?php
/**
 * Delete Media API - Hardened endpoint
 * POST-only, CSRF-protected, authenticated
 * Deletes from both filesystem and database
 */

define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot();
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/core/models/mediamodel.php';

// Enforce POST-only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => false,
        'error' => 'Method not allowed'
    ]);
    exit;
}

// Validate CSRF token
csrf_validate_or_403();

// Get filename from either JSON body or form data
$basename = null;

// Check form data first (from HTML form submissions)
if (!empty($_POST['file'])) {
    $basename = basename((string) $_POST['file']);
}

// If not in form data, try JSON body
if ($basename === null) {
    $rawBody = file_get_contents('php://input');
    $payload = json_decode($rawBody, true);
    if (is_array($payload) && !empty($payload['basename'])) {
        $basename = basename((string) $payload['basename']);
    } elseif (is_array($payload) && !empty($payload['file'])) {
        $basename = basename((string) $payload['file']);
    }
}

if ($basename === null || $basename === '' || $basename === '.' || $basename === '..') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => false,
        'error' => 'Missing or invalid filename'
    ]);
    exit;
}

// Base uploads directory
$uploadsDir = CMS_ROOT . '/uploads/media';
$targetPath = $uploadsDir . '/' . $basename;

// Resolve real paths and ensure target is inside uploads directory
$realUploadsDir = realpath($uploadsDir);
$realTargetPath = $realUploadsDir !== false ? realpath($targetPath) : false;

if ($realUploadsDir === false) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => false,
        'error' => 'Upload directory not found'
    ]);
    exit;
}

// File must exist in uploads directory (realpath returns false for non-existent files)
if ($realTargetPath === false || strpos($realTargetPath, $realUploadsDir . DIRECTORY_SEPARATOR) !== 0) {
    // Check if file exists in database even if not on disk
    $mediaModel = new MediaModel(db());
    $media = $mediaModel->findByFilename($basename);

    if ($media) {
        // File in database but not on disk - remove from database
        $mediaModel->deleteByFilename($basename);
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'message' => 'Removed from database (file was already missing)']);
        exit;
    }

    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => false,
        'error' => 'File not found'
    ]);
    exit;
}

// Disallow deleting directories or symlinks
if (is_dir($realTargetPath) || is_link($realTargetPath)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => false,
        'error' => 'Invalid target type'
    ]);
    exit;
}

// Delete main file if it exists
if (file_exists($realTargetPath)) {
    if (!@unlink($realTargetPath)) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'ok' => false,
            'error' => 'Failed to delete file'
        ]);
        exit;
    }
}

// Attempt to delete thumbnail if exists
$pathInfo = pathinfo($basename);
$thumbName = $pathInfo['filename'] . '_thumb.jpg';
$thumbPath = CMS_ROOT . '/uploads/media/thumbs/' . $thumbName;

if (file_exists($thumbPath)) {
    @unlink($thumbPath);
}

// Delete from database
$mediaModel = new MediaModel(db());
$mediaModel->deleteByFilename($basename);

// Redirect back to media page for form submissions
if (!empty($_POST['file'])) {
    header('Location: /admin/media.php');
    exit;
}

// Success response for AJAX/API requests
http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['ok' => true]);
