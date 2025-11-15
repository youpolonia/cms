# Builder Engine v2.1 Documentation

## Overview
The Builder Engine provides content editing capabilities with:
- Performance optimized rendering
- Extensible plugin architecture
- Version control system
- Real-time AJAX interface

## Core Components

### Lazy Loading
Implemented in `admin/editor/editor.js`:
```javascript
// Initialize IntersectionObserver for block loading
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      loadBlock(entry.target);
      observer.unobserve(entry.target);
    }
  });
}, {threshold: 0.1});

// Observe all block placeholders
document.querySelectorAll('.block-placeholder').forEach(el => {
  observer.observe(el);
});
```

### Version Control System
Core functionality in `models/VersionModel.php`:
```php
/**
 * Creates new content version
 * @param int $contentId - Parent content ID
 * @param string $data - Serialized content data
 * @param int $createdBy - User ID of creator
 * @param string $notes - Version notes/comment
 * @return bool - Success status
 */
public static function createVersion($contentId, $data, $createdBy, $notes) {
  // Auto-increments version number
  // Stores version data in database
}

/**
 * Restores content to specific version
 * @param int $contentId - Parent content ID 
 * @param int $versionId - Version ID to restore
 * @param int $restoredBy - User ID performing restore
 * @return bool - Success status
 */
public static function restoreVersion($contentId, $versionId, $restoredBy) {
  // Retrieves version data
  // Updates main content record
}
```

### AJAX Interface
Implemented in `components/PageBuilder.vue`:
```javascript
// Save page with AJAX
async savePage() {
  try {
    const response = await axios.post('/api/page/save', {
      id: this.pageId,
      blocks: this.blocks
    });
    // Handle response
  } catch (error) {
    // Error handling with retry logic
  }
}
```

## API Reference

### Version Endpoints
- `POST /api/versions/create` - Create new version
- `GET /api/versions/list/:contentId` - List versions for content
- `POST /api/versions/restore` - Restore specific version

### Builder Endpoints  
- `POST /api/page/save` - Save page content
- `GET /api/page/load/:id` - Load page content
- `POST /api/block/update` - Update single block

## Implementation Status
| Feature           | Status     | Location               |
|-------------------|------------|------------------------|
| Lazy Loading      | Implemented| admin/editor/editor.js |
| Version Control   | Implemented| models/VersionModel.php|
| AJAX Interface    | Implemented| components/PageBuilder.vue |