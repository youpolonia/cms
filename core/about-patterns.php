<?php
/**
 * About Section Pattern Registry
 * 
 * Pre-built About HTML layouts with structural CSS.
 * AI generates only decorative CSS (colors, fonts, effects) — never the structure.
 * 
 * 10 patterns across 3 groups.
 * @since 2026-02-19
 */

class AboutPatternRegistry
{
    // ═══════════════════════════════════════
    // PATTERN DEFINITIONS
    // ═══════════════════════════════════════

    private static array $patterns = [
        // --- Split (two-column text/image layouts) ---
        ['id'=>'split-image-right',  'group'=>'split',    'css_type'=>'split-image-right',
         'best_for'=>['agency','consulting','marketing','branding','seo','web-design',
                      'social-media','creative-agency']],
        ['id'=>'split-image-left',   'group'=>'split',    'css_type'=>'split-image-left',
         'best_for'=>['restaurant','bakery','cafe','hotel','spa','resort','bar',
                      'fine-dining','catering','wedding']],
        ['id'=>'split-with-stats',   'group'=>'split',    'css_type'=>'split-with-stats',
         'best_for'=>['manufacturing','engineering','construction','logistics','paving',
                      'roofing','plumbing','electrical','hvac']],
        ['id'=>'split-video',        'group'=>'split',    'css_type'=>'split-video',
         'best_for'=>['education','university','school','coaching','library','recruitment',
                      'hr','training']],

        // --- Creative (unique/bold designs) ---
        ['id'=>'creative-timeline',      'group'=>'creative', 'css_type'=>'creative-timeline',
         'best_for'=>['legal','financial','bank','insurance','accounting','consulting',
                      'investment']],
        ['id'=>'creative-team-mission',  'group'=>'creative', 'css_type'=>'creative-team-mission',
         'best_for'=>['nonprofit','charity','healthcare','clinic','hospital','dental',
                      'pharmacy','veterinary']],
        ['id'=>'creative-values-grid',   'group'=>'creative', 'css_type'=>'creative-values-grid',
         'best_for'=>['tech','saas','startup','fintech','ai','blockchain','platform',
                      'digital','app']],

        // --- Minimal (clean, understated) ---
        ['id'=>'minimal-centered',       'group'=>'minimal',  'css_type'=>'minimal-centered',
         'best_for'=>['architecture','gallery','museum','art','fashion','photography',
                      'influencer','content-creator']],
        ['id'=>'minimal-two-column',     'group'=>'minimal',  'css_type'=>'minimal-two-column',
         'best_for'=>['real-estate','landscaping','interior-design','magazine','blog',
                      'news','media']],
        ['id'=>'minimal-with-signature', 'group'=>'minimal',  'css_type'=>'minimal-with-signature',
         'best_for'=>['luxury','winery','jewelry','florist','brewery','boutique',
                      'perfume','chocolate']],
    ];

    // ═══════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════

    /**
     * Pick the best About pattern for an industry.
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
     * Render an About pattern.
     * Returns ['html'=>..., 'structural_css'=>..., 'pattern_id'=>..., 'classes'=>[...], 'fields'=>[...]]
     */
    public static function render(string $patternId, string $prefix, array $brief): array
    {
        $def = null;
        foreach (self::$patterns as $p) {
            if ($p['id'] === $patternId) { $def = $p; break; }
        }
        if (!$def) {
            $def = self::$patterns[0]; // fallback to split-image-right
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
        // Common fields all About sections have
        $common = [
            'badge'    => ['type' => 'text',     'label' => 'Badge / Label'],
            'title'    => ['type' => 'text',     'label' => 'About Title'],
            'subtitle' => ['type' => 'textarea', 'label' => 'About Subtitle'],
            'text'     => ['type' => 'textarea', 'label' => 'About Text'],
            'btn_text' => ['type' => 'text',     'label' => 'Button Text'],
            'btn_link' => ['type' => 'text',     'label' => 'Button Link'],
        ];

        // Pattern-specific extras
        $extras = match($patternId) {
            'split-image-right', 'split-image-left' => [
                'image' => ['type' => 'image', 'label' => 'About Image'],
            ],
            'split-with-stats' => [
                'image'       => ['type' => 'image', 'label' => 'About Image'],
                'stat1_value' => ['type' => 'text',  'label' => 'Stat 1 Value'],
                'stat1_label' => ['type' => 'text',  'label' => 'Stat 1 Label'],
                'stat2_value' => ['type' => 'text',  'label' => 'Stat 2 Value'],
                'stat2_label' => ['type' => 'text',  'label' => 'Stat 2 Label'],
                'stat3_value' => ['type' => 'text',  'label' => 'Stat 3 Value'],
                'stat3_label' => ['type' => 'text',  'label' => 'Stat 3 Label'],
            ],
            'split-video' => [
                'image'     => ['type' => 'image', 'label' => 'Video Thumbnail'],
                'video_url' => ['type' => 'text',  'label' => 'Video URL'],
            ],
            'creative-timeline' => [
                'year1'      => ['type' => 'text', 'label' => 'Year 1'],
                'milestone1' => ['type' => 'text', 'label' => 'Milestone 1'],
                'year2'      => ['type' => 'text', 'label' => 'Year 2'],
                'milestone2' => ['type' => 'text', 'label' => 'Milestone 2'],
                'year3'      => ['type' => 'text', 'label' => 'Year 3'],
                'milestone3' => ['type' => 'text', 'label' => 'Milestone 3'],
                'year4'      => ['type' => 'text', 'label' => 'Year 4'],
                'milestone4' => ['type' => 'text', 'label' => 'Milestone 4'],
            ],
            'creative-team-mission' => [
                'image'        => ['type' => 'image',    'label' => 'Team Photo'],
                'mission'      => ['type' => 'textarea', 'label' => 'Mission'],
                'vision'       => ['type' => 'textarea', 'label' => 'Vision'],
                'values'       => ['type' => 'textarea', 'label' => 'Values'],
            ],
            'creative-values-grid' => [
                'value1_icon'  => ['type' => 'text',     'label' => 'Value 1 Icon'],
                'value1_title' => ['type' => 'text',     'label' => 'Value 1 Title'],
                'value1_text'  => ['type' => 'textarea', 'label' => 'Value 1 Text'],
                'value2_icon'  => ['type' => 'text',     'label' => 'Value 2 Icon'],
                'value2_title' => ['type' => 'text',     'label' => 'Value 2 Title'],
                'value2_text'  => ['type' => 'textarea', 'label' => 'Value 2 Text'],
                'value3_icon'  => ['type' => 'text',     'label' => 'Value 3 Icon'],
                'value3_title' => ['type' => 'text',     'label' => 'Value 3 Title'],
                'value3_text'  => ['type' => 'textarea', 'label' => 'Value 3 Text'],
                'value4_icon'  => ['type' => 'text',     'label' => 'Value 4 Icon'],
                'value4_title' => ['type' => 'text',     'label' => 'Value 4 Title'],
                'value4_text'  => ['type' => 'textarea', 'label' => 'Value 4 Text'],
                'value5_icon'  => ['type' => 'text',     'label' => 'Value 5 Icon'],
                'value5_title' => ['type' => 'text',     'label' => 'Value 5 Title'],
                'value5_text'  => ['type' => 'textarea', 'label' => 'Value 5 Text'],
                'value6_icon'  => ['type' => 'text',     'label' => 'Value 6 Icon'],
                'value6_title' => ['type' => 'text',     'label' => 'Value 6 Title'],
                'value6_text'  => ['type' => 'textarea', 'label' => 'Value 6 Text'],
            ],
            'minimal-with-signature' => [
                'founder_name' => ['type' => 'text', 'label' => 'Founder Name'],
                'founder_role' => ['type' => 'text', 'label' => 'Founder Role'],
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
            'split-image-right' => <<<'G'
- Image right: border-radius var(--radius-lg), box-shadow 0 20px 60px rgba(0,0,0,0.12)
- Decorative shape behind image: circle or blob, bg rgba(primary,0.08), large
- Content left: clean, generous line-height 1.8
- Accent line before title: 40px width, 3px height, var(--primary)
G,
            'split-image-left' => <<<'G'
- Same as split-image-right but reversed
- Image left with border-radius + shadow
- Decorative accent on opposite side from image
G,
            'split-with-stats' => <<<'G'
- Stats row below: numbers large (2.5rem), font-weight 700, color var(--primary)
- Labels: small, uppercase, letter-spacing, muted
- Stats separated by border-right or gap divider
- Image: same elevated treatment as split variants
G,
            'split-video' => <<<'G'
- Video thumbnail: border-radius, box-shadow
- Play button: circle 64px, bg white, center triangle icon
- Play hover: scale(1.1), box-shadow 0 0 30px rgba(primary,0.3)
- Overlay on thumbnail: subtle dark gradient from bottom
G,
            'creative-timeline' => <<<'G'
- Vertical timeline line: 2px solid var(--primary), centered
- Year markers: font-weight 700, bg var(--primary), color white, circle or pill
- Alternating cards left/right off timeline
- Dot connectors: 12px circles, primary border, white center
G,
            'creative-team-mission' => <<<'G'
- Team photo grid: small images in grid, border-radius, grayscale filter, hover full color
- Mission statement: large italic quote or highlighted block
- Photo overlay on hover: bg rgba(primary,0.3), name appears
G,
            'creative-values-grid' => <<<'G'
- Value cards: each with distinct icon treatment
- nth-child accent variation: alternate subtle bg colors between cards
- Icon prominent: large (64px), primary bg tint, centered above title
- Subtle border between cards or shadow
G,
            'minimal-centered' => <<<'G'
- Centered text block, no image, typography-focused
- Decorative divider: thin line or ornament, centered, primary color
- Large text: generous font-size for description, line-height 1.9
- Minimal visual clutter, whitespace is the design element
G,
            'minimal-two-column' => <<<'G'
- Two text columns side-by-side, balanced widths
- Clean, no borders between columns, gap handles separation
- Heading accents: primary color or underline decoration
- Symmetric, balanced, professional
G,
            'minimal-with-signature' => <<<'G'
- Signature: handwriting-style font (cursive) or actual signature image
- Quote marks: large decorative, opacity 0.15, primary color
- Personal touch: italic quote or founder message
- Clean, intimate, warm feel
G,
            default => '',
        };
    }

    // ═══════════════════════════════════════
    // HTML TEMPLATES
    // ═══════════════════════════════════════

    /**
     * Replace generic placeholder defaults in About template with actual brief content.
     */
    private static function injectBriefContent(string $html, array $brief): string
    {
        $name = $brief['name'] ?? '';
        $industry = $brief['industry'] ?? '';

        // About title from brief
        $title = $brief['about_title'] ?? '';
        if (!$title && $name) {
            $title = "About {$name}";
        }

        // About subtitle from brief
        $subtitle = $brief['about_subtitle'] ?? '';

        // About text from brief
        $text = $brief['about_text'] ?? '';

        // Badge from industry
        $badge = '';
        if ($industry) {
            $badge = ucwords(str_replace('-', ' ', $industry));
        }

        // Button text from brief
        $btnText = $brief['about_btn_text'] ?? '';

        // Replace defaults in theme_get() calls
        $replacements = [];
        if ($title)    $replacements["theme_get('about.title', 'About Us')"]                                                                                                          = "theme_get('about.title', '" . addslashes($title) . "')";
        if ($subtitle) $replacements["theme_get('about.subtitle', 'We are a passionate team dedicated to delivering excellence in everything we do.')"]                                = "theme_get('about.subtitle', '" . addslashes($subtitle) . "')";
        if ($text)     $replacements["theme_get('about.text', 'Founded with a vision to make a difference, we have grown from a small team into a trusted name in our industry. Our commitment to quality, innovation, and customer satisfaction drives everything we do. We believe in building lasting relationships and delivering results that exceed expectations.')"] = "theme_get('about.text', '" . addslashes($text) . "')";
        if ($badge)    $replacements["theme_get('about.badge', '')"]                                                                                                                   = "theme_get('about.badge', '" . addslashes($badge) . "')";
        if ($btnText)  $replacements["theme_get('about.btn_text', 'Learn More')"]                                                                                                     = "theme_get('about.btn_text', '" . addslashes($btnText) . "')";

        foreach ($replacements as $search => $replace) {
            $html = str_replace($search, $replace, $html);
        }

        return $html;
    }

    private static function buildHTML(string $patternId, string $p): string
    {
        $templates = self::getTemplates($p);
        return $templates[$patternId] ?? $templates['split-image-right'];
    }

    private static function getTemplates(string $p): array
    {
        return [

// ── Split Image Right: Text left, image right ──
'split-image-right' => <<<HTML
<?php
\$aboutBadge = theme_get('about.badge', '');
\$aboutTitle = theme_get('about.title', 'About Us');
\$aboutSubtitle = theme_get('about.subtitle', 'We are a passionate team dedicated to delivering excellence in everything we do.');
\$aboutText = theme_get('about.text', 'Founded with a vision to make a difference, we have grown from a small team into a trusted name in our industry. Our commitment to quality, innovation, and customer satisfaction drives everything we do. We believe in building lasting relationships and delivering results that exceed expectations.');
\$aboutImage = theme_get('about.image', '');
\$aboutBtnText = theme_get('about.btn_text', 'Learn More');
\$aboutBtnLink = theme_get('about.btn_link', '/about');
?>
<section class="{$p}-about {$p}-about--split-image-right" id="about">
  <div class="container">
    <div class="{$p}-about-grid" data-animate="fade-up">
      <div class="{$p}-about-content">
        <?php if (\$aboutBadge): ?><span class="{$p}-about-badge" data-ts="about.badge"><?= esc(\$aboutBadge) ?></span><?php endif; ?>
        <h2 class="{$p}-about-title" data-ts="about.title"><?= esc(\$aboutTitle) ?></h2>
        <p class="{$p}-about-subtitle" data-ts="about.subtitle"><?= esc(\$aboutSubtitle) ?></p>
        <p class="{$p}-about-text" data-ts="about.text"><?= esc(\$aboutText) ?></p>
        <div class="{$p}-about-actions">
          <a href="<?= esc(\$aboutBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="about.btn_text" data-ts-href="about.btn_link"><?= esc(\$aboutBtnText) ?></a>
        </div>
      </div>
      <div class="{$p}-about-image-col">
        <?php if (\$aboutImage): ?>
          <img src="<?= esc(\$aboutImage) ?>" alt="<?= esc(\$aboutTitle) ?>" class="{$p}-about-image" data-ts-src="about.image" loading="lazy">
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Split Image Left: Image left, text right ──
'split-image-left' => <<<HTML
<?php
\$aboutBadge = theme_get('about.badge', '');
\$aboutTitle = theme_get('about.title', 'About Us');
\$aboutSubtitle = theme_get('about.subtitle', 'We are a passionate team dedicated to delivering excellence in everything we do.');
\$aboutText = theme_get('about.text', 'Founded with a vision to make a difference, we have grown from a small team into a trusted name in our industry. Our commitment to quality, innovation, and customer satisfaction drives everything we do. We believe in building lasting relationships and delivering results that exceed expectations.');
\$aboutImage = theme_get('about.image', '');
\$aboutBtnText = theme_get('about.btn_text', 'Learn More');
\$aboutBtnLink = theme_get('about.btn_link', '/about');
?>
<section class="{$p}-about {$p}-about--split-image-left" id="about">
  <div class="container">
    <div class="{$p}-about-grid" data-animate="fade-up">
      <div class="{$p}-about-image-col">
        <?php if (\$aboutImage): ?>
          <img src="<?= esc(\$aboutImage) ?>" alt="<?= esc(\$aboutTitle) ?>" class="{$p}-about-image" data-ts-src="about.image" loading="lazy">
        <?php endif; ?>
      </div>
      <div class="{$p}-about-content">
        <?php if (\$aboutBadge): ?><span class="{$p}-about-badge" data-ts="about.badge"><?= esc(\$aboutBadge) ?></span><?php endif; ?>
        <h2 class="{$p}-about-title" data-ts="about.title"><?= esc(\$aboutTitle) ?></h2>
        <p class="{$p}-about-subtitle" data-ts="about.subtitle"><?= esc(\$aboutSubtitle) ?></p>
        <p class="{$p}-about-text" data-ts="about.text"><?= esc(\$aboutText) ?></p>
        <div class="{$p}-about-actions">
          <a href="<?= esc(\$aboutBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="about.btn_text" data-ts-href="about.btn_link"><?= esc(\$aboutBtnText) ?></a>
        </div>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Split With Stats: Text left, image right, stats row below ──
'split-with-stats' => <<<HTML
<?php
\$aboutBadge = theme_get('about.badge', '');
\$aboutTitle = theme_get('about.title', 'About Us');
\$aboutSubtitle = theme_get('about.subtitle', 'We are a passionate team dedicated to delivering excellence in everything we do.');
\$aboutText = theme_get('about.text', 'Founded with a vision to make a difference, we have grown from a small team into a trusted name in our industry. Our commitment to quality, innovation, and customer satisfaction drives everything we do. We believe in building lasting relationships and delivering results that exceed expectations.');
\$aboutImage = theme_get('about.image', '');
\$aboutBtnText = theme_get('about.btn_text', 'Learn More');
\$aboutBtnLink = theme_get('about.btn_link', '/about');
\$stat1Value = theme_get('about.stat1_value', '15+');
\$stat1Label = theme_get('about.stat1_label', 'Years Experience');
\$stat2Value = theme_get('about.stat2_value', '500+');
\$stat2Label = theme_get('about.stat2_label', 'Projects Completed');
\$stat3Value = theme_get('about.stat3_value', '98%');
\$stat3Label = theme_get('about.stat3_label', 'Client Satisfaction');
?>
<section class="{$p}-about {$p}-about--split-with-stats" id="about">
  <div class="container">
    <div class="{$p}-about-grid" data-animate="fade-up">
      <div class="{$p}-about-content">
        <?php if (\$aboutBadge): ?><span class="{$p}-about-badge" data-ts="about.badge"><?= esc(\$aboutBadge) ?></span><?php endif; ?>
        <h2 class="{$p}-about-title" data-ts="about.title"><?= esc(\$aboutTitle) ?></h2>
        <p class="{$p}-about-subtitle" data-ts="about.subtitle"><?= esc(\$aboutSubtitle) ?></p>
        <p class="{$p}-about-text" data-ts="about.text"><?= esc(\$aboutText) ?></p>
        <div class="{$p}-about-actions">
          <a href="<?= esc(\$aboutBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="about.btn_text" data-ts-href="about.btn_link"><?= esc(\$aboutBtnText) ?></a>
        </div>
      </div>
      <div class="{$p}-about-image-col">
        <?php if (\$aboutImage): ?>
          <img src="<?= esc(\$aboutImage) ?>" alt="<?= esc(\$aboutTitle) ?>" class="{$p}-about-image" data-ts-src="about.image" loading="lazy">
        <?php endif; ?>
      </div>
    </div>
    <div class="{$p}-about-stats" data-animate="fade-up">
      <div class="{$p}-about-stat">
        <span class="{$p}-about-stat-value" data-ts="about.stat1_value"><?= esc(\$stat1Value) ?></span>
        <span class="{$p}-about-stat-label" data-ts="about.stat1_label"><?= esc(\$stat1Label) ?></span>
      </div>
      <div class="{$p}-about-stat">
        <span class="{$p}-about-stat-value" data-ts="about.stat2_value"><?= esc(\$stat2Value) ?></span>
        <span class="{$p}-about-stat-label" data-ts="about.stat2_label"><?= esc(\$stat2Label) ?></span>
      </div>
      <div class="{$p}-about-stat">
        <span class="{$p}-about-stat-value" data-ts="about.stat3_value"><?= esc(\$stat3Value) ?></span>
        <span class="{$p}-about-stat-label" data-ts="about.stat3_label"><?= esc(\$stat3Label) ?></span>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Split Video: Text left, video thumbnail right with play button ──
'split-video' => <<<HTML
<?php
\$aboutBadge = theme_get('about.badge', '');
\$aboutTitle = theme_get('about.title', 'About Us');
\$aboutSubtitle = theme_get('about.subtitle', 'We are a passionate team dedicated to delivering excellence in everything we do.');
\$aboutText = theme_get('about.text', 'Founded with a vision to make a difference, we have grown from a small team into a trusted name in our industry. Our commitment to quality, innovation, and customer satisfaction drives everything we do. We believe in building lasting relationships and delivering results that exceed expectations.');
\$aboutImage = theme_get('about.image', '');
\$aboutVideoUrl = theme_get('about.video_url', '#');
\$aboutBtnText = theme_get('about.btn_text', 'Learn More');
\$aboutBtnLink = theme_get('about.btn_link', '/about');
?>
<section class="{$p}-about {$p}-about--split-video" id="about">
  <div class="container">
    <div class="{$p}-about-grid" data-animate="fade-up">
      <div class="{$p}-about-content">
        <?php if (\$aboutBadge): ?><span class="{$p}-about-badge" data-ts="about.badge"><?= esc(\$aboutBadge) ?></span><?php endif; ?>
        <h2 class="{$p}-about-title" data-ts="about.title"><?= esc(\$aboutTitle) ?></h2>
        <p class="{$p}-about-subtitle" data-ts="about.subtitle"><?= esc(\$aboutSubtitle) ?></p>
        <p class="{$p}-about-text" data-ts="about.text"><?= esc(\$aboutText) ?></p>
        <div class="{$p}-about-actions">
          <a href="<?= esc(\$aboutBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="about.btn_text" data-ts-href="about.btn_link"><?= esc(\$aboutBtnText) ?></a>
        </div>
      </div>
      <div class="{$p}-about-video-col">
        <div class="{$p}-about-video-wrapper">
          <?php if (\$aboutImage): ?>
            <img src="<?= esc(\$aboutImage) ?>" alt="<?= esc(\$aboutTitle) ?>" class="{$p}-about-video-thumb" data-ts-src="about.image" loading="lazy">
          <?php endif; ?>
          <a href="<?= esc(\$aboutVideoUrl) ?>" class="{$p}-about-play-btn" data-ts-href="about.video_url" aria-label="Play video">
            <svg viewBox="0 0 68 48" width="68" height="48"><path d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,0,34,0,34,0S12.21,0,6.9,1.55C3.97,2.33,2.27,4.81,1.48,7.74C0,13.05,0,24,0,24s0,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19C12.21,48,34,48,34,48s21.79,0,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C68,34.95,68,24,68,24S68,13.05,66.52,7.74z" fill="rgba(255,255,255,0.9)"></path><path d="M 45,24 27,14 27,34" fill="var(--primary, #3b82f6)"></path></svg>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Creative Timeline: Company timeline with year milestones ──
'creative-timeline' => <<<HTML
<?php
\$aboutBadge = theme_get('about.badge', '');
\$aboutTitle = theme_get('about.title', 'About Us');
\$aboutSubtitle = theme_get('about.subtitle', 'We are a passionate team dedicated to delivering excellence in everything we do.');
\$aboutText = theme_get('about.text', 'Founded with a vision to make a difference, we have grown from a small team into a trusted name in our industry. Our commitment to quality, innovation, and customer satisfaction drives everything we do. We believe in building lasting relationships and delivering results that exceed expectations.');
\$aboutBtnText = theme_get('about.btn_text', 'Learn More');
\$aboutBtnLink = theme_get('about.btn_link', '/about');
\$year1 = theme_get('about.year1', '2010');
\$milestone1 = theme_get('about.milestone1', 'Company Founded');
\$year2 = theme_get('about.year2', '2014');
\$milestone2 = theme_get('about.milestone2', 'Expanded Operations');
\$year3 = theme_get('about.year3', '2018');
\$milestone3 = theme_get('about.milestone3', 'Reached 100 Clients');
\$year4 = theme_get('about.year4', '2024');
\$milestone4 = theme_get('about.milestone4', 'Industry Leader');
?>
<section class="{$p}-about {$p}-about--creative-timeline" id="about">
  <div class="container">
    <div class="{$p}-about-header" data-animate="fade-up">
      <?php if (\$aboutBadge): ?><span class="{$p}-about-badge" data-ts="about.badge"><?= esc(\$aboutBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-about-title" data-ts="about.title"><?= esc(\$aboutTitle) ?></h2>
      <p class="{$p}-about-subtitle" data-ts="about.subtitle"><?= esc(\$aboutSubtitle) ?></p>
    </div>
    <p class="{$p}-about-text {$p}-about-text--centered" data-ts="about.text" data-animate="fade-up"><?= esc(\$aboutText) ?></p>
    <div class="{$p}-about-timeline" data-animate="fade-up">
      <div class="{$p}-about-timeline-line"></div>
      <div class="{$p}-about-timeline-item">
        <span class="{$p}-about-timeline-dot"></span>
        <span class="{$p}-about-timeline-year" data-ts="about.year1"><?= esc(\$year1) ?></span>
        <span class="{$p}-about-timeline-desc" data-ts="about.milestone1"><?= esc(\$milestone1) ?></span>
      </div>
      <div class="{$p}-about-timeline-item">
        <span class="{$p}-about-timeline-dot"></span>
        <span class="{$p}-about-timeline-year" data-ts="about.year2"><?= esc(\$year2) ?></span>
        <span class="{$p}-about-timeline-desc" data-ts="about.milestone2"><?= esc(\$milestone2) ?></span>
      </div>
      <div class="{$p}-about-timeline-item">
        <span class="{$p}-about-timeline-dot"></span>
        <span class="{$p}-about-timeline-year" data-ts="about.year3"><?= esc(\$year3) ?></span>
        <span class="{$p}-about-timeline-desc" data-ts="about.milestone3"><?= esc(\$milestone3) ?></span>
      </div>
      <div class="{$p}-about-timeline-item">
        <span class="{$p}-about-timeline-dot"></span>
        <span class="{$p}-about-timeline-year" data-ts="about.year4"><?= esc(\$year4) ?></span>
        <span class="{$p}-about-timeline-desc" data-ts="about.milestone4"><?= esc(\$milestone4) ?></span>
      </div>
    </div>
    <div class="{$p}-about-actions {$p}-about-actions--centered" data-animate="fade-up">
      <a href="<?= esc(\$aboutBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="about.btn_text" data-ts-href="about.btn_link"><?= esc(\$aboutBtnText) ?></a>
    </div>
  </div>
</section>
HTML,

// ── Creative Team Mission: Mission/vision/values grid + team photo ──
'creative-team-mission' => <<<HTML
<?php
\$aboutBadge = theme_get('about.badge', '');
\$aboutTitle = theme_get('about.title', 'About Us');
\$aboutSubtitle = theme_get('about.subtitle', 'We are a passionate team dedicated to delivering excellence in everything we do.');
\$aboutText = theme_get('about.text', 'Founded with a vision to make a difference, we have grown from a small team into a trusted name in our industry. Our commitment to quality, innovation, and customer satisfaction drives everything we do. We believe in building lasting relationships and delivering results that exceed expectations.');
\$aboutImage = theme_get('about.image', '');
\$aboutMission = theme_get('about.mission', 'To provide exceptional service and create meaningful impact in every community we serve.');
\$aboutVision = theme_get('about.vision', 'A world where everyone has access to quality care and support when they need it most.');
\$aboutValues = theme_get('about.values', 'Compassion, integrity, and dedication guide every decision we make and every life we touch.');
\$aboutBtnText = theme_get('about.btn_text', 'Learn More');
\$aboutBtnLink = theme_get('about.btn_link', '/about');
?>
<section class="{$p}-about {$p}-about--creative-team-mission" id="about">
  <div class="container">
    <div class="{$p}-about-header" data-animate="fade-up">
      <?php if (\$aboutBadge): ?><span class="{$p}-about-badge" data-ts="about.badge"><?= esc(\$aboutBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-about-title" data-ts="about.title"><?= esc(\$aboutTitle) ?></h2>
      <p class="{$p}-about-subtitle" data-ts="about.subtitle"><?= esc(\$aboutSubtitle) ?></p>
    </div>
    <div class="{$p}-about-mission-grid" data-animate="fade-up">
      <div class="{$p}-about-mission-card">
        <div class="{$p}-about-mission-icon"><i class="fas fa-bullseye"></i></div>
        <h3 class="{$p}-about-mission-label">Our Mission</h3>
        <p class="{$p}-about-mission-text" data-ts="about.mission"><?= esc(\$aboutMission) ?></p>
      </div>
      <div class="{$p}-about-mission-card">
        <div class="{$p}-about-mission-icon"><i class="fas fa-eye"></i></div>
        <h3 class="{$p}-about-mission-label">Our Vision</h3>
        <p class="{$p}-about-mission-text" data-ts="about.vision"><?= esc(\$aboutVision) ?></p>
      </div>
      <div class="{$p}-about-mission-card">
        <div class="{$p}-about-mission-icon"><i class="fas fa-heart"></i></div>
        <h3 class="{$p}-about-mission-label">Our Values</h3>
        <p class="{$p}-about-mission-text" data-ts="about.values"><?= esc(\$aboutValues) ?></p>
      </div>
    </div>
    <div class="{$p}-about-team-row" data-animate="fade-up">
      <div class="{$p}-about-team-image-col">
        <?php if (\$aboutImage): ?>
          <img src="<?= esc(\$aboutImage) ?>" alt="Our Team" class="{$p}-about-team-photo" data-ts-src="about.image" loading="lazy">
        <?php endif; ?>
      </div>
      <div class="{$p}-about-team-text-col">
        <p class="{$p}-about-text" data-ts="about.text"><?= esc(\$aboutText) ?></p>
        <div class="{$p}-about-actions">
          <a href="<?= esc(\$aboutBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="about.btn_text" data-ts-href="about.btn_link"><?= esc(\$aboutBtnText) ?></a>
        </div>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Creative Values Grid: 2x3 grid of value cards with icons ──
'creative-values-grid' => <<<HTML
<?php
\$aboutBadge = theme_get('about.badge', '');
\$aboutTitle = theme_get('about.title', 'About Us');
\$aboutSubtitle = theme_get('about.subtitle', 'We are a passionate team dedicated to delivering excellence in everything we do.');
\$aboutText = theme_get('about.text', 'Founded with a vision to make a difference, we have grown from a small team into a trusted name in our industry. Our commitment to quality, innovation, and customer satisfaction drives everything we do. We believe in building lasting relationships and delivering results that exceed expectations.');
\$aboutBtnText = theme_get('about.btn_text', 'Learn More');
\$aboutBtnLink = theme_get('about.btn_link', '/about');
\$v1Icon = theme_get('about.value1_icon', 'fas fa-heart');
\$v1Title = theme_get('about.value1_title', 'Integrity');
\$v1Text = theme_get('about.value1_text', 'We hold ourselves to the highest ethical standards in everything we do.');
\$v2Icon = theme_get('about.value2_icon', 'fas fa-rocket');
\$v2Title = theme_get('about.value2_title', 'Innovation');
\$v2Text = theme_get('about.value2_text', 'We push boundaries and embrace new technologies to deliver cutting-edge solutions.');
\$v3Icon = theme_get('about.value3_icon', 'fas fa-users');
\$v3Title = theme_get('about.value3_title', 'Collaboration');
\$v3Text = theme_get('about.value3_text', 'We believe the best results come from working together as one unified team.');
\$v4Icon = theme_get('about.value4_icon', 'fas fa-shield-alt');
\$v4Title = theme_get('about.value4_title', 'Reliability');
\$v4Text = theme_get('about.value4_text', 'Our clients count on us to deliver consistent, dependable results every time.');
\$v5Icon = theme_get('about.value5_icon', 'fas fa-lightbulb');
\$v5Title = theme_get('about.value5_title', 'Creativity');
\$v5Text = theme_get('about.value5_text', 'We approach every challenge with fresh thinking and creative problem-solving.');
\$v6Icon = theme_get('about.value6_icon', 'fas fa-chart-line');
\$v6Title = theme_get('about.value6_title', 'Growth');
\$v6Text = theme_get('about.value6_text', 'We are committed to continuous improvement and helping our clients scale.');
?>
<section class="{$p}-about {$p}-about--creative-values-grid" id="about">
  <div class="container">
    <div class="{$p}-about-header" data-animate="fade-up">
      <?php if (\$aboutBadge): ?><span class="{$p}-about-badge" data-ts="about.badge"><?= esc(\$aboutBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-about-title" data-ts="about.title"><?= esc(\$aboutTitle) ?></h2>
      <p class="{$p}-about-subtitle" data-ts="about.subtitle"><?= esc(\$aboutSubtitle) ?></p>
    </div>
    <p class="{$p}-about-text {$p}-about-text--centered" data-ts="about.text" data-animate="fade-up"><?= esc(\$aboutText) ?></p>
    <div class="{$p}-about-values-grid" data-animate="fade-up">
      <div class="{$p}-about-value-card">
        <div class="{$p}-about-value-icon"><i class="<?= esc(\$v1Icon) ?>" data-ts="about.value1_icon"></i></div>
        <h3 class="{$p}-about-value-title" data-ts="about.value1_title"><?= esc(\$v1Title) ?></h3>
        <p class="{$p}-about-value-text" data-ts="about.value1_text"><?= esc(\$v1Text) ?></p>
      </div>
      <div class="{$p}-about-value-card">
        <div class="{$p}-about-value-icon"><i class="<?= esc(\$v2Icon) ?>" data-ts="about.value2_icon"></i></div>
        <h3 class="{$p}-about-value-title" data-ts="about.value2_title"><?= esc(\$v2Title) ?></h3>
        <p class="{$p}-about-value-text" data-ts="about.value2_text"><?= esc(\$v2Text) ?></p>
      </div>
      <div class="{$p}-about-value-card">
        <div class="{$p}-about-value-icon"><i class="<?= esc(\$v3Icon) ?>" data-ts="about.value3_icon"></i></div>
        <h3 class="{$p}-about-value-title" data-ts="about.value3_title"><?= esc(\$v3Title) ?></h3>
        <p class="{$p}-about-value-text" data-ts="about.value3_text"><?= esc(\$v3Text) ?></p>
      </div>
      <div class="{$p}-about-value-card">
        <div class="{$p}-about-value-icon"><i class="<?= esc(\$v4Icon) ?>" data-ts="about.value4_icon"></i></div>
        <h3 class="{$p}-about-value-title" data-ts="about.value4_title"><?= esc(\$v4Title) ?></h3>
        <p class="{$p}-about-value-text" data-ts="about.value4_text"><?= esc(\$v4Text) ?></p>
      </div>
      <div class="{$p}-about-value-card">
        <div class="{$p}-about-value-icon"><i class="<?= esc(\$v5Icon) ?>" data-ts="about.value5_icon"></i></div>
        <h3 class="{$p}-about-value-title" data-ts="about.value5_title"><?= esc(\$v5Title) ?></h3>
        <p class="{$p}-about-value-text" data-ts="about.value5_text"><?= esc(\$v5Text) ?></p>
      </div>
      <div class="{$p}-about-value-card">
        <div class="{$p}-about-value-icon"><i class="<?= esc(\$v6Icon) ?>" data-ts="about.value6_icon"></i></div>
        <h3 class="{$p}-about-value-title" data-ts="about.value6_title"><?= esc(\$v6Title) ?></h3>
        <p class="{$p}-about-value-text" data-ts="about.value6_text"><?= esc(\$v6Text) ?></p>
      </div>
    </div>
    <div class="{$p}-about-actions {$p}-about-actions--centered" data-animate="fade-up">
      <a href="<?= esc(\$aboutBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="about.btn_text" data-ts-href="about.btn_link"><?= esc(\$aboutBtnText) ?></a>
    </div>
  </div>
</section>
HTML,

// ── Minimal Centered: Centered text, no image ──
'minimal-centered' => <<<HTML
<?php
\$aboutBadge = theme_get('about.badge', '');
\$aboutTitle = theme_get('about.title', 'About Us');
\$aboutSubtitle = theme_get('about.subtitle', 'We are a passionate team dedicated to delivering excellence in everything we do.');
\$aboutText = theme_get('about.text', 'Founded with a vision to make a difference, we have grown from a small team into a trusted name in our industry. Our commitment to quality, innovation, and customer satisfaction drives everything we do. We believe in building lasting relationships and delivering results that exceed expectations.');
\$aboutBtnText = theme_get('about.btn_text', 'Learn More');
\$aboutBtnLink = theme_get('about.btn_link', '/about');
?>
<section class="{$p}-about {$p}-about--minimal-centered" id="about">
  <div class="container">
    <div class="{$p}-about-content {$p}-about-content--centered" data-animate="fade-up">
      <?php if (\$aboutBadge): ?><span class="{$p}-about-badge" data-ts="about.badge"><?= esc(\$aboutBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-about-title" data-ts="about.title"><?= esc(\$aboutTitle) ?></h2>
      <p class="{$p}-about-subtitle" data-ts="about.subtitle"><?= esc(\$aboutSubtitle) ?></p>
      <div class="{$p}-about-divider"></div>
      <p class="{$p}-about-text" data-ts="about.text"><?= esc(\$aboutText) ?></p>
      <div class="{$p}-about-actions">
        <a href="<?= esc(\$aboutBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="about.btn_text" data-ts-href="about.btn_link"><?= esc(\$aboutBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Minimal Two Column: Two text columns (story left, details right) ──
'minimal-two-column' => <<<HTML
<?php
\$aboutBadge = theme_get('about.badge', '');
\$aboutTitle = theme_get('about.title', 'About Us');
\$aboutSubtitle = theme_get('about.subtitle', 'We are a passionate team dedicated to delivering excellence in everything we do.');
\$aboutText = theme_get('about.text', 'Founded with a vision to make a difference, we have grown from a small team into a trusted name in our industry. Our commitment to quality, innovation, and customer satisfaction drives everything we do. We believe in building lasting relationships and delivering results that exceed expectations.');
\$aboutBtnText = theme_get('about.btn_text', 'Learn More');
\$aboutBtnLink = theme_get('about.btn_link', '/about');
?>
<section class="{$p}-about {$p}-about--minimal-two-column" id="about">
  <div class="container">
    <div class="{$p}-about-two-col" data-animate="fade-up">
      <div class="{$p}-about-col-left">
        <?php if (\$aboutBadge): ?><span class="{$p}-about-badge" data-ts="about.badge"><?= esc(\$aboutBadge) ?></span><?php endif; ?>
        <h2 class="{$p}-about-title" data-ts="about.title"><?= esc(\$aboutTitle) ?></h2>
        <p class="{$p}-about-subtitle" data-ts="about.subtitle"><?= esc(\$aboutSubtitle) ?></p>
        <div class="{$p}-about-actions">
          <a href="<?= esc(\$aboutBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="about.btn_text" data-ts-href="about.btn_link"><?= esc(\$aboutBtnText) ?></a>
        </div>
      </div>
      <div class="{$p}-about-col-right">
        <p class="{$p}-about-text" data-ts="about.text"><?= esc(\$aboutText) ?></p>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Minimal With Signature: Text + handwritten-style founder name ──
'minimal-with-signature' => <<<HTML
<?php
\$aboutBadge = theme_get('about.badge', '');
\$aboutTitle = theme_get('about.title', 'About Us');
\$aboutSubtitle = theme_get('about.subtitle', 'We are a passionate team dedicated to delivering excellence in everything we do.');
\$aboutText = theme_get('about.text', 'Founded with a vision to make a difference, we have grown from a small team into a trusted name in our industry. Our commitment to quality, innovation, and customer satisfaction drives everything we do. We believe in building lasting relationships and delivering results that exceed expectations.');
\$aboutFounderName = theme_get('about.founder_name', 'John Smith');
\$aboutFounderRole = theme_get('about.founder_role', 'Founder & CEO');
\$aboutBtnText = theme_get('about.btn_text', 'Learn More');
\$aboutBtnLink = theme_get('about.btn_link', '/about');
?>
<section class="{$p}-about {$p}-about--minimal-with-signature" id="about">
  <div class="container">
    <div class="{$p}-about-signature-layout" data-animate="fade-up">
      <div class="{$p}-about-content">
        <?php if (\$aboutBadge): ?><span class="{$p}-about-badge" data-ts="about.badge"><?= esc(\$aboutBadge) ?></span><?php endif; ?>
        <h2 class="{$p}-about-title" data-ts="about.title"><?= esc(\$aboutTitle) ?></h2>
        <p class="{$p}-about-subtitle" data-ts="about.subtitle"><?= esc(\$aboutSubtitle) ?></p>
        <p class="{$p}-about-text" data-ts="about.text"><?= esc(\$aboutText) ?></p>
        <div class="{$p}-about-signature">
          <span class="{$p}-about-signature-name" data-ts="about.founder_name"><?= esc(\$aboutFounderName) ?></span>
          <span class="{$p}-about-signature-role" data-ts="about.founder_role"><?= esc(\$aboutFounderRole) ?></span>
        </div>
        <div class="{$p}-about-actions">
          <a href="<?= esc(\$aboutBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="about.btn_text" data-ts-href="about.btn_link"><?= esc(\$aboutBtnText) ?></a>
        </div>
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
        $base = ["{$p}-about", "{$p}-about-content", "{$p}-about-badge", "{$p}-about-title",
                 "{$p}-about-subtitle", "{$p}-about-text", "{$p}-about-actions",
                 "{$p}-btn", "{$p}-btn-primary"];

        $extra = match($patternId) {
            'split-image-right' => ["{$p}-about-grid", "{$p}-about-image-col", "{$p}-about-image"],
            'split-image-left' => ["{$p}-about-grid", "{$p}-about-image-col", "{$p}-about-image"],
            'split-with-stats' => ["{$p}-about-grid", "{$p}-about-image-col", "{$p}-about-image",
                                   "{$p}-about-stats", "{$p}-about-stat", "{$p}-about-stat-value", "{$p}-about-stat-label"],
            'split-video' => ["{$p}-about-grid", "{$p}-about-video-col", "{$p}-about-video-wrapper",
                              "{$p}-about-video-thumb", "{$p}-about-play-btn"],
            'creative-timeline' => ["{$p}-about-header", "{$p}-about-timeline", "{$p}-about-timeline-line",
                                    "{$p}-about-timeline-item", "{$p}-about-timeline-dot",
                                    "{$p}-about-timeline-year", "{$p}-about-timeline-desc"],
            'creative-team-mission' => ["{$p}-about-header", "{$p}-about-mission-grid", "{$p}-about-mission-card",
                                        "{$p}-about-mission-icon", "{$p}-about-mission-label", "{$p}-about-mission-text",
                                        "{$p}-about-team-row", "{$p}-about-team-image-col", "{$p}-about-team-photo",
                                        "{$p}-about-team-text-col"],
            'creative-values-grid' => ["{$p}-about-header", "{$p}-about-values-grid", "{$p}-about-value-card",
                                       "{$p}-about-value-icon", "{$p}-about-value-title", "{$p}-about-value-text"],
            'minimal-centered' => ["{$p}-about-divider"],
            'minimal-two-column' => ["{$p}-about-two-col", "{$p}-about-col-left", "{$p}-about-col-right"],
            'minimal-with-signature' => ["{$p}-about-signature-layout", "{$p}-about-signature",
                                         "{$p}-about-signature-name", "{$p}-about-signature-role"],
            default => [],
        };

        return array_merge($base, $extra);
    }

    private static function buildStructuralCSS(string $cssType, string $p): string
    {
        $css = self::css_base($p);

        $typeCSS = match($cssType) {
            'split-image-right'      => self::css_split_image_right($p),
            'split-image-left'       => self::css_split_image_left($p),
            'split-with-stats'       => self::css_split_with_stats($p),
            'split-video'            => self::css_split_video($p),
            'creative-timeline'      => self::css_creative_timeline($p),
            'creative-team-mission'  => self::css_creative_team_mission($p),
            'creative-values-grid'   => self::css_creative_values_grid($p),
            'minimal-centered'       => self::css_minimal_centered($p),
            'minimal-two-column'     => self::css_minimal_two_column($p),
            'minimal-with-signature' => self::css_minimal_with_signature($p),
            default                  => self::css_split_image_right($p),
        };

        return $css . $typeCSS . self::css_responsive($p);
    }

    // --- Base (shared by all About patterns) ---
    private static function css_base(string $p): string
    {
        return <<<CSS

/* ═══ About Structural CSS (auto-generated — do not edit) ═══ */
.{$p}-about {
  position: relative; overflow: hidden;
  padding: clamp(60px, 10vh, 120px) 0;
}
.{$p}-about .container {
  position: relative; z-index: 2;
}
.{$p}-about-badge {
  display: inline-block;
  font-size: 0.8125rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.12em;
  padding: 6px 16px; border-radius: 100px;
  margin-bottom: 16px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.15);
  color: var(--primary, #3b82f6);
  border: 1px solid rgba(var(--primary-rgb, 42,125,225), 0.25);
}
.{$p}-about-title {
  font-family: var(--font-heading, inherit);
  font-size: clamp(1.75rem, 4vw, 3rem);
  font-weight: 700; line-height: 1.15;
  margin: 0 0 16px 0;
  color: var(--text, #1e293b);
}
.{$p}-about-subtitle {
  font-size: clamp(1rem, 1.5vw, 1.25rem);
  line-height: 1.6; margin: 0 0 20px 0;
  color: var(--text-muted, #64748b);
  font-weight: 500;
}
.{$p}-about-text {
  font-size: clamp(0.9375rem, 1.2vw, 1.0625rem);
  line-height: 1.8; margin: 0 0 28px 0;
  color: var(--text-muted, #64748b);
  max-width: 65ch;
}
.{$p}-about-actions {
  display: flex; flex-wrap: wrap; gap: 12px;
  align-items: center;
}
.{$p}-about-actions--centered {
  justify-content: center;
  margin-top: 40px;
}
.{$p}-about-header {
  text-align: center;
  margin-bottom: 40px;
}
.{$p}-about-header .{$p}-about-subtitle {
  max-width: 50ch; margin-left: auto; margin-right: auto;
}
.{$p}-about-text--centered {
  text-align: center;
  max-width: 70ch;
  margin-left: auto; margin-right: auto;
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

CSS;
    }

    // --- Split Image Right ---
    private static function css_split_image_right(string $p): string
    {
        return <<<CSS
.{$p}-about--split-image-right .{$p}-about-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 80px); align-items: center;
}
.{$p}-about--split-image-right .{$p}-about-image-col {
  position: relative;
}
.{$p}-about--split-image-right .{$p}-about-image {
  width: 100%; height: auto;
  border-radius: var(--radius, 12px);
  object-fit: cover;
  box-shadow: 0 20px 60px rgba(0,0,0,0.1);
}

CSS;
    }

    // --- Split Image Left ---
    private static function css_split_image_left(string $p): string
    {
        return <<<CSS
.{$p}-about--split-image-left .{$p}-about-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 80px); align-items: center;
}
.{$p}-about--split-image-left .{$p}-about-image-col {
  position: relative;
}
.{$p}-about--split-image-left .{$p}-about-image {
  width: 100%; height: auto;
  border-radius: var(--radius, 12px);
  object-fit: cover;
  box-shadow: 0 20px 60px rgba(0,0,0,0.1);
}

CSS;
    }

    // --- Split With Stats ---
    private static function css_split_with_stats(string $p): string
    {
        return <<<CSS
.{$p}-about--split-with-stats .{$p}-about-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 80px); align-items: center;
  margin-bottom: clamp(40px, 6vw, 60px);
}
.{$p}-about--split-with-stats .{$p}-about-image-col {
  position: relative;
}
.{$p}-about--split-with-stats .{$p}-about-image {
  width: 100%; height: auto;
  border-radius: var(--radius, 12px);
  object-fit: cover;
  box-shadow: 0 20px 60px rgba(0,0,0,0.1);
}
.{$p}-about-stats {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  padding-top: clamp(32px, 4vw, 48px);
  border-top: 1px solid rgba(var(--text-rgb, 30,41,59), 0.1);
}
.{$p}-about-stat {
  text-align: center;
}
.{$p}-about-stat-value {
  display: block;
  font-family: var(--font-heading, inherit);
  font-size: clamp(1.5rem, 3vw, 2.5rem);
  font-weight: 700; line-height: 1.2;
  color: var(--primary, #3b82f6);
  margin-bottom: 4px;
}
.{$p}-about-stat-label {
  display: block;
  font-size: 0.875rem;
  color: var(--text-muted, #64748b);
  font-weight: 500;
}

CSS;
    }

    // --- Split Video ---
    private static function css_split_video(string $p): string
    {
        return <<<CSS
.{$p}-about--split-video .{$p}-about-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 80px); align-items: center;
}
.{$p}-about-video-col {
  position: relative;
}
.{$p}-about-video-wrapper {
  position: relative;
  border-radius: var(--radius, 12px);
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.1);
}
.{$p}-about-video-thumb {
  width: 100%; height: auto; display: block;
  object-fit: cover; aspect-ratio: 16/9;
}
.{$p}-about-play-btn {
  position: absolute; top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  display: flex; align-items: center; justify-content: center;
  width: 80px; height: 56px;
  background: transparent;
  border-radius: 14px;
  transition: all 0.3s ease;
  z-index: 2;
}
.{$p}-about-play-btn:hover {
  transform: translate(-50%, -50%) scale(1.1);
}
.{$p}-about-play-btn svg {
  filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
}
.{$p}-about-video-wrapper::after {
  content: '';
  position: absolute; inset: 0;
  background: rgba(0,0,0,0.15);
  pointer-events: none;
  transition: background 0.3s ease;
}
.{$p}-about-video-wrapper:hover::after {
  background: rgba(0,0,0,0.05);
}

CSS;
    }

    // --- Creative Timeline ---
    private static function css_creative_timeline(string $p): string
    {
        return <<<CSS
.{$p}-about--creative-timeline {
  text-align: center;
}
.{$p}-about-timeline {
  position: relative;
  display: flex; justify-content: space-between; align-items: flex-start;
  max-width: 900px; margin: 40px auto 0;
  padding-top: 40px;
}
.{$p}-about-timeline-line {
  position: absolute; top: 46px; left: 5%; right: 5%;
  height: 2px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.2);
}
.{$p}-about-timeline-item {
  position: relative; z-index: 1;
  display: flex; flex-direction: column; align-items: center;
  flex: 1;
  padding: 0 8px;
}
.{$p}-about-timeline-dot {
  width: 14px; height: 14px;
  border-radius: 50%;
  background: var(--primary, #3b82f6);
  border: 3px solid var(--background, #fff);
  box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 42,125,225), 0.3);
  margin-bottom: 16px;
}
.{$p}-about-timeline-year {
  display: block;
  font-family: var(--font-heading, inherit);
  font-size: 1.25rem; font-weight: 700;
  color: var(--primary, #3b82f6);
  margin-bottom: 6px;
}
.{$p}-about-timeline-desc {
  display: block;
  font-size: 0.875rem; line-height: 1.5;
  color: var(--text-muted, #64748b);
  max-width: 160px;
}

CSS;
    }

    // --- Creative Team Mission ---
    private static function css_creative_team_mission(string $p): string
    {
        return <<<CSS
.{$p}-about-mission-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  margin-bottom: clamp(40px, 6vw, 60px);
}
.{$p}-about-mission-card {
  background: var(--surface, #f8fafc);
  border-radius: var(--radius, 12px);
  padding: clamp(24px, 3vw, 36px);
  text-align: center;
  border: 1px solid rgba(var(--text-rgb, 30,41,59), 0.08);
  transition: all 0.3s ease;
}
.{$p}-about-mission-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.08);
}
.{$p}-about-mission-icon {
  width: 56px; height: 56px;
  display: inline-flex; align-items: center; justify-content: center;
  border-radius: 50%;
  background: rgba(var(--primary-rgb, 42,125,225), 0.1);
  color: var(--primary, #3b82f6);
  font-size: 1.25rem;
  margin-bottom: 16px;
}
.{$p}-about-mission-label {
  font-family: var(--font-heading, inherit);
  font-size: 1.125rem; font-weight: 700;
  color: var(--text, #1e293b);
  margin: 0 0 10px 0;
}
.{$p}-about-mission-text {
  font-size: 0.9375rem; line-height: 1.7;
  color: var(--text-muted, #64748b);
  margin: 0;
}
.{$p}-about-team-row {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 60px); align-items: center;
}
.{$p}-about-team-photo {
  width: 100%; height: auto;
  border-radius: var(--radius, 12px);
  object-fit: cover;
  box-shadow: 0 20px 60px rgba(0,0,0,0.1);
}

CSS;
    }

    // --- Creative Values Grid ---
    private static function css_creative_values_grid(string $p): string
    {
        return <<<CSS
.{$p}-about-values-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  margin-top: 40px;
}
.{$p}-about-value-card {
  background: var(--surface, #f8fafc);
  border-radius: var(--radius, 12px);
  padding: clamp(24px, 3vw, 36px);
  text-align: center;
  border: 1px solid rgba(var(--text-rgb, 30,41,59), 0.08);
  transition: all 0.3s ease;
}
.{$p}-about-value-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.08);
}
.{$p}-about-value-icon {
  width: 56px; height: 56px;
  display: inline-flex; align-items: center; justify-content: center;
  border-radius: var(--radius, 12px);
  background: rgba(var(--primary-rgb, 42,125,225), 0.1);
  color: var(--primary, #3b82f6);
  font-size: 1.25rem;
  margin-bottom: 16px;
}
.{$p}-about-value-title {
  font-family: var(--font-heading, inherit);
  font-size: 1.0625rem; font-weight: 700;
  color: var(--text, #1e293b);
  margin: 0 0 8px 0;
}
.{$p}-about-value-text {
  font-size: 0.875rem; line-height: 1.7;
  color: var(--text-muted, #64748b);
  margin: 0;
}

CSS;
    }

    // --- Minimal Centered ---
    private static function css_minimal_centered(string $p): string
    {
        return <<<CSS
.{$p}-about--minimal-centered {
  text-align: center;
}
.{$p}-about-content--centered {
  max-width: 700px; margin: 0 auto;
}
.{$p}-about-content--centered .{$p}-about-subtitle {
  margin-left: auto; margin-right: auto;
}
.{$p}-about-content--centered .{$p}-about-text {
  max-width: 60ch;
  margin-left: auto; margin-right: auto;
}
.{$p}-about-content--centered .{$p}-about-actions {
  justify-content: center;
}
.{$p}-about-divider {
  width: 60px; height: 2px;
  background: var(--primary, #3b82f6);
  margin: 24px auto;
  opacity: 0.6;
}

CSS;
    }

    // --- Minimal Two Column ---
    private static function css_minimal_two_column(string $p): string
    {
        return <<<CSS
.{$p}-about-two-col {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 80px); align-items: start;
}
.{$p}-about-col-left .{$p}-about-title {
  font-size: clamp(1.75rem, 4vw, 2.75rem);
}
.{$p}-about-col-right {
  padding-top: 8px;
}
.{$p}-about-col-right .{$p}-about-text {
  max-width: none;
  margin-bottom: 0;
}

CSS;
    }

    // --- Minimal With Signature ---
    private static function css_minimal_with_signature(string $p): string
    {
        return <<<CSS
.{$p}-about-signature-layout {
  max-width: 700px;
}
.{$p}-about-signature-layout .{$p}-about-content {
  max-width: none;
}
.{$p}-about-signature {
  display: flex; flex-direction: column;
  margin-bottom: 28px; padding-top: 8px;
}
.{$p}-about-signature-name {
  font-family: 'Georgia', 'Times New Roman', serif;
  font-size: clamp(1.5rem, 2.5vw, 2rem);
  font-style: italic;
  color: var(--text, #1e293b);
  line-height: 1.3;
  margin-bottom: 4px;
}
.{$p}-about-signature-role {
  font-size: 0.875rem;
  color: var(--text-muted, #64748b);
  font-weight: 500;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

CSS;
    }

    // --- Responsive ---
    private static function css_responsive(string $p): string
    {
        return <<<CSS
@media (max-width: 768px) {
  .{$p}-about-grid {
    grid-template-columns: 1fr !important;
    gap: 32px !important;
  }
  .{$p}-about--split-image-left .{$p}-about-grid .{$p}-about-image-col {
    order: -1;
  }
  .{$p}-about-stats {
    grid-template-columns: 1fr !important;
    gap: 20px !important;
  }
  .{$p}-about-mission-grid {
    grid-template-columns: 1fr !important;
  }
  .{$p}-about-values-grid {
    grid-template-columns: 1fr !important;
  }
  .{$p}-about-team-row {
    grid-template-columns: 1fr !important;
    gap: 32px !important;
  }
  .{$p}-about-two-col {
    grid-template-columns: 1fr !important;
    gap: 24px !important;
  }
  .{$p}-about-timeline {
    flex-direction: column;
    align-items: flex-start;
    padding-left: 30px;
    gap: 24px;
  }
  .{$p}-about-timeline-line {
    top: 0; bottom: 0; left: 16px; right: auto;
    width: 2px; height: auto;
  }
  .{$p}-about-timeline-item {
    flex-direction: row; align-items: center; gap: 16px;
    padding: 0;
  }
  .{$p}-about-timeline-dot {
    margin-bottom: 0; flex-shrink: 0;
    position: absolute; left: -23px;
  }
  .{$p}-about-timeline-item {
    flex-direction: column; align-items: flex-start;
  }
  .{$p}-about-timeline-year {
    margin-bottom: 2px;
  }
  .{$p}-about-timeline-desc {
    max-width: none;
  }
}

CSS;
    }
}
