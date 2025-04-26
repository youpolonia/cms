<?php

namespace App\Services;

use App\Models\ExportTag;
use App\Models\AnalyticsExport;
use Illuminate\Support\Collection;

class ExportTagService
{
    /**
     * Create a new tag or return existing one
     */
    public function findOrCreateTag(string $name, string $color = '#3b82f6'): ExportTag
    {
        return ExportTag::firstOrCreate(
            ['name' => $name],
            ['color' => $color]
        );
    }

    /**
     * Assign tags to an export
     */
    public function assignTags(AnalyticsExport $export, array $tags): void
    {
        $tagIds = collect($tags)->map(function ($tag) {
            if (is_numeric($tag)) {
                return $tag;
            }
            return $this->findOrCreateTag($tag)->id;
        });

        $export->tags()->sync($tagIds);
    }

    /**
     * Get all tags with usage counts
     */
    public function getAllTagsWithCounts(): Collection
    {
        return ExportTag::withCount('exports')
            ->orderBy('name')
            ->get();
    }

    /**
     * Search tags by name
     */
    public function searchTags(string $query, int $limit = 10): Collection
    {
        return ExportTag::search($query)
            ->take($limit)
            ->get();
    }
}