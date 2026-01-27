<?php
declare(strict_types=1);

class StyleCompiler {
    private static string $cacheDir = 'cache/styles/';
    private static int $cacheTtl = 86400; // 24 hours

    public static function compile(string $tenantId, string $inputPath): string {
        $themeConfig = \includes\ThemeManager::getActiveTheme($tenantId);
        $outputPath = self::getOutputPath($inputPath, $tenantId);

        if (self::isCacheValid($outputPath)) {
            return $outputPath;
        }

        $content = file_get_contents($inputPath);
        $compiled = self::processStyles($content, $themeConfig);
        self::saveCompiled($outputPath, $compiled);

        return $outputPath;
    }

    private static function processStyles(string $content, array $config): string {
        // Variable injection
        foreach ($config['variables'] ?? [] as $var => $value) {
            $content = str_replace("\${$var}", $value, $content);
        }

        // Basic minification
        $content = preg_replace('/\/\*.*?\*\/|\s+/s', ' ', $content);
        return trim($content);
    }

    private static function getOutputPath(string $inputPath, string $tenantId): string {
        $hash = md5_file($inputPath);
        $filename = pathinfo($inputPath, PATHINFO_FILENAME);
        return self::$cacheDir."{$tenantId}_{$filename}_{$hash}.css";
    }

    private static function isCacheValid(string $path): bool {
        return file_exists($path) && 
               (time() - filemtime($path)) < self::$cacheTtl;
    }

    private static function saveCompiled(string $path, string $content): void {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
        file_put_contents($path, $content);
    }

    public static function clearCache(?string $tenantId = null): void {
        $files = glob(self::$cacheDir.'*.css');
        foreach ($files as $file) {
            if (!$tenantId || str_contains($file, $tenantId.'_')) {
                unlink($file);
            }
        }
    }
}
