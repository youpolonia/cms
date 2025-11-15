<?php
declare(strict_types=1);

namespace CMS\Content;

/**
 * Manages content creation, retrieval, updating and deletion
 */
class ContentManager
{
    private ContentTypeManager $typeManager;
    private ContentRepository $repository;

    public function __construct(ContentTypeManager $typeManager, ContentRepository $repository)
    {
        $this->typeManager = $typeManager;
        $this->repository = $repository;
    }

    /**
     * Create new content
     * @param string $type Content type
     * @param array $data Content data
     * @param int $authorId User ID of content author
     * @param string $status Content status (draft/published/archived)
     * @return int The new content ID
     * @throws \InvalidArgumentException If validation fails
     */
    public function create(string $type, array $data, int $authorId, string $status = 'draft'): int
    {
        $this->validateType($type);
        $this->validateStatus($status);

        $content = new Content($type, $data, $authorId, $status);
        
        try {
            $this->repository->beginTransaction();
            $id = $this->repository->create($content);
            $this->repository->commit();
            return $id;
        } catch (PDOException $e) {
            $this->repository->rollback();
            throw new \RuntimeException("Failed to create content: " . $e->getMessage());
        }
    }

    /**
     * Update existing content
     * @param int $id Content ID
     * @param array $data New content data
     * @param string|null $status New status (optional)
     * @throws \InvalidArgumentException If content doesn't exist or validation fails
     */
    public function update(int $id, array $data, ?string $status = null): void
    {
        $content = $this->repository->find($id);
        if (!$content) {
            throw new \InvalidArgumentException("Content ID $id not found");
        }

        $content->setData(array_merge($content->getData(), $data));
        
        if ($status !== null) {
            $this->validateStatus($status);
            $content->setStatus($status);
        }

        try {
            $this->repository->beginTransaction();
            $this->repository->update($content);
            $this->repository->commit();
        } catch (PDOException $e) {
            $this->repository->rollback();
            throw new \RuntimeException("Failed to update content: " . $e->getMessage());
        }
    }

    /**
     * Get content by ID
     * @param int $id Content ID
     * @return array Content data
     * @throws \InvalidArgumentException If content doesn't exist
     */
    public function get(int $id): array
    {
        $content = $this->repository->find($id);
        if (!$content) {
            throw new \InvalidArgumentException("Content ID $id not found");
        }

        return [
            'id' => $content->getId(),
            'type' => $content->getType(),
            'data' => $content->getData(),
            'author_id' => $content->getAuthorId(),
            'status' => $content->getStatus(),
            'created_at' => $content->getCreatedAt(),
            'updated_at' => $content->getUpdatedAt()
        ];
    }

    /**
     * Delete content
     * @param int $id Content ID
     * @throws \InvalidArgumentException If content doesn't exist
     */
    public function delete(int $id): void
    {
        $content = $this->repository->find($id);
        if (!$content) {
            throw new \InvalidArgumentException("Content ID $id not found");
        }

        try {
            $this->repository->beginTransaction();
            $this->repository->delete($id);
            $this->repository->commit();
        } catch (PDOException $e) {
            $this->repository->rollback();
            throw new \RuntimeException("Failed to delete content: " . $e->getMessage());
        }
    }

    /**
     * Validate content type exists
     * @param string $type Content type to validate
     * @throws \InvalidArgumentException If type doesn't exist
     */
    private function validateType(string $type): void
    {
        $types = $this->typeManager->getContentTypes();
        if (!isset($types[$type])) {
            throw new \InvalidArgumentException("Content type '$type' not registered");
        }
    }

    /**
     * Validate content status
     * @param string $status Status to validate
     * @throws \InvalidArgumentException If status is invalid
     */
    private function validateStatus(string $status): void
    {
        $validStatuses = ['draft', 'published', 'archived'];
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status '$status'");
        }
    }
}
