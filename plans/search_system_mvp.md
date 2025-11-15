# Search System MVP Implementation Plan

## Core Components
1. **IndexBuilder**
   - Scheduled job to rebuild search index
   - Processes only published content (is_published=true)
   - Maintains tenant isolation (WHERE tenant_id=?)
   - Stores processed content in search_index table

2. **SearchEngine**
   - Full-text search capabilities
   - Tenant-aware results filtering
   - Relevance scoring based on:
     - Title match (highest weight)
     - Content match
     - Metadata tags

3. **API Endpoint**
   - `/api/search?q=term&tenant=id`
   - Returns paginated JSON results
   - Basic relevance sorting

## Database Schema
```sql
CREATE TABLE search_index (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id VARCHAR(36) NOT NULL,
  content_id BIGINT NOT NULL,
  title TEXT NOT NULL,
  content TEXT NOT NULL,
  metadata JSON,
  last_indexed TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_tenant (tenant_id),
  FULLTEXT idx_search (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Implementation Phases
1. Phase 1: IndexBuilder + Schema
2. Phase 2: SearchEngine core
3. Phase 3: API endpoint
4. Phase 4: Performance optimizations

## Tenant Isolation
- All queries must include tenant_id filter
- API validates tenant access before searching
- IndexBuilder processes tenants separately

## Version Integration
- Only indexes content_versions where is_published=true
- Re-indexes when published versions change