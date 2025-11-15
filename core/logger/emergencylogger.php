<?php
namespace Core\Logger;

use Core\Logger\LoggerInterface;

class EmergencyLogger implements LoggerInterface
{
    private string $filePath;
    private bool $useStderr;

    public function __construct(string $filePath, bool $useStderr = false) 
    {
        $this->filePath = $filePath;
        $this->useStderr = $useStderr;
    }

    public function log(string $message, array $context = [], string $level = 'info'): bool
    {
        $logEntry = sprintf(
            "[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            json_encode($context)
        );

        if ($this->useStderr) {
            return (bool)file_put_contents('php://stderr', $logEntry);
        }

        try {
            $dir = dirname($this->filePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            return (bool)file_put_contents($this->filePath, $logEntry, FILE_APPEND);
        } catch (\Throwable $e) {
            // Final fallback to stderr if file writing fails
            return (bool)file_put_contents('php://stderr', $logEntry);
        }
    }

    public function emergency($message, array $context = []): void
    {
        $this->log($message, $context, 'emergency');
    }

    public function alert($message, array $context = []): void
    {
        $this->log($message, $context, 'alert');
    }

    public function critical($message, array $context = []): void
    {
        $this->log($message, $context, 'critical');
    }

    public function error($message, array $context = []): void
    {
        $this->log($message, $context, 'error');
    }

    public function warning($message, array $context = []): void
    {
        $this->log($message, $context, 'warning');
    }

    public function notice($message, array $context = []): void
    {
        $this->log($message, $context, 'notice');
    }

    public function info($message, array $context = []): void
    {
        $this->log($message, $context, 'info');
    }

    public function debug($message, array $context = []): void
    {
        $this->log($message, $context, 'debug');
    }
}
