<?php
require_once __DIR__.'/../includes/routing/Response.php';

// Get router instance from app
$app = require __DIR__.'/../bootstrap/app.php';
$router = $app->router;

// API routes
$router->addRoute('GET', '/api/content', function() {
    require_once __DIR__.'/../app/Http/Controllers/Api/ContentController.php';
    $controller = new Api\ContentController();
    $result = $controller->index();
    return new CMS\Routing\Response(json_encode($result), 200, ['Content-Type' => 'application/json']);
});

$router->addRoute('GET', '/api/content/$id', function($id) {
    require_once __DIR__.'/../app/Http/Controllers/Api/ContentController.php';
    $controller = new Api\ContentController();
    $result = $controller->show($id);
    return new CMS\Routing\Response(json_encode($result), 200, ['Content-Type' => 'application/json']);
});

$router->addRoute('POST', '/api/content', function() {
    require_once __DIR__.'/../app/Http/Controllers/Api/ContentController.php';
    $controller = new Api\ContentController();
    $result = $controller->store($_POST);
    return new CMS\Routing\Response(json_encode($result), 201, ['Content-Type' => 'application/json']);
});

$router->addRoute('PUT', '/api/content/$id', function($id) {
    parse_str(file_get_contents('php://input'), $_PUT);
    require_once __DIR__.'/../app/Http/Controllers/Api/ContentController.php';
    $controller = new Api\ContentController();
    $result = $controller->update($id, $_PUT);
    return new CMS\Routing\Response(json_encode($result), 200, ['Content-Type' => 'application/json']);
});

$router->addRoute('DELETE', '/api/content/$id', function($id) {
    require_once __DIR__.'/../app/Http/Controllers/Api/ContentController.php';
    $controller = new Api\ContentController();
    $result = $controller->destroy($id);
    return new CMS\Routing\Response(json_encode($result), 200, ['Content-Type' => 'application/json']);
});

// Version diff routes
$router->addRoute('GET', '/api/versions/{id}/diff', function($id) {
    require_once __DIR__.'/../includes/controllers/Api/VersionController.php';
    require_once __DIR__.'/../includes/Core/Middleware/ApiAuthMiddleware.php';
    
    $controller = new Api\VersionController();
    $middleware = new ApiAuthMiddleware();
    
    if (!$middleware->handle()) {
        return new CMS\Routing\Response(
            json_encode(['error' => 'Unauthorized']),
            401,
            ['Content-Type' => 'application/json']
        );
    }
    
    $result = $controller->diffAgainstCurrent($id);
    return new CMS\Routing\Response(json_encode($result), 200, ['Content-Type' => 'application/json']);
});

$router->addRoute('GET', '/api/versions/{id1}/diff/{id2}', function($id1, $id2) {
    require_once __DIR__.'/../includes/controllers/Api/VersionController.php';
    require_once __DIR__.'/../includes/Core/Middleware/ApiAuthMiddleware.php';
    
    $controller = new Api\VersionController();
    $middleware = new ApiAuthMiddleware();
    
    if (!$middleware->handle()) {
        return new CMS\Routing\Response(
            json_encode(['error' => 'Unauthorized']),
            401,
            ['Content-Type' => 'application/json']
        );
    }
    
    $result = $controller->diffBetweenVersions($id1, $id2);
    return new CMS\Routing\Response(json_encode($result), 200, ['Content-Type' => 'application/json']);
});

// Dispatch the request
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];
$response = $router->dispatch($requestMethod, $requestUri);
$response->send();
