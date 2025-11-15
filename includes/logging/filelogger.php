<?php

class FileLogger {
    private $logFile;
    
    public function __construct($filePath) {
        $this->logFile = $filePath;
        $this->ensureLogDirectoryExists();
    }
    
    private function ensureLogDirectoryExists() {
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    public function log($level, $message) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
    
    public function info($message) {
        $this->log('INFO', $message);
    }
    
    public function error($message) {
        $this->log('ERROR', $message);
    }
    
    public function warning($message) {
        $this->log('WARNING', $message);
    }
}
