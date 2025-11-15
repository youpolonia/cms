<?php
class ContentModule {
    private static $router;

    public static function init() {
        // Router initialization moved to bootstrap
        // Module routes can be registered via Core\Router instead
        // Bypassed for admin panel standalone mode
    }

    public static function registerRoutesModern() {
        // TODO: Register routes using Core\Router
    }

    public static function registerRoutes() {
        $router = self::$router;
        
        // Blog post route (handles /{slug} pattern)
        $router->addRoute('GET', '/{slug}', ['BlogController', 'show']);
        
        // Content routes
        $router->addRoute('GET', '/content', ['ContentController', 'listContent']);
        $router->addRoute('GET', '/content/{slug}', ['ContentController', 'showContent']);
        
        // Blog routes
        $router->addRoute('GET', '/blog', ['ContentController', 'showBlog']);
        $router->addRoute('GET', '/blog/{slug}', ['ContentController', 'showBlogPost']);
    }

    public static function dispatch($path) {
        $method = $_SERVER['REQUEST_METHOD'];
        $response = self::$router->dispatch($method, $path);
        
        if ($response === null) {
            http_response_code(404);
            require_once __DIR__ . '/../../views/errors/404.php';
        }
    }
}
