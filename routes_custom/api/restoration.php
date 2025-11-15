<?php

$router = new \App\Core\Router();

$router->group(['middleware' => 'auth:sanctum'], function() use ($router) {
    $router->post('/restore/{version}', 'ContentVersionRestorationController@restore');
    $router->get('/history/{content}', 'ContentVersionRestorationController@history');
});
