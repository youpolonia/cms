<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/versionmanager.php';
require_once __DIR__ . '/../Controllers/VersionController.php';

class RollbackManager {
    /**
     * Restores content to a specific version
     * @param int $contentId The ID of the content to restore
     * @param string $version The version to restore to (format: 00XX)
     * @param int $userId The ID of user performing the rollback
     * @return array Result with status and message
     */
    /**
     * Validates all data needed for a rollback operation
     * @param int $contentId The content ID
     * @param string $version The version string
     * @param int $userId The user ID
     * @return array|null Returns null if valid, or error array if invalid
     */
    private static function validateRollbackData(
        int $contentId,
        string $version,
        int $userId
    ): ?array {
        // Validate content exists
        $currentData = \Includes\Controllers\ContentVersionController::getCurrent($contentId);
        if (!$currentData) {
            return [
                'status' => false,
                'message' => "Content not found or inaccessible",
                'error_code' => 'CONTENT_NOT_FOUND',
                'details' => [
                    'content_id' => $contentId,
                    'timestamp' => date('c'),
                    'suggestions' => [
                        'Check if content exists',
                        'Verify user permissions',
                        'Review content logs'
                    ],
                    'documentation' => '/docs/errors/CONTENT_NOT_FOUND',
                    'debug' => [
                        'user_id' => $userId,
                        'requested_version' => $version
                    ]
                ]
            ];
        }

        // Validate version format
        if (!preg_match('/^\d{4}$/', $version)) {
            return [
                'status' => false,
                'message' => "Invalid version format - must be 4 digits",
                'error_code' => 'INVALID_VERSION_FORMAT',
                'details' => [
                    'received_version' => $version,
                    'expected_format' => 'YYYY (4 digits)',
                    'suggestions' => [
                        'Check version history',
                        'Use version picker UI',
                        'Contact support if version is correct'
                    ],
                    'documentation' => '/docs/versions/formatting',
                    'debug' => [
                        'content_id' => $contentId,
                        'user_id' => $userId
                    ]
                ]
            ];
        }

        // Validate user permissions
        if (!UserPermissions::canEditContent($userId, $contentId)) {
            return [
                'status' => false,
                'message' => "User lacks permission to edit this content",
                'error_code' => 'PERMISSION_DENIED',
                'details' => [
                    'required_permission' => 'content_edit',
                    'user_permissions' => UserPermissions::getForUser($userId),
                    'suggestions' => [
                        'Request elevated permissions',
                        'Contact content owner',
                        'Use read-only mode'
                    ],
                    'documentation' => '/docs/permissions/content',
                    'debug' => [
                        'content_id' => $contentId,
                        'user_id' => $userId
                    ]
                ]
            ];
        }

        return null;
    }

    public static function restoreVersion(
        int $contentId,
        string $version,
        int $userId,
        bool $force = false
    ): array {
        $pdo = \core\Database::connection();
        
        try {
            // Get version data for preview/confirmation
            $currentData = \Includes\Controllers\ContentVersionController::getCurrent($contentId);
            $targetData = \Includes\Controllers\ContentVersionController::getVersion($contentId, $version);
            
            if (!$force) {
                return [
                    'status' => 'confirmation_required',
                    'message' => 'Rollback requires confirmation',
                    'preview' => [
                        'current_version' => $currentData['version'],
                        'target_version' => $version,
                        'content_title' => $currentData['title'],
                        'changes' => [
                            'fields_changed' => array_keys(array_diff_assoc(
                                $currentData['content'],
                                $targetData['content']
                            )),
                            'preview_url' => "/content/compare/$contentId?from={$currentData['version']}&to=$version"
                        ]
                    ],
                    'confirmation' => [
                        'endpoint' => "/api/rollback/confirm/$contentId",
                        'method' => 'POST',
                        'parameters' => [
                            'version' => $version,
                            'force' => true
                        ]
                    ]
                ];
            }

            $pdo->beginTransaction();

            // Validate all data before proceeding
            $validationError = self::validateRollbackData($contentId, $version, $userId);
            if ($validationError) {
                $pdo->rollBack();
                return $validationError;
            }
            if (!$currentData) {
                $pdo->rollBack();
                return [
                    'status' => false,
                    'message' => "Content $contentId not found or inaccessible"
                ];
            }

            // Get and validate target version
            $versionData = \Includes\Controllers\ContentVersionController::get($contentId, $version);
            if (!$versionData) {
                $pdo->rollBack();
                return [
                    'status' => false,
                    'message' => "Version $version not found for content $contentId",
                    'available_versions' => self::getRollbackTargets($contentId)
                ];
            }

            // Create backup with transaction safety
            $backupResult = VersionManager::createVersionBeforeUpdate(
                $contentId,
                $currentData,
                "Pre-rollback backup for version $version",
                $userId
            );

            if (!$backupResult) {
                $pdo->rollBack();
                return [
                    'status' => false,
                    'message' => "Failed to create backup version",
                    'error_details' => VersionManager::getLastError()
                ];
            }

            // Perform the rollback
            $restoreResult = \Includes\Controllers\ContentVersionController::restore(
                $contentId,
                $version,
                $userId
            );

            if (!$restoreResult) {
                $pdo->rollBack();
                return [
                    'status' => false,
                    'message' => "Failed to restore version $version",
                    'error_details' => \Includes\Controllers\ContentVersionController::getLastError()
                ];
            }

            // Mark versions
            VersionManager::setActiveVersion($contentId, $version);
            VersionManager::archiveVersion($contentId, $backupResult['version']);

            $pdo->commit();
            
            // Get content details for better feedback
            $contentDetails = ContentController::getBasicInfo($contentId);
            
            return [
                'status' => true,
                'message' => "Successfully restored content '{$contentDetails['title']}'",
                'details' => [
                    'content_id' => $contentId,
                    'content_title' => $contentDetails['title'],
                    'from_version' => $currentData['version'],
                    'to_version' => $version,
                    'backup_created' => true,
                    'backup_version' => $backupResult['version'],
                    'active_version' => $version,
                    'timestamp' => date('c'),
                    'user_id' => $userId
                ],
                'actions' => [
                    'view_content' => "/content/view/$contentId",
                    'compare_versions' => "/content/compare/$contentId?from={$backupResult['version']}&to=$version",
                    'restore_backup' => "/admin/restore/$contentId?version={$backupResult['version']}"
                ]
            ];
        } catch (PDOException $e) {
            $db->rollBack();
            error_log("Rollback transaction failed: " . $e->getMessage());
            return [
                'status' => false,
                'message' => "Database error during rollback",
                'error_code' => $e->getCode(),
                'error_details' => "Transaction rolled back safely"
            ];
        }
    }

    /**
     * Gets available rollback targets for content
     * @param int $contentId The content ID
     * @return array List of available versions with metadata
     */
    public static function getRollbackTargets(int $contentId): array {
        return \Includes\Controllers\ContentVersionController::getAllVersions($contentId);
    }
}
