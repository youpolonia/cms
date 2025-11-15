# Custom Blocks System Documentation

## Overview
The custom blocks system allows users to:
1. Save frequently used content as reusable blocks
2. Insert saved blocks into any page
3. Manage block metadata (name, icon, description)

## Directory Structure
```
/data/custom-blocks/
├── blocks/       # Stores HTML content of blocks
└── metadata/     # Stores JSON metadata for blocks
```

## API Endpoints
- `GET /api/blocks` - List all blocks
- `GET /api/blocks?name={blockName}` - Get specific block
- `POST /api/blocks` - Save new block

## JavaScript Integration
The `CustomBlocksUI` class provides:
- "Save as Block" button
- Blocks dropdown selector
- Methods for loading/inserting blocks

## Usage Example
```javascript
// Initialize in builder
const blocksUI = new CustomBlocksUI(editor);
```

## PHP Classes
`BlockManager` handles:
- Saving blocks to filesystem
- Loading blocks
- Managing metadata