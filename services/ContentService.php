<?php

namespace Services;

use Includes\Content\ContentManager;
use Admin\Security\Models\ContentPolicy;
use Core\Security\SecureSession;

class ContentService
{
    private ContentManager $contentManager;

    public function __construct(ContentManager $contentManager)
    {
        $this->contentManager = $contentManager;
    }

    // Public methods first
    public function getContentBySlug(string $slug, string $type = 'blog'): ?array {
        try {
            $slug = $this->validateString($slug);
            $type = $this->validateContentType($type);
            $tenantId = $GLOBALS['current_tenant']['id'] ?? null;
            
            $policy = new ContentPolicy();
            $userId = SecureSession::get('user_id');
            if (!$policy->canViewContent($userId, $type)) {
                throw new \RuntimeException('You do not have permission to view this content');
            }
            
            return $this->contentManager->getContentBySlug($slug, $type, $tenantId);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * Get paginated content list with filtering and sorting
     *
     * @param int $page Current page (1-based)
     * @param int $pageSize Items per page (default 20)
     * @param array $filters Associative array of filters (type, status)
     * @param string $sortField Field to sort by (date|title|type)
     * @param string $sortDirection Sort direction (asc|desc)
     * @return array ['items' => [], 'total' => 0]
     */
    public function getPaginatedContent(
        int $page = 1,
        int $pageSize = 20,
        array $filters = [],
        string $sortField = 'date',
        string $sortDirection = 'desc'
    ): array {
        try {
            // Validate inputs
            $page = max(1, $page);
            $pageSize = max(1, min(100, $pageSize));
            $sortField = $this->validateSortField($sortField);
            $sortDirection = $this->validateSortDirection($sortDirection);
            
            // Check permissions
            $userId = SecureSession::get('user_id');
            $policy = new ContentPolicy();
            if (!$policy->canListContent($userId)) {
                throw new \RuntimeException('You do not have permission to list content');
            }

            $tenantId = $GLOBALS['current_tenant']['id'] ?? null;
            $offset = ($page - 1) * $pageSize;

            return $this->contentManager->getPaginatedContent(
                $offset,
                $pageSize,
                $filters,
                $sortField,
                $sortDirection,
                $tenantId
            );
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return ['items' => [], 'total' => 0];
        }
    }

    // [Other public methods...]

    // Private methods at the end
    private function validateSortField(string $field): string
    {
        $validFields = ['date', 'title', 'type'];
        if (!in_array($field, $validFields)) {
            throw new \InvalidArgumentException("Invalid sort field: $field");
        }
        return $field;
    }

    private function validateSortDirection(string $direction): string
    {
        $direction = strtolower($direction);
        if (!in_array($direction, ['asc', 'desc'])) {
            throw new \InvalidArgumentException("Invalid sort direction: $direction");
        }
        return $direction;
    }

    private function validateContentType(string $type): string
    {
        $validTypes = ['blog', 'page', 'news', 'product'];
        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException("Invalid content type: $type");
        }
        return $type;
    }
}
