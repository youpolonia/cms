<?php
/**
 * Image Optimizer — thumbnail generation, WebP conversion, responsive images
 * 
 * Shared hosting compatible (GD only, no exec/shell_exec).
 * 
 * @package JessieCMS
 * @since 2026-02-15
 */

class ImageOptimizer
{
    /** Standard thumbnail sizes */
    const SIZES = [
        'thumbnail' => ['width' => 150, 'height' => 150, 'crop' => true],
        'medium'    => ['width' => 600, 'height' => 0, 'crop' => false],
        'large'     => ['width' => 1200, 'height' => 0, 'crop' => false],
    ];

    /** Supported MIME types */
    const SUPPORTED = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    /**
     * Generate all thumbnail sizes for an uploaded image
     * 
     * @param string $sourcePath Full path to the original image
     * @return array Associative array of size name => file path (only successfully created)
     */
    public static function generateThumbnails(string $sourcePath): array
    {
        if (!file_exists($sourcePath) || !extension_loaded('gd')) {
            return [];
        }

        $info = @getimagesize($sourcePath);
        if (!$info || !in_array($info['mime'], self::SUPPORTED)) {
            return [];
        }

        $dir = dirname($sourcePath);
        $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        $base = pathinfo($sourcePath, PATHINFO_FILENAME);
        $results = [];

        foreach (self::SIZES as $sizeName => $spec) {
            // Skip if source is smaller than target
            if ($info[0] <= $spec['width'] && ($spec['height'] === 0 || $info[1] <= $spec['height'])) {
                continue;
            }

            $destPath = $dir . '/' . $base . '-' . $sizeName . '.' . $ext;

            if (self::resize($sourcePath, $destPath, $spec['width'], $spec['height'], $spec['crop'])) {
                $results[$sizeName] = $destPath;
            }
        }

        return $results;
    }

    /**
     * Resize an image using GD
     */
    public static function resize(string $source, string $dest, int $width, int $height, bool $crop = false): bool
    {
        $info = @getimagesize($source);
        if (!$info) return false;

        $srcW = $info[0];
        $srcH = $info[1];

        // Calculate dimensions
        if ($crop && $height > 0) {
            // Crop to exact dimensions
            $ratio = max($width / $srcW, $height / $srcH);
            $newW = (int)ceil($srcW * $ratio);
            $newH = (int)ceil($srcH * $ratio);
            $cropX = (int)(($newW - $width) / 2);
            $cropY = (int)(($newH - $height) / 2);
        } else {
            // Scale proportionally
            if ($height === 0) {
                $ratio = $width / $srcW;
                $height = (int)round($srcH * $ratio);
            } else {
                $ratio = min($width / $srcW, $height / $srcH);
                $width = (int)round($srcW * $ratio);
                $height = (int)round($srcH * $ratio);
            }
            $newW = $width;
            $newH = $height;
            $cropX = 0;
            $cropY = 0;
        }

        // Create source GD image
        $srcImg = self::createFromFile($source, $info['mime']);
        if (!$srcImg) return false;

        if ($crop && isset($ratio)) {
            // Two-step: resize then crop
            $tmpImg = imagecreatetruecolor($newW, $newH);
            self::preserveTransparency($tmpImg, $info['mime']);
            imagecopyresampled($tmpImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);

            $destImg = imagecreatetruecolor($width, $height);
            self::preserveTransparency($destImg, $info['mime']);
            imagecopy($destImg, $tmpImg, 0, 0, $cropX, $cropY, $width, $height);
            imagedestroy($tmpImg);
        } else {
            $destImg = imagecreatetruecolor($width, $height);
            self::preserveTransparency($destImg, $info['mime']);
            imagecopyresampled($destImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcW, $srcH);
        }

        imagedestroy($srcImg);

        $result = self::saveImage($destImg, $dest, $info['mime']);
        imagedestroy($destImg);

        return $result;
    }

    /**
     * Convert image to WebP format
     * 
     * @return string|null Path to WebP file, or null on failure
     */
    public static function toWebP(string $sourcePath, int $quality = 80): ?string
    {
        if (!function_exists('imagewebp') || !file_exists($sourcePath)) {
            return null;
        }

        $info = @getimagesize($sourcePath);
        if (!$info) return null;

        $srcImg = self::createFromFile($sourcePath, $info['mime']);
        if (!$srcImg) return null;

        $webpPath = preg_replace('/\.(jpe?g|png|gif)$/i', '.webp', $sourcePath);
        $result = imagewebp($srcImg, $webpPath, $quality);
        imagedestroy($srcImg);

        return $result ? $webpPath : null;
    }

    /**
     * Get responsive srcset string for a media file
     */
    public static function srcset(string $filename): string
    {
        $dir = (defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__)) . '/uploads/media';
        $webDir = '/uploads/media';
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $parts = [];

        foreach (self::SIZES as $sizeName => $spec) {
            $thumbFile = $base . '-' . $sizeName . '.' . $ext;
            if (file_exists($dir . '/' . $thumbFile)) {
                $parts[] = $webDir . '/' . $thumbFile . ' ' . $spec['width'] . 'w';
            }
        }

        // Add original as largest
        if (file_exists($dir . '/' . $filename)) {
            $info = @getimagesize($dir . '/' . $filename);
            if ($info) {
                $parts[] = $webDir . '/' . $filename . ' ' . $info[0] . 'w';
            }
        }

        return implode(', ', $parts);
    }

    /**
     * Generate an optimized <img> tag with lazy loading and srcset
     */
    public static function imgTag(string $src, string $alt = '', string $class = '', string $sizes = '100vw'): string
    {
        $alt = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');
        $class = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');

        $attrs = 'loading="lazy" decoding="async"';
        if ($class) $attrs .= ' class="' . $class . '"';

        // Try to generate srcset
        $filename = basename($src);
        $srcsetStr = self::srcset($filename);

        if ($srcsetStr) {
            $sizes = htmlspecialchars($sizes, ENT_QUOTES, 'UTF-8');
            return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . $alt . '" srcset="' . $srcsetStr . '" sizes="' . $sizes . '" ' . $attrs . '>';
        }

        return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . $alt . '" ' . $attrs . '>';
    }

    // === Private helpers ===

    private static function createFromFile(string $path, string $mime): ?\GdImage
    {
        return match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path) ?: null,
            'image/png'  => @imagecreatefrompng($path) ?: null,
            'image/gif'  => @imagecreatefromgif($path) ?: null,
            'image/webp' => function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($path) ?: null) : null,
            default      => null,
        };
    }

    private static function saveImage(\GdImage $img, string $path, string $mime, int $quality = 85): bool
    {
        return match ($mime) {
            'image/jpeg' => imagejpeg($img, $path, $quality),
            'image/png'  => imagepng($img, $path, 6),
            'image/gif'  => imagegif($img, $path),
            'image/webp' => function_exists('imagewebp') ? imagewebp($img, $path, $quality) : false,
            default      => false,
        };
    }

    private static function preserveTransparency(\GdImage $img, string $mime): void
    {
        if ($mime === 'image/png' || $mime === 'image/gif') {
            imagealphablending($img, false);
            imagesavealpha($img, true);
            $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
            imagefill($img, 0, 0, $transparent);
        }
    }
}
