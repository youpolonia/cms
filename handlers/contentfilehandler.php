<?php
/**
 * Content File Handler - Manages file operations for CMS content
 */
class ContentFileHandler {
    /**
     * Save content to file with directory checks and atomic write
     * @param string $path File path
     * @param string $content Content to save
     * @param int|null $expectedVersion Expected file version (mtime)
     * @param bool $lock Whether to acquire exclusive lock during save
     * @return bool True on success, false on failure
     * @throws ContentFileException On failure
     */
    public static function saveContent(string $path, string $content, ?int $expectedVersion = null, bool $lock = false, string $tenantId): bool {
        if (!\Tenant\Validation::isValidTenantId($tenantId)) {
            throw new ContentFileException("Invalid tenant ID format", 'security_error');
        }
        $sanitizedPath = \Tenant\Validation::sanitizePath($path);
        $resolvedPath = self::resolveTenantPath($sanitizedPath, $tenantId);
        $dir = dirname($resolvedPath);
        
        // Create directory if it doesn't exist
        if (!self::ensureDirectory($dir)) {
            throw new ContentFileException("Failed to create directory: $dir", 'directory_error');
        }

        // Verify directory is writable
        if (!is_writable($dir)) {
            throw new ContentFileException("Directory not writable: $dir", 'permission_error');
        }

        // Check version conflict if expected version provided
        if ($expectedVersion !== null && file_exists($resolvedPath)) {
            $currentVersion = filemtime($resolvedPath);
            if ($currentVersion !== $expectedVersion) {
                throw new VersionConflictException(
                    "Version conflict for $path (expected: $expectedVersion, actual: $currentVersion)",
                    $currentVersion
                );
            }
        }

        // Atomic write via temp file
        $tempPath = $resolvedPath . '.tmp.' . uniqid();
        $handle = null;
        
        try {
            if ($lock) {
                $handle = fopen($path, 'c+');
                if ($handle === false || !flock($handle, LOCK_EX)) {
                    throw new ContentFileException("Failed to acquire exclusive lock on file: $path", 'lock_error');
                }
            }

            // Write to temp file first
            if (file_put_contents($tempPath, $content) === false) {
                throw new ContentFileException("Failed to write temp file: $tempPath", 'write_error');
            }

            // Rename temp file to target (atomic operation)
            if (!rename($tempPath, $resolvedPath)) {
                throw new ContentFileException("Failed to rename temp file to target: $resolvedPath", 'rename_error');
            }

            return true;
        } catch (Exception $e) {
            // Clean up temp file if it exists
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
            throw $e;
        } finally {
            if ($handle !== null) {
                flock($handle, LOCK_UN);
                fclose($handle);
            }
        }
    }

    /**
     * Migrate content with version checking and locking
     * @param array $content Content array to save
     * @param int|null $expectedVersion Expected version
     * @return bool True on success
     * @throws ContentFileException|VersionConflictException On failure
     */
    public static function migrateContent(array $content, ?int $expectedVersion = null): bool {
        if (empty($content['path'])) {
            throw new ContentFileException("Content path is required", 'validation_error');
        }

        return self::saveContent(
            $content['path'],
            json_encode($content),
            $expectedVersion,
            true // Always lock for migrations
        );
    }

    /**
     * Load content from file with optional lock
     * @param string $path File path
     * @param bool $lock Whether to acquire a shared lock
     * @return string|null File content or null on failure
     * @throws ContentFileException On failure
     */
    public static function loadContent(string $path, bool $lock = false, string $tenantId): ?string {
        if (!\Tenant\Validation::isValidTenantId($tenantId)) {
            throw new ContentFileException("Invalid tenant ID format", 'security_error');
        }
        $sanitizedPath = \Tenant\Validation::sanitizePath($path);
        $resolvedPath = self::resolveTenantPath($sanitizedPath, $tenantId);
        if (!file_exists($resolvedPath)) {
            return null;
        }

        if (!is_readable($resolvedPath)) {
            throw new ContentFileException("File not readable: $resolvedPath", 'permission_error');
        }

        $handle = fopen($resolvedPath, 'r');
        if ($handle === false) {
            throw new ContentFileException("Failed to open file: $resolvedPath", 'read_error');
        }

        try {
            if ($lock && !flock($handle, LOCK_SH)) {
                throw new ContentFileException("Failed to acquire lock on file: $path", 'lock_error');
            }

            $content = stream_get_contents($handle);
            if ($content === false) {
                throw new ContentFileException("Failed to read file: $path", 'read_error');
            }

            return $content;
        } finally {
            if ($lock) {
                flock($handle, LOCK_UN);
            }
            fclose($handle);
        }
    }

    /**
     * Resolve tenant-specific file path
     * @param string $path Original path
     * @param string $tenantId Tenant identifier
     * @return string Tenant-specific path
     */
    private static function resolveTenantPath(string $path, string $tenantId): string {
        $baseDir = dirname($path);
        $filename = basename($path);
        $tenantDir = "$baseDir/tenants/$tenantId";
        
        if (!self::ensureDirectory($tenantDir)) {
            throw new ContentFileException("Failed to create tenant directory", 'directory_error');
        }
        
        return "$tenantDir/$filename";
    }

    /**
     * Get file version (last modified time)
     * @param string $path File path
     * @return int|null Unix timestamp or null if file doesn't exist
     */
    public static function getVersion(string $path, string $tenantId): ?int {
        if (!\Tenant\Validation::isValidTenantId($tenantId)) {
            throw new ContentFileException("Invalid tenant ID format", 'security_error');
        }
        $sanitizedPath = \Tenant\Validation::sanitizePath($path);
        $resolvedPath = self::resolveTenantPath($sanitizedPath, $tenantId);
        return file_exists($resolvedPath) ? filemtime($resolvedPath) : null;
    }

    // ... (keep existing ensureDirectory and verifyPermissions methods)
}

class ContentFileException extends Exception {
    public function __construct($message, $code = 'file_error', $previous = null) {
        parent::__construct($message, 0, $previous);
        $this->code = $code;
    }
}

class VersionConflictException extends ContentFileException {
    public $currentVersion;

    public function __construct($message, $currentVersion, $code = 'version_conflict') {
        parent::__construct($message, $code);
        $this->currentVersion = $currentVersion;
    }
}
