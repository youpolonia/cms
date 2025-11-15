<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../../includes/workflow/WorkflowEngine.php';
require_once __DIR__ . '/../../includes/securelogger.php';
require_once __DIR__ . '/../../core/csrf.php';

class WorkflowController {
    private $workflow;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->workflow = new WorkflowEngine($db);
    }

    public function showStateSelector($content_id) {
        $current_state = $this->workflow->getCurrentState($content_id);
        $available_states = $this->getAvailableTransitions($current_state);
        
        require_once __DIR__ . '/../views/workflow/state_selector.php';
    }

    public function changeState($content_id, $new_state) {
        csrf_validate_or_403();
        try {
            $success = $this->workflow->transition($content_id, $new_state);
            if ($success) {
                FlashMessage::add(FlashMessage::TYPE_SUCCESS, "State updated to $new_state", [
                    'content_id' => $content_id,
                    'new_state' => $new_state
                ]);
            } else {
                FlashMessage::add(FlashMessage::TYPE_ERROR, "Failed to update state");
            }
            return ['success' => $success];
        } catch (Exception $e) {
            SecureLogger::logError($e, 'Workflow state change');
            return ['success' => false, 'message' => 'State transition failed'];
        }
    }

    private function getAvailableTransitions($current_state) {
        $states = [
            'draft' => ['review'],
            'review' => ['published', 'draft'],
            'published' => ['archived'],
            'archived' => ['published']
        ];
        
        return $states[$current_state] ?? [];
    }
}
