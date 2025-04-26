<?php

namespace App\Repositories;

use App\Models\ContentVersion;
use App\Repositories\Contracts\ContentVersionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentContentVersionRepository implements ContentVersionRepositoryInterface
{
    public function createVersion(array $data): ContentVersion
    {
        return ContentVersion::create($data);
    }

    public function getVersionsForContent(int $contentId, bool $includeAutosaves = false)
    {
        $query = ContentVersion::where('content_id', $contentId)
            ->orderBy('created_at', 'desc');

        if (!$includeAutosaves) {
            $query->where('is_autosave', false);
        }

        return $query->get();
    }

    public function getPublishedVersion(int $contentId): ?ContentVersion
    {
        return ContentVersion::where('content_id', $contentId)
            ->where('is_published', true)
            ->first();
    }

    public function getVersionById(int $versionId): ?ContentVersion
    {
        return ContentVersion::find($versionId);
    }

    public function publishVersion(int $versionId): bool
    {
        return DB::transaction(function () use ($versionId) {
            // Unpublish any currently published version
            ContentVersion::where('content_id', function ($query) use ($versionId) {
                $query->select('content_id')
                    ->from('content_versions')
                    ->where('id', $versionId);
            })
            ->where('is_published', true)
            ->update(['is_published' => false]);

            // Publish the new version
            return ContentVersion::where('id', $versionId)
                ->update([
                    'is_published' => true,
                    'published_at' => now()
                ]);
        });
    }

    public function restoreVersion(int $versionId): bool
    {
        $version = $this->getVersionById($versionId);
        if (!$version) {
            return false;
        }

        return (bool) $this->createVersion([
            'content_id' => $version->content_id,
            'content_data' => $version->content_data,
            'created_by' => auth()->id(),
            'restored_from' => $versionId
        ]);
    }

    public function deleteVersion(int $versionId): bool
    {
        return (bool) ContentVersion::where('id', $versionId)->delete();
    }

    public function compareVersions(int $versionId1, int $versionId2): array
    {
        $version1 = $this->getVersionById($versionId1);
        $version2 = $this->getVersionById($versionId2);

        if (!$version1 || !$version2) {
            throw new \InvalidArgumentException('One or both versions not found');
        }

        if ($version1->content_id !== $version2->content_id) {
            throw new \InvalidArgumentException('Versions must belong to the same content');
        }

        // Implement your comparison logic here
        return [
            'changes' => [], // Array of changes
            'stats' => []    // Comparison statistics
        ];
    }
}