<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\ContentRollback;
use App\Services\ContentVersionAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContentVersionRestorationController extends Controller
{
    protected ContentVersionAnalyticsService $analyticsService;

    public function __construct(ContentVersionAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function apiCompare(Request $request, $baseId, $compareId)
    {
        try {
            $baseVersion = ContentVersion::findOrFail($baseId);
            $compareVersion = ContentVersion::findOrFail($compareId);

            $stats = $this->analyticsService->getComparisonStats($baseVersion, $compareVersion);
            
            return response()->json([
                'success' => true,
                'base_content' => $baseVersion->content_data,
                'compare_content' => $compareVersion->content_data,
                'differences' => $this->calculateDifferences($baseVersion->content_data, $compareVersion->content_data),
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error("API version comparison failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Comparison failed'], 500);
        }
    }

    public function compare(Request $request, $contentId, $versionId)
    {
        try {
            $version = ContentVersion::findOrFail($versionId);
            $content = Content::findOrFail($contentId);

            $stats = $this->analyticsService->getComparisonStats($content->currentVersion, $version);
            
            return response()->json([
                'success' => true,
                'current_content' => $content->content_data,
                'version_content' => $version->content_data,
                'differences' => $this->calculateDifferences($content->content_data, $version->content_data),
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error("Version comparison failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Comparison failed'], 500);
        }
    }

    public function prepareRestore(Request $request, $contentId, $versionId)
    {
        try {
            $version = ContentVersion::findOrFail($versionId);
            $content = Content::findOrFail($contentId);

            $rollback = ContentRollback::create([
                'content_id' => $content->id,
                'version_id' => $version->id,
                'user_id' => auth()->id(),
                'version_data_before' => $content->content_data,
                'version_data_after' => $version->content_data,
                'comparison_data' => $this->calculateDifferences($content->content_data, $version->content_data),
                'reason' => $request->input('reason')
            ]);

            return response()->json([
                'success' => true,
                'rollback_id' => $rollback->id,
                'comparison' => $rollback->comparison_data
            ]);
        } catch (\Exception $e) {
            Log::error("Rollback preparation failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Preparation failed'], 500);
        }
    }

    public function confirmRestore(Request $request, $contentId, $versionId)
    {
        try {
            DB::transaction(function () use ($request, $contentId, $versionId) {
                $version = ContentVersion::findOrFail($versionId);
                $content = Content::findOrFail($contentId);
                $rollback = ContentRollback::findOrFail($request->input('rollback_id'));

                // Create new version of current content
                $currentVersion = ContentVersion::create([
                    'content_id' => $content->id,
                    'version_number' => $content->versions()->max('version_number') + 1,
                    'content_data' => $content->content_data,
                    'created_by' => auth()->id(),
                    'is_autosave' => false
                ]);

                // Restore the selected version
                $content->update([
                    'content_data' => $version->content_data,
                    'current_version_id' => $version->id,
                    'updated_at' => now()
                ]);

                // Track restoration in version
                $version->update([
                    'restored_at' => now(),
                    'restored_by' => auth()->id()
                ]);

                // Complete the rollback record
                $rollback->update([
                    'confirmed' => true,
                    'confirmed_at' => now()
                ]);

                Log::info("Content {$contentId} restored to version {$versionId} by user " . auth()->id());
            });

            return response()->json([
                'success' => true,
                'message' => 'Version restored successfully'
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to restore content version: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Restoration failed'], 500);
        }
    }

    private function calculateDifferences($current, $version)
    {
        // Simple diff implementation - can be enhanced with more sophisticated diffing
        $currentArray = (array) $current;
        $versionArray = (array) $version;

        $differences = [];
        foreach ($currentArray as $key => $value) {
            if (!isset($versionArray[$key])) {
                $differences[$key] = ['action' => 'remove', 'old' => $value];
            } elseif ($versionArray[$key] !== $value) {
                $differences[$key] = ['action' => 'change', 'old' => $value, 'new' => $versionArray[$key]];
            }
        }

        foreach ($versionArray as $key => $value) {
            if (!isset($currentArray[$key])) {
                $differences[$key] = ['action' => 'add', 'new' => $value];
            }
        }

        return $differences;
    }
    public function show(Content $content, ContentVersion $version)
    {
        try {
            $currentVersion = $content->currentVersion;
            $stats = $this->analyticsService->getComparisonStats($currentVersion, $version);
            
            return view('content.versions.restore', [
                'content' => $content,
                'version' => $version,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to fetch version: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Version not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $version = ContentVersion::findOrFail($id);
            $version->update($request->only(['content_data', 'notes']));
            
            return response()->json([
                'success' => true,
                'message' => 'Version updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update version: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Update failed'], 500);
        }
    }

    public function analytics(Content $content, ContentVersion $version)
    {
        try {
            $currentVersion = $content->currentVersion;
            $stats = $this->analyticsService->getComparisonStats($currentVersion, $version);
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to get version analytics: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Analytics failed'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $version = ContentVersion::findOrFail($id);
            $version->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Version deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to delete version: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Deletion failed'], 500);
        }
    }
}