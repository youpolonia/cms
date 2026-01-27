<?php
/**
 * Theme/Layout Import Service
 */
class ImportService {
    /**
     * Import a theme from zip archive
     * @param string $zipPath Path to theme zip file
     * @param string $themeName Name for imported theme
     * @return string Path to imported theme
     * @throws Exception On import failure
     */
    public static function importTheme(string $zipPath, string $themeName): string {
        if (!class_exists('ZipArchive')) {
            throw new Exception('ZipArchive extension required for imports');
        }

        if (!file_exists($zipPath)) {
            throw new Exception("Import file not found: $zipPath");
        }

        $targetPath = "assets/themes/$themeName";
        if (is_dir($targetPath)) {
            throw new Exception("Theme already exists: $themeName");
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== TRUE) {
            throw new Exception("Failed to open import archive");
        }

        $tempDir = '';
        try {
            // Validate archive structure
            self::validateArchive($zip);

            // Extract to temp directory first
            require_once __DIR__ . '/../core/tmp_sandbox.php';
            $tempDir = cms_tmp_path('cms_import_' . uniqid());
            if (!mkdir($tempDir, 0755, true)) {
                throw new Exception("Failed to create temp directory");
            }

            if (!$zip->extractTo($tempDir)) {
                throw new Exception("Failed to extract archive");
            }

            // Validate extracted files
            self::validateExtractedTheme($tempDir);

            // Move to final location
            if (!rename($tempDir, $targetPath)) {
                throw new Exception("Failed to move theme to destination");
            }

            return $targetPath;
        } catch (Exception $e) {
            // Cleanup temp directory if it exists
            if ($tempDir && is_dir($tempDir)) {
                self::removeDirectory($tempDir);
            }
            throw $e;
        } finally {
            $zip->close();
        }
    }

    private static function validateArchive(ZipArchive $zip): void {
        // Must have theme.json
        if ($zip->locateName('theme.json') === false) {
            throw new Exception("Invalid theme package: missing theme.json");
        }

        // Check for disallowed paths
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (strpos($filename, '../') !== false) {
                throw new Exception("Invalid path in archive: $filename");
            }
        }
    }

    private static function validateExtractedTheme(string $path): void {
        $themeJson = "$path/theme.json";
        if (!file_exists($themeJson)) {
            throw new Exception("Extracted theme missing theme.json");
        }

        $config = json_decode(file_get_contents($themeJson), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid theme.json format");
        }

        // Validate required fields
        $required = ['name', 'version', 'description'];
        foreach ($required as $field) {
            if (empty($config[$field])) {
                throw new Exception("Missing required field in theme.json: $field");
            }
        }

        // Validate referenced files exist
        foreach (['styles', 'scripts', 'templates'] as $type) {
            if (!empty($config[$type])) {
                foreach ($config[$type] as $file) {
                    if (!file_exists("$path/$file")) {
                        throw new Exception("Referenced file not found: $file");
                    }
                }
            }
        }
    }

    private static function removeDirectory(string $path): void {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($path);
    }
}
