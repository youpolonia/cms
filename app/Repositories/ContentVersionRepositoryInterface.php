<?php

namespace App\Repositories;

use App\Models\ContentVersion;
use Illuminate\Support\Collection;

interface ContentVersionRepositoryInterface
{
    /**
     * Find content version with view count
     */
    public function findWithViews(int $id): ContentVersion;

    /**
     * Get raw content for version
     */
    public function getContent(int $versionId): string;

    /**
     * Get related versions for content
     */
    public function getRelatedVersions(int $contentId, ?int $excludeId = null): Collection;

    /**
     * Get version timeline for content
     */
    public function getVersionTimeline(int $contentId): Collection;
}