<?php

namespace App\Services;

use App\Models\VersionComparison;
use App\Models\User;
use App\Models\Content;
use App\Models\ContentVersion;

class ComparisonTrackingService
{
    public function __construct(
        protected VersionComparison $comparisonModel
    ) {}

    public function recordComparison(
        User $user,
        Content $content,
        ContentVersion $versionA,
        ContentVersion $versionB,
        array $stats
    ): VersionComparison {
        return $this->comparisonModel->create([
            'user_id' => $user->id,
            'content_id' => $content->id,
            'version_a_id' => $versionA->id,
            'version_b_id' => $versionB->id,
            'lines_changed' => $stats['linesChanged'],
            'words_changed' => $stats['wordsChanged'],
            'similarity_score' => $stats['similarity'],
            'significant_changes' => $stats['significantChanges'],
            'change_rate' => $stats['changeRate'],
            'time_between' => $stats['timeBetween'],
            'compared_at' => now()
        ]);
    }

    public function getComparisonStatsForContent(Content $content, int $days = 30): array
    {
        $comparisons = $this->comparisonModel
            ->forContent($content->id)
            ->recent($days)
            ->get();

        return [
            'total_comparisons' => $comparisons->count(),
            'avg_lines_changed' => $comparisons->avg('lines_changed'),
            'avg_words_changed' => $comparisons->avg('words_changed'),
            'avg_similarity' => $comparisons->avg('similarity_score'),
            'significant_comparisons' => $comparisons->where('significant_changes', '>', 0)->count()
        ];
    }

    public function getRecentComparisons(int $limit = 5)
    {
        return $this->comparisonModel
            ->with(['content', 'versionA', 'versionB'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}