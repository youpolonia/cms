<?php
require_once __DIR__ . '/../core/bootstrap.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Content Management Core
 * Handles all content operations including versioning and publishing
 */

require_once __DIR__ . '/../core/database.php';
class ContentManager {
    private $db;

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    public function createContent($data, $userId) {
        require_once CMS_ROOT . '/includes/validation/Validator.php';
        
        [$valid, $errors] = Validator::validateContentData($data);
        if (!$valid) {
            throw new InvalidArgumentException(json_encode($errors));
        }

        $contentId = $this->db->insert('contents', [
            'title' => $data['title'],
            'slug' => $this->generateSlug($data['title']),
            'content_type' => $data['content_type'] ?? 'page',
            'status' => 'draft',
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->createVersion($contentId, $data, $userId, 'Initial version');
        NotificationTriggers::triggerContentSubmissionNotification($contentId, $userId);
        return $contentId;
    }

    public function updateContent($contentId, $data, $userId) {
        require_once CMS_ROOT . '/includes/validation/Validator.php';
        
        [$valid, $errors] = Validator::validateContentData($data);
        if (!$valid) {
            throw new InvalidArgumentException(json_encode($errors));
        }

        $this->db->update('contents', [
            'title' => $data['title'],
            'slug' => $data['slug'] ?? $this->generateSlug($data['title']),
            'updated_by' => $userId,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$contentId]);

        $versionId = $this->createVersion($contentId, $data, $userId, $data['version_note'] ?? '');
        NotificationTriggers::triggerContentSubmissionNotification($contentId, $userId);
        return $versionId;
    }

    public function publishContent($contentId, $userId) {
        $this->db->update('contents', [
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s'),
            'updated_by' => $userId,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$contentId]);
        NotificationTriggers::triggerContentApprovalNotification($contentId, $userId);
    }

    public function createVersion($contentId, $data, $userId, $note = '') {
        return $this->db->insert('content_versions', [
            'content_id' => $contentId,
            'content_data' => json_encode($data['content']),
            'version_note' => $note,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'is_autosave' => $data['is_autosave'] ?? 0
        ]);
        NotificationTriggers::triggerVersionChangeNotification($contentId, $userId);
        return $this->db->lastInsertId();
    }

    public function getContent($contentId, $versionId = null) {
        $content = $this->db->fetch('SELECT * FROM contents WHERE id = ?', [$contentId]);
        
        if ($versionId) {
            $version = $this->db->fetch(
                'SELECT * FROM content_versions WHERE id = ? AND content_id = ?',
                [$versionId, $contentId]
            );
        } else {
            $version = $this->db->fetch(
                'SELECT * FROM content_versions WHERE content_id = ? ORDER BY id DESC LIMIT 1',
                [$contentId]
            );
        }

        if ($content && $version) {
            $content['content'] = json_decode($version['content_data'], true);
            $content['version_id'] = $version['id'];
            return $content;
        }

        return null;
    }

    public function getContentBySlug($slug) {
        $content = $this->db->fetch(
            'SELECT * FROM contents WHERE slug = ? AND status = "published"',
            [$slug]
        );

        if ($content) {
            $version = $this->db->fetch(
                'SELECT * FROM content_versions WHERE content_id = ? ORDER BY id DESC LIMIT 1',
                [$content['id']]
            );
            $content['content'] = json_decode($version['content_data'], true);
            return $content;
        }

        return null;
    }

    public function listContent($type = null, $status = null, $limit = 20, $offset = 0) {
        $where = [];
        $params = [];

        if ($type) {
            $where[] = 'content_type = ?';
            $params[] = $type;
        }

        if ($status) {
            $where[] = 'status = ?';
            $params[] = $status;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT * FROM contents $whereClause ORDER BY updated_at DESC LIMIT ? OFFSET ?";
        $params = array_merge($params, [$limit, $offset]);

        return $this->db->fetchAll($sql, $params);
    }

    public function getPublishingWorkflow($contentType) {
        $workflows = [
            'page' => ['draft', 'review', 'approved', 'published'],
            'post' => ['draft', 'review', 'published'],
            'product' => ['draft', 'approved', 'published']
        ];
        
        return $workflows[$contentType] ?? ['draft', 'published'];
    }

    public function canPublish($contentId, $userId) {
        $content = $this->getContent($contentId);
        if (!$content) return false;

        require_once __DIR__.'/../auth/Session.php';
        $session = new Session();
        $userRoles = $session->getUserRoles($userId);
        
        if (in_array('admin', $userRoles) || in_array('editor', $userRoles)) {
            return true;
        }

        if (in_array('author', $userRoles) && 
            $content['created_by'] == $userId && 
            $content['status'] == 'review') {
            return true;
        }

        return false;
    }

    public function requestReview($contentId, $userId) {
        $content = $this->getContent($contentId);
        if (!$content) return false;

        $this->db->update('contents', [
            'status' => 'review',
            'updated_by' => $userId,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$contentId]);

        return true;
    }

    private function generateSlug($title) {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return $slug;
    }
}
