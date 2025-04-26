<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Events\ContentVersionRestored;
use Illuminate\Http\Request;

class ContentVersionController extends Controller
{
    public function restoreVersion(Request $request, Content $content, ContentVersion $version)
    {
        $this->authorize('restore', $version);

        $request->validate([
            'reason' => 'required|min:10|max:500',
            'create_new_version' => 'sometimes|boolean'
        ]);

        try {
            // Create backup version if requested
            if ($request->boolean('create_new_version', true)) {
                $currentVersion = $content->versions()->create([
                    'body' => $content->body,
                    'version_notes' => 'Auto-created backup before restoration',
                    'user_id' => auth()->id(),
                    'is_autosave' => false
                ]);
            }

            // Restore the content
            $content->update([
                'body' => $version->body,
                'version_notes' => "Restored from version {$version->version_number}. Reason: {$request->reason}",
                'restored_version_id' => $version->id,
                'restored_at' => now(),
                'restored_by' => auth()->id(),
                'restoration_reason' => $request->reason
            ]);

            // Record the restoration
            event(new ContentVersionRestored(
                $content->id,
                $version->id,
                auth()->id(),
                $request->reason
            ));

            return redirect()->route('content.show', $content)
                ->with('success', 'Version successfully restored');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to restore version: ' . $e->getMessage())
                ->withInput();
        }
    }
}
