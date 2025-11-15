<?php
/**
 * Content Version Diff Engine
 * 
 * Implements a version comparison system for tracking content changes.
 * Uses Myers diff algorithm for efficient difference detection.
 * 
 * Features:
 * - Text and HTML content comparison
 * - Line-level and word-level diffing
 * - Human-readable output formatting
 * - Memory-efficient operations
 */

class DiffEngine {
    const MAX_COMPARISON_SIZE = 1048576; // 1MB memory limit for comparison
    const SMALL_CONTENT_THRESHOLD = 102400; // 100KB threshold for small content
    const CACHE_TTL = 3600; // 1 hour cache TTL
    
    /**
     * Compare two versions of content and return differences
     *
     * @param string $oldContent The original content
     * @param string $newContent The modified content
     * @param bool $isHtml Whether the content is HTML
     * @param bool $isStructured Whether content is structured data
     * @return array Array of differences with metadata
     */
    public static function compare(mixed $oldContent, mixed $newContent, bool $isHtml = false, bool $isStructured = false): array {
        // Validate input size
        if (is_string($oldContent)) {
            $oldSize = strlen($oldContent);
            $newSize = strlen($newContent);
            
            if ($oldSize > self::MAX_COMPARISON_SIZE || $newSize > self::MAX_COMPARISON_SIZE) {
                throw new \RuntimeException('Content exceeds maximum comparison size');
            }
            
            // Use cached result if available
            $cacheKey = md5($oldContent . $newContent . (int)$isHtml . (int)$isStructured);
            if ($cached = self::getFromCache($cacheKey)) {
                return $cached;
            }
        } elseif (is_array($oldContent) && is_array($newContent)) {
            if (empty($oldContent) || empty($newContent)) {
                return ['status' => 'empty_input'];
            }
        }
        
        if ($isStructured) {
            return self::compareStructured($oldContent, $newContent);
        }
        if ($oldContent === $newContent) {
            return [];
        }

        if ($isHtml) {
            return self::compareHtml($oldContent, $newContent);
        }
        
        return self::compareText($oldContent, $newContent);
    }

    /**
     * Compare plain text content using line-level diffing
     *
     * @param string $oldText Original text content
     * @param string $newText Modified text content
     * @return array Difference array
     * @throws \RuntimeException If content is too large
     */
    protected static function compareText(string $oldText, string $newText): array {
        $oldLines = explode("\n", $oldText);
        $newLines = explode("\n", $newText);
        
        $diff = [];
        $operations = self::myersDiff($oldLines, $newLines);
        
        foreach ($operations as $op) {
            switch ($op['type']) {
                case 'insert':
                    $diff[] = [
                        'type' => 'insert',
                        'line' => $op['new_pos'],
                        'content' => $newLines[$op['new_pos']]
                    ];
                    break;
                    
                case 'delete':
                    $diff[] = [
                        'type' => 'delete',
                        'line' => $op['old_pos'],
                        'content' => $oldLines[$op['old_pos']]
                    ];
                    break;
                    
                case 'change':
                    $diff[] = [
                        'type' => 'change',
                        'line' => $op['new_pos'],
                        'old_content' => $oldLines[$op['old_pos']],
                        'new_content' => $newLines[$op['new_pos']]
                    ];
                    break;
            }
        }
        
        return $diff;
    }

    /**
     * Compare HTML content with special handling for tags
     */
    protected static function compareHtml(string $oldHtml, string $newHtml): array {
        // Normalize HTML by removing extra whitespace
        $oldHtml = self::normalizeHtml($oldHtml);
        $newHtml = self::normalizeHtml($newHtml);
        
        // First do line-level comparison
        $lineDiff = self::compareText($oldHtml, $newHtml);
        
        // Then do word-level comparison for changed lines
        foreach ($lineDiff as &$change) {
            if ($change['type'] === 'change') {
                $change['word_diff'] = self::compareWords(
                    $change['old_content'],
                    $change['new_content']
                );
            }
        }
        
        return $lineDiff;
    }

    /**
     * Myers diff algorithm implementation
     * Finds the shortest edit sequence between two arrays
     */
    protected static function myersDiff(array $a, array $b): array {
        $n = count($a);
        $m = count($b);
        $max = $n + $m;
        $v = [1 => 0];
        $trace = [];
        
        for ($d = 0; $d <= $max; $d++) {
            $trace[] = $v;
            for ($k = -$d; $k <= $d; $k += 2) {
                if ($k === -$d || ($k !== $d && $v[$k-1] < $v[$k+1])) {
                    $x = $v[$k+1];
                } else {
                    $x = $v[$k-1] + 1;
                }
                
                $y = $x - $k;
                
                while ($x < $n && $y < $m && $a[$x] === $b[$y]) {
                    $x++;
                    $y++;
                }
                
                $v[$k] = $x;
                
                if ($x >= $n && $y >= $m) {
                    return self::backtrack($a, $b, $trace);
                }
            }
        }
        
        return [];
    }

    /**
     * Backtrack through the trace to find the edit path
     */
    protected static function backtrack(array $a, array $b, array $trace): array {
        $x = count($a);
        $y = count($b);
        $d = count($trace) - 1;
        $result = [];
        $path = [];
        
        while ($d >= 0) {
            $v = $trace[$d];
            $k = $x - $y;
            
            if ($k === -$d || ($k !== $d && $v[$k-1] < $v[$k+1])) {
                $prev_k = $k + 1;
            } else {
                $prev_k = $k - 1;
            }
            
            $prev_x = $v[$prev_k];
            $prev_y = $prev_x - $prev_k;
            
            while ($x > $prev_x && $y > $prev_y) {
                $result[] = ['type' => 'equal', 'old_pos' => $x-1, 'new_pos' => $y-1];
                $x--;
                $y--;
            }
            
            if ($d > 0) {
                if ($x === $prev_x) {
                    $result[] = ['type' => 'insert', 'old_pos' => $prev_x, 'new_pos' => $prev_y];
                } else {
                    $result[] = ['type' => 'delete', 'old_pos' => $prev_x, 'new_pos' => $prev_y];
                }
            }
            
            $x = $prev_x;
            $y = $prev_y;
            $d--;
        }
        
        return array_reverse($result);
    }
    
    /**
     * Compare structured content (arrays/JSON)
     */
    private static function compareStructured($oldData, $newData): array {
        if (is_string($oldData)) {
            $oldData = json_decode($oldData, true);
            $newData = json_decode($newData, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON data: ' . json_last_error_msg());
            }
        }

        $diff = [];
        
        // Compare array keys
        $allKeys = array_unique(array_merge(
            array_keys($oldData),
            array_keys($newData)
        ));

        foreach ($allKeys as $key) {
            if (!array_key_exists($key, $oldData)) {
                $diff[$key] = ['type' => 'added', 'value' => $newData[$key]];
            } elseif (!array_key_exists($key, $newData)) {
                $diff[$key] = ['type' => 'removed', 'value' => $oldData[$key]];
            } elseif ($oldData[$key] !== $newData[$key]) {
                if (is_array($oldData[$key]) && is_array($newData[$key])) {
                    $diff[$key] = self::compareStructured($oldData[$key], $newData[$key]);
                } else {
                    $diff[$key] = [
                        'type' => 'changed',
                        'old_value' => $oldData[$key],
                        'new_value' => $newData[$key]
                    ];
                }
            }
        }

        return $diff;
    }

    /**
     * Cache comparison results
     */
    private static function getFromCache(string $key): ?array {
        static $cache = [];
        
        if (isset($cache[$key])) {
            return $cache[$key];
        }
        
        // In a real implementation, this would use a proper caching system
        return null;
    }
    
    /**
     * Store comparison results in cache
     */
    private static function storeInCache(string $key, array $result): void {
        static $cache = [];
        $cache[$key] = $result;
    }

    /**
     * Compare words within a line for granular changes
     */
    protected static function compareWords(string $oldText, string $newText): array {
        // Optimized word splitting that handles HTML entities better
        $oldWords = preg_split('/(&[^;]+;|\s+)/', $oldText, -1, PREG_SPLIT_DELIM_CAPTURE);
        $newWords = preg_split('/(&[^;]+;|\s+)/', $newText, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        // Filter out empty strings
        $oldWords = array_filter($oldWords, fn($w) => $w !== '');
        $newWords = array_filter($newWords, fn($w) => $w !== '');
        
        return self::myersDiff($oldWords, $newWords);
    }

    /**
     * Normalize HTML content for better comparison
     */
    protected static function normalizeHtml(string $html): string {
        // Remove extra whitespace and normalize line endings
        $html = preg_replace('/\s+/', ' ', $html);
        $html = str_replace(["\r\n", "\r"], "\n", $html);
        
        // Normalize HTML attributes (order and quotes)
        $html = preg_replace_callback('/<([a-z][a-z0-9]*)([^>]*)>/i', function($matches) {
            $tag = $matches[1];
            $attrs = $matches[2];
            
            // Parse attributes
            preg_match_all('/(\w+)=("[^"]*"|\'[^\']*\'|[^"\'\s>]+)/', $attrs, $attrMatches);
            $attributes = array_combine($attrMatches[1], $attrMatches[2]);
            ksort($attributes);
            
            // Rebuild tag with sorted attributes
            $newAttrs = '';
            foreach ($attributes as $name => $value) {
                $newAttrs .= " $name=$value";
            }
            
            return "<$tag$newAttrs>";
        }, $html);
        
        return trim($html);
    }

    /**
     * Format differences for human-readable output
     */
    public static function formatDiff(array $diff): string {
        $output = '';
        $stats = self::getDiffStats($diff);
        
        $output .= "Summary:\n";
        $output .= "- Total changes: {$stats['total']}\n";
        $output .= "- Insertions: {$stats['insertions']}\n";
        $output .= "- Deletions: {$stats['deletions']}\n";
        $output .= "- Modifications: {$stats['changes']}\n";
        $output .= "- Conflicts: {$stats['conflicts']}\n\n";
        
        foreach ($diff as $change) {
            switch ($change['type']) {
                case 'insert':
                    $output .= "+ Line {$change['line']}: {$change['content']}\n";
                    break;
                    
                case 'delete':
                    $output .= "- Line {$change['line']}: {$change['content']}\n";
                    break;
                    
                case 'change':
                    $output .= "! Line {$change['line']}:\n";
                    $output .= "- {$change['old_content']}\n";
                    $output .= "+ {$change['new_content']}\n";
                    
                    if (isset($change['word_diff'])) {
                        $output .= "  Word changes:\n";
                        foreach ($change['word_diff'] as $wordChange) {
                            $output .= self::formatWordChange($wordChange, $change);
                        }
                    }
                    
                    if (isset($change['conflict'])) {
                        $output .= "  CONFLICT: {$change['conflict_reason']}\n";
                    }
                    break;
            }
        }
        
        return $output;
    }

    /**
     * Format word-level changes for output
     */
    protected static function formatWordChange(array $wordChange, array $lineChange): string {
        $output = '';
        $oldWords = preg_split('/\s+/', $lineChange['old_content']);
        $newWords = preg_split('/\s+/', $lineChange['new_content']);
        
        switch ($wordChange['type']) {
            case 'insert':
                $output .= "    + Word {$wordChange['new_pos']}: {$newWords[$wordChange['new_pos']]}\n";
                break;
                
            case 'delete':
                $output .= "    - Word {$wordChange['old_pos']}: {$oldWords[$wordChange['old_pos']]}\n";
                break;
                
            case 'change':
                $output .= "    ! Word {$wordChange['new_pos']}:\n";
                $output .= "      - {$oldWords[$wordChange['old_pos']]}\n";
                $output .= "      + {$newWords[$wordChange['new_pos']]}\n";
                break;
        }
        
        return $output;
    }

    /**
     * Calculate diff statistics
     */
    public static function getDiffStats(array $diff): array {
        $stats = [
            'total' => count($diff),
            'insertions' => 0,
            'deletions' => 0,
            'changes' => 0,
            'conflicts' => 0
        ];
        
        foreach ($diff as $change) {
            switch ($change['type']) {
                case 'insert': $stats['insertions']++; break;
                case 'delete': $stats['deletions']++; break;
                case 'change':
                    $stats['changes']++;
                    if (isset($change['conflict'])) {
                        $stats['conflicts']++;
                    }
                    break;
            }
        }
        
        return $stats;
    }

    /**
     * Detect conflicts between changes
     */
    public static function detectConflicts(array $diff1, array $diff2): array {
        $conflicts = [];
        $lines1 = self::getChangedLines($diff1);
        $lines2 = self::getChangedLines($diff2);
        
        $intersect = array_intersect_key($lines1, $lines2);
        foreach ($intersect as $line => $changes) {
            $conflicts[] = [
                'line' => $line,
                'conflict_reason' => 'Overlapping changes on same line',
                'changes' => [$changes, $lines2[$line]]
            ];
        }
        
        return $conflicts;
    }

    /**
     * Get map of changed lines
     */
    protected static function getChangedLines(array $diff): array {
        $lines = [];
        foreach ($diff as $change) {
            $lineKey = $change['line'] ?? 0;
            $lines[$lineKey] = $change;
        }
        return $lines;
    }
}
