<?php
/**
 * File system utilities
 */
class FileUtils {
    /**
     * Check if directory exists and is writable
     * @param string $path Directory path
     * @return bool True if directory exists and is writable
     */
    public static function is_directory_writable(string $path): bool {
        if (!is_dir($path)) {
            return false;
        }
        
        // Check write permissions by attempting to create temp file
        $temp_file = rtrim($path, '/') . '/.tmp_' . uniqid();
        if (@file_put_contents($temp_file, 'test') === false) {
            return false;
        }
        
        unlink($temp_file);
        return true;
    }
}
