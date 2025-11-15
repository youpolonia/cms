<?php
require_once __DIR__ . '/../../../media/ai/MediaAIAssistant.php';
/**
 * MediaUploader - Handles secure file uploads
 */
class MediaUploader {
    private $registry;
    private $allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'video/mp4',
        'audio/mpeg'
    ];
    private $maxFileSize = 10485760; // 10MB

    public function __construct(MediaRegistry $registry) {
        $this->registry = $registry;
    }

    /**
     * Process file upload
     */
    public function upload(array $file, int $userId): array {
        // Validate file upload
        $this->validateUpload($file);

        // Generate safe filename and path
        $fileInfo = $this->generateFileInfo($file['name']);
        $targetPath = $this->getUploadPath() . $fileInfo['path'];

        // Create directory if needed
        $this->ensureDirectoryExists(dirname($targetPath));

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Failed to move uploaded file');
        }

        // Generate AI description
        $aiData = MediaAIAssistant::generateDescription($targetPath);

        // Store metadata
        return $this->registry->registerMedia([
            'filename' => $fileInfo['filename'],
            'path' => $fileInfo['path'],
            'type' => $file['type'],
            'size' => $file['size'],
            'user_id' => $userId,
            'ai_title' => $aiData['title'],
            'ai_description' => $aiData['description'],
            'ai_tags' => implode(',', $aiData['tags'])
        ]);
    }

    /**
     * Validate file upload meets requirements
     */
    private function validateUpload(array $file): void {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File upload error: ' . $file['error']);
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new RuntimeException('File size exceeds maximum limit');
        }

        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new RuntimeException('File type not allowed');
        }
    }

    /**
     * Generate safe filename and path structure
     */
    private function generateFileInfo(string $originalName): array {
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . strtolower($ext);
        $path = date('Y/m/') . $filename;

        return [
            'filename' => $filename,
            'path' => $path
        ];
    }

    /**
     * Get base upload directory
     */
    private function getUploadPath(): string {
        return $_SERVER['DOCUMENT_ROOT'] . '/media/uploads/';
    }

    /**
     * Ensure directory exists
     */
    private function ensureDirectoryExists(string $path): void {
        if (!is_dir($path) && !mkdir($path, 0755, true)) {
            throw new RuntimeException("Failed to create directory: $path");
        }
    }
}
