<?php
declare(strict_types=1);

class VersioningIntegration {
    private static $instance;
    private $versioningSystem;
    private $autoVersioning;
    private $rollbackManager;
    private $diffVisualizer;
    private $versionControlAPI;

    private function __construct() {
        $this->versioningSystem = ContentVersioningSystem::getInstance();
        $this->autoVersioning = new AutoVersioning();
        $this->rollbackManager = new RollbackManager();
        $this->diffVisualizer = new DiffVisualizer();
        $this->versionControlAPI = new VersionControlAPI();
    }

    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function initAutoVersioning(array $config = []): void {
        $this->autoVersioning->configure($config);
    }

    public function handleContentSave(int $contentId, string $content, int $authorId): void {
        $this->autoVersioning->checkAndCreateVersion($contentId, $content, $authorId);
    }

    public function getVersionHistory(int $contentId, int $limit = 10): array {
        return $this->rollbackManager->getRollbackHistory($contentId, $limit);
    }

    public function restoreContentVersion(int $versionId, int $userId): bool {
        return $this->rollbackManager->restoreVersion($versionId, $userId);
    }

    public function compareContentVersions(int $versionId1, int $versionId2): string {
        $content1 = $this->versioningSystem->getVersionContent($versionId1);
        $content2 = $this->versioningSystem->getVersionContent($versionId2);
        return $this->diffVisualizer->compareVersions($content1, $content2);
    }

    public function getDiffStyles(): string {
        return $this->diffVisualizer->getInlineDiffStyles();
    }

    public function handleAPIRequest(array $request): array {
        return $this->versionControlAPI->handleRequest($request);
    }

    public function cleanupOldVersions(int $days = 30): int {
        return $this->autoVersioning->cleanupOldContentVersions($days);
    }
}
