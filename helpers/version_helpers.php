<?php
/**
 * Version Helpers
 * User: krala
 * Date: 2025-07-05
 */

namespace App\Helpers;

class VersionHelpers
{
    /**
     * Generate a text diff between two versions
     * 
     * @param string $oldText The older text version
     * @param string $newText The newer text version
     * @param bool $htmlAware Whether to handle HTML content specially
     * @return array Diff results including unified, sideBySide formats and stats
     */
    public static function text_diff(string $oldText, string $newText, bool $htmlAware = false): array
    {
        $oldLines = explode("\n", $oldText);
        $newLines = explode("\n", $newText);
        
        $diff = [
            'unified' => '',
            'sideBySide' => '',
            'stats' => [
                'lines_added' => 0,
                'lines_removed' => 0,
                'lines_changed' => 0
            ]
        ];

        // Implement line-by-line comparison
        $maxLines = max(count($oldLines), count($newLines));
        
        for ($i = 0; $i < $maxLines; $i++) {
            $oldLine = $oldLines[$i] ?? null;
            $newLine = $newLines[$i] ?? null;

            if ($oldLine === $newLine) {
                // Lines match
                $diff['unified'] .= "  {$oldLine}\n";
                $diff['sideBySide'] .= [
                    'left' => $oldLine,
                    'right' => $newLine,
                    'type' => 'match'
                ];
            } elseif ($oldLine === null) {
                // Line added
                $diff['unified'] .= "+ {$newLine}\n";
                $diff['sideBySide'] .= [
                    'left' => '',
                    'right' => $newLine,
                    'type' => 'add'
                ];
                $diff['stats']['lines_added']++;
            } elseif ($newLine === null) {
                // Line removed
                $diff['unified'] .= "- {$oldLine}\n";
                $diff['sideBySide'] .= [
                    'left' => $oldLine,
                    'right' => '',
                    'type' => 'remove'
                ];
                $diff['stats']['lines_removed']++;
            } else {
                // Line changed
                $diff['unified'] .= "- {$oldLine}\n+ {$newLine}\n";
                $diff['sideBySide'] .= [
                    'left' => $oldLine,
                    'right' => $newLine,
                    'type' => 'change'
                ];
                $diff['stats']['lines_changed']++;
            }
        }

        // Format sideBySide for HTML output
        if ($htmlAware) {
            $diff['sideBySide'] = self::formatHtmlDiff($diff['sideBySide']);
        }

        return $diff;
    }

    /**
     * Format diff for HTML display
     */
    private static function formatHtmlDiff(array $sideBySide): string
    {
        $html = '';
        foreach ($sideBySide as $line) {
            $leftClass = $line['type'] === 'remove' || $line['type'] === 'change' ? 'diff-removed' : '';
            $rightClass = $line['type'] === 'add' || $line['type'] === 'change' ? 'diff-added' : '';
            
            $html .= sprintf(
                '
<div class="diff-line">' .
                '<div class="diff-left %s">%s</div>' .
                '<div class="diff-right %s">%s</div>' .
                '</div>',
                $leftClass,
                htmlspecialchars($line['left']),
                $rightClass,
                htmlspecialchars($line['right'])
            );
        }
        return $html;
    }
}
