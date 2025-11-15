<?php

class ContentStateHistoryLogger {
    private static $instance = null;
    private $logFile = 'logs/content_state_changes.log';
    private $history = [];
    private $fallbackLogDir = null;

    private function __construct() {
        // Try primary log directory first
        $logDir = dirname($this->logFile);
        $success = false;

        if (!file_exists($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        if (file_exists($logDir) && is_writable($logDir)) {
            $success = true;
        } else {
            // Fallback to internal temp directory
            require_once __DIR__ . '/../tmp_sandbox.php';
            $this->fallbackLogDir = cms_tmp_path('cms_logs');
            if (!file_exists($this->fallbackLogDir)) {
                @mkdir($this->fallbackLogDir, 0755, true);
            }

            if (file_exists($this->fallbackLogDir) && is_writable($this->fallbackLogDir)) {
                $this->logFile = $this->fallbackLogDir . '/content_state_changes.log';
                $success = true;
            }
        }

        if (!$success) {
            error_log('CMS Warning: Could not initialize log directory. Logging to error_log only.');
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function logStateChange(int $contentId, string $oldState, string $newState): void {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'content_id' => $contentId,
            'old_state' => $oldState,
            'new_state' => $newState
        ];

        $this->history[] = $entry;
        try {
            if ($this->fallbackLogDir !== null || (file_exists(dirname($this->logFile)) && is_writable(dirname($this->logFile)))) {
                file_put_contents(
                    $this->logFile,
                    json_encode($entry) . PHP_EOL,
                    FILE_APPEND
                );
            } else {
                error_log('ContentStateChange: ' . json_encode($entry));
            }
        } catch (\Exception $e) {
            error_log('ContentStateChange (failed to write log): ' . json_encode($entry));
        }
    }

    public function getHistory(): array {
        return $this->history;
    }

    public function clearHistory(): void {
        $this->history = [];
    }
}
