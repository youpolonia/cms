<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/api_error_handler.php';
require_once __DIR__ . '/../includes/core/rollbackmanager.php';
require_once __DIR__ . '/../includes/core/versionmanager.php';
require_once __DIR__ . '/../includes/core/DiffVisualizer.php';

class VersionControlAPI {
    /**
     * Handle version control API requests
     * @param array $request The parsed request data
     * @return array API response
     */
    public static function handleRequest(array $request): array {
        try {
            // Validate authentication
            if (empty($request['user_id'])) {
                return [
                    'status' => 401,
                    'error' => 'Authentication required'
                ];
            }

            $method = $request['method'] ?? '';
            $contentId = (int)($request['content_id'] ?? 0);
            $userId = (int)$request['user_id'];
            $version = $request['version'] ?? '';
            $reason = $request['reason'] ?? '';

            switch ($method) {
                case 'GET_VERSIONS':
                    return self::getVersions($contentId, $userId);
                case 'RESTORE_VERSION':
                    return self::restoreVersion($contentId, $version, $userId, $reason);
                case 'PREVIEW_VERSION':
                    return self::previewVersion($contentId, $version, $userId);
                default:
                    return [
                        'status' => 400,
                        'error' => 'Invalid method'
                    ];
            }
        } catch (Exception $e) {
            return APIErrorHandler::handle($e);
        }
    }

    private static function getVersions(int $contentId, int $userId): array {
        if ($contentId <= 0) {
            return [
                'status' => 400,
                'error' => 'Invalid content ID'
            ];
        }

        $versions = RollbackManager::getRollbackTargets($contentId);
        return [
            'status' => 200,
            'data' => $versions
        ];
    }

    private static function restoreVersion(
        int $contentId,
        string $version,
        int $userId,
        string $reason = ''
    ): array {
        if ($contentId <= 0 || empty($version)) {
            return [
                'status' => 400,
                'error' => 'Invalid parameters'
            ];
        }

        $manager = new RollbackManager();
        $success = $manager->rollbackToVersion($contentId, $version, $userId, $reason);
        return $success
            ? ['status' => 200, 'data' => ['success' => true]]
            : ['status' => 500, 'error' => 'Failed to restore version'];
    }

    private static function previewVersion(
        int $contentId,
        string $version,
        int $userId
    ): array {
        if ($contentId <= 0 || empty($version)) {
            return [
                'status' => 400,
                'error' => 'Invalid parameters'
            ];
        }

        $manager = new RollbackManager();
        $preview = $manager->previewRollback($version);
        return $preview
            ? ['status' => 200, 'data' => $preview]
            : ['status' => 404, 'error' => 'Version not found'];
    }
}
