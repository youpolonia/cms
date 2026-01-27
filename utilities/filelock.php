<?php
/**
 * Atomic file operations utility
 */
class FileLock {
    /**
     * @var resource $handle File handle
     */
    private $handle;

    /**
     * Lock file and get exclusive write access
     * @param string $filePath Path to file
     * @param int $timeout Timeout in seconds (default 5)
     * @return bool True if lock acquired
     */
    public static function lockForWrite(string $filePath, int $timeout = 5): bool {
        $handle = fopen($filePath, 'c+');
        if (!$handle) return false;

        $startTime = microtime(true);
        while (!flock($handle, LOCK_EX | LOCK_NB)) {
            if (microtime(true) - $startTime > $timeout) {
                fclose($handle);
                return false;
            }
            usleep(100000); // 100ms
        }
        return true;
    }

    /**
     * Release lock and close file
     * @param resource $handle File handle from lockForWrite()
     */
    public static function unlock($handle): void {
        if (is_resource($handle)) {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
