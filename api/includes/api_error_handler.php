<?php
// API Error Handler
// Version: 1.1
// Date: 2025-06-02

class AuthenticationException extends Exception {}
class RateLimitException extends Exception {}
class MaintenanceException extends Exception {}

class APIErrorHandler {
    public static function handle($exception) {
        $statusCode = self::getStatusCode($exception);
        $env = getenv('APP_ENV') ?: 'production';

        if ($env === 'production') {
            $message = 'An error occurred. Please try again later.';
        } else {
            $message = $exception->getMessage();
        }

        // Log the error
        error_log($exception->getMessage() . "\n" . $exception->getTraceAsString());

        $response = [
            'error' => [
                'code' => $statusCode,
                'message' => $message,
                'timestamp' => time()
            ]
        ];

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    private static function getStatusCode($exception) {
        if ($exception instanceof InvalidArgumentException) {
            return 400;
        } elseif ($exception instanceof AuthenticationException) {
            return 401;
        } elseif ($exception instanceof AuthorizationException) {
            return 403;
        } elseif ($exception instanceof RateLimitException) {
            return 429;
        } elseif ($exception instanceof MaintenanceException) {
            return 503;
        } elseif ($exception instanceof Exception) {
            return 500;
        }
        return 500;
    }
}
