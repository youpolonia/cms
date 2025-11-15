<?php
class DiffVisualizer {
    public static function textDiff(string $old, string $new): array {
        $oldLines = explode("\n", $old);
        $newLines = explode("\n", $new);
        
        $diff = [];
        $maxLines = max(count($oldLines), count($newLines));
        
        for ($i = 0; $i < $maxLines; $i++) {
            $oldLine = $oldLines[$i] ?? '';
            $newLine = $newLines[$i] ?? '';
            
            if ($oldLine === $newLine) {
                $diff[] = ['type' => 'unchanged', 'line' => $oldLine];
            } else {
                if (!empty($oldLine)) {
                    $diff[] = ['type' => 'removed', 'line' => $oldLine];
                }
                if (!empty($newLine)) {
                    $diff[] = ['type' => 'added', 'line' => $newLine];
                }
            }
        }
        
        return $diff;
    }

    public static function htmlDiff(string $old, string $new): string {
        require_once __DIR__ . '/htmlinlinediffrenderer.php';
        return HtmlInlineDiffRenderer::render(
            explode("\n", $old),
            explode("\n", $new)
        );
    }

    public static function jsonDiff(array $old, array $new): array {
        $diff = [];
        
        $allKeys = array_unique(array_merge(
            array_keys($old),
            array_keys($new)
        ));
        
        foreach ($allKeys as $key) {
            if (!array_key_exists($key, $new)) {
                $diff[$key] = ['type' => 'removed', 'value' => $old[$key]];
            } elseif (!array_key_exists($key, $old)) {
                $diff[$key] = ['type' => 'added', 'value' => $new[$key]];
            } elseif ($old[$key] !== $new[$key]) {
                if (is_array($old[$key]) && is_array($new[$key])) {
                    $diff[$key] = self::jsonDiff($old[$key], $new[$key]);
                } else {
                    $diff[$key] = [
                        'type' => 'changed',
                        'old' => $old[$key],
                        'new' => $new[$key]
                    ];
                }
            }
        }
        
        return $diff;
    }

    public static function generateVisualDiff(array $diff): string {
        $html = '<div class="diff-container">';
        
        foreach ($diff as $item) {
            $class = match($item['type']) {
                'added' => 'diff-added',
                'removed' => 'diff-removed',
                'changed' => 'diff-changed',
                default => ''
            };
            
            $html .= sprintf(
                '<div class="diff-line %s">%s</div>',
                $class,
                htmlspecialchars($item['line'] ?? '')
            );
        }
        
        return $html . '</div>';
    }
}