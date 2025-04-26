<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentBranch;
use App\Models\ContentVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ContentCollaborationService
{
    public function createBranch(Content $content, User $user, string $branchName, ?string $sourceVersion = null): ContentBranch
    {
        return DB::transaction(function () use ($content, $user, $branchName, $sourceVersion) {
            $branch = ContentBranch::create([
                'content_id' => $content->id,
                'name' => $branchName,
                'created_by' => $user->id,
                'base_version_id' => $sourceVersion ?: $content->current_version_id
            ]);

            // Create initial version on the branch
            $baseVersion = $sourceVersion 
                ? ContentVersion::find($sourceVersion)
                : $content->currentVersion;

            $branchVersion = ContentVersion::create([
                'content_id' => $content->id,
                'branch_id' => $branch->id,
                'version_number' => 1,
                'created_by' => $user->id,
                'data' => $baseVersion->data,
                'is_autosave' => false
            ]);

            $branch->update(['current_version_id' => $branchVersion->id]);

            return $branch;
        });
    }

    public function createVersionOnBranch(
        ContentBranch $branch,
        User $user,
        array $data,
        bool $isAutosave = false
    ): ContentVersion {
        return DB::transaction(function () use ($branch, $user, $data, $isAutosave) {
            $latestVersion = $branch->currentVersion;

            $newVersion = ContentVersion::create([
                'content_id' => $branch->content_id,
                'branch_id' => $branch->id,
                'version_number' => $latestVersion ? $latestVersion->version_number + 1 : 1,
                'created_by' => $user->id,
                'data' => $data,
                'is_autosave' => $isAutosave,
                'parent_version_id' => $latestVersion?->id
            ]);

            if (!$isAutosave) {
                $branch->update(['current_version_id' => $newVersion->id]);
            }

            return $newVersion;
        });
    }

    public function mergeBranch(
        ContentBranch $branch,
        User $user,
        string $mergeStrategy = 'smart',
        ?array $customResolution = null
    ): ContentVersion {
        return DB::transaction(function () use ($branch, $user, $mergeStrategy, $customResolution) {
            $content = $branch->content;
            $mainBranch = $content->mainBranch();
            $mainVersion = $mainBranch->currentVersion;
            $branchVersion = $branch->currentVersion;

            // Create merge version
            $mergedData = $this->resolveMergeConflicts(
                $mainVersion->data,
                $branchVersion->data,
                $mergeStrategy,
                $customResolution
            );

            $mergeVersion = ContentVersion::create([
                'content_id' => $content->id,
                'branch_id' => $mainBranch->id,
                'version_number' => $mainVersion->version_number + 1,
                'created_by' => $user->id,
                'data' => $mergedData,
                'is_autosave' => false,
                'parent_version_id' => $mainVersion->id,
                'merged_from_branch_id' => $branch->id,
                'merged_version_id' => $branchVersion->id
            ]);

            // Update main branch
            $mainBranch->update(['current_version_id' => $mergeVersion->id]);

            // Mark source branch as merged
            $branch->update([
                'status' => 'merged',
                'merged_at' => now(),
                'merged_by' => $user->id,
                'merged_into_version_id' => $mergeVersion->id
            ]);

            return $mergeVersion;
        });
    }

    protected function resolveMergeConflicts(
        array $mainData,
        array $branchData,
        string $strategy,
        ?array $customResolution
    ): array {
        switch ($strategy) {
            case 'theirs':
                return $branchData;
            case 'ours':
                return $mainData;
            case 'custom':
                return $customResolution ?? $mainData;
            case 'smart':
            default:
                return $this->smartMerge($mainData, $branchData);
        }
    }

    protected function smartMerge(array $mainData, array $branchData): array
    {
        $merged = $mainData;

        // Merge simple fields (non-array values)
        foreach ($branchData as $key => $value) {
            if (!is_array($value)) {
                if (!isset($merged[$key]) || $merged[$key] === $mainData[$key]) {
                    $merged[$key] = $value;
                }
            }
        }

        // Merge complex fields (arrays)
        foreach ($branchData as $key => $value) {
            if (is_array($value)) {
                if (!isset($merged[$key])) {
                    $merged[$key] = $value;
                } else {
                    $merged[$key] = array_merge($merged[$key], $value);
                }
            }
        }

        return $merged;
    }

    public function getActiveCollaborators(Content $content): Collection
    {
        return User::whereHas('contentVersions', function($query) use ($content) {
            $query->where('content_id', $content->id)
                ->where('created_at', '>', now()->subHours(2));
        })
        ->with(['roles', 'permissions'])
        ->get();
    }

    public function getContentBranches(Content $content, ?string $status = null): Collection
    {
        $query = $content->branches()
            ->with(['createdBy', 'currentVersion', 'mergedBy']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')
            ->get();
    }

    public function getBranchHistory(ContentBranch $branch): Collection
    {
        return $branch->versions()
            ->with(['createdBy'])
            ->orderBy('version_number', 'desc')
            ->get();
    }

    public function getContentVersionDiffs(
        ContentVersion $version1,
        ContentVersion $version2
    ): array {
        $differ = new \Diff\Differ\Differ();
        $diff = [];

        foreach ($version1->data as $key => $value) {
            if (!isset($version2->data[$key])) {
                $diff[$key] = [
                    'type' => 'removed',
                    'old' => $value,
                    'new' => null
                ];
                continue;
            }

            if ($version1->data[$key] !== $version2->data[$key]) {
                if (is_array($value)) {
                    $diff[$key] = [
                        'type' => 'array_diff',
                        'diff' => $this->arrayRecursiveDiff($version1->data[$key], $version2->data[$key])
                    ];
                } else {
                    $diff[$key] = [
                        'type' => 'changed',
                        'old' => $version1->data[$key],
                        'new' => $version2->data[$key]
                    ];
                }
            }
        }

        // Check for new keys in version2
        foreach ($version2->data as $key => $value) {
            if (!isset($version1->data[$key])) {
                $diff[$key] = [
                    'type' => 'added',
                    'old' => null,
                    'new' => $value
                ];
            }
        }

        return $diff;
    }

    protected function arrayRecursiveDiff(array $a1, array $a2): array
    {
        $diff = [];
        foreach ($a1 as $key => $value) {
            if (array_key_exists($key, $a2)) {
                if (is_array($value)) {
                    $recursiveDiff = $this->arrayRecursiveDiff($value, $a2[$key]);
                    if (count($recursiveDiff)) {
                        $diff[$key] = $recursiveDiff;
                    }
                } else {
                    if ($value != $a2[$key]) {
                        $diff[$key] = [
                            'old' => $value,
                            'new' => $a2[$key]
                        ];
                    }
                }
            } else {
                $diff[$key] = [
                    'old' => $value,
                    'new' => null
                ];
            }
        }
        return $diff;
    }

    public function addCommentToVersion(
        ContentVersion $version,
        User $user,
        string $comment,
        ?string $field = null,
        ?int $line = null
    ): Comment {
        return $version->comments()->create([
            'user_id' => $user->id,
            'comment' => $comment,
            'field' => $field,
            'line' => $line,
            'resolved' => false
        ]);
    }

    public function resolveComment(Comment $comment, User $resolvedBy): Comment
    {
        return $comment->update([
            'resolved' => true,
            'resolved_by' => $resolvedBy->id,
            'resolved_at' => now()
        ]);
    }

    public function getVersionComments(
        ContentVersion $version,
        ?bool $resolved = null
    ): Collection {
        $query = $version->comments()
            ->with(['user', 'resolvedBy']);

        if ($resolved !== null) {
            $query->where('resolved', $resolved);
        }

        return $query->orderBy('created_at', 'desc')
            ->get();
    }

    public function getUnresolvedCommentsCount(Content $content): array
    {
        return [
            'total' => Comment::whereHas('version', function($q) use ($content) {
                $q->where('content_id', $content->id);
            })
            ->where('resolved', false)
            ->count(),
            'by_field' => Comment::whereHas('version', function($q) use ($content) {
                $q->where('content_id', $content->id);
            })
            ->where('resolved', false)
            ->select('field', DB::raw('count(*) as count'))
            ->groupBy('field')
            ->get()
            ->pluck('count', 'field')
        ];
    }

    public function lockContentForEditing(
        Content $content,
        User $user,
        ?string $section = null
    ): ContentLock {
        // Release any existing locks by this user
        ContentLock::where('content_id', $content->id)
            ->where('user_id', $user->id)
            ->delete();

        return ContentLock::create([
            'content_id' => $content->id,
            'user_id' => $user->id,
            'section' => $section,
            'locked_at' => now(),
            'expires_at' => now()->addMinutes(30)
        ]);
    }

    public function extendContentLock(ContentLock $lock): ContentLock
    {
        return $lock->update([
            'expires_at' => now()->addMinutes(30)
        ]);
    }

    public function releaseContentLock(ContentLock $lock): bool
    {
        return $lock->delete();
    }

    public function checkContentLock(
        Content $content,
        ?string $section = null
    ): ?ContentLock {
        return ContentLock::where('content_id', $content->id)
            ->where(function($q) use ($section) {
                if ($section) {
                    $q->where('section', $section)
                        ->orWhereNull('section');
                }
            })
            ->where('expires_at', '>', now())
            ->first();
    }

    public function getContentLocks(Content $content): Collection
    {
        return ContentLock::where('content_id', $content->id)
            ->where('expires_at', '>', now())
            ->with(['user'])
            ->get();
    }

    public function cleanupExpiredLocks(): int
    {
        return ContentLock::where('expires_at', '<=', now())
            ->delete();
    }

    public function trackCollaboratorActivity(
        Content $content,
        User $user,
        string $activityType,
        ?array $metadata = null
    ): CollaborationActivity {
        return CollaborationActivity::create([
            'content_id' => $content->id,
            'user_id' => $user->id,
            'activity_type' => $activityType,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    public function getCollaborationHistory(
        Content $content,
        ?string $activityType = null,
        ?int $limit = null
    ): Collection {
        $query = CollaborationActivity::where('content_id', $content->id)
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        if ($activityType) {
            $query->where('activity_type', $activityType);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function getCollaborationStats(Content $content): array
    {
        $activities = $this->getCollaborationHistory($content);

        return [
            'total_collaborators' => $activities->groupBy('user_id')->count(),
            'activity_by_type' => $activities->groupBy('activity_type')
                ->map->count(),
            'recent_activity' => $activities->take(5),
            'most_active_collaborators' => $activities->groupBy('user_id')
                ->map(function($items) {
                    return [
                        'user' => $items->first()->user,
                        'count' => $items->count(),
                        'last_activity' => $items->first()->created_at
                    ];
                })
                ->sortDesc()
                ->take(3)
                ->values()
        ];
    }

    public function getSuggestedCollaborators(
        Content $content,
        int $limit = 5
    ): Collection {
        // Get users who have collaborated on similar content
        return User::whereHas('collaborationActivities', function($q) use ($content) {
            $q->whereHas('content', function($q2) use ($content) {
                $q2->where('content_type', $content->content_type)
                    ->where('id', '!=', $content->id);
            });
        })
        ->whereNotIn('id', function($q) use ($content) {
            $q->select('user_id')
                ->from('collaboration_activities')
                ->where('content_id', $content->id);
        })
        ->withCount(['collaborationActivities as activity_count' => function($q) {
            $q->where('created_at', '>', now()->subMonth());
        }])
        ->orderBy('activity_count', 'desc')
        ->limit($limit)
        ->get();
    }

    public function notifyCollaborators(
        Content $content,
        string $message,
        array $exceptUsers = []
    ): void {
        $collaborators = $this->getActiveCollaborators($content)
            ->reject(function($user) use ($exceptUsers) {
                return in_array($user->id, $exceptUsers);
            });

        foreach ($collaborators as $user) {
            $user->notify(new CollaborationNotification($content, $message));
        }
    }
}