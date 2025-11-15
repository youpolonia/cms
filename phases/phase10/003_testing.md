# Phase 10 - Content Management Test Documentation

## Test Scenarios

### 1. CRUD Operations
- **Content Creation**
  - Validates required fields (title, content, status)
  - Returns success status and content ID
  - Sample data: `{'title':'Test','content':'Test content','status':'draft'}`

- **Content Retrieval**
  - Single content fetch by ID
  - Content list retrieval
  - Validates response structure

- **Content Update**  
  - Field validation
  - Status code verification
  - Sample update data: `{'title':'Updated','content':'New content','status':'published'}`

- **Content Deletion**
  - Success status verification
  - Post-deletion validation

### 2. Version Control
- **Version Creation**
  - Returns version ID
  - Maintains content integrity

- **Version Comparison**
   - Identical version comparison
   - Different version comparison
   - Detailed diff analysis (added/removed/changed content)
   - Large content comparison (10k+ characters)
   - Special character handling

- **Version Rollback**
  - Restores previous state
  - Maintains version history

### 3. State Transitions
- Valid transitions:
  - Draft → Published
  - Published → Archived 
  - Archived → Draft
- Invalid transition handling
   - Direct invalid transitions (Draft → Archived)
   - Multiple invalid steps (Draft → Published → Draft)
   - Transition with invalid data
   - Concurrent transition attempts

## Validation Criteria

| Test Case | Success Criteria | Failure Criteria |
|-----------|------------------|------------------|
| Create | Returns 200 with content ID | Missing fields return 400 |
| Read | Returns 200 with content | Invalid ID returns 404 |
| Update | Returns 200 with updated data | Invalid data returns 400 |
| Delete | Returns 200 success | Non-existent ID returns 404 |
| Versioning | Maintains data integrity | Failed operations return proper errors |
| State Change | Enforces valid transitions | Invalid transitions return 400 |
| Content Size | Handles large content (10MB+) | Rejects oversized content (20MB+) |
| Concurrent Updates | Handles simultaneous edits | Returns proper conflict errors |
| Data Validation | Accepts valid special chars | Rejects malformed/invalid data |

## Sample Test Data

```json
{
  "create": {
    "valid": {
      "title": "Test Content",
      "content": "Sample content",
      "status": "draft"
    },
    "invalid": {
      "title": "",
      "content": "Missing title",
      "status": "invalid"
    }
  },
  "version": {
    "compare": {
      "version1": "v1.0",
      "version2": "v1.1" 
    }
  },
  "concurrent": {
    "user1": {
      "title": "User 1 Edit",
      "content": "First concurrent edit"
    },
    "user2": {
      "title": "User 2 Edit",
      "content": "Second concurrent edit"
    }
  },
  "validation": {
    "special_chars": {
      "title": "Test & Content",
      "content": "Special: \n\t\\\"'{}[]()"
    },
    "invalid": {
      "title": "XSS<script>alert(1)</script>",
      "content": "SQL' OR 1=1--"
    }
  }
}
}

## Test Execution
1. Run via CLI: `php tests/api/v1/ContentTest.php`
2. Web endpoint: `/api/test/content` (POST with test case parameter)
3. Expected output: JSON with test results
## Additional Test Cases

### 4. Content Deletion Tests
- **Permanent deletion verification**
  - Content removal from database
  - Associated version history cleanup
- **Soft deletion validation**
  - Content marked as deleted but retained
  - Restoration capability
- **Bulk deletion handling**
  - Multiple content items deletion
  - Partial success scenarios

### 5. Version Control Tests
- **Version creation edge cases**
  - Empty content versions
  - Identical version creation prevention
- **Version comparison validation**
  - Side-by-side diff visualization
  - Change percentage calculation
- **Rollback verification**
  - Data integrity after rollback
  - Version history maintenance

### 6. State Transition Validation  
- **Workflow enforcement**
  - Required fields for transitions
  - Approval requirements
- **Transition logging**
  - Audit trail creation
  - User attribution
- **Concurrent transition handling**
  - Locking mechanisms
  - Conflict resolution

### 7. Large Content Handling
- **Performance testing**
  - 10MB+ content processing
  - Memory usage monitoring
- **Storage validation**
  - Database field limits
  - Filesystem storage checks
- **Transfer testing**
  - Chunked uploads/downloads
  - Compression verification

### 8. Concurrent Update Handling
- **Optimistic locking**
  - Version stamp validation
  - Conflict detection
- **Pessimistic locking**
  - Edit lock acquisition
  - Timeout handling
- **Merge strategies**
  - Automatic merging
  - Manual conflict resolution

### 9. Special Character Validation
- **Input sanitization**
  - HTML/script tag stripping
  - SQL injection prevention
- **Encoding handling**
  - UTF-8 support
  - Emoji/unicode validation
- **Output escaping**
  - XSS prevention
  - Safe rendering