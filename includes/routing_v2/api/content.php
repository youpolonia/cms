<?php
use Includes\Controllers\ContentController;
use Includes\Middleware\TenantIsolationMiddleware;

$router->group('/api/content', function($router) {
    $controller = new ContentController();
    
    $router->get('/list', [$controller, 'list'])
        ->add(new TenantIsolationMiddleware());
        
    $router->post('/save', [$controller, 'save'])
        ->add(new TenantIsolationMiddleware());
});
