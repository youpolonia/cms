# Migration Safety Procedures

## Backup Procedures

### Pre-Migration Backups
1. **Full Database Backup**:
   ```bash
   mysqldump -u [user] -p [database] > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Critical Tables Backup**:
   ```bash
   mysqldump -u [user] -p [database] users contents content_versions > critical_tables_$(date +%Y%m%d).sql
   ```

3. **Automated Backup Script** (place in `/scripts/db_backup.sh`):
   ```bash
   #!/bin/bash
   DATE=$(date +%Y%m%d_%H%M%S)
   BACKUP_DIR="/backups/db"
   mysqldump -u [user] -p[password] [database] > $BACKUP_DIR/full_$DATE.sql
   gzip $BACKUP_DIR/full_$DATE.sql
   ```

## Migration Verification

1. **Pre-Migration Checks**:
   - Verify migration runs in testing environment first
   - Check foreign key constraints
   - Validate data types match existing schema

2. **Test Migration**:
   ```bash
   php artisan migrate --pretend
   ```

3. **Post-Migration Verification**:
   ```bash
   php artisan migrate:status
   php artisan db:check
   ```

## Rollback Mechanisms

1. **Automated Rollback Triggers**:
   - Implement try/catch in migration batches
   - Set `$this->haltOnError = true;` in migration service provider

2. **Manual Rollback**:
   ```bash
   php artisan migrate:rollback --step=1
   ```

3. **Emergency Restore**:
   ```bash
   mysql -u [user] -p [database] < backup_file.sql
   ```

## Best Practices

1. Always run migrations in transaction blocks
2. Keep migrations small and focused
3. Test rollback functionality
4. Document all schema changes
5. Monitor performance impact
6. Maintain backup retention policy (30 days recommended)
## Categories Table Schema Notes

### Structure
- **Nested Set Model**: Uses `parent_id` self-referential foreign key to create hierarchical categories
  - Allows unlimited depth nesting
  - Supports efficient tree traversal with proper indexing
- **Slug**: Unique constraint prevents duplicate URLs
- **Order**: Allows manual sorting of categories at same level

### Indexes
- `parent_id`: Optimizes hierarchical queries (child lookups)
- `is_active`: Speeds up active/inactive filtering
- `slug`: Unique constraint automatically creates index

### Soft Deletes
- Allows non-destructive removal of categories
- Requires special handling for hierarchical data:
  - Consider cascading soft deletes to children
  - May need to reparent children when deleting
  
### Performance Considerations
- Deep nesting may require recursive queries
- Consider adding `depth` column for easier querying
- Large trees may benefit from nested set model (left/right) implementation
## Category Content Pivot Table Notes

### Relationship Design
- **Many-to-Many**: Connects contents to categories via composite foreign keys
- **Primary Key**: Uses composite primary key (category_id + content_id) to enforce uniqueness
- **Timestamps**: Added to track relationship creation/modification times

### Constraints
- **Cascade Deletion**: Both foreign keys configured to cascade on delete
  - Automatically removes relationship when either content or category is deleted
  - Prevents orphaned records

### Performance Optimizations  
- **Order Index**: Added index on order field for faster sorting
- **Query Patterns**: Optimized for:
  - Finding all contents in a category
  - Finding all categories for a content
  - Sorting contents within a category
## Categories SEO Fields Notes

### Field Specifications
- **seo_title**: String (max 255 chars) - Optimized title for search engines
- **seo_description**: Text - Meta description content 
- **seo_keywords**: JSON array - List of keywords for SEO optimization
  - Stored as JSON to allow array operations and typing
  - Example: ["web development", "programming", "laravel"]

### Usage Guidelines  
- All fields are nullable to allow gradual population
- Should be populated from category content if not explicitly set
- Keywords should be normalized (lowercase, trimmed)

### Performance Considerations
- No direct indexes by default since these fields:
  - Aren't typically filtered directly
  - Have high cardinality (keywords)
  - Are often used in fulltext searches
- For advanced SEO search, consider:
  - Fulltext index on title+description
  - Generated column for keyword counts
## Contents Table Schema Notes

### Core Structure
- **Primary Content Storage**: Stores all CMS content with version history
- **Relationships**:
  - Has many versions through content_versions
  - Belongs to many categories through category_content
  - Belongs to user via user_id foreign key

### Indexing Strategy  
- **slug**: Unique index for URL routing
- **user_id**: Foreign key index for ownership queries
- **content_type**: Partial index recommended for type filtering
- **created_at**: Indexed for version ordering

### JSON Fields
- **ai_metadata**: Stores generation/analysis metadata
  - Structure: {generator: string, prompts: string[], quality_score: float}
  - Used by AI content generation system
- **seo_keywords**: Array of normalized keywords (matches categories)

### Performance Notes
- **content**: Large text may benefit from:
  - Compression for storage
  - External storage for very large content
- **Versioning**:
  - Content versions stored separately
  - Consider periodic archiving of old versions