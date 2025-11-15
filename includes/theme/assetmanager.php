<?php
/**
 * Asset Manager for CMS Themes
 * Handles asset enqueuing and rendering with theme inheritance support
 */
class AssetManager {
    private static $enqueuedAssets = [
        'css' => [],
        'js' => []
    ];

    /**
     * Enqueue a CSS file
     * @param string $handle Unique identifier
     * @param string $path Relative path to asset
     * @param array $dependencies Array of handles this asset depends on
     * @param string|null $version Version string for cache busting
     */
    public static function enqueueStyle(string $handle, string $path, array $dependencies = [], ?string $version = null): void {
        self::$enqueuedAssets['css'][$handle] = [
            'path' => $path,
            'deps' => $dependencies,
            'ver' => $version
        ];
    }

    /**
     * Enqueue a JS file
     * @param string $handle Unique identifier
     * @param string $path Relative path to asset
     * @param array $dependencies Array of handles this asset depends on
     * @param string|null $version Version string for cache busting
     * @param bool $inFooter Whether to load in footer
     */
    public static function enqueueScript(string $handle, string $path, array $dependencies = [], ?string $version = null, bool $inFooter = false): void {
        self::$enqueuedAssets['js'][$handle] = [
            'path' => $path,
            'deps' => $dependencies,
            'ver' => $version,
            'footer' => $inFooter
        ];
    }

    /**
     * Render all enqueued CSS assets
     * @return string HTML link tags
     */
    public static function renderStyles(): string {
        $output = '';
        foreach (self::$enqueuedAssets['css'] as $handle => $asset) {
            $url = self::getAssetUrl($asset['path']);
            $version = $asset['ver'] ? '?ver=' . $asset['ver'] : '';
            $output .= "<link rel='stylesheet' href='{$url}{$version}' />\n";
        }
        return $output;
    }

    /**
     * Render all enqueued JS assets
     * @param bool $inFooter Whether to render footer scripts
     * @return string HTML script tags
     */
    public static function renderScripts(bool $inFooter = false): string {
        $output = '';
        foreach (self::$enqueuedAssets['js'] as $handle => $asset) {
            if ($asset['footer'] === $inFooter) {
                $url = self::getAssetUrl($asset['path']);
                $version = $asset['ver'] ? '?ver=' . $asset['ver'] : '';
                $output .= "<script src='{$url}{$version}'></script>\n";
            }
        }
        return $output;
    }

    /**
     * Get full URL for an asset
     * @param string $relativePath
     * @return string
     */
    public static function getAssetUrl(string $relativePath): string {
        $activeTheme = \includes\ThemeManager::getActiveTheme();
        $themePath = \includes\ThemeManager::getActiveThemePath();
        $parentTheme = \includes\ThemeManager::getParentTheme($activeTheme);

        // Check active theme first
        $fullPath = $themePath . 'assets/' . ltrim($relativePath, '/');
        if (file_exists($fullPath)) {
            return self::pathToUrl($fullPath);
        }

        // Check parent theme if exists
        if ($parentTheme) {
            $parentPath = \includes\ThemeManager::getThemePath($parentTheme);
            $fullPath = $parentPath . 'assets/' . ltrim($relativePath, '/');
            if (file_exists($fullPath)) {
                return self::pathToUrl($fullPath);
            }
        }

        return $relativePath; // Fallback to relative path if not found
    }

    /**
     * Convert filesystem path to URL
     * @param string $path
     * @return string
     */
    private static function pathToUrl(string $path): string {
        $basePath = realpath(__DIR__ . '/../../');
        $relativePath = str_replace($basePath, '', $path);
        return rtrim($_SERVER['REQUEST_URI'], '/') . '/' . ltrim($relativePath, '/');
    }
}
