<?php

namespace CMS\Core;

class ContentVersioningSystem
{
    private $versionHistory = [];
    private $currentVersion;
    private $tenantId;

    public function __construct(string $tenantId)
    {
        $this->tenantId = $tenantId;
        $this->currentVersion = $this->generateInitialVersion();
    }

    public function createVersion(string $content, string $author): string
    {
        $versionHash = $this->generateVersionHash($content);
        $versionData = [
            'hash' => $versionHash,
            'timestamp' => time(),
            'author' => $author,
            'content' => $content
        ];

        $this->versionHistory[$versionHash] = $versionData;
        $this->currentVersion = $versionHash;
        
        return $versionHash;
    }

    public function getVersion(string $versionHash): ?array
    {
        return $this->versionHistory[$versionHash] ?? null;
    }

    public function getCurrentVersion(): string
    {
        return $this->currentVersion;
    }

    public function getVersionHistory(): array
    {
        return $this->versionHistory;
    }

    public function syncWithExternalVersion(array $externalVersion): bool
    {
        if (!isset($externalVersion['hash'])) {
            return false;
        }

        $this->versionHistory[$externalVersion['hash']] = $externalVersion;
        return true;
    }

    public function resolveConflict(string $localVersionHash, string $externalVersionHash, string $resolutionStrategy): string
    {
        $local = $this->getVersion($localVersionHash);
        $external = $this->getVersion($externalVersionHash);

        if (!$local || !$external) {
            return $this->currentVersion;
        }

        switch ($resolutionStrategy) {
            case 'last-write-wins':
                return $local['timestamp'] > $external['timestamp'] ? $localVersionHash : $externalVersionHash;
            case 'manual':
                return $this->currentVersion;
            default:
                return $this->currentVersion;
        }
    }

    private function generateVersionHash(string $content): string
    {
        return hash('sha256', $this->tenantId . $content . microtime());
    }

    private function generateInitialVersion(): string
    {
        return hash('sha256', $this->tenantId . 'initial');
    }
}
