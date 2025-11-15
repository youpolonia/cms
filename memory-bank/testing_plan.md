# Builder Engine v2.1 Testing Plan

## Test Categories

### 1. Lazy Loading Tests
**Location:** `admin/editor/editor.js`
- **Unit Tests:**
  - Verify IntersectionObserver triggers loadBlock() when elements enter viewport
  - Test threshold configuration (0.1)
  - Verify unobserve() called after loading
- **Integration Tests:**
  - Test with multiple block placeholders
  - Verify performance impact with many elements
- **UI Tests:**
  - Scroll behavior triggers loading
  - Loading state indicators

### 2. AJAX Interface Tests
**Components:** `components/PageBuilder.vue`
**Endpoints:** `/api/page/save`, `/api/page/load/:id`, `/api/block/update`
- **Unit Tests:**
  - Verify axios request structure
  - Test error handling and retry logic
  - Validate request throttling (300ms)
- **Integration Tests:**
  - End-to-end save/load cycle
  - Concurrent edit handling
  - Offline mode behavior
- **API Tests:**
  - Verify response formats
  - Test error responses
  - Validate authentication

### 3. Version Control Tests
**Model:** `models/VersionModel.php`
**UI:** `admin/versioning/VersionHistory.vue`
- **Unit Tests (PHP):**
  - `createVersion()`:
    - Auto-increment logic
    - Data validation
    - Success/failure cases
  - `restoreVersion()`:
    - Data integrity checks
    - Permission validation
- **Integration Tests:**
  - Version creation → restore cycle
  - Concurrent version creation
  - Large content handling
- **UI Tests:**
  - Version browser navigation
  - Diff visualization
  - Rollback confirmation

### 4. AI Integration Tests
**Components:** To be determined
- **Unit Tests:**
  - Prompt validation
  - Response parsing
  - Error handling
- **Integration Tests:**
  - End-to-end generation flow
  - Content validation
  - Performance benchmarks

## Test Data Requirements
- Sample content pages (varying sizes)
- Version history data
- Mock AI responses
- Error scenarios (timeouts, invalid data)

## Test Environment
- Headless browser for UI tests
- Mock server for API tests
- Isolated database for version tests

## Implementation Timeline
1. Unit tests (Week 1)
2. Integration tests (Week 2)
3. UI/API tests (Week 3)
4. Final validation (Week 4)