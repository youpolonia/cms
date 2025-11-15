<?php

class VersionDiffService {
    /**
     * Compare two content versions and return differences
     * @param string $version1 Content of first version
     * @param string $version2 Content of second version 
     * @return array Array containing diff results
     */
    public static function compareVersions(string $version1, string $version2): array {
        $result = [
            'added' => [],
            'removed' => [],
            'changed' => []
        ];

        // Split content into lines for comparison
        $lines1 = explode("\n", $version1);
        $lines2 = explode("\n", $version2);

        // Find added and removed lines
        $result['added'] = array_diff($lines2, $lines1);
        $result['removed'] = array_diff($lines1, $lines2);

        // Find changed lines (present in both but different)
        $commonLines = array_intersect($lines1, $lines2);
        foreach ($commonLines as $index => $line) {
            if ($lines1[$index] !== $lines2[$index]) {
                $result['changed'][$index] = [
                    'old' => $lines1[$index],
                    'new' => $lines2[$index]
                ];
            }
        }

        return $result;
    }

    /**
     * Create a new version snapshot
     * @param string $content Content to version
     * @return string Version ID
     */
    public static function createVersion(string $content): string {
        return 'v' . date('YmdHis') . '-' . substr(md5($content), 0, 8);
    }

    /**
     * Rollback to previous version
     * @param string $currentContent Current content
     * @param string $targetContent Target version content
     * @return string Rolled back content
     */
    public static function rollbackVersion(string $currentContent, string $targetContent): string {
        return $targetContent;
    }

    /**
     * Handle large content comparison efficiently
     * @param string $content1 First content
     * @param string $content2 Second content
     * @return array Simplified diff for large content
     */
    public static function compareLargeContent(string $content1, string $content2): array {
        // For large content, we compare hashes of chunks
        $chunkSize = 1024; // 1KB chunks
        $result = [
            'changed_chunks' => 0,
            'total_chunks' => 0
        ];

        $chunks1 = str_split($content1, $chunkSize);
        $chunks2 = str_split($content2, $chunkSize);
        $result['total_chunks'] = max(count($chunks1), count($chunks2));

        foreach ($chunks1 as $i => $chunk) {
            if (!isset($chunks2[$i])) {
                $result['changed_chunks']++;
            } elseif (md5($chunk) !== md5($chunks2[$i])) {
                $result['changed_chunks']++;
            }
        }

        return $result;
    }
}
