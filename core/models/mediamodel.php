<?php

/**
 * MediaModel - Database operations for media files
 */
class MediaModel {
    private \PDO $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    /**
     * Find media by ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM media WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $media = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $media ?: null;
    }

    /**
     * Find media by filename
     */
    public function findByFilename(string $filename): ?array {
        $stmt = $this->db->prepare("SELECT * FROM media WHERE filename = :filename");
        $stmt->execute([':filename' => $filename]);
        $media = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $media ?: null;
    }

    /**
     * Get all media with sorting and pagination
     */
    public function getAll(string $sortBy = 'created_at', string $sortOrder = 'DESC', int $limit = 200, int $offset = 0): array {
        // Whitelist allowed sort columns
        $allowedSort = ['created_at', 'size', 'mime_type', 'filename', 'original_name'];
        if (!in_array($sortBy, $allowedSort)) {
            $sortBy = 'created_at';
        }
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $query = "SELECT * FROM media ORDER BY {$sortBy} {$sortOrder} LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Count total media records
     */
    public function count(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM media");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Create a new media record
     */
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO media (filename, original_name, mime_type, size, path, title, alt_text, description, folder, created_at)
            VALUES (:filename, :original_name, :mime_type, :size, :path, :title, :alt_text, :description, :folder, NOW())
        ");

        $stmt->execute([
            ':filename' => $data['filename'],
            ':original_name' => $data['original_name'] ?? $data['filename'],
            ':mime_type' => $data['mime_type'],
            ':size' => $data['size'],
            ':path' => $data['path'],
            ':title' => $data['title'] ?? null,
            ':alt_text' => $data['alt_text'] ?? null,
            ':description' => $data['description'] ?? null,
            ':folder' => $data['folder'] ?? 'media'
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update a media record
     */
    public function update(int $id, array $data): bool {
        $updates = [];
        $params = [':id' => $id];

        $allowedFields = ['title', 'alt_text', 'description', 'folder'];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $query = "UPDATE media SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Delete a media record
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM media WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Delete a media record by filename
     */
    public function deleteByFilename(string $filename): bool {
        $stmt = $this->db->prepare("DELETE FROM media WHERE filename = :filename");
        return $stmt->execute([':filename' => $filename]);
    }

    /**
     * Get media by MIME type prefix (e.g., 'image/', 'video/')
     */
    public function getByMimePrefix(string $prefix, int $limit = 100): array {
        $stmt = $this->db->prepare("
            SELECT * FROM media
            WHERE mime_type LIKE :prefix
            ORDER BY created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':prefix', $prefix . '%', \PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Search media by filename or original name
     */
    public function search(string $query, int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT * FROM media
            WHERE filename LIKE :query OR original_name LIKE :query
            ORDER BY created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':query', '%' . $query . '%', \PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
