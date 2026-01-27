# Theme Builder Preset Library - Installation Guide

## Overview
This package adds **60 pre-built templates** to your Theme Builder:
- 10 Header presets
- 10 Footer presets  
- 10 Sidebar presets
- 10 404 Page presets
- 10 Archive presets
- 10 Single Post presets

## Files Included

1. **presets.php** → `/core/theme-builder/presets.php`
   - Database functions for preset library
   - Must be added to Theme Builder core

2. **install-tb-presets.php** → `/admin/install-tb-presets.php`
   - One-time installation script
   - Run once to populate database with 60 presets

## Installation Steps

### Step 1: Upload presets.php
```
Upload to: /var/www/html/cms/core/theme-builder/presets.php
Permissions: chmod 644 presets.php
```

### Step 2: Edit init.php
Add this line after existing require_once statements in `/core/theme-builder/init.php`:
```php
require_once __DIR__ . '/presets.php';
```

### Step 3: Upload install-tb-presets.php
```
Upload to: /var/www/html/cms/admin/install-tb-presets.php
Permissions: chmod 644 install-tb-presets.php
```

### Step 4: Run Installation
1. Make sure DEV_MODE is enabled in config.php
2. Go to: https://your-domain.com/admin/install-tb-presets.php
3. Wait for "Installation Complete" message
4. Delete install-tb-presets.php (optional but recommended)

### Step 5: Set Permissions (via SSH/Terminal)
```bash
cd /var/www/html/cms
sudo chown www-data:www-data core/theme-builder/presets.php
sudo chmod 644 core/theme-builder/presets.php
```

## Usage

After installation:
1. Go to Theme Builder → Templates
2. Create or edit any template (Header, Footer, etc.)
3. Click the **"📚 Library"** button in the toolbar
4. Browse and select a preset
5. Click "Use This Preset" to load it
6. Customize as needed and save

## Database

The presets are stored in `tb_preset_library` table with these columns:
- id, type, name, slug, description, thumbnail
- content_json (the actual template structure)
- tags, is_premium, sort_order, created_at

## Troubleshooting

**"Access denied" error:**
- Make sure DEV_MODE = true in config.php
- Make sure you're logged in as admin

**"Function not found" error:**
- Verify presets.php was uploaded correctly
- Verify init.php includes presets.php

**No presets showing:**
- Run install-tb-presets.php again
- Check tb_preset_library table in database

## File Checksums
- presets.php: ~230 lines (database functions)
- install-tb-presets.php: ~1170 lines (60 preset definitions)
