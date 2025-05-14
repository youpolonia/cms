# Database Schema

## Entity Relationship Diagram

```mermaid
erDiagram
    CONTENTS ||--o{ CONTENT_VERSIONS : has
    CONTENTS ||--o{ CATEGORY_CONTENT : categorized
    CATEGORIES ||--o{ CATEGORY_CONTENT : has
    CONTENTS {
        bigint id PK
        string title
        text body
        string status
        timestamp created_at
        timestamp updated_at
    }
    CONTENT_VERSIONS {
        bigint id PK
        bigint content_id FK
        text body
        boolean is_autosave
        timestamp created_at
    }
    CATEGORIES {
        bigint id PK
        string name
        string slug
        text seo_description
        timestamp created_at
        timestamp updated_at
    }
    CATEGORY_CONTENT {
        bigint id PK
        bigint category_id FK
        bigint content_id FK
        timestamp created_at
    }
```

## Key Tables

### Contents
- Stores all content items
- Fields: id, title, body, status, created_at, updated_at
- Relationships: Has many versions, belongs to many categories

### Content Versions
- Tracks historical versions of content
- Fields: id, content_id, body, is_autosave, created_at
- Relationships: Belongs to content

### Categories
- Organizes content into hierarchical categories
- Fields: id, name, slug, seo_description, created_at, updated_at
- Relationships: Belongs to many contents

### Category Content
- Junction table for content categorization
- Fields: id, category_id, content_id, created_at

## Indexes
- `contents(status)` - For filtering by publication status
- `content_versions(content_id)` - For version lookup
- `categories(slug)` - For URL routing
- `category_content(category_id, content_id)` - For efficient categorization queries

## Data Retention
- Content versions are retained indefinitely
- Soft deletes used for all main tables
- Automated archiving of old versions after 1 year