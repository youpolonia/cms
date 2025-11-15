<?php
/**
 * ImageProcessor - Handles image thumbnail generation
 */
class ImageProcessor {
    private $defaultWidth = 200;
    private $defaultHeight = 200;
    private $quality = 80;

    /**
     * Generate thumbnail from source image
     */
    public function generateThumbnail(string $sourcePath, ?int $width = null, ?int $height = null): string {
        $width = $width ?? $this->defaultWidth;
        $height = $height ?? $this->defaultHeight;

        // Get image info and create GD resource
        $imageInfo = getimagesize($sourcePath);
        $source = $this->createImageResource($sourcePath, $imageInfo['mime']);

        // Calculate new dimensions maintaining aspect ratio
        list($newWidth, $newHeight) = $this->calculateDimensions(
            $imageInfo[0], 
            $imageInfo[1], 
            $width, 
            $height
        );

        // Create thumbnail
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled(
            $thumbnail, $source,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $imageInfo[0], $imageInfo[1]
        );

        // Generate thumbnail path and save
        $thumbnailPath = $this->generateThumbnailPath($sourcePath);
        $this->saveImage($thumbnail, $thumbnailPath, $imageInfo['mime']);

        // Clean up
        imagedestroy($source);
        imagedestroy($thumbnail);

        return $thumbnailPath;
    }

    /**
     * Create GD image resource from file
     */
    private function createImageResource(string $path, string $mime) {
        switch ($mime) {
            case 'image/jpeg':
                return imagecreatefromjpeg($path);
            case 'image/png':
                return imagecreatefrompng($path);
            case 'image/gif':
                return imagecreatefromgif($path);
            default:
                throw new Exception("Unsupported image type: $mime");
        }
    }

    /**
     * Calculate new dimensions maintaining aspect ratio
     */
    private function calculateDimensions(int $origWidth, int $origHeight, int $maxWidth, int $maxHeight): array {
        $ratio = $origWidth / $origHeight;
        
        if ($maxWidth / $maxHeight > $ratio) {
            $maxWidth = $maxHeight * $ratio;
        } else {
            $maxHeight = $maxWidth / $ratio;
        }

        return [round($maxWidth), round($maxHeight)];
    }

    /**
     * Generate thumbnail path from source path
     */
    private function generateThumbnailPath(string $sourcePath): string {
        $info = pathinfo($sourcePath);
        return $info['dirname'] . '/' . $info['filename'] . '_thumb.' . $info['extension'];
    }

    /**
     * Save image to file
     */
    private function saveImage($image, string $path, string $mime): void {
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($image, $path, $this->quality);
                break;
            case 'image/png':
                imagepng($image, $path, round(9 * $this->quality / 100));
                break;
            case 'image/gif':
                imagegif($image, $path);
                break;
        }
    }

    /**
     * Set default thumbnail dimensions
     */
    public function setDefaultDimensions(int $width, int $height): void {
        $this->defaultWidth = $width;
        $this->defaultHeight = $height;
    }

    /**
     * Set output quality
     */
    public function setQuality(int $quality): void {
        $this->quality = min(max($quality, 0), 100);
    }
}