<?php
require_once __DIR__ . '/../../includes/export/csvexporter.php';
require_once __DIR__ . '/excelexporter.php';

// Method guard: GET only
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    exit;
}

// Validate input
if (!isset($_GET['file'])) {
    http_response_code(400);
    exit;
}

$filename = $_GET['file'];

// Strict filename validation
if (!preg_match('/^[A-Za-z0-9._-]+$/', $filename)) {
    http_response_code(400);
    exit;
}

// Validate extension
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
if (!in_array($extension, ['pdf', 'csv', 'xlsx', 'txt'])) {
    http_response_code(400);
    exit;
}

// Secure path resolution
require_once __DIR__ . '/../../core/tmp_sandbox.php';
$base = realpath(cms_tmp_dir());
if (!$base) {
    http_response_code(404);
    exit;
}

$path = realpath($base . '/' . $filename);
if ($path === false || strncmp($path, $base, strlen($base)) !== 0) {
    http_response_code(404);
    exit;
}

// Additional security checks
if (!file_exists($path) || !is_readable($path)) {
    http_response_code(404);
    exit;
}

// Send file using appropriate exporter
try {
    if ($extension === 'csv') {
        $exporter = new CsvExporter();
        $exporter->sendHeaders($filename);
        readfile($path);
    } elseif ($extension === 'xlsx') {
        $exporter = new ExcelExporter();
        if ($exporter->validateFile($path)) {
            $exporter->sendHeaders($filename);
            readfile($path);
        } else {
            error_log('Invalid Excel file: ' . $filename);
            http_response_code(404);
            exit;
        }
    } else {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($path);
    }
} catch (Exception $e) {
    error_log('Download error: ' . $e->getMessage());
    http_response_code(500);
    exit;
}

// Clean up
unlink($path);
exit;
