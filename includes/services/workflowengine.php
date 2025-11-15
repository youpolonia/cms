<?php
require_once __DIR__ . '/../../api/controllers/contentversioncontroller.php';
require_once __DIR__ . '/moderationservice.php';

class WorkflowEngine {
    private $db;
    protected $moderationService;
    
    public function __construct($db) {
        $this->db = $db;
        $this->moderationService = ModerationService::getInstance($db);
    }

    public function getModerationService() {
        return $this->moderationService;
    }
    
    public function processQueueItem($queueId) {
        // Get queue item
        $queueItem = $this->getQueueItem($queueId);
        if (!$queueItem) {
            return false;
        }
        
        // Get content flags
        $flags = $this->getContentFlags($queueItem['content_id']);
        
        // Apply workflow rules
        return $this->applyWorkflowRules($queueItem, $flags);
    }
    
    private function getQueueItem($queueId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM moderation_queue WHERE id = ?"
        );
        $stmt->execute([$queueId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getContentFlags($contentId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM content_flags 
            WHERE content_id = ? AND status = 'pending'
            ORDER BY severity DESC, confidence_score DESC"
        );
        $stmt->execute([$contentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function applyWorkflowRules($queueItem, $flags) {
        $actions = [];
        
        // Group flags by type
        $flagGroups = [];
        foreach ($flags as $flag) {
            $flagGroups[$flag['type']][] = $flag;
        }
        
        // Apply rules for each flag type
        foreach ($flagGroups as $type => $typeFlags) {
            $actions = array_merge(
                $actions,
                $this->applyRulesForFlagType($type, $typeFlags)
            );
        }
        
        // Execute actions
        return $this->executeActions($queueItem['content_id'], $actions);
    }
    
    private function applyRulesForFlagType($type, $flags) {
        $config = require_once __DIR__ . '/../../config/content_moderation.php';
        $rules = $config['workflow_rules'][$type] ?? [];
        $actions = [];
        
        foreach ($rules as $rule) {
            if ($this->meetsThreshold($flags, $rule['threshold'])) {
                $actions[] = $rule['action'];
            }
        }
        
        return $actions;
    }
    
    private function meetsThreshold($flags, $threshold) {
        $totalConfidence = 0;
        foreach ($flags as $flag) {
            $totalConfidence += $flag['confidence_score'];
        }
        return $totalConfidence >= $threshold;
    }
    
    private function getCurrentContent($contentId) {
        $stmt = $this->db->prepare(
            "SELECT content FROM content_versions
            WHERE content_id = ? AND is_current = 1"
        );
        $stmt->execute([$contentId]);
        return $stmt->fetchColumn();
    }

    private function getCurrentUserId() {
        // Implementation depends on your auth system
        return $_SESSION['user_id'] ?? 0;
    }

    private function executeActions($contentId, $actions) {
        $success = true;
        $currentContent = $this->getCurrentContent($contentId);
        $userId = $this->getCurrentUserId();
        
        foreach ($actions as $action) {
            switch ($action['type']) {
                case 'auto_approve':
                    \Includes\Controllers\ContentVersionController::create(
                        $contentId,
                        ['content' => $currentContent, 'user_id' => $userId]
                    );
                    $success = $success && $this->getModerationService()->markAsAutoApproved($contentId);
                    break;
                    
                case 'auto_reject':
                    // Create version before modification
                    \Includes\Controllers\ContentVersionController::create(
                        $contentId,
                        ['content' => $currentContent, 'user_id' => $this->getCurrentUserId()]
                    );
                    $success = $success && $this->markAsRejected($contentId);
                    break;
                    
                case 'require_human_review':
                    $success = $success && $this->escalateToHumanReview($contentId);
                    break;
                    
                case 'notify_admin':
                    $success = $success && $this->sendAdminNotification($contentId, $action['message']);
                    break;
            }
        }
        
        return $success;
    }
    
    private function markAsRejected($contentId) {
        $stmt = $this->db->prepare(
            "UPDATE contents SET moderation_status = 'rejected' WHERE id = ?"
        );
        return $stmt->execute([$contentId]);
    }
    
    private function escalateToHumanReview($contentId) {
        $stmt = $this->db->prepare(
            "UPDATE moderation_queue SET status = 'human_review' WHERE content_id = ?"
        );
        return $stmt->execute([$contentId]);
    }
    
    private function sendAdminNotification($contentId, $message) {
        // Implementation would depend on notification system
        return true;
    }
}
