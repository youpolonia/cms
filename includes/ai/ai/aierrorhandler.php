<?php
/**
 * AI Error Handler - Manages retries, fallbacks and detailed logging for AI operations
 * 
 * @package CMS
 * @subpackage AI
 */

class AIErrorHandler {
    private static $errorLog = [];
    private static $successLog = [];
    private static $retryCount = 0;
    private static $maxRetries = 3;
    private static $startTime = 0;
    
    /**
     * Handle AI operation with retry, fallback and detailed logging
     * @param callable $operation AI operation to execute
     * @param string $provider Initial provider to use
     * @param array $input Sanitized input data for logging
     * @return mixed Operation result
     * @throws Exception If all retries and fallbacks fail
     */
    public static function executeWithRetry(callable $operation, string $provider, array $input = []) {
        $providers = TenantAIConfig::getFallbackOrder();
        $currentProvider = $provider;
        self::$startTime = microtime(true);
        
        while (self::$retryCount <= self::$maxRetries) {
            try {
                $result = $operation($currentProvider);
                $duration = microtime(true) - self::$startTime;
                
                self::logSuccess($currentProvider, $input, $result, $duration);
                self::resetState();
                return $result;
            } catch (Exception $e) {
                $duration = microtime(true) - self::$startTime;
                self::logError($currentProvider, $e, $input, $duration);
                
                if (self::$retryCount < self::$maxRetries) {
                    // Exponential backoff
                    $delay = pow(2, self::$retryCount) * 1000;
                    usleep($delay * 1000);
                    self::$retryCount++;
                } else {
                    // Try next provider in fallback order
                    $nextProvider = self::getNextProvider($providers, $currentProvider);
                    if ($nextProvider === null) {
                        throw new Exception("All AI providers failed", 0, new Exception(json_encode(self::$errorLog)));
                    }
                    $currentProvider = $nextProvider;
                    self::$retryCount = 0;
                }
            }
        }
    }
    
    private static function getNextProvider(array $providers, string $current): ?string {
        $currentIndex = array_search($current, $providers);
        return $providers[$currentIndex + 1] ?? null;
    }
    
    private static function sanitizeData($data): array {
        if (!is_array($data)) {
            return ['type' => gettype($data), 'size' => is_string($data) ? strlen($data) : null];
        }
        
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value) && strlen($value) > 100) {
                $sanitized[$key] = substr($value, 0, 100) . '...[' . strlen($value) . ' chars]';
            } elseif (is_array($value) || is_object($value)) {
                $sanitized[$key] = self::sanitizeData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
    
    private static function logError(string $provider, Exception $e, array $input, float $duration): void {
        self::$errorLog[] = [
            'provider' => $provider,
            'status' => 'failed',
            'error' => [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => self::sanitizeData($e->getTrace())
            ],
            'input' => self::sanitizeData($input),
            'timestamp' => time(),
            'duration' => round($duration, 4),
            'retry_count' => self::$retryCount
        ];
    }
    
    private static function logSuccess(string $provider, array $input, $result, float $duration): void {
        self::$successLog[] = [
            'provider' => $provider,
            'status' => 'success',
            'input' => self::sanitizeData($input),
            'output' => self::sanitizeData($result),
            'timestamp' => time(),
            'duration' => round($duration, 4)
        ];
    }
    
    private static function resetState(): void {
        self::$retryCount = 0;
        self::$errorLog = [];
        self::$startTime = 0;
    }
    
    public static function getErrorLog(): array {
        return self::$errorLog;
    }
    
    public static function getSuccessLog(): array {
        return self::$successLog;
    }
    
    public static function getAllLogs(): array {
        return [
            'errors' => self::$errorLog,
            'successes' => self::$successLog
        ];
    }
}
