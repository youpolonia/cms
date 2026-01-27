<?php
/**
 * TB 4.0 Bundle Server
 * Serves the React bundle with correct MIME type
 */

$bundlePath = __DIR__ . '/assets/css/tb4/dist/tb4.bundle.js';

if (!file_exists($bundlePath)) {
    http_response_code(404);
    exit('Bundle not found');
}

// Set correct headers for JavaScript module
header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: public, max-age=3600');
header('X-Content-Type-Options: nosniff');

// Serve the bundle
readfile($bundlePath);
