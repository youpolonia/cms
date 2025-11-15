# Block Editor Test Documentation

## Overview
This test page (`public/test/block-editor.html`) provides a sandbox environment for testing all block editor functionality. It includes all block types, mode toggling, and save/load capabilities.

## Features Tested

### Block Types
- **Text Block**: Basic text input with textarea
- **Image Block**: URL and alt text inputs
- **Video Block**: Embed code input
- **Quote Block**: Quote text and author inputs

### Mode Toggle
- Switch between **Edit** and **Preview** modes
- Active mode is highlighted (blue background)
- Console logs mode changes

### Save/Load Functionality
- **Save**: Stores current blocks to localStorage
- **Load**: Retrieves saved blocks from localStorage
- Console confirms save/load operations

### Debugging
- Console logs all major actions
- Test styles highlight interactive elements
- Added/modified blocks have visual indicators

## Usage Instructions

1. Open `http://localhost:8000/test/block-editor.html` in browser
2. Select a block type from dropdown to add it
3. Toggle between Edit/Preview modes
4. Use Save/Load buttons to test persistence
5. Check browser console for debug logs

## Test Scenarios

1. **Basic Block Operations**
   - Add multiple blocks of different types
   - Verify proper rendering and styling

2. **Mode Switching**
   - Verify UI changes between modes
   - Check console logs for mode changes

3. **Save/Load Cycle**
   - Add blocks and save
   - Refresh page and load
   - Verify blocks restore correctly

4. **Responsive Testing**
   - Resize browser window
   - Verify mobile styles apply below 768px width

## CSS Reference
Test-specific styles are in `public/css/test.css` which extends the base `public/css/editor.css`.

## Troubleshooting
- If blocks don't save/load: check localStorage in dev tools
- If styles look broken: verify both CSS files are loading
- Check console for errors if functionality fails