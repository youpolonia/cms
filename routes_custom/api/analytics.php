<?php

$router = new \App\Core\Router();

$router->group(['middleware' => 'auth:sanctum'], function() use ($router) {
    $router->get('/events', 'Api\AnalyticsController@getEvents');
    $router->post('/track', 'Api\AnalyticsController@trackEvent');
    $router->get('/metrics', 'Api\AnalyticsController@getMetrics');
    $router->get('/export', 'Api\AnalyticsController@exportData');
});
