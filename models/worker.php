<?php
/**
 * Worker Priority Queue Implementation
 */
class Worker {
    const CACHE_DIR = __DIR__ . '/../storage/cache/workers/';
    const AVAILABILITY_CACHE = 'availability.cache';
    const PRIORITIES_CACHE = 'priorities.cache';
    const CACHE_TTL = 300; // 5 minutes in seconds

    /**
     * Gets next available worker based on priority
     * @return int|null Worker ID or null if none available
     */
    public static function getNextAvailableWorker(): ?int {
        // Check cache first
        $cachePath = self::getCachePath(self::AVAILABILITY_CACHE);
        if (self::isCacheValid($cachePath)) {
            $cached = unserialize(file_get_contents($cachePath));
            if (!empty($cached)) {
                return $cached[0]; // Return first available worker
            }
        }

        try {
            $db = \core\Database::connection();
            $query = "SELECT worker_id FROM workers 
                      WHERE is_available = 1 
                      ORDER BY priority DESC, workload ASC 
                      LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchColumn() ?: null;

            // Update cache
            if ($result !== null) {
                self::updateAvailabilityCache([$result]);
            }

            return $result;
        } catch (PDOException $e) {
            LogServiceProvider::error("Worker queue error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Assigns priority to a worker
     * @param int $workerId
     * @param int $priority (1-10, 10 being highest)
     * @return bool True on success
     */
    public static function assignPriority(int $workerId, int $priority): bool {
        try {
            $db = \core\Database::connection();
            $db->beginTransaction();
            
            // Update worker priority
            $update = "UPDATE workers SET priority = ? WHERE id = ?";
            $stmt = $db->prepare($update);
            $stmt->execute([$priority, $workerId]);
            
            // Log priority change
            $log = "INSERT INTO worker_priority_history 
                    (worker_id, priority, assigned_at, assigned_by) 
                    VALUES (?, ?, NOW(), ?)";
            $stmt = $db->prepare($log);
            $stmt->execute([
                $workerId, 
                $priority,
                $_SESSION['user_id'] ?? 0
            ]);
            
            $db->commit();

            // Invalidate priorities cache
            self::invalidateCache(self::PRIORITIES_CACHE);
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            LogServiceProvider::error("Priority assignment failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets full path for cache file
     */
    private static function getCachePath(string $filename): string {
        if (!is_dir(self::CACHE_DIR)) {
            mkdir(self::CACHE_DIR, 0755, true);
        }
        return self::CACHE_DIR . $filename;
    }

    /**
     * Checks if cache is still valid (internal)
     */
    private static function isCacheValid(string $path): bool {
        return file_exists($path) &&
               (time() - filemtime($path)) < self::CACHE_TTL;
    }

    /**
     * Public method for testing cache validity
     */
    public static function testIsCacheValid(string $path): bool {
        return self::isCacheValid($path);
    }

    /**
     * Updates availability cache
     */
    private static function updateAvailabilityCache(array $workerIds): void {
        $path = self::getCachePath(self::AVAILABILITY_CACHE);
        file_put_contents($path, serialize($workerIds));
    }

    /**
     * Invalidates specified cache
     */
    private static function invalidateCache(string $filename): void {
        $path = self::getCachePath($filename);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Delete a worker
     * @param int $id Worker ID
     * @return bool True on success, false on failure
     */
    public function delete(int $id): bool {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("DELETE FROM workers WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
