# CLAUDE.md - Jessie Theme Builder (JTB)

## PODSUMOWANIE PROJEKTU

JTB to visual page builder dla Jessie CMS, inspirowany Divi Builder. Plugin pozwala na wizualne budowanie stron z sekcjami, wierszami, kolumnami i modułami. Zawiera także Theme Builder do tworzenia header/footer/body templates.

---

## LOKALIZACJE PLIKÓW

### Źródło (Windows)
```
C:\Users\krala\Downloads\jessie theme builder\
├── docs/              # Dokumentacja (CZYTAJ PRZED ZMIANAMI!)
├── future-features/   # Plany nowych komponentów (AI Copilot, Command Palette, etc.)
├── plugin/            # Kod pluginu
│   ├── plugin.php     # Główna klasa pluginu
│   ├── plugin.json    # Manifest
│   ├── controller.php # Kontroler dla /admin/jtb/edit/{id}
│   ├── includes/      # Klasy PHP (16 plików)
│   ├── modules/       # Moduły (8 kategorii, 45+ modułów)
│   ├── api/           # Endpointy API (20 plików)
│   ├── views/         # Widoki (6 plików)
│   ├── controllers/   # Kontrolery MVC
│   └── assets/        # CSS i JS
│       ├── css/       # 5 plików CSS
│       └── js/        # 9 plików JS
```

### Cel (WSL/Linux)
```
/var/www/cms/plugins/jessie-theme-builder/
```

---

## KOMPLETNA STRUKTURA PLIKÓW

### includes/ (16 plików)
```
class-jtb-builder.php           # Zarządzanie contentem (save/load)
class-jtb-css-generator.php     # Generator CSS z theme settings
class-jtb-dynamic-context.php   # Kontekst dynamiczny dla theme modules
class-jtb-element.php           # BAZOWA KLASA - wszystkie moduły dziedziczą
class-jtb-fields.php            # Renderer pól PHP
class-jtb-fonts.php             # Google Fonts integracja
class-jtb-global-modules.php    # Global reusable modules
class-jtb-registry.php          # Rejestr modułów
class-jtb-renderer.php          # Renderowanie HTML + CSS
class-jtb-settings.php          # Ustawienia pluginu
class-jtb-template-conditions.php   # Warunki dla templates
class-jtb-template-matcher.php      # Matcher template -> request
class-jtb-templates.php             # CRUD templates
class-jtb-theme-integration.php     # Integracja frontend override
class-jtb-theme-settings.php        # Global theme settings (10 grup)
```

### includes/ai/ (13 plików) - AI Integration
```
# Core
class-jtb-ai-core.php           # Singleton, komunikacja z AI (HuggingFace, OpenAI, Anthropic, DeepSeek, Google)
class-jtb-ai-schema.php         # Eksporter schematów wszystkich 78 modułów dla AI
class-jtb-ai-context.php        # Builder kontekstu (strona, site, style, branding)
class-jtb-ai-prompts.php        # Szablony promptów dla wszystkich modułów i sekcji
class-jtb-ai-content.php        # Generatory contentu dla wszystkich 78 modułów
class-jtb-ai-images.php         # Generowanie obrazów AI, integracja z media library
class-jtb-ai-pexels.php         # Integracja z Pexels API (pobiera obrazy na podstawie kontekstu)
class-jtb-ai-styles.php         # Profesjonalne presety stylów (kolory, typografia, spacing)

# NEW: Layout AST Pipeline (01.02.2026)
class-jtb-ai-layout-ast.php     # Schema i walidacja Layout AST (abstrakcyjne drzewo layoutu)
class-jtb-ai-layout-engine.php  # AI-driven layout generation (FAKTYCZNIE wywołuje AI!)
class-jtb-ai-layout-compiler.php # Kompilacja AST → JTB JSON (czysta transformacja)
class-jtb-ai-generator.php      # Główny generator - obsługuje AST i legacy mode

# Legacy Compositional System
class-jtb-ai-composer.php       # Hardcoded pattern sequences (legacy)
class-jtb-ai-pattern-renderer.php # Pattern → JTB renderer (legacy)
class-jtb-ai-autofix.php        # Auto-fix engine (Stages 11-17)
class-jtb-ai-confidence.php     # Confidence scoring + stop conditions
```

### modules/ (8 kategorii)
```
structure/      # section.php, row.php, column.php
content/        # text, heading, image, button, blurb, divider, code, cta,
                # number_counter, circle_counter, bar_counter, icon,
                # testimonial, team_member, pricing_table, social_follow,
                # comments, sidebar, countdown, post_navigation, shop
interactive/    # accordion.php, accordion_item.php, tabs.php, tabs_item.php, toggle.php
media/          # audio.php, video.php, gallery.php, slider.php, map.php
forms/          # contact_form.php, login.php, signup.php, search.php
blog/           # blog.php, portfolio.php, post_slider.php
fullwidth/      # fullwidth_header, fullwidth_image, fullwidth_menu,
                # fullwidth_slider, fullwidth_portfolio, fullwidth_code,
                # fullwidth_map, fullwidth_post_slider, fullwidth_post_title
theme/          # NEW! 8 modułów dla Theme Builder:
                # featured-image.php, post-excerpt.php, post-meta.php,
                # author-box.php, related-posts.php, archive-title.php,
                # breadcrumbs.php, archive-posts.php, menu.php, post-content.php,
                # post-title.php, search-form.php, site-logo.php, social-icons.php
```

### api/ (20 plików)
```
router.php              # Main router dla /api/jtb/*
# Page Builder
modules.php             # GET lista modułów
load.php                # GET content posta
save.php                # POST zapisz content
render.php              # POST renderuj preview
upload.php              # POST upload obrazka
# Theme Builder - Templates
templates.php           # GET lista templates
template-get.php        # GET pojedynczy template
template-save.php       # POST zapisz template
template-delete.php     # POST usuń template
template-duplicate.php  # POST duplikuj template
template-set-default.php # POST ustaw jako default
template-preview.php    # POST preview template
# Theme Builder - Conditions
conditions.php          # GET/POST warunki
conditions-objects.php  # GET obiekty do warunków
# Theme Builder - Global Modules
global-modules.php      # GET lista global modules
global-module-get.php   # GET pojedynczy
global-module-save.php  # POST zapisz
global-module-delete.php # POST usuń
# Theme Settings
theme-settings.php      # GET/POST global theme settings
```

### api/ai/ (7 plików) - AI Integration Endpoints
```
generate-layout.php     # POST generuj pełny layout strony
generate-section.php    # POST generuj pojedynczą sekcję
generate-content.php    # POST generuj content dla modułu
generate-image.php      # POST generuj obraz AI
suggest-modules.php     # POST sugestie modułów na podstawie kontekstu
get-schema.php          # GET/POST pobierz schematy modułów
analyze-content.php     # POST analizuj content i sugeruj ulepszenia
```

### views/ (7 plików)
```
builder.php                 # Page Builder UI
template-manager.php        # Lista templates
template-editor.php         # Edytor template
global-modules-manager.php  # Lista global modules
theme-settings.php          # Theme Settings panel
module-wrapper.php          # Wrapper dla modułów
ai-panel.php                # AI Panel UI (slide-out panel)
```

### assets/js/ (9 plików)
```
builder.js              # Główny builder (76KB) - drag&drop, canvas, save/load
settings-panel.js       # Panel ustawień (45KB) - renderowanie pól, event handlers
fields.js               # Definicje pól (37KB) - 25+ typów pól
frontend.js             # Frontend (18KB) - sticky header, mobile menu, animacje
theme-settings.js       # Theme settings panel (18KB)
template-manager.js     # Zarządzanie templates (8KB)
template-editor.js      # Edytor templates (16KB)
global-modules.js       # Global modules (8KB)
conditions-builder.js   # Builder warunków (6KB)
ai-panel.js             # AI Panel JavaScript (22KB) - panel control, API calls, preview
```

### assets/css/ (6 plików)
```
builder.css             # Style buildera (68KB)
frontend.css            # Style frontend (39KB)
template-manager.css    # Style template manager (17KB)
theme-settings.css      # Style theme settings (13KB)
animations.css          # Animacje CSS (13KB)
ai-panel.css            # Style AI panelu (43KB) - kompletny UI, dark mode, responsive
```

### controllers/
```
template-controller.php # MVC kontroler dla Theme Builder
                        # Metody: index(), edit(), globalModules(), themeSettings()
```

---

## BAZA DANYCH

### Tabele (4)
```sql
-- Tabela 1: Content stron
CREATE TABLE jtb_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL UNIQUE,
    content JSON NOT NULL,
    css_cache TEXT,
    version VARCHAR(10) DEFAULT '1.0',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_post_id (post_id)
);

-- Tabela 2: Templates (header, footer, body, 404, etc.)
CREATE TABLE jtb_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,     -- header|footer|body|404|search|archive
    content JSON NOT NULL,
    conditions JSON,                -- warunki wyświetlania
    is_active TINYINT(1) DEFAULT 0,
    priority INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela 3: Warunki templates
CREATE TABLE jtb_template_conditions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    condition_type VARCHAR(50),    -- include|exclude
    object_type VARCHAR(50),       -- post_type|taxonomy|specific
    object_id INT,
    FOREIGN KEY (template_id) REFERENCES jtb_templates(id)
);

-- Tabela 4: Global reusable modules
CREATE TABLE jtb_global_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100) NOT NULL,
    content JSON NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

## ROUTING W CMS (index.php)

**Lokalizacja:** `/var/www/cms/index.php`

```php
// ============================================
// JTB (JESSIE THEME BUILDER) ROUTES
// Dodane PRZED dispatch()
// ============================================
$jtbUri = $_SERVER["REQUEST_URI"] ?? "/";
$jtbQpos = strpos($jtbUri, "?");
if ($jtbQpos !== false) { $jtbUri = substr($jtbUri, 0, $jtbQpos); }

// 1. API Routes
if (preg_match('#^/api/jtb/([\w-]+)(?:/(\d+))?$#', $jtbUri, $jtbMatches)) {
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/api/router.php';
    exit;
}

// 2. Legacy Page Builder
if (preg_match('#^/admin/jessie-theme-builder/?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/controller.php';
    exit;
}

// 3. Page Builder Edit
if (preg_match('#^/admin/jessie-theme-builder/edit/(\d+)$#', $jtbUri, $jtbMatches)) {
    $_GET['post_id'] = $jtbMatches[1];
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/controller.php';
    exit;
}

// 4. Template Manager
if (preg_match('#^/admin/jtb/templates/?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/controllers/template-controller.php';
    (new \JessieThemeBuilder\TemplateController())->index();
    exit;
}

// 5. Template Editor
if (preg_match('#^/admin/jtb/template/edit(?:/(\d+))?$#', $jtbUri, $jtbMatches)) {
    $_GET['template_id'] = $jtbMatches[1] ?? null;
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/controllers/template-controller.php';
    (new \JessieThemeBuilder\TemplateController())->edit($_GET['template_id'] ? (int)$_GET['template_id'] : null);
    exit;
}

// 6. Theme Settings
if (preg_match('#^/admin/jtb/theme-settings/?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/controllers/template-controller.php';
    (new \JessieThemeBuilder\TemplateController())->themeSettings();
    exit;
}

// 7. Global Modules
if (preg_match('#^/admin/jtb/global-modules/?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/controllers/template-controller.php';
    (new \JessieThemeBuilder\TemplateController())->globalModules();
    exit;
}

// 8. Frontend Override (Theme Integration)
if (!str_starts_with($jtbUri, '/admin') && !str_starts_with($jtbUri, '/api')) {
    $jtbPluginPath = CMS_ROOT . '/plugins/jessie-theme-builder';
    if (file_exists($jtbPluginPath . '/includes/class-jtb-theme-integration.php')) {
        // Load all required classes
        require_once $jtbPluginPath . '/includes/class-jtb-element.php';
        require_once $jtbPluginPath . '/includes/class-jtb-registry.php';
        // ... (all other includes)

        // Load modules
        $moduleDirs = ['structure', 'content', 'interactive', 'media', 'forms', 'blog', 'fullwidth', 'theme'];
        foreach ($moduleDirs as $dir) {
            // Load all modules from each directory
        }

        // Try to handle request
        $jtbIntegration = new \JessieThemeBuilder\JTB_Theme_Integration();
        $jtbResponse = $jtbIntegration->tryHandle($jtbUri);

        if ($jtbResponse !== null) {
            echo $jtbResponse;
            exit;
        }
    }
}
```

---

## API ENDPOINTS - SZCZEGÓŁY

### Page Builder
| Endpoint | Metoda | Opis | Auth |
|----------|--------|------|------|
| `/api/jtb/modules` | GET | Lista modułów z fieldami | Yes |
| `/api/jtb/load/{post_id}` | GET | Załaduj content posta | Yes |
| `/api/jtb/save` | POST | Zapisz content (CSRF) | Yes |
| `/api/jtb/render` | POST | Renderuj HTML preview (CSRF) | Yes |
| `/api/jtb/upload` | POST | Upload obrazka (CSRF) | Yes |

### Theme Builder - Templates
| Endpoint | Metoda | Opis |
|----------|--------|------|
| `/api/jtb/templates` | GET | Lista templates |
| `/api/jtb/template-get/{id}` | GET | Pojedynczy template |
| `/api/jtb/template-save` | POST | Zapisz template |
| `/api/jtb/template-delete` | POST | Usuń template |
| `/api/jtb/template-duplicate` | POST | Duplikuj template |
| `/api/jtb/template-set-default` | POST | Ustaw jako default |
| `/api/jtb/template-preview` | POST | Preview template |

### Theme Builder - Conditions
| Endpoint | Metoda | Opis |
|----------|--------|------|
| `/api/jtb/conditions` | GET/POST | CRUD warunków |
| `/api/jtb/conditions-objects` | GET | Obiekty do warunków |

### Theme Builder - Global Modules
| Endpoint | Metoda | Opis |
|----------|--------|------|
| `/api/jtb/global-modules` | GET | Lista global modules |
| `/api/jtb/global-module-get/{id}` | GET | Pojedynczy module |
| `/api/jtb/global-module-save` | POST | Zapisz module |
| `/api/jtb/global-module-delete` | POST | Usuń module |

### Theme Settings
| Endpoint | Metoda | Opis |
|----------|--------|------|
| `/api/jtb/theme-settings` | GET | Pobierz wszystkie ustawienia |
| `/api/jtb/theme-settings` | POST | Zapisz ustawienia + regeneruj CSS |

---

## KLASA JTB_Element - FEATURE FLAGS

```php
abstract class JTB_Element
{
    // Feature flags - co moduł obsługuje
    public bool $use_background = true;      // Background color/image/gradient
    public bool $use_spacing = true;         // Margin/Padding
    public bool $use_border = true;          // Border width/style/color/radius
    public bool $use_box_shadow = true;      // Box shadow
    public bool $use_typography = false;     // Font family/size/weight/style
    public bool $use_animation = true;       // Entrance animations
    public bool $use_transform = true;       // Scale/rotate/skew/translate
    public bool $use_position = false;       // Absolute/fixed positioning
    public bool $use_filters = true;         // CSS filters (blur, brightness, etc.)

    // Child modules support
    public bool $is_child = false;
    public ?string $child_slug = null;

    // Category for module picker
    public string $category = 'content';
    public string $icon = 'box';

    // Abstract methods
    abstract public function getSlug(): string;
    abstract public function getName(): string;
    abstract public function getFields(): array;      // Content fields
    abstract public function render(array $attrs, string $content = ''): string;

    // Auto-generated based on feature flags
    public function getDesignFields(): array;     // Design tab fields
    public function getAdvancedFields(): array;   // Advanced tab fields

    // CSS generation
    public function generateCss(array $attrs, string $selector): string;
}
```

---

## FIELD TYPES - KOMPLETNA LISTA

### Dostępne w fields.js (25+ typów)
```
text            # Input tekstowy
textarea        # Wieloliniowy tekst
richtext        # WYSIWYG editor
select          # Dropdown
toggle          # Yes/No switch
checkbox        # Checkbox
radio           # Radio buttons
range           # Slider + number input
number          # Number input
color           # Color picker (bez alpha!)
upload          # Image upload
url             # URL input
icon            # Icon picker (placeholder!)
code            # Code editor
date            # Date picker
datetime        # DateTime picker
gallery         # Multi-image gallery
repeater        # Repeatable fields
buttonGroup     # Button radio
align           # Alignment buttons
multiSelect     # Multi-checkbox
gradient        # Gradient picker (DUPLIKAT!)
boxShadow       # Box shadow builder
border          # Border controls
font            # Typography controls
spacing         # 4-side margin/padding
```

### Event Handlers w settings-panel.js
Wszystkie powyższe typy mają podpięte event handlers w `JTB.Settings.bindFieldEvents()`.

---

## THEME SETTINGS - 10 GRUP

```php
$settingsGroups = [
    'colors' => [
        'primary_color', 'secondary_color', 'accent_color',
        'text_color', 'text_light_color', 'heading_color',
        'link_color', 'link_hover_color',
        'background_color', 'surface_color', 'border_color',
        'success_color', 'warning_color', 'error_color', 'info_color'
    ],
    'typography' => [
        'body_font', 'body_size', 'body_weight', 'body_line_height',
        'heading_font', 'heading_weight', 'heading_line_height', 'heading_letter_spacing',
        'h1_size', 'h2_size', 'h3_size', 'h4_size', 'h5_size', 'h6_size'
    ],
    'layout' => [
        'content_width', 'gutter_width',
        'section_padding_top', 'section_padding_bottom',
        'row_gap', 'column_gap'
    ],
    'buttons' => [
        'button_bg_color', 'button_text_color', 'button_border_color',
        'button_border_width', 'button_border_radius',
        'button_padding_tb', 'button_padding_lr',
        'button_font_size', 'button_font_weight', 'button_text_transform',
        'button_hover_bg', 'button_hover_text', 'button_hover_border',
        'button_transition'
    ],
    'forms' => [
        'input_bg_color', 'input_text_color', 'input_border_color',
        'input_border_width', 'input_border_radius',
        'input_padding_tb', 'input_padding_lr', 'input_font_size',
        'input_focus_border_color', 'placeholder_color',
        'label_color', 'label_font_size'
    ],
    'header' => [
        'header_bg_color', 'header_text_color', 'header_height',
        'header_padding_lr', 'logo_height',
        'header_sticky', 'header_sticky_bg', 'logo_height_sticky',
        'header_transparent', 'header_transparent_text'
    ],
    'menu' => [
        'menu_font_family', 'menu_font_size', 'menu_font_weight', 'menu_text_transform',
        'menu_link_color', 'menu_link_hover_color', 'menu_link_active_color',
        'menu_link_padding_tb', 'menu_link_padding_lr',
        'dropdown_bg_color', 'dropdown_text_color', 'dropdown_hover_bg', 'dropdown_border_radius',
        'mobile_breakpoint', 'mobile_menu_bg', 'mobile_menu_text', 'hamburger_color'
    ],
    'footer' => [
        'footer_bg_color', 'footer_text_color', 'footer_heading_color',
        'footer_link_color', 'footer_link_hover_color',
        'footer_padding_top', 'footer_padding_bottom', 'footer_columns',
        'copyright_bg_color', 'copyright_text_color', 'copyright_padding_tb', 'copyright_text'
    ],
    'blog' => [
        'blog_layout', 'blog_columns', 'blog_gap',
        'post_card_bg', 'post_card_border_radius',
        'show_featured_image', 'show_date', 'show_author', 'show_categories',
        'show_excerpt', 'excerpt_length', 'show_read_more', 'read_more_text'
    ],
    'responsive' => [
        'tablet_breakpoint', 'phone_breakpoint',
        'h1_size_tablet', 'h2_size_tablet', 'body_size_tablet', 'section_padding_tablet',
        'h1_size_phone', 'h2_size_phone', 'body_size_phone', 'section_padding_phone'
    ]
];
```

---

## DEPLOYMENT

### Kopiowanie do WSL
```bash
# Kopiuj wszystkie pliki
wsl -u root bash -c 'cp -r "/mnt/c/Users/krala/Downloads/jessie theme builder/plugin/"* /var/www/cms/plugins/jessie-theme-builder/'

# Ustaw uprawnienia
wsl -u root bash -c 'chown -R www-data:www-data /var/www/cms/plugins/jessie-theme-builder'
```

### Sprawdzenie składni PHP
```bash
# Wszystkie pliki
wsl -u root bash -c 'find /var/www/cms/plugins/jessie-theme-builder -name "*.php" -exec php -l {} \; 2>&1 | grep -v "No syntax errors"'

# Pojedynczy plik
wsl -u root bash -c 'php -l /var/www/cms/plugins/jessie-theme-builder/plugin.php'
```

### Test HTTP
```bash
# API (401 = OK - wymaga auth)
curl -s -o /dev/null -w "%{http_code}" "http://localhost/api/jtb/modules"

# Admin pages (302 = OK - redirect to login)
curl -sI "http://localhost/admin/jtb/templates" | head -1
```

---

## NAPRAWIONE BŁĘDY

### 21.01.2026 - Namespace issues
- `\EnhancedPluginInterface` (globalny namespace)
- `\CMS\Plugins\HookManager`
- `\CMS\Plugins\PluginLoader::getHookManager()`

### 23.01.2026 - class-jtb-theme-integration.php
**Problem:** Komentarz `/* */` w docblock powodował syntax error
```php
// Było (błędne):
* Use in theme: if (JTB_Theme_Integration::outputHeader()) { /* skip default header */ }

// Jest (poprawne):
* Use in theme: if (JTB_Theme_Integration::outputHeader()) { // skip default header }
```

### 23.01.2026 - Admin Menu w plugin.php
Dodano metodę `registerAdminMenu()` z menu items:
- Theme Builder (parent)
  - Templates
  - Global Modules
  - Theme Settings

---

## ZNANE PROBLEMY DO NAPRAWIENIA

### 🔴 KRYTYCZNE

#### 1. Icon Picker - BRAK PRAWDZIWYCH IKON
**Plik:** `assets/js/settings-panel.js:1015-1034`
**Problem:** Tylko 40 placeholder ikon, brak SVG
**Rozwiązanie:** Dodać Feather Icons (~287 ikon) jako SVG inline

#### 2. Color Picker - BRAK ALPHA CHANNEL
**Plik:** `assets/js/fields.js:148-164`
**Problem:** Używa `<input type="color">` który nie obsługuje RGBA
**Rozwiązanie:** Własny color picker z alpha slider

#### 3. Gradient Field - DUPLIKAT FUNKCJI
**Plik:** `assets/js/fields.js`
**Problem:** Dwie różne implementacje:
- Linia 472-499: Starsza wersja
- Linia 733-796: Nowsza wersja
**Rozwiązanie:** Usunąć starszą wersję (472-499)

#### 4. Media Library - BRAK BROWSER
**Plik:** `assets/js/settings-panel.js:1128-1185`
**Problem:** Tylko upload nowych plików, brak przeglądania istniejących
**Rozwiązanie:** Integracja z CMS media system + modal browser

#### 5. Responsive Preview - NIE DZIAŁA
**Problem:** Przełączniki desktop/tablet/phone tylko zmieniają atrybuty, canvas nie zmienia rozmiaru
**Rozwiązanie:** Dodać rzeczywisty resize iframe w preview

### 🟡 ŚREDNIE

#### 6. Animacje - TYLKO 7 TYPÓW
**Plik:** `includes/class-jtb-element.php`
**Problem:** Divi ma 20+ animacji, JTB tylko 7 (fade, slide, bounce, zoom, flip, fold, roll)
**Rozwiązanie:** Dodać 15+ nowych typów animacji

#### 7. Brak Undo/Redo
**Plik:** `assets/js/builder.js`
**Problem:** Brak historii zmian
**Rozwiązanie:** Implementacja history stack

---

## ANALIZA PORÓWNAWCZA DIVI vs JTB

### Compatibility Score
```
┌─────────────────────────────────┐
│ DIVI vs JTB Compatibility       │
├─────────────────────────────────┤
│ Module Coverage:        88% ✅   │
│ Field Types:            75% ⚠️   │
│ CSS Generation:         95% ✅   │
│ Animation System:       65% ⚠️   │
│ UX/UI:                  80% ✅   │
│ Database/Storage:       90% ✅   │
│ Theme Builder:          50% ⚠️   │
│ Dynamic Content:        30% ⚠️   │
│ Media Library:           0% ❌   │
├─────────────────────────────────┤
│ OVERALL:               ~65%      │
└─────────────────────────────────┘
```

### Co działa dobrze ✅
1. CSS Generation - kompletne (typography, transforms, filters, shadows, borders, spacing, responsive)
2. Event Handlers - wszystkie 25+ typów pól
3. Module System - 45+ modułów w 8 kategoriach
4. 3-tab structure (Content/Design/Advanced)
5. Parent-child modules (accordion items, tabs items)
6. Database caching CSS
7. API endpoints - kompletne

### Co wymaga pracy ⚠️
1. Icon Picker - placeholder
2. Color Picker - brak RGBA
3. Media Library - brak browsera
4. Responsive Preview - nie działa w canvas
5. Animacje - ograniczone (7 vs 20+)
6. Undo/Redo - brak

---

## UNIFIED THEME SYSTEM (NOWE - 29.01.2026)

### Architektura
System inspirowany Divi, zapewnia spójne style we wszystkich modułach:

1. **JTB_Global_Settings** (`class-jtb-global-settings.php`)
   - Centralne domyślne wartości dla KAŻDEGO atrybutu modułu
   - Wzór: `{module_prefix}_{property}` np. `gallery_title_font_size`
   - Metody: `get()`, `isDifferentFromDefault()`, `mergeWithDefaults()`

2. **JTB_CSS_Variables** (`class-jtb-css-variables.php`)
   - Generator CSS custom properties z Theme Settings
   - Obsługuje responsive, dark mode
   - Wzór: `--jtb-{module}-{property}` np. `--jtb-gallery-gap`

3. **$style_config w JTB_Element**
   - Deklaratywna konfiguracja stylów w każdym module
   - Mapuje atrybuty na właściwości CSS i selektory
   ```php
   protected array $style_config = [
       'title_font_size' => [
           'property' => 'font-size',
           'selector' => '.jtb-gallery-title',
           'unit' => 'px',
           'responsive' => true,
           'hover' => false
       ]
   ];
   ```

4. **jtb-base-modules.css**
   - Bazowe style dla wszystkich modułów używające CSS variables
   - Profesjonalny design out-of-the-box
   - Pełne responsive breakpoints

### Zmigrowane moduły
- Gallery ✅
- Blog ✅
- Blurb ✅
- Button ✅
- Testimonial ✅
- Pricing Table ✅
- Accordion ✅
- Tabs ✅
- CTA ✅
- Team Member ✅

### Jak dodać nowy moduł do systemu
1. Dodaj `protected string $module_prefix = 'module_name';`
2. Zdefiniuj `protected array $style_config = [...];`
3. W `generateCss()` wywołaj `$this->generateStyleConfigCss($attrs, $selector);`
4. Dodaj domyślne wartości do `JTB_Global_Settings::$defaults`
5. Dodaj bazowe style do `jtb-base-modules.css`

---

## PLAN NAPRAWCZY - PRIORYTETY

### FAZA 1: KRYTYCZNE (1-2 dni pracy)
1. **Napraw Icon Picker** - dodaj Feather Icons (287 ikon jako SVG)
2. **Napraw Color Picker** - dodaj alpha channel (RGBA)
3. **Usuń duplikat Gradient** - zostaw tylko nowszą wersję (733-796)
4. **Napraw Responsive Preview** - resize iframe w canvas

### FAZA 2: WAŻNE (3-5 dni)
5. **Media Library Browser** - integracja z CMS
6. **Więcej animacji** - dodaj 15 nowych typów
7. **Undo/Redo** - history stack w builder.js

### FAZA 3: ULEPSZENIA (opcjonalne)
8. Live inline editing
9. Module presets
10. Global color palette

---

## KOMENDY DEBUGOWANIA

```bash
# Sprawdź logi Apache
wsl -u root bash -c 'tail -50 /var/log/apache2/error.log'

# Sprawdź tabele DB
wsl -u root bash -c 'mysql -u root -e "USE cms; SHOW TABLES LIKE \"jtb_%\";"'

# Test składni wszystkich PHP
wsl -u root bash -c 'find /var/www/cms/plugins/jessie-theme-builder -name "*.php" -exec php -l {} \; 2>&1 | grep -v "No syntax errors"'

# Restart Apache
wsl -u root bash -c 'service apache2 restart'
```

---

## UWAGA: DWA KONTEKSTY ŁADOWANIA

JTB może być ładowany na dwa sposoby:

### 1. Przez plugin system (plugin.php)
- Pełna klasa `JessieThemeBuilderPlugin` dostępna
- Hooki i filtry zarejestrowane
- Używane dla frontendowego renderowania

### 2. Przez MVC kontroler (TemplateController)
- Tylko klasy includes załadowane
- Brak klasy głównej pluginu
- Używane dla admin `/admin/jtb/*`

**WAŻNE:** Wszystkie widoki i pliki muszą działać w obu kontekstach!

---

## QUICK START DLA NOWEJ SESJI

1. **Przeczytaj CLAUDE.md** (ten plik)
2. **Sprawdź docs/** jeśli potrzebujesz szczegółów architektury
3. **Deploy do WSL:**
   ```bash
   wsl -u root bash -c 'cp -r "/mnt/c/Users/krala/Downloads/jessie theme builder/plugin/"* /var/www/cms/plugins/jessie-theme-builder/ && chown -R www-data:www-data /var/www/cms/plugins/jessie-theme-builder'
   ```
4. **Test składni:**
   ```bash
   wsl -u root bash -c 'find /var/www/cms/plugins/jessie-theme-builder -name "*.php" -exec php -l {} \; 2>&1 | grep -v "No syntax errors"'
   ```
5. **Zacznij od PLANU NAPRAWCZEGO powyżej**

---

## FUTURE FEATURES - DOKUMENTACJA

**Lokalizacja:** `C:\Users\krala\Downloads\jessie theme builder\future-features\`

Kompletna dokumentacja dla 5 planowanych komponentów CMS:

| Plik | Komponent | Czas implementacji |
|------|-----------|-------------------|
| `00_MASTER_PLAN.md` | Master Plan | - |
| `01_AI_CHAT_COPILOT.md` | AI Chat Copilot | 5-7 dni |
| `02_COMMAND_PALETTE.md` | Command Palette (Cmd+K) | 2.5-3.5 dni |
| `03_VERSION_HISTORY.md` | Visual Version History | 5-7 dni |
| `04_COMPONENT_MARKETPLACE.md` | Component Marketplace | 7-10 dni |
| `05_ANALYTICS.md` | Built-in Analytics | 5 dni |

**Zalecana kolejność:**
1. Command Palette → 2. AI Copilot → 3. Version History → 4. Marketplace → 5. Analytics

Każdy dokument zawiera:
- Pełne klasy PHP (namespace, metody)
- Kod JavaScript (frontend)
- Style CSS
- Schematy bazy danych (SQL)
- API endpoints
- Instrukcje integracji
- Checklisty implementacji

---

## ZMIANY W TEJ SESJI (27.01.2026)

### 1. Naprawiono Dropdown Menu w Admin
**Plik:** `/var/www/cms/admin/includes/topbar_nav.php`
**Problem:** Dropdown menu nie działało, `overflow: hidden` obcinało menu
**Rozwiązanie:** Zmieniono na `overflow: visible`, dodano profesjonalny design glassmorphism

### 2. Profesjonalny Design Dropdown
- Glassmorphism effect (`backdrop-filter: blur(20px)`)
- Icon boxes dla menu items
- Gradient hover effects
- Smooth cubic-bezier animations
- Specjalne wyróżnienie dla pozycji "v5"

### 3. Two-Row Layout dla Header
- Row 1: Logo + User info
- Row 2: Navigation items
- `max-width: 100vw` - nie wystaje poza ekran

### 4. Dokumentacja Future Features
Stworzona kompletna dokumentacja dla 5 komponentów (patrz sekcja powyżej)

---

## AI INTEGRATION (NOWE - 30.01.2026)

### Architektura

Kompletna integracja AI dla JTB, umożliwiająca:
- Generowanie pełnych layoutów stron na podstawie opisu
- Generowanie pojedynczych sekcji
- Generowanie contentu dla wszystkich 78 modułów
- Generowanie obrazów AI
- Analizę istniejącego contentu z sugestiami

### Klasy PHP (includes/ai/)

#### 1. JTB_AI_Core (Singleton)
```php
// Komunikacja z AI - obsługuje 4 providery
$ai = JTB_AI_Core::getInstance();
$response = $ai->query($prompt, $systemPrompt, $options);
$response = $ai->queryWithRetry($prompt, $systemPrompt, $options, $maxRetries);
$ai->streamQuery($prompt, $systemPrompt, $callback, $options);

// Metody provider-specific
private function callHuggingFace($prompt, $systemPrompt, $options);
private function callOpenAI($prompt, $systemPrompt, $options);
private function callAnthropic($prompt, $systemPrompt, $options);
private function callDeepSeek($prompt, $systemPrompt, $options);
```

#### 2. JTB_AI_Schema
```php
// Eksport schematów modułów dla AI
$schemas = JTB_AI_Schema::exportAllModules();
$schema = JTB_AI_Schema::exportModuleSchema('heading');
$fields = JTB_AI_Schema::getModuleFields('blurb');
$fieldTypes = JTB_AI_Schema::getFieldTypes();
$columnLayouts = JTB_AI_Schema::getColumnLayouts();
$icons = JTB_AI_Schema::getAvailableIcons();
```

#### 3. JTB_AI_Context
```php
// Budowanie kontekstu dla AI
$context = JTB_AI_Context::getPageContext($pageId);
$siteContext = JTB_AI_Context::getSiteContext();
$styleContext = JTB_AI_Context::getStyleContext();
$branding = JTB_AI_Context::getBrandingContext();
$existing = JTB_AI_Context::getExistingContent($pageId);
$fullContext = JTB_AI_Context::buildPromptContext($pageId, $options);
```

#### 4. JTB_AI_Prompts
```php
// Szablony promptów
$system = JTB_AI_Prompts::getSystemPrompt();
$prompt = JTB_AI_Prompts::buildLayoutPrompt($description, $context);
$sectionPrompt = JTB_AI_Prompts::getSectionPrompt('hero', $context);
$modulePrompt = JTB_AI_Prompts::getModulePrompt('testimonial', $context);

// Dostępne sekcje: hero, features, testimonials, cta, pricing, faq,
// contact, about, team, portfolio, blog, newsletter, stats, partners, services
```

#### 5. JTB_AI_Generator
```php
// Generowanie layoutów
$layout = JTB_AI_Generator::generateLayout($prompt, $pageType, $pageId);
$section = JTB_AI_Generator::generateSection($sectionType, $prompt, $context);
$row = JTB_AI_Generator::generateRow($columns, $context);
$module = JTB_AI_Generator::generateModule($moduleType, $context);

// Semantic parsing
$intent = JTB_AI_Generator::parseSemanticIntent($prompt);
// Returns: pageType, industry, sections[], style, tone
```

#### 6. JTB_AI_Content
```php
// Generowanie contentu
$content = JTB_AI_Content::generateModuleContent($moduleType, $context);
$value = JTB_AI_Content::regenerateField($moduleType, $fieldName, $currentValue, $context);

// Text generators
$headline = JTB_AI_Content::generateHeadline($topic, $style);
$subheadline = JTB_AI_Content::generateSubheadline($topic, $style);
$paragraph = JTB_AI_Content::generateParagraph($topic, $length, $style);
$bullets = JTB_AI_Content::generateBulletPoints($topic, $count);

// Module-specific generators dla WSZYSTKICH 78 modułów
```

#### 7. JTB_AI_Images
```php
// Generowanie obrazów
$imageUrl = JTB_AI_Images::generateImage($prompt, $options);
$heroImage = JTB_AI_Images::generateHeroImage($topic, $style);
$bgImage = JTB_AI_Images::generateBackgroundImage($description);
$icon = JTB_AI_Images::generateFeatureIcon($description);
$teamPhoto = JTB_AI_Images::generateTeamPhoto($role, $gender);
$productImage = JTB_AI_Images::generateProductImage($description);

// Media Library integration
$mediaId = JTB_AI_Images::uploadToMediaLibrary($imageData, $filename);
$url = JTB_AI_Images::assignToModule($moduleId, $mediaId);
```

#### 8. JTB_AI_Pexels (NOWE)
```php
// Integracja z Pexels API - pobieranie obrazów stock
JTB_AI_Pexels::isConfigured();  // Sprawdź czy API key jest ustawiony

// Wyszukiwanie zdjęć
$result = JTB_AI_Pexels::searchPhotos($query, ['per_page' => 10, 'orientation' => 'landscape']);

// Kontekstowe pobieranie obrazów
$heroImage = JTB_AI_Pexels::getHeroImage(['industry' => 'technology']);
$personPhoto = JTB_AI_Pexels::getPersonPhoto(['gender' => 'female', 'role' => 'CEO']);
$aboutImage = JTB_AI_Pexels::getAboutImage(['industry' => 'agency']);
$featureImage = JTB_AI_Pexels::getFeatureImage(['feature' => 'security']);
$galleryImages = JTB_AI_Pexels::getGalleryImages(['industry' => 'restaurant'], 6);
$backgroundImage = JTB_AI_Pexels::getBackgroundImage(['background_type' => 'abstract']);

// Pobranie i zapis lokalnie
$saved = JTB_AI_Pexels::downloadAndSave($pexelsUrl, ['alt' => 'Image description']);
```

#### 9. JTB_AI_Styles (NOWE)
```php
// Profesjonalne presety stylów dla AI-generowanych layoutów

// Pełny preset stylu
$preset = JTB_AI_Styles::getStylePreset('modern', $context);
// Returns: colors, typography, spacing, buttons, shadows, borders, animations

// Palety kolorów (modern, minimal, bold, elegant, playful, corporate, dark)
$colors = JTB_AI_Styles::getColorPalette('modern');
// Returns: primary, secondary, accent, text, text_light, background, background_alt, heading

// Typography (dopasowane do stylu)
$typography = JTB_AI_Styles::getTypography('elegant');
// Returns: heading_font, body_font, h1_size, h2_size, h3_size, body_size, weights, line_heights

// Style dla modułów
$sectionAttrs = JTB_AI_Styles::getSectionAttrs('hero', 'modern', $context);
$headingStyles = JTB_AI_Styles::getHeadingStyles('bold', ['level' => 'h1']);
$textStyles = JTB_AI_Styles::getTextStyles('minimal', $context);
$buttonStyles = JTB_AI_Styles::getButtonStyles('elegant', ['variant' => 'primary']);
$blurbStyles = JTB_AI_Styles::getBlurbStyles('modern', $context);
$testimonialStyles = JTB_AI_Styles::getTestimonialStyles('corporate', $context);
$pricingStyles = JTB_AI_Styles::getPricingStyles('bold', ['featured' => true]);
$ctaStyles = JTB_AI_Styles::getCTAStyles('playful', $context);
$counterStyles = JTB_AI_Styles::getCounterStyles('modern', $context);
$teamMemberStyles = JTB_AI_Styles::getTeamMemberStyles('elegant', $context);

// Kolory dostosowane do branży
$industryColors = JTB_AI_Styles::getIndustryColors('healthcare');
// Returns: primary (#0D9488 teal), secondary, accent

// Połączone style + branża
$mergedStyles = JTB_AI_Styles::getMergedStyles('modern', 'technology', $context);
```

### API Endpoints

| Endpoint | Metoda | Opis | Body |
|----------|--------|------|------|
| `/api/jtb/ai/generate-layout` | POST | Generuj pełny layout | `{prompt, page_type, page_id}` |
| `/api/jtb/ai/generate-section` | POST | Generuj sekcję | `{section_type, prompt, context}` |
| `/api/jtb/ai/generate-content` | POST | Generuj content modułu | `{module_type, field_name, context}` |
| `/api/jtb/ai/generate-image` | POST | Generuj obraz AI | `{prompt, size, style}` |
| `/api/jtb/ai/suggest-modules` | POST | Sugestie modułów | `{context, current_modules}` |
| `/api/jtb/ai/get-schema` | GET/POST | Pobierz schematy | `{modules[]}` |
| `/api/jtb/ai/analyze-content` | POST | Analizuj content | `{page_id, content}` |

### UI Panel (ai-panel.php + ai-panel.js + ai-panel.css)

Panel boczny (slide-out) z 4 zakładkami:
1. **Generate** - Generowanie pełnych layoutów
2. **Add Section** - Dodawanie sekcji (12 typów)
3. **Content** - Generowanie contentu dla wybranego modułu
4. **Analyze** - Analiza i sugestie

### JavaScript API (JTB_AI)

```javascript
// Inicjalizacja
JTB_AI.init({
    csrfToken: 'token',
    apiUrl: '/api/jtb/ai',
    pageId: 123
});

// Metody
JTB_AI.openPanel();
JTB_AI.closePanel();
JTB_AI.generateLayout(prompt, pageType);
JTB_AI.generateSection(sectionType, prompt);
JTB_AI.generateContent(moduleType, context);
JTB_AI.analyzeContent();
JTB_AI.applyLayout(layout);
JTB_AI.showPreview(layout);
JTB_AI.showToast(message, type);
```

### Konfiguracja

AI provider konfigurowany w CMS settings (`settings` table):
```php
// Klucze w tabeli settings:
'ai_provider'           // huggingface|openai|anthropic|deepseek
'huggingface_api_key'   // API key dla HuggingFace
'openai_api_key'        // API key dla OpenAI
'anthropic_api_key'     // API key dla Anthropic
'deepseek_api_key'      // API key dla DeepSeek
'ai_model'              // Model do użycia (opcjonalne)
'ai_temperature'        // Temperature 0.0-2.0 (default: 0.7)
```

### Semantic Intent Parsing

Generator automatycznie rozpoznaje z promptu:
- **Page Type**: landing, homepage, about, contact, services, portfolio, blog, product, pricing
- **Industry**: technology, healthcare, finance, education, retail, real_estate, restaurant, fitness, legal, creative
- **Sections**: hero, features, about, services, testimonials, pricing, cta, contact, team, portfolio, faq, blog, newsletter, stats, partners
- **Style**: modern, classic, minimalist, bold, elegant, playful, corporate, creative
- **Tone**: professional, friendly, formal, casual, enthusiastic, authoritative

### Obsługiwane moduły (78)

#### Structure (3)
section, row, column

#### Content (21)
text, heading, image, button, blurb, divider, code, cta, number_counter, circle_counter, bar_counter, icon, testimonial, team_member, pricing_table, social_follow, comments, sidebar, countdown, post_navigation, shop

#### Interactive (5)
accordion, accordion_item, tabs, tabs_item, toggle

#### Media (5)
audio, video, gallery, slider, map

#### Forms (4)
contact_form, login, signup, search

#### Blog (3)
blog, portfolio, post_slider

#### Fullwidth (9)
fullwidth_header, fullwidth_image, fullwidth_menu, fullwidth_slider, fullwidth_portfolio, fullwidth_code, fullwidth_map, fullwidth_post_slider, fullwidth_post_title

#### Theme (14)
featured_image, post_excerpt, post_meta, author_box, related_posts, archive_title, breadcrumbs, archive_posts, menu, post_content, post_title, search_form, site_logo, social_icons

---

## AI DIRECT GENERATION (NOWE - 01.02.2026)

### Architektura (inspirowana Divi AI)

Uproszczona architektura gdzie **AI generuje bezpośrednio finalny JTB JSON**:

```
User Prompt
    ↓
/api/jtb/ai/generate (POST)
    ↓
JTB_AI_Direct::generateLayout()
    ↓
System Prompt z pełnym schematem JTB
    ↓
AI generuje FINALNY JTB JSON:
{
  "sections": [
    {
      "type": "section",
      "attrs": { "padding": {"top": 100, "bottom": 100} },
      "children": [
        {
          "type": "row",
          "attrs": { "columns": "1_2,1_2" },
          "children": [...]
        }
      ]
    }
  ]
}
    ↓
AutoFix::process() (opcjonalnie)
    ↓
Enrichment z Pexels (obrazy)
    ↓
GOTOWE DO UŻYCIA
```

### Korzyści (vs poprzednie architektury)
1. **Zero kompilacji/transformacji** - AI generuje gotowy format
2. **AI kontroluje wszystko** - struktura + content + style
3. **Szybsze** - 1 call AI zamiast wielu warstw (AST → Compiler → etc.)
4. **Prostsze debugowanie** - brak pośrednich warstw
5. **Jak Divi AI** - "Everything in a single JSON file"

### Nowa klasa: JTB_AI_Direct

```php
// Główna metoda - generuje pełny layout
$result = JTB_AI_Direct::generateLayout($prompt, [
    'page_type' => 'landing',     // landing, homepage, about, contact, services
    'industry' => 'technology',   // technology, healthcare, finance, etc.
    'style' => 'modern',          // modern, classic, minimalist, bold, elegant
    'page_id' => 123              // opcjonalnie - kontekst z istniejącej strony
]);

// Generowanie pojedynczej sekcji
$result = JTB_AI_Direct::generateSection('hero', $prompt, $options);

// Odpowiedź:
[
    'ok' => true,
    'layout' => ['sections' => [...]],
    'error' => null,
    'stats' => [
        'time_ms' => 3500,
        'tokens_used' => 2450,
        'provider' => 'anthropic',
        'sections_count' => 6
    ]
]
```

### Nowy endpoint API

| Endpoint | Metoda | Opis |
|----------|--------|------|
| `/api/jtb/ai/generate` | POST | Bezpośrednia generacja AI |

**Request body:**
```json
{
    "action": "layout",          // lub "section"
    "prompt": "Landing page for SaaS product",
    "page_type": "landing",
    "industry": "technology",
    "style": "modern",
    "page_id": 123,              // opcjonalne
    "section_type": "hero"       // tylko dla action: "section"
}
```

**Response:**
```json
{
    "ok": true,
    "layout": {
        "sections": [
            {
                "type": "section",
                "attrs": {...},
                "children": [...]
            }
        ]
    },
    "stats": {
        "time_ms": 3500,
        "tokens_used": 2450,
        "provider": "anthropic",
        "sections_count": 6
    }
}
```

### System Prompt (w JTB_AI_Direct)

System prompt zawiera:
1. Kompletną strukturę JTB JSON (section → row → column → module)
2. Wszystkie dostępne moduły z ich atrybutami
3. Wytyczne stylowania (colors, typography, spacing)
4. Zasady projektowania (visual hierarchy, whitespace, accessibility)
5. Kontekst branży i stylu (jeśli podane)

### Integracja z frontendem

W `ai-panel.js` funkcja `handleComposeLayout()` używa nowego endpointu:

```javascript
const response = await fetch('/api/jtb/ai/generate', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': JTB_AI.csrfToken
    },
    body: JSON.stringify({
        action: 'layout',
        prompt: prompt,
        page_type: pageType,
        style: style,
        industry: industry,
        page_id: JTB_AI.pageId
    })
});
```

---

## OSTATNIA AKTUALIZACJA

**Data:** 01.02.2026
**Stan:**
- Wszystkie 8 faz Theme Builder Implementation zaimplementowane
- Admin dropdown menu naprawione i ulepszone
- Dokumentacja future features gotowa
- **AI Integration KOMPLETNA** (14 klas, 8 endpoints, UI panel)
- **Pexels API Integration** - automatyczne pobieranie obrazów stock
- **Professional Styles System** - spójne style dla AI-generowanych layoutów
- **AI AutoFix Stages 11-17 KOMPLETNE** - zaawansowany system post-processingu + Narrative Flow
- **Layout AST Architecture** - AI projektuje layouty (poprzednia wersja)
- **NEW: AI Direct Generation (01.02.2026)** - Divi-style, AI generuje finalny JSON bezpośrednio!

---

## LAYOUT AST ARCHITECTURE (NOWE - 01.02.2026)

### Problem (stara architektura)
Poprzednio AI Composer używał **hardcoded pattern sequences** - AI nie decydowało o strukturze:
```
User Prompt → keyword matching → wybór z 11 sekwencji → render patterns → JTB JSON
```
AI było tylko "wypełniaczem pól", nie projektantem.

### Rozwiązanie: Layout AST Pipeline
Nowa architektura gdzie **AI faktycznie projektuje strukturę strony**:
```
User Prompt
    ↓
JTB_AI_Layout_Engine (NOWE) ← wywołuje AI!
    ↓
Layout AST (abstrakcyjne drzewo)
    ↓
JTB_AI_Layout_Compiler (NOWE) ← czysta transformacja
    ↓
JTB JSON
    ↓
AutoFix Stages 11-17 (bez zmian)
```

### Nowe klasy

#### 1. JTB_AI_Layout_AST (`class-jtb-ai-layout-ast.php`)
Schema i walidacja Layout AST - abstrakcyjna reprezentacja niezależna od JTB:
```php
// Section intents (dlaczego sekcja istnieje)
INTENT_CAPTURE   // Hero - capture attention
INTENT_EXPLAIN   // Features - what we do
INTENT_PROVE     // Testimonials, stats - build trust
INTENT_CONVINCE  // Benefits - convince to act
INTENT_CONVERT   // Pricing, CTA - drive conversion
INTENT_REASSURE  // FAQ - remove objections
INTENT_CONNECT   // Contact - enable connection

// Layout types
LAYOUT_ASYMMETRIC, LAYOUT_CENTERED, LAYOUT_SPLIT, LAYOUT_GRID, LAYOUT_ALTERNATING, LAYOUT_STACKED

// Abstract element types (nie JTB modules!)
headline, subheadline, cta_primary, image_hero, testimonial, pricing_card, faq_item, form, etc.

// Metody
JTB_AI_Layout_AST::validate($ast)      // Waliduj strukturę AST
JTB_AI_Layout_AST::getJsonSchema()     // Schema dla AI prompt
JTB_AI_Layout_AST::createSection(...)  // Factory method
```

#### 2. JTB_AI_Layout_Engine (`class-jtb-ai-layout-engine.php`)
**FAKTYCZNIE wywołuje AI** do generowania Layout AST:
```php
// GŁÓWNA METODA - wysyła prompt do AI!
$result = JTB_AI_Layout_Engine::generateLayoutAST($prompt, $context);
// Returns: ['ok' => true, 'ast' => [...], 'source' => 'ai', 'provider' => 'openai']

// Fallback gdy AI niedostępne
$result = JTB_AI_Layout_Engine::generateFallbackAST($prompt, $context);
// Returns: ['ok' => true, 'ast' => [...], 'source' => 'fallback']
```

System prompt instruuje AI jak projektować layouty, zawiera JSON Schema dla AST.

#### 3. JTB_AI_Layout_Compiler (`class-jtb-ai-layout-compiler.php`)
Kompiluje AST → JTB JSON (**zero logiki AI**, czysta transformacja):
```php
$sections = JTB_AI_Layout_Compiler::compile($ast, $context);
// Mapuje: headline → heading, cta_primary → button, testimonial → testimonial, etc.
```

### Użycie

#### API Endpoint
```bash
# NOWY tryb AST (AI projektuje layout)
curl -X POST /api/jtb/ai/compose-layout \
  -d '{"prompt": "Landing page for fitness app", "use_ast": true}'

# Stary tryb (hardcoded patterns)
curl -X POST /api/jtb/ai/compose-layout \
  -d '{"prompt": "Landing page for fitness app", "use_ast": false}'
```

#### PHP
```php
// Tryb AST
$layout = JTB_AI_Generator::generateASTLayout($prompt, $options);

// Przez generateWithValidation()
$layout = JTB_AI_Generator::generateWithValidation([
    'prompt' => $prompt,
    'options' => ['use_ast' => true]
]);
```

#### JavaScript (ai-panel.js)
```javascript
// Flaga w stanie
JTB_AI.useASTMode = true; // domyślnie TRUE!

// Przekazywana do API automatycznie
```

### Przykład Layout AST
```json
{
  "goal": "SaaS landing page for project management tool",
  "style": "modern",
  "sections": [
    {
      "type": "hero",
      "intent": "capture",
      "layout": "asymmetric",
      "visual_weight": "high",
      "columns": [
        {"width": 7, "elements": [
          {"type": "headline", "role": "value_proposition"},
          {"type": "subheadline", "role": "benefit_summary"},
          {"type": "cta_primary", "role": "main_action"}
        ]},
        {"width": 5, "elements": [
          {"type": "image_hero", "role": "product_screenshot"}
        ]}
      ]
    },
    {
      "type": "social_proof",
      "intent": "prove",
      "layout": "centered",
      "columns": [
        {"width": 12, "elements": [
          {"type": "label"},
          {"type": "logo_grid", "count": 6}
        ]}
      ]
    }
  ]
}
```

### Kompatybilność
- **AutoFix Stages 11-17**: Nadal działają - Compiler generuje `_pattern` attribute
- **Content Generation**: Bez zmian - `JTB_AI_Content` generuje tekst
- **Pexels/Styles**: Bez zmian - działają przez context
- **Fallback**: Gdy AI niedostępne, używa istniejącego Composera (legacy path)

---

## AI AUTOFIX STAGES (11-16)

System deterministycznego post-processingu AI-generowanych layoutów. Każdy stage dodaje warstwę wizualną bez re-promptowania AI.

### Stage 11: SAFE AUTOFIX
Bezpieczne naprawianie błędów bez regresji.
- DARK_MISUSE detection (DARK na LIGHT-only patterns)
- Light-only patterns: grid_density, testimonials, pricing, faq, contact, features, team, etc.
- Dark-allowed patterns: hero, trust_metrics, stats

### Stage 12: VISUAL INTENT ENGINE
Automatyczne przypisywanie intencji wizualnej.
- **visual_intent**: DOMINANT, EMPHASIS, NEUTRAL, SOFT
- Pattern mapping: hero → DOMINANT, features → EMPHASIS, faq → SOFT
- Warnings: VI_CONFLICT (2+ DOMINANT), HERO_NOT_DOMINANT

### Stage 13: VISUAL RHYTHM ENGINE
Kontrola gęstości i przepływu wizualnego.
- **visual_density**: DENSE, NORMAL, SPARSE
- **before_spacing / after_spacing**: sm (24px), md (48px), lg (72px), xl (96px), 2xl (140px)
- Rules: max 2 DENSE z rzędu, SPARSE wymaga lg+ spacing, final_cta = 2xl before
- Warnings: DENSE_CHAIN, NO_CLIMAX, SPARSE_TOO_TIGHT

### Stage 14: ADAPTIVE HERO & CTA SCALING ENGINE
Adaptacyjne skalowanie kluczowych sekcji.
- **visual_scale**: XS (0.85), SM (0.92), MD (1.0), LG (1.12), XL (1.25)
- CSS variables: --jtb-scale, --jtb-scale-heading, --jtb-scale-padding
- Rules: hero = LG/XL, final_cta = LG/XL, max 2 XL sections
- Warnings: HERO_UNDER_SCALED, CTA_NOT_CLIMAX, MULTI_XL

### Stage 15: ADAPTIVE TYPOGRAPHY & CONTENT EMPHASIS ENGINE
Inteligentna hierarchia typografii.
- **typography_scale**: XS, SM, MD, LG, XL
- **text_emphasis**: strong, normal, soft
- Rules: hero = XL + strong, cta = LG/XL + strong, faq = SM/MD + soft
- Warnings: HERO_TYPO_TOO_WEAK, CTA_TYPO_NOT_CLIMAX

### Stage 16: EMOTIONAL CONTRAST & ATTENTION FLOW ENGINE
Kontrola emocjonalnej podróży użytkownika.
- **emotional_tone**: calm, focus, trust, urgency
- **attention_level**: low, medium, high
- Pattern mapping:
  - hero → focus + high
  - testimonials/trust_metrics → trust + medium
  - faq → calm + low
  - final_cta → urgency + high
- Rules: max 2 HIGH z rzędu, urgency >60% strony, wymagane trust + calm sections
- Warnings: ATTENTION_OVERLOAD, NO_TRUST_SECTION, NO_CALM_SECTION, URGENCY_TOO_EARLY, FLAT_FLOW
- Metrics: emotional_flow_signature (F-T-F-U), attention distribution

### AutoFix Pipeline Order
```php
// 1-8: Basic fixes (structure, modules, attrs)
// 9. Stage 12: applyVisualIntent()
// 10. Stage 13: applyVisualDensity()
// 11. Stage 13: applyVisualRhythm()
// 12. Stage 14: applyVisualScale()
// 13. Stage 15: applyTypographyIntent()
// 14. Stage 16: applyEmotionalFlow()
// 15. Stage 17: applyNarrativeRoles()
// 16. Stage 17: fixNarrativeFlow()
```

### Stage 17: NARRATIVE FLOW ENGINE (Story Beats)
Kontrola narracji strony — każda sekcja dostaje rolę w historii użytkownika.
- **narrative_role**: HOOK, PROBLEM, PROMISE, PROOF, DETAILS, RELIEF, RESOLUTION
- Story beats flow: HOOK → PROBLEM → PROMISE → PROOF → DETAILS → RELIEF → RESOLUTION
- Pattern mapping:
  - hero → HOOK
  - problem/challenges → PROBLEM
  - features/services/benefits → PROMISE
  - testimonials/trust_metrics/stats → PROOF
  - pricing/faq → DETAILS
  - breathing_space/contact → RELIEF
  - final_cta → RESOLUTION
- Validation: narrative_signature (e.g., H-PR-PF-D-RL-RS), narrative_score (0-100)
- Rules: PROOF required before DETAILS, CTA_BEFORE_PROMISE = HARD FAIL
- Warnings: NO_PROOF, NO_HOOK, NO_PROMISE, CTA_BEFORE_PROMISE, PROOF_BEFORE_PROMISE, BROKEN_STORY_FLOW
- Metrics: narrative_signature, narrative_score, missing_narrative_roles, broken_story_flow

### CSS Classes Generated
```css
/* Stage 12 */ .jtb-vi-dominant, .jtb-vi-emphasis, .jtb-vi-neutral, .jtb-vi-soft
/* Stage 13 */ .jtb-vd-dense, .jtb-vd-normal, .jtb-vd-sparse
/* Stage 14 */ .jtb-scale-xs, .jtb-scale-sm, .jtb-scale-md, .jtb-scale-lg, .jtb-scale-xl
/* Stage 15 */ .jtb-ts-xs/sm/md/lg/xl, .jtb-te-strong/normal/soft
/* Stage 16 */ .jtb-et-calm/focus/trust/urgency, .jtb-att-low/medium/high
/* Stage 17 */ .jtb-nr-hook/problem/promise/proof/details/relief/resolution
```

### Debug Overlay (DEV_MODE)
- Section badges: pattern, context, ALT, VI, VD, spacing, scale, TS, TE, ET, ATT, NR
- Banner stats: VD distribution, hero/cta scale, typography, attention distribution, flow signature, narrative signature, narrative score
- Story status: ✓ FLOW OK (score ≥70) or ⚠ BROKEN STORY (score <40)
- Warnings: all violations displayed with tooltips

---

**Nowe klasy AI (30.01.2026):**
- `class-jtb-ai-pexels.php` - Integracja z Pexels API (pobiera obrazy na podstawie kontekstu)
- `class-jtb-ai-styles.php` - Profesjonalne presety stylów (kolory, typografia, spacing, shadows)

**Ulepszona jakość generacji AI:**
- Moduły image/testimonial/team_member/gallery pobierają obrazy z Pexels
- Sekcje hero/about/features używają kontekstowych obrazów
- Wszystkie moduły otrzymują profesjonalne style (kolory, typography, spacing)
- 6 predefiniowanych stylów: modern, minimal, bold, elegant, playful, corporate
- Kolory dostosowane do branży (healthcare=teal, finance=blue, technology=indigo, etc.)

**POPRAWKI MAPOWANIA ATRYBUTÓW (30.01.2026):**

Naprawiono krytyczny problem z mapowaniem nazw atrybutów AI → JTB modules:

| Moduł | Błędne nazwy | Prawidłowe nazwy |
|-------|-------------|------------------|
| heading | title, tag | **text**, **level** |
| button | url | **link_url** |
| blurb | icon, icon_size | **font_icon**, **icon_font_size** |
| testimonial | position, image | **job_title**, **portrait_url** |
| section padding | padding_top/bottom | **padding** (array {top,right,bottom,left}) |
| border_radius | string "12px" | **array** {top_left,top_right,bottom_right,bottom_left} |

Style są teraz generowane w prawidłowym formacie:
```php
// Heading - prawidłowe pola
[
    'text' => 'Build Something Extraordinary',
    'level' => 'h2',
    'font_family' => 'Inter',
    'font_size' => 42,  // numeric!
    'font_weight' => '700',
    'text_color' => '#111827'
]

// Section padding - prawidłowy format
[
    'padding' => ['top' => 100, 'right' => 0, 'bottom' => 100, 'left' => 0],
    'padding__tablet' => ['top' => 60, 'right' => 0, 'bottom' => 60, 'left' => 0]
]

// Border radius - prawidłowy format
[
    'border_radius' => ['top_left' => 12, 'top_right' => 12, 'bottom_right' => 12, 'bottom_left' => 12]
]
```

---

## PEŁNY AUDYT JTB (03.02.2026)

### Status gotowości: ~65%

| Kategoria | Status | Gotowość |
|-----------|--------|----------|
| Renderer & CSS | ⚠️ Wymaga pracy | 60% |
| Moduły (79) | ⚠️ Częściowo | 65% |
| API (59 endpoints) | ✅ Dobrze | 85% |
| JavaScript (14 plików) | ⚠️ Wymaga pracy | 55% |
| Theme Builder | ⚠️ Prawie | 75% |
| AI Integration | ✅ Działa | 90% |

### Statystyki modułów
```
COMPLETE (✅):  31/79 (39%)
PARTIAL (⚠️):  42/79 (53%)
STUB (❌):      6/79  (8%)
```

### Szczegółowe raporty
- `docs/JTB_FULL_AUDIT_REPORT.md` - Podsumowanie audytu
- `docs/IMPLEMENTATION_PLAN_PART1.md` - Reguły + Etap 1 (CSS)
- `docs/IMPLEMENTATION_PLAN_PART2.md` - Etapy 2-4 (JS, Pickers, CSS Arch)
- `docs/IMPLEMENTATION_PLAN_PART3.md` - Etapy 5-8 (Media, Builder, Carousel, Theme)
- `docs/IMPLEMENTATION_PLAN_PART4.md` - Etapy 9-12 (CMS, API, Stub, Testing)

---

## ABSOLUTNE REGUŁY IMPLEMENTACJI

### Reguła 1: NIGDY nie nadpisuj całych plików CMS
```
Pliki w /var/www/cms/ mogą być modyfikowane TYLKO przez chirurgiczne diffy.
Każda zmiana w CMS musi być minimalna i precyzyjna.
Przed edycją CMS - zawsze przeczytaj aktualną wersję pliku.
```

### Reguła 2: NIGDY nie upraszczaj implementacji
```
Każda funkcja musi być W PEŁNI zaimplementowana.
Brak placeholderów, stubów, TODO komentarzy.
Wszystkie edge cases muszą być obsłużone.
```

### Reguła 3: Zachowaj kompatybilność wsteczną
```
Istniejące API nie może się zmienić (tylko rozszerzenia).
Istniejące CSS classes muszą nadal działać.
Istniejące JS funkcje muszą zachować sygnaturę.
```

### Reguła 4: Testuj przed przejściem dalej
```
Po każdej zmianie - sprawdź składnię PHP.
Po zmianach CSS - sprawdź renderowanie.
Po zmianach JS - sprawdź console errors.
```

### Reguła 5: Dokumentuj zmiany
```
Komentarz w kodzie z datą i opisem.
Update CLAUDE.md po większych zmianach.
```

---

## PLAN IMPLEMENTACJI - PODZIAŁ NA SESJE

### SESJA 1: CSS Critical Fixes (Etap 1)
**Czas:** 1 dzień pracy
**Cel:** Naprawić renderowanie CSS

**Zadania:**
1. Utworzyć `class-jtb-css-output.php` - CSS w `<head>` zamiast `<body>`
2. Zaktualizować `class-jtb-renderer.php` - użycie CSS_Output
3. Zaktualizować `class-jtb-theme-integration.php` - render CSS w head
4. Dodać Stage 12-17 CSS classes do `frontend.css`
5. Naprawić visibility classes (display: block → none)
6. Dodać animation keyframes (7 typów)

**Pliki do utworzenia:**
- `plugin/includes/class-jtb-css-output.php`

**Pliki do edycji:**
- `plugin/includes/class-jtb-renderer.php`
- `plugin/includes/class-jtb-theme-integration.php`
- `plugin/assets/css/frontend.css`
- `plugin/api/router.php`

**Weryfikacja:**
```bash
wsl -u root bash -c 'php -l /var/www/cms/plugins/jessie-theme-builder/includes/class-jtb-css-output.php'
```

**PRZERWA PO SESJI 1** - Deploy i test renderowania

---

### SESJA 2: JavaScript Memory Leaks (Etap 2)
**Czas:** 0.5 dnia pracy
**Cel:** Usunąć memory leaks

**Zadania:**
1. `frontend.js` - setInterval z clearInterval
2. `frontend.js` - MutationObserver z disconnect
3. `ai-panel.js` - Event listeners cleanup
4. `settings-panel.js` - Usunięcie duplikatu gradient

**Pliki do edycji:**
- `plugin/assets/js/frontend.js`
- `plugin/assets/js/ai-panel.js`
- `plugin/assets/js/settings-panel.js`

**Weryfikacja:**
- DevTools → Performance → Heap snapshots
- Otwórz/zamknij AI panel 10x, sprawdź memory

**PRZERWA PO SESJI 2** - Test memory w przeglądarce

---

### SESJA 3: Icon Picker & Color Picker (Etap 3)
**Czas:** 1.5 dnia pracy
**Cel:** Pełna implementacja pickers

**Zadania:**
1. Icon Picker - pełna integracja z Feather Icons (287 ikon)
2. Icon Picker CSS - modal styles
3. Color Picker - RGBA z alpha slider
4. Color Picker CSS

**Pliki do edycji:**
- `plugin/assets/js/settings-panel.js` (Icon Picker)
- `plugin/assets/js/fields.js` (Color Picker)
- `plugin/assets/css/builder.css` (styles dla obu)

**Weryfikacja:**
- Otwórz Icon Picker, wyszukaj "arrow", wybierz
- Otwórz Color Picker, ustaw alpha 50%

**PRZERWA PO SESJI 3** - Test pickers w builderze

---

### SESJA 4: CSS Architecture Unification (Etap 4)
**Czas:** 1 dzień pracy
**Cel:** Ujednolicić system CSS

**Zadania:**
1. Utworzyć `class-jtb-style-system.php` - zastąpi konflikt CSS_Generator vs CSS_Variables
2. Zaktualizować theme-settings API do użycia Style System
3. Dodać cache invalidation

**Pliki do utworzenia:**
- `plugin/includes/class-jtb-style-system.php`

**Pliki do edycji:**
- `plugin/api/theme-settings.php`
- `plugin/api/router.php`

**Weryfikacja:**
- Zmień theme setting, sprawdź czy CSS się regeneruje
- Sprawdź czy nie ma zduplikowanych zmiennych CSS

**PRZERWA PO SESJI 4** - Test theme settings

---

### SESJA 5: Media Library Browser (Etap 5)
**Czas:** 1.5 dnia pracy
**Cel:** Przeglądanie istniejących mediów

**Zadania:**
1. Utworzyć `media-browser.php` API endpoint
2. JavaScript Media Library Modal
3. CSS dla modala
4. Integracja z upload fields

**Pliki do utworzenia:**
- `plugin/api/media-browser.php`

**Pliki do edycji:**
- `plugin/api/router.php`
- `plugin/assets/js/settings-panel.js`
- `plugin/assets/css/builder.css`

**Weryfikacja:**
- Otwórz Media Library, przeglądaj obrazy
- Wyszukaj, filtruj, wybierz obraz

**PRZERWA PO SESJI 5** - Test Media Library

---

### SESJA 6: Builder Features (Etap 6)
**Czas:** 1 dzień pracy
**Cel:** Undo/Redo + Responsive Preview

**Zadania:**
1. Undo/Redo system (JTB.History)
2. Keyboard shortcuts (Ctrl+Z, Ctrl+Shift+Z)
3. Responsive Preview controller
4. CSS dla device buttons i preview

**Pliki do edycji:**
- `plugin/assets/js/builder.js`
- `plugin/assets/css/builder.css`

**Weryfikacja:**
- Zrób zmianę, Ctrl+Z (powinno cofnąć)
- Przełącz desktop/tablet/phone (canvas powinien zmienić rozmiar)

**PRZERWA PO SESJI 6** - Test Undo/Redo i Preview

---

### SESJA 7: Carousel/Slider (Etap 7)
**Czas:** 1.5 dnia pracy
**Cel:** Działające carousel dla 5 modułów

**Zadania:**
1. Utworzyć `carousel.js` - uniwersalny carousel
2. CSS dla carousel
3. Aktualizacja modułów slider do użycia data-jtb-carousel
4. Test wszystkich 5 modułów slider

**Pliki do utworzenia:**
- `plugin/assets/js/carousel.js`

**Pliki do edycji:**
- `plugin/assets/css/frontend.css`
- `plugin/modules/media/slider.php`
- `plugin/modules/fullwidth/fullwidth_slider.php`
- (i inne slider modules)
- `plugin/views/builder.php` (dodać script tag)

**Weryfikacja:**
- Dodaj slider, sprawdź nawigację strzałkami
- Sprawdź autoplay, paginację dots
- Test swipe na mobile

**PRZERWA PO SESJI 7** - Test wszystkich sliderów

---

### SESJA 8: Theme Modules Dynamic (Etap 8)
**Czas:** 1.5 dnia pracy
**Cel:** Dynamic data w theme modules

**Zadania:**
1. Utworzyć `class-jtb-dynamic-queries.php`
2. Zaktualizować 10 theme modules do użycia Dynamic Context
3. Test na frontendie z rzeczywistymi danymi

**Pliki do utworzenia:**
- `plugin/includes/class-jtb-dynamic-queries.php`

**Pliki do edycji:**
- `plugin/modules/theme/post-title.php`
- `plugin/modules/theme/post-excerpt.php`
- `plugin/modules/theme/post-content.php`
- `plugin/modules/theme/post-meta.php`
- `plugin/modules/theme/featured-image.php`
- `plugin/modules/theme/author-box.php`
- `plugin/modules/theme/related-posts.php`
- `plugin/modules/theme/archive-title.php`
- `plugin/modules/theme/archive-posts.php`
- `plugin/modules/theme/breadcrumbs.php`

**Weryfikacja:**
- Otwórz stronę posta na frontendie
- Sprawdź czy tytuł, excerpt, meta pokazują rzeczywiste dane

**PRZERWA PO SESJI 8** - Test frontend z templates

---

### SESJA 9: CMS Integration Hook (Etap 9)
**Czas:** 0.5 dnia pracy
**Cel:** Frontend templates działają

**UWAGA: CHIRURGICZNY DIFF W CMS!**

**Zadania:**
1. Przeczytać aktualny `/var/www/cms/index.php`
2. Dodać MINIMALNY fragment kodu dla JTB frontend
3. Test na frontendie

**Pliki do edycji (TYLKO DIFF!):**
- `/var/www/cms/index.php`

**Fragment do dodania (przed dispatch()):**
```php
// JTB FRONTEND INTEGRATION - dodane 2026-02-XX
if (!str_starts_with($jtbUri, '/admin') && !str_starts_with($jtbUri, '/api')) {
    // ... (patrz IMPLEMENTATION_PLAN_PART4.md)
}
```

**Weryfikacja:**
- Utwórz template header w JTB
- Przypisz do "All pages"
- Otwórz dowolną stronę na frontencie - header powinien się wyświetlić

**PRZERWA PO SESJI 9** - Test całej integracji frontend

---

### SESJA 10: API Improvements (Etap 10)
**Czas:** 1 dzień pracy
**Cel:** Ulepszenia API

**Zadania:**
1. Standaryzacja response format (success vs ok)
2. Usunięcie DEBUG logs
3. Dodanie rate limiting
4. Error messages bez ścieżek

**Pliki do edycji:**
- `plugin/api/router.php`
- `plugin/api/ai/*.php` (wszystkie AI endpoints)
- `plugin/api/render.php`

**Weryfikacja:**
- Wyślij 50 requestów do AI endpoint - powinien zwrócić 429
- Sprawdź czy response ma format `{success: true/false}`

**PRZERWA PO SESJI 10** - Test API

---

### SESJA 11: Stub Modules Completion (Etap 11)
**Czas:** 2 dni pracy
**Cel:** Dokończyć stub modules

**Zadania:**
1. Toggle module - pełna implementacja
2. Circle counter - SVG generation
3. Bar counter - bar rendering
4. Inne stub modules (shop, filterable_portfolio - jeśli czas)

**Pliki do edycji:**
- `plugin/modules/interactive/toggle.php`
- `plugin/modules/content/circle_counter.php`
- `plugin/modules/content/bar_counter.php`
- (opcjonalnie inne)

**Weryfikacja:**
- Dodaj Toggle, kliknij - powinien się rozwinąć
- Dodaj Circle Counter - powinien pokazać SVG circle

**PRZERWA PO SESJI 11** - Test wszystkich modułów

---

### SESJA 12: Final Testing (Etap 12)
**Czas:** 1 dzień pracy
**Cel:** Końcowe testy integracyjne

**Zadania:**
1. Utworzyć `integration-test.php`
2. Przejść przez checklist testów
3. Naprawić znalezione problemy

**Pliki do utworzenia:**
- `plugin/tests/integration-test.php`

**Checklist:**
```
[ ] CSS w <head>
[ ] Stage 12-17 classes działają
[ ] Visibility działa
[ ] Brak memory leaks
[ ] Icon Picker działa (287 ikon)
[ ] Color Picker RGBA działa
[ ] Media Library działa
[ ] Undo/Redo działa
[ ] Responsive Preview działa
[ ] Carousel działa
[ ] Theme modules pokazują dane
[ ] Frontend templates renderują się
[ ] API ma spójny format
[ ] Rate limiting działa
```

---

## PODSUMOWANIE SESJI

| Sesja | Etap | Czas | Priorytet |
|-------|------|------|-----------|
| 1 | CSS Critical | 1 dzień | CRITICAL |
| 2 | Memory Leaks | 0.5 dnia | CRITICAL |
| 3 | Pickers | 1.5 dnia | CRITICAL |
| 4 | CSS Architecture | 1 dzień | HIGH |
| 5 | Media Library | 1.5 dnia | HIGH |
| 6 | Builder Features | 1 dzień | HIGH |
| 7 | Carousel | 1.5 dnia | HIGH |
| 8 | Theme Modules | 1.5 dnia | HIGH |
| 9 | CMS Hook | 0.5 dnia | HIGH |
| 10 | API | 1 dzień | MEDIUM |
| 11 | Stub Modules | 2 dni | MEDIUM |
| 12 | Testing | 1 dzień | MEDIUM |
| **RAZEM** | | **~14 dni** | |

---

## QUICK START DLA NASTĘPNEJ SESJI

1. **Przeczytaj CLAUDE.md** (ten plik) - sekcja "PLAN IMPLEMENTACJI"
2. **Sprawdź która sesja jest następna**
3. **Przeczytaj odpowiedni IMPLEMENTATION_PLAN_PARTX.md**
4. **Deploy do WSL:**
   ```bash
   wsl -u root bash -c 'cp -r "/mnt/c/Users/krala/Downloads/jessie theme builder/plugin/"* /var/www/cms/plugins/jessie-theme-builder/ && chown -R www-data:www-data /var/www/cms/plugins/jessie-theme-builder'
   ```
5. **Wykonaj zadania z sesji**
6. **Po zakończeniu - weryfikacja z listy**
7. **PRZERWA** przed następną sesją

---

## THEME BUILDER - SZCZEGÓŁOWY PLAN (04.02.2026)

### Status Theme Builder: ~75% gotowości

| Komponent | Status | Gotowość |
|-----------|--------|----------|
| Architektura templates | ✅ | 100% |
| Baza danych (3 tabele) | ✅ | 100% |
| API Templates CRUD (8 endpointów) | ✅ | 100% |
| API Conditions (2 endpointy) | ✅ | 100% |
| API Global Modules (4 endpointy) | ✅ | 100% |
| Template Manager UI | ✅ | 95% |
| Template Editor UI | ✅ | 90% |
| JTB_Dynamic_Context (25+ metod) | ✅ | 90% |
| Theme Modules (19+) | ⚠️ | 75% |
| AI Panel w Template Editor | ⚠️ | 30% |
| Frontend Integration | ⚠️ | 50% |
| Conditions Builder | ⚠️ | 70% |

### Theme Modules - Lista (19 modułów)

```
modules/theme/
├── post-title.php          ✅ Dynamic
├── post-content.php        ✅ Dynamic
├── post-excerpt.php        ✅ Dynamic
├── post-meta.php           ✅ Dynamic
├── featured-image.php      ✅ Dynamic
├── author-box.php          ✅ Dynamic (04.02.2026)
├── related-posts.php       ✅ Dynamic (04.02.2026)
├── archive-posts.php       ✅ Dynamic (04.02.2026)
├── archive-title.php       ⚠️ Needs fix
├── breadcrumbs.php         ✅ Dynamic
├── menu.php                ✅ Dynamic (04.02.2026)
├── site-logo.php           ✅ Dynamic (04.02.2026)
├── social-icons.php        ✅ Dynamic (04.02.2026)
├── search-form.php         ⚠️ Needs action URL
├── footer-menu.php         ❌ TODO
└── copyright.php           ❌ TODO
```

### JTB_Dynamic_Context - Metody (25+)

```php
// Core
setContext(array $context)
get(?string $key = null)
isPreviewMode(): bool

// Post Data
getPost(): ?array
getPostTitle(): string
getPostContent(): string
getPostExcerpt(): string
getPostDate(?string $format = null): string
getPostUrl(): string
getFeaturedImage(): string
getPostCategories(): array
getPostTags(): array

// Author Data
getAuthor(): array
getAuthorName(): string
getAuthorBio(): string
getAuthorAvatar(): string
getAuthorUrl(): string
getAuthorRole(): string              // NEW 04.02.2026
getAuthorSocial(): array             // NEW 04.02.2026

// Archive Data
getArchivePosts(int $limit = 10, int $page = 1): array
getRelatedPosts(int $limit = 3): array
getArchiveTitle(): string
getArchiveDescription(): string

// Site Data
getSiteTitle(): string
getSiteLogo(): string
getSiteSocial(): array               // NEW 04.02.2026
getSearchUrl(): string

// Menu Data
getMenuItems(?string $menuId = null): array
```

### Theme Builder - Etapy do realizacji

| Etap | Nazwa | Czas | Priorytet | Status |
|------|-------|------|-----------|--------|
| TB-1 | AI Panel Integration | 1 dzień | HIGH | ✅ DONE (04.02.2026) |
| TB-2 | Theme Modules Completion | 1.5 dnia | HIGH | ✅ DONE (04.02.2026) |
| TB-3 | Conditions Builder Fix | 0.5 dnia | HIGH | TODO |
| TB-4 | CMS Frontend Hook | 0.5 dnia | CRITICAL | TODO |
| TB-5 | Live Preview | 1 dzień | MEDIUM | TODO |
| TB-6 | Testing & Polish | 0.5 dnia | MEDIUM | TODO |
| **RAZEM** | | **5 dni** | | |

### Wykonane prace (04.02.2026)

**TB-1: AI Panel Integration - DONE**
- Dodano HTML AI Panel do template-editor.php
- Dodano CSS dla panelu (180 linii) do template-manager.css
- Dodano JavaScript JTB_AI_Template do template-editor.js
- Naprawiono mapping parametrów (type vs template_type)

**TB-2: Theme Modules Completion - DONE**
- Naprawiono search-form.php - używa getSearchUrl()
- Naprawiono archive-title.php - dynamiczne dane z JTB_Dynamic_Context
- Naprawiono copyright.php - dynamiczne site_name z getSiteTitle()
- Dodano metody do JTB_Dynamic_Context:
  - getSearchUrl()
  - getArchiveTitle() - pełna wersja z prefixami
  - getArchiveDescription()
- footer-menu.php i copyright.php już istniały i działają

### TB-1: AI Panel Integration (1 dzień)

**Cel:** AI generowanie templates w Template Editor

**Pliki do edycji:**
- `plugin/views/template-editor.php` - dodać HTML AI Panel
- `plugin/assets/css/template-manager.css` - CSS dla panelu
- `plugin/assets/js/template-editor.js` - JavaScript JTB_AI_Template

**API Endpoint:** `/api/jtb/ai/generate-template` (już istnieje)

**Weryfikacja:**
```bash
# 1. Deploy
wsl -u root bash -c 'cp -r "/mnt/c/Users/krala/Downloads/jessie theme builder/plugin/"* /var/www/cms/plugins/jessie-theme-builder/'

# 2. Test
# - Otwórz /admin/jtb/template/edit/1
# - Kliknij przycisk AI w prawym dolnym rogu
# - Panel powinien się wysunąć
# - Wpisz prompt i kliknij Generate
```

### TB-2: Theme Modules Completion (1.5 dnia)

**Cel:** Wszystkie 19+ modułów działają z dynamic data

**Do naprawienia:**
1. `search-form.php` - brak action URL → użyć getSearchUrl()
2. `archive-title.php` - placeholder → użyć getArchiveTitle()

**Do utworzenia:**
1. `footer-menu.php` - nowy moduł
2. `copyright.php` - nowy moduł

**Do dodania w JTB_Dynamic_Context:**
- `getSearchUrl(): string`
- `getArchiveDescription(): string`

### TB-3: Conditions Builder Fix (0.5 dnia)

**Cel:** Object selector ładuje obiekty

**Plik:** `plugin/assets/js/conditions-builder.js`

**Problem:** Object selector pokazuje "Loading..." i nie ładuje obiektów

**Rozwiązanie:** Naprawić funkcję `renderObjectSelector()` - fetch do `/api/jtb/conditions-objects`

### TB-4: CMS Frontend Hook (0.5 dnia) - CRITICAL

**Cel:** Templates renderują się na frontencie

**UWAGA: CHIRURGICZNY DIFF W CMS!**

**Plik:** `/var/www/cms/index.php`

**Fragment do dodania (przed dispatch()):**
```php
// JTB THEME BUILDER - FRONTEND TEMPLATES
// Added: 2026-02-04
if (!str_starts_with($jtbUri, '/admin') && !str_starts_with($jtbUri, '/api')) {
    $jtbPluginPath = CMS_ROOT . '/plugins/jessie-theme-builder';
    $jtbIntegrationFile = $jtbPluginPath . '/includes/class-jtb-theme-integration.php';

    if (file_exists($jtbIntegrationFile)) {
        // Load required classes...
        // Try to handle request with JTB templates...
    }
}
```

**Weryfikacja:**
- Utwórz header template w JTB
- Ustaw warunek "Include All Pages"
- Otwórz dowolną stronę na frontencie - header powinien się wyświetlić

### TB-5: Live Preview (1 dzień)

**Cel:** Iframe preview w Template Editor

**Pliki do edycji:**
- `plugin/views/template-editor.php` - dodać iframe preview panel
- `plugin/assets/css/template-manager.css` - CSS dla preview
- `plugin/assets/js/template-editor.js` - device switcher

### TB-6: Testing & Polish (0.5 dnia)

**Checklist:**
```
[ ] AI Panel otwiera się w Template Editor
[ ] AI generuje templates (header/footer/body)
[ ] Wygenerowany template można zaaplikować do canvas
[ ] Wszystkie theme modules renderują się poprawnie
[ ] Search form ma prawidłowy action URL
[ ] Archive title pokazuje prawidłowy tytuł
[ ] Footer menu renderuje menu z bazy
[ ] Copyright pokazuje prawidłowy rok i nazwę
[ ] Conditions builder ładuje obiekty
[ ] Frontend integration wyświetla templates
[ ] Live preview działa dla wszystkich device sizes
```

---

## ZUNIFIKOWANY SYSTEM AI (PLAN - 04.02.2026)

### PROBLEM: Chaos w AI Integration

**Stan przed unifikacją:**
- 14 różnych endpointów AI (zduplikowane, niespójne)
- 3 konkurujące architektury: Legacy Composer, AST Pipeline, Direct Generation
- **KRYTYCZNY BUG**: `generate-template.php` ma HARDCODED błędną dokumentację modułów:
  - `site-logo`: doc mówi `logo_url` ale moduł używa `logo`
  - `menu`: doc mówi `menu_items: [...]` ale moduł pobiera z bazy danych
  - `button`: doc mówi `primary/secondary` ale moduł używa `solid/outline/ghost`
  - `social-icons`: doc mówi `icons: [{platform, url}]` ale moduł używa `facebook_url`, `twitter_url` etc.
- To powoduje że AI generuje atrybuty których moduły NIE ROZPOZNAJĄ!

### ROZWIĄZANIE: Jeden zunifikowany system AI

```
┌─────────────────────────────────────────────────────────────────────┐
│                         JTB UNIFIED AI                               │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│   ┌──────────────┐    ┌──────────────┐    ┌──────────────┐         │
│   │ Page Builder │    │Theme Builder │    │   Library    │         │
│   │   (posts)    │    │ (templates)  │    │   (saved)    │         │
│   └──────┬───────┘    └──────┬───────┘    └──────┬───────┘         │
│          │                   │                   │                  │
│          └───────────────────┼───────────────────┘                  │
│                              ▼                                       │
│                    ┌─────────────────┐                              │
│                    │ /api/jtb/ai/gen │  ◄── JEDEN endpoint          │
│                    └────────┬────────┘                              │
│                             │                                        │
│          ┌──────────────────┼──────────────────┐                    │
│          ▼                  ▼                  ▼                    │
│   ┌─────────────┐    ┌─────────────┐    ┌─────────────┐            │
│   │JTB_Registry │    │JTB_Renderer │    │JTB_Dynamic  │            │
│   │ (85 modules)│    │  (shared)   │    │  Context    │            │
│   └─────────────┘    └─────────────┘    └─────────────┘            │
│          │                                                          │
│          ▼                                                          │
│   ┌─────────────┐                                                   │
│   │JTB_AI_Schema│ ◄── AUTOMATYCZNE schematy z Registry!            │
│   │exportAll()  │     (ZERO hardcoded dokumentacji)                │
│   └─────────────┘                                                   │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

### JEDEN ENDPOINT: `/api/jtb/ai/generate`

**Request format:**
```json
{
    "action": "layout|section|module",
    "context": {
        "type": "page|template",
        "id": 123,
        "template_type": "header|footer|body|404",
        "industry": "technology",
        "style": "modern"
    },
    "prompt": "Professional header with logo and navigation"
}
```

**Response format (zunifikowany):**
```json
{
    "success": true,
    "data": {
        "sections": [...]
    },
    "stats": {
        "time_ms": 3500,
        "tokens_used": 2450,
        "provider": "anthropic"
    }
}
```

### JEDEN FORMAT DANYCH (dla Page Builder i Template Builder)

```json
{
    "sections": [
        {
            "type": "section",
            "attrs": {"padding": {"top": 80, "bottom": 80}},
            "children": [
                {
                    "type": "row",
                    "attrs": {"columns": "1_3,2_3"},
                    "children": [
                        {
                            "type": "column",
                            "children": [
                                {
                                    "type": "site_logo",
                                    "attrs": {
                                        "logo": "/uploads/logo.png",
                                        "logo_alt": "Company Logo"
                                    }
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ]
}
```

### PLAN IMPLEMENTACJI

| Etap | Zadanie | Czas | Priorytet |
|------|---------|------|-----------|
| AI-1 | Usunięcie `generate-template.php` i błędnych endpointów | 30 min | CRITICAL |
| AI-2 | Utworzenie `/api/jtb/ai/generate.php` (jeden endpoint) | 2-3h | CRITICAL |
| AI-3 | Integracja z `JTB_AI_Schema::exportAllModules()` | 1h | CRITICAL |
| AI-4 | Aktualizacja `ai-panel.js` (Page Builder) | 1h | HIGH |
| AI-5 | Aktualizacja `template-editor.js` (Template Builder) | 1h | HIGH |
| AI-6 | Testy integracyjne | 1h | HIGH |
| **RAZEM** | | **~7h** | |

### PLIKI DO USUNIĘCIA

```
plugin/api/ai/generate-template.php    ← KRYTYCZNY BUG! Hardcoded błędna dokumentacja
plugin/api/ai/generate-layout.php      ← Duplikat
plugin/api/ai/compose-layout.php       ← Duplikat (legacy)
```

### PLIKI DO UTWORZENIA

```
plugin/api/ai/generate.php             ← Jeden zunifikowany endpoint
```

### PLIKI DO MODYFIKACJI

```
plugin/api/router.php                  ← Routing do nowego endpointu
plugin/assets/js/ai-panel.js           ← Użycie /api/jtb/ai/generate
plugin/assets/js/template-editor.js    ← Użycie tego samego endpointu
```

### KLUCZOWE ZASADY

1. **ZERO hardcoded dokumentacji** - AI używa `JTB_AI_Schema::exportAllModules()`
2. **JEDEN format danych** - sections → rows → columns → modules
3. **JEDEN Renderer** - `JTB_Renderer::render()` dla wszystkiego
4. **AUTOMATYCZNA normalizacja slugów** - Registry konwertuje `site-logo` → `site_logo`
5. **Kontekst decyduje** - `context.type` określa czy Page Builder czy Template Builder

### WERYFIKACJA PO IMPLEMENTACJI

```
[ ] AI generuje layout dla Page Builder
[ ] AI generuje template dla Theme Builder (header/footer/body)
[ ] site_logo renderuje się z prawidłowym obrazem (nie placeholder)
[ ] menu renderuje elementy z bazy danych
[ ] button używa prawidłowych stylów (solid/outline/ghost)
[ ] social_icons renderuje ikony z prawidłowymi URL-ami
[ ] Format response jest identyczny dla obu kontekstów
[ ] Brak hardcoded dokumentacji w kodzie
```

---

## OSTATNIA AKTUALIZACJA

**Data:** 04.02.2026
**Wykonane:**
- Pełny audyt wszystkich komponentów JTB
- Raport z 67 problemami (15 CRITICAL, 20 HIGH, 32 MEDIUM)
- Plan implementacji podzielony na 12 etapów / 12 sesji
- Dokumentacja w 4 częściach (docs/IMPLEMENTATION_PLAN_PART*.md)
- Zaktualizowany CLAUDE.md z regułami i planem
- **Theme Builder audit (04.02.2026):**
  - Zaktualizowane theme modules z dynamic data (6 modułów)
  - Dodane helper methods do JTB_Dynamic_Context (3 metody)
  - Utworzony szczegółowy plan Theme Builder (6 etapów, 5 dni)
  - Zaktualizowany THEME_BUILDER_IMPLEMENTATION_PLAN.md
- **ZUNIFIKOWANY SYSTEM AI - ZAIMPLEMENTOWANY (04.02.2026):**
  - ✅ USUNIĘTO: `generate-template.php` z błędną hardcoded dokumentacją
  - ✅ UTWORZONO: Zunifikowany `/api/jtb/ai/generate.php` dla Page Builder i Template Builder
  - ✅ NAPRAWIONO: `JTB_AI_Schema::getCompactSchemasForAI()` - prawidłowe atrybuty theme modules
  - ✅ ZAKTUALIZOWANO: `ai-panel.js` - używa zunifikowanego endpointu
  - ✅ ZDEPLOYOWANO: Do WSL, wszystkie pliki PHP bez błędów składni

**Status:** ZUNIFIKOWANY SYSTEM AI GOTOWY DO TESTÓW

**Zmiany w poprzednich sesjach (04.02.2026):**
- **IMPLEMENTACJA ZUNIFIKOWANEGO AI - KOMPLETNA:**
  - Usunięto: `plugin/api/ai/generate-template.php`
  - Utworzono/przepisano: `plugin/api/ai/generate.php` (zunifikowany endpoint)
  - Zaktualizowano: `plugin/api/router.php` (usunięto stary endpoint)
  - Zaktualizowano: `plugin/assets/js/ai-panel.js` (3 miejsca używające starego endpointu)
  - Naprawiono: `plugin/includes/ai/class-jtb-ai-schema.php` (prawidłowe atrybuty dla:
    - site_logo: logo, logo_url, logo_alt (NIE logo_url jako obraz!)
    - menu: bez menu_items (pobiera z DB)
    - button: solid/outline/ghost (NIE primary/secondary!)
    - social_icons: facebook_url, twitter_url, etc. (NIE icons array!)
  - Deploy do WSL: OK, brak błędów składni

---

## WEBSITE BUILDER - UNIFIED INTERFACE (NOWE - 04.02.2026)

### Cel
Jeden interfejs do budowania **całego website**:
- Header (wspólny)
- Footer (wspólny)
- Pages (content poszczególnych podstron)
- Theme Settings (globalne style)

### Nowe Pliki
```
plugin/views/website-builder.php      # Unified Theme Builder UI
plugin/api/ai/generate-website.php    # AI Website Generation endpoint
plugin/includes/ai/class-jtb-ai-website.php  # Website generator class
```

### Routing
Dodano w `/var/www/cms/index.php`:
```php
// Website Builder
if (preg_match('#^/admin/jtb/website-builder/?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/controllers/template-controller.php';
    (new \JessieThemeBuilder\TemplateController())->websiteBuilder();
    exit;
}
```

### API Endpoint: `/api/jtb/ai/generate-website`

**Request:**
```json
{
    "prompt": "Professional law firm website",
    "industry": "legal",
    "style": "corporate",
    "pages": ["home", "about", "services", "contact"]
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "website": {
            "header": {"sections": [...]},
            "footer": {"sections": [...]},
            "pages": {
                "home": {"title": "Home", "sections": [...]},
                "about": {"title": "About Us", "sections": [...]}
            },
            "theme_settings": {
                "colors": {...},
                "typography": {...}
            }
        },
        "stats": {
            "time_ms": 5000,
            "provider": "anthropic"
        }
    }
}
```

### ARCHITEKTURA AI - BEZ HARDKODÓW

**KRYTYCZNA ZASADA:** Wszystkie endpointy AI MUSZĄ używać istniejących klas bez hardkodowanej dokumentacji.

| Endpoint | Używa klasy | Źródło schematów |
|----------|-------------|------------------|
| `/api/jtb/ai/generate` | `JTB_AI_Direct` | `JTB_AI_Schema::getCompactSchemasForAI()` |
| `/api/jtb/ai/generate-website` | `JTB_AI_Website` | `JTB_AI_Schema::getCompactSchemasForAI()` |

**NIGDY NIE DODAWAJ:**
- Hardkodowanych opisów modułów w endpointach
- Duplikowania logiki która już istnieje w klasach
- Własnych list atrybutów - wszystko musi pochodzić z `JTB_AI_Schema`

### Klasy AI - Hierarchia

```
JTB_AI_Schema
├── getCompactSchemasForAI()  → Zwraca schematy WSZYSTKICH modułów
└── exportAllModules()        → Pełny eksport dla debugowania

JTB_AI_Website
├── generate()                → Generuje całą stronę (header + footer + pages)
├── buildSystemPrompt()       → Używa JTB_AI_Schema::getCompactSchemasForAI()
├── parseWebsiteJson()        → Parsuje odpowiedź AI
└── postProcess()             → Normalizacja slugów, enrichment z Pexels

JTB_AI_Direct
├── generateLayout()          → 5-step pipeline (outline → wireframe → style → content → images)
└── Używa JTB_AI_Knowledge    → Który używa JTB_AI_Schema
```

---

## OSTATNIA AKTUALIZACJA

**Data:** 04.02.2026 (sesja 2)
**Wykonane:**
- ✅ Website Builder UI (`plugin/views/website-builder.php`)
- ✅ AI Website Generation endpoint (przepisany na używanie `JTB_AI_Website`)
- ✅ Routing w CMS index.php
- ✅ Admin menu z linkiem do Website Builder

**NAPRAWIONE:**
- ✅ `generate-website.php` - USUNIĘTO hardkodowane opisy modułów (linie 226-253)
- ✅ Endpoint teraz używa `JTB_AI_Website::generate()` która pobiera schematy z `JTB_AI_Schema`
- ✅ Zero duplikacji kodu - cała logika w klasie `JTB_AI_Website`

**Status:** Website Builder UI działa, AI modal otwiera się prawidłowo

---

## AI WEBSITE BUILDER - MASTER PLAN (04.02.2026)

### Dokumentacja szczegółowa
**Plik:** `docs/AI_WEBSITE_BUILDER_MASTER_PLAN.md`

### Wyniki audytów

| Komponent | Wynik | Kluczowe problemy |
|-----------|-------|-------------------|
| **AI Generation** | 82/100 | Minimalna ilość sekcji (1-2 vs 6-10), placeholder URLs, brak SEO |
| **CMS Integration** | 75/100 | Debug logs do /tmp, dual AI config (JSON + DB) |
| **Module Rendering** | 75/100 | Brak focus states, brak prefers-reduced-motion, hamburger JS missing |

### 7-etapowy AI Pipeline (NOWA ARCHITEKTURA)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         AI WEBSITE GENERATION PIPELINE                       │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  1. ANALYSIS        2. SITEMAP         3. WIREFRAME        4. CONTENT       │
│  ┌─────────┐       ┌─────────┐        ┌─────────┐        ┌─────────┐       │
│  │ Parse   │──────▶│ Pages   │───────▶│ Sections│───────▶│ Text    │       │
│  │ Intent  │       │ Structure│        │ per page │        │ Generate│       │
│  └─────────┘       └─────────┘        └─────────┘        └─────────┘       │
│       │                                                        │            │
│       ▼                                                        ▼            │
│  5. STYLING         6. MEDIA           7. VALIDATION                        │
│  ┌─────────┐       ┌─────────┐        ┌─────────┐                          │
│  │ Colors  │──────▶│ Pexels  │───────▶│ Quality │───────▶ FINAL OUTPUT     │
│  │ Fonts   │       │ DALL-E  │        │ Check   │                          │
│  └─────────┘       └─────────┘        └─────────┘                          │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Web Designer Knowledge Base

System bazowy wiedzy dla AI:

| Kategoria | Zawartość |
|-----------|-----------|
| **Industry Templates** | 12+ branż (tech, healthcare, legal, restaurant, etc.) |
| **Section Patterns** | 15+ typów (hero, features, testimonials, pricing, CTA, FAQ) |
| **Design Rules** | Typography scales, spacing systems, color harmony |
| **SEO Requirements** | Meta tags, Schema.org, heading hierarchy |

### Media Pipeline

```
Image Request
    │
    ├─── Pexels API (stock photos)
    │    └── API key: settings.pexels_api_key
    │
    └─── DALL-E (custom generation)
         └── API key: ai-settings.openai_api_key
```

**Zasada:** NIGDY placeholder URLs (example.com) - zawsze rzeczywiste obrazy!

### Plan implementacji (10 etapów)

| Etap | Nazwa | Czas | Priorytet |
|------|-------|------|-----------|
| 1 | AI Prompt Engineering | 2 dni | CRITICAL |
| 2 | Media Pipeline (Pexels + DALL-E) | 1.5 dnia | CRITICAL |
| 3 | Rendering Fixes (CSS) | 1.5 dnia | CRITICAL |
| 4 | SEO Engine | 1 dzień | HIGH |
| 5 | Web Designer Knowledge Base | 2 dni | HIGH |
| 6 | Menu & Mobile Fixes | 1 dzień | HIGH |
| 7 | Accessibility Fixes | 0.5 dnia | MEDIUM |
| 8 | Settings Unification | 1 dzień | MEDIUM |
| 9 | Testing & QA | 1 dzień | MEDIUM |
| 10 | Documentation | 0.5 dnia | LOW |
| **RAZEM** | | **~12 dni** | |

### Kluczowe pliki do modyfikacji

**AI Generation:**
- `plugin/includes/ai/class-jtb-ai-website.php` - główny generator
- `plugin/includes/ai/class-jtb-ai-prompts.php` - system prompts
- `plugin/includes/ai/class-jtb-ai-pexels.php` - Pexels integration
- `plugin/includes/ai/class-jtb-ai-images.php` - DALL-E integration

**Rendering:**
- `plugin/includes/class-jtb-renderer.php` - HTML generation
- `plugin/includes/class-jtb-css-generator.php` - CSS generation
- `plugin/assets/css/frontend.css` - frontend styles
- `plugin/assets/js/frontend.js` - hamburger menu, dropdowns

**API:**
- `plugin/api/render.php` - preview rendering (NAPRAWIONE)
- `plugin/api/ai/generate-website.php` - website generation

### Metryki sukcesu

| Metryka | Cel | Obecny |
|---------|-----|--------|
| Sekcje na stronę | 6-10 | 1-2 ❌ |
| Placeholder images | 0% | 100% ❌ |
| SEO completeness | 100% | 0% ❌ |
| Accessibility score | 90+ | ~60 ⚠️ |
| Mobile responsiveness | 100% | 85% ⚠️ |

### Quick Start dla implementacji

1. **Przeczytaj Master Plan:** `docs/AI_WEBSITE_BUILDER_MASTER_PLAN.md`
2. **Wybierz etap:** Zacznij od Etap 1 (AI Prompt Engineering)
3. **Deploy po każdym etapie:**
   ```bash
   wsl -u root bash -c 'cp -r "/mnt/c/Users/krala/Downloads/jessie theme builder/plugin/"* /var/www/cms/plugins/jessie-theme-builder/'
   ```
4. **Testuj:** Wygeneruj website i sprawdź wynik

### NAPRAWIONE PROBLEMY (04.02.2026)

1. **render.php** - obsługuje JSON body + POST form data
2. **Auto-ID generation** - brakujące ID w sekcjach AI
3. **Structure normalization** - orphan modules zawijane w section/row/column
4. **Preview rendering** - async/await dla fetch przed otwarciem okna

---

## ETAP 1: AI PROMPT ENGINEERING - UKOŃCZONY (04.02.2026)

### Zmiany w `class-jtb-ai-website.php`

**Całkowita przebudowa klasy z Web Designer Knowledge Base:**

#### 1. INDUSTRY_TEMPLATES (9 branż)
```php
private const INDUSTRY_TEMPLATES = [
    'technology' => [
        'home' => ['hero', 'trust_logos', 'features', 'how_it_works', 'benefits', 'testimonials', 'pricing', 'faq', 'cta'],
        'about' => ['hero_about', 'story', 'values', 'team', 'stats', 'timeline', 'cta'],
        // ...
        'colors' => ['primary' => '#3b82f6', 'secondary' => '#1e40af', 'accent' => '#10b981'],
    ],
    'healthcare' => [...],
    'legal' => [...],
    'restaurant' => [...],
    'real_estate' => [...],
    'fitness' => [...],
    'agency' => [...],
    'ecommerce' => [...],
    'education' => [...],
    'general' => [...],
];
```

#### 2. SECTION_BLUEPRINTS (20+ typów sekcji)
```php
private const SECTION_BLUEPRINTS = [
    'hero' => [
        'description' => 'Full-width hero with headline, subheadline, CTA button, and optional image',
        'layout' => '1_2,1_2',
        'modules' => ['heading h1', 'text subheadline', 'button primary'],
        'padding' => ['top' => 120, 'bottom' => 120],
    ],
    'features' => [...],
    'testimonials' => [...],
    'pricing' => [...],
    'team' => [...],
    'faq' => [...],
    'cta' => [...],
    // ... 20+ typów
];
```

#### 3. Nowe metody
- `buildAdvancedSystemPrompt()` - zaawansowany system prompt z Knowledge Base
- `buildPageRequirements()` - wymagania sekcji per strona
- `buildDetailedUserPrompt()` - szczegółowy user prompt
- `validateSectionCounts()` - walidacja min. 6 sekcji na stronę
- `countSections()` - liczenie sekcji w całym website
- `detectIndustry()` - automatyczne wykrywanie branży z promptu
- `addUniqueIds()` - dodawanie unikalnych ID do wszystkich elementów

#### 4. Kluczowe zmiany w promptach

**System Prompt zawiera:**
- Krytyczne reguły (6-10 sekcji OBOWIĄZKOWO)
- Kompletna struktura JSON z przykładami
- Wymagania per strona (home 8-10 sekcji, about 6-8, etc.)
- Referencja modułów z atrybutami
- Zasady designu (hierarchy, spacing, contrast)
- Lista modułów per kontekst (header, footer, hero, features, etc.)
- Wymagania jakości contentu

**User Prompt zawiera:**
- Szczegółowe wymagania per sekcję
- Dokładne liczby sekcji
- Wymagania header/footer
- Kolory do użycia
- Strukturę każdej strony

### Oczekiwane rezultaty po Etap 1

| Metryka | Przed | Po Etap 1 |
|---------|-------|-----------|
| Sekcje na stronę | 1-2 | 6-10 ✅ |
| Struktura | Błędna | Poprawna ✅ |
| Industry detection | Brak | Automatyczne ✅ |
| Knowledge Base | Brak | 9 branż + 20 sekcji ✅ |

### Weryfikacja

```bash
# Test generacji
# 1. Otwórz http://localhost/admin/jtb/website-builder
# 2. Wpisz prompt np. "Professional law firm website"
# 3. Sprawdź czy:
#    - Każda strona ma 6-10 sekcji
#    - Struktura JSON jest poprawna
#    - Content jest profesjonalny
```
