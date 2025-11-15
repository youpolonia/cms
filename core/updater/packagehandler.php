<?php
namespace Core\Updater;

use Core\Updater\Exceptions\ValidationException;
use ZipArchive;

class PackageHandler
{
    private $tempDir;

    public function __construct()
    {
        require_once __DIR__ . '/../tmp_sandbox.php';
        $this->tempDir = cms_tmp_path('cms_updates') . '/';
        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    /**
     * Downloads an update package with validation
     */
    public function download(string $url, string $expectedChecksum): string
    {
        $tempFile = $this->tempDir . uniqid('update_') . '.zip';
        
        // Download file
        $fileContents = file_get_contents($url);
        if ($fileContents === false) {
            throw new ValidationException("Failed to download package");
        }

        // Save to temp file
        if (file_put_contents($tempFile, $fileContents) === false) {
            throw new ValidationException("Failed to save package");
        }

        // Validate checksum
        $actualChecksum = hash_file('sha256', $tempFile);
        if ($actualChecksum !== $expectedChecksum) {
            unlink($tempFile);
            throw new ValidationException("Checksum verification failed");
        }

        return $tempFile;
    }

    /**
     * Extracts update package to target directory
     */
    public function extract(string $packagePath, string $targetDir): array
    {
        $zip = new ZipArchive;
        if ($zip->open($packagePath) !== true) {
            throw new ValidationException("Invalid package format");
        }

        $extractedFiles = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $targetPath = $targetDir . '/' . $filename;

            // Skip directories
            if (substr($filename, -1) === '/') {
                continue;
            }

            // Validate path is within allowed directories
            if (!$this->isAllowedPath($filename)) {
                continue;
            }

            // Ensure target directory exists
            $targetDirPath = dirname($targetPath);
            if (!file_exists($targetDirPath)) {
                mkdir($targetDirPath, 0755, true);
            }

            // Extract file
            if ($zip->extractTo($targetDir, [$filename])) {
                $extractedFiles[] = $filename;
            }
        }

        $zip->close();
        return $extractedFiles;
    }

    private function isAllowedPath(string $path): bool
    {
        $allowedPrefixes = ['core/', 'plugins/', 'templates/'];
        foreach ($allowedPrefixes as $prefix) {
            if (strpos($path, $prefix) === 0) {
                return true;
            }
        }
        return false;
    }
}
