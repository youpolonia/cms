# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Architecture Overview

This is a **custom PHP 8.2+ CMS** built **without Laravel or any framework**. The system uses a custom router, manual dependency injection, and vanilla PHP patterns throughout.

**System Requirements**:
- **PHP**: 8.1+ minimum
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **RAM**: 2GB minimum
- **Disk**: 10GB minimum
- **Web Server**: Apache with mod_rewrite or nginx

**Current Status** (as of 2025-09-26):
- ✅ Production-ready with full security compliance
- ✅ 462 legacy Laravel test errors cleaned up (historical artifacts only)
- ✅ Zero framework dependencies
- ✅ Complete CSRF protection coverage
- ✅ Centralized database access via `\core\Database::connection()`

### Critical Constraints
- **NO FRAMEWORKS**: Do not use Laravel, Symfony, CodeIgniter, or any framework
- **NO COMPOSER**: No autoloaders, all includes via explicit `require_once`
- **NO CLI TOOLS**: No Artisan, no command-line migration runners with CLI arguments
- **FTP-ONLY DEPLOYMENT**: All code must work via direct FTP upload
- **Pure PHP**: No build steps, no package managers, no compiled assets

### DEV_MODE Security Gate
**CRITICAL**: Most entry points check `DEV_MODE` constant:
```php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}
```
Set in `config.php`: `define('DEV_MODE', false);`

This affects:
- Frontend/admin access (`public/index.php`, `admin/index.php`)
- Migration runner (`migrate.php`)
- Debug tools and test endpoints
- Error display verbosity

### Entry Points & Bootstrap

**Frontend Entry**: `public/index.php`
```
1. DEV_MODE check (403 if not dev)
2. require core/bootstrap.php
3. Load ExceptionHandler
4. Check maintenance.flag
5. Initialize MultiSite (tenant detection)
6. Load static cache handlers
7. Route matching and content rendering
```

**Admin Entry**: `admin/index.php`
```
1. DEV_MODE check (403 if not dev)
2. require config.php
3. Load error_handler.php and register handlers
4. Load csrf.php and call csrf_boot('admin')
5. Load maintenance_gate.php
6. require core/session_boot.php
7. Call cms_session_start('admin')
8. AuthController::requireLogin()
9. Render admin dashboard
```

**Bootstrap Process**: `core/bootstrap.php`
```
1. Security headers - cms_emit_security_headers()
2. Session hardening - Strict mode, HttpOnly, Secure, SameSite
3. EventBus - getInstance() and dispatch 'system.init'
4. Router - Load route files from routes/
5. Plugin auto-discovery - Scan /plugins/ directory
6. Core services - WorkflowEngine, ContentStateService, etc.
7. Error reporting - Configure based on DEV_MODE
```

## Database Layer

### Connection: `core/Database`
Singleton PDO wrapper with no ORM:
```php
$db = \core\Database::connection(); // Returns PDO singleton
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
```

### Configuration Sources
Two configuration files exist:

**Primary**: `config.php` (constants)
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cms');
define('DB_USER', 'cms_user');
define('DB_PASSWORD', 'secure_password_123');
```

**Alternative**: `config/database.php` (array, loads from `.env`)
```php
return [
    'connections' => [
        'mysql' => [
            'host' => $envVars['DB_HOST'] ?? 'localhost',
            'database' => $envVars['DB_DATABASE'] ?? '',
            // ...
        ]
    ]
];
```

**Important**: Check which is actively used before modifying database credentials.

### Query Pattern
Always use prepared statements (no query builders, no ORM):
```php
// Good
$stmt = $db->prepare("SELECT * FROM content WHERE slug = ?");
$stmt->execute([$slug]);

// Bad - never concatenate user input
$result = $db->query("SELECT * FROM content WHERE slug = '$slug'");
```

## Migrations

### Running Migrations: `migrate.php`
```bash
# 1. Edit migrate.php and check mode
# Default: $mode = 'dry-run';

# 2. Run dry-run to see pending migrations
php migrate.php
# Output: DRY-RUN: Migrations pending: [ migration1.php, migration2.php ]

# 3. Edit migrate.php and set mode to execute
# Change: $mode = 'execute';

# 4. Run in execute mode
php migrate.php
# Output: EXECUTE: Applied migrations: [ migration1.php ]
```

### Migration Structure
Located in `includes/migrations/`:
- `migration_registry.php` - Ordered array of migration basenames
- `abstractmigration.php` - Base class with `execute(PDO $db): bool` method
- `migrations_log.json` - Tracks executed migrations
- Individual migration files (e.g., `0000_example_noop.php`)

### Writing Migrations
```php
<?php
// includes/migrations/202501201200_create_posts.php

class CreatePostsTable extends AbstractMigration
{
    public function execute(PDO $db): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        $db->exec($sql);
        return true; // Return true on success
    }
}
```

**Important**:
- NO `up()` or `down()` methods (not Laravel)
- NO `Schema::` facade
- ONE method only: `execute(PDO $db): bool`
- Add basename to `migration_registry.php` in order

## Routing System

### Primary Router: `core/router.php` (namespace: `Core`)
Static routing engine with controller validation:

```php
use Core\Router;

// HTTP verb shortcuts
Router::get('/blog/{slug}', [BlogController::class, 'show']);
Router::post('/api/content', [ContentController::class, 'store']);

// Middleware and grouping
Router::middleware(['auth'])->prefix('/admin')->group(function() {
    Router::get('/dashboard', [DashboardController::class, 'index']);
});

// Load route files
Router::load([
    __DIR__ . '/../routes/web.php',
    __DIR__ . '/../routes/admin.php'
]);
```

**Route Parameters**: Converts `{paramName}` to regex capture groups
**Handler Types**: Callable, `[Class, 'method']`, or function name
**Validation**: Uses `ControllerRegistry::validateController()` before registration

### Legacy Router: `includes/RoutingV2/Router`
Being migrated via `CoreRouterAdapter` - avoid using directly.

## Security

### CSRF Protection: `core/csrf.php`
```php
// In bootstrap/controller
csrf_boot('admin'); // Initialize CSRF token in session

// In forms
<?php csrf_field(); ?> // Outputs hidden input

// In POST handlers
csrf_validate_or_403(); // Validates or returns 403
```

### Session Management: `core/session_boot.php`
```php
cms_session_start('admin'); // Uses CMSSESSID_ADMIN cookie
cms_session_start();        // Uses CMSSESSID cookie (default)
```

**Security Features**:
- HttpOnly cookies (prevents XSS cookie theft)
- Secure flag (HTTPS only)
- SameSite=Lax (CSRF protection)
- Strict session mode
- Automatic HTTPS detection

### Security Headers: `core/security_headers.php`
Automatically emitted in `core/bootstrap.php`:
- Content-Security-Policy
- X-Frame-Options: SAMEORIGIN
- X-Content-Type-Options: nosniff
- Strict-Transport-Security (HSTS)
- Permissions-Policy

### Input/Output Escaping
```php
// Always escape output
function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

echo "<h1>" . esc($userInput) . "</h1>";
```

### Session Security Enhancements
Additional security features available in `config/session.php`:

```php
return [
    'lifetime' => 120,              // 120 minutes (2 hours)
    'encrypt' => true,              // Enable session encryption (recommended for production)
    'partitioned_cookie' => true,   // Enable partitioned cookies (prevents cross-site tracking)
    // ... other settings
];
```

**Security Controls**:
- **Session Regeneration**: Automatically regenerate session ID after login/privilege change
- **Session Encryption**: Encrypt session data to prevent interception
- **Partitioned Cookies**: Enable `Partitioned` attribute for enhanced privacy
- **Strict SameSite**: Already enabled (set to `Lax` or `Strict`)

### Worker Authentication (JWT-Based)
For background workers and API endpoints:

```php
// Configuration
define('WORKER_JWT_LIFETIME', 3600); // 1 hour token lifetime

// Token validation
$auth = new WorkerAuthenticate($jwtSecret);
if (!$auth->validateToken($token)) {
    http_response_code(401);
    exit('Unauthorized');
}

// Auto-refresh when <10 minutes remain
if ($auth->shouldRefreshToken($token)) {
    $newToken = $auth->refreshToken($token);
    header('X-New-Token: ' . $newToken);
}
```

**Features**:
- HMAC signature verification
- Expiration checking
- Automatic token refresh
- Configurable token lifetime

## Plugin System

### Plugin Structure
```
plugins/PluginName/
├── plugin.json       # Manifest
└── bootstrap.php     # Initialization (returns closure)
```

### Plugin Manifest: `plugin.json`
```json
{
    "name": "ExamplePlugin",
    "version": "1.0.0",
    "description": "Plugin description",
    "author": "Author Name",
    "requires": "1.0.0",
    "hooks": {
        "action": ["init", "admin_init"],
        "filter": ["content_before_render"]
    }
}
```

### Bootstrap Pattern: `bootstrap.php`
```php
<?php
// Returns closure receiving $pluginManager
return function($pluginManager) {
    // Register hooks
    $pluginManager->addHook('init', function() {
        // Initialization logic
    });

    $pluginManager->addFilter('content_before_render', function($content) {
        // Modify content before rendering
        return $content;
    });
};
```

### Auto-Discovery
Plugins in `/plugins/` directory are automatically:
1. Scanned during `core/bootstrap.php`
2. Registered via `Core\ModuleRegistry::registerPlugin()`
3. Events dispatched: `plugin.loaded`, `plugin.error`

## Event System

### EventBus: `core/EventBus.php`
Singleton event dispatcher:
```php
$eventBus = \Core\EventBus::getInstance();

// Listen to events
$eventBus->listen('content.published', function($data) {
    // Handle event
    $contentId = $data['id'];
});

// Dispatch events
$eventBus->dispatch('content.published', ['id' => 123]);
```

**System Events**:
- `system.init` - After bootstrap
- `plugin.loaded` - Plugin successfully loaded
- `plugin.error` - Plugin loading failed
- `content.published` - Content published
- Custom events defined by modules/plugins

## Scheduled Tasks

### Cron Jobs
Located in `cron/` directory:
- `publish_scheduled_content.php` - Publishes scheduled content
- `builder_storage_cleanup.php` - Cleans up builder storage

### Running Cron Jobs
```bash
# Via command line (if available)
php cron/publish_scheduled_content.php

# Via web request (setup in cron)
curl https://yourdomain.com/cron/publish_scheduled_content.php
```

### Writing Custom Tasks
Create in `core/tasks/` with pattern:
```php
<?php
class ExampleTask {
    public static function run(): bool {
        // Task logic here
        return true; // or false on failure
    }
}
```

## View System

### Template Rendering: `includes/Core/View.php`
Framework-free view system with layout inheritance:

```php
// In controller
$view = new View(__DIR__ . '/../../templates');
$view->setLayout('layouts/main');
$content = $view->render('home/index', [
    'title' => 'Page Title',
    'user' => $userData
]);
```

**Features**:
- Layout inheritance via `$this->extend()`
- Partial includes via `$this->include()`
- Variable interpolation
- No dependencies beyond PHP 8.1+

**Template Example** (`templates/home/index.php`):
```php
<h1><?= $title ?></h1>
<p>Welcome, <?= esc($user['name']) ?></p>
```

**Layout Example** (`templates/layouts/main.php`):
```php
<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
</head>
<body>
    <?= $this->content() ?>
</body>
</html>
```

## Theme System

### Theme Structure
Located in `public/themes/`:
```
public/themes/
└── theme-name/
    ├── assets/       # CSS, JS, images
    │   ├── css/
    │   ├── js/
    │   └── images/
    └── templates/    # PHP template files
```

### Theme Usage
```php
// Reference assets using Theme::asset()
<link href="<?= Theme::asset('css/style.css') ?>" rel="stylesheet">
<script src="<?= Theme::asset('js/app.js') ?>"></script>

// Render theme templates
$content = View::render('home', ['title' => 'Welcome']);
```

**Best Practices**:
- Keep templates focused on presentation
- Move complex logic to controllers
- Use `esc()` helper to escape output
- Organize assets by type

## AI Content Generation

### Multi-Provider Architecture
The system supports multiple AI providers (OpenAI, HuggingFace, etc.):

**Configuration**: `config/ai.php`
```php
return [
    'default_provider' => 'openai',
    'providers' => [
        'openai' => [
            'api_key' => $_ENV['OPENAI_API_KEY'],
            'organization' => $_ENV['OPENAI_ORG'],
            'models' => ['gpt-4', 'gpt-3.5-turbo']
        ],
        'huggingface' => [
            'api_key' => $_ENV['HUGGINGFACE_API_KEY'],
            'models' => ['meta-llama/Llama-2-7b']
        ]
    ]
];
```

**Provider Interface**: All providers implement:
- `generateContent()` - Generate content from prompt
- `validateContent()` - Validate generated content
- `getModels()` - Get available models

**Usage**:
```php
$aiService = new AIService();
$content = $aiService->generateContent($prompt, $provider);
```

## Page Builder

### Page Builder Routes
Located in admin panel with version management:
- `/admin/page-builder/{contentId}` - Editor view
- `/admin/page-builder/{contentId}/versions` - Version list
- `/admin/page-builder/{contentId}/compare/{versionId}/latest` - Compare versions
- `/admin/page-builder/{contentId}/restore/{versionId}` - Restore version

### PageBuilderController
Key methods:
- `showEditor()` - Render page builder interface
- `showVersions()` - List all versions with pagination
- `bulkDeleteVersions()` - Delete multiple versions
- `compareVersions()` - Show diff between versions
- `restoreVersion()` - Restore historical version

### Version Management Features
- **Version Browser**: Paginated listing with search and filters
- **Diff Viewer**: Myers algorithm for line-level and word-level diffs
- **Restoration Panel**: Safe restoration with preview and audit trail
- **Bulk Operations**: Multi-select delete/restore with CSRF protection

## Controllers & Models

### Controller Pattern
Located in `controllers/`:
```php
class ContentController {
    private $db;

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    public function show(string $slug) {
        $stmt = $this->db->prepare(
            "SELECT * FROM content WHERE slug = ?"
        );
        $stmt->execute([$slug]);
        $content = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$content) {
            http_response_code(404);
            require_once __DIR__ . '/../views/errors/404.php';
            return;
        }

        require_once __DIR__ . '/../views/content/show.php';
    }
}
```

### Model Pattern
Located in `core/models/` and `includes/Models/`:
```php
class UserModel {
    private \PDO $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
```

## Multi-Tenant System

### Tenant Detection: `includes/multisite.php`
The system detects current tenant in this priority order:
1. **Subdomain**: `tenant1.example.com`
2. **Path prefix**: `/tenant1/route`
3. **X-Tenant-ID header**: For API requests
4. **Fallback**: Default site

### Tenant-Specific Routing
```php
// Global routes (available to all tenants)
$router->addRoute('GET', '/about', $handler);

// Tenant-specific routes (explicit tenant ID)
$router->addRoute('GET', '/dashboard', $handler, 'tenant1');

// Using path prefix helper
$router->addTenantRoute('GET', '/dashboard', $handler, 'tenant1');
// Maps to: /tenant1/dashboard
```

### TenantDetectionMiddleware
Add early in middleware stack for automatic tenant context:
```php
$router->addGlobalMiddleware(new TenantDetectionMiddleware());
```

### Tenant-Aware Queries
Always filter by `tenant_id`:
```php
$stmt = $db->prepare(
    "SELECT * FROM content_entries
     WHERE slug = ? AND tenant_id = ? AND published_status = 'published'"
);
$stmt->execute([$slug, $currentTenantId]);
```

## Testing

### Running Tests
```bash
# Run specific test file
php core/tests/statustransitiontest.php

# Module tests
php modules/content/tests/TemplateFallbackTest.php
```

**Note**: Tests are executable PHP files, not PHPUnit/Pest. They echo results directly.

## Maintenance Mode

### Enabling Maintenance Mode
Create flag file: `config/maintenance.flag` or `maintenance.flag`

OR define constant in `config.php`:
```php
define('MAINTENANCE_MODE', true);
```

### IP Allowlist
In `config.php`:
```php
define('MAINTENANCE_ALLOW_IPS', ['127.0.0.1', '192.168.1.100']);
```

### Response
- HTTP 503 Service Unavailable
- `Retry-After: 3600` header
- Bypassed for allowlisted IPs

## Error Handling

### Registration: `core/error_handler.php`
```php
cms_register_error_handlers(); // Called in admin/index.php
```

### Logging Locations
- **PHP Errors**: `/logs/php_errors.log`
- **App Exceptions**: `/logs/app_errors.log` (JSONL format)

### Development vs Production
- **DEV_MODE=true**: Full error details, stack traces
- **DEV_MODE=false**: Error ID only, details hidden

## Common Development Tasks

### Adding a New Route
1. Create controller in `controllers/` (if needed)
2. Add route in appropriate file in `routes/`
3. If new route file, register in `core/bootstrap.php` via `Router::load()`

Example in `routes/web.php`:
```php
<?php
use Core\Router;

Router::get('/about', [PageController::class, 'about']);
Router::get('/blog/{slug}', [BlogController::class, 'show']);
```

### Creating a Migration
1. Create file: `includes/migrations/202501201500_descriptive_name.php`
2. Extend `AbstractMigration` with `execute(PDO $db): bool` method
3. Add basename to `includes/migrations/migration_registry.php`
4. Test: `php migrate.php` (dry-run)
5. Execute: Edit `migrate.php` → set `$mode = 'execute';` → run `php migrate.php`

### Installing a Plugin
1. Create directory: `plugins/PluginName/`
2. Create `plugin.json` with metadata
3. Create `bootstrap.php` returning closure
4. Plugin auto-loads on next request

### Adding a Scheduled Task
1. Create class in `core/tasks/ExampleTask.php`
2. Implement `public static function run(): bool`
3. Add to `config/scheduled_tasks.php`
4. Setup cron job to trigger:
   ```bash
   */5 * * * * curl https://yourdomain.com/cron/run_tasks.php
   ```

## File Structure

```
cms/
├── admin/              # Admin panel (admin/index.php entry)
├── core/               # Core system classes
│   ├── bootstrap.php   # System initialization
│   ├── router.php      # Primary routing engine
│   ├── database.php    # PDO singleton
│   ├── csrf.php        # CSRF protection
│   ├── session_boot.php # Session management
│   ├── error_handler.php # Error/exception handling
│   ├── security_headers.php # Security headers
│   ├── maintenance_gate.php # Maintenance mode
│   ├── models/         # Data models
│   ├── services/       # Core services (AIService, etc.)
│   └── tasks/          # Scheduled tasks
├── config/             # Configuration files
│   ├── ai.php          # AI provider configuration
│   ├── database.php    # Database config (.env loader)
│   └── session.php     # Session security settings
├── config.php          # Primary config (constants)
├── controllers/        # HTTP controllers
├── cron/               # Cron job scripts
├── includes/           # Legacy includes
│   ├── Core/           # Core classes (View, etc.)
│   ├── migrations/     # Database migrations
│   └── multisite.php   # Multi-tenant system
├── models/             # Additional models
├── modules/            # Feature modules
├── plugins/            # Third-party plugins
│   └── PluginName/     # Plugin directory
│       ├── plugin.json # Plugin manifest
│       └── bootstrap.php # Plugin initialization
├── public/             # Web root
│   ├── index.php       # Frontend entry point
│   └── themes/         # Theme files
│       └── theme-name/ # Individual theme
│           ├── assets/ # CSS, JS, images
│           └── templates/ # Theme templates
├── routes/             # Route definitions
│   ├── web.php
│   ├── admin.php
│   └── api.php
├── templates/          # View templates
├── migrate.php         # Migration runner
└── logs/               # Log files
```

## Framework Restrictions (CRITICAL)

**This codebase explicitly prohibits Laravel and framework usage.**

### Do NOT Use:
- `Schema::create()`, `Schema::table()` - Use raw SQL in migrations
- `Artisan::call()` - No CLI commands
- `Eloquent` models - Use PDO prepared statements
- `Blade` templates - Use plain PHP in templates
- Laravel facades (Cache, DB, Auth, etc.) - Use custom implementations
- `Illuminate\*` components - Custom-built only
- Composer autoloading - Explicit `require_once` only

### Use Instead:
- **Migrations**: `AbstractMigration` with `execute(PDO $db)` method
- **Database**: `\core\Database::connection()` returning PDO
- **Routing**: `Core\Router` static methods
- **Templates**: `View::render()` with plain PHP templates
- **Layouts**: `View::setLayout()` and `$this->extend()`
- **Auth**: `AuthController` and `AuthService`
- **Workers**: JWT-based `WorkerAuthenticate`
- **Cache**: `CacheManager` in `core/`
- **AI**: Multi-provider `AIService`

## Workflow System

### Workflow States
The CMS uses a state machine for content workflows:
- **Pending**: Queued but not started
- **Running**: Currently executing
- **Success**: Completed successfully
- **Failed**: Completed with errors
- **Cancelled**: Manually stopped

### Workflow Components
- **WorkflowEngine**: `core/workflowengine.php` - Core execution engine
- **WorkflowManager**: `core/workflowmanager.php` - Workflow management
- **StatusTransitionHandler**: `core/statustransitionhandler.php` - State transitions

### Database Tables
```sql
workflows (id, name, description, content_type, is_active, version)
status_transitions (id, workflow_id, from_status, to_status, permission_required)
approval_instances (id, workflow_type, current_state, tenant_id)
workflow_history (id, instance_id, from_state, to_state, action_by, timestamp)
```

### API Endpoints
- `POST /api/workflows/save` - Save workflow definition
- `POST /api/workflows/execute` - Execute workflow
- `GET /api/workflows/executions` - List executions
- `POST /api/workflows/executions/{id}/cancel` - Cancel execution

### Required Permissions
- `workflow_view` - View workflows
- `workflow_edit` - Create/modify workflows
- `workflow_execute` - Run workflows
- `workflow_admin` - Manage system workflows

## Content Versioning

### Version Control System
Advanced version management with branching:
- Automatic version creation on save
- Full version history with diffs
- Version comparison (Myers algorithm)
- Version restoration with audit trail
- Semantic versioning support (SemVer)

### Database Schema
```sql
versions (id, content_id, version_number, created_by, status, is_current)
version_content (id, version_id, content_type, content_hash, content, compressed)
version_metadata (id, version_id, change_type, change_description, diff_summary)
version_analytics (id, version_id, event_type, event_data, user_id, ip_address)
```

### ContentHistoryManager
Located in `core/ContentHistoryManager.php`:
- `saveVersion($contentId, $authorId, $dataArray)` - Save new version
- `getVersions($contentId)` - List all versions
- `getVersion($contentId, $versionNumber)` - Get specific version
- `restoreVersion($contentId, $versionNumber)` - Restore version

### Notification Events
- `content_version_saved` - New version created
- `content_version_restored` - Version restored

## Permissions & RBAC

### Role-Based Access Control
Granular permission system with:
- Role management (admin, editor, viewer)
- Permission assignment via PermissionManager
- Audit logging via AuditLogger
- Runtime permission verification

### Core Permissions
- Module-level CRUD permissions (create, read, update, delete, manage)
- Content-specific permissions (publish, schedule, moderate)
- Workflow permissions (view, edit, execute, admin)
- System permissions (run_scheduler, manage_users)

### Permission Middleware
```php
// Check permission before action
if (!PermissionMiddleware::check('content.edit')) {
    throw new ForbiddenException();
}
```

### Audit Logging
All permission changes tracked with:
- User ID and IP address
- Timestamp and action context
- Before/after state for changes
- Exportable audit logs

## Notification System

### NotificationManager
Multi-channel notification system:
- **Channels**: Email, SMS, Webhook, In-app
- **Types**: info, warning, error, system
- **Storage**: `logs/notifications.json` (JSON queue)

### API Methods
```php
NotificationManager::queueNotification(string $type, string $message, array $context);
NotificationManager::getQueuedNotifications();
NotificationManager::clearNotification(string $id);
```

### Features
- User notification preferences
- Scheduled notifications
- Bulk actions (mark read, delete)
- HTML-escaped content (XSS protection)

## SEO Engine

### SEO Services
Located in `services/SeoService.php`:
- **Content Analysis**:
  - Readability scoring (Flesch-Kincaid)
  - Keyword extraction
  - Word counting
- **Meta Tag Generation**:
  - Auto-generated titles
  - Auto-generated descriptions
  - Keyword formatting

### Integration
- Template-level meta tag injection
- Real-time editor feedback
- Overall SEO score calculation
- Improvement suggestions

## Publishing Scheduler

### Scheduled Publishing
Automatic content publishing/unpublishing at future dates:

**Database Fields**:
- `publish_at` (DATETIME) - Scheduled publish time
- `unpublish_at` (DATETIME) - Scheduled unpublish time

**Methods** (`core/workflowmanager.php`):
- `publish($contentId)` - Publish immediately
- `schedule($contentId, $publishAt, $unpublishAt)` - Set future dates
- `processScheduledContent()` - Process pending actions

**Cron Setup**:
```bash
*/10 * * * * php /var/www/html/cms/scripts/scheduler.php
```

**Manual Trigger**: `admin/workflow/run_scheduler.php` (requires `run_scheduler` permission)

## Backup Management

### BackupTask
Automated backup system in `core/tasks/`:
- Creates timestamped ZIP archives
- Includes config/ and memory-bank/ directories
- Storage: `/var/www/html/cms/backups/`
- Logging: `logs/migrations.log`

### Admin Endpoints (DEV_MODE only)
- `/admin/test-backup/run_backup.php` - Create backup
- `/admin/test-backup/backup_logs.php` - View logs
- `/admin/test-backup/backup_status.php` - Check status
- `/admin/test-backup/clear_backups.php` - Remove old backups

### Security
- DEV_MODE gated (403 in production)
- Non-recursive backup (top-level files only)
- Automatic directory creation
- Graceful error handling

## Content Export/Import

### Export/Import Services
Bulk content migration system:

**Components**:
- `ContentExportService` - Handles bulk exports
- `ContentImportService` - Data validation and import
- `ContentPackage` - Container for content + metadata
- Export handlers for type-specific processing

**Formats**: JSON, CSV, XML

**Features**:
- Version history preservation
- Relationship mapping
- Metadata retention
- Conflict resolution
- Permission validation
- Audit logging

**Storage**: `/exports/` directory

## Widget System

### Widget Architecture
Located in `admin/components/widgets/`:
- PHP classes with static render methods
- Output buffering for HTML generation
- Theme integration via `render_theme_view()`

**Structure**:
```
public/themes/theme-name/
└── widgets/           # Widget templates
    └── widget-name.php
```

**Recommended Pattern**:
- Move HTML from PHP classes to template files
- Use widget content injection points in layouts
- Extend `render_theme_view()` for widget support

## Builder System

### Page & Theme Builder
Integrated builder components:
- **Page Builder**: Content structure and layout
- **Theme Builder**: Visual styling and presets
- **AI Block**: AI-generated content suggestions
- **Version Control**: Change tracking

**Integration Points**:
- Theme styles via CSS classes
- Theme presets in `/themes/core/`
- AI endpoint: `/api/ai/generate-block`
- Storage: `ContentVersionModel` for versions

**Requirements**:
- FTP-deployable (no CLI)
- Static methods only
- JSON-based configuration

## Security & Compliance

### Compliance Status (2025-09-26)
**PASSED** - Full production compliance:

**Code Hygiene**:
- Trailing `?>` at EOF: **0** (removed all)
- `include/include_once`: **0** (forbidden)
- `require_once`: **300+** (all includes use this)
- Plain `require`: **0** (forbidden)
- Encoding: UTF-8, no BOM, exactly one newline at EOF

**Security Guardrails**:
- `DEV_MODE`: **false** (production locked)
- `admin/.htaccess` deny block: **present**
- Disallowed functions: **0** (no system/exec/shell_exec/passthru/proc_*)
- Database access: **100% centralized** via `\core\Database::connection()`

### CSRF Protection (Complete)
**PASSED** - 0 missing validations:
- All POST endpoints validate CSRF via `csrf_validate_or_403()`
- All 111 POST forms include `csrf_field()` or hidden token
- Consistent implementation across admin and API

### Security Features
**Core Protections**:
- CSRF tokens on all state-changing actions
- Session hardening (HttpOnly, SameSite=Lax, Secure on HTTPS)
- Session regeneration on login
- Unified error handler (dev/prod branching)
- Maintenance mode with 503 + Retry-After
- Security headers (HSTS on HTTPS, CSP deferred)

**Logging & Audit**:
- App errors: `logs/app_errors.log` and `php_errors.log`
- Migrations: `logs/migrations.log` (JSONL)
- Extensions: `logs/extensions.log` with rotation
- Security events: Context tracking (IP, user, timestamp)

**Attack Surface**:
- Public test endpoints: **blocked** (DEV gate + .htaccess)
- Admin sensitive files: **blocked** (.log, .md, .txt, .ht*, dotfiles)
- JWT secrets: Single stable secret from config.php
- No credential exposure in any endpoint

### Recommended Hardening (Backlog)
1. CSP rollout (report-only → enforce with nonce/sha256)
2. Rate limiting on login and sensitive endpoints
3. Audit dashboard for JSONL logs (DEV-gated)
4. Backup integrity checks (hash verification)
5. Secrets rotation playbook

## Database Schema

### Core Tables (58 total)
**Content Management**:
- `content_types` - Content type definitions
- `contents` - Main content storage
- `content_fields` - Custom field definitions
- `content_flags` - Flagged content
- `content_schedules` - Scheduled publishing
- `content_workflow` - Workflow state
- `content_workflow_history` - State transitions

**Version Control**:
- `versions` - Version metadata
- `version_content` - Content snapshots
- `version_metadata` - Change tracking
- `version_analytics` - Version events

**Workflow**:
- `workflows` - Workflow definitions
- `status_transitions` - Allowed transitions
- `approval_instances` - Active approvals
- `workflow_history` - Audit trail

**Analytics**:
- `analytics_monthly_summary` - Aggregated metrics
- `tenant_analytics_events` - Event tracking

**Multi-Tenant**:
- `tenants` - Tenant information
- `content_pages` - Tenant-specific pages
- `user_sites` - User-to-tenant mappings

**System**:
- `system_scheduler_log` - Scheduler history
- `system_alerts` - System notifications
- `system_tasks` - Background tasks
- `workers` - Worker processes
- `worker_metrics` - Performance metrics
- `rate_limits` - API throttling

**AI Integration**:
- `ai_provider_configs` - AI service settings
- `seo_analysis` - SEO recommendations
- `media_metadata` - AI-generated tags

**Theme Management**:
- `theme_variables` - Theme customization
- `settings` - System-wide settings

### Important Constraints
- All tables use `tenant_id` for multi-tenant isolation
- Foreign keys with CASCADE/SET NULL for referential integrity
- Indexes on frequently queried columns (tenant_id, status, timestamps)
- JSON columns for flexible metadata storage

## Important Notes

### Legacy Cleanup Status
**WordPress Contamination** (Removed):
- WordPress-style hooks (`add_action`, `add_filter`) were found in modules
- Replaced with custom EventBus system
- No actual WordPress code, only naming patterns

**Laravel Cleanup** (Complete):
- 462 test errors were **historical artifacts** from old Laravel implementation
- JUnit XML removed (outdated results)
- Current system is framework-free and functioning correctly
- No Laravel dependencies remain in production code

### Code Standards
**Enforced Patterns**:
- Pure PHP, FTP-only deployment
- `require_once` only (no include/include_once)
- No trailing `?>` at end of PHP files
- UTF-8 encoding, no BOM
- Exactly one newline at EOF
- Prepared statements for all database queries
- HTML escaping via `esc()` helper
- CSRF validation on all POST endpoints

**Forbidden Patterns**:
- Laravel/Symfony/framework code
- Composer autoloading
- CLI tools (Artisan, etc.)
- `system()`, `exec()`, `shell_exec()`, `passthru()`, `proc_*`
- Direct `new PDO()` instantiation
- Raw SQL string concatenation
- Unescaped output to HTML

## Additional Documentation

Comprehensive docs in `/docs/` and `/memory-bank/`:
- `docs/architecture.md` - System architecture
- `docs/routing.md` - Routing system details
- `docs/plugins.md` - Plugin development guide
- `docs/api-reference.md` - API documentation
- `docs/version-control.md` - Content versioning
- `docs/database-migration-best-practices.md` - Migration guidelines
- `docs/development-roadmap.md` - Development roadmap
- `memory-bank/reports/final_compliance_2025-09-26.md` - Compliance status
- `memory-bank/reports/final_security_summary_2025-09-26.md` - Security posture
- `memory-bank/database_schema.md` - Complete schema documentation
- `memory-bank/workflow_docs.md` - Workflow system details
- `memory-bank/permissions_architecture.md` - RBAC implementation
- `progress.md` - Production verification log
