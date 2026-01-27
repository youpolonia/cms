<?php
/**
 * Blog Manager - Handles all blog business logic
 */
class BlogManager {
    private BlogRepository $repository;

    public function __construct() {
        $this->repository = new BlogRepository();
    }

    /**
     * Get all blog posts
     * @return array List of BlogPost objects
     */
    public function getAllPosts(): array {
        return $this->repository->findAll();
    }

    /**
     * Get single blog post by ID
     * @param int $id Post ID
     * @return BlogPost|null Post object or null if not found
     */
    public function getPostById(int $id): ?BlogPost {
        return $this->repository->findById($id);
    }

    /**
     * Create new blog post
     * @param array $data Post data
     * @return BlogPost Created post
     * @throws InvalidArgumentException If validation fails
     */
    public function createPost(array $data): BlogPost {
        $this->validatePostData($data);
        return $this->repository->create($data);
    }

    /**
     * Update existing blog post
     * @param int $id Post ID
     * @param array $data Update data
     * @return BlogPost Updated post
     * @throws InvalidArgumentException If validation fails
     */
    public function updatePost(int $id, array $data): BlogPost {
        $this->validatePostData($data);
        return $this->repository->update($id, $data);
    }

    /**
     * Delete blog post
     * @param int $id Post ID
     * @return bool True if deleted
     */
    public function deletePost(int $id): bool {
        return $this->repository->delete($id);
    }

    /**
     * Validate post data
     * @param array $data Post data
     * @throws InvalidArgumentException If validation fails
     */
    private function validatePostData(array $data): void {
        if (empty($data['title'])) {
            throw new InvalidArgumentException('Title is required');
        }
        if (empty($data['content'])) {
            throw new InvalidArgumentException('Content is required');
        }
    }
}
