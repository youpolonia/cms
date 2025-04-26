<?php

namespace App\Http\Controllers;

use App\Models\ContentVersion;
use App\Services\ContentDiffService;
use Illuminate\Http\Request;

class VersionDiffController extends Controller
{
    protected $diffService;

    public function __construct(ContentDiffService $diffService)
    {
        $this->diffService = $diffService;
    }

    public function compareVersions($fromId, $toId)
    {
        $fromVersion = ContentVersion::findOrFail($fromId);
        $toVersion = ContentVersion::findOrFail($toId);

        return response()->json(
            $this->diffService->calculateDiff($fromVersion, $toVersion)
        );
    }

    public function bulkCompare(Request $request)
    {
        $request->validate([
            'from_version_id' => 'required|exists:content_versions,id',
            'to_version_id' => 'required|exists:content_versions,id'
        ]);

        $fromVersion = ContentVersion::find($request->input('from_version_id'));
        $toVersion = ContentVersion::find($request->input('to_version_id'));

        return response()->json(
            $this->diffService->calculateDiff($fromVersion, $toVersion)
        );
    }
}