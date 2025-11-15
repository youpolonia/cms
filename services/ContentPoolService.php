<?php
declare(strict_types=1);

class ContentPoolService {
    private static ?ContentPoolService $instance = null;
    private array $pools = [];
    private array $contentVersions = [];

    private function __construct() {
        // Initialize with default pools
        $this->initializeDefaultPools();
    }

    public static function getInstance(): ContentPoolService {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeDefaultPools(): void {
        // TODO: Load default content pools from configuration
    }

    public function createPool(string $name, array $sites): void {
        $this->pools[$name] = [
            'sites' => $sites,
            'created_at' => time()
        ];
    }

    public function addContentVersion(string $pool, string $contentId, string $version, array $data): void {
        if (!isset($this->contentVersions[$pool])) {
            $this->contentVersions[$pool] = [];
        }
        
        $this->contentVersions[$pool][$contentId][$version] = $data;
    }

    public function getLatestVersion(string $pool, string $contentId): ?array {
        if (empty($this->contentVersions[$pool][$contentId])) {
            return null;
        }
        
        $versions = $this->contentVersions[$pool][$contentId];
        uksort($versions, 'version_compare');
        return end($versions);
    }

    public function syncContent(string $pool, string $contentId): bool {
        $latest = $this->getLatestVersion($pool, $contentId);
        if (!$latest) {
            return false;
        }

        // TODO: Implement actual sync to member sites
        return true;
    }
}
