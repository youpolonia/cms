# Jessie CMS

A modern, AI-powered content management system built with PHP 8.2+. No frameworks, no Composer — just clean, fast, deployable code.

## Features

### 🎨 AI Theme Builder
Generate complete, production-ready themes with AI. 5-step wizard: Configure → Content → Design → Build → Publish. 190 pattern templates (headers, heroes, sections, footers) ensure structural consistency while AI handles the creative design.

### 🖱️ Visual Editor
Click-to-edit any element on your site. AI-powered content generation and design suggestions. Drag & drop sections, undo/redo, and real-time preview.

### 🧩 Jessie Theme Builder (JTB)
Drag & drop page builder with 79 modules across 8 categories. Build complex layouts without code.

### 🎯 SEO Suite
15 AI-powered SEO tools: content analysis, keyword tracking, competitor research, broken link checker, bulk meta editor, score timeline, image alt text generator, internal linking suggestions.

### 🎨 Theme Studio
Full visual customizer with live preview. Brand colors, typography, layout, effects — all editable with instant feedback. 24 industry color presets, Google Fonts integration.

### 📱 Responsive
All generated themes are mobile-first with responsive navigation, touch-friendly interfaces, and optimized layouts.

### 🌙 Dark Mode
Auto-detected system preference with manual toggle. Generated themes include full dark mode CSS variables.

### ♿ Accessible
Skip navigation, focus-visible outlines, ARIA attributes, semantic HTML, lazy-loaded images.

### 🔌 REST API
Read-only public API for headless CMS usage. JSON responses with pagination.

## Requirements

- PHP 8.2+
- MySQL 5.7+ / MariaDB 10.3+
- Apache with mod_rewrite
- GD extension (for image optimization)

## Quick Start

```bash
# 1. Clone
git clone https://github.com/youpolonia/cms.git /var/www/cms

# 2. Create database
mysql -u root -p -e "CREATE DATABASE jessie_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 3. Configure
cp config.example.php config.php
# Edit config.php with your database credentials

# 4. Set permissions
chown -R www-data:www-data /var/www/cms
chmod -R 755 /var/www/cms

# 5. Import database
mysql -u root -p jessie_cms < database/schema.sql

# 6. Visit your site
# http://yoursite.com/admin/setup-wizard
```

## Directory Structure

```
cms/
├── app/
│   ├── controllers/     # MVC controllers (admin + front + api)
│   ├── helpers/         # Global helper functions
│   ├── models/          # BaseModel + domain models
│   └── views/           # Blade-free PHP templates
├── config/
│   ├── routes.php       # All HTTP routes
│   └── ai_settings.json # AI provider configuration
├── core/                # Framework core (router, database, session, etc.)
├── plugins/
│   └── jessie-theme-builder/  # JTB drag & drop builder
├── themes/              # CMS themes (layout.php + assets + templates)
├── public/              # Static assets (CSS, JS, uploads)
├── tests/               # Unit test suite
└── index.php            # Entry point
```

## Architecture

- **Zero frameworks** — no Laravel, Symfony, or Composer dependencies
- **MVC routing** — clean REST routes in `config/routes.php`
- **PDO singleton** — `\Core\Database::connection()`
- **CSRF protection** — on all POST routes
- **Rate limiting** — login + API endpoints
- **Theme system** — filesystem-based with `theme.json` + `layout.php`
- **AI multi-provider** — OpenAI, Anthropic, DeepSeek, Google, HuggingFace
- **FTP-deployable** — upload and go, no build step

## AI Providers

Configure in `/admin/settings` or `config/ai_settings.json`:

| Provider | Models | Use Case |
|----------|--------|----------|
| Anthropic | Claude Opus/Sonnet | Best quality themes & content |
| OpenAI | GPT-4o, o1 | Good general purpose |
| DeepSeek | V3, R1 | Budget-friendly |
| Google | Gemini 2.0 | Alternative |
| HuggingFace | Various | Open source models |

## Admin Roles

| Role | Permissions |
|------|------------|
| Admin | Full access — users, settings, themes, plugins |
| Editor | Content management — pages, articles, media, SEO |
| Viewer | Read-only dashboard access |

## API

Public read-only endpoints:

```
GET /api/v1/articles          # List articles
GET /api/v1/articles/{slug}   # Single article
GET /api/v1/pages             # List pages
GET /api/v1/pages/{slug}      # Single page
GET /api/v1/menus             # List menus
GET /api/v1/menus/{location}  # Menu by location
GET /api/v1/site              # Site info
```

## Tests

```bash
sudo -u www-data php tests/run_all_tests.php
# 143 tests (102 core + 41 plugin), 0 failures
```

## License

Proprietary. All rights reserved.

## Credits

Built by Piotr. Named after Jessie 🐕 — the best dog who ever lived.
