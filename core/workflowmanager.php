<?php
/**
 * Workflow Manager - Handles content workflow state transitions
 */
require_once __DIR__ . '/rolemanager.php';

class WorkflowManager {
    private $db;
    
    public function __construct() {
        require_once __DIR__ . '/dbmodel.php';
        $this->db = DBModel::getInstance();
    }

    /**
     * Submit content for review
     * @param int $contentId
     * @return bool
     */
    public function submitForReview($contentId) {
        $roleManager = RoleManager::getInstance();
        if (!$roleManager->hasPermission($_SESSION['user_id'] ?? 0, 'submit_content')) {
            throw new RuntimeException('Permission denied - submit_content required');
        }
        return $this->updateWorkflowState($contentId, 'submitted');
    }

    /**
     * Approve content
     * @param int $contentId
     * @return bool
     */
    public function approve($contentId) {
        $roleManager = RoleManager::getInstance();
        if (!$roleManager->hasPermission($_SESSION['user_id'] ?? 0, 'approve_content')) {
            throw new RuntimeException('Permission denied - approve_content required');
        }
        return $this->updateWorkflowState($contentId, 'approved');
    }

    /**
     * Reject content
     * @param int $contentId
     * @return bool
     */
    public function reject($contentId) {
        $roleManager = RoleManager::getInstance();
        if (!$roleManager->hasPermission($_SESSION['user_id'] ?? 0, 'reject_content')) {
            throw new RuntimeException('Permission denied - reject_content required');
        }
        return $this->updateWorkflowState($contentId, 'rejected');
    }

    /**
     * Get current workflow state
     * @param int $contentId
     * @return string|null
     */
    public function getWorkflowState($contentId) {
        $stmt = $this->db->prepare("SELECT workflow_state FROM content WHERE id = ?");
        $stmt->execute([$contentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['workflow_state'] ?? null;
    }

    /**
     * Update workflow state with validation
     * @param int $contentId
     * @param string $newState
     * @return bool
     */
    private function updateWorkflowState($contentId, $newState) {
        $currentState = $this->getWorkflowState($contentId);
        
        if (!$this->isValidTransition($currentState, $newState)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE content SET workflow_state = ? WHERE id = ?");
        return $stmt->execute([$newState, $contentId]);
    }

    /**
     * Validate state transition
     * @param string $currentState
     * @param string $newState
     * @return bool
     */
    private function isValidTransition($currentState, $newState) {
        $validTransitions = [
            'draft' => ['submitted'],
            'submitted' => ['approved', 'rejected'],
            'rejected' => ['submitted', 'draft'],
            'approved' => ['published']
        ];

        return in_array($newState, $validTransitions[$currentState] ?? []);
    }

    /**
     * Publish content immediately
     * @param int $contentId
     * @return bool
     */
    public function publish($contentId) {
        $roleManager = RoleManager::getInstance();
        if (!$roleManager->hasPermission($_SESSION['user_id'] ?? 0, 'publish_content')) {
            throw new RuntimeException('Permission denied - publish_content required');
        }

        // Check if content is scheduled for future publication
        $stmt = $this->db->prepare("SELECT publish_at FROM content WHERE id = ?");
        $stmt->execute([$contentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!empty($result['publish_at']) && strtotime($result['publish_at']) > time()) {
            throw new RuntimeException('Cannot publish - content is scheduled for future publication');
        }

        return $this->updateWorkflowState($contentId, 'published');
    }

    /**
     * Schedule content for publication/unpublication
     * @param int $contentId
     * @param string|null $publishAt
     * @param string|null $unpublishAt
     * @return bool
     */
    public function schedule($contentId, $publishAt = null, $unpublishAt = null) {
        $roleManager = RoleManager::getInstance();
        if (!$roleManager->hasPermission($_SESSION['user_id'] ?? 0, 'schedule_content')) {
            throw new RuntimeException('Permission denied - schedule_content required');
        }

        // Validate dates
        if ($publishAt && strtotime($publishAt) === false) {
            throw new InvalidArgumentException('Invalid publish date format');
        }
        if ($unpublishAt && strtotime($unpublishAt) === false) {
            throw new InvalidArgumentException('Invalid unpublish date format');
        }

        $stmt = $this->db->prepare("UPDATE content SET publish_at = ?, unpublish_at = ? WHERE id = ?");
        return $stmt->execute([$publishAt, $unpublishAt, $contentId]);
    }

    /**
     * Check for content that needs to be published/unpublished
     * @return array Count of processed items
     */
    public function processScheduledContent() {
        $now = date('Y-m-d H:i:s');
        $processed = ['published' => 0, 'unpublished' => 0];

        // Publish scheduled content
        $stmt = $this->db->prepare(
            "UPDATE content SET workflow_state = 'published'
             WHERE workflow_state = 'approved'
             AND publish_at IS NOT NULL
             AND publish_at <= ?"
        );
        $stmt->execute([$now]);
        $processed['published'] = $stmt->rowCount();

        // Unpublish expired content
        $stmt = $this->db->prepare(
            "UPDATE content SET workflow_state = 'archived'
             WHERE workflow_state = 'published'
             AND unpublish_at IS NOT NULL
             AND unpublish_at <= ?"
        );
        $stmt->execute([$now]);
        $processed['unpublished'] = $stmt->rowCount();

        return $processed;
    }
}
