# Content Model Architecture Specification

## 1. Core Content Entity Structure

### Base Fields
```mermaid
classDiagram
    class Content {
        +int id
        +string title
        +string slug
        +text body
        +string status
        +int author_id
        +datetime created_at
        +datetime updated_at
        +getFieldDefinitions() array
    }
```

### Relationships
```mermaid
erDiagram
    CONTENT ||--o{ CONTENT_VERSION : has
    CONTENT ||--o{ CONTENT_METADATA : has
    CONTENT ||--|{ USER : "created by"
    CONTENT ||--o{ TAG : "tagged with"
```

## 2. Content Type System Design

### Type Hierarchy
```mermaid
graph TD
    A[ContentTypeInterface] --> B[Content]
    B --> C[ArticleContent]
    B --> D[PageContent]
    B --> E[ProductContent]
```

### Field Definitions
- Each content type implements `getFieldDefinitions()`
- Field types supported:
  - String (with max length)
  - Text
  - Number
  - Boolean
  - Date
  - Enum (dropdown)
  - Relationship

## 3. Storage Requirements

### Database Schema
```sql
CREATE TABLE contents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    body TEXT,
    status ENUM('draft','published','archived') DEFAULT 'draft',
    author_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
);

CREATE TABLE content_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_id INT NOT NULL,
    version_number INT NOT NULL,
    is_autosave BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (content_id) REFERENCES contents(id)
);
```

## 4. Validation Rules

### Validation Flow
```mermaid
sequenceDiagram
    Controller->>+Content: getFieldDefinitions()
    Content-->>-Controller: field definitions
    Controller->>+Validator: validate(data, rules)
    Validator-->>-Controller: validated data
```

### Rule Types
- Required fields
- Data type validation
- Length restrictions
- Pattern matching (regex)
- Enum value checking

## 5. Integration Points

### Existing Database Layer
- Uses `DatabaseConnection` singleton
- Implements `Model` base class methods:
  - `save()`
  - `update()`
  - `delete()`
  - `find()`

### Versioning System
- Hook into `beforeSave` event
- Create version snapshot
- Store diff in `content_versions` table

## Implementation Classes

### Class Structure
```mermaid
classDiagram
    ContentTypeInterface <|-- Content
    Content <|-- ArticleContent
    Content <|-- PageContent
    
    class ContentTypeInterface {
        <<interface>>
        +getTypeName() string
        +getFieldDefinitions() array
    }
    
    class ContentFactory {
        +create(type, attributes) Content
        +getAvailableTypes() array
    }
    
    class ContentController {
        +create(request) Response
        -validateContent(data) array
        -buildValidationRules(definitions) array
    }
```

## Deployment Considerations
- All code is pure PHP 8.1+
- No Composer dependencies
- Database migrations included in `/database/migrations`
- FTP deployment compatible