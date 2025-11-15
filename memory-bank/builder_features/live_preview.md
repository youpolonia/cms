# Live Preview System Documentation

## Overview
The live preview system provides real-time visualization of content changes in an iframe overlay. Key features:
- Toggleable preview panel
- Responsive design
- Content caching for performance
- Automatic updates on content changes

## Implementation

### Files
- `admin/editor/preview.js` - Core preview logic
- `admin/editor/preview.css` - Preview styling
- Integrated into `admin/editor/editor.js`

### Key Components
1. **LivePreview Class**
   - Manages iframe creation and updates
   - Implements content caching
   - Handles debounced updates

2. **Preview UI**
   - Fixed position overlay
   - Responsive sizing
   - Toggle and refresh controls

3. **Integration**
   - Added to ContentEditor class
   - Watches block changes via MutationObserver

## Usage
1. Click "Toggle Preview" to show/hide the preview panel
2. Preview updates automatically when content changes
3. Use "Refresh" button to force update if needed

## Technical Notes
- Caching uses content-based keys
- Debounce interval: 300ms
- Maximum cache size: 50 entries
- Works with all block types