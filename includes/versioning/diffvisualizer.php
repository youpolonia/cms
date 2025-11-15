<?php
declare(strict_types=1);

class DiffVisualizer {
    public static function compareText(string $oldText, string $newText): string {
        $oldLines = explode("\n", $oldText);
        $newLines = explode("\n", $newText);
        
        $diff = [];
        $oldCount = count($oldLines);
        $newCount = count($newLines);
        $maxLines = max($oldCount, $newCount);

        for ($i = 0; $i < $maxLines; $i++) {
            $oldLine = $oldLines[$i] ?? '';
            $newLine = $newLines[$i] ?? '';
            
            if ($oldLine === $newLine) {
                $diff[] = "  " . htmlspecialchars($oldLine);
            } else {
                $diff[] = "- " . htmlspecialchars($oldLine);
                $diff[] = "+ " . htmlspecialchars($newLine);
            }
        }

        return implode("\n", $diff);
    }

    public static function compareSideBySide(string $oldText, string $newText): array {
        $oldLines = explode("\n", $oldText);
        $newLines = explode("\n", $newText);
        
        $result = [];
        $maxLines = max(count($oldLines), count($newLines));
        
        for ($i = 0; $i < $maxLines; $i++) {
            $result[] = [
                'old' => $oldLines[$i] ?? null,
                'new' => $newLines[$i] ?? null,
                'changed' => ($oldLines[$i] ?? null) !== ($newLines[$i] ?? null)
            ];
        }
        
        return $result;
    }

    public static function calculateChangePercentage(string $oldText, string $newText): float {
        similar_text($oldText, $newText, $percentage);
        return 100 - $percentage;
    }

    public static function compareJson(array $oldJson, array $newJson): array {
        $changes = [];
        
        try {
            $mergedKeys = array_merge(
                array_keys($oldJson),
                array_keys($newJson)
            );
            $allKeys = array_unique($mergedKeys);

            foreach ($allKeys as $key) {
                $oldValue = $oldJson[$key] ?? null;
                $newValue = $newJson[$key] ?? null;

                if ($oldValue !== $newValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                        'changed' => true
                    ];
                }
            }
        } catch (Exception $e) {
            error_log("JSON diff error: " . $e->getMessage());
        }

        return $changes;
    }

    public static function generateDiffHash(string $content): string {
        return hash('sha256', $content);
    }
}
