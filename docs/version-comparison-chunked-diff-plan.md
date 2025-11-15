# Server-Side Chunked Diff Implementation

## Problem Statement
The current version comparison system fails with "maximum context length" errors when processing large content versions. This document outlines the implemented solution using server-side chunked diff processing.

## Solution Overview
Implemented chunked processing of content versions to:
1. Avoid memory limits (10,000 character chunks)
2. Enable progressive loading
3. Maintain accurate line-level comparisons
4. Provide aggregated stats

## Technical Implementation

### Backend Architecture
Uses dedicated ContentDiffController with ChunkedDiffService

#### API Endpoints
```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function() {
    Route::post('/content-diff/init', [ContentDiffController::class, 'initChunkedDiff']);
    Route::get('/content-diff/chunk/{n}', [ContentDiffController::class, 'getDiffChunk']);
    Route::get('/content-diff/stats/{id}', [ContentDiffController::class, 'getDiffStats']);
});
```

#### Cache-Based Processing
- Uses Laravel Cache for metadata storage
- 24-hour TTL for diff sessions
- UUID comparison identifiers
- No database chunk storage - processes content on demand

Service Methods:
- `initComparison(version1_id, version2_id)`
- `getChunk(comparison_id, chunk_number)`
- `getStats(comparison_id)`

### Frontend Integration
```javascript
// Example frontend usage
async function loadDiff(version1Id, version2Id) {
  // Initialize comparison
  const { comparison_id, total_chunks } = await api.post('/content-diff/init', {
    version1_id: version1Id,
    version2_id: version2Id
  });

  // Process chunks
  for (let i = 0; i < total_chunks; i++) {
    const chunk = await api.get(`/content-diff/chunk/${i}`, {
      params: { comparison_id }
    });
    renderDiffChunk(chunk);
  }

  // Get final stats
  const stats = await api.get(`/content-diff/stats/${comparison_id}`);
  renderStats(stats);
}
```

## Deployment Phases
1. **Phase 1**: Add new chunked endpoints alongside existing
2. **Phase 2**: Background job to pre-chunk existing content
3. **Phase 3**: UI updates for progressive loading
4. **Phase 4**: Monitor and optimize chunk sizes
5. **Phase 5**: Deprecate legacy comparison system

## Performance Considerations
- Optimal chunk size: 10,000 characters
- Parallel chunk loading: Max 3 concurrent requests
- Caching strategy: 24-hour cache for chunked diffs

## Rollback Plan
1. Maintain legacy endpoints during transition
2. Feature flag for chunked processing
3. Database migrations are reversible