# Version Comparison Metadata Schema Design

## Database Migration for `comparison_metadata` Table

```php
Schema::create('comparison_metadata', function (Blueprint $table) {
    // Primary key and relationships
    $table->id();
    $table->foreignId('version_a_id')->constrained('content_versions');
    $table->foreignId('version_b_id')->constrained('content_versions');
    $table->foreignId('content_id')->constrained('contents');
    $table->foreignId('user_id')->constrained('users');
    
    // Basic diff statistics
    $table->float('similarity_percentage');
    $table->integer('lines_added');
    $table->integer('lines_removed');
    $table->integer('lines_unchanged');
    $table->integer('words_added');
    $table->integer('words_removed');
    $table->integer('words_unchanged');
    
    // Structural analysis
    $table->json('frequent_changes')->nullable();
    $table->json('change_distribution')->nullable();
    
    // Performance metrics
    $table->float('comparison_time_ms');
    $table->string('comparison_algorithm');
    
    // Additional metadata
    $table->json('custom_metadata')->nullable();
    $table->text('notes')->nullable();
    
    $table->timestamps();
    
    // Indexes for performance
    $table->index(['version_a_id', 'version_b_id']);
    $table->index(['content_id']);
    $table->index(['user_id']);
    $table->index(['similarity_percentage']);
});
```

## Key Features

1. **Relationships**:
   - Many-to-one with `content_versions` (both compared versions)
   - Many-to-one with `contents` (parent content)
   - Many-to-one with `users` (who performed comparison)

2. **Diff Statistics**:
   - Line-level changes (added/removed/unchanged)
   - Word-level changes (added/removed/unchanged)
   - Similarity percentage

3. **Structural Analysis**:
   - Frequent changes by element type (headings, paragraphs, etc.)
   - Change distribution by content section

4. **Performance Tracking**:
   - Comparison execution time
   - Algorithm used

5. **Extensibility**:
   - Custom metadata JSON field
   - Notes field for manual annotations

## Indexes

1. Composite index on version pairs
2. Individual indexes on content and user
3. Index on similarity percentage for filtering
4. Automatic indexes on foreign keys

## ER Diagram

```mermaid
erDiagram
    CONTENTS ||--o{ CONTENT_VERSIONS : has
    CONTENT_VERSIONS ||--o{ COMPARISON_METADATA : "version_a"
    CONTENT_VERSIONS ||--o{ COMPARISON_METADATA : "version_b"
    CONTENTS ||--o{ COMPARISON_METADATA : has
    USERS ||--o{ COMPARISON_METADATA : performed

    CONTENTS {
        int id PK
        string title
        text content
        string slug
    }
    CONTENT_VERSIONS {
        int id PK
        int content_id FK
        text content
        boolean is_autosave
        boolean is_bookmarked
    }
    COMPARISON_METADATA {
        int id PK
        int version_a_id FK
        int version_b_id FK
        int content_id FK
        int user_id FK
        float similarity_percentage
        json frequent_changes
        json change_distribution
    }
    USERS {
        int id PK
        string name
    }

## Comparison Modes

### Line-by-Line Comparison
- Highlights exact text differences between versions
- Uses FineDiff algorithm for text comparison
- Supports syntax highlighting for code content
- Performance: ~200ms for average document

### Side-by-Side View
- Displays versions in parallel columns
- Synchronized scrolling
- Color-coded change indicators
- Supports HTML content comparison using HtmlDiff

### Semantic Comparison
- Analyzes content structure rather than raw text
- Detects moved/reorganized sections
- Identifies semantically similar content
- Uses machine learning for similarity scoring

## Real-time Collaboration Features

- Multiple users can compare versions simultaneously
- Live annotations and comments
- Shared comparison sessions with unique URLs
- Presence indicators showing active collaborators
- Conflict resolution for concurrent edits

## Version Restoration Workflow

1. User selects version to restore from comparison view
2. System creates new version with restored content
3. Preserves original version history
4. Notifies all collaborators via:
   - In-app notifications
   - Email alerts (if configured)
5. Updates all related comparison metadata

## Performance Optimizations

- Cached comparison results (TTL: 1 hour)
- Background processing for large documents
- Progressive loading for complex comparisons
- Database optimizations:
  - Indexed version pairs
  - Materialized views for frequent queries
  - Partitioned comparison_metadata table

## Analytics Capabilities

### Comparison Analytics
- Tracks most compared versions
- Measures comparison frequency
- Records average comparison time
- Identifies common change patterns

### User Behavior
- Tracks comparison methods used
- Measures time spent reviewing changes
- Records restoration frequency
- Identifies power user patterns

### System Metrics
- Monitors comparison queue depth
- Tracks memory usage during operations
- Logs error rates
- Measures cache hit ratios