<?php

use App\Controllers\VersionController;

// List all versions
$router->get('/api/versions', [VersionController::class, 'index']);

// Compare a version
$router->get('/api/versions/compare/(\d+)', [VersionController::class, 'compare']);

// Restore a version
$router->post('/api/versions/restore', [VersionController::class, 'restore']);

// AI Content Generation with middleware
$router->post('/api/ai/generate', [AIController::class, 'generate'])
    ->middleware('auth')
    ->middleware('rate_limit:60,1') // 60 requests per minute
    ->middleware('validate:ai_generate');
