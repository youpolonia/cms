<?php
/**
 * API Response Formatter v2
 * Standardizes API response structure with enhanced features
 */

class ApiResponse {
    private static function sendHeaders($code = 200, $headers = []) {
        http_response_code($code);
        header('Content-Type: application/json');
        
        foreach ($headers as $name => $value) {
            header("$name: $value");
        }
    }

    public static function success($data = null, $meta = [], $code = 200, $headers = []) {
        $response = [
            'success' => true,
            'data' => $data,
            'meta' => array_merge($meta, [
                'timestamp' => time(),
                'status' => $code
            ])
        ];

        self::sendHeaders($code, $headers);
        return json_encode($response, JSON_PRETTY_PRINT);
    }

    public static function error($message, $code = 400, $errors = [], $headers = []) {
        $response = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'errors' => $errors
            ],
            'meta' => [
                'timestamp' => time(),
                'status' => $code
            ]
        ];

        self::sendHeaders($code, $headers);
        return json_encode($response, JSON_PRETTY_PRINT);
    }

    public static function paginated($data, $total, $perPage, $currentPage, $headers = []) {
        return self::success($data, [
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'last_page' => ceil($total / $perPage)
            ]
        ], 200, $headers);
    }

    public static function notFound($message = 'Resource not found') {
        return self::error($message, 404);
    }

    public static function unauthorized($message = 'Unauthorized') {
        return self::error($message, 401);
    }

    public static function forbidden($message = 'Forbidden') {
        return self::error($message, 403);
    }

    public static function rateLimited($retryAfter = 60) {
        return self::error('Too many requests', 429, [], [
            'Retry-After' => $retryAfter,
            'X-RateLimit-Limit' => 60,
            'X-RateLimit-Remaining' => 0
        ]);
    }
}
