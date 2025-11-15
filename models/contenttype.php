<?php

namespace Models;

use PDO;

class ContentType
{
    public int $id;
    public string $name;
    public string $slug;
    public ?string $description;
    public bool $is_hierarchical;
    public bool $has_tags;
    public bool $has_categories;
    public string $created_at;
    public string $updated_at;

    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find a content type by its ID.
     * @param PDO $db
     * @param int $id
     * @return ContentType|null
     */
    public static function findById(PDO $db, int $id): ?ContentType
    {
        $stmt = $db->prepare("SELECT * FROM content_types WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $contentType = new self($db);
        $contentType->id = (int)$data['id'];
        $contentType->name = $data['name'];
        $contentType->slug = $data['slug'];
        $contentType->description = $data['description'];
        $contentType->is_hierarchical = (bool)$data['is_hierarchical'];
        $contentType->has_tags = (bool)$data['has_tags'];
        $contentType->has_categories = (bool)$data['has_categories'];
        $contentType->created_at = $data['created_at'];
        $contentType->updated_at = $data['updated_at'];
        
        return $contentType;
    }

    /**
     * Find a content type by its slug.
     * @param PDO $db
     * @param string $slug
     * @return ContentType|null
     */
    public static function findBySlug(PDO $db, string $slug): ?ContentType
    {
        $stmt = $db->prepare("SELECT * FROM content_types WHERE slug = :slug");
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }
        // Map data to object properties (similar to findById)
        $contentType = new self($db);
        // ... (mapping code as in findById) ...
        $contentType->id = (int)$data['id'];
        $contentType->name = $data['name'];
        $contentType->slug = $data['slug'];
        $contentType->description = $data['description'];
        $contentType->is_hierarchical = (bool)$data['is_hierarchical'];
        $contentType->has_tags = (bool)$data['has_tags'];
        $contentType->has_categories = (bool)$data['has_categories'];
        $contentType->created_at = $data['created_at'];
        $contentType->updated_at = $data['updated_at'];

        return $contentType;
    }
    
    // Potentially add save(), delete(), getAll() methods later
}
