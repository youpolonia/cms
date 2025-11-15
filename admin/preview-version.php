<?php
require_once __DIR__ . '/../core/accesschecker.php';
require_once __DIR__ . '/../core/Sanitizer.php';

// Check admin permissions
$accessChecker = new AccessChecker();
if (!$accessChecker->hasAccess('version_control')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$sanitizer = new Sanitizer();

// Validate input
$contentId = $sanitizer->sanitizeInput($_GET['content_id'] ?? '');
$version = $sanitizer->sanitizeInput($_GET['version'] ?? '');

if (empty($contentId) || empty($version)) {
    header('HTTP/1.0 400 Bad Request');
    exit('Invalid parameters');
}

// Validate path
$versionFile = realpath(__DIR__ . "/../content/{$contentId}/versions/{$version}.html");
if (strpos($versionFile, realpath(__DIR__ . '/../content/')) === false || !file_exists($versionFile)) {
    header('HTTP/1.0 404 Not Found');
    exit('Version not found');
}

// Output the version content
header('Content-Type: text/html');
readfile($versionFile);
