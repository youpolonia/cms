# Media Gallery Module Design

## Feature Roadmap
### MVP (Phase 1)
- File upload (drag/drop + API)
- Basic file listing
- File metadata storage
- MIME validation
- Filename sanitization

### Core (Phase 2)
- Folder organization
- Tagging system
- Search/filter capabilities
- Thumbnail generation
- Integration with Builder Engine/CPTs/Themes

### Optional (Phase 3)
- AI-powered tagging/analysis
- Image optimization
- Bulk operations
- Version history

## Module Structure
```
modules/MediaGallery/
├── MediaRegistry.php       # File metadata storage
├── MediaUploader.php       # Upload handler
├── MediaBrowser.php        # UI/API browser
├── MediaSanitizer.php      # File validation
└── MediaOptimizer.php      # Optional optimization
```

## Security Checklist
- [ ] MIME type validation
- [ ] File extension whitelist
- [ ] Filename sanitization
- [ ] Directory traversal protection
- [ ] Size limits enforcement
- [ ] Virus scanning (if possible)

## Example JSON Metadata
```json
{
  "id": "media_123",
  "filename": "example.jpg",
  "path": "/media/uploads/2025/06/example.jpg",
  "type": "image/jpeg",
  "size": 102400,
  "width": 1920,
  "height": 1080,
  "tags": ["nature", "landscape"],
  "created_at": "2025-06-14T22:00:00Z",
  "modified_at": "2025-06-14T22:00:00Z",
  "thumbnail": "/media/thumbs/example_thumb.jpg"
}
```

## Integration Points
1. Builder Engine:
   - Image selection from gallery
   - Background image selection

2. CPTs:
   - Media field type
   - Gallery field type

3. Theme Manager:
   - Theme asset selection
   - Logo/favicon upload