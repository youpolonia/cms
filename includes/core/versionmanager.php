<?php
declare(strict_types=1);

require_once __DIR__ . '/../Controllers/VersionController.php';

class VersionManager {
    /**
     * Rolls back content to specific version
     */
    public static function rollback(
        int $contentId,
        int $version,
        callable $updateContentCallback
    ): bool {
        $versionData = \Includes\Controllers\ContentVersionController::get($contentId, $version);
        if (!$versionData) {
            error_log("Version $version not found for content $contentId");
            return false;
        }

        try {
            $data = json_decode($versionData['data'], true);
            if (!is_array($data)) {
                throw new Exception("Invalid version data format");
            }

            return $updateContentCallback($contentId, $data);
        } catch (Exception $e) {
            error_log("Rollback failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates new version before content update
     */
    public static function createVersionBeforeUpdate(
        int $contentId,
        array $currentData
    ): bool {
        try {
            $result = \Includes\Controllers\ContentVersionController::create($contentId, $currentData);
            return !empty($result);
        } catch (Exception $e) {
            error_log("Version creation failed: " . $e->getMessage());
            return false;
        }
    }
}
