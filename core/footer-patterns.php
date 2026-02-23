<?php
/**
 * Footer Pattern Registry
 * 
 * Pre-built footer HTML layouts with structural CSS.
 * AI generates only decorative CSS (colors, fonts, effects) — never the structure.
 * 
 * 15 patterns across 5 groups.
 * @since 2026-02-18
 */

class FooterPatternRegistry
{
    // ═══════════════════════════════════════
    // PATTERN DEFINITIONS
    // ═══════════════════════════════════════

    private static array $patterns = [
        // --- Minimal ---
        ['id'=>'minimal-single',    'group'=>'minimal',     'css_type'=>'single-row',
         'best_for'=>['personal-blog','writer','author','journalist','coach','therapist','counselor','portfolio']],
        ['id'=>'minimal-centered',  'group'=>'minimal',     'css_type'=>'centered',
         'best_for'=>['art-gallery','exhibition','minimalist','zen','japanese','architect','product-design']],
        ['id'=>'minimal-stacked',   'group'=>'minimal',     'css_type'=>'stacked',
         'best_for'=>['photography','videography','creative-agency','design-studio','illustrator','artist']],

        // --- Classic ---
        ['id'=>'classic-3col',      'group'=>'classic',     'css_type'=>'three-column',
         'best_for'=>['business','agency','consulting','construction','manufacturing','law-firm','accounting']],
        ['id'=>'classic-4col',      'group'=>'classic',     'css_type'=>'four-column',
         'best_for'=>['university','school','hospital','clinic','dental','medical','healthcare','nonprofit']],
        ['id'=>'classic-asymmetric','group'=>'classic',     'css_type'=>'asymmetric',
         'best_for'=>['restaurant','bakery','cafe','hotel','resort','spa','luxury','fine-dining']],

        // --- Modern ---
        ['id'=>'modern-split',      'group'=>'modern',      'css_type'=>'split',
         'best_for'=>['startup','tech','saas','platform','app','digital','ai','fintech']],
        ['id'=>'modern-magazine',   'group'=>'modern',      'css_type'=>'magazine',
         'best_for'=>['magazine','newspaper','news','blog','publication','content-creator','media']],
        ['id'=>'modern-bands',      'group'=>'modern',      'css_type'=>'bands',
         'best_for'=>['ecommerce','retail','fashion','beauty','cosmetics','jewelry','online-store']],

        // --- Creative ---
        ['id'=>'creative-wave',     'group'=>'creative',    'css_type'=>'wave',
         'best_for'=>['music','entertainment','nightclub','festival','event','creative-portfolio','motion-design']],
        ['id'=>'creative-diagonal', 'group'=>'creative',    'css_type'=>'diagonal',
         'best_for'=>['gym','fitness','sports','adventure','extreme-sports','outdoor','gaming','esports']],
        ['id'=>'creative-bigbrand', 'group'=>'creative',    'css_type'=>'big-brand',
         'best_for'=>['luxury','fashion-brand','winery','brewery','craft','artisan','organic']],

        // --- Detailed ---
        ['id'=>'detailed-mega',     'group'=>'detailed',    'css_type'=>'mega',
         'best_for'=>['marketplace','directory','portal','large-business','corporation','enterprise']],
        ['id'=>'detailed-corporate','group'=>'detailed',    'css_type'=>'corporate',
         'best_for'=>['financial-advisor','insurance','wealth-management','professional','corporate']],
        ['id'=>'detailed-inforich', 'group'=>'detailed',    'css_type'=>'info-rich',
         'best_for'=>['travel','tourism','real-estate','automotive','service-business','emergency-service']],
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

        // Find industry matches
        $candidates = [];
        foreach (self::$patterns as $p) {
            if (in_array($industry, $p['best_for'])) {
                $candidates[] = $p['id'];
            }
        }

        // Fallback: style-based
        if (empty($candidates)) {
            $styleMap = [
                'minimal'    => ['minimal-single','minimal-centered','minimal-stacked'],
                'bold'       => ['creative-diagonal','modern-bands','creative-bigbrand'],
                'elegant'    => ['classic-asymmetric','minimal-centered','creative-bigbrand'],
                'modern'     => ['modern-split','modern-magazine','modern-bands'],
                'corporate'  => ['classic-3col','classic-4col','detailed-corporate'],
                'playful'    => ['creative-wave','creative-diagonal','modern-magazine'],
                'luxurious'  => ['creative-bigbrand','classic-asymmetric','minimal-centered'],
                'creative'   => ['creative-wave','creative-diagonal','creative-bigbrand'],
                'vintage'    => ['classic-3col','classic-asymmetric','detailed-corporate'],
                'dark'       => ['creative-wave','creative-diagonal','modern-split'],
                'clean'      => ['minimal-single','minimal-centered','classic-3col'],
                'professional'=>['classic-3col','detailed-corporate','classic-4col'],
                'artistic'   => ['minimal-stacked','creative-wave','creative-bigbrand'],
                'rustic'     => ['classic-asymmetric','creative-bigbrand','classic-3col'],
                'futuristic' => ['modern-split','modern-bands','creative-diagonal'],
                'warm'       => ['classic-asymmetric','creative-bigbrand','classic-3col'],
                'geometric'  => ['modern-split','modern-bands','creative-diagonal'],
                'organic'    => ['creative-bigbrand','classic-asymmetric','minimal-stacked'],
                'industrial' => ['creative-diagonal','modern-split','classic-3col'],
                'sophisticated'=>['minimal-centered','detailed-corporate','classic-asymmetric'],
                'vibrant'    => ['creative-wave','creative-diagonal','modern-magazine'],
                'serene'     => ['minimal-single','minimal-centered','minimal-stacked'],
            ];
            $pool = $styleMap[$style] ?? ['classic-3col','modern-split','minimal-single'];
            $candidates = $pool;
        }

        if (empty($candidates)) return 'classic-3col';
        return $candidates[array_rand($candidates)];
    }

    /**
     * Render a footer pattern.
     * Returns ['html'=>..., 'structural_css'=>..., 'pattern_id'=>..., 'classes'=>[...]]
     */
    public static function render(string $patternId, string $prefix, array $brief): array
    {
        $def = self::findPattern($patternId);
        if (!$def) {
            $patternId = 'classic-3col';
            $def = self::findPattern('classic-3col');
        }

        $html = self::buildHTML($patternId, $prefix, $brief);
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
     * Get decorative CSS guide for a specific footer pattern.
     * Returns hints for AI to generate visual/decorative CSS (NOT structural layout).
     */
    public static function getDecorativeGuide(string $patternId): string
    {
        return match($patternId) {
            'minimal-single' => <<<'GUIDE'
Use a thin 1px top border in a muted tone or subtle accent color.
Background should be barely tinted or transparent — almost invisible.
Copyright and links use small, lightweight font (font-weight 300-400).
Social icons: no background, just icon color with hover color shift.
Overall feel: airy, lots of whitespace, nothing heavy or decorated.
GUIDE,

            'minimal-centered' => <<<'GUIDE'
Background: very subtle solid color or gentle gradient (near-white or near-black).
Text is centered and uses generous letter-spacing (0.5-1px) on brand name.
Social icons styled as simple circles with thin borders, hover fills them.
Links use understated color with smooth underline-on-hover animation.
Everything breathes — vertical spacing between elements is generous.
GUIDE,

            'minimal-stacked' => <<<'GUIDE'
Thin horizontal dividers (1px, low opacity) separate each stacked row.
Background is flat, single color — no gradients, no patterns.
Links row uses horizontal inline layout with subtle separator dots between items.
Social icons are small and monochrome, gaining color only on hover.
Typography is clean and uniform — one font weight, consistent sizing throughout.
GUIDE,

            'classic-3col' => <<<'GUIDE'
Column headings have a small border-bottom accent (2-3px, brand color).
Background uses a dark or contrasting tone from the palette.
Links have no underline by default, show underline or color shift on hover.
Footer bottom bar is slightly darker than the main footer area.
Social icons use rounded or square background shapes with brand colors.
Contact info icons are tinted with the accent color.
GUIDE,

            'classic-4col' => <<<'GUIDE'
Newsletter input has a clean border style; button uses bold brand color fill.
Column headings are uppercase with letter-spacing for authority.
Background is rich and solid — dark navy, charcoal, or deep brand tone.
Links are light-colored on dark bg, with opacity hover effect (0.7 → 1).
Newsletter button has slight border-radius and hover brightness shift.
Consistent vertical rhythm — all columns align at the top.
GUIDE,

            'classic-asymmetric' => <<<'GUIDE'
Brand column (wide side) feels premium — larger font size for description.
Use elegant serif or display font for the brand name if available.
Background can have a very subtle texture or noise overlay for richness.
Social icons styled as refined circles or squares with thin borders.
Right-side columns use smaller, tighter typography for contrast with the brand area.
Hover effects are gentle — color transitions, no dramatic transforms.
GUIDE,

            'modern-split' => <<<'GUIDE'
One half has an accent/tinted background, the other is neutral — creates contrast.
Use a bold color block or gradient on the accent side.
Typography mixes weights: bold brand name, light body text.
Social icons use modern pill or rounded-square shapes.
Links have a left-border accent on hover or an animated underline effect.
Clean geometric feel — sharp edges, no rounded corners on containers.
GUIDE,

            'modern-magazine' => <<<'GUIDE'
Top brand row has strong border-bottom — thick (2-4px) editorial line.
Typography is editorial: mix serif headings with sans-serif body text.
Category/nav links styled as tag-like elements or bold uppercase labels.
Background uses newsprint-inspired tones or high contrast black/white.
Contact area has a slightly different background tint for visual separation.
Hover effects on links are quick, snappy — immediate color swap.
GUIDE,

            'modern-bands' => <<<'GUIDE'
Each horizontal band has a different shade — dark, medium, darker pattern.
Top band (brand) is the darkest with prominent branding.
Middle band uses a slightly lighter or contrasting tone for navigation.
Bottom copyright band is the most subdued — smallest text, lowest contrast.
Social icons are bright/white on dark bands, with scale-up hover effect.
Transitions between bands are seamless — no visible borders, just color shifts.
GUIDE,

            'creative-wave' => <<<'GUIDE'
SVG wave uses a gradient fill — blend two brand colors or brand-to-transparent.
Footer background is dark/rich to contrast with the wave shape above.
Content feels like it floats below the wave — generous top padding.
Social icons glow or have shadow effects on hover for a dreamy feel.
Use subtle text-shadow on headings for depth against the dark background.
Links have a soft glow or color-shift hover, matching the wave gradient palette.
GUIDE,

            'creative-diagonal' => <<<'GUIDE'
Diagonal background element uses a semi-transparent gradient or accent color.
Bold, high-energy color palette — strong contrasts and saturated tones.
Text uses heavy font weights; headings may be uppercase with tight letter-spacing.
Social icons are angular or have sharp-cornered backgrounds (no border-radius).
Hover effects are dynamic — scale, color shift, or skew micro-animations.
Overall feel: energetic, sporty, forward-leaning like the diagonal itself.
GUIDE,

            'creative-bigbrand' => <<<'GUIDE'
Brand name is oversized (3-5rem) and acts as a visual watermark or hero element.
Use very low opacity (0.03-0.08) giant text in the background as a watermark.
Brand text may use letter-spacing (2-5px) for a luxurious spread-out feel.
Color palette is restrained — monochrome or two-tone, elegant and minimal.
Social icons are delicate — thin line style, small, refined hover color change.
Bottom details section uses smaller, quieter typography to not compete with the hero brand.
GUIDE,

            'detailed-mega' => <<<'GUIDE'
Rich background — deep dark color with enough contrast for many text elements.
Column headings use consistent styling: uppercase, small font-size, letter-spacing.
Map link has an icon + text combo with hover underline animation.
Newsletter section stands out with a slightly different background tint or bordered box.
Links are organized in tight columns with small font-size (0.875rem) and hover opacity shift.
Footer bottom bar is a darker strip with centered, very small copyright text.
GUIDE,

            'detailed-corporate' => <<<'GUIDE'
Top brand section is clean and centered with generous padding — feels authoritative.
Section dividers use thin borders (1px) in muted corporate tones.
Hours and contact info use structured, tabular-feeling typography.
Color palette is professional — navy, slate, charcoal with subtle accent color.
Social icons are conservative — simple, small, uniform shape with brand color hover.
Overall typography is crisp: system fonts or professional sans-serif, consistent sizing.
GUIDE,

            'detailed-inforich' => <<<'GUIDE'
Primary brand column uses larger text and more visual weight than info columns.
Map/directions link is styled as a small button or pill-shaped CTA.
Hours section uses clear formatting — consider monospace or tabular-nums for alignment.
Multiple info sections have subtle left borders or top borders for visual grouping.
Social icons are medium-sized with background shapes and hover color transitions.
Background has enough contrast to support dense text — dark with light text works best.
GUIDE,

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

    private static function s_brand(string $p): string
    {
        return <<<HTML
      <?php if (\$logo = theme_get('brand.logo')): ?>
        <img loading="lazy" src="<?= esc(\$logo) ?>" alt="<?= esc(\$siteName) ?>" class="{$p}-footer-logo" data-ts="brand.logo">
      <?php else: ?>
        <span class="{$p}-footer-brand-text" data-ts="brand.site_name"><?= esc(theme_get('brand.site_name', \$siteName)) ?></span>
      <?php endif; ?>
HTML;
    }

    private static function s_description(string $p): string
    {
        return <<<HTML
      <p class="{$p}-footer-desc" data-ts="footer.description"><?= esc(theme_get('footer.description', '')) ?></p>
HTML;
    }

    private static function s_nav(string $p): string
    {
        return <<<HTML
    <?= render_menu('footer', ['class' => '{$p}-footer-links', 'link_class' => '{$p}-footer-link', 'wrap' => false]) ?>
HTML;
    }

    private static function s_social(string $p): string
    {
        return <<<HTML
    <div class="{$p}-footer-social">
      <?php if (\$fb = theme_get('footer.facebook')): ?><a href="<?= esc(\$fb) ?>" target="_blank" class="{$p}-social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
      <?php if (\$ig = theme_get('footer.instagram')): ?><a href="<?= esc(\$ig) ?>" target="_blank" class="{$p}-social-link" aria-label="Instagram"><i class="fab fa-instagram"></i></a><?php endif; ?>
      <?php if (\$tw = theme_get('footer.twitter')): ?><a href="<?= esc(\$tw) ?>" target="_blank" class="{$p}-social-link" aria-label="Twitter"><i class="fab fa-twitter"></i></a><?php endif; ?>
      <?php if (\$li = theme_get('footer.linkedin')): ?><a href="<?= esc(\$li) ?>" target="_blank" class="{$p}-social-link" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
    </div>
HTML;
    }

    private static function s_copyright(string $p): string
    {
        return <<<HTML
    <p class="{$p}-footer-copyright">&copy; <?= date('Y') ?> <?= esc(theme_get('brand.site_name', \$siteName)) ?>. All rights reserved.</p>
HTML;
    }

    private static function s_contact_info(string $p): string
    {
        return <<<HTML
      <?php if (theme_get('footer.phone')): ?><p class="{$p}-footer-phone"><i class="fas fa-phone"></i> <a href="tel:<?= esc(theme_get('footer.phone')) ?>" data-ts="footer.phone"><?= esc(theme_get('footer.phone')) ?></a></p><?php endif; ?>
      <?php if (theme_get('footer.email')): ?><p class="{$p}-footer-email"><i class="fas fa-envelope"></i> <a href="mailto:<?= esc(theme_get('footer.email')) ?>" data-ts="footer.email"><?= esc(theme_get('footer.email')) ?></a></p><?php endif; ?>
      <?php if (theme_get('footer.address')): ?><p class="{$p}-footer-address" data-ts="footer.address"><i class="fas fa-map-marker-alt"></i> <?= esc(theme_get('footer.address')) ?></p><?php endif; ?>
HTML;
    }

    private static function s_nav_titled(string $p, string $title = 'Quick Links'): string
    {
        return <<<HTML
    <div class="{$p}-footer-nav-section">
      <h4 class="{$p}-footer-title">{$title}</h4>
      <?= render_menu('footer', ['class' => '{$p}-footer-links', 'link_class' => '{$p}-footer-link', 'wrap' => false]) ?>
    </div>
HTML;
    }

    private static function s_contact_titled(string $p): string
    {
        return <<<HTML
    <div class="{$p}-footer-contact">
      <h4 class="{$p}-footer-title" data-ts="footer.contact_title"><?= esc(theme_get('footer.contact_title', 'Contact')) ?></h4>
      <?php if (theme_get('footer.phone')): ?><p class="{$p}-footer-phone"><i class="fas fa-phone"></i> <a href="tel:<?= esc(theme_get('footer.phone')) ?>" data-ts="footer.phone"><?= esc(theme_get('footer.phone')) ?></a></p><?php endif; ?>
      <?php if (theme_get('footer.email')): ?><p class="{$p}-footer-email"><i class="fas fa-envelope"></i> <a href="mailto:<?= esc(theme_get('footer.email')) ?>" data-ts="footer.email"><?= esc(theme_get('footer.email')) ?></a></p><?php endif; ?>
      <?php if (theme_get('footer.address')): ?><p class="{$p}-footer-address" data-ts="footer.address"><i class="fas fa-map-marker-alt"></i> <?= esc(theme_get('footer.address')) ?></p><?php endif; ?>
    </div>
HTML;
    }

    private static function s_newsletter(string $p): string
    {
        return <<<HTML
    <div class="{$p}-footer-newsletter">
      <h4 class="{$p}-footer-title" data-ts="footer.newsletter_title"><?= esc(theme_get('footer.newsletter_title', 'Newsletter')) ?></h4>
      <p class="{$p}-footer-newsletter-desc" data-ts="footer.newsletter_description"><?= esc(theme_get('footer.newsletter_description', 'Stay updated with our latest news')) ?></p>
      <form class="{$p}-newsletter-form" action="/newsletter/signup" method="post">
        <input type="email" placeholder="Enter your email" class="{$p}-newsletter-input" required>
        <button type="submit" class="{$p}-newsletter-btn">Subscribe</button>
      </form>
    </div>
HTML;
    }

    // ═══════════════════════════════════════
    // HTML BUILDERS
    // ═══════════════════════════════════════

    private static function buildHTML(string $id, string $p, array $brief): string
    {
        $brand = self::s_brand($p);
        $desc = self::s_description($p);
        $nav = self::s_nav($p);
        $social = self::s_social($p);
        $copyright = self::s_copyright($p);
        $contact = self::s_contact_info($p);
        $navTitled = self::s_nav_titled($p);
        $contactTitled = self::s_contact_titled($p);
        $newsletter = self::s_newsletter($p);

        return match($id) {

            // ─── MINIMAL ────────────────────────
            'minimal-single' => <<<HTML
<footer class="{$p}-footer {$p}-footer--minimal">
  <div class="{$p}-footer-inner">
    <div class="{$p}-footer-brand">
{$brand}
    </div>
    <div class="{$p}-footer-nav">
{$nav}
    </div>
{$social}
  </div>
  <div class="{$p}-footer-bottom">
{$copyright}
  </div>
</footer>
HTML,

            'minimal-centered' => <<<HTML
<footer class="{$p}-footer {$p}-footer--minimal-center">
  <div class="{$p}-footer-inner">
    <div class="{$p}-footer-brand">
{$brand}
{$desc}
    </div>
{$social}
    <div class="{$p}-footer-nav">
{$nav}
    </div>
{$copyright}
  </div>
</footer>
HTML,

            'minimal-stacked' => <<<HTML
<footer class="{$p}-footer {$p}-footer--minimal-stack">
  <div class="{$p}-footer-inner">
    <div class="{$p}-footer-brand">
{$brand}
{$desc}
    </div>
    <div class="{$p}-footer-nav">
{$nav}
    </div>
{$social}
{$copyright}
  </div>
</footer>
HTML,

            // ─── CLASSIC ────────────────────────
            'classic-3col' => <<<HTML
<footer class="{$p}-footer {$p}-footer--classic">
  <div class="{$p}-footer-main">
    <div class="{$p}-footer-inner">
      <div class="{$p}-footer-col {$p}-footer-brand-col">
        <div class="{$p}-footer-brand">
{$brand}
        </div>
{$desc}
{$social}
      </div>
      <div class="{$p}-footer-col">
{$navTitled}
      </div>
      <div class="{$p}-footer-col">
{$contactTitled}
      </div>
    </div>
  </div>
  <div class="{$p}-footer-bottom">
    <div class="{$p}-footer-inner">
{$copyright}
    </div>
  </div>
</footer>
HTML,

            'classic-4col' => <<<HTML
<footer class="{$p}-footer {$p}-footer--classic-4col">
  <div class="{$p}-footer-main">
    <div class="{$p}-footer-inner">
      <div class="{$p}-footer-col {$p}-footer-brand-col">
        <div class="{$p}-footer-brand">
{$brand}
        </div>
{$desc}
{$social}
      </div>
      <div class="{$p}-footer-col">
{$navTitled}
      </div>
      <div class="{$p}-footer-col">
{$contactTitled}
      </div>
      <div class="{$p}-footer-col">
{$newsletter}
      </div>
    </div>
  </div>
  <div class="{$p}-footer-bottom">
    <div class="{$p}-footer-inner">
{$copyright}
    </div>
  </div>
</footer>
HTML,

            'classic-asymmetric' => <<<HTML
<footer class="{$p}-footer {$p}-footer--asymmetric">
  <div class="{$p}-footer-main">
    <div class="{$p}-footer-inner">
      <div class="{$p}-footer-primary">
        <div class="{$p}-footer-brand">
{$brand}
        </div>
{$desc}
{$social}
      </div>
      <div class="{$p}-footer-secondary">
        <div class="{$p}-footer-nav-wrapper">
{$navTitled}
        </div>
        <div class="{$p}-footer-contact-wrapper">
{$contactTitled}
        </div>
      </div>
    </div>
  </div>
  <div class="{$p}-footer-bottom">
    <div class="{$p}-footer-inner">
{$copyright}
    </div>
  </div>
</footer>
HTML,

            // ─── MODERN ─────────────────────────
            'modern-split' => <<<HTML
<footer class="{$p}-footer {$p}-footer--split">
  <div class="{$p}-footer-inner">
    <div class="{$p}-footer-left">
      <div class="{$p}-footer-brand">
{$brand}
      </div>
{$desc}
{$contact}
    </div>
    <div class="{$p}-footer-right">
      <div class="{$p}-footer-nav-group">
{$navTitled}
{$social}
      </div>
    </div>
  </div>
  <div class="{$p}-footer-bottom">
    <div class="{$p}-footer-inner">
{$copyright}
    </div>
  </div>
</footer>
HTML,

            'modern-magazine' => <<<HTML
<footer class="{$p}-footer {$p}-footer--magazine">
  <div class="{$p}-footer-top">
    <div class="{$p}-footer-inner">
      <div class="{$p}-footer-brand-row">
        <div class="{$p}-footer-brand">
{$brand}
        </div>
{$social}
      </div>
    </div>
  </div>
  <div class="{$p}-footer-main">
    <div class="{$p}-footer-inner">
      <div class="{$p}-footer-content">
{$desc}
      </div>
      <div class="{$p}-footer-nav-area">
{$nav}
      </div>
      <div class="{$p}-footer-contact-area">
{$contact}
      </div>
    </div>
  </div>
  <div class="{$p}-footer-bottom">
    <div class="{$p}-footer-inner">
{$copyright}
    </div>
  </div>
</footer>
HTML,

            'modern-bands' => <<<HTML
<footer class="{$p}-footer {$p}-footer--bands">
  <div class="{$p}-footer-dark">
    <div class="{$p}-footer-inner">
      <div class="{$p}-footer-brand">
{$brand}
      </div>
{$desc}
{$social}
    </div>
  </div>
  <div class="{$p}-footer-light">
    <div class="{$p}-footer-inner">
      <div class="{$p}-footer-nav-section">
{$nav}
      </div>
      <div class="{$p}-footer-contact-section">
{$contact}
      </div>
    </div>
  </div>
  <div class="{$p}-footer-bottom">
    <div class="{$p}-footer-inner">
{$copyright}
    </div>
  </div>
</footer>
HTML,

            // ─── CREATIVE ───────────────────────
            'creative-wave' => <<<HTML
<footer class="{$p}-footer {$p}-footer--wave">
  <div class="{$p}-footer-wave-divider">
    <svg viewBox="0 0 1200 120" class="{$p}-wave-svg">
      <path d="M0,0V46.29c0,0,184.27,36.57,479.47,26.39C779.67,62.5,1200,30.87,1200,30.87V0Z" class="{$p}-wave-path"></path>
    </svg>
  </div>
  <div class="{$p}-footer-inner">
    <div class="{$p}-footer-content">
      <div class="{$p}-footer-brand-area">
        <div class="{$p}-footer-brand">
{$brand}
        </div>
{$desc}
      </div>
      <div class="{$p}-footer-nav-area">
{$navTitled}
      </div>
      <div class="{$p}-footer-contact-area">
{$contactTitled}
      </div>
      <div class="{$p}-footer-social-area">
        <h4 class="{$p}-footer-title">Follow Us</h4>
{$social}
      </div>
    </div>
{$copyright}
  </div>
</footer>
HTML,

            'creative-diagonal' => <<<HTML
<footer class="{$p}-footer {$p}-footer--diagonal">
  <div class="{$p}-footer-diagonal-bg"></div>
  <div class="{$p}-footer-inner">
    <div class="{$p}-footer-primary-section">
      <div class="{$p}-footer-brand">
{$brand}
      </div>
{$desc}
{$social}
    </div>
    <div class="{$p}-footer-secondary-section">
      <div class="{$p}-footer-nav-block">
{$navTitled}
      </div>
      <div class="{$p}-footer-contact-block">
{$contactTitled}
      </div>
    </div>
  </div>
  <div class="{$p}-footer-bottom">
    <div class="{$p}-footer-inner">
{$copyright}
    </div>
  </div>
</footer>
HTML,

            'creative-bigbrand' => <<<HTML
<footer class="{$p}-footer {$p}-footer--bigbrand">
  <div class="{$p}-footer-hero">
    <div class="{$p}-footer-inner">
      <div class="{$p}-footer-brand-large">
{$brand}
      </div>
{$desc}
{$social}
    </div>
  </div>
  <div class="{$p}-footer-details">
    <div class="{$p}-footer-inner">
      <div class="{$p}-footer-info-grid">
        <div class="{$p}-footer-nav-section">
{$navTitled}
        </div>
        <div class="{$p}-footer-contact-section">
{$contactTitled}
        </div>
      </div>
    </div>
  </div>
  <div class="{$p}-footer-bottom">
    <div class="{$p}-footer-inner">
{$copyright}
    </div>
  </div>
</footer>
HTML,

            // ─── DETAILED ───────────────────────
            'detailed-mega' => <<<HTML
<footer class="{$p}-footer {$p}-footer--mega">
  <div class="{$p}-footer-main">
    <div class="{$p}-footer-inner">
      <div class="{$p}-footer-mega-grid">
        <div class="{$p}-footer-brand-section">
          <div class="{$p}-footer-brand">
{$brand}
          </div>
{$desc}
{$social}
          <?php if (theme_get('footer.address')): ?>
          <div class="{$p}-footer-map-link">
            <a href="https://maps.google.com/?q=<?= urlencode(theme_get('footer.address')) ?>" target="_blank" class="{$p}-map-link">
              <i class="fas fa-map"></i> View on Map
            </a>
          </div>
          <?php endif; ?>
        </div>
        <div class="{$p}-footer-nav-section">
{$navTitled}
        </div>
        <div class="{$p}-footer-contact-section">
{$contactTitled}
        </div>
        <div class="{$p}-footer-newsletter-section">
{$newsletter}
        </div>
      </div>
    </div>
  </div>
  <div class="{$p}-footer-bottom">
    <div class="{$p}-footer-inner">
{$copyright}
    </div>
  </div>
</footer>
HTML,

            'detailed-corporate' => <<<HTML
<footer class="{$p}-footer {$p}-footer--corporate">
  <div class="{$p}-footer-top">
    <div class="{$p}-footer-inner">
      <div class="{$p}-footer-brand-section">
        <div class="{$p}-footer-brand">
{$brand}
        </div>
{$desc}
      </div>
    </div>
  </div>
  <div class="{$p}-footer-main">
    <div class="{$p}-footer-inner">
      <div class="{$p}-footer-corporate-grid">
        <div class="{$p}-footer-services-section">
          <h4 class="{$p}-footer-title" data-ts="footer.services_title"><?= esc(theme_get('footer.services_title', 'Services')) ?></h4>
{$nav}
        </div>
        <div class="{$p}-footer-contact-section">
{$contactTitled}
        </div>
        <div class="{$p}-footer-hours-section">
          <h4 class="{$p}-footer-title" data-ts="footer.hours_title"><?= esc(theme_get('footer.hours_title', 'Hours')) ?></h4>
          <?php if (theme_get('footer.hours')): ?>
          <p class="{$p}-footer-hours" data-ts="footer.hours"><?= esc(theme_get('footer.hours')) ?></p>
          <?php endif; ?>
        </div>
        <div class="{$p}-footer-social-section">
          <h4 class="{$p}-footer-title">Follow Us</h4>
{$social}
        </div>
      </div>
    </div>
  </div>
  <div class="{$p}-footer-bottom">
    <div class="{$p}-footer-inner">
{$copyright}
    </div>
  </div>
</footer>
HTML,

            'detailed-inforich' => <<<HTML
<footer class="{$p}-footer {$p}-footer--inforich">
  <div class="{$p}-footer-main">
    <div class="{$p}-footer-inner">
      <div class="{$p}-footer-primary">
        <div class="{$p}-footer-brand">
{$brand}
        </div>
{$desc}
      </div>
      <div class="{$p}-footer-info-sections">
        <div class="{$p}-footer-nav-section">
{$navTitled}
        </div>
        <div class="{$p}-footer-contact-section">
{$contactTitled}
          <?php if (theme_get('footer.address')): ?>
          <div class="{$p}-footer-map">
            <a href="https://maps.google.com/?q=<?= urlencode(theme_get('footer.address')) ?>" target="_blank" class="{$p}-map-link">
              <i class="fas fa-map-marker-alt"></i> Get Directions
            </a>
          </div>
          <?php endif; ?>
        </div>
        <div class="{$p}-footer-extra-section">
          <h4 class="{$p}-footer-title" data-ts="footer.hours_title"><?= esc(theme_get('footer.hours_title', 'Hours')) ?></h4>
          <?php if (theme_get('footer.hours')): ?>
          <p class="{$p}-footer-hours" data-ts="footer.hours"><?= esc(theme_get('footer.hours')) ?></p>
          <?php endif; ?>
{$social}
        </div>
      </div>
    </div>
  </div>
  <div class="{$p}-footer-bottom">
    <div class="{$p}-footer-inner">
{$copyright}
    </div>
  </div>
</footer>
HTML,

            // fallback
            default => self::buildHTML('classic-3col', $p, $brief),
        };
    }

    // ═══════════════════════════════════════
    // CSS BUILDERS
    // ═══════════════════════════════════════

    private static function buildStructuralCSS(string $cssType, string $p): string
    {
        $css = self::css_base($p);

        $css .= match($cssType) {
            'single-row'     => self::css_single_row($p),
            'centered'       => self::css_centered($p),
            'stacked'        => self::css_stacked($p),
            'three-column'   => self::css_three_column($p),
            'four-column'    => self::css_four_column($p),
            'asymmetric'     => self::css_asymmetric($p),
            'split'          => self::css_split($p),
            'magazine'       => self::css_magazine($p),
            'bands'          => self::css_bands($p),
            'wave'           => self::css_wave($p),
            'diagonal'       => self::css_diagonal($p),
            'big-brand'      => self::css_big_brand($p),
            'mega'           => self::css_mega($p),
            'corporate'      => self::css_corporate($p),
            'info-rich'      => self::css_info_rich($p),
            default          => self::css_three_column($p),
        };

        $css .= self::css_mobile($p, $cssType);

        return $css;
    }

    // --- Base (shared by ALL) ---
    private static function css_base(string $p): string
    {
        return <<<CSS

/* ═══ Footer Structural CSS (auto-generated — do not edit) ═══ */
.{$p}-footer {
  margin-top: auto;
}
.{$p}-footer-inner {
  max-width: 1280px; margin: 0 auto;
  padding: 0 clamp(16px, 3vw, 40px);
}
.{$p}-footer-brand {
  display: inline-flex; align-items: center; gap: 8px;
  text-decoration: none;
}
.{$p}-footer-logo {
  height: 32px; width: auto; display: block;
}
.{$p}-footer-brand-text {
  font-size: 1.25rem; font-weight: 700;
}
.{$p}-footer-desc {
  margin: 12px 0 0 0; line-height: 1.6;
}
.{$p}-footer-links {
  list-style: none; margin: 0; padding: 0;
  display: flex; flex-direction: column; gap: 6px;
}
.{$p}-footer-link {
  text-decoration: none; padding: 2px 0;
  display: inline-block; transition: color 0.2s;
  font-size: 0.9375rem; opacity: 0.85;
}
.{$p}-footer-link:hover {
  opacity: 1;
}
.{$p}-footer-social {
  display: flex; gap: 12px; align-items: center; margin-top: 16px;
}
.{$p}-social-link {
  display: inline-flex; align-items: center; justify-content: center;
  width: 36px; height: 36px; text-decoration: none;
  transition: all 0.2s;
}
.{$p}-footer-title {
  font-size: 1rem; font-weight: 600; margin: 0 0 12px 0;
}
.{$p}-footer-phone, .{$p}-footer-email, .{$p}-footer-address {
  margin: 8px 0; display: flex; align-items: center; gap: 8px;
}
.{$p}-footer-phone a, .{$p}-footer-email a {
  text-decoration: none; color: inherit;
}
.{$p}-footer-copyright {
  margin: 0; font-size: 0.875rem; opacity: 0.6;
  padding-top: 24px; border-top: 1px solid rgba(128,128,128,0.15);
}
.{$p}-footer-bottom {
  padding: 16px 0; border-top: 1px solid rgba(128,128,128,0.15);
}
/* Hide empty contact/social areas */
.{$p}-footer-contact-area:empty,
.{$p}-footer-social-area:has(.{$p}-footer-social:empty),
.{$p}-footer-contact:not(:has(.{$p}-footer-phone, .{$p}-footer-email, .{$p}-footer-address)) {
  display: none;
}
.{$p}-newsletter-form {
  display: flex; gap: 8px; margin-top: 12px;
}
.{$p}-newsletter-input {
  flex: 1; padding: 8px 12px; border: 1px solid rgba(128,128,128,0.3);
  border-radius: 4px; outline: none;
}
.{$p}-newsletter-btn {
  padding: 8px 16px; background: var(--primary, #007cba);
  color: var(--primary-contrast, #fff); border: none; border-radius: 4px;
  cursor: pointer; font-weight: 600; transition: background 0.2s;
}
/* Footer responsive */
@media (max-width: 768px) {
  .{$p}-footer-content {
    grid-template-columns: 1fr !important;
    gap: 32px !important;
  }
}

CSS;
    }

    // --- Single Row (minimal-single) ---
    private static function css_single_row(string $p): string
    {
        return <<<CSS
.{$p}-footer--minimal .{$p}-footer-inner {
  display: flex; align-items: center; gap: 24px;
  padding-top: 24px; padding-bottom: 8px;
}
.{$p}-footer--minimal .{$p}-footer-nav {
  flex: 1; display: flex; justify-content: center;
}
.{$p}-footer--minimal .{$p}-footer-social {
  margin-top: 0;
}
.{$p}-footer--minimal .{$p}-footer-bottom {
  text-align: center; border-top: none;
  padding-top: 8px; padding-bottom: 24px;
}

CSS;
    }

    // --- Centered (minimal-centered) ---
    private static function css_centered(string $p): string
    {
        return <<<CSS
.{$p}-footer--minimal-center .{$p}-footer-inner {
  text-align: center; padding: 32px clamp(16px, 3vw, 40px);
}
.{$p}-footer--minimal-center .{$p}-footer-brand {
  justify-content: center; margin-bottom: 12px;
}
.{$p}-footer--minimal-center .{$p}-footer-social {
  justify-content: center;
}
.{$p}-footer--minimal-center .{$p}-footer-nav {
  margin: 24px 0;
}
.{$p}-footer--minimal-center .{$p}-footer-links {
  justify-content: center;
}

CSS;
    }

    // --- Stacked (minimal-stacked) ---
    private static function css_stacked(string $p): string
    {
        return <<<CSS
.{$p}-footer--minimal-stack .{$p}-footer-inner {
  display: flex; flex-direction: column; gap: 20px;
  padding: 32px clamp(16px, 3vw, 40px);
}
.{$p}-footer--minimal-stack .{$p}-footer-brand {
  align-self: flex-start;
}
.{$p}-footer--minimal-stack .{$p}-footer-social {
  margin-top: 0; align-self: flex-start;
}

CSS;
    }

    // --- Three Column (classic-3col) ---
    private static function css_three_column(string $p): string
    {
        return <<<CSS
.{$p}-footer--classic .{$p}-footer-main {
  padding: 48px 0;
}
.{$p}-footer--classic .{$p}-footer-inner {
  display: grid; grid-template-columns: 2fr 1fr 1fr;
  gap: 32px; align-items: flex-start;
}
.{$p}-footer--classic .{$p}-footer-brand-col .{$p}-footer-brand {
  margin-bottom: 12px;
}

CSS;
    }

    // --- Four Column (classic-4col) ---
    private static function css_four_column(string $p): string
    {
        return <<<CSS
.{$p}-footer--classic-4col .{$p}-footer-main {
  padding: 48px 0;
}
.{$p}-footer--classic-4col .{$p}-footer-inner {
  display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr;
  gap: 32px; align-items: flex-start;
}
.{$p}-footer--classic-4col .{$p}-footer-brand-col .{$p}-footer-brand {
  margin-bottom: 12px;
}

CSS;
    }

    // --- Asymmetric (classic-asymmetric) ---
    private static function css_asymmetric(string $p): string
    {
        return <<<CSS
.{$p}-footer--asymmetric .{$p}-footer-main {
  padding: 48px 0;
}
.{$p}-footer--asymmetric .{$p}-footer-inner {
  display: grid; grid-template-columns: 3fr 2fr;
  gap: 48px; align-items: flex-start;
}
.{$p}-footer--asymmetric .{$p}-footer-secondary {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 24px;
}
.{$p}-footer--asymmetric .{$p}-footer-brand {
  margin-bottom: 16px;
}

CSS;
    }

    // --- Split (modern-split) ---
    private static function css_split(string $p): string
    {
        return <<<CSS
.{$p}-footer--split .{$p}-footer-inner {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 48px; padding: 48px clamp(16px, 3vw, 40px) 0;
  align-items: flex-start;
}
.{$p}-footer--split .{$p}-footer-brand {
  margin-bottom: 16px;
}
.{$p}-footer--split .{$p}-footer-nav-group {
  display: flex; flex-direction: column; gap: 20px;
}

CSS;
    }

    // --- Magazine (modern-magazine) ---
    private static function css_magazine(string $p): string
    {
        return <<<CSS
.{$p}-footer--magazine .{$p}-footer-top {
  padding: 32px 0 16px 0; border-bottom: 1px solid rgba(128,128,128,0.2);
}
.{$p}-footer--magazine .{$p}-footer-brand-row {
  display: flex; justify-content: space-between; align-items: center;
}
.{$p}-footer--magazine .{$p}-footer-main {
  padding: 32px 0;
}
.{$p}-footer--magazine .{$p}-footer-main .{$p}-footer-inner {
  display: grid; grid-template-columns: 2fr 1fr 1fr;
  gap: 32px; align-items: flex-start;
}

CSS;
    }

    // --- Bands (modern-bands) ---
    private static function css_bands(string $p): string
    {
        return <<<CSS
.{$p}-footer--bands .{$p}-footer-dark {
  padding: 48px 0 32px 0;
}
.{$p}-footer--bands .{$p}-footer-dark .{$p}-footer-inner {
  text-align: center;
}
.{$p}-footer--bands .{$p}-footer-light {
  padding: 32px 0;
}
.{$p}-footer--bands .{$p}-footer-light .{$p}-footer-inner {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 32px; align-items: flex-start;
}
.{$p}-footer--bands .{$p}-footer-brand {
  justify-content: center; margin-bottom: 16px;
}
.{$p}-footer--bands .{$p}-footer-social {
  justify-content: center;
}

CSS;
    }

    // --- Wave (creative-wave) ---
    private static function css_wave(string $p): string
    {
        return <<<CSS
.{$p}-footer--wave {
  position: relative;
}
.{$p}-footer-wave-divider {
  position: absolute; top: 0; left: 0; width: 100%;
  line-height: 0; direction: ltr;
}
.{$p}-wave-svg {
  position: relative; display: block; width: calc(100% + 1.3px);
  height: 60px;
}
.{$p}-wave-path {
  fill: var(--background, #0a0f1a);
}
.{$p}-footer--wave .{$p}-footer-inner {
  padding-top: 80px; padding-bottom: 32px;
}
.{$p}-footer--wave .{$p}-footer-content {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 40px; align-items: flex-start; margin-bottom: 32px;
}
.{$p}-footer--wave .{$p}-footer-brand-area {
  min-width: 280px;
}
.{$p}-footer--wave .{$p}-footer-brand {
  margin-bottom: 16px;
}

CSS;
    }

    // --- Diagonal (creative-diagonal) ---
    private static function css_diagonal(string $p): string
    {
        return <<<CSS
.{$p}-footer--diagonal {
  position: relative; overflow: hidden;
}
.{$p}-footer-diagonal-bg {
  position: absolute; top: 0; right: 0; bottom: 0;
  width: 60%; background: linear-gradient(135deg, transparent 0%, rgba(128,128,128,0.05) 100%);
  transform: skew(-15deg); transform-origin: top right;
}
.{$p}-footer--diagonal .{$p}-footer-inner {
  position: relative; z-index: 2;
  display: grid; grid-template-columns: 1.5fr 1fr;
  gap: 48px; padding: 48px clamp(16px, 3vw, 40px);
  align-items: flex-start;
}
.{$p}-footer--diagonal .{$p}-footer-secondary-section {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 24px;
}
.{$p}-footer--diagonal .{$p}-footer-brand {
  margin-bottom: 16px;
}

CSS;
    }

    // --- Big Brand (creative-bigbrand) ---
    private static function css_big_brand(string $p): string
    {
        return <<<CSS
.{$p}-footer--bigbrand .{$p}-footer-hero {
  padding: 64px 0 32px 0; text-align: center;
}
.{$p}-footer--bigbrand .{$p}-footer-brand-large {
  margin-bottom: 20px;
}
.{$p}-footer--bigbrand .{$p}-footer-brand-large .{$p}-footer-logo {
  height: 48px;
}
.{$p}-footer--bigbrand .{$p}-footer-brand-large .{$p}-footer-brand-text {
  font-size: 2rem;
}
.{$p}-footer--bigbrand .{$p}-footer-social {
  justify-content: center;
}
.{$p}-footer--bigbrand .{$p}-footer-details {
  padding: 32px 0;
}
.{$p}-footer--bigbrand .{$p}-footer-info-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 32px; align-items: flex-start;
}

CSS;
    }

    // --- Mega (detailed-mega) ---
    private static function css_mega(string $p): string
    {
        return <<<CSS
.{$p}-footer--mega .{$p}-footer-main {
  padding: 64px 0;
}
.{$p}-footer--mega .{$p}-footer-mega-grid {
  display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr;
  gap: 40px; align-items: flex-start;
}
.{$p}-footer--mega .{$p}-footer-brand {
  margin-bottom: 16px;
}
.{$p}-footer--mega .{$p}-footer-map-link {
  margin-top: 16px;
}
.{$p}-map-link {
  display: inline-flex; align-items: center; gap: 6px;
  text-decoration: none; font-weight: 500;
  transition: color 0.2s;
}

CSS;
    }

    // --- Corporate (detailed-corporate) ---
    private static function css_corporate(string $p): string
    {
        return <<<CSS
.{$p}-footer--corporate .{$p}-footer-top {
  padding: 48px 0 32px 0; text-align: center;
  border-bottom: 1px solid rgba(128,128,128,0.2);
}
.{$p}-footer--corporate .{$p}-footer-main {
  padding: 48px 0;
}
.{$p}-footer--corporate .{$p}-footer-corporate-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 40px; align-items: flex-start;
}
.{$p}-footer--corporate .{$p}-footer-hours {
  margin: 0; line-height: 1.6;
}

CSS;
    }

    // --- Info Rich (detailed-inforich) ---
    private static function css_info_rich(string $p): string
    {
        return <<<CSS
.{$p}-footer--inforich .{$p}-footer-main {
  padding: 48px 0;
}
.{$p}-footer--inforich .{$p}-footer-inner {
  display: grid; grid-template-columns: 2fr 3fr;
  gap: 48px; align-items: flex-start;
}
.{$p}-footer--inforich .{$p}-footer-brand {
  margin-bottom: 16px;
}
.{$p}-footer--inforich .{$p}-footer-info-sections {
  display: grid; grid-template-columns: 1fr 1fr 1fr;
  gap: 32px;
}
.{$p}-footer--inforich .{$p}-footer-map {
  margin-top: 16px;
}

CSS;
    }

    // ═══════════════════════════════════════
    // MOBILE CSS
    // ═══════════════════════════════════════

    private static function css_mobile(string $p, string $cssType): string
    {
        return <<<CSS

/* ═══ Footer Mobile ═══ */
@media (max-width: 768px) {
  .{$p}-footer-inner {
    padding: 0 16px;
  }
  
  /* Single row becomes stacked */
  .{$p}-footer--minimal .{$p}-footer-inner {
    flex-direction: column; align-items: center; text-align: center; gap: 16px;
    padding-top: 32px;
  }
  
  /* Multi-column grids become single column */
  .{$p}-footer--classic .{$p}-footer-inner,
  .{$p}-footer--classic-4col .{$p}-footer-inner,
  .{$p}-footer--magazine .{$p}-footer-main .{$p}-footer-inner,
  .{$p}-footer--mega .{$p}-footer-mega-grid,
  .{$p}-footer--corporate .{$p}-footer-corporate-grid,
  .{$p}-footer--inforich .{$p}-footer-info-sections {
    grid-template-columns: 1fr;
    gap: 24px;
  }
  
  /* Split layouts become stacked */
  .{$p}-footer--split .{$p}-footer-inner,
  .{$p}-footer--asymmetric .{$p}-footer-inner,
  .{$p}-footer--bands .{$p}-footer-light .{$p}-footer-inner,
  .{$p}-footer--diagonal .{$p}-footer-inner,
  .{$p}-footer--bigbrand .{$p}-footer-info-grid,
  .{$p}-footer--inforich .{$p}-footer-inner {
    grid-template-columns: 1fr;
    gap: 24px;
  }
  
  /* Secondary grids also stack */
  .{$p}-footer--asymmetric .{$p}-footer-secondary,
  .{$p}-footer--diagonal .{$p}-footer-secondary-section {
    grid-template-columns: 1fr;
  }
  
  /* Wave footer adjustments */
  .{$p}-footer--wave .{$p}-footer-content {
    grid-template-columns: 1fr;
    gap: 24px;
  }
  
  /* Newsletter form stacks on very small screens */
  @media (max-width: 480px) {
    .{$p}-newsletter-form {
      flex-direction: column;
    }
  }
  
  /* Footer links wrap better on mobile */
  .{$p}-footer-links {
    justify-content: center; gap: 4px 12px;
  }
  
  /* Social icons center on mobile */
  .{$p}-footer-social {
    justify-content: center;
  }
  
  /* Reduce padding on mobile */
  .{$p}-footer--classic .{$p}-footer-main,
  .{$p}-footer--classic-4col .{$p}-footer-main,
  .{$p}-footer--magazine .{$p}-footer-main,
  .{$p}-footer--mega .{$p}-footer-main,
  .{$p}-footer--corporate .{$p}-footer-main,
  .{$p}-footer--inforich .{$p}-footer-main {
    padding: 32px 0;
  }
  
  .{$p}-footer--split .{$p}-footer-inner,
  .{$p}-footer--diagonal .{$p}-footer-inner {
    padding: 32px 16px;
  }
  
  .{$p}-footer--wave .{$p}-footer-inner {
    padding-top: 80px;
    padding-bottom: 24px;
  }
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
}