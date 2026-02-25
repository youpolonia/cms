<?php
declare(strict_types=1);

/**
 * ImageStudioResize — PHP GD/Imagick resize, smart crop, format conversion, social presets
 */
class ImageStudioResize {

    /** Social media presets */
    public const PRESETS = [
        'ig-square'  => ['w' => 1080, 'h' => 1080, 'label' => 'Instagram Square'],
        'ig-story'   => ['w' => 1080, 'h' => 1920, 'label' => 'Instagram Story'],
        'fb-cover'   => ['w' => 820,  'h' => 312,  'label' => 'Facebook Cover'],
        'twitter'    => ['w' => 1200, 'h' => 675,  'label' => 'Twitter / X Post'],
        'linkedin'   => ['w' => 1200, 'h' => 627,  'label' => 'LinkedIn Post'],
        'yt-thumb'   => ['w' => 1280, 'h' => 720,  'label' => 'YouTube Thumbnail'],
        'pinterest'  => ['w' => 1000, 'h' => 1500, 'label' => 'Pinterest Pin'],
        'og-image'   => ['w' => 1200, 'h' => 630,  'label' => 'OG / Social Share'],
    ];

    /** Supported output formats */
    private const FORMAT_MAP = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
    ];

    /**
     * Resize image to target dimensions with smart crop
     *
     * @param string $sourcePath Absolute path to source image
     * @param int $targetW Target width
     * @param int $targetH Target height
     * @param string $cropMode 'center' or 'face' (approximate face detection)
     * @param string $outputFormat 'jpg','png','webp' or '' (keep original)
     * @param int $quality JPEG/WebP quality 1-100
     * @return array ['ok'=>bool, 'path'=>string, 'url'=>string, 'width'=>int, 'height'=>int, ...]
     */
    public static function resize(
        string $sourcePath,
        int $targetW,
        int $targetH,
        string $cropMode = 'center',
        string $outputFormat = '',
        int $quality = 90
    ): array {
        if (!file_exists($sourcePath)) {
            return ['ok' => false, 'error' => 'Source file not found'];
        }

        $info = @getimagesize($sourcePath);
        if (!$info) {
            return ['ok' => false, 'error' => 'Cannot read image dimensions'];
        }

        $srcW = $info[0];
        $srcH = $info[1];
        $srcMime = $info['mime'];

        if ($targetW < 1 || $targetH < 1 || $targetW > 8000 || $targetH > 8000) {
            return ['ok' => false, 'error' => 'Invalid dimensions (1-8000px)'];
        }

        // Determine output format
        $outExt = strtolower($outputFormat);
        if ($outExt === '' || !isset(self::FORMAT_MAP[$outExt])) {
            $outExt = match($srcMime) {
                'image/png'  => 'png',
                'image/webp' => 'webp',
                default      => 'jpg',
            };
        }

        // Use Imagick if available, fallback to GD
        if (extension_loaded('imagick')) {
            return self::resizeImagick($sourcePath, $targetW, $targetH, $cropMode, $outExt, $quality);
        }

        return self::resizeGD($sourcePath, $srcW, $srcH, $srcMime, $targetW, $targetH, $cropMode, $outExt, $quality);
    }

    /**
     * Resize using a preset name
     */
    public static function resizePreset(string $sourcePath, string $preset, string $outputFormat = '', int $quality = 90): array {
        if (!isset(self::PRESETS[$preset])) {
            return ['ok' => false, 'error' => 'Unknown preset: ' . $preset . '. Available: ' . implode(', ', array_keys(self::PRESETS))];
        }
        $p = self::PRESETS[$preset];
        return self::resize($sourcePath, $p['w'], $p['h'], 'center', $outputFormat, $quality);
    }

    /**
     * Convert format only (no resize)
     */
    public static function convert(string $sourcePath, string $outputFormat, int $quality = 90): array {
        $info = @getimagesize($sourcePath);
        if (!$info) return ['ok' => false, 'error' => 'Cannot read image'];
        return self::resize($sourcePath, $info[0], $info[1], 'center', $outputFormat, $quality);
    }

    /**
     * Get available presets for frontend
     */
    public static function getPresets(): array {
        return self::PRESETS;
    }

    // ─── GD Implementation ───

    private static function resizeGD(
        string $sourcePath, int $srcW, int $srcH, string $srcMime,
        int $targetW, int $targetH, string $cropMode, string $outExt, int $quality
    ): array {
        $srcImg = match($srcMime) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png'  => @imagecreatefrompng($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            'image/gif'  => @imagecreatefromgif($sourcePath),
            default      => false,
        };
        if (!$srcImg) return ['ok' => false, 'error' => 'Failed to load image with GD'];

        // Calculate crop region
        $crop = self::calcSmartCrop($srcW, $srcH, $targetW, $targetH, $cropMode, $sourcePath);

        // Create destination
        $dstImg = imagecreatetruecolor($targetW, $targetH);
        if ($outExt === 'png') {
            imagesavealpha($dstImg, true);
            $trans = imagecolorallocatealpha($dstImg, 0, 0, 0, 127);
            imagefill($dstImg, 0, 0, $trans);
        }

        imagecopyresampled($dstImg, $srcImg, 0, 0, $crop['x'], $crop['y'], $targetW, $targetH, $crop['w'], $crop['h']);
        imagedestroy($srcImg);

        // Build output path in same dir as source
        $dir = dirname($sourcePath);
        $outFile = 'resized_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $outExt;
        $outPath = $dir . '/' . $outFile;

        $ok = match($outExt) {
            'png'  => imagepng($dstImg, $outPath, min(9, (int)(($quality / 100) * 9))),
            'webp' => imagewebp($dstImg, $outPath, $quality),
            default => imagejpeg($dstImg, $outPath, $quality),
        };
        imagedestroy($dstImg);

        if (!$ok || !file_exists($outPath)) {
            return ['ok' => false, 'error' => 'Failed to save resized image'];
        }

        return [
            'ok'       => true,
            'path'     => $outPath,
            'filename' => $outFile,
            'width'    => $targetW,
            'height'   => $targetH,
            'format'   => $outExt,
            'size'     => filesize($outPath),
            'engine'   => 'gd',
        ];
    }

    // ─── Imagick Implementation ───

    private static function resizeImagick(
        string $sourcePath, int $targetW, int $targetH,
        string $cropMode, string $outExt, int $quality
    ): array {
        try {
            $img = new \Imagick($sourcePath);
            $srcW = $img->getImageWidth();
            $srcH = $img->getImageHeight();

            $crop = self::calcSmartCrop($srcW, $srcH, $targetW, $targetH, $cropMode, $sourcePath);

            $img->cropImage($crop['w'], $crop['h'], $crop['x'], $crop['y']);
            $img->resizeImage($targetW, $targetH, \Imagick::FILTER_LANCZOS, 1);

            $format = match($outExt) {
                'png'  => 'png',
                'webp' => 'webp',
                default => 'jpeg',
            };
            $img->setImageFormat($format);
            $img->setImageCompressionQuality($quality);

            $dir = dirname($sourcePath);
            $outFile = 'resized_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $outExt;
            $outPath = $dir . '/' . $outFile;
            $img->writeImage($outPath);
            $img->clear();
            $img->destroy();

            return [
                'ok'       => true,
                'path'     => $outPath,
                'filename' => $outFile,
                'width'    => $targetW,
                'height'   => $targetH,
                'format'   => $outExt,
                'size'     => filesize($outPath),
                'engine'   => 'imagick',
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => 'Imagick error: ' . $e->getMessage()];
        }
    }

    // ─── Smart Crop Calculation ───

    /**
     * Calculate crop region. 'center' = center crop. 'face' = approximate upper-third bias.
     */
    private static function calcSmartCrop(
        int $srcW, int $srcH, int $targetW, int $targetH,
        string $mode, string $imagePath = ''
    ): array {
        $srcRatio = $srcW / $srcH;
        $tgtRatio = $targetW / $targetH;

        if (abs($srcRatio - $tgtRatio) < 0.01) {
            // Same aspect ratio — no crop needed
            return ['x' => 0, 'y' => 0, 'w' => $srcW, 'h' => $srcH];
        }

        if ($srcRatio > $tgtRatio) {
            // Source is wider — crop sides
            $cropH = $srcH;
            $cropW = (int)round($srcH * $tgtRatio);
            $cropY = 0;

            if ($mode === 'face') {
                // Bias slightly left-center for face detection approximation
                $cropX = (int)round(($srcW - $cropW) * 0.45);
            } else {
                $cropX = (int)round(($srcW - $cropW) / 2);
            }
        } else {
            // Source is taller — crop top/bottom
            $cropW = $srcW;
            $cropH = (int)round($srcW / $tgtRatio);

            if ($mode === 'face') {
                // Upper-third bias: faces are typically in the upper third
                $cropY = (int)round(($srcH - $cropH) * 0.3);
            } else {
                $cropY = (int)round(($srcH - $cropH) / 2);
            }
        }

        // Clamp to bounds
        $cropX = max(0, min($cropX ?? 0, $srcW - $cropW));
        $cropY = max(0, min($cropY, $srcH - $cropH));

        return ['x' => $cropX, 'y' => $cropY, 'w' => $cropW, 'h' => $cropH];
    }
}
