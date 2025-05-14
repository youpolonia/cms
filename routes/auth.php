<?php

// Custom authentication routes
$router->get('login', 'AuthController@showLoginForm');
$router->post('login', 'AuthController@login');
$router->post('logout', 'AuthController@logout');

$router->get('register', 'AuthController@showRegistrationForm');
$router->post('register', 'AuthController@register');

$router->get('password/reset', 'AuthController@showLinkRequestForm');
$router->post('password/email', 'AuthController@sendResetLinkEmail');
$router->get('password/reset/{token}', 'AuthController@showResetForm');
$router->post('password/reset', 'AuthController@reset');
