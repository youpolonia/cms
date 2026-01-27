<?php
/**
 * MediaController - Admin controller for media library operations
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(dirname(__DIR__)));
}
require_once CMS_ROOT . '/config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/core/models/mediamodel.php';

class MediaController {
    private MediaModel $model;
    private \PDO $db;

    public function __construct() {
        $this->db = db();
        $this->model = new MediaModel($this->db);
    }

    /**
     * Get all media with sorting and pagination
     */
    public function index(string $sortBy = 'created_at', string $sortOrder = 'DESC', int $limit = 200): array {
        // Map frontend sort keys to database columns
        $sortMap = [
            'date' => 'created_at',
            'size' => 'size',
            'type' => 'mime_type',
            'name' => 'filename'
        ];

        $dbSortBy = $sortMap[$sortBy] ?? 'created_at';
        return $this->model->getAll($dbSortBy, $sortOrder, $limit);
    }

    /**
     * Get single media item
     */
    public function show(int $id): ?array {
        return $this->model->findById($id);
    }

    /**
     * Get media by filename
     */
    public function findByFilename(string $filename): ?array {
        return $this->model->findByFilename($filename);
    }

    /**
     * Upload a new media file
     */
    public function upload(array $file): array {
        // Validate file
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['ok' => false, 'error' => 'Invalid file upload'];
        }

        // Check MIME type
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'video/mp4', 'application/pdf'
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            return ['ok' => false, 'error' => 'File type not allowed: ' . $mimeType];
        }

        // Check file size (5MB limit)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return ['ok' => false, 'error' => 'File too large. Max 5MB allowed.'];
        }

        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $ext = strtolower(preg_replace('/[^a-z0-9]/i', '', $ext));
        $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        // Move file
        $uploadDir = CMS_ROOT . '/uploads/media/';
        $targetPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['ok' => false, 'error' => 'Failed to save file'];
        }

        // Generate thumbnail for images
        $thumbFilename = null;
        if (strpos($mimeType, 'image/') === 0 && $mimeType !== 'image/gif') {
            $thumbFilename = $this->generateThumbnail($targetPath, $filename);
        }

        // Save to database
        try {
            $id = $this->model->create([
                'filename' => $filename,
                'original_name' => basename($file['name']),
                'mime_type' => $mimeType,
                'size' => $file['size'],
                'path' => 'uploads/media/' . $filename,
                'folder' => 'media'
            ]);

            return [
                'ok' => true,
                'file' => [
                    'id' => $id,
                    'name' => $filename,
                    'original' => $file['name'],
                    'mime' => $mimeType,
                    'size' => $file['size'],
                    'thumb' => $thumbFilename,
                    'url' => '/uploads/media/' . $filename
                ]
            ];
        } catch (\Exception $e) {
            // Clean up uploaded file on error
            @unlink($targetPath);
            return ['ok' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Delete a media file
     */
    public function delete(string $filename): array {
        // Security: prevent path traversal
        $filename = basename($filename);

        // Find in database
        $media = $this->model->findByFilename($filename);
        if (!$media) {
            return ['ok' => false, 'error' => 'File not found in database'];
        }

        $uploadDir = CMS_ROOT . '/uploads/media/';
        $filePath = $uploadDir . $filename;

        // Verify file is within allowed directory
        $realPath = realpath($filePath);
        $realUploadDir = realpath($uploadDir);
        if ($realPath && strpos($realPath, $realUploadDir) !== 0) {
            return ['ok' => false, 'error' => 'Invalid file path'];
        }

        // Delete file from disk
        if ($realPath && file_exists($realPath)) {
            if (!@unlink($realPath)) {
                return ['ok' => false, 'error' => 'Failed to delete file from disk'];
            }
        }

        // Delete thumbnail if exists
        $thumbPath = $uploadDir . 'thumbs/' . pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg';
        if (file_exists($thumbPath)) {
            @unlink($thumbPath);
        }

        // Delete from database
        if (!$this->model->deleteByFilename($filename)) {
            return ['ok' => false, 'error' => 'Failed to delete from database'];
        }

        return ['ok' => true, 'message' => 'File deleted successfully'];
    }

    /**
     * Update media metadata (title, alt_text, description)
     */
    public function update(int $id, array $data): array {
        $media = $this->model->findById($id);
        if (!$media) {
            return ['ok' => false, 'error' => 'Media not found'];
        }

        if ($this->model->update($id, $data)) {
            return ['ok' => true, 'message' => 'Media updated successfully'];
        }

        return ['ok' => false, 'error' => 'Failed to update media'];
    }

    /**
     * Search media files
     */
    public function search(string $query): array {
        return $this->model->search($query);
    }

    /**
     * Get total media count
     */
    public function count(): int {
        return $this->model->count();
    }

    /**
     * Generate thumbnail for image
     */
    private function generateThumbnail(string $sourcePath, string $filename): ?string {
        $thumbDir = CMS_ROOT . '/uploads/media/thumbs/';
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        $thumbFilename = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumbPath = $thumbDir . $thumbFilename;

        // Get image info
        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            return null;
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $type = $imageInfo[2];

        // Target thumbnail size
        $maxWidth = 300;
        $maxHeight = 200;

        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        if ($ratio >= 1) {
            $newWidth = $width;
            $newHeight = $height;
        } else {
            $newWidth = (int) ($width * $ratio);
            $newHeight = (int) ($height * $ratio);
        }

        // Create source image based on type
        $sourceImage = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($sourcePath),
            default => null
        };

        if (!$sourceImage) {
            return null;
        }

        // Create thumbnail
        $thumbImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG
        if ($type === IMAGETYPE_PNG) {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
            $transparent = imagecolorallocatealpha($thumbImage, 255, 255, 255, 127);
            imagefilledrectangle($thumbImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled(
            $thumbImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight, $width, $height
        );

        // Save as JPEG
        $result = imagejpeg($thumbImage, $thumbPath, 85);

        imagedestroy($sourceImage);
        imagedestroy($thumbImage);

        return $result ? $thumbFilename : null;
    }

    /**
     * Get image metadata (resolution)
     */
    public function getImageMetadata(string $filename): array {
        $filePath = CMS_ROOT . '/uploads/media/' . basename($filename);
        if (!file_exists($filePath)) {
            return [];
        }

        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo) {
            return [];
        }

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'resolution' => $imageInfo[0] . 'x' . $imageInfo[1]
        ];
    }
}
