<?php

namespace Core;

class Logger
{
    private string $logDir;
    private string $logFile;
    private array $levels = ['info', 'warning', 'error', 'debug', 'test'];
    private ?string $currentTestId = null;
    private float $testStartTime = 0;

    public function __construct(string $logDir = 'logs/')
    {
        $this->logDir = rtrim($logDir, '/') . '/';
        $this->logFile = $this->logDir . 'app_' . date('Y-m-d') . '.log';

        if (!is_dir($this->logDir)) {
            if (!mkdir($this->logDir, 0755, true)) {
                throw new \RuntimeException("Failed to create log directory: {$this->logDir}");
            }
        }

        if (!is_writable($this->logDir)) {
            throw new \RuntimeException("Log directory is not writable: {$this->logDir}");
        }
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $level = strtolower($level);
        
        if (!in_array($level, $this->levels, true)) {
            throw new \InvalidArgumentException("Invalid log level: {$level}");
        }

        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$level}]";
        
        if ($this->currentTestId) {
            $logEntry .= " [test:{$this->currentTestId}]";
        }
        
        $logEntry .= " {$message}";

        if (!empty($context)) {
            try {
                $encoded = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
                $logEntry .= ' ' . $encoded;
            } catch (\JsonException $e) {
                error_log("Failed to encode context: " . $e->getMessage());
                $logEntry .= ' {"error": "failed to encode context"}';
            }
        }

        $logEntry .= PHP_EOL;

        if (false === file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX)) {
            error_log("Failed to write to log file: {$this->logFile}");
        }
    }

    public function startTest(string $testId): void
    {
        $this->currentTestId = $testId;
        $this->testStartTime = microtime(true);
        $this->log('test', "Test started: {$testId}");
    }

    public function endTest(bool $passed, array $metrics = []): void
    {
        if (!$this->currentTestId) {
            return;
        }
        
        $duration = round((microtime(true) - $this->testStartTime) * 1000, 2);
        $result = $passed ? 'PASSED' : 'FAILED';
        
        $this->log('test', "Test completed: {$this->currentTestId} ({$result})", [
            'duration_ms' => $duration,
            'result' => $result,
            'metrics' => $metrics
        ]);
        
        $this->currentTestId = null;
    }
}
