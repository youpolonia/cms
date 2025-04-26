<?php

namespace App\Repositories\Eloquent;

use App\Models\ContentVersion;
use App\Repositories\Contracts\ContentVersionRepositoryInterface;

class ContentVersionRepository implements ContentVersionRepositoryInterface
{
    public function findById(int $id): ?ContentVersion
    {
        return ContentVersion::find($id);
    }

    public function findByContentId(int $contentId): array
    {
        return ContentVersion::where('content_id', $contentId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function create(array $data): ContentVersion
    {
        return ContentVersion::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return ContentVersion::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return ContentVersion::destroy($id);
    }

    public function publish(int $id): bool
    {
        return ContentVersion::where('id', $id)->update(['is_published' => true]);
    }

    public function restore(int $id): bool
    {
        return ContentVersion::where('id', $id)->update(['is_restored' => true]);
    }

    public function compareVersions(int $versionA, int $versionB): array
    {
        $version1 = $this->findById($versionA);
        $version2 = $this->findById($versionB);
        
        return [
            'differences' => array_diff($version1->toArray(), $version2->toArray()),
            'similarities' => array_intersect($version1->toArray(), $version2->toArray())
        ];
    }
}