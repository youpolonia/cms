<?php

namespace Models;

use PDO;
use Exception; // Added for potential exceptions

class ContentItem
{
    public int $id;
    public int $content_type_id;
    public int $author_id;
    public ?int $parent_id;
    public string $title;
    public string $slug;
    public ?string $content_body;
    public ?string $excerpt;
    public string $status;
    public string $visibility;
    public ?string $password;
    public ?string $published_at;
    public string $created_at;
    public string $updated_at;

    // Related objects (can be lazy-loaded)
    public ?ContentType $contentType = null;
    public ?User $author = null; // Assuming a User model exists or will exist

    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Populate ContentItem from an associative array of data.
     * @param array $data
     */
    private function populate(array $data): void
    {
        $this->id = (int)$data['id'];
        $this->content_type_id = (int)$data['content_type_id'];
        $this->author_id = (int)$data['author_id'];
        $this->parent_id = isset($data['parent_id']) ? (int)$data['parent_id'] : null;
        $this->title = $data['title'];
        $this->slug = $data['slug'];
        $this->content_body = $data['content_body'];
        $this->excerpt = $data['excerpt'];
        $this->status = $data['status'];
        $this->visibility = $data['visibility'];
        $this->password = $data['password'];
        $this->published_at = $data['published_at'];
        $this->created_at = $data['created_at'];
        $this->updated_at = $data['updated_at'];
    }

    /**
     * Find a content item by its ID.
     * @param PDO $db
     * @param int $id
     * @return ContentItem|null
     */
    public static function findById(PDO $db, int $id): ?ContentItem
    {
        $stmt = $db->prepare("SELECT * FROM content_items WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $item = new self($db);
        $item->populate($data);
        return $item;
    }

    /**
     * Find a content item by its slug and content type ID.
     * @param PDO $db
     * @param string $slug
     * @param int $contentTypeId
     * @return ContentItem|null
     */
    public static function findBySlug(PDO $db, string $slug, int $contentTypeId): ?ContentItem
    {
        $stmt = $db->prepare("SELECT * FROM content_items WHERE slug = :slug AND content_type_id = :content_type_id");
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':content_type_id', $contentTypeId, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }
        
        $item = new self($db);
        $item->populate($data);
        return $item;
    }

    /**
     * Get the ContentType object for this item.
     * @return ContentType|null
     */
    public function getContentType(): ?ContentType
    {
        if ($this->contentType === null && $this->content_type_id) {
            // Assuming Models\ContentType exists and has findById
            if (class_exists('Models\ContentType')) {
                 $this->contentType = ContentType::findById($this->db, $this->content_type_id);
            }
        }
        return $this->contentType;
    }

    /**
     * Get the User object for the author of this item.
     * @return User|null
     */
    public function getAuthor(): ?User
    {
        if ($this->author === null && $this->author_id) {
            // Assuming Models\User exists and has findById
            if (class_exists('Models\User')) {
                 $this->author = User::findById($this->db, $this->author_id);
            }
        }
        return $this->author;
    }
    
    // TODO: Add methods for custom fields, categories, tags
    // TODO: Add save(), delete(), getAllByContentType() etc.
}
