<?php
require_once __DIR__ . '/../../core/database.php';

/**
 * Media management core class
 */
class MediaManager {
    
    private static $instance;
    private $db;
    
    private function __construct() {
        $this->db = \core\Database::connection();
    }
    
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function uploadFile(array $file, string $tenantId): array {
        // Validate file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type');
        }
        
        // Create tenant directory if not exists
        $tenantDir = __DIR__ . '/../../public/media/' . $tenantId;
        if (!file_exists($tenantDir)) {
            mkdir($tenantDir, 0755, true);
            mkdir($tenantDir . '/originals', 0755);
            mkdir($tenantDir . '/thumbnails', 0755);
            mkdir($tenantDir . '/cache', 0755);
        }
        
        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $path = $tenantId . '/originals/' . $filename;
        $fullPath = __DIR__ . '/../../public/media/' . $path;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        // Insert database record
        $this->db->query(
            "INSERT INTO media_files 
            (tenant_id, filename, path, mime_type, size) 
            VALUES (?, ?, ?, ?, ?)",
            [
                $tenantId,
                $file['name'],
                $path,
                $file['type'],
                $file['size']
            ]
        );
        
        $mediaId = $this->db->lastInsertId();
        
        return [
            'id' => $mediaId,
            'path' => $path,
            'filename' => $file['name']
        ];
    }
    
    public function getFile(int $id, string $tenantId): ?array {
        $result = $this->db->query(
            "SELECT * FROM media_files 
            WHERE id = ? AND tenant_id = ?",
            [$id, $tenantId]
        )->fetch();
        
        return $result ?: null;
    }
    
    public function deleteFile(int $id, string $tenantId): bool {
        $file = $this->getFile($id, $tenantId);
        if (!$file) {
            return false;
        }
        
        // Delete file from filesystem
        $fullPath = __DIR__ . '/../../public/media/' . $file['path'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        
        // Delete database record (cascade will handle metadata)
        $this->db->query(
            "DELETE FROM media_files WHERE id = ?",
            [$id]
        );
        
        return true;
    }
    
    public function addMetadata(int $mediaId, string $key, string $value): bool {
        return $this->db->query(
            "INSERT INTO media_metadata (media_id, key, value)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE value = VALUES(value)",
            [$mediaId, $key, $value]
        );
    }
    
    public function getMetadata(int $mediaId): array {
        $result = $this->db->query(
            "SELECT key, value FROM media_metadata 
            WHERE media_id = ?",
            [$mediaId]
        )->fetchAll();
        
        $metadata = [];
        foreach ($result as $row) {
            $metadata[$row['key']] = $row['value'];
        }
        
        return $metadata;
    }
}
