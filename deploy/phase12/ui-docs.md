# Version History UI Components

## Version Table
Displays list of versions with actions

**HTML Structure:**
```html
<div class="version-management-container">
  <h2>Version History</h2>
  <table class="version-table">
    <thead>
      <tr>
        <th>Version</th>
        <th>Date</th>
        <th>Author</th>
        <th>Comment</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <!-- Versions populated via JavaScript -->
    </tbody>
  </table>
</div>
```

## Action Buttons
Styled action buttons for version operations

**Classes:**
- `.version-btn.compare` - Green compare button
- `.version-btn.restore` - Blue restore button  
- `.version-btn.delete` - Red delete button

## Diff View
Side-by-side comparison of versions

**Implementation:**
- Uses [`assets/js/diff.js`](assets/js/diff.js) class
- Options:
  - `sideBySide: true` - Shows comparison side-by-side
  - `htmlAware: false` - Treats content as plain text

**Usage:**
```javascript
const diff = new Diff({sideBySide: true});
const result = diff.compare(oldText, newText);
```

## Styling
All styles defined in [`assets/css/version-management.css`](assets/css/version-management.css)