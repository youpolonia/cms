<?php
/**
 * ResponseHandler - Framework-free response handler
 *
 * Provides methods for sending JSON responses, HTTP redirects, and rendering views
 * with HTTP status codes, error handling, and debug information.
 */
class ResponseHandler {
    /**
     * @var bool $debugMode Show debug info in responses
     */
    private static $debugMode = false;

    /**
     * Set debug mode
     * @param bool $enabled
     */
    public static function setDebugMode(bool $enabled): void {
        self::$debugMode = $enabled;
    }

    /**
     * Send a successful JSON response
     * @param mixed $data Response data
     * @param int $status HTTP status code (default: 200)
     * @param string|null $message Optional success message
     */
    public static function success($data = null, int $status = 200, ?string $message = null): void {
        $response = [
            'success' => true,
            'status' => $status,
            'data' => $data
        ];

        if ($message) {
            $response['message'] = $message;
        }

        self::sendResponse($response, $status);
    }

    /**
     * Send an error JSON response
     * @param string $message Error message
     * @param int $status HTTP status code (default: 400)
     * @param array|null $errors Optional validation errors
     * @param mixed $debugData Optional debug data
     */
    public static function error(string $message, int $status = 400, ?array $errors = null, $debugData = null): void {
        $response = [
            'success' => false,
            'status' => $status,
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        if (self::$debugMode && $debugData) {
            $response['debug'] = $debugData;
        }

        self::sendResponse($response, $status);
    }

    /**
     * Send validation error response
     * @param array $errors Validation errors
     * @param string|null $message Optional error message
     */
    public static function validationError(array $errors, ?string $message = null): void {
        self::error(
            $message ?? 'Validation failed',
            422,
            $errors
        );
    }

    /**
     * Send the JSON response and set headers
     * @param array $response
     * @param int $status
     */
    private static function sendResponse(array $response, int $status): void {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code($status);
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Redirect to another URL
     * @param string $url URL to redirect to
     * @param int $status HTTP redirect status code (default: 302)
     * @param array|null $flashData Optional data to flash to session
     */
    public static function redirect(string $url, int $status = 302, ?array $flashData = null): void {
        if ($flashData) {
            $_SESSION['_flash'] = $flashData;
        }

        if (!headers_sent()) {
            header("Location: $url", true, $status);
        }
        exit;
    }

    /**
     * Render a view template with optional data
     * @param string $templatePath Path to template file
     * @param array $data Data to pass to template
     * @param int $status HTTP status code (default: 200)
     */
    public static function view(string $templatePath, array $data = [], int $status = 200): void {
        if (!headers_sent()) {
            header('Content-Type: text/html');
            http_response_code($status);
        }

        extract($data);
        require_once $templatePath;
        exit;
    }
}
