# /includes/ Directory Structure Analysis

## Current Issues

1. **Case Sensitivity Duplicates** (26 pairs):
   - Core functionality duplicated (Core/ vs core/)
   - API endpoints scattered (Api/, API/, api/)
   - Auth system split (Auth/ vs auth/)

2. **File Organization Problems**:
   - Flat files mixed with class-based folders
   - Auth.php exists in both /includes/Core/ and /includes/auth/
   - Config.php vs config.php case sensitivity issue

3. **Redundant Structures**:
   - Multiple auth implementations
   - Duplicate utility folders
   - Theme-related folders split (Theme/ vs Themes/)

## Recommended Consolidation

1. **Standardize Naming** (lowercase recommended):
   ```mermaid
   graph TD
     A[Current] --> B[Proposed]
     A -->|Core/| B(core/)
     A -->|Auth/| B(auth/)
     A -->|API/| B(api/)
   ```

2. **Merge Directories**:
   - Merge Core/ into core/ (87 files)
   - Combine Auth/ and auth/ implementations
   - Consolidate utility folders

3. **File Organization**:
   - Move all class files into appropriate namespaced folders
   - Keep flat files only for:
     - config.php (entry points)
     - utilities.php (global helpers)

## Implementation Plan

1. **Phase 1 - Standardization**:
   - Apply folder_rename_map.md changes
   - Add remaining lowercase conversions

2. **Phase 2 - Consolidation**:
   - Merge auth implementations
   - Combine utility folders
   - Remove orphaned files

3. **Phase 3 - Validation**:
   - Update all references
   - Test case sensitivity
   - Verify FTP deployment

## Special Considerations
- Maintain backward compatibility during transition
- Update all require_once statements
- Document changes in decisionLog.md