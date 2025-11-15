<?php

namespace Api\Handlers;

use PDO;
use Models\ContentItem; // Assuming ContentItem model is in Models namespace

class PostsHandler
{
    private ?PDO $db;

    public function __construct(?PDO $db)
    {
        $this->db = $db;
    }

    private function requireDb(): void
    {
        if ($this->db === null) {
            // This should ideally use the global json_response from api.php
            // but to keep handler self-contained for now:
            http_response_code(500);
            echo json_encode(['error' => 'Database connection not available for PostsHandler.']);
            exit;
        }
    }
    
    /**
     * Handles GET requests to /api.php/posts/list
     * Example: /api.php/posts/list?limit=10&offset=0
     */
    public function getList(?string $id = null, array $requestData = []): void
    {
        $this->requireDb();
        
        // For "posts", we need to find the 'post' content type ID first.
        // This assumes a ContentType model or a direct query.
        $contentTypeStmt = $this->db->prepare("SELECT id FROM content_types WHERE slug = 'post' LIMIT 1");
        $contentTypeStmt->execute();
        $contentType = $contentTypeStmt->fetch(PDO::FETCH_ASSOC);

        if (!$contentType) {
            $this->jsonResponse(['error' => "Content type 'post' not found."], 404);
            return;
        }
        $postContentTypeId = (int)$contentType['id'];

        $limit = isset($requestData['limit']) ? (int)$requestData['limit'] : 10;
        $offset = isset($requestData['offset']) ? (int)$requestData['offset'] : 0;

        // Fetch published posts
        $stmt = $this->db->prepare(
            "SELECT id, title, slug, excerpt, published_at, created_at 
             FROM content_items 
             WHERE content_type_id = :content_type_id AND status = 'published'
             ORDER BY published_at DESC 
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindParam(':content_type_id', $postContentTypeId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->jsonResponse($posts);
    }

    /**
     * Handles GET requests to /api.php/posts/get/{id}
     * Example: /api.php/posts/get/123
     */
    public function getGet(?string $id = null, array $requestData = []): void // Method name is get{ActionName}
    {
        $this->requireDb();

        if (empty($id) || !is_numeric($id)) {
            $this->jsonResponse(['error' => 'Post ID is required and must be numeric.'], 400);
            return;
        }
        $postId = (int)$id;

        // For "posts", we need to find the 'post' content type ID first.
        $contentTypeStmt = $this->db->prepare("SELECT id FROM content_types WHERE slug = 'post' LIMIT 1");
        $contentTypeStmt->execute();
        $contentType = $contentTypeStmt->fetch(PDO::FETCH_ASSOC);

        if (!$contentType) {
            $this->jsonResponse(['error' => "Content type 'post' not found."], 404);
            return;
        }
        $postContentTypeId = (int)$contentType['id'];

        // Fetch a specific published post
        // Using ContentItem model if available and suitable
        // $post = ContentItem::findById($this->db, $postId);
        // if ($post && $post->content_type_id === $postContentTypeId && $post->status === 'published') {
        //    $this->jsonResponse($this->formatPostOutput($post));
        // }

        $stmt = $this->db->prepare(
            "SELECT * FROM content_items 
             WHERE id = :id AND content_type_id = :content_type_id AND status = 'published'"
        );
        $stmt->bindParam(':id', $postId, PDO::PARAM_INT);
        $stmt->bindParam(':content_type_id', $postContentTypeId, PDO::PARAM_INT);
        $stmt->execute();
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($post) {
            // Potentially enrich with author, category, tags data later
            $this->jsonResponse($post);
        } else {
            $this->jsonResponse(['error' => 'Post not found or not published.'], 404);
        }
    }
    
    // Helper to format output if using the ContentItem model
    // private function formatPostOutput(ContentItem $item): array
    // {
    //     return [
    //         'id' => $item->id,
    //         'title' => $item->title,
    //         'slug' => $item->slug,
    //         'content_body' => $item->content_body,
    //         'excerpt' => $item->excerpt,
    //         'published_at' => $item->published_at,
    //         'author_id' => $item->author_id, 
    //         // Add author name, categories, tags later
    //     ];
    // }

    /**
     * Helper for JSON response within this handler.
     */
    private function jsonResponse(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}
