<?php
class HtmlInlineDiffRenderer {
    public static function render(array $oldLines, array $newLines): string {
        $diff = DiffVisualizer::textDiff(implode("\n", $oldLines), implode("\n", $newLines));
        $html = '<div class="diff">';
        
        foreach ($diff as $item) {
            switch ($item['type']) {
                case 'unchanged':
                    $html .= '<span>' . htmlspecialchars($item['line']) . '</span><br>';
                    break;
                case 'removed':
                    $html .= '<del>' . htmlspecialchars($item['line']) . '</del><br>';
                    break;
                case 'added':
                    $html .= '<ins>' . htmlspecialchars($item['line']) . '</ins><br>';
                    break;
            }
        }
        
        return $html . '</div>';
    }
}