<?php
declare(strict_types=1);

class SiteGroupService {
    private static ?SiteGroupService $instance = null;
    private array $groups = [];
    private array $sharedContent = [];

    private function __construct() {
        // Load initial configuration
        $this->loadConfiguration();
    }

    public static function getInstance(): SiteGroupService {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfiguration(): void {
        // TODO: Load site groups and shared content from configuration
    }

    public function createGroup(string $name, array $sites): void {
        $this->groups[$name] = [
            'sites' => $sites,
            'created_at' => time()
        ];
    }

    public function addSharedContent(string $group, string $contentId): void {
        if (!isset($this->sharedContent[$group])) {
            $this->sharedContent[$group] = [];
        }
        $this->sharedContent[$group][] = $contentId;
    }

    public function getGroupSites(string $group): array {
        return $this->groups[$group]['sites'] ?? [];
    }

    public function getSharedContent(string $group): array {
        return $this->sharedContent[$group] ?? [];
    }

    public function isContentShared(string $contentId): bool {
        foreach ($this->sharedContent as $group => $contents) {
            if (in_array($contentId, $contents)) {
                return true;
            }
        }
        return false;
    }
}
