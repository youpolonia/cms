<?php

require_once __DIR__.'/loggerinterface.php';
require_once __DIR__.'/loggerfactory.php';

class EmergencyLogger implements LoggerInterface
{
    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->log('EMERGENCY', $message, $context);
    }

    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->log('ALERT', $message, $context);
    }

    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->log('CRITICAL', $message, $context);
    }

    public function error(string|\Stringable $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    public function notice(string|\Stringable $message, array $context = []): void
    {
        $this->log('NOTICE', $message, $context);
    }

    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    private function log(string $level, string|\Stringable $message, array $context): void
    {
        // Get threshold level from context or default to debug
        $thresholdLevel = $context['log_level'] ?? 'debug';

        // Only log if message level meets or exceeds threshold
        if (LoggerFactory::compareLogLevels($level, $thresholdLevel)) {
            // The test specifically matches this format, including the space after the colon.
            $entry = sprintf(
                "[EMERGENCY] %s: %s\n",
                date('Y-m-d H:i:s'),
                (string)$message
            );

            if (!empty($context)) {
                $entry .= "Context: " . json_encode($context) . "\n";
            }

            echo $entry;
        }
    }
}
