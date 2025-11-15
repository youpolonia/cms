<?php
/**
 * Path resolution utility for the CMS
 */
class PathResolver {
    /**
     * Resolves a relative path to absolute from project root
     * @param string $relativePath Path relative to project root
     * @return string|false Absolute path or false if invalid/missing
     */
    public static function resolve(string $relativePath) {
        // Normalize slashes and remove any directory traversal attempts
        $cleanPath = str_replace(['\\', '../'], ['/', ''], $relativePath);
        $cleanPath = ltrim($cleanPath, '/');
        
        // Build absolute path from project root
        $absolutePath = __DIR__ . '/../../' . $cleanPath;
        $absolutePath = realpath($absolutePath);
        
        // Verify the path exists and is within project boundaries
        if ($absolutePath && strpos($absolutePath, realpath(__DIR__ . '/../../')) === 0) {
            return $absolutePath;
        }
        
        return false;
    }
}
