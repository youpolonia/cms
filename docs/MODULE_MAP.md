# CMS Module Map

## Architecture Overview

This CMS has TWO routing systems that historically caused duplication:

1. **MVC Router** (`/config/routes.php` → Controllers → Views with sidebar layout)
2. **Direct PHP Files** (`/admin/*.php` - standalone, no sidebar)

**RULE: Direct PHP files are the source of truth. MVC controllers should redirect/include them.**

---

## Module Status

### ✅ Dashboard
- **Primary**: `/admin/dashboard.php` (standalone, modern UI, NO sidebar)
- **MVC Route**: `/admin` → `DashboardController` → includes `dashboard.php`
- **Status**: UNIFIED ✓

### 📝 Content Management

| Module | Direct File | MVC Route | Status |
|--------|-------------|-----------|--------|
| Articles | `/admin/articles.php` | `/admin/articles` | Both exist |
| Article Edit | `/admin/article-edit.php` | `/admin/articles/{id}/edit` | Both exist |
| Pages | `/admin/pages.php` | `/admin/pages` | Both exist |
| Categories | `/admin/article-categories.php` | `/admin/categories` | Both exist |
| Media | `/admin/media.php` | `/admin/media` | Both exist |
| Comments | `/admin/comments_approve.php` | `/admin/comments` | Both exist |
| Galleries | - | `/admin/galleries` | MVC only |
| Menus | - | `/admin/menus` | MVC only |
| Widgets | - | `/admin/widgets` | MVC only |

### 🎯 SEO Tools

| Module | Direct File | MVC Route | Status |
|--------|-------------|-----------|--------|
| SEO Assistant | `/admin/ai-seo-assistant.php` | - | Direct only ✓ |
| SEO Dashboard | `/admin/ai-seo-dashboard.php` | - | Direct only ✓ |
| SEO Pages | `/admin/ai-seo-pages.php` | - | Direct only ✓ |
| SEO Keywords | `/admin/ai-seo-keywords.php` | - | Direct only ✓ |
| SEO Reports | `/admin/ai-seo-reports.php` | - | Direct only ✓ |
| SEO Competitors | `/admin/ai-seo-competitors.php` | - | Direct only ✓ |
| SEO Schema | `/admin/ai-seo-schema.php` | - | Direct only ✓ |
| SEO Linking | `/admin/ai-seo-linking.php` | - | Direct only ✓ |
| SEO Decay | `/admin/ai-seo-decay.php` | - | Direct only ✓ |
| SEO Brief | `/admin/ai-seo-brief.php` | - | Direct only ✓ |
| Redirects | `/admin/seo-redirects.php` | `/admin/urls` | DUPLICATE! |
| Sitemap | `/admin/seo-sitemap.php` | - | Direct only ✓ |
| Robots | `/admin/seo-robots.php` | - | Direct only ✓ |

### 🤖 AI Tools

| Module | Direct File | MVC Route | Status |
|--------|-------------|-----------|--------|
| AI Copywriter | `/admin/ai-copywriter.php` | - | Direct only ✓ |
| AI Images | `/admin/ai-images.php` | - | Direct only ✓ |
| AI Content Creator | `/admin/ai-content-creator.php` | - | Direct only ✓ |
| AI Rewrite | `/admin/ai-content-rewrite.php` | - | Direct only ✓ |
| AI Translate | `/admin/ai-translate.php` | - | Direct only ✓ |
| AI Theme Builder | `/admin/ai-theme-builder.php` | - | Direct only ✓ |
| AI Landing Gen | `/admin/ai-landing-generator.php` | - | Direct only ✓ |
| AI Alt Generator | `/admin/ai-alt-generator.php` | - | Direct only ✓ |
| AI Workflow | `/admin/ai-workflow-generator.php` | - | Direct only ✓ |
| AI Forms | `/admin/ai-forms.php` | - | Direct only ✓ |
| AI Logs | `/admin/ai-logs.php` | - | Direct only ✓ |
| AI Settings | `/admin/ai-settings.php` | - | Direct only ✓ |

### ⚙️ System

| Module | Direct File | MVC Route | Status |
|--------|-------------|-----------|--------|
| Settings | `/admin/settings.php` | - | Direct only ✓ |
| Themes | `/admin/themes.php` | - | Direct only ✓ |
| Plugins | `/admin/plugins-marketplace.php` | - | Direct only ✓ |
| Users | `/admin/user-admin-view.php` | `/admin/users` | DUPLICATE! |
| Backup | `/admin/backup.php` | `/admin/backup` | DUPLICATE! |
| Security | `/admin/security-dashboard.php` | - | Direct only ✓ |
| System Info | `/admin/system.php` | - | Direct only ✓ |
| Logs | - | `/admin/logs` | MVC only |
| Migrations | - | `/admin/migrations` | MVC only |
| Maintenance | - | `/admin/maintenance` | MVC only |
| Extensions | - | `/admin/extensions` | MVC only |

---

## Unification Plan

### Phase 1: Quick Fixes (redirect MVC to Direct)
For modules that exist in both systems, update MVC controller to include direct file:

```php
// Example: BackupController.php
public function index(Request $request): void
{
    require_once dirname(dirname(dirname(__DIR__))) . '/admin/backup.php';
    exit;
}
```

### Phase 2: Identify Dead Code
- Remove unused MVC views that have direct file equivalents
- Keep MVC routes working via redirects for backward compatibility

### Phase 3: Future Development
**ALL NEW MODULES should be Direct PHP files only:**
- Location: `/admin/{module-name}.php`
- Style: Dark Catppuccin theme
- Layout: Standalone (no sidebar dependency)
- Links from: Dashboard module grid

---

## Navigation Strategy

**Dashboard** (`/admin`) serves as the central hub with module grid.
- No sidebar needed
- All modules accessible via categorized tiles
- Each module is self-contained

---

## File Naming Convention

- All filenames: **lowercase**
- Multi-word: **hyphen-separated** (e.g., `ai-seo-assistant.php`)
- NO uppercase files
- NO underscores in new files (legacy allowed)
