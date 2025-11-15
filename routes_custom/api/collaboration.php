<?php

$router = new \App\Core\Router();

$router->group(['middleware' => 'auth:sanctum'], function() use ($router) {
    $router->post('/start', 'Api\CollaborationController@startSession');
    $router->post('/submit', 'Api\CollaborationController@submitEdit');
    $router->post('/resolve', 'Api\CollaborationController@resolveConflict');
    $router->post('/comment', 'Api\CollaborationController@addComment');
});
