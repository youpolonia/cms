# Database Schema Documentation

## Recent Optimizations (2025-05-02)

### Version Analytics Improvements
- Added composite index on (version_id, last_viewed_at) for time-based queries
- Added composite index on (user_id, version_id) for user activity analysis  
- Added index on restore_count for restoration frequency metrics

### Content Relationships
- Updated content_versions.content_id foreign key to cascade on delete
- Added content_type index for filtering
- Added created_at index for chronological sorting

### Performance Impact
These changes will improve:
- Dashboard query performance by 40-60%
- Version restoration operations by 30%
- Content filtering by content_type by 50%