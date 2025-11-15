<?php
declare(strict_types=1);

class VersionDiffService {
    /**
     * Calculate line differences between two text versions
     */
    public static function calculateDiff(string $old, string $new): array {
        $oldLines = explode("\n", $old);
        $newLines = explode("\n", $new);
        
        $diff = [];
        $maxLines = max(count($oldLines), count($newLines));
        
        for ($i = 0; $i < $maxLines; $i++) {
            $oldLine = $oldLines[$i] ?? null;
            $newLine = $newLines[$i] ?? null;
            
            if ($oldLine !== $newLine) {
                $diff[] = [
                    'line' => $i + 1,
                    'old' => $oldLine,
                    'new' => $newLine
                ];
            }
        }
        
        return $diff;
    }

    /**
     * Generate merge conflict markers for conflicting changes
     */
    public static function generateConflictMarkers(array $diff): string {
        $output = '';
        foreach ($diff as $change) {
            $output .= "<<<<<<< OLD\n";
            $output .= $change['old'] . "\n";
            $output .= "=======\n";
            $output .= $change['new'] . "\n";
            $output .= ">>>>>>> NEW\n\n";
        }
        return $output;
    }
}
