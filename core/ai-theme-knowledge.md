# AI Theme Builder — Knowledge Base
# This file is the AI's complete reference for generating CMS themes.
# Read this BEFORE generating any code. Follow every rule precisely.

---

## 1. Theme Directory Structure

```
themes/{slug}/
├── theme.json              ← Design system + section definitions
├── layout.php              ← Master layout (head, header, footer, scripts)
├── sections/               ← Homepage sections (loaded dynamically)
│   ├── hero.php
│   ├── about.php
│   ├── features.php        ← (or services, portfolio, menu, etc.)
│   ├── articles.php
│   └── cta.php
├── templates/              ← Page templates
│   ├── home.php            ← Section loader (reads sections/ dir)
│   ├── page.php            ← Single page
│   ├── article.php         ← Single article
│   ├── articles.php        ← Article listing + sidebar
│   ├── gallery.php         ← Photo gallery
│   └── 404.php             ← Error page
├── assets/
│   ├── css/style.css       ← Complete stylesheet
│   └── js/main.js          ← Interactions (scroll, menu, animations)
└── content/
    └── demo.json           ← Optional demo content seed
```

---

## 2. theme.json — Full Schema

```json
{
  "name": "Display Name",
  "description": "One-line description",
  "version": "1.0.0",
  "author": "AI Theme Builder",
  "supports": {
    "theme-builder": true,
    "custom-header": true,
    "custom-footer": true,
    "custom-colors": true
  },
  "options": {
    "show_header": true,
    "show_footer": true,
    "body_background": "#hex",
    "preload_fonts": true
  },
  "colors": {
    "primary": "#hex",
    "secondary": "#hex",
    "accent": "#hex",
    "background": "#hex",
    "surface": "#hex",
    "text": "#hex",
    "text_muted": "#hex",
    "border": "#3d2e1e",
    "success": "#hex",
    "warning": "#hex",
    "error": "#hex"
  },
  "typography": {
    "fontFamily": "Inter",
    "headingFont": "Playfair Display",
    "baseFontSize": "16",
    "lineHeight": "1.7",
    "fontWeight": "400",
    "headingWeight": "700"
  },
  "header": {
    "background": "transparent",
    "sticky": true,
    "blur": true,
    "height": "80",
    "logoSize": "36"
  },
  "buttons": {
    "borderRadius": "4",
    "paddingX": "32",
    "paddingY": "14",
    "fontWeight": "600",
    "uppercase": true,
    "shadow": true
  },
  "layout": {
    "containerWidth": "1200",
    "sectionSpacing": "120",
    "borderRadius": "8"
  },
  "effects": {
    "shadowStrength": "15",
    "hoverScale": "1.03",
    "transitionSpeed": "300"
  },
  "homepage_sections": [
    {"id": "hero", "label": "Hero", "icon": "⭐", "required": true},
    {"id": "about", "label": "About", "icon": "📖"},
    {"id": "pages", "label": "Explore", "icon": "📋"},
    {"id": "articles", "label": "Latest News", "icon": "📰"},
    {"id": "cta", "label": "Call to Action", "icon": "🎯"}
  ]
}
```

---

## 3. PHP Functions Available in Templates

### Content & Customization
```php
// Get customized value (Theme Studio). ALWAYS use for editable text.
theme_get('section.field', 'Default fallback text')

// Escape HTML output. ALWAYS wrap user-facing text.
esc($string)

// Get site info
get_site_name()    // → string
get_site_logo()    // → string|null (URL)
get_active_theme() // → string (slug like "starter-restaurant")
get_body_class()   // → string
```

### Rendering
```php
// SEO meta tags — call in <head>
render_seo_meta($pageData)

// Navigation menu
render_menu('header', ['class' => 'nav-links', 'link_class' => 'nav-link', 'wrap' => false])
render_menu('footer', ['class' => 'footer-links', 'link_class' => 'footer-link', 'wrap' => false])

// Theme Studio CSS
generate_theme_css_variables($themeConfig)  // CSS variables from theme.json
generate_studio_css_overrides()             // User customizations from Theme Studio

// Head/body helpers
theme_render_favicon()          // <link rel="icon">
theme_render_og_image()         // <meta og:image>
theme_render_announcement_bar() // Banner after <body>
```

### Section Manager
```php
// Used in home.php for dynamic section loading
theme_get_section_order()         // → array of section IDs in user-defined order
theme_section_enabled($sectionId) // → bool
```

---

## 4. Variables Available in Each Template

### layout.php
```php
$themeConfig   // array — parsed theme.json
$siteName      // string — site name
$siteLogo      // string|null — logo URL
$tsLogo        // string|null — theme_get('brand.logo') ?: $siteLogo
$showHeader    // bool
$showFooter    // bool
$isTbPage      // bool — is JTB page?
$content       // string — rendered template HTML
$page          // array — current page data
$themePath     // string — e.g. "/themes/my-theme"
```

### home.php (via HomeController)
```php
$pages    // array — published pages for this theme [{id, slug, title, content, featured_image, ...}]
$articles // array — latest articles [{id, slug, title, excerpt, content, featured_image, category_name, published_at, created_at, views, ...}]
```

### page.php (via PageController)
```php
$page // array — {id, slug, title, content, featured_image, template, status, meta_title, meta_description, ...}
```

### article.php (via ArticleController)
```php
$article // array — {id, slug, title, excerpt, content, featured_image, category_name, published_at, created_at, views, ...}
```

### articles.php (via ArticlesController)
```php
$articles    // array of articles
$categories  // array — [{id, name, slug, article_count}]
$currentPage // int
$totalPages  // int
$total       // int — total article count
```

### gallery.php
Gallery data is loaded directly via DB query inside the template:
```php
// Query galleries table WHERE is_public=1 AND (theme=active OR theme IS NULL)
// Each gallery has: id, name, slug, description, display_template, images[]
// Each image: id, filename, title, caption, sort_order
// Image URL pattern: /uploads/media/{filename}
// display_template: grid | masonry | mosaic | carousel | justified
```

---

## 5. data-ts Attributes — Theme Studio Bindings

These attributes make content editable via Theme Studio's live preview.

```html
<!-- Text content -->
<h1 data-ts="hero.headline"><?= esc(theme_get('hero.headline', 'Default')) ?></h1>

<!-- Background image -->
<div data-ts-bg="hero.bg_image" style="background:url(<?= esc($bgUrl) ?>)..."></div>

<!-- Link href -->
<a data-ts-href="hero.btn_link" href="<?= esc(theme_get('hero.btn_link', '#')) ?>">

<!-- Combined pattern (most common) -->
<a href="<?= esc(theme_get('cta.btn_link', '#')) ?>"
   class="btn btn-primary"
   data-ts="cta.btn_text"
   data-ts-href="cta.btn_link">
    <?= esc(theme_get('cta.btn_text', 'Get Started')) ?>
</a>
```

### Standard section fields (data-ts naming):
- `hero.headline`, `hero.subtitle`, `hero.btn_text`, `hero.btn_link`, `hero.bg_image`, `hero.badge`
- `about.label`, `about.title`, `about.description`, `about.image`
- `pages.label`, `pages.title`, `pages.description`
- `articles.label`, `articles.title`, `articles.description`, `articles.btn_text`, `articles.btn_link`
- `cta.title`, `cta.description`, `cta.btn_text`, `cta.btn_link`, `cta.bg_image`
- `footer.description`, `footer.copyright`
- `header.cta_text`, `header.cta_link`
- `brand.site_name`, `brand.logo`

---

## 6. Required layout.php Structure

```php
<?php
if (!defined('CMS_ROOT')) define('CMS_ROOT', dirname(__DIR__, 2));
if (!defined('CMS_APP')) define('CMS_APP', CMS_ROOT . '/app');

require_once CMS_APP . '/helpers/functions.php';
if (file_exists(CMS_ROOT . '/includes/helpers/menu.php'))
    require_once CMS_ROOT . '/includes/helpers/menu.php';
$jtbBootPath = CMS_ROOT . '/plugins/jessie-theme-builder/includes/jtb-frontend-boot.php';
if (file_exists($jtbBootPath)) require_once $jtbBootPath;

$themeConfig = get_theme_config();
$themeOptions = $themeConfig['options'] ?? [];
$showHeader = $themeOptions['show_header'] ?? true;
$showFooter = $themeOptions['show_footer'] ?? true;

$siteName = theme_get('brand.site_name', get_site_name());
$siteLogo = theme_get('brand.logo', get_site_logo());
$tsLogo = theme_get('brand.logo') ?: $siteLogo;

$pageData = $page ?? [];
if (!empty($title) && empty($pageData['title'])) $pageData['title'] = $title;
$isTbPage = !empty($page['is_tb_page']);
$themeCssVariables = generate_theme_css_variables($themeConfig);
$themePath = '/themes/' . basename(__DIR__);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= render_seo_meta($pageData) ?>
    <!-- Google Fonts link here -->
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="/assets/css/tb-frontend.css">
    <link rel="stylesheet" href="<?= $themePath ?>/assets/css/style.css">
    <style>
<?= $themeCssVariables ?>
<?= generate_studio_css_overrides() ?>
    </style>
<?= function_exists("theme_render_favicon") ? theme_render_favicon() : "" ?>
<?= function_exists("theme_render_og_image") ? theme_render_og_image() : "" ?>
</head>
<body class="<?= esc(get_body_class() ?? '') ?><?= $isTbPage ? ' tb-page' : '' ?>">
<?= function_exists("theme_render_announcement_bar") ? theme_render_announcement_bar() : "" ?>

<?php if ($showHeader): ?>
    <!-- HEADER HTML HERE -->
    <div class="mobile-overlay" id="mobileOverlay"></div>
<?php endif; ?>

<?php if ($isTbPage): ?>
    <?= $content ?? '' ?>
<?php else: ?>
    <main><?= $content ?? '' ?></main>
<?php endif; ?>

<?php if ($showFooter): ?>
    <!-- FOOTER HTML HERE -->
<?php endif; ?>

<script src="<?= $themePath ?>/assets/js/main.js"></script>
</body>
</html>
```

---

## 7. Required Header Structure

```html
<header class="site-header" id="siteHeader">
    <div class="header-container">
        <a href="/" class="header-logo" data-ts="brand.logo">
            <?php if ($tsLogo): ?>
                <img src="<?= esc($tsLogo) ?>" alt="<?= esc(theme_get('brand.site_name', $siteName)) ?>">
            <?php else: ?>
                <span class="logo-text" data-ts="brand.site_name"><?= esc(theme_get('brand.site_name', $siteName)) ?></span>
            <?php endif; ?>
        </a>
        <nav class="header-nav" id="headerNav">
            <?= render_menu('header', ['class' => 'nav-links', 'link_class' => 'nav-link', 'wrap' => false]) ?>
        </nav>
        <a href="<?= esc(theme_get('header.cta_link', '#contact')) ?>"
           class="header-cta"
           data-ts="header.cta_text"
           data-ts-href="header.cta_link">
            <?= esc(theme_get('header.cta_text', 'Get Started')) ?>
        </a>
        <button class="mobile-toggle" id="mobileToggle" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>
```

Required IDs: `siteHeader`, `headerNav`, `mobileToggle`, `mobileOverlay`
Required classes: `.site-header`, `.header-container`, `.header-logo`, `.logo-text`, `.header-nav`, `.nav-links`, `.nav-link`, `.header-cta`, `.mobile-toggle`

---

## 8. Required Footer Structure

```html
<footer class="site-footer">
    <div class="footer-top">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="/" class="footer-logo" data-ts="brand.logo">
                        <!-- same logo pattern as header -->
                    </a>
                    <p class="footer-tagline" data-ts="footer.description">
                        <?= esc(theme_get('footer.description', 'Default description.')) ?>
                    </p>
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <!-- Additional footer columns: nav, contact, hours, etc. -->
                <div class="footer-nav">
                    <h4>Links</h4>
                    <?= render_menu('footer', ['class' => 'footer-links', 'link_class' => 'footer-link', 'wrap' => false]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <p data-ts="footer.copyright">
                <?= theme_get('footer.copyright') ? esc(theme_get('footer.copyright')) : '&copy; ' . date('Y') . ' ' . esc(theme_get('brand.site_name', $siteName)) . '. All rights reserved.' ?>
            </p>
        </div>
    </div>
</footer>
```

---

## 9. Required home.php Structure

```php
<?php
// Pre-load all customizable values
$heroHeadline = theme_get('hero.headline', 'Default Headline');
$heroSubtitle = theme_get('hero.subtitle', 'Default subtitle text.');
$heroBtnText  = theme_get('hero.btn_text', 'Get Started');
$heroBtnLink  = theme_get('hero.btn_link', '#');
$heroBgImage  = theme_get('hero.bg_image');

$aboutLabel = theme_get('about.label', 'About Us');
$aboutTitle = theme_get('about.title', 'Our Story');
$aboutDesc  = theme_get('about.description', 'Description text.');
$aboutImage = theme_get('about.image');

// ... same pattern for all sections ...

// Dynamic section loading
$themeConfig = get_theme_config(get_active_theme());
$defaultOrder = array_column($themeConfig['homepage_sections'] ?? [], 'id');
$sectionOrder = theme_get_section_order();
if (empty($sectionOrder)) $sectionOrder = $defaultOrder;

foreach ($sectionOrder as $sectionId) {
    if (!theme_section_enabled($sectionId)) continue;
    $sectionFile = __DIR__ . '/../sections/' . $sectionId . '.php';
    if (file_exists($sectionFile)) require $sectionFile;
}
?>
```

---

## 10. Section PHP Pattern

Every section file follows this pattern:

```php
<?php
// Variables inherited from home.php parent scope
// e.g. $aboutLabel, $aboutTitle, $aboutDesc, $aboutImage
?>
<section class="section about-section" id="about">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="about.label"><?= esc($aboutLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
            <p class="section-desc" data-ts="about.description"><?= esc($aboutDesc) ?></p>
        </div>
        <!-- Section-specific content here -->
    </div>
</section>
```

### Pages loop (for displaying CMS pages):
```php
<?php if (!empty($pages)): ?>
<?php foreach ($pages as $p): ?>
<a href="/page/<?= esc($p['slug']) ?>" data-animate>
    <?php if (!empty($p['featured_image'])): ?>
    <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" loading="lazy">
    <?php endif; ?>
    <h3><?= esc($p['title']) ?></h3>
</a>
<?php endforeach; ?>
<?php endif; ?>
```

### Articles loop (for displaying latest articles):
```php
<?php if (!empty($articles)): ?>
<?php foreach (array_slice($articles, 0, 4) as $a): ?>
<a href="/article/<?= esc($a['slug']) ?>" data-animate>
    <?php if (!empty($a['featured_image'])): ?>
    <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" loading="lazy">
    <?php endif; ?>
    <?php if (!empty($a['category_name'])): ?>
    <span class="tag"><?= esc($a['category_name']) ?></span>
    <?php endif; ?>
    <span class="date"><?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
    <h3><?= esc($a['title']) ?></h3>
    <p><?= esc(mb_strimwidth(strip_tags(!empty($a['excerpt']) ? $a['excerpt'] : $a['content']), 0, 130, '...')) ?></p>
</a>
<?php endforeach; ?>
<?php endif; ?>
```

---

## 11. CSS Requirements

### Mandatory :root variables
```css
:root {
    /* Colors — from theme.json, use var() EVERYWHERE below */
    --primary: #hex;
    --primary-light: #hex;
    --primary-dark: #hex;
    --secondary: #hex;
    --accent: #hex;
    --background: #hex;
    --surface: #hex;
    --surface-elevated: #hex;
    --surface-card: #hex;
    --text: #hex;
    --text-muted: #hex;
    --text-dim: #hex;
    --border: rgba(...);
    --border-hover: rgba(...);

    /* Typography */
    --font-heading: 'Heading Font', serif;
    --font-body: 'Body Font', sans-serif;

    /* Layout */
    --radius: 4-12px;
    --radius-lg: 8-16px;
    --transition: 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    --transition-slow: 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    --container: 1200px;
    --container-narrow: 800px;
    --section-spacing: 100-140px;
    --header-height: 64-80px;
}
```

### Mandatory selectors to style (ALL of these MUST exist):

**Layout:** `.container`, `.container-narrow`, `.section`, `.section-header`, `.section-label`, `.section-divider`, `.section-title`, `.section-desc`

**Buttons:** `.btn`, `.btn-primary`, `.btn-outline` (with hover: translateY + box-shadow)

**Header:** `.site-header`, `.site-header.header-scrolled` (with backdrop-filter), `.header-container`, `.header-logo`, `.logo-text`, `.header-nav`, `.nav-links`, `.nav-link` (with ::after underline animation), `.header-cta`, `.mobile-toggle` (3 spans → X on `.toggle-active`), `.mobile-overlay`, `.header-nav.nav-open`

**Hero:** `.hero`, `.hero-bg`, `.hero-overlay`, `.hero-content`, `.hero-title`, `.hero-subtitle`, `.hero-actions`

**Page templates:** `.page-hero`, `.page-hero-overlay`, `.page-hero-title`, `.page-breadcrumb`, `.breadcrumb-sep`, `.page-content-section`

**Prose:** `.prose p`, `.prose h2`, `.prose h3`, `.prose blockquote`, `.prose img`, `.prose ul/ol`, `.prose a`, `.prose hr`

**Articles:** `.articles-layout` (grid: 1fr 300px), `.articles-grid`, `.article-card`, `.article-card-img`, `.article-card-tag`, `.article-card-body`, `.article-card-date`, `.sidebar-widget`, `.sidebar-widget h4`, `.sidebar-cat-link`, `.sidebar-cat-count`, `.pagination`, `.pagination-info`, `.article-meta`, `.article-category`, `.article-featured-img`, `.article-back`

**Gallery:** `.gallery-section`, `.gallery-header`, `.gallery-desc`, `.gallery-count`

**404:** `.error-section`, `.error-code`, `.error-title`, `.error-text`, `.error-actions`

**Footer:** `.site-footer`, `.footer-top`, `.footer-grid` (3-4 columns), `.footer-brand`, `.footer-logo`, `.footer-tagline`, `.footer-social a`, `.footer-grid h4`, `.footer-links`, `.footer-link`, `.footer-bottom`

**Animations:** `[data-animate]` (opacity:0, translateY:24px), `[data-animate].animated` (opacity:1, translateY:0)

**Utility:** `body.tb-page main` (padding-top), `body.menu-open` (overflow:hidden), `img` (max-width:100%)

### CSS Rules
- NEVER use hardcoded hex outside :root — use var(--name) everywhere
- ALL interactive elements must have transition
- Hover states: translateY(-2px) + box-shadow for cards/buttons
- Use cubic-bezier, not plain "ease"
- Responsive: @media 1024px, 768px, 480px (stack grids, hide nav, reduce spacing)
- Minimum 600 lines

---

## 12. main.js Requirements

Must include:
1. Header scroll effect: add `.header-scrolled` class on scroll > 60px
2. Mobile menu: toggle `.nav-open`, `.toggle-active`, `.overlay-active`, `body.menu-open`
3. Smooth scroll for `a[href^="#"]` links
4. Scroll animations: IntersectionObserver for `[data-animate]` → add `.animated` class (with staggered delay)
5. Hero parallax: translateY on `.hero-bg` based on scroll (use requestAnimationFrame)

Required IDs: `siteHeader`, `headerNav`, `mobileToggle`, `mobileOverlay`

---

## 13. Font Awesome Icons Available

The CMS includes Font Awesome 6.x. Use icons freely:
- `fas fa-` — solid icons (most common)
- `far fa-` — regular (outline) icons
- `fab fa-` — brand icons (facebook-f, instagram, twitter, github, linkedin-in, tripadvisor, etc.)

Common useful icons: `fa-utensils`, `fa-code`, `fa-palette`, `fa-pen`, `fa-camera`, `fa-chart-line`, `fa-users`, `fa-star`, `fa-heart`, `fa-arrow-right`, `fa-chevron-right`, `fa-calendar`, `fa-eye`, `fa-envelope`, `fa-phone`, `fa-map-marker-alt`, `fa-book-open`, `fa-newspaper`, `fa-quote-left`, `fa-diamond`, `fa-leaf`, `fa-award`, `fa-wine-glass-alt`, `fa-check`, `fa-globe`, `fa-shield-alt`, `fa-rocket`, `fa-lightbulb`, `fa-cog`

---

## 14. Quality Standards

### What makes a PROFESSIONAL theme:
- **Typography hierarchy**: Clear visual distinction between h1→h6, section labels (small, uppercase, letter-spacing), descriptions (muted, lighter weight)
- **Generous whitespace**: Sections spaced 100-140px apart. Content doesn't feel cramped.
- **Consistent rhythm**: Same spacing patterns repeated throughout
- **Subtle decorative elements**: Gradient lines, ornamental dividers, border accents — NOT overdone
- **Photo-forward design**: Images dominate, text complements. Hover zoom on images.
- **Premium hover states**: Cards lift (translateY), buttons glow (box-shadow), links animate
- **Dark themes**: Rich darks (#0f-#1a range), NOT pure black. Gold/warm accents.
- **Light themes**: Off-whites (#f8-#ff range), NOT pure white. Subtle shadows.
- **Responsive grace**: Not just "it works on mobile" — it looks GOOD on mobile

### What makes an AMATEUR theme:
- Flat, no depth (missing shadows, transitions, hover effects)
- Cramped spacing
- Inconsistent typography (random sizes, weights)
- Pure black/white backgrounds
- No hover states
- No animations
- Inline styles everywhere
- Same card design for everything
- Generic placeholder text not matching industry

---

## 15. Content Isolation

All content is filtered by `theme_slug`:
- Pages: `WHERE theme_slug = :theme OR theme_slug IS NULL`
- Articles: same pattern
- Menus: `render_menu()` prioritizes matching `theme_slug`

When seeding content for a generated theme, set `theme_slug` to the theme slug.

---

## 16. Gallery System

Gallery CSS is loaded separately: `/public/css/gallery-layouts.css`
Gallery JS is loaded separately: `/public/js/gallery-layouts.js`

These provide 5 layout templates: grid, masonry, mosaic, carousel, justified.
Plus a full lightbox implementation (keyboard nav, touch swipe).

The gallery template should include both CSS and JS links and query the DB directly.
