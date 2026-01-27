# AI DESIGNER 4.0 - ARCHITECTURE PLAN (UPDATED)

## Data: 2024-12-30 (Updated after implementation)
## Autor: Claude (dla Piotr/Jessie AI-CMS)
## Status: ✅ ZAIMPLEMENTOWANO

---

## 1. ARCHITEKTURA

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                             │
│  USER INPUT                                                                 │
│  {                                                                          │
│    "brief": "Włoska restauracja w centrum Warszawy...",                    │
│    "business_name": "Ristorante Milano",                                   │
│    "industry": "restaurant",         // 1 z 30 branż                       │
│    "design_style": "elegant",        // 1 z 10 stylów (lub "auto")         │
│    "pages": ["homepage", "about", "menu", "gallery", "contact"]            │
│  }                                                                          │
│                                                                             │
│         ↓                                                                   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                      AI DESIGNER (Agent)                            │   │
│  │                                                                     │   │
│  │  STEP 1: ANALIZA (Analyzer.php)                                    │   │
│  │  - Zrozumienie branży, stylu, potrzeb                              │   │
│  │  - Przyjęcie listy stron OD UŻYTKOWNIKA (1-N pages)                │   │
│  │  - Auto-wybór stylu na podstawie branży (jeśli "auto")             │   │
│  │                                                                     │   │
│  │  STEP 2: DESIGN SYSTEM (DesignSystem.php)                          │   │
│  │  - Paleta kolorów (primary, secondary, accent, neutrals)           │   │
│  │  - Typography (headings, body, accent fonts)                       │   │
│  │  - Spacing scale, border-radius, shadows                           │   │
│  │  - Wspólne komponenty (buttons, cards, etc.)                       │   │
│  │                                                                     │   │
│  │  STEP 3: BUILD PAGES (PageBuilder.php)                             │   │
│  │  - Buduje każdą stronę z listy użytkownika                         │   │
│  │  - 40+ typów sekcji (hero, features, testimonials, etc.)           │   │
│  │  - Zachowuje spójność z design system                              │   │
│  │  - Każda strona jako osobny plik PHP                               │   │
│  │                                                                     │   │
│  │  STEP 4: HEADER + FOOTER (HeaderFooterBuilder.php)                 │   │
│  │  - Spójne z design system                                          │   │
│  │  - Responsywne (mobile menu)                                       │   │
│  │  - Nawigacja zawiera wszystkie strony użytkownika                  │   │
│  │                                                                     │   │
│  │  STEP 5: TB EXPORT (ThemeExporter.php)                             │   │
│  │  - Konwersja HTML/PHP do TB JSON przez HTML Converter              │   │
│  │  - Zapis do tb-export/ directory                                   │   │
│  │                                                                     │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│         ↓                                                                   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                    OUTPUT: COMPLETE THEME                           │   │
│  │                                                                     │   │
│  │  /themes/theme-slug/                                                │   │
│  │  ├── theme.json          (metadata, design system)                 │   │
│  │  ├── header.php          (oryginalny HTML/PHP)                     │   │
│  │  ├── footer.php          (oryginalny HTML/PHP)                     │   │
│  │  ├── pages/                                                        │   │
│  │  │   ├── homepage.php                                              │   │
│  │  │   ├── about.php                                                 │   │
│  │  │   └── ...                                                       │   │
│  │  ├── assets/                                                       │   │
│  │  │   └── css/style.css   (design system jako CSS)                  │   │
│  │  └── tb-export/          (TB JSON dla edycji)                      │   │
│  │      ├── theme-export.json                                         │   │
│  │      ├── header.json                                               │   │
│  │      ├── footer.json                                               │   │
│  │      └── pages/*.json                                              │   │
│  │                                                                     │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. DESIGN STYLES (10)

Z istniejącego UI AI Theme Builder:

| Key | Name | Charakterystyka |
|-----|------|-----------------|
| `modern` | Modern & Clean | Contemporary, clean lines, whitespace, geometric, professional |
| `corporate` | Corporate | Professional, trustworthy, structured, conservative, blues |
| `creative` | Creative & Bold | Artistic, innovative, expressive, unique, gradients |
| `minimal` | Minimal | Ultra-clean, maximum whitespace, essential only, monochromatic |
| `elegant` | Elegant | Refined, sophisticated, warm, serif fonts, gold accents |
| `vintage` | Vintage & Classic | Retro-inspired, nostalgic, timeless charm, sepia tones |
| `luxury` | Luxury | High-end, exclusive, premium, black/gold, dramatic |
| `bold` | Bold & Dynamic | High contrast, energetic, impactful, oversized typography |
| `organic` | Organic & Natural | Natural, friendly, warm, earth tones, rounded shapes |
| `industrial` | Industrial | Raw, utilitarian, authentic, grays, mechanical feel |

---

## 3. INDUSTRIES (30)

Z istniejącego UI AI Theme Builder:

| Category | Industries |
|----------|------------|
| **Business** | business, professional_services, finance, realestate |
| **Food & Drink** | restaurant, cafe, bar, catering, foodtruck |
| **Health & Beauty** | healthcare, spa, salon, barber, fitness, yoga |
| **Hospitality** | hotel |
| **Technology** | technology, ecommerce |
| **Creative** | photography, wedding, music, tattoo, art |
| **Other** | education, nonprofit, automotive, construction |
| **Content** | blog, portfolio, landing |

---

## 4. PAGE TYPES (10)

Z istniejącego UI AI Theme Builder:

| Key | Name | Sekcje |
|-----|------|--------|
| `homepage` | Homepage | hero, features, about, services, testimonials, cta |
| `about` | About Us | hero, story, values, team, timeline |
| `services` | Services | hero, services_grid, process, pricing_preview, cta |
| `contact` | Contact | hero, contact_form, map, info, faq |
| `blog` | Blog | hero, posts_grid, categories, newsletter |
| `portfolio` | Portfolio | hero, portfolio_grid, categories, cta |
| `pricing` | Pricing | hero, pricing_tables, comparison, faq, cta |
| `team` | Team | hero, team_grid, values, cta |
| `faq` | FAQ | hero, faq_accordion, contact_cta |
| `testimonials` | Testimonials | hero, testimonials_grid, stats, cta |

---

## 5. SECTION TYPES (40+)

PageBuilder.php obsługuje następujące typy sekcji:

**Hero & Headers:**
- hero, hero_split, hero_video, hero_slider

**Content:**
- about, story, history, values, mission

**Features & Services:**
- features, features_grid, services, services_grid, process

**Social Proof:**
- testimonials, testimonials_slider, reviews, clients, partners

**Pricing:**
- pricing, pricing_tables, pricing_comparison

**Team:**
- team, team_grid, team_slider

**Portfolio & Gallery:**
- portfolio, portfolio_grid, gallery, gallery_masonry

**Blog:**
- blog, blog_grid, posts, latest_posts

**Contact:**
- contact, contact_form, contact_info, map

**CTA:**
- cta, cta_banner, cta_split, newsletter

**Other:**
- stats, counters, faq, faq_accordion, timeline

---

## 6. PERSONALITIES SYSTEM

10 klas osobowości w `/core/ai-designer/personalities/`:

```php
// Użycie:
$personality = PersonalityFactory::create('modern');
$context = $personality->getPromptContext();

// Dostępne metody:
$personality->getName();           // "Modern & Clean"
$personality->getKey();            // "modern"
$personality->getTraits();         // Design traits
$personality->getInfluences();     // Design influences
$personality->getColorGuidance();  // Color guidance for AI
$personality->getTypographyGuidance();
$personality->getLayoutGuidance();
$personality->getImageryGuidance();
$personality->toArray();           // All data as array
```

---

## 7. STRUKTURA PLIKÓW

```
/var/www/html/cms/
├── core/ai-designer/                    [296 KB total]
│   ├── Designer.php         (622 lines) - Main orchestrator, 5-step workflow
│   ├── Theme.php            (203 lines) - Theme data object
│   ├── Analyzer.php         (659 lines) - Brief analysis, style selection
│   ├── DesignSystem.php     (946 lines) - Design system generation
│   ├── PageBuilder.php      (1377 lines) - Page HTML builder (40+ sections)
│   ├── HeaderFooterBuilder.php (686 lines) - Header/footer generation
│   ├── ThemeExporter.php    (479 lines) - TB JSON export
│   ├── ImageFetcher.php     (468 lines) - Pexels/Unsplash integration
│   ├── index.php            (13 lines) - Security
│   └── personalities/
│       ├── PersonalityInterface.php (27 lines)
│       ├── AbstractPersonality.php  (72 lines)
│       ├── AllPersonalities.php     (237 lines) - 10 personality classes
│       └── index.php                (6 lines)
│
├── app/controllers/admin/
│   └── aidesignercontroller.php (532 lines) - MVC Controller
│
├── app/views/admin/ai-designer/
│   ├── index.php            (843 lines) - 4-step wizard UI
│   └── preview.php          (415 lines) - Theme preview
│
└── admin/
    └── ai-designer.php      (89 lines) - Entry point

TOTAL: 17 files, ~7,700 lines
```

---

## 8. API ENDPOINTS

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/admin/ai-designer.php` | GET | Wizard UI |
| `/admin/ai-designer.php?action=generate` | POST | Generate theme |
| `/admin/ai-designer.php?action=preview&slug=xxx` | GET | Preview theme |
| `/admin/ai-designer.php?action=list` | GET | List generated themes |
| `/admin/ai-designer.php?action=get-theme&slug=xxx` | GET | Get theme details |
| `/admin/ai-designer.php?action=deploy` | POST | Deploy to Theme Builder |
| `/admin/ai-designer.php?action=delete` | POST | Delete theme |

---

## 9. ETAPY IMPLEMENTACJI

| Etap | Opis | Status |
|------|------|--------|
| **1** | Designer.php + Theme.php + Analyzer.php | ✅ DONE |
| **2** | DesignSystem.php (10 stylów, 30 branż) | ✅ DONE |
| **3** | PageBuilder.php (40+ section types) | ✅ DONE |
| **4** | HeaderFooterBuilder.php | ✅ DONE |
| **5** | ThemeExporter.php (TB JSON export) | ✅ DONE |
| **6** | Personalities (10 classes + factory) | ✅ DONE |
| **7** | ImageFetcher.php (Pexels/Unsplash) | ✅ DONE |
| **8** | Controller Integration | ✅ DONE |
| **9** | UI Views (wizard + preview) | ✅ DONE |
| **10** | Testing & Verification | ✅ DONE |

---

## 10. WYMAGANIA

1. **AI Provider** skonfigurowany w `/config/ai_settings.json`
2. **Database tables:** `tb_layout_library`, `tb_site_templates`
3. **Write permissions** na `/themes/` directory
4. **(Optional)** Pexels/Unsplash API keys dla auto-images

---

## 11. UŻYCIE

```php
// 1. Inicjalizuj Designer
$designer = new \Core\AiDesigner\Designer($aiSettings);

// 2. Generuj theme
$theme = $designer->create([
    'brief' => 'Włoska restauracja w centrum Warszawy...',
    'business_name' => 'Ristorante Milano',
    'industry' => 'restaurant',
    'design_style' => 'elegant',  // lub 'auto'
    'pages' => ['homepage', 'about', 'menu', 'gallery', 'contact']
]);

// 3. Wynik
$theme->getId();        // Unique ID
$theme->getSlug();      // URL-friendly slug
$theme->getName();      // Display name
$theme->getPath();      // /themes/ristorante-milano-xxx/
$theme->getPages();     // Array of page names
$theme->getTbExport();  // TB JSON data
```

---

## 12. SUCCESS CRITERIA

1. ✅ Użytkownik podaje brief i wybiera strony (1-N)
2. ✅ Użytkownik wybiera styl (10 opcji) lub "auto"
3. ✅ AI Designer analizuje i generuje design system
4. ✅ Buduje każdą stronę z zachowaniem spójności
5. ✅ Theme zapisana jako HTML/PHP (oryginał)
6. ✅ Theme skonwertowana do TB JSON (edycja)
7. ✅ Obrazy mogą być pobrane z Pexels/Unsplash
8. ✅ Użytkownik może edytować w TB 3.0
9. ✅ 10 różnych stylów = różne wyniki
10. ✅ Theme może być bazą dla innych projektów

---

## CHANGELOG

- **2024-12-30 v1.0** - Oryginalny plan (6 personalities - NIEPOPRAWNY)
- **2024-12-30 v2.0** - Zaktualizowany po korekcie (10 stylów z UI, 30 branż, 10 page types)
- **2024-12-30 v2.1** - Plan zaktualizowany po pełnej implementacji

---

## END OF DOCUMENT
