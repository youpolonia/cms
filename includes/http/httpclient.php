<?php
declare(strict_types=1);

namespace App\Http; // Assuming a basic namespace structure

/**
 * A very simple HTTP client wrapper.
 * In a real application, use a robust library like Guzzle or Symfony HttpClient.
 */
class HttpClient {
    public function post(string $url, array $data = [], array $headers = []): string {
        $options = [
            'http' => [
                'header' => array_merge(["Content-type: application/json"], $headers),
                'method' => 'POST',
                'content' => json_encode($data),
                'ignore_errors' => true // To capture HTTP error responses
            ],
            'ssl' => [ // Basic SSL context, enhance as needed
                'verify_peer' => true,
                'verify_peer_name' => true,
                'cafile' => '/etc/ssl/certs/ca-certificates.crt' // Common path for CA bundle
            ]
        ];
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if ($result === FALSE) {
            $error = error_get_last();
            throw new \RuntimeException("HTTP request failed: " . ($error['message'] ?? 'Unknown error'));
        }
        
        // Check for HTTP status code in headers if possible (depends on server response)
        // For simplicity, this basic client doesn't parse response headers to get status code.
        // A more robust client would handle this.

        return $result;
    }

    public function get(string $url, array $queryParams = [], array $headers = []): string {
        $queryString = http_build_query($queryParams);
        $fullUrl = $url . ($queryString ? '?' . $queryString : '');

        $options = [
            'http' => [
                'header' => implode("\r\n", $headers),
                'method' => 'GET',
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'cafile' => '/etc/ssl/certs/ca-certificates.crt'
            ]
        ];
        $context = stream_context_create($options);
        $result = @file_get_contents($fullUrl, false, $context);

        if ($result === FALSE) {
            $error = error_get_last();
            throw new \RuntimeException("HTTP request failed: " . ($error['message'] ?? 'Unknown error'));
        }
        return $result;
    }
}
