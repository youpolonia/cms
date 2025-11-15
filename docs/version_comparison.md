# Version Comparison System

## Overview
The version comparison system provides:
- Semantic version comparison (SemVer 2.0)
- Text diff visualization
- Change statistics tracking
- Role-based access control

## Components

### 1. Semantic Version Comparator
Location: [`includes/Version/SemanticVersionComparator.php`](includes/Version/SemanticVersionComparator.php)

```php
/**
 * Compares two semantic versions
 * @param string $version1 First version to compare
 * @param string $version2 Second version to compare
 * @return int Returns:
 *   -1 if $version1 < $version2
 *    0 if $version1 == $version2
 *    1 if $version1 > $version2
 */
public function compare($version1, $version2) {
    $v1 = $this->parseVersion($version1);
    $v2 = $this->parseVersion($version2);
    return $this->compareParsedVersions($v1, $v2);
}
```

### 2. Version Diff Component
Location: [`src/Components/VersionDiff.php`](src/Components/VersionDiff.php)

Provides:
- Text diff (plain text)
- HTML-aware diff
- Change statistics

```php
public static function compareText(string $oldText, string $newText, bool $htmlAware = false): array
{
    if ($htmlAware) {
        return self::htmlDiff($oldHtml, $newHtml);
    }
    return self::textDiff($oldText, $newText);
}
```

### 3. Frontend Diff Renderer
Location: [`assets/js/diff.js`](assets/js/diff.js)

Features:
- Side-by-side comparison
- Unified diff view
- Change highlighting

```javascript
class Diff {
    constructor(options = {}) {
        this.options = {
            sideBySide: true,
            htmlAware: false,
            ...options
        };
    }
}
```

## Workflow
1. User requests version comparison via UI
2. Frontend calls `/api/versions/compare/{v1}/{v2}`
3. Backend:
   - Validates versions using `SemanticVersionComparator`
   - Retrieves content for both versions
   - Generates diff using `VersionDiff`
4. Frontend renders results using `Diff` class

## Statistics Tracking
The system tracks:
- Lines added/removed/changed
- Characters added/removed
- Version precedence

## Access Control
Version comparison requires:
- `content.view` permission for both versions
- `versions.compare` permission