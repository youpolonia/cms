<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Core API Handler
 * Standardizes API responses and error handling
 */
class ApiHandler {
    /**
     * Send JSON response
     * @param array $data Response data
     * @param int $status HTTP status code
     */
    public static function respond(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Handle API errors
     * @param string $message Error message
     * @param int $status HTTP status code
     */
    public static function error(string $message, int $status = 400): void {
        self::respond([
            'success' => false,
            'message' => $message
        ], $status);
    }

    /**
     * Get request method
     */
    public static function getMethod(): string {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get request data based on method
     */
    public static function getRequestData(): array {
        $method = self::getMethod();
        if ($method === 'GET') {
            return $_GET;
        }
        
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
}
