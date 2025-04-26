<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ContentVersionService;
use Illuminate\Http\Request;

class ContentVersionController extends Controller
{
    protected $versionService;

    public function __construct(ContentVersionService $versionService)
    {
        $this->versionService = $versionService;
    }

    public function index(Request $request, int $contentId)
    {
        $includeAutosaves = $request->boolean('include_autosaves', false);
        $versions = $this->versionService->getContentVersions($contentId, $includeAutosaves);

        return response()->json([
            'data' => $versions,
            'meta' => [
                'count' => $versions->count()
            ]
        ]);
    }

    public function store(Request $request, int $contentId)
    {
        $request->validate([
            'content_data' => 'required|array',
            'is_autosave' => 'sometimes|boolean'
        ]);

        $version = $this->versionService->createNewVersion(
            $contentId,
            $request->input('content_data'),
            $request->boolean('is_autosave', false)
        );

        return response()->json([
            'data' => $version,
            'message' => 'Version created successfully'
        ], 201);
    }

    public function publish(int $versionId)
    {
        $success = $this->versionService->publishVersion($versionId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Version published successfully' : 'Failed to publish version'
        ]);
    }

    public function restore(int $versionId)
    {
        try {
            $version = $this->versionService->restoreVersion($versionId);
            return response()->json([
                'data' => $version,
                'message' => 'Version restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function compare(Request $request)
    {
        $request->validate([
            'version1' => 'required|integer',
            'version2' => 'required|integer'
        ]);

        $diff = $this->versionService->compareVersions(
            $request->input('version1'),
            $request->input('version2')
        );

        return response()->json([
            'data' => $diff
        ]);
    }
}