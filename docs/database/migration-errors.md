# Content Versions Table Migration Errors and Solutions

## Common Issues

### 1. Schema-Model Mismatch
**Error**: Model expects columns not present in database
**Solution**: Create migration to add missing columns
**Example**:
```php
Schema::table('content_versions', function (Blueprint $table) {
    $table->text('content')->nullable();
    $table->string('approval_status')->nullable();
    // Additional fields as needed
});
```

### 2. Failed Migrations
**Error**: Migrations marked as failed or incomplete
**Solution**:
1. Check migrations table: `SELECT * FROM migrations`
2. Remove problematic record: 
```sql
DELETE FROM migrations 
WHERE migration = 'migration_name'
LIMIT 1;
```

### 3. Column Type Conflicts
**Error**: Model casts don't match database column types
**Solution**: Ensure model $casts matches column types:
```php
protected $casts = [
    'is_archived' => 'boolean',
    'approval_history' => 'array'
];
```

## Best Practices

1. Always verify database schema matches model expectations
2. Document all schema changes in migrations
3. Keep migration files in version control
4. Test migrations in development before production