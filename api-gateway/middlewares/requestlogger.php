<?php

namespace ApiGateway\Middlewares;

use Core\Logger;

class RequestLogger
{
    private Logger $logger;
    private float $startTime;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log incoming request details
     */
    public function logRequest(): void
    {
        $this->startTime = microtime(true);
        
        $this->logger->info('Request received', [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Log response details including processing time
     * @param int $statusCode HTTP status code
     */
    public function logResponse(int $statusCode): void
    {
        $responseTime = round((microtime(true) - $this->startTime) * 1000, 2);
        
        $this->logger->info('Response sent', [
            'status_code' => $statusCode,
            'response_time_ms' => $responseTime,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Log error details
     * @param \Throwable $e The exception/error
     */
    public function logError(\Throwable $e): void
    {
        $this->logger->error('Request failed', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
