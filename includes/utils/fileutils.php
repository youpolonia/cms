<?php

namespace Includes\Utils;

class FileUtils {
    /**
     * Checks if a directory exists and is writable
     * @param string $path Directory path
     * @return bool True if directory exists and is writable
     */
    public static function is_directory_writable(string $path): bool {
        return is_dir($path) && is_writable($path);
    }

    /**
     * Recursively scans a directory and returns file information
     * @param string $directory Path to scan
     * @param bool $includeHidden Whether to require_once hidden files (starting with .)
     * @param bool $followSymlinks Whether to follow symbolic links
     * @return array Array of file information with keys: path, size, modified
     * @throws \RuntimeException If directory cannot be read
     */
    public static function scanDirectory(string $directory, bool $includeHidden = false, bool $followSymlinks = false): array {
        if (!self::is_directory_writable($directory)) {
            throw new \RuntimeException("Cannot read directory: $directory");
        }

        $files = [];
        $flags = \FilesystemIterator::SKIP_DOTS;
        if ($followSymlinks) {
            $flags |= \FilesystemIterator::FOLLOW_SYMLINKS;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, $flags),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if (!$includeHidden && $file->getFilename()[0] === '.') {
                continue;
            }

            if ($file->isFile()) {
                $files[] = [
                    'path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime()
                ];
            }
        }

        return $files;
    }
}
