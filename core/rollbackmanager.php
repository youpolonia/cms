<?php
declare(strict_types=1);

/**
 * Rollback Manager - Handles version restoration operations with automation
 * and workflow integration
 */
class RollbackManager {
    private ContentVersioningSystem $versioning;
    private RevisionHistory $history;
    private VersionComparison $comparison;

    public function __construct() {
        $this->versioning = ContentVersioningSystem::getInstance();
        $this->history = RevisionHistory::getInstance();
        $this->comparison = new VersionComparison();
    }

    public function rollbackToVersion(
        int $contentId,
        string $versionId,
        int $userId,
        string $reason = ''
    ): bool {
        $version = $this->versioning->getVersion($versionId);
        if ($version === null || $version['content_id'] !== $contentId) {
            return false;
        }

        // Get current content for history tracking
        $currentVersion = $this->versioning->listVersions($contentId)[0] ?? null;
        $currentContent = $currentVersion 
            ? $this->versioning->getVersion($currentVersion['version_id'])['content']
            : '';

        // Create new version with rollback content
        $newVersionId = $this->versioning->createVersion(
            $contentId,
            $version['content'],
            ['rollback_from' => $versionId, 'reason' => $reason]
        );

        // Log the rollback operation
        $this->history->logChange(
            $contentId,
            'rollback',
            $currentContent,
            $version['content'],
            $userId,
            [
                'from_version' => $currentVersion['version_id'] ?? null,
                'to_version' => $versionId,
                'new_version' => $newVersionId,
                'reason' => $reason
            ]
        );

        return true;
    }

    public function batchRollback(array $versionIds, int $userId, string $reason = ''): array {
        $results = [];
        foreach ($versionIds as $versionId) {
            $version = $this->versioning->getVersion($versionId);
            if ($version === null) {
                $results[$versionId] = false;
                continue;
            }

            $results[$versionId] = $this->rollbackToVersion(
                $version['content_id'],
                $versionId,
                $userId,
                $reason
            );
        }
        return $results;
    }

    private function detectPotentialConflicts(array $currentVersion, array $targetVersion): array {
        $currentDeps = $currentVersion['meta']['dependencies'] ?? [];
        $targetDeps = $targetVersion['meta']['dependencies'] ?? [];
        
        $conflicts = [];
        foreach ($targetDeps as $dep => $version) {
            if (isset($currentDeps[$dep])) {
                $conflicts[$dep] = [
                    'current' => $currentDeps[$dep],
                    'target' => $version
                ];
            }
        }
        return $conflicts;
    }

    public function previewRollback(string $versionId): ?array {
        $version = $this->versioning->getVersion($versionId);
        if ($version === null) {
            return null;
        }

        $currentVersion = $this->versioning->listVersions($version['content_id'])[0] ?? null;
        if ($currentVersion === null) {
            return ['content' => $version['content']];
        }

        $currentContent = $this->versioning->getVersion($currentVersion['version_id'])['content'];
        
        return [
            'diff' => $this->comparison->compareVersionsSemantic(
                $currentContent,
                $version['content'],
                $currentVersion['meta'] ?? [],
                $version['meta'] ?? []
            ),
            'content' => $version['content'],
            'conflicts' => $this->detectPotentialConflicts($currentVersion, $version)
        ];
    }

    public function triggerWorkflow(string $workflowId, array $payload): bool {
        // Placeholder for workflow system integration
        return true;
    }
}
