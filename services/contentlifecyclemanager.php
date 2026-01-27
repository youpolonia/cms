<?php

class ContentLifecycleManager {
    const STATE_DRAFT = 'draft';
    const STATE_REVIEW = 'review';
    const STATE_PUBLISHED = 'published';
    const STATE_ARCHIVED = 'archived';

    private $validTransitions = [
        self::STATE_DRAFT => [self::STATE_REVIEW],
        self::STATE_REVIEW => [self::STATE_DRAFT, self::STATE_PUBLISHED],
        self::STATE_PUBLISHED => [self::STATE_ARCHIVED],
        self::STATE_ARCHIVED => []
    ];

    private $contentId;
    private $tenantId;
    private $currentState;
    private $logger;
    private $versionManager;

    public function __construct($contentId, $tenantId, $initialState = self::STATE_DRAFT) {
        if (empty($tenantId)) {
            throw new InvalidArgumentException('Tenant ID is required');
        }
        $this->contentId = $contentId;
        $this->tenantId = $tenantId;
        $this->currentState = $initialState;
        $this->logger = new ContentLifecycleLogger();
        $this->versionManager = new ContentVersionManager();
    }

    public function getCurrentState() {
        return $this->currentState;
    }

    public function transitionTo($newState, $contentData = null) {
        if (!in_array($newState, $this->validTransitions[$this->currentState])) {
            throw new InvalidTransitionException(
                "Invalid transition from {$this->currentState} to {$newState} for tenant {$this->tenantId}"
            );
        }

        // Create version snapshot before transition if content data provided
        if ($contentData !== null) {
            $this->versionManager->createVersion(
                $this->contentId,
                $this->tenantId,
                $contentData,
                $this->currentState,
                $newState
            );
        }

        $this->logger->logTransition(
            $this->contentId,
            $this->tenantId,
            $this->currentState,
            $newState
        );

        $this->currentState = $newState;
        return true;
    }

    public function getTransitionHistory() {
        return $this->logger->getHistory($this->contentId);
    }

    public function getVersionHistory() {
        return $this->versionManager->getVersions($this->contentId);
    }

    public function restoreVersion($versionId) {
        return $this->versionManager->restoreVersion($versionId);
    }
}

class ContentLifecycleLogger {
    public function logTransition($contentId, $tenantId, $fromState, $toState) {
        if (empty($tenantId)) {
            throw new InvalidArgumentException('Tenant ID is required for logging');
        }

        $logEntry = [
            'timestamp' => time(),
            'content_id' => $contentId,
            'tenant_id' => $tenantId,
            'from_state' => $fromState,
            'to_state' => $toState,
            'user_id' => $_SESSION['user_id'] ?? null
        ];

        try {
            $this->saveLogEntry($logEntry);
        } catch (Exception $e) {
            error_log("[ContentLifecycleLogger] Failed to log transition for tenant {$tenantId}: " . $e->getMessage());
            throw $e;
        }
    }

    private function saveLogEntry($entry) {
        // Try database first if available
        if (class_exists('TenantAwareDB')) {
            try {
                $db = TenantAwareDB::getConnection($entry['tenant_id']);
                $stmt = $db->prepare(
                    'INSERT INTO content_state_transitions
                    (content_id, tenant_id, from_state, to_state, user_id, timestamp)
                    VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $entry['content_id'],
                    $entry['tenant_id'],
                    $entry['from_state'],
                    $entry['to_state'],
                    $entry['user_id'],
                    $entry['timestamp']
                ]);
                return;
            } catch (Exception $dbEx) {
                error_log("[ContentLifecycleLogger] DB logging failed, falling back to file: " . $dbEx->getMessage());
            }
        }

        // Fallback to file logging
        file_put_contents(
            'logs/content_lifecycle_' . $entry['tenant_id'] . '.log',
            json_encode($entry) . PHP_EOL,
            FILE_APPEND
        );
    }

    public function getHistory($contentId) {
        // Retrieve history from storage
        return []; // Implementation depends on storage
    }
}

class ContentVersionManager {
    public function createVersion($contentId, $tenantId, $contentData, $fromState, $toState) {
        $versionData = [
            'content_id' => $contentId,
            'tenant_id' => $tenantId,
            'content_data' => $contentData,
            'from_state' => $fromState,
            'to_state' => $toState,
            'created_at' => time(),
            'user_id' => $_SESSION['user_id'] ?? null
        ];

        $this->saveVersion($versionData);
    }

    private function saveVersion($versionData) {
        // Try database first
        if (class_exists('TenantAwareDB')) {
            try {
                $db = TenantAwareDB::getConnection($versionData['tenant_id']);
                $stmt = $db->prepare(
                    'INSERT INTO content_versions
                    (content_id, tenant_id, content_data, from_state, to_state, user_id, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $versionData['content_id'],
                    $versionData['tenant_id'],
                    json_encode($versionData['content_data']),
                    $versionData['from_state'],
                    $versionData['to_state'],
                    $versionData['user_id'],
                    $versionData['created_at']
                ]);
                return $db->lastInsertId();
            } catch (Exception $dbEx) {
                error_log("[ContentVersionManager] DB version save failed, falling back to file: " . $dbEx->getMessage());
            }
        }

        // Fallback to file storage
        $versionId = uniqid('ver_', true);
        $versionDir = "content_versions/{$versionData['tenant_id']}/{$versionData['content_id']}";
        if (!file_exists($versionDir)) {
            mkdir($versionDir, 0755, true);
        }

        file_put_contents(
            "{$versionDir}/{$versionId}.json",
            json_encode($versionData)
        );

        return $versionId;
    }

    public function getVersions($contentId) {
        // Retrieve versions from storage
        return []; // Implementation depends on storage
    }

    public function restoreVersion($versionId) {
        // Retrieve and apply version
        return true; // Implementation depends on storage
    }
}

class InvalidTransitionException extends Exception {}
