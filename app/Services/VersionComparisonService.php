<?php

namespace App\Services;

use App\Models\ContentVersion;
use App\Models\User;
use Illuminate\Support\Str;
use cebe\markdown\GithubMarkdown;

class VersionComparisonService
{
    public function __construct(
        private GithubMarkdown $markdownParser
    ) {}

    public function compare(
        int $version1Id,
        int $version2Id,
        string $granularity = 'line',
        bool $includeMetadata = true,
        bool $highlightChanges = true
    ): array {
        $version1 = ContentVersion::findOrFail($version1Id);
        $version2 = ContentVersion::findOrFail($version2Id);

        $content1 = $this->normalizeContent($version1->content);
        $content2 = $this->normalizeContent($version2->content);

        $diff = $this->computeDiff($content1, $content2, $granularity, $highlightChanges);

        // Update comparison metrics
        $version1->increment('times_compared');
        $version2->increment('times_compared');

        $result = [
            'version1' => $version1->id,
            'version2' => $version2->id,
            'diff' => $diff,
            'granularity' => $granularity,
            'similarity_score' => $this->calculateSimilarity($content1, $content2),
            'stats' => $this->calculateDiffStats($diff),
        ];

        if ($includeMetadata) {
            $result['metadata'] = [
                'version1' => $version1->only(['version_number', 'created_at', 'user_id']),
                'version2' => $version2->only(['version_number', 'created_at', 'user_id']),
            ];
        }

        return $result;
    }

    public function restoreVersion(ContentVersion $version, User $restoredBy): ContentVersion
    {
        $newVersion = $version->content->versions()->create([
            'content' => $version->content,
            'user_id' => $restoredBy->id,
            'version_number' => $version->content->versions()->count() + 1,
            'change_description' => "Restored from version #{$version->version_number}",
            'restored_from_version_id' => $version->id,
            'is_restored' => true,
            'restored_by' => $restoredBy->id,
            'restored_at' => now(),
        ]);

        $version->increment('restore_count');

        return $newVersion;
    }

    private function normalizeContent(string $content): string
    {
        // Convert markdown to plain text for comparison
        return Str::lower(strip_tags(
            $this->markdownParser->parse($content)
        ));
    }

    private function computeDiff(string $old, string $new, string $granularity, bool $highlight): array
    {
        if ($granularity === 'word') {
            return $this->wordDiff($old, $new, $highlight);
        }

        return $this->lineDiff($old, $new, $highlight);
    }

    private function lineDiff(string $old, string $new, bool $highlight): array
    {
        $oldLines = explode("\n", $old);
        $newLines = explode("\n", $new);
        
        $diff = [];
        $changes = [];
        
        foreach ($oldLines as $i => $line) {
            if (!isset($newLines[$i])) {
                $changes[] = ['type' => 'removed', 'line' => $i, 'content' => $line];
            } elseif ($line !== $newLines[$i]) {
                $changes[] = ['type' => 'modified', 'line' => $i, 'old' => $line, 'new' => $newLines[$i]];
            }
        }
        
        foreach ($newLines as $i => $line) {
            if (!isset($oldLines[$i])) {
                $changes[] = ['type' => 'added', 'line' => $i, 'content' => $line];
            }
        }

        return [
            'old' => $highlight ? $oldLines : null,
            'new' => $highlight ? $newLines : null,
            'changes' => $changes,
            'total_lines' => count($newLines),
            'changed_lines' => count($changes),
        ];
    }

    private function wordDiff(string $old, string $new, bool $highlight): array
    {
        $oldWords = preg_split('/\s+/', $old);
        $newWords = preg_split('/\s+/', $new);
        
        $changes = [];
        $diff = [];
        
        foreach ($oldWords as $i => $word) {
            if (!isset($newWords[$i])) {
                $changes[] = ['type' => 'removed', 'position' => $i, 'word' => $word];
            } elseif ($word !== $newWords[$i]) {
                $changes[] = ['type' => 'modified', 'position' => $i, 'old' => $word, 'new' => $newWords[$i]];
            }
        }
        
        foreach ($newWords as $i => $word) {
            if (!isset($oldWords[$i])) {
                $changes[] = ['type' => 'added', 'position' => $i, 'word' => $word];
            }
        }

        return [
            'old' => $highlight ? $oldWords : null,
            'new' => $highlight ? $newWords : null,
            'changes' => $changes,
            'total_words' => count($newWords),
            'changed_words' => count($changes),
        ];
    }

    private function calculateSimilarity(string $content1, string $content2): float
    {
        similar_text($content1, $content2, $percent);
        return round($percent, 2);
    }

    private function calculateDiffStats(array $diff): array
    {
        $changed = $diff['changed_' . ($diff['granularity'] === 'word' ? 'words' : 'lines')];
        $total = $diff['total_' . ($diff['granularity'] === 'word' ? 'words' : 'lines')];
        
        return [
            'changed' => $changed,
            'total' => $total,
            'change_percentage' => $total > 0 ? round(($changed / $total) * 100, 2) : 0,
            'unchanged' => $total - $changed,
        ];
    }
}
