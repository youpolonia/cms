<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../app/controllers/VersionController.php';

$controller = new VersionController();

// Existing routes
$router->get('/versions', [$controller, 'index']);
$router->post('/versions', [$controller, 'createVersion']);
$router->get('/versions/{id}/compare', [$controller, 'compare']);
$router->post('/versions/{id}/restore', [$controller, 'restore']);

// New routes for AI preview and publishing
$router->get('/content/{id}/state', [$controller, 'getContentState']);
$router->get('/content/{id}/suggestions', [$controller, 'getAISuggestions']);
$router->post('/content/{id}/transition', [$controller, 'transitionState']);
