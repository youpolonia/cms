# Routing System Examples

## Basic Usage

```php
<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/routing/Router.php';
require_once __DIR__.'/../includes/routing/Request.php';

$router = new CMS\Routing\Router();

// Simple GET route
$router->addRoute('GET', '/', function($request) {
    return 'Welcome to the homepage!';
});

// Route with parameters
$router->addRoute('GET', '/user/{id}', function($request) {
    $userId = $request->input('id');
    return "Viewing user $userId";
});

// POST route with JSON response
$router->addRoute('POST', '/api/users', function($request) {
    $data = $request->json();
    return ['status' => 'success', 'data' => $data];
});

// Handle the request
$request = new CMS\Routing\Request();
$router->dispatch($request);
```

## Middleware Examples

```php
// Authentication middleware
$authMiddleware = function($request, $next) {
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        return 'Unauthorized';
    }
    return $next($request);
};

// Logging middleware
$loggingMiddleware = function($request, $next) {
    error_log("Request to: " . $request->uri);
    return $next($request);
};

// Apply middleware to a route
$router->addRoute('GET', '/dashboard', function($request) {
    return 'Dashboard content';
}, [$authMiddleware, $loggingMiddleware]);

// Apply middleware to a group
$router->group('/admin', function($router) {
    $router->addRoute('GET', '/users', function($request) {
        return 'Admin users list';
    });
}, [$authMiddleware]);
```

## RESTful Resource Example

```php
$router->group('/api', function($router) {
    // GET /api/posts
    $router->addRoute('GET', '/posts', function($request) {
        return ['posts' => []];
    });
    
    // POST /api/posts
    $router->addRoute('POST', '/posts', function($request) {
        $data = $request->json();
        return ['status' => 'created', 'data' => $data];
    });
    
    // GET /api/posts/{id}
    $router->addRoute('GET', '/posts/{id}', function($request) {
        $id = $request->input('id');
        return ['post' => ['id' => $id]];
    });
}, [$loggingMiddleware]);
```

## Error Handling

The router automatically handles:
- 404 Not Found for unmatched routes
- 500 Internal Server Error for exceptions
- JSON error responses for API routes
- CSRF verification (when implemented)