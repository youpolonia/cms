# Version Control Test Suite

## API Tests
1. **Version Creation**
   - Create version with comment
   - Verify version appears in list
   - Test invalid content ID

2. **Version Listing**  
   - List versions for content
   - Test empty content
   - Verify sort order (newest first)

3. **Version Comparison**
   - Compare two valid versions
   - Test identical versions
   - Test invalid version IDs

4. **Version Restoration**
   - Restore valid version
   - Test restore permission checks
   - Verify content updates

5. **Version Deletion**
   - Delete version
   - Test delete permission checks
   - Verify version removed from list

## UI Tests
1. **Version Table Rendering**
   - Load with multiple versions
   - Empty state handling
   - Pagination (if implemented)

2. **Diff View**
   - Side-by-side comparison
   - Highlighting changes
   - Large content handling

3. **Action Buttons**
   - Compare button functionality
   - Restore confirmation flow
   - Delete confirmation flow

## Test Data
Sample content IDs for testing:
- `test_page_1` - Simple text content
- `test_page_2` - HTML content
- `test_page_3` - Large content (>10KB)