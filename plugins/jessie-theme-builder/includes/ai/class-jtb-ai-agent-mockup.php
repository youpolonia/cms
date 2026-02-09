<?php
/**
 * JTB AI Agent: Mockup Designer
 *
 * Generates HTML/CSS mockup from user prompt.
 * AI has FULL creative freedom - no hardcoded styles, colors, or layouts.
 *
 * @package JessieThemeBuilder
 * @since 2.0.0
 * @updated 2026-02-06 - Fixed incomplete HTML handling, increased max_tokens
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');


class JTB_AI_Agent_Mockup
{
    /**
     * Provider-specific configurations (technical only, no design specs)
     * Note: Anthropic increased to 16000 to avoid truncation
     */
    private const PROVIDER_CONFIG = [
        'deepseek' => [

            'max_tokens' => 8000,
            'timeout' => 180
        ],
        'openai' => [
            'max_tokens' => 16000,
            'timeout' => 180
        ],
        'anthropic' => [
            'max_tokens' => 16000,  // Claude needs more for full HTML
            'timeout' => 180
        ],
        'google' => [
            'max_tokens' => 8000,
            'timeout' => 120
        ],
        'default' => [
            'max_tokens' => 4000,
            'timeout' => 180
        ]
    ];

    // =========================================================================
    // MAIN GENERATION METHOD
    // =========================================================================

    /**
     * Generate HTML mockup directly from AI
     * AI has full creative freedom to choose colors, fonts, layout, etc.
     */
    public static function generate(array $session): array
    {
        $prompt = $session['prompt'] ?? '';
        $language = $session['language'] ?? '';

        // Get provider config (technical params only)
        $aiProvider = $session['ai_provider'] ?? null;
        $aiModel = $session['ai_model'] ?? null;
        $providerConfig = self::PROVIDER_CONFIG[$aiProvider] ?? self::PROVIDER_CONFIG['default'];

        // Build prompts - AI decides all design aspects
        $industry = $session['industry'] ?? 'general';
        $style = $session['style'] ?? 'modern';
        $systemPrompt = self::buildSystemPrompt($style);
        $userPrompt = self::buildUserPrompt($prompt, $industry, $style, $language);

        try {
            $ai = JTB_AI_Core::getInstance();
            
            // Set provider if specified
            if ($aiProvider) {
                $ai->setProvider($aiProvider);
            }

            $queryOptions = [
                'system_prompt' => $systemPrompt,
                'temperature' => 0.8,  // Higher for more creativity
                'max_tokens' => $providerConfig['max_tokens'],
                'timeout' => $providerConfig['timeout']
            ];

            if (!empty($aiProvider)) {
                $queryOptions['provider'] = $aiProvider;
            }
            if (!empty($aiModel)) {
                $queryOptions['model'] = $aiModel;
            }

            $response = $ai->query($userPrompt, $queryOptions);

            // DEBUG LOG
            file_put_contents('/tmp/mockup_debug.log', date('H:i:s') . " MOCKUP START\n", FILE_APPEND);
            file_put_contents('/tmp/mockup_debug.log', 'providerConfig max_tokens: ' . ($providerConfig['max_tokens'] ?? 'NULL') . "\n", FILE_APPEND);
            file_put_contents('/tmp/mockup_debug.log', 'Provider: ' . ($aiProvider ?? 'NULL') . "\n", FILE_APPEND);
            file_put_contents('/tmp/mockup_debug.log', 'Model: ' . ($aiModel ?? 'NULL') . "\n", FILE_APPEND);
            file_put_contents('/tmp/mockup_debug.log', 'Response OK: ' . ($response['ok'] ? 'YES' : 'NO') . "\n", FILE_APPEND);
            file_put_contents('/tmp/mockup_debug.log', 'stop_reason: ' . ($response['stop_reason'] ?? 'NULL') . "\n", FILE_APPEND);
            file_put_contents('/tmp/mockup_debug.log', 'output_tokens: ' . ($response['output_tokens'] ?? 'NULL') . "\n", FILE_APPEND);
            file_put_contents('/tmp/mockup_debug.log', 'Response length: ' . strlen($response['text'] ?? '') . "\n", FILE_APPEND);
            file_put_contents('/tmp/mockup_debug.log', 'Has </html>: ' . (stripos($response['text'] ?? '', '</html>') !== false ? 'YES' : 'NO') . "\n", FILE_APPEND);

            if (!$response['ok']) {
                return [
                    'ok' => false,
                    'error' => 'AI query failed: ' . ($response['error'] ?? 'Unknown error')
                ];
            }

            $html = self::extractHTML($response['text']);

            if (empty($html)) {
                return [
                    'ok' => false,
                    'error' => 'Failed to extract HTML from AI response'
                ];
            }

            $structure = self::extractStructureFromHTML($html, $prompt);

            return [
                'ok' => true,
                'mockup_html' => $html,
                'structure' => $structure,
                'tokens_used' => $response['tokens_used'] ?? 0,
                'stop_reason' => $response['stop_reason'] ?? null
            ];

        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'error' => 'Mockup generation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Iterate on existing mockup based on user feedback
     */
    public static function iterate(array $session, string $instruction): array
    {
        $currentHtml = $session['mockup_html'] ?? '';
        $language = $session['language'] ?? '';
        $aiProvider = $session['ai_provider'] ?? null;
        $aiModel = $session['ai_model'] ?? null;
        $providerConfig = self::PROVIDER_CONFIG[$aiProvider] ?? self::PROVIDER_CONFIG['default'];

        if (empty($currentHtml)) {
            return ['ok' => false, 'error' => 'No mockup to iterate on'];
        }

        $systemPrompt = <<<PROMPT
You are a professional web designer modifying an existing website.
Apply the requested changes while maintaining design consistency.
Output ONLY the complete updated HTML document.
No explanations, no markdown code blocks - just pure HTML starting with <!DOCTYPE html>.
PROMPT;

        $userPrompt = "CURRENT WEBSITE:\n\n{$currentHtml}\n\n---\n\nCHANGE REQUEST: {$instruction}\n\nApply this change and return the complete updated HTML.";
        
        if (!empty($language)) {
            $userPrompt .= "\n\nIMPORTANT: All text content must be in {$language}.";
        }

        try {
            $ai = JTB_AI_Core::getInstance();
            
            // Set provider if specified
            if ($aiProvider) {
                $ai->setProvider($aiProvider);
            }

            $queryOptions = [
                'system_prompt' => $systemPrompt,
                'temperature' => 0.7,
                'max_tokens' => $providerConfig['max_tokens'],
                'timeout' => $providerConfig['timeout']
            ];

            if (!empty($aiProvider)) $queryOptions['provider'] = $aiProvider;
            if (!empty($aiModel)) $queryOptions['model'] = $aiModel;

            $response = $ai->query($userPrompt, $queryOptions);

            if (!$response['ok']) {
                return ['ok' => false, 'error' => 'AI query failed: ' . ($response['error'] ?? 'Unknown')];
            }

            $html = self::extractHTML($response['text']);
            if (empty($html)) {
                return ['ok' => false, 'error' => 'Failed to extract HTML'];
            }

            // Parse updated HTML to get new structure
            $structure = self::extractStructureFromHTML($html, $instruction);
            
            return [
                'ok' => true,
                'mockup_html' => $html,
                'structure' => $structure,
                'tokens_used' => $response['tokens_used'] ?? 0
            ];

        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => 'Iteration failed: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // INDUSTRY & STYLE DEFINITIONS - Rich descriptions for AI
    // =========================================================================

    /**
     * Detailed industry definitions with specific design guidance
     */
    private static function getIndustryGuide(string $industry): string
    {
        $guides = [
            'technology' => <<<GUIDE
TECHNOLOGY/SAAS INDUSTRY DESIGN:
- Color palette: Blues, purples, teals with bright accent colors (lime, cyan, orange)
- Typography: Clean sans-serif fonts (Inter, SF Pro, Manrope), large bold headlines
- Layout patterns: 
  * Hero with product screenshot/mockup floating or in device frame
  * Feature grid with icons (3 or 4 columns)
  * Integration logos section
  * Pricing comparison table
  * Code snippets or terminal windows as decorative elements
- Visual elements: Gradients, glassmorphism, subtle grid patterns, floating 3D elements
- Sections to include: Hero, Features, How It Works, Integrations, Pricing, Testimonials, FAQ, CTA
- Tone: Innovative, efficient, trustworthy
GUIDE,

            'agency' => <<<GUIDE
AGENCY/CREATIVE INDUSTRY DESIGN:
- Color palette: Bold and unexpected combinations, often black + one vibrant accent
- Typography: Mix of serif and sans-serif, oversized headlines, experimental layouts
- Layout patterns:
  * Full-screen hero with bold typography or video background
  * Portfolio grid with hover effects (irregular sizes)
  * Awards/recognition section
  * Client logos marquee
  * Team section with creative photography
- Visual elements: Large whitespace, asymmetric layouts, horizontal scrolling sections, cursor effects
- Sections: Hero, Selected Work, Services, About/Philosophy, Team, Clients, Contact
- Tone: Confident, creative, sophisticated
GUIDE,

            'ecommerce' => <<<GUIDE
E-COMMERCE/RETAIL INDUSTRY DESIGN:
- Color palette: Clean whites with accent colors matching brand identity
- Typography: Easy-to-read fonts, clear hierarchy for pricing and product info
- Layout patterns:
  * Hero featuring bestseller or seasonal collection
  * Product category cards/grid
  * Featured products carousel
  * Sale/promotion banners
  * Trust badges (shipping, returns, secure payment)
- Visual elements: High-quality product photography, subtle shadows, clean cards
- Sections: Hero, Categories, Featured Products, Benefits, Reviews, Newsletter
- Tone: Trustworthy, exciting, easy to navigate
GUIDE,

            'healthcare' => <<<GUIDE
HEALTHCARE/MEDICAL INDUSTRY DESIGN:
- Color palette: Calming blues, greens, white, soft teals - avoid harsh reds
- Typography: Professional, highly readable fonts (Source Sans, Lato)
- Layout patterns:
  * Hero with caring imagery (doctors, patients, families)
  * Services with clear icons
  * Doctor/team profiles with credentials
  * Patient testimonials
  * Insurance/accepted plans section
  * Appointment booking CTA
- Visual elements: Soft gradients, rounded corners, photos of real people
- Sections: Hero, Services, Doctors, Why Choose Us, Patient Stories, Insurance, Contact
- Tone: Caring, professional, trustworthy, accessible
GUIDE,

            'finance' => <<<GUIDE
FINANCE/FINTECH INDUSTRY DESIGN:
- Color palette: Navy blue, dark green, gold accents, clean whites
- Typography: Authoritative serif or professional sans-serif
- Layout patterns:
  * Hero with value proposition and signup CTA
  * Stats/metrics section with large numbers
  * Services/products comparison
  * Security certifications and compliance badges
  * Calculator or interactive tool preview
- Visual elements: Charts, graphs, secure lock icons, minimal decoration
- Sections: Hero, Key Metrics, Services, Security, How It Works, Testimonials, FAQ
- Tone: Secure, trustworthy, professional, modern
GUIDE,

            'education' => <<<GUIDE
EDUCATION/E-LEARNING INDUSTRY DESIGN:
- Color palette: Friendly and approachable - blues, greens, yellows, purples
- Typography: Warm and readable, slightly playful but professional
- Layout patterns:
  * Hero with student success imagery or course preview
  * Course categories or learning paths
  * Featured instructors/teachers
  * Student success stories with metrics
  * Free trial or sample lesson CTA
- Visual elements: Illustrations, icons, progress indicators, achievement badges
- Sections: Hero, Courses, Why Learn With Us, Instructors, Student Success, Pricing, Start Learning
- Tone: Encouraging, accessible, inspiring, growth-oriented
GUIDE,

            'realestate' => <<<GUIDE
REAL ESTATE INDUSTRY DESIGN:
- Color palette: Sophisticated neutrals, navy, gold or green accents
- Typography: Elegant serif for headlines, clean sans-serif for body
- Layout patterns:
  * Hero with stunning property image or search bar
  * Property cards with key details (beds, baths, price)
  * Neighborhood/area guides
  * Agent profiles with contact info
  * Market stats section
- Visual elements: Large property photos, maps, floor plan icons
- Sections: Hero/Search, Featured Listings, Neighborhoods, About Us, Agent Team, Testimonials
- Tone: Luxurious, trustworthy, local expertise
GUIDE,

            'restaurant' => <<<GUIDE
RESTAURANT/FOOD INDUSTRY DESIGN:
- Color palette: Warm and appetizing - reds, oranges, browns, greens, cream
- Typography: Mix of elegant scripts and clean fonts, menu-inspired
- Layout patterns:
  * Hero with mouthwatering food photography
  * Menu highlights or categories
  * Ambiance/interior photos
  * Chef story or philosophy
  * Reservation widget or hours
  * Location with map
- Visual elements: Food photography is KEY, textured backgrounds, handwritten elements
- Sections: Hero, Menu Highlights, About/Story, Gallery, Reservations, Location
- Tone: Appetizing, welcoming, authentic
GUIDE,

            'fitness' => <<<GUIDE
FITNESS/WELLNESS INDUSTRY DESIGN:
- Color palette: Energetic - blacks, neons (lime, orange, pink), or calming pastels for wellness
- Typography: Bold, impactful headlines, clean body text
- Layout patterns:
  * Hero with action shot or transformation
  * Class/program schedule or types
  * Trainer profiles
  * Membership/pricing tiers
  * Success stories with before/after
  * Free trial CTA
- Visual elements: Dynamic angles, movement imagery, progress graphics
- Sections: Hero, Programs, Trainers, Success Stories, Pricing, Schedule, Join Now
- Tone: Motivating, energetic, transformative, community
GUIDE,

            'legal' => <<<GUIDE
LEGAL/CONSULTING INDUSTRY DESIGN:
- Color palette: Conservative navy, burgundy, dark green, gold accents
- Typography: Traditional serif fonts, very professional appearance
- Layout patterns:
  * Hero with firm values or specialty
  * Practice areas with icons
  * Attorney profiles with credentials
  * Case results or testimonials
  * Free consultation CTA
- Visual elements: Minimal, professional photography, subtle patterns
- Sections: Hero, Practice Areas, Our Team, Case Results, Why Choose Us, Contact
- Tone: Authoritative, trustworthy, experienced, professional
GUIDE,

            'nonprofit' => <<<GUIDE
NONPROFIT/NGO INDUSTRY DESIGN:
- Color palette: Cause-appropriate (green for environment, blue for water, etc.), warm and hopeful
- Typography: Friendly and accessible, emotionally engaging
- Layout patterns:
  * Hero with impactful imagery and mission statement
  * Impact statistics (people helped, funds raised)
  * Programs/initiatives
  * Stories from beneficiaries
  * Multiple donation CTAs
  * Volunteer opportunities
- Visual elements: Real photography, infographics, progress bars
- Sections: Hero/Mission, Impact, Programs, Stories, Ways to Help, Events, Donate
- Tone: Inspiring, urgent, hopeful, transparent
GUIDE,

            'general' => <<<GUIDE
GENERAL BUSINESS DESIGN:
- Color palette: Professional yet approachable, based on brand values
- Typography: Clean and versatile
- Layout patterns: Adapt to business needs
- Include standard sections: Hero, About, Services, Testimonials, Contact
- Tone: Professional, trustworthy
GUIDE
        ];

        return $guides[$industry] ?? $guides['general'];
    }

    /**
     * Detailed visual style definitions
     */
    private static function getStyleGuide(string $style): string
    {
        $guides = [
            'modern' => <<<GUIDE
MODERN & CLEAN STYLE:
- Lots of whitespace, breathing room between sections
- Subtle shadows and depth (box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1))
- Rounded corners (border-radius: 8-16px)
- Neutral backgrounds with one or two accent colors
- Sans-serif typography (Inter, Plus Jakarta Sans, DM Sans)
- Subtle hover animations (transform, opacity changes)
- Card-based layouts with consistent spacing
- Icons: Line icons or subtle filled icons
GUIDE,

            'minimal' => <<<GUIDE
MINIMAL & SIMPLE STYLE:
- Maximum whitespace, very sparse layouts
- Black and white as primary, one accent color maximum
- Typography-focused design, large elegant fonts
- Almost no decorative elements
- Thin lines and borders if any
- Monospace or elegant serif fonts for accents
- No gradients, no shadows or very subtle ones
- Simple geometric shapes only
- Focus on content, not decoration
GUIDE,

            'bold' => <<<GUIDE
BOLD & VIBRANT STYLE:
- Saturated, electric colors (hot pink, electric blue, lime green, orange)
- Dark backgrounds with neon accents work great
- Oversized typography, extra bold weights (800, 900)
- Unexpected color combinations
- Animated elements, movement
- Gradients and color overlays
- Chunky rounded corners (20-30px)
- Playful, energetic feel
- Shadows with colored tints
GUIDE,

            'elegant' => <<<GUIDE
ELEGANT & LUXURY STYLE:
- Refined color palette: black, white, gold, champagne, deep jewel tones
- Serif typography for headlines (Playfair Display, Cormorant, Libre Baskerville)
- Generous whitespace and margins
- Thin gold lines and borders as accents
- High-quality photography with subtle overlays
- Subtle animations, nothing flashy
- Classic layouts with modern touches
- Letter-spacing in uppercase headings
GUIDE,

            'playful' => <<<GUIDE
PLAYFUL & FUN STYLE:
- Bright, happy color palette (yellows, pinks, sky blues, mint greens)
- Rounded, bubbly shapes and elements
- Friendly, rounded sans-serif fonts (Nunito, Quicksand, Poppins)
- Illustrations and hand-drawn elements
- Bouncy animations and hover effects
- Patterns and decorative backgrounds
- Irregular layouts, not too rigid
- Emoji and playful icons welcome
GUIDE,

            'corporate' => <<<GUIDE
CORPORATE & PROFESSIONAL STYLE:
- Conservative colors: navy, gray, muted blues, minimal accent
- Traditional layout structure, predictable and trustworthy
- Professional sans-serif fonts (Open Sans, Roboto, Source Sans Pro)
- Structured grid layouts
- Subtle shadows and borders
- Professional photography
- Clear CTAs in accent color
- No experiments, reliability is key
GUIDE,

            'dark' => <<<GUIDE
DARK & DRAMATIC STYLE:
- Dark backgrounds (#0a0a0a, #1a1a1a, #0f172a)
- Light text on dark backgrounds
- Accent colors that pop: neon greens, electric blues, hot pinks, golds
- Dramatic gradients and glows
- Subtle texture overlays (grain, noise)
- Bold typography with high contrast
- Glassmorphism and blur effects
- Moody, atmospheric imagery
- Shadows that add depth in dark mode
GUIDE
        ];

        return $guides[$style] ?? $guides['modern'];
    }


    /**
     * Comprehensive layout taxonomy with implementation details
     * Returns random combination from each category
     */
    private static function getLayoutVariant(): string
    {
        // ========================================
        // EYE-TRACKING / READING PATTERNS
        // ========================================
        $readingPatterns = [
            "F-PATTERN LAYOUT:
            - Users scan horizontally across top (logo, nav, hero headline)
            - Then down left side, scanning horizontally at key points
            - Place most important content in top-left quadrant
            - Use bold headings to create horizontal 'scan lines'
            - Left-align key information, CTAs on left side
            CSS: Use flexbox with flex-direction: column, align-items: flex-start",
            
            "Z-PATTERN LAYOUT:
            - Eye moves: top-left → top-right → diagonal → bottom-left → bottom-right
            - Perfect for landing pages with single CTA goal
            - Logo top-left, nav/CTA top-right
            - Hero content in diagonal middle
            - Final CTA bottom-right
            CSS: Use grid with strategic placement at Z points",
            
            "INVERTED-Z PATTERN:
            - Reverse Z: top-right → top-left → diagonal → bottom-right → bottom-left
            - Unexpected, creates visual interest
            - Logo right, nav left
            - Good for RTL-inspired designs or creative agencies",
            
            "LAYER-CAKE PATTERN:
            - Strong horizontal bands/stripes
            - Each section is a distinct 'layer' with different background
            - Users scan headings, skip to relevant layer
            - Alternating backgrounds crucial
            CSS: Full-width sections, alternate bg colors, clear section headings",
            
            "SPOTTED PATTERN:
            - Key data points highlighted as 'spots'
            - Users jump between highlighted elements
            - Perfect for: pricing, stats, dates, key figures
            - Use color, size, or badges to create spots
            CSS: Use accent colors, larger font-size, badges for key data",
            
            "COMMITMENT PATTERN:
            - Progressive disclosure for forms/onboarding
            - Start simple, reveal complexity gradually
            - Numbered steps, progress indicators
            - Each step feels achievable
            CSS: Stepper with progress bar, collapsible sections"
        ];

        // ========================================
        // STRUCTURAL LAYOUT PATTERNS
        // ========================================
        $structuralPatterns = [
            "SINGLE-COLUMN LAYOUT:
            - All content in one centered column (max-width: 800px)
            - Perfect for reading-focused pages, blogs, articles
            - Very clean, no distractions
            CSS: .container { max-width: 800px; margin: 0 auto; }",
            
            "TWO-COLUMN LAYOUT:
            - Content + sidebar OR equal split
            - Sidebar can be sticky
            CSS: display: grid; grid-template-columns: 1fr 300px; OR 1fr 1fr;",
            
            "THREE-COLUMN LAYOUT:
            - Nav | Content | Aside OR equal thirds
            - Classic portal/news layout
            CSS: grid-template-columns: 250px 1fr 300px;",
            
            "HOLY GRAIL LAYOUT:
            - Header (full width) → [Sidebar | Main | Aside] → Footer
            - The classic, never fails
            CSS: grid-template-areas: 'header header header' 'nav main aside' 'footer footer footer';",
            
            "SIDEBAR-DRIVEN LAYOUT:
            - Prominent fixed/sticky sidebar (navigation or filters)
            - Main content scrolls independently
            - Dashboard or app feel
            CSS: Sidebar position: fixed or sticky, main with margin-left",
            
            "CENTERED CANVAS LAYOUT:
            - Content floats in center of viewport
            - Generous margins on all sides
            - Elegant, focused feel
            CSS: min-height: 100vh; display: flex; align-items: center; justify-content: center;",
            
            "FULL-WIDTH LAYOUT:
            - Edge-to-edge content, no margins
            - Immersive, bold feel
            - Sections span full viewport width
            CSS: Sections at 100vw, content inside with max-width container"
        ];

        // ========================================
        // GRID-BASED / MODULAR PATTERNS
        // ========================================
        $gridPatterns = [
            "CARD-BASED LAYOUT:
            - Content in discrete cards with shadows, borders, or backgrounds
            - Cards on grid, uniform or varied sizes
            - Hover effects on cards (lift, glow)
            CSS: display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;",
            
            "BENTO GRID LAYOUT:
            - Mixed card sizes like Japanese bento box
            - Some cards span 2 columns or 2 rows
            - Creates visual hierarchy through size
            CSS: grid-template-columns: repeat(4, 1fr); then span specific items with grid-column: span 2;",
            
            "DASHBOARD GRID LAYOUT:
            - Widgets/panels in organized grid
            - Different widgets: stats, charts, lists, actions
            - Clear hierarchy, data-focused
            CSS: Complex grid with named areas or explicit row/column spans",
            
            "RESPONSIVE GRID LAYOUT:
            - Columns reflow based on screen size
            - 4 cols → 2 cols → 1 col
            CSS: grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));",
            
            "NESTED GRID LAYOUT:
            - Grids within grids for complex layouts
            - Section has its own sub-grid
            CSS: Parent grid with children that are also display: grid"
        ];

        // ========================================
        // MASONRY / FLOW PATTERNS
        // ========================================
        $masonryPatterns = [
            "MASONRY LAYOUT:
            - Pinterest-style staggered grid
            - Items of varying heights fit together
            - No gaps, organic flow
            CSS: Use CSS columns: column-count: 3; column-gap: 20px; OR CSS Grid with masonry (where supported)",
            
            "WATERFALL LAYOUT:
            - Content flows down in columns
            - Each column fills independently
            - Good for feeds, galleries
            CSS: Similar to masonry, column-based",
            
            "ISOTOPE/FILTERABLE LAYOUT:
            - Grid with filter buttons above
            - Items rearrange on filter
            - Categories: All, Type A, Type B, etc.
            CSS: Flexbox or grid with filter UI, items have data-category"
        ];

        // ========================================
        // SPLIT / COMPARISON PATTERNS
        // ========================================
        $splitPatterns = [
            "SPLIT-SCREEN LAYOUT:
            - 50/50 vertical split
            - Left: content/text, Right: image/visual (or reverse)
            - Often used for hero sections
            CSS: display: grid; grid-template-columns: 1fr 1fr; min-height: 100vh;",
            
            "HORIZONTAL SPLIT LAYOUT:
            - Top/bottom split instead of left/right
            - Top: hero image, Bottom: content
            CSS: Stack with distinct sections, different heights",
            
            "DIAGONAL SPLIT LAYOUT:
            - Angled divider between sections
            - Creates dynamic, modern feel
            CSS: clip-path: polygon(...); or skewed pseudo-elements",
            
            "BEFORE/AFTER LAYOUT:
            - Two states side by side or with slider
            - Comparison, transformation showcase
            CSS: Slider with overflow: hidden and draggable divider",
            
            "DUAL-TRACK LAYOUT:
            - Two parallel content streams
            - E.g., Features vs Benefits, Starter vs Pro
            - Side by side comparison
            CSS: Two-column layout with aligned content"
        ];

        // ========================================
        // HERO / STORY-DRIVEN PATTERNS
        // ========================================
        $heroPatterns = [
            "HERO-CENTRIC LAYOUT:
            - Massive hero (80-100vh) dominates above fold
            - Below fold: supporting content in smaller sections
            - Hero is the star, everything else supports it
            CSS: Hero section min-height: 100vh or 80vh",
            
            "SCROLL-NARRATIVE LAYOUT:
            - Story unfolds as user scrolls
            - Each scroll position reveals new content
            - Sequential storytelling
            CSS: Sections are 100vh, scroll-snap or parallax effects",
            
            "SCROLLYTELLING LAYOUT:
            - Fixed visual that changes as text scrolls
            - Text panels on one side, sticky visual on other
            - Data visualization or product showcase
            CSS: Sticky element with position: sticky, content scrolls past",
            
            "PARALLAX-DRIVEN LAYOUT:
            - Background moves slower than foreground
            - Creates depth and immersion
            - Multiple parallax layers
            CSS: background-attachment: fixed; or JS-based parallax"
        ];

        // ========================================
        // ASYMMETRY / CREATIVE PATTERNS
        // ========================================
        $creativePatterns = [
            "ASYMMETRICAL LAYOUT:
            - Intentionally unbalanced composition
            - Elements off-center, varied sizes
            - Tension creates interest
            CSS: Grid with unequal columns like 2fr 1fr, items placed asymmetrically",
            
            "BROKEN GRID LAYOUT:
            - Elements break out of the grid intentionally
            - Overlapping, negative margins
            - Bold, agency-style
            CSS: Negative margins, transform: translate(), z-index layering",
            
            "DIAGONAL LAYOUT:
            - Angled sections and elements
            - Content flows diagonally
            - Dynamic, energetic feel
            CSS: transform: skewY(-5deg); with counter-skew on children",
            
            "OVERLAPPING LAYERS LAYOUT:
            - Elements overlap each other
            - Creates depth without 3D
            - Cards, images, text overlap
            CSS: Negative margins, position: relative with offset, z-index",
            
            "FLOATING ELEMENTS LAYOUT:
            - Key elements appear to float in space
            - Generous whitespace around floating items
            - Minimal, focused
            CSS: Elements with large margins, subtle shadows, isolated placement"
        ];

        // ========================================
        // MOBILE-INFLUENCED PATTERNS
        // ========================================
        $mobilePatterns = [
            "THUMB-ZONE LAYOUT:
            - Key actions in bottom half of viewport (mobile-first)
            - Navigation or CTAs at bottom
            - Translates to desktop as sticky bottom bar or clear hierarchy
            CSS: Fixed bottom bar or hero CTA in accessible position",
            
            "STACKED CARD LAYOUT:
            - Full-width cards stacked vertically
            - Each card is a complete unit
            - Works on all screen sizes
            CSS: Cards at 100% width, consistent padding, stacked",
            
            "PROGRESSIVE DISCLOSURE LAYOUT:
            - Expandable sections, accordions
            - User reveals content on demand
            - Reduces cognitive load
            CSS: Accordion or collapsible with smooth height transitions"
        ];

        // Randomly select one from each category
        $reading = $readingPatterns[array_rand($readingPatterns)];
        $structural = $structuralPatterns[array_rand($structuralPatterns)];
        $grid = $gridPatterns[array_rand($gridPatterns)];
        $masonry = $masonryPatterns[array_rand($masonryPatterns)];
        $split = $splitPatterns[array_rand($splitPatterns)];
        $hero = $heroPatterns[array_rand($heroPatterns)];
        $creative = $creativePatterns[array_rand($creativePatterns)];
        $mobile = $mobilePatterns[array_rand($mobilePatterns)];
        
        // Select 4-5 patterns to combine (not all, to avoid overload)
        $selected = [
            $reading,
            $structural, 
            rand(0,1) ? $grid : $masonry,
            rand(0,1) ? $split : $hero,
            $creative
        ];
        
        shuffle($selected);
        $patterns = implode("

", array_slice($selected, 0, 4));
        
        return <<<VARIANT
=== LAYOUT BLUEPRINT (IMPLEMENT THESE PATTERNS) ===

{$patterns}

=== IMPLEMENTATION RULES ===
1. COMBINE these patterns creatively - they should work together
2. The reading pattern defines the VISUAL FLOW - where eyes go
3. The structural pattern defines the OVERALL STRUCTURE
4. The creative pattern adds VISUAL INTEREST
5. Use the CSS hints provided - they are specific techniques
6. Don't just stack sections - CREATE SPATIAL RELATIONSHIPS
7. Vary section heights - not everything needs to be 100vh
8. Mix full-width and contained sections

VARIANT;
    }

    /**
     * System prompt - sets expectations but no design constraints
     */
    private static function buildSystemPrompt(string $style = 'modern'): string
    {
        return <<<PROMPT
You are a WORLD-CLASS web designer creating a stunning, unique website mockup.
Every design you create should look like it came from a top design agency.

YOUR DESIGN PHILOSOPHY:
- Each website must feel UNIQUE and CUSTOM - never generic templates
- Follow the industry and style guides EXACTLY - they contain specific instructions
- Be BOLD with your choices - memorable designs stand out
- Pay attention to micro-interactions and details
- Create visual hierarchy that guides the eye

TECHNICAL REQUIREMENTS:
- Complete HTML document with embedded CSS in <style> tag
- Include Google Fonts: <link href="https://fonts.googleapis.com/css2?family=..." rel="stylesheet">
- Font Awesome icons: <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
- Fully responsive (mobile-first approach with media queries)
- Smooth transitions: transition: all 0.3s ease
- Hover effects on all interactive elements
- Use CSS variables for colors: :root { --primary: #...; }
- Images: https://picsum.photos/WIDTH/HEIGHT?random=N (vary the random number)

CONTENT REQUIREMENTS:
- Write realistic, compelling copy - NO Lorem ipsum
- Include specific numbers and stats where appropriate
- Create believable testimonials with names and roles
- Write CTAs that create urgency

LAYOUT VARIETY:
- DON'T always use the same hero->features->testimonials pattern
- Try: Split hero, full-screen video background, diagonal sections, overlapping elements
- Use asymmetric layouts, broken grids, creative whitespace
- Consider: sticky headers, parallax hints, animated counters

CRITICAL: Complete the ENTIRE HTML. End with </body></html>.

OUTPUT: Only HTML code. No markdown, no explanations. Start with <!DOCTYPE html>.
PROMPT;
    }

    /**
     * User prompt - just the business description, AI decides everything else
     */
    private static function buildUserPrompt(string $prompt, string $industry, string $style, string $language): string
    {
        $industryGuide = self::getIndustryGuide($industry);
        $styleGuide = self::getStyleGuide($style);
        
        $userPrompt = "Create a stunning, professional website for:\n\n{$prompt}\n\n";
        $userPrompt .= "=== INDUSTRY-SPECIFIC DESIGN REQUIREMENTS ===\n";
        $userPrompt .= $industryGuide . "\n\n";
        $userPrompt .= "=== VISUAL STYLE REQUIREMENTS ===\n";
        $userPrompt .= $styleGuide . "\n\n";
        $layoutVariant = self::getLayoutVariant();
        $userPrompt .= $layoutVariant . "\n\n";
        
        $userPrompt .= "=== CRITICAL INSTRUCTIONS ===\n";
        $userPrompt .= "1. FOLLOW THE LAYOUT STRUCTURE ABOVE - it defines your hero, sections, and patterns\n";
        $userPrompt .= "2. Apply the INDUSTRY guide for content and section types\n";
        $userPrompt .= "3. Apply the STYLE guide for colors, typography, and visual effects\n";
        $userPrompt .= "4. The layout variant is MANDATORY - implement it exactly as described\n";
        $userPrompt .= "5. Be creative within these constraints - add polish and details\n";
        $userPrompt .= "6. Complete the ENTIRE HTML document. End with </body></html>.\n";

        if (!empty($language)) {
            $userPrompt .= "\n7. All text content must be written in {$language}.";
        }

        return $userPrompt;
    }

    // =========================================================================
    // HTML EXTRACTION & PARSING
    // =========================================================================

    /**
     * Extract HTML from AI response (handles various formats)
     * Updated 2026-02-06: Handles incomplete HTML from truncated responses
     */
    private static function extractHTML(string $response): string
    {
        $response = trim($response);

        // Already clean HTML (complete)
        if ((stripos($response, '<!DOCTYPE') === 0 || stripos($response, '<html') === 0) 
            && stripos($response, '</html>') !== false) {
            return $response;
        }
        
        // Already HTML but incomplete (truncated) - try to fix
        if (stripos($response, '<!DOCTYPE') === 0 || stripos($response, '<html') === 0) {
            return self::fixIncompleteHTML($response);
        }

        // Extract from markdown code block
        if (preg_match('/```(?:html)?\s*(<!DOCTYPE[\s\S]*?<\/html>)\s*```/i', $response, $matches)) {
            return trim($matches[1]);
        }

        // Extract DOCTYPE to </html>
        if (preg_match('/(<!DOCTYPE[\s\S]*?<\/html>)/i', $response, $matches)) {
            return trim($matches[1]);
        }

        // Extract <html> to </html> and add DOCTYPE
        if (preg_match('/(<html[\s\S]*?<\/html>)/i', $response, $matches)) {
            return '<!DOCTYPE html>' . "\n" . trim($matches[1]);
        }
        
        // Incomplete HTML in response - try to fix
        if (preg_match('/(<!DOCTYPE[\s\S]+)/i', $response, $matches)) {
            return self::fixIncompleteHTML(trim($matches[1]));
        }

        return '';
    }

    /**
     * Fix incomplete HTML from truncated AI responses
     * Closes any unclosed tags to make valid HTML
     */
    private static function fixIncompleteHTML(string $html): string
    {
        $needsClosing = [];
        
        // Check for unclosed <style> tag - close any open CSS rules first
        $styleOpenCount = preg_match_all('/<style[^>]*>/i', $html);
        $styleCloseCount = preg_match_all('/<\/style>/i', $html);
        
        if ($styleOpenCount > $styleCloseCount) {
            // Find how the CSS ends and try to close it properly
            // Count unclosed braces in the last style section
            if (preg_match('/<style[^>]*>([^<]*$)/is', $html, $lastStyle)) {
                $css = $lastStyle[1];
                $openBraces = substr_count($css, '{');
                $closeBraces = substr_count($css, '}');
                $unclosed = $openBraces - $closeBraces;
                
                // Close any unclosed braces
                $html .= str_repeat("\n}", max(0, $unclosed));
            }
            $html .= "\n    </style>";
            $needsClosing[] = 'style';
        }
        
        // Check for unclosed tags in order
        $tagsToCheck = ['head', 'body', 'html'];
        
        foreach ($tagsToCheck as $tag) {
            $openCount = preg_match_all("/<{$tag}[^>]*>/i", $html);
            $closeCount = preg_match_all("/<\/{$tag}>/i", $html);
            
            if ($openCount > $closeCount) {
                $html .= "\n</{$tag}>";
                $needsClosing[] = $tag;
            }
        }
        
        // Log what was fixed
        if (!empty($needsClosing)) {
            file_put_contents('/tmp/mockup_debug.log', 
                date('H:i:s') . " FIXED incomplete HTML, closed: " . implode(', ', $needsClosing) . "\n", 
                FILE_APPEND);
        }
        
        return $html;
    }
    /**
     * Extract structure from generated HTML using HTMLToJTBParser
     * Updated 2026-02-06: Full parsing instead of just metadata
     */
    private static function extractStructureFromHTML(string $html, string $prompt): array
    {
        // Use the new parser to extract full structure
        $parsed = HTMLToJTBParser::parse($html);
        
        // Get business name from title or h1
        $businessName = '';
        if (preg_match('/<title>([^<]+)<\/title>/i', $html, $matches)) {
            $businessName = trim(preg_replace('/\s*[-|].*$/', '', $matches[1]));
        }
        if (empty($businessName) && preg_match('/<h1[^>]*>([^<]+)<\/h1>/i', $html, $matches)) {
            $businessName = trim($matches[1]);
        }

        // Merge parsed structure with metadata
        return array_merge($parsed, [
            'business_name' => $businessName ?: 'Website',
            'prompt' => $prompt,
            'generated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
