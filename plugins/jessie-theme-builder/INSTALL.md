# Jessie Theme Builder - Installation Guide

## 1. Copy Plugin Files

Copy the `jessie-theme-builder` folder to your CMS plugins directory:

```
/var/www/cms/plugins/jessie-theme-builder/
```

## 2. Add API Routing

Add the following code to your CMS router (typically `index.php` or a dedicated router file):

```php
// JTB API Routes
if (preg_match('#^/api/jtb/(\w+)(?:/(\d+))?#', $requestUri, $matches)) {
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/api/router.php';
    exit;
}
```

## 3. Add Builder Page Route

Add the following code to handle the builder page:

```php
// JTB Builder Page
if (preg_match('#^/admin/jtb/edit/(\d+)#', $requestUri, $matches)) {
    $_GET['post_id'] = $matches[1];
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/controller.php';
    exit;
}
```

## 4. Set File Permissions (Linux/WSL)

```bash
cd /var/www/cms/plugins
sudo chown -R www-data:www-data jessie-theme-builder
sudo chmod -R 755 jessie-theme-builder
```

## 5. Create Upload Directory

```bash
mkdir -p /var/www/cms/uploads/jtb
sudo chown -R www-data:www-data /var/www/cms/uploads/jtb
sudo chmod -R 755 /var/www/cms/uploads/jtb
```

## 6. Install Database Tables

The tables will be created automatically when the plugin is first loaded.
Alternatively, run this SQL:

```sql
CREATE TABLE IF NOT EXISTS jtb_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL UNIQUE,
    content JSON NOT NULL,
    css_cache TEXT,
    version VARCHAR(10) DEFAULT '1.0',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_post_id (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS jtb_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    content JSON NOT NULL,
    conditions JSON,
    is_active TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS jtb_global_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100) NOT NULL,
    content JSON NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 7. Add "Edit with JTB" Button (Optional)

Add a link to the post edit page:

```php
<a href="/admin/jtb/edit/<?php echo $postId; ?>" class="btn">
    Edit with Theme Builder
</a>
```

## 8. Testing Checklist

- [ ] Plugin loads without errors
- [ ] Database tables created
- [ ] API endpoints respond:
  - [ ] GET `/api/jtb/modules` - returns module list
  - [ ] GET `/api/jtb/load/{id}` - returns content
  - [ ] POST `/api/jtb/save` - saves content
  - [ ] POST `/api/jtb/render` - returns preview
  - [ ] POST `/api/jtb/upload` - handles file upload
- [ ] Builder interface loads at `/admin/jtb/edit/{post_id}`
- [ ] Can add sections, rows, modules
- [ ] Settings panel opens and saves values
- [ ] Save button works (Ctrl+S)
- [ ] Undo/Redo works (Ctrl+Z / Ctrl+Y)
- [ ] Device preview works (desktop/tablet/phone)
- [ ] Frontend renders correctly

## File Structure

```
jessie-theme-builder/
├── plugin.json              # Plugin manifest
├── plugin.php               # Main plugin class
├── controller.php           # Builder page controller
├── INSTALL.md               # This file
├── includes/
│   ├── class-jtb-element.php    # Base element class
│   ├── class-jtb-registry.php   # Module registry
│   ├── class-jtb-fields.php     # Field renderer
│   ├── class-jtb-renderer.php   # Content renderer
│   ├── class-jtb-settings.php   # Settings panel
│   └── class-jtb-builder.php    # Content manager
├── modules/
│   ├── structure/
│   │   ├── section.php
│   │   ├── row.php
│   │   └── column.php
│   └── content/
│       ├── text.php
│       ├── heading.php
│       ├── image.php
│       └── button.php
├── api/
│   ├── router.php           # API router
│   ├── save.php
│   ├── load.php
│   ├── render.php
│   ├── modules.php
│   └── upload.php
├── assets/
│   ├── css/
│   │   ├── builder.css
│   │   └── frontend.css
│   └── js/
│       ├── builder.js
│       ├── settings-panel.js
│       └── fields.js
└── views/
    ├── builder.php
    └── module-wrapper.php
```

## Troubleshooting

### "CSRF validation failed"
Make sure `csrf_token()` and `csrf_validate_or_403()` functions exist in your CMS.

### "Unauthorized"
Check that `\Core\Session::isLoggedIn()` returns true for logged-in users.

### "Database connection error"
Verify `\core\Database::connection()` returns a valid PDO instance.

### "Module not found"
Ensure all module files in `modules/structure/` and `modules/content/` are loaded.

### Images not uploading
Check that `/uploads/jtb/` directory exists and is writable by the web server.
