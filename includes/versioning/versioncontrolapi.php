<?php
declare(strict_types=1);

if (!class_exists('VersionControlAPI')) {
    class VersionControlAPI {
        private $versioningSystem;
        private $rollbackManager;
        private $revisionHistory;
        private $diffVisualizer;

        public function __construct() {
            $this->versioningSystem = ContentVersioningSystem::getInstance();
            $this->rollbackManager = new RollbackManager();
            $this->revisionHistory = new RevisionHistory();
            $this->diffVisualizer = new DiffVisualizer();
        }

        public function handleRequest(array $request): array {
            try {
                $action = $request['action'] ?? '';
                $params = $request['params'] ?? [];

                switch ($action) {
                    case 'create_version':
                        return $this->createVersion($params);
                    case 'get_versions':
                        return $this->getVersions($params);
                    case 'get_version_content':
                        return $this->getVersionContent($params);
                    case 'compare_versions':
                        return $this->compareVersions($params);
                    case 'restore_version':
                        return $this->restoreVersion($params);
                    default:
                        throw new InvalidArgumentException("Invalid action");
                }
            } catch (Exception $e) {
                return [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ];
            }
        }

        private function createVersion(array $params): array {
            $required = ['content_id', 'content', 'author_id'];
            $this->validateParams($params, $required);

            $versionId = $this->versioningSystem->createVersion(
                (int)$params['content_id'],
                $params['content'],
                [
                    'author_id' => (int)$params['author_id'],
                    'change_summary' => $params['change_summary'] ?? ''
                ]
            );

            return [
                'status' => 'success',
                'version_id' => $versionId
            ];
        }

        private function getVersions(array $params): array {
            $required = ['content_id'];
            $this->validateParams($params, $required);

            $limit = min($params['limit'] ?? 10, 50);
            $versions = $this->revisionHistory->getHistoryForContent(
                (int)$params['content_id'],
                $limit
            );

            return [
                'status' => 'success',
                'versions' => $versions
            ];
        }

        private function getVersionContent(array $params): array {
            $required = ['version_id'];
            $this->validateParams($params, $required);

            $content = $this->versioningSystem->getVersionContent(
                (int)$params['version_id']
            );

            return [
                'status' => 'success',
                'content' => $content
            ];
        }

        private function compareVersions(array $params): array {
            $required = ['version_id_1', 'version_id_2'];
            $this->validateParams($params, $required);

            $content1 = $this->versioningSystem->getVersionContent(
                (int)$params['version_id_1']
            );
            $content2 = $this->versioningSystem->getVersionContent(
                (int)$params['version_id_2']
            );

            $diff = $this->diffVisualizer->compareVersions($content1, $content2);

            return [
                'status' => 'success',
                'diff_html' => $diff,
                'styles' => $this->diffVisualizer->getInlineDiffStyles()
            ];
        }

        private function restoreVersion(array $params): array {
            $required = ['version_id', 'user_id'];
            $this->validateParams($params, $required);

            $success = $this->rollbackManager->restoreVersion(
                (int)$params['version_id'],
                (int)$params['user_id']
            );

            return [
                'status' => $success ? 'success' : 'error',
                'message' => $success ? 'Version restored' : 'Restore failed'
            ];
        }

        private function validateParams(array $params, array $required): void {
            foreach ($required as $field) {
                if (!isset($params[$field])) {
                    throw new InvalidArgumentException("Missing required parameter: $field");
                }
            }
        }
    }
}
