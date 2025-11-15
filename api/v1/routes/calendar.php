<?php
use Api\v1\Controllers\CalendarController;
use Middleware\RateLimiterMiddleware;

$router->group(['prefix' => 'calendar', 'middleware' => ['auth', new RateLimiterMiddleware($container->get('cache'), 100, 60)]], function($router) {
    $router->post('/sync', [CalendarController::class, 'sync']);
    $router->post('/schedule-sync', [CalendarController::class, 'scheduleSync']);
    $router->post('/resolve-conflict', [CalendarController::class, 'resolveConflict']);
    $router->get('/sync-status', [CalendarController::class, 'getSyncStatus']);
});
