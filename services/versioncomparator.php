<?php
class VersionComparator {
    private $db;
    private $diffRenderer;

    public function __construct() {
        require_once __DIR__.'/../core/database.php';
        require_once __DIR__ . '/../includes/diffrenderer.php';
        $this->db = \core\Database::connection();
        $this->diffRenderer = new DiffRenderer();
    }

    /**
     * Compare two versions and generate diff results
     * 
     * @param int $version1Id First version ID
     * @param int $version2Id Second version ID
     * @return array Comparison results
     */
    public function compareVersions($version1Id, $version2Id) {
        // Get version content
        $version1 = $this->getVersionContent($version1Id);
        $version2 = $this->getVersionContent($version2Id);
        
        if (!$version1 || !$version2) {
            throw new Exception("One or both versions not found");
        }
        
        // Generate diff
        $diffResult = $this->generateDiff($version1['body'], $version2['body']);
        
        // Store diff result
        $this->storeDiffResult($version1Id, $version2Id, $diffResult);
        
        return $diffResult;
    }
    
    /**
     * Get version content by ID
     *
     * @param int $versionId Version ID
     * @return array|false Version data or false if not found
     */
    public function getVersionContent($versionId) {
        return $this->db->query("
            SELECT * FROM content_versions 
            WHERE id = ?
        ", [$versionId])->fetch();
    }
    
    /**
     * Generate diff between two content versions
     * 
     * @param string $oldContent Old content
     * @param string $newContent New content
     * @return array Diff result with changes and stats
     */
    private function generateDiff($oldContent, $newContent) {
        // Use DiffRenderer to get basic diff
        $basicDiff = $this->diffRenderer->compareTexts($oldContent, $newContent);
        
        // Process diff to generate structured changes
        $changes = $this->processChanges($basicDiff);
        
        // Calculate statistics
        $stats = $this->calculateStats($changes);
        
        return [
            'changes' => $changes,
            'stats' => $stats,
            'raw_diff' => $basicDiff
        ];
    }
    
    /**
     * Process basic diff to generate structured changes
     * 
     * @param array $basicDiff Basic diff from DiffRenderer
     * @return array Structured changes
     */
    private function processChanges($basicDiff) {
        $changes = [];
        $position = 0;
        
        // Process side-by-side diff to generate changes
        foreach ($basicDiff['side_by_side'] as $index => $line) {
            if ($line['type'] === 'added') {
                $changes[] = [
                    'type' => 'insert',
                    'content' => $line['new_line'],
                    'position' => $position
                ];
            } elseif ($line['type'] === 'removed') {
                $changes[] = [
                    'type' => 'delete',
                    'content' => $line['old_line'],
                    'position' => $position
                ];
            } elseif ($line['old_line'] !== $line['new_line']) {
                $changes[] = [
                    'type' => 'change',
                    'content' => [
                        'old' => $line['old_line'],
                        'new' => $line['new_line']
                    ],
                    'position' => $position
                ];
            }
            
            $position++;
        }
        
        return $changes;
    }
    
    /**
     * Calculate statistics for the diff
     * 
     * @param array $changes Structured changes
     * @return array Statistics
     */
    private function calculateStats($changes) {
        $charsAdded = 0;
        $charsRemoved = 0;
        $linesChanged = count($changes);
        
        foreach ($changes as $change) {
            if ($change['type'] === 'insert') {
                $charsAdded += strlen($change['content']);
            } elseif ($change['type'] === 'delete') {
                $charsRemoved += strlen($change['content']);
            } elseif ($change['type'] === 'change') {
                $charsAdded += strlen($change['content']['new']);
                $charsRemoved += strlen($change['content']['old']);
            }
        }
        
        return [
            'chars_added' => $charsAdded,
            'chars_removed' => $charsRemoved,
            'lines_changed' => $linesChanged
        ];
    }
    
    /**
     * Store diff result in the database
     * 
     * @param int $version1Id First version ID
     * @param int $version2Id Second version ID
     * @param array $diffResult Diff result
     * @return int|false Inserted ID or false on failure
     */
    private function storeDiffResult($version1Id, $version2Id, $diffResult) {
        return $this->db->insert("version_diffs", [
            'version_id' => $version1Id,
            'compared_to' => $version2Id,
            'diff_content' => json_encode($diffResult),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get HTML-aware diff for rich content
     * 
     * @param string $oldHtml Old HTML content
     * @param string $newHtml New HTML content
     * @return array HTML-aware diff
     */
    public function getHtmlDiff($oldHtml, $newHtml) {
        // For HTML content, we need to be more careful with the diff
        // First, normalize the HTML to make comparison more reliable
        $oldNormalized = $this->normalizeHtml($oldHtml);
        $newNormalized = $this->normalizeHtml($newHtml);
        
        // Then generate diff on normalized HTML
        $diffResult = $this->generateDiff($oldNormalized, $newNormalized);
        
        // Add HTML-specific metadata
        $diffResult['is_html'] = true;
        
        return $diffResult;
    }
    
    /**
     * Normalize HTML for more reliable comparison
     * 
     * @param string $html HTML content
     * @return string Normalized HTML
     */
    private function normalizeHtml($html) {
        // Remove excessive whitespace
        $html = preg_replace('/\s+/', ' ', $html);
        
        // Normalize self-closing tags
        $html = preg_replace('/<([a-z]+)([^>]*)\/>/i', '<$1$2></$1>', $html);
        
        return trim($html);
    }
    /**
     * Restore a previous version of content
     *
     * @param int $versionId Version ID to restore
     * @param int $userId User ID performing the restoration
     * @return array|false Restored version data or false on failure
     */
    public function restoreVersion($versionId, $userId) {
        try {
            // Get the version to restore
            $version = $this->getVersionContent($versionId);
            if (!$version) {
                throw new Exception("Version not found");
            }

            // Create new version based on the restored one
            $newVersionId = $this->db->insert("content_versions", [
                'content_id' => $version['content_id'],
                'version_number' => $this->getNextVersionNumber($version['content_id']),
                'title' => $version['title'] . " (Restored)",
                'body' => $version['body'],
                'author_id' => $userId,
                'tenant_id' => $version['tenant_id'],
                'is_current' => true
            ]);

            // Mark previous current version as not current
            $this->db->update(
                "content_versions",
                ['is_current' => false],
                ['content_id' => $version['content_id'], 'is_current' => true]
            );

            return $this->getVersionContent($newVersionId);
        } catch (Exception $e) {
            error_log("Version restoration failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get next version number for content
     *
     * @param int $contentId Content ID
     * @return int Next version number
     */
    private function getNextVersionNumber($contentId) {
        $result = $this->db->query("
            SELECT MAX(version_number) as max_version
            FROM content_versions
            WHERE content_id = ?
        ", [$contentId])->fetch();

        return ($result['max_version'] ?? 0) + 1;
    }

    /**
     * Merge two versions of content
     *
     * @param int $baseVersionId Base version ID
     * @param int $otherVersionId Other version ID to merge
     * @param int $userId User ID performing the merge
     * @param array $mergeOptions Merge configuration options
     * @return array|false Merged version data or false on failure
     */
    public function mergeVersions($baseVersionId, $otherVersionId, $userId, $mergeOptions = []) {
        try {
            $baseVersion = $this->getVersionContent($baseVersionId);
            $otherVersion = $this->getVersionContent($otherVersionId);

            if (!$baseVersion || !$otherVersion) {
                throw new Exception("One or both versions not found");
            }

            // Compare versions to get differences
            $diff = $this->compareVersions($baseVersionId, $otherVersionId);

            // Apply merge strategy based on options
            $mergedContent = $this->applyMergeStrategy(
                $baseVersion['body'],
                $otherVersion['body'],
                $diff,
                $mergeOptions
            );

            // Create new merged version
            $newVersionId = $this->db->insert("content_versions", [
                'content_id' => $baseVersion['content_id'],
                'version_number' => $this->getNextVersionNumber($baseVersion['content_id']),
                'title' => "Merged: v{$baseVersion['version_number']} + v{$otherVersion['version_number']}",
                'body' => $mergedContent,
                'author_id' => $userId,
                'tenant_id' => $baseVersion['tenant_id'],
                'is_current' => true
            ]);

            // Mark previous current version as not current
            $this->db->update(
                "content_versions",
                ['is_current' => false],
                ['content_id' => $baseVersion['content_id'], 'is_current' => true]
            );

            return $this->getVersionContent($newVersionId);
        } catch (Exception $e) {
            error_log("Version merge failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Apply merge strategy to combine versions
     *
     * @param string $baseContent Base version content
     * @param string $otherContent Other version content
     * @param array $diff Diff result between versions
     * @param array $options Merge options
     * @return string Merged content
     */
    public function applyMergeStrategy($baseContent, $otherContent, $diff, $options) {
        $baseLines = explode("\n", $baseContent);
        $otherLines = explode("\n", $otherContent);
        $mergedLines = [];

        // Default merge strategy: prefer base version with manual conflict markers
        foreach ($diff['changes'] as $change) {
            if ($change['type'] === 'insert') {
                // Optionally require_once additions from other version
                if ($options['include_additions'] ?? true) {
                    $mergedLines[] = $otherLines[$change['position']];
                }
            } elseif ($change['type'] === 'delete') {
                // Optionally exclude deletions from base version
                if ($options['exclude_deletions'] ?? false) {
                    continue;
                }
                $mergedLines[] = $baseLines[$change['position']];
            } else {
                // For changes, require_once conflict markers
                $mergedLines[] = "<<<<<<< BASE";
                $mergedLines[] = $change['content']['old'];
                $mergedLines[] = "=======";
                $mergedLines[] = $change['content']['new'];
                $mergedLines[] = ">>>>>>> OTHER";
            }
        }

        return implode("\n", $mergedLines);
    }
}
