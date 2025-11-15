<?php
require_once __DIR__ . '/../core/bootstrap.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Simple PHP Router for CMS
 * Replaces Laravel routing functionality
 */

class Router {
    private static $routes = [];
    private static $middleware = [];

    public static function post(string $uri, callable $handler, array $middleware = [], array $validationRules = []) {
        self::$routes['POST'][$uri] = [
            'handler' => $handler,
            'middleware' => array_merge(['csrf'], $middleware),
            'validation' => $validationRules
        ];
    }

    public static function put(string $uri, callable $handler, array $middleware = [], array $validationRules = []) {
        self::$routes['PUT'][$uri] = [
            'handler' => $handler,
            'middleware' => array_merge(['csrf'], $middleware),
            'validation' => $validationRules
        ];
    }

    public static function delete(string $uri, callable $handler, array $middleware = [], array $validationRules = []) {
        self::$routes['DELETE'][$uri] = [
            'handler' => $handler,
            'middleware' => array_merge(['csrf'], $middleware),
            'validation' => $validationRules
        ];
    }

    public static function patch(string $uri, callable $handler, array $middleware = [], array $validationRules = []) {
        self::$routes['PATCH'][$uri] = [
            'handler' => $handler,
            'middleware' => array_merge(['csrf'], $middleware),
            'validation' => $validationRules
        ];
    }
private static function validateInput(array $rules): array {
    $errors = [];
    $validated = [];
    
    // Get input data based on request method
    $inputData = $_POST;
    if (empty($_POST)) {
        $input = file_get_contents('php://input');
        if (!empty($input) && json_decode($input) !== null) {
            $inputData = json_decode($input, true);
        }
    }
    
    foreach ($rules as $field => $rule) {
        $value = $inputData[$field] ?? null;
        
            
            if (str_contains($rule, 'required') && empty($value)) {
                $errors[$field] = "The $field field is required";
                continue;
            }
            
            if (!empty($value)) {
                if (str_contains($rule, 'email') && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "The $field must be a valid email";
                }
                
                if (str_contains($rule, 'numeric') && !is_numeric($value)) {
                    $errors[$field] = "The $field must be a number";
                }
                
                // Additional validation rules can be added here
                
                $validated[$field] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        
        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['errors' => $errors]);
            exit;
        }
        
        return $validated;
    }

    private static function validateCsrf(): bool {
        if (!isset($_POST['_token']) || $_POST['_token'] !== ($_SESSION['_token'] ?? '')) {
            return false;
        }
        return true;
    }

    public static function generateCsrfToken(): string {
        if (empty($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_token'];
    }

    public static function middleware(array $middleware, callable $callback) {
        $previous = self::$middleware;
        self::$middleware = array_merge($previous, $middleware);
        $callback();
        self::$middleware = $previous;
    }

    public static function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach (self::$routes[$method] ?? [] as $route => $config) {
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);
            $pattern = "@^$pattern$@D";

            if (preg_match($pattern, $uri, $matches)) {
                // Apply middleware
                foreach (array_merge(self::$middleware, $config['middleware']) as $mw) {
                    if (!self::runMiddleware($mw)) {
                        http_response_code(403);
                        exit;
                    }
                }

                // Validate input if rules exist
                $validatedInput = [];
                if (!empty($config['validation'])) {
                    $validatedInput = self::validateInput($config['validation']);
                }

                // Call handler with parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                call_user_func($config['handler'], ...array_merge($params, [$validatedInput]));
                return;
            }
        }

        http_response_code(404);
    }

    private static function runMiddleware(string $name): bool {
        // Implement middleware checks here
        switch ($name) {
            case 'auth:sanctum':
                return self::checkAuthentication();
            case 'can:approve-content':
                return self::checkPermission('approve-content');
            case 'csrf':
                return self::validateCsrf();
            // Add other middleware checks as needed
            default:
                return true;
        }
    }

    private static function checkAuthentication(): bool {
        // Implement authentication check
        return isset($_SESSION['user_id']);
    }

    private static function checkPermission(string $permission): bool {
        // Implement permission check
        return $_SESSION['permissions'][$permission] ?? false;
    }
}
