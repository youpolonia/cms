<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ContentVersion;
use App\Models\ContentVersionDiff;
use App\Services\VersionComparisonService;
use Illuminate\Support\Facades\Log;

class ContentVersionComparison extends Component
{
    public $contentId;
    public $version1;
    public $version2;
    public $diffs = [];
    public $version1Content;
    public $version2Content;
    public $showSideBySide = false;
    public $confirmingRestore = false;
    public $versionToRestore;

    public function mount($contentId)
    {
        $this->contentId = $contentId;
    }

    public $isLoading = false;
    public $loadedChunks = 0;
    public $totalChunks = 0;
    public $chunkSize = 100; // Number of diffs to process per chunk

    public $semanticDiff = [];
    public $changeSummary = '';
    public $conflicts = [];
    public $showSemanticView = false;

    public function compareVersions(VersionComparisonService $comparisonService)
    {
        $this->validate([
            'version1' => 'required|exists:content_versions,id',
            'version2' => 'required|exists:content_versions,id'
        ]);

        $this->isLoading = true;
        $this->loadedChunks = 0;
        $this->diffs = collect();
        $this->semanticDiff = [];
        $this->changeSummary = '';
        $this->conflicts = [];
        
        $this->version1Content = ContentVersion::find($this->version1);
        $this->version2Content = ContentVersion::find($this->version2);

        // Track comparison frequency
        $comparisonService->trackComparisonFrequency($this->version1, $this->version2);

        // First check cache
        $cachedResult = $comparisonService->getCachedComparison($this->version1, $this->version2);

        // Get semantic diff from MCP knowledge server
        try {
            $this->semanticDiff = $comparisonService->getSemanticDiff(
                $this->version1Content->content,
                $this->version2Content->content
            );
            
            $this->changeSummary = $comparisonService->getChangeSummary(
                $this->version1Content->content,
                $this->version2Content->content
            );

            $this->conflicts = $comparisonService->detectConflicts(
                $this->version1Content->content,
                $this->version2Content->content
            );
            
            $this->dispatch('notify',
                type: 'info',
                message: 'Semantic analysis completed',
                duration: 2000
            );
        } catch (\Exception $e) {
            Log::error("MCP knowledge server error: " . $e->getMessage());
            $this->dispatch('notify',
                type: 'error',
                message: 'Advanced analysis unavailable - using basic diff',
                duration: 3000
            );
        }
        
        if ($cachedResult) {
            $this->diffs = collect($cachedResult['diffs']);
            $this->isLoading = false;
            Log::info("Loaded comparison from cache for versions {$this->version1} and {$this->version2}");
        } else {
            // Get total count for progress tracking
            $totalDiffs = ContentVersionDiff::where(function($query) {
                $query->where('from_version_id', $this->version1)
                      ->where('to_version_id', $this->version2);
            })->orWhere(function($query) {
                $query->where('from_version_id', $this->version2)
                      ->where('to_version_id', $this->version1);
            })->count();

            $this->totalChunks = ceil($totalDiffs / $this->chunkSize);

            // Process in chunks
            for ($i = 0; $i < $this->totalChunks; $i++) {
                $chunk = ContentVersionDiff::where(function($query) {
                        $query->where('from_version_id', $this->version1)
                              ->where('to_version_id', $this->version2);
                    })->orWhere(function($query) {
                        $query->where('from_version_id', $this->version2)
                              ->where('to_version_id', $this->version1);
                    })
                    ->offset($i * $this->chunkSize)
                    ->limit($this->chunkSize)
                    ->get();

                $this->diffs = $this->diffs->merge($chunk);
                $this->loadedChunks = $i + 1;
                
                // Small delay to allow UI updates
                usleep(100000); // 100ms
            }

            // Cache the result
            $comparisonService->cacheComparisonResult(
                $this->version1,
                $this->version2,
                ['diffs' => $this->diffs->toArray()]
            );
            
            $this->isLoading = false;
            Log::info("Performed and cached comparison for versions {$this->version1} and {$this->version2}");
        }

        // Store comparison metadata
        if (!$this->isLoading) {
            $comparisonService->storeComparison([
                'content_id' => $this->contentId,
                'from_version_id' => $this->version1,
                'to_version_id' => $this->version2,
                'diff_count' => $this->diffs->count(),
                'view_mode' => $this->showSideBySide ? 'side-by-side' : 'diff',
                'from_cache' => $cachedResult !== null
            ]);
        }

        $this->dispatch('notify',
            type: 'success',
            message: $cachedResult ? 'Versions loaded from cache' : 'Versions compared successfully',
            duration: 2000
        );
    }

    public function confirmRestore($versionId)
    {
        $this->confirmingRestore = true;
        $this->versionToRestore = $versionId;
    }

    public function cancelRestore()
    {
        $this->confirmingRestore = false;
        $this->versionToRestore = null;
    }

    public function restoreVersion(VersionComparisonService $comparisonService)
    {
        $version = ContentVersion::findOrFail($this->versionToRestore);
        $currentVersion = ContentVersion::where('content_id', $this->contentId)
            ->latest()
            ->first();

        // Create new version with restored content
        $newVersion = $currentVersion->replicate();
        $newVersion->content = $version->content;
        $newVersion->save();

        // Log restore operation to memory server
        $comparisonService->logRestore(
            $this->contentId,
            $this->versionToRestore,
            $newVersion->id
        );

        Log::info("Restored version {$this->versionToRestore} as new version {$newVersion->id} for content {$this->contentId}");
        $this->dispatch('version-restored');
        $this->confirmingRestore = false;
        $this->versionToRestore = null;
    }

    public function toggleViewMode()
    {
        $this->showSideBySide = !$this->showSideBySide;
        $this->dispatch('notify',
            type: 'info',
            message: $this->showSideBySide ? 'Switched to side-by-side view' : 'Switched to diff view',
            duration: 2000
        );
    }

    public $tagFilter1 = '';
    public $tagFilter2 = '';

    public function render()
    {
        $versionsQuery = ContentVersion::where('content_id', $this->contentId)
            ->orderBy('created_at', 'desc');

        $allTags = ContentVersion::where('content_id', $this->contentId)
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        $versions1Query = clone $versionsQuery;
        $versions2Query = clone $versionsQuery;

        if ($this->tagFilter1) {
            $versions1Query->whereJsonContains('tags', $this->tagFilter1);
        }
        if ($this->tagFilter2) {
            $versions2Query->whereJsonContains('tags', $this->tagFilter2);
        }

        $versions = $versionsQuery->get();
        $versions1 = $versions1Query->get();
        $versions2 = $versions2Query->get();

        return view('livewire.content-version-comparison', [
            'versions' => $versions,
            'versions1' => $versions1,
            'versions2' => $versions2,
            'allTags' => $allTags
        ]);
    }

    public function updatedTagFilter1($value)
    {
        $this->version1 = '';
    }

    public function updatedTagFilter2($value)
    {
        $this->version2 = '';
    }

    protected function getListeners()
    {
        return [
            'keydown.ctrl.1' => 'selectVersion1',
            'keydown.ctrl.2' => 'selectVersion2',
            'keydown.ctrl.c' => 'compareVersions',
            'keydown.ctrl.s' => 'toggleViewMode',
            'keydown.ctrl.t' => 'focusTagFilter',
        ];
    }

    public function selectVersion1()
    {
        if ($this->versions->isNotEmpty()) {
            $this->version1 = $this->versions->first()->id;
            $this->dispatch('notify',
                type: 'success', 
                message: 'First version selected',
                duration: 2000
            );
        }
    }

    public function selectVersion2()
    {
        if ($this->versions->isNotEmpty()) {
            $this->version2 = $this->versions->last()->id;
            $this->dispatch('notify',
                type: 'success',
                message: 'Last version selected',
                duration: 2000
            );
        }
    }

    public function addTag($versionId, $tagName)
    {
        $version = ContentVersion::findOrFail($versionId);
        $version->tags = array_unique(array_merge(
            $version->tags ?? [],
            [$tagName]
        ));
        $version->save();
    }

    public function removeTag($versionId, $tagName)
    {
        $version = ContentVersion::findOrFail($versionId);
        $version->tags = array_filter($version->tags ?? [], function($tag) use ($tagName) {
            return $tag !== $tagName;
        });
        $version->save();
    }

    public function focusTagFilter()
    {
        $this->dispatch('focus-tag-filter');
        $this->dispatch('notify', 
            type: 'info',
            message: 'Tag filter focused',
            duration: 2000
        );
    }

}
