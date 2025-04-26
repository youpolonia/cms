# Theme Versioning System

## Overview
The theme versioning system tracks changes to themes over time, enabling:
- Version history tracking
- Comparison between versions
- Branching for parallel development
- Approval workflows

## Key Components

### ThemeVersion Model
- Represents a specific version of a theme
- Contains version metadata (number, description, changelog)
- Has relationships to branches and approvals
- Stores comparison stats against previous versions

### VersionComparisonService
- Handles comparison logic between theme versions
- Generates statistics about changes (files modified, lines changed)
- Provides visual diff capabilities

### ThemeBranch Model
- Represents parallel development branches
- Each branch maintains its own version history
- Supports merging between branches

## Database Schema
Key tables:
- `theme_versions`
- `theme_branches`
- `theme_version_comparison_stats`

## Version Comparison
The system provides detailed comparison between versions including:
- File-level changes
- Line-by-line diffs
- Statistics on additions/removals
- Quality metrics comparison

## API Endpoints
- `/api/versions` - Manage theme versions
- `/api/versions/compare` - Compare versions
- `/api/branches` - Manage branches

## Views
- `compare.blade.php` - Version comparison interface
- `compare-stats.blade.php` - Detailed comparison statistics
- `history.blade.php` - Version history timeline
