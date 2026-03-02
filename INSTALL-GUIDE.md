# Jessie CMS — Installation Guide

## Requirements

- **PHP** 8.2+ with extensions: pdo_mysql, json, mbstring, curl, gd/imagick, fileinfo, zip
- **MySQL** 5.7+ or MariaDB 10.3+
- **Apache** 2.4+ with mod_rewrite enabled
- **Disk space:** ~300 MB (includes themes, plugins)

## Quick Install

### 1. Upload Files

Upload all CMS files to your web root (e.g., `/var/www/cms/` or `public_html/`).

```bash
# Set correct permissions
chown -R www-data:www-data /var/www/cms/
chmod -R 755 /var/www/cms/
chmod -R 775 /var/www/cms/uploads/ /var/www/cms/cache/ /var/www/cms/logs/
```

### 2. Create Database

```sql
CREATE DATABASE jessie_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'jessie'@'localhost' IDENTIFIED BY 'your-password';
GRANT ALL PRIVILEGES ON jessie_cms.* TO 'jessie'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Configure

Copy `config.example.php` → `config.php` and fill in database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'jessie_cms');
define('DB_USER', 'jessie');
define('DB_PASS', 'your-password');
```

### 4. Run Installer

Navigate to `https://your-domain.com/install.php` in your browser.

The installer will:
1. Check system requirements
2. Import database schema (81 core tables)
3. Create admin account
4. Set up initial configuration

### 5. Setup Wizard

After installation, log in to `/admin` and the Setup Wizard will guide you through:
- AI provider configuration (OpenAI, Anthropic, DeepSeek, Google, HuggingFace)
- Starter website selection
- Basic site settings

## Plugin Installation

Plugins are auto-discovered from the `plugins/` directory. Each plugin has an `install.php` that creates required database tables.

To install a plugin:
1. Upload plugin folder to `plugins/`
2. Visit `/admin/plugins`
3. Click "Install" — this runs `install.php` (creates tables, seeds data)
4. Enable the plugin

### Included Plugins (19)

| Plugin | Description |
|--------|-------------|
| jessie-theme-builder | Drag & drop page builder (79 modules) |
| jessie-booking | Appointment scheduling |
| jessie-newsletter | Email newsletters & campaigns |
| jessie-restaurant | Restaurant menu & orders |
| jessie-lms | Learning management (courses, quizzes) |
| jessie-membership | Membership plans & gated content |
| jessie-events | Events, tickets, QR check-in |
| jessie-directory | Business directory & listings |
| jessie-jobs | Job board & applications |
| jessie-realestate | Property listings |
| jessie-affiliate | Referral programs |
| jessie-portfolio | Project showcase |
| jessie-saas-core | SaaS platform (auth, credits, billing) |
| jessie-seowriter | AI SEO content tool |
| jessie-copywriter | AI copywriting tool |
| jessie-imagestudio | AI image processing |
| jessie-social | Social media management |
| jessie-emailmarketing | Email marketing automation |
| jessie-analytics | Website analytics |

## AI Configuration

Create `config/ai_settings.json` from the example:

```bash
cp config/ai_settings.example.json config/ai_settings.json
```

Add your API keys for desired providers:
- **OpenAI** — GPT-4, GPT-4o, o1
- **Anthropic** — Claude 3.5 Sonnet, Claude 4
- **DeepSeek** — deepseek-v3, deepseek-r1
- **Google** — Gemini 2.0 Flash
- **HuggingFace** — Free image processing

## Docker (Alternative)

```bash
docker-compose up -d
```

This starts Apache + MySQL with the CMS pre-configured.

## Troubleshooting

- **500 Error**: Check `logs/php_errors.log` and ensure `mod_rewrite` is enabled
- **Permission denied**: Run `chown -R www-data:www-data /var/www/cms/`
- **Missing tables**: Run `php core/ensure-tables.php` to create shop/dropshipping tables
- **Plugin errors**: Check that plugin's `install.php` was run

## Version

Current: **v0.15.0** (2026-02-25)
