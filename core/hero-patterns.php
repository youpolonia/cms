<?php
/**
 * Hero Section Pattern Registry
 * 
 * Pre-built hero HTML layouts with structural CSS.
 * AI generates only decorative CSS (colors, fonts, effects) — never the structure.
 * 
 * 35 patterns across 11 groups.
 * @since 2026-02-19
 */

class HeroPatternRegistry
{
    // ═══════════════════════════════════════
    // PATTERN DEFINITIONS
    // ═══════════════════════════════════════

    private static array $patterns = [
        // --- Centered (text center, bg image) ---
        ['id'=>'centered',          'group'=>'centered',    'css_type'=>'centered',
         'best_for'=>['restaurant','bakery','cafe','bar','hotel','resort','spa','wedding','florist','winery','brewery',
                      'fine-dining','luxury','country-club','event-planning','catering']],
        ['id'=>'centered-video',    'group'=>'centered',    'css_type'=>'centered',
         'best_for'=>['music','entertainment','film','gaming','nightclub','festival','concert']],
        ['id'=>'centered-minimal',  'group'=>'centered',    'css_type'=>'centered-minimal',
         'best_for'=>['architecture','interior-design','gallery','museum','photography','art','fashion']],

        // --- Split (text left, image/visual right) ---
        ['id'=>'split-image',       'group'=>'split',       'css_type'=>'split',
         'best_for'=>['saas','tech','startup','app','digital','fintech','ai','blockchain','platform',
                      'ecommerce','marketplace','agency','consulting']],
        ['id'=>'split-cards',       'group'=>'split',       'css_type'=>'split',
         'best_for'=>['healthcare','clinic','hospital','dental','pharmacy','insurance','accounting',
                      'legal','financial','bank']],
        ['id'=>'split-reverse',     'group'=>'split',       'css_type'=>'split-reverse',
         'best_for'=>['education','university','school','library','nonprofit','charity','coaching']],

        // --- Fullwidth (dramatic full-screen) ---
        ['id'=>'fullscreen',        'group'=>'fullwidth',   'css_type'=>'fullscreen',
         'best_for'=>['travel','tourism','adventure','outdoor','fitness','sports','real-estate','construction','landscaping']],
        ['id'=>'fullscreen-stats',  'group'=>'fullwidth',   'css_type'=>'fullscreen',
         'best_for'=>['manufacturing','logistics','engineering','paving','roofing','plumbing','electrical','hvac']],

        // --- Creative (unique layouts) ---
        ['id'=>'editorial',         'group'=>'creative',    'css_type'=>'editorial',
         'best_for'=>['magazine','blog','news','media','podcast','influencer','content-creator','youtube']],
        ['id'=>'gradient-wave',     'group'=>'creative',    'css_type'=>'gradient-wave',
         'best_for'=>['creative-agency','design','branding','marketing','social-media','seo','web-design']],
        // --- Slider (animated text/bg transitions) ---
        ['id'=>'slider-fade',          'group'=>'slider',    'css_type'=>'slider-fade',
         'best_for'=>['automotive','security','moving','storage','towing','taxi','courier']],
        ['id'=>'slider-text',          'group'=>'slider',    'css_type'=>'slider-text',
         'best_for'=>['pet','veterinary','aquarium','zoo','farm','ranch','vineyard']],
        ['id'=>'slider-kenburns',      'group'=>'slider',    'css_type'=>'slider-kenburns',
         'best_for'=>['theater','dance','music-school','event-venue','concert','festival']],

        // --- Mosaic (multi-image layouts) ---
        ['id'=>'mosaic-grid',          'group'=>'mosaic',    'css_type'=>'mosaic-grid',
         'best_for'=>['furniture','appliance','grocery','organic','printing','signage']],
        ['id'=>'mosaic-collage',       'group'=>'mosaic',    'css_type'=>'mosaic-collage',
         'best_for'=>['tattoo','salon','beauty','cosmetics','barbershop']],
        ['id'=>'mosaic-masonry',       'group'=>'mosaic',    'css_type'=>'mosaic-masonry',
         'best_for'=>['jewelry','watch','tailor','laundry','fashion']],

        // --- Interactive (CSS animations) ---
        ['id'=>'interactive-typed',    'group'=>'interactive','css_type'=>'interactive-typed',
         'best_for'=>['tutoring','translation','driving-school','coworking','recruitment']],
        ['id'=>'interactive-counter',  'group'=>'interactive','css_type'=>'interactive-counter',
         'best_for'=>['staffing','pr','cleaning','pest-control','locksmith']],
        ['id'=>'interactive-parallax', 'group'=>'interactive','css_type'=>'interactive-parallax',
         'best_for'=>['surfing','ski','golf','tennis','swimming','cycling']],

        // --- Split Advanced (enhanced split layouts) ---
        ['id'=>'split-diagonal',       'group'=>'split-advanced','css_type'=>'split-diagonal',
         'best_for'=>['yoga','meditation','martial-arts','boxing','dance']],
        ['id'=>'split-overlap',        'group'=>'split-advanced','css_type'=>'split-overlap',
         'best_for'=>['childcare','daycare','senior-care','home-care']],
        ['id'=>'split-with-features',  'group'=>'split-advanced','css_type'=>'split-with-features',
         'best_for'=>['supplements','vitamins','cbd','cannabis','vape']],
        ['id'=>'split-testimonial',    'group'=>'split-advanced','css_type'=>'split-testimonial',
         'best_for'=>['ice-cream','coffee','tea','juice','food-truck']],

        // --- Card (card-based hero layouts) ---
        ['id'=>'card-glass',           'group'=>'card',      'css_type'=>'card-glass',
         'best_for'=>['nightclub','bar','brewery','winery','cocktail']],
        ['id'=>'card-floating',        'group'=>'card',      'css_type'=>'card-floating',
         'best_for'=>['creative-agency','design','branding','web-design']],
        ['id'=>'card-product',         'group'=>'card',      'css_type'=>'card-product',
         'best_for'=>['ecommerce','marketplace','grocery','organic']],

        // --- Minimal Advanced ---
        ['id'=>'minimal-text-only',    'group'=>'minimal-advanced','css_type'=>'minimal-text-only',
         'best_for'=>['architecture','interior-design','gallery','museum']],
        ['id'=>'minimal-underline',    'group'=>'minimal-advanced','css_type'=>'minimal-underline',
         'best_for'=>['photography','art','fashion','design']],
        ['id'=>'minimal-sidebar',      'group'=>'minimal-advanced','css_type'=>'minimal-sidebar',
         'best_for'=>['coworking','recruitment','staffing','consulting']],

        // --- Dramatic ---
        ['id'=>'dramatic-dark',        'group'=>'dramatic',  'css_type'=>'dramatic-dark',
         'best_for'=>['gaming','nightclub','tattoo','barbershop','music']],
        ['id'=>'dramatic-split-screen','group'=>'dramatic',  'css_type'=>'dramatic-split-screen',
         'best_for'=>['fashion','cosmetics','jewelry','watch','luxury']],
        ['id'=>'dramatic-spotlight',   'group'=>'dramatic',  'css_type'=>'dramatic-spotlight',
         'best_for'=>['theater','dance','concert','festival','entertainment']],

        // --- Business ---
        ['id'=>'business-classic',     'group'=>'business',  'css_type'=>'business-classic',
         'best_for'=>['legal','financial','bank','insurance','accounting']],
        ['id'=>'business-search',      'group'=>'business',  'css_type'=>'business-search',
         'best_for'=>['real-estate','marketplace','directory','job-board']],
        ['id'=>'business-appointment', 'group'=>'business',  'css_type'=>'business-appointment',
         'best_for'=>['dental','clinic','salon','barbershop','spa']],
    ];

    // ═══════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════

    /**
     * Pick the best hero pattern for an industry.
     */
    public static function pickPattern(string $industry): string
    {
        $industry = strtolower(trim($industry));
        foreach (self::$patterns as $p) {
            if (in_array($industry, $p['best_for'], true)) {
                return $p['id'];
            }
        }
        // Fallback: random from split group (most versatile)
        $splitPatterns = array_filter(self::$patterns, fn($p) => $p['group'] === 'split');
        $splitIds = array_column(array_values($splitPatterns), 'id');
        return $splitIds[array_rand($splitIds)];
    }

    /**
     * Render a hero pattern.
     * Returns ['html'=>..., 'structural_css'=>..., 'pattern_id'=>..., 'classes'=>[...], 'fields'=>[...]]
     */
    public static function render(string $patternId, string $prefix, array $brief): array
    {
        $def = null;
        foreach (self::$patterns as $p) {
            if ($p['id'] === $patternId) { $def = $p; break; }
        }
        if (!$def) {
            $def = self::$patterns[0]; // fallback to centered
            $patternId = $def['id'];
        }

        $p = $prefix; // short alias for templates
        $html = self::buildHTML($patternId, $p);

        // Replace generic placeholders with brief content
        $html = self::injectBriefContent($html, $brief);

        $css = self::buildStructuralCSS($def['css_type'], $p);
        $classes = self::getClasses($patternId, $p);

        return [
            'html'           => $html,
            'structural_css' => $css,
            'pattern_id'     => $patternId,
            'classes'        => $classes,
            'fields'         => self::getFields($patternId),
        ];
    }

    /**
     * Get all pattern IDs and names (for UI/wizard).
     */
    public static function getPatternList(): array
    {
        return array_map(fn($p) => [
            'id'    => $p['id'],
            'group' => $p['group'],
            'label' => ucwords(str_replace('-', ' ', $p['id'])),
        ], self::$patterns);
    }

    /**
     * Get schema fields for a pattern (for Visual Editor Content tab).
     */
    public static function getFields(string $patternId): array
    {
        // Common fields all heroes have
        $common = [
            'badge'     => ['type' => 'text',     'label' => 'Badge / Label'],
            'headline'  => ['type' => 'text',     'label' => 'Headline'],
            'subtitle'  => ['type' => 'textarea', 'label' => 'Subtitle'],
            'btn_text'  => ['type' => 'text',     'label' => 'Button Text'],
            'btn_link'  => ['type' => 'text',     'label' => 'Button Link'],
            'bg_image'  => ['type' => 'image',    'label' => 'Background Image'],
        ];

        // Pattern-specific extras
        $extras = match($patternId) {
            'centered', 'centered-video', 'fullscreen', 'fullscreen-stats' => [
                'btn2_text' => ['type' => 'text', 'label' => 'Secondary Button'],
                'btn2_link' => ['type' => 'text', 'label' => 'Secondary Button Link'],
            ],
            'split-image', 'split-cards', 'split-reverse' => [
                'image'     => ['type' => 'image', 'label' => 'Side Image'],
                'btn2_text' => ['type' => 'text',  'label' => 'Secondary Button'],
                'btn2_link' => ['type' => 'text',  'label' => 'Secondary Button Link'],
            ],
            'fullscreen-stats' => [
                'stat1_number' => ['type' => 'text', 'label' => 'Stat 1 Number'],
                'stat1_label'  => ['type' => 'text', 'label' => 'Stat 1 Label'],
                'stat2_number' => ['type' => 'text', 'label' => 'Stat 2 Number'],
                'stat2_label'  => ['type' => 'text', 'label' => 'Stat 2 Label'],
                'stat3_number' => ['type' => 'text', 'label' => 'Stat 3 Number'],
                'stat3_label'  => ['type' => 'text', 'label' => 'Stat 3 Label'],
                'stat4_number' => ['type' => 'text', 'label' => 'Stat 4 Number'],
                'stat4_label'  => ['type' => 'text', 'label' => 'Stat 4 Label'],
            ],
            'editorial' => [
                'category' => ['type' => 'text', 'label' => 'Category Label'],
                'author'   => ['type' => 'text', 'label' => 'Author Name'],
                'image'    => ['type' => 'image', 'label' => 'Feature Image'],
            ],
            'gradient-wave' => [
                'btn2_text' => ['type' => 'text', 'label' => 'Secondary Button'],
                'btn2_link' => ['type' => 'text', 'label' => 'Secondary Button Link'],
            ],
            'slider-fade' => [
                'btn2_text' => ['type' => 'text', 'label' => 'Secondary Button'],
                'btn2_link' => ['type' => 'text', 'label' => 'Secondary Button Link'],
                'line2'     => ['type' => 'text', 'label' => 'Fade Line 2'],
                'line3'     => ['type' => 'text', 'label' => 'Fade Line 3'],
            ],
            'slider-text' => [
                'rotate_word1' => ['type' => 'text', 'label' => 'Rotating Word 1'],
                'rotate_word2' => ['type' => 'text', 'label' => 'Rotating Word 2'],
                'rotate_word3' => ['type' => 'text', 'label' => 'Rotating Word 3'],
            ],
            'slider-kenburns' => [
                'btn2_text' => ['type' => 'text', 'label' => 'Secondary Button'],
                'btn2_link' => ['type' => 'text', 'label' => 'Secondary Button Link'],
            ],
            'mosaic-grid' => [
                'image'  => ['type' => 'image', 'label' => 'Image 1'],
                'image2' => ['type' => 'image', 'label' => 'Image 2'],
                'image3' => ['type' => 'image', 'label' => 'Image 3'],
                'image4' => ['type' => 'image', 'label' => 'Image 4'],
            ],
            'mosaic-collage' => [
                'image'  => ['type' => 'image', 'label' => 'Collage Image 1'],
                'image2' => ['type' => 'image', 'label' => 'Collage Image 2'],
                'image3' => ['type' => 'image', 'label' => 'Collage Image 3'],
            ],
            'mosaic-masonry' => [
                'image'  => ['type' => 'image', 'label' => 'Masonry Image 1'],
                'image2' => ['type' => 'image', 'label' => 'Masonry Image 2'],
                'image3' => ['type' => 'image', 'label' => 'Masonry Image 3'],
            ],
            'interactive-typed' => [
                'typed_word' => ['type' => 'text', 'label' => 'Typed Word'],
            ],
            'interactive-counter' => [
                'stat1_number' => ['type' => 'text', 'label' => 'Counter 1 Number'],
                'stat1_label'  => ['type' => 'text', 'label' => 'Counter 1 Label'],
                'stat2_number' => ['type' => 'text', 'label' => 'Counter 2 Number'],
                'stat2_label'  => ['type' => 'text', 'label' => 'Counter 2 Label'],
                'stat3_number' => ['type' => 'text', 'label' => 'Counter 3 Number'],
                'stat3_label'  => ['type' => 'text', 'label' => 'Counter 3 Label'],
            ],
            'interactive-parallax' => [
                'btn2_text' => ['type' => 'text', 'label' => 'Secondary Button'],
                'btn2_link' => ['type' => 'text', 'label' => 'Secondary Button Link'],
            ],
            'split-diagonal' => [
                'image' => ['type' => 'image', 'label' => 'Diagonal Image'],
            ],
            'split-overlap' => [
                'image' => ['type' => 'image', 'label' => 'Side Image'],
            ],
            'split-with-features' => [
                'image'      => ['type' => 'image', 'label' => 'Side Image'],
                'feat1_title' => ['type' => 'text', 'label' => 'Feature 1 Title'],
                'feat1_desc'  => ['type' => 'text', 'label' => 'Feature 1 Description'],
                'feat2_title' => ['type' => 'text', 'label' => 'Feature 2 Title'],
                'feat2_desc'  => ['type' => 'text', 'label' => 'Feature 2 Description'],
                'feat3_title' => ['type' => 'text', 'label' => 'Feature 3 Title'],
                'feat3_desc'  => ['type' => 'text', 'label' => 'Feature 3 Description'],
            ],
            'split-testimonial' => [
                'image'        => ['type' => 'image', 'label' => 'Side Image'],
                'quote'        => ['type' => 'textarea', 'label' => 'Testimonial Quote'],
                'quote_author' => ['type' => 'text', 'label' => 'Quote Author'],
            ],
            'card-glass' => [],
            'card-floating' => [
                'card1_text' => ['type' => 'text', 'label' => 'Card 1 Text'],
                'card2_text' => ['type' => 'text', 'label' => 'Card 2 Text'],
                'card3_text' => ['type' => 'text', 'label' => 'Card 3 Text'],
            ],
            'card-product' => [
                'image'       => ['type' => 'image', 'label' => 'Product Image'],
                'price'       => ['type' => 'text',  'label' => 'Price'],
                'price_label' => ['type' => 'text',  'label' => 'Price Label'],
            ],
            'minimal-text-only' => [],
            'minimal-underline' => [],
            'minimal-sidebar' => [
                'image' => ['type' => 'image', 'label' => 'Side Image'],
                'nav1'  => ['type' => 'text',  'label' => 'Sidebar Nav 1'],
                'nav2'  => ['type' => 'text',  'label' => 'Sidebar Nav 2'],
                'nav3'  => ['type' => 'text',  'label' => 'Sidebar Nav 3'],
            ],
            'dramatic-dark' => [],
            'dramatic-split-screen' => [
                'image' => ['type' => 'image', 'label' => 'Right Side Image'],
            ],
            'dramatic-spotlight' => [],
            'business-classic' => [
                'image'  => ['type' => 'image', 'label' => 'Side Image'],
                'btn2_text' => ['type' => 'text', 'label' => 'Secondary Button'],
                'btn2_link' => ['type' => 'text', 'label' => 'Secondary Button Link'],
                'trust1' => ['type' => 'text', 'label' => 'Trust Badge 1'],
                'trust2' => ['type' => 'text', 'label' => 'Trust Badge 2'],
                'trust3' => ['type' => 'text', 'label' => 'Trust Badge 3'],
            ],
            'business-search' => [
                'search_placeholder' => ['type' => 'text', 'label' => 'Search Placeholder'],
            ],
            'business-appointment' => [
                'form_title' => ['type' => 'text', 'label' => 'Form Title'],
            ],
            default => [],
        };

        return array_merge($common, $extras);
    }

    /**
     * Get pattern-specific decorative CSS guide for Step 3 AI prompt.
     */
    public static function getDecorativeGuide(string $patternId): string
    {
        return match($patternId) {
            'centered' => <<<'G'
- Full-bleed bg with heavy overlay gradient (135deg, 0.85→0.4 opacity)
- Strong text-shadow: 0 2px 30px rgba(0,0,0,0.4) for readability
- Scroll-down indicator: bounce animation (translateY 0→8px), opacity 0.6
- Buttons: primary solid + outline ghost, pill border-radius
G,
            'centered-video' => <<<'G'
- Darker overlay than centered (0.9→0.5) for video contrast
- Play button glow: box-shadow 0 0 40px rgba(primary,0.4), pulse animation
- Video: object-fit cover, filter brightness(0.8) when paused
G,
            'centered-minimal' => <<<'G'
- NO heavy overlay, subtle or no bg image, whitespace-heavy
- Thin typography: font-weight 300-400, letter-spacing -0.03em
- Minimal color: mostly var(--text), primary only on buttons
- Badge: thin border only, refined uppercase
G,
            'split-image' => <<<'G'
- Image: border-radius var(--radius-lg), box-shadow 0 20px 60px rgba(0,0,0,0.15)
- Image hover: scale(1.02), transition 0.6s ease
- Decorative accent shape near image: primary color, opacity 0.3
G,
            'split-cards' => <<<'G'
- Floating info cards: bg var(--surface-card), box-shadow 0 10px 40px rgba(0,0,0,0.1)
- Cards: border-radius var(--radius-lg), icon + number + label
- Card hover: translateY(-4px), shadow increase
G,
            'split-reverse' => <<<'G'
- Reversed split with accent geometric decoration behind content
- Decoration: bg rgba(primary,0.08), large circle or square, low opacity
G,
            'fullscreen' => <<<'G'
- Dramatic full-viewport, heavy overlay, bold typography
- Extra large text: clamp(3rem,7vw,6rem), font-weight 800, letter-spacing -0.04em
- Strong text-shadow: 0 4px 40px rgba(0,0,0,0.5)
G,
            'fullscreen-stats' => <<<'G'
- Stat bar at bottom: bg rgba(0,0,0,0.3), backdrop-filter blur
- Numbers: font-size 2.5rem+, font-weight 700, color white
- Labels: 0.8rem, uppercase, letter-spacing 0.1em, opacity 0.8
- Separators: border-right 1px solid rgba(white,0.2)
G,
            'editorial' => <<<'G'
- Serif font-family for headline, magazine feel
- Category label: bg var(--primary), color white, pill, uppercase 0.75rem
- Author credit: italic, muted color, small
- Featured image: frame/border, subtle shadow
G,
            'gradient-wave' => <<<'G'
- Animated gradient: background-size 200%, animation gradientShift 8s infinite
- Multi-stop gradient (primary→secondary→accent), vibrant
- ⛔ DO NOT style .{prefix}-hero-wave, .{prefix}-hero-wave svg, or .{prefix}-hero-wave path — the wave fill is handled by structural CSS and MUST match var(--background) to blend with the next section
G,
            'slider-fade' => <<<'G'
- Crossfade text: opacity 0→1, transition 0.8s ease
- Nav dots: inactive rgba(white,0.3), active white, small circles
- Staggered line timing (200ms offset between lines)
G,
            'slider-text' => <<<'G'
- Rotating word: color var(--primary) or gradient background-clip
- Cursor: border-right 3px solid var(--primary), blink animation 0.7s
- Static text normal, rotating word highlighted
G,
            'slider-kenburns' => <<<'G'
- Ken Burns: scale(1.0→1.15) over 15-20s, linear timing
- Alternating scale origins per slide
- Heavy overlay 0.7+ for readability during zoom
G,
            'mosaic-grid' => <<<'G'
- Grid images: subtle 2-4px gap, varied aspect-ratios
- Hover overlay: bg rgba(primary,0.7), opacity transition
- Hover zoom: scale(1.05), overflow hidden
G,
            'mosaic-collage' => <<<'G'
- Overlapping images: box-shadow for depth between layers
- Slight rotation: rotate(±2-3deg), Polaroid border effect
- Hover: rotation→0, scale(1.05), z-index bump
G,
            'mosaic-masonry' => <<<'G'
- Varied heights, gradient overlay from bottom on hover
- Caption: color white, font-weight 600, translateY reveal
- Smooth cubic-bezier transitions
G,
            'interactive-typed' => <<<'G'
- Typing cursor: border-right 3px solid var(--primary), blink step-end animation
- Typed word: color var(--primary) or bg highlight rgba(primary,0.15)
G,
            'interactive-counter' => <<<'G'
- Counter numbers: 3rem+, font-weight 800, color var(--primary), tabular-nums
- Labels: 0.875rem, uppercase, letter-spacing 0.08em, muted
- Separators: border-right between items
G,
            'interactive-parallax' => <<<'G'
- Parallax depth via translateZ and scale on bg layer
- Multiple layers: slight blur on far layers, sharp foreground
- will-change transform on parallax elements
G,
            'split-diagonal' => <<<'G'
- Diagonal clip-path on image (~15deg angle)
- Geometric accent at division: triangle shape, primary color
- Dynamic energy from angled edge
G,
            'split-overlap' => <<<'G'
- Image overlaps content area via negative margin/translate
- Image frame: 4-6px solid white border, depth shadow
- Accent bg behind overlap: subtle primary tint
G,
            'split-with-features' => <<<'G'
- Mini feature list: small 32px icon circles, bg rgba(primary,0.1)
- Feature text: 0.875rem, muted, compact single-line
- Divider: thin border-top between hero content and feature list
G,
            'split-testimonial' => <<<'G'
- Quote marks: font-size 3rem, color primary, opacity 0.3
- Avatar: 48px circle, border 2px solid white, shadow
- Testimonial card: subtle bg, thin border, border-radius
G,
            'card-glass' => <<<'G'
- GLASSMORPHISM: backdrop-filter blur(20px), bg rgba(surface,0.15)
- Glass border: 1px solid rgba(255,255,255,0.2)
- Light reflection: gradient overlay rgba(white,0.1)→transparent
- Text: slight text-shadow for readability on glass
G,
            'card-floating' => <<<'G'
- Hover rotate3d(1,1,0,2deg), heavy shadow 0 20px 60px rgba(0,0,0,0.2)
- Cards: subtle gradient bg, staggered translateY for depth
- Hover: remove rotation, increase shadow, translateY(-8px)
G,
            'card-product' => <<<'G'
- Price badge: bg var(--primary), color white, border-radius, font-weight 700
- Product image: clean white bg, centered
- Sale ribbon: absolute positioned, rotated, accent color
G,
            'minimal-text-only' => <<<'G'
- NO bg image, solid bg, typography-only hero
- Font: clamp(3rem,8vw,7rem), weight 200-300, letter-spacing -0.05em
- Whitespace-heavy, decorative line: thin hr, primary color, 60-80px width
G,
            'minimal-underline' => <<<'G'
- Decorative underline: border-bottom 3px solid var(--primary) on headline
- Partial width underline (60%) for design interest
- Clean editorial feel, restrained palette
G,
            'minimal-sidebar' => <<<'G'
- Side nav: vertical anchor links, hover bg rgba(primary,0.05)
- Active: left border 3px solid var(--primary)
- Nav items: 0.875rem, muted, table-of-contents feel
G,
            'dramatic-dark' => <<<'G'
- Deep dark bg: #0a0a0a, NOT grey
- High contrast white text: #f8fafc
- Spotlight glow: radial-gradient(ellipse, rgba(primary,0.15), transparent 70%)
- Neon text-shadow: 0 0 80px rgba(primary,0.3)
G,
            'dramatic-split-screen' => <<<'G'
- Hard vertical split: left dark, right full image
- Clean straight division, no diagonal
- Optional accent border between halves: 2-3px primary
G,
            'dramatic-spotlight' => <<<'G'
- Radial gradient spotlight: rgba(primary,0.2) at center→transparent
- Vignette: box-shadow inset 0 0 200px rgba(0,0,0,0.5)
- Theater lighting feel: bright center, dark edges
G,
            'business-classic' => <<<'G'
- Trust badges: small icons with subtle bg, flex-wrap, uniform size
- Conservative: solid colors, moderate border-radius, professional
- Subtle bg pattern: repeating-linear-gradient at very low opacity
G,
            'business-search' => <<<'G'
- Search bar: large input, border 2px solid var(--border), border-radius var(--radius-lg)
- Focus: border-color var(--primary), box-shadow 0 0 0 4px rgba(primary,0.15)
- Search icon: inside input (position absolute), muted color
G,
            'business-appointment' => <<<'G'
- Form card: bg var(--surface-card), border-radius var(--radius-lg), shadow 0 10px 40px rgba(0,0,0,0.1)
- Fields: clean input styling, label above, consistent spacing
- Submit: full-width within card, primary bg, prominent
G,
            default => '',
        };
    }

    // ═══════════════════════════════════════
    // HTML TEMPLATES
    // ═══════════════════════════════════════

    /**
     * Replace generic placeholder defaults in hero template with actual brief content.
     * This ensures the PHP file has meaningful defaults even before DB values are saved.
     */
    private static function injectBriefContent(string $html, array $brief): string
    {
        $name = $brief['name'] ?? '';
        $industry = $brief['industry'] ?? '';
        $description = $brief['description'] ?? '';

        // Hero headline: use brief's hero_headline, or generate from name
        $headline = $brief['hero_headline'] ?? '';
        if (!$headline && $name) {
            $headline = $name;
        }

        // Hero subtitle: use brief's hero_subheadline, or description
        $subtitle = $brief['hero_subheadline'] ?? '';
        if (!$subtitle && $description) {
            $subtitle = $description;
        }

        // Hero badge: use industry or a short tagline
        $badge = '';
        if (!empty($brief['style_preset'])) {
            $badge = ucwords(str_replace('-', ' ', $brief['style_preset']));
        } elseif ($industry) {
            $badge = ucwords(str_replace('-', ' ', $industry));
        }

        // CTA button text from brief
        $btnText = $brief['cta_text'] ?? '';

        // Replace defaults in theme_get() calls
        $replacements = [];
        if ($headline)  $replacements["theme_get('hero.headline', 'Your Headline Here')"] = "theme_get('hero.headline', '" . addslashes($headline) . "')";
        if ($subtitle)  $replacements["theme_get('hero.subtitle', 'A compelling description of your business or service.')"] = "theme_get('hero.subtitle', '" . addslashes($subtitle) . "')";
        if ($badge)     $replacements["theme_get('hero.badge', '')"] = "theme_get('hero.badge', '" . addslashes($badge) . "')";
        if ($btnText)   $replacements["theme_get('hero.btn_text', 'Get Started')"] = "theme_get('hero.btn_text', '" . addslashes($btnText) . "')";

        foreach ($replacements as $search => $replace) {
            $html = str_replace($search, $replace, $html);
        }

        return $html;
    }

    private static function buildHTML(string $patternId, string $p): string
    {
        $templates = self::getTemplates($p);
        return $templates[$patternId] ?? $templates['centered'];
    }

    private static function getTemplates(string $p): array
    {
        return [

// ── Centered: Classic centered hero with bg image ──
'centered' => <<<HTML
<?php
\$heroBgImage = theme_get('hero.bg_image', '');
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroBtn2Text = theme_get('hero.btn2_text', '');
\$heroBtn2Link = theme_get('hero.btn2_link', '#about');
?>
<section class="{$p}-hero {$p}-hero--centered" id="hero">
  <div class="{$p}-hero-bg" style="background-image: url('<?= esc(\$heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
  <div class="{$p}-hero-overlay"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
        <?php if (\$heroBtn2Text): ?><a href="<?= esc(\$heroBtn2Link) ?>" class="{$p}-btn {$p}-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc(\$heroBtn2Text) ?></a><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="{$p}-hero-scroll"><a href="#next-section" aria-label="Scroll down"><i class="fas fa-chevron-down"></i></a></div>
</section>
HTML,

// ── Centered Video: Like centered but with video background ──
'centered-video' => <<<HTML
<?php
\$heroBgImage = theme_get('hero.bg_image', '');
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroBtn2Text = theme_get('hero.btn2_text', '');
\$heroBtn2Link = theme_get('hero.btn2_link', '#about');
?>
<section class="{$p}-hero {$p}-hero--centered {$p}-hero--video" id="hero">
  <div class="{$p}-hero-bg" style="background-image: url('<?= esc(\$heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
  <div class="{$p}-hero-overlay"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
        <?php if (\$heroBtn2Text): ?><a href="<?= esc(\$heroBtn2Link) ?>" class="{$p}-btn {$p}-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc(\$heroBtn2Text) ?></a><?php endif; ?>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Centered Minimal: Clean, no overlay, just text on subtle bg ──
'centered-minimal' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Explore');
\$heroBtnLink = theme_get('hero.btn_link', '/portfolio');
?>
<section class="{$p}-hero {$p}-hero--minimal" id="hero">
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Split Image: Text left, image right ──
'split-image' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroBtn2Text = theme_get('hero.btn2_text', 'Learn More');
\$heroBtn2Link = theme_get('hero.btn2_link', '#features');
\$heroImage = theme_get('hero.image', '');
?>
<section class="{$p}-hero {$p}-hero--split" id="hero">
  <div class="container">
    <div class="{$p}-hero-grid">
      <div class="{$p}-hero-content" data-animate="fade-right">
        <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
        <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
        <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
        <div class="{$p}-hero-actions">
          <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
          <?php if (\$heroBtn2Text): ?><a href="<?= esc(\$heroBtn2Link) ?>" class="{$p}-btn {$p}-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc(\$heroBtn2Text) ?></a><?php endif; ?>
        </div>
      </div>
      <div class="{$p}-hero-visual" data-animate="fade-left">
        <img src="<?= esc(\$heroImage) ?>" alt="<?= esc(\$heroHeadline) ?>" class="{$p}-hero-image" data-ts-bg="hero.image" loading="eager">
      </div>
    </div>
  </div>
</section>
HTML,

// ── Split Cards: Text left, floating feature cards right ──
'split-cards' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroBtn2Text = theme_get('hero.btn2_text', 'Learn More');
\$heroBtn2Link = theme_get('hero.btn2_link', '#features');
\$heroImage = theme_get('hero.image', '');
?>
<section class="{$p}-hero {$p}-hero--split {$p}-hero--cards" id="hero">
  <div class="container">
    <div class="{$p}-hero-grid">
      <div class="{$p}-hero-content" data-animate="fade-right">
        <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
        <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
        <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
        <div class="{$p}-hero-actions">
          <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
          <?php if (\$heroBtn2Text): ?><a href="<?= esc(\$heroBtn2Link) ?>" class="{$p}-btn {$p}-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc(\$heroBtn2Text) ?></a><?php endif; ?>
        </div>
      </div>
      <div class="{$p}-hero-visual" data-animate="fade-left">
        <img src="<?= esc(\$heroImage) ?>" alt="<?= esc(\$heroHeadline) ?>" class="{$p}-hero-image" data-ts-bg="hero.image" loading="eager">
        <div class="{$p}-hero-float-card {$p}-hero-float-1">
          <i class="fas fa-shield-alt"></i>
          <span>Trusted by 1000+</span>
        </div>
        <div class="{$p}-hero-float-card {$p}-hero-float-2">
          <i class="fas fa-star"></i>
          <span>5-Star Rated</span>
        </div>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Split Reverse: Image left, text right ──
'split-reverse' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroBtn2Text = theme_get('hero.btn2_text', 'Learn More');
\$heroBtn2Link = theme_get('hero.btn2_link', '#about');
\$heroImage = theme_get('hero.image', '');
?>
<section class="{$p}-hero {$p}-hero--split {$p}-hero--reverse" id="hero">
  <div class="container">
    <div class="{$p}-hero-grid">
      <div class="{$p}-hero-visual" data-animate="fade-right">
        <img src="<?= esc(\$heroImage) ?>" alt="<?= esc(\$heroHeadline) ?>" class="{$p}-hero-image" data-ts-bg="hero.image" loading="eager">
      </div>
      <div class="{$p}-hero-content" data-animate="fade-left">
        <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
        <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
        <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
        <div class="{$p}-hero-actions">
          <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
          <?php if (\$heroBtn2Text): ?><a href="<?= esc(\$heroBtn2Link) ?>" class="{$p}-btn {$p}-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc(\$heroBtn2Text) ?></a><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Fullscreen: Dramatic full-viewport bg image ──
'fullscreen' => <<<HTML
<?php
\$heroBgImage = theme_get('hero.bg_image', '');
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Explore');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroBtn2Text = theme_get('hero.btn2_text', '');
\$heroBtn2Link = theme_get('hero.btn2_link', '#about');
?>
<section class="{$p}-hero {$p}-hero--fullscreen" id="hero">
  <div class="{$p}-hero-bg" style="background-image: url('<?= esc(\$heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
  <div class="{$p}-hero-overlay"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
        <?php if (\$heroBtn2Text): ?><a href="<?= esc(\$heroBtn2Link) ?>" class="{$p}-btn {$p}-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc(\$heroBtn2Text) ?></a><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="{$p}-hero-scroll"><a href="#next-section" aria-label="Scroll down"><i class="fas fa-chevron-down"></i></a></div>
</section>
HTML,

// ── Fullscreen Stats: Full-viewport with stats bar at bottom ──
'fullscreen-stats' => <<<HTML
<?php
\$heroBgImage = theme_get('hero.bg_image', '');
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get a Quote');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroBtn2Text = theme_get('hero.btn2_text', 'Our Work');
\$heroBtn2Link = theme_get('hero.btn2_link', '/portfolio');
\$stat1Num = theme_get('hero.stat1_number', '500+');
\$stat1Lab = theme_get('hero.stat1_label', 'Projects');
\$stat2Num = theme_get('hero.stat2_number', '25+');
\$stat2Lab = theme_get('hero.stat2_label', 'Years');
\$stat3Num = theme_get('hero.stat3_number', '98%');
\$stat3Lab = theme_get('hero.stat3_label', 'Satisfaction');
\$stat4Num = theme_get('hero.stat4_number', '50+');
\$stat4Lab = theme_get('hero.stat4_label', 'Team Members');
?>
<section class="{$p}-hero {$p}-hero--fullscreen {$p}-hero--stats" id="hero">
  <div class="{$p}-hero-bg" style="background-image: url('<?= esc(\$heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
  <div class="{$p}-hero-overlay"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
        <?php if (\$heroBtn2Text): ?><a href="<?= esc(\$heroBtn2Link) ?>" class="{$p}-btn {$p}-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc(\$heroBtn2Text) ?></a><?php endif; ?>
      </div>
    </div>
    <div class="{$p}-hero-stats" data-animate="fade-up">
      <div class="{$p}-hero-stat">
        <span class="{$p}-stat-number" data-ts="hero.stat1_number"><?= esc(\$stat1Num) ?></span>
        <span class="{$p}-stat-label" data-ts="hero.stat1_label"><?= esc(\$stat1Lab) ?></span>
      </div>
      <div class="{$p}-hero-stat">
        <span class="{$p}-stat-number" data-ts="hero.stat2_number"><?= esc(\$stat2Num) ?></span>
        <span class="{$p}-stat-label" data-ts="hero.stat2_label"><?= esc(\$stat2Lab) ?></span>
      </div>
      <div class="{$p}-hero-stat">
        <span class="{$p}-stat-number" data-ts="hero.stat3_number"><?= esc(\$stat3Num) ?></span>
        <span class="{$p}-stat-label" data-ts="hero.stat3_label"><?= esc(\$stat3Lab) ?></span>
      </div>
      <div class="{$p}-hero-stat">
        <span class="{$p}-stat-number" data-ts="hero.stat4_number"><?= esc(\$stat4Num) ?></span>
        <span class="{$p}-stat-label" data-ts="hero.stat4_label"><?= esc(\$stat4Lab) ?></span>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Editorial: Magazine-style with category, large image ──
'editorial' => <<<HTML
<?php
\$heroCategory = theme_get('hero.category', 'Featured');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroAuthor = theme_get('hero.author', '');
\$heroBtnText = theme_get('hero.btn_text', 'Read More');
\$heroBtnLink = theme_get('hero.btn_link', '#content');
\$heroImage = theme_get('hero.image', '');
?>
<section class="{$p}-hero {$p}-hero--editorial" id="hero">
  <div class="container">
    <div class="{$p}-hero-grid">
      <div class="{$p}-hero-content" data-animate="fade-right">
        <span class="{$p}-hero-category" data-ts="hero.category"><?= esc(\$heroCategory) ?></span>
        <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
        <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
        <div class="{$p}-hero-meta">
          <?php if (\$heroAuthor): ?><span class="{$p}-hero-author" data-ts="hero.author"><i class="fas fa-pen-nib"></i> <?= esc(\$heroAuthor) ?></span><?php endif; ?>
          <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?> <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
      <div class="{$p}-hero-visual" data-animate="fade-left">
        <img src="<?= esc(\$heroImage) ?>" alt="<?= esc(\$heroHeadline) ?>" class="{$p}-hero-image" data-ts-bg="hero.image" loading="eager">
      </div>
    </div>
  </div>
</section>
HTML,

// ── Gradient Wave: Abstract gradient bg with wave divider ──
'gradient-wave' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroBtn2Text = theme_get('hero.btn2_text', 'See Our Work');
\$heroBtn2Link = theme_get('hero.btn2_link', '/portfolio');
?>
<section class="{$p}-hero {$p}-hero--gradient" id="hero">
  <div class="{$p}-hero-gradient-bg"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
        <?php if (\$heroBtn2Text): ?><a href="<?= esc(\$heroBtn2Link) ?>" class="{$p}-btn {$p}-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc(\$heroBtn2Text) ?></a><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="{$p}-hero-wave">
    <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
      <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"></path>
    </svg>
  </div>
</section>
HTML,


// ── Slider Fade: Full-width bg with CSS fade animation between text lines ──
'slider-fade' => <<<HTML
<?php
\$heroBgImage = theme_get('hero.bg_image', '');
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroBtn2Text = theme_get('hero.btn2_text', '');
\$heroBtn2Link = theme_get('hero.btn2_link', '#about');
\$heroLine2 = theme_get('hero.line2', '');
\$heroLine3 = theme_get('hero.line3', '');
?>
<section class="{$p}-hero {$p}-hero--slider-fade" id="hero">
  <div class="{$p}-hero-bg" style="background-image: url('<?= esc(\$heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
  <div class="{$p}-hero-overlay"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <div class="{$p}-hero-slider-text">
        <h1 class="{$p}-hero-headline {$p}-fade-line {$p}-fade-active" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
        <?php if (\$heroLine2): ?><h1 class="{$p}-hero-headline {$p}-fade-line" data-ts="hero.line2"><?= esc(\$heroLine2) ?></h1><?php endif; ?>
        <?php if (\$heroLine3): ?><h1 class="{$p}-hero-headline {$p}-fade-line" data-ts="hero.line3"><?= esc(\$heroLine3) ?></h1><?php endif; ?>
      </div>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
        <?php if (\$heroBtn2Text): ?><a href="<?= esc(\$heroBtn2Link) ?>" class="{$p}-btn {$p}-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc(\$heroBtn2Text) ?></a><?php endif; ?>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Slider Text: Fixed bg, headline text rotates with CSS animation ──
'slider-text' => <<<HTML
<?php
\$heroBgImage = theme_get('hero.bg_image', '');
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroRotate1 = theme_get('hero.rotate_word1', 'Amazing');
\$heroRotate2 = theme_get('hero.rotate_word2', 'Beautiful');
\$heroRotate3 = theme_get('hero.rotate_word3', 'Powerful');
?>
<section class="{$p}-hero {$p}-hero--slider-text" id="hero">
  <div class="{$p}-hero-bg {$p}-hero-bg--fixed" style="background-image: url('<?= esc(\$heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
  <div class="{$p}-hero-overlay"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <div class="{$p}-hero-rotate-wrapper">
        <span class="{$p}-hero-rotate-word {$p}-rotate-active" data-ts="hero.rotate_word1"><?= esc(\$heroRotate1) ?></span>
        <span class="{$p}-hero-rotate-word" data-ts="hero.rotate_word2"><?= esc(\$heroRotate2) ?></span>
        <span class="{$p}-hero-rotate-word" data-ts="hero.rotate_word3"><?= esc(\$heroRotate3) ?></span>
      </div>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Slider Ken Burns: Slow zoom CSS animation on bg image ──
'slider-kenburns' => <<<HTML
<?php
\$heroBgImage = theme_get('hero.bg_image', '');
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Explore');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroBtn2Text = theme_get('hero.btn2_text', '');
\$heroBtn2Link = theme_get('hero.btn2_link', '#about');
?>
<section class="{$p}-hero {$p}-hero--kenburns" id="hero">
  <div class="{$p}-hero-bg {$p}-hero-bg--kenburns" style="background-image: url('<?= esc(\$heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
  <div class="{$p}-hero-overlay"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
        <?php if (\$heroBtn2Text): ?><a href="<?= esc(\$heroBtn2Link) ?>" class="{$p}-btn {$p}-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc(\$heroBtn2Text) ?></a><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="{$p}-hero-scroll"><a href="#next-section" aria-label="Scroll down"><i class="fas fa-chevron-down"></i></a></div>
</section>
HTML,

// ── Mosaic Grid: 2x2 image grid one side, text other ──
'mosaic-grid' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroImage1 = theme_get('hero.image', '');
\$heroImage2 = theme_get('hero.image2', '');
\$heroImage3 = theme_get('hero.image3', '');
\$heroImage4 = theme_get('hero.image4', '');
?>
<section class="{$p}-hero {$p}-hero--mosaic-grid" id="hero">
  <div class="container">
    <div class="{$p}-hero-grid">
      <div class="{$p}-hero-content" data-animate="fade-right">
        <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
        <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
        <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
        <div class="{$p}-hero-actions">
          <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
        </div>
      </div>
      <div class="{$p}-hero-mosaic" data-animate="fade-left">
        <div class="{$p}-mosaic-item"><img src="<?= esc(\$heroImage1) ?>" alt="" data-ts-bg="hero.image" loading="eager"></div>
        <div class="{$p}-mosaic-item"><img src="<?= esc(\$heroImage2) ?>" alt="" data-ts-bg="hero.image2" loading="eager"></div>
        <div class="{$p}-mosaic-item"><img src="<?= esc(\$heroImage3) ?>" alt="" data-ts-bg="hero.image3" loading="eager"></div>
        <div class="{$p}-mosaic-item"><img src="<?= esc(\$heroImage4) ?>" alt="" data-ts-bg="hero.image4" loading="eager"></div>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Mosaic Collage: Overlapping images behind centered text ──
'mosaic-collage' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroImage1 = theme_get('hero.image', '');
\$heroImage2 = theme_get('hero.image2', '');
\$heroImage3 = theme_get('hero.image3', '');
?>
<section class="{$p}-hero {$p}-hero--mosaic-collage" id="hero">
  <div class="{$p}-hero-collage-bg">
    <div class="{$p}-collage-img {$p}-collage-1" style="background-image: url('<?= esc(\$heroImage1) ?>');" data-ts-bg="hero.image"></div>
    <div class="{$p}-collage-img {$p}-collage-2" style="background-image: url('<?= esc(\$heroImage2) ?>');" data-ts-bg="hero.image2"></div>
    <div class="{$p}-collage-img {$p}-collage-3" style="background-image: url('<?= esc(\$heroImage3) ?>');" data-ts-bg="hero.image3"></div>
  </div>
  <div class="{$p}-hero-overlay"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Mosaic Masonry: 3-col masonry images with text overlay ──
'mosaic-masonry' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'View Collection');
\$heroBtnLink = theme_get('hero.btn_link', '/portfolio');
\$heroImage1 = theme_get('hero.image', '');
\$heroImage2 = theme_get('hero.image2', '');
\$heroImage3 = theme_get('hero.image3', '');
?>
<section class="{$p}-hero {$p}-hero--mosaic-masonry" id="hero">
  <div class="{$p}-hero-masonry-bg">
    <div class="{$p}-masonry-col"><img src="<?= esc(\$heroImage1) ?>" alt="" data-ts-bg="hero.image" loading="eager"></div>
    <div class="{$p}-masonry-col {$p}-masonry-col--offset"><img src="<?= esc(\$heroImage2) ?>" alt="" data-ts-bg="hero.image2" loading="eager"></div>
    <div class="{$p}-masonry-col"><img src="<?= esc(\$heroImage3) ?>" alt="" data-ts-bg="hero.image3" loading="eager"></div>
  </div>
  <div class="{$p}-hero-overlay"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Interactive Typed: Typing animation on headline ──
'interactive-typed' => <<<HTML
<?php
\$heroBgImage = theme_get('hero.bg_image', '');
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'We Build');
\$heroTypedWord = theme_get('hero.typed_word', 'Solutions');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
?>
<section class="{$p}-hero {$p}-hero--typed" id="hero">
  <div class="{$p}-hero-bg" style="background-image: url('<?= esc(\$heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
  <div class="{$p}-hero-overlay"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline">
        <span data-ts="hero.headline"><?= esc(\$heroHeadline) ?></span>
        <span class="{$p}-typed-text" data-ts="hero.typed_word"><?= esc(\$heroTypedWord) ?></span><span class="{$p}-typed-cursor">|</span>
      </h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Interactive Counter: Big animated counter numbers ──
'interactive-counter' => <<<HTML
<?php
\$heroBgImage = theme_get('hero.bg_image', '');
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$stat1Num = theme_get('hero.stat1_number', '1500+');
\$stat1Lab = theme_get('hero.stat1_label', 'Clients Served');
\$stat2Num = theme_get('hero.stat2_number', '99%');
\$stat2Lab = theme_get('hero.stat2_label', 'Satisfaction');
\$stat3Num = theme_get('hero.stat3_number', '24/7');
\$stat3Lab = theme_get('hero.stat3_label', 'Support');
?>
<section class="{$p}-hero {$p}-hero--counter" id="hero">
  <div class="{$p}-hero-bg" style="background-image: url('<?= esc(\$heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
  <div class="{$p}-hero-overlay"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-counters">
        <div class="{$p}-counter-item">
          <span class="{$p}-counter-number" data-ts="hero.stat1_number"><?= esc(\$stat1Num) ?></span>
          <span class="{$p}-counter-label" data-ts="hero.stat1_label"><?= esc(\$stat1Lab) ?></span>
        </div>
        <div class="{$p}-counter-item">
          <span class="{$p}-counter-number" data-ts="hero.stat2_number"><?= esc(\$stat2Num) ?></span>
          <span class="{$p}-counter-label" data-ts="hero.stat2_label"><?= esc(\$stat2Lab) ?></span>
        </div>
        <div class="{$p}-counter-item">
          <span class="{$p}-counter-number" data-ts="hero.stat3_number"><?= esc(\$stat3Num) ?></span>
          <span class="{$p}-counter-label" data-ts="hero.stat3_label"><?= esc(\$stat3Lab) ?></span>
        </div>
      </div>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Interactive Parallax: Multi-layer parallax bg ──
'interactive-parallax' => <<<HTML
<?php
\$heroBgImage = theme_get('hero.bg_image', '');
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Explore');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroBtn2Text = theme_get('hero.btn2_text', '');
\$heroBtn2Link = theme_get('hero.btn2_link', '#about');
?>
<section class="{$p}-hero {$p}-hero--parallax" id="hero">
  <div class="{$p}-parallax-layer {$p}-parallax-back" style="background-image: url('<?= esc(\$heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
  <div class="{$p}-parallax-layer {$p}-parallax-mid"></div>
  <div class="{$p}-hero-overlay"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
        <?php if (\$heroBtn2Text): ?><a href="<?= esc(\$heroBtn2Link) ?>" class="{$p}-btn {$p}-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc(\$heroBtn2Text) ?></a><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="{$p}-hero-scroll"><a href="#next-section" aria-label="Scroll down"><i class="fas fa-chevron-down"></i></a></div>
</section>
HTML,

// ── Split Diagonal: Diagonal clip-path split ──
'split-diagonal' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroImage = theme_get('hero.image', '');
?>
<section class="{$p}-hero {$p}-hero--diagonal" id="hero">
  <div class="{$p}-hero-diagonal-img" style="background-image: url('<?= esc(\$heroImage) ?>');" data-ts-bg="hero.image"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-right">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Split Overlap: Text box overlapping image ──
'split-overlap' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroImage = theme_get('hero.image', '');
?>
<section class="{$p}-hero {$p}-hero--overlap" id="hero">
  <div class="container">
    <div class="{$p}-hero-grid">
      <div class="{$p}-hero-visual" data-animate="fade-right">
        <img src="<?= esc(\$heroImage) ?>" alt="<?= esc(\$heroHeadline) ?>" class="{$p}-hero-image" data-ts-bg="hero.image" loading="eager">
      </div>
      <div class="{$p}-hero-content {$p}-hero-content--overlap" data-animate="fade-left">
        <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
        <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
        <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
        <div class="{$p}-hero-actions">
          <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
        </div>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Split With Features: Split + 3 feature icons below ──
'split-with-features' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroImage = theme_get('hero.image', '');
\$feat1Title = theme_get('hero.feat1_title', 'Quality');
\$feat1Desc = theme_get('hero.feat1_desc', 'Premium quality in everything we do.');
\$feat2Title = theme_get('hero.feat2_title', 'Fast');
\$feat2Desc = theme_get('hero.feat2_desc', 'Quick turnaround times guaranteed.');
\$feat3Title = theme_get('hero.feat3_title', 'Trusted');
\$feat3Desc = theme_get('hero.feat3_desc', 'Thousands of satisfied customers.');
?>
<section class="{$p}-hero {$p}-hero--split-features" id="hero">
  <div class="container">
    <div class="{$p}-hero-grid">
      <div class="{$p}-hero-content" data-animate="fade-right">
        <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
        <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
        <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
        <div class="{$p}-hero-actions">
          <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
        </div>
      </div>
      <div class="{$p}-hero-visual" data-animate="fade-left">
        <img src="<?= esc(\$heroImage) ?>" alt="<?= esc(\$heroHeadline) ?>" class="{$p}-hero-image" data-ts-bg="hero.image" loading="eager">
      </div>
    </div>
    <div class="{$p}-hero-features" data-animate="fade-up">
      <div class="{$p}-hero-feature">
        <i class="fas fa-check-circle"></i>
        <h3 data-ts="hero.feat1_title"><?= esc(\$feat1Title) ?></h3>
        <p data-ts="hero.feat1_desc"><?= esc(\$feat1Desc) ?></p>
      </div>
      <div class="{$p}-hero-feature">
        <i class="fas fa-bolt"></i>
        <h3 data-ts="hero.feat2_title"><?= esc(\$feat2Title) ?></h3>
        <p data-ts="hero.feat2_desc"><?= esc(\$feat2Desc) ?></p>
      </div>
      <div class="{$p}-hero-feature">
        <i class="fas fa-heart"></i>
        <h3 data-ts="hero.feat3_title"><?= esc(\$feat3Title) ?></h3>
        <p data-ts="hero.feat3_desc"><?= esc(\$feat3Desc) ?></p>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Split Testimonial: Split + customer quote ──
'split-testimonial' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroImage = theme_get('hero.image', '');
\$heroQuote = theme_get('hero.quote', 'An amazing experience from start to finish!');
\$heroQuoteAuthor = theme_get('hero.quote_author', 'Happy Customer');
?>
<section class="{$p}-hero {$p}-hero--split-testimonial" id="hero">
  <div class="container">
    <div class="{$p}-hero-grid">
      <div class="{$p}-hero-content" data-animate="fade-right">
        <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
        <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
        <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
        <div class="{$p}-hero-actions">
          <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
        </div>
        <blockquote class="{$p}-hero-quote">
          <p data-ts="hero.quote">&ldquo;<?= esc(\$heroQuote) ?>&rdquo;</p>
          <cite data-ts="hero.quote_author">&mdash; <?= esc(\$heroQuoteAuthor) ?></cite>
        </blockquote>
      </div>
      <div class="{$p}-hero-visual" data-animate="fade-left">
        <img src="<?= esc(\$heroImage) ?>" alt="<?= esc(\$heroHeadline) ?>" class="{$p}-hero-image" data-ts-bg="hero.image" loading="eager">
      </div>
    </div>
  </div>
</section>
HTML,

// ── Card Glass: Glassmorphism card over bg image ──
'card-glass' => <<<HTML
<?php
\$heroBgImage = theme_get('hero.bg_image', '');
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
?>
<section class="{$p}-hero {$p}-hero--card-glass" id="hero">
  <div class="{$p}-hero-bg" style="background-image: url('<?= esc(\$heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
  <div class="{$p}-hero-overlay"></div>
  <div class="container">
    <div class="{$p}-hero-glass-card" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Card Floating: Multiple floating cards over gradient ──
'card-floating' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroCard1 = theme_get('hero.card1_text', 'Design');
\$heroCard2 = theme_get('hero.card2_text', 'Develop');
\$heroCard3 = theme_get('hero.card3_text', 'Deploy');
?>
<section class="{$p}-hero {$p}-hero--card-floating" id="hero">
  <div class="{$p}-hero-gradient-bg"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
      </div>
    </div>
    <div class="{$p}-hero-floating-cards" data-animate="fade-up">
      <div class="{$p}-floating-card"><i class="fas fa-palette"></i><span data-ts="hero.card1_text"><?= esc(\$heroCard1) ?></span></div>
      <div class="{$p}-floating-card"><i class="fas fa-code"></i><span data-ts="hero.card2_text"><?= esc(\$heroCard2) ?></span></div>
      <div class="{$p}-floating-card"><i class="fas fa-rocket"></i><span data-ts="hero.card3_text"><?= esc(\$heroCard3) ?></span></div>
    </div>
  </div>
</section>
HTML,

// ── Card Product: Product showcase with price badge ──
'card-product' => <<<HTML
<?php
\$heroBgImage = theme_get('hero.bg_image', '');
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Shop Now');
\$heroBtnLink = theme_get('hero.btn_link', '/shop');
\$heroImage = theme_get('hero.image', '');
\$heroPrice = theme_get('hero.price', '$99');
\$heroPriceLabel = theme_get('hero.price_label', 'Starting at');
?>
<section class="{$p}-hero {$p}-hero--card-product" id="hero">
  <div class="{$p}-hero-bg" style="background-image: url('<?= esc(\$heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
  <div class="{$p}-hero-overlay"></div>
  <div class="container">
    <div class="{$p}-hero-grid">
      <div class="{$p}-hero-content" data-animate="fade-right">
        <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
        <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
        <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
        <div class="{$p}-hero-price-badge">
          <span class="{$p}-price-label" data-ts="hero.price_label"><?= esc(\$heroPriceLabel) ?></span>
          <span class="{$p}-price-value" data-ts="hero.price"><?= esc(\$heroPrice) ?></span>
        </div>
        <div class="{$p}-hero-actions">
          <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
        </div>
      </div>
      <div class="{$p}-hero-visual" data-animate="fade-left">
        <img src="<?= esc(\$heroImage) ?>" alt="<?= esc(\$heroHeadline) ?>" class="{$p}-hero-image" data-ts-bg="hero.image" loading="eager">
      </div>
    </div>
  </div>
</section>
HTML,

// ── Minimal Text Only: Huge headline, no bg/image ──
'minimal-text-only' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Explore');
\$heroBtnLink = theme_get('hero.btn_link', '/portfolio');
?>
<section class="{$p}-hero {$p}-hero--text-only" id="hero">
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Minimal Underline: Headline with animated underline ──
'minimal-underline' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Discover');
\$heroBtnLink = theme_get('hero.btn_link', '/portfolio');
?>
<section class="{$p}-hero {$p}-hero--underline" id="hero">
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline {$p}-hero-headline--underline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Minimal Sidebar: Side nav + hero main area ──
'minimal-sidebar' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroImage = theme_get('hero.image', '');
\$heroNav1 = theme_get('hero.nav1', 'About');
\$heroNav2 = theme_get('hero.nav2', 'Services');
\$heroNav3 = theme_get('hero.nav3', 'Contact');
?>
<section class="{$p}-hero {$p}-hero--sidebar" id="hero">
  <div class="{$p}-hero-sidebar-nav">
    <a href="#about" class="{$p}-sidebar-link" data-ts="hero.nav1"><?= esc(\$heroNav1) ?></a>
    <a href="#services" class="{$p}-sidebar-link" data-ts="hero.nav2"><?= esc(\$heroNav2) ?></a>
    <a href="#contact" class="{$p}-sidebar-link" data-ts="hero.nav3"><?= esc(\$heroNav3) ?></a>
  </div>
  <div class="{$p}-hero-sidebar-main">
    <div class="container">
      <div class="{$p}-hero-grid">
        <div class="{$p}-hero-content" data-animate="fade-right">
          <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
          <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
          <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
          <div class="{$p}-hero-actions">
            <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
          </div>
        </div>
        <div class="{$p}-hero-visual" data-animate="fade-left">
          <img src="<?= esc(\$heroImage) ?>" alt="<?= esc(\$heroHeadline) ?>" class="{$p}-hero-image" data-ts-bg="hero.image" loading="eager">
        </div>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Dramatic Dark: Very dark, bright accent text ──
'dramatic-dark' => <<<HTML
<?php
\$heroBgImage = theme_get('hero.bg_image', '');
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Enter');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
?>
<section class="{$p}-hero {$p}-hero--dramatic-dark" id="hero">
  <div class="{$p}-hero-bg" style="background-image: url('<?= esc(\$heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
  <div class="{$p}-hero-overlay {$p}-hero-overlay--heavy"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Dramatic Split Screen: 50/50 two-color split ──
'dramatic-split-screen' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Explore');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroImage = theme_get('hero.image', '');
?>
<section class="{$p}-hero {$p}-hero--dramatic-split" id="hero">
  <div class="{$p}-hero-split-left">
    <div class="{$p}-hero-content" data-animate="fade-right">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
      </div>
    </div>
  </div>
  <div class="{$p}-hero-split-right">
    <img src="<?= esc(\$heroImage) ?>" alt="<?= esc(\$heroHeadline) ?>" class="{$p}-hero-image" data-ts-bg="hero.image" loading="eager">
  </div>
</section>
HTML,

// ── Dramatic Spotlight: Radial spotlight on dark bg ──
'dramatic-spotlight' => <<<HTML
<?php
\$heroBgImage = theme_get('hero.bg_image', '');
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Discover');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
?>
<section class="{$p}-hero {$p}-hero--spotlight" id="hero">
  <div class="{$p}-hero-bg" style="background-image: url('<?= esc(\$heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
  <div class="{$p}-hero-spotlight-overlay"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-actions">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Business Classic: Professional with trust badges ──
'business-classic' => <<<HTML
<?php
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Get Started');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroBtn2Text = theme_get('hero.btn2_text', 'Learn More');
\$heroBtn2Link = theme_get('hero.btn2_link', '#about');
\$heroImage = theme_get('hero.image', '');
\$heroTrust1 = theme_get('hero.trust1', 'Certified');
\$heroTrust2 = theme_get('hero.trust2', 'Insured');
\$heroTrust3 = theme_get('hero.trust3', 'Licensed');
?>
<section class="{$p}-hero {$p}-hero--business-classic" id="hero">
  <div class="container">
    <div class="{$p}-hero-grid">
      <div class="{$p}-hero-content" data-animate="fade-right">
        <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
        <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
        <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
        <div class="{$p}-hero-actions">
          <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
          <?php if (\$heroBtn2Text): ?><a href="<?= esc(\$heroBtn2Link) ?>" class="{$p}-btn {$p}-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc(\$heroBtn2Text) ?></a><?php endif; ?>
        </div>
        <div class="{$p}-hero-trust-badges">
          <span class="{$p}-trust-badge"><i class="fas fa-certificate"></i> <span data-ts="hero.trust1"><?= esc(\$heroTrust1) ?></span></span>
          <span class="{$p}-trust-badge"><i class="fas fa-shield-alt"></i> <span data-ts="hero.trust2"><?= esc(\$heroTrust2) ?></span></span>
          <span class="{$p}-trust-badge"><i class="fas fa-award"></i> <span data-ts="hero.trust3"><?= esc(\$heroTrust3) ?></span></span>
        </div>
      </div>
      <div class="{$p}-hero-visual" data-animate="fade-left">
        <img src="<?= esc(\$heroImage) ?>" alt="<?= esc(\$heroHeadline) ?>" class="{$p}-hero-image" data-ts-bg="hero.image" loading="eager">
      </div>
    </div>
  </div>
</section>
HTML,

// ── Business Search: Large search bar CTA ──
'business-search' => <<<HTML
<?php
\$heroBgImage = theme_get('hero.bg_image', '');
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroSearchPlaceholder = theme_get('hero.search_placeholder', 'Search properties, locations...');
\$heroBtnText = theme_get('hero.btn_text', 'Search');
\$heroBtnLink = theme_get('hero.btn_link', '/search');
?>
<section class="{$p}-hero {$p}-hero--business-search" id="hero">
  <div class="{$p}-hero-bg" style="background-image: url('<?= esc(\$heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
  <div class="{$p}-hero-overlay"></div>
  <div class="container">
    <div class="{$p}-hero-content" data-animate="fade-up">
      <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
      <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
      <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      <div class="{$p}-hero-search-bar">
        <input type="text" placeholder="<?= esc(\$heroSearchPlaceholder) ?>" class="{$p}-hero-search-input" data-ts="hero.search_placeholder">
        <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary {$p}-hero-search-btn" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><i class="fas fa-search"></i> <?= esc(\$heroBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Business Appointment: Mini booking form ──
'business-appointment' => <<<HTML
<?php
\$heroBgImage = theme_get('hero.bg_image', '');
\$heroBadge = theme_get('hero.badge', '');
\$heroHeadline = theme_get('hero.headline', 'Your Headline Here');
\$heroSubtitle = theme_get('hero.subtitle', 'A compelling description of your business or service.');
\$heroBtnText = theme_get('hero.btn_text', 'Book Now');
\$heroBtnLink = theme_get('hero.btn_link', '/contact');
\$heroFormTitle = theme_get('hero.form_title', 'Book an Appointment');
?>
<section class="{$p}-hero {$p}-hero--appointment" id="hero">
  <div class="{$p}-hero-bg" style="background-image: url('<?= esc(\$heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
  <div class="{$p}-hero-overlay"></div>
  <div class="container">
    <div class="{$p}-hero-grid">
      <div class="{$p}-hero-content" data-animate="fade-right">
        <?php if (\$heroBadge): ?><span class="{$p}-hero-badge" data-ts="hero.badge"><?= esc(\$heroBadge) ?></span><?php endif; ?>
        <h1 class="{$p}-hero-headline" data-ts="hero.headline"><?= esc(\$heroHeadline) ?></h1>
        <p class="{$p}-hero-subtitle" data-ts="hero.subtitle"><?= esc(\$heroSubtitle) ?></p>
      </div>
      <div class="{$p}-hero-form-card" data-animate="fade-left">
        <h3 class="{$p}-form-title" data-ts="hero.form_title"><?= esc(\$heroFormTitle) ?></h3>
        <form class="{$p}-hero-booking-form">
          <input type="text" placeholder="Your Name" class="{$p}-form-input" required>
          <input type="tel" placeholder="Phone Number" class="{$p}-form-input" required>
          <input type="date" class="{$p}-form-input" required>
          <a href="<?= esc(\$heroBtnLink) ?>" class="{$p}-btn {$p}-btn-primary {$p}-form-submit" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc(\$heroBtnText) ?></a>
        </form>
      </div>
    </div>
  </div>
</section>
HTML,

        ];
    }

    // ═══════════════════════════════════════
    // CSS
    // ═══════════════════════════════════════

    private static function getClasses(string $patternId, string $p): array
    {
        $base = ["{$p}-hero", "{$p}-hero-bg", "{$p}-hero-overlay", "{$p}-hero-content",
                 "{$p}-hero-badge", "{$p}-hero-headline", "{$p}-hero-subtitle",
                 "{$p}-hero-actions", "{$p}-btn", "{$p}-btn-primary", "{$p}-btn-outline",
                 "{$p}-hero-scroll"];

        $extra = match($patternId) {
            'split-image', 'split-cards', 'split-reverse', 'editorial' =>
                ["{$p}-hero-grid", "{$p}-hero-visual", "{$p}-hero-image"],
            'fullscreen-stats' =>
                ["{$p}-hero-stats", "{$p}-hero-stat", "{$p}-stat-number", "{$p}-stat-label"],
            'split-cards' =>
                ["{$p}-hero-float-card"],
            'gradient-wave' =>
                ["{$p}-hero-gradient-bg", "{$p}-hero-wave"],
            'editorial' =>
                ["{$p}-hero-category", "{$p}-hero-author", "{$p}-hero-meta"],
            'slider-fade' =>
                ["{$p}-hero-slider-text", "{$p}-fade-line", "{$p}-fade-active"],
            'slider-text' =>
                ["{$p}-hero-rotate-wrapper", "{$p}-hero-rotate-word", "{$p}-rotate-active"],
            'slider-kenburns' =>
                ["{$p}-hero-bg--kenburns"],
            'mosaic-grid' =>
                ["{$p}-hero-grid", "{$p}-hero-mosaic", "{$p}-mosaic-item"],
            'mosaic-collage' =>
                ["{$p}-hero-collage-bg", "{$p}-collage-img"],
            'mosaic-masonry' =>
                ["{$p}-hero-masonry-bg", "{$p}-masonry-col"],
            'interactive-typed' =>
                ["{$p}-typed-text", "{$p}-typed-cursor"],
            'interactive-counter' =>
                ["{$p}-hero-counters", "{$p}-counter-item", "{$p}-counter-number", "{$p}-counter-label"],
            'interactive-parallax' =>
                ["{$p}-parallax-layer", "{$p}-parallax-back", "{$p}-parallax-mid"],
            'split-diagonal' =>
                ["{$p}-hero-diagonal-img"],
            'split-overlap' =>
                ["{$p}-hero-grid", "{$p}-hero-visual", "{$p}-hero-image", "{$p}-hero-content--overlap"],
            'split-with-features' =>
                ["{$p}-hero-grid", "{$p}-hero-visual", "{$p}-hero-image", "{$p}-hero-features", "{$p}-hero-feature"],
            'split-testimonial' =>
                ["{$p}-hero-grid", "{$p}-hero-visual", "{$p}-hero-image", "{$p}-hero-quote"],
            'card-glass' =>
                ["{$p}-hero-glass-card"],
            'card-floating' =>
                ["{$p}-hero-gradient-bg", "{$p}-hero-floating-cards", "{$p}-floating-card"],
            'card-product' =>
                ["{$p}-hero-grid", "{$p}-hero-visual", "{$p}-hero-image", "{$p}-hero-price-badge", "{$p}-price-label", "{$p}-price-value"],
            'minimal-text-only' =>
                [],
            'minimal-underline' =>
                ["{$p}-hero-headline--underline"],
            'minimal-sidebar' =>
                ["{$p}-hero-sidebar-nav", "{$p}-sidebar-link", "{$p}-hero-sidebar-main", "{$p}-hero-grid", "{$p}-hero-visual", "{$p}-hero-image"],
            'dramatic-dark' =>
                ["{$p}-hero-overlay--heavy"],
            'dramatic-split-screen' =>
                ["{$p}-hero-split-left", "{$p}-hero-split-right", "{$p}-hero-image"],
            'dramatic-spotlight' =>
                ["{$p}-hero-spotlight-overlay"],
            'business-classic' =>
                ["{$p}-hero-grid", "{$p}-hero-visual", "{$p}-hero-image", "{$p}-hero-trust-badges", "{$p}-trust-badge"],
            'business-search' =>
                ["{$p}-hero-search-bar", "{$p}-hero-search-input", "{$p}-hero-search-btn"],
            'business-appointment' =>
                ["{$p}-hero-grid", "{$p}-hero-form-card", "{$p}-form-title", "{$p}-hero-booking-form", "{$p}-form-input", "{$p}-form-submit"],
            default => [],
        };

        return array_merge($base, $extra);
    }

    private static function buildStructuralCSS(string $cssType, string $p): string
    {
        $css = self::css_base($p);

        $typeCSS = match($cssType) {
            'centered'              => self::css_centered($p),
            'centered-minimal'      => self::css_centered_minimal($p),
            'split'                 => self::css_split($p),
            'split-reverse'         => self::css_split_reverse($p),
            'fullscreen'            => self::css_fullscreen($p),
            'editorial'             => self::css_editorial($p),
            'gradient-wave'         => self::css_gradient_wave($p),
            'slider-fade'           => self::css_slider_fade($p),
            'slider-text'           => self::css_slider_text($p),
            'slider-kenburns'       => self::css_slider_kenburns($p),
            'mosaic-grid'           => self::css_mosaic_grid($p),
            'mosaic-collage'        => self::css_mosaic_collage($p),
            'mosaic-masonry'        => self::css_mosaic_masonry($p),
            'interactive-typed'     => self::css_interactive_typed($p),
            'interactive-counter'   => self::css_interactive_counter($p),
            'interactive-parallax'  => self::css_interactive_parallax($p),
            'split-diagonal'        => self::css_split_diagonal($p),
            'split-overlap'         => self::css_split_overlap($p),
            'split-with-features'   => self::css_split_with_features($p),
            'split-testimonial'     => self::css_split_testimonial($p),
            'card-glass'            => self::css_card_glass($p),
            'card-floating'         => self::css_card_floating($p),
            'card-product'          => self::css_card_product($p),
            'minimal-text-only'     => self::css_minimal_text_only($p),
            'minimal-underline'     => self::css_minimal_underline($p),
            'minimal-sidebar'       => self::css_minimal_sidebar($p),
            'dramatic-dark'         => self::css_dramatic_dark($p),
            'dramatic-split-screen' => self::css_dramatic_split_screen($p),
            'dramatic-spotlight'    => self::css_dramatic_spotlight($p),
            'business-classic'      => self::css_business_classic($p),
            'business-search'       => self::css_business_search($p),
            'business-appointment'  => self::css_business_appointment($p),
            default                 => self::css_centered($p),
        };

        return $css . $typeCSS . self::css_responsive($p);
    }

    // --- Base (shared by all hero patterns) ---
    private static function css_base(string $p): string
    {
        return <<<CSS

/* ═══ Hero Structural CSS (auto-generated — do not edit) ═══ */
.{$p}-hero {
  position: relative; overflow: hidden;
}
.{$p}-hero-bg {
  position: absolute; inset: 0;
  background-size: cover; background-position: center;
  z-index: 0;
}
.{$p}-hero-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(135deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.3) 100%);
  z-index: 1;
}
.{$p}-hero .container {
  position: relative; z-index: 2;
}
.{$p}-hero-content {
  max-width: 700px;
}
.{$p}-hero-badge {
  display: inline-block;
  font-size: 0.8125rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.12em;
  padding: 6px 16px; border-radius: 100px;
  margin-bottom: 20px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.15);
  color: var(--primary, #3b82f6);
  border: 1px solid rgba(var(--primary-rgb, 42,125,225), 0.25);
}
.{$p}-hero-headline {
  font-family: var(--font-heading, inherit);
  font-size: clamp(2.5rem, 5vw, 4.5rem);
  font-weight: 700; line-height: 1.1;
  margin: 0 0 20px 0;
  color: var(--text, #fff);
}
.{$p}-hero-subtitle {
  font-size: clamp(1rem, 2vw, 1.25rem);
  line-height: 1.7; margin: 0 0 32px 0;
  color: var(--text-muted, rgba(255,255,255,0.85));
  max-width: 55ch;
}
.{$p}-hero-actions {
  display: flex; flex-wrap: wrap; gap: 12px;
  align-items: center;
}
.{$p}-btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 14px 32px; border-radius: 6px;
  font-weight: 600; font-size: 0.9375rem;
  text-decoration: none; transition: all 0.3s ease;
  cursor: pointer; border: 2px solid transparent;
}
.{$p}-btn-primary {
  background: var(--primary, #3b82f6);
  color: var(--primary-contrast, #fff);
  border-color: var(--primary, #3b82f6);
}
.{$p}-btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(var(--primary-rgb, 42,125,225), 0.35);
}
.{$p}-btn-outline {
  background: transparent;
  color: var(--text, #fff);
  border-color: rgba(var(--text-rgb, 255,255,255), 0.3);
}
.{$p}-btn-outline:hover {
  border-color: var(--text, #fff);
  background: rgba(var(--text-rgb, 255,255,255), 0.05);
}
.{$p}-hero-scroll {
  position: absolute; bottom: 24px; left: 50%;
  transform: translateX(-50%); z-index: 2;
}
.{$p}-hero-scroll a {
  color: var(--text, #fff); opacity: 0.6;
  font-size: 1.5rem; text-decoration: none;
  animation: {$p}-bounce 2s infinite;
}
@keyframes {$p}-bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(8px); }
}

CSS;
    }

    // --- Centered ---
    private static function css_centered(string $p): string
    {
        return <<<CSS
.{$p}-hero--centered {
  min-height: 90vh; display: flex; align-items: center;
  text-align: center; padding: 120px 0 80px;
}
.{$p}-hero--centered .{$p}-hero-content {
  max-width: 800px; margin: 0 auto;
}
.{$p}-hero--centered .{$p}-hero-subtitle {
  margin-left: auto; margin-right: auto;
}
.{$p}-hero--centered .{$p}-hero-actions {
  justify-content: center;
}

CSS;
    }

    // --- Centered Minimal ---
    private static function css_centered_minimal(string $p): string
    {
        return <<<CSS
.{$p}-hero--minimal {
  padding: clamp(100px, 15vh, 200px) 0 clamp(80px, 12vh, 160px);
  text-align: center;
}
.{$p}-hero--minimal .{$p}-hero-content {
  max-width: 750px; margin: 0 auto;
}
.{$p}-hero--minimal .{$p}-hero-subtitle {
  margin-left: auto; margin-right: auto;
}
.{$p}-hero--minimal .{$p}-hero-actions {
  justify-content: center;
}
.{$p}-hero--minimal .{$p}-hero-headline {
  font-size: clamp(2.5rem, 6vw, 5rem);
}

CSS;
    }

    // --- Split ---
    private static function css_split(string $p): string
    {
        return <<<CSS
.{$p}-hero--split {
  padding: clamp(100px, 12vh, 160px) 0 clamp(80px, 10vh, 120px);
}
.{$p}-hero-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 80px); align-items: center;
}
.{$p}-hero-visual {
  position: relative;
}
.{$p}-hero-image {
  width: 100%; height: auto; display: block;
  border-radius: var(--radius, 12px);
  box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
/* Floating cards (split-cards variant) */
.{$p}-hero-float-card {
  position: absolute; display: flex; align-items: center; gap: 10px;
  padding: 12px 20px; border-radius: 10px;
  background: var(--surface, rgba(255,255,255,0.95));
  color: var(--text, #1e293b);
  box-shadow: 0 8px 30px rgba(0,0,0,0.12);
  font-size: 0.875rem; font-weight: 600;
  backdrop-filter: blur(10px);
}
.{$p}-hero-float-card i {
  color: var(--primary, #3b82f6); font-size: 1.125rem;
}
.{$p}-hero-float-1 { top: 10%; right: -20px; }
.{$p}-hero-float-2 { bottom: 15%; left: -20px; }

CSS;
    }

    // --- Split Reverse ---
    private static function css_split_reverse(string $p): string
    {
        return self::css_split($p) . <<<CSS
.{$p}-hero--reverse .{$p}-hero-grid {
  direction: rtl;
}
.{$p}-hero--reverse .{$p}-hero-grid > * {
  direction: ltr;
}

CSS;
    }

    // --- Fullscreen ---
    private static function css_fullscreen(string $p): string
    {
        return <<<CSS
.{$p}-hero--fullscreen {
  min-height: 100vh; display: flex; align-items: center;
  padding: 120px 0 80px;
}
.{$p}-hero--fullscreen .{$p}-hero-content {
  max-width: 700px;
}
/* Stats bar */
.{$p}-hero-stats {
  display: flex; gap: 0;
  margin-top: 60px; padding: 24px 0;
  border-top: 1px solid rgba(var(--text-rgb, 255,255,255), 0.15);
}
.{$p}-hero-stat {
  flex: 1; text-align: center;
  border-right: 1px solid rgba(var(--text-rgb, 255,255,255), 0.1);
}
.{$p}-hero-stat:last-child { border-right: none; }
.{$p}-stat-number {
  display: block; font-family: var(--font-heading, inherit);
  font-size: clamp(1.5rem, 3vw, 2.5rem); font-weight: 700;
  color: var(--primary, #3b82f6);
}
.{$p}-stat-label {
  display: block; font-size: 0.8125rem;
  text-transform: uppercase; letter-spacing: 0.08em;
  color: var(--text-muted, rgba(255,255,255,0.7));
  margin-top: 4px;
}

CSS;
    }

    // --- Editorial ---
    private static function css_editorial(string $p): string
    {
        return <<<CSS
.{$p}-hero--editorial {
  padding: clamp(100px, 12vh, 160px) 0 clamp(80px, 10vh, 120px);
}
.{$p}-hero--editorial .{$p}-hero-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 64px); align-items: center;
}
.{$p}-hero-category {
  display: inline-block;
  font-size: 0.75rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.15em;
  color: var(--primary, #3b82f6);
  margin-bottom: 16px;
  padding-bottom: 8px;
  border-bottom: 2px solid var(--primary, #3b82f6);
}
.{$p}-hero--editorial .{$p}-hero-headline {
  font-size: clamp(2rem, 4vw, 3.5rem);
  line-height: 1.15;
}
.{$p}-hero-meta {
  display: flex; align-items: center; gap: 20px;
  margin-top: 24px;
}
.{$p}-hero-author {
  font-size: 0.875rem; color: var(--text-muted, #94a3b8);
  display: flex; align-items: center; gap: 8px;
}
.{$p}-hero--editorial .{$p}-hero-visual {
  position: relative;
}
.{$p}-hero--editorial .{$p}-hero-image {
  width: 100%; height: auto; display: block;
  border-radius: var(--radius, 8px);
  box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

CSS;
    }

    // --- Gradient Wave ---
    private static function css_gradient_wave(string $p): string
    {
        return <<<CSS
.{$p}-hero--gradient {
  position: relative; padding: clamp(120px, 18vh, 220px) 0 clamp(100px, 14vh, 160px);
  text-align: center; overflow: hidden;
}
.{$p}-hero-gradient-bg {
  position: absolute; inset: 0; z-index: 0;
  background: linear-gradient(135deg, var(--primary, #3b82f6) 0%, var(--secondary, #8b5cf6) 50%, var(--accent, #06b6d4) 100%);
}
.{$p}-hero--gradient .{$p}-hero-content {
  max-width: 750px; margin: 0 auto;
}
.{$p}-hero--gradient .{$p}-hero-actions {
  justify-content: center;
}
.{$p}-hero--gradient .{$p}-hero-badge {
  background: rgba(255,255,255,0.15); color: #fff;
  border-color: rgba(255,255,255,0.25);
}
.{$p}-hero--gradient .{$p}-hero-headline { color: #fff; }
.{$p}-hero--gradient .{$p}-hero-subtitle { color: rgba(255,255,255,0.9); }
.{$p}-hero--gradient .{$p}-btn-primary {
  background: #fff; color: var(--primary, #3b82f6);
  border-color: #fff;
}
.{$p}-hero--gradient .{$p}-btn-outline {
  color: #fff; border-color: rgba(255,255,255,0.4);
}
.{$p}-hero-wave {
  position: absolute; bottom: -1px; left: 0; width: 100%;
  line-height: 0; z-index: 2;
}
.{$p}-hero-wave svg {
  display: block; width: 100%; height: 60px;
}
.{$p}-hero-wave path {
  fill: var(--background, #0a0f1a) !important;
}
/* Wave auto-matches next section's background via JS injection at assembly time.
   Fallback: body background color. Generator sets --wave-fill CSS var when next section has custom bg. */

CSS;
    }


    // --- Slider Fade ---
    private static function css_slider_fade(string $p): string
    {
        return <<<CSS
.{$p}-hero--slider-fade {
  min-height: 90vh; display: flex; align-items: center;
  text-align: center; padding: 120px 0 80px;
}
.{$p}-hero--slider-fade .{$p}-hero-content {
  max-width: 800px; margin: 0 auto;
}
.{$p}-hero--slider-fade .{$p}-hero-actions { justify-content: center; }
.{$p}-hero-slider-text { position: relative; min-height: 1.2em; }
.{$p}-fade-line {
  position: absolute; left: 0; right: 0;
  opacity: 0; transition: opacity 1s ease;
}
.{$p}-fade-line.{$p}-fade-active { position: relative; opacity: 1; }

CSS;
    }

    // --- Slider Text ---
    private static function css_slider_text(string $p): string
    {
        return <<<CSS
.{$p}-hero--slider-text {
  min-height: 90vh; display: flex; align-items: center;
  text-align: center; padding: 120px 0 80px;
}
.{$p}-hero--slider-text .{$p}-hero-content {
  max-width: 800px; margin: 0 auto;
}
.{$p}-hero--slider-text .{$p}-hero-actions { justify-content: center; }
.{$p}-hero-bg--fixed { background-attachment: fixed; }
.{$p}-hero-rotate-wrapper {
  position: relative; height: 1.5em; overflow: hidden;
  margin-bottom: 16px;
}
.{$p}-hero-rotate-word {
  position: absolute; left: 0; right: 0;
  font-family: var(--font-heading, inherit);
  font-size: clamp(2rem, 4vw, 3.5rem); font-weight: 700;
  color: var(--primary, #3b82f6);
  opacity: 0; transform: translateY(100%);
  transition: all 0.6s ease;
}
.{$p}-hero-rotate-word.{$p}-rotate-active {
  opacity: 1; transform: translateY(0);
}

CSS;
    }

    // --- Slider Ken Burns ---
    private static function css_slider_kenburns(string $p): string
    {
        return <<<CSS
.{$p}-hero--kenburns {
  min-height: 100vh; display: flex; align-items: center;
  text-align: center; padding: 120px 0 80px;
}
.{$p}-hero--kenburns .{$p}-hero-content {
  max-width: 800px; margin: 0 auto;
}
.{$p}-hero--kenburns .{$p}-hero-actions { justify-content: center; }
.{$p}-hero-bg--kenburns {
  animation: {$p}-kenburns 20s ease infinite alternate;
}
@keyframes {$p}-kenburns {
  0% { transform: scale(1); }
  100% { transform: scale(1.1); }
}

CSS;
    }

    // --- Mosaic Grid ---
    private static function css_mosaic_grid(string $p): string
    {
        return <<<CSS
.{$p}-hero--mosaic-grid {
  padding: clamp(100px, 12vh, 160px) 0 clamp(80px, 10vh, 120px);
}
.{$p}-hero--mosaic-grid .{$p}-hero-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 80px); align-items: center;
}
.{$p}-hero-mosaic {
  display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
}
.{$p}-mosaic-item img {
  width: 100%; height: 200px; object-fit: cover;
  border-radius: var(--radius, 8px);
}

CSS;
    }

    // --- Mosaic Collage ---
    private static function css_mosaic_collage(string $p): string
    {
        return <<<CSS
.{$p}-hero--mosaic-collage {
  min-height: 90vh; display: flex; align-items: center;
  text-align: center; padding: 120px 0 80px;
  position: relative;
}
.{$p}-hero--mosaic-collage .{$p}-hero-content {
  max-width: 700px; margin: 0 auto;
}
.{$p}-hero--mosaic-collage .{$p}-hero-actions { justify-content: center; }
.{$p}-hero-collage-bg {
  position: absolute; inset: 0; z-index: 0;
}
.{$p}-collage-img {
  position: absolute; background-size: cover; background-position: center;
  border-radius: var(--radius, 12px); opacity: 0.3;
}
.{$p}-collage-1 { top: 5%; left: 5%; width: 35%; height: 45%; transform: rotate(-3deg); }
.{$p}-collage-2 { top: 10%; right: 5%; width: 30%; height: 50%; transform: rotate(2deg); }
.{$p}-collage-3 { bottom: 5%; left: 30%; width: 40%; height: 35%; transform: rotate(-1deg); }

CSS;
    }

    // --- Mosaic Masonry ---
    private static function css_mosaic_masonry(string $p): string
    {
        return <<<CSS
.{$p}-hero--mosaic-masonry {
  min-height: 90vh; display: flex; align-items: center;
  text-align: center; padding: 120px 0 80px;
  position: relative;
}
.{$p}-hero--mosaic-masonry .{$p}-hero-content {
  max-width: 700px; margin: 0 auto;
}
.{$p}-hero--mosaic-masonry .{$p}-hero-actions { justify-content: center; }
.{$p}-hero-masonry-bg {
  position: absolute; inset: 0; z-index: 0;
  display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px;
  padding: 8px; opacity: 0.25;
}
.{$p}-masonry-col img {
  width: 100%; height: 100%; object-fit: cover;
}
.{$p}-masonry-col--offset { margin-top: 40px; }

CSS;
    }

    // --- Interactive Typed ---
    private static function css_interactive_typed(string $p): string
    {
        return <<<CSS
.{$p}-hero--typed {
  min-height: 90vh; display: flex; align-items: center;
  text-align: center; padding: 120px 0 80px;
}
.{$p}-hero--typed .{$p}-hero-content {
  max-width: 800px; margin: 0 auto;
}
.{$p}-hero--typed .{$p}-hero-actions { justify-content: center; }
.{$p}-typed-text {
  color: var(--primary, #3b82f6);
}
.{$p}-typed-cursor {
  display: inline-block;
  color: var(--primary, #3b82f6);
  animation: {$p}-blink 1s step-end infinite;
  font-weight: 300;
}
@keyframes {$p}-blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0; }
}

CSS;
    }

    // --- Interactive Counter ---
    private static function css_interactive_counter(string $p): string
    {
        return <<<CSS
.{$p}-hero--counter {
  min-height: 90vh; display: flex; align-items: center;
  text-align: center; padding: 120px 0 80px;
}
.{$p}-hero--counter .{$p}-hero-content {
  max-width: 800px; margin: 0 auto;
}
.{$p}-hero--counter .{$p}-hero-actions { justify-content: center; }
.{$p}-hero-counters {
  display: flex; justify-content: center; gap: 48px;
  margin: 32px 0;
}
.{$p}-counter-item { text-align: center; }
.{$p}-counter-number {
  display: block; font-family: var(--font-heading, inherit);
  font-size: clamp(2rem, 4vw, 3.5rem); font-weight: 700;
  color: var(--primary, #3b82f6);
}
.{$p}-counter-label {
  display: block; font-size: 0.875rem;
  text-transform: uppercase; letter-spacing: 0.08em;
  color: var(--text-muted, rgba(255,255,255,0.7));
  margin-top: 4px;
}

CSS;
    }

    // --- Interactive Parallax ---
    private static function css_interactive_parallax(string $p): string
    {
        return <<<CSS
.{$p}-hero--parallax {
  min-height: 100vh; display: flex; align-items: center;
  padding: 120px 0 80px; position: relative; overflow: hidden;
}
.{$p}-parallax-layer {
  position: absolute; inset: 0; background-size: cover;
  background-position: center;
}
.{$p}-parallax-back { z-index: 0; }
.{$p}-parallax-mid {
  z-index: 1;
  background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.3) 100%);
}
.{$p}-hero--parallax .{$p}-hero-content { max-width: 700px; }

CSS;
    }

    // --- Split Diagonal ---
    private static function css_split_diagonal(string $p): string
    {
        return <<<CSS
.{$p}-hero--diagonal {
  min-height: 90vh; display: flex; align-items: center;
  padding: clamp(100px, 12vh, 160px) 0;
  position: relative; overflow: hidden;
}
.{$p}-hero-diagonal-img {
  position: absolute; top: 0; right: 0; width: 55%; height: 100%;
  background-size: cover; background-position: center;
  clip-path: polygon(15% 0, 100% 0, 100% 100%, 0 100%);
  z-index: 0;
}
.{$p}-hero--diagonal .{$p}-hero-content {
  max-width: 45%; position: relative; z-index: 2;
}

CSS;
    }

    // --- Split Overlap ---
    private static function css_split_overlap(string $p): string
    {
        return <<<CSS
.{$p}-hero--overlap {
  padding: clamp(100px, 12vh, 160px) 0 clamp(80px, 10vh, 120px);
}
.{$p}-hero--overlap .{$p}-hero-grid {
  display: grid; grid-template-columns: 1.2fr 1fr;
  gap: 0; align-items: center; position: relative;
}
.{$p}-hero--overlap .{$p}-hero-visual { position: relative; }
.{$p}-hero--overlap .{$p}-hero-image {
  width: 100%; height: auto; display: block;
  border-radius: var(--radius, 12px);
}
.{$p}-hero-content--overlap {
  background: var(--surface, #fff);
  padding: clamp(32px, 4vw, 56px);
  border-radius: var(--radius, 12px);
  box-shadow: 0 20px 60px rgba(0,0,0,0.12);
  margin-left: -60px; position: relative; z-index: 2;
}

CSS;
    }

    // --- Split With Features ---
    private static function css_split_with_features(string $p): string
    {
        return self::css_split($p) . <<<CSS
.{$p}-hero--split-features .{$p}-hero-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 80px); align-items: center;
}
.{$p}-hero-features {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 24px; margin-top: 48px; padding-top: 48px;
  border-top: 1px solid rgba(var(--text-rgb, 0,0,0), 0.1);
}
.{$p}-hero-feature {
  text-align: center; padding: 16px;
}
.{$p}-hero-feature i {
  font-size: 1.5rem; color: var(--primary, #3b82f6);
  margin-bottom: 12px; display: block;
}
.{$p}-hero-feature h3 {
  font-size: 1rem; font-weight: 600; margin: 0 0 8px;
}
.{$p}-hero-feature p {
  font-size: 0.875rem; color: var(--text-muted, #64748b);
  margin: 0; line-height: 1.5;
}

CSS;
    }

    // --- Split Testimonial ---
    private static function css_split_testimonial(string $p): string
    {
        return self::css_split($p) . <<<CSS
.{$p}-hero--split-testimonial .{$p}-hero-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 80px); align-items: center;
}
.{$p}-hero-quote {
  margin: 32px 0 0; padding: 20px 24px;
  border-left: 3px solid var(--primary, #3b82f6);
  background: rgba(var(--primary-rgb, 42,125,225), 0.05);
  border-radius: 0 var(--radius, 8px) var(--radius, 8px) 0;
}
.{$p}-hero-quote p {
  font-style: italic; font-size: 1rem;
  color: var(--text, #1e293b); margin: 0 0 8px;
}
.{$p}-hero-quote cite {
  font-size: 0.875rem; color: var(--text-muted, #64748b);
  font-style: normal;
}

CSS;
    }

    // --- Card Glass ---
    private static function css_card_glass(string $p): string
    {
        return <<<CSS
.{$p}-hero--card-glass {
  min-height: 90vh; display: flex; align-items: center;
  justify-content: center; padding: 120px 0 80px;
  text-align: center;
}
.{$p}-hero-glass-card {
  max-width: 600px; margin: 0 auto;
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: var(--radius, 16px);
  padding: clamp(32px, 5vw, 64px);
}
.{$p}-hero-glass-card .{$p}-hero-actions { justify-content: center; }

CSS;
    }

    // --- Card Floating ---
    private static function css_card_floating(string $p): string
    {
        return self::css_gradient_wave($p) . <<<CSS
.{$p}-hero--card-floating { text-align: center; }
.{$p}-hero--card-floating .{$p}-hero-content {
  max-width: 750px; margin: 0 auto;
}
.{$p}-hero--card-floating .{$p}-hero-actions { justify-content: center; }
.{$p}-hero-floating-cards {
  display: flex; justify-content: center; gap: 24px;
  margin-top: 48px;
}
.{$p}-floating-card {
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: var(--radius, 12px);
  padding: 24px 32px;
  display: flex; flex-direction: column; align-items: center; gap: 12px;
  color: #fff; font-weight: 600;
  transition: transform 0.3s ease;
}
.{$p}-floating-card:hover { transform: translateY(-4px); }
.{$p}-floating-card i { font-size: 1.5rem; }

CSS;
    }

    // --- Card Product ---
    private static function css_card_product(string $p): string
    {
        return <<<CSS
.{$p}-hero--card-product {
  min-height: 80vh; display: flex; align-items: center;
  padding: clamp(100px, 12vh, 160px) 0;
}
.{$p}-hero--card-product .{$p}-hero-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 80px); align-items: center;
}
.{$p}-hero--card-product .{$p}-hero-visual { position: relative; }
.{$p}-hero--card-product .{$p}-hero-image {
  width: 100%; height: auto; display: block;
  border-radius: var(--radius, 12px);
  box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
.{$p}-hero-price-badge {
  display: inline-flex; flex-direction: column;
  background: var(--primary, #3b82f6); color: #fff;
  padding: 12px 24px; border-radius: var(--radius, 10px);
  margin-bottom: 20px;
}
.{$p}-price-label {
  font-size: 0.75rem; text-transform: uppercase;
  letter-spacing: 0.1em; opacity: 0.85;
}
.{$p}-price-value {
  font-family: var(--font-heading, inherit);
  font-size: 2rem; font-weight: 700;
}

CSS;
    }

    // --- Minimal Text Only ---
    private static function css_minimal_text_only(string $p): string
    {
        return <<<CSS
.{$p}-hero--text-only {
  padding: clamp(120px, 20vh, 260px) 0 clamp(100px, 15vh, 200px);
  text-align: center;
}
.{$p}-hero--text-only .{$p}-hero-content {
  max-width: 900px; margin: 0 auto;
}
.{$p}-hero--text-only .{$p}-hero-headline {
  font-size: clamp(3rem, 8vw, 7rem);
  line-height: 1.05; letter-spacing: -0.02em;
}
.{$p}-hero--text-only .{$p}-hero-actions { justify-content: center; }

CSS;
    }

    // --- Minimal Underline ---
    private static function css_minimal_underline(string $p): string
    {
        return <<<CSS
.{$p}-hero--underline {
  padding: clamp(100px, 15vh, 200px) 0 clamp(80px, 12vh, 160px);
  text-align: center;
}
.{$p}-hero--underline .{$p}-hero-content {
  max-width: 800px; margin: 0 auto;
}
.{$p}-hero--underline .{$p}-hero-actions { justify-content: center; }
.{$p}-hero-headline--underline {
  display: inline-block; position: relative;
}
.{$p}-hero-headline--underline::after {
  content: ''; position: absolute;
  bottom: -8px; left: 0; width: 100%; height: 4px;
  background: var(--primary, #3b82f6);
  border-radius: 2px;
  animation: {$p}-underline-grow 1s ease forwards;
  transform-origin: left;
}
@keyframes {$p}-underline-grow {
  0% { transform: scaleX(0); }
  100% { transform: scaleX(1); }
}

CSS;
    }

    // --- Minimal Sidebar ---
    private static function css_minimal_sidebar(string $p): string
    {
        return <<<CSS
.{$p}-hero--sidebar {
  display: flex; min-height: 90vh;
}
.{$p}-hero-sidebar-nav {
  width: 80px; display: flex; flex-direction: column;
  align-items: center; justify-content: center; gap: 24px;
  background: var(--surface, rgba(0,0,0,0.05));
  border-right: 1px solid rgba(var(--text-rgb, 0,0,0), 0.1);
}
.{$p}-sidebar-link {
  writing-mode: vertical-rl; text-orientation: mixed;
  font-size: 0.75rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.15em;
  color: var(--text-muted, #64748b);
  text-decoration: none; transition: color 0.3s ease;
}
.{$p}-sidebar-link:hover { color: var(--primary, #3b82f6); }
.{$p}-hero-sidebar-main {
  flex: 1; display: flex; align-items: center;
  padding: clamp(80px, 10vh, 140px) 0;
}
.{$p}-hero--sidebar .{$p}-hero-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 80px); align-items: center;
}
.{$p}-hero--sidebar .{$p}-hero-image {
  width: 100%; height: auto; display: block;
  border-radius: var(--radius, 12px);
  box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

CSS;
    }

    // --- Dramatic Dark ---
    private static function css_dramatic_dark(string $p): string
    {
        return <<<CSS
.{$p}-hero--dramatic-dark {
  min-height: 90vh; display: flex; align-items: center;
  text-align: center; padding: 120px 0 80px;
  background: #0a0a0a;
}
.{$p}-hero--dramatic-dark .{$p}-hero-content {
  max-width: 800px; margin: 0 auto;
}
.{$p}-hero--dramatic-dark .{$p}-hero-actions { justify-content: center; }
.{$p}-hero-overlay--heavy {
  background: linear-gradient(135deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.7) 100%);
}
.{$p}-hero--dramatic-dark .{$p}-hero-headline {
  color: #fff; font-size: clamp(3rem, 6vw, 5.5rem);
}
.{$p}-hero--dramatic-dark .{$p}-hero-badge {
  background: var(--primary, #3b82f6); color: #fff;
  border-color: var(--primary, #3b82f6);
}

CSS;
    }

    // --- Dramatic Split Screen ---
    private static function css_dramatic_split_screen(string $p): string
    {
        return <<<CSS
.{$p}-hero--dramatic-split {
  display: flex; min-height: 100vh;
}
.{$p}-hero-split-left {
  flex: 1; display: flex; align-items: center;
  padding: clamp(60px, 8vh, 120px) clamp(32px, 5vw, 80px);
  background: var(--background, #0a0f1a);
}
.{$p}-hero-split-left .{$p}-hero-content { max-width: 500px; }
.{$p}-hero-split-right {
  flex: 1; position: relative; overflow: hidden;
}
.{$p}-hero-split-right .{$p}-hero-image {
  width: 100%; height: 100%; object-fit: cover;
  display: block;
}

CSS;
    }

    // --- Dramatic Spotlight ---
    private static function css_dramatic_spotlight(string $p): string
    {
        return <<<CSS
.{$p}-hero--spotlight {
  min-height: 100vh; display: flex; align-items: center;
  text-align: center; padding: 120px 0 80px;
  background: #000;
}
.{$p}-hero--spotlight .{$p}-hero-content {
  max-width: 750px; margin: 0 auto;
}
.{$p}-hero--spotlight .{$p}-hero-actions { justify-content: center; }
.{$p}-hero-spotlight-overlay {
  position: absolute; inset: 0; z-index: 1;
  background: radial-gradient(ellipse at center, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.85) 70%);
}

CSS;
    }

    // --- Business Classic ---
    private static function css_business_classic(string $p): string
    {
        return self::css_split($p) . <<<CSS
.{$p}-hero--business-classic .{$p}-hero-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 80px); align-items: center;
}
.{$p}-hero-trust-badges {
  display: flex; flex-wrap: wrap; gap: 16px;
  margin-top: 24px;
}
.{$p}-trust-badge {
  display: inline-flex; align-items: center; gap: 8px;
  font-size: 0.8125rem; font-weight: 600;
  color: var(--text-muted, #64748b);
  padding: 8px 16px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.06);
  border-radius: 100px;
}
.{$p}-trust-badge i {
  color: var(--primary, #3b82f6);
}

CSS;
    }

    // --- Business Search ---
    private static function css_business_search(string $p): string
    {
        return <<<CSS
.{$p}-hero--business-search {
  min-height: 70vh; display: flex; align-items: center;
  text-align: center; padding: 120px 0 80px;
}
.{$p}-hero--business-search .{$p}-hero-content {
  max-width: 750px; margin: 0 auto;
}
.{$p}-hero-search-bar {
  display: flex; max-width: 600px; margin: 0 auto;
  border-radius: var(--radius, 12px);
  overflow: hidden;
  box-shadow: 0 12px 40px rgba(0,0,0,0.2);
  background: var(--surface, #fff);
}
.{$p}-hero-search-input {
  flex: 1; padding: 16px 24px;
  border: none; outline: none;
  font-size: 1rem; background: transparent;
  color: var(--text, #1e293b);
}
.{$p}-hero-search-btn {
  border-radius: 0;
  white-space: nowrap;
}

CSS;
    }

    // --- Business Appointment ---
    private static function css_business_appointment(string $p): string
    {
        return <<<CSS
.{$p}-hero--appointment {
  min-height: 80vh; display: flex; align-items: center;
  padding: clamp(100px, 12vh, 160px) 0;
}
.{$p}-hero--appointment .{$p}-hero-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 80px); align-items: center;
}
.{$p}-hero-form-card {
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  padding: clamp(24px, 3vw, 40px);
  box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
.{$p}-form-title {
  font-family: var(--font-heading, inherit);
  font-size: 1.25rem; font-weight: 700;
  margin: 0 0 20px; color: var(--text, #1e293b);
}
.{$p}-hero-booking-form {
  display: flex; flex-direction: column; gap: 12px;
}
.{$p}-form-input {
  padding: 12px 16px; border: 1px solid rgba(var(--text-rgb, 0,0,0), 0.15);
  border-radius: var(--radius, 8px); font-size: 0.9375rem;
  outline: none; transition: border-color 0.3s ease;
  background: transparent; color: var(--text, #1e293b);
}
.{$p}-form-input:focus {
  border-color: var(--primary, #3b82f6);
}
.{$p}-form-submit {
  width: 100%; justify-content: center; margin-top: 8px;
}

CSS;
    }

    // --- Responsive ---
    private static function css_responsive(string $p): string
    {
        return <<<CSS
@media (max-width: 768px) {
  .{$p}-hero-grid {
    grid-template-columns: 1fr !important;
    gap: 40px !important;
  }
  .{$p}-hero--reverse .{$p}-hero-grid {
    direction: ltr;
  }
  .{$p}-hero--fullscreen,
  .{$p}-hero--centered {
    min-height: 80vh;
  }
  .{$p}-hero-stats {
    flex-wrap: wrap;
  }
  .{$p}-hero-stat {
    flex: 0 0 50%; border-right: none;
    padding: 12px 0;
  }
  .{$p}-hero-float-card { display: none; }
  .{$p}-hero-actions {
    justify-content: center;
  }
  /* Mosaic */
  .{$p}-hero-mosaic { grid-template-columns: 1fr; }
  .{$p}-hero-masonry-bg { grid-template-columns: 1fr; }
  .{$p}-masonry-col--offset { margin-top: 0; }
  /* Counter */
  .{$p}-hero-counters { flex-direction: column; gap: 24px; }
  /* Split diagonal */
  .{$p}-hero-diagonal-img { display: none; }
  .{$p}-hero--diagonal .{$p}-hero-content { max-width: 100%; }
  /* Split overlap */
  .{$p}-hero-content--overlap { margin-left: 0; }
  /* Features */
  .{$p}-hero-features { grid-template-columns: 1fr; }
  /* Dramatic split */
  .{$p}-hero--dramatic-split { flex-direction: column; }
  .{$p}-hero-split-right { min-height: 300px; }
  /* Sidebar */
  .{$p}-hero--sidebar { flex-direction: column; }
  .{$p}-hero-sidebar-nav {
    width: 100%; flex-direction: row;
    padding: 12px; border-right: none;
    border-bottom: 1px solid rgba(var(--text-rgb, 0,0,0), 0.1);
  }
  .{$p}-sidebar-link { writing-mode: horizontal-tb; }
  /* Floating cards */
  .{$p}-hero-floating-cards { flex-direction: column; align-items: center; }
  /* Search */
  .{$p}-hero-search-bar { flex-direction: column; }
  .{$p}-hero-search-btn { border-radius: 0 0 var(--radius, 12px) var(--radius, 12px); }
  /* Collage hidden on mobile */
  .{$p}-collage-img { display: none; }
}

CSS;
    }
}
