<?php

namespace App\Http\Controllers;

use App\Models\ContentVersion;
use App\Jobs\ContentVersionComparisonJob;
use Illuminate\Http\Request;

class VersionRestorationController extends Controller
{
    public function show(ContentVersion $version)
    {
        $currentVersion = $version->content->currentVersion;
        $comparison = new ContentVersionComparisonJob($currentVersion, $version);
        $diff = $comparison->handle();

        return view('versions.restore', [
            'currentVersion' => $currentVersion,
            'targetVersion' => $version,
            'diff' => $diff
        ]);
    }

    public function restore(Request $request, ContentVersion $version)
    {
        $request->validate([
            'confirm' => 'required|accepted'
        ]);

        $version->content->update([
            'current_version_id' => $version->id
        ]);

        return redirect()
            ->route('contents.show', $version->content)
            ->with('success', 'Version restored successfully');
    }
}