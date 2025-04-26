<?php

namespace App\Http\Controllers;

use App\Models\ContentVersion;
use App\Services\SemanticDiffService;
use App\Services\ContentVersionAnalyticsService;
use Illuminate\Http\Request;

class ContentVersionComparisonController extends Controller
{
    protected SemanticDiffService $diffService;
    protected ContentVersionAnalyticsService $analyticsService;

    public function __construct(
        SemanticDiffService $diffService,
        ContentVersionAnalyticsService $analyticsService
    ) {
        $this->diffService = $diffService;
        $this->analyticsService = $analyticsService;
    }

    public function compare($contentId, $oldVersionId, $newVersionId)
    {
        $version = ContentVersion::where('content_id', $contentId)
            ->findOrFail($oldVersionId);
        $comparedVersion = ContentVersion::where('content_id', $contentId)
            ->findOrFail($newVersionId);

        $oldContent = json_decode($version->content_data, true)['body'] ?? '';
        $newContent = json_decode($comparedVersion->content_data, true)['body'] ?? '';

        $diff = $this->diffService->compareMarkdown($oldContent, $newContent);
        $stats = $this->analyticsService->getComparisonStats($version, $comparedVersion);

        return response()->json([
            'content_id' => $contentId,
            'old_version' => [
                'id' => $version->id,
                'version_number' => $version->version_number,
                'created_at' => $version->created_at,
                'user_id' => $version->created_by
            ],
            'new_version' => [
                'id' => $comparedVersion->id,
                'version_number' => $comparedVersion->version_number,
                'created_at' => $comparedVersion->created_at,
                'user_id' => $comparedVersion->created_by
            ],
            'comparison' => [
                'content_diff' => $diff,
                'meta_diff' => $stats['metadata'],
                'seo_diff' => $stats['seo']
            ]
        ]);
    }

    public function analytics(Request $request)
    {
        $request->validate([
            'version_id' => 'required|exists:content_versions,id',
            'compared_version_id' => 'required|exists:content_versions,id'
        ]);

        $version = ContentVersion::findOrFail($request->version_id);
        $comparedVersion = ContentVersion::findOrFail($request->compared_version_id);

        return response()->json(
            $this->analyticsService->getComparisonStats($version, $comparedVersion)
        );
    }
}