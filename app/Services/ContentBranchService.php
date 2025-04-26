<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentBranch;
use App\Models\ContentVersion;
use App\Models\ContentVersionDiff;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContentBranchService
{
    public function createBranch(Content $content, User $user, string $branchName, ?ContentVersion $baseVersion = null): ContentBranch
    {
        $baseVersion = $baseVersion ?? $content->currentVersion;

        return DB::transaction(function () use ($content, $user, $branchName, $baseVersion) {
            $branch = ContentBranch::create([
                'content_id' => $content->id,
                'name' => $branchName,
                'base_version_id' => $baseVersion->id,
                'created_by' => $user->id,
                'is_active' => true
            ]);

            // Create initial version from base
            $this->createVersionFromBranch($branch, $user, 'Initial branch version');

            return $branch;
        });
    }

    public function createVersionFromBranch(ContentBranch $branch, User $user, string $message): ContentVersion
    {
        return DB::transaction(function () use ($branch, $user, $message) {
            $latestVersion = $branch->versions()->latest()->first();
            $baseContent = $latestVersion ? $latestVersion->content : $branch->baseVersion->content;

            $version = ContentVersion::create([
                'content_id' => $branch->content_id,
                'content_branch_id' => $branch->id,
                'version_number' => $this->getNextVersionNumber($branch),
                'content' => $baseContent,
                'created_by' => $user->id,
                'change_description' => $message
            ]);

            if ($latestVersion) {
                $this->createDiff($latestVersion, $version);
            }

            return $version;
        });
    }

    protected function getNextVersionNumber(ContentBranch $branch): int
    {
        $latest = $branch->versions()->latest()->first();
        return $latest ? $latest->version_number + 1 : 1;
    }

    protected function createDiff(ContentVersion $fromVersion, ContentVersion $toVersion): void
    {
        $diff = $this->calculateDiff($fromVersion->content, $toVersion->content);

        ContentVersionDiff::create([
            'from_version_id' => $fromVersion->id,
            'to_version_id' => $toVersion->id,
            'diff_content' => $diff,
            'change_percentage' => $this->calculateChangePercentage($fromVersion->content, $toVersion->content)
        ]);
    }

    protected function calculateDiff(string $oldContent, string $newContent): string
    {
        // Implement diff algorithm (simplified for example)
        $oldLines = explode("\n", $oldContent);
        $newLines = explode("\n", $newContent);

        $diff = [];
        foreach ($newLines as $i => $line) {
            if (!isset($oldLines[$i])) {
                $diff[] = ['type' => 'add', 'line' => $line];
            } elseif ($line !== $oldLines[$i]) {
                $diff[] = ['type' => 'modify', 'old' => $oldLines[$i], 'new' => $line];
            }
        }

        return json_encode($diff);
    }

    protected function calculateChangePercentage(string $oldContent, string $newContent): float
    {
        similar_text($oldContent, $newContent, $percentage);
        return 100 - $percentage;
    }

    public function mergeBranch(ContentBranch $branch, User $user, string $message, bool $deleteAfterMerge = true): ContentVersion
    {
        return DB::transaction(function () use ($branch, $user, $message, $deleteAfterMerge) {
            $latestBranchVersion = $branch->versions()->latest()->firstOrFail();
            $mainContent = $branch->content;

            // Create new version on main branch
            $mergedVersion = ContentVersion::create([
                'content_id' => $mainContent->id,
                'version_number' => $this->getNextMainVersionNumber($mainContent),
                'content' => $latestBranchVersion->content,
                'created_by' => $user->id,
                'change_description' => $message,
                'merged_from_branch_id' => $branch->id
            ]);

            // Create diff from current version
            $this->createDiff($mainContent->currentVersion, $mergedVersion);

            // Update content to point to new version
            $mainContent->update([
                'current_version_id' => $mergedVersion->id
            ]);

            if ($deleteAfterMerge) {
                $branch->update(['is_active' => false]);
            }

            return $mergedVersion;
        });
    }

    protected function getNextMainVersionNumber(Content $content): int
    {
        $latest = $content->versions()->latest()->first();
        return $latest ? $latest->version_number + 1 : 1;
    }

    public function rebaseBranch(ContentBranch $branch, User $user, string $message): ContentVersion
    {
        return DB::transaction(function () use ($branch, $user, $message) {
            $latestMainVersion = $branch->content->currentVersion;
            $latestBranchVersion = $branch->versions()->latest()->firstOrFail();

            // Create new version with latest main content as base
            $rebaseVersion = ContentVersion::create([
                'content_id' => $branch->content_id,
                'content_branch_id' => $branch->id,
                'version_number' => $this->getNextVersionNumber($branch),
                'content' => $latestMainVersion->content,
                'created_by' => $user->id,
                'change_description' => 'Rebase from main'
            ]);

            // Apply branch changes on top
            $mergedContent = $this->mergeContent($latestMainVersion->content, $latestBranchVersion->content);
            
            $newVersion = ContentVersion::create([
                'content_id' => $branch->content_id,
                'content_branch_id' => $branch->id,
                'version_number' => $this->getNextVersionNumber($branch),
                'content' => $mergedContent,
                'created_by' => $user->id,
                'change_description' => $message
            ]);

            // Update branch base version
            $branch->update([
                'base_version_id' => $latestMainVersion->id
            ]);

            // Create diffs
            $this->createDiff($latestBranchVersion, $rebaseVersion);
            $this->createDiff($rebaseVersion, $newVersion);

            return $newVersion;
        });
    }

    protected function mergeContent(string $baseContent, string $branchContent): string
    {
        // Simplified merge - in real implementation would need conflict resolution
        return $branchContent;
    }

    public function getBranchConflicts(ContentBranch $branch): array
    {
        $latestMainVersion = $branch->content->currentVersion;
        $latestBranchVersion = $branch->versions()->latest()->firstOrFail();

        return $this->findConflicts($latestMainVersion->content, $latestBranchVersion->content);
    }

    protected function findConflicts(string $mainContent, string $branchContent): array
    {
        $mainLines = explode("\n", $mainContent);
        $branchLines = explode("\n", $branchContent);
        $conflicts = [];

        $maxLines = max(count($mainLines), count($branchLines));
        for ($i = 0; $i < $maxLines; $i++) {
            $mainLine = $mainLines[$i] ?? null;
            $branchLine = $branchLines[$i] ?? null;

            if ($mainLine !== $branchLine && $mainLine !== null && $branchLine !== null) {
                $conflicts[] = [
                    'line_number' => $i + 1,
                    'main' => $mainLine,
                    'branch' => $branchLine
                ];
            }
        }

        return $conflicts;
    }

    public function resolveConflict(ContentBranch $branch, array $resolutions): ContentVersion
    {
        return DB::transaction(function () use ($branch, $resolutions) {
            $latestMainVersion = $branch->content->currentVersion;
            $latestBranchVersion = $branch->versions()->latest()->firstOrFail();

            $mainLines = explode("\n", $latestMainVersion->content);
            $branchLines = explode("\n", $latestBranchVersion->content);
            $resolvedLines = [];

            $maxLines = max(count($mainLines), count($branchLines));
            for ($i = 0; $i < $maxLines; $i++) {
                $resolution = $resolutions[$i] ?? null;

                if ($resolution === 'main') {
                    $resolvedLines[] = $mainLines[$i] ?? '';
                } elseif ($resolution === 'branch') {
                    $resolvedLines[] = $branchLines[$i] ?? '';
                } else {
                    // No conflict or custom resolution
                    $resolvedLines[] = $branchLines[$i] ?? $mainLines[$i] ?? '';
                }
            }

            $resolvedContent = implode("\n", $resolvedLines);

            return ContentVersion::create([
                'content_id' => $branch->content_id,
                'content_branch_id' => $branch->id,
                'version_number' => $this->getNextVersionNumber($branch),
                'content' => $resolvedContent,
                'created_by' => $branch->created_by,
                'change_description' => 'Conflict resolution'
            ]);
        });
    }

    public function getBranchComparison(ContentBranch $branch): array
    {
        $latestMainVersion = $branch->content->currentVersion;
        $latestBranchVersion = $branch->versions()->latest()->firstOrFail();

        return [
            'main_version' => $latestMainVersion,
            'branch_version' => $latestBranchVersion,
            'diff' => $this->calculateDiff($latestMainVersion->content, $latestBranchVersion->content),
            'change_percentage' => $this->calculateChangePercentage(
                $latestMainVersion->content,
                $latestBranchVersion->content
            ),
            'conflicts' => $this->getBranchConflicts($branch)
        ];
    }

    public function getBranchHistory(ContentBranch $branch): array
    {
        return [
            'branch' => $branch,
            'versions' => $branch->versions()->with(['creator'])->orderBy('version_number')->get(),
            'diffs' => ContentVersionDiff::whereIn('to_version_id', $branch->versions()->pluck('id'))
                ->with(['fromVersion', 'toVersion'])
                ->get()
        ];
    }

    public function archiveBranch(ContentBranch $branch): void
    {
        $branch->update(['is_active' => false]);
    }

    public function restoreBranch(ContentBranch $branch): void
    {
        $branch->update(['is_active' => true]);
    }

    public function getActiveBranches(Content $content)
    {
        return $content->branches()
            ->where('is_active', true)
            ->with(['createdBy', 'baseVersion'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getInactiveBranches(Content $content)
    {
        return $content->branches()
            ->where('is_active', false)
            ->with(['createdBy', 'baseVersion'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getBranchStats(ContentBranch $branch): array
    {
        return [
            'version_count' => $branch->versions()->count(),
            'change_percentage' => $this->getTotalChangePercentage($branch),
            'active_days' => now()->diffInDays($branch->created_at),
            'contributors' => $branch->versions()
                ->select('created_by', DB::raw('count(*) as version_count'))
                ->groupBy('created_by')
                ->with(['creator'])
                ->orderBy('version_count', 'desc')
                ->get()
        ];
    }

    protected function getTotalChangePercentage(ContentBranch $branch): float
    {
        $baseContent = $branch->baseVersion->content;
        $latestContent = $branch->versions()->latest()->first()->content;

        return $this->calculateChangePercentage($baseContent, $latestContent);
    }
}