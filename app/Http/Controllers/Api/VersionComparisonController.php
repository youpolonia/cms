<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\VersionComparison;
use App\Services\ContentComparisonService;
use Illuminate\Http\Request;

class VersionComparisonController extends Controller
{
    protected $comparisonService;

    public function __construct(ContentComparisonService $comparisonService)
    {
        $this->comparisonService = $comparisonService;
    }

    public function compare(Request $request, Content $content)
    {
        $request->validate([
            'base_version_id' => 'required|exists:content_versions,id',
            'compare_version_id' => 'required|exists:content_versions,id'
        ]);

        $baseVersion = ContentVersion::findOrFail($request->base_version_id);
        $compareVersion = ContentVersion::findOrFail($request->compare_version_id);

        $comparison = $this->comparisonService->compareVersions(
            $baseVersion,
            $compareVersion
        );

        return response()->json([
            'data' => $comparison,
            'message' => 'Version comparison completed successfully'
        ]);
    }

    public function show(VersionComparison $comparison)
    {
        return response()->json([
            'data' => $comparison->load(['baseVersion', 'compareVersion'])
        ]);
    }

    public function index(Content $content)
    {
        $comparisons = VersionComparison::where('content_id', $content->id)
            ->with(['baseVersion', 'compareVersion'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => $comparisons
        ]);
    }
}