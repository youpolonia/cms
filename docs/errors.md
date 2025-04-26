# Common Errors and Solutions

## SQLite JSON Function Compatibility

### Error
When running migrations on SQLite, you may encounter errors like:
"SQLite does not support JSON_MERGE_PATCH function"

### Cause
SQLite has limited JSON function support compared to MySQL/MariaDB. The JSON_MERGE_PATCH function is not available in SQLite.

### Solution
Replace JSON_MERGE_PATCH with JSON_SET in your migrations. Example:

```php
// Before (incompatible with SQLite)
DB::table('users')->update([
    'notification_preferences' => DB::raw(
        "JSON_MERGE_PATCH(
            COALESCE(notification_preferences, '{}'),
            '{\"analytics_export_ready\": true}'
        )"
    )
]);

// After (SQLite compatible)
DB::table('users')->update([
    'notification_preferences' => DB::raw(
        "JSON_SET(
            COALESCE(notification_preferences, '{}'),
            '$.analytics_export_ready', true
        )"
    )
]);
```

### Prevention
- Test migrations on SQLite during development
- Use JSON_SET instead of JSON_MERGE_PATCH for cross-database compatibility
- Consider using Laravel's JSON column casting for simpler operations

## Content Approval Workflow Database Issues

**Problem**: Missing foreign key relationships between notifications and approval steps in content approval workflow.

**Symptoms**:
- Approval notifications not being properly linked to workflow steps
- Data integrity issues when managing approval processes

**Solution**:
1. Created migrations to establish proper relationships:
   - `2025_04_15_104512_create_notifications_table`
   - `2025_04_15_104619_add_workflow_id_to_approval_steps_table` 
   - `2025_04_15_104715_add_step_id_to_notifications_table`

2. Added foreign key constraints to maintain data integrity

**Prevention**:
- Always verify database relationships when implementing new workflow features
- Include proper foreign key constraints in initial schema design

[Rest of existing documentation...]

## Content Scheduling System Troubleshooting

### Issue: Test failures due to output verification
**Symptoms:**
- Tests fail when verifying exact command output strings
- Content state changes correctly but output format differs

**Solution:**
Focus test assertions on state changes rather than exact output formatting:
1. Verify content status changes (e.g., STATUS_PUBLISHED)
2. Check timestamps (published_at)
3. Avoid strict output string matching unless absolutely necessary

**Example:**
```php
// Instead of:
$this->assertStringContainsString('Published content:', $output);

// Do:
$content->refresh();
$this->assertEquals(Content::STATUS_PUBLISHED, $content->status);
$this->assertNotNull($content->published_at);
```

**Rationale:**
- Output formatting may change during development
- State changes represent the core business logic
- Tests become more maintainable and less brittle
