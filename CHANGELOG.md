# Changelog

All notable changes to Jessie CMS.

## [0.15.0] — 2026-02-25

### Added
- **Demo site** — jessie-demo theme (8 homepage sections, 3 sub-pages, pricing, about)
- **Dynamic theme showcase** — marquee with all 40+ themes read from filesystem

## [0.14.0] — 2026-02-25

### Added
- **LMS upgrade** — reviews, certificates, 30 API endpoints, 2 frontend views
- **Social Media** — hashtag research, bulk scheduling, content recycling, engagement tracking
- **Email Marketing** — segmentation, automations, A/B testing, bounce handling, list hygiene
- **Analytics** — funnels, UTM tracking, geo breakdown, realtime, heatmap, session paths
- **Booking** — customer history, recurring appointments, reschedule, waitlist, export
- **Membership** — 2 frontend views (signup, member portal)
- **Newsletter** — 2 frontend views (preferences, unsubscribe)

## [0.13.0] — 2026-02-25

### Added
- **SaaS Tier 2** — Social Media Manager, Email Marketing, Analytics Dashboard
- 6 SaaS frontend views (/saas/seo, /saas/copy, /saas/images, /saas/social, /saas/email, /saas/analytics)

## [0.12.0] — 2026-02-25

### Added
- **SaaS Platform** — jessie-saas-core (auth, credits, billing, 40 plans)
- **SEO Writer** — AI article generation, keyword research, live scoring
- **AI Copywriter** — 7 platforms, brand voices, bulk batches
- **Image Studio** — background removal, ALT text, enhance, generate, resize

## [0.11.0] — 2026-02-24

### Added
- **11 domain plugins** — Booking, Newsletter, Restaurant, LMS, Membership, Events, Directory, Jobs, Real Estate, Affiliate, Portfolio
- **Shop AI** — SEO dashboard (14 methods), HuggingFace images (6 methods)
- **Dropshipping** — suppliers, product links, imports, order forwarding, price rules, AI descriptions
- **143 unit tests** (102 core + 41 plugin), 0 failures

## [0.10.0] — 2026-02-23

### Added
- **E-commerce v2** — Shop extensions (coupons, variants, reviews, digital downloads, wishlists, analytics, abandoned carts)
- **CRM** — customer management, order history
- **A/B Testing & Popups** — campaigns, exit-intent, targeting rules
- **Chatbot** — AI-powered widget, knowledge base, training


### Added
- **Contact form handler** — AJAX submissions, honeypot spam prevention, rate limiting, admin inbox
- **Frontend search** — `/search?q=` with highlighted results, rendered through active theme
- **RSS feed** — `/feed` with latest 20 articles (RSS 2.0 + Atom self-link)
- **Dynamic robots.txt** — auto-generated with sitemap link, customizable via settings
- **Dynamic favicon** — serves from theme/uploads or generates SVG from primary color
- **Role-based access control** — admin/editor/viewer hierarchy with `Session::requireRole()`
- **Dark mode** — system preference detection + manual toggle on all AI-generated themes
- **Theme thumbnails** — SVG previews auto-generated from theme colors
- **CSS minification** — `style.css` (minified) + `style.dev.css` (readable)
- **Accessibility** — skip-nav, focus-visible, ARIA expanded, lazy loading
- **Section drag & drop** — reorder homepage sections in wizard Step 2
- **OG image fallback** — uses theme thumbnail when no featured image set

### Fixed
- Mobile menu class mismatch (`menu-open` → `nav-open`) across 33 themes
- Header `height: 72px; overflow: hidden` → `min-height: 72px` (allows growth)
- CTA toggle in visual editor now actually hides/shows the button
- Block handle clipped by header overflow in edit mode
- Sub-page inline styles stripped via post-processing
- `/features` route rendering through active theme (was hardcoded view)
- Search variable collision with `page.php` template `$page` variable

### Changed
- `FeaturesController` removed — pages render through `PageController`
- Login stores actual role from DB (was hardcoded 'admin')

## [0.8.0] — 2026-02-20

### Added
- Section pattern CSS guide — 190 patterns with decorative-only instructions
- Per-pattern decorative guides in all 15 registries
- Dynamic pattern grids in wizard Step 3
- Hero/CTA ordering enforcement (hero first, CTA last)
- Font Awesome 7 auto-fix in CSS post-processing
- Footer contact/social auto-seeding
- Header CTA → `header-cta` class (prevents section CTA collision)

### Fixed
- Theme template seeding for dynamic `{$i}` patterns
- Visual editor section selection on sub-pages
- AI dropdown positioning (fixed + auto-flip)
- "Generate all content" for sub-pages

## [0.7.0] — 2026-02-18

### Added
- ATB Wizard restructured to 5-step content-first flow
- Footer Pattern Registry (15 patterns, 5 groups)
- Hero Pattern Registry (10 patterns, 4 groups)
- Sub-page generation from database content (not PHP templates)
- Smart rendering in page.php (rich → full-width, simple → prose wrapper)
- Content saved to DB (`theme_customizations`) during assembly

### Fixed
- `render_menu()` echo requirement
- Admin toolbar CSS specificity (was matching all `[class*="header"]`)
- Container class normalization (prefix → generic)

## [0.6.0] — 2026-02-15

### Added
- REST API v1 (read-only: articles, pages, menus, site)
- Model layer (BaseModel + 5 domain models)
- Image optimizer with WebP + srcset
- HTTP caching (mod_expires + gzip)
- Rate limiting on login
- Migration system
- 102 unit tests

## [0.5.0] — 2026-02-13

### Added
- Frontend Visual Editor v3 (click-to-edit, AI content/design, drag & drop)
- AI Theme Builder — complete 4-step pipeline
- Theme Studio — 15 features, live preview, section manager

## [0.4.0] — 2026-02-12

### Added
- Theme Studio with visual customizer
- Homepage section manager (drag & drop, toggle visibility)
- Restaurant theme redesign (33KB CSS)
- Theme content isolation (per-theme pages, articles, menus)

## [0.3.0] — 2026-02-10

### Added
- AI Theme Builder with knowledge base approach
- 8 template functions for theme generation
- CSS variables mandate in all themes

## [0.2.0] — 2026-02-09

### Added
- Setup wizard (4-step onboarding)
- 5 starter themes (SaaS, Restaurant, Agency, Medical, Portfolio)
- Demo content system
- SEO suite (15 pages, dynamic sitemap, score tracking)
- Admin toolbar

### Removed
- 3,038 PHP files (78% cleanup)
- Legacy TB3, TB4, AI Designer

## [0.1.0] — 2026-02-08

### Added
- JTB deep audit (79 modules, 95%+ ready)
- CSS Extractor for style preservation
- Frontend integration (header/footer/layouts)
- 30 unit tests
- Legacy removal (444 files, 19 tables)
