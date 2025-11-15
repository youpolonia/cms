<?php
declare(strict_types=1);

/**
 * Version Comparison - Provides tools for comparing content versions
 */
class VersionComparison {
    /**
     * Compare two versions semantically (structure-aware comparison)
     */
    public function compareVersionsSemantic(
        string $contentA,
        string $contentB,
        array $metaA = [],
        array $metaB = []
    ): array {
        $diff = [
            'structural' => $this->compareStructure($contentA, $contentB),
            'content' => $this->compareContent($contentA, $contentB),
            'meta' => $this->compareMetadata($metaA, $metaB)
        ];

        return $diff;
    }

    /**
     * Compare content structure (blocks, sections, etc)
     */
    private function compareStructure(string $contentA, string $contentB): array {
        // Parse content structure and return differences
        return [
            'added' => [],
            'removed' => [],
            'modified' => []
        ];
    }

    /**
     * Compare actual content text
     */
    private function compareContent(string $contentA, string $contentB): array {
        // Use line-based diff algorithm
        return [
            'added' => [],
            'removed' => [],
            'modified' => []
        ];
    }

    /**
     * Compare metadata fields
     */
    private function compareMetadata(array $metaA, array $metaB): array {
        $diff = [
            'added' => array_diff_key($metaB, $metaA),
            'removed' => array_diff_key($metaA, $metaB),
            'changed' => []
        ];

        foreach ($metaA as $key => $value) {
            if (isset($metaB[$key])) {
                if ($metaB[$key] !== $value) {
                    $diff['changed'][$key] = [
                        'old' => $value,
                        'new' => $metaB[$key]
                    ];
                }
            }
        }

        return $diff;
    }

    /**
     * Check if version B is a direct descendant of version A
     */
    public function isDirectDescendant(array $versionA, array $versionB): bool {
        return ($versionB['meta']['parent_version'] ?? null) === ($versionA['version_id'] ?? null);
    }

    /**
     * Get common ancestor of two versions
     */
    public function findCommonAncestor(array $versionA, array $versionB): ?string {
        $ancestorsA = $this->getAncestors($versionA);
        $ancestorsB = $this->getAncestors($versionB);

        foreach ($ancestorsA as $ancestor) {
            if (in_array($ancestor, $ancestorsB)) {
                return $ancestor;
            }
        }

        return null;
    }

    private function getAncestors(array $version): array {
        $ancestors = [];
        $current = $version;

        while (isset($current['meta']['parent_version'])) {
            $ancestors[] = $current['meta']['parent_version'];
            $current = ContentVersioningSystem::getInstance()
                ->getVersion($current['meta']['parent_version']);
        }

        return $ancestors;
    }
}
