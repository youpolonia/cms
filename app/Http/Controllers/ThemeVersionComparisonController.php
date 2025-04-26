<?php

namespace App\Http\Controllers;

use App\Models\ThemeVersionComparisonStat;
use Illuminate\Http\Request;

class ThemeVersionComparisonController extends Controller
{
    public function compareStats(Request $request, $themeId, $baseVersionId, $targetVersionId)
    {
        $stats = ThemeVersionComparisonStat::where([
            'theme_id' => $themeId,
            'base_version_id' => $baseVersionId,
            'target_version_id' => $targetVersionId
        ])->firstOrFail();

        return response()->json([
            'data' => $stats,
            'message' => 'Theme version comparison stats retrieved successfully'
        ]);
    }

    public function sizeMetrics(Request $request, $themeId, $baseVersionId, $targetVersionId)
    {
        $stats = ThemeVersionComparisonStat::where([
            'theme_id' => $themeId,
            'base_version_id' => $baseVersionId,
            'target_version_id' => $targetVersionId
        ])->firstOrFail();

        return response()->json([
            'data' => [
                'total_size_diff_kb' => $stats->total_size_diff_kb,
                'css_size_diff_kb' => $stats->css_size_diff_kb,
                'js_size_diff_kb' => $stats->js_size_diff_kb,
                'image_size_diff_kb' => $stats->image_size_diff_kb,
                'file_count_diff' => $stats->file_count_diff
            ],
            'message' => 'Theme version size metrics retrieved successfully'
        ]);
    }

    public function fileChanges(Request $request, $themeId, $baseVersionId, $targetVersionId)
    {
        $stats = ThemeVersionComparisonStat::where([
            'theme_id' => $themeId,
            'base_version_id' => $baseVersionId,
            'target_version_id' => $targetVersionId
        ])->firstOrFail();

        $comparisonData = $stats->comparison_data ?? [];
        $fileChanges = $comparisonData['file_changes'] ?? [];

        // Apply file type filter if specified
        if ($request->has('file_type')) {
            $fileType = $request->input('file_type');
            $fileChanges = array_filter($fileChanges, function($change) use ($fileType) {
                $extension = pathinfo($change['file_path'], PATHINFO_EXTENSION);
                return strtolower($extension) === strtolower($fileType);
            });
        }

        // Paginate results
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedChanges = array_slice($fileChanges, $offset, $perPage);

        return response()->json([
            'data' => [
                'changes' => $paginatedChanges,
                'pagination' => [
                    'total' => count($fileChanges),
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil(count($fileChanges) / $perPage)
                ]
            ],
            'message' => 'Theme version file changes retrieved successfully'
        ]);
    }

    /**
     * Get quality metrics for version comparison
     */
    public function qualityMetrics(Request $request, $themeId, $baseVersionId, $targetVersionId)
    {
        $stats = ThemeVersionComparisonStat::where([
            'theme_id' => $themeId,
            'base_version_id' => $baseVersionId,
            'target_version_id' => $targetVersionId
        ])->firstOrFail();

        return response()->json([
            'data' => [
                'quality_score' => $stats->quality_score,
                'performance_impact' => $stats->performance_impact,
                'compatibility_issues' => $stats->comparison_data['compatibility_issues'] ?? []
            ],
            'message' => 'Theme version quality metrics retrieved successfully'
        ]);
    }

    /**
     * Get summary statistics for version comparison
     */
    public function summary(Request $request, $themeId, $baseVersionId, $targetVersionId)
    {
        $stats = ThemeVersionComparisonStat::where([
            'theme_id' => $themeId,
            'base_version_id' => $baseVersionId,
            'target_version_id' => $targetVersionId
        ])->firstOrFail();

        return response()->json([
            'data' => [
                'files_added' => $stats->files_added,
                'files_removed' => $stats->files_removed,
                'files_modified' => $stats->files_modified,
                'lines_added' => $stats->lines_added,
                'lines_removed' => $stats->lines_removed,
                'total_size_diff_kb' => $stats->total_size_diff_kb,
                'file_count_diff' => $stats->file_count_diff,
                'quality_score' => $stats->quality_score,
                'performance_impact' => $stats->performance_impact
            ],
            'message' => 'Theme version comparison summary retrieved successfully'
        ]);
    }
}
