<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentBranch;
use App\Models\ContentVersion;
use App\Models\ContentVersionDiff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VersionBranchingService
{
    public function createBranch(Content $content, string $name, string $description = null): ContentBranch
    {
        return DB::transaction(function () use ($content, $name, $description) {
            $branch = new ContentBranch([
                'content_id' => $content->id,
                'name' => $name,
                'description' => $description,
                'is_default' => false,
            ]);
            $branch->save();

            // Create initial version from current content
            $this->createVersionFromContent($content, $branch);

            return $branch;
        });
    }

    public function createVersionFromContent(Content $content, ContentBranch $branch): ContentVersion
    {
        return DB::transaction(function () use ($content, $branch) {
            $latestVersion = $branch->versions()->latest()->first();
            $versionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

            $version = new ContentVersion([
                'content_id' => $content->id,
                'branch_id' => $branch->id,
                'version_number' => $versionNumber,
                'user_id' => Auth::id(),
                'title' => $content->title,
                'content' => $content->content,
                'status' => 'draft',
            ]);
            $version->save();

            if ($latestVersion) {
                $this->createDiff($latestVersion, $version);
            }

            return $version;
        });
    }

    public function createDiff(ContentVersion $fromVersion, ContentVersion $toVersion): ContentVersionDiff
    {
        $diff = new ContentVersionDiff([
            'from_version_id' => $fromVersion->id,
            'to_version_id' => $toVersion->id,
            'changes' => $this->calculateDiff($fromVersion->content, $toVersion->content),
        ]);
        $diff->save();

        return $diff;
    }

    public function mergeVersions(ContentVersion $sourceVersion, ContentBranch $targetBranch): ContentVersion
    {
        return DB::transaction(function () use ($sourceVersion, $targetBranch) {
            $targetContent = $targetBranch->content;
            $targetContent->content = $sourceVersion->content;
            $targetContent->save();

            return $this->createVersionFromContent($targetContent, $targetBranch);
        });
    }

    public function setDefaultBranch(ContentBranch $branch): void
    {
        DB::transaction(function () use ($branch) {
            // Remove default flag from all other branches
            ContentBranch::where('content_id', $branch->content_id)
                ->where('id', '!=', $branch->id)
                ->update(['is_default' => false]);

            // Set this branch as default
            $branch->is_default = true;
            $branch->save();
        });
    }

    protected function calculateDiff(string $oldContent, string $newContent): array
    {
        // Implement diff algorithm (simplified example)
        $oldLines = explode("\n", $oldContent);
        $newLines = explode("\n", $newContent);

        $changes = [];
        $maxLines = max(count($oldLines), count($newLines));

        for ($i = 0; $i < $maxLines; $i++) {
            $oldLine = $oldLines[$i] ?? null;
            $newLine = $newLines[$i] ?? null;

            if ($oldLine !== $newLine) {
                $changes[] = [
                    'line' => $i + 1,
                    'old' => $oldLine,
                    'new' => $newLine,
                    'type' => $oldLine === null ? 'added' : ($newLine === null ? 'removed' : 'modified'),
                ];
            }
        }

        return $changes;
    }

    public function resolveConflict(ContentVersion $version, string $resolution): ContentVersion
    {
        return DB::transaction(function () use ($version, $resolution) {
            $content = $version->content;
            
            if ($resolution === 'accept_incoming') {
                $content->content = $version->content;
            } else {
                // For 'accept_current', we keep the current content
                $currentVersion = $content->currentVersion;
                $content->content = $currentVersion->content;
            }

            $content->save();
            return $this->createVersionFromContent($content, $version->branch);
        });
    }

    public function restoreVersion(ContentVersion $version): void
    {
        DB::transaction(function () use ($version) {
            $content = $version->content;
            $content->content = $version->content;
            $content->save();

            // Increment restore count
            $version->increment('restore_count');
            $content->increment('restore_count');
        });
    }

    public function getVersionDiffs(ContentVersion $version1, ContentVersion $version2): array
    {
        $diff = ContentVersionDiff::where(function($query) use ($version1, $version2) {
                $query->where('from_version_id', $version1->id)
                    ->where('to_version_id', $version2->id);
            })
            ->orWhere(function($query) use ($version1, $version2) {
                $query->where('from_version_id', $version2->id)
                    ->where('to_version_id', $version1->id);
            })
            ->first();

        if (!$diff) {
            $diff = $this->createDiff($version1, $version2);
        }

        return [
            'changes' => $diff->changes,
            'created_at' => $diff->created_at,
            'from_version' => $version1->version_number,
            'to_version' => $version2->version_number
        ];
    }
}
