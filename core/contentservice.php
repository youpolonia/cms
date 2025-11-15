<?php
/**
 * Core content management service
 */
class ContentService {
    private $db;
    
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function createContent(
        string $title,
        string $content,
        int $userId,
        ?int $tenantId = null
    ): array {
        $contentId = $this->insertContent($title, $content, $userId, $tenantId);
        return $this->getContentById($contentId, $tenantId);
    }

    public function getContent(int $contentId, ?int $tenantId = null): ?array {
        return $this->getContentById($contentId, $tenantId);
    }

    public function updateContent(
        int $contentId,
        array $updates,
        ?int $tenantId = null
    ): array {
        $this->validateContentUpdates($updates);
        $this->applyContentUpdates($contentId, $updates, $tenantId);
        return $this->getContentById($contentId, $tenantId);
    }

    private function insertContent(
        string $title,
        string $content,
        int $userId,
        ?int $tenantId
    ): int {
        $effectiveTenantId = $tenantId ?? $GLOBALS['current_tenant']['id'] ?? null;
        
        if (!$effectiveTenantId) {
            throw new \RuntimeException('Tenant context required');
        }

        // Mock implementation with tenant assignment
        return 1;
    }

    private function getContentById(int $contentId, ?int $tenantId): ?array {
        $effectiveTenantId = $tenantId ?? $GLOBALS['current_tenant']['id'] ?? null;
        
        if (!$effectiveTenantId) {
            throw new \RuntimeException('Tenant context required');
        }

        // Mock implementation with tenant filtering
        if ($contentId === 1) {
            return [
                'id' => $contentId,
                'title' => 'Sample Content',
                'content' => 'Sample content body',
                'state' => 'draft',
                'tenant_id' => $effectiveTenantId
            ];
        }
        return null;
    }

    private function validateContentUpdates(array $updates): void {
        // Validation logic
    }

    private function applyContentUpdates(
        int $contentId,
        array $updates,
        ?int $tenantId
    ): void {
        $effectiveTenantId = $tenantId ?? $GLOBALS['current_tenant']['id'] ?? null;
        
        if (!$effectiveTenantId) {
            throw new \RuntimeException('Tenant context required');
        }

        // Verify content belongs to tenant before updating
        $existing = $this->getContentById($contentId, $effectiveTenantId);
        if (!$existing) {
            throw new \RuntimeException('Content not found or tenant violation');
        }
    }
}
