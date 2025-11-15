<?php
/**
 * Webhook Notification Dispatcher
 * Sends HTTP POST requests with JSON payload
 */
class WebhookDispatcher {
    private $defaultHeaders;
    private $timeout;

    public function __construct(array $defaultHeaders = [], int $timeout = 10) {
        $this->defaultHeaders = array_merge([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ], $defaultHeaders);
        $this->timeout = $timeout;
    }

    public function send(string $url, array $payload, array $headers = []): bool {
        $finalHeaders = array_merge($this->defaultHeaders, $headers);
        $headerString = implode("\r\n", array_map(
            fn($k, $v) => "$k: $v",
            array_keys($finalHeaders),
            $finalHeaders
        ));

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => $headerString,
                'content' => json_encode($payload),
                'timeout' => $this->timeout
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            error_log("Webhook failed to $url: " . error_get_last()['message']);
            return false;
        }

        return true;
    }
}
