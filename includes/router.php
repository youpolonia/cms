<?php
/**
 * CMS Router Class
 * Handles request routing with caching support
 */
class Router {
    private static $instance;
    private $routes = [];
    private $cache;
    private $cacheKey = 'cached_routes';
    private $cacheTtl = 3600; // 1 hour

    private function __construct() {
        // Removed legacy include that caused duplicate Cache class declaration
        $this->cache = new Cache();
    }

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add a route
     * @param string $method HTTP method
     * @param string $path Route path
     * @param callable $handler Route handler
     */
    public function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];
    }

    /**
     * Load routes from cache or initialize
     */
    public function loadRoutes() {
        $cached = $this->cache->get($this->cacheKey);
        if ($cached !== null) {
            $this->routes = $cached;
            return;
        }

        // Default routes if none cached
        $this->addRoute('GET', '/', function() {
            require_once __DIR__ . '/../index.php';
        });
    }

    /**
     * Cache current routes
     */
    public function cacheRoutes() {
        $this->cache->set($this->cacheKey, $this->routes, $this->cacheTtl);
    }

    /**
     * Match and execute route
     * @param string $requestMethod
     * @param string $requestPath
     */
    public function dispatch($requestMethod, $requestPath) {
        $requestMethod = strtoupper($requestMethod);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $route['path'] === $requestPath) {
                call_user_func($route['handler']);
                return;
            }
        }

        // No route matched
        http_response_code(404);
        echo "404 Not Found";
    }
}
