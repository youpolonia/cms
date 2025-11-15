<?php

use includes\Diff\TextDiff;
use includes\Diff\HTMLDiff;

class ComparisonController {
    public static function compareVersions(string $version1, string $version2): array {
        $content1 = self::getVersionContent($version1);
        $content2 = self::getVersionContent($version2);
        
        return [
            'oldText' => $content1,
            'newText' => $content2,
            'text_diff' => self::textDiff($content1, $content2),
            'html_diff' => self::htmlDiff($content1, $content2),
            'stats' => self::calculateDiffStats($content1, $content2)
        ];
    }

    private static function getVersionContent(string $version): string {
        $content = @file_get_contents("versions/{$version}.txt");
        return $content !== false ? $content : "Sample content for version $version";
    }

    private static function textDiff(string $oldText, string $newText): string {
        return TextDiff::compare($oldText, $newText);
    }

    private static function htmlDiff(string $oldHtml, string $newHtml): string {
        return HTMLDiff::compare($oldHtml, $newHtml);
    }

    private static function calculateDiffStats(string $oldText, string $newText): array {
        $oldLines = explode("\n", $oldText);
        $newLines = explode("\n", $newText);
        
        $added = count(array_diff($newLines, $oldLines));
        $removed = count(array_diff($oldLines, $newLines));
        $unchanged = count(array_intersect($oldLines, $newLines));
        
        return [
            'added' => $added,
            'removed' => $removed,
            'unchanged' => $unchanged,
            'similarity' => round($unchanged / max(count($oldLines), 1) * 100, 2)
        ];
    }
}
