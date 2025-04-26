<?php

namespace App\Http\Controllers\ContentVersion;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Services\ContentVersionComparisonService;
use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    protected $comparisonService;

    public function __construct(ContentVersionComparisonService $comparisonService)
    {
        $this->comparisonService = $comparisonService;
    }

    public function index(Content $content)
    {
        $versions = $content->versions()
            ->orderBy('version_number', 'desc')
            ->paginate(10);

        return view('content.versions.comparison.index', [
            'content' => $content,
            'versions' => $versions
        ]);
    }

    public function show(Content $content, ContentVersion $versionFrom, ContentVersion $versionTo)
    {
        $comparison = $this->comparisonService->compareVersions($versionFrom, $versionTo);

        return view('content.versions.comparison.show', [
            'content' => $content,
            'comparison' => $comparison,
            'versionFrom' => $versionFrom,
            'versionTo' => $versionTo
        ]);
    }

    public function compare(Request $request, Content $content)
    {
        $request->validate([
            'version_from' => 'required|exists:content_versions,id',
            'version_to' => 'required|exists:content_versions,id'
        ]);

        $versionFrom = ContentVersion::find($request->version_from);
        $versionTo = ContentVersion::find($request->version_to);

        return redirect()->route('content.versions.comparison.show', [
            'content' => $content->id,
            'versionFrom' => $versionFrom->id,
            'versionTo' => $versionTo->id
        ]);
    }
}