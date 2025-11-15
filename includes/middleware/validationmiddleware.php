<?php
/**
 * Validation Middleware
 * Handles request validation before routing
 */
class ValidationMiddleware {
    private $router;

    public function __construct($router = null) {
        $this->router = $router;
    }

    /**
     * Validate request before routing
     * @param string $requestMethod
     * @param string $requestPath
     */
    public function validate($requestMethod, $requestPath) {
        // Basic security validations
        if (!$this->isValidMethod($requestMethod)) {
            http_response_code(405);
            echo "405 Method Not Allowed";
            return false;
        }

        if (!$this->isValidPath($requestPath)) {
            http_response_code(400);
            echo "400 Bad Request";
            return false;
        }

        return true;
    }

    private function isValidMethod($method) {
        $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'];
        return in_array(strtoupper($method), $allowedMethods);
    }

    private function isValidPath($path) {
        // Basic path validation
        if (!is_string($path)) {
            return false;
        }

        // Check for path traversal attempts
        if (strpos($path, '../') !== false || strpos($path, '..\\') !== false) {
            return false;
        }

        return true;
    }
}
