<?php

namespace App\Services;

use App\Models\ContentVersion;
use App\Models\ContentVersionComparison;
use App\Models\ContentUserView;
use Illuminate\Support\Facades\Auth;

class EnhancedContentDiffService
{
    private ContentDiffService $basicDiffService;
    private ?AIAnalysisService $aiService;

    public function __construct(ContentDiffService $basicDiffService, ?AIAnalysisService $aiService = null)
    {
        $this->basicDiffService = $basicDiffService;
        $this->aiService = $aiService;
    }

    public function compareVersions(ContentVersion $oldVersion, ContentVersion $newVersion): array
    {
        $basicDiff = $this->basicDiffService->calculateDiff($oldVersion, $newVersion);
        
        $enhancedDiff = [
            'basic_metrics' => $basicDiff['metrics'],
            'change_categories' => [],
            'semantic_analysis' => null,
            'ai_insights' => null
        ];

        // Add AI analysis if available
        if ($this->aiService) {
            $enhancedDiff['semantic_analysis'] = $this->aiService->analyzeChanges(
                $oldVersion->content,
                $newVersion->content
            );
            
            $enhancedDiff['ai_insights'] = $this->aiService->generateChangeInsights(
                $basicDiff['metrics'],
                $enhancedDiff['semantic_analysis']
            );
        }

        // Categorize changes
        $enhancedDiff['change_categories'] = $this->categorizeChanges($basicDiff);

        // Track this comparison
        $this->trackComparison($oldVersion, $newVersion, $enhancedDiff);

        return array_merge($basicDiff, $enhancedDiff);
    }

    protected function categorizeChanges(array $diff): array
    {
        $categories = [
            'structural' => 0,
            'content' => 0,
            'formatting' => 0
        ];

        foreach ($diff['line_changes'] as $change) {
            if ($change['type'] === 'added' || $change['type'] === 'removed') {
                $categories['structural']++;
            } elseif (strlen($change['old_content']) !== strlen($change['new_content'])) {
                $categories['formatting']++;
            } else {
                $categories['content']++;
            }
        }

        return $categories;
    }

    protected function trackComparison(
        ContentVersion $oldVersion,
        ContentVersion $newVersion,
        array $diffData
    ): void {
        // Record in content_version_comparisons table
        ContentVersionComparison::create([
            'base_version_id' => $oldVersion->id,
            'compare_version_id' => $newVersion->id,
            'metrics' => $diffData['basic_metrics'],
            'change_categories' => $diffData['change_categories'],
            'significance' => $this->calculateSignificance($diffData),
            'user_id' => Auth::id()
        ]);

        // Update user's AI usage count if AI was used
        if ($diffData['ai_insights']) {
            ContentUserView::updateOrCreate(
                ['user_id' => Auth::id()],
                ['ai_usage_count' => \DB::raw('ai_usage_count + 1')]
            );
        }
    }

    protected function calculateSignificance(array $diffData): string
    {
        $score = $diffData['basic_metrics']['characters']['added'] * 0.1 +
               $diffData['basic_metrics']['characters']['removed'] * 0.1 +
               $diffData['basic_metrics']['words']['added'] * 0.3 +
               $diffData['basic_metrics']['words']['removed'] * 0.3 +
               $diffData['basic_metrics']['lines']['added'] * 0.5 +
               $diffData['basic_metrics']['lines']['removed'] * 0.5;

        if ($score < 5) return 'minor';
        if ($score < 20) return 'moderate';
        if ($score < 50) return 'significant';
        return 'major';
    }
}