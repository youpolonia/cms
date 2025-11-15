<?php
class DiffRenderer {
    public function compareTexts($oldText, $newText) {
        $oldLines = explode("\n", $oldText);
        $newLines = explode("\n", $newText);
        
        $diff = $this->computeDiff($oldLines, $newLines);
        return [
            'side_by_side' => $this->renderSideBySide($diff, $oldLines, $newLines),
            'unified' => $this->renderUnified($diff),
            'semantic' => $this->renderSemantic($diff)
        ];
    }

    private function computeDiff($old, $new) {
        $matrix = [];
        $maxLen = 0;
        $omax = 0;
        $nmax = 0;

        foreach ($old as $oindex => $ovalue) {
            $nkeys = array_keys($new, $ovalue);
            foreach ($nkeys as $nindex) {
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1])
                    ? $matrix[$oindex - 1][$nindex - 1] + 1
                    : 1;
                if ($matrix[$oindex][$nindex] > $maxLen) {
                    $maxLen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxLen;
                    $nmax = $nindex + 1 - $maxLen;
                }
            }
        }

        if ($maxLen == 0) {
            return [['old' => $old, 'new' => $new]];
        }

        return array_merge(
            $this->computeDiff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
            array_slice($new, $nmax, $maxLen),
            $this->computeDiff(array_slice($old, $omax + $maxLen), array_slice($new, $nmax + $maxLen))
        );
    }


    private function renderSideBySide($diff, $oldLines, $newLines) {
        $result = [];
        $oldIndex = 0;
        $newIndex = 0;
        
        foreach ($diff as $d) {
            if (is_array($d)) {
                // Changed lines
                foreach ($d['old'] as $line) {
                    $result[] = [
                        'old_line' => $line,
                        'new_line' => '',
                        'type' => 'removed'
                    ];
                }
                foreach ($d['new'] as $line) {
                    $result[] = [
                        'old_line' => '',
                        'new_line' => $line,
                        'type' => 'added'
                    ];
                }
            } else {
                // Unchanged line
                $result[] = [
                    'old_line' => $oldLines[$oldIndex] ?? '',
                    'new_line' => $newLines[$newIndex] ?? '',
                    'type' => 'unchanged'
                ];
                $oldIndex++;
                $newIndex++;
            }
        }
        
        return $result;
    }

    private function renderUnified($diff) {
        $result = [];
        foreach ($diff as $d) {
            if (is_array($d)) {
                foreach ($d['old'] as $line) {
                    $result[] = '- ' . $line;
                }
                foreach ($d['new'] as $line) {
                    $result[] = '+ ' . $line;
                }
            } else {
                $result[] = '  ' . $d;
            }
        }
        return implode("\n", $result);
    }

    private function renderSemantic($diff) {
        $result = [];
        foreach ($diff as $d) {
            if (is_array($d)) {
                $result[] = [
                    'type' => 'change',
                    'old' => $d['old'],
                    'new' => $d['new']
                ];
            } else {
                $result[] = [
                    'type' => 'context',
                    'content' => $d
                ];
            }
        }
        return $result;
    }

    public static function visualDiff($diffData) {
        $html = '
<div class="diff-container">';
        foreach ($diffData as $change) {
            if ($change['type'] === 'removed') {
                $html .= '
<div class="diff-line removed">- ' . htmlspecialchars(
$change['old_line']) . '</div>';
            } else
if ($change['type'] === 'added') {
                $html .= '
<div class="diff-line added">+ ' . htmlspecialchars(
$change['new_line']) . '</div>';
            }
 else {
                $html .= '
<div class="diff-line unchanged">  ' . htmlspecialchars(
$change['old_line']) . '</div>';
            }
        }
        $html .= '</div>';
        return $html;
    }
}
