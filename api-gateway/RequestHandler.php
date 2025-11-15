<?php
/**
 * API Gateway Request Handler
 * 
 * Responsibilities:
 * - Parse incoming HTTP requests
 * - Format responses
 * - Handle errors and logging
 */
class RequestHandler {
    /**
     * Process incoming request
     * 
     * @param array $server $_SERVER data
     * @param array $input Raw input data
     * @param PDO $pdo Database connection
     * @return array Response data
     */
    public static function handle(array $server, array $input, PDO $pdo): array {
        try {
            // Parse request
            $request = [
                'method' => $server['REQUEST_METHOD'],
                'path' => parse_url($server['REQUEST_URI'], PHP_URL_PATH),
                'headers' => self::getHeaders($server),
                'body' => json_decode($input['body'] ?? '', true) ?? []
            ];

            // Route request
            $response = Router::handleRequest($request, $pdo);

            // Format response
            return [
                'status' => $response['status'] ?? 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($response['body'] ?? [])
            ];
        } catch (Exception $e) {
            error_log("Request handling error: " . $e->getMessage());
            return [
                'status' => 500,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['error' => 'Internal server error'])
            ];
        }
    }

    /**
     * Extract headers from server array
     */
    private static function getHeaders(array $server): array {
        $headers = [];
        foreach ($server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }
}
