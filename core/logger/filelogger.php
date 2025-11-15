<?php
/**
 * File-based logger implementation
 */
namespace Core\Logger;

class FileLogger implements LoggerInterface
{
    private string $filePath;
    private $fileHandle;

    /**
     * Constructor - opens log file
     * 
     * @param string $filePath Path to log file
     * @throws \RuntimeException If file cannot be opened
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $this->ensureDirectoryExists(dirname($filePath));
        $this->fileHandle = fopen($filePath, 'a');
        
        if ($this->fileHandle === false) {
            throw new \RuntimeException("Failed to open log file: {$filePath}");
        }
    }

    /**
     * Ensure log directory exists
     */
    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            throw new \RuntimeException("Failed to create log directory: {$directory}");
        }
    }

    /**
     * Get current timestamp in ISO 8601 format
     */
    private function getTimestamp(): string
    {
        return (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM);
    }

    /**
     * Serialize context array to JSON string
     */
    private function serializeContext(array $context): string
    {
        try {
            return json_encode($context, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return '[context serialization error]';
        }
    }

    /**
     * Check if logger is healthy (file is writable)
     */
    public function isHealthy(): bool
    {
        if (!is_resource($this->fileHandle)) {
            return false;
        }
        
        // Test write capability
        $testPosition = ftell($this->fileHandle);
        $testWrite = fwrite($this->fileHandle, '');
        fseek($this->fileHandle, $testPosition);
        
        return $testWrite !== false;
    }

    /**
     * Handle logging errors by writing to stderr
     */
    private function handleError(string $message): void
    {
        fwrite(STDERR, "[LOGGER ERROR] {$message}\n");
    }

    /**
     * Log a message to file
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $logEntry = sprintf(
            "[%s] %s: %s %s\n",
            $this->getTimestamp(),
            strtoupper($level),
            $message,
            !empty($context) ? $this->serializeContext($context) : ''
        );

        if (fwrite($this->fileHandle, $logEntry) === false) {
            $this->handleError("Failed to write to log file");
        }
    }

    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function error(string|\Stringable $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function notice(string|\Stringable $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Close file handle on destruction
     */
    public function __destruct()
    {
        if (is_resource($this->fileHandle)) {
            fclose($this->fileHandle);
        }
    }
}
