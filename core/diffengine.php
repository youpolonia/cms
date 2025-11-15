<?php
/**
 * DiffEngine - Advanced diff algorithm implementation
 */
class DiffEngine {
    /**
     * Generate line-level diff between two texts using Myers algorithm
     * @param string $oldText Original text
     * @param string $newText Modified text
     * @return array Array of changes with line numbers and types
     */
    public static function lineDiff($oldText, $newText) {
        $oldLines = explode("\n", $oldText);
        $newLines = explode("\n", $newText);
        
        $changes = [];
        $matrix = [];
        $max = count($oldLines) + count($newLines);
        
        // Myers algorithm implementation
        $v = [1 => 0];
        for ($d = 0; $d <= $max; $d++) {
            for ($k = -$d; $k <= $d; $k += 2) {
                if ($k === -$d || ($k !== $d && $v[$k-1] < $v[$k+1])) {
                    $x = $v[$k+1];
                } else {
                    $x = $v[$k-1] + 1;
                }
                
                $y = $x - $k;
                
                while ($x < count($oldLines) && $y < count($newLines) && 
                       $oldLines[$x] === $newLines[$y]) {
                    $x++;
                    $y++;
                }
                
                $v[$k] = $x;
                
                if ($x >= count($oldLines) && $y >= count($newLines)) {
                    // Reached the end
                    return self::buildDiff($oldLines, $newLines, $v, $d);
                }
            }
        }
        
        return $changes;
    }

    private static function buildDiff($oldLines, $newLines, $v, $d) {
        $changes = [];
        $x = count($oldLines);
        $y = count($newLines);
        
        for ($i = $d; $i >= 0; $i--) {
            $prevV = $v;
            $prevD = $i - 1;
            
            $k = $x - $y;
            
            if ($k === -$prevD || ($k !== $prevD && $prevV[$k-1] < $prevV[$k+1])) {
                $prevK = $k + 1;
            } else {
                $prevK = $k - 1;
            }
            
            $prevX = $prevV[$prevK];
            $prevY = $prevX - $prevK;
            
            while ($x > $prevX && $y > $prevY) {
                $changes[] = [
                    'type' => 'equal',
                    'old_line' => $x,
                    'new_line' => $y,
                    'content' => $oldLines[$x-1]
                ];
                $x--;
                $y--;
            }
            
            if ($i === 0) break;
            
            if ($x === $prevX) {
                $changes[] = [
                    'type' => 'insert',
                    'new_line' => $y,
                    'content' => $newLines[$y-1]
                ];
                $y--;
            } else {
                $changes[] = [
                    'type' => 'delete',
                    'old_line' => $x,
                    'content' => $oldLines[$x-1]
                ];
                $x--;
            }
        }
        
        return array_reverse($changes);
    }

    /**
     * Generate HTML diff with visual changes
     * @param string $oldText Original text
     * @param string $newText Modified text
     * @return string HTML with visual diff
     */
    public static function htmlDiff($oldText, $newText) {
        $changes = self::lineDiff($oldText, $newText);
        $html = '<div class="diff-container">';
        
        $oldHtml = '<div class="diff-old">';
        $newHtml = '<div class="diff-new">';
        
        foreach ($changes as $change) {
            switch ($change['type']) {
                case 'equal':
                    $oldHtml .= '<div class="diff-line unchanged">' . htmlspecialchars($change['content']) . '</div>';
                    $newHtml .= '<div class="diff-line unchanged">' . htmlspecialchars($change['content']) . '</div>';
                    break;
                case 'delete':
                    $oldHtml .= '<div class="diff-line deleted">' . htmlspecialchars($change['content']) . '</div>';
                    break;
                case 'insert':
                    $newHtml .= '<div class="diff-line inserted">' . htmlspecialchars($change['content']) . '</div>';
                    break;
            }
        }
        
        $oldHtml .= '</div>';
        $newHtml .= '</div>';
        
        return $html . $oldHtml . $newHtml . '</div>';
    }
}