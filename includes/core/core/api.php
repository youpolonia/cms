<?php
/**
 * Core API Implementation
 *
 * @deprecated Since 2025-07-01 - Use core/Router.php instead
 * Will be removed in Phase 10 (2025-09-30)
 *
 * Implements RESTful JSON API following the architecture plan
 */

class Api {
    private $router;
    private $request;
    private $response;
    private $middleware = [];

    public function __construct() {
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new \core\Router();
    }

    /**
     * Add middleware to API pipeline
     */
    public function addMiddleware($middleware) {
        $this->middleware[] = $middleware;
    }

    /**
     * Process API request
     */
    public function handleRequest() {
        try {
            // Run middleware
            foreach ($this->middleware as $middleware) {
                $middleware->process($this->request, $this->response);
            }

            // Route request
            $route = $this->router->match($this->request);
            $controller = new $route['controller'];
            $action = $route['action'];

            // Execute controller action
            $result = $controller->$action($this->request);

            // Format response
            $this->response->setContentType('application/json');
            $this->response->setBody(json_encode($result));
            
        } catch (Exception $e) {
            $this->handleError($e);
        }

        return $this->response;
    }

    /**
     * Standard error handling
     */
    private function handleError($exception) {
        $this->response->setStatusCode(500);
        $this->response->setBody(json_encode([
            'error' => $exception->getMessage(),
            'code' => $exception->getCode()
        ]));
    }
}

class Request {
    public $method;
    public $uri;
    public $headers = [];
    public $body = [];

    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->headers = getallheaders();
        $this->body = json_decode(file_get_contents('php://input'), true) ?: [];
    }
}

class Response {
    private $statusCode = 200;
    private $headers = [];
    private $body;

    public function setStatusCode($code) {
        $this->statusCode = $code;
    }

    public function setContentType($type) {
        $this->headers['Content-Type'] = $type;
    }

    public function setBody($content) {
        $this->body = $content;
    }

    public function send() {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->body;
    }
}

class Router {
    private $routes = [];

    public function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function match(Request $request) {
        foreach ($this->routes as $route) {
            if ($route['method'] === $request->method && 
                $this->pathMatches($route['path'], $request->uri)) {
                return $route;
            }
        }
        throw new Exception('Route not found', 404);
    }

    private function pathMatches($pattern, $path) {
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $pattern);
        return preg_match("#^$pattern$#", $path);
    }
}
