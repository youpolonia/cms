<?php
/**
 * Gallery Section Pattern Registry
 * 
 * Pre-built Gallery HTML layouts with structural CSS.
 * AI generates only decorative CSS (colors, fonts, effects) — never the structure.
 * 
 * 8 patterns across 3 groups.
 * @since 2026-02-19
 */

class GalleryPatternRegistry
{
    // ═══════════════════════════════════════
    // PATTERN DEFINITIONS
    // ═══════════════════════════════════════

    private static array $patterns = [
        // --- Grid (even layouts) ---
        ['id'=>'grid-3col',       'group'=>'grid',     'css_type'=>'grid-3col',
         'best_for'=>['photography','art','gallery','museum','fashion']],
        ['id'=>'grid-masonry',    'group'=>'grid',     'css_type'=>'grid-masonry',
         'best_for'=>['wedding','florist','interior-design','architecture']],
        ['id'=>'grid-filterable', 'group'=>'grid',     'css_type'=>'grid-filterable',
         'best_for'=>['creative-agency','design','web-design','branding']],

        // --- Showcase (feature-focused) ---
        ['id'=>'showcase-featured',  'group'=>'showcase', 'css_type'=>'showcase-featured',
         'best_for'=>['restaurant','bakery','hotel','resort','spa']],
        ['id'=>'showcase-carousel',  'group'=>'showcase', 'css_type'=>'showcase-carousel',
         'best_for'=>['automotive','jewelry','luxury','watch','furniture']],
        ['id'=>'showcase-lightbox',  'group'=>'showcase', 'css_type'=>'showcase-lightbox',
         'best_for'=>['tattoo','salon','beauty','cosmetics','barbershop']],

        // --- Creative (unique layouts) ---
        ['id'=>'creative-mosaic',       'group'=>'creative', 'css_type'=>'creative-mosaic',
         'best_for'=>['travel','tourism','adventure','outdoor','fitness']],
        ['id'=>'creative-before-after', 'group'=>'creative', 'css_type'=>'creative-before-after',
         'best_for'=>['construction','landscaping','cleaning','renovation']],
    ];

    // ═══════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════

    /**
     * Pick the best gallery pattern for an industry.
     */
    public static function pickPattern(string $industry): string
    {
        $industry = strtolower(trim($industry));
        foreach (self::$patterns as $p) {
            if (in_array($industry, $p['best_for'], true)) {
                return $p['id'];
            }
        }
        // Fallback: random from grid group (most versatile)
        $gridPatterns = array_filter(self::$patterns, fn($p) => $p['group'] === 'grid');
        $gridIds = array_column(array_values($gridPatterns), 'id');
        return $gridIds[array_rand($gridIds)];
    }

    /**
     * Render a gallery pattern.
     * Returns ['html'=>..., 'structural_css'=>..., 'pattern_id'=>..., 'classes'=>[...], 'fields'=>[...]]
     */
    public static function render(string $patternId, string $prefix, array $brief): array
    {
        $def = null;
        foreach (self::$patterns as $p) {
            if ($p['id'] === $patternId) { $def = $p; break; }
        }
        if (!$def) {
            $def = self::$patterns[0]; // fallback to grid-3col
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
        // Common fields all gallery patterns have
        $common = [
            'title'    => ['type' => 'text',     'label' => 'Gallery Title'],
            'subtitle' => ['type' => 'textarea', 'label' => 'Gallery Subtitle'],
            'badge'    => ['type' => 'text',     'label' => 'Badge / Label'],
        ];

        // Image fields (6 images with captions)
        for ($i = 1; $i <= 6; $i++) {
            $common["image{$i}"] = ['type' => 'image', 'label' => "Image {$i}"];
            $common["caption{$i}"] = ['type' => 'text', 'label' => "Caption {$i}"];
        }

        // Pattern-specific extras
        $extras = match($patternId) {
            'grid-filterable' => [
                'cat1' => ['type' => 'text', 'label' => 'Category 1'],
                'cat2' => ['type' => 'text', 'label' => 'Category 2'],
                'cat3' => ['type' => 'text', 'label' => 'Category 3'],
                'cat4' => ['type' => 'text', 'label' => 'Category 4'],
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
            'grid-3col' => <<<'GUIDE'
Images maintain consistent aspect-ratio (1:1 square) with border-radius on container.
Hover overlay uses dark gradient from bottom with white caption text sliding up.
Image hover: subtle scale(1.08) zoom with overflow hidden for smooth crop effect.
Consistent soft shadow on each grid cell, border-radius matches site radius var.
Caption text uses white color on dark gradient overlay, small font, left-aligned.
GUIDE,
            'grid-masonry' => <<<'GUIDE'
Images have natural varied heights — no forced aspect-ratio, organic flow.
Hover: subtle zoom scale(1.05) with smooth 0.4s transition, overflow hidden.
Border-radius on each masonry item container, consistent gap spacing.
Optional soft shadow on items, caption below image in muted text color.
Overall feel: Pinterest-like organic gallery, no rigid uniformity.
GUIDE,
            'grid-filterable' => <<<'GUIDE'
Filter buttons use pill shape (border-radius 100px), inactive has muted border and text.
Active filter button: primary bg color, white text, primary border — filled pill.
Filtered items animate with fade-in/out opacity transition on show/hide.
Images have consistent aspect-ratio with border-radius and hover zoom.
Optional category badge overlay on images using small pill label.
GUIDE,
            'showcase-featured' => <<<'GUIDE'
Featured main image has larger border-radius and prominent box-shadow frame.
Thumbnail images have subtle border, hover: border-color transitions to primary.
Featured image caption overlays with dark gradient bottom, white text.
Click-to-swap interaction — active thumbnail gets primary border highlight.
Overall feel: hero-focused, editorial, one dominant image with supporting cast.
GUIDE,
            'showcase-carousel' => <<<'GUIDE'
Carousel slides have rounded corners, consistent aspect-ratio (3:4 portrait).
Hover: subtle image zoom, caption reveals from bottom with gradient overlay.
Scrollbar styled thin and subtle (scrollbar-width: thin), track muted color.
Optional navigation arrow buttons: circular, primary bg, white icon.
Progress indicator as thin bar or dots below carousel track.
GUIDE,
            'showcase-lightbox' => <<<'GUIDE'
Grid items show magnifying glass icon centered on hover overlay.
Hover overlay uses semi-transparent dark bg (rgba 0,0,0,0.5) with fade-in.
Image zoom on hover scale(1.08) with border-radius on container.
Lightbox overlay uses very dark bg (rgba 0,0,0,0.9), image has border-radius.
Close button: white circle with X icon, positioned top-right, hover opacity change.
GUIDE,
            'creative-mosaic' => <<<'GUIDE'
Mixed-size tiles create visual variety — some span 2 columns or 2 rows.
Each tile has border-radius, hover: zoom scale(1.05) with smooth transition.
Hover caption overlay uses dark gradient from bottom, white text.
Gap borders between tiles use consistent spacing, no visible grid lines.
Large tiles feel heroic, small tiles feel supporting — shadow varies by size.
GUIDE,
            'creative-before-after' => <<<'GUIDE'
Before/After labels use small pill badges, primary bg, white text, positioned top-left.
Comparison container has subtle surface bg and padding for framed look.
Optional drag handle line uses primary color vertical stripe with grab cursor.
Side labels (Before/After) use uppercase small text, letter-spacing, bold weight.
Images have matching border-radius, consistent aspect-ratio within each pair.
GUIDE,
            default => <<<'GUIDE'
Images have border-radius and hover zoom effect.
Soft shadows on containers, consistent spacing.
Captions use muted text color below or overlaid on images.
Hover transitions smooth with 0.3-0.4s ease timing.
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

        $title = $brief['gallery_title'] ?? '';
        if (!$title && $name) {
            $title = "Our Gallery";
        }

        $subtitle = $brief['gallery_subtitle'] ?? '';

        $badge = '';
        if ($industry) {
            $badge = ucwords(str_replace('-', ' ', $industry));
        }

        $replacements = [];
        if ($title)    $replacements["theme_get('gallery.title', 'Our Gallery')"]                                                  = "theme_get('gallery.title', '" . addslashes($title) . "')";
        if ($subtitle) $replacements["theme_get('gallery.subtitle', 'Browse through our collection of work and projects.')"]       = "theme_get('gallery.subtitle', '" . addslashes($subtitle) . "')";
        if ($badge)    $replacements["theme_get('gallery.badge', '')"]                                                             = "theme_get('gallery.badge', '" . addslashes($badge) . "')";

        foreach ($replacements as $search => $replace) {
            $html = str_replace($search, $replace, $html);
        }

        return $html;
    }

    private static function buildHTML(string $patternId, string $p): string
    {
        $templates = self::getTemplates($p);
        return $templates[$patternId] ?? $templates['grid-3col'];
    }

    private static function getTemplates(string $p): array
    {
        return [

// ── Grid 3-Column: Even 3-col grid with hover zoom ──
'grid-3col' => <<<HTML
<?php
\$galBadge = theme_get('gallery.badge', '');
\$galTitle = theme_get('gallery.title', 'Our Gallery');
\$galSubtitle = theme_get('gallery.subtitle', 'Browse through our collection of work and projects.');
?>
<section class="{$p}-gallery {$p}-gallery--grid-3col" id="gallery">
  <div class="container">
    <div class="{$p}-gallery-header" data-animate="fade-up">
      <?php if (\$galBadge): ?><span class="{$p}-gallery-badge" data-ts="gallery.badge"><?= esc(\$galBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-gallery-title" data-ts="gallery.title"><?= esc(\$galTitle) ?></h2>
      <p class="{$p}-gallery-subtitle" data-ts="gallery.subtitle"><?= esc(\$galSubtitle) ?></p>
    </div>
    <div class="{$p}-gallery-grid" data-animate="fade-up">
      <?php for (\$i = 1; \$i <= 6; \$i++): ?>
        <?php \$img = theme_get("gallery.image{\$i}", ''); \$cap = theme_get("gallery.caption{\$i}", ''); ?>
        <?php if (\$img): ?>
        <div class="{$p}-gallery-item">
          <div class="{$p}-gallery-item-inner">
            <img src="<?= esc(\$img) ?>" alt="<?= esc(\$cap) ?>" class="{$p}-gallery-img" loading="lazy" data-ts-bg="gallery.image<?= \$i ?>">
            <?php if (\$cap): ?><div class="{$p}-gallery-caption" data-ts="gallery.caption<?= \$i ?>"><?= esc(\$cap) ?></div><?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
  </div>
</section>
HTML,

// ── Grid Masonry: CSS columns masonry layout ──
'grid-masonry' => <<<HTML
<?php
\$galBadge = theme_get('gallery.badge', '');
\$galTitle = theme_get('gallery.title', 'Our Gallery');
\$galSubtitle = theme_get('gallery.subtitle', 'Browse through our collection of work and projects.');
?>
<section class="{$p}-gallery {$p}-gallery--grid-masonry" id="gallery">
  <div class="container">
    <div class="{$p}-gallery-header" data-animate="fade-up">
      <?php if (\$galBadge): ?><span class="{$p}-gallery-badge" data-ts="gallery.badge"><?= esc(\$galBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-gallery-title" data-ts="gallery.title"><?= esc(\$galTitle) ?></h2>
      <p class="{$p}-gallery-subtitle" data-ts="gallery.subtitle"><?= esc(\$galSubtitle) ?></p>
    </div>
    <div class="{$p}-gallery-masonry" data-animate="fade-up">
      <?php for (\$i = 1; \$i <= 6; \$i++): ?>
        <?php \$img = theme_get("gallery.image{\$i}", ''); \$cap = theme_get("gallery.caption{\$i}", ''); ?>
        <?php if (\$img): ?>
        <div class="{$p}-gallery-item">
          <img src="<?= esc(\$img) ?>" alt="<?= esc(\$cap) ?>" class="{$p}-gallery-img" loading="lazy" data-ts-bg="gallery.image<?= \$i ?>">
          <?php if (\$cap): ?><div class="{$p}-gallery-caption" data-ts="gallery.caption<?= \$i ?>"><?= esc(\$cap) ?></div><?php endif; ?>
        </div>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
  </div>
</section>
HTML,

// ── Grid Filterable: Category filter buttons + grid ──
'grid-filterable' => <<<HTML
<?php
\$galBadge = theme_get('gallery.badge', '');
\$galTitle = theme_get('gallery.title', 'Our Gallery');
\$galSubtitle = theme_get('gallery.subtitle', 'Browse through our collection of work and projects.');
\$galCat1 = theme_get('gallery.cat1', 'All');
\$galCat2 = theme_get('gallery.cat2', 'Design');
\$galCat3 = theme_get('gallery.cat3', 'Branding');
\$galCat4 = theme_get('gallery.cat4', 'Web');
?>
<section class="{$p}-gallery {$p}-gallery--grid-filterable" id="gallery">
  <div class="container">
    <div class="{$p}-gallery-header" data-animate="fade-up">
      <?php if (\$galBadge): ?><span class="{$p}-gallery-badge" data-ts="gallery.badge"><?= esc(\$galBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-gallery-title" data-ts="gallery.title"><?= esc(\$galTitle) ?></h2>
      <p class="{$p}-gallery-subtitle" data-ts="gallery.subtitle"><?= esc(\$galSubtitle) ?></p>
    </div>
    <div class="{$p}-gallery-filters" data-animate="fade-up">
      <button class="{$p}-gallery-filter active" data-filter="all" data-ts="gallery.cat1"><?= esc(\$galCat1) ?></button>
      <button class="{$p}-gallery-filter" data-filter="cat2" data-ts="gallery.cat2"><?= esc(\$galCat2) ?></button>
      <button class="{$p}-gallery-filter" data-filter="cat3" data-ts="gallery.cat3"><?= esc(\$galCat3) ?></button>
      <button class="{$p}-gallery-filter" data-filter="cat4" data-ts="gallery.cat4"><?= esc(\$galCat4) ?></button>
    </div>
    <div class="{$p}-gallery-grid" data-animate="fade-up">
      <?php for (\$i = 1; \$i <= 6; \$i++): ?>
        <?php \$img = theme_get("gallery.image{\$i}", ''); \$cap = theme_get("gallery.caption{\$i}", ''); ?>
        <?php if (\$img): ?>
        <div class="{$p}-gallery-item" data-category="cat<?= ((\$i - 1) % 3) + 2 ?>">
          <div class="{$p}-gallery-item-inner">
            <img src="<?= esc(\$img) ?>" alt="<?= esc(\$cap) ?>" class="{$p}-gallery-img" loading="lazy" data-ts-bg="gallery.image<?= \$i ?>">
            <?php if (\$cap): ?><div class="{$p}-gallery-caption" data-ts="gallery.caption<?= \$i ?>"><?= esc(\$cap) ?></div><?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
  </div>
</section>
HTML,

// ── Showcase Featured: 1 large image + 4 thumbnails ──
'showcase-featured' => <<<HTML
<?php
\$galBadge = theme_get('gallery.badge', '');
\$galTitle = theme_get('gallery.title', 'Our Gallery');
\$galSubtitle = theme_get('gallery.subtitle', 'Browse through our collection of work and projects.');
\$galImg1 = theme_get('gallery.image1', '');
\$galCap1 = theme_get('gallery.caption1', '');
?>
<section class="{$p}-gallery {$p}-gallery--showcase-featured" id="gallery">
  <div class="container">
    <div class="{$p}-gallery-header" data-animate="fade-up">
      <?php if (\$galBadge): ?><span class="{$p}-gallery-badge" data-ts="gallery.badge"><?= esc(\$galBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-gallery-title" data-ts="gallery.title"><?= esc(\$galTitle) ?></h2>
      <p class="{$p}-gallery-subtitle" data-ts="gallery.subtitle"><?= esc(\$galSubtitle) ?></p>
    </div>
    <div class="{$p}-gallery-featured-layout" data-animate="fade-up">
      <?php if (\$galImg1): ?>
      <div class="{$p}-gallery-featured-main">
        <img src="<?= esc(\$galImg1) ?>" alt="<?= esc(\$galCap1) ?>" class="{$p}-gallery-img" loading="lazy" data-ts-bg="gallery.image1">
        <?php if (\$galCap1): ?><div class="{$p}-gallery-caption" data-ts="gallery.caption1"><?= esc(\$galCap1) ?></div><?php endif; ?>
      </div>
      <?php endif; ?>
      <div class="{$p}-gallery-featured-thumbs">
        <?php for (\$i = 2; \$i <= 5; \$i++): ?>
          <?php \$img = theme_get("gallery.image{\$i}", ''); \$cap = theme_get("gallery.caption{\$i}", ''); ?>
          <?php if (\$img): ?>
          <div class="{$p}-gallery-thumb">
            <img src="<?= esc(\$img) ?>" alt="<?= esc(\$cap) ?>" class="{$p}-gallery-img" loading="lazy" data-ts-bg="gallery.image<?= \$i ?>">
          </div>
          <?php endif; ?>
        <?php endfor; ?>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Showcase Carousel: Horizontal scroll gallery ──
'showcase-carousel' => <<<HTML
<?php
\$galBadge = theme_get('gallery.badge', '');
\$galTitle = theme_get('gallery.title', 'Our Gallery');
\$galSubtitle = theme_get('gallery.subtitle', 'Browse through our collection of work and projects.');
?>
<section class="{$p}-gallery {$p}-gallery--showcase-carousel" id="gallery">
  <div class="container">
    <div class="{$p}-gallery-header" data-animate="fade-up">
      <?php if (\$galBadge): ?><span class="{$p}-gallery-badge" data-ts="gallery.badge"><?= esc(\$galBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-gallery-title" data-ts="gallery.title"><?= esc(\$galTitle) ?></h2>
      <p class="{$p}-gallery-subtitle" data-ts="gallery.subtitle"><?= esc(\$galSubtitle) ?></p>
    </div>
  </div>
  <div class="{$p}-gallery-carousel" data-animate="fade-up">
    <div class="{$p}-gallery-carousel-track">
      <?php for (\$i = 1; \$i <= 6; \$i++): ?>
        <?php \$img = theme_get("gallery.image{\$i}", ''); \$cap = theme_get("gallery.caption{\$i}", ''); ?>
        <?php if (\$img): ?>
        <div class="{$p}-gallery-carousel-slide">
          <img src="<?= esc(\$img) ?>" alt="<?= esc(\$cap) ?>" class="{$p}-gallery-img" loading="lazy" data-ts-bg="gallery.image<?= \$i ?>">
          <?php if (\$cap): ?><div class="{$p}-gallery-caption" data-ts="gallery.caption<?= \$i ?>"><?= esc(\$cap) ?></div><?php endif; ?>
        </div>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
  </div>
</section>
HTML,

// ── Showcase Lightbox: Grid with lightbox overlay on click ──
'showcase-lightbox' => <<<HTML
<?php
\$galBadge = theme_get('gallery.badge', '');
\$galTitle = theme_get('gallery.title', 'Our Gallery');
\$galSubtitle = theme_get('gallery.subtitle', 'Browse through our collection of work and projects.');
?>
<section class="{$p}-gallery {$p}-gallery--showcase-lightbox" id="gallery">
  <div class="container">
    <div class="{$p}-gallery-header" data-animate="fade-up">
      <?php if (\$galBadge): ?><span class="{$p}-gallery-badge" data-ts="gallery.badge"><?= esc(\$galBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-gallery-title" data-ts="gallery.title"><?= esc(\$galTitle) ?></h2>
      <p class="{$p}-gallery-subtitle" data-ts="gallery.subtitle"><?= esc(\$galSubtitle) ?></p>
    </div>
    <div class="{$p}-gallery-grid" data-animate="fade-up">
      <?php for (\$i = 1; \$i <= 6; \$i++): ?>
        <?php \$img = theme_get("gallery.image{\$i}", ''); \$cap = theme_get("gallery.caption{\$i}", ''); ?>
        <?php if (\$img): ?>
        <div class="{$p}-gallery-item" data-lightbox="<?= esc(\$img) ?>">
          <div class="{$p}-gallery-item-inner">
            <img src="<?= esc(\$img) ?>" alt="<?= esc(\$cap) ?>" class="{$p}-gallery-img" loading="lazy" data-ts-bg="gallery.image<?= \$i ?>">
            <div class="{$p}-gallery-overlay">
              <i class="fas fa-search-plus"></i>
              <?php if (\$cap): ?><span class="{$p}-gallery-overlay-caption" data-ts="gallery.caption<?= \$i ?>"><?= esc(\$cap) ?></span><?php endif; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
  </div>
</section>
HTML,

// ── Creative Mosaic: Mixed-size tiles ──
'creative-mosaic' => <<<HTML
<?php
\$galBadge = theme_get('gallery.badge', '');
\$galTitle = theme_get('gallery.title', 'Our Gallery');
\$galSubtitle = theme_get('gallery.subtitle', 'Browse through our collection of work and projects.');
?>
<section class="{$p}-gallery {$p}-gallery--creative-mosaic" id="gallery">
  <div class="container">
    <div class="{$p}-gallery-header" data-animate="fade-up">
      <?php if (\$galBadge): ?><span class="{$p}-gallery-badge" data-ts="gallery.badge"><?= esc(\$galBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-gallery-title" data-ts="gallery.title"><?= esc(\$galTitle) ?></h2>
      <p class="{$p}-gallery-subtitle" data-ts="gallery.subtitle"><?= esc(\$galSubtitle) ?></p>
    </div>
    <div class="{$p}-gallery-mosaic" data-animate="fade-up">
      <?php for (\$i = 1; \$i <= 6; \$i++): ?>
        <?php \$img = theme_get("gallery.image{\$i}", ''); \$cap = theme_get("gallery.caption{\$i}", ''); ?>
        <?php if (\$img): ?>
        <div class="{$p}-gallery-mosaic-item {$p}-gallery-mosaic-item--<?= \$i ?>">
          <img src="<?= esc(\$img) ?>" alt="<?= esc(\$cap) ?>" class="{$p}-gallery-img" loading="lazy" data-ts-bg="gallery.image<?= \$i ?>">
          <?php if (\$cap): ?><div class="{$p}-gallery-caption" data-ts="gallery.caption<?= \$i ?>"><?= esc(\$cap) ?></div><?php endif; ?>
        </div>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
  </div>
</section>
HTML,

// ── Creative Before-After: Side-by-side comparison pairs ──
'creative-before-after' => <<<HTML
<?php
\$galBadge = theme_get('gallery.badge', '');
\$galTitle = theme_get('gallery.title', 'Our Gallery');
\$galSubtitle = theme_get('gallery.subtitle', 'Browse through our collection of work and projects.');
?>
<section class="{$p}-gallery {$p}-gallery--creative-before-after" id="gallery">
  <div class="container">
    <div class="{$p}-gallery-header" data-animate="fade-up">
      <?php if (\$galBadge): ?><span class="{$p}-gallery-badge" data-ts="gallery.badge"><?= esc(\$galBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-gallery-title" data-ts="gallery.title"><?= esc(\$galTitle) ?></h2>
      <p class="{$p}-gallery-subtitle" data-ts="gallery.subtitle"><?= esc(\$galSubtitle) ?></p>
    </div>
    <div class="{$p}-gallery-comparisons" data-animate="fade-up">
      <?php for (\$i = 1; \$i <= 6; \$i += 2): ?>
        <?php
          \$imgBefore = theme_get("gallery.image{\$i}", '');
          \$imgAfter  = theme_get("gallery.image" . (\$i + 1), '');
          \$capBefore = theme_get("gallery.caption{\$i}", '');
          \$capAfter  = theme_get("gallery.caption" . (\$i + 1), '');
        ?>
        <?php if (\$imgBefore && \$imgAfter): ?>
        <div class="{$p}-gallery-comparison">
          <div class="{$p}-gallery-comparison-before">
            <span class="{$p}-gallery-comparison-label">Before</span>
            <img src="<?= esc(\$imgBefore) ?>" alt="<?= esc(\$capBefore) ?>" class="{$p}-gallery-img" loading="lazy" data-ts-bg="gallery.image<?= \$i ?>">
            <?php if (\$capBefore): ?><div class="{$p}-gallery-caption" data-ts="gallery.caption<?= \$i ?>"><?= esc(\$capBefore) ?></div><?php endif; ?>
          </div>
          <div class="{$p}-gallery-comparison-after">
            <span class="{$p}-gallery-comparison-label">After</span>
            <img src="<?= esc(\$imgAfter) ?>" alt="<?= esc(\$capAfter) ?>" class="{$p}-gallery-img" loading="lazy" data-ts-bg="gallery.image<?= \$i + 1 ?>">
            <?php if (\$capAfter): ?><div class="{$p}-gallery-caption" data-ts="gallery.caption<?= \$i + 1 ?>"><?= esc(\$capAfter) ?></div><?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      <?php endfor; ?>
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
        $base = ["{$p}-gallery", "{$p}-gallery-header", "{$p}-gallery-badge", "{$p}-gallery-title",
                 "{$p}-gallery-subtitle", "{$p}-gallery-img", "{$p}-gallery-caption"];

        $extra = match($patternId) {
            'grid-3col' => ["{$p}-gallery-grid", "{$p}-gallery-item", "{$p}-gallery-item-inner"],
            'grid-masonry' => ["{$p}-gallery-masonry", "{$p}-gallery-item"],
            'grid-filterable' => ["{$p}-gallery-filters", "{$p}-gallery-filter", "{$p}-gallery-grid", "{$p}-gallery-item", "{$p}-gallery-item-inner"],
            'showcase-featured' => ["{$p}-gallery-featured-layout", "{$p}-gallery-featured-main", "{$p}-gallery-featured-thumbs", "{$p}-gallery-thumb"],
            'showcase-carousel' => ["{$p}-gallery-carousel", "{$p}-gallery-carousel-track", "{$p}-gallery-carousel-slide"],
            'showcase-lightbox' => ["{$p}-gallery-grid", "{$p}-gallery-item", "{$p}-gallery-item-inner", "{$p}-gallery-overlay", "{$p}-gallery-overlay-caption"],
            'creative-mosaic' => ["{$p}-gallery-mosaic", "{$p}-gallery-mosaic-item"],
            'creative-before-after' => ["{$p}-gallery-comparisons", "{$p}-gallery-comparison", "{$p}-gallery-comparison-before", "{$p}-gallery-comparison-after", "{$p}-gallery-comparison-label"],
            default => [],
        };

        return array_merge($base, $extra);
    }

    private static function buildStructuralCSS(string $cssType, string $p): string
    {
        $css = self::css_base($p);

        $typeCSS = match($cssType) {
            'grid-3col'              => self::css_grid_3col($p),
            'grid-masonry'           => self::css_grid_masonry($p),
            'grid-filterable'        => self::css_grid_filterable($p),
            'showcase-featured'      => self::css_showcase_featured($p),
            'showcase-carousel'      => self::css_showcase_carousel($p),
            'showcase-lightbox'      => self::css_showcase_lightbox($p),
            'creative-mosaic'        => self::css_creative_mosaic($p),
            'creative-before-after'  => self::css_creative_before_after($p),
            default                  => self::css_grid_3col($p),
        };

        return $css . $typeCSS . self::css_responsive($p);
    }

    // --- Base (shared by all gallery patterns) ---
    private static function css_base(string $p): string
    {
        return <<<CSS

/* ═══ Gallery Structural CSS (auto-generated — do not edit) ═══ */
.{$p}-gallery {
  position: relative; overflow: hidden;
  padding: clamp(60px, 10vh, 120px) 0;
}
.{$p}-gallery .container {
  position: relative; z-index: 2;
}
.{$p}-gallery-header {
  text-align: center; max-width: 650px;
  margin: 0 auto clamp(32px, 5vw, 56px) auto;
}
.{$p}-gallery-badge {
  display: inline-block;
  font-size: 0.8125rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.12em;
  padding: 6px 16px; border-radius: 100px;
  margin-bottom: 16px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.15);
  color: var(--primary, #3b82f6);
  border: 1px solid rgba(var(--primary-rgb, 42,125,225), 0.25);
}
.{$p}-gallery-title {
  font-family: var(--font-heading, inherit);
  font-size: clamp(1.75rem, 4vw, 3rem);
  font-weight: 700; line-height: 1.15;
  margin: 0 0 16px 0;
  color: var(--text, #1e293b);
}
.{$p}-gallery-subtitle {
  font-size: clamp(1rem, 1.5vw, 1.125rem);
  line-height: 1.7; margin: 0;
  color: var(--text-muted, #64748b);
  max-width: 50ch; margin-left: auto; margin-right: auto;
}
.{$p}-gallery-img {
  width: 100%; height: 100%; object-fit: cover;
  display: block; border-radius: var(--radius, 8px);
}
.{$p}-gallery-caption {
  font-size: 0.875rem; color: var(--text-muted, #64748b);
  padding: 8px 0; text-align: center;
}

CSS;
    }

    // --- Grid 3-Column ---
    private static function css_grid_3col(string $p): string
    {
        return <<<CSS
.{$p}-gallery--grid-3col .{$p}-gallery-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: clamp(12px, 2vw, 24px);
}
.{$p}-gallery--grid-3col .{$p}-gallery-item-inner {
  position: relative; overflow: hidden;
  border-radius: var(--radius, 8px);
  aspect-ratio: 1;
}
.{$p}-gallery--grid-3col .{$p}-gallery-img {
  transition: transform 0.4s ease;
  border-radius: 0;
}
.{$p}-gallery--grid-3col .{$p}-gallery-item:hover .{$p}-gallery-img {
  transform: scale(1.08);
}
.{$p}-gallery--grid-3col .{$p}-gallery-caption {
  position: absolute; bottom: 0; left: 0; right: 0;
  padding: 12px 16px; text-align: left;
  background: linear-gradient(transparent, rgba(0,0,0,0.7));
  color: #fff; border-radius: 0;
  transform: translateY(100%);
  transition: transform 0.3s ease;
}
.{$p}-gallery--grid-3col .{$p}-gallery-item:hover .{$p}-gallery-caption {
  transform: translateY(0);
}

CSS;
    }

    // --- Grid Masonry ---
    private static function css_grid_masonry(string $p): string
    {
        return <<<CSS
.{$p}-gallery--grid-masonry .{$p}-gallery-masonry {
  columns: 3; column-gap: clamp(12px, 2vw, 24px);
}
.{$p}-gallery--grid-masonry .{$p}-gallery-item {
  break-inside: avoid; margin-bottom: clamp(12px, 2vw, 24px);
  border-radius: var(--radius, 8px); overflow: hidden;
}
.{$p}-gallery--grid-masonry .{$p}-gallery-img {
  border-radius: 0;
  height: auto;
  transition: transform 0.4s ease;
}
.{$p}-gallery--grid-masonry .{$p}-gallery-item:hover .{$p}-gallery-img {
  transform: scale(1.05);
}

CSS;
    }

    // --- Grid Filterable ---
    private static function css_grid_filterable(string $p): string
    {
        return <<<CSS
.{$p}-gallery--grid-filterable .{$p}-gallery-filters {
  display: flex; flex-wrap: wrap; gap: 8px;
  justify-content: center; margin-bottom: clamp(24px, 4vw, 40px);
}
.{$p}-gallery-filter {
  padding: 10px 24px; border-radius: 100px;
  border: 2px solid rgba(var(--text-rgb, 30,41,59), 0.15);
  background: transparent; cursor: pointer;
  font-size: 0.875rem; font-weight: 600;
  color: var(--text-muted, #64748b);
  transition: all 0.3s ease;
}
.{$p}-gallery-filter:hover,
.{$p}-gallery-filter.active {
  background: var(--primary, #3b82f6);
  color: var(--primary-contrast, #fff);
  border-color: var(--primary, #3b82f6);
}
.{$p}-gallery--grid-filterable .{$p}-gallery-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: clamp(12px, 2vw, 24px);
}
.{$p}-gallery--grid-filterable .{$p}-gallery-item-inner {
  position: relative; overflow: hidden;
  border-radius: var(--radius, 8px);
  aspect-ratio: 1;
}
.{$p}-gallery--grid-filterable .{$p}-gallery-img {
  border-radius: 0;
  transition: transform 0.4s ease;
}
.{$p}-gallery--grid-filterable .{$p}-gallery-item:hover .{$p}-gallery-img {
  transform: scale(1.05);
}

CSS;
    }

    // --- Showcase Featured ---
    private static function css_showcase_featured(string $p): string
    {
        return <<<CSS
.{$p}-gallery--showcase-featured .{$p}-gallery-featured-layout {
  display: grid; grid-template-columns: 1.5fr 1fr;
  gap: clamp(12px, 2vw, 24px);
}
.{$p}-gallery--showcase-featured .{$p}-gallery-featured-main {
  position: relative; overflow: hidden;
  border-radius: var(--radius, 8px);
  aspect-ratio: 4/3;
}
.{$p}-gallery--showcase-featured .{$p}-gallery-featured-main .{$p}-gallery-caption {
  position: absolute; bottom: 0; left: 0; right: 0;
  padding: 16px 20px; text-align: left;
  background: linear-gradient(transparent, rgba(0,0,0,0.7));
  color: #fff; margin: 0;
}
.{$p}-gallery--showcase-featured .{$p}-gallery-featured-thumbs {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(8px, 1.5vw, 16px);
}
.{$p}-gallery--showcase-featured .{$p}-gallery-thumb {
  position: relative; overflow: hidden;
  border-radius: var(--radius, 8px);
  aspect-ratio: 1;
}
.{$p}-gallery--showcase-featured .{$p}-gallery-thumb .{$p}-gallery-img {
  transition: transform 0.4s ease;
}
.{$p}-gallery--showcase-featured .{$p}-gallery-thumb:hover .{$p}-gallery-img {
  transform: scale(1.08);
}

CSS;
    }

    // --- Showcase Carousel ---
    private static function css_showcase_carousel(string $p): string
    {
        return <<<CSS
.{$p}-gallery--showcase-carousel .{$p}-gallery-carousel {
  overflow-x: auto; overflow-y: hidden;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
  padding: 0 clamp(16px, 4vw, 40px);
}
.{$p}-gallery--showcase-carousel .{$p}-gallery-carousel-track {
  display: flex; gap: clamp(12px, 2vw, 24px);
  padding-bottom: 16px;
}
.{$p}-gallery--showcase-carousel .{$p}-gallery-carousel-slide {
  flex: 0 0 clamp(260px, 30vw, 400px);
  position: relative; overflow: hidden;
  border-radius: var(--radius, 8px);
  aspect-ratio: 3/4;
}
.{$p}-gallery--showcase-carousel .{$p}-gallery-carousel-slide .{$p}-gallery-img {
  border-radius: 0;
  transition: transform 0.4s ease;
}
.{$p}-gallery--showcase-carousel .{$p}-gallery-carousel-slide:hover .{$p}-gallery-img {
  transform: scale(1.05);
}
.{$p}-gallery--showcase-carousel .{$p}-gallery-caption {
  position: absolute; bottom: 0; left: 0; right: 0;
  padding: 12px 16px; text-align: left;
  background: linear-gradient(transparent, rgba(0,0,0,0.7));
  color: #fff;
}

CSS;
    }

    // --- Showcase Lightbox ---
    private static function css_showcase_lightbox(string $p): string
    {
        return <<<CSS
.{$p}-gallery--showcase-lightbox .{$p}-gallery-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: clamp(12px, 2vw, 24px);
}
.{$p}-gallery--showcase-lightbox .{$p}-gallery-item {
  cursor: pointer;
}
.{$p}-gallery--showcase-lightbox .{$p}-gallery-item-inner {
  position: relative; overflow: hidden;
  border-radius: var(--radius, 8px);
  aspect-ratio: 1;
}
.{$p}-gallery--showcase-lightbox .{$p}-gallery-img {
  border-radius: 0;
  transition: transform 0.4s ease;
}
.{$p}-gallery--showcase-lightbox .{$p}-gallery-overlay {
  position: absolute; inset: 0;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center; gap: 8px;
  background: rgba(0,0,0,0.5);
  opacity: 0; transition: opacity 0.3s ease;
  color: #fff; font-size: 1.5rem;
}
.{$p}-gallery--showcase-lightbox .{$p}-gallery-overlay-caption {
  font-size: 0.875rem; font-weight: 500;
}
.{$p}-gallery--showcase-lightbox .{$p}-gallery-item:hover .{$p}-gallery-img {
  transform: scale(1.08);
}
.{$p}-gallery--showcase-lightbox .{$p}-gallery-item:hover .{$p}-gallery-overlay {
  opacity: 1;
}

CSS;
    }

    // --- Creative Mosaic ---
    private static function css_creative_mosaic(string $p): string
    {
        return <<<CSS
.{$p}-gallery--creative-mosaic .{$p}-gallery-mosaic {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  grid-auto-rows: clamp(120px, 15vw, 200px);
  gap: clamp(8px, 1.5vw, 16px);
}
.{$p}-gallery--creative-mosaic .{$p}-gallery-mosaic-item {
  position: relative; overflow: hidden;
  border-radius: var(--radius, 8px);
}
.{$p}-gallery--creative-mosaic .{$p}-gallery-mosaic-item--1 {
  grid-column: span 2; grid-row: span 2;
}
.{$p}-gallery--creative-mosaic .{$p}-gallery-mosaic-item--2 {
  grid-column: span 1; grid-row: span 1;
}
.{$p}-gallery--creative-mosaic .{$p}-gallery-mosaic-item--3 {
  grid-column: span 1; grid-row: span 1;
}
.{$p}-gallery--creative-mosaic .{$p}-gallery-mosaic-item--4 {
  grid-column: span 1; grid-row: span 2;
}
.{$p}-gallery--creative-mosaic .{$p}-gallery-mosaic-item--5 {
  grid-column: span 1; grid-row: span 1;
}
.{$p}-gallery--creative-mosaic .{$p}-gallery-mosaic-item--6 {
  grid-column: span 2; grid-row: span 1;
}
.{$p}-gallery--creative-mosaic .{$p}-gallery-img {
  border-radius: 0;
  transition: transform 0.4s ease;
}
.{$p}-gallery--creative-mosaic .{$p}-gallery-mosaic-item:hover .{$p}-gallery-img {
  transform: scale(1.05);
}
.{$p}-gallery--creative-mosaic .{$p}-gallery-caption {
  position: absolute; bottom: 0; left: 0; right: 0;
  padding: 12px 16px; text-align: left;
  background: linear-gradient(transparent, rgba(0,0,0,0.7));
  color: #fff;
}

CSS;
    }

    // --- Creative Before-After ---
    private static function css_creative_before_after(string $p): string
    {
        return <<<CSS
.{$p}-gallery--creative-before-after .{$p}-gallery-comparisons {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 500px), 1fr));
  gap: clamp(24px, 4vw, 48px);
}
.{$p}-gallery--creative-before-after .{$p}-gallery-comparison {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(8px, 1.5vw, 16px);
  border-radius: var(--radius, 8px);
  overflow: hidden;
  background: var(--surface, #f8fafc);
  padding: clamp(12px, 2vw, 20px);
}
.{$p}-gallery--creative-before-after .{$p}-gallery-comparison-before,
.{$p}-gallery--creative-before-after .{$p}-gallery-comparison-after {
  position: relative; overflow: hidden;
  border-radius: var(--radius, 8px);
}
.{$p}-gallery--creative-before-after .{$p}-gallery-comparison-label {
  position: absolute; top: 12px; left: 12px; z-index: 2;
  display: inline-block;
  padding: 4px 14px; border-radius: 100px;
  font-size: 0.75rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.08em;
  background: var(--primary, #3b82f6);
  color: var(--primary-contrast, #fff);
}
.{$p}-gallery--creative-before-after .{$p}-gallery-img {
  border-radius: 0;
  aspect-ratio: 4/3;
}
.{$p}-gallery--creative-before-after .{$p}-gallery-caption {
  text-align: left; padding: 8px 4px;
}

CSS;
    }

    // --- Responsive ---
    private static function css_responsive(string $p): string
    {
        return <<<CSS
@media (max-width: 768px) {
  .{$p}-gallery--grid-3col .{$p}-gallery-grid,
  .{$p}-gallery--grid-filterable .{$p}-gallery-grid,
  .{$p}-gallery--showcase-lightbox .{$p}-gallery-grid {
    grid-template-columns: repeat(2, 1fr) !important;
  }
  .{$p}-gallery--grid-masonry .{$p}-gallery-masonry {
    columns: 2 !important;
  }
  .{$p}-gallery--showcase-featured .{$p}-gallery-featured-layout {
    grid-template-columns: 1fr !important;
  }
  .{$p}-gallery--creative-mosaic .{$p}-gallery-mosaic {
    grid-template-columns: repeat(2, 1fr) !important;
  }
  .{$p}-gallery--creative-mosaic .{$p}-gallery-mosaic-item--1,
  .{$p}-gallery--creative-mosaic .{$p}-gallery-mosaic-item--4,
  .{$p}-gallery--creative-mosaic .{$p}-gallery-mosaic-item--6 {
    grid-column: span 1 !important; grid-row: span 1 !important;
  }
  .{$p}-gallery--creative-before-after .{$p}-gallery-comparison {
    grid-template-columns: 1fr !important;
  }
}
@media (max-width: 480px) {
  .{$p}-gallery--grid-3col .{$p}-gallery-grid,
  .{$p}-gallery--grid-filterable .{$p}-gallery-grid,
  .{$p}-gallery--showcase-lightbox .{$p}-gallery-grid {
    grid-template-columns: 1fr !important;
  }
  .{$p}-gallery--grid-masonry .{$p}-gallery-masonry {
    columns: 1 !important;
  }
  .{$p}-gallery--creative-mosaic .{$p}-gallery-mosaic {
    grid-template-columns: 1fr !important;
  }
}

CSS;
    }
}
