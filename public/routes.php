<?php
require_once __DIR__ . '/../core/bootstrap.php';
// Core dependencies
require_once __DIR__ . '/../core/router.php';
require_once __DIR__ . '/../modules/content/services/templaterenderer.php';
require_once __DIR__ . '/../services/ContentService.php';

// Content module
require_once __DIR__ . '/../modules/content/contentmodule.php';

// Initialize content module
ContentModule::init();

// Get request URI
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($requestUri, '/');

// Dispatch the request
try {
    ContentModule::dispatch($path);
} catch (Exception $e) {
    error_log("Route error: " . $e->getMessage());
    http_response_code(500);
    require_once __DIR__ . '/../views/errors/500.php';
}
