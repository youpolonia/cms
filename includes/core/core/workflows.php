<?php

class Workflows {
    private $webhooks = [];
    private $eventSubscriptions = [];
    private $rateLimiter;

    public function __construct() {
        $this->rateLimiter = new RateLimiterMiddleware();
    }

    public function registerWebhook(string $name, string $url, array $events): void {
        $this->webhooks[$name] = [
            'url' => $url,
            'events' => $events,
            'active' => true
        ];
    }

    public function handleWebhook(string $name, array $payload): array {
        if (!isset($this->webhooks[$name])) {
            throw new Exception("Webhook not found");
        }

        if (!$this->webhooks[$name]['active']) {
            throw new Exception("Webhook is inactive");
        }

        if (!$this->rateLimiter->check('webhook_'.$name)) {
            throw new Exception("Rate limit exceeded");
        }

        return $this->validateAndProcessPayload($payload);
    }

    private function validateAndProcessPayload(array $payload): array {
        // Basic payload validation
        if (empty($payload['event']) || empty($payload['data'])) {
            throw new Exception("Invalid payload format");
        }

        return [
            'status' => 'processed',
            'event' => $payload['event'],
            'data' => $this->transformData($payload['data'])
        ];
    }

    private function transformData(array $data): array {
        // Default transformation - can be overridden per workflow
        return $data;
    }

    public function subscribeToEvent(string $event, callable $handler): void {
        $this->eventSubscriptions[$event][] = $handler;
    }

    public function triggerEvent(string $event, array $data): void {
        if (isset($this->eventSubscriptions[$event])) {
            foreach ($this->eventSubscriptions[$event] as $handler) {
                $handler($data);
            }
        }
    }
}
