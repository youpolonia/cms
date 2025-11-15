<?php

namespace Includes\RoutingV2;

class Response {
    private int $statusCode;
    private array $headers;
    private $body;

    public function __construct(int $statusCode, array $headers = [], $body = null) {
        $this->statusCode = $statusCode;
        
        // Set default headers if none provided
        $this->headers = array_merge([
            'Content-Type' => 'text/html; charset=UTF-8'
        ], $headers);
        
        $this->body = $body;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function getBody() {
        return $this->body;
    }

    public function send(): void {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->body;
    }
}
