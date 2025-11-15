<?php
require_once __DIR__.'/loggerfactory.php';

class TaskLogger implements LoggerInterface {
    private $logger;
    
    public function __construct(LoggerInterface $logger = null) {
        $this->logger = $logger ?? LoggerFactory::create('task');
    }
    
    // PSR-3 Methods
    public function emergency(string $message, array $context = []) {
        $this->logger->emergency($message, $context);
    }
    
    public function alert(string $message, array $context = []) {
        $this->logger->alert($message, $context);
    }
    
    public function critical(string $message, array $context = []) {
        $this->logger->critical($message, $context);
    }
    
    public function error(string $message, array $context = []) {
        $this->logger->error($message, $context);
    }
    
    public function warning(string $message, array $context = []) {
        $this->logger->warning($message, $context);
    }
    
    public function notice(string $message, array $context = []) {
        $this->logger->notice($message, $context);
    }
    
    public function info(string $message, array $context = []) {
        $this->logger->info($message, $context);
    }
    
    public function debug(string $message, array $context = []) {
        $this->logger->debug($message, $context);
    }
    
    // Backward compatibility methods
    public function logSuccess(int $taskId, string $output, float $duration): void {
        $message = sprintf(
            "Task #%d completed in %.3fs - Output: %s",
            $taskId,
            $duration,
            $this->sanitizeOutput($output)
        );
        $this->info($message, ['task_id' => $taskId]);
    }
    
    public function logError(int $taskId, Exception $e): void {
        $message = sprintf(
            "Task #%d failed - Error: %s (File: %s, Line: %d)",
            $taskId,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        $this->error($message, [
            'task_id' => $taskId,
            'exception' => $e->getTraceAsString()
        ]);
    }
    
    private function sanitizeOutput(string $output): string {
        return trim(preg_replace('/\s+/', ' ', $output));
    }
}
