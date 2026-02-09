# AI WEBSITE BUILDER - MASTER PLAN

**Data utworzenia:** 04.02.2026
**Wersja:** 1.0
**Status:** DO IMPLEMENTACJI

---

## SPIS TREŚCI

1. [Podsumowanie Audytów](#1-podsumowanie-audytów)
2. [Architektura Docelowa](#2-architektura-docelowa)
3. [Etapy Implementacji](#3-etapy-implementacji)
4. [Szczegółowy Plan Etapów](#4-szczegółowy-plan-etapów)
5. [Wymagania Techniczne](#5-wymagania-techniczne)
6. [Metryki Sukcesu](#6-metryki-sukcesu)

---

## 1. PODSUMOWANIE AUDYTÓW

### 1.1 Stan Obecny

| Obszar | Ocena | Status |
|--------|-------|--------|
| AI Generation System | 82/100 | ⚠️ Wymaga pracy |
| CMS Integration | 75/100 | ⚠️ Fragmentaryczna |
| Module Rendering | 75/100 | ⚠️ Wymaga pracy |
| CSS Architecture | 80/100 | ✅ Dobra baza |
| API Endpoints | 95/100 | ✅ Dobrze |

### 1.2 Kluczowe Problemy

#### 🔴 KRYTYCZNE (blokujące)

| # | Problem | Lokalizacja | Wpływ |
|---|---------|-------------|-------|
| 1 | AI generuje ubogi content (1-2 sekcje zamiast 6-8) | JTB_AI_Website | Niskiej jakości strony |
| 2 | Obrazy = placeholder URLs (example.com) | AI prompts | Broken images |
| 3 | Menu hamburger nie działa (brak JS) | menu.php | Mobile unusable |
| 4 | Brak focus states na buttonach | button.php | Accessibility fail |
| 5 | Dropdown menu brak CSS | menu.php | Submenu invisible |
| 6 | Debug logs do /tmp | router.php, pexels.php | Security risk |

#### 🟡 WYSOKIE (ważne)

| # | Problem | Lokalizacja | Wpływ |
|---|---------|-------------|-------|
| 7 | Dual sourcing AI config (JSON + DB) | AI_Core, settings | Inconsistency |
| 8 | Brak SEO (meta, schema, sitemap) | AI generation | Poor SEO |
| 9 | Heading za mały (36px vs 48-56px) | AI prompts | Poor hierarchy |
| 10 | Padding za mały (60px vs 100-120px) | AI prompts | Cramped design |
| 11 | Brak prefers-reduced-motion | frontend.css | Accessibility |
| 12 | Visibility classes niejasne | frontend.css | May not work |

#### 🟢 ŚREDNIE (nice to have)

| # | Problem | Lokalizacja | Wpływ |
|---|---------|-------------|-------|
| 13 | Gradient text w heading | heading.php | Missing feature |
| 14 | Column selector zbyt szeroki (> *) | column.php | Potential bugs |
| 15 | Hardcoded "68" modules | admin.php | Maintenance |
| 16 | Brak rate limiting | AI_Core | Stability |

### 1.3 Co Działa Dobrze ✅

- Zero hardcoded dokumentacji w AI (używa JTB_AI_Schema)
- Unified API endpoint dla Page Builder i Template Builder
- Pexels integration (klasa gotowa, wymaga wywołania)
- CSS Variables architecture
- 79 modułów zarejestrowanych
- Responsive breakpoints (3 poziomy)
- 7 typów animacji
- Slug normalization (hyphens → underscores)

---

## 2. ARCHITEKTURA DOCELOWA

### 2.1 AI Generation Pipeline

```
┌─────────────────────────────────────────────────────────────────────┐
│                    AI WEBSITE BUILDER PIPELINE                       │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  USER INPUT                                                          │
│  ├── Business description (2-3 sentences)                           │
│  ├── Industry selector                                               │
│  ├── Style preference (modern/classic/bold/minimal)                 │
│  └── Pages selection                                                 │
│                                                                      │
│       ↓                                                              │
│                                                                      │
│  STAGE 1: ANALYSIS (Claude Opus 4.5)                                │
│  ├── Extract: industry, tone, target audience                       │
│  ├── Identify: key features, USPs, CTAs                             │
│  └── Output: structured brief JSON                                  │
│                                                                      │
│       ↓                                                              │
│                                                                      │
│  STAGE 2: SITEMAP                                                    │
│  ├── Define: page structure, navigation hierarchy                   │
│  ├── Plan: sections per page (6-10 each)                            │
│  └── Output: sitemap JSON with section types                        │
│                                                                      │
│       ↓                                                              │
│                                                                      │
│  STAGE 3: WIREFRAME                                                  │
│  ├── Layout: column structures (1, 1_2, 1_3, etc.)                  │
│  ├── Modules: which modules in each column                          │
│  └── Output: structure JSON without content                         │
│                                                                      │
│       ↓                                                              │
│                                                                      │
│  STAGE 4: CONTENT (SEO-optimized)                                   │
│  ├── Headlines: compelling, keyword-rich                            │
│  ├── Body copy: benefit-focused, scannable                          │
│  ├── CTAs: action-oriented                                          │
│  ├── Meta: title, description for each page                         │
│  └── Output: content JSON with all text                             │
│                                                                      │
│       ↓                                                              │
│                                                                      │
│  STAGE 5: STYLING                                                    │
│  ├── Colors: from industry palette + brand input                    │
│  ├── Typography: heading/body fonts, sizes                          │
│  ├── Spacing: section padding, gaps (generous)                      │
│  └── Output: theme_settings JSON                                    │
│                                                                      │
│       ↓                                                              │
│                                                                      │
│  STAGE 6: MEDIA                                                      │
│  ├── Pexels: search contextual images                               │
│  ├── DALL-E: generate custom if needed                              │
│  ├── Optimize: resize, compress, WebP                               │
│  └── Output: URLs replaced in content JSON                          │
│                                                                      │
│       ↓                                                              │
│                                                                      │
│  STAGE 7: VALIDATION                                                 │
│  ├── Structure: section/row/column/module hierarchy                 │
│  ├── Content: no empty fields, no placeholders                      │
│  ├── SEO: heading hierarchy (h1→h2→h3), alt texts                   │
│  ├── Accessibility: contrast ratios, touch targets                  │
│  └── Output: validated JSON or error report                         │
│                                                                      │
│       ↓                                                              │
│                                                                      │
│  FINAL OUTPUT                                                        │
│  └── Complete website JSON ready for rendering                      │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

### 2.2 Web Designer Knowledge Base

```
┌─────────────────────────────────────────────────────────────────────┐
│                 WEB DESIGNER KNOWLEDGE BASE                          │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  INDUSTRY TEMPLATES                                                  │
│  ├── SaaS Landing Page                                              │
│  │   └── Hero + Trust + Features + How It Works + Pricing           │
│  │       + Testimonials + FAQ + CTA                                 │
│  ├── Agency Portfolio                                                │
│  │   └── Hero + Services + Portfolio + Team + Testimonials          │
│  │       + Process + Contact                                        │
│  ├── Restaurant                                                      │
│  │   └── Hero + Menu Highlights + About + Gallery                   │
│  │       + Reservations + Location + Hours                          │
│  ├── Medical Practice                                                │
│  │   └── Hero + Services + Doctors + Insurance + Testimonials       │
│  │       + Appointment + Location                                   │
│  ├── E-commerce                                                      │
│  │   └── Hero + Featured Products + Categories + Bestsellers        │
│  │       + Reviews + Newsletter + Trust Badges                      │
│  └── ... (10+ more)                                                 │
│                                                                      │
│  SECTION PATTERNS                                                    │
│  ├── Hero Variants (12+)                                            │
│  │   ├── Centered text + image below                                │
│  │   ├── Split 50/50 text | image                                   │
│  │   ├── Full background image + overlay                            │
│  │   ├── Video background                                           │
│  │   ├── Animated gradient                                          │
│  │   └── ... more                                                   │
│  ├── Features Variants (8+)                                          │
│  │   ├── 3-column cards with icons                                  │
│  │   ├── Alternating image/text rows                                │
│  │   ├── Bento grid layout                                          │
│  │   └── ... more                                                   │
│  ├── Testimonials Variants (6+)                                      │
│  ├── Pricing Variants (4+)                                          │
│  ├── CTA Variants (5+)                                              │
│  └── Footer Variants (8+)                                           │
│                                                                      │
│  DESIGN RULES                                                        │
│  ├── Typography Scale (1.25 ratio)                                  │
│  │   └── h1: 48-64px, h2: 36-48px, h3: 24-32px, body: 16-18px      │
│  ├── Spacing Scale (8px base)                                       │
│  │   └── xs:8, sm:16, md:24, lg:32, xl:48, 2xl:64, 3xl:96          │
│  ├── Section Padding                                                 │
│  │   └── Desktop: 100-140px, Tablet: 80-100px, Mobile: 60-80px     │
│  ├── Color Contrast                                                  │
│  │   └── Text on bg: min 4.5:1, Large text: min 3:1                │
│  ├── Touch Targets                                                   │
│  │   └── Minimum 44x44px                                            │
│  └── Visual Hierarchy                                                │
│      └── One h1 per page, h2 for sections, h3 for subsections      │
│                                                                      │
│  INDUSTRY COLORS                                                     │
│  ├── Technology: Indigo (#4F46E5), Purple accents                   │
│  ├── Healthcare: Teal (#0D9488), Green trust                        │
│  ├── Finance: Navy (#1E3A5F), Gold accents                          │
│  ├── Creative: Bold colors, gradients                               │
│  ├── Legal: Navy, Burgundy, conservative                            │
│  ├── Restaurant: Warm oranges, rich browns                          │
│  └── ... more                                                        │
│                                                                      │
│  TONE OF VOICE                                                       │
│  ├── Professional: Clear, authoritative, trustworthy                │
│  ├── Friendly: Warm, approachable, conversational                   │
│  ├── Bold: Confident, provocative, memorable                        │
│  ├── Minimal: Clean, essential, sophisticated                       │
│  └── Playful: Fun, energetic, youthful                              │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

### 2.3 Rendering Pipeline

```
┌─────────────────────────────────────────────────────────────────────┐
│                    RENDERING PIPELINE                                │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  JSON INPUT                                                          │
│  └── {sections: [{type, attrs, children}...]}                       │
│                                                                      │
│       ↓                                                              │
│                                                                      │
│  NORMALIZATION (render.php)                                          │
│  ├── Add missing IDs (uniqid)                                       │
│  ├── Wrap orphan modules (section > row > column)                   │
│  ├── Normalize slugs (site-logo → site_logo)                        │
│  └── Merge with defaults (JTB_Global_Settings)                      │
│                                                                      │
│       ↓                                                              │
│                                                                      │
│  JTB_RENDERER                                                        │
│  ├── Iterate sections                                                │
│  ├── Call module->render() for each                                 │
│  ├── Collect CSS via module->generateCss()                          │
│  └── Extract fonts for Google Fonts                                 │
│                                                                      │
│       ↓                                                              │
│                                                                      │
│  CSS OUTPUT                                                          │
│  ├── CSS Variables (theme settings)                                 │
│  ├── Module-specific CSS (scoped by ID)                             │
│  ├── Responsive media queries                                        │
│  └── Animation keyframes                                             │
│                                                                      │
│       ↓                                                              │
│                                                                      │
│  HTML OUTPUT                                                         │
│  ├── Semantic structure (section, nav, article, footer)             │
│  ├── BEM-like classes (.jtb-section, .jtb-heading, etc.)           │
│  ├── Data attributes for JS (data-jtb-*, data-animation)           │
│  └── Accessibility attrs (alt, aria-label, role)                    │
│                                                                      │
│       ↓                                                              │
│                                                                      │
│  FINAL PAGE                                                          │
│  ├── <head>: meta, fonts, CSS                                       │
│  ├── <body>: rendered HTML                                          │
│  └── <script>: frontend.js (animations, menu, etc.)                │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 3. ETAPY IMPLEMENTACJI

### Przegląd Etapów

| Etap | Nazwa | Czas | Priorytet | Zależności |
|------|-------|------|-----------|------------|
| 1 | AI Prompt Engineering | 2 dni | 🔴 P0 | - |
| 2 | Media Pipeline (Pexels + DALL-E) | 1.5 dnia | 🔴 P0 | Etap 1 |
| 3 | Rendering Fixes | 1.5 dnia | 🔴 P0 | - |
| 4 | SEO Engine | 1 dzień | 🟡 P1 | Etap 1 |
| 5 | Web Designer Knowledge Base | 2 dni | 🟡 P1 | Etap 1 |
| 6 | Menu & Mobile Fixes | 1 dzień | 🔴 P0 | - |
| 7 | Accessibility Fixes | 0.5 dnia | 🟡 P1 | Etap 3, 6 |
| 8 | Settings Unification | 1 dzień | 🟡 P1 | - |
| 9 | Testing & QA | 1 dzień | 🟡 P1 | Wszystkie |
| 10 | Documentation | 0.5 dnia | 🟢 P2 | Wszystkie |

**Łączny czas: ~12 dni roboczych**

### Diagram Zależności

```
Etap 1 (AI Prompts)
    │
    ├──→ Etap 2 (Media Pipeline)
    │         │
    │         └──→ Etap 4 (SEO Engine)
    │                   │
    │                   └──→ Etap 5 (Knowledge Base)
    │
Etap 3 (Rendering) ──→ Etap 7 (Accessibility)
    │
Etap 6 (Menu/Mobile) ──→ Etap 7 (Accessibility)
    │
Etap 8 (Settings) ──────────────────────────────┐
                                                 │
                                                 ↓
                                          Etap 9 (Testing)
                                                 │
                                                 ↓
                                          Etap 10 (Docs)
```

---

## 4. SZCZEGÓŁOWY PLAN ETAPÓW

### ETAP 1: AI PROMPT ENGINEERING (2 dni)

**Cel:** AI generuje kompletne, profesjonalne strony z pełnym contentem.

#### Dzień 1: System Prompt Overhaul

**Plik:** `includes/ai/class-jtb-ai-website.php`

**Zadania:**

1. **Przepisać buildSystemPrompt()** - dodać Web Designer knowledge:
   ```php
   private static function buildSystemPrompt(array $options): string {
       $industry = $options['industry'] ?? 'general';
       $style = $options['style'] ?? 'modern';

       return "You are a PROFESSIONAL WEB DESIGNER with 15+ years experience.

   YOUR DESIGN PRINCIPLES:
   1. Visual Hierarchy - One clear focal point per section
   2. Generous Whitespace - Section padding: 100-140px desktop
   3. Typography Scale - h1: 48-64px, h2: 36-48px, h3: 24-32px, body: 16-18px
   4. Mobile-First - Every element must work on 320px screens
   5. Accessibility - 4.5:1 contrast, 44px touch targets

   INDUSTRY: {$industry}
   STYLE: {$style}

   PAGE STRUCTURE REQUIREMENTS:
   Every page MUST have 6-10 sections minimum:

   HOME PAGE (8-10 sections):
   1. Hero - Headline + subheadline + 2 CTAs + hero image
   2. Trust Bar - Client logos OR \"As featured in...\"
   3. Features - 3-6 cards with icons
   4. How It Works - 3-4 numbered steps
   5. Testimonials - 3 reviews with photos
   6. Stats - 3-4 impressive numbers
   7. FAQ - 5-6 questions
   8. Final CTA - Dark background, compelling offer

   ABOUT PAGE (6-8 sections):
   1. Hero - Mission statement
   2. Story - Company history/journey
   3. Team - 3-6 team members with photos
   4. Values - 3-4 core values
   5. Milestones - Timeline or stats
   6. CTA - Join us / Contact

   ...";
   }
   ```

2. **Dodać section templates** - predefiniowane struktury:
   ```php
   private static function getSectionTemplate(string $type): array {
       $templates = [
           'hero_split' => [
               'type' => 'section',
               'attrs' => [
                   'padding' => ['top' => 120, 'bottom' => 120],
                   'background_color' => '#f8fafc'
               ],
               'children' => [
                   [
                       'type' => 'row',
                       'attrs' => ['columns' => '1_2,1_2'],
                       'children' => [
                           ['type' => 'column', 'children' => [
                               ['type' => 'heading', 'attrs' => ['level' => 'h1']],
                               ['type' => 'text'],
                               ['type' => 'button', 'attrs' => ['button_style' => 'solid']],
                               ['type' => 'button', 'attrs' => ['button_style' => 'outline']]
                           ]],
                           ['type' => 'column', 'children' => [
                               ['type' => 'image']
                           ]]
                       ]
                   ]
               ]
           ],
           // ... więcej templates
       ];
       return $templates[$type] ?? [];
   }
   ```

3. **Dodać content guidelines** - szczegółowe instrukcje dla copywritingu:
   ```
   HEADLINE RULES:
   - Max 8 words for h1
   - Lead with benefit, not feature
   - Use power words: "Transform", "Unlock", "Discover"
   - Include number if relevant: "10x Faster", "3 Easy Steps"

   SUBHEADLINE RULES:
   - 15-25 words
   - Expand on the promise
   - Address pain point or desire

   CTA RULES:
   - Primary: Action verb + benefit ("Start Free Trial", "Get Started Free")
   - Secondary: Lower commitment ("Learn More", "See How It Works")
   ```

#### Dzień 2: Multi-Stage Pipeline

**Plik:** `includes/ai/class-jtb-ai-website.php`

**Zadania:**

1. **Implementować 7-stage pipeline:**
   ```php
   public static function generate(string $prompt, array $options = []): array {
       $stages = [
           'analysis' => ['temp' => 0.3, 'tokens' => 2000],
           'sitemap' => ['temp' => 0.3, 'tokens' => 3000],
           'wireframe' => ['temp' => 0.4, 'tokens' => 8000],
           'content' => ['temp' => 0.7, 'tokens' => 10000],
           'styling' => ['temp' => 0.5, 'tokens' => 3000],
           'media' => ['temp' => 0.3, 'tokens' => 2000],
           'validation' => ['temp' => 0.1, 'tokens' => 2000]
       ];

       $context = ['prompt' => $prompt, 'options' => $options];

       foreach ($stages as $stage => $config) {
           $result = self::runStage($stage, $context, $config);
           if (!$result['ok']) {
               return $result; // Early exit on error
           }
           $context[$stage] = $result['data'];
       }

       return ['ok' => true, 'website' => $context['validation']];
   }
   ```

2. **Dodać stage-specific prompts** dla każdego etapu

3. **Dodać progress callback** dla UI feedback

**Weryfikacja Etapu 1:**
- [ ] AI generuje 6-10 sekcji per strona
- [ ] Headlines są 48-64px
- [ ] Section padding 100-140px
- [ ] Content jest pełny, nie placeholder
- [ ] Dual CTAs w hero (primary + secondary)

---

### ETAP 2: MEDIA PIPELINE (1.5 dnia)

**Cel:** Wszystkie obrazy są prawdziwe (Pexels) lub wygenerowane (DALL-E).

#### Dzień 1: Pexels Integration

**Pliki:**
- `includes/ai/class-jtb-ai-pexels.php`
- `includes/ai/class-jtb-ai-website.php`

**Zadania:**

1. **Naprawić logging** (obecnie używa /tmp):
   ```php
   // Zmienić:
   file_put_contents('/tmp/jtb_pexels.log', ...);

   // Na:
   $logPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'jtb_pexels.log';
   @file_put_contents($logPath, $message, FILE_APPEND) ?: error_log($message);
   ```

2. **Dodać context-aware image search:**
   ```php
   public static function getImageForContext(string $context, array $options = []): ?string {
       $queryMap = [
           'hero' => '{industry} professional team office',
           'about' => '{industry} company culture workplace',
           'team_member' => 'professional headshot {gender} {role}',
           'testimonial' => 'professional portrait {gender}',
           'feature_icon' => '{feature} abstract minimal icon',
           'background' => '{industry} abstract pattern texture'
       ];

       $query = str_replace(
           ['{industry}', '{gender}', '{role}', '{feature}'],
           [$options['industry'] ?? '', $options['gender'] ?? '', $options['role'] ?? '', $options['feature'] ?? ''],
           $queryMap[$context] ?? $context
       );

       return self::searchAndDownload($query, $options);
   }
   ```

3. **Implementować image enrichment w post-processing:**
   ```php
   private static function enrichWithImages(array $website, array $context): array {
       // Przeszukaj wszystkie moduły
       array_walk_recursive($website, function(&$value, $key) use ($context) {
           // Znajdź image fields z placeholder URLs
           if (in_array($key, ['image', 'src', 'logo', 'portrait_url', 'background_image'])) {
               if (empty($value) || strpos($value, 'example.com') !== false) {
                   // Pobierz prawdziwy obraz z Pexels
                   $value = JTB_AI_Pexels::getImageForContext(
                       $this->detectImageContext($key),
                       $context
                   );
               }
           }
       });
       return $website;
   }
   ```

#### Dzień 1.5: DALL-E Fallback

**Plik:** `includes/ai/class-jtb-ai-images.php`

**Zadania:**

1. **Implementować DALL-E generation** jako fallback gdy Pexels nie znajdzie:
   ```php
   public static function generateImage(string $prompt, array $options = []): ?string {
       $apiKey = self::getOpenAIKey();
       if (!$apiKey) return null;

       $response = self::callDALLE([
           'model' => 'dall-e-3',
           'prompt' => $prompt,
           'size' => $options['size'] ?? '1024x1024',
           'quality' => $options['quality'] ?? 'standard',
           'n' => 1
       ]);

       if ($response['url']) {
           // Download and save to media library
           return self::saveToMediaLibrary($response['url'], $prompt);
       }

       return null;
   }
   ```

2. **Dodać logo generation:**
   ```php
   public static function generateLogo(string $businessName, string $industry): ?string {
       $prompt = "Minimal, modern logo mark for '{$businessName}', {$industry} industry.
                  Simple geometric shape, single color, professional, scalable.
                  Style: flat design, no text, abstract symbol.";

       return self::generateImage($prompt, ['size' => '1024x1024']);
   }
   ```

**Weryfikacja Etapu 2:**
- [ ] Żadne obrazy nie używają example.com
- [ ] Hero images są z Pexels (prawdziwe zdjęcia)
- [ ] Team member photos są profesjonalne headshots
- [ ] Logo jest generowane przez DALL-E jeśli nie podane
- [ ] Wszystkie obrazy mają alt text

---

### ETAP 3: RENDERING FIXES (1.5 dnia)

**Cel:** Perfekcyjny HTML/CSS output.

#### Dzień 1: CSS Fixes

**Pliki:**
- `assets/css/frontend.css`
- `modules/content/button.php`
- `modules/content/heading.php`

**Zadania:**

1. **Dodać focus states:**
   ```css
   /* button.php generateCss() */
   .jtb-button:focus-visible {
       outline: 2px solid var(--jtb-color-primary);
       outline-offset: 2px;
   }
   ```

2. **Dodać prefers-reduced-motion:**
   ```css
   /* frontend.css */
   @media (prefers-reduced-motion: reduce) {
       .jtb-animated,
       .jtb-section,
       .jtb-button,
       * {
           animation: none !important;
           transition: none !important;
       }
   }
   ```

3. **Naprawić visibility classes:**
   ```css
   /* frontend.css - upewnić się że istnieją */
   @media (max-width: 767px) {
       .jtb-hide-phone { display: none !important; }
   }
   @media (min-width: 768px) and (max-width: 980px) {
       .jtb-hide-tablet { display: none !important; }
   }
   @media (min-width: 981px) {
       .jtb-hide-desktop { display: none !important; }
   }
   ```

4. **Poprawić column selector:**
   ```css
   /* column.php - zmienić */
   /* Było: */
   .jtb-column > * { }

   /* Powinno być: */
   .jtb-column > .jtb-module { }
   ```

#### Dzień 1.5: Module Improvements

**Pliki:**
- `modules/content/heading.php`
- `modules/content/text.php`

**Zadania:**

1. **Heading - dodać gradient text support:**
   ```php
   // W getFields():
   'use_gradient' => [
       'type' => 'toggle',
       'label' => 'Use Gradient Text',
       'default' => false
   ],
   'gradient_from' => [
       'type' => 'color',
       'label' => 'Gradient From',
       'default' => '#4F46E5',
       'show_if' => ['use_gradient' => true]
   ],
   'gradient_to' => [
       'type' => 'color',
       'label' => 'Gradient To',
       'default' => '#7C3AED',
       'show_if' => ['use_gradient' => true]
   ]

   // W generateCss():
   if ($attrs['use_gradient'] ?? false) {
       $css .= "background: linear-gradient(135deg, {$attrs['gradient_from']}, {$attrs['gradient_to']});";
       $css .= "-webkit-background-clip: text;";
       $css .= "-webkit-text-fill-color: transparent;";
       $css .= "background-clip: text;";
   }
   ```

2. **Text - dodać typography enhancements:**
   ```php
   protected array $style_config = [
       'line_height' => ['property' => 'line-height', 'selector' => '.jtb-text-inner'],
       'letter_spacing' => ['property' => 'letter-spacing', 'selector' => '.jtb-text-inner', 'unit' => 'em'],
       'text_shadow' => ['property' => 'text-shadow', 'selector' => '.jtb-text-inner']
   ];
   ```

**Weryfikacja Etapu 3:**
- [ ] Button ma focus ring na Tab
- [ ] Animations disabled gdy prefers-reduced-motion
- [ ] Visibility classes działają na wszystkich breakpoints
- [ ] Heading może mieć gradient text
- [ ] Column nie łapie niezamierzonych elementów

---

### ETAP 4: SEO ENGINE (1 dzień)

**Cel:** Każda strona ma pełne SEO.

**Plik:** `includes/ai/class-jtb-ai-seo.php` (NOWY)

**Zadania:**

1. **Utworzyć klasę SEO:**
   ```php
   <?php
   namespace JessieThemeBuilder;

   class JTB_AI_SEO {

       public static function generateMeta(array $page, array $context): array {
           $title = self::generateTitle($page, $context);
           $description = self::generateDescription($page, $context);

           return [
               'title' => $title,
               'description' => $description,
               'og_title' => $title,
               'og_description' => $description,
               'og_image' => self::findHeroImage($page),
               'schema' => self::generateSchema($page, $context)
           ];
       }

       private static function generateTitle(array $page, array $context): string {
           // AI-generated, keyword-optimized title
           // Max 60 chars, includes brand name
       }

       private static function generateDescription(array $page, array $context): string {
           // AI-generated, compelling description
           // 150-160 chars, includes CTA
       }

       private static function generateSchema(array $page, array $context): array {
           // Schema.org markup based on page type
           $type = $context['page_type'] ?? 'WebPage';

           return [
               '@context' => 'https://schema.org',
               '@type' => $type,
               'name' => $page['title'],
               'description' => $page['meta']['description'] ?? '',
               // ... more schema
           ];
       }
   }
   ```

2. **Integrować z AI Website generation:**
   ```php
   // W JTB_AI_Website::generate()
   foreach ($website['pages'] as $slug => &$page) {
       $page['meta'] = JTB_AI_SEO::generateMeta($page, [
           'industry' => $options['industry'],
           'business_name' => $context['analysis']['business_name']
       ]);
   }
   ```

3. **Dodać heading hierarchy validation:**
   ```php
   public static function validateHeadingHierarchy(array $sections): array {
       $issues = [];
       $h1Count = 0;
       $lastLevel = 0;

       self::walkModules($sections, function($module) use (&$issues, &$h1Count, &$lastLevel) {
           if ($module['type'] === 'heading') {
               $level = (int) substr($module['attrs']['level'] ?? 'h2', 1);

               if ($level === 1) {
                   $h1Count++;
                   if ($h1Count > 1) {
                       $issues[] = "Multiple h1 tags found (should be only one)";
                   }
               }

               if ($level > $lastLevel + 1 && $lastLevel > 0) {
                   $issues[] = "Heading hierarchy skip: h{$lastLevel} → h{$level}";
               }

               $lastLevel = $level;
           }
       });

       return $issues;
   }
   ```

**Weryfikacja Etapu 4:**
- [ ] Każda strona ma meta title (max 60 chars)
- [ ] Każda strona ma meta description (150-160 chars)
- [ ] Schema.org markup jest generowany
- [ ] Tylko jeden h1 per strona
- [ ] Heading hierarchy jest poprawna (h1→h2→h3)

---

### ETAP 5: WEB DESIGNER KNOWLEDGE BASE (2 dni)

**Cel:** AI ma wbudowaną wiedzę profesjonalnego web designera.

**Plik:** `includes/ai/class-jtb-ai-knowledge.php`

**Zadania:**

1. **Rozbudować istniejącą klasę o industry templates:**
   ```php
   public static function getIndustryTemplate(string $industry): array {
       $templates = [
           'saas' => [
               'home' => ['hero_split', 'trust_logos', 'features_grid', 'how_it_works',
                          'testimonials_carousel', 'pricing_table', 'faq_accordion', 'cta_dark'],
               'about' => ['hero_centered', 'story_timeline', 'team_grid', 'values_cards', 'cta_simple'],
               'pricing' => ['hero_simple', 'pricing_comparison', 'features_checklist', 'faq_accordion', 'cta_dark'],
               'contact' => ['hero_simple', 'contact_split', 'map_embed', 'cta_simple']
           ],
           'agency' => [
               'home' => ['hero_creative', 'services_showcase', 'portfolio_grid', 'process_steps',
                          'testimonials_featured', 'clients_logos', 'cta_bold'],
               // ... more pages
           ],
           // ... more industries
       ];

       return $templates[$industry] ?? $templates['general'];
   }
   ```

2. **Dodać section pattern library:**
   ```php
   public static function getSectionPattern(string $pattern): array {
       // Zwraca kompletną strukturę sekcji z placeholderami
   }
   ```

3. **Dodać design tokens:**
   ```php
   public static function getDesignTokens(string $style): array {
       return [
           'modern' => [
               'border_radius' => '12px',
               'shadow' => '0 4px 6px -1px rgba(0,0,0,0.1)',
               'transition' => 'all 0.2s ease',
               'font_heading' => 'Inter',
               'font_body' => 'Inter'
           ],
           'classic' => [
               'border_radius' => '4px',
               'shadow' => '0 1px 3px rgba(0,0,0,0.12)',
               'transition' => 'all 0.3s ease',
               'font_heading' => 'Playfair Display',
               'font_body' => 'Source Sans Pro'
           ],
           // ... more styles
       ];
   }
   ```

**Weryfikacja Etapu 5:**
- [ ] 10+ industry templates dostępnych
- [ ] 30+ section patterns w library
- [ ] Design tokens dla każdego stylu
- [ ] AI używa templates zamiast generować od zera

---

### ETAP 6: MENU & MOBILE FIXES (1 dzień)

**Cel:** Menu działa perfekcyjnie na wszystkich urządzeniach.

**Pliki:**
- `modules/theme/menu.php`
- `assets/js/frontend.js`
- `assets/css/frontend.css`

**Zadania:**

1. **Dodać hamburger JavaScript handler:**
   ```javascript
   // frontend.js
   document.addEventListener('DOMContentLoaded', function() {
       // Mobile menu toggle
       document.querySelectorAll('.jtb-hamburger').forEach(hamburger => {
           hamburger.addEventListener('click', function() {
               const menu = this.closest('.jtb-menu');
               const nav = menu.querySelector('.jtb-nav');
               const isOpen = nav.classList.toggle('jtb-nav-open');

               // Toggle hamburger icon
               this.classList.toggle('is-active');

               // Accessibility
               this.setAttribute('aria-expanded', isOpen);

               // Prevent body scroll when menu open
               document.body.classList.toggle('jtb-menu-open', isOpen);
           });
       });

       // Close menu on link click
       document.querySelectorAll('.jtb-nav a').forEach(link => {
           link.addEventListener('click', () => {
               document.querySelector('.jtb-nav-open')?.classList.remove('jtb-nav-open');
               document.querySelector('.jtb-hamburger.is-active')?.classList.remove('is-active');
               document.body.classList.remove('jtb-menu-open');
           });
       });
   });
   ```

2. **Dodać dropdown CSS:**
   ```css
   /* frontend.css */
   .jtb-nav-item {
       position: relative;
   }

   .jtb-nav-dropdown {
       position: absolute;
       top: 100%;
       left: 0;
       min-width: 200px;
       background: white;
       border-radius: 8px;
       box-shadow: 0 10px 40px rgba(0,0,0,0.1);
       padding: 8px 0;
       opacity: 0;
       visibility: hidden;
       transform: translateY(10px);
       transition: all 0.2s ease;
   }

   .jtb-nav-item:hover .jtb-nav-dropdown {
       opacity: 1;
       visibility: visible;
       transform: translateY(0);
   }

   .jtb-nav-dropdown a {
       display: block;
       padding: 10px 20px;
       color: #333;
   }

   .jtb-nav-dropdown a:hover {
       background: #f8fafc;
   }
   ```

3. **Dodać mobile menu CSS:**
   ```css
   @media (max-width: 767px) {
       .jtb-nav {
           position: fixed;
           top: 0;
           right: -100%;
           width: 80%;
           max-width: 320px;
           height: 100vh;
           background: white;
           box-shadow: -10px 0 40px rgba(0,0,0,0.1);
           padding: 80px 24px 24px;
           transition: right 0.3s ease;
           z-index: 1000;
       }

       .jtb-nav.jtb-nav-open {
           right: 0;
       }

       .jtb-nav-list {
           flex-direction: column;
           gap: 0;
       }

       .jtb-nav-link {
           padding: 16px 0;
           border-bottom: 1px solid #eee;
       }

       .jtb-hamburger {
           display: flex !important;
       }

       body.jtb-menu-open {
           overflow: hidden;
       }
   }
   ```

**Weryfikacja Etapu 6:**
- [ ] Hamburger click otwiera mobile menu
- [ ] Mobile menu slide-in animation
- [ ] Dropdown menu na hover (desktop)
- [ ] Menu zamyka się po kliknięciu linka
- [ ] Body scroll zablokowany gdy menu otwarte

---

### ETAP 7: ACCESSIBILITY FIXES (0.5 dnia)

**Cel:** WCAG 2.1 Level AA compliance.

**Zadania:**

1. **Dodać skip link:**
   ```html
   <!-- W header template -->
   <a href="#main-content" class="jtb-skip-link">Skip to main content</a>
   ```
   ```css
   .jtb-skip-link {
       position: absolute;
       top: -40px;
       left: 0;
       background: var(--jtb-color-primary);
       color: white;
       padding: 8px 16px;
       z-index: 10000;
   }
   .jtb-skip-link:focus {
       top: 0;
   }
   ```

2. **Dodać ARIA labels gdzie brakuje:**
   ```php
   // W menu.php
   <button class="jtb-hamburger" aria-label="Toggle menu" aria-expanded="false" aria-controls="mobile-menu">
   ```

3. **Sprawdzić i naprawić contrast ratios** - audit z axe-core

**Weryfikacja Etapu 7:**
- [ ] Skip link działa
- [ ] Wszystkie interactive elements mają focus visible
- [ ] ARIA labels na hamburger, search, cart icons
- [ ] Color contrast min 4.5:1 dla tekstu
- [ ] axe-core audit = 0 critical issues

---

### ETAP 8: SETTINGS UNIFICATION (1 dzień)

**Cel:** Jeden system konfiguracji AI.

**Zadania:**

1. **Przenieść config do database:**
   ```sql
   -- settings table
   INSERT INTO settings (`key`, `value`) VALUES
   ('ai_provider', 'anthropic'),
   ('ai_model', 'claude-opus-4-5-20251101'),
   ('anthropic_api_key', '...encrypted...'),
   ('openai_api_key', '...encrypted...'),
   ('pexels_api_key', '...'),
   ('ai_temperature', '0.7'),
   ('ai_max_tokens', '16000');
   ```

2. **Utworzyć unified settings class:**
   ```php
   class JTB_AI_Settings {
       private static ?array $cache = null;

       public static function get(string $key, $default = null) {
           if (self::$cache === null) {
               self::loadFromDatabase();
           }
           return self::$cache[$key] ?? $default;
       }

       public static function getProvider(): string {
           return self::get('ai_provider', 'anthropic');
       }

       public static function getApiKey(string $provider): ?string {
           $encrypted = self::get($provider . '_api_key');
           return $encrypted ? self::decrypt($encrypted) : null;
       }
   }
   ```

3. **Usunąć /config/ai_settings.json** i migrować do DB

4. **Usunąć debug logging** z router.php

**Weryfikacja Etapu 8:**
- [ ] Wszystkie API keys w database (encrypted)
- [ ] Brak /config/ai_settings.json
- [ ] Brak debug logs do /tmp
- [ ] JTB_AI_Settings jest jedynym źródłem config

---

### ETAP 9: TESTING & QA (1 dzień)

**Cel:** Wszystko działa bez błędów.

**Zadania:**

1. **Test Suite dla AI Generation:**
   ```php
   // tests/AIWebsiteTest.php
   public function testGeneratesMinimumSections() {
       $result = JTB_AI_Website::generate('Professional agency website', [
           'industry' => 'agency',
           'style' => 'modern'
       ]);

       $this->assertTrue($result['ok']);
       $this->assertGreaterThanOrEqual(6, count($result['website']['pages']['home']['sections']));
   }

   public function testNoPlaceholderImages() {
       // ... assert no example.com URLs
   }

   public function testSEOMetaPresent() {
       // ... assert meta title, description exist
   }
   ```

2. **Visual Regression Tests:**
   - Screenshot comparison przed/po zmianach

3. **Mobile Testing:**
   - Test na prawdziwych urządzeniach (iPhone, Android)

4. **Accessibility Audit:**
   - axe-core automated testing
   - Manual keyboard navigation test

**Checklist QA:**
- [ ] AI generates 6-10 sections per page
- [ ] All images are real (Pexels/DALL-E)
- [ ] Menu works on mobile
- [ ] Focus states visible
- [ ] SEO meta present
- [ ] No console errors
- [ ] No PHP errors in logs
- [ ] Performance: < 3s generation time

---

### ETAP 10: DOCUMENTATION (0.5 dnia)

**Cel:** Kompletna dokumentacja dla developerów i użytkowników.

**Zadania:**

1. **Update CLAUDE.md** z nowymi sekcjami
2. **API Documentation** - wszystkie endpointy
3. **User Guide** - jak używać AI Website Builder
4. **Troubleshooting Guide** - common issues

---

## 5. WYMAGANIA TECHNICZNE

### API Keys Required

| Service | Key Location | Purpose |
|---------|--------------|---------|
| Anthropic | settings.anthropic_api_key | Claude Opus 4.5 for generation |
| OpenAI | settings.openai_api_key | DALL-E for image generation |
| Pexels | settings.pexels_api_key | Stock photos |

### Database Schema Updates

```sql
-- New columns for settings encryption
ALTER TABLE settings ADD COLUMN encrypted TINYINT(1) DEFAULT 0;

-- New table for AI generation history (optional)
CREATE TABLE jtb_ai_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    prompt TEXT,
    result JSON,
    tokens_used INT,
    generation_time_ms INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### PHP Requirements

- PHP 8.1+
- curl extension (for API calls)
- json extension
- openssl extension (for key encryption)

---

## 6. METRYKI SUKCESU

### Jakość Generacji

| Metryka | Cel | Obecny |
|---------|-----|--------|
| Sekcje per strona | 6-10 | 1-2 ❌ |
| Placeholder images | 0% | 100% ❌ |
| Pełny content | 100% | 50% ❌ |
| SEO meta | 100% | 0% ❌ |
| Generation time | < 30s | ~60s ⚠️ |

### Jakość Renderingu

| Metryka | Cel | Obecny |
|---------|-----|--------|
| Mobile menu works | ✅ | ❌ |
| Focus states | ✅ | ❌ |
| WCAG 2.1 AA | ✅ | ⚠️ |
| Console errors | 0 | ~2 ⚠️ |

### Po Implementacji (Oczekiwane)

| Metryka | Przed | Po |
|---------|-------|-----|
| Sekcje per strona | 1-2 | 6-10 |
| Placeholder images | 100% | 0% |
| User satisfaction | 3/10 | 9/10 |
| Time to usable site | Manual fix required | Ready to use |

---

## APPENDIX A: QUICK START DLA NOWEJ SESJI

```bash
# 1. Przeczytaj CLAUDE.md
# 2. Przeczytaj ten plan (AI_WEBSITE_BUILDER_MASTER_PLAN.md)
# 3. Sprawdź który etap jest w trakcie
# 4. Deploy i test:

wsl -u root bash -c 'cp -r "/mnt/c/Users/krala/Downloads/jessie theme builder/plugin/"* /var/www/cms/plugins/jessie-theme-builder/'
wsl -u root bash -c 'php -l /var/www/cms/plugins/jessie-theme-builder/includes/ai/class-jtb-ai-website.php'
```

---

**KONIEC DOKUMENTU**

*Ostatnia aktualizacja: 04.02.2026*
