<?php
use Includes\Core\Router;
use Includes\Core\Request;
use Includes\Core\Response;
use Includes\Auth\AuthController;

// Register routes
$router->addRoute('GET', '/login', function() use ($router) {
    $auth = new AuthController($router->getDependencies()['db']);
    $csrfToken = $auth->getCsrfToken();
    return new Response(require __DIR__.'/../templates/login.php');
});

$router->addRoute('POST', '/login', function() use ($router) {
    $auth = new AuthController($router->getDependencies()['db']);
    $request = new Request();
    
    if (!$auth->validateCsrfToken($request->input('csrf_token'))) {
        return new Response('Invalid CSRF token', 403);
    }

    $result = $auth->login($request->input('username'), $request->input('password'));
    
    if ($result['success']) {
        return new Response('', 302, ['Location' => '/dashboard']);
    }
    
    return new Response(require __DIR__.'/../templates/login.php', 200, [
        'error' => $result['message'],
        'csrfToken' => $auth->getCsrfToken()
    ]);
});

$router->addRoute('GET', '/logout', function() use ($router) {
    $auth = new AuthController($router->getDependencies()['db']);
    $auth->logout();
    return new Response('', 302, ['Location' => '/login']);
});
