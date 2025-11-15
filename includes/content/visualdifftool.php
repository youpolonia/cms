<?php
declare(strict_types=1);

/**
 * Content Management - Visual Diff Tool
 * Compares content versions and highlights differences
 */
class VisualDiffTool {
    /**
     * Generate line-based diff between two content versions
     */
    public static function compareText(string $oldContent, string $newContent): array {
        $oldLines = explode("\n", $oldContent);
        $newLines = explode("\n", $newContent);
        
        $diff = [];
        $maxLines = max(count($oldLines), count($newLines));
        
        for ($i = 0; $i < $maxLines; $i++) {
            $oldLine = $oldLines[$i] ?? null;
            $newLine = $newLines[$i] ?? null;
            
            if ($oldLine === $newLine) {
                $diff[] = [
                    'type' => 'unchanged',
                    'old' => $oldLine,
                    'new' => $newLine,
                    'line' => $i + 1
                ];
            } elseif ($oldLine === null) {
                $diff[] = [
                    'type' => 'added',
                    'old' => null,
                    'new' => $newLine,
                    'line' => $i + 1
                ];
            } elseif ($newLine === null) {
                $diff[] = [
                    'type' => 'removed',
                    'old' => $oldLine,
                    'new' => null,
                    'line' => $i + 1
                ];
            } else {
                $diff[] = [
                    'type' => 'modified',
                    'old' => $oldLine,
                    'new' => $newLine,
                    'line' => $i + 1
                ];
            }
        }
        
        return $diff;
    }

    /**
     * Generate HTML representation of diff
     */
    public static function renderHtmlDiff(array $diff): string {
        $html = '
<div class="content-diff">';
        foreach ($diff as $change) {
            $html .= match ($change['type']) {
                'added' => sprintf(
                    '
<div class="diff-added">+ Line %d: %s</div>',
                    $change['line'],
                    htmlspecialchars($change['new'])
                ),
                'removed' => sprintf(
                    '
<div class="diff-removed">- Line %d: %s</div>',
                    $change['line'],
                    htmlspecialchars($change['old'])
                ),
                'modified' => sprintf(
                    '
<div class="diff-modified">± Line %d: %s → %s</div>',
                    $change['line'],
                    htmlspecialchars($change['old']),
                    htmlspecialchars($change['new'])
                ),
                default => sprintf(
                    '
<div class="diff-unchanged">  Line %d: %s</div>',
                    $change['line'],
                    htmlspecialchars($change['old'])
                )
            };
        }
        $html .= '
</div>';
        return $html;
    }

    // BREAKPOINT: Continue with more advanced diff algorithms
}
