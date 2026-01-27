# CMS Architecture Reset Plan
**Author:** Claude (AI Architect)  
**Date:** 2025-12-04  
**Status:** In Progress

---

## 1. Vision

Build a modern, professional AI-powered CMS that:
- Has clean URLs (no .php extensions)
- Uses consistent authentication pattern
- Follows MVC-like architecture (without frameworks)
- Is secure, maintainable, and extensible
- Looks and feels professional

---

## 2. Technical Constraints (ABSOLUTE)

These rules are NON-NEGOTIABLE:

| Rule | Reason |
|------|--------|
| Pure PHP 8.1+ | No frameworks, no Composer |
| FTP-only deployment | No CLI access in production |
| NO system()/exec()/shell_exec() | Security + FTP constraint |
| require_once only | No autoloaders |
| No closing ?> | Prevent whitespace issues |
| UTF-8 no BOM | Encoding consistency |

---

## 3. New Directory Structure

```
/var/www/html/cms/
├── public/                 # Web root (DocumentRoot should point here)
│   ├── index.php          # Single entry point (front controller)
│   ├── assets/            # CSS, JS, images
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── .htaccess          # URL rewriting
│
├── app/                    # Application code
│   ├── Controllers/       # Request handlers
│   │   ├── Admin/         # Admin panel controllers
│   │   │   ├── DashboardController.php
│   │   │   ├── PagesController.php
│   │   │   ├── ArticlesController.php
│   │   │   ├── UsersController.php
│   │   │   ├── MediaController.php
│   │   │   └── SettingsController.php
│   │   └── Front/         # Public site controllers
│   │       ├── HomeController.php
│   │       └── PageController.php
│   │
│   ├── Models/            # Database models
│   │   ├── Page.php
│   │   ├── Article.php
│   │   ├── User.php
│   │   ├── Admin.php
│   │   ├── Category.php
│   │   └── Setting.php
│   │
│   ├── Views/             # Templates
│   │   ├── admin/         # Admin templates
│   │   │   ├── layouts/
│   │   │   │   └── main.php
│   │   │   ├── dashboard/
│   │   │   ├── pages/
│   │   │   ├── articles/
│   │   │   └── ...
│   │   └── front/         # Public templates
│   │       ├── layouts/
│   │       └── pages/
│   │
│   ├── Middleware/        # Request middleware
│   │   ├── AuthMiddleware.php
│   │   ├── CsrfMiddleware.php
│   │   └── AdminMiddleware.php
│   │
│   └── Helpers/           # Utility functions
│       ├── functions.php
│       └── View.php
│
├── core/                   # Framework core (keep existing, refactor)
│   ├── Router.php         # Clean URL router
│   ├── Database.php       # PDO wrapper (existing)
│   ├── Session.php        # Session management
│   ├── Request.php        # Request wrapper
│   ├── Response.php       # Response wrapper
│   └── App.php            # Application bootstrap
│
├── modules/               # AI and extension modules (preserve existing)
│   ├── ai/
│   │   ├── SeoAssistant.php
│   │   ├── ContentRewrite.php
│   │   ├── HuggingFace.php
│   │   └── ...
│   └── integrations/
│       ├── N8n.php
│       └── ...
│
├── config/                # All configuration
│   ├── app.php           # Main config
│   ├── database.php      # DB credentials
│   ├── routes.php        # Route definitions
│   └── ai.php            # AI settings
│
├── storage/              # Runtime data
│   ├── logs/
│   ├── cache/
│   └── uploads/
│
└── database/             # Migrations and seeds
    ├── migrations/
    └── seeds/
```

---

## 4. Routing System

### Clean URLs
```
/admin                     → Admin\DashboardController@index
/admin/pages               → Admin\PagesController@index
/admin/pages/create        → Admin\PagesController@create
/admin/pages/1/edit        → Admin\PagesController@edit
/admin/pages/1/delete      → Admin\PagesController@delete (POST)
/admin/articles            → Admin\ArticlesController@index
/admin/settings            → Admin\SettingsController@index
/admin/ai/seo              → Admin\Ai\SeoController@index
/about                     → Front\PageController@show (slug: about)
```

### Route Definition (config/routes.php)
```php
<?php
return [
    // Admin routes (require auth)
    'GET /admin' => ['Admin\\DashboardController', 'index', ['auth' => true]],
    'GET /admin/pages' => ['Admin\\PagesController', 'index', ['auth' => true]],
    'GET /admin/pages/create' => ['Admin\\PagesController', 'create', ['auth' => true]],
    'POST /admin/pages' => ['Admin\\PagesController', 'store', ['auth' => true, 'csrf' => true]],
    'GET /admin/pages/{id}/edit' => ['Admin\\PagesController', 'edit', ['auth' => true]],
    'POST /admin/pages/{id}' => ['Admin\\PagesController', 'update', ['auth' => true, 'csrf' => true]],
    'POST /admin/pages/{id}/delete' => ['Admin\\PagesController', 'destroy', ['auth' => true, 'csrf' => true]],
    
    // Auth routes
    'GET /admin/login' => ['Admin\\AuthController', 'showLogin'],
    'POST /admin/login' => ['Admin\\AuthController', 'login', ['csrf' => true]],
    'GET /admin/logout' => ['Admin\\AuthController', 'logout'],
    
    // Public routes
    'GET /' => ['Front\\HomeController', 'index'],
    'GET /{slug}' => ['Front\\PageController', 'show'],
];
```

---

## 5. Authentication Flow (Single Pattern)

```
Request → Router → AuthMiddleware → Controller → Response
                        ↓
              Check $_SESSION['admin_id']
              Check $_SESSION['admin_role']
                        ↓
              Fail? → Redirect to /admin/login
```

### Session Variables (standardized)
```php
$_SESSION['admin_id']        // Admin user ID
$_SESSION['admin_username']  // Username
$_SESSION['admin_role']      // 'admin' or other role
$_SESSION['csrf_token']      // CSRF protection
$_SESSION['login_time']      // For session timeout
```

---

## 6. Implementation Phases

### Phase 1: Core Framework (Day 1)
- [ ] Create public/index.php (front controller)
- [ ] Create public/.htaccess (URL rewriting)
- [ ] Create core/App.php (bootstrap)
- [ ] Create core/Router.php (clean URLs)
- [ ] Create core/Request.php
- [ ] Create core/Response.php
- [ ] Create app/Middleware/AuthMiddleware.php
- [ ] Create app/Middleware/CsrfMiddleware.php

### Phase 2: Admin Authentication (Day 1)
- [ ] Create app/Controllers/Admin/AuthController.php
- [ ] Create app/Views/admin/auth/login.php
- [ ] Create app/Views/admin/layouts/main.php
- [ ] Test login/logout flow

### Phase 3: Core Admin Modules (Day 2-3)
- [ ] Dashboard with real stats
- [ ] Pages CRUD (full)
- [ ] Articles CRUD (full)
- [ ] Categories CRUD
- [ ] Media Library
- [ ] Users Management

### Phase 4: Settings & Config (Day 3)
- [ ] Settings module
- [ ] AI configuration
- [ ] Integration settings (n8n, HuggingFace)

### Phase 5: AI Modules Integration (Day 4)
- [ ] Migrate existing AI code to new structure
- [ ] SEO Assistant
- [ ] Content Rewrite
- [ ] Other AI tools

### Phase 6: Polish & Testing (Day 5)
- [ ] UI consistency
- [ ] Error handling
- [ ] Security audit
- [ ] Performance optimization

---

## 7. Database Schema (Final)

```sql
-- Core tables
CREATE TABLE admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    last_login DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(191) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    content MEDIUMTEXT,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    author_id INT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES admins(id) ON DELETE SET NULL
);

CREATE TABLE articles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(191) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    excerpt TEXT NULL,
    content MEDIUMTEXT,
    featured_image VARCHAR(500) NULL,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    category_id INT UNSIGNED NULL,
    author_id INT UNSIGNED NULL,
    published_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES admins(id) ON DELETE SET NULL
);

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(191) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    parent_id INT UNSIGNED NULL,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE media (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    path VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255) NULL,
    uploaded_by INT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES admins(id) ON DELETE SET NULL
);

CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT NULL,
    `group` VARCHAR(50) DEFAULT 'general',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE article_categories (
    article_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (article_id, category_id),
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);
```

---

## 8. File Preservation Strategy

### Keep (migrate to modules/)
- core/ai_*.php → modules/ai/
- core/n8n_*.php → modules/integrations/
- core/database.php → core/Database.php (refactor)

### Keep (as reference)
- admin/ai-seo-assistant.php (1679 lines of working code)
- admin/ai-content-rewrite.php
- Other large, working AI modules

### Delete
- All 18-line scaffold files
- Duplicate implementations
- Old Laravel remnants
- Unused config files

---

## 9. Success Criteria

- [ ] Clean URLs work (/admin/pages not /admin/pages.php)
- [ ] Single login, session persists across all admin pages
- [ ] All CRUD operations work for Pages, Articles, Categories
- [ ] Media upload works
- [ ] AI modules accessible and functional
- [ ] Professional UI with consistent styling
- [ ] No PHP errors or warnings
- [ ] Mobile responsive admin panel

---

## 10. Notes

- Preserve DEV_MODE gating for development features
- Keep bcrypt for password hashing
- Maintain CSRF protection on all forms
- Use prepared statements for all DB queries
- Escape all output with htmlspecialchars()
