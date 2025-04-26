<?php

namespace App\Services;

use App\Models\ContentVersion;
use App\Models\VersionComparisonStat;
use Illuminate\Support\Facades\DB;

class VersionComparisonAnalyticsService
{
    public function recordComparison(
        ContentVersion $versionA,
        ContentVersion $versionB,
        ?int $userId = null
    ): VersionComparisonStat {
        $diff = $this->calculateDiffStats($versionA, $versionB);

        return VersionComparisonStat::create([
            'version_a_id' => $versionA->id,
            'version_b_id' => $versionB->id,
            'content_id' => $versionA->content_id,
            'user_id' => $userId,
            'similarity_percentage' => $diff['similarity'],
            'lines_added' => $diff['lines_added'],
            'lines_removed' => $diff['lines_removed'],
            'lines_unchanged' => $diff['lines_unchanged'],
            'words_added' => $diff['words_added'],
            'words_removed' => $diff['words_removed'],
            'words_unchanged' => $diff['words_unchanged'],
            'frequent_changes' => $diff['frequent_changes'],
            'change_distribution' => $diff['change_distribution']
        ]);
    }

    protected function calculateDiffStats(ContentVersion $versionA, ContentVersion $versionB): array
    {
        // TODO: Implement actual diff calculation
        // For now return mock data
        return [
            'similarity' => 75,
            'lines_added' => 10,
            'lines_removed' => 5,
            'lines_unchanged' => 85,
            'words_added' => 120,
            'words_removed' => 80,
            'words_unchanged' => 800,
            'frequent_changes' => [
                'headings' => 2,
                'paragraphs' => 5,
                'images' => 1,
                'metadata' => 0
            ],
            'change_distribution' => [
                'introduction' => 20,
                'body' => 50,
                'conclusion' => 10,
                'sidebar' => 5
            ]
        ];
    }

    public function getFrequentComparisons(int $contentId, int $limit = 5)
    {
        return VersionComparisonStat::query()
            ->where('content_id', $contentId)
            ->select([
                'version_a_id',
                'version_b_id',
                DB::raw('COUNT(*) as comparison_count')
            ])
            ->groupBy(['version_a_id', 'version_b_id'])
            ->orderByDesc('comparison_count')
            ->limit($limit)
            ->get();
    }

    public function getComparisonCount(int $contentId): int
    {
        return VersionComparisonStat::where('content_id', $contentId)->count();
    }

    public function getAverageSimilarity(int $contentId): float
    {
        return VersionComparisonStat::where('content_id', $contentId)
            ->avg('similarity_percentage') ?? 0;
    }

    public function getChangeDistribution(int $contentId): array
    {
        return VersionComparisonStat::where('content_id', $contentId)
            ->select([
                DB::raw('AVG(JSON_EXTRACT(change_distribution, "$.introduction")) as introduction'),
                DB::raw('AVG(JSON_EXTRACT(change_distribution, "$.body")) as body'),
                DB::raw('AVG(JSON_EXTRACT(change_distribution, "$.conclusion")) as conclusion'),
                DB::raw('AVG(JSON_EXTRACT(change_distribution, "$.sidebar")) as sidebar'),
            ])
            ->first()
            ->toArray();
    }

    public function getComparisonStats(int $versionAId, int $versionBId): array
    {
        return VersionComparisonStat::query()
            ->where(function($query) use ($versionAId, $versionBId) {
                $query->where('version_a_id', $versionAId)
                      ->where('version_b_id', $versionBId);
            })
            ->orWhere(function($query) use ($versionAId, $versionBId) {
                $query->where('version_a_id', $versionBId)
                      ->where('version_b_id', $versionAId);
            })
            ->firstOrFail()
            ->toArray();
    }

    public function getSystemStats()
    {
        return [
            'total_comparisons' => VersionComparisonStat::count(),
            'average_similarity' => VersionComparisonStat::avg('similarity_percentage'),
            'most_compared_content' => VersionComparisonStat::query()
                ->select(['content_id', DB::raw('COUNT(*) as comparison_count')])
                ->groupBy('content_id')
                ->orderByDesc('comparison_count')
                ->first()
        ];
    }
}