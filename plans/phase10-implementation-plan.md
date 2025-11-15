# Phase 10 Implementation Plan

## Core Components
1. **API Expansion**
   - Add PUT/PATCH/DELETE endpoints in `api/routes.php`
   - Implement versioning endpoints (`/versions`, `/publish`)
   - Maintain existing authentication/error handling

2. **Database Changes**
   - Create `content_versions` table migration
   - Add foreign key to `content` table
   - Implement rollback procedure

3. **Testing Infrastructure**
   - Create test endpoints:
     - `/api/test/content-versions`
     - `/api/test/content-states` 
     - `/api/test/migration-rollback`
   - Document test procedures

## Implementation Steps

### 1. API Implementation
```php
// api/routes.php updates
$router->put('/content/{id}', 'ContentController@update');
$router->patch('/content/{id}', 'ContentController@partialUpdate');
$router->delete('/content/{id}', 'ContentController@destroy');
$router->get('/content/{id}/versions', 'ContentController@versions');
$router->post('/content/{id}/publish', 'ContentController@publish');
```

### 2. Database Migration
```php
// database/migrations/0010_content_versions.php
class Migration_0010_ContentVersions {
    public static function up() {
        // Create content_versions table
    }
    
    public static function down() {
        // Drop table
    }
}
```

### 3. Testing Endpoints
```php
// public/api/test/content-versions.php
function testContentVersioning() {
    // Test cases
}
```

## Documentation Updates
1. Update API reference
2. Create migration guide
3. Document testing framework

## Timeline
1. Week 1: API implementation
2. Week 2: Database changes
3. Week 3: Testing infrastructure
4. Week 4: Documentation updates