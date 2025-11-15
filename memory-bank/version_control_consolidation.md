# Version Control System Consolidation Plan

## Current State Analysis
- Multiple implementations exist (phases 11-15)
- Phase 15 (`2025_phase15_create_version_storage_tables.php`) has most complete feature set
- Core functionality consistent across versions:
  - Content version tracking
  - Metadata storage
  - Basic version comparison

## Standardization Plan
1. **Base Schema**: Adopt Phase 15 implementation as standard
```sql
CREATE TABLE content_versions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  content_id INT NOT NULL,
  version INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  data LONGTEXT NOT NULL,
  UNIQUE KEY (content_id, version)
```

2. **Migration Path**:
- Create migration to consolidate all version tables
- Preserve existing data through INSERT...SELECT
- Maintain backward compatibility during transition

3. **Feature Roadmap**:
- Implement branching functionality
- Complete approval workflows
- Enhance version comparison tools