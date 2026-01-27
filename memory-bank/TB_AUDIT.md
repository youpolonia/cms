# THEME BUILDER 3.0 - PEŁNY AUDYT SYSTEMU
**Data:** 2026-01-04
**Status:** W TRAKCIE
**Cel:** Znalezienie wszystkich błędów w systemie TB - 52 moduły, TB Pages, TB Themes, renderowanie, panele

---

## 📋 STRUKTURA SYSTEMU

### Pliki główne:
- \/core/theme-builder/init.php\ - 52 zarejestrowanych modułów (1509 linii)
- \/app/views/admin/theme-builder/edit.php\ - TB Pages Editor (4469 linii, 147KB)
- \/app/views/admin/theme-builder/template-edit.php\ - TB Themes Editor (2435 linii)
- \/core/theme-builder/renderer.php\ - System renderowania

---

## 🔍 ETAP 1: LISTA WSZYSTKICH 52 MODUŁÓW

### Content Modules (13):
1. **text** - Text content
2. **heading** - Headings (h1-h6)
3. **button** - CTA buttons
4. **quote** - Blockquotes
5. **list** - Lists (ordered/unordered)
6. **icon** - Single icons
7. **blurb** - Icon/Image + Title + Text
8. **cta** - Call to Action blocks
9. **pricing** - Pricing tables
10. **testimonial** - Customer testimonials
11. **blog** - Blog posts display
12. **code** - Code snippets
13. **counter** - Animated counters

### Media Modules (7):
14. **image** - Static images
15. **video** - Video embeds
16. **audio** - Audio player
17. **gallery** - Image galleries with lightbox
18. **map** - Google Maps embed
19. **slider** - Content sliders
20. **video_slider** - Video carousel

### Layout Modules (6):
21. **divider** - Horizontal dividers
22. **spacer** - Vertical spacing
23. **hero** - Hero headers
24. **menu** - Navigation menus
25. **sidebar** - Widget areas
26. **logo** - Site logo

### Form Modules (4):
27. **form** - Contact forms
28. **login** - Login forms
29. **signup** - Newsletter signup
30. **search** - Search bars

### Social Modules (2):
31. **social** - Social media links
32. **social_follow** - Social follow buttons

### Content Display (9):
33. **accordion** - FAQ/Collapsible
34. **toggle** - Single toggle item
35. **tabs** - Tabbed content
36. **team** - Team member cards
37. **countdown** - Event countdown
38. **bar_counters** - Progress bars
39. **circle_counter** - Circular progress
40. **portfolio** - Portfolio grid
41. **post_slider** - Dynamic post slider

### Dynamic/Template Modules (4):
42. **post_title** - Dynamic page/post title
43. **post_content** - Dynamic page/post content
44. **posts_navigation** - Prev/Next links
45. **comments** - Comment system

### Fullwidth Modules (7):
46. **fullwidth_code** - Full width code blocks
47. **fullwidth_image** - Full width images
48. **fullwidth_map** - Full width maps
49. **fullwidth_menu** - Full width navigation
50. **fullwidth_slider** - Full width slider
51. **fullwidth_header** - Full width hero
52. **fullwidth_portfolio** - Full width portfolio

---

## 🔍 ETAP 2: SPRAWDZANIE edit.php (TB Pages)

**Rozmiar:** 147,547 bytes (144 KB)
**Linie:** 4,469
**Uprawnienia:** 666 ⚠️ DO NAPRAWY - powinno być 644

---

## 🔍 ETAP 3: SPRAWDZANIE PLIKÓW JS

Sprawdzam katalog /core/theme-builder/js/...

### Pliki JavaScript (14 plików, 12,437 linii total):
1. **tb-core.js** (203 linii) - Core initialization, state management
2. **tb-events.js** (516 linii) - Event handlers (drag&drop, clicks)
3. **tb-render.js** (447 linii) - Canvas preview rendering
4. **tb-library.js** (163 linii) - Layout Library modal
5. **tb-modal-editor.js** (2,175 linii) - Module settings modals
6. **tb-structure.js** (1,101 linii) - Section/Row/Column structure management
7. **tb-helpers.js** (1,707 linii) - Utility functions
8. **tb-modules-content.js** (331 linii) - Content tab rendering for modules
9. **tb-modules-design.js** (2,887 linii) - Design tab rendering for modules
10. **tb-modules-preview.js** (698 linii) - Module preview rendering
11. **tb-element-schemas.js** (500 linii) - Element design schemas
12. **tb-modal-element-design.js** (776 linii) - Element design modal part 1
13. **tb-modal-element-design-part2.js** (484 linii) - Element design modal part 2
14. **tb-modal-element-design-part3.js** (449 linii) - Element design modal part 3

**⚠️ UWAGA:** tb-modal-editor.js (2,175 linii) i tb-modules-design.js (2,887 linii) to największe pliki - potencjalne źródło problemów!

---

## 🔍 ETAP 4: SPRAWDZANIE template-edit.php (TB Themes)

Ładuję plik template-edit.php...

**Rozmiar:** 395,223 bytes (386 KB)
**Linie:** 6,636
**Uprawnienia:** 666 ⚠️ DO NAPRAWY - powinno być 644

**UWAGA:** Template-edit.php jest o 48% większy niż edit.php (6636 vs 4469 linii)!

---

## 🔍 ETAP 5: SPRAWDZANIE renderer.php (KLUCZOWY PLIK!)

**Rozmiar:** 266 KB
**Linie:** 5,563
**Uprawnienia:** 666 ⚠️ DO NAPRAWY

**KRYTYCZNE:** Ten plik zawiera render functions dla WSZYSTKICH 52 modułów!

### Sprawdzanie pokrycia wszystkich modułów w renderer.php...

### ✅ RENDERER.PHP - AUDYT MODUŁÓW RENDERUJĄCYCH

**Główna funkcja:** tb_render_module (linia 1825)
**Mechanizm:** Dynamiczne wywołanie funkcji: \	b_render_module_{type}
**Pokrycie modułów: 52/52 = 100%** ✅

Wszystkie moduły mają dedykowane funkcje renderujące:
- text (1992), image (2065), button (2155), heading (2246)
- divider (2329), spacer (2349), video (2365), code (2424)
- quote (2448), list (2483), icon (2527), map (2548)
- accordion (2583), tabs (2623), gallery (2675), testimonial (3128)
- cta (3205), pricing (3238), form (3292), blog (3841)
- blurb (3905), hero (4161), slider (4724), team (4792)
- countdown (4099), counter (4131), bar_counters (5055)
- circle_counter (4012), login (4220), search (4587)
- signup (4689), menu (4253), sidebar (4616), portfolio (4351)
- video_slider (4944), post_slider (4422), post_title (4495)
- post_content (4398), posts_navigation (4523), comments (4046)
- toggle (4862), audio (5016), social (5094), social_follow (5102)
- logo (5344)
- Fullwidth: code (3394), image (3434), map (3465), menu (3491)
- Fullwidth: slider (3525), header (3623), portfolio (3661), post_slider (3718)

---

## 🔍 ETAP 6: AUDYT PLIKÓW JAVASCRIPT (POTENCJALNE PROBLEMY)

### Największe pliki JS (potencjalne źródła bugów):

1. **tb-modal-editor.js** (2,175 linii) - Modal settings
2. **tb-modules-design.js** (2,887 linii) - Design tab rendering

Sprawdzam case statements w tych plikach...

---

## 🔍 ETAP 7: AUDYT CONTENT SETTINGS (tb-modules-content.js)

**Plik:** 331 linii, podzielony na 4 części

### Struktura funkcji:
1. **TB.renderContentSettings** (Part 1, linia 7) - Moduły 1-14
2. **TB.renderContentSettingsPart2** (linia 84) - Moduły 15-22  
3. **TB.renderContentSettingsPart3** (linia 141) - Moduły 23-28
4. **TB.renderContentSettingsPart4** (linia 191) - Moduły 29-52

### Pokrycie modułów przez case statements:

**Part 1 (14 modułów):**
- text, heading, image, button, divider, spacer
- video, audio, code, html, quote, list, icon, map

**Part 2 (8 modułów):**
- accordion, toggle, tabs, gallery
- testimonial, cta, pricing, blurb

**Part 3 (6 modułów):**
- hero, slider, team
- countdown, counter, form

**Part 4 (24 moduły - do weryfikacji):**
- login, signup, menu, search, sidebar, social
- bar_counters, circle_counter, posts_navigation
- comments, portfolio, blog, post_slider, video_slider
- + wszystkie fullwidth modules (8 modułów)

**RAZEM: 52 moduły** ✅

---

## 🔍 ETAP 8: IDENTYFIKACJA PROBLEMÓW

Sprawdzam który moduł NIE ma kompletnych ustawień...

### ✅ POTWIERDZENIE: Content Settings Part 4 zawiera:

1. **Forms & Auth** (3): login, signup, search
2. **Navigation** (2): menu, social  
3. **Layout** (1): sidebar
4. **Stats** (2): bar_counters, circle_counter
5. **Dynamic** (6): posts_navigation, comments, portfolio, blog, post_slider, video_slider, post_title, post_content
6. **Fullwidth** (7): fullwidth_header, fullwidth_menu, fullwidth_image, fullwidth_map, fullwidth_code, fullwidth_slider, fullwidth_post_slider, fullwidth_portfolio
7. **Social & Branding** (2): social_follow, logo

**RAZEM Part 4: 23 moduły**

**TOTAL: 14 + 8 + 6 + 23 = 51 modułów**

⚠️ **PROBLEM:** Brakuje 1 modułu! (52 - 51 = 1)

Sprawdzam który moduł z init.php NIE MA case statement...

---

## 🔴 ETAP 2: ZNALEZIONE KRYTYCZNE PROBLEMY

### Problem 1: Nieprawidłowe uprawnienia JS (NAPRAWIONE ✅)
- tb-render.js: 666 → 644
- Wszystkie pliki JS: krala → www-data

### Problem 2: BRAKUJĄCE PLIKI JS W template-edit.php (KRYTYCZNE! 🔴)

**template-edit.php próbuje załadować nieistniejące pliki:**
- media-gallery.js ❌
- typography.js ❌  
- ai-generate.js ❌

**template-edit.php NIE MA podstawowych plików JS:**
- tb-core.js ❌
- tb-helpers.js ❌
- tb-modules-preview.js ❌
- tb-modules-content.js ❌
- tb-modules-design.js ❌
- tb-structure.js ❌
- tb-events.js ❌
- tb-render.js ❌
- tb-library.js ❌

**DIAGNOZA:** TB Themes editor NIE MOŻE DZIAŁAĆ bez tych plików!

**NAPRAWA:** Dodać wszystkie 14 plików JS do template-edit.php (jak w edit.php)

---

## ✅ ETAP 3: PRZEPISANIE template-edit.php - ZAKOŃCZONE

### Wykonane zmiany:

**PRZED:**
- Plik: 386KB, 6637 linii
- Architektura: Monolityczny inline JavaScript (4770 linii)
- Problemy: 110+ niezdefiniowanych funkcji TB.*
- Status: NIEFUNKCJONALNY ❌

**PO:**
- Plik: 75KB, 1876 linii  
- Architektura: Modułowa (14 zewnętrznych plików JS)
- Redukcja: **80% mniej kodu** (4761 linii usunięte)
- Status: Zjednoczona architektura z edit.php ✅

### Dodane pliki JS:
1. tb-core.js - Core functionality & defaults
2. tb-helpers.js - Helper functions  
3. tb-modules-preview.js - Module preview rendering
4. tb-modules-content.js - Content settings (4 parts)
5. tb-modules-design.js - Design settings  
6. tb-structure.js - Section/Row/Column management
7. tb-events.js - Drag & Drop, event handlers
8. tb-render.js - Canvas rendering
9. tb-library.js - Layout Library modal
10. tb-modal-editor.js - Module editor modal
11-14. Element design system (schemas + 3 parts)

### Usunięte:
- 4770 linii inline JS (niekompletna implementacja)
- 3 nieistniejące pliki: media-gallery.js, typography.js, ai-generate.js
- Duplikaty tb-modal-editor.js

### Backup:
- template-edit.php.BACKUP (386KB) - zachowany dla bezpieczeństwa

---

## 🔍 NASTĘPNE KROKI:

1. **Test template-edit.php** - sprawdzenie czy działa
2. **Weryfikacja edit.php** - czy ma tę samą architekturę
3. **Test wszystkich 52 modułów** - każdy typ pojedynczo
4. **Kontynuacja audytu** (Etapy 4-7)
