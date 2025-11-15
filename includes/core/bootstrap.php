<?php

require_once __DIR__ . '/router.php';

// Load all route files
$routeFiles = glob(__DIR__ . '/../routes/*.php');
foreach ($routeFiles as $file) {
    require_once $file;
}

// Dispatch the request
Router::dispatch();
