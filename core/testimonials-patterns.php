<?php
/**
 * Testimonials Section Pattern Registry
 *
 * Pre-built testimonial HTML layouts with structural CSS.
 * AI generates only decorative CSS (colors, fonts, effects) — never the structure.
 *
 * 12 patterns across 4 groups.
 * @since 2026-02-19
 */

class TestimonialsPatternRegistry
{
    // ═══════════════════════════════════════
    // PATTERN DEFINITIONS
    // ═══════════════════════════════════════

    private static array $patterns = [
        // --- Cards (grid/masonry/featured/minimal) ---
        ['id'=>'cards-grid',            'group'=>'cards',    'css_type'=>'cards-grid',
         'best_for'=>['consulting','accounting','legal','financial','bank','insurance',
                      'healthcare','clinic','dental','pharmacy']],
        ['id'=>'cards-masonry',         'group'=>'cards',    'css_type'=>'cards-masonry',
         'best_for'=>['agency','marketing','branding','seo','web-design','social-media',
                      'creative-agency','design']],
        ['id'=>'cards-single-featured', 'group'=>'cards',    'css_type'=>'cards-single-featured',
         'best_for'=>['coaching','education','university','school','nonprofit','charity',
                      'library']],
        ['id'=>'cards-minimal',         'group'=>'cards',    'css_type'=>'cards-minimal',
         'best_for'=>['saas','tech','startup','app','fintech','ai','platform','digital']],

        // --- Slider (centered/split/cards-row) ---
        ['id'=>'slider-centered',       'group'=>'slider',   'css_type'=>'slider-centered',
         'best_for'=>['restaurant','bakery','cafe','bar','fine-dining','catering',
                      'florist','winery','brewery']],
        ['id'=>'slider-split',          'group'=>'slider',   'css_type'=>'slider-split',
         'best_for'=>['hotel','resort','spa','travel','tourism','adventure','fitness',
                      'sports','outdoor']],
        ['id'=>'slider-cards-row',      'group'=>'slider',   'css_type'=>'slider-cards-row',
         'best_for'=>['ecommerce','marketplace','retail','fashion','beauty','salon']],

        // --- Creative (quote-wall/video/social-proof) ---
        ['id'=>'creative-quote-wall',        'group'=>'creative',  'css_type'=>'creative-quote-wall',
         'best_for'=>['entertainment','music','film','gaming','nightclub','festival',
                      'concert','podcast']],
        ['id'=>'creative-video-testimonial',  'group'=>'creative',  'css_type'=>'creative-video-testimonial',
         'best_for'=>['youtube','influencer','content-creator','media','blog','magazine',
                      'news']],
        ['id'=>'creative-social-proof',       'group'=>'creative',  'css_type'=>'creative-social-proof',
         'best_for'=>['blockchain','real-estate','construction','manufacturing','logistics',
                      'engineering']],

        // --- Minimal (list/centered) ---
        ['id'=>'minimal-list',          'group'=>'minimal',  'css_type'=>'minimal-list',
         'best_for'=>['architecture','interior-design','gallery','museum','photography',
                      'art','luxury']],
        ['id'=>'minimal-centered',      'group'=>'minimal',  'css_type'=>'minimal-centered',
         'best_for'=>['wedding','event-planning','country-club','paving','roofing',
                      'plumbing','electrical','hvac','landscaping']],
    ];

    // ═══════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════

    /**
     * Pick the best testimonials pattern for an industry.
     */
    public static function pickPattern(string $industry): string
    {
        $industry = strtolower(trim($industry));
        foreach (self::$patterns as $p) {
            if (in_array($industry, $p['best_for'], true)) {
                return $p['id'];
            }
        }
        // Fallback: random from cards group (most versatile)
        $cardsPatterns = array_filter(self::$patterns, fn($p) => $p['group'] === 'cards');
        $cardsIds = array_column(array_values($cardsPatterns), 'id');
        return $cardsIds[array_rand($cardsIds)];
    }

    /**
     * Render a testimonials pattern.
     * Returns ['html'=>..., 'structural_css'=>..., 'pattern_id'=>..., 'classes'=>[...], 'fields'=>[...]]
     */
    public static function render(string $patternId, string $prefix, array $brief): array
    {
        $def = null;
        foreach (self::$patterns as $p) {
            if ($p['id'] === $patternId) { $def = $p; break; }
        }
        if (!$def) {
            $def = self::$patterns[0]; // fallback to cards-grid
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
        // Common fields all testimonial sections have
        $common = [
            'title'    => ['type' => 'text',     'label' => 'Section Title'],
            'subtitle' => ['type' => 'textarea', 'label' => 'Section Subtitle'],
        ];

        // Per-item fields — count depends on pattern
        $itemCount = match($patternId) {
            'cards-grid', 'cards-masonry', 'cards-minimal', 'creative-quote-wall',
            'creative-social-proof', 'slider-cards-row' => 6,
            'cards-single-featured' => 3,
            'slider-centered', 'slider-split', 'minimal-list' => 4,
            'creative-video-testimonial' => 4,
            'minimal-centered' => 3,
            default => 6,
        };

        for ($i = 1; $i <= $itemCount; $i++) {
            $common["item{$i}_quote"]  = ['type' => 'textarea', 'label' => "Testimonial {$i} Quote"];
            $common["item{$i}_name"]   = ['type' => 'text',     'label' => "Testimonial {$i} Name"];
            $common["item{$i}_role"]   = ['type' => 'text',     'label' => "Testimonial {$i} Role"];
            $common["item{$i}_avatar"] = ['type' => 'image',    'label' => "Testimonial {$i} Avatar"];
            $common["item{$i}_rating"] = ['type' => 'text',     'label' => "Testimonial {$i} Rating (1-5)"];
        }

        // Pattern-specific extras
        $extras = match($patternId) {
            'creative-video-testimonial' => [
                'item1_video' => ['type' => 'text', 'label' => 'Testimonial 1 Video URL'],
                'item2_video' => ['type' => 'text', 'label' => 'Testimonial 2 Video URL'],
                'item3_video' => ['type' => 'text', 'label' => 'Testimonial 3 Video URL'],
                'item4_video' => ['type' => 'text', 'label' => 'Testimonial 4 Video URL'],
            ],
            'creative-social-proof' => [
                'item1_platform' => ['type' => 'text', 'label' => 'Testimonial 1 Platform (twitter/linkedin)'],
                'item2_platform' => ['type' => 'text', 'label' => 'Testimonial 2 Platform'],
                'item3_platform' => ['type' => 'text', 'label' => 'Testimonial 3 Platform'],
                'item4_platform' => ['type' => 'text', 'label' => 'Testimonial 4 Platform'],
                'item5_platform' => ['type' => 'text', 'label' => 'Testimonial 5 Platform'],
                'item6_platform' => ['type' => 'text', 'label' => 'Testimonial 6 Platform'],
            ],
            'slider-split' => [
                'item1_image' => ['type' => 'image', 'label' => 'Testimonial 1 Large Image'],
                'item2_image' => ['type' => 'image', 'label' => 'Testimonial 2 Large Image'],
                'item3_image' => ['type' => 'image', 'label' => 'Testimonial 3 Large Image'],
                'item4_image' => ['type' => 'image', 'label' => 'Testimonial 4 Large Image'],
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
            'cards-grid' => <<<'GUIDE'
Decorative quotation marks use large serif font, primary color at 15% opacity.
Star ratings colored amber/gold (#f59e0b) with subtle text-shadow glow.
Avatar images get circular border 2-3px solid primary with a faint ring shadow.
Cards have soft background tint, generous border-radius, and light border color.
Hover: lift shadow intensifies, border-color transitions to primary accent.
GUIDE,
            'cards-masonry' => <<<'GUIDE'
Cards use alternating subtle background tints (white vs slight off-white).
Quote text varies font-size slightly per card to reinforce organic masonry feel.
Soft warm-toned drop shadows, generous border-radius (16-20px).
Hover: shadow deepens with warm hue, faint background brightness increase.
Star ratings use amber with gentle glow, quote marks italic serif accent.
GUIDE,
            'cards-single-featured' => <<<'GUIDE'
Featured card has bold left border (4-5px solid primary) as accent stripe.
Featured quote uses larger italic serif font-family for emphasis.
Photo/avatar on featured card has stronger shadow ring and larger size.
Secondary cards are visually subdued — lighter shadows, thinner borders.
Giant decorative quotation mark in background at very low opacity (0.08).
GUIDE,
            'cards-minimal' => <<<'GUIDE'
No card backgrounds — transparent with thin 1px border only.
Thin bottom-border separators between author info and quote.
Typography-focused: clean sans-serif, generous letter-spacing on names.
Hover: border-color shifts to primary, no shadow increase, no transform.
Stars and quotes use muted colors, understated and restrained palette.
GUIDE,
            'slider-centered' => <<<'GUIDE'
Large centered quotation mark uses elegant serif font, primary color at low opacity.
Navigation dots are small circles: inactive muted/transparent, active primary filled.
Avatar gets decorative circular border with subtle primary-tinted shadow.
Quote text uses larger italic font with generous line-height for elegance.
Fade transition between slides — opacity animation smooth 0.5s ease.
GUIDE,
            'slider-split' => <<<'GUIDE'
Giant decorative quotation marks positioned as background accent, primary at 10% opacity.
Image side has subtle border-radius on corners and soft shadow frame.
Quote text uses serif or elegant italic font for contrast with clean body text.
Navigation arrows use primary bg with white icon, rounded circle shape.
Slide transition is horizontal slide with smooth ease-in-out timing.
GUIDE,
            'slider-cards-row' => <<<'GUIDE'
Center card emphasized with slight scale(1.05) and deeper shadow than siblings.
Side cards have reduced opacity (0.7) to create depth/focus on center.
Cards use consistent border-radius with soft gradient shadow on hover.
Star ratings amber, avatar borders use primary-tinted ring.
Scroll row has subtle fade-out gradient on left/right edges.
GUIDE,
            'creative-quote-wall' => <<<'GUIDE'
Background quote marks are massive (200-400px), scattered at varied positions, very low opacity (0.03-0.08).
Individual quote cards use varied font-sizes to create visual hierarchy and wall effect.
Cards have mixed opacity levels (0.85-1.0) for floating depth illusion.
No uniform shadows — some cards darker, some lighter for organic scattered feel.
Color accents vary per card: alternate between primary tints and neutral tones.
GUIDE,
            'creative-video-testimonial' => <<<'GUIDE'
Video thumbnails have rounded corners with 3-4px solid border in muted color.
Play button overlay is centered circle with primary bg, white triangle icon, box-shadow glow.
Hover on thumbnail: border-color transitions to primary, play button scales up slightly.
Video card background uses subtle dark gradient overlay on thumbnail for text readability.
Quote text below video uses clean sans-serif, smaller font-size, muted color.
GUIDE,
            'creative-social-proof' => <<<'GUIDE'
Cards mimic social media post styling — subtle platform-colored top border (Twitter blue, LinkedIn blue).
Platform icon in header uses brand color (Twitter #1da1f2, LinkedIn #0077b5).
Verified badge style: small checkmark circle in platform brand color next to name.
Card backgrounds clean white/surface with very subtle shadow, rounded corners.
Hover: card lifts slightly, shadow increases, border-top color intensifies.
GUIDE,
            'minimal-list' => <<<'GUIDE'
Thin divider lines (1px) between items, muted border color.
Left-aligned text, no cards, no shadows, no background treatments.
Quote uses subtle italic serif font or slightly larger weight for distinction.
Name and role separated by em-dash, compact single line, muted role color.
Overall feel: gallery-like restraint, generous whitespace, typography-driven.
GUIDE,
            'minimal-centered' => <<<'GUIDE'
Centered elegant typography: quote in large italic serif font with generous line-height.
Decorative quotation mark above quote, primary color, medium opacity, serif font.
Navigation dots minimal: tiny circles, active dot uses primary fill.
Name in small-caps or letter-spaced uppercase for refined feel.
Subtle fade transition between testimonials, no harsh cuts.
GUIDE,
            default => <<<'GUIDE'
Cards use soft shadows, rounded corners, and subtle border.
Star ratings amber/gold, avatars circular with border.
Hover effects: gentle lift and shadow increase.
Typography clean and readable with proper hierarchy.
GUIDE,
        };
    }

    // ═══════════════════════════════════════
    // HTML TEMPLATES
    // ═══════════════════════════════════════

    /**
     * Replace generic placeholder defaults with actual brief content.
     */
    private static function injectBriefContent(string $html, array $brief): string
    {
        $name = $brief['name'] ?? '';
        $industry = $brief['industry'] ?? '';

        $replacements = [];
        if ($name) {
            $replacements["theme_get('testimonials.title', 'What Our Clients Say')"] =
                "theme_get('testimonials.title', 'What Our Clients Say About " . addslashes($name) . "')";
        }

        foreach ($replacements as $search => $replace) {
            $html = str_replace($search, $replace, $html);
        }

        return $html;
    }

    private static function buildHTML(string $patternId, string $p): string
    {
        $templates = self::getTemplates($p);
        return $templates[$patternId] ?? $templates['cards-grid'];
    }

    private static function getTemplates(string $p): array
    {
        return [

// ── Cards Grid: 3-column grid of testimonial cards ──
'cards-grid' => <<<HTML
<?php
\$tTitle = theme_get('testimonials.title', 'What Our Clients Say');
\$tSubtitle = theme_get('testimonials.subtitle', 'Trusted by businesses worldwide');
\$items = [];
for (\$i = 1; \$i <= 6; \$i++) {
    \$q = theme_get("testimonials.item{\$i}_quote", '');
    if (\$q || \$i <= 3) {
        \$items[] = [
            'quote'  => \$q ?: 'This service exceeded all our expectations. Highly recommended to anyone looking for quality and reliability.',
            'name'   => theme_get("testimonials.item{\$i}_name", 'Client ' . \$i),
            'role'   => theme_get("testimonials.item{\$i}_role", 'CEO, Company'),
            'avatar' => theme_get("testimonials.item{\$i}_avatar", ''),
            'rating' => (int) theme_get("testimonials.item{\$i}_rating", '5'),
        ];
    }
}
?>
<section class="{$p}-testimonials {$p}-testimonials--cards-grid" id="testimonials">
  <div class="container">
    <div class="{$p}-testimonials-header" data-animate="fade-up">
      <h2 class="{$p}-testimonials-title" data-ts="testimonials.title"><?= esc(\$tTitle) ?></h2>
      <p class="{$p}-testimonials-subtitle" data-ts="testimonials.subtitle"><?= esc(\$tSubtitle) ?></p>
    </div>
    <div class="{$p}-testimonials-grid">
      <?php foreach (\$items as \$idx => \$item): ?>
      <div class="{$p}-testimonial-card" data-animate="fade-up">
        <div class="{$p}-testimonial-stars">
          <?php for (\$s = 0; \$s < \$item['rating']; \$s++): ?><i class="fas fa-star"></i><?php endfor; ?>
        </div>
        <blockquote class="{$p}-testimonial-quote">
          <span class="{$p}-testimonial-quote-mark">&ldquo;</span>
          <p data-ts="testimonials.item<?= \$idx+1 ?>_quote"><?= esc(\$item['quote']) ?></p>
        </blockquote>
        <div class="{$p}-testimonial-author">
          <?php if (\$item['avatar']): ?>
          <img loading="lazy" src="<?= esc(\$item['avatar']) ?>" alt="<?= esc(\$item['name']) ?>" class="{$p}-testimonial-avatar" data-ts-bg="testimonials.item<?= \$idx+1 ?>_avatar">
          <?php else: ?>
          <div class="{$p}-testimonial-avatar-placeholder"><i class="fas fa-user"></i></div>
          <?php endif; ?>
          <div class="{$p}-testimonial-author-info">
            <strong data-ts="testimonials.item<?= \$idx+1 ?>_name"><?= esc(\$item['name']) ?></strong>
            <span data-ts="testimonials.item<?= \$idx+1 ?>_role"><?= esc(\$item['role']) ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Cards Masonry: Masonry layout, mixed height cards ──
'cards-masonry' => <<<HTML
<?php
\$tTitle = theme_get('testimonials.title', 'What Our Clients Say');
\$tSubtitle = theme_get('testimonials.subtitle', 'Real stories from real clients');
\$items = [];
for (\$i = 1; \$i <= 6; \$i++) {
    \$q = theme_get("testimonials.item{\$i}_quote", '');
    if (\$q || \$i <= 3) {
        \$items[] = [
            'quote'  => \$q ?: 'Working with this team has been an incredible experience. The attention to detail and creative solutions they provide are unmatched in the industry.',
            'name'   => theme_get("testimonials.item{\$i}_name", 'Client ' . \$i),
            'role'   => theme_get("testimonials.item{\$i}_role", 'Marketing Director'),
            'avatar' => theme_get("testimonials.item{\$i}_avatar", ''),
            'rating' => (int) theme_get("testimonials.item{\$i}_rating", '5'),
        ];
    }
}
?>
<section class="{$p}-testimonials {$p}-testimonials--cards-masonry" id="testimonials">
  <div class="container">
    <div class="{$p}-testimonials-header" data-animate="fade-up">
      <h2 class="{$p}-testimonials-title" data-ts="testimonials.title"><?= esc(\$tTitle) ?></h2>
      <p class="{$p}-testimonials-subtitle" data-ts="testimonials.subtitle"><?= esc(\$tSubtitle) ?></p>
    </div>
    <div class="{$p}-testimonials-masonry">
      <?php foreach (\$items as \$idx => \$item): ?>
      <div class="{$p}-testimonial-card" data-animate="fade-up">
        <div class="{$p}-testimonial-stars">
          <?php for (\$s = 0; \$s < \$item['rating']; \$s++): ?><i class="fas fa-star"></i><?php endfor; ?>
        </div>
        <blockquote class="{$p}-testimonial-quote">
          <p data-ts="testimonials.item<?= \$idx+1 ?>_quote"><?= esc(\$item['quote']) ?></p>
        </blockquote>
        <div class="{$p}-testimonial-author">
          <?php if (\$item['avatar']): ?>
          <img loading="lazy" src="<?= esc(\$item['avatar']) ?>" alt="<?= esc(\$item['name']) ?>" class="{$p}-testimonial-avatar" data-ts-bg="testimonials.item<?= \$idx+1 ?>_avatar">
          <?php else: ?>
          <div class="{$p}-testimonial-avatar-placeholder"><i class="fas fa-user"></i></div>
          <?php endif; ?>
          <div class="{$p}-testimonial-author-info">
            <strong data-ts="testimonials.item<?= \$idx+1 ?>_name"><?= esc(\$item['name']) ?></strong>
            <span data-ts="testimonials.item<?= \$idx+1 ?>_role"><?= esc(\$item['role']) ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Cards Single Featured: One large + 2 smaller ──
'cards-single-featured' => <<<HTML
<?php
\$tTitle = theme_get('testimonials.title', 'What Our Clients Say');
\$tSubtitle = theme_get('testimonials.subtitle', 'Hear from those who matter most');
\$items = [];
for (\$i = 1; \$i <= 3; \$i++) {
    \$q = theme_get("testimonials.item{\$i}_quote", '');
    \$items[] = [
        'quote'  => \$q ?: 'An absolutely transformative experience. The results speak for themselves and our team could not be happier with the outcome.',
        'name'   => theme_get("testimonials.item{\$i}_name", 'Client ' . \$i),
        'role'   => theme_get("testimonials.item{\$i}_role", 'Director, Organization'),
        'avatar' => theme_get("testimonials.item{\$i}_avatar", ''),
        'rating' => (int) theme_get("testimonials.item{\$i}_rating", '5'),
    ];
}
?>
<section class="{$p}-testimonials {$p}-testimonials--single-featured" id="testimonials">
  <div class="container">
    <div class="{$p}-testimonials-header" data-animate="fade-up">
      <h2 class="{$p}-testimonials-title" data-ts="testimonials.title"><?= esc(\$tTitle) ?></h2>
      <p class="{$p}-testimonials-subtitle" data-ts="testimonials.subtitle"><?= esc(\$tSubtitle) ?></p>
    </div>
    <div class="{$p}-testimonials-featured-layout">
      <div class="{$p}-testimonial-card {$p}-testimonial-card--featured" data-animate="fade-up">
        <span class="{$p}-testimonial-quote-mark">&ldquo;</span>
        <div class="{$p}-testimonial-stars">
          <?php for (\$s = 0; \$s < \$items[0]['rating']; \$s++): ?><i class="fas fa-star"></i><?php endfor; ?>
        </div>
        <blockquote class="{$p}-testimonial-quote">
          <p data-ts="testimonials.item1_quote"><?= esc(\$items[0]['quote']) ?></p>
        </blockquote>
        <div class="{$p}-testimonial-author">
          <?php if (\$items[0]['avatar']): ?>
          <img loading="lazy" src="<?= esc(\$items[0]['avatar']) ?>" alt="<?= esc(\$items[0]['name']) ?>" class="{$p}-testimonial-avatar" data-ts-bg="testimonials.item1_avatar">
          <?php else: ?>
          <div class="{$p}-testimonial-avatar-placeholder"><i class="fas fa-user"></i></div>
          <?php endif; ?>
          <div class="{$p}-testimonial-author-info">
            <strong data-ts="testimonials.item1_name"><?= esc(\$items[0]['name']) ?></strong>
            <span data-ts="testimonials.item1_role"><?= esc(\$items[0]['role']) ?></span>
          </div>
        </div>
      </div>
      <div class="{$p}-testimonials-secondary">
        <?php for (\$i = 1; \$i < count(\$items); \$i++): ?>
        <div class="{$p}-testimonial-card" data-animate="fade-up">
          <div class="{$p}-testimonial-stars">
            <?php for (\$s = 0; \$s < \$items[\$i]['rating']; \$s++): ?><i class="fas fa-star"></i><?php endfor; ?>
          </div>
          <blockquote class="{$p}-testimonial-quote">
            <p data-ts="testimonials.item<?= \$i+1 ?>_quote"><?= esc(\$items[\$i]['quote']) ?></p>
          </blockquote>
          <div class="{$p}-testimonial-author">
            <?php if (\$items[\$i]['avatar']): ?>
            <img loading="lazy" src="<?= esc(\$items[\$i]['avatar']) ?>" alt="<?= esc(\$items[\$i]['name']) ?>" class="{$p}-testimonial-avatar" data-ts-bg="testimonials.item<?= \$i+1 ?>_avatar">
            <?php else: ?>
            <div class="{$p}-testimonial-avatar-placeholder"><i class="fas fa-user"></i></div>
            <?php endif; ?>
            <div class="{$p}-testimonial-author-info">
              <strong data-ts="testimonials.item<?= \$i+1 ?>_name"><?= esc(\$items[\$i]['name']) ?></strong>
              <span data-ts="testimonials.item<?= \$i+1 ?>_role"><?= esc(\$items[\$i]['role']) ?></span>
            </div>
          </div>
        </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Cards Minimal: Clean cards, no avatar, just quote + name + company ──
'cards-minimal' => <<<HTML
<?php
\$tTitle = theme_get('testimonials.title', 'What Our Clients Say');
\$tSubtitle = theme_get('testimonials.subtitle', 'Trusted by industry leaders');
\$items = [];
for (\$i = 1; \$i <= 6; \$i++) {
    \$q = theme_get("testimonials.item{\$i}_quote", '');
    if (\$q || \$i <= 3) {
        \$items[] = [
            'quote'  => \$q ?: 'A game-changing solution that streamlined our entire workflow. The ROI has been exceptional.',
            'name'   => theme_get("testimonials.item{\$i}_name", 'Client ' . \$i),
            'role'   => theme_get("testimonials.item{\$i}_role", 'CTO, Tech Corp'),
            'rating' => (int) theme_get("testimonials.item{\$i}_rating", '5'),
        ];
    }
}
?>
<section class="{$p}-testimonials {$p}-testimonials--cards-minimal" id="testimonials">
  <div class="container">
    <div class="{$p}-testimonials-header" data-animate="fade-up">
      <h2 class="{$p}-testimonials-title" data-ts="testimonials.title"><?= esc(\$tTitle) ?></h2>
      <p class="{$p}-testimonials-subtitle" data-ts="testimonials.subtitle"><?= esc(\$tSubtitle) ?></p>
    </div>
    <div class="{$p}-testimonials-grid">
      <?php foreach (\$items as \$idx => \$item): ?>
      <div class="{$p}-testimonial-card" data-animate="fade-up">
        <div class="{$p}-testimonial-stars">
          <?php for (\$s = 0; \$s < \$item['rating']; \$s++): ?><i class="fas fa-star"></i><?php endfor; ?>
        </div>
        <blockquote class="{$p}-testimonial-quote">
          <p data-ts="testimonials.item<?= \$idx+1 ?>_quote"><?= esc(\$item['quote']) ?></p>
        </blockquote>
        <div class="{$p}-testimonial-author-minimal">
          <strong data-ts="testimonials.item<?= \$idx+1 ?>_name"><?= esc(\$item['name']) ?></strong>
          <span data-ts="testimonials.item<?= \$idx+1 ?>_role"><?= esc(\$item['role']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Slider Centered: Single centered testimonial, large quote, dots ──
'slider-centered' => <<<HTML
<?php
\$tTitle = theme_get('testimonials.title', 'What Our Clients Say');
\$tSubtitle = theme_get('testimonials.subtitle', 'Words from our valued guests');
\$items = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$q = theme_get("testimonials.item{\$i}_quote", '');
    if (\$q || \$i <= 3) {
        \$items[] = [
            'quote'  => \$q ?: 'An unforgettable experience from start to finish. Every detail was perfect and the team went above and beyond our expectations.',
            'name'   => theme_get("testimonials.item{\$i}_name", 'Client ' . \$i),
            'role'   => theme_get("testimonials.item{\$i}_role", 'Valued Customer'),
            'avatar' => theme_get("testimonials.item{\$i}_avatar", ''),
            'rating' => (int) theme_get("testimonials.item{\$i}_rating", '5'),
        ];
    }
}
?>
<section class="{$p}-testimonials {$p}-testimonials--slider-centered" id="testimonials">
  <div class="container">
    <div class="{$p}-testimonials-header" data-animate="fade-up">
      <h2 class="{$p}-testimonials-title" data-ts="testimonials.title"><?= esc(\$tTitle) ?></h2>
      <p class="{$p}-testimonials-subtitle" data-ts="testimonials.subtitle"><?= esc(\$tSubtitle) ?></p>
    </div>
    <div class="{$p}-testimonials-slider" data-animate="fade-up">
      <?php foreach (\$items as \$idx => \$item): ?>
      <div class="{$p}-testimonial-slide<?= \$idx === 0 ? ' active' : '' ?>">
        <span class="{$p}-testimonial-quote-mark">&ldquo;</span>
        <blockquote class="{$p}-testimonial-quote">
          <p data-ts="testimonials.item<?= \$idx+1 ?>_quote"><?= esc(\$item['quote']) ?></p>
        </blockquote>
        <div class="{$p}-testimonial-stars">
          <?php for (\$s = 0; \$s < \$item['rating']; \$s++): ?><i class="fas fa-star"></i><?php endfor; ?>
        </div>
        <div class="{$p}-testimonial-author">
          <?php if (\$item['avatar']): ?>
          <img loading="lazy" src="<?= esc(\$item['avatar']) ?>" alt="<?= esc(\$item['name']) ?>" class="{$p}-testimonial-avatar" data-ts-bg="testimonials.item<?= \$idx+1 ?>_avatar">
          <?php else: ?>
          <div class="{$p}-testimonial-avatar-placeholder"><i class="fas fa-user"></i></div>
          <?php endif; ?>
          <div class="{$p}-testimonial-author-info">
            <strong data-ts="testimonials.item<?= \$idx+1 ?>_name"><?= esc(\$item['name']) ?></strong>
            <span data-ts="testimonials.item<?= \$idx+1 ?>_role"><?= esc(\$item['role']) ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <div class="{$p}-testimonial-dots">
        <?php foreach (\$items as \$idx => \$item): ?>
        <button class="{$p}-testimonial-dot<?= \$idx === 0 ? ' active' : '' ?>" aria-label="Slide <?= \$idx+1 ?>"></button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Slider Split: Large image left, quote right, arrows ──
'slider-split' => <<<HTML
<?php
\$tTitle = theme_get('testimonials.title', 'What Our Clients Say');
\$tSubtitle = theme_get('testimonials.subtitle', 'Stories from unforgettable journeys');
\$items = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$q = theme_get("testimonials.item{\$i}_quote", '');
    if (\$q || \$i <= 3) {
        \$items[] = [
            'quote'  => \$q ?: 'A truly remarkable experience. The attention to detail, the warm hospitality, and the stunning surroundings made our stay absolutely perfect.',
            'name'   => theme_get("testimonials.item{\$i}_name", 'Client ' . \$i),
            'role'   => theme_get("testimonials.item{\$i}_role", 'Guest'),
            'avatar' => theme_get("testimonials.item{\$i}_avatar", ''),
            'image'  => theme_get("testimonials.item{\$i}_image", ''),
            'rating' => (int) theme_get("testimonials.item{\$i}_rating", '5'),
        ];
    }
}
?>
<section class="{$p}-testimonials {$p}-testimonials--slider-split" id="testimonials">
  <div class="container">
    <div class="{$p}-testimonials-header" data-animate="fade-up">
      <h2 class="{$p}-testimonials-title" data-ts="testimonials.title"><?= esc(\$tTitle) ?></h2>
      <p class="{$p}-testimonials-subtitle" data-ts="testimonials.subtitle"><?= esc(\$tSubtitle) ?></p>
    </div>
    <div class="{$p}-testimonials-split-slider" data-animate="fade-up">
      <?php foreach (\$items as \$idx => \$item): ?>
      <div class="{$p}-testimonial-slide-split<?= \$idx === 0 ? ' active' : '' ?>">
        <div class="{$p}-testimonial-slide-image">
          <?php if (\$item['image']): ?>
          <img loading="lazy" src="<?= esc(\$item['image']) ?>" alt="<?= esc(\$item['name']) ?>" data-ts-bg="testimonials.item<?= \$idx+1 ?>_image">
          <?php else: ?>
          <div class="{$p}-testimonial-image-placeholder"><i class="fas fa-quote-right"></i></div>
          <?php endif; ?>
        </div>
        <div class="{$p}-testimonial-slide-content">
          <span class="{$p}-testimonial-quote-mark">&ldquo;</span>
          <blockquote class="{$p}-testimonial-quote">
            <p data-ts="testimonials.item<?= \$idx+1 ?>_quote"><?= esc(\$item['quote']) ?></p>
          </blockquote>
          <div class="{$p}-testimonial-stars">
            <?php for (\$s = 0; \$s < \$item['rating']; \$s++): ?><i class="fas fa-star"></i><?php endfor; ?>
          </div>
          <div class="{$p}-testimonial-author">
            <?php if (\$item['avatar']): ?>
            <img loading="lazy" src="<?= esc(\$item['avatar']) ?>" alt="<?= esc(\$item['name']) ?>" class="{$p}-testimonial-avatar" data-ts-bg="testimonials.item<?= \$idx+1 ?>_avatar">
            <?php else: ?>
            <div class="{$p}-testimonial-avatar-placeholder"><i class="fas fa-user"></i></div>
            <?php endif; ?>
            <div class="{$p}-testimonial-author-info">
              <strong data-ts="testimonials.item<?= \$idx+1 ?>_name"><?= esc(\$item['name']) ?></strong>
              <span data-ts="testimonials.item<?= \$idx+1 ?>_role"><?= esc(\$item['role']) ?></span>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <div class="{$p}-testimonial-arrows">
        <button class="{$p}-testimonial-arrow {$p}-testimonial-arrow--prev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
        <button class="{$p}-testimonial-arrow {$p}-testimonial-arrow--next" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Slider Cards Row: 3 visible cards, CSS horizontal scroll ──
'slider-cards-row' => <<<HTML
<?php
\$tTitle = theme_get('testimonials.title', 'What Our Clients Say');
\$tSubtitle = theme_get('testimonials.subtitle', 'See what our customers love about us');
\$items = [];
for (\$i = 1; \$i <= 6; \$i++) {
    \$q = theme_get("testimonials.item{\$i}_quote", '');
    if (\$q || \$i <= 3) {
        \$items[] = [
            'quote'  => \$q ?: 'Amazing quality and fast delivery. This is now our go-to for everything. Five stars!',
            'name'   => theme_get("testimonials.item{\$i}_name", 'Client ' . \$i),
            'role'   => theme_get("testimonials.item{\$i}_role", 'Happy Customer'),
            'avatar' => theme_get("testimonials.item{\$i}_avatar", ''),
            'rating' => (int) theme_get("testimonials.item{\$i}_rating", '5'),
        ];
    }
}
?>
<section class="{$p}-testimonials {$p}-testimonials--slider-cards-row" id="testimonials">
  <div class="container">
    <div class="{$p}-testimonials-header" data-animate="fade-up">
      <h2 class="{$p}-testimonials-title" data-ts="testimonials.title"><?= esc(\$tTitle) ?></h2>
      <p class="{$p}-testimonials-subtitle" data-ts="testimonials.subtitle"><?= esc(\$tSubtitle) ?></p>
    </div>
    <div class="{$p}-testimonials-scroll-row" data-animate="fade-up">
      <?php foreach (\$items as \$idx => \$item): ?>
      <div class="{$p}-testimonial-card">
        <div class="{$p}-testimonial-stars">
          <?php for (\$s = 0; \$s < \$item['rating']; \$s++): ?><i class="fas fa-star"></i><?php endfor; ?>
        </div>
        <blockquote class="{$p}-testimonial-quote">
          <p data-ts="testimonials.item<?= \$idx+1 ?>_quote"><?= esc(\$item['quote']) ?></p>
        </blockquote>
        <div class="{$p}-testimonial-author">
          <?php if (\$item['avatar']): ?>
          <img loading="lazy" src="<?= esc(\$item['avatar']) ?>" alt="<?= esc(\$item['name']) ?>" class="{$p}-testimonial-avatar" data-ts-bg="testimonials.item<?= \$idx+1 ?>_avatar">
          <?php else: ?>
          <div class="{$p}-testimonial-avatar-placeholder"><i class="fas fa-user"></i></div>
          <?php endif; ?>
          <div class="{$p}-testimonial-author-info">
            <strong data-ts="testimonials.item<?= \$idx+1 ?>_name"><?= esc(\$item['name']) ?></strong>
            <span data-ts="testimonials.item<?= \$idx+1 ?>_role"><?= esc(\$item['role']) ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Creative Quote Wall: Large quote marks background, overlapping quotes ──
'creative-quote-wall' => <<<HTML
<?php
\$tTitle = theme_get('testimonials.title', 'What Our Clients Say');
\$tSubtitle = theme_get('testimonials.subtitle', 'Voices from our community');
\$items = [];
for (\$i = 1; \$i <= 6; \$i++) {
    \$q = theme_get("testimonials.item{\$i}_quote", '');
    if (\$q || \$i <= 3) {
        \$items[] = [
            'quote'  => \$q ?: 'An electrifying experience that left us wanting more. Truly one of a kind!',
            'name'   => theme_get("testimonials.item{\$i}_name", 'Client ' . \$i),
            'role'   => theme_get("testimonials.item{\$i}_role", 'Fan'),
            'rating' => (int) theme_get("testimonials.item{\$i}_rating", '5'),
        ];
    }
}
?>
<section class="{$p}-testimonials {$p}-testimonials--quote-wall" id="testimonials">
  <div class="{$p}-testimonials-wall-bg">
    <span class="{$p}-testimonials-wall-mark {$p}-testimonials-wall-mark--1">&ldquo;</span>
    <span class="{$p}-testimonials-wall-mark {$p}-testimonials-wall-mark--2">&rdquo;</span>
  </div>
  <div class="container">
    <div class="{$p}-testimonials-header" data-animate="fade-up">
      <h2 class="{$p}-testimonials-title" data-ts="testimonials.title"><?= esc(\$tTitle) ?></h2>
      <p class="{$p}-testimonials-subtitle" data-ts="testimonials.subtitle"><?= esc(\$tSubtitle) ?></p>
    </div>
    <div class="{$p}-testimonials-wall-grid">
      <?php foreach (\$items as \$idx => \$item): ?>
      <div class="{$p}-testimonial-wall-card" data-animate="fade-up">
        <blockquote class="{$p}-testimonial-quote">
          <span class="{$p}-testimonial-quote-mark">&ldquo;</span>
          <p data-ts="testimonials.item<?= \$idx+1 ?>_quote"><?= esc(\$item['quote']) ?></p>
        </blockquote>
        <div class="{$p}-testimonial-wall-footer">
          <strong data-ts="testimonials.item<?= \$idx+1 ?>_name"><?= esc(\$item['name']) ?></strong>
          <span data-ts="testimonials.item<?= \$idx+1 ?>_role"><?= esc(\$item['role']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Creative Video Testimonial: Video thumbnail grid with play buttons ──
'creative-video-testimonial' => <<<HTML
<?php
\$tTitle = theme_get('testimonials.title', 'What Our Clients Say');
\$tSubtitle = theme_get('testimonials.subtitle', 'Watch their stories');
\$items = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$q = theme_get("testimonials.item{\$i}_quote", '');
    \$items[] = [
        'quote'  => \$q ?: 'This changed the way we work. Absolutely incredible results.',
        'name'   => theme_get("testimonials.item{\$i}_name", 'Client ' . \$i),
        'role'   => theme_get("testimonials.item{\$i}_role", 'Content Creator'),
        'avatar' => theme_get("testimonials.item{\$i}_avatar", ''),
        'video'  => theme_get("testimonials.item{\$i}_video", ''),
        'rating' => (int) theme_get("testimonials.item{\$i}_rating", '5'),
    ];
}
?>
<section class="{$p}-testimonials {$p}-testimonials--video" id="testimonials">
  <div class="container">
    <div class="{$p}-testimonials-header" data-animate="fade-up">
      <h2 class="{$p}-testimonials-title" data-ts="testimonials.title"><?= esc(\$tTitle) ?></h2>
      <p class="{$p}-testimonials-subtitle" data-ts="testimonials.subtitle"><?= esc(\$tSubtitle) ?></p>
    </div>
    <div class="{$p}-testimonials-video-grid">
      <?php foreach (\$items as \$idx => \$item): ?>
      <div class="{$p}-testimonial-video-card" data-animate="fade-up">
        <div class="{$p}-testimonial-video-thumb">
          <?php if (\$item['avatar']): ?>
          <img loading="lazy" src="<?= esc(\$item['avatar']) ?>" alt="<?= esc(\$item['name']) ?>" data-ts-bg="testimonials.item<?= \$idx+1 ?>_avatar">
          <?php endif; ?>
          <div class="{$p}-testimonial-play-btn">
            <i class="fas fa-play"></i>
          </div>
        </div>
        <div class="{$p}-testimonial-video-info">
          <blockquote class="{$p}-testimonial-quote">
            <p data-ts="testimonials.item<?= \$idx+1 ?>_quote"><?= esc(\$item['quote']) ?></p>
          </blockquote>
          <div class="{$p}-testimonial-author">
            <div class="{$p}-testimonial-author-info">
              <strong data-ts="testimonials.item<?= \$idx+1 ?>_name"><?= esc(\$item['name']) ?></strong>
              <span data-ts="testimonials.item<?= \$idx+1 ?>_role"><?= esc(\$item['role']) ?></span>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Creative Social Proof: Social media style cards ──
'creative-social-proof' => <<<HTML
<?php
\$tTitle = theme_get('testimonials.title', 'What Our Clients Say');
\$tSubtitle = theme_get('testimonials.subtitle', 'Real feedback from across the web');
\$items = [];
for (\$i = 1; \$i <= 6; \$i++) {
    \$q = theme_get("testimonials.item{\$i}_quote", '');
    if (\$q || \$i <= 3) {
        \$platforms = ['twitter', 'linkedin', 'twitter', 'linkedin', 'twitter', 'linkedin'];
        \$items[] = [
            'quote'    => \$q ?: 'Just had an amazing experience working with this team. Highly recommend! #impressed',
            'name'     => theme_get("testimonials.item{\$i}_name", 'Client ' . \$i),
            'role'     => theme_get("testimonials.item{\$i}_role", '@client' . \$i),
            'avatar'   => theme_get("testimonials.item{\$i}_avatar", ''),
            'platform' => theme_get("testimonials.item{\$i}_platform", \$platforms[\$i-1] ?? 'twitter'),
            'rating'   => (int) theme_get("testimonials.item{\$i}_rating", '5'),
        ];
    }
}
?>
<section class="{$p}-testimonials {$p}-testimonials--social-proof" id="testimonials">
  <div class="container">
    <div class="{$p}-testimonials-header" data-animate="fade-up">
      <h2 class="{$p}-testimonials-title" data-ts="testimonials.title"><?= esc(\$tTitle) ?></h2>
      <p class="{$p}-testimonials-subtitle" data-ts="testimonials.subtitle"><?= esc(\$tSubtitle) ?></p>
    </div>
    <div class="{$p}-testimonials-social-grid">
      <?php foreach (\$items as \$idx => \$item): ?>
      <div class="{$p}-testimonial-social-card {$p}-testimonial-social--<?= esc(\$item['platform']) ?>" data-animate="fade-up">
        <div class="{$p}-testimonial-social-header">
          <div class="{$p}-testimonial-social-user">
            <?php if (\$item['avatar']): ?>
            <img loading="lazy" src="<?= esc(\$item['avatar']) ?>" alt="<?= esc(\$item['name']) ?>" class="{$p}-testimonial-avatar" data-ts-bg="testimonials.item<?= \$idx+1 ?>_avatar">
            <?php else: ?>
            <div class="{$p}-testimonial-avatar-placeholder"><i class="fas fa-user"></i></div>
            <?php endif; ?>
            <div class="{$p}-testimonial-author-info">
              <strong data-ts="testimonials.item<?= \$idx+1 ?>_name"><?= esc(\$item['name']) ?></strong>
              <span data-ts="testimonials.item<?= \$idx+1 ?>_role"><?= esc(\$item['role']) ?></span>
            </div>
          </div>
          <i class="fab fa-<?= \$item['platform'] === 'linkedin' ? 'linkedin' : 'x-twitter' ?>"></i>
        </div>
        <blockquote class="{$p}-testimonial-quote">
          <p data-ts="testimonials.item<?= \$idx+1 ?>_quote"><?= esc(\$item['quote']) ?></p>
        </blockquote>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Minimal List: Simple vertical list with thin dividers ──
'minimal-list' => <<<HTML
<?php
\$tTitle = theme_get('testimonials.title', 'What Our Clients Say');
\$tSubtitle = theme_get('testimonials.subtitle', 'Selected words from our portfolio');
\$items = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$q = theme_get("testimonials.item{\$i}_quote", '');
    if (\$q || \$i <= 3) {
        \$items[] = [
            'quote'  => \$q ?: 'Refined, elegant, and meticulously crafted. The result is nothing short of extraordinary.',
            'name'   => theme_get("testimonials.item{\$i}_name", 'Client ' . \$i),
            'role'   => theme_get("testimonials.item{\$i}_role", 'Private Client'),
            'rating' => (int) theme_get("testimonials.item{\$i}_rating", '5'),
        ];
    }
}
?>
<section class="{$p}-testimonials {$p}-testimonials--minimal-list" id="testimonials">
  <div class="container">
    <div class="{$p}-testimonials-header" data-animate="fade-up">
      <h2 class="{$p}-testimonials-title" data-ts="testimonials.title"><?= esc(\$tTitle) ?></h2>
      <p class="{$p}-testimonials-subtitle" data-ts="testimonials.subtitle"><?= esc(\$tSubtitle) ?></p>
    </div>
    <div class="{$p}-testimonials-list">
      <?php foreach (\$items as \$idx => \$item): ?>
      <div class="{$p}-testimonial-list-item" data-animate="fade-up">
        <blockquote class="{$p}-testimonial-quote">
          <p data-ts="testimonials.item<?= \$idx+1 ?>_quote"><?= esc(\$item['quote']) ?></p>
        </blockquote>
        <div class="{$p}-testimonial-list-meta">
          <strong data-ts="testimonials.item<?= \$idx+1 ?>_name"><?= esc(\$item['name']) ?></strong>
          <span class="{$p}-testimonial-list-divider">&mdash;</span>
          <span data-ts="testimonials.item<?= \$idx+1 ?>_role"><?= esc(\$item['role']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Minimal Centered: One quote at a time, centered, large italic text ──
'minimal-centered' => <<<HTML
<?php
\$tTitle = theme_get('testimonials.title', 'What Our Clients Say');
\$tSubtitle = theme_get('testimonials.subtitle', '');
\$items = [];
for (\$i = 1; \$i <= 3; \$i++) {
    \$q = theme_get("testimonials.item{\$i}_quote", '');
    \$items[] = [
        'quote'  => \$q ?: 'They delivered exactly what we envisioned, with impeccable craftsmanship and genuine care for our project.',
        'name'   => theme_get("testimonials.item{\$i}_name", 'Client ' . \$i),
        'role'   => theme_get("testimonials.item{\$i}_role", 'Homeowner'),
        'rating' => (int) theme_get("testimonials.item{\$i}_rating", '5'),
    ];
}
?>
<section class="{$p}-testimonials {$p}-testimonials--minimal-centered" id="testimonials">
  <div class="container">
    <?php if (\$tTitle): ?>
    <div class="{$p}-testimonials-header" data-animate="fade-up">
      <h2 class="{$p}-testimonials-title" data-ts="testimonials.title"><?= esc(\$tTitle) ?></h2>
      <?php if (\$tSubtitle): ?><p class="{$p}-testimonials-subtitle" data-ts="testimonials.subtitle"><?= esc(\$tSubtitle) ?></p><?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="{$p}-testimonials-centered-slider" data-animate="fade-up">
      <?php foreach (\$items as \$idx => \$item): ?>
      <div class="{$p}-testimonial-centered-slide<?= \$idx === 0 ? ' active' : '' ?>">
        <span class="{$p}-testimonial-quote-mark">&ldquo;</span>
        <blockquote class="{$p}-testimonial-quote">
          <p data-ts="testimonials.item<?= \$idx+1 ?>_quote"><?= esc(\$item['quote']) ?></p>
        </blockquote>
        <div class="{$p}-testimonial-centered-meta">
          <strong data-ts="testimonials.item<?= \$idx+1 ?>_name"><?= esc(\$item['name']) ?></strong>
          <span data-ts="testimonials.item<?= \$idx+1 ?>_role"><?= esc(\$item['role']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
      <div class="{$p}-testimonial-dots">
        <?php foreach (\$items as \$idx => \$item): ?>
        <button class="{$p}-testimonial-dot<?= \$idx === 0 ? ' active' : '' ?>" aria-label="Slide <?= \$idx+1 ?>"></button>
        <?php endforeach; ?>
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
        $base = [
            "{$p}-testimonials", "{$p}-testimonials-header", "{$p}-testimonials-title",
            "{$p}-testimonials-subtitle", "{$p}-testimonial-card", "{$p}-testimonial-quote",
            "{$p}-testimonial-quote-mark", "{$p}-testimonial-author", "{$p}-testimonial-avatar",
            "{$p}-testimonial-avatar-placeholder", "{$p}-testimonial-author-info",
            "{$p}-testimonial-stars",
        ];

        $extra = match($patternId) {
            'cards-grid', 'cards-minimal' =>
                ["{$p}-testimonials-grid"],
            'cards-masonry' =>
                ["{$p}-testimonials-masonry"],
            'cards-single-featured' =>
                ["{$p}-testimonials-featured-layout", "{$p}-testimonial-card--featured", "{$p}-testimonials-secondary"],
            'cards-minimal' =>
                ["{$p}-testimonial-author-minimal"],
            'slider-centered' =>
                ["{$p}-testimonials-slider", "{$p}-testimonial-slide", "{$p}-testimonial-dots", "{$p}-testimonial-dot"],
            'slider-split' =>
                ["{$p}-testimonials-split-slider", "{$p}-testimonial-slide-split", "{$p}-testimonial-slide-image",
                 "{$p}-testimonial-slide-content", "{$p}-testimonial-arrows", "{$p}-testimonial-arrow"],
            'slider-cards-row' =>
                ["{$p}-testimonials-scroll-row"],
            'creative-quote-wall' =>
                ["{$p}-testimonials-wall-bg", "{$p}-testimonials-wall-mark", "{$p}-testimonials-wall-grid",
                 "{$p}-testimonial-wall-card", "{$p}-testimonial-wall-footer"],
            'creative-video-testimonial' =>
                ["{$p}-testimonials-video-grid", "{$p}-testimonial-video-card", "{$p}-testimonial-video-thumb",
                 "{$p}-testimonial-play-btn", "{$p}-testimonial-video-info"],
            'creative-social-proof' =>
                ["{$p}-testimonials-social-grid", "{$p}-testimonial-social-card", "{$p}-testimonial-social-header",
                 "{$p}-testimonial-social-user"],
            'minimal-list' =>
                ["{$p}-testimonials-list", "{$p}-testimonial-list-item", "{$p}-testimonial-list-meta",
                 "{$p}-testimonial-list-divider"],
            'minimal-centered' =>
                ["{$p}-testimonials-centered-slider", "{$p}-testimonial-centered-slide",
                 "{$p}-testimonial-centered-meta", "{$p}-testimonial-dots", "{$p}-testimonial-dot"],
            default => [],
        };

        return array_merge($base, $extra);
    }

    private static function buildStructuralCSS(string $cssType, string $p): string
    {
        $css = self::css_base($p);

        $typeCSS = match($cssType) {
            'cards-grid'             => self::css_cards_grid($p),
            'cards-masonry'          => self::css_cards_masonry($p),
            'cards-single-featured'  => self::css_cards_single_featured($p),
            'cards-minimal'          => self::css_cards_minimal($p),
            'slider-centered'        => self::css_slider_centered($p),
            'slider-split'           => self::css_slider_split($p),
            'slider-cards-row'       => self::css_slider_cards_row($p),
            'creative-quote-wall'    => self::css_creative_quote_wall($p),
            'creative-video-testimonial' => self::css_creative_video_testimonial($p),
            'creative-social-proof'  => self::css_creative_social_proof($p),
            'minimal-list'           => self::css_minimal_list($p),
            'minimal-centered'       => self::css_minimal_centered($p),
            default                  => self::css_cards_grid($p),
        };

        return $css . $typeCSS . self::css_responsive($p);
    }

    // --- Base (shared by all testimonial patterns) ---
    private static function css_base(string $p): string
    {
        return <<<CSS

/* ═══ Testimonials Structural CSS (auto-generated — do not edit) ═══ */
.{$p}-testimonials {
  position: relative; overflow: hidden;
  padding: clamp(80px, 12vh, 140px) 0;
}
.{$p}-testimonials-header {
  text-align: center; max-width: 700px;
  margin: 0 auto clamp(40px, 6vh, 64px);
}
.{$p}-testimonials-title {
  font-family: var(--font-heading, inherit);
  font-size: clamp(1.75rem, 3.5vw, 2.75rem);
  font-weight: 700; line-height: 1.2;
  margin: 0 0 16px 0;
  color: var(--heading, var(--text, #1e293b));
}
.{$p}-testimonials-subtitle {
  font-size: clamp(0.9375rem, 1.5vw, 1.125rem);
  line-height: 1.7; margin: 0;
  color: var(--text-muted, #64748b);
}
.{$p}-testimonial-card {
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  padding: clamp(24px, 3vw, 36px);
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border: 1px solid rgba(var(--text-rgb, 30,41,59), 0.06);
}
.{$p}-testimonial-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.1);
}
.{$p}-testimonial-stars {
  display: flex; gap: 2px; margin-bottom: 16px;
}
.{$p}-testimonial-stars i {
  color: var(--warning, #f59e0b); font-size: 0.875rem;
}
.{$p}-testimonial-quote {
  margin: 0 0 20px 0; padding: 0;
  border: none;
}
.{$p}-testimonial-quote p {
  font-size: clamp(0.9375rem, 1.5vw, 1.0625rem);
  line-height: 1.75; margin: 0;
  color: var(--text, #1e293b);
}
.{$p}-testimonial-quote-mark {
  font-family: Georgia, 'Times New Roman', serif;
  font-size: clamp(3rem, 5vw, 5rem);
  line-height: 1; display: block;
  color: var(--primary, #3b82f6);
  opacity: 0.15; margin-bottom: -16px;
}
.{$p}-testimonial-author {
  display: flex; align-items: center; gap: 12px;
}
.{$p}-testimonial-avatar {
  width: 56px; height: 56px;
  border-radius: 50%; object-fit: cover;
  flex-shrink: 0;
}
.{$p}-testimonial-avatar-placeholder {
  width: 56px; height: 56px;
  border-radius: 50%; flex-shrink: 0;
  background: rgba(var(--primary-rgb, 42,125,225), 0.1);
  color: var(--primary, #3b82f6);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.25rem;
}
.{$p}-testimonial-author-info {
  display: flex; flex-direction: column;
}
.{$p}-testimonial-author-info strong {
  font-size: 0.9375rem; font-weight: 600;
  color: var(--heading, var(--text, #1e293b));
}
.{$p}-testimonial-author-info span {
  font-size: 0.8125rem;
  color: var(--text-muted, #64748b);
}

CSS;
    }

    // --- Cards Grid ---
    private static function css_cards_grid(string $p): string
    {
        return <<<CSS
.{$p}-testimonials--cards-grid .{$p}-testimonials-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: clamp(20px, 3vw, 32px);
}

CSS;
    }

    // --- Cards Masonry ---
    private static function css_cards_masonry(string $p): string
    {
        return <<<CSS
.{$p}-testimonials--cards-masonry .{$p}-testimonials-masonry {
  columns: 3;
  column-gap: clamp(20px, 3vw, 32px);
}
.{$p}-testimonials--cards-masonry .{$p}-testimonial-card {
  break-inside: avoid;
  margin-bottom: clamp(20px, 3vw, 32px);
  display: inline-block;
  width: 100%;
}

CSS;
    }

    // --- Cards Single Featured ---
    private static function css_cards_single_featured(string $p): string
    {
        return <<<CSS
.{$p}-testimonials-featured-layout {
  display: flex; flex-direction: column;
  gap: clamp(24px, 3vw, 40px);
}
.{$p}-testimonial-card--featured {
  padding: clamp(32px, 4vw, 56px);
  position: relative;
}
.{$p}-testimonial-card--featured .{$p}-testimonial-quote p {
  font-size: clamp(1.0625rem, 2vw, 1.3125rem);
  line-height: 1.8;
}
.{$p}-testimonial-card--featured .{$p}-testimonial-quote-mark {
  font-size: clamp(4rem, 7vw, 7rem);
  opacity: 0.1; position: absolute;
  top: 20px; left: 24px;
}
.{$p}-testimonials-secondary {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: clamp(20px, 3vw, 32px);
}

CSS;
    }

    // --- Cards Minimal ---
    private static function css_cards_minimal(string $p): string
    {
        return <<<CSS
.{$p}-testimonials--cards-minimal .{$p}-testimonials-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: clamp(20px, 3vw, 32px);
}
.{$p}-testimonials--cards-minimal .{$p}-testimonial-card {
  background: transparent;
  border: 1px solid rgba(var(--text-rgb, 30,41,59), 0.1);
  box-shadow: none;
}
.{$p}-testimonials--cards-minimal .{$p}-testimonial-card:hover {
  border-color: var(--primary, #3b82f6);
  box-shadow: none;
  transform: none;
}
.{$p}-testimonial-author-minimal {
  display: flex; flex-direction: column;
  padding-top: 16px;
  border-top: 1px solid rgba(var(--text-rgb, 30,41,59), 0.08);
}
.{$p}-testimonial-author-minimal strong {
  font-size: 0.9375rem; font-weight: 600;
  color: var(--heading, var(--text, #1e293b));
}
.{$p}-testimonial-author-minimal span {
  font-size: 0.8125rem;
  color: var(--text-muted, #64748b);
}

CSS;
    }

    // --- Slider Centered ---
    private static function css_slider_centered(string $p): string
    {
        return <<<CSS
.{$p}-testimonials--slider-centered .{$p}-testimonials-slider {
  max-width: 700px; margin: 0 auto;
  text-align: center; position: relative;
}
.{$p}-testimonial-slide {
  display: none;
}
.{$p}-testimonial-slide.active {
  display: block;
}
.{$p}-testimonials--slider-centered .{$p}-testimonial-quote-mark {
  font-size: clamp(4rem, 7vw, 7rem);
  margin-bottom: -10px;
}
.{$p}-testimonials--slider-centered .{$p}-testimonial-quote p {
  font-size: clamp(1.0625rem, 2vw, 1.3125rem);
  line-height: 1.8; font-style: italic;
}
.{$p}-testimonials--slider-centered .{$p}-testimonial-stars {
  justify-content: center;
}
.{$p}-testimonials--slider-centered .{$p}-testimonial-author {
  justify-content: center;
}
.{$p}-testimonial-dots {
  display: flex; justify-content: center;
  gap: 8px; margin-top: 32px;
}
.{$p}-testimonial-dot {
  width: 10px; height: 10px; border-radius: 50%;
  border: 2px solid var(--primary, #3b82f6);
  background: transparent; cursor: pointer;
  padding: 0; transition: all 0.3s ease;
}
.{$p}-testimonial-dot.active {
  background: var(--primary, #3b82f6);
}

CSS;
    }

    // --- Slider Split ---
    private static function css_slider_split(string $p): string
    {
        return <<<CSS
.{$p}-testimonials-split-slider {
  position: relative;
}
.{$p}-testimonial-slide-split {
  display: none;
}
.{$p}-testimonial-slide-split.active {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 64px);
  align-items: center;
}
.{$p}-testimonial-slide-image {
  border-radius: var(--radius, 12px);
  overflow: hidden; aspect-ratio: 4/3;
}
.{$p}-testimonial-slide-image img {
  width: 100%; height: 100%;
  object-fit: cover; display: block;
}
.{$p}-testimonial-image-placeholder {
  width: 100%; height: 100%;
  background: rgba(var(--primary-rgb, 42,125,225), 0.08);
  display: flex; align-items: center; justify-content: center;
  font-size: 3rem; color: var(--primary, #3b82f6); opacity: 0.3;
}
.{$p}-testimonial-slide-content .{$p}-testimonial-quote-mark {
  font-size: clamp(3rem, 5vw, 5rem);
}
.{$p}-testimonial-slide-content .{$p}-testimonial-quote p {
  font-size: clamp(1rem, 1.8vw, 1.1875rem);
  line-height: 1.8;
}
.{$p}-testimonial-arrows {
  display: flex; gap: 12px;
  margin-top: 32px; justify-content: center;
}
.{$p}-testimonial-arrow {
  width: 48px; height: 48px;
  border-radius: 50%; border: 2px solid rgba(var(--text-rgb, 30,41,59), 0.15);
  background: transparent; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  color: var(--text, #1e293b);
  transition: all 0.3s ease;
}
.{$p}-testimonial-arrow:hover {
  border-color: var(--primary, #3b82f6);
  color: var(--primary, #3b82f6);
}

CSS;
    }

    // --- Slider Cards Row ---
    private static function css_slider_cards_row(string $p): string
    {
        return <<<CSS
.{$p}-testimonials-scroll-row {
  display: flex; gap: clamp(20px, 3vw, 32px);
  overflow-x: auto; scroll-snap-type: x mandatory;
  padding-bottom: 16px;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
}
.{$p}-testimonials-scroll-row::-webkit-scrollbar {
  height: 6px;
}
.{$p}-testimonials-scroll-row::-webkit-scrollbar-track {
  background: rgba(var(--text-rgb, 30,41,59), 0.05);
  border-radius: 3px;
}
.{$p}-testimonials-scroll-row::-webkit-scrollbar-thumb {
  background: rgba(var(--primary-rgb, 42,125,225), 0.3);
  border-radius: 3px;
}
.{$p}-testimonials--slider-cards-row .{$p}-testimonial-card {
  min-width: calc(33.333% - 22px);
  flex-shrink: 0; scroll-snap-align: start;
}

CSS;
    }

    // --- Creative Quote Wall ---
    private static function css_creative_quote_wall(string $p): string
    {
        return <<<CSS
.{$p}-testimonials--quote-wall {
  position: relative;
}
.{$p}-testimonials-wall-bg {
  position: absolute; inset: 0;
  pointer-events: none; overflow: hidden;
}
.{$p}-testimonials-wall-mark {
  position: absolute;
  font-family: Georgia, 'Times New Roman', serif;
  font-size: clamp(15rem, 25vw, 30rem);
  line-height: 1;
  color: var(--primary, #3b82f6);
  opacity: 0.04;
}
.{$p}-testimonials-wall-mark--1 {
  top: -5%; left: -5%;
}
.{$p}-testimonials-wall-mark--2 {
  bottom: -10%; right: -5%;
}
.{$p}-testimonials-wall-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: clamp(16px, 2vw, 24px);
}
.{$p}-testimonial-wall-card {
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  padding: clamp(20px, 3vw, 32px);
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  border: 1px solid rgba(var(--text-rgb, 30,41,59), 0.06);
  position: relative;
  transition: transform 0.3s ease;
}
.{$p}-testimonial-wall-card:hover {
  transform: translateY(-4px) rotate(-0.5deg);
}
.{$p}-testimonial-wall-card:nth-child(even):hover {
  transform: translateY(-4px) rotate(0.5deg);
}
.{$p}-testimonial-wall-card .{$p}-testimonial-quote-mark {
  font-size: 2.5rem; margin-bottom: -12px;
}
.{$p}-testimonial-wall-footer {
  display: flex; flex-direction: column;
  margin-top: 16px; padding-top: 12px;
  border-top: 1px solid rgba(var(--text-rgb, 30,41,59), 0.08);
}
.{$p}-testimonial-wall-footer strong {
  font-size: 0.875rem; font-weight: 600;
  color: var(--heading, var(--text, #1e293b));
}
.{$p}-testimonial-wall-footer span {
  font-size: 0.75rem;
  color: var(--text-muted, #64748b);
}

CSS;
    }

    // --- Creative Video Testimonial ---
    private static function css_creative_video_testimonial(string $p): string
    {
        return <<<CSS
.{$p}-testimonials-video-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: clamp(24px, 3vw, 40px);
}
.{$p}-testimonial-video-card {
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  border: 1px solid rgba(var(--text-rgb, 30,41,59), 0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-testimonial-video-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.1);
}
.{$p}-testimonial-video-thumb {
  position: relative; aspect-ratio: 16/9;
  background: rgba(var(--primary-rgb, 42,125,225), 0.08);
  overflow: hidden;
}
.{$p}-testimonial-video-thumb img {
  width: 100%; height: 100%;
  object-fit: cover; display: block;
}
.{$p}-testimonial-play-btn {
  position: absolute; top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  width: 64px; height: 64px; border-radius: 50%;
  background: rgba(var(--primary-rgb, 42,125,225), 0.9);
  color: #fff; display: flex; align-items: center; justify-content: center;
  font-size: 1.25rem; cursor: pointer;
  transition: transform 0.3s ease, background 0.3s ease;
  box-shadow: 0 8px 30px rgba(0,0,0,0.2);
}
.{$p}-testimonial-play-btn:hover {
  transform: translate(-50%, -50%) scale(1.1);
}
.{$p}-testimonial-video-info {
  padding: clamp(20px, 3vw, 28px);
}
.{$p}-testimonial-video-info .{$p}-testimonial-quote p {
  font-size: 0.9375rem; line-height: 1.65;
}

CSS;
    }

    // --- Creative Social Proof ---
    private static function css_creative_social_proof(string $p): string
    {
        return <<<CSS
.{$p}-testimonials-social-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: clamp(20px, 3vw, 28px);
}
.{$p}-testimonial-social-card {
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  padding: clamp(20px, 3vw, 28px);
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  border: 1px solid rgba(var(--text-rgb, 30,41,59), 0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-testimonial-social-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.1);
}
.{$p}-testimonial-social-header {
  display: flex; justify-content: space-between;
  align-items: flex-start; margin-bottom: 16px;
}
.{$p}-testimonial-social-header > i {
  font-size: 1.25rem; opacity: 0.5;
}
.{$p}-testimonial-social--twitter .{$p}-testimonial-social-header > i {
  color: #1da1f2;
}
.{$p}-testimonial-social--linkedin .{$p}-testimonial-social-header > i {
  color: #0077b5;
}
.{$p}-testimonial-social-user {
  display: flex; align-items: center; gap: 10px;
}
.{$p}-testimonial-social-card .{$p}-testimonial-avatar,
.{$p}-testimonial-social-card .{$p}-testimonial-avatar-placeholder {
  width: 44px; height: 44px;
}
.{$p}-testimonial-social-card .{$p}-testimonial-quote p {
  font-size: 0.9375rem; line-height: 1.65;
}

CSS;
    }

    // --- Minimal List ---
    private static function css_minimal_list(string $p): string
    {
        return <<<CSS
.{$p}-testimonials--minimal-list .{$p}-testimonials-list {
  max-width: 800px; margin: 0 auto;
}
.{$p}-testimonial-list-item {
  padding: clamp(32px, 4vw, 48px) 0;
  border-bottom: 1px solid rgba(var(--text-rgb, 30,41,59), 0.08);
}
.{$p}-testimonial-list-item:first-child {
  padding-top: 0;
}
.{$p}-testimonial-list-item:last-child {
  border-bottom: none;
}
.{$p}-testimonial-list-item .{$p}-testimonial-quote {
  margin-bottom: 16px;
}
.{$p}-testimonial-list-item .{$p}-testimonial-quote p {
  font-size: clamp(1rem, 1.8vw, 1.1875rem);
  line-height: 1.8; font-style: italic;
}
.{$p}-testimonial-list-meta {
  display: flex; align-items: center; gap: 8px;
}
.{$p}-testimonial-list-meta strong {
  font-size: 0.9375rem; font-weight: 600;
  color: var(--heading, var(--text, #1e293b));
}
.{$p}-testimonial-list-divider {
  color: var(--text-muted, #64748b); opacity: 0.5;
}
.{$p}-testimonial-list-meta span:last-child {
  font-size: 0.875rem;
  color: var(--text-muted, #64748b);
}

CSS;
    }

    // --- Minimal Centered ---
    private static function css_minimal_centered(string $p): string
    {
        return <<<CSS
.{$p}-testimonials--minimal-centered {
  text-align: center;
}
.{$p}-testimonials-centered-slider {
  max-width: 800px; margin: 0 auto;
}
.{$p}-testimonial-centered-slide {
  display: none;
}
.{$p}-testimonial-centered-slide.active {
  display: block;
}
.{$p}-testimonials--minimal-centered .{$p}-testimonial-quote-mark {
  font-size: clamp(4rem, 8vw, 8rem);
  margin-bottom: -10px;
}
.{$p}-testimonial-centered-slide .{$p}-testimonial-quote p {
  font-size: clamp(1.125rem, 2.5vw, 1.5rem);
  line-height: 1.8; font-style: italic;
  color: var(--text, #1e293b);
}
.{$p}-testimonial-centered-meta {
  display: flex; flex-direction: column;
  align-items: center; gap: 4px;
  margin-top: 24px;
}
.{$p}-testimonial-centered-meta strong {
  font-size: 1rem; font-weight: 600;
  color: var(--heading, var(--text, #1e293b));
}
.{$p}-testimonial-centered-meta span {
  font-size: 0.875rem;
  color: var(--text-muted, #64748b);
}
.{$p}-testimonials--minimal-centered .{$p}-testimonial-dots {
  display: flex; justify-content: center;
  gap: 8px; margin-top: 40px;
}
.{$p}-testimonials--minimal-centered .{$p}-testimonial-dot {
  width: 10px; height: 10px; border-radius: 50%;
  border: 2px solid var(--primary, #3b82f6);
  background: transparent; cursor: pointer;
  padding: 0; transition: all 0.3s ease;
}
.{$p}-testimonials--minimal-centered .{$p}-testimonial-dot.active {
  background: var(--primary, #3b82f6);
}

CSS;
    }

    // --- Responsive ---
    private static function css_responsive(string $p): string
    {
        return <<<CSS
@media (max-width: 1024px) {
  .{$p}-testimonials--cards-grid .{$p}-testimonials-grid,
  .{$p}-testimonials--cards-minimal .{$p}-testimonials-grid,
  .{$p}-testimonials-social-grid,
  .{$p}-testimonials-wall-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .{$p}-testimonials--cards-masonry .{$p}-testimonials-masonry {
    columns: 2;
  }
  .{$p}-testimonials--slider-cards-row .{$p}-testimonial-card {
    min-width: calc(50% - 16px);
  }
}
@media (max-width: 768px) {
  .{$p}-testimonials--cards-grid .{$p}-testimonials-grid,
  .{$p}-testimonials--cards-minimal .{$p}-testimonials-grid,
  .{$p}-testimonials-social-grid,
  .{$p}-testimonials-wall-grid,
  .{$p}-testimonials-video-grid,
  .{$p}-testimonials-secondary {
    grid-template-columns: 1fr !important;
  }
  .{$p}-testimonials--cards-masonry .{$p}-testimonials-masonry {
    columns: 1;
  }
  .{$p}-testimonial-slide-split.active {
    grid-template-columns: 1fr !important;
    gap: 24px !important;
  }
  .{$p}-testimonials--slider-cards-row .{$p}-testimonial-card {
    min-width: 85%;
  }
  .{$p}-testimonial-arrows {
    justify-content: center;
  }
}

CSS;
    }
}
