<?php

namespace Includes\Storage;

class TenantStorage
{
    /**
     * Resolves storage path for tenant-specific assets
     * 
     * @param int $siteId Tenant site ID
     * @param string $path Relative path within tenant storage
     * @return string Full storage path
     */
    public static function resolvePath(int $siteId, string $path = ''): string
    {
        $basePath = STORAGE_PATH . '/sites/' . $siteId . '/';
        
        // Ensure directory exists
        if (!is_dir($basePath)) {
            mkdir($basePath, 0755, true);
        }

        return $basePath . ltrim($path, '/');
    }

    /**
     * Checks if path is in shared global storage
     * 
     * @param string $path Path to check
     * @return bool True if path is in shared storage
     */
    public static function isSharedStorage(string $path): bool
    {
        return strpos($path, STORAGE_PATH . '/shared/') === 0;
    }

    /**
     * Delivers asset from tenant storage
     * 
     * @param string $fullPath Full path to asset
     * @return void Outputs file with appropriate headers
     */
    public static function deliverAsset(string $fullPath): void
    {
        if (!file_exists($fullPath)) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        $mime = mime_content_type($fullPath);
        header('Content-Type: ' . $mime);
        readfile($fullPath);
        exit;
    }
}
