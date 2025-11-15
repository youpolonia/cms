<?php
/**
 * Theme/Layout Export Service
 */
class ExportService {
    /**
     * Export a theme to zip archive
     * @param string $themeName Theme to export
     * @return string Path to exported zip file
     * @throws Exception On export failure
     */
    public static function exportTheme(string $themeName): string {
        if (!class_exists('ZipArchive')) {
            throw new Exception('ZipArchive extension required for exports');
        }

        $themePath = "assets/themes/$themeName";
        if (!is_dir($themePath)) {
            throw new Exception("Theme directory not found: $themePath");
        }

        $exportPath = "data/exports/theme_{$themeName}_" . date('Ymd_His') . '.zip';
        if (!is_dir(dirname($exportPath))) {
            mkdir(dirname($exportPath), 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($exportPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new Exception("Failed to create export archive");
        }

        try {
            // Add theme.json
            $themeJsonPath = "$themePath/theme.json";
            if (!file_exists($themeJsonPath)) {
                throw new Exception("Missing theme.json in theme directory");
            }
            $zip->addFile($themeJsonPath, 'theme.json');

            // Add referenced assets
            $themeConfig = json_decode(file_get_contents($themeJsonPath), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid theme.json format");
            }
            self::addReferencedFiles($zip, $themeConfig);

            // Add public directory
            self::addDirectory($zip, "$themePath/public", 'public');

            // Add validation metadata
            $zip->addFromString('meta.json', json_encode([
                'export_date' => date('c'),
                'cms_version' => self::getCmsVersion(),
                'checksum' => self::generateChecksum($zip)
            ]));

            if (!$zip->close()) {
                throw new Exception("Failed to finalize export archive");
            }

            return $exportPath;
        } catch (Exception $e) {
            $zip->close();
            @unlink($exportPath);
            throw $e;
        }
    }

    private static function addReferencedFiles(ZipArchive $zip, array $config): void {
        foreach (['styles', 'scripts', 'templates'] as $type) {
            if (!empty($config[$type])) {
                foreach ($config[$type] as $file) {
                    if (file_exists($file)) {
                        $zip->addFile($file, $file);
                    }
                }
            }
        }
    }

    private static function addDirectory(ZipArchive $zip, string $path, string $localPath): void {
        if (!is_dir($path)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($path) + 1);
                $zip->addFile($filePath, "$localPath/$relativePath");
            }
        }
    }

    private static function getCmsVersion(): string {
        // TODO: Replace with actual version check
        return '1.0.0'; 
    }

    private static function generateChecksum(ZipArchive $zip): string {
        return md5(json_encode($zip->statIndex(0)));
    }
}
