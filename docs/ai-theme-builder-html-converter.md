# AI Theme Builder 4.0 - HTML Converter

## Overview

The HTML Converter is a new generation mode for AI Theme Builder that allows AI to generate free-form HTML/CSS, which is then automatically converted to Theme Builder 3.0 JSON structure.

**Problem solved:** AI forced to generate rigid 52-module TB JSON directly produces generic designs.

**Solution:** AI → Free HTML/CSS → PHP Converter → TB JSON → Renderer

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    AI Theme Builder 4.0                         │
├─────────────────────────────────────────────────────────────────┤
│  User Brief                                                     │
│      ↓                                                          │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐         │
│  │  AI Model   │ →  │   HTML/CSS  │ →  │  Converter  │         │
│  │ (GPT/Claude)│    │  (Creative) │    │  (PHP 8.1)  │         │
│  └─────────────┘    └─────────────┘    └─────────────┘         │
│                                              ↓                  │
│                                        ┌─────────────┐         │
│                                        │  TB JSON    │         │
│                                        │  (52 mods)  │         │
│                                        └─────────────┘         │
│                                              ↓                  │
│                                        ┌─────────────┐         │
│                                        │  Renderer   │         │
│                                        │  (Preview)  │         │
│                                        └─────────────┘         │
└─────────────────────────────────────────────────────────────────┘
```

## File Structure

```
/var/www/html/cms/
├── core/theme-builder/
│   ├── ai-html-prompts.php          # AI prompts for HTML generation
│   └── html-converter/
│       ├── index.php                # Security (403 block)
│       ├── Converter.php            # Main orchestrator
│       ├── StyleExtractor.php       # CSS → TB design properties
│       ├── SectionDetector.php      # Semantic section detection
│       ├── LayoutAnalyzer.php       # Grid/Flex layout analysis
│       └── ElementMapper.php        # HTML elements → TB modules
│
├── app/controllers/admin/
│   └── aithemebuildercontroller.php # +generateWithHtml(), +convertHtml()
│
├── app/views/admin/ai-theme-builder/
│   └── index.php                    # +Generation Mode toggle
│
├── config/
│   └── routes.php                   # +2 new routes
│
├── admin/
│   └── test-html-converter.php      # Interactive test page
│
└── .htaccess                        # +16 MVC rules for AI Theme Builder
```

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/admin/ai-theme-builder/generate-html` | POST | Full theme generation via HTML |
| `/admin/ai-theme-builder/convert-html` | POST | Raw HTML → TB JSON utility |

### Request: generate-html

```json
{
    "brief": "Modern tech startup website...",
    "business_name": "Acme Corp",
    "industry": "technology",
    "style_preference": "modern",
    "model": "gpt-4o",
    "page_names": ["Home", "About", "Services", "Contact"],
    "sections": ["hero", "features", "about", "testimonials", "cta", "contact"],
    "csrf_token": "..."
}
```

### Response

```json
{
    "success": true,
    "theme": {
        "header": { "title": "...", "content": { "sections": [...] }, "source_html": "..." },
        "pages": [
            { "title": "Home", "slug": "home", "content": { "sections": [...] }, "source_html": "..." }
        ],
        "footer": { "title": "...", "content": { "sections": [...] }, "source_html": "..." },
        "meta": {
            "business_name": "Acme Corp",
            "industry": "technology",
            "style": "modern",
            "method": "html-converter",
            "generated_at": "2025-12-30 18:30:00"
        }
    }
}
```

## Converter Classes

### 1. Converter.php (Main Orchestrator)
- `convert(string $html): array` - Main entry point
- `processElement()` - Recursive element processing with grid detection
- `buildGridRow()` - Multi-column row construction
- `detectSectionName()` - Section naming from class/id patterns

### 2. StyleExtractor.php
- Parses `<style>` tags and inline styles
- Extracts CSS variables (`:root`, `var()`)
- Maps 35+ CSS properties to TB design
- Handles shorthand expansion (padding, margin, border)

### 3. SectionDetector.php
- Detects semantic HTML5 sections (header, footer, section, article)
- Pattern matching for 30+ section types (hero, features, pricing, etc.)
- Deduplication and nesting resolution
- Document order sorting

### 4. LayoutAnalyzer.php
- CSS Grid detection and parsing
- Flexbox analysis
- Framework column detection (Bootstrap, Tailwind, Foundation)
- Structural column detection (similar siblings)
- Column width calculation

### 5. ElementMapper.php
**Simple mappings (14 types):**
- heading (h1-h6)
- text (p)
- image (img)
- video (video)
- embed (iframe - YouTube, Vimeo detection)
- divider (hr)
- list (ul/ol)
- quote (blockquote)
- code (pre/code)
- table
- button
- icon (Font Awesome, Material, Bootstrap Icons)
- map (Google Maps)

**Complex patterns (10 types):**
- blurb (icon/image + heading + text)
- testimonial (quote + name + role + avatar)
- team_member (image + name + role + bio + social)
- pricing (title + price + features + button)
- cta (heading + text + button)
- counter (number + label)
- accordion (items with title/content)
- tabs (tab headers + panels)
- social (social media links)

## Usage

### In UI (Admin Panel)
1. Go to `/admin/ai-theme-builder`
2. Select "🎨 HTML Converter" mode in AI Settings
3. Fill in project details
4. Click "Generate Theme"

### Programmatic
```php
require_once CMS_ROOT . '/core/theme-builder/html-converter/Converter.php';
// ... other requires ...

$converter = new \Core\ThemeBuilder\HtmlConverter\Converter();
$tbJson = $converter->convert($htmlString);
// $tbJson = ['sections' => [...]]
```

### Test Page
Visit `/admin/test-html-converter.php` for interactive testing.

## Technical Constraints

- Pure PHP 8.1+ (no frameworks)
- FTP-only deployment
- require_once only
- No CLI tools
- UTF-8 encoding
- CSRF protection required

## Version History

- **4.0.0** (2025-12-30): Initial release
  - Core converter with 5 classes
  - 24 module types supported
  - Multi-column grid/flex detection
  - UI integration with mode toggle

---
*Part of Jessie AI-CMS by Piotr*
