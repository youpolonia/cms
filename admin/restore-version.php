<?php
require_once __DIR__ . '/../core/accesschecker.php';
require_once __DIR__ . '/../core/logger/LoggerFactory.php';
require_once __DIR__ . '/../core/Sanitizer.php';

// Check admin permissions
$accessChecker = new AccessChecker();
if (!$accessChecker->hasAccess('version_control')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Initialize logger and sanitizer
$logger = LoggerFactory::create('file', [
    'file_path' => __DIR__ . '/../logs/version-restore.log'
]);
$sanitizer = new Sanitizer();

// Validate input
$contentId = $sanitizer->sanitizeInput($_GET['content_id'] ?? '');
$version = $sanitizer->sanitizeInput($_GET['version'] ?? '');

if (empty($contentId) || empty($version)) {
    $logger->log("Invalid restore attempt - missing parameters");
    die('Invalid parameters');
}

// Validate paths
$versionFile = realpath(__DIR__ . "/../content/{$contentId}/versions/{$version}.html");
$currentFile = realpath(__DIR__ . "/../content/{$contentId}/current.html");

if (strpos($versionFile, realpath(__DIR__ . '/../content/')) === false || 
    !file_exists($versionFile) ||
    strpos($currentFile, realpath(__DIR__ . '/../content/')) === false) {
    $logger->log("Invalid path attempt for content {$contentId}, version {$version}");
    die('Invalid file path');
}

// Create backup of current version
$backupDir = __DIR__ . "/../content/{$contentId}/backups";
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$backupFile = $backupDir . '/' . time() . '.html';
if (!copy($currentFile, $backupFile)) {
    $logger->log("Backup failed for content {$contentId}");
    die('Backup failed');
}

// Restore version
if (!copy($versionFile, $currentFile)) {
    $logger->log("Restore failed for content {$contentId}, version {$version}");
    die('Restore failed');
}

// Log successful restore
$logger->log("Successfully restored content {$contentId} to version {$version}");

// Redirect back to version control
header('Location: version-control.php?restored=1');
exit;
