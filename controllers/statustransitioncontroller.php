<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../core/statustransitionhandler.php';
require_once __DIR__ . '/../core/logger.php';

class StatusTransitionController {
    /**
     * Share content between tenants
     */
    public function shareContent(array $data): array {
        try {
            // Validate input
            if (empty($data['content_id']) || empty($data['target_tenant'])) {
                throw new Exception('Missing required fields');
            }

            // Execute transition
            $result = StatusTransitionHandler::executeTransition(
                'private',
                'shared',
                [
                    'content_id' => $data['content_id'],
                    'target_tenant' => $data['target_tenant'],
                    'reason' => $data['reason'] ?? 'Content sharing'
                ]
            );

            return [
                'success' => true,
                'data' => $result
            ];
        } catch (Exception $e) {
            Logger::error("Content sharing failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Sync content versions between tenants
     */
    public function syncVersions(?string $version): array {
        try {
            // Implementation depends on versioning system
            $result = StatusTransitionHandler::executeTransition(
                'outdated',
                'synced',
                ['version' => $version]
            );

            return [
                'success' => true,
                'data' => $result
            ];
        } catch (Exception $e) {
            Logger::error("Version sync failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Resolve content conflicts
     */
    public function resolveConflict(array $data): array {
        try {
            if (empty($data['conflict_id']) || empty($data['resolution'])) {
                throw new Exception('Missing conflict resolution data');
            }

            $result = StatusTransitionHandler::executeTransition(
                'conflict',
                'resolved',
                $data
            );

            return [
                'success' => true,
                'data' => $result
            ];
        } catch (Exception $e) {
            Logger::error("Conflict resolution failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
