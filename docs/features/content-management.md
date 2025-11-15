# Content Management

## Core Features
- **Versioned Content**: All changes create new versions
- **Workflow System**: Approval process for publishing
- **Organization**: Categories and tags
- **Scheduling**: Future publish/unpublish dates

## Version Control
- Automatic versioning on save
- Visual comparison between versions
- One-click restoration
- Branching support for parallel edits

### Version Operations
```mermaid
sequenceDiagram
    participant User
    participant CMS
    User->>CMS: Edit content
    CMS->>CMS: Create new version
    CMS->>User: Return version info
    User->>CMS: Request version compare
    CMS->>User: Show differences
    User->>CMS: Restore version
    CMS->>CMS: Create restoration version
    CMS->>User: Confirm restoration
```

## Media Handling
- Drag-and-drop uploads
- Automatic image optimization
- Collections for organization
- Metadata storage (EXIF, IPTC)
- Transformations via MCP Media Processing