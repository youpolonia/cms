<?php
declare(strict_types=1);

namespace CMS\Services;

require_once CMS_ROOT . '/includes/filecache.php';

use FileCache;
use CMS\Services\CacheManager;

class AssetOptimizer {
    private FileCache $fileCache;
    private CacheManager $cacheManager;
    private string $assetsPath;
    private array $config;

    public function __construct(
        FileCache $fileCache,
        CacheManager $cacheManager,
        string $assetsPath,
        array $config = []
    ) {
        $this->fileCache = $fileCache;
        $this->cacheManager = $cacheManager;
        $this->assetsPath = rtrim($assetsPath, '/') . '/';
        $this->config = $config;
    }

    /**
     * Optimize CSS by minifying and adding cache headers
     */
    public function optimizeCss(string $filePath): string {
        $cacheKey = 'css_' . md5($filePath);
        
        if ($cached = $this->cacheManager->getCachedContent($cacheKey)) {
            return $cached;
        }

        $fullPath = $this->assetsPath . ltrim($filePath, '/');
        $css = file_get_contents($fullPath);
        
        // Basic minification
        $optimized = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        $optimized = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $optimized);
        $optimized = str_replace([' {', '{ ', ' }', '} ', ' :', ': ', ' ;', '; ', ' ,', ', '], 
                               ['{', '{', '}', '}', ':', ':', ';', ';', ',', ','], $optimized);

        $this->cacheManager->cacheContent($cacheKey, $optimized, $this->config['css_ttl'] ?? 86400);
        
        return $optimized;
    }

    /**
     * Optimize JS by minifying and adding cache headers
     */
    public function optimizeJs(string $filePath): string {
        $cacheKey = 'js_' . md5($filePath);
        
        if ($cached = $this->cacheManager->getCachedContent($cacheKey)) {
            return $cached;
        }

        $fullPath = $this->assetsPath . ltrim($filePath, '/');
        $js = file_get_contents($fullPath);
        
        // Basic minification
        $optimized = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $js);
        $optimized = preg_replace('/\/\/.*/', '', $optimized);
        $optimized = str_replace(["\r\n", "\r", "\n", "\t"], '', $optimized);
        $optimized = preg_replace('/\s+/', ' ', $optimized);
        $optimized = preg_replace('/\s?([=+\-*\/%&|^~!<>(){}\[\];,:?])\s?/', '$1', $optimized);

        $this->cacheManager->cacheContent($cacheKey, $optimized, $this->config['js_ttl'] ?? 86400);
        
        return $optimized;
    }

    /**
     * Generate versioned asset URL for cache busting
     */
    public function versionAsset(string $filePath): string {
        $fullPath = $this->assetsPath . ltrim($filePath, '/');
        $version = file_exists($fullPath) ? filemtime($fullPath) : time();
        return $filePath . '?v=' . $version;
    }

    /**
     * Optimize and bundle multiple assets
     */
    public function bundleAssets(array $files, string $type): string {
        $cacheKey = 'bundle_' . $type . '_' . md5(implode(',', $files));
        
        if ($cached = $this->cacheManager->getCachedContent($cacheKey)) {
            return $cached;
        }

        $content = '';
        foreach ($files as $file) {
            $fullPath = $this->assetsPath . ltrim($file, '/');
            if (file_exists($fullPath)) {
                $content .= file_get_contents($fullPath) . "\n";
            }
        }

        $optimized = match($type) {
            'css' => $this->optimizeCssString($content),
            'js' => $this->optimizeJsString($content),
            default => $content
        };

        $this->cacheManager->cacheContent($cacheKey, $optimized, $this->config['bundle_ttl'] ?? 86400);
        
        return $optimized;
    }

    private function optimizeCssString(string $css): string {
        return preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    }

    private function optimizeJsString(string $js): string {
        return preg_replace('/\/\/.*/', '', $js);
    }

    /**
     * Preload critical assets
     */
    public function preloadCriticalAssets(): array {
        $criticalCss = $this->bundleAssets(
            $this->config['critical_css'] ?? [],
            'css'
        );
        
        $criticalJs = $this->bundleAssets(
            $this->config['critical_js'] ?? [],
            'js'
        );

        return [
            'css' => $criticalCss,
            'js' => $criticalJs
        ];
    }
}
