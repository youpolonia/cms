<?php
/**
 * MediaRegistry - Handles media file metadata storage and retrieval
 */
class MediaRegistry {
    private $db;
    private $mediaDir = '/media/uploads/';
    private $thumbDir = '/media/thumbs/';

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Register new media file with metadata
     */
    public function registerMedia(array $fileData): array {
        // Validate required fields
        $required = ['filename', 'path', 'type', 'size', 'user_id'];
        foreach ($required as $field) {
            if (empty($fileData[$field])) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }

        // Sanitize filename and path
        $fileData['filename'] = $this->sanitizeFilename($fileData['filename']);
        $fileData['path'] = $this->sanitizePath($fileData['path']);

        // Store metadata in database
        $stmt = $this->db->prepare("
            INSERT INTO media_files 
            (filename, path, type, size, width, height, user_id, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $fileData['filename'],
            $fileData['path'],
            $fileData['type'],
            $fileData['size'],
            $fileData['width'] ?? null,
            $fileData['height'] ?? null,
            $fileData['user_id']
        ]);

        return [
            'id' => $this->db->lastInsertId(),
            'filename' => $fileData['filename'],
            'path' => $fileData['path'],
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get media file metadata by ID
     */
    public function getMedia(int $mediaId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM media_files WHERE id = ?");
        $stmt->execute([$mediaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Sanitize filename to prevent directory traversal
     */
    private function sanitizeFilename(string $filename): string {
        return preg_replace('/[^a-zA-Z0-9\-_.]/', '', $filename);
    }

    /**
     * Sanitize path to prevent directory traversal
     */
    private function sanitizePath(string $path): string {
        $path = str_replace('../', '', $path);
        return ltrim($path, '/');
    }
}
