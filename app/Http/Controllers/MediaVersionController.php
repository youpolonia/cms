<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\MediaVersion;
use App\Models\MediaBranch;
use Illuminate\Http\Request;

class MediaVersionController extends Controller
{
    public function index(Media $media)
    {
        $query = $media->versions();
        
        if ($tag = request('tag')) {
            $query->whereJsonContains('tags', $tag);
        }

        if ($branch = request('branch')) {
            $query->where('branch_name', $branch);
        }

        return view('media.versions.index', [
            'media' => $media,
            'versions' => $query->paginate(10),
            'branches' => $media->branches()->get()
        ]);
    }

    public function show(Media $media, MediaVersion $version)
    {
        return view('media.versions.show', [
            'media' => $media,
            'version' => $version,
            'childVersions' => $version->childVersions()->get()
        ]);
    }

    public function restore(Request $request, Media $media)
    {
        $request->validate([
            'version_number' => 'required|integer|min:1|max:'.$media->version_count
        ]);

        $versionNumber = $request->input('version_number');
        
        if ($media->restoreVersion($versionNumber)) {
            return redirect()->route('media.show', $media)
                ->with('success', "Restored to version $versionNumber successfully");
        }

        return back()->with('error', 'Failed to restore version');
    }

    public function compare(Media $media, MediaVersion $version1, MediaVersion $version2 = null)
    {
        $version2 = $version2 ?? $media->current_version_data;

        return view('media.versions.compare', [
            'media' => $media,
            'version1' => $version1,
            'version2' => $version2
        ]);
    }

    public function diff(Media $media, MediaVersion $version1, MediaVersion $version2 = null)
    {
        $version2 = $version2 ?? $media->current_version_data;

        // Generate line-by-line diffs
        $diffs = [];
        $oldLines = explode("\n", $version1->metadata['content'] ?? '');
        $newLines = explode("\n", $version2->metadata['content'] ?? '');

        foreach ($oldLines as $i => $line) {
            if (!isset($newLines[$i])) {
                $diffs[] = ['type' => 'removed', 'old' => $line, 'new' => ''];
            } elseif ($line !== $newLines[$i]) {
                $diffs[] = ['type' => 'changed', 'old' => $line, 'new' => $newLines[$i]];
            } else {
                $diffs[] = ['type' => 'unchanged', 'old' => $line, 'new' => $newLines[$i]];
            }
        }

        // Handle any extra lines in new version
        for ($i = count($oldLines); $i < count($newLines); $i++) {
            $diffs[] = ['type' => 'added', 'old' => '', 'new' => $newLines[$i]];
        }

        return view('media.versions.diff', [
            'media' => $media,
            'version1' => $version1,
            'version2' => $version2,
            'diffs' => $diffs
        ]);
    }

    public function store(Request $request, Media $media)
    {
        $request->validate([
            'changes' => 'required|string',
            'comment' => 'nullable|string|max:500',
            'tags' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:100',
            'parent_version_id' => 'nullable|exists:media_versions,id'
        ]);

        $newVersionNumber = $media->version_count + 1;

        $tags = $request->input('tags') 
            ? array_map('trim', explode(',', $request->input('tags')))
            : [];

        $versionData = [
            'media_id' => $media->id,
            'user_id' => auth()->id(),
            'version_number' => $newVersionNumber,
            'filename' => $media->filename,
            'path' => $media->path,
            'metadata' => $media->metadata,
            'changes' => $request->input('changes'),
            'comment' => $request->input('comment'),
            'tags' => $tags,
            'created_by' => auth()->id()
        ];

        if ($request->branch_name) {
            $versionData['branch_name'] = $request->branch_name;
            $versionData['parent_version_id'] = $request->parent_version_id;
        }

        $version = MediaVersion::create($versionData);
        $media->increment('version_count');

        return redirect()->route('media.versions.index', $media)
            ->with('success', "Version $newVersionNumber created successfully");
    }

    public function createBranch(Media $media)
    {
        return view('media.versions.create-branch', [
            'media' => $media,
            'versions' => $media->versions()->orderBy('version_number', 'desc')->get()
        ]);
    }

    public function storeBranch(Request $request, Media $media)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:media_branches,name,NULL,id,media_id,'.$media->id,
            'description' => 'nullable|string|max:255',
            'base_version_id' => 'required|exists:media_versions,id',
            'is_default' => 'nullable|boolean'
        ]);

        $branch = MediaBranch::create([
            'media_id' => $media->id,
            'name' => $request->name,
            'description' => $request->description,
            'base_version_id' => $request->base_version_id,
            'is_default' => $request->boolean('is_default')
        ]);

        return redirect()->route('media.versions.index', $media)
            ->with('success', "Branch {$branch->name} created successfully");
    }

    public function merge(Request $request, Media $media, MediaVersion $version)
    {
        $request->validate([
            'target_branch' => 'required|string|exists:media_branches,name'
        ]);

        if ($version->mergeIntoBranch($request->target_branch)) {
            return redirect()->route('media.versions.show', [$media, $version])
                ->with('success', "Version merged into {$request->target_branch} successfully");
        }

        return back()->with('error', 'Failed to merge version');
    }
}
