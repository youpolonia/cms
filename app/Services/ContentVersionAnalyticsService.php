<?php

namespace App\Services;

use App\Models\ContentVersion;
use App\Models\ContentUserView;
use App\Services\AnalyticsCacheService;

class ContentVersionAnalyticsService
{
    protected $cacheService;

    public function __construct(AnalyticsCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function getComparisonStats(ContentVersion $version1, ContentVersion $version2): array
    {
        // Check cache first
        if ($cachedStats = $this->cacheService->getStats($version1, $version2)) {
            return $cachedStats;
        }

        $oldContent = json_decode($version1->content_data, true)['body'] ?? '';
        $newContent = json_decode($version2->content_data, true)['body'] ?? '';

        $diff = $this->calculateDiffStats($oldContent, $newContent);
        
        $stats = [
            'change_count' => $diff['total_changes'],
            'additions' => $diff['additions'],
            'deletions' => $diff['deletions'],
            'similarity' => $this->calculateSimilarity($oldContent, $newContent),
            'changed_lines' => $diff['changed_lines'],
            'version1_views' => ContentUserView::where('content_version_id', $version1->id)->count(),
            'version2_views' => ContentUserView::where('content_version_id', $version2->id)->count(),
        ];

        // Store in cache
        $this->cacheService->storeStats($version1, $version2, $stats);

        return $stats;
    }

    protected function calculateDiffStats(string $oldContent, string $newContent): array
    {
        $oldLines = explode("\n", $oldContent);
        $newLines = explode("\n", $newContent);
        
        $additions = 0;
        $deletions = 0;
        $changedLines = 0;
        
        foreach ($newLines as $i => $line) {
            if (!isset($oldLines[$i])) {
                $additions++;
            } elseif ($line !== $oldLines[$i]) {
                $changedLines++;
            }
        }
        
        $deletions = count($oldLines) - (count($newLines) - $additions);
        
        return [
            'total_changes' => $additions + $deletions + $changedLines,
            'additions' => $additions,
            'deletions' => $deletions,
            'changed_lines' => $changedLines
        ];
    }

    protected function calculateSimilarity(string $oldContent, string $newContent): float
    {
        similar_text($oldContent, $newContent, $percent);
        return round($percent, 2);
    }
}