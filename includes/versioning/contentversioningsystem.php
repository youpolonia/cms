<?php
declare(strict_types=1);

class ContentVersioningSystem {
    private static $instance;
    private $dbConnection;
    private $contentStoragePath;

    private function __construct() {
        $this->dbConnection = \core\Database::connection();
        $this->contentStoragePath = __DIR__ . '/../../storage/content_versions/';
        
        if (!is_dir($this->contentStoragePath)) {
            mkdir($this->contentStoragePath, 0755, true);
        }
    }

    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function createVersion(int $contentId, string $content, array $metadata = []): int {
        $this->validateContentId($contentId);
        $this->validateContent($content);

        $versionId = $this->createVersionRecord($contentId, $metadata);
        $this->storeVersionContent($versionId, $content);

        return $versionId;
    }

    private function createVersionRecord(int $contentId, array $metadata): int {
        $stmt = $this->dbConnection->prepare(
            "INSERT INTO content_versions 
            (content_id, author_id, change_summary) 
            VALUES (?, ?, ?)"
        );
        
        $stmt->execute([
            $contentId,
            $metadata['author_id'] ?? 0,
            $metadata['change_summary'] ?? ''
        ]);

        return (int)$this->dbConnection->lastInsertId();
    }

    private function storeVersionContent(int $versionId, string $content): void {
        $filePath = $this->getVersionFilePath($versionId);
        file_put_contents($filePath, $content);
    }

    public function getVersionContent(int $versionId): string {
        $filePath = $this->getVersionFilePath($versionId);
        
        if (!file_exists($filePath)) {
            throw new RuntimeException("Version content not found");
        }

        return file_get_contents($filePath);
    }

    public function getVersionMetadata(int $versionId): array {
        $stmt = $this->dbConnection->prepare(
            "SELECT * FROM content_versions WHERE version_id = ?"
        );
        $stmt->execute([$versionId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            throw new RuntimeException("Version not found");
        }

        return $result;
    }

    public function deleteVersion(int $versionId): bool {
        $filePath = $this->getVersionFilePath($versionId);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $stmt = $this->dbConnection->prepare(
            "DELETE FROM content_versions WHERE version_id = ?"
        );
        return $stmt->execute([$versionId]);
    }

    private function getVersionFilePath(int $versionId): string {
        return $this->contentStoragePath . "version_{$versionId}.dat";
    }

    private function validateContentId(int $contentId): void {
        if ($contentId <= 0) {
            throw new InvalidArgumentException("Invalid content ID");
        }
    }

    private function validateContent(string $content): void {
        if (empty(trim($content))) {
            throw new InvalidArgumentException("Content cannot be empty");
        }
    }
}
