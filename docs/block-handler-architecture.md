# Block Handler Architecture

## Overview
The block handler system provides a structured way to manage different content block types in the CMS editor. Each block type has its own handler class that implements consistent rendering and data handling.

## Core Components

### 1. BaseBlockHandler
- Abstract class defining the interface all blocks must implement
- Required methods:
  - `renderEdit()` - Returns HTML for editor interface
  - `renderPreview()` - Returns HTML for frontend display
  - `serialize()` - Prepares data for storage
  - `deserialize()` - Restores data from storage

### 2. Block Interfaces
- `TextBlockHandler` - Handles plain text/markdown content
- `ImageBlockHandler` - Manages image uploads/embeds
- `VideoBlockHandler` - Handles video embeds from various providers

### 3. BlockRegistry
- Singleton that manages all available block types
- Provides methods to:
  - Register new block handlers
  - Retrieve handlers by type
  - Render blocks in edit/preview modes

### 4. BlockManager (JS)
- Client-side controller for block operations
- Features:
  - Drag/drop reordering
  - Position tracking
  - Event handling

## JSON Schema
Blocks are stored with this structure:
```json
{
  "type": "text|image|video",
  "data": {
    // Block-specific data
  },
  "meta": {
    "id": "unique-id",
    "position": 0
  }
}
```

## Media Sandbox Integration
Media blocks integrate with the media sandbox through:
1. Media picker buttons in edit mode
2. URL validation for external media
3. Thumbnail generation for previews

## Implementation Notes
- No external JS frameworks required
- Pure PHP/JS implementation
- FTP-deployable structure
- Works on shared hosting