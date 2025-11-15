# Version Control Module Documentation

## Database Schema

### content_versions Table
```sql
CREATE TABLE content_versions (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    content_id INTEGER NOT NULL,
    version_number INTEGER NOT NULL,
    content_data TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INTEGER,
    is_current BOOLEAN DEFAULT FALSE,
    change_reason TEXT,
    is_major BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
    INDEX idx_content_versions_content_id (content_id),
    INDEX idx_content_versions_version (content_id, version_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## API Reference

### VersionDiff Class
```php
/**
 * Compares text versions and generates diffs
 */
class VersionDiff {
    /**
     * Compare two text versions
     * @param string $oldText Original content
     * @param string $newText Modified content 
     * @param bool $htmlAware Whether to parse HTML tags (default: false)
     * @return array Diff results including:
     *   - unified_diff: Standard unified diff format
     *   - side_by_side: HTML table with side-by-side comparison
     *   - stats: Change statistics (lines added/removed/changed)
     */
    public function compareText(string $oldText, string $newText, bool $htmlAware = false): array;
}
```

### VersionComparator Class
```php
/**
 * Handles version comparison and restoration
 */
class VersionComparator {
    /**
     * Compare two versions by ID
     * @param int $version1Id First version ID
     * @param int $version2Id Second version ID
     * @param PDO $pdo Database connection
     * @return array Diff results (same format as VersionDiff)
     */
    public function compare(int $version1Id, int $version2Id, PDO $pdo): array;
    
    /**
     * Restore a specific version
     * @param int $versionId Version ID to restore
     * @param PDO $pdo Database connection
     * @return string The restored content
     * @throws RuntimeException If version not found
     */
    public function restoreVersion(int $versionId, PDO $pdo): string;
}
```

## Usage Examples

### Comparing Versions
```php
$diff = $versionComparator->compare($version1Id, $version2Id, $pdo);
echo $diff['unified_diff']; // Standard diff output
echo $diff['side_by_side']; // HTML comparison
print_r($diff['stats']);    // Change statistics
```

### Creating New Version
```php
$stmt = $pdo->prepare("
    INSERT INTO content_versions 
    (content_id, version_number, content_data, created_by) 
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$contentId, $nextVersion, $contentData, $userId]);
```

### Restoring Version
```php
try {
    $content = $versionComparator->restoreVersion($versionId, $pdo);
    // Use restored content...
} catch (RuntimeException $e) {
    // Handle version not found
}
```

## Integration Guide

1. Initialize components:
```php
$pdo = Database::getConnection();
$versionDiff = new VersionDiff();
$versionComparator = new VersionComparator($versionDiff);
```

2. Track content changes:
```php
// When saving content:
$nextVersion = getNextVersionNumber($contentId);
saveNewVersion($contentId, $nextVersion, $content, $userId);
```

3. Compare versions:
```php
// Get version IDs from your application logic
$diff = $versionComparator->compare($oldVersionId, $newVersionId, $pdo);
```

4. Handle version restoration:
```php
// In your admin interface
$restoredContent = $versionComparator->restoreVersion($versionId, $pdo);
updateCurrentContent($contentId, $restoredContent);