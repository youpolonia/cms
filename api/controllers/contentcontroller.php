<?php
// Content Controller
// Version: 1.3
// Date: 2025-06-27
// Security Updates: Added role checks and object validation

require_once __DIR__ . '/../../includes/securityhelper.php';

class ContentController {
    // CRUD Operations
    public static function create($tenantId, $input) {
        SecurityHelper::checkRole('editor');
        $contentId = DBSupport::createContentForTenant($tenantId, $input);
        $content = DBSupport::getContentById($tenantId, $contentId);
        EventDispatcher::dispatch('content_saved', [$content]);
        return [
            'status' => 'success',
            'data' => [
                'id' => $contentId,
                'message' => 'Content created successfully'
            ]
        ];
    }

    public static function read($tenantId, $contentId = null) {
        SecurityHelper::checkRole('viewer');
        if ($contentId) {
            SecurityHelper::validateContentOwnership($tenantId, $contentId);
            return self::getSingleContent($tenantId, $contentId);
        }
        return self::getContentList($tenantId);
    }

    public static function update($tenantId, $contentId, $input) {
        SecurityHelper::checkRole('editor');
        SecurityHelper::validateContentOwnership($tenantId, $contentId);
        $result = DBSupport::updateContentForTenant($tenantId, $contentId, $input);
        $content = DBSupport::getContentById($tenantId, $contentId);
        EventDispatcher::dispatch('content_saved', [$content]);
        return [
            'status' => 'success',
            'data' => [
                'affected_rows' => $result,
                'message' => 'Content updated successfully'
            ]
        ];
    }

    public static function delete($tenantId, $contentId) {
        SecurityHelper::checkRole('admin');
        SecurityHelper::validateContentOwnership($tenantId, $contentId);
        $content = DBSupport::getContentById($tenantId, $contentId);
        $result = DBSupport::deleteContentForTenant($tenantId, $contentId);
        EventDispatcher::dispatch('content_deleted', [$content]);
        return [
            'status' => 'success',
            'data' => [
                'affected_rows' => $result,
                'message' => 'Content deleted successfully'
            ]
        ];
    }

    // Version Control
    public static function createVersion($tenantId, $contentId) {
        SecurityHelper::checkRole('editor');
        SecurityHelper::validateContentOwnership($tenantId, $contentId);
        $versionId = DBSupport::createContentVersion($tenantId, $contentId);
        return [
            'status' => 'success',
            'data' => [
                'version_id' => $versionId,
                'message' => 'Content version created successfully'
            ]
        ];
    }

    public static function crossSiteOperation($tenantId, $input) {
        SecurityHelper::checkRole('admin');
        $input = Sanitization::validateInput($input, [
            'target_tenants' => 'required|array',
            'content_ids' => 'required|array'
        ]);
        
        SecurityHelper::validateTenantBoundary($tenantId, $input['target_tenants']);
        $result = DBSupport::crossSiteContentOperation($tenantId, $input);
        return [
            'status' => 'success',
            'data' => [
                'affected_tenants' => count($input['target_tenants']),
                'message' => 'Cross-site operation completed'
            ]
        ];
    }

    public static function bulkOperation($tenantId, $input) {
        SecurityHelper::checkRole('editor');
        $input = Sanitization::validateInput($input, [
            'operation' => 'required|in:delete,publish,unpublish',
            'content_ids' => 'required|array'
        ]);
        
        foreach ($input['content_ids'] as $contentId) {
            SecurityHelper::validateContentOwnership($tenantId, $contentId);
        }
        $result = DBSupport::bulkContentOperation($tenantId, $input);
        return [
            'status' => 'success',
            'data' => [
                'affected_rows' => $result,
                'message' => 'Bulk operation completed'
            ]
        ];
    }

    public static function rollbackVersion($tenantId, $versionId) {
        SecurityHelper::checkRole('editor');
        SecurityHelper::validateVersionOwnership($tenantId, $versionId);
        $result = DBSupport::rollbackToVersion($tenantId, $versionId);
        return [
            'status' => 'success',
            'data' => [
                'affected_rows' => $result,
                'message' => 'Rollback completed successfully'
            ]
        ];
    }

    public static function compareVersions($tenantId, $fromVersionId, $toVersionId) {
        SecurityHelper::checkRole('viewer');
        SecurityHelper::validateVersionOwnership($tenantId, $fromVersionId);
        SecurityHelper::validateVersionOwnership($tenantId, $toVersionId);
        
        $diff = DBSupport::getVersionDiff($tenantId, $fromVersionId, $toVersionId);
        return [
            'status' => 'success',
            'data' => [
                'from_version' => $fromVersionId,
                'to_version' => $toVersionId,
                'diff' => $diff,
                'message' => 'Version comparison completed'
            ]
        ];
    }

    // State Management
    public static function changeState($tenantId, $contentId, $newState) {
        SecurityHelper::checkRole('editor');
        SecurityHelper::validateContentOwnership($tenantId, $contentId);
        
        $validStates = ['draft', 'published', 'archived'];
        if (!in_array($newState, $validStates)) {
            throw new Exception('Invalid state', 400);
        }

        $result = DBSupport::updateContentState($tenantId, $contentId, $newState);
        return [
            'status' => 'success',
            'data' => [
                'affected_rows' => $result,
                'message' => 'State changed successfully'
            ]
        ];
    }

    // Helper methods
    private static function getSingleContent($tenantId, $contentId) {
        $content = DBSupport::getContentById($tenantId, $contentId);
        if (!$content) {
            throw new Exception('Content not found', 404);
        }
        return ['status' => 'success', 'data' => $content];
    }

    private static function getContentList($tenantId) {
        $page = Sanitization::filterInt($_GET['page'] ?? null, 1) ?? 1;
        $perPage = Sanitization::filterInt($_GET['per_page'] ?? null, 1, 100) ?? 10;
        
        $content = DBSupport::getContentForTenant($tenantId, [
            'page' => $page,
            'per_page' => $perPage,
            'status' => Sanitization::filterString($_GET['status'] ?? 'published')
        ]);

        return [
            'status' => 'success',
            'data' => $content,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => DBSupport::getContentCount($tenantId)
            ]
        ];
    }

    private static function validateInput() {
        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            throw new Exception('Invalid content type', 400);
        }

        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input', 400);
        }

        $required = ['title', 'content', 'status'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                throw new Exception("Missing required field: $field", 400);
            }
        }

        // Sanitize all input values
        $sanitized = [];
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = array_map([Sanitization::class, 'filterString'], $value);
            } else {
                $sanitized[$key] = Sanitization::filterString($value);
            }
        }
        
        return $sanitized;
    }

    private static function handleGet($tenantId) {
        $page = Sanitization::filterInt($_GET['page'] ?? null, 1) ?? 1;
        $perPage = Sanitization::filterInt($_GET['per_page'] ?? null, 1, 100) ?? 10;
        
        $content = DBSupport::getContentForTenant($tenantId, [
            'page' => $page,
            'per_page' => $perPage,
            'status' => Sanitization::filterString($_GET['status'] ?? 'published')
        ]);

        return [
            'status' => 'success',
            'data' => $content,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => DBSupport::getContentCount($tenantId)
            ]
        ];
    }

    private static function handlePost($tenantId) {
        $input = self::validateInput();
        $contentId = DBSupport::createContentForTenant($tenantId, $input);
        
        return [
            'status' => 'success',
            'data' => [
                'id' => $contentId,
                'message' => 'Content created successfully'
            ]
        ];
    }

    // Content Locking
    public static function acquireLock($tenantId, $contentId, $userId) {
        $lockExpiry = time() + 1800; // 30 minutes from now
        $result = DBSupport::setContentLock($tenantId, $contentId, $userId, $lockExpiry);
        
        return [
            'status' => 'success',
            'data' => [
                'lock_acquired' => $result,
                'expires_at' => $lockExpiry,
                'message' => $result ? 'Lock acquired' : 'Failed to acquire lock'
            ]
        ];
    }

    public static function releaseLock($tenantId, $contentId, $userId) {
        $result = DBSupport::clearContentLock($tenantId, $contentId, $userId);
        
        return [
            'status' => 'success',
            'data' => [
                'lock_released' => $result,
                'message' => $result ? 'Lock released' : 'No active lock found'
            ]
        ];
    }

    public static function checkLock($tenantId, $contentId) {
        $lock = DBSupport::getContentLock($tenantId, $contentId);
        
        if (!$lock) {
            return [
                'status' => 'success',
                'data' => [
                    'is_locked' => false,
                    'message' => 'Content is not locked'
                ]
            ];
        }
        
        return [
            'status' => 'success',
            'data' => [
                'is_locked' => true,
                'locked_by' => $lock['user_id'],
                'expires_at' => $lock['expires_at'],
                'message' => 'Content is locked'
            ]
        ];
    }
    // Frontend Content Display Methods
    public static function blogIndex() {
        SecurityHelper::checkRole('viewer');
        $tenantId = Tenant::currentId();
        SecurityHelper::validateTenantAccess($tenantId);
        
        if (!DBSupport::isValidContentType('blog')) {
            throw new Exception('Blog content type not registered', 500);
        }

        $content = DBSupport::getContentForTenant($tenantId, [
            'status' => 'published',
            'type' => 'blog',
            'order_by' => 'published_at',
            'order_dir' => 'DESC'
        ]);
        
        return Theme::render('blog/index', [
            'posts' => $content,
            'title' => 'Blog'
        ]);
    }

    public static function show($slug) {
        SecurityHelper::checkRole('viewer');
        $tenantId = Tenant::currentId();
        SecurityHelper::validateTenantAccess($tenantId);
        
        $content = DBSupport::getContentBySlug($tenantId, $slug);
        
        if (!$content || $content['status'] !== 'published') {
            return ErrorController::notFound();
        }

        if (!DBSupport::isValidContentType($content['type'])) {
            throw new Exception('Content type not registered', 500);
        }

        return Theme::render('content/show', [
            'content' => $content,
            'title' => $content['title']
        ]);
    }
}
