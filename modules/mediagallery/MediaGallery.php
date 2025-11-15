<?php
/**
 * MediaGallery - Main facade for media gallery functionality
 */
class MediaGallery {
    private $registry;
    private $uploader;
    private $sanitizer;
    private $browser;

    public function __construct(PDO $db) {
        $this->registry = new MediaRegistry($db);
        $this->uploader = new MediaUploader($this->registry);
        $this->sanitizer = new MediaSanitizer();
        $this->browser = new MediaBrowser($this->registry);
    }

    /**
     * Upload a media file
     */
    public function upload(array $file, int $userId): array {
        // Validate file first
        $this->sanitizer->validateFile($file['tmp_name'], $file['name']);
        
        // Process upload
        return $this->uploader->upload($file, $userId);
    }

    /**
     * Get media by ID
     */
    public function getMedia(int $mediaId): ?array {
        return $this->registry->getMedia($mediaId);
    }

    /**
     * List media with pagination and filters
     */
    public function listMedia(int $page = 1, array $filters = []): array {
        return $this->browser->listMedia($page, $filters);
    }

    /**
     * Search media by query
     */
    public function searchMedia(string $query, int $page = 1): array {
        return $this->browser->searchMedia($query, $page);
    }

    /**
     * Get allowed MIME types for upload
     */
    public function getAllowedTypes(): array {
        return $this->sanitizer->getAllowedTypes();
    }
}
