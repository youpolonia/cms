# Media Module Architecture Plan

## Overview
Modular media management system for PHP CMS with:
- Tenant isolation
- File storage management
- Metadata handling
- Image transformations
- Secure uploads

## Database Schema

```php
// media_files table
CREATE TABLE media_files (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id VARCHAR(36) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    path VARCHAR(512) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (tenant_id)
);

// media_metadata table
CREATE TABLE media_metadata (
    media_id BIGINT NOT NULL,
    key VARCHAR(100) NOT NULL,
    value TEXT,
    PRIMARY KEY (media_id, key),
    FOREIGN KEY (media_id) REFERENCES media_files(id) ON DELETE CASCADE
);
```

## File Storage Structure
```
/public/media/
  /{tenant_id}/
    /originals/    # Original uploaded files
    /thumbnails/   # Generated thumbnails
    /cache/        # Processed/resized versions
```

## API Endpoints

### Core Endpoints
- `POST /api/media/upload` - File upload with validation
- `GET /api/media/{id}` - Get media details
- `DELETE /api/media/{id}` - Delete media item
- `GET /api/media/search` - Search by metadata

### Transformation Endpoints
- `POST /api/media/{id}/transform` - Request image transformation
- `GET /api/media/{id}/versions` - List available transformations

## Security Measures
1. File Upload:
   - MIME type whitelisting
   - Size limits (configurable per tenant)
   - Virus scanning integration

2. Access Control:
   - Tenant isolation at filesystem level
   - Role-based permissions for media operations

## UI Components
1. Media Browser:
   - Grid/List view toggle
   - Filter by type/date/size
   - Bulk operations

2. Upload Interface:
   - Drag & drop support
   - Progress indicators
   - Metadata editor

3. Transformation Panel:
   - Preset sizes
   - Custom dimension input
   - Quality controls

## Implementation Phases
1. Core Database & Storage (Phase 1)
2. Basic API Endpoints (Phase 2)
3. Admin UI (Phase 3)
4. Advanced Features (Phase 4)

## Integration Points
- Content editor media picker
- Workflow triggers for media processing
- API for external services