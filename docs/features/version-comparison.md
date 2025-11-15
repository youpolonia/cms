# Version Comparison Feature Guide

## Overview
The version comparison feature allows users to:
- Compare different versions of HTML content
- View semantic version differences
- See side-by-side changes with visual indicators

## Usage

### HTML Content Comparison
1. Initialize the comparison view:
```javascript
const view = new VersionComparisonView('container-id', html1, html2);
view.render();
```

2. The output will show:
- `+` for added lines
- `-` for removed lines
- No marker for unchanged lines

### Semantic Version Comparison
The system automatically detects and compares semantic versions (e.g., 1.2.3 vs 2.0.0).

## Interface Components
1. **Comparison View**: Shows the diff output
2. **Version Navigation**: Allows switching between versions
3. **Version Stats**: Displays comparison statistics

## Example
```javascript
// Compare two HTML versions
const html1 = `<div>Version 1</div>`;
const html2 = `<div>Version 2</div>`;
const view = new VersionComparisonView('diff-container', html1, html2);
view.render();
```

## Output Format
The comparison uses unified diff format:
```
<div>
- Version 1
+ Version 2
</div>