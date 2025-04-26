<?php

namespace App\Services;

use DiffMatchPatch\DiffMatchPatch;
use App\Models\ContentVersion;

class ContentDiffService
{
    public function calculateDiff(ContentVersion $oldVersion, ContentVersion $newVersion): array
    {
        return $this->compare(
            $oldVersion->content,
            $newVersion->content,
            [
                'version_a' => $oldVersion->id,
                'version_b' => $newVersion->id,
                'timestamp' => now()->toIso8601String(),
                'is_autosave_a' => $oldVersion->is_autosave,
                'is_autosave_b' => $newVersion->is_autosave,
                'version_number_a' => $oldVersion->version_number,
                'version_number_b' => $newVersion->version_number,
                'approval_status_a' => $oldVersion->approval_status,
                'approval_status_b' => $newVersion->approval_status,
                'is_approved_a' => $oldVersion->is_approved,
                'is_approved_b' => $newVersion->is_approved,
                'approved_at_a' => $oldVersion->approved_at?->toIso8601String(),
                'approved_at_b' => $newVersion->approved_at?->toIso8601String()
            ]
        );
    }

    public function compare(string $oldContent, string $newContent, array $meta = []): array
    {
        $dmp = new DiffMatchPatch();
        $diffs = $dmp->diff_main($oldContent, $newContent);
        $dmp->diff_cleanupSemantic($diffs);

        $lineDiffs = $this->calculateLineDiffs($oldContent, $newContent);
        $similarity = $this->calculateSimilarity($diffs);
        $stats = $this->calculateStats($diffs);
        $wordStats = $this->calculateWordStats($oldContent, $newContent);
        $semanticGroups = $this->groupSemanticChanges($diffs);

        return array_merge([
            'diffs' => $diffs,
            'line_diffs' => $lineDiffs,
            'stats' => array_merge($stats, $wordStats),
            'similarity' => $similarity,
            'semantic_groups' => $semanticGroups,
            'html' => $this->isHtmlContent($oldContent)
                ? $this->renderHtmlDiffWithTags($diffs)
                : $this->renderHtmlDiff($diffs),
            'line_html' => $this->renderLineHtmlDiff($lineDiffs),
            'semantic_html' => $this->renderSemanticHtmlDiff($semanticGroups)
        ], $meta);
    }

    protected function calculateLineDiffs(string $oldContent, string $newContent): array
    {
        $oldLines = explode("\n", $oldContent);
        $newLines = explode("\n", $newContent);
        
        $dmp = new DiffMatchPatch();
        $diffs = $dmp->diff_main(implode("\n", $oldLines), implode("\n", $newLines));
        $dmp->diff_cleanupSemantic($diffs);
        
        $lineDiffs = [];
        $lineNumber = 1;
        
        foreach ($diffs as $diff) {
            $lines = explode("\n", $diff[1]);
            foreach ($lines as $line) {
                if (!empty($line)) {
                    $lineDiffs[] = [
                        'type' => $diff[0],
                        'line' => $line,
                        'line_number' => $lineNumber++
                    ];
                }
            }
        }
        
        return $lineDiffs;
    }

    protected function calculateSimilarity(array $diffs): float
    {
        $total = 0;
        $equal = 0;
        
        foreach ($diffs as $diff) {
            $length = mb_strlen($diff[1]);
            $total += $length;
            if ($diff[0] === DIFF_EQUAL) {
                $equal += $length;
            }
        }
        
        return $total > 0 ? round(($equal / $total) * 100, 2) : 100;
    }

    protected function renderLineHtmlDiff(array $lineDiffs): string
    {
        $html = '<div class="line-diff-container">';
        foreach ($lineDiffs as $diff) {
            $line = htmlspecialchars($diff['line']);
            $lineNum = $diff['line_number'];
            
            switch ($diff['type']) {
                case DIFF_INSERT:
                    $html .= "<div class=\"diff-line added\" data-line=\"{$lineNum}\">";
                    $html .= "<span class=\"line-number\">+{$lineNum}</span>";
                    $html .= "<span class=\"line-content\">{$line}</span>";
                    $html .= "</div>";
                    break;
                case DIFF_DELETE:
                    $html .= "<div class=\"diff-line removed\" data-line=\"{$lineNum}\">";
                    $html .= "<span class=\"line-number\">-{$lineNum}</span>";
                    $html .= "<span class=\"line-content\">{$line}</span>";
                    $html .= "</div>";
                    break;
                case DIFF_EQUAL:
                    $html .= "<div class=\"diff-line unchanged\" data-line=\"{$lineNum}\">";
                    $html .= "<span class=\"line-number\">{$lineNum}</span>";
                    $html .= "<span class=\"line-content\">{$line}</span>";
                    $html .= "</div>";
                    break;
            }
        }
        $html .= '</div>';
        return $html;
    }

    protected function calculateStats(array $diffs): array
    {
        $stats = [
            'added' => 0,
            'removed' => 0,
            'unchanged' => 0,
            'added_chars' => 0,
            'removed_chars' => 0,
            'unchanged_chars' => 0
        ];

        foreach ($diffs as $diff) {
            $length = mb_strlen($diff[1]);
            switch ($diff[0]) {
                case DIFF_INSERT:
                    $stats['added']++;
                    $stats['added_chars'] += $length;
                    break;
                case DIFF_DELETE:
                    $stats['removed']++;
                    $stats['removed_chars'] += $length;
                    break;
                case DIFF_EQUAL:
                    $stats['unchanged']++;
                    $stats['unchanged_chars'] += $length;
                    break;
            }
        }

        return $stats;
    }

    protected function groupSemanticChanges(array $diffs): array
    {
        $groups = [];
        $currentGroup = null;
        
        foreach ($diffs as $diff) {
            $type = $diff[0];
            $text = $diff[1];
            
            // Start new group if:
            // 1. First diff
            // 2. Type changed from previous
            // 3. Large gap between diffs
            if (!$currentGroup || $currentGroup['type'] !== $type) {
                if ($currentGroup) {
                    $groups[] = $currentGroup;
                }
                $currentGroup = [
                    'type' => $type,
                    'text' => $text,
                    'is_content_change' => $this->isContentChange($text),
                    'is_formatting_change' => $this->isFormattingChange($text)
                ];
            } else {
                $currentGroup['text'] .= $text;
            }
        }
        
        if ($currentGroup) {
            $groups[] = $currentGroup;
        }
        
        return $groups;
    }

    protected function isContentChange(string $text): bool
    {
        // Consider it content if it contains meaningful words
        return preg_match('/\w{3,}/', $text) > 0;
    }

    protected function isFormattingChange(string $text): bool
    {
        // Consider it formatting if it's mostly whitespace/punctuation
        return preg_match('/^[\s\p{P}]+$/u', $text) > 0;
    }

    protected function renderSemanticHtmlDiff(array $groups): string
    {
        $html = [];
        foreach ($groups as $group) {
            $text = htmlspecialchars($group['text']);
            $classes = [];
            
            if ($group['type'] === DIFF_INSERT) $classes[] = 'added';
            if ($group['type'] === DIFF_DELETE) $classes[] = 'deleted';
            if ($group['is_content_change']) $classes[] = 'content-change';
            if ($group['is_formatting_change']) $classes[] = 'formatting-change';
            
            $html[] = sprintf(
                '<span class="%s" data-change-type="%s">%s</span>',
                implode(' ', $classes),
                $group['is_content_change'] ? 'content' : 'formatting',
                $text
            );
        }
        return implode('', $html);
    }

    protected function calculateWordStats(string $oldContent, string $newContent): array
    {
        $oldWords = preg_split('/\s+/', $oldContent);
        $newWords = preg_split('/\s+/', $newContent);
        
        $dmp = new DiffMatchPatch();
        $wordDiffs = $dmp->diff_main(implode(' ', $oldWords), implode(' ', $newWords));
        $dmp->diff_cleanupSemantic($wordDiffs);
        
        $stats = [
            'added_words' => 0,
            'removed_words' => 0,
            'unchanged_words' => 0
        ];
        
        foreach ($wordDiffs as $diff) {
            $words = preg_split('/\s+/', $diff[1]);
            $count = count(array_filter($words));
            
            switch ($diff[0]) {
                case DIFF_INSERT:
                    $stats['added_words'] += $count;
                    break;
                case DIFF_DELETE:
                    $stats['removed_words'] += $count;
                    break;
                case DIFF_EQUAL:
                    $stats['unchanged_words'] += $count;
                    break;
            }
        }
        
        return $stats;
    }

    protected function isHtmlContent(string $content): bool
    {
        return preg_match('/<[a-z][\s\S]*>/i', $content) > 0;
    }

    protected function renderHtmlDiffWithTags(array $diffs): string
    {
        $html = [];
        foreach ($diffs as $diff) {
            $text = htmlspecialchars($diff[1]);
            switch ($diff[0]) {
                case DIFF_INSERT:
                    $html[] = "<ins class=\"html-diff\">{$text}</ins>";
                    break;
                case DIFF_DELETE:
                    $html[] = "<del class=\"html-diff\">{$text}</del>";
                    break;
                case DIFF_EQUAL:
                    $html[] = "<span class=\"html-diff\">{$text}</span>";
                    break;
            }
        }
        return implode('', $html);
    }

    protected function renderHtmlDiff(array $diffs): string
    {
        $html = [];
        foreach ($diffs as $diff) {
            $text = htmlspecialchars($diff[1]);
            switch ($diff[0]) {
                case DIFF_INSERT:
                    $html[] = "<ins>{$text}</ins>";
                    break;
                case DIFF_DELETE:
                    $html[] = "<del>{$text}</del>";
                    break;
                case DIFF_EQUAL:
                    $html[] = $text;
                    break;
            }
        }
        return implode('', $html);
    }
}