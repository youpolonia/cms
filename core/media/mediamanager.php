<?php
/**
 * MediaManager - Handles all media operations
 * 
 * Features:
 * - File upload validation
 * - Thumbnail generation
 * - Virus scanning
 * - Storage organization
 */
class MediaManager {
    private $validator;
    private $imageProcessor;
    private $storageOrganizer;

    public function __construct() {
        $this->validator = new FileValidator();
        $this->imageProcessor = new ImageProcessor();
        $this->storageOrganizer = new StorageOrganizer();
    }

    /**
     * Process uploaded file
     */
    public function processUpload(array $file): array {
        // Validate file first
        $validation = $this->validator->validate($file);
        if (!$validation['valid']) {
            return $validation;
        }

        // Organize storage
        $storagePath = $this->storageOrganizer->organize($file);

        // Generate thumbnail if image
        if (strpos($file['type'], 'image/') === 0) {
            $thumbnailPath = $this->imageProcessor->generateThumbnail($storagePath);
        }

        return [
            'success' => true,
            'path' => $storagePath,
            'thumbnail' => $thumbnailPath ?? null
        ];
    }

    /**
     * Get file info
     */
    public function getFileInfo(string $path): array {
        return $this->storageOrganizer->getFileInfo($path);
    }
}
