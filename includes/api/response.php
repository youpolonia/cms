<?php
/**
 * API Response Formatter with Network Resilience
 */

declare(strict_types=1);

namespace CMS\API;

use CMS\API\NetworkHandler;

class Response
{
    private array $headers = [];
    private int $statusCode = 200;
    private array $data = [];
    private array $meta = [];
    private array $errors = [];

    public function success(array $data = [], array $meta = []): void
    {
        $this->statusCode = 200;
        $this->data = $data;
        $this->meta = $meta;
        $this->send();
    }

    public function created(array $data = [], string $location = null): void
    {
        $this->statusCode = 201;
        $this->data = $data;
        
        if ($location) {
            $this->headers['Location'] = $location;
        }
        
        $this->send();
    }

    public function error(string $message, int $code = 400, array $details = []): void
    {
        $this->statusCode = $code;
        $this->errors = [
            'message' => $message,
            'details' => $details
        ];
        $this->send();
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    private function send(): void
    {
        try {
            NetworkHandler::executeWithRetry(function() {
                // Set status code
                http_response_code($this->statusCode);

                // Set headers
                $this->setDefaultHeaders();
                foreach ($this->headers as $name => $value) {
                    header("$name: $value");
                }

                // Prepare response data
        $response = [
            'status' => $this->statusCode >= 200 && $this->statusCode < 300 ? 'success' : 'error',
            'data' => $this->data,
            'meta' => $this->meta,
            'errors' => $this->errors
        ];

        // Remove empty fields
        $response = array_filter($response, function($value) {
            return !empty($value) || $value === 0 || $value === false;
        });

                // Send JSON response
                header('Content-Type: application/json');
                echo json_encode($response, JSON_PRETTY_PRINT);
                exit;
            });
        } catch (\RuntimeException $e) {
            // Fallback to simple response if retries fail
            http_response_code(503);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'errors' => [
                    'message' => 'Service unavailable',
                    'details' => ['Network connectivity issues']
                ]
            ]);
            exit;
        }
    }

    private function setDefaultHeaders(): void
    {
        $this->headers['X-API-Version'] = '1.0';
        $this->headers['X-Content-Type-Options'] = 'nosniff';
        
        // Cache headers for successful responses
        if ($this->statusCode >= 200 && $this->statusCode < 300) {
            $this->headers['Cache-Control'] = $this->headers['Cache-Control'] ?? 'public, max-age=300';
            $this->headers['ETag'] = md5(json_encode($this->data));
        } else {
            $this->headers['Cache-Control'] = 'no-store, no-cache, must-revalidate';
        }
    }
}
