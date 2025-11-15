<?php

/**
 * Web routes for the CMS
// Blog routes
Router::get('/blog', [BlogController::class, 'listPosts']);
 */

use Includes\Routing\Router;
use Includes\Controllers\PageController;
use Includes\Controllers\Admin\MultisiteController;

// Home page
Router::get('/', [PageController::class, 'show'], ['slug' => 'home']);

// Page routes
Router::get('/page/{slug}', [PageController::class, 'show']);

// Admin routes
Router::group('/admin', function() {
    // Multisite management
    Router::get('/multisite', [MultisiteController::class, 'index']);
    Router::get('/multisite/create', [MultisiteController::class, 'create']);
    Router::post('/multisite', [MultisiteController::class, 'store']);
    Router::get('/multisite/{siteId}/edit', [MultisiteController::class, 'edit']);
    Router::put('/multisite/{siteId}', [MultisiteController::class, 'update']);
    Router::delete('/multisite/{siteId}', [MultisiteController::class, 'delete']);
    Router::post('/multisite/{siteId}/switch', [MultisiteController::class, 'switchSite']);
});
