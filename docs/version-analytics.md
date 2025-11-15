# Version Analytics System

## Overview
The Version Analytics system tracks user interactions with content versions including:
- Views (when users view a version)
- Restorations (when users restore a version)

## Data Model
```php
Schema::create('version_analytics', function (Blueprint $table) {
    $table->id();
    $table->foreignId('version_id')->constrained('content_versions');
    $table->foreignId('user_id')->constrained('users');
    $table->integer('view_count')->default(0);
    $table->integer('restore_count')->default(0);
    $table->timestamp('last_viewed_at')->nullable();
    $table->timestamp('last_restored_at')->nullable();
    $table->timestamps();
});
```

## API Endpoints

### GET /api/version-analytics/summary
Get analytics summary for all versions

**Query Parameters:**
- `timeframe` (optional): Filter by timeframe (day, week, month, year)
- `content_id` (optional): Filter by specific content ID

**Example Response:**
```json
{
    "data": [
        {
            "version_id": 123,
            "total_views": 42,
            "total_restores": 3,
            "last_viewed": "2025-04-30T14:32:10Z",
            "last_restored": "2025-04-28T09:15:22Z",
            "version": {
                "id": 123,
                "content_id": 456,
                "version_number": 2
            }
        }
    ],
    "timeframe": "week",
    "content_filter": "none"
}
```

### GET /api/version-analytics/{id}/detail
Get detailed analytics for a specific version

**Example Response:**
```json
{
    "version": {
        "id": 123,
        "content_id": 456,
        "version_number": 2,
        "created_at": "2025-04-15T08:30:00Z",
        "content": {
            "id": 456,
            "title": "Sample Content"
        }
    },
    "analytics": {
        "total_views": 42,
        "total_restores": 3,
        "last_viewed_at": "2025-04-30T14:32:10Z",
        "last_restored_at": "2025-04-28T09:15:22Z",
        "user_breakdown": [
            {
                "user_id": 789,
                "views": 15,
                "restores": 1
            }
        ]
    }
}
```

## Implementation Details

### Tracking Views
Views are automatically tracked when:
- A user views a version in the version comparison UI
- A user previews a version before restoration

### Tracking Restorations
Restorations are tracked when:
- A user restores a single version
- A user performs a bulk restoration

## Testing
Run analytics tests with:
```bash
php artisan test tests/Feature/VersionAnalyticsTest.php