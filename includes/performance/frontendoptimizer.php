<?php
declare(strict_types=1);

/**
 * Performance - Frontend Optimizer
 * Handles asset optimization for frontend performance
 */
class FrontendOptimizer {
    private static string $cacheDir = __DIR__ . '/../../public/cache/';
    private static string $assetManifest = __DIR__ . '/../../public/assets/manifest.json';
    private static array $optimizedAssets = [];

    /**
     * Initialize optimizer and create required directories
     */
    public static function init(): void {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }

    /**
     * Optimize CSS content
     */
    public static function optimizeCss(string $css): string {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Remove whitespace
        $css = preg_replace('/\s+/', ' ', $css);
        // Remove unnecessary semicolons
        $css = str_replace(';}', '}', $css);
        return trim($css);
    }

    /**
     * Optimize JS content
     */
    public static function optimizeJs(string $js): string {
        // Simple JS minification (basic whitespace removal)
        $js = preg_replace('/\s+/', ' ', $js);
        $js = preg_replace('/\s?([=+\-*\/%&|^!<>?:;,{}()])\s?/', '$1', $js);
        return trim($js);
    }

    /**
     * Get optimized asset URL with cache busting
     */
    public static function getOptimizedAsset(string $path): string {
        if (!isset(self::$optimizedAssets[$path])) {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $content = file_get_contents($path);
            $hash = substr(md5($content), 0, 8);
            
            $optimized = match($ext) {
                'css' => self::optimizeCss($content),
                'js' => self::optimizeJs($content),
                default => $content
            };

            $filename = basename($path, ".$ext") . ".min.$ext";
            $cachePath = self::$cacheDir . $filename;
            file_put_contents($cachePath, $optimized);
            
            self::$optimizedAssets[$path] = "/cache/$filename?hash=$hash";
        }

        return self::$optimizedAssets[$path];
    }

    /**
     * Generate critical CSS for above-the-fold content
     */
    public static function generateCriticalCss(string $html, string $fullCss): string {
        // Basic implementation - would integrate with a service in production
        // This extracts styles for elements visible in viewport
        $criticalCss = '';
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        
        $xpath = new DOMXPath($dom);
        $elements = $xpath->query("//*[contains(@class, 'critical') or contains(@id, 'hero')]");
        
        foreach ($elements as $element) {
            $classes = $element->getAttribute('class');
            $id = $element->getAttribute('id');
            
            if ($classes) {
                foreach (explode(' ', $classes) as $class) {
                    if (preg_match("/\.$class\s*\{[^}]*\}/", $fullCss, $matches)) {
                        $criticalCss .= $matches[0];
                    }
                }
            }
            
            if ($id && preg_match("/#$id\s*\{[^}]*\}/", $fullCss, $matches)) {
                $criticalCss .= $matches[0];
            }
        }

        return self::optimizeCss($criticalCss);
    }

    /**
     * Generate image placeholder for lazy loading
     */
    public static function generateImagePlaceholder(string $imagePath): string {
        $info = getimagesize($imagePath);
        $width = $info[0];
        $height = $info[1];
        
        // Create a tiny placeholder (1px transparent GIF)
        return "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
    }

    // BREAKPOINT: Continue with advanced optimization methods
}
