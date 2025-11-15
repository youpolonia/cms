<?php
declare(strict_types=1);

class AssetVersioner {
    private const VERSION_SEPARATOR = '-v';
    private const HASH_LENGTH = 8;
    private const ALLOWED_EXTENSIONS = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'];
    
    public static function versionAsset(string $path): string {
        $filePath = self::resolveAssetPath($path);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($extension), self::ALLOWED_EXTENSIONS, true)) {
            return $path;
        }

        $hash = self::generateAssetHash($filePath);
        return self::insertVersion($path, $hash);
    }

    private static function resolveAssetPath(string $path): string {
        $basePath = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $assetPath = ltrim($path, '/');
        return realpath("$basePath/$assetPath") ?: $path;
    }

    private static function generateAssetHash(string $filePath): string {
        if (!file_exists($filePath)) {
            return '00000000';
        }
        
        $content = file_get_contents($filePath);
        return substr(md5($content), 0, self::HASH_LENGTH);
    }

    private static function insertVersion(string $path, string $hash): string {
        $dotPos = strrpos($path, '.');
        if ($dotPos === false) {
            return $path;
        }
        
        return substr_replace($path, self::VERSION_SEPARATOR.$hash, $dotPos, 0);
    }
}
