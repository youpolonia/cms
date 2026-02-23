<?php
/**
 * Header Pattern Registry
 * 
 * Pre-built header HTML layouts with structural CSS.
 * AI generates only decorative CSS (colors, fonts, effects) — never the structure.
 * 
 * 25 patterns across 7 groups.
 * @since 2026-02-17
 */

class HeaderPatternRegistry
{
    // ═══════════════════════════════════════
    // PATTERN DEFINITIONS
    // ═══════════════════════════════════════

    private static array $patterns = [
        // --- Standard ---
        ['id'=>'classic',           'group'=>'standard',    'max_nav'=>8,  'css_type'=>'single-row',
         'best_for'=>['business','agency','consulting','construction','manufacturing','logistics','insurance','accounting']],
        ['id'=>'nav-center',        'group'=>'standard',    'max_nav'=>6,  'css_type'=>'nav-center',
         'best_for'=>['interior-design','architecture','fashion','beauty','jewelry','wedding','florist','event-planning']],
        ['id'=>'brand-center',      'group'=>'standard',    'max_nav'=>6,  'css_type'=>'brand-center',
         'best_for'=>['restaurant','bakery','cafe','bar','winery','brewery','catering','food-truck']],
        ['id'=>'stacked',           'group'=>'standard',    'max_nav'=>8,  'css_type'=>'stacked',
         'best_for'=>['university','school','library','museum','gallery','cultural-center','nonprofit','charity']],
        ['id'=>'inline-tight',      'group'=>'standard',    'max_nav'=>5,  'css_type'=>'inline',
         'best_for'=>['startup','tech','app','digital','ai','blockchain','fintech']],

        // --- Topbar ---
        ['id'=>'topbar-info',       'group'=>'topbar',      'max_nav'=>7,  'css_type'=>'topbar',
         'best_for'=>['hospital','clinic','dental','veterinary','pharmacy','healthcare','medical']],
        ['id'=>'topbar-social',     'group'=>'topbar',      'max_nav'=>7,  'css_type'=>'topbar',
         'best_for'=>['influencer','content-creator','podcast','youtube','music','entertainment','media']],
        ['id'=>'topbar-full',       'group'=>'topbar',      'max_nav'=>7,  'css_type'=>'topbar',
         'best_for'=>['hotel','resort','travel','tourism','airline','cruise','car-rental']],
        ['id'=>'topbar-announce',   'group'=>'topbar',      'max_nav'=>7,  'css_type'=>'topbar',
         'best_for'=>['ecommerce','retail','marketplace','fashion-brand','cosmetics','supplements']],

        // --- Creative ---
        ['id'=>'split-nav',         'group'=>'creative',    'max_nav'=>6,  'css_type'=>'brand-center',
         'best_for'=>['luxury','spa','fine-dining','yacht','country-club','golf']],
        ['id'=>'actions-bar',       'group'=>'creative',    'max_nav'=>6,  'css_type'=>'single-row',
         'best_for'=>['saas','platform','directory','portal','dashboard']],
        ['id'=>'brand-tagline',     'group'=>'creative',    'max_nav'=>6,  'css_type'=>'single-row',
         'best_for'=>['craft','artisan','handmade','organic','farm','vineyard','distillery']],

        // --- Transparent ---
        ['id'=>'transparent',       'group'=>'transparent', 'max_nav'=>6,  'css_type'=>'transparent',
         'best_for'=>['photography','videography','film','creative-agency','portfolio','art','design-studio']],
        ['id'=>'transparent-center','group'=>'transparent', 'max_nav'=>6,  'css_type'=>'transparent-center',
         'best_for'=>['wedding-venue','estate','boutique-hotel','chateau','garden']],
        ['id'=>'transparent-bold',  'group'=>'transparent', 'max_nav'=>5,  'css_type'=>'transparent-bold',
         'best_for'=>['extreme-sports','adventure','outdoor','camping','surfing','climbing']],

        // --- Minimal ---
        ['id'=>'minimal-clean',     'group'=>'minimal',     'max_nav'=>5,  'css_type'=>'minimal',
         'best_for'=>['personal-blog','writer','author','journalist','coach','therapist','counselor']],
        ['id'=>'minimal-line',      'group'=>'minimal',     'max_nav'=>5,  'css_type'=>'minimal-line',
         'best_for'=>['architect','industrial-design','product-design','minimalist','zen','japanese']],
        ['id'=>'minimal-dots',      'group'=>'minimal',     'max_nav'=>5,  'css_type'=>'minimal-dots',
         'best_for'=>['art-gallery','exhibition','print','typography','calligraphy','bookshop']],

        // --- Bold ---
        ['id'=>'bold-bar',          'group'=>'bold',        'max_nav'=>6,  'css_type'=>'bold-bar',
         'best_for'=>['gym','fitness','crossfit','martial-arts','sports','esports','gaming']],
        ['id'=>'bold-offset',       'group'=>'bold',        'max_nav'=>5,  'css_type'=>'bold-offset',
         'best_for'=>['nightclub','lounge','concert','festival','event','promoter']],
        ['id'=>'burger-only',       'group'=>'bold',        'max_nav'=>99, 'css_type'=>'burger-only',
         'best_for'=>['creative-portfolio','artist','illustrator','motion-design','3d','vfx']],

        // --- Industry ---
        ['id'=>'professional',      'group'=>'industry',    'max_nav'=>7,  'css_type'=>'professional',
         'best_for'=>['law-firm','attorney','notary','financial-advisor','wealth-management','cpa','tax']],
        ['id'=>'service-header',    'group'=>'industry',    'max_nav'=>6,  'css_type'=>'service',
         'best_for'=>['plumber','electrician','hvac','roofing','pest-control','locksmith','towing','emergency-service']],
        ['id'=>'commerce',          'group'=>'industry',    'max_nav'=>6,  'css_type'=>'commerce',
         'best_for'=>['online-store','fashion-store','electronics','furniture','home-decor','pet-store','bookstore']],
        ['id'=>'editorial',         'group'=>'industry',    'max_nav'=>8,  'css_type'=>'editorial',
         'best_for'=>['magazine','newspaper','news','blog','publication','journal','review-site']],
    ];

    // ═══════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════

    /**
     * Select best pattern for a brief.
     */
    public static function selectPattern(array $brief): string
    {
        $industry = strtolower(str_replace(' ', '-', $brief['industry'] ?? ''));
        $style    = strtolower($brief['style'] ?? 'modern');
        $navCount = count($brief['selected_pages'] ?? []) + 1;

        // Find industry matches
        $candidates = [];
        foreach (self::$patterns as $p) {
            if (in_array($industry, $p['best_for']) && $navCount <= $p['max_nav']) {
                $candidates[] = $p['id'];
            }
        }

        // Fallback: style-based
        if (empty($candidates)) {
            $styleMap = [
                'minimal'    => ['minimal-clean','minimal-line','minimal-dots','inline-tight'],
                'bold'       => ['bold-bar','bold-offset','transparent-bold'],
                'elegant'    => ['brand-center','stacked','nav-center','transparent-center'],
                'modern'     => ['classic','nav-center','actions-bar','inline-tight'],
                'corporate'  => ['classic','topbar-info','professional'],
                'playful'    => ['bold-bar','brand-tagline','split-nav'],
                'luxurious'  => ['transparent-center','stacked','brand-center','split-nav'],
                'creative'   => ['transparent','burger-only','bold-offset','minimal-dots'],
                'vintage'    => ['brand-tagline','stacked','editorial'],
                'dark'       => ['transparent','bold-bar','burger-only'],
                'clean'      => ['minimal-clean','classic','inline-tight'],
                'professional'=>['classic','professional','topbar-info'],
                'artistic'   => ['minimal-dots','transparent','burger-only'],
                'rustic'     => ['brand-tagline','stacked','brand-center'],
                'futuristic' => ['inline-tight','actions-bar','bold-bar'],
                'warm'       => ['brand-center','topbar-info','brand-tagline'],
                'geometric'  => ['minimal-line','inline-tight','bold-bar'],
                'organic'    => ['brand-tagline','brand-center','stacked'],
                'industrial' => ['bold-bar','classic','minimal-line'],
                'sophisticated'=>['nav-center','transparent-center','professional'],
                'vibrant'    => ['bold-bar','bold-offset','topbar-announce'],
                'serene'     => ['minimal-clean','transparent-center','stacked'],
            ];
            $pool = $styleMap[$style] ?? ['classic','nav-center','stacked','inline-tight'];
            foreach ($pool as $id) {
                $def = self::findPattern($id);
                if ($def && $navCount <= $def['max_nav']) {
                    $candidates[] = $id;
                }
            }
        }

        if (empty($candidates)) return 'classic';
        return $candidates[array_rand($candidates)];
    }

    /**
     * Render a header pattern.
     * Returns ['html'=>..., 'structural_css'=>..., 'pattern_id'=>..., 'classes'=>[...]]
     */
    public static function render(string $patternId, string $prefix, array $brief): array
    {
        $def = self::findPattern($patternId);
        if (!$def) {
            $patternId = 'classic';
            $def = self::findPattern('classic');
        }

        $cta = $brief['header']['cta_text']
            ?? self::getIndustryCTA($brief['industry'] ?? '');

        $html = self::buildHTML($patternId, $prefix, $cta);
        $css  = self::buildStructuralCSS($def['css_type'], $prefix);

        // Extract class names for Step 3 reference
        preg_match_all('/class="([^"]*)"/', $html, $m);
        $classes = [];
        foreach ($m[1] as $classList) {
            foreach (preg_split('/\s+/', $classList) as $c) {
                $c = trim($c);
                if ($c && strpos($c, '<?') === false) $classes[$c] = true;
            }
        }

        return [
            'html'           => $html,
            'structural_css' => $css,
            'pattern_id'     => $patternId,
            'classes'        => array_keys($classes),
            'max_nav'        => $def['max_nav'] ?? 7,
        ];
    }

    /**
     * Get all pattern IDs and names (for UI/debug).
     */
    public static function getPatternList(): array
    {
        return array_map(fn($p) => ['id' => $p['id'], 'group' => $p['group']], self::$patterns);
    }

    /**
     * Get pattern-specific decorative CSS guide for Step 3 AI prompt.
     * Tells AI what UNIQUE visual treatment this header pattern needs.
     */
    public static function getDecorativeGuide(string $patternId): string
    {
        return match($patternId) {
            // --- Standard ---
            'classic' => <<<'G'
- Clean single-row header, subtle bottom border or shadow on scroll
- Brand text: bold, no-nonsense, professional weight
- Nav links: even spacing, subtle hover underline ::after, smooth color transition
- CTA: solid primary button, pill or rounded, stands out against nav
G,
            'nav-center' => <<<'G'
- Navigation visually centered between brand and CTA
- Brand and CTA anchored to sides, nav flows naturally in middle
- Elegant link spacing, delicate hover effects (opacity or underline)
- Lighter visual weight than classic — refined, fashion/design feel
G,
            'brand-center', 'split-nav' => <<<'G'
- Brand/logo is the visual focal point (centered, larger)
- Nav split around brand or grouped to one side
- Sophisticated, luxury feel — generous letter-spacing on brand
- Subtle nav links, no heavy hover effects — underline or opacity
G,
            'stacked' => <<<'G'
- Two-row header: brand row on top, nav row below
- Brand row: larger padding, centered or left-aligned, tagline visible
- Nav row: tighter, possibly different bg shade, acts as secondary bar
- Institutional/academic feel — dignified, structured
G,
            'inline-tight' => <<<'G'
- Compact single row, minimal padding, space-efficient
- Tight letter-spacing on nav, smaller font-size
- Tech/startup feel — clean, no decorative elements
- Sharp transitions, monospace or geometric font hints
G,
            // --- Topbar ---
            'topbar-info' => <<<'G'
- Topbar: subtle darker/lighter bg than main header, thin height
- Phone/email with icons: icon color primary or muted, small font
- Social icons in topbar: tiny circles or plain icons
- Main bar: clean, professional, separated from topbar by thin border
G,
            'topbar-social' => <<<'G'
- Topbar: social icons prominent, colorful or monochrome on hover
- Social icons left, phone right — inverted emphasis from info variant
- Influencer/creator feel — social presence emphasized
- Topbar bg slightly contrasting, not heavy
G,
            'topbar-full' => <<<'G'
- Rich topbar with phone, email, AND social — information-dense
- Topbar icons: consistent size, subtle color, organized into left/right groups
- Travel/hospitality feel — trustworthy, contact-ready
- Main nav below: cleaner, lets topbar carry the utility info
G,
            'topbar-announce' => <<<'G'
- Announcement bar: eye-catching bg (primary or accent), contrasting text
- Announcement text centered, link with underline or arrow
- E-commerce feel — promotional, urgency-driven
- Dismiss/close button optional, bar should feel temporary/promotional
G,
            // --- Creative ---
            'actions-bar' => <<<'G'
- Actions group (search + CTA + burger) clustered on right
- Search toggle: icon button, subtle, expands on click
- SaaS/dashboard feel — utility-focused header
- Clean separation between nav and action buttons
G,
            'brand-tagline' => <<<'G'
- Brand has visible tagline below brand name
- Tagline: smaller font, italic or muted color, letter-spacing
- Artisan/craft feel — warm, personal, story-driven
- Nav links: warm hover colors, friendly underline transitions
G,
            // --- Transparent ---
            'transparent' => <<<'G'
- Transparent bg over hero — NO visible header bg initially
- White/light text on dark hero, color transitions on scroll
- .header-scrolled: solid bg fades in, text darkens, shadow appears
- Photography/portfolio feel — immersive, hero-first
- backdrop-filter: blur() as scrolled bg option
G,
            'transparent-center' => <<<'G'
- Transparent + brand centered — luxury immersive layout
- Even more delicate than standard transparent
- Wedding/estate feel — romantic, ethereal text treatment
- Scroll transition: gentle fade-in of solid bg
G,
            'transparent-bold' => <<<'G'
- Transparent + larger brand — adventure/outdoor bold feel
- Brand text: bolder weight, possibly uppercase, larger
- High contrast white text, strong text-shadow for readability
- Scroll: dramatic bg transition, possibly primary-colored bar
G,
            // --- Minimal ---
            'minimal-clean' => <<<'G'
- Bare minimum: brand + nav + burger, no CTA button
- Ultra-thin, almost invisible header — content-first
- Personal/writer feel — intimate, no commercial pressure
- Subtle hover: opacity change or thin underline only
G,
            'minimal-line' => <<<'G'
- Thin line (border-bottom) separating header from content
- Japanese/zen minimalism — restrained, precise
- Letter-spacing wider on nav, uppercase small-caps feel
- Line color: subtle, var(--border) or rgba
G,
            'minimal-dots' => <<<'G'
- Dots/bullet separators between nav items instead of spacing
- Art gallery feel — typographic, editorial navigation
- Dots: small circles, muted color, act as visual separator
- Delicate, refined, no heavy hover effects
G,
            // --- Bold ---
            'bold-bar' => <<<'G'
- Strong visual presence: heavier bg color or thick border
- Bold font weights on brand and nav, uppercase nav links
- Gym/sports feel — energetic, high contrast, strong shadows
- CTA button: extra prominent, possibly accent-colored (not primary)
G,
            'bold-offset' => <<<'G'
- Asymmetric bold design, larger brand treatment
- Nightclub/event feel — dramatic, possibly neon accent hints
- Strong hover effects: color shift, underline glow
- Bolder shadows, heavier border treatments
G,
            'burger-only' => <<<'G'
- No visible nav links — only hamburger menu
- Maximally clean: brand + CTA + burger, nothing else visible
- Creative portfolio feel — mysterious, content-focused
- Burger: custom animation (X transform), prominent size
- Opened menu: full-screen or slide-in panel, dramatic reveal
G,
            // --- Industry ---
            'professional' => <<<'G'
- Phone number visible in header bar — clickable, prominent
- Law/finance feel — trustworthy, conservative color use
- Phone icon: subtle, professional, not playful
- Nav links: measured spacing, no flashy hover effects
G,
            'service-header' => <<<'G'
- Emergency/urgent topbar with badge icon and phone
- Service industry feel — 24/7 available, action-oriented
- Emergency badge: accent bg (red or orange), icon + text
- Topbar: slightly taller than info variant, more prominent
G,
            'commerce' => <<<'G'
- Search bar visible in header — input with border, search icon button
- Cart/account icons: clean line icons, badge count circle on cart
- E-commerce feel — shop-ready, utility-dense header
- Search input: border on focus glow primary, rounded
G,
            'editorial' => <<<'G'
- Two-row: large brand row + slim nav bar below
- Magazine/newspaper feel — brand is oversized, editorial typography
- Nav bar: distinct bg shade, compact, horizontal scroll on mobile possible
- Search icon in nav bar, subtle placement
G,
            default => '',
        };
    }

    /**
     * Generate a CSS class prefix from theme name.
     * "Nordic Brew Coffee" → "nbc"
     */
    public static function generatePrefix(string $name): string
    {
        $words = preg_split('/[\s\-_]+/', trim($name));
        if (count($words) >= 3) {
            return strtolower($words[0][0] . $words[1][0] . $words[2][0]);
        } elseif (count($words) >= 2) {
            return strtolower($words[0][0] . $words[1][0]);
        }
        return strtolower(substr(preg_replace('/[^a-z]/i', '', $name), 0, 3));
    }

    // ═══════════════════════════════════════
    // HTML SNIPPETS
    // ═══════════════════════════════════════

    private static function s_logo(string $p): string
    {
        return <<<HTML
    <a href="/" class="{$p}-brand">
      <?php if (\$logo = theme_get('brand.logo')): ?>
        <img src="<?= esc(\$logo) ?>" alt="<?= esc(\$siteName) ?>" class="{$p}-brand-img">
      <?php else: ?>
        <span class="{$p}-brand-text" data-ts="brand.site_name"><?= esc(theme_get('brand.site_name', \$siteName)) ?></span>
      <?php endif; ?>
    </a>
HTML;
    }

    private static function s_logo_tagline(string $p): string
    {
        return <<<HTML
    <a href="/" class="{$p}-brand {$p}-brand--with-tagline">
      <?php if (\$logo = theme_get('brand.logo')): ?>
        <img src="<?= esc(\$logo) ?>" alt="<?= esc(\$siteName) ?>" class="{$p}-brand-img">
      <?php else: ?>
        <span class="{$p}-brand-text" data-ts="brand.site_name"><?= esc(theme_get('brand.site_name', \$siteName)) ?></span>
      <?php endif; ?>
      <span class="{$p}-brand-tagline" data-ts="brand.tagline"><?= esc(theme_get('brand.tagline', '')) ?></span>
    </a>
HTML;
    }

    private static function s_logo_large(string $p): string
    {
        return <<<HTML
    <a href="/" class="{$p}-brand {$p}-brand--large">
      <?php if (\$logo = theme_get('brand.logo')): ?>
        <img src="<?= esc(\$logo) ?>" alt="<?= esc(\$siteName) ?>" class="{$p}-brand-img">
      <?php else: ?>
        <span class="{$p}-brand-text" data-ts="brand.site_name"><?= esc(theme_get('brand.site_name', \$siteName)) ?></span>
      <?php endif; ?>
    </a>
HTML;
    }

    private static function s_nav(string $p): string
    {
        return <<<HTML
    <nav id="headerNav" class="{$p}-nav">
      <?php echo render_menu('header', ['class' => '{$p}-nav-list', 'link_class' => '{$p}-nav-link', 'wrap' => false]); ?>
    </nav>
HTML;
    }

    private static function s_cta(string $p, string $ctaText): string
    {
        return <<<HTML
    <?php if (theme_get('header.show_cta', true)): ?>
    <a href="<?= esc(theme_get('header.cta_link', '/contact')) ?>" class="{$p}-header-cta" data-ts="header.cta_text" data-ts-href="header.cta_link"><?= esc(theme_get('header.cta_text', '{$ctaText}')) ?></a>
    <?php endif; ?>
HTML;
    }

    private static function s_dark_toggle(): string
    {
        return <<<HTML
    <button class="dark-mode-toggle" data-dark-toggle aria-label="Toggle dark mode" style="background:none;border:none;cursor:pointer;font-size:1.25rem;padding:4px 8px">🌙</button>
HTML;
    }

    private static function s_burger(string $p): string
    {
        return <<<HTML
    <button id="mobileToggle" class="{$p}-burger" aria-label="Menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
HTML;
    }

    private static function s_phone(string $p): string
    {
        return <<<HTML
      <span class="{$p}-topbar-phone" data-ts="header.phone"><i class="fas fa-phone"></i> <?= esc(theme_get('header.phone', '+1 234 567 890')) ?></span>
HTML;
    }

    private static function s_email(string $p): string
    {
        return <<<HTML
      <span class="{$p}-topbar-email" data-ts="header.email"><i class="fas fa-envelope"></i> <?= esc(theme_get('header.email', 'info@example.com')) ?></span>
HTML;
    }

    private static function s_social(string $p): string
    {
        return <<<HTML
      <div class="{$p}-topbar-social">
        <?php if (\$fb = theme_get('footer.facebook')): ?><a href="<?= esc(\$fb) ?>" target="_blank" class="{$p}-social-icon"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
        <?php if (\$ig = theme_get('footer.instagram')): ?><a href="<?= esc(\$ig) ?>" target="_blank" class="{$p}-social-icon"><i class="fab fa-instagram"></i></a><?php endif; ?>
        <?php if (\$tw = theme_get('footer.twitter')): ?><a href="<?= esc(\$tw) ?>" target="_blank" class="{$p}-social-icon"><i class="fab fa-twitter"></i></a><?php endif; ?>
        <?php if (\$li = theme_get('footer.linkedin')): ?><a href="<?= esc(\$li) ?>" target="_blank" class="{$p}-social-icon"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
      </div>
HTML;
    }

    private static function s_search(string $p): string
    {
        return <<<HTML
    <?php if (theme_get('header.show_search', false)): ?>
    <button class="{$p}-search-toggle" aria-label="Search"><i class="fas fa-search"></i></button>
    <?php endif; ?>
HTML;
    }

    private static function s_phone_visible(string $p): string
    {
        return <<<HTML
    <a href="tel:<?= esc(theme_get('header.phone', '+1 234 567 890')) ?>" class="{$p}-header-phone" data-ts="header.phone">
      <i class="fas fa-phone"></i> <span><?= esc(theme_get('header.phone', '+1 234 567 890')) ?></span>
    </a>
HTML;
    }

    private static function s_announcement(string $p): string
    {
        return <<<HTML
      <span class="{$p}-announce-text" data-ts="announcement.text"><?= esc(theme_get('announcement.text', 'Free shipping on orders over $50')) ?></span>
      <?php if (\$aLink = theme_get('announcement.link')): ?>
        <a href="<?= esc(\$aLink) ?>" class="{$p}-announce-link" data-ts="announcement.link_text"><?= esc(theme_get('announcement.link_text', 'Shop now')) ?></a>
      <?php endif; ?>
HTML;
    }

    // ═══════════════════════════════════════
    // HTML BUILDERS
    // ═══════════════════════════════════════

    private static function buildHTML(string $id, string $p, string $cta): string
    {
        $logo     = self::s_logo($p);
        $logoTag  = self::s_logo_tagline($p);
        $logoLg   = self::s_logo_large($p);
        $nav      = self::s_nav($p);
        $ctaBtn   = self::s_cta($p, $cta);
        $darkTgl  = self::s_dark_toggle();
        $burger   = self::s_burger($p);
        $phone    = self::s_phone($p);
        $email    = self::s_email($p);
        $social   = self::s_social($p);
        $search   = self::s_search($p);
        $phoneVis = self::s_phone_visible($p);
        $announce = self::s_announcement($p);

        $result = match($id) {

            // ─── STANDARD ───────────────────────
            'classic' => <<<HTML
<header id="siteHeader" class="{$p}-header">
  <div class="{$p}-header-inner">
{$logo}
{$nav}
{$ctaBtn}
{$burger}
  </div>
</header>
HTML,

            'nav-center' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--nav-center">
  <div class="{$p}-header-inner">
{$logo}
{$nav}
{$ctaBtn}
{$burger}
  </div>
</header>
HTML,

            'brand-center', 'split-nav' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--brand-center">
  <div class="{$p}-header-inner">
{$nav}
{$logo}
    <div class="{$p}-header-actions">
{$ctaBtn}
{$burger}
    </div>
  </div>
</header>
HTML,

            'stacked' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--stacked">
  <div class="{$p}-header-inner">
    <div class="{$p}-header-brand-row">
{$logo}
    </div>
    <div class="{$p}-header-nav-row">
{$nav}
{$ctaBtn}
{$burger}
    </div>
  </div>
</header>
HTML,

            'inline-tight' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--inline">
  <div class="{$p}-header-inner">
{$logo}
{$nav}
{$ctaBtn}
{$burger}
  </div>
</header>
HTML,

            // ─── TOPBAR ─────────────────────────
            'topbar-info' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--topbar">
  <div class="{$p}-topbar">
    <div class="{$p}-topbar-left">
{$phone}
{$email}
    </div>
    <div class="{$p}-topbar-right">
{$social}
    </div>
  </div>
  <div class="{$p}-header-inner">
{$logo}
{$nav}
{$ctaBtn}
{$burger}
  </div>
</header>
HTML,

            'topbar-social' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--topbar">
  <div class="{$p}-topbar">
    <div class="{$p}-topbar-left">
{$social}
    </div>
    <div class="{$p}-topbar-right">
{$phone}
    </div>
  </div>
  <div class="{$p}-header-inner">
{$logo}
{$nav}
{$ctaBtn}
{$burger}
  </div>
</header>
HTML,

            'topbar-full' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--topbar">
  <div class="{$p}-topbar">
    <div class="{$p}-topbar-left">
{$phone}
{$email}
    </div>
    <div class="{$p}-topbar-right">
{$social}
    </div>
  </div>
  <div class="{$p}-header-inner">
{$logo}
{$nav}
{$ctaBtn}
{$burger}
  </div>
</header>
HTML,

            'topbar-announce' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--topbar">
  <div class="{$p}-topbar {$p}-topbar--announce">
{$announce}
  </div>
  <div class="{$p}-header-inner">
{$logo}
{$nav}
{$ctaBtn}
{$burger}
  </div>
</header>
HTML,

            // ─── CREATIVE ───────────────────────
            'actions-bar' => <<<HTML
<header id="siteHeader" class="{$p}-header">
  <div class="{$p}-header-inner">
{$logo}
{$nav}
    <div class="{$p}-header-actions">
{$search}
{$ctaBtn}
{$burger}
    </div>
  </div>
</header>
HTML,

            'brand-tagline' => <<<HTML
<header id="siteHeader" class="{$p}-header">
  <div class="{$p}-header-inner">
{$logoTag}
{$nav}
{$ctaBtn}
{$burger}
  </div>
</header>
HTML,

            // ─── TRANSPARENT ────────────────────
            'transparent' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--transparent">
  <div class="{$p}-header-inner">
{$logo}
{$nav}
{$ctaBtn}
{$burger}
  </div>
</header>
HTML,

            'transparent-center' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--transparent {$p}-header--brand-center">
  <div class="{$p}-header-inner">
{$nav}
{$logo}
    <div class="{$p}-header-actions">
{$ctaBtn}
{$burger}
    </div>
  </div>
</header>
HTML,

            'transparent-bold' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--transparent">
  <div class="{$p}-header-inner">
{$logoLg}
{$nav}
{$ctaBtn}
{$burger}
  </div>
</header>
HTML,

            // ─── MINIMAL ────────────────────────
            'minimal-clean' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--minimal">
  <div class="{$p}-header-inner">
{$logo}
{$nav}
{$burger}
  </div>
</header>
HTML,

            'minimal-line' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--minimal {$p}-header--line">
  <div class="{$p}-header-inner">
{$logo}
{$nav}
{$burger}
  </div>
</header>
HTML,

            'minimal-dots' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--minimal {$p}-header--dots">
  <div class="{$p}-header-inner">
{$logo}
{$nav}
{$burger}
  </div>
</header>
HTML,

            // ─── BOLD ───────────────────────────
            'bold-bar' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--bold">
  <div class="{$p}-header-inner">
{$logo}
{$nav}
{$ctaBtn}
{$burger}
  </div>
</header>
HTML,

            'bold-offset' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--bold-offset">
  <div class="{$p}-header-inner">
{$logoLg}
{$nav}
{$ctaBtn}
{$burger}
  </div>
</header>
HTML,

            'burger-only' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--burger-only">
  <div class="{$p}-header-inner">
{$logo}
{$ctaBtn}
{$burger}
  </div>
{$nav}
</header>
HTML,

            // ─── INDUSTRY ───────────────────────
            'professional' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--professional">
  <div class="{$p}-header-inner">
{$logo}
{$phoneVis}
{$nav}
{$ctaBtn}
{$burger}
  </div>
</header>
HTML,

            'service-header' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--service">
  <div class="{$p}-topbar {$p}-topbar--emergency">
    <div class="{$p}-topbar-left">
      <span class="{$p}-emergency-badge"><i class="fas fa-bolt"></i> 24/7 Emergency</span>
{$phone}
    </div>
    <div class="{$p}-topbar-right">
{$email}
    </div>
  </div>
  <div class="{$p}-header-inner">
{$logo}
{$nav}
{$ctaBtn}
{$burger}
  </div>
</header>
HTML,

            'commerce' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--commerce">
  <div class="{$p}-header-inner">
{$logo}
    <div class="{$p}-search-bar">
      <input type="text" placeholder="Search products..." class="{$p}-search-input" aria-label="Search">
      <button class="{$p}-search-submit" aria-label="Search"><i class="fas fa-search"></i></button>
    </div>
{$nav}
    <div class="{$p}-header-icons">
      <a href="/account" class="{$p}-header-icon" aria-label="Account"><i class="fas fa-user"></i></a>
      <a href="/cart" class="{$p}-header-icon" aria-label="Cart"><i class="fas fa-shopping-bag"></i></a>
    </div>
{$burger}
  </div>
</header>
HTML,

            'editorial' => <<<HTML
<header id="siteHeader" class="{$p}-header {$p}-header--editorial">
  <div class="{$p}-header-brand-row">
{$logoLg}
  </div>
  <div class="{$p}-header-nav-bar">
    <div class="{$p}-header-inner">
{$nav}
{$search}
{$burger}
    </div>
  </div>
</header>
HTML,

            // fallback
            default => self::buildHTML('classic', $p, $cta),
        };

        // Inject dark mode toggle before the burger button in every pattern
        $result = str_replace($burger, $darkTgl . "\n" . $burger, $result);

        return $result;
    }

    // ═══════════════════════════════════════
    // CSS BUILDERS
    // ═══════════════════════════════════════

    private static function buildStructuralCSS(string $cssType, string $p): string
    {
        $css = self::css_base($p);

        $css .= match($cssType) {
            'single-row'        => self::css_single_row($p),
            'nav-center'        => self::css_nav_center($p),
            'brand-center'      => self::css_brand_center($p),
            'stacked'           => self::css_stacked($p),
            'inline'            => self::css_inline($p),
            'topbar'            => self::css_topbar($p),
            'transparent'       => self::css_single_row($p) . self::css_transparent($p),
            'transparent-center'=> self::css_brand_center($p) . self::css_transparent($p),
            'transparent-bold'  => self::css_single_row($p) . self::css_transparent($p) . self::css_bold_offset($p),
            'minimal'           => self::css_minimal($p),
            'minimal-line'      => self::css_minimal($p) . self::css_minimal_line($p),
            'minimal-dots'      => self::css_minimal($p) . self::css_minimal_dots($p),
            'bold-bar'          => self::css_single_row($p) . self::css_bold_bar($p),
            'bold-offset'       => self::css_single_row($p) . self::css_bold_offset($p),
            'burger-only'       => self::css_burger_only($p),
            'professional'      => self::css_professional($p),
            'service'           => self::css_topbar($p) . self::css_service($p),
            'commerce'          => self::css_commerce($p),
            'editorial'         => self::css_editorial($p),
            default             => self::css_single_row($p),
        };

        $css .= self::css_mobile($p, $cssType);

        return $css;
    }

    // --- Base (shared by ALL) ---
    private static function css_base(string $p): string
    {
        return <<<CSS

/* ═══ Structural CSS (auto-generated — do not edit) ═══ */
.container { max-width: 1280px; margin: 0 auto; padding: 0 clamp(16px, 3vw, 40px); width: 100%; }
.container-narrow { max-width: 800px; margin: 0 auto; padding: 0 clamp(16px, 3vw, 40px); width: 100%; }
/* ═══ Skip Navigation ═══ */
.skip-nav { position:absolute;top:-100%;left:16px;padding:8px 16px;z-index:10000;background:var(--primary,#6366f1);color:#fff;border-radius:4px;font-size:14px;text-decoration:none;transition:top 0.2s; }
.skip-nav:focus { top:8px; }
/* ═══ Header ═══ */
.{$p}-header {
  position: fixed; top: 0; left: 0; width: 100%; z-index: 1000;
  height: auto; /* prevent AI from setting fixed height on outer header */
  transition: background 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
}
.{$p}-brand {
  text-decoration: none; display: inline-flex; align-items: center;
  flex-shrink: 0; gap: 8px;
}
.{$p}-brand-img { height: 36px; width: auto; display: block; }
.{$p}-brand-text { font-size: 1.25rem; font-weight: 700; white-space: nowrap; }
.{$p}-brand-tagline { font-size: 0.75rem; display: block; opacity: 0.7; }
.{$p}-brand--with-tagline { flex-direction: column; gap: 0; align-items: flex-start; }
.{$p}-brand--large .{$p}-brand-text { font-size: 1.75rem; }
.{$p}-brand--large .{$p}-brand-img { height: 48px; }
.{$p}-nav-list {
  display: flex; list-style: none; margin: 0; padding: 0;
  align-items: center; gap: 4px;
}
.{$p}-nav-link {
  text-decoration: none; white-space: nowrap;
  font-size: 0.875rem; padding: 8px 14px;
  transition: color 0.2s, background 0.2s;
}
.{$p}-header-cta {
  text-decoration: none; display: inline-flex; align-items: center;
  justify-content: center; white-space: nowrap; flex-shrink: 0;
  font-size: 0.875rem; font-weight: 600; padding: 10px 24px;
  transition: all 0.2s;
}
.{$p}-burger {
  display: none; flex-direction: column; justify-content: center;
  align-items: center; gap: 5px; background: none; border: none;
  cursor: pointer; padding: 8px; z-index: 1001;
}
.{$p}-burger span {
  display: block; width: 22px; height: 2px; border-radius: 1px;
  transition: all 0.3s;
}
/* ═══ Focus-visible ═══ */
.{$p}-nav-link:focus-visible, .{$p}-header-cta:focus-visible, .{$p}-burger:focus-visible { outline:2px solid var(--primary,#6366f1);outline-offset:2px;border-radius:4px; }

CSS;
    }

    // --- Single Row (classic, actions-bar, brand-tagline) ---
    private static function css_single_row(string $p): string
    {
        return <<<CSS
.{$p}-header-inner {
  max-width: 1280px; margin: 0 auto;
  display: flex; align-items: center;
  padding: 0 clamp(16px, 3vw, 40px); min-height: 72px;
  gap: 16px;
}
.{$p}-nav { flex: 1; display: flex; justify-content: flex-end; overflow: hidden; flex-shrink: 1; }
.{$p}-header-actions { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
.{$p}-search-toggle { background: none; border: none; cursor: pointer; padding: 8px; flex-shrink: 0; }

CSS;
    }

    // --- Nav Center ---
    private static function css_nav_center(string $p): string
    {
        return <<<CSS
.{$p}-header-inner {
  max-width: 1280px; margin: 0 auto;
  display: flex; align-items: center;
  padding: 0 clamp(16px, 3vw, 40px); min-height: 72px;
  gap: 16px;
}
.{$p}-nav {
  flex: 1; display: flex; justify-content: center;
  overflow: hidden; flex-shrink: 1;
}

CSS;
    }

    // --- Brand Center ---
    private static function css_brand_center(string $p): string
    {
        return <<<CSS
.{$p}-header-inner {
  max-width: 1280px; margin: 0 auto;
  display: flex; align-items: center; justify-content: center;
  padding: 0 clamp(16px, 3vw, 40px); min-height: 72px;
  position: relative; gap: 16px;
}
.{$p}-header--brand-center .{$p}-brand {
  order: 2; flex-shrink: 0;
}
.{$p}-header--brand-center .{$p}-nav {
  order: 1; flex: 1; display: flex; justify-content: flex-end;
  overflow: hidden; flex-shrink: 1;
}
.{$p}-header--brand-center .{$p}-header-actions {
  order: 3; flex: 1; display: flex; justify-content: flex-start;
  align-items: center; gap: 12px;
}

CSS;
    }

    // --- Stacked ---
    private static function css_stacked(string $p): string
    {
        return <<<CSS
.{$p}-header-inner {
  max-width: 1280px; margin: 0 auto;
  display: flex; flex-direction: column; align-items: center;
  padding: 12px clamp(16px, 3vw, 40px) 0;
}
.{$p}-header-brand-row { flex-shrink: 0; padding-bottom: 8px; }
.{$p}-header-nav-row {
  display: flex; align-items: center; gap: 8px;
  width: 100%; justify-content: center;
  padding-bottom: 12px;
}
.{$p}-header--stacked .{$p}-nav { overflow: hidden; flex-shrink: 1; }

CSS;
    }

    // --- Inline ---
    private static function css_inline(string $p): string
    {
        return <<<CSS
.{$p}-header-inner {
  max-width: 1280px; margin: 0 auto;
  display: flex; align-items: center;
  padding: 0 clamp(16px, 3vw, 40px); min-height: 56px;
  gap: 8px;
}
.{$p}-header--inline .{$p}-brand-text { font-size: 1rem; }
.{$p}-header--inline .{$p}-brand-img { height: 28px; }
.{$p}-nav { flex: 1; display: flex; overflow: hidden; flex-shrink: 1; }
.{$p}-header--inline .{$p}-nav-link { font-size: 0.8125rem; padding: 6px 10px; }
.{$p}-header--inline .{$p}-header-cta { font-size: 0.8125rem; padding: 8px 18px; }

CSS;
    }

    // --- Topbar ---
    private static function css_topbar(string $p): string
    {
        return <<<CSS
.{$p}-topbar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 6px clamp(16px, 3vw, 40px);
  font-size: 0.8125rem; max-width: 1280px; margin: 0 auto;
}
.{$p}-topbar-left, .{$p}-topbar-right {
  display: flex; align-items: center; gap: 16px;
}
.{$p}-topbar-phone, .{$p}-topbar-email {
  display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;
}
.{$p}-topbar-social { display: flex; gap: 8px; align-items: center; }
.{$p}-social-icon { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; text-decoration: none; }
.{$p}-topbar--announce {
  justify-content: center; gap: 12px; text-align: center;
}
.{$p}-announce-link { text-decoration: underline; font-weight: 600; }
.{$p}-header-inner {
  max-width: 1280px; margin: 0 auto;
  display: flex; align-items: center;
  padding: 0 clamp(16px, 3vw, 40px); min-height: 64px;
  gap: 16px;
}
.{$p}-nav { flex: 1; display: flex; justify-content: flex-end; overflow: hidden; flex-shrink: 1; }

CSS;
    }

    // --- Transparent modifier ---
    private static function css_transparent(string $p): string
    {
        return <<<CSS
.{$p}-header--transparent { background: transparent !important; }
.{$p}-header--transparent.header-scrolled { background: var(--surface, #fff) !important; }

CSS;
    }

    // --- Minimal ---
    private static function css_minimal(string $p): string
    {
        return <<<CSS
.{$p}-header-inner {
  max-width: 1280px; margin: 0 auto;
  display: flex; align-items: center;
  padding: 0 clamp(16px, 3vw, 40px); min-height: 64px;
  gap: 16px;
}
.{$p}-nav { flex: 1; display: flex; justify-content: flex-end; overflow: hidden; flex-shrink: 1; }

CSS;
    }

    private static function css_minimal_line(string $p): string
    {
        return <<<CSS
.{$p}-header--line { border-bottom: 2px solid currentColor; }
.{$p}-header--line.header-scrolled { border-bottom-color: transparent; }

CSS;
    }

    private static function css_minimal_dots(string $p): string
    {
        return <<<CSS
.{$p}-header--dots .{$p}-nav-list { gap: 0; }
.{$p}-header--dots .{$p}-nav-link { position: relative; }
.{$p}-header--dots .{$p}-nav-list li + li .{$p}-nav-link::before {
  content: '·'; position: absolute; left: -2px; top: 50%;
  transform: translateY(-50%); pointer-events: none; opacity: 0.4;
}

CSS;
    }

    // --- Bold Bar ---
    private static function css_bold_bar(string $p): string
    {
        return <<<CSS
.{$p}-header--bold .{$p}-header-inner { min-height: 80px; }
.{$p}-header--bold .{$p}-brand-text { font-size: 1.5rem; text-transform: uppercase; letter-spacing: 0.05em; }

CSS;
    }

    // --- Bold Offset ---
    private static function css_bold_offset(string $p): string
    {
        return <<<CSS
.{$p}-header--bold-offset .{$p}-brand { margin-right: auto; }
.{$p}-header--bold-offset .{$p}-nav { flex: 0; }

CSS;
    }

    // --- Burger Only ---
    private static function css_burger_only(string $p): string
    {
        return <<<CSS
.{$p}-header--burger-only .{$p}-header-inner {
  max-width: 1280px; margin: 0 auto;
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 clamp(16px, 3vw, 40px); min-height: 72px;
}
.{$p}-header--burger-only .{$p}-burger { display: flex; }
.{$p}-header--burger-only .{$p}-nav {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  transform: translateX(100%); transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 999;
}
body.nav-open .{$p}-header--burger-only .{$p}-nav { transform: translateX(0); }
.{$p}-header--burger-only .{$p}-nav-list { flex-direction: column; gap: 16px; text-align: center; }
.{$p}-header--burger-only .{$p}-nav-link { font-size: 1.5rem; padding: 12px 24px; }

CSS;
    }

    // --- Professional ---
    private static function css_professional(string $p): string
    {
        return <<<CSS
.{$p}-header-inner {
  max-width: 1280px; margin: 0 auto;
  display: flex; align-items: center;
  padding: 0 clamp(16px, 3vw, 40px); min-height: 72px;
  gap: 16px;
}
.{$p}-header-phone {
  display: inline-flex; align-items: center; gap: 8px;
  text-decoration: none; font-size: 0.875rem; white-space: nowrap;
  flex-shrink: 0; margin-right: 8px;
}
.{$p}-nav { flex: 1; display: flex; justify-content: flex-end; overflow: hidden; flex-shrink: 1; }

CSS;
    }

    // --- Service (emergency topbar) ---
    private static function css_service(string $p): string
    {
        return <<<CSS
.{$p}-topbar--emergency { font-weight: 700; }
.{$p}-emergency-badge {
  display: inline-flex; align-items: center; gap: 6px;
  font-weight: 700; text-transform: uppercase; font-size: 0.75rem;
  letter-spacing: 0.05em;
}

CSS;
    }

    // --- Commerce ---
    private static function css_commerce(string $p): string
    {
        return <<<CSS
.{$p}-header-inner {
  max-width: 1280px; margin: 0 auto;
  display: flex; align-items: center;
  padding: 0 clamp(16px, 3vw, 40px); min-height: 72px;
  gap: 16px;
}
.{$p}-search-bar {
  flex: 1; max-width: 480px; position: relative; margin: 0 8px;
}
.{$p}-search-input {
  width: 100%; padding: 8px 40px 8px 16px;
  border: 1px solid rgba(128,128,128,0.3); border-radius: 8px;
  font-size: 0.875rem; outline: none; background: transparent;
}
.{$p}-search-submit {
  position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer; padding: 4px;
}
.{$p}-nav { flex-shrink: 1; display: flex; overflow: hidden; }
.{$p}-header-icons {
  display: flex; align-items: center; gap: 12px; flex-shrink: 0;
}
.{$p}-header-icon {
  display: inline-flex; align-items: center; justify-content: center;
  width: 36px; height: 36px; text-decoration: none;
}

CSS;
    }

    // --- Editorial ---
    private static function css_editorial(string $p): string
    {
        return <<<CSS
.{$p}-header--editorial .{$p}-header-brand-row {
  display: flex; justify-content: center; align-items: center;
  padding: 20px clamp(16px, 3vw, 40px) 12px;
}
.{$p}-header--editorial .{$p}-brand--large .{$p}-brand-text {
  font-size: 2.25rem; letter-spacing: 0.08em; text-transform: uppercase;
}
.{$p}-header--editorial .{$p}-brand--large .{$p}-brand-img { height: 56px; }
.{$p}-header-nav-bar {
  border-top: 1px solid currentColor; border-bottom: 1px solid currentColor;
}
.{$p}-header-nav-bar .{$p}-header-inner {
  max-width: 1280px; margin: 0 auto;
  display: flex; align-items: center; justify-content: center;
  padding: 0 clamp(16px, 3vw, 40px); min-height: 44px;
  gap: 8px;
}
.{$p}-header-nav-bar .{$p}-nav { flex: 0; overflow: hidden; }
.{$p}-search-toggle { background: none; border: none; cursor: pointer; padding: 8px; flex-shrink: 0; }

CSS;
    }

    // ═══════════════════════════════════════
    // MOBILE CSS
    // ═══════════════════════════════════════

    private static function css_mobile(string $p, string $cssType): string
    {
        // Burger-only handles mobile in its own CSS
        if ($cssType === 'burger-only') return '';

        return <<<CSS

/* ═══ Mobile ═══ */
@media (max-width: 768px) {
  .{$p}-topbar { display: none; }
  .{$p}-header-brand-row { padding: 12px 16px 8px; }
  .{$p}-header--editorial .{$p}-brand--large .{$p}-brand-text { font-size: 1.5rem; }
  .{$p}-header-nav-bar .{$p}-header-inner { min-height: 40px; }
  .{$p}-header-inner { height: auto; min-height: 56px; padding: 8px 16px; }
  .{$p}-header--stacked .{$p}-header-inner { padding: 8px 16px; }
  .{$p}-header-brand-row + .{$p}-header-nav-row { display: none; }
  .{$p}-header-phone { display: none; }
  .{$p}-search-bar { display: none; }
  .{$p}-header-icons { gap: 8px; }

  .{$p}-nav {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    display: flex !important; flex-direction: column;
    align-items: center; justify-content: center;
    background: var(--surface, #fff);
    transform: translateX(100%);
    transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 999; overflow-y: auto;
  }
  body.nav-open .{$p}-nav { transform: translateX(0); }
  .{$p}-nav-list { flex-direction: column; gap: 8px; text-align: center; }
  .{$p}-nav-link { font-size: 1.125rem; padding: 12px 24px; }
  .{$p}-burger { display: flex; }

  .{$p}-header--brand-center .{$p}-brand { order: 0; position: static; transform: none; }
  .{$p}-header--brand-center .{$p}-nav { order: 0; flex: 0; }
  .{$p}-header--brand-center .{$p}-header-actions { order: 0; flex: 0; }
  .{$p}-header--nav-center .{$p}-nav { justify-content: flex-end; }
}

CSS;
    }

    // ═══════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════

    private static function findPattern(string $id): ?array
    {
        foreach (self::$patterns as $p) {
            if ($p['id'] === $id) return $p;
        }
        return null;
    }

    private static function getIndustryCTA(string $industry): string
    {
        $industry = strtolower(str_replace(' ', '-', $industry));
        $map = [
            'restaurant'=>'Reserve a Table','cafe'=>'Order Online','bakery'=>'Order Now',
            'bar'=>'Make a Reservation','hotel'=>'Book a Room','resort'=>'Book Your Stay',
            'spa'=>'Book Treatment','law-firm'=>'Free Consultation','attorney'=>'Schedule Consultation',
            'medical'=>'Book Appointment','dental'=>'Book Appointment','hospital'=>'Find a Doctor',
            'veterinary'=>'Book Visit','pharmacy'=>'Order Online','construction'=>'Get a Quote',
            'plumber'=>'Call Now','electrician'=>'Get a Quote','hvac'=>'Schedule Service',
            'roofing'=>'Free Estimate','photography'=>'View Portfolio','videography'=>'Watch Reel',
            'real-estate'=>'View Listings','saas'=>'Start Free Trial','startup'=>'Get Started',
            'ecommerce'=>'Shop Now','fashion'=>'Shop Collection','fitness'=>'Join Now',
            'gym'=>'Start Training','education'=>'Enroll Now','university'=>'Apply Now',
            'accounting'=>'Get a Quote','consulting'=>'Book a Call','agency'=>"Let's Talk",
            'portfolio'=>'Hire Me','blog'=>'Subscribe','magazine'=>'Subscribe',
            'nonprofit'=>'Donate Now','wedding'=>'Plan Your Day','travel'=>'Explore Trips',
            'automotive'=>'Schedule Service','insurance'=>'Get a Quote','winery'=>'Visit Us',
            'brewery'=>'Tap List','catering'=>'Get a Quote','food-truck'=>'Find Us',
            'interior-design'=>'View Projects','architecture'=>'Our Work',
            'beauty'=>'Book Now','jewelry'=>'Shop Collection','florist'=>'Order Flowers',
            'event-planning'=>'Plan Your Event','tech'=>'Get Started','digital'=>'Get Started',
            'ai'=>'Try It Free','fintech'=>'Open Account','clinic'=>'Book Visit',
            'healthcare'=>'Find Care','influencer'=>'Collaborate','content-creator'=>'Work With Me',
            'podcast'=>'Listen Now','youtube'=>'Subscribe','music'=>'Listen Now',
            'entertainment'=>'Get Tickets','media'=>'Subscribe','tourism'=>'Plan Trip',
            'airline'=>'Book Flight','cruise'=>'View Cruises','car-rental'=>'Reserve Now',
            'luxury'=>'Explore','fine-dining'=>'Reserve','yacht'=>'Charter Now',
            'country-club'=>'Join Us','golf'=>'Book Tee Time','platform'=>'Get Started',
            'directory'=>'Browse','portal'=>'Log In','dashboard'=>'Start Free',
            'craft'=>'Shop Handmade','artisan'=>'Our Craft','handmade'=>'Shop Now',
            'organic'=>'Shop Organic','farm'=>'Shop Farm','vineyard'=>'Visit Us',
            'distillery'=>'Tour & Taste','wedding-venue'=>'Tour Venue','estate'=>'Visit',
            'boutique-hotel'=>'Book Stay','extreme-sports'=>'Join Adventure',
            'adventure'=>'Book Trip','outdoor'=>'Explore','camping'=>'Book Site',
            'surfing'=>'Book Lesson','climbing'=>'Start Climbing',
            'personal-blog'=>'Subscribe','writer'=>'Read More','author'=>'Latest Book',
            'journalist'=>'Follow','coach'=>'Book Session','therapist'=>'Book Session',
            'counselor'=>'Get Help','architect'=>'View Work','industrial-design'=>'Portfolio',
            'product-design'=>'Case Studies','minimalist'=>'Explore',
            'art-gallery'=>'Current Exhibition','exhibition'=>'Visit','print'=>'Shop Prints',
            'bookshop'=>'Browse Books','crossfit'=>'Join Box','martial-arts'=>'Start Training',
            'sports'=>'Join Team','esports'=>'Watch Live','gaming'=>'Play Now',
            'nightclub'=>'Guest List','lounge'=>'Reserve Table','concert'=>'Get Tickets',
            'festival'=>'Buy Passes','event'=>'Get Tickets','promoter'=>'Upcoming Events',
            'creative-portfolio'=>'View Work','artist'=>'Gallery','illustrator'=>'Commission',
            'motion-design'=>'Showreel','3d'=>'Portfolio','vfx'=>'Our Work',
            'notary'=>'Book Appointment','financial-advisor'=>'Free Review',
            'wealth-management'=>'Get Started','cpa'=>'Free Consultation','tax'=>'File Now',
            'pest-control'=>'Get Estimate','locksmith'=>'Call Now','towing'=>'Call Now',
            'emergency-service'=>'Call Now','online-store'=>'Shop Now',
            'fashion-store'=>'New Arrivals','electronics'=>'Shop Deals',
            'furniture'=>'Browse Collection','home-decor'=>'Shop Decor',
            'pet-store'=>'Shop Now','bookstore'=>'Browse Books',
            'newspaper'=>'Subscribe','news'=>'Read Now','publication'=>'Subscribe',
            'journal'=>'Latest Issue','review-site'=>'Read Reviews',
            'school'=>'Apply Now','library'=>'Browse Catalog','museum'=>'Plan Visit',
            'gallery'=>'Current Shows','cultural-center'=>'Events','charity'=>'Donate',
            'manufacturing'=>'Request Quote','logistics'=>'Get Quote',
            'insurance'=>'Get Quote','business'=>'Contact Us',
            'blockchain'=>'Get Started','marketplace'=>'Browse',
            'fashion-brand'=>'Shop Now','cosmetics'=>'Shop Beauty','supplements'=>'Shop Now',
        ];
        return $map[$industry] ?? 'Get Started';
    }
}
