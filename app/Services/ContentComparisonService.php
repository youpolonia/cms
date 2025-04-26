<?php

namespace App\Services;

use App\Models\ContentVersion;
use App\Models\VersionComparison;
use Illuminate\Support\Arr;

class ContentComparisonService
{
    public function compareVersions(ContentVersion $baseVersion, ContentVersion $compareVersion)
    {
        $baseContent = $baseVersion->content_data;
        $compareContent = $compareVersion->content_data;

        $diffResults = $this->calculateDiff($baseContent, $compareContent);
        $metrics = $this->calculateMetrics($baseContent, $compareContent);

        return VersionComparison::create([
            'content_id' => $baseVersion->content_id,
            'base_version_id' => $baseVersion->id,
            'compare_version_id' => $compareVersion->id,
            'diff_results' => $diffResults,
            'metrics' => $metrics
        ]);
    }

    protected function calculateDiff(array $base, array $compare): array
    {
        $diffs = [];
        
        foreach ($base as $key => $value) {
            if (!array_key_exists($key, $compare)) {
                $diffs[$key] = [
                    'type' => 'removed',
                    'old' => $value,
                    'new' => null
                ];
                continue;
            }

            if ($value !== $compare[$key]) {
                $diffs[$key] = [
                    'type' => 'changed',
                    'old' => $value,
                    'new' => $compare[$key]
                ];
            }
        }

        foreach ($compare as $key => $value) {
            if (!array_key_exists($key, $base)) {
                $diffs[$key] = [
                    'type' => 'added',
                    'old' => null,
                    'new' => $value
                ];
            }
        }

        return $diffs;
    }

    protected function calculateMetrics(array $base, array $compare): array
    {
        $totalFields = count($base) + count(array_diff_key($compare, $base));
        $changedFields = count(array_diff_assoc($base, $compare));
        
        return [
            'similarity_score' => $totalFields > 0 
                ? round(100 - ($changedFields / $totalFields * 100), 2)
                : 100,
            'changed_fields' => $changedFields,
            'added_fields' => count(array_diff_key($compare, $base)),
            'removed_fields' => count(array_diff_key($base, $compare))
        ];
    }
}