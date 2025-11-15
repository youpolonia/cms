# Media Gallery API Reference

## Overview
The Media Gallery module provides file management capabilities for the CMS, handling:
- File uploads (images, documents, media)
- Media organization and retrieval
- File validation and sanitization

It integrates with:
- Builder Engine (for image selection)
- Custom Post Types (for media fields)
- Theme Manager (for theme assets)

## Public Methods

### `uploadFile(array $file): bool`
Uploads a file to the media gallery.

**Parameters:**
- `$file`: Must match `$_FILES` structure with:
  - `name`: Original filename
  - `type`: MIME type
  - `tmp_name`: Temporary upload path
  - `error`: Upload error code
  - `size`: File size in bytes

**Returns:**  
`true` on success, `false` on failure

### `getMediaList(): array`
Retrieves all available media files.

**Returns:**  
Array of file metadata objects with:
- `filename`: Stored filename
- `original_name`: Original upload name
- `path`: Relative path from CMS root
- `size`: File size in bytes
- `type`: MIME type
- `upload_date`: ISO 8601 timestamp

### `deleteFile(string $filename): bool`
Deletes a file from the media gallery.

**Parameters:**
- `$filename`: The stored filename to delete

**Returns:**  
`true` on success, `false` on failure

## Integration Notes
The module works with:
- `MediaUploader.php`: Handles file uploads
- `MediaRegistry.php`: Tracks file metadata
- `MediaSanitizer.php`: Validates files
- `media-gallery-view.php`: Provides UI

No frameworks are used - all functionality is custom PHP.

## Example Usage

```php
// Uploading a file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mediaGallery = new MediaGallery();
    $success = $mediaGallery->uploadFile($_FILES['upload']);
}

// Listing files
$mediaGallery = new MediaGallery();
$files = $mediaGallery->getMediaList();
foreach ($files as $file) {
    echo "{$file['original_name']} ({$file['type']})";
}