# TB Preset Library - Safe Installation Guide

## ⚠️ SAFETY FIRST

**THESE ARE NEW FILES ONLY** - No existing files are modified!

| File | Destination | Action |
|------|-------------|--------|
| `presets.php` | `/core/theme-builder/presets.php` | NEW FILE |
| `install-tb-presets.php` | `/admin/install-tb-presets.php` | NEW FILE |
| `presets-api.php` | `/admin/api/theme-builder/presets.php` | NEW FILE |

---

## 📦 What's Included

**60 Pre-built Templates:**
- 10 Header designs (minimal, corporate, creative, ecommerce, blog, etc.)
- 10 Footer designs (simple, multi-column, newsletter, mega, dark, etc.)
- 10 Sidebar designs (blog, shop, newsletter, social, tags, etc.)
- 10 404 Page designs (simple, creative, search, navigation, etc.)
- 10 Archive designs (grid, list, masonry, cards, magazine, etc.)
- 10 Single Post designs (classic, modern, magazine, minimal, etc.)

---

## 🔧 Installation Steps

### Step 1: Create API directory (if needed)
```bash
# SSH into server
mkdir -p /var/www/html/cms/admin/api/theme-builder
chmod 755 /var/www/html/cms/admin/api/theme-builder
```

### Step 2: Upload files via FTP

Upload these files:

1. `presets.php` → `/var/www/html/cms/core/theme-builder/presets.php`
2. `presets-api.php` → `/var/www/html/cms/admin/api/theme-builder/presets.php`
3. `install-tb-presets.php` → `/var/www/html/cms/admin/install-tb-presets.php`

### Step 3: Set permissions
```bash
sudo chown www-data:www-data /var/www/html/cms/core/theme-builder/presets.php
sudo chown www-data:www-data /var/www/html/cms/admin/api/theme-builder/presets.php
sudo chown www-data:www-data /var/www/html/cms/admin/install-tb-presets.php
sudo chmod 644 /var/www/html/cms/core/theme-builder/presets.php
sudo chmod 644 /var/www/html/cms/admin/api/theme-builder/presets.php
sudo chmod 644 /var/www/html/cms/admin/install-tb-presets.php
```

### Step 4: Add require_once to init.php

**ONLY CHANGE NEEDED in existing file!**

Edit `/core/theme-builder/init.php` and add this line after other require_once statements:

```php
require_once __DIR__ . '/presets.php';
```

### Step 5: Run installer

1. Ensure `DEV_MODE = true` in config.php
2. Go to: `https://your-domain.com/admin/install-tb-presets.php`
3. Wait for success message
4. (Optional) Delete `install-tb-presets.php` after

---

## ✅ Verification

After installation, verify:

1. **Database table exists:**
   ```sql
   SHOW TABLES LIKE 'tb_preset_library';
   SELECT COUNT(*) FROM tb_preset_library; -- Should be 60
   ```

2. **API works:**
   ```
   GET /admin/api/theme-builder/presets.php
   ```

3. **Presets load:**
   Go to Theme Builder → Templates → Edit any template
   (Library button will be added in next phase)

---

## 🔄 Rollback (if needed)

If anything goes wrong:

1. Delete new files:
   ```bash
   rm /var/www/html/cms/core/theme-builder/presets.php
   rm /var/www/html/cms/admin/api/theme-builder/presets.php
   rm /var/www/html/cms/admin/install-tb-presets.php
   ```

2. Remove require_once line from init.php

3. Drop database table (optional):
   ```sql
   DROP TABLE IF EXISTS tb_preset_library;
   ```

---

## 📋 Files Summary

| File | Lines | Size | Purpose |
|------|-------|------|---------|
| presets.php | 273 | 8 KB | Database functions |
| install-tb-presets.php | 1173 | 87 KB | 60 preset definitions + installer |
| presets-api.php | 68 | 2 KB | JSON API endpoint |

---

## 🚀 Next Phase

After this installation works, we'll add:
1. "📚 Library" button to template-edit.php toolbar
2. Modal UI for browsing presets
3. One-click preset loading

These UI changes will be minimal additions to template-edit.php.
