<?php

namespace App\Http\Controllers;

use App\Models\ContentVersion;
use App\Services\ContentDiffService;
use Illuminate\Http\Request;

class VersionComparisonController extends Controller
{
    protected $diffService;

    public function __construct(ContentDiffService $diffService)
    {
        $this->diffService = $diffService;
    }

    public function compare(Request $request, $versionAId, $versionBId)
    {
        $versionA = ContentVersion::findOrFail($versionAId);
        $versionB = ContentVersion::findOrFail($versionBId);

        // Verify versions belong to same content
        if ($versionA->content_id !== $versionB->content_id) {
            abort(400, 'Versions must belong to the same content');
        }

        $diff = $this->diffService->calculateDiff($versionA, $versionB);

        return response()->json([
            'success' => true,
            'data' => $diff,
            'version_a' => $versionA->only(['id', 'version_number', 'created_at', 'user_id']),
            'version_b' => $versionB->only(['id', 'version_number', 'created_at', 'user_id'])
        ]);
    }

    public function show(Request $request, $versionAId, $versionBId)
    {
        $versionA = ContentVersion::findOrFail($versionAId);
        $versionB = ContentVersion::findOrFail($versionBId);

        // Verify versions belong to same content
        if ($versionA->content_id !== $versionB->content_id) {
            abort(400, 'Versions must belong to the same content');
        }

        $diff = $this->diffService->calculateDiff($versionA, $versionB);

        return view('content.version-comparison', [
            'versionA' => $versionA,
            'versionB' => $versionB,
            'diff' => $diff
        ]);
    }
}