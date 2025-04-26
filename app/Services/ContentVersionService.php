<?php

namespace App\Services;

use App\Repositories\Contracts\ContentVersionRepositoryInterface;
use App\Models\ContentVersion;
use Illuminate\Support\Facades\Auth;

class ContentVersionService
{
    protected $versionRepository;

    public function __construct(ContentVersionRepositoryInterface $versionRepository)
    {
        $this->versionRepository = $versionRepository;
    }

    public function createNewVersion(int $contentId, array $contentData, bool $isAutosave = false): ContentVersion
    {
        return $this->versionRepository->createVersion([
            'content_id' => $contentId,
            'content_data' => $contentData,
            'created_by' => Auth::id(),
            'is_autosave' => $isAutosave,
            'version_number' => $this->getNextVersionNumber($contentId)
        ]);
    }

    public function getContentVersions(int $contentId, bool $includeAutosaves = false)
    {
        return $this->versionRepository->getVersionsForContent($contentId, $includeAutosaves);
    }

    public function publishVersion(int $versionId): bool
    {
        return $this->versionRepository->publishVersion($versionId);
    }

    public function restoreVersion(int $versionId): ContentVersion
    {
        $restored = $this->versionRepository->restoreVersion($versionId);
        if (!$restored) {
            throw new \RuntimeException('Failed to restore version');
        }
        return $restored;
    }

    public function compareVersions(int $versionId1, int $versionId2): array
    {
        return $this->versionRepository->compareVersions($versionId1, $versionId2);
    }

    protected function getNextVersionNumber(int $contentId): int
    {
        $latestVersion = $this->versionRepository->getVersionsForContent($contentId)
            ->first();

        return $latestVersion ? $latestVersion->version_number + 1 : 1;
    }

    public function getVersionDiff(int $versionId1, int $versionId2): array
    {
        return $this->versionRepository->compareVersions($versionId1, $versionId2);
    }
}