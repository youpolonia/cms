<?php
/**
 * Content Publisher - Handles content state transitions and versioning
 */
require_once __DIR__ . '/database.php';
require_once CMS_ROOT . '/includes/content/workflowmanager.php';
require_once CMS_ROOT . '/services/auditlogger.php';

class ContentPublisher {
    private static $db;
    
    public static function init(): void {
        self::$db = \core\Database::connection();
    }

    public static function publish(int $contentId, string $reason = ''): bool {
        // Check permissions
        $roleManager = RoleManager::getInstance();
        if (!$roleManager->hasPermission($_SESSION['user_id'] ?? 0, 'publish_content')) {
            throw new RuntimeException('Permission denied - publish_content required');
        }

        // Check workflow state
        $workflowManager = new WorkflowManager(self::$db);
        $workflowEntry = $workflowManager->getCurrentWorkflowEntry($contentId);
        
        if (!$workflowEntry || $workflowEntry['state_name'] !== 'approved') {
            throw new RuntimeException('Content cannot be published - workflow state is not approved');
        }

        try {
            self::$db->beginTransaction();
            $stmt = self::$db->prepare(
                "UPDATE content_entries
                SET state = 'published',
                    published_at = NOW(),
                    version = version + 1
                WHERE id = ?"
            );

            if ($stmt->execute([$contentId])) {
                self::logTransition($contentId, 'draft', 'published', $reason);
                AuditLogger::log(
                    $_SESSION['user_id'],
                    'publish',
                    'content',
                    $contentId,
                    $reason
                );
                self::$db->commit();
                return true;
            }
            self::$db->rollBack();
            return false;
        } catch (\Exception $e) {
            if (method_exists(self::$db, 'inTransaction') && self::$db->inTransaction()) {
                self::$db->rollBack();
            }
            throw $e;
        }
    }

    public static function unpublish(int $contentId, string $reason = ''): bool {
        // Check permissions
        $roleManager = RoleManager::getInstance();
        if (!$roleManager->hasPermission($_SESSION['user_id'] ?? 0, 'unpublish_content')) {
            throw new RuntimeException('Permission denied - unpublish_content required');
        }

        try {
            self::$db->beginTransaction();
            $stmt = self::$db->prepare(
                "UPDATE content_entries
                SET state = 'draft'
                WHERE id = ?"
            );

            if ($stmt->execute([$contentId])) {
                self::logTransition($contentId, 'published', 'draft', $reason);
                AuditLogger::log(
                    $_SESSION['user_id'],
                    'unpublish',
                    'content',
                    $contentId,
                    $reason
                );
                self::$db->commit();
                return true;
            }
            self::$db->rollBack();
            return false;
        } catch (\Exception $e) {
            if (method_exists(self::$db, 'inTransaction') && self::$db->inTransaction()) {
                self::$db->rollBack();
            }
            throw $e;
        }
    }

    public static function archive(int $contentId, string $reason = ''): bool {
        // Check permissions
        $roleManager = RoleManager::getInstance();
        if (!$roleManager->hasPermission($_SESSION['user_id'] ?? 0, 'archive_content')) {
            throw new RuntimeException('Permission denied - archive_content required');
        }

        try {
            self::$db->beginTransaction();
            $stmt = self::$db->prepare(
                "UPDATE content_entries
                SET state = 'archived'
                WHERE id = ?"
            );

            if ($stmt->execute([$contentId])) {
                self::logTransition($contentId, 'published', 'archived', $reason);
                AuditLogger::log(
                    $_SESSION['user_id'],
                    'archive',
                    'content',
                    $contentId,
                    $reason
                );
                self::$db->commit();
                return true;
            }
            self::$db->rollBack();
            return false;
        } catch (\Exception $e) {
            if (method_exists(self::$db, 'inTransaction') && self::$db->inTransaction()) {
                self::$db->rollBack();
            }
            throw $e;
        }
    }

    public static function getCurrentVersion(int $contentId): int {
        $stmt = self::$db->prepare(
            "SELECT version FROM content_entries WHERE id = ?"
        );
        $stmt->execute([$contentId]);
        return (int)$stmt->fetchColumn();
    }

    private static function logTransition(
        int $contentId, 
        string $fromState, 
        string $toState, 
        string $reason = ''
    ): void {
        $stmt = self::$db->prepare(
            "INSERT INTO status_transitions 
            (entity_type, entity_id, from_status, to_status, reason)
            VALUES ('content', ?, ?, ?, ?)"
        );
        $stmt->execute([$contentId, $fromState, $toState, $reason]);
    }
}
