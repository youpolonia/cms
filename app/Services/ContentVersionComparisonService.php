<?php

namespace App\Services;

use App\Models\ContentVersion;
use DiffMatchPatch\DiffMatchPatch;
use App\Contracts\DiffServiceInterface;

class ContentVersionComparisonService implements DiffServiceInterface
{
    protected DiffMatchPatch $dmp;

    public function __construct()
    {
        $this->dmp = new DiffMatchPatch();
    }

    public function compare(ContentVersion $version1, ContentVersion $version2): array
    {
        return [
            'version_numbers' => [
                'from' => $version1->version_number,
                'to' => $version2->version_number,
            ],
            'content_changes' => $this->compareContentData(
                $version1->content_data ?? [],
                $version2->content_data ?? []
            ),
            'metadata_changes' => $this->compareMetadata($version1, $version2),
            'comparison_metrics' => $this->calculateComparisonMetrics($version1, $version2)
        ];
    }

    public function compareVersions(ContentVersion $oldVersion, ContentVersion $newVersion): array
    {
        return $this->compare($oldVersion, $newVersion);
    }

    public function getSemanticChanges(ContentVersion $oldVersion, ContentVersion $newVersion): array
    {
        $changes = $this->compare($oldVersion, $newVersion);
        $semanticChanges = [];

        foreach ($changes['content_changes'] as $key => $change) {
            if ($change['change'] === 'modified') {
                $semanticChanges[] = "Modified content field: $key";
            } elseif ($change['change'] === 'added') {
                $semanticChanges[] = "Added content field: $key";
            } elseif ($change['change'] === 'removed') {
                $semanticChanges[] = "Removed content field: $key";
            }
        }

        foreach ($changes['metadata_changes'] as $key => $change) {
            if ($key === 'publish_at') {
                $semanticChanges[] = $change['old_value']
                    ? "Changed publish schedule from {$change['old_value']} to {$change['new_value']}"
                    : "Set publish schedule to {$change['new_value']}";
            } elseif ($key === 'expire_at') {
                $semanticChanges[] = $change['old_value']
                    ? "Changed expiration schedule from {$change['old_value']} to {$change['new_value']}"
                    : "Set expiration schedule to {$change['new_value']}";
            } else {
                $semanticChanges[] = "Changed metadata: $key";
            }
        }

        return $semanticChanges;
    }

    public function generateHtmlDiff(ContentVersion $oldVersion, ContentVersion $newVersion): array
    {
        return $this->generateHtmlDiffInternal(
            $oldVersion->content_data['html'] ?? '',
            $newVersion->content_data['html'] ?? ''
        );
    }

    public function generateCssDiff(ContentVersion $oldVersion, ContentVersion $newVersion): array
    {
        return $this->generateHtmlDiffInternal(
            $oldVersion->content_data['css'] ?? '',
            $newVersion->content_data['css'] ?? ''
        );
    }

    public function generateJsDiff(ContentVersion $oldVersion, ContentVersion $newVersion): array
    {
        return $this->generateHtmlDiffInternal(
            $oldVersion->content_data['js'] ?? '',
            $newVersion->content_data['js'] ?? ''
        );
    }

    protected function compareContentData(array $content1, array $content2): array
    {
        $changes = [];
        $allKeys = array_unique(array_merge(
            array_keys($content1),
            array_keys($content2)
        ));

        foreach ($allKeys as $key) {
            $value1 = $content1[$key] ?? null;
            $value2 = $content2[$key] ?? null;

            if (!array_key_exists($key, $content1)) {
                $changes[$key] = [
                    'change' => 'added',
                    'new_value' => $value2,
                ];
            } elseif (!array_key_exists($key, $content2)) {
                $changes[$key] = [
                    'change' => 'removed',
                    'old_value' => $value1,
                ];
            } elseif ($this->isDifferent($value1, $value2)) {
                $changes[$key] = $this->getDetailedChange($key, $value1, $value2);
            }
        }

        return $changes;
    }

    protected function isDifferent($value1, $value2): bool
    {
        if (is_array($value1) && is_array($value2)) {
            return json_encode($value1) !== json_encode($value2);
        }
        return $value1 !== $value2;
    }

    protected function getDetailedChange(string $key, $oldValue, $newValue): array
    {
        $change = [
            'change' => 'modified',
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ];

        if (is_string($oldValue) && is_string($newValue)) {
            $change['diff'] = $this->generateTextDiff($oldValue, $newValue);
            $diffResult = $this->generateHtmlDiffInternal($oldValue, $newValue);
            $change['html_diff'] = $diffResult['html_diff'];
            $change['left_diff'] = $diffResult['left_diff'];
            $change['right_diff'] = $diffResult['right_diff'];
        }

        return $change;
    }

    protected function generateTextDiff(string $text1, string $text2): array
    {
        $diffs = $this->dmp->diff_main($text1, $text2);
        $this->dmp->diff_cleanupSemantic($diffs);
        return $diffs;
    }

    protected function generateHtmlDiffInternal(string $html1, string $html2): array
    {
        $diffs = $this->dmp->diff_main($html1, $html2);
        $this->dmp->diff_cleanupSemantic($diffs);
        
        $leftHtml = '';
        $rightHtml = '';
        
        foreach ($diffs as $diff) {
            $text = htmlspecialchars($diff[1]);
            
            if ($diff[0] === 0) { // Equal
                $leftHtml .= $text;
                $rightHtml .= $text;
            } elseif ($diff[0] === -1) { // Deleted
                $leftHtml .= '<span class="diff-deleted">'.$text.'</span>';
            } elseif ($diff[0] === 1) { // Inserted
                $rightHtml .= '<span class="diff-inserted">'.$text.'</span>';
            }
        }
        
        return [
            'left_diff' => $leftHtml,
            'right_diff' => $rightHtml,
            'html_diff' => $this->dmp->diff_prettyHtml($diffs) // Keep backward compatibility
        ];
    }

    protected function compareMetadata(ContentVersion $version1, ContentVersion $version2): array
    {
        $changes = [];
        
        $fields = [
            'is_autosave', 'user_id', 'approval_status',
            'change_description', 'approved_at', 'approved_by',
            'reviewed_at', 'reviewed_by', 'rejection_reason'
        ];
        
        foreach ($fields as $field) {
            if ($version1->$field !== $version2->$field) {
                $changes[$field] = [
                    'old_value' => $version1->$field,
                    'new_value' => $version2->$field,
                ];
            }
        }

        return $changes;
    }

    protected function calculateComparisonMetrics(ContentVersion $version1, ContentVersion $version2): array
    {
        $metrics = [];
        $content1 = json_encode($version1->content_data ?? []);
        $content2 = json_encode($version2->content_data ?? []);
        
        $metrics['similarity_score'] = $this->calculateSimilarity($content1, $content2);
        $metrics['change_percentage'] = $this->calculateChangePercentage($content1, $content2);
        
        return $metrics;
    }

    protected function calculateSimilarity(string $text1, string $text2): float
    {
        $diffs = $this->dmp->diff_main($text1, $text2);
        $this->dmp->diff_cleanupSemantic($diffs);
        
        $common = 0;
        $total = strlen($text1) + strlen($text2);
        
        foreach ($diffs as $diff) {
            if ($diff[0] === 0) { // DIFF_EQUAL
                $common += strlen($diff[1]) * 2;
            }
        }
        
        return $total > 0 ? ($common / $total) : 1.0;
    }

    protected function calculateChangePercentage(string $text1, string $text2): float
    {
        $diffs = $this->dmp->diff_main($text1, $text2);
        $this->dmp->diff_cleanupSemantic($diffs);
        
        $changes = 0;
        $total = max(strlen($text1), strlen($text2));
        
        foreach ($diffs as $diff) {
            if ($diff[0] !== 0) { // DIFF_INSERT or DIFF_DELETE
                $changes += strlen($diff[1]);
            }
        }
        
        return $total > 0 ? ($changes / $total) : 0.0;
    }
    public function compareFiles(ContentVersion $oldVersion, ContentVersion $newVersion, array $filePaths = []): array
    {
        throw new \BadMethodCallException('File comparison not supported for ContentVersion');
    }
}