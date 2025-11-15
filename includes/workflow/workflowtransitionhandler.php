<?php
declare(strict_types=1);

class WorkflowTransitionHandler {
    public static function executeTransition(
        int $contentId,
        string $transition,
        int $userId,
        array $context = []
    ): bool {
        $currentState = WorkflowStateManager::getCurrentState($contentId);
        
        if (!ContentWorkflowSystem::isValidTransition($currentState, $transition)) {
            return false;
        }

        $transitionData = ContentWorkflowSystem::getTransitionData($transition);
        $newState = $transitionData['to'];

        try {
            // Begin state transition
            if (!WorkflowStateManager::setState($contentId, $newState, $userId)) {
                throw new Exception("Failed to update workflow state");
            }

            // Log the transition
            WorkflowStateManager::logTransition($contentId, $currentState, $newState, $userId);

            // Trigger notifications
            WorkflowNotificationSystem::sendTransitionNotification(
                $contentId,
                $currentState,
                $newState,
                $userId,
                $context
            );

            return true;
        } catch (Exception $e) {
            error_log("Workflow transition failed: " . $e->getMessage());
            return false;
        }
    }

    public static function canUserTransition(
        int $userId,
        string $currentState,
        string $transition
    ): bool {
        $userRoles = UserManager::getUserRoles($userId);
        $transitionData = ContentWorkflowSystem::getTransitionData($transition);
        
        return !empty(array_intersect($userRoles, $transitionData['roles']));
    }
}
