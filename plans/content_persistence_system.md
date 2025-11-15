# Content Persistence System Implementation Plan

## 1. File Structure
```
/data/
  /pages/                  # Root directory for content storage
    /{content_id}/         # Each content item gets its own directory
      content.json         # Primary content storage (schema below)
      versions/            # Version history directory
        v1.json            # Version 1 content
        v2.json            # Version 2 content
      meta.json            # Metadata for indexing
  /index.json              # Global content index
```

## 2. JSON Schema Specifications

### content.json
```json
{
  "id": "string|required",
  "title": "string|required|max:255",
  "content": {
    "blocks": "array", 
    "html": "string|required"
  },
  "meta": {
    "created": "ISO8601|required",
    "modified": "ISO8601|required",
    "author": "string|required",
    "state": "enum:draft,review,published|required",
    "tenant_id": "string|required"
  },
  "aiContext": "object" 
}
```

### meta.json
```json
{
  "id": "string",
  "title": "string",
  "state": "string",
  "tenant_id": "string",
  "created": "ISO8601",
  "modified": "ISO8601",
  "author": "string",
  "path": "string"
}
```

## 3. API Endpoint Modifications

### Existing Endpoints to Modify:
1. `POST /api/content/save`
   - Save to both DB and filesystem
   - Validate against JSON schema
   - Generate content_id if new

2. `GET /api/content/load/{id}`
   - First check filesystem, fallback to DB
   - Return unified JSON format

3. `GET /api/content/list`
   - Read from /data/index.json
   - Support pagination/filtering

### New Endpoints:
1. `POST /api/content/migrate`
   - Admin-only endpoint
   - Triggers DB-to-files migration

## 4. Validation Rules

| Rule Type          | Implementation                          |
|--------------------|-----------------------------------------|
| Schema Validation  | JSON schema validation library          |
| HTML Sanitization  | DOMDocument with allowed tags list      |
| State Transitions  | Finite state machine validation         |
| File Size          | 1MB limit per content.json              |
| Required Fields    | id, title, content.html, meta fields    |

## 5. Migration Path

### Phase 1: Preparation
1. Create /data/pages directory structure
2. Implement ContentFileHandler class
3. Update API endpoints for dual-write

### Phase 2: Migration
1. Create migration script:
   ```php
   foreach (Content::all() as $content) {
       ContentFileHandler::save($content);
       ContentFileHandler::saveVersions($content->versions);
   }
   ```
2. Run migration via /api/content/migrate
3. Verify data integrity

### Phase 3: Cutover
1. Switch API to read primarily from files
2. Keep DB as backup/audit trail
3. Monitor for 1 week before DB cleanup

## 6. Testing Approach

1. Unit Tests:
   - JSON schema validation
   - File operations
   - State transitions

2. Integration Tests:
   - API endpoint behavior
   - Dual-write consistency
   - Fallback scenarios

3. Migration Tests:
   - DB-to-files migration
   - Data integrity checks
   - Rollback procedure