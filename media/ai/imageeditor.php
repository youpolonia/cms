<?php
/**
 * ImageEditor - Provides basic image editing operations
 */
class ImageEditor {
    /**
     * Resize an image
     * @param string $filename Path to image file
     * @param int $width New width in pixels
     * @param int $height New height in pixels
     * @return bool True on success, false on failure
     */
    public static function resize(string $filename, int $width, int $height): bool {
        $image = self::loadImage($filename);
        if (!$image) return false;

        $resized = imagescale($image, $width, $height);
        if (!$resized) return false;

        return self::saveImage($filename, $resized);
    }

    /**
     * Crop an image
     * @param string $filename Path to image file
     * @param int $x X-coordinate of top-left corner
     * @param int $y Y-coordinate of top-left corner
     * @param int $width Width of crop area
     * @param int $height Height of crop area
     * @return bool True on success, false on failure
     */
    public static function crop(string $filename, int $x, int $y, int $width, int $height): bool {
        $image = self::loadImage($filename);
        if (!$image) return false;

        $cropped = imagecrop($image, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
        if (!$cropped) return false;

        return self::saveImage($filename, $cropped);
    }

    /**
     * Remove background from image using AI
     * @param string $filename Path to image file
     * @return string Path to processed image
     */
    public static function removeBackground(string $filename): string {
        $base64Image = AIClient::removeImageBackground($filename);
        $imageData = base64_decode($base64Image);
        if ($imageData === false) {
            throw new Exception('Invalid base64 image data');
        }

        $saveDir = __DIR__ . '/../../media/processed/';
        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0755, true);
        }

        $newFilename = 'bg_removed_' . basename($filename);
        $filepath = $saveDir . $newFilename;

        if (file_put_contents($filepath, $imageData) === false) {
            throw new Exception('Failed to save processed image');
        }

        return $filepath;
    }

    /**
     * Load image from file using GD
     * @param string $filename Path to image file
     * @return resource|false GD image resource or false on failure
     */
    private static function loadImage(string $filename) {
        $type = exif_imagetype($filename);
        switch ($type) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($filename);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($filename);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($filename);
            default:
                return false;
        }
    }

    /**
     * Save image to file using GD
     * @param string $filename Path to save image
     * @param resource $image GD image resource
     * @return bool True on success, false on failure
     */
    private static function saveImage(string $filename, $image): bool {
        $type = exif_imagetype($filename);
        switch ($type) {
            case IMAGETYPE_JPEG:
                return imagejpeg($image, $filename);
            case IMAGETYPE_PNG:
                return imagepng($image, $filename);
            case IMAGETYPE_GIF:
                return imagegif($image, $filename);
            default:
                return false;
        }
    }
}
