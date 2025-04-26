# Task 1: Content Version Comparison UI

## Requirements
- Create interface to compare two content versions
- Highlight differences (added/removed/changed content)
- Show side-by-side comparison view
- Include metadata comparison (author, dates, etc.)
- Allow restoring previous versions
- Implement diff algorithm for text comparison
- Support HTML content comparison
- Add keyboard navigation
- Make accessible (WCAG 2.1 compliant)

## Technical Specifications
1. Create new Livewire component: `ContentVersionComparison`
2. Add route: `/content/{content}/versions/compare`
3. Create view: `resources/views/content/versions/compare.blade.php`
4. Implement diffing using:
   - Text: FineDiff library
   - HTML: HtmlDiff
5. Store comparison history in database
6. Add tests for all comparison scenarios

## Acceptance Criteria
- [ ] Users can select two versions to compare
- [ ] Differences are clearly visualized
- [ ] Can restore previous versions
- [ ] Works with all content types
- [ ] Mobile responsive
- [ ] Performance < 500ms for average content