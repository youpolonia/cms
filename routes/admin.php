<?php
declare(strict_types=1);

use Includes\Routing\Router;
use Includes\Controllers\Admin\{
    DashboardController,
    ContentController,
    UserController
};

$router = Router::getInstance();

// Admin Dashboard
$router->get('/admin', [DashboardController::class, 'index']);

// Content Management
$router->get('/admin/content', [ContentController::class, 'index']);
$router->get('/admin/content/create', [ContentController::class, 'create']);
$router->post('/admin/content/create', [ContentController::class, 'create']);
$router->get('/admin/content/edit/{id}', [ContentController::class, 'edit']);
$router->post('/admin/content/edit/{id}', [ContentController::class, 'edit']);
$router->post('/admin/content/delete/{id}', [ContentController::class, 'delete']);

// User Management
$router->get('/admin/users', [UserController::class, 'index']);
$router->get('/admin/users/create', [UserController::class, 'create']);
$router->post('/admin/users/create', [UserController::class, 'create']);
$router->get('/admin/users/edit/{id}', [UserController::class, 'edit']);
$router->post('/admin/users/edit/{id}', [UserController::class, 'edit']);
$router->post('/admin/users/delete/{id}', [UserController::class, 'delete']);

// Add authentication middleware to all admin routes
$router->group(['middleware' => ['auth', 'admin']], function() use ($router) {
    // All routes defined above will inherit these middleware
});