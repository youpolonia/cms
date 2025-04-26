<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\ContentVersionDiff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContentVersioningService
{
    public function createVersion(Content $content, array $data, string $createdBy, bool $isAutosave = false): ContentVersion
    {
        return DB::transaction(function () use ($content, $data, $createdBy, $isAutosave) {
            // Get previous version
            $previousVersion = $content->versions()->latest()->first();

            // Create new version
            $version = ContentVersion::create([
                'content_id' => $content->id,
                'version_number' => $this->generateVersionNumber($content),
                'data' => $data,
                'created_by' => $createdBy,
                'is_autosave' => $isAutosave,
                'is_current' => !$isAutosave // Autosaves are never current versions
            ]);

            // If this is a manual save (not autosave), mark all other versions as not current
            if (!$isAutosave) {
                $content->versions()
                    ->where('id', '!=', $version->id)
                    ->update(['is_current' => false]);
            }

            // Create diff if previous version exists
            if ($previousVersion && !$isAutosave) {
                $this->createDiff($previousVersion, $version);
            }

            return $version;
        });
    }

    public function restoreVersion(ContentVersion $version): Content
    {
        return DB::transaction(function () use ($version) {
            // Update content with version data
            $content = $version->content;
            $content->fill($version->data);
            $content->save();

            // Create new version representing the restore
            $restoredVersion = $this->createVersion(
                $content,
                $version->data,
                auth()->id(),
                false
            );

            // Mark this as a rollback
            $restoredVersion->update([
                'is_rollback' => true,
                'rollback_from_version' => $version->version_number
            ]);

            return $content;
        });
    }

    public function compareVersions(ContentVersion $versionA, ContentVersion $versionB): array
    {
        if ($versionA->content_id !== $versionB->content_id) {
            throw new \InvalidArgumentException('Cannot compare versions from different content items');
        }

        $diff = $this->calculateDiff($versionA->data, $versionB->data);

        return [
            'changes' => $diff,
            'stats' => $this->calculateDiffStats($diff),
            'version_a' => $versionA->version_number,
            'version_b' => $versionB->version_number
        ];
    }

    public function getVersionTimeline(Content $content): array
    {
        return $content->versions()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($version) {
                return [
                    'id' => $version->id,
                    'version_number' => $version->version_number,
                    'created_at' => $version->created_at,
                    'created_by' => $version->creator->name,
                    'is_current' => $version->is_current,
                    'is_autosave' => $version->is_autosave,
                    'is_rollback' => $version->is_rollback,
                    'summary' => $this->generateVersionSummary($version)
                ];
            })
            ->toArray();
    }

    public function cleanupOldVersions(Content $content, int $keep = 10): int
    {
        $count = 0;

        $content->versions()
            ->where('is_current', false)
            ->orderBy('created_at', 'desc')
            ->skip($keep)
            ->take(PHP_INT_MAX)
            ->chunk(100, function ($versions) use (&$count) {
                $ids = $versions->pluck('id');
                ContentVersionDiff::whereIn('from_version_id', $ids)
                    ->orWhereIn('to_version_id', $ids)
                    ->delete();
                $count += $versions->count();
                $versions->each->delete();
            });

        return $count;
    }

    public function getVersionDiffs(Content $content): array
    {
        return $content->versions()
            ->has('diffs')
            ->with(['diffs', 'diffs.fromVersion', 'diffs.toVersion'])
            ->get()
            ->flatMap(function ($version) {
                return $version->diffs->map(function ($diff) use ($version) {
                    return [
                        'from_version' => $diff->fromVersion->version_number,
                        'to_version' => $diff->toVersion->version_number,
                        'changes' => $diff->changes,
                        'created_at' => $diff->created_at
                    ];
                });
            })
            ->sortByDesc('created_at')
            ->values()
            ->toArray();
    }

    protected function generateVersionNumber(Content $content): string
    {
        $latestVersion = $content->versions()->latest()->first();
        
        if (!$latestVersion) {
            return '1.0';
        }

        $parts = explode('.', $latestVersion->version_number);
        $major = (int)$parts[0];
        $minor = (int)$parts[1];

        // If latest version is a rollback, increment minor version
        if ($latestVersion->is_rollback) {
            return $major . '.' . ($minor + 1);
        }

        // Otherwise increment major version
        return ($major + 1) . '.0';
    }

    protected function createDiff(ContentVersion $fromVersion, ContentVersion $toVersion): ContentVersionDiff
    {
        $diff = $this->calculateDiff($fromVersion->data, $toVersion->data);

        return ContentVersionDiff::create([
            'from_version_id' => $fromVersion->id,
            'to_version_id' => $toVersion->id,
            'changes' => $diff,
            'stats' => $this->calculateDiffStats($diff)
        ]);
    }

    protected function calculateDiff(array $oldData, array $newData): array
    {
        $diff = [];

        foreach ($newData as $key => $newValue) {
            $oldValue = $oldData[$key] ?? null;

            if ($oldValue !== $newValue) {
                $diff[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                    'type' => $this->getChangeType($oldValue, $newValue)
                ];
            }
        }

        // Check for removed fields
        foreach ($oldData as $key => $oldValue) {
            if (!array_key_exists($key, $newData)) {
                $diff[$key] = [
                    'old' => $oldValue,
                    'new' => null,
                    'type' => 'removed'
                ];
            }
        }

        return $diff;
    }

    protected function calculateDiffStats(array $diff): array
    {
        $stats = [
            'total_changes' => count($diff),
            'added' => 0,
            'removed' => 0,
            'modified' => 0,
            'fields_changed' => array_keys($diff)
        ];

        foreach ($diff as $change) {
            switch ($change['type']) {
                case 'added':
                    $stats['added']++;
                    break;
                case 'removed':
                    $stats['removed']++;
                    break;
                default:
                    $stats['modified']++;
            }
        }

        return $stats;
    }

    protected function getChangeType($oldValue, $newValue): string
    {
        if ($oldValue === null) {
            return 'added';
        }

        if ($newValue === null) {
            return 'removed';
        }

        return 'modified';
    }

    protected function generateVersionSummary(ContentVersion $version): string
    {
        if ($version->is_rollback) {
            return "Rollback to version {$version->rollback_from_version}";
        }

        if ($version->is_autosave) {
            return 'Autosaved draft';
        }

        if ($version->is_current) {
            return 'Current published version';
        }

        $diffs = $version->diffs()->first();
        if ($diffs) {
            $stats = $diffs->stats;
            $changes = [];

            if ($stats['added'] > 0) {
                $changes[] = "{$stats['added']} additions";
            }
            if ($stats['removed'] > 0) {
                $changes[] = "{$stats['removed']} removals";
            }
            if ($stats['modified'] > 0) {
                $changes[] = "{$stats['modified']} modifications";
            }

            return implode(', ', $changes) ?: 'Minor changes';
        }

        return 'Initial version';
    }

    public function getContentVersion(Content $content, string $versionNumber): ?ContentVersion
    {
        return $content->versions()
            ->where('version_number', $versionNumber)
            ->first();
    }

    public function getCurrentVersion(Content $content): ?ContentVersion
    {
        return $content->versions()
            ->where('is_current', true)
            ->first();
    }

    public function getLatestAutosave(Content $content): ?ContentVersion
    {
        return $content->versions()
            ->where('is_autosave', true)
            ->latest()
            ->first();
    }

    public function promoteAutosaveToVersion(ContentVersion $autosave): ContentVersion
    {
        if (!$autosave->is_autosave) {
            throw new \InvalidArgumentException('Only autosave versions can be promoted');
        }

        return DB::transaction(function () use ($autosave) {
            // Create new version from autosave
            $version = $this->createVersion(
                $autosave->content,
                $autosave->data,
                $autosave->created_by,
                false
            );

            // Delete the autosave
            $autosave->delete();

            return $version;
        });
    }

    public function getVersionBranch(ContentVersion $version): array
    {
        $branch = [];
        $current = $version;

        // Walk backwards through versions
        while ($current) {
            $branch[] = [
                'version_number' => $current->version_number,
                'created_at' => $current->created_at,
                'created_by' => $current->creator->name,
                'is_rollback' => $current->is_rollback,
                'summary' => $this->generateVersionSummary($current)
            ];

            // If this is a rollback, find the version it rolled back to
            if ($current->is_rollback) {
                $current = $current->content->versions()
                    ->where('version_number', $current->rollback_from_version)
                    ->first();
            } else {
                // Otherwise get previous version via diff
                $diff = $current->diffs()->first();
                $current = $diff ? $diff->fromVersion : null;
            }
        }

        return array_reverse($branch);
    }

    public function getMergeConflicts(ContentVersion $base, ContentVersion $current, ContentVersion $incoming): array
    {
        $baseData = $base->data;
        $currentData = $current->data;
        $incomingData = $incoming->data;

        $conflicts = [];

        foreach ($incomingData as $key => $incomingValue) {
            $baseValue = $baseData[$key] ?? null;
            $currentValue = $currentData[$key] ?? null;

            // Conflict exists if both current and incoming changed from base differently
            if ($baseValue !== $currentValue && 
                $baseValue !== $incomingValue && 
                $currentValue !== $incomingValue) {
                $conflicts[$key] = [
                    'base' => $baseValue,
                    'current' => $currentValue,
                    'incoming' => $incomingValue
                ];
            }
        }

        return $conflicts;
    }

    public function mergeVersions(
        ContentVersion $base,
        ContentVersion $current,
        ContentVersion $incoming,
        array $resolutions,
        string $mergedBy
    ): ContentVersion {
        $mergedData = $current->data;

        foreach ($resolutions as $key => $resolution) {
            if ($resolution === 'incoming') {
                $mergedData[$key] = $incoming->data[$key];
            }
            // For 'current' we keep the existing value
        }

        return $this->createVersion(
            $current->content,
            $mergedData,
            $mergedBy,
            false
        );
    }

    public function getVersionImpact(ContentVersion $version): array
    {
        $impact = [
            'downstream_dependencies' => [],
            'linked_content' => [],
            'published_status' => null
        ];

        $content = $version->content;

        // Check if this version is published
        if ($version->is_current) {
            $impact['published_status'] = 'current';
        }

        // Get content that references this content
        $impact['linked_content'] = $content->referencingContent()
            ->get()
            ->map(function ($referencing) {
                return [
                    'id' => $referencing->id,
                    'title' => $referencing->title,
                    'version' => $referencing->currentVersion->version_number
                ];
            })
            ->toArray();

        // Get API endpoints that use this content
        $impact['downstream_dependencies'] = $this->getApiDependencies($content);

        return $impact;
    }

    protected function getApiDependencies(Content $content): array
    {
        // This would typically query an API registry or configuration
        // For now we'll return a mock implementation
        return [
            [
                'endpoint' => '/api/v1/content/' . $content->slug,
                'method' => 'GET',
                'consumers' => ['web', 'mobile']
            ],
            [
                'endpoint' => '/api/v1/related-content',
                'method' => 'POST',
                'consumers' => ['recommendation-service']
            ]
        ];
    }

    public function getVersionSize(ContentVersion $version): array
    {
        $data = $version->data;
        $size = 0;
        $fields = [];

        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            } else {
                $value = (string)$value;
            }

            $fieldSize = mb_strlen($value, '8bit');
            $size += $fieldSize;
            $fields[$key] = $fieldSize;
        }

        return [
            'total_bytes' => $size,
            'fields' => $fields,
            'field_count' => count($data)
        ];
    }

    public function getVersionStorageUsage(Content $content): array
    {
        return [
            'total_versions' => $content->versions()->count(),
            'total_storage_bytes' => $content->versions()->sum(
                DB::raw('LENGTH(CAST(data AS TEXT))')
            ),
            'versions_by_size' => $content->versions()
                ->select([
                    'id',
                    'version_number',
                    DB::raw('LENGTH(CAST(data AS TEXT)) as size')
                ])
                ->orderBy('size', 'desc')
                ->limit(10)
                ->get()
                ->toArray()
        ];
    }
}