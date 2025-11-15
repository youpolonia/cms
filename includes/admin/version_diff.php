<?php
/**
 * Content Version Diff Viewer
 * 
 * Enhanced side-by-side version comparison with:
 * - Line highlighting
 * - Context toggles
 * - Navigation controls
 * - Word-level diff for HTML
 */

require_once __DIR__ . '/../versioning/diffengine.php';

function renderVersionDiff(string $oldContent, string $newContent, bool $isHtml = false): string {
    $diff = DiffEngine::compare($oldContent, $newContent, $isHtml);
    $stats = calculateDiffStats($diff);
    
    $oldContentHtml = htmlspecialchars(renderDiffContent($diff, 'old', $isHtml));
    $newContentHtml = htmlspecialchars(renderDiffContent($diff, 'new', $isHtml));

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Content Version Diff Viewer</title>
    <style>
        .diff-container {
            display: flex;
            flex-direction: column;
            font-family: monospace;
            margin: 1rem;
        }
        .diff-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .diff-controls {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        .diff-view {
            display: flex;
            gap: 1rem;
            overflow: auto;
        }
        .version-pane {
            flex: 1;
            border: 1px solid #ddd;
            padding: 0.5rem;
            background: #f8f8f8;
        }
        .diff-line {
            padding: 0.2rem;
            white-space: pre-wrap;
        }
        .diff-insert {
            background: #e6ffed;
        }
        .diff-delete {
            background: #ffebe9;
        }
        .diff-change {
            background: #fff8c5;
        }
        .diff-equal {
            color: #999;
        }
        .highlighted {
            box-shadow: 0 0 0 2px #0366d6;
        }
        .word-diff {
            background: rgba(0,0,0,0.1);
            border-radius: 2px;
        }
    </style>
</head>
<body>
    <div class="diff-container">
        <div class="diff-header">
            <h2>Version Comparison</h2>
            <div class="diff-stats">
                <span>Added: {$stats['added']}</span>
                <span>Removed: {$stats['removed']}</span>
                <span>Changed: {$stats['changed']}</span>
                <span>Unchanged: {$stats['equal']}</span>
            </div>
        </div>
        
        <div class="diff-controls">
            <button id="prev-change">Previous Change</button>
            <button id="next-change">Next Change</button>
            <button id="toggle-context">Show Unchanged Lines</button>
        </div>
        
        <div class="diff-view">
            <div class="version-pane old-version">
                <h3>Original Version</h3>
                <div class="content-container">
                    {$oldContentHtml}
                </div>
            </div>

            <div class="version-pane new-version">
                <h3>New Version</h3>
                <div class="content-container">
                    {$newContentHtml}
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const changes = document.querySelectorAll('.diff-change');
        const equalLines = document.querySelectorAll('.diff-equal');
        const toggleBtn = document.getElementById('toggle-context');
        let currentChange = 0;
        let showContext = false;
        
        // Initialize
        if (changes.length > 0) {
            changes[0].scrollIntoView({behavior: 'smooth', block: 'center'});
            changes[0].classList.add('highlighted');
        }
        
        // Navigation between changes
        document.getElementById('next-change').addEventListener('click', function() {
            if (currentChange < changes.length - 1) {
                changes[currentChange].classList.remove('highlighted');
                currentChange++;
                changes[currentChange].classList.add('highlighted');
                changes[currentChange].scrollIntoView({behavior: 'smooth', block: 'center'});
            }
        });
        
        document.getElementById('prev-change').addEventListener('click', function() {
            if (currentChange > 0) {
                changes[currentChange].classList.remove('highlighted');
                currentChange--;
                changes[currentChange].classList.add('highlighted');
                changes[currentChange].scrollIntoView({behavior: 'smooth', block: 'center'});
            }
        });
        
        // Context toggle
        toggleBtn.addEventListener('click', function() {
            showContext = !showContext;
            this.textContent = showContext ? 'Hide Unchanged Lines' : 'Show Unchanged Lines';
            
            equalLines.forEach(line => {
                line.style.display = showContext ? 'block' : 'none';
            });
        });
        
        // Hide context by default
        equalLines.forEach(line => {
            line.style.display = 'none';
        });
    });
    </script>
</body>
</html>
HTML;
}

function calculateDiffStats(array $diff): array {
    $stats = ['added' => 0, 'removed' => 0, 'changed' => 0, 'equal' => 0];
    
    foreach ($diff as $change) {
        switch ($change['type']) {
            case 'insert': $stats['added']++; break;
            case 'delete': $stats['removed']++; break;
            case 'change': $stats['changed']++; break;
            case 'equal': $stats['equal']++; break;
        }
    }
    
    return $stats;
}

function renderDiffContent(array $diff, string $version, bool $isHtml = false): string {
    $output = '';
    
    foreach ($diff as $change) {
        if ($change['type'] === 'equal') {
            $output .= renderDiffLine($change['content'], 'equal', $change['line']);
            continue;
        }
        
        switch ($change['type']) {
            case 'insert':
                if ($version === 'new') {
                    $content = $isHtml ? highlightWordDiffs($change['content'], []) : $change['content'];
                    $output .= renderDiffLine($content, 'insert', $change['line']);
                }
                break;
                
            case 'delete':
                if ($version === 'old') {
                    $content = $isHtml ? highlightWordDiffs($change['content'], []) : $change['content'];
                    $output .= renderDiffLine($content, 'delete', $change['line']);
                }
                break;
                
            case 'change':
                $content = ($version === 'old') ? $change['old_content'] : $change['new_content'];
                if ($isHtml && isset($change['word_diff'])) {
                    $content = highlightWordDiffs($content, $change['word_diff']);
                }
                $output .= renderDiffLine($content, 'change', $change['line']);
                break;
        }
    }
    
    return $output;
}

function renderDiffLine(string $content, string $type, int $lineNum): string {
    return sprintf(
        '
<div class="diff-line diff-%s diff-change" data-line="%d">%s</div>',
        $type,
        $lineNum,
        htmlspecialchars($content)
    );
}

function highlightWordDiffs(string $content, array $wordDiff): string {
    // Simple word highlighting implementation
    $words = preg_split('/(\s+)/', $content);
    $result = [];
    
    foreach ($words as $i => $word) {
        $isChanged = false;
        foreach ($wordDiff as $change) {
            if (($change['type'] === 'delete' || $change['type'] === 'insert') && 
                $change['old_pos'] === $i) {
                $isChanged = true;
                break;
            }
        }
        
        if ($isChanged) {
            $result[] = '
<span class="word-diff">' . htmlspecialchars(
$word) . '</span>';
        }
 else {
            $result[] = htmlspecialchars($word);
        }
    }
    
    return implode(' ', $result);
}
