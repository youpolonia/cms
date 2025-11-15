<?php
class TextDiff {
    public static function compare(string $oldText, string $newText): array {
        $diff = [];
        $oldLines = explode("\n", $oldText);
        $newLines = explode("\n", $newText);
        
        $maxLines = max(count($oldLines), count($newLines));
        
        for ($i = 0; $i < $maxLines; $i++) {
            $oldLine = $oldLines[$i] ?? null;
            $newLine = $newLines[$i] ?? null;
            
            if ($oldLine !== $newLine) {
                $diff[] = [
                    'line' => $i + 1,
                    'old' => $oldLine,
                    'new' => $newLine,
                    'type' => $oldLine === null ? 'added' : ($newLine === null ? 'removed' : 'changed')
                ];
            }
        }
        
        return $diff;
    }
    
    public static function getStats(array $diff): array {
        $stats = [
            'lines_added' => 0,
            'lines_removed' => 0,
            'lines_changed' => 0
        ];
        
        foreach ($diff as $change) {
            switch ($change['type']) {
                case 'added': $stats['lines_added']++; break;
                case 'removed': $stats['lines_removed']++; break;
                case 'changed': $stats['lines_changed']++; break;
            }
        }
        
        return $stats;
    }
}
