<?php

namespace App\Services;

use App\Models\ContentVersion;
use App\Models\ContentVersionDiff;
use Illuminate\Support\Facades\DB;

class ContentApprovalComparisonService
{
    protected ContentDiffService $diffService;

    public function __construct(ContentDiffService $diffService)
    {
        $this->diffService = $diffService;
    }

    public function compareForApproval(ContentVersion $fromVersion, ContentVersion $toVersion): array
    {
        $basicDiff = $this->diffService->calculateDiff($fromVersion, $toVersion);
        
        return [
            'basic_diff' => $basicDiff,
            'approval_metrics' => $this->calculateApprovalMetrics($basicDiff),
            'risk_assessment' => $this->assessRisks($basicDiff, $toVersion),
            'impact_analysis' => $this->analyzeImpact($fromVersion, $toVersion),
            'comparison_history' => $this->getComparisonHistory($fromVersion, $toVersion)
        ];
    }

    protected function calculateApprovalMetrics(array $diff): array
    {
        $totalChanges = $diff['characters_added'] + $diff['characters_removed'];
        $changePercentage = $totalChanges > 0 
            ? ($totalChanges / max(strlen($diff['old_content'] ?? ''), strlen($diff['new_content'] ?? ''))) * 100
            : 0;

        return [
            'change_percentage' => round($changePercentage, 2),
            'risk_score' => min(100, $changePercentage * 1.5), // Simple risk calculation
            'approval_threshold' => $this->getApprovalThreshold($diff),
            'sections_affected' => $this->countAffectedSections($diff)
        ];
    }

    protected function assessRisks(array $diff, ContentVersion $newVersion): array
    {
        $risks = [];
        
        // Check for large content removals
        if ($diff['characters_removed'] > 1000) {
            $risks[] = 'large_content_removal';
        }

        // Check for HTML/script changes
        if (str_contains($diff['new_content'] ?? '', '<script>')) {
            $risks[] = 'potential_xss';
        }

        // Check for sensitive keywords
        $sensitiveKeywords = ['password', 'credit card', 'ssn'];
        foreach ($sensitiveKeywords as $keyword) {
            if (str_contains(strtolower($diff['new_content'] ?? ''), $keyword)) {
                $risks[] = 'sensitive_data';
                break;
            }
        }

        return [
            'identified_risks' => $risks,
            'risk_level' => count($risks) > 0 ? 'high' : 'low'
        ];
    }

    protected function analyzeImpact(ContentVersion $oldVersion, ContentVersion $newVersion): array
    {
        $dependencies = $this->getContentDependencies($newVersion);
        
        return [
            'api_dependencies' => $dependencies['apis'],
            'template_dependencies' => $dependencies['templates'],
            'media_dependencies' => $dependencies['media'],
            'estimated_affected_users' => $this->estimateAffectedUsers($oldVersion->content)
        ];
    }

    protected function getComparisonHistory(ContentVersion $version1, ContentVersion $version2): array
    {
        return DB::table('content_version_diffs')
            ->where(function($q) use ($version1, $version2) {
                $q->where('from_version_id', $version1->id)
                  ->where('to_version_id', $version2->id);
            })
            ->orWhere(function($q) use ($version1, $version2) {
                $q->where('from_version_id', $version2->id)
                  ->where('to_version_id', $version1->id);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    protected function getContentDependencies(ContentVersion $version): array
    {
        // Parse content to find dependencies
        $content = $version->content_data['content'] ?? '';
        
        return [
            'apis' => $this->extractApiCalls($content),
            'templates' => $this->extractTemplateReferences($content),
            'media' => $this->extractMediaReferences($content)
        ];
    }

    // Helper methods would be implemented here...
    protected function extractApiCalls(string $content): array { /* ... */ }
    protected function extractTemplateReferences(string $content): array { /* ... */ }
    protected function extractMediaReferences(string $content): array { /* ... */ }
    protected function estimateAffectedUsers(Content $content): int { /* ... */ }
    protected function getApprovalThreshold(array $diff): string { /* ... */ }
    protected function countAffectedSections(array $diff): int { /* ... */ }
    public function restoreVersion(ContentVersion $version): ContentVersion
    {
        $newVersion = $version->replicate();
        $newVersion->status = 'restored';
        $newVersion->restored_from = $version->id;
        $newVersion->save();

        // Create a diff record
        ContentVersionDiff::create([
            'from_version_id' => $version->id,
            'to_version_id' => $newVersion->id,
            'diff_data' => $this->diffService->calculateDiff($version, $newVersion),
            'restore_operation' => true
        ]);

        return $newVersion;
    }

    public function getVisualDiff(ContentVersion $version): array
    {
        $previousVersion = $version->content->versions()
            ->where('id', '<', $version->id)
            ->orderBy('id', 'desc')
            ->first();

        if (!$previousVersion) {
            return [
                'html_diff' => '',
                'side_by_side' => [
                    'old' => '',
                    'new' => $version->content_data['content'] ?? ''
                ]
            ];
        }

        $diff = $this->diffService->calculateDiff($previousVersion, $version);
        
        return [
            'html_diff' => $this->formatHtmlDiff($diff),
            'side_by_side' => [
                'old' => $previousVersion->content_data['content'] ?? '',
                'new' => $version->content_data['content'] ?? ''
            ],
            'stats' => [
                'characters_added' => $diff['characters_added'] ?? 0,
                'characters_removed' => $diff['characters_removed'] ?? 0,
                'lines_added' => $diff['lines_added'] ?? 0,
                'lines_removed' => $diff['lines_removed'] ?? 0
            ]
        ];
    }

    protected function formatHtmlDiff(array $diff): string
    {
        // Simple HTML formatting for diff visualization
        $html = '';
        $oldLines = explode("\n", $diff['old_content'] ?? '');
        $newLines = explode("\n", $diff['new_content'] ?? '');
        
        foreach ($diff['line_changes'] ?? [] as $change) {
            if ($change['type'] === 'added') {
                $html .= '<div class="diff-added">+ ' . htmlspecialchars($newLines[$change['new_line']]) . '</div>';
            } elseif ($change['type'] === 'removed') {
                $html .= '<div class="diff-removed">- ' . htmlspecialchars($oldLines[$change['old_line']]) . '</div>';
            } else {
                $html .= '<div class="diff-unchanged">  ' . htmlspecialchars($newLines[$change['new_line']]) . '</div>';
            }
        }
        
        return $html;
    }

    public function bulkRestoreVersions(array $versionIds): array
    {
        $results = [];
        foreach ($versionIds as $versionId) {
            $version = ContentVersion::findOrFail($versionId);
            $results[] = $this->restoreVersion($version);
        }
        return $results;
    }
}