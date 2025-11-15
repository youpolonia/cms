<?php
/**
 * StorageOrganizer - Organizes file storage by type and date
 */
class StorageOrganizer {
    private $basePath = 'storage/media';
    private $db;

    public function __construct() {
        // Initialize database connection
        $this->db = new Database(); // Assume this is already available
    }

    /**
     * Organize an uploaded file into the storage system
     */
    public function organize(array $file): string {
        $fileType = $this->getFileType($file['type']);
        $datePath = date('Y/m/d');
        $storageDir = "{$this->basePath}/{$fileType}/{$datePath}";
        
        // Create directory if needed
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $this->generateUniqueFilename($storageDir, $extension);
        $fullPath = "{$storageDir}/{$filename}";

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new Exception("Failed to move uploaded file");
        }

        // Store metadata
        $this->storeMetadata($fullPath, $file);

        return $fullPath;
    }

    /**
     * Get file information
     */
    public function getFileInfo(string $path): array {
        $stmt = $this->db->prepare("
            SELECT * FROM media_metadata 
            WHERE file_path = :path
        ");
        $stmt->execute([':path' => $path]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Categorize file by type
     */
    private function getFileType(string $mimeType): string {
        if (strpos($mimeType, 'image/') === 0) return 'images';
        if (strpos($mimeType, 'video/') === 0) return 'videos';
        if (strpos($mimeType, 'audio/') === 0) return 'audio';
        if ($mimeType === 'application/pdf') return 'documents';
        return 'other';
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(string $dir, string $extension): string {
        do {
            $filename = uniqid() . '.' . $extension;
            $path = "{$dir}/{$filename}";
        } while (file_exists($path));

        return $filename;
    }

    /**
     * Store file metadata in database
     */
    private function storeMetadata(string $path, array $file): void {
        $stmt = $this->db->prepare("
            INSERT INTO media_metadata 
            (file_path, original_name, file_type, file_size, upload_date) 
            VALUES (:path, :name, :type, :size, NOW())
        ");
        $stmt->execute([
            ':path' => $path,
            ':name' => $file['name'],
            ':type' => $file['type'],
            ':size' => $file['size']
        ]);
    }

    /**
     * Set base storage path
     */
    public function setBasePath(string $path): void {
        $this->basePath = rtrim($path, '/');
    }
}
