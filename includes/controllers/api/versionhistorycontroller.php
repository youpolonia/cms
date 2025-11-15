<?php
require_once __DIR__.'/../../Core/ApiResponse.php';
require_once __DIR__ . '/../../models/versionmodel.php';

class VersionHistoryController {
    private $versionModel;

    public function __construct() {
        $this->versionModel = new VersionModel();
    }

    /**
     * List all versions with pagination and filtering
     */
    public function listVersions($tenantId) {
        try {
            if (empty($tenantId)) {
                throw new Exception('Tenant ID is required');
            }
            $filters = [
                'tenant_id' => $tenantId,
                'content_type' => $_GET['content_type'] ?? null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null,
                'search' => $_GET['search'] ?? null,
                'sort' => $_GET['sort'] ?? 'created_at',
                'order' => $_GET['order'] ?? 'DESC',
                'limit' => $_GET['limit'] ?? 20,
                'offset' => $_GET['offset'] ?? 0
            ];

            $versions = $this->versionModel->getFilteredVersions($filters);
            $total = $this->versionModel->getFilteredVersionsCount($filters);

            ApiResponse::success([
                'data' => $versions,
                'total' => $total,
                'limit' => (int)$filters['limit'],
                'offset' => (int)$filters['offset']
            ]);
        } catch (Exception $e) {
            ApiResponse::error($e->getMessage());
        }
    }

    /**
     * Get specific version by ID
     */
    public function getVersion($versionId, $tenantId) {
        try {
            if (empty($tenantId)) {
                throw new Exception('Tenant ID is required');
            }
            $version = $this->versionModel->getById($versionId, $tenantId);
            ApiResponse::success($version);
        } catch (Exception $e) {
            ApiResponse::error($e->getMessage());
        }
    }

    /**
     * Get versions for specific content
     */
    public function getContentVersions($contentId, $tenantId) {
        try {
            if (empty($tenantId)) {
                throw new Exception('Tenant ID is required');
            }
            $versions = $this->versionModel->getVersionsForContent($contentId, $tenantId);
            ApiResponse::success($versions);
        } catch (Exception $e) {
            ApiResponse::error($e->getMessage());
        }
    }
    /**
     * Compare two versions and return the differences
     */
    public function compareVersions($tenantId) {
        try {
            if (empty($tenantId)) {
                throw new Exception('Tenant ID is required');
            }
            // Get parameters
            $oldVersionId = $_GET['old'] ?? null;
            $newVersionId = $_GET['new'] ?? null;
            $contentId = $_GET['content_id'] ?? null;
            
            if (!$oldVersionId || !$newVersionId) {
                throw new Exception('Both old and new version IDs are required');
            }
            
            // Get version data
            $oldVersion = $this->versionModel->getById($oldVersionId);
            $newVersion = $this->versionModel->getById($newVersionId);
            
            // Ensure versions belong to the same content
            if ($oldVersion['content_id'] !== $newVersion['content_id']) {
                throw new Exception('Versions must belong to the same content item');
            }
            
            // If content_id is provided, ensure it matches the versions
            if ($contentId && $contentId != $oldVersion['content_id']) {
                throw new Exception('Content ID does not match the versions');
            }
            
            // Load DiffEngine
            require_once __DIR__ . '/../../versioning/diffengine.php';
            
            // Generate diff
            $diff = DiffEngine::compare($oldVersion['content'], $newVersion['content']);
            $stats = DiffEngine::getDiffStats($diff);
            
            // Get available versions for the content
            $availableVersions = $this->versionModel->getVersionsForContent($oldVersion['content_id']);
            
            ApiResponse::success([
                'diff' => $diff,
                'stats' => [
                    'added' => $stats['insertions'],
                    'removed' => $stats['deletions'],
                    'changed' => $stats['changes']
                ],
                'old_version' => $oldVersion,
                'new_version' => $newVersion,
                'available_versions' => $availableVersions
            ]);
        } catch (Exception $e) {
            ApiResponse::error($e->getMessage());
        }
    }
}
