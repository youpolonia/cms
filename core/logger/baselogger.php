<?php
/**
 * Base logger with common functionality
 */
namespace core\Logger;

abstract class BaseLogger implements LoggerInterface
{
    /**
     * Get current timestamp in ISO 8601 format
     */
    protected function getTimestamp(): string
    {
        return date('c');
    }

    /**
     * Serialize context data to JSON
     */
    protected function serializeContext(array $context): string
    {
        try {
            return json_encode($context, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return '{"error": "Failed to serialize context"}';
        }
    }

    /**
     * Handle logging errors
     */
    protected function handleError(string $error): void
    {
        error_log("Logger error: {$error}");
    }

    /**
     * Log a message - must be implemented by concrete loggers
     */
    abstract public function log(string $message, array $context = [], string $level = 'info'): bool;
}
