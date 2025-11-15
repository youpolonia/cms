<?php

class ApiAuthMiddleware {
    private $auth;

    public function __construct() {
        $this->auth = new Authentication();
    }

    public function handle(): bool {
        $token = $this->getBearerToken();
        if (!$this->auth->validateToken($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid API token']);
            return false;
        }
        return true;
    }

    private function getBearerToken(): string {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        return str_replace('Bearer ', '', $header);
    }
}
