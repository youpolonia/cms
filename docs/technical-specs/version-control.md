# Version Control Technical Specifications

## Database Schema Changes

### Extending version_analytics table
```sql
ALTER TABLE version_analytics ADD COLUMN IF NOT EXISTS (
    change_rate DECIMAL(5,2) COMMENT 'Percentage of content changed',
    time_between_versions INT COMMENT 'Seconds between version creations',
    change_complexity ENUM('low', 'medium', 'high') COMMENT 'Estimated complexity of changes',
    semantic_changes JSON COMMENT 'Structured analysis of semantic changes'
);
```

### New tables for version analytics
```sql
CREATE TABLE IF NOT EXISTS version_analytics_metadata (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    version_id BIGINT NOT NULL,
    analysis_type VARCHAR(50) NOT NULL,
    analysis_data JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (version_id) REFERENCES content_versions(id)
);

CREATE TABLE IF NOT EXISTS version_analytics_aggregates (
    content_id BIGINT NOT NULL,
    period_date DATE NOT NULL,
    avg_change_rate DECIMAL(5,2),
    total_versions INT,
    PRIMARY KEY (content_id, period_date),
    FOREIGN KEY (content_id) REFERENCES contents(id)
);
```

## API Endpoint Specifications

### REST Endpoints for Version Control
```
POST /api/v1/version-control/compare
- Request body: { version1_id, version2_id }
- Response: { comparison_id, formatted_diff, change_stats }

POST /api/v1/version-control/restore/{version_id}
- Response: { new_version_id, restored_from_version_id }

GET /api/v1/version-control/analytics/{content_id}
- Query params: ?period=day|week|month
- Response: { 
    change_rate_history: [...],
    version_count_history: [...],
    change_complexity_distribution: {...}
  }

POST /api/v1/version-control/analyze/{version_id}
- Response: { analysis_id, status }
```

## Permission Matrix

| Role               | Compare Versions | Restore Versions | View Analytics | Run Analysis |
|--------------------|------------------|------------------|----------------|--------------|
| Administrator      | ✓                | ✓                | ✓              | ✓            |
| Content Editor     | ✓                | ✓                | ✓              | ✗            |
| Reviewer           | ✓                | ✗                | ✓              | ✗            |
| Analytics Viewer   | ✗                | ✗                | ✓              | ✗            |

## Version Control Algorithms

### Diff/Patch Implementation
```php
// Using php-diff library with custom normalization
public function compareVersions(ContentVersion $v1, ContentVersion $v2) {
    $differ = new Differ();
    return $differ->doDiff(
        $this->normalizeContent($v1->content),
        $this->normalizeContent($v2->content)
    );
}

protected function normalizeContent(string $content): array {
    // Split into lines, trim whitespace, ignore empty lines
    return array_filter(
        array_map('trim', explode("\n", $content)),
        fn($line) => !empty($line)
    );
}
```

### Change Analysis Algorithm
```php
public function analyzeChanges(array $diff): array {
    $stats = [
        'additions' => 0,
        'deletions' => 0,
        'changes' => 0,
        'semantic_changes' => []
    ];
    
    foreach ($diff as $diffOp) {
        if ($diffOp instanceof DiffOpAdd) {
            $stats['additions']++;
            $stats['semantic_changes'][] = $this->analyzeSemanticChange($diffOp->getNewValue());
        } 
        // Similar for other operation types
    }
    
    $stats['change_rate'] = $this->calculateChangeRate($stats);
    return $stats;
}
```

### Version Restoration
```php
public function restoreVersion(ContentVersion $version): ContentVersion {
    $newVersion = $version->replicate();
    $newVersion->version_number = $this->getNextVersionNumber($version->content);
    $newVersion->restored_from_version_id = $version->id;
    $newVersion->save();
    
    // Update content to point to new version
    $version->content()->update([
        'current_version_id' => $newVersion->id
    ]);
    
    return $newVersion;
}