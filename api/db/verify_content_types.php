<?php
// Temporary endpoint to verify content_types table

if (!defined('DEV_MODE')) {
    http_response_code(500);
    echo '{"error":"Configuration error"}';
    return;
}
if (!DEV_MODE) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo '{"error":"Forbidden in production"}';
    return;
}

header('Content-Type: application/json');

// Verify temp file exists and is readable
$temp_file = __DIR__ . '/../../../database/migrations/temp_verify_content_types.php';
if (!file_exists($temp_file) || !is_readable($temp_file)) {
    http_response_code(500);
    die('{"error":"Verification script missing or inaccessible"}');
}

$base = realpath(__DIR__ . '/../../../database/migrations');
$target = realpath($temp_file);
if (!$target || !str_starts_with($target, $base . DIRECTORY_SEPARATOR) || !is_file($target)) {
    error_log("SECURITY: blocked dynamic include: temp_verify_content_types.php");
    http_response_code(400);
    die('{"error":"Invalid file path"}');
}
require_once $target;

// Output results
$results_file = __DIR__ . '/../../../memory-bank/content_types_verification.md';
if (file_exists($results_file) && is_readable($results_file)) {
    $content = file_get_contents($results_file);
    echo '{"status":"success","data":' . json_encode($content) . '}';
} else {
    http_response_code(404);
    echo '{"error":"Verification results not found or unreadable"}';
}
