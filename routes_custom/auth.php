<?php

// Custom authentication routes
$router->get('login', 'AuthController@showLoginForm');
$router->post('login', 'AuthController@login');
$router->post('logout', 'AuthController@logout');

$router->get('register', 'AuthController@showRegistrationForm');
$router->post('register', 'AuthController@register');

$router->get('password/reset', 'PasswordResetController@showLinkRequestForm');
$router->post('password/email', 'PasswordResetController@sendResetLinkEmail');
$router->get('password/reset/{token}', 'PasswordResetController@showResetForm');
$router->post('password/reset', 'PasswordResetController@reset');
