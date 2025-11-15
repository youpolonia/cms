# Version Comparison API Reference

## VersionComparator Class

### `compare(html1: string, html2: string): string`
Compares two HTML strings and returns unified diff output.

**Parameters**:
- `html1`: First HTML version to compare
- `html2`: Second HTML version to compare

**Returns**: 
String in unified diff format showing changes

**Example**:
```php
$comparator = new VersionComparator();
$diff = $comparator->compare('<div>v1</div>', '<div>v2</div>');
// Returns:
// <div>
// - v1
// + v2
// </div>
```

## SemanticVersionComparator Class

### `compare(version1: string, version2: string): int`
Compares two semantic version strings.

**Parameters**:
- `version1`: First version string (e.g. "1.2.3")
- `version2`: Second version string (e.g. "2.0.0-beta")

**Returns**:
- `-1` if version1 < version2
- `0` if version1 == version2  
- `1` if version1 > version2

**Throws**:
- `Exception` for invalid version formats

**Example**:
```php
$comparator = new SemanticVersionComparator();
$result = $comparator->compare('1.2.3', '2.0.0'); // Returns -1
```

## VersionComparisonView Class (JavaScript)

### `constructor(containerId: string, version1: string, version2: string)`
Creates new comparison view instance.

**Parameters**:
- `containerId`: DOM element ID to render into
- `version1`: First content version
- `version2`: Second content version

### `render(): void`
Renders the comparison output into the container.

**Example**:
```javascript
const view = new VersionComparisonView('diff-container', 'v1', 'v2');
view.render();