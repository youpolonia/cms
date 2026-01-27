<?php
require_once __DIR__ . '/../../core/bootstrap.php';
/**
 * Static Page Handler
 * Routes: /static/[page-slug]
 */

require_once __DIR__.'/../includes/init.php';

// Get requested page slug
$slug = $_GET['slug'] ?? 'home';

// Validate slug format
if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
    http_response_code(400);
    exit('Invalid page slug');
}

// Resolve template path
$templatePath = "templates/default/{$slug}.php";
if (!file_exists($templatePath)) {
    http_response_code(404);
    exit('Page not found');
}

// Render template with default data
$data = [
    'title' => ucwords(str_replace('-', ' ', $slug)),
    'slug' => $slug
];

$templateBase = realpath('templates/default');
$templateTarget = realpath($templatePath);
if (!$templateTarget || !str_starts_with($templateTarget, $templateBase . DIRECTORY_SEPARATOR) || !is_file($templateTarget)) {
    error_log("SECURITY: blocked dynamic include: template");
    http_response_code(400);
    exit('Invalid template path');
}

ob_start();
require_once $templateTarget;
$content = ob_get_clean();

// Output final page
echo $content;
