<?php
/**
 * CTA Section Pattern Registry
 * 
 * Pre-built CTA HTML layouts with structural CSS.
 * AI generates only decorative CSS (colors, fonts, effects) — never the structure.
 * 
 * 12 patterns across 4 groups.
 * @since 2026-02-19
 */

class CTAPatternRegistry
{
    // ═══════════════════════════════════════
    // PATTERN DEFINITIONS
    // ═══════════════════════════════════════

    private static array $patterns = [
        // --- Banner (full-width, bold) ---
        ['id'=>'banner-centered',    'group'=>'banner',   'css_type'=>'banner-centered',
         'best_for'=>['restaurant','bakery','cafe','bar','hotel','resort','spa','wedding',
                      'florist','winery','brewery','fine-dining','catering']],
        ['id'=>'banner-gradient',    'group'=>'banner',   'css_type'=>'banner-gradient',
         'best_for'=>['saas','fintech','ai','blockchain','platform','digital','app',
                      'ecommerce','marketplace']],
        ['id'=>'banner-image',       'group'=>'banner',   'css_type'=>'banner-image',
         'best_for'=>['travel','tourism','adventure','outdoor','fitness','sports',
                      'real-estate','construction','landscaping']],

        // --- Split (two-column layouts) ---
        ['id'=>'split-text-button',  'group'=>'split',    'css_type'=>'split-text-button',
         'best_for'=>['consulting','agency','coaching','financial','accounting','legal',
                      'insurance','bank']],
        ['id'=>'split-image-text',   'group'=>'split',    'css_type'=>'split-image-text',
         'best_for'=>['healthcare','clinic','hospital','dental','pharmacy','veterinary',
                      'nonprofit','charity']],
        ['id'=>'split-card',         'group'=>'split',    'css_type'=>'split-card',
         'best_for'=>['education','university','school','library','startup','tech',
                      'recruitment','hr']],

        // --- Creative (unique/bold designs) ---
        ['id'=>'creative-diagonal',  'group'=>'creative', 'css_type'=>'creative-diagonal',
         'best_for'=>['creative-agency','design','branding','marketing','social-media',
                      'seo','web-design']],
        ['id'=>'creative-wave',      'group'=>'creative', 'css_type'=>'creative-wave',
         'best_for'=>['music','entertainment','film','gaming','nightclub','festival',
                      'concert','podcast']],
        ['id'=>'creative-glassmorphism','group'=>'creative','css_type'=>'creative-glassmorphism',
         'best_for'=>['fashion','luxury','gallery','art','photography','influencer',
                      'content-creator']],

        // --- Minimal (clean, understated) ---
        ['id'=>'minimal-inline',     'group'=>'minimal',  'css_type'=>'minimal-inline',
         'best_for'=>['architecture','interior-design','museum','magazine','blog','news',
                      'media']],
        ['id'=>'minimal-bordered',   'group'=>'minimal',  'css_type'=>'minimal-bordered',
         'best_for'=>['manufacturing','logistics','engineering','paving','roofing',
                      'plumbing','electrical','hvac']],
        ['id'=>'minimal-dark',       'group'=>'minimal',  'css_type'=>'minimal-dark',
         'best_for'=>['youtube','event-planning','country-club','movie','theatre',
                      'comedy','streaming']],
    ];

    // ═══════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════

    /**
     * Pick the best CTA pattern for an industry.
     */
    public static function pickPattern(string $industry): string
    {
        $industry = strtolower(trim($industry));
        foreach (self::$patterns as $p) {
            if (in_array($industry, $p['best_for'], true)) {
                return $p['id'];
            }
        }
        // Fallback: random from banner group (most versatile)
        $bannerPatterns = array_filter(self::$patterns, fn($p) => $p['group'] === 'banner');
        $bannerIds = array_column(array_values($bannerPatterns), 'id');
        return $bannerIds[array_rand($bannerIds)];
    }

    /**
     * Render a CTA pattern.
     * Returns ['html'=>..., 'structural_css'=>..., 'pattern_id'=>..., 'classes'=>[...], 'fields'=>[...]]
     */
    public static function render(string $patternId, string $prefix, array $brief): array
    {
        $def = null;
        foreach (self::$patterns as $p) {
            if ($p['id'] === $patternId) { $def = $p; break; }
        }
        if (!$def) {
            $def = self::$patterns[0]; // fallback to banner-centered
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
        // Common fields all CTAs have
        $common = [
            'title'    => ['type' => 'text',     'label' => 'CTA Title'],
            'subtitle' => ['type' => 'textarea', 'label' => 'CTA Subtitle'],
            'btn_text' => ['type' => 'text',     'label' => 'Button Text'],
            'btn_link' => ['type' => 'text',     'label' => 'Button Link'],
            'badge'    => ['type' => 'text',     'label' => 'Badge / Label'],
        ];

        // Pattern-specific extras
        $extras = match($patternId) {
            'banner-image' => [
                'bg_image' => ['type' => 'image', 'label' => 'Background Image'],
            ],
            'split-image-text' => [
                'bg_image' => ['type' => 'image', 'label' => 'Side Image'],
            ],
            'creative-glassmorphism' => [
                'bg_image' => ['type' => 'image', 'label' => 'Background Image'],
            ],
            default => [],
        };

        return array_merge($common, $extras);
    }

    /**
     * Get decorative CSS guidance for a pattern (for AI CSS generation).
     * Returns hints about visual/decorative properties — NOT structural layout.
     */
    public static function getDecorativeGuide(string $patternId): string
    {
        return match($patternId) {
            'banner-centered' => <<<'GUIDE'
Full-width primary background color, solid or very subtle single-color gradient.
Text is white/high-contrast against primary bg, title bold and impactful.
Button inverted: white bg with primary text color, strong hover shadow.
Badge uses rgba white background (0.15) with white text and semi-transparent border.
Overall feel: bold, confident, high-contrast single-color impact.
GUIDE,
            'banner-gradient' => <<<'GUIDE'
Multi-stop gradient background: primary to secondary at 135deg angle, vibrant saturation.
Text pure white, title extra bold weight for contrast against colorful gradient.
Button white with primary text, hover adds strong colored box-shadow.
Badge translucent white (rgba 0.15), text white, border semi-transparent.
Optional subtle noise/grain texture overlay at very low opacity for depth.
GUIDE,
            'banner-image' => <<<'GUIDE'
Background image with dark overlay gradient (rgba black 0.4-0.65) for text readability.
Title uses text-shadow (0 2px 8px rgba black 0.3) for extra legibility over photo.
Button uses primary color bg, strong contrast, hover lifts with shadow.
Badge translucent white on dark overlay, subtle border.
Overlay gradient angled to be darker where text sits, lighter at edges.
GUIDE,
            'split-text-button' => <<<'GUIDE'
Clean minimal background — white or very light surface color.
Optional thin vertical divider line between text and button sides.
Button large and prominent: primary bg, generous padding, rounded corners.
Button hover: lift transform, colored shadow spread, slight scale.
Title uses heading color, subtitle muted, restrained professional palette.
GUIDE,
            'split-image-text' => <<<'GUIDE'
Image side has subtle border-radius (on visible corners) and soft shadow frame.
Text side uses clean surface background, comfortable padding.
Button primary with standard hover lift and shadow.
Image may have slight overlay gradient at edge blending into text-side bg.
Overall feel: editorial, clean split between visual and content.
GUIDE,
            'split-card' => <<<'GUIDE'
Outer section uses primary bg color as contrast backdrop.
Floating card has white/surface bg, heavy box-shadow (0 20px 60px rgba 0.15), large border-radius.
Button inside card uses primary bg, stands out against white card surface.
Card hover: shadow deepens slightly, subtle lift.
Badge inside card uses standard primary-tinted style.
GUIDE,
            'creative-diagonal' => <<<'GUIDE'
Diagonal clip-path on background creates dynamic angled split (polygon).
Primary color fills the diagonal bg, text white/high-contrast.
Button inverted white with primary text, hover adds shadow.
The diagonal angle should be subtle (5-8%) — dramatic but not extreme.
Badge translucent white, text white, on the primary diagonal surface.
GUIDE,
            'creative-wave' => <<<'GUIDE'
SVG wave shapes at top and bottom edges, fill matches surrounding section bg color.
Wave section bg uses primary to secondary gradient, vibrant and flowing.
Text white on gradient, button inverted white with primary text.
Wave paths should feel organic — smooth curves, not sharp.
Badge translucent white, overall feel: playful, energetic, flowing.
GUIDE,
            'creative-glassmorphism' => <<<'GUIDE'
Card uses backdrop-filter blur(20px) with rgba white bg (0.1) for frosted glass effect.
Card border: 1px solid rgba white (0.2) — translucent glass edge.
Background behind glass is colorful (image or gradient) creating visible blur effect.
Text white on glass, button white bg with primary text for contrast.
Card box-shadow uses spread shadow for floating glass feel.
GUIDE,
            'minimal-inline' => <<<'GUIDE'
Thin top and bottom border lines (1px) in muted color create contained strip.
No background color change — inherits parent section bg.
Title and button on same line, title uses heading weight, restrained size.
Button uses primary bg but compact size, arrow icon adds directional cue.
Overall feel: understated, editorial, single-line efficiency.
GUIDE,
            'minimal-bordered' => <<<'GUIDE'
Bordered box with 2px solid border in muted/subtle color, generous border-radius.
Hover: border-color transitions to primary color, subtle and elegant.
Interior uses centered text, clean typography, restrained color palette.
Button primary but not oversized, balanced within the bordered container.
No shadows, no gradients — purely line-based minimal aesthetic.
GUIDE,
            'minimal-dark' => <<<'GUIDE'
Dark background section (#0f172a or similar deep dark) for dramatic contrast.
Title pure white, subtitle uses muted white (rgba 255,255,255,0.65).
Button primary colored, glows against dark bg, hover shadow in primary color.
Badge uses primary color with dark-adjusted transparency.
Overall feel: cinematic, high-contrast, dramatic dark section.
GUIDE,
            default => <<<'GUIDE'
Primary colored background or gradient for impact.
High contrast text, bold title, clear button styling.
Button hover adds shadow and slight lift transform.
Clean typography with proper visual hierarchy.
GUIDE,
        };
    }

    // ═══════════════════════════════════════
    // HTML TEMPLATES
    // ═══════════════════════════════════════

    /**
     * Replace generic placeholder defaults in CTA template with actual brief content.
     */
    private static function injectBriefContent(string $html, array $brief): string
    {
        $name = $brief['name'] ?? '';
        $industry = $brief['industry'] ?? '';

        // CTA headline from brief
        $title = $brief['cta_headline'] ?? '';
        if (!$title && $name) {
            $title = "Ready to Work with {$name}?";
        }

        // CTA subtitle from brief
        $subtitle = $brief['cta_subheadline'] ?? '';

        // CTA button text from brief
        $btnText = $brief['cta_text'] ?? '';

        // Badge from industry
        $badge = '';
        if ($industry) {
            $badge = ucwords(str_replace('-', ' ', $industry));
        }

        // Replace defaults in theme_get() calls
        $replacements = [];
        if ($title)   $replacements["theme_get('cta.title', 'Ready to Get Started?')"]                                                   = "theme_get('cta.title', '" . addslashes($title) . "')";
        if ($subtitle) $replacements["theme_get('cta.subtitle', 'Join thousands of satisfied customers. Take the first step today.')"]     = "theme_get('cta.subtitle', '" . addslashes($subtitle) . "')";
        if ($btnText)  $replacements["theme_get('cta.btn_text', 'Get Started')"]                                                          = "theme_get('cta.btn_text', '" . addslashes($btnText) . "')";
        if ($badge)    $replacements["theme_get('cta.badge', '')"]                                                                        = "theme_get('cta.badge', '" . addslashes($badge) . "')";

        foreach ($replacements as $search => $replace) {
            $html = str_replace($search, $replace, $html);
        }

        return $html;
    }

    private static function buildHTML(string $patternId, string $p): string
    {
        $templates = self::getTemplates($p);
        return $templates[$patternId] ?? $templates['banner-centered'];
    }

    private static function getTemplates(string $p): array
    {
        return [

// ── Banner Centered: Full-width colored bg, centered text + CTA ──
'banner-centered' => <<<HTML
<?php
\$ctaBadge = theme_get('cta.badge', '');
\$ctaTitle = theme_get('cta.title', 'Ready to Get Started?');
\$ctaSubtitle = theme_get('cta.subtitle', 'Join thousands of satisfied customers. Take the first step today.');
\$ctaBtnText = theme_get('cta.btn_text', 'Get Started');
\$ctaBtnLink = theme_get('cta.btn_link', '/contact');
?>
<section class="{$p}-cta {$p}-cta--banner-centered" id="cta">
  <div class="container">
    <div class="{$p}-cta-content" data-animate="fade-up">
      <?php if (\$ctaBadge): ?><span class="{$p}-cta-badge" data-ts="cta.badge"><?= esc(\$ctaBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-cta-title" data-ts="cta.title"><?= esc(\$ctaTitle) ?></h2>
      <p class="{$p}-cta-subtitle" data-ts="cta.subtitle"><?= esc(\$ctaSubtitle) ?></p>
      <div class="{$p}-cta-actions">
        <a href="<?= esc(\$ctaBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="cta.btn_text" data-ts-href="cta.btn_link"><?= esc(\$ctaBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Banner Gradient: Gradient bg (primary→secondary), centered ──
'banner-gradient' => <<<HTML
<?php
\$ctaBadge = theme_get('cta.badge', '');
\$ctaTitle = theme_get('cta.title', 'Ready to Get Started?');
\$ctaSubtitle = theme_get('cta.subtitle', 'Join thousands of satisfied customers. Take the first step today.');
\$ctaBtnText = theme_get('cta.btn_text', 'Get Started');
\$ctaBtnLink = theme_get('cta.btn_link', '/contact');
?>
<section class="{$p}-cta {$p}-cta--banner-gradient" id="cta">
  <div class="{$p}-cta-gradient-bg"></div>
  <div class="container">
    <div class="{$p}-cta-content" data-animate="fade-up">
      <?php if (\$ctaBadge): ?><span class="{$p}-cta-badge" data-ts="cta.badge"><?= esc(\$ctaBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-cta-title" data-ts="cta.title"><?= esc(\$ctaTitle) ?></h2>
      <p class="{$p}-cta-subtitle" data-ts="cta.subtitle"><?= esc(\$ctaSubtitle) ?></p>
      <div class="{$p}-cta-actions">
        <a href="<?= esc(\$ctaBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="cta.btn_text" data-ts-href="cta.btn_link"><?= esc(\$ctaBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Banner Image: Background image with overlay, centered content ──
'banner-image' => <<<HTML
<?php
\$ctaBgImage = theme_get('cta.bg_image', '');
\$ctaBadge = theme_get('cta.badge', '');
\$ctaTitle = theme_get('cta.title', 'Ready to Get Started?');
\$ctaSubtitle = theme_get('cta.subtitle', 'Join thousands of satisfied customers. Take the first step today.');
\$ctaBtnText = theme_get('cta.btn_text', 'Get Started');
\$ctaBtnLink = theme_get('cta.btn_link', '/contact');
?>
<section class="{$p}-cta {$p}-cta--banner-image" id="cta">
  <div class="{$p}-cta-bg" style="background-image: url('<?= esc(\$ctaBgImage) ?>');" data-ts-bg="cta.bg_image"></div>
  <div class="{$p}-cta-overlay"></div>
  <div class="container">
    <div class="{$p}-cta-content" data-animate="fade-up">
      <?php if (\$ctaBadge): ?><span class="{$p}-cta-badge" data-ts="cta.badge"><?= esc(\$ctaBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-cta-title" data-ts="cta.title"><?= esc(\$ctaTitle) ?></h2>
      <p class="{$p}-cta-subtitle" data-ts="cta.subtitle"><?= esc(\$ctaSubtitle) ?></p>
      <div class="{$p}-cta-actions">
        <a href="<?= esc(\$ctaBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="cta.btn_text" data-ts-href="cta.btn_link"><?= esc(\$ctaBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Split Text-Button: Text left, big CTA button right ──
'split-text-button' => <<<HTML
<?php
\$ctaBadge = theme_get('cta.badge', '');
\$ctaTitle = theme_get('cta.title', 'Ready to Get Started?');
\$ctaSubtitle = theme_get('cta.subtitle', 'Join thousands of satisfied customers. Take the first step today.');
\$ctaBtnText = theme_get('cta.btn_text', 'Get Started');
\$ctaBtnLink = theme_get('cta.btn_link', '/contact');
?>
<section class="{$p}-cta {$p}-cta--split-text-button" id="cta">
  <div class="container">
    <div class="{$p}-cta-grid" data-animate="fade-up">
      <div class="{$p}-cta-content">
        <?php if (\$ctaBadge): ?><span class="{$p}-cta-badge" data-ts="cta.badge"><?= esc(\$ctaBadge) ?></span><?php endif; ?>
        <h2 class="{$p}-cta-title" data-ts="cta.title"><?= esc(\$ctaTitle) ?></h2>
        <p class="{$p}-cta-subtitle" data-ts="cta.subtitle"><?= esc(\$ctaSubtitle) ?></p>
      </div>
      <div class="{$p}-cta-action-col">
        <a href="<?= esc(\$ctaBtnLink) ?>" class="{$p}-btn {$p}-btn-primary {$p}-btn-lg" data-ts="cta.btn_text" data-ts-href="cta.btn_link"><?= esc(\$ctaBtnText) ?> <i class="fas fa-arrow-right"></i></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Split Image-Text: Image left half, text + button right half ──
'split-image-text' => <<<HTML
<?php
\$ctaBgImage = theme_get('cta.bg_image', '');
\$ctaBadge = theme_get('cta.badge', '');
\$ctaTitle = theme_get('cta.title', 'Ready to Get Started?');
\$ctaSubtitle = theme_get('cta.subtitle', 'Join thousands of satisfied customers. Take the first step today.');
\$ctaBtnText = theme_get('cta.btn_text', 'Get Started');
\$ctaBtnLink = theme_get('cta.btn_link', '/contact');
?>
<section class="{$p}-cta {$p}-cta--split-image-text" id="cta">
  <div class="{$p}-cta-split-grid">
    <div class="{$p}-cta-image-col">
      <img src="<?= esc(\$ctaBgImage) ?>" alt="" class="{$p}-cta-image" data-ts-bg="cta.bg_image" loading="lazy">
    </div>
    <div class="{$p}-cta-text-col">
      <div class="{$p}-cta-content" data-animate="fade-up">
        <?php if (\$ctaBadge): ?><span class="{$p}-cta-badge" data-ts="cta.badge"><?= esc(\$ctaBadge) ?></span><?php endif; ?>
        <h2 class="{$p}-cta-title" data-ts="cta.title"><?= esc(\$ctaTitle) ?></h2>
        <p class="{$p}-cta-subtitle" data-ts="cta.subtitle"><?= esc(\$ctaSubtitle) ?></p>
        <div class="{$p}-cta-actions">
          <a href="<?= esc(\$ctaBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="cta.btn_text" data-ts-href="cta.btn_link"><?= esc(\$ctaBtnText) ?></a>
        </div>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Split Card: Raised card floating over colored bg ──
'split-card' => <<<HTML
<?php
\$ctaBadge = theme_get('cta.badge', '');
\$ctaTitle = theme_get('cta.title', 'Ready to Get Started?');
\$ctaSubtitle = theme_get('cta.subtitle', 'Join thousands of satisfied customers. Take the first step today.');
\$ctaBtnText = theme_get('cta.btn_text', 'Get Started');
\$ctaBtnLink = theme_get('cta.btn_link', '/contact');
?>
<section class="{$p}-cta {$p}-cta--split-card" id="cta">
  <div class="container">
    <div class="{$p}-cta-card" data-animate="fade-up">
      <div class="{$p}-cta-card-inner">
        <div class="{$p}-cta-content">
          <?php if (\$ctaBadge): ?><span class="{$p}-cta-badge" data-ts="cta.badge"><?= esc(\$ctaBadge) ?></span><?php endif; ?>
          <h2 class="{$p}-cta-title" data-ts="cta.title"><?= esc(\$ctaTitle) ?></h2>
          <p class="{$p}-cta-subtitle" data-ts="cta.subtitle"><?= esc(\$ctaSubtitle) ?></p>
        </div>
        <div class="{$p}-cta-action-col">
          <a href="<?= esc(\$ctaBtnLink) ?>" class="{$p}-btn {$p}-btn-primary {$p}-btn-lg" data-ts="cta.btn_text" data-ts-href="cta.btn_link"><?= esc(\$ctaBtnText) ?></a>
        </div>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Creative Diagonal: Diagonal split background, centered text ──
'creative-diagonal' => <<<HTML
<?php
\$ctaBadge = theme_get('cta.badge', '');
\$ctaTitle = theme_get('cta.title', 'Ready to Get Started?');
\$ctaSubtitle = theme_get('cta.subtitle', 'Join thousands of satisfied customers. Take the first step today.');
\$ctaBtnText = theme_get('cta.btn_text', 'Get Started');
\$ctaBtnLink = theme_get('cta.btn_link', '/contact');
?>
<section class="{$p}-cta {$p}-cta--creative-diagonal" id="cta">
  <div class="{$p}-cta-diagonal-bg"></div>
  <div class="container">
    <div class="{$p}-cta-content" data-animate="fade-up">
      <?php if (\$ctaBadge): ?><span class="{$p}-cta-badge" data-ts="cta.badge"><?= esc(\$ctaBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-cta-title" data-ts="cta.title"><?= esc(\$ctaTitle) ?></h2>
      <p class="{$p}-cta-subtitle" data-ts="cta.subtitle"><?= esc(\$ctaSubtitle) ?></p>
      <div class="{$p}-cta-actions">
        <a href="<?= esc(\$ctaBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="cta.btn_text" data-ts-href="cta.btn_link"><?= esc(\$ctaBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Creative Wave: Wave SVG borders, gradient bg, centered ──
'creative-wave' => <<<HTML
<?php
\$ctaBadge = theme_get('cta.badge', '');
\$ctaTitle = theme_get('cta.title', 'Ready to Get Started?');
\$ctaSubtitle = theme_get('cta.subtitle', 'Join thousands of satisfied customers. Take the first step today.');
\$ctaBtnText = theme_get('cta.btn_text', 'Get Started');
\$ctaBtnLink = theme_get('cta.btn_link', '/contact');
?>
<section class="{$p}-cta {$p}-cta--creative-wave" id="cta">
  <div class="{$p}-cta-wave-top">
    <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
      <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"></path>
    </svg>
  </div>
  <div class="{$p}-cta-wave-bg"></div>
  <div class="container">
    <div class="{$p}-cta-content" data-animate="fade-up">
      <?php if (\$ctaBadge): ?><span class="{$p}-cta-badge" data-ts="cta.badge"><?= esc(\$ctaBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-cta-title" data-ts="cta.title"><?= esc(\$ctaTitle) ?></h2>
      <p class="{$p}-cta-subtitle" data-ts="cta.subtitle"><?= esc(\$ctaSubtitle) ?></p>
      <div class="{$p}-cta-actions">
        <a href="<?= esc(\$ctaBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="cta.btn_text" data-ts-href="cta.btn_link"><?= esc(\$ctaBtnText) ?></a>
      </div>
    </div>
  </div>
  <div class="{$p}-cta-wave-bottom">
    <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
      <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"></path>
    </svg>
  </div>
</section>
HTML,

// ── Creative Glassmorphism: Glass card floating over colorful bg ──
'creative-glassmorphism' => <<<HTML
<?php
\$ctaBgImage = theme_get('cta.bg_image', '');
\$ctaBadge = theme_get('cta.badge', '');
\$ctaTitle = theme_get('cta.title', 'Ready to Get Started?');
\$ctaSubtitle = theme_get('cta.subtitle', 'Join thousands of satisfied customers. Take the first step today.');
\$ctaBtnText = theme_get('cta.btn_text', 'Get Started');
\$ctaBtnLink = theme_get('cta.btn_link', '/contact');
?>
<section class="{$p}-cta {$p}-cta--creative-glass" id="cta">
  <div class="{$p}-cta-glass-bg" style="background-image: url('<?= esc(\$ctaBgImage) ?>');" data-ts-bg="cta.bg_image"></div>
  <div class="container">
    <div class="{$p}-cta-glass-card" data-animate="fade-up">
      <div class="{$p}-cta-content">
        <?php if (\$ctaBadge): ?><span class="{$p}-cta-badge" data-ts="cta.badge"><?= esc(\$ctaBadge) ?></span><?php endif; ?>
        <h2 class="{$p}-cta-title" data-ts="cta.title"><?= esc(\$ctaTitle) ?></h2>
        <p class="{$p}-cta-subtitle" data-ts="cta.subtitle"><?= esc(\$ctaSubtitle) ?></p>
        <div class="{$p}-cta-actions">
          <a href="<?= esc(\$ctaBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="cta.btn_text" data-ts-href="cta.btn_link"><?= esc(\$ctaBtnText) ?></a>
        </div>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Minimal Inline: Single line — text + button on same row ──
'minimal-inline' => <<<HTML
<?php
\$ctaTitle = theme_get('cta.title', 'Ready to Get Started?');
\$ctaBtnText = theme_get('cta.btn_text', 'Get Started');
\$ctaBtnLink = theme_get('cta.btn_link', '/contact');
?>
<section class="{$p}-cta {$p}-cta--minimal-inline" id="cta">
  <div class="container">
    <div class="{$p}-cta-inline" data-animate="fade-up">
      <h2 class="{$p}-cta-title" data-ts="cta.title"><?= esc(\$ctaTitle) ?></h2>
      <a href="<?= esc(\$ctaBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="cta.btn_text" data-ts-href="cta.btn_link"><?= esc(\$ctaBtnText) ?> <i class="fas fa-arrow-right"></i></a>
    </div>
  </div>
</section>
HTML,

// ── Minimal Bordered: Box with border, centered content ──
'minimal-bordered' => <<<HTML
<?php
\$ctaBadge = theme_get('cta.badge', '');
\$ctaTitle = theme_get('cta.title', 'Ready to Get Started?');
\$ctaSubtitle = theme_get('cta.subtitle', 'Join thousands of satisfied customers. Take the first step today.');
\$ctaBtnText = theme_get('cta.btn_text', 'Get Started');
\$ctaBtnLink = theme_get('cta.btn_link', '/contact');
?>
<section class="{$p}-cta {$p}-cta--minimal-bordered" id="cta">
  <div class="container">
    <div class="{$p}-cta-bordered-box" data-animate="fade-up">
      <div class="{$p}-cta-content">
        <?php if (\$ctaBadge): ?><span class="{$p}-cta-badge" data-ts="cta.badge"><?= esc(\$ctaBadge) ?></span><?php endif; ?>
        <h2 class="{$p}-cta-title" data-ts="cta.title"><?= esc(\$ctaTitle) ?></h2>
        <p class="{$p}-cta-subtitle" data-ts="cta.subtitle"><?= esc(\$ctaSubtitle) ?></p>
        <div class="{$p}-cta-actions">
          <a href="<?= esc(\$ctaBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="cta.btn_text" data-ts-href="cta.btn_link"><?= esc(\$ctaBtnText) ?></a>
        </div>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Minimal Dark: Dark section with bright accent text + button ──
'minimal-dark' => <<<HTML
<?php
\$ctaBadge = theme_get('cta.badge', '');
\$ctaTitle = theme_get('cta.title', 'Ready to Get Started?');
\$ctaSubtitle = theme_get('cta.subtitle', 'Join thousands of satisfied customers. Take the first step today.');
\$ctaBtnText = theme_get('cta.btn_text', 'Get Started');
\$ctaBtnLink = theme_get('cta.btn_link', '/contact');
?>
<section class="{$p}-cta {$p}-cta--minimal-dark" id="cta">
  <div class="container">
    <div class="{$p}-cta-content" data-animate="fade-up">
      <?php if (\$ctaBadge): ?><span class="{$p}-cta-badge" data-ts="cta.badge"><?= esc(\$ctaBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-cta-title" data-ts="cta.title"><?= esc(\$ctaTitle) ?></h2>
      <p class="{$p}-cta-subtitle" data-ts="cta.subtitle"><?= esc(\$ctaSubtitle) ?></p>
      <div class="{$p}-cta-actions">
        <a href="<?= esc(\$ctaBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="cta.btn_text" data-ts-href="cta.btn_link"><?= esc(\$ctaBtnText) ?></a>
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
        $base = ["{$p}-cta", "{$p}-cta-content", "{$p}-cta-badge", "{$p}-cta-title",
                 "{$p}-cta-subtitle", "{$p}-cta-actions", "{$p}-btn", "{$p}-btn-primary"];

        $extra = match($patternId) {
            'banner-gradient' => ["{$p}-cta-gradient-bg"],
            'banner-image' => ["{$p}-cta-bg", "{$p}-cta-overlay"],
            'split-text-button' => ["{$p}-cta-grid", "{$p}-cta-action-col", "{$p}-btn-lg"],
            'split-image-text' => ["{$p}-cta-split-grid", "{$p}-cta-image-col", "{$p}-cta-text-col", "{$p}-cta-image"],
            'split-card' => ["{$p}-cta-card", "{$p}-cta-card-inner", "{$p}-cta-action-col", "{$p}-btn-lg"],
            'creative-diagonal' => ["{$p}-cta-diagonal-bg"],
            'creative-wave' => ["{$p}-cta-wave-top", "{$p}-cta-wave-bottom", "{$p}-cta-wave-bg"],
            'creative-glassmorphism' => ["{$p}-cta-glass-bg", "{$p}-cta-glass-card"],
            'minimal-inline' => ["{$p}-cta-inline"],
            'minimal-bordered' => ["{$p}-cta-bordered-box"],
            default => [],
        };

        return array_merge($base, $extra);
    }

    private static function buildStructuralCSS(string $cssType, string $p): string
    {
        $css = self::css_base($p);

        $typeCSS = match($cssType) {
            'banner-centered'        => self::css_banner_centered($p),
            'banner-gradient'        => self::css_banner_gradient($p),
            'banner-image'           => self::css_banner_image($p),
            'split-text-button'      => self::css_split_text_button($p),
            'split-image-text'       => self::css_split_image_text($p),
            'split-card'             => self::css_split_card($p),
            'creative-diagonal'      => self::css_creative_diagonal($p),
            'creative-wave'          => self::css_creative_wave($p),
            'creative-glassmorphism' => self::css_creative_glassmorphism($p),
            'minimal-inline'         => self::css_minimal_inline($p),
            'minimal-bordered'       => self::css_minimal_bordered($p),
            'minimal-dark'           => self::css_minimal_dark($p),
            default                  => self::css_banner_centered($p),
        };

        return $css . $typeCSS . self::css_responsive($p);
    }

    // --- Base (shared by all CTA patterns) ---
    private static function css_base(string $p): string
    {
        return <<<CSS

/* ═══ CTA Structural CSS (auto-generated — do not edit) ═══ */
.{$p}-cta {
  position: relative; overflow: hidden;
}
.{$p}-cta .container {
  position: relative; z-index: 2;
}
.{$p}-cta-content {
  max-width: 650px;
}
.{$p}-cta-badge {
  display: inline-block;
  font-size: 0.8125rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.12em;
  padding: 6px 16px; border-radius: 100px;
  margin-bottom: 16px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.15);
  color: var(--primary, #3b82f6);
  border: 1px solid rgba(var(--primary-rgb, 42,125,225), 0.25);
}
.{$p}-cta-title {
  font-family: var(--font-heading, inherit);
  font-size: clamp(1.75rem, 4vw, 3rem);
  font-weight: 700; line-height: 1.15;
  margin: 0 0 16px 0;
  color: var(--text, #1e293b);
}
.{$p}-cta-subtitle {
  font-size: clamp(1rem, 1.5vw, 1.125rem);
  line-height: 1.7; margin: 0 0 28px 0;
  color: var(--text-muted, #64748b);
  max-width: 50ch;
}
.{$p}-cta-actions {
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
.{$p}-btn-lg {
  padding: 18px 40px; font-size: 1.0625rem;
}

CSS;
    }

    // --- Banner Centered ---
    private static function css_banner_centered(string $p): string
    {
        return <<<CSS
.{$p}-cta--banner-centered {
  padding: clamp(60px, 10vh, 120px) 0;
  text-align: center;
  background: var(--primary, #3b82f6);
}
.{$p}-cta--banner-centered .{$p}-cta-content {
  max-width: 700px; margin: 0 auto;
}
.{$p}-cta--banner-centered .{$p}-cta-title {
  color: var(--primary-contrast, #fff);
}
.{$p}-cta--banner-centered .{$p}-cta-subtitle {
  color: rgba(255,255,255,0.85);
  margin-left: auto; margin-right: auto;
}
.{$p}-cta--banner-centered .{$p}-cta-actions {
  justify-content: center;
}
.{$p}-cta--banner-centered .{$p}-cta-badge {
  background: rgba(255,255,255,0.15); color: #fff;
  border-color: rgba(255,255,255,0.25);
}
.{$p}-cta--banner-centered .{$p}-btn-primary {
  background: #fff; color: var(--primary, #3b82f6);
  border-color: #fff;
}
.{$p}-cta--banner-centered .{$p}-btn-primary:hover {
  box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

CSS;
    }

    // --- Banner Gradient ---
    private static function css_banner_gradient(string $p): string
    {
        return <<<CSS
.{$p}-cta--banner-gradient {
  padding: clamp(60px, 10vh, 120px) 0;
  text-align: center;
}
.{$p}-cta-gradient-bg {
  position: absolute; inset: 0; z-index: 0;
  background: linear-gradient(135deg, var(--primary, #3b82f6) 0%, var(--secondary, #8b5cf6) 100%);
}
.{$p}-cta--banner-gradient .{$p}-cta-content {
  max-width: 700px; margin: 0 auto;
}
.{$p}-cta--banner-gradient .{$p}-cta-title {
  color: #fff;
}
.{$p}-cta--banner-gradient .{$p}-cta-subtitle {
  color: rgba(255,255,255,0.85);
  margin-left: auto; margin-right: auto;
}
.{$p}-cta--banner-gradient .{$p}-cta-actions {
  justify-content: center;
}
.{$p}-cta--banner-gradient .{$p}-cta-badge {
  background: rgba(255,255,255,0.15); color: #fff;
  border-color: rgba(255,255,255,0.25);
}
.{$p}-cta--banner-gradient .{$p}-btn-primary {
  background: #fff; color: var(--primary, #3b82f6);
  border-color: #fff;
}
.{$p}-cta--banner-gradient .{$p}-btn-primary:hover {
  box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

CSS;
    }

    // --- Banner Image ---
    private static function css_banner_image(string $p): string
    {
        return <<<CSS
.{$p}-cta--banner-image {
  padding: clamp(60px, 10vh, 120px) 0;
  text-align: center;
}
.{$p}-cta-bg {
  position: absolute; inset: 0;
  background-size: cover; background-position: center;
  z-index: 0;
}
.{$p}-cta-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(135deg, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0.4) 100%);
  z-index: 1;
}
.{$p}-cta--banner-image .{$p}-cta-content {
  max-width: 700px; margin: 0 auto;
}
.{$p}-cta--banner-image .{$p}-cta-title {
  color: #fff;
}
.{$p}-cta--banner-image .{$p}-cta-subtitle {
  color: rgba(255,255,255,0.85);
  margin-left: auto; margin-right: auto;
}
.{$p}-cta--banner-image .{$p}-cta-actions {
  justify-content: center;
}
.{$p}-cta--banner-image .{$p}-cta-badge {
  background: rgba(255,255,255,0.15); color: #fff;
  border-color: rgba(255,255,255,0.25);
}

CSS;
    }

    // --- Split Text-Button ---
    private static function css_split_text_button(string $p): string
    {
        return <<<CSS
.{$p}-cta--split-text-button {
  padding: clamp(60px, 10vh, 120px) 0;
}
.{$p}-cta-grid {
  display: grid; grid-template-columns: 1fr auto;
  gap: clamp(24px, 4vw, 60px); align-items: center;
}
.{$p}-cta-action-col {
  flex-shrink: 0;
}

CSS;
    }

    // --- Split Image-Text ---
    private static function css_split_image_text(string $p): string
    {
        return <<<CSS
.{$p}-cta--split-image-text {
  padding: 0;
}
.{$p}-cta-split-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  min-height: 400px;
}
.{$p}-cta-image-col {
  position: relative; overflow: hidden;
}
.{$p}-cta-image {
  width: 100%; height: 100%; object-fit: cover;
  display: block;
}
.{$p}-cta-text-col {
  display: flex; align-items: center;
  padding: clamp(40px, 6vw, 80px);
  background: var(--surface, #f8fafc);
}

CSS;
    }

    // --- Split Card ---
    private static function css_split_card(string $p): string
    {
        return <<<CSS
.{$p}-cta--split-card {
  padding: clamp(60px, 10vh, 120px) 0;
  background: var(--primary, #3b82f6);
}
.{$p}-cta-card {
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  box-shadow: 0 20px 60px rgba(0,0,0,0.15);
  overflow: hidden;
}
.{$p}-cta-card-inner {
  display: grid; grid-template-columns: 1fr auto;
  gap: clamp(24px, 4vw, 60px); align-items: center;
  padding: clamp(32px, 5vw, 60px);
}
.{$p}-cta--split-card .{$p}-cta-title {
  color: var(--text, #1e293b);
}
.{$p}-cta--split-card .{$p}-cta-subtitle {
  color: var(--text-muted, #64748b);
  margin-bottom: 0;
}

CSS;
    }

    // --- Creative Diagonal ---
    private static function css_creative_diagonal(string $p): string
    {
        return <<<CSS
.{$p}-cta--creative-diagonal {
  padding: clamp(80px, 12vh, 140px) 0;
  text-align: center;
}
.{$p}-cta-diagonal-bg {
  position: absolute; inset: 0; z-index: 0;
  background: var(--primary, #3b82f6);
  clip-path: polygon(0 0, 100% 8%, 100% 100%, 0 92%);
}
.{$p}-cta--creative-diagonal .{$p}-cta-content {
  max-width: 700px; margin: 0 auto;
}
.{$p}-cta--creative-diagonal .{$p}-cta-title {
  color: #fff;
}
.{$p}-cta--creative-diagonal .{$p}-cta-subtitle {
  color: rgba(255,255,255,0.85);
  margin-left: auto; margin-right: auto;
}
.{$p}-cta--creative-diagonal .{$p}-cta-actions {
  justify-content: center;
}
.{$p}-cta--creative-diagonal .{$p}-cta-badge {
  background: rgba(255,255,255,0.15); color: #fff;
  border-color: rgba(255,255,255,0.25);
}
.{$p}-cta--creative-diagonal .{$p}-btn-primary {
  background: #fff; color: var(--primary, #3b82f6);
  border-color: #fff;
}

CSS;
    }

    // --- Creative Wave ---
    private static function css_creative_wave(string $p): string
    {
        return <<<CSS
.{$p}-cta--creative-wave {
  padding: clamp(100px, 14vh, 160px) 0;
  text-align: center;
}
.{$p}-cta-wave-bg {
  position: absolute; inset: 0; z-index: 0;
  background: linear-gradient(135deg, var(--primary, #3b82f6) 0%, var(--secondary, #8b5cf6) 100%);
}
.{$p}-cta-wave-top,
.{$p}-cta-wave-bottom {
  position: absolute; left: 0; width: 100%;
  line-height: 0; z-index: 1;
}
.{$p}-cta-wave-top {
  top: -1px;
}
.{$p}-cta-wave-bottom {
  bottom: -1px;
}
.{$p}-cta-wave-top svg,
.{$p}-cta-wave-bottom svg {
  display: block; width: 100%; height: 50px;
}
.{$p}-cta-wave-top path,
.{$p}-cta-wave-bottom path {
  fill: var(--background, #0a0f1a);
}
.{$p}-cta--creative-wave .{$p}-cta-content {
  max-width: 700px; margin: 0 auto;
}
.{$p}-cta--creative-wave .{$p}-cta-title {
  color: #fff;
}
.{$p}-cta--creative-wave .{$p}-cta-subtitle {
  color: rgba(255,255,255,0.85);
  margin-left: auto; margin-right: auto;
}
.{$p}-cta--creative-wave .{$p}-cta-actions {
  justify-content: center;
}
.{$p}-cta--creative-wave .{$p}-cta-badge {
  background: rgba(255,255,255,0.15); color: #fff;
  border-color: rgba(255,255,255,0.25);
}
.{$p}-cta--creative-wave .{$p}-btn-primary {
  background: #fff; color: var(--primary, #3b82f6);
  border-color: #fff;
}

CSS;
    }

    // --- Creative Glassmorphism ---
    private static function css_creative_glassmorphism(string $p): string
    {
        return <<<CSS
.{$p}-cta--creative-glass {
  padding: clamp(80px, 12vh, 140px) 0;
  text-align: center;
}
.{$p}-cta-glass-bg {
  position: absolute; inset: 0; z-index: 0;
  background-size: cover; background-position: center;
  background-color: var(--primary, #3b82f6);
}
.{$p}-cta-glass-bg::after {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(135deg, rgba(var(--primary-rgb, 42,125,225), 0.7) 0%, rgba(var(--secondary-rgb, 139,92,246), 0.7) 100%);
}
.{$p}-cta-glass-card {
  position: relative; z-index: 2;
  max-width: 700px; margin: 0 auto;
  padding: clamp(32px, 5vw, 60px);
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
  border-radius: var(--radius, 16px);
  border: 1px solid rgba(255,255,255,0.2);
  box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
.{$p}-cta--creative-glass .{$p}-cta-content {
  max-width: none;
}
.{$p}-cta--creative-glass .{$p}-cta-title {
  color: #fff;
}
.{$p}-cta--creative-glass .{$p}-cta-subtitle {
  color: rgba(255,255,255,0.85);
  margin-left: auto; margin-right: auto;
}
.{$p}-cta--creative-glass .{$p}-cta-actions {
  justify-content: center;
}
.{$p}-cta--creative-glass .{$p}-cta-badge {
  background: rgba(255,255,255,0.15); color: #fff;
  border-color: rgba(255,255,255,0.25);
}
.{$p}-cta--creative-glass .{$p}-btn-primary {
  background: #fff; color: var(--primary, #3b82f6);
  border-color: #fff;
}

CSS;
    }

    // --- Minimal Inline ---
    private static function css_minimal_inline(string $p): string
    {
        return <<<CSS
.{$p}-cta--minimal-inline {
  padding: clamp(40px, 6vh, 80px) 0;
  border-top: 1px solid rgba(var(--text-rgb, 30,41,59), 0.1);
  border-bottom: 1px solid rgba(var(--text-rgb, 30,41,59), 0.1);
}
.{$p}-cta-inline {
  display: flex; align-items: center;
  justify-content: space-between; gap: 24px;
  flex-wrap: wrap;
}
.{$p}-cta--minimal-inline .{$p}-cta-title {
  font-size: clamp(1.25rem, 2.5vw, 1.75rem);
  margin: 0;
}

CSS;
    }

    // --- Minimal Bordered ---
    private static function css_minimal_bordered(string $p): string
    {
        return <<<CSS
.{$p}-cta--minimal-bordered {
  padding: clamp(60px, 10vh, 120px) 0;
}
.{$p}-cta-bordered-box {
  border: 2px solid rgba(var(--text-rgb, 30,41,59), 0.15);
  border-radius: var(--radius, 12px);
  padding: clamp(32px, 5vw, 60px);
  text-align: center;
}
.{$p}-cta--minimal-bordered .{$p}-cta-content {
  max-width: 600px; margin: 0 auto;
}
.{$p}-cta--minimal-bordered .{$p}-cta-subtitle {
  margin-left: auto; margin-right: auto;
}
.{$p}-cta--minimal-bordered .{$p}-cta-actions {
  justify-content: center;
}

CSS;
    }

    // --- Minimal Dark ---
    private static function css_minimal_dark(string $p): string
    {
        return <<<CSS
.{$p}-cta--minimal-dark {
  padding: clamp(60px, 10vh, 120px) 0;
  background: #0f172a;
  text-align: center;
}
.{$p}-cta--minimal-dark .{$p}-cta-content {
  max-width: 650px; margin: 0 auto;
}
.{$p}-cta--minimal-dark .{$p}-cta-title {
  color: #fff;
}
.{$p}-cta--minimal-dark .{$p}-cta-subtitle {
  color: rgba(255,255,255,0.65);
  margin-left: auto; margin-right: auto;
}
.{$p}-cta--minimal-dark .{$p}-cta-actions {
  justify-content: center;
}
.{$p}-cta--minimal-dark .{$p}-cta-badge {
  background: rgba(var(--primary-rgb, 42,125,225), 0.2);
  color: var(--primary, #3b82f6);
  border-color: rgba(var(--primary-rgb, 42,125,225), 0.3);
}

CSS;
    }

    // --- Responsive ---
    private static function css_responsive(string $p): string
    {
        return <<<CSS
@media (max-width: 768px) {
  .{$p}-cta-grid {
    grid-template-columns: 1fr !important;
    gap: 24px !important;
    text-align: center;
  }
  .{$p}-cta-grid .{$p}-cta-actions {
    justify-content: center;
  }
  .{$p}-cta-split-grid {
    grid-template-columns: 1fr !important;
  }
  .{$p}-cta-text-col {
    padding: clamp(32px, 5vw, 48px) !important;
  }
  .{$p}-cta-card-inner {
    grid-template-columns: 1fr !important;
    text-align: center;
  }
  .{$p}-cta-card-inner .{$p}-cta-subtitle {
    margin-bottom: 24px;
  }
  .{$p}-cta-inline {
    flex-direction: column; text-align: center;
  }
  .{$p}-cta-diagonal-bg {
    clip-path: polygon(0 0, 100% 3%, 100% 100%, 0 97%);
  }
}

CSS;
    }
}
