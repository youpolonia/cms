<?php

namespace App\Repositories;

use App\Models\ContentVersion;
use Illuminate\Database\Eloquent\Builder;

class ContentVersionRepository implements ContentVersionRepositoryInterface
{
    public function findWithViews($id)
    {
        return ContentVersion::withCount('views')
            ->with(['content' => function($query) {
                $query->select('id', 'content');
            }])
            ->findOrFail($id);
    }

    public function getContent($versionId)
    {
        return ContentVersion::where('id', $versionId)
            ->value('content');
    }

    public function getRelatedVersions($contentId, $excludeId = null)
    {
        $query = ContentVersion::where('content_id', $contentId)
            ->orderBy('created_at', 'desc');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get(['id', 'version_name', 'created_at']);
    }

    public function getVersionTimeline($contentId)
    {
        return ContentVersion::where('content_id', $contentId)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'version_name', 'created_at', 'is_published']);
    }

    public function createBranch(string $contentId, string $branchName, string $sourceVersion) 
    {
        return \DB::transaction(function() use ($contentId, $branchName, $sourceVersion) {
            $baseVersion = ContentVersion::findOrFail($sourceVersion);
            
            $branch = new ContentVersion([
                'content_id' => $contentId,
                'version_name' => $branchName,
                'is_branch' => true,
                'base_version_id' => $baseVersion->id,
                'content' => $baseVersion->content
            ]);
            
            $branch->save();
            
            return $branch;
        });
    }
    
    public function getAllBranches(string $contentId)
    {
        return ContentVersion::where('content_id', $contentId)
            ->where('is_branch', true)
            ->with(['baseVersion' => function($query) {
                $query->select('id', 'version_name', 'created_at');
            }])
            ->orderBy('created_at', 'desc')
            ->get(['id', 'version_name', 'created_at', 'is_published']);
    }
    
    public function mergeBranch(
        string $contentId, 
        string $branchId,
        string $targetBranchId,
        string $strategy = 'merge'
    ) {
        return \DB::transaction(function() use ($contentId, $branchId, $targetBranchId, $strategy) {
            $branch = ContentVersion::where('content_id', $contentId)
                ->where('id', $branchId)
                ->where('is_branch', true)
                ->firstOrFail();
                
            $target = ContentVersion::where('content_id', $contentId)
                ->where('id', $targetBranchId)
                ->firstOrFail();
                
            // TODO: Implement merge strategy logic
            $merged = $branch;
            $merged->is_published = false;
            $merged->is_merged = true;
            $merged->save();
            
            return $merged;
        });
    }
}