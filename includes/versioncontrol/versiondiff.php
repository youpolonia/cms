<?php
/**
 * VersionDiff - PHP-native text comparison utility
 * 
 * Compares two text versions and identifies changes
 */
class VersionDiff {
    /**
     * Compare two text versions
     * @param string $old Original content
     * @param string $new Modified content
     * @param bool $lineByLine Whether to compare line-by-line (true) or as blocks (false)
     * @return array Comparison results with:
     *   - 'added': Array of added lines/blocks
     *   - 'removed': Array of removed lines/blocks  
     *   - 'unchanged': Array of unchanged lines/blocks
     *   - 'similarity': Percentage similarity (0-100)
     */
    public function compare(string $old, string $new, bool $lineByLine = true): array {
        $result = [
            'added' => [],
            'removed' => [],
            'unchanged' => [],
            'similarity' => 0
        ];

        // Calculate overall similarity
        similar_text($old, $new, $result['similarity']);

        if ($lineByLine) {
            // Line-by-line comparison
            $oldLines = explode("\n", $old);
            $newLines = explode("\n", $new);
            
            $diff = array_diff($newLines, $oldLines);
            $result['added'] = array_values($diff);
            
            $diff = array_diff($oldLines, $newLines);
            $result['removed'] = array_values($diff);
            
            $result['unchanged'] = array_intersect($oldLines, $newLines);
        } else {
            // Block comparison (split by double newlines)
            $oldBlocks = preg_split('/\n\s*\n/', $old);
            $newBlocks = preg_split('/\n\s*\n/', $new);
            
            $diff = array_diff($newBlocks, $oldBlocks);
            $result['added'] = array_values($diff);
            
            $diff = array_diff($oldBlocks, $newBlocks);
            $result['removed'] = array_values($diff);
            
            $result['unchanged'] = array_intersect($oldBlocks, $newBlocks);
        }

        return $result;
    }

    /**
     * Format comparison results as human-readable text
     * @param array $diffResult Result from compare() method
     * @return string Formatted diff output
     */
    public function formatDiff(array $diffResult): string {
        $output = "Similarity: {$diffResult['similarity']}%\n\n";
        
        if (!empty($diffResult['removed'])) {
            $output .= "=== REMOVED ===\n";
            foreach ($diffResult['removed'] as $line) {
                $output .= "- $line\n";
            }
            $output .= "\n";
        }
        
        if (!empty($diffResult['added'])) {
            $output .= "=== ADDED ===\n";
            foreach ($diffResult['added'] as $line) {
                $output .= "+ $line\n";
            }
            $output .= "\n";
        }
        
        return $output;
    }
}
