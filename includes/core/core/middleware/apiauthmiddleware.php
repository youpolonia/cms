<?php
/**
 * API Authentication Middleware
 * Handles API key validation
 */

class ApiAuthMiddleware {
    private $validKeys = [];

    public function __construct() {
        // Load API keys from config
        $this->validKeys = require_once __DIR__ . '/../../config/api_keys.php';
    }

    public function process($request, $response) {
        $apiKey = $request->headers['X-API-KEY'] ?? null;

        if (!$apiKey || !in_array($apiKey, $this->validKeys)) {
            $response->setStatusCode(401);
            $response->setBody(json_encode([
                'error' => 'Invalid API key'
            ]));
            $response->send();
            exit;
        }
    }
}
