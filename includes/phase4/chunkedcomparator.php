<?php

declare(strict_types=1);

namespace Includes\Phase4;

use Includes\Core\ActivityTracker; // Assuming for logging

/**
 * ChunkedComparator
 *
 * Compares two large strings or arrays of strings in chunks to identify differences,
 * optimized for shared hosting environments with memory limits.
 */
final class ChunkedComparator
{
    private const DEFAULT_CHUNK_SIZE = 500; // Lines or characters per chunk
    private const MAX_MEMORY_MB = 64; // Max memory for a single diff operation

    /**
     * Compare two large strings line by line in chunks.
     *
     * @param string $string1 The first string.
     * @param string $string2 The second string.
     * @param int $chunkSize Number of lines per chunk.
     * @return array An array of differences. Each difference is an array:
     *               ['type' => 'added'|'removed'|'changed', 'line_old' => int|null, 'content_old' => string|null, 'line_new' => int|null, 'content_new' => string|null]
     */
    public static function compareStringsByLine(string $string1, string $string2, int $chunkSize = self::DEFAULT_CHUNK_SIZE): array
    {
        $lines1 = explode("\n", $string1);
        $lines2 = explode("\n", $string2);
        return self::compareLineArrays($lines1, $lines2, $chunkSize);
    }

    /**
     * Compare two arrays of strings (lines) in chunks.
     * This is the core comparison logic.
     *
     * @param array $lines1 Array of lines from the first source.
     * @param array $lines2 Array of lines from the second source.
     * @param int $chunkSize Number of lines per chunk for processing.
     * @return array Array of differences.
     */
    public static function compareLineArrays(array $lines1, array $lines2, int $chunkSize = self::DEFAULT_CHUNK_SIZE): array
    {
        $diffs = [];
        $len1 = count($lines1);
        $len2 = count($lines2);
        $maxLen = max($len1, $len2);

        $offset1 = 0;
        $offset2 = 0;

        // Simple Longest Common Subsequence (LCS) based diff logic (adapted for chunking)
        // This is a basic implementation. More sophisticated diff algorithms (e.g., Myers)
        // would be more accurate but also more complex to implement in pure PHP with memory constraints.

        while ($offset1 < $len1 || $offset2 < $len2) {
            // Memory check (simplified)
            if (memory_get_usage(true) / (1024 * 1024) > self::MAX_MEMORY_MB * 0.8) {
                ActivityTracker::logWarning('ChunkedComparator: Memory usage high, processing smaller chunk or stopping.', [
                    'memory_usage_mb' => memory_get_usage(true) / (1024 * 1024)
                ]);
                // Potentially reduce chunk size dynamically or throw an exception
                // For now, we'll just log and continue, but in a real scenario, this needs robust handling.
            }

            $chunk1 = array_slice($lines1, $offset1, $chunkSize);
            $chunk2 = array_slice($lines2, $offset2, $chunkSize);

            $currentChunkLen1 = count($chunk1);
            $currentChunkLen2 = count($chunk2);
            $maxCurrentChunkLen = max($currentChunkLen1, $currentChunkLen2);

            $i = 0; // Pointer for chunk1
            $j = 0; // Pointer for chunk2

            while ($i < $currentChunkLen1 || $j < $currentChunkLen2) {
                $lineNum1 = $offset1 + $i + 1;
                $lineNum2 = $offset2 + $j + 1;

                if ($i < $currentChunkLen1 && $j < $currentChunkLen2) {
                    if ($chunk1[$i] === $chunk2[$j]) {
                        // Lines are the same
                        $i++;
                        $j++;
                    } else {
                        // Lines are different. Try to find if it's an add, remove, or change.
                        // This is a simplified heuristic.
                        $lookahead_i = $i + 1;
                        $lookahead_j = $j + 1;

                        $found_in_lines2 = false;
                        if ($lookahead_j < $currentChunkLen2 && $chunk1[$i] === $chunk2[$lookahead_j]) {
                            // $chunk2[$j] was added
                            $diffs[] = ['type' => 'added', 'line_old' => null, 'content_old' => null, 'line_new' => $lineNum2, 'content_new' => $chunk2[$j]];
                            $j++;
                            $found_in_lines2 = true;
                        }

                        $found_in_lines1 = false;
                        if ($lookahead_i < $currentChunkLen1 && $chunk1[$lookahead_i] === $chunk2[$j]) {
                            // $chunk1[$i] was removed
                            $diffs[] = ['type' => 'removed', 'line_old' => $lineNum1, 'content_old' => $chunk1[$i], 'line_new' => null, 'content_new' => null];
                            $i++;
                            $found_in_lines1 = true;
                        }

                        if (!$found_in_lines1 && !$found_in_lines2) {
                             // Lines are changed
                            $diffs[] = ['type' => 'changed', 'line_old' => $lineNum1, 'content_old' => $chunk1[$i], 'line_new' => $lineNum2, 'content_new' => $chunk2[$j]];
                            $i++;
                            $j++;
                        }
                    }
                } elseif ($i < $currentChunkLen1) {
                    // Lines remaining in chunk1 only (removed)
                    $diffs[] = ['type' => 'removed', 'line_old' => $lineNum1, 'content_old' => $chunk1[$i], 'line_new' => null, 'content_new' => null];
                    $i++;
                } elseif ($j < $currentChunkLen2) {
                    // Lines remaining in chunk2 only (added)
                    $diffs[] = ['type' => 'added', 'line_old' => null, 'content_old' => null, 'line_new' => $lineNum2, 'content_new' => $chunk2[$j]];
                    $j++;
                }
            }
            $offset1 += $currentChunkLen1;
            $offset2 += $currentChunkLen2;

             // Safety break for very large dissimilar files to prevent infinite loops with basic diff logic
            if ($offset1 >= $len1 && $offset2 >= $len2 && ($currentChunkLen1 === 0 && $currentChunkLen2 === 0)) {
                 break;
            }
             if ($chunkSize === 0) { // Prevent division by zero or infinite loop if chunksize becomes 0
                ActivityTracker::logError('ChunkedComparator: Chunk size became zero.', []);
                break;
            }
        }
        return $diffs;
    }

    /**
     * A more advanced (but still simplified for this context) diff for arrays of lines.
     * This attempts a slightly more robust line-by-line comparison.
     *
     * @param array $oldLines
     * @param array $newLines
     * @return array
     */
    public static function diffArrays(array $oldLines, array $newLines): array
    {
        $diff = [];
        $oldIdx = 0;
        $newIdx = 0;
        $oldLen = count($oldLines);
        $newLen = count($newLines);

        while ($oldIdx < $oldLen || $newIdx < $newLen) {
            if ($oldIdx < $oldLen && $newIdx < $newLen && $oldLines[$oldIdx] === $newLines[$newIdx]) {
                // Unchanged line
                // $diff[] = ['type' => 'unchanged', 'line_old' => $oldIdx + 1, 'content_old' => $oldLines[$oldIdx], 'line_new' => $newIdx + 1, 'content_new' => $newLines[$newIdx]];
                $oldIdx++;
                $newIdx++;
            } else {
                // Look ahead to see if this is an insert or delete
                $foundMatch = false;
                // Try to find $oldLines[$oldIdx] in $newLines
                for ($k = $newIdx; $k < min($newIdx + 10, $newLen); $k++) { // Look ahead 10 lines
                    if ($oldIdx < $oldLen && $oldLines[$oldIdx] === $newLines[$k]) {
                        // $oldLines[$oldIdx] appears later in $newLines, so lines $newIdx to $k-1 were added
                        for ($l = $newIdx; $l < $k; $l++) {
                            $diff[] = ['type' => 'added', 'line_old' => null, 'content_old' => null, 'line_new' => $l + 1, 'content_new' => $newLines[$l]];
                        }
                        $newIdx = $k;
                        $foundMatch = true;
                        break;
                    }
                }

                if ($foundMatch) continue;

                // Try to find $newLines[$newIdx] in $oldLines
                for ($k = $oldIdx; $k < min($oldIdx + 10, $oldLen); $k++) { // Look ahead 10 lines
                    if ($newIdx < $newLen && $newLines[$newIdx] === $oldLines[$k]) {
                        // $newLines[$newIdx] appears later in $oldLines, so lines $oldIdx to $k-1 were removed
                        for ($l = $oldIdx; $l < $k; $l++) {
                            $diff[] = ['type' => 'removed', 'line_old' => $l + 1, 'content_old' => $oldLines[$l], 'line_new' => null, 'content_new' => null];
                        }
                        $oldIdx = $k;
                        $foundMatch = true;
                        break;
                    }
                }

                if ($foundMatch) continue;

                // If no match found by simple lookahead, mark as changed or add/remove remaining
                if ($oldIdx < $oldLen && $newIdx < $newLen) {
                    $diff[] = ['type' => 'changed', 'line_old' => $oldIdx + 1, 'content_old' => $oldLines[$oldIdx], 'line_new' => $newIdx + 1, 'content_new' => $newLines[$newIdx]];
                    $oldIdx++;
                    $newIdx++;
                } elseif ($oldIdx < $oldLen) {
                    $diff[] = ['type' => 'removed', 'line_old' => $oldIdx + 1, 'content_old' => $oldLines[$oldIdx], 'line_new' => null, 'content_new' => null];
                    $oldIdx++;
                } elseif ($newIdx < $newLen) {
                    $diff[] = ['type' => 'added', 'line_old' => null, 'content_old' => null, 'line_new' => $newIdx + 1, 'content_new' => $newLines[$newIdx]];
                    $newIdx++;
                }
            }
        }
        return $diff;
    }
}
