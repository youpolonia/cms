<?php
if (!class_exists('TokenMonitor')) {
    class TokenMonitor {
    private static $threshold = 0.75; // Default threshold
    private static $historyFile = 'memory-bank/token_history.md';
    private static $growthRates = []; // Tracks context growth rates
    private static $modelRates = [
        'gemini-pro-1.0' => 60, // RPM
        'gemini-pro-2.5' => 30,
        'deepseek/deepseek-chat-v3-0324' => 120
    ];
    private static $requestQueue = [];
    private static $lastRequestTime = 0;

    public static function checkUsage($currentUsage, $maxLimit) {
        $usageRatio = $currentUsage / $maxLimit;
        
        // Calculate dynamic threshold based on growth rate
        self::calculateDynamicThreshold($currentUsage, $maxLimit);
        
        if ($usageRatio >= self::$threshold) {
            self::logWarning($currentUsage, $maxLimit);
            
            // Emergency pruning if approaching critical threshold
            if ($usageRatio >= 0.7) {
                self::emergencyPrune();
            }
            return false;
        }
        
        // Track growth rate
        self::trackGrowthRate($currentUsage);
        return true;
    }

    public static function throttleRequest(string $model): bool {
        $currentTime = microtime(true);
        $rateLimit = self::$modelRates[$model] ?? 60; // Default to 60 RPM
        
        // Calculate minimum time between requests (in seconds)
        $minInterval = 60 / $rateLimit;
        
        // Check if we need to delay
        if ($currentTime - self::$lastRequestTime < $minInterval) {
            $delay = $minInterval - ($currentTime - self::$lastRequestTime);
            usleep($delay * 1000000); // Convert to microseconds
        }
        
        self::$lastRequestTime = microtime(true);
        return true;
    }

    public static function queueRequest(callable $request, string $model): mixed {
        $maxRetries = 3;
        $retryCount = 0;
        $result = null;
        
        while ($retryCount < $maxRetries) {
            try {
                self::throttleRequest($model);
                $result = $request();
                break;
            } catch (Exception $e) {
                $retryCount++;
                $backoff = 1 << $retryCount; // Exponential backoff
                usleep($backoff * 1000000);
                self::logError($model, $e->getMessage(), $retryCount);
            }
        }
        
        return $result;
    }
    
    private static function trackGrowthRate(int $currentUsage): void {
        $now = microtime(true);
        $lastUsage = end(self::$growthRates)['usage'] ?? 0;
        $lastTime = end(self::$growthRates)['time'] ?? $now;
        
        if ($lastUsage > 0) {
            $growthRate = ($currentUsage - $lastUsage) / ($now - $lastTime);
            file_put_contents(
                __DIR__.'/../memory-bank/token_growth.log',
                sprintf("[%s] Growth rate: %.2f tokens/sec\n", date('Y-m-d H:i:s'), $growthRate),
                FILE_APPEND
            );
        }
        
        self::$growthRates[] = [
            'time' => $now,
            'usage' => $currentUsage
        ];
        
        // Keep only last 10 measurements
        if (count(self::$growthRates) > 10) {
            array_shift(self::$growthRates);
        }
    }

    private static function calculateDynamicThreshold(int $currentUsage, int $maxLimit): void {
        if (count(self::$growthRates) < 2) {
            return; // Not enough data yet
        }
        
        $lastRate = ($currentUsage - self::$growthRates[count(self::$growthRates)-2]['usage']) /
                   (microtime(true) - self::$growthRates[count(self::$growthRates)-2]['time']);
        
        // Adjust threshold based on growth rate (faster growth = lower threshold)
        if ($lastRate > 100) { // Rapid growth
            self::$threshold = 0.65;
        } elseif ($lastRate > 50) {
            self::$threshold = 0.7;
        } else {
            self::$threshold = 0.75;
        }
    }

    private static function emergencyPrune(): void {
        // Log emergency state
        file_put_contents(
            __DIR__.'/../memory-bank/emergency_state.md',
            sprintf("## [%s] EMERGENCY PRUNE TRIGGERED\n", date('Y-m-d H:i:s')),
            FILE_APPEND
        );
        
        // Implement pruning logic here
        // This would typically call memory management functions
        // to reduce context size
    }

    private static function logWarning($currentUsage, $maxLimit) {
        $warning = sprintf(
            "## [%s] Token Warning\n- Current: %d tokens\n- Max: %d tokens\n- Ratio: %.2f%%\n",
            date('Y-m-d H:i:s'),
            $currentUsage,
            $maxLimit,
            ($currentUsage / $maxLimit) * 100
        );
        
        file_put_contents(self::$historyFile, $warning, FILE_APPEND);
    }

    private static function logError(string $model, string $error, int $retryCount): void {
        $logEntry = sprintf(
            "[%s] Model: %s - Error: %s - Retry: %d\n",
            date('Y-m-d H:i:s'),
            $model,
            $error,
            $retryCount
        );
        
        file_put_contents(
            __DIR__.'/../memory-bank/request_errors.md',
            $logEntry,
            FILE_APPEND
        );
    }
    }
}
