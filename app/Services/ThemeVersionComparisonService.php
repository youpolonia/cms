<?php

namespace App\Services;

use App\Models\ThemeVersion;
use Illuminate\Support\Collection;
use App\Contracts\DiffServiceInterface;

class ThemeVersionComparisonService
{
    protected DiffServiceInterface $diffService;

    public function __construct(DiffServiceInterface $diffService)
    {
        $this->diffService = $diffService;
    }

    /**
     * Compare two theme versions
     */
    public function compareVersions(ThemeVersion $versionA, ThemeVersion $versionB): array
    {
        $semanticDiff = $this->diffService->compareVersions($versionA, $versionB);
        
        return array_merge($semanticDiff, [
            'files_changed' => $this->getChangedFiles($versionA, $versionB),
            'dependencies_changed' => $this->getDependencyChanges($versionA, $versionB),
            'size_changes' => $this->getSizeChanges($versionA, $versionB),
        ]);
    }

    protected function getChangedFiles(ThemeVersion $versionA, ThemeVersion $versionB): Collection
    {
        // Get files from both versions
        $filesA = collect($versionA->files);
        $filesB = collect($versionB->files);

        // Find added, removed and modified files
        $added = $filesB->diffKeys($filesA);
        $removed = $filesA->diffKeys($filesB);
        $modified = $filesA->intersectByKeys($filesB)
            ->filter(fn ($fileA, $path) => $fileA['hash'] !== $filesB[$path]['hash']);

        return collect([
            'added' => $added,
            'removed' => $removed,
            'modified' => $modified,
            'total_changes' => $added->count() + $removed->count() + $modified->count(),
        ]);
    }

    protected function getLinesChanged(ThemeVersion $versionA, ThemeVersion $versionB): array
    {
        $semanticDiff = $this->diffService->compareVersions($versionA, $versionB);
        return $semanticDiff['summary'] ?? [
            'added' => 0,
            'removed' => 0,
            'modified' => 0,
            'semantic_changes' => 0
        ];
    }

    protected function getDependencyChanges(ThemeVersion $versionA, ThemeVersion $versionB): array
    {
        return [
            'added' => array_diff($versionB->dependencies ?? [], $versionA->dependencies ?? []),
            'removed' => array_diff($versionA->dependencies ?? [], $versionB->dependencies ?? []),
            'updated' => [], // TODO: Implement version comparison
        ];
    }

    protected function getSizeChanges(ThemeVersion $versionA, ThemeVersion $versionB): array
    {
        return [
            'total_size_diff' => ($versionB->total_size_kb ?? 0) - ($versionA->total_size_kb ?? 0),
            'css_size_diff' => ($versionB->css_size_kb ?? 0) - ($versionA->css_size_kb ?? 0),
            'js_size_diff' => ($versionB->js_size_kb ?? 0) - ($versionA->js_size_kb ?? 0),
        ];
    }

    /**
     * Generate visual diff between two versions
     */
    public function generateVisualDiff(ThemeVersion $versionA, ThemeVersion $versionB): array
    {
        $semanticDiff = $this->diffService->compareVersions($versionA, $versionB);
        
        return [
            'html_diff' => $this->diffService->generateHtmlDiff($versionA, $versionB),
            'css_diff' => $this->diffService->generateCssDiff($versionA, $versionB),
            'js_diff' => $this->diffService->generateJsDiff($versionA, $versionB),
            'semantic_changes' => $semanticDiff['summary'] ?? []
        ];
    }
}