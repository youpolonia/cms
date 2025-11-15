<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

declare(strict_types=1);

require_once __DIR__ . '/../../includes/content/versionmanager.php';
require_once __DIR__.'/../../includes/core/response.php';

class VersionHistoryApi {
    private $versionManager;
    private $response;

    public function __construct(PDO $pdo) {
        $this->versionManager = new \Includes\Content\VersionManager($pdo);
        $this->response = new \Includes\Core\Response();
    }

    public function createVersion(int $contentId, array $contentData): array {
        try {
            $versionId = $this->versionManager->createVersion(
                $contentId,
                json_encode($contentData),
                $_SESSION['user_id'] ?? null,
                $_POST['notes'] ?? null
            );
            
            return $this->response->success([
                'version_id' => $versionId,
                'content_id' => $contentId
            ]);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function listVersions(int $contentId, int $page = 1, int $perPage = 10): array {
        try {
            $versions = $this->versionManager->getAllVersions($contentId, $perPage, ($page - 1) * $perPage);
            $total = $this->versionManager->countVersions($contentId);
            
            return $this->response->success([
                'versions' => $versions,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage)
                ]
            ]);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function restoreVersion(int $contentId, int $versionId): array {
        try {
            $newVersionId = $this->versionManager->revertToVersion(
                $contentId,
                $versionId,
                $_SESSION['user_id'] ?? null,
                $_POST['notes'] ?? null
            );
            
            return $this->response->success([
                'new_version_id' => $newVersionId,
                'restored_version' => $versionId
            ]);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function compareVersions(int $contentId, int $version1, int $version2): array {
        try {
            $diff = $this->versionManager->compareVersions($contentId, $version1, $version2);
            return $this->response->success(['diff' => $diff]);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }
}
