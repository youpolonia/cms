# Conflict Resolution Architecture

## API Endpoint
```php
add_route('POST', '/content/{id}/versions/resolve-conflict', 'ContentController@resolveVersionConflict', 
    ['middleware' => ['CheckPermission:content_schedule']]);
```

## Resolution Strategies
1. **First-in Priority** - Earliest scheduled version wins
2. **Version Priority** - Major > Minor > Patch versions
3. **Manual Resolution** - Requires user input

## Database Changes
```sql
CREATE TABLE resolutions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conflict_id INT NOT NULL,
    resolution_method ENUM('first_in', 'version_priority', 'manual') NOT NULL,
    resolved_by INT NOT NULL,
    resolved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (conflict_id) REFERENCES version_conflicts(id),
    FOREIGN KEY (resolved_by) REFERENCES users(id)
);
```

## Implementation Sequence
1. Add resolution endpoint to API routes
2. Implement VersionedScheduleService.resolveConflict()
3. Create database migration
4. Add conflict logging
5. Update TestController