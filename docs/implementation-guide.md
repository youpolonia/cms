# Implementation Guide

## Getting Started
1. Review all documentation:
   - `system-overview.md` for architecture
   - `technical-specs.md` for details
   - `development-roadmap.md` for timeline

2. Set up development environment:
```bash
git clone [repository]
composer install
npm install
cp .env.example .env
php artisan key:generate
```

## Database Migrations

### Key Changes Implemented:

1. **Duplicate Migration Prevention**:
   - System now scans migrations for duplicate table creation attempts
   - Conflicts show original migration timestamp for resolution
   - Example check from AppServiceProvider:
   ```php
   if (Schema::hasTable($tableName)) {
       throw new Exception("Table $tableName already exists (originally created by migration X)");
   }
   ```

2. **Idempotent Migration Patterns**:
   - All migrations now check for existing columns/tables:
   ```php
   // Safe column addition example
   if (!Schema::hasColumn('content_versions', 'approval_status')) {
       $table->string('approval_status')->default('pending');
   }
   ```

3. **Error Handling**:
   - Transactions wrap all migration operations
   - Detailed error messages for troubleshooting
   - Automatic rollback on failure

```

## First Implementation Steps

### Media Gallery
1. Create database migrations:
```bash
php artisan make:migration create_media_gallery_tables
```

2. Implement base models:
```php
// app/Models/Media.php
class Media extends Model {
    protected $casts = ['metadata' => 'array'];
    // ...
}

// app/Models/MediaCollection.php
class MediaCollection extends Model {
    public function items() {
        return $this->belongsToMany(Media::class);
    }
}
```

### Themes System
1. Create theme directory structure:
```bash
mkdir -p themes/default/{views/layouts,assets}
```

2. Implement theme loader:
```php
// app/Providers/ThemeServiceProvider.php
public function boot() {
    View::addLocation(base_path('themes/default/views'));
}
```

## Testing Strategy
1. Unit tests for core functionality
2. Feature tests for:
   - Media upload/management
   - Theme switching
   - Page builder interactions
3. Browser tests for UI components

## Contribution Guidelines
1. Branch naming: `feature/[name]` or `fix/[issue]`
2. Commit messages:
   - Prefix with [Media], [Themes], etc.
   - Reference issue numbers
3. Pull requests require:
   - Passing tests
   - Documentation updates
   - Code review approval

## Analytics Integration
### WebSocket Implementation
1. Endpoint: `/api/version-analytics/ws-connect`
2. Authentication via Sanctum tokens
3. Rate limited to 30 connections/minute

### Rate Limiting Configuration
```php
'analytics' => [
    'api' => '120,1',    // 120 requests/minute (main API)
    'realtime' => '30,1', // 30 connections/minute (WebSocket)
    'export' => '10,5',   // 10 exports/5 minutes
],
```

### User Preferences
- Stored in browser local storage
- Can sync with backend via:
```bash
PATCH /api/user/preferences
```
- Supported preferences:
  - Dashboard layout
  - Default time range
  - Notification settings

### Notification System
- Triggers for:
  - Significant content changes (>50% diff)
  - High traffic volume alerts
  - Failed export attempts
- Delivered via:
  - WebSocket (real-time)
  - Email (digest)

## Next Immediate Actions
1. Implement Media Gallery database schema
2. Create basic theme structure
3. Set up CI/CD pipeline
4. Monitor analytics rate limits
5. Review WebSocket connection scaling
