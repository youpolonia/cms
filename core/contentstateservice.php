<?php
/**
 * Content state management service
 */
class ContentStateService {
    private $db;
    private $historyLogger;

    public function __construct($dbConnection, ContentStateHistoryLogger $historyLogger) {
        $this->db = $dbConnection;
        $this->historyLogger = $historyLogger;
    }

    public function getContentState(int $contentId, ?int $tenantId = null): ?array {
        return $this->fetchContentState($contentId, $tenantId);
    }

    public function changeContentState(
        int $contentId,
        string $targetState,
        int $userId,
        ?int $tenantId = null,
        string $notes = ''
    ): array {
        $currentState = $this->getContentState($contentId, $tenantId);
        
        if (!$currentState) {
            throw new RuntimeException("Content state not found");
        }

        $this->validateTransition($currentState['state'], $targetState, $tenantId);
        
        $this->updateContentState($contentId, $targetState, $tenantId);
        
        $this->historyLogger->logTransition(
            $contentId,
            $currentState['state'],
            $targetState,
            $userId,
            $tenantId,
            $notes
        );

        return $this->getContentState($contentId, $tenantId);
    }

    public function getStateHistory(
        int $contentId,
        ?int $tenantId = null,
        int $limit = 50
    ): array {
        return $this->historyLogger->getHistory($contentId, $tenantId, $limit);
    }

    private function fetchContentState(int $contentId, ?int $tenantId): ?array {
        // Implementation would query database
        return ['state' => 'draft']; // Mock data
    }

    private function validateTransition(
        string $currentState,
        string $targetState,
        ?int $tenantId
    ): void {
        StatusTransitionHandler::executeTransition($currentState, $targetState, [
            'tenant_id' => $tenantId
        ]);
    }

    private function updateContentState(
        int $contentId,
        string $state,
        ?int $tenantId
    ): void {
        // Implementation would update database
    }
}
