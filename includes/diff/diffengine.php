<?php
declare(strict_types=1);

class DiffEngine {
    /**
     * Compares two text strings line by line
     * @param string $oldText Original text
     * @param string $newText Modified text
     * @return array Line-by-line diff results
     */
    public static function textDiff(string $oldText, string $newText): array {
        $oldLines = explode("\n", $oldText);
        $newLines = explode("\n", $newText);
        
        $diff = [];
        $maxLines = max(count($oldLines), count($newLines));
        
        for ($i = 0; $i < $maxLines; $i++) {
            $oldLine = $oldLines[$i] ?? null;
            $newLine = $newLines[$i] ?? null;
            
            if ($oldLine === $newLine) {
                $diff[] = ['type' => 'unchanged', 'line' => $oldLine];
            } elseif (!isset($newLines[$i])) {
                $diff[] = ['type' => 'removed', 'line' => $oldLine];
            } elseif (!isset($oldLines[$i])) {
                $diff[] = ['type' => 'added', 'line' => $newLine];
            } else {
                $diff[] = ['type' => 'modified', 'old' => $oldLine, 'new' => $newLine];
            }
        }
        
        return $diff;
    }

    /**
     * Compares two JSON structures
     * @param string $oldJson Original JSON
     * @param string $newJson Modified JSON
     * @return array Structural diff results
     */
    public static function jsonDiff(string $oldJson, string $newJson): array {
        $oldData = json_decode($oldJson, true);
        $newData = json_decode($newJson, true);
        
        // TODO: Implement JSON structure comparison
        return ['status' => 'pending', 'message' => 'JSON diff not implemented'];
    }
}
