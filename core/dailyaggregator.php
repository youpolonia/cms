<?php
/**
 * Daily metrics aggregator for Redis-to-MySQL transfer
 * Implements error recovery and batch processing
 */
class DailyAggregator {
    private static $maxRetries = 3;
    private static $batchSize = 1000;

    /**
     * Process daily metrics from Redis to MySQL
     * @param string $tenantId Optional tenant filter
     * @return array Result stats
     */
    public static function processDailyMetrics(?string $tenantId = null): array {
        $stats = [
            'processed' => 0,
            'errors' => 0,
            'retries' => 0
        ];

        try {
            $redis = self::getRedisConnection();
            $db = \core\Database::connection();
            
            $pattern = $tenantId ? "metrics:$tenantId:*" : "metrics:*";
            $cursor = 0;
            
            do {
                // Scan Redis keys in batches
                $result = $redis->scan($cursor, $pattern, self::$batchSize);
                $cursor = $result[0];
                $keys = $result[1];
                
                if (!empty($keys)) {
                    $metrics = $redis->mget($keys);
                    
                    // Process batch with retry logic
                    $batchResult = self::processBatch($db, $keys, $metrics);
                    
                    $stats['processed'] += $batchResult['processed'];
                    $stats['errors'] += $batchResult['errors'];
                    $stats['retries'] += $batchResult['retries'];
                }
            } while ($cursor != 0);
            
            return $stats;
        } catch (Exception $e) {
            error_log("Aggregation failed: " . $e->getMessage());
            return $stats;
        }
    }

    /**
     * Process batch with retry logic
     */
    private static function processBatch($db, array $keys, array $metrics): array {
        $result = ['processed' => 0, 'errors' => 0, 'retries' => 0];
        $retryCount = 0;
        
        while ($retryCount <= self::$maxRetries) {
            try {
                $db->beginTransaction();
                
                foreach ($keys as $i => $key) {
                    if ($metrics[$i] !== false) {
                        $data = json_decode($metrics[$i], true);
                        self::insertMetric($db, $key, $data);
                        $result['processed']++;
                    }
                }
                
                $db->commit();
                break;
            } catch (Exception $e) {
                $db->rollBack();
                $result['errors']++;
                $result['retries']++;
                $retryCount++;
                
                if ($retryCount > self::$maxRetries) {
                    error_log("Batch failed after retries: " . $e->getMessage());
                    break;
                }
                
                usleep(100000 * $retryCount); // Exponential backoff
            }
        }
        
        return $result;
    }

    /**
     * Get Redis connection
     */
    private static function getRedisConnection() {
        // Implementation depends on your Redis client
    }

    /**
     * Insert metric into database
     */
    private static function insertMetric($db, string $key, array $data): bool {
        // Implementation depends on your schema
        return true;
    }
}
