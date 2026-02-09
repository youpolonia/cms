# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Architecture Overview

This is **Jessie AI-CMS** — a custom PHP 8.2+ CMS built **without Laravel or any framework**. The system uses a custom router, manual dependency injection, and vanilla PHP patterns throughout.

**System Requirements**:
- **PHP**: 8.2+
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Web Server**: Apache with mod_rewrite (WSL or Linux)

**Current Status** (2026-02-09):
- ✅ 567 PHP files, 55 database tables, 1,145 total files
- ✅ 60 unit tests (custom TestRunner, no PHPUnit)
- ✅ Zero framework dependencies
- ✅ Complete CSRF protection coverage
- ✅ JTB (Jessie Theme Builder) — visual page builder with 79 modules + AI integration
- ✅ MVC architecture for admin (39 controllers) + legacy admin pages (43 total)

### Critical Constraints
- **NO FRAMEWORKS**: Do not use Laravel, Symfony, CodeIgniter, or any framework
- **NO COMPOSER**: No autoloaders, all includes via explicit `require_once`
- **NO `include`**: Only `require_once` (never `include` or `include_once`)
- **FTP-ONLY DEPLOYMENT**: All code must work via direct FTP upload
- **Pure PHP**: No build steps, no package managers, no compiled assets

### Entry Point & Bootstrap

**Single entry**: `index.php` — handles all requests (frontend + admin + API)

```
1. require core/bootstrap.php
2. require core/error_handler.php → cms_register_error_handlers()
3. Define constants: CMS_ROOT, CMS_APP, CMS_CORE, CMS_CONFIG
4. require version.php, models/settingsmodel.php, includes/thememanager.php
5. require core/controllerregistry.php, core/router.php
6. CSRF protection (csrf_boot)
7. Session management
8. Auth check for admin routes
9. MVC routing via Core\Router (config/routes.php)
10. Legacy admin fallback for non-MVC pages
```

**Bootstrap** (`core/bootstrap.php`):
```
1. Security headers — cms_emit_security_headers()
2. Session hardening — Strict mode, HttpOnly, Secure, SameSite
3. EventBus — getInstance() and dispatch 'system.init'
4. Error reporting — Configure based on DEV_MODE
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

### Configuration
**Primary**: `config.php` (constants — gitignored)
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cms');
define('DB_USER', 'cms_user');
define('DB_PASSWORD', '...');
```

**AI Settings**: `config/ai_settings.json` (gitignored, template in `config/ai_settings.example.json`)

## File Structure

```
cms/
├── index.php               # Single entry point (all requests)
├── version.php             # CMS version manifest
├── config.php              # DB credentials (gitignored)
├── .htaccess               # Apache rewrite rules
│
├── app/                    # MVC Application
│   ├── controllers/
│   │   ├── admin/          # 39 admin controllers
│   │   └── front/          # 5 front controllers
│   ├── views/
│   │   ├── admin/          # Admin views (37 dirs + layouts/)
│   │   └── front/          # Front views (home, articles, page, etc.)
│   └── helpers/            # View helpers
│
├── admin/                  # Legacy admin pages
│   ├── *.php               # 43 admin pages (AI tools, SEO, n8n, settings)
│   ├── api/                # Admin AJAX endpoints
│   ├── assets/             # Admin JS/CSS/fonts
│   ├── css/                # Admin stylesheets
│   ├── includes/           # Admin shared includes
│   └── views/              # Admin-specific views
│
├── config/                 # Configuration
│   ├── routes.php          # MVC route definitions
│   ├── ai.php              # AI provider config loader
│   ├── ai_settings.json    # API keys (gitignored)
│   ├── credentials.php     # Admin credentials
│   ├── security.php        # Security settings
│   ├── session.php         # Session config
│   └── *.json              # Various settings
│
├── core/                   # Core system (~70 files)
│   ├── bootstrap.php       # System initialization
│   ├── router.php          # MVC routing engine
│   ├── database.php        # PDO singleton
│   ├── csrf.php            # CSRF protection
│   ├── session_boot.php    # Session management
│   ├── error_handler.php   # Error/exception handling
│   ├── security_headers.php # Security headers
│   ├── eventbus.php        # Event system
│   ├── cache.php           # CacheManager
│   ├── ai_*.php            # ~25 AI tool modules
│   ├── seo.php             # SEO functions
│   └── tasks/              # Scheduled tasks
│
├── includes/               # Shared includes (14 files)
│   ├── init.php            # Core initialization
│   ├── auth.php            # Authentication helpers
│   ├── helpers/            # Utility functions
│   ├── middleware/          # Request middleware
│   └── system/             # System utilities
│
├── models/                 # Data models (5 files)
│   ├── settingsmodel.php
│   ├── user.php
│   ├── notification.php
│   ├── worker.php
│   └── shift.php
│
├── plugins/
│   └── jessie-theme-builder/   # JTB plugin (see below)
│
├── api/                    # Public API endpoints
│   ├── ai/                 # AI API endpoints
│   ├── v1/                 # API v1
│   └── gateway/            # API gateway
│
├── themes/                 # Theme definitions
│   ├── default/            # Default theme
│   ├── jessie/             # Jessie theme
│   ├── blank/              # Blank theme
│   ├── jtb/                # JTB theme
│   ├── core/               # Theme core (handler)
│   └── presets/            # Theme presets (modern, corporate, light, dark)
│
├── tests/                  # Unit tests (60 tests, 13 files)
│   ├── run_all_tests.php   # Test runner
│   ├── TestRunner.php      # Custom framework
│   └── *Test.php           # Test files
│
├── public/                 # Static assets
│   ├── js/                 # Frontend JS
│   └── css/                # Frontend CSS
│
├── content/blog/           # Blog content (file-based)
├── data_models/            # DB connection helper
├── logs/                   # Log files (gitignored)
├── uploads/                # User uploads (gitignored)
├── cache/                  # Cache files
└── docs/                   # Slim README only
```

## MVC Architecture

### Routing: `config/routes.php`
All MVC routes defined in a single file. Router dispatches to controllers:
```php
Router::get('/admin/dashboard', 'Admin\DashboardController@index');
Router::post('/admin/articles/store', 'Admin\ArticlesController@store');
```

### Controllers
**Admin** (39 in `app/controllers/admin/`):
Analytics, Articles, Auth, AutomationRules, Automations, Backup, Categories, Comments,
Content, Dashboard, EmailCampaigns, EmailQueue, EmailSettings, Extensions, Galleries,
Gdpr, JtbApi, Jtb, Logs, Maintenance, Media, Menus, Migrations, Modules, N8nBindings,
N8nSettings, Notifications, Pages, Plugins, Profile, Scheduler, Search, SecurityDashboard,
ThemeEditor, Themes, Urls, Users, VersionControl, Widgets

**Front** (5 in `app/controllers/front/`):
Article, Articles, Features, Home, Page

### Autoloader
SPL autoloader in `index.php`: `Admin\SomeController` → `app/controllers/admin/somecontroller.php`

### Views
- Admin views: `app/views/admin/{controller-name}/` (e.g., `app/views/admin/articles/`)
- Front views: `app/views/front/` (home.php, articles.php, page.php, etc.)
- Admin layout: `app/views/admin/layouts/` (topbar, sidebar, Catppuccin dark theme)

### Legacy Admin Pages
43 pages in `admin/*.php` — mostly AI tools and SEO pages:
- **AI tools** (27): ai-alt-generator, ai-competitor-tracker, ai-content-brief, etc.
- **SEO** (7): seo-dashboard, seo-metadata, seo-edit, seo-redirects, seo-url-inspector, seo-robots, seo-sitemap
- **System** (6): settings, clear-cache, login, logout, content-suggestions, content-quality
- **n8n** (2): n8n-workflows, n8n-settings
- **Navigation**: navigation.php → redirects to MVC `/admin/menus`

### Admin Layout Systems (IMPORTANT!)
CMS has TWO admin layout systems — both correct:
- **MVC**: `app/views/admin/layouts/topbar.php` — CSS vars: `--border`, `--accent`
- **Legacy**: `admin/includes/topbar_nav.php` + `page_header.php` — CSS vars: `--border-color`, `--accent-color`
- **DO NOT mix** CSS variable names between systems!
- Both use **Catppuccin dark theme**: `#1e293b`, `#334155`, `#e2e8f0`

## Jessie Theme Builder (JTB)

### Overview
Visual page builder plugin with **79 modules** in 8 categories. AI can generate entire websites from prompts.

**Location**: `plugins/jessie-theme-builder/`

**Detailed docs**: `plugins/jessie-theme-builder/CLAUDE.md` (2700+ lines)

### Structure
```
plugins/jessie-theme-builder/
├── includes/           # 16+ PHP classes
│   ├── ai/             # 14 AI classes
│   └── jtb-frontend-boot.php  # Lightweight frontend bootstrap
├── modules/            # 79 modules (structure, content, interactive, media, forms, blog, fullwidth, theme)
├── api/                # 20+ endpoints (CRUD, AI, render)
├── views/              # UI (builder, template-editor, website-builder)
├── assets/             # JS (14 files) + CSS (6 files)
├── admin/              # Admin views
└── controllers/        # JTB controllers
```

### AI Integration
- **Multi-provider**: OpenAI, Anthropic, DeepSeek, Google, HuggingFace
- **AI Core**: `includes/ai/class-jtb-ai-core.php` — singleton, provider switching
- **Website Generator**: `includes/ai/class-jtb-ai-website.php` — full site generation
- **Multi-Agent Pipeline**: Mockup → Architect → Content → Stylist → SEO → Images → Assemble
- **CSS Extractor**: `includes/ai/class-jtb-css-extractor.php` — preserves styles from mockups
- **Stock Images**: `includes/ai/class-jtb-ai-pexels.php` — Pexels integration

### Key Endpoints
- `/api/jtb/ai/generate` — unified generation (layout/section/module)
- `/api/jtb/ai/generate-website` — full website
- `/api/jtb/ai/multi-agent` — pipeline
- `/api/jtb/ai/save-website` — save to CMS

### Frontend Integration
- `includes/jtb-frontend-boot.php` — loads 17 classes + 79 modules
- `JTB_Theme_Integration::renderHeader()` / `renderFooter()` — template rendering
- Used by theme `header.php` / `footer.php`

## Framework Restrictions (CRITICAL)

### Do NOT Use:
- `Schema::create()`, `Schema::table()` — Use raw SQL in migrations
- `Artisan::call()` — No CLI commands
- `Eloquent` models — Use PDO prepared statements
- `Blade` templates — Use plain PHP
- Laravel facades — Use custom implementations
- `Illuminate\*` components — Custom-built only
- Composer autoloading — Explicit `require_once` only

### Use Instead:
- **Database**: `\core\Database::connection()` → PDO
- **Routing**: `Core\Router` static methods
- **Templates**: `View::render()` with plain PHP templates
- **Auth**: `AuthController` and `AuthService`
- **Cache**: `CacheManager` in `core/cache.php`
- **Events**: `EventBus` in `core/eventbus.php`
- **AI**: Multi-provider via `config/ai.php` + JTB AI classes

## Security

### CSRF Protection (Complete)
- All POST endpoints validate via `csrf_validate_or_403()`
- All forms include `csrf_field()` or hidden token

### Session Security
- HttpOnly, SameSite=Lax, Secure on HTTPS
- Session regeneration on login
- Unified error handler (dev/prod branching)

### Security Headers
- HSTS on HTTPS
- X-Content-Type-Options: nosniff
- X-Frame-Options: DENY
- CSP (deferred for full rollout)

### Access Control
- DEV_MODE gate for development features
- Admin auth via `AuthController::requireLogin()`
- JWT-based worker authentication

## Database Schema (55 tables)

### Content
`articles`, `article_categories`, `categories`, `comments`, `content`, `content_blocks`, `content_versions`, `pages`

### JTB (Theme Builder)
`jtb_pages`, `jtb_templates`, `jtb_template_conditions`, `jtb_global_modules`, `jtb_library_templates`, `jtb_library_categories`, `jtb_theme_settings`

### Users & Auth
`users`, `admins`, `roles`, `permissions`, `role_permissions`, `login_attempts`, `blocked_ips`

### SEO
`seo_metadata`, `seo_keywords`, `seo_redirects`, `seo_crawl_log`

### Analytics
`analytics_events`, `analytics_daily_stats`, `analytics_content_stats`, `page_views`, `search_logs`, `tenant_metrics`

### Email
`email_queue`

### Media
`media`, `galleries`, `gallery_images`, `gallery_items`

### System
`settings`, `system_settings`, `maintenance_settings`, `security_settings`, `security_policies`, `security_logs`, `activity_logs`, `backups`, `extensions`, `menus`, `menu_items`, `migrations`, `redirects`, `restoration_log`, `scheduler_jobs`, `sites`, `tenants`, `widgets`

### Workers
`content_versions` (versioning for scheduler)

## Testing

### Custom Test Framework
No PHPUnit — uses `tests/TestRunner.php`:
```bash
# Run all tests (as www-data)
sudo -u www-data php /var/www/cms/tests/run_all_tests.php
```

### Test Files (13 files, 60 tests)
- **DatabaseTest** (6): connection, query, prepared statements
- **RouterTest** (5): route matching, parameters
- **CsrfTest** (5): token generation, validation
- **JtbElementTest** (4): element creation, attributes
- **JtbTemplateTest** (10): template CRUD, rendering
- **CacheTest** (5): set, get, clear, expiry
- **HelpersTest** (3): XSS escaping
- **AuthTest** (4): auth functions
- **SeoTest** (3): SEO defaults
- **JtbCssExtractorTest** (5): CSS parsing, variable resolution
- **AiConfigTest** (3): provider config
- **EventBusTest** (3): event dispatch/listen
- **MvcControllersTest** (4): controller loading

## Code Standards

### Enforced Patterns
- Pure PHP, FTP-only deployment
- `require_once` only (never `include` or `include_once`)
- No trailing `?>` at end of PHP files
- UTF-8 encoding, no BOM
- Prepared statements for all database queries
- HTML escaping via `esc()` or `h()` helper
- CSRF validation on all POST endpoints
- File ownership: `www-data:www-data` for all CMS files

### Forbidden Patterns
- Laravel/Symfony/framework code
- Composer autoloading
- CLI tools (Artisan, etc.)
- `system()`, `exec()`, `shell_exec()`, `passthru()`, `proc_*`
- Direct `new PDO()` instantiation
- Raw SQL string concatenation
- Unescaped output to HTML
- Hardcoded light theme colors (`white`, `#fff`, `#f8f9fa`, `#333`, `#666`)

### Dark Theme Rule
All admin pages MUST use Catppuccin dark theme:
- Background: `#1e293b` (base), `#334155` (surface)
- Text: `#e2e8f0` (primary), `#94a3b8` (secondary)
- Accent: `#89b4fa` (blue), `#a6e3a1` (green)
- Use CSS variables, never hardcoded light colors

## File Permissions

After editing files in `/var/www/cms/`:
```bash
sudo chown www-data:www-data <file>
```

For log files in `/tmp/`:
```bash
sudo touch /tmp/<log>.log
sudo chown www-data:www-data /tmp/<log>.log
sudo chmod 666 /tmp/<log>.log
```

## Git

- Remote: `origin` → `github.com/youpolonia/cms.git`
- Branch: `main`
- `.gitignore` covers: `config.php`, `config/ai_settings.json`, `.env`, `logs/`, `uploads/`, `node_modules/`, etc.
- Template config: `config/ai_settings.example.json` (copy and fill API keys)
