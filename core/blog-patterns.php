<?php
/**
 * Blog Section Pattern Registry
 * 
 * Pre-built Blog HTML layouts with structural CSS.
 * AI generates only decorative CSS (colors, fonts, effects) — never the structure.
 * 
 * 8 patterns across 3 groups.
 * @since 2026-02-19
 */

class BlogPatternRegistry
{
    // ═══════════════════════════════════════
    // PATTERN DEFINITIONS
    // ═══════════════════════════════════════

    private static array $patterns = [
        // --- Grid (card-based layouts) ---
        ['id'=>'grid-3col',           'group'=>'grid',     'css_type'=>'grid-3col',
         'best_for'=>['agency','marketing','blog','magazine','media']],
        ['id'=>'grid-2col',           'group'=>'grid',     'css_type'=>'grid-2col',
         'best_for'=>['education','university','nonprofit','charity','coaching']],
        ['id'=>'grid-masonry',        'group'=>'grid',     'css_type'=>'grid-masonry',
         'best_for'=>['photography','art','fashion','design','creative-agency']],

        // --- List (linear layouts) ---
        ['id'=>'list-horizontal',     'group'=>'list',     'css_type'=>'list-horizontal',
         'best_for'=>['tech','saas','startup','consulting','fintech']],
        ['id'=>'list-minimal',        'group'=>'list',     'css_type'=>'list-minimal',
         'best_for'=>['legal','financial','accounting','insurance']],
        ['id'=>'list-featured',       'group'=>'list',     'css_type'=>'list-featured',
         'best_for'=>['news','podcast','entertainment','music','film']],

        // --- Creative (unique layouts) ---
        ['id'=>'creative-magazine',   'group'=>'creative', 'css_type'=>'creative-magazine',
         'best_for'=>['restaurant','hotel','travel','tourism','luxury']],
        ['id'=>'creative-carousel',   'group'=>'creative', 'css_type'=>'creative-carousel',
         'best_for'=>['ecommerce','marketplace','retail','fitness','sports']],
    ];

    // ═══════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════

    /**
     * Pick the best Blog pattern for an industry.
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
     * Render a Blog pattern.
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
        // Common fields all Blog patterns have
        $common = [
            'title'    => ['type' => 'text',     'label' => 'Section Title'],
            'subtitle' => ['type' => 'textarea', 'label' => 'Section Subtitle'],
            'badge'    => ['type' => 'text',     'label' => 'Badge / Label'],
        ];

        // Per-post fields (3 posts)
        for ($i = 1; $i <= 3; $i++) {
            $common["post{$i}_title"]    = ['type' => 'text',     'label' => "Post {$i} Title"];
            $common["post{$i}_excerpt"]  = ['type' => 'textarea', 'label' => "Post {$i} Excerpt"];
            $common["post{$i}_date"]     = ['type' => 'text',     'label' => "Post {$i} Date"];
            $common["post{$i}_link"]     = ['type' => 'text',     'label' => "Post {$i} Link"];
            $common["post{$i}_category"] = ['type' => 'text',     'label' => "Post {$i} Category"];

            // Image field — skip for list-minimal (no images)
            if ($patternId !== 'list-minimal') {
                $common["post{$i}_image"] = ['type' => 'image', 'label' => "Post {$i} Image"];
            }
        }

        return $common;
    }

    /**
     * Get decorative CSS guide for a pattern (tells AI what visual CSS to write).
     */
    public static function getDecorativeGuide(string $patternId): string
    {
        return match($patternId) {
            'grid-3col' => <<<'GUIDE'
Cards: background: var(--surface), border-radius: var(--radius), box-shadow: 0 4px 20px rgba(0,0,0,0.06).
Hover: transform: translateY(-4px), box-shadow intensifies, image scale(1.05).
Image: aspect-ratio: 16/10, overflow: hidden, transition: transform 0.5s.
Title links: color: var(--text), hover: color: var(--primary), no underline.
GUIDE,
            'grid-2col' => <<<'GUIDE'
Wider cards: more padding, larger image aspect-ratio: 16/9.
More visible excerpt: font-size: 0.9375rem, -webkit-line-clamp: 3.
Spacious body padding: clamp(20px, 3vw, 32px).
Title: font-size: 1.25rem for bigger readable headlines.
GUIDE,
            'grid-masonry' => <<<'GUIDE'
Varied card heights: no fixed aspect-ratio on images, natural heights.
CSS columns layout: staggered visual, break-inside: avoid per card.
Cards: border-radius: var(--radius), box-shadow, hover lift.
Image transitions: scale(1.05) on hover, smooth 0.5s ease.
GUIDE,
            'list-horizontal' => <<<'GUIDE'
Image left (280px), content right: horizontal card layout.
Thin divider: border-bottom or box-shadow between items.
Compact: less padding, tighter spacing, efficient use of space.
Read more link: color: var(--primary), arrow icon animates gap on hover.
GUIDE,
            'list-minimal' => <<<'GUIDE'
No images — text only, clean typographic layout.
Thin bottom borders: 1px solid rgba(var(--text-rgb), 0.08) between items.
Date column: muted, small font, left-aligned.
Title: font-weight: 700, 1.25rem, hover: color: var(--primary).
GUIDE,
            'list-featured' => <<<'GUIDE'
First article: full-width image, large title (1.5rem), visible excerpt.
Category badge: background: rgba(var(--primary-rgb), 0.1), color: var(--primary), small pill.
Sidebar articles: compact, thumbnail left (100px square), title right.
Featured image: hover scale(1.05), smooth transition.
GUIDE,
            'creative-magazine' => <<<'GUIDE'
Hero article: large image with gradient overlay from bottom, text over image in white.
Sidebar articles: smaller cards stacked vertically beside hero.
Editorial feel: serif or elegant heading font, refined spacing.
Hero overlay: linear-gradient(0deg, rgba(0,0,0,0.8) 0%, transparent 100%).
GUIDE,
            'creative-carousel' => <<<'GUIDE'
Horizontal scroll: scroll-snap-type: x mandatory, card snap-align: start.
Cards: flex: 0 0 320px, peek of next card visible at edge.
Smooth scrollbar: scrollbar-width: thin, webkit-overflow-scrolling: touch.
Card hover: translateY(-4px), box-shadow deepens.
GUIDE,
            default => '',
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

        // Title from brief
        $title = $brief['blog_headline'] ?? '';
        if (!$title && $name) {
            $title = "Latest from {$name}";
        }

        // Subtitle from brief
        $subtitle = $brief['blog_subheadline'] ?? '';

        // Badge from industry
        $badge = '';
        if ($industry) {
            $badge = ucwords(str_replace('-', ' ', $industry));
        }

        // Replace defaults in theme_get() calls
        $replacements = [];
        if ($title)    $replacements["theme_get('blog.title', 'Latest Articles')"]                                                 = "theme_get('blog.title', '" . addslashes($title) . "')";
        if ($subtitle) $replacements["theme_get('blog.subtitle', 'Insights, news, and updates from our team.')"]                   = "theme_get('blog.subtitle', '" . addslashes($subtitle) . "')";
        if ($badge)    $replacements["theme_get('blog.badge', '')"]                                                                = "theme_get('blog.badge', '" . addslashes($badge) . "')";

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

// ── Grid 3-Col: 3-column post cards (image, date, title, excerpt) ──
'grid-3col' => <<<HTML
<?php
\$blogBadge = theme_get('blog.badge', '');
\$blogTitle = theme_get('blog.title', 'Latest Articles');
\$blogSubtitle = theme_get('blog.subtitle', 'Insights, news, and updates from our team.');
\$posts = [];
for (\$i = 1; \$i <= 3; \$i++) {
    \$post = [
        'title'    => theme_get("blog.post{\$i}_title", \$i === 1 ? 'Getting Started with Our Platform' : (\$i === 2 ? 'Best Practices for Success' : 'What\'s New This Month')),
        'excerpt'  => theme_get("blog.post{\$i}_excerpt", 'Discover the latest insights and strategies to help you grow.'),
        'image'    => theme_get("blog.post{\$i}_image", ''),
        'date'     => theme_get("blog.post{\$i}_date", \$i === 1 ? 'Jan 15, 2026' : (\$i === 2 ? 'Jan 10, 2026' : 'Jan 5, 2026')),
        'link'     => theme_get("blog.post{\$i}_link", '/blog/article-' . \$i),
        'category' => theme_get("blog.post{\$i}_category", 'News'),
    ];
    if (\$post['title']) \$posts[] = \$post;
}
?>
<section class="{$p}-blog {$p}-blog--grid-3col" id="blog">
  <div class="container">
    <div class="{$p}-blog-header" data-animate="fade-up">
      <?php if (\$blogBadge): ?><span class="{$p}-blog-badge" data-ts="blog.badge"><?= esc(\$blogBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-blog-title" data-ts="blog.title"><?= esc(\$blogTitle) ?></h2>
      <?php if (\$blogSubtitle): ?><p class="{$p}-blog-subtitle" data-ts="blog.subtitle"><?= esc(\$blogSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="{$p}-blog-grid" data-animate="fade-up">
      <?php foreach (\$posts as \$idx => \$post): \$n = \$idx + 1; ?>
      <article class="{$p}-blog-card">
        <?php if (\$post['image']): ?>
          <div class="{$p}-blog-card-image">
            <a href="<?= esc(\$post['link']) ?>" data-ts-href="blog.post<?= \$n ?>_link">
              <img src="<?= esc(\$post['image']) ?>" alt="<?= esc(\$post['title']) ?>" loading="lazy" data-ts="blog.post<?= \$n ?>_image">
            </a>
          </div>
        <?php endif; ?>
        <div class="{$p}-blog-card-body">
          <div class="{$p}-blog-card-meta">
            <?php if (\$post['category']): ?><span class="{$p}-blog-card-category" data-ts="blog.post<?= \$n ?>_category"><?= esc(\$post['category']) ?></span><?php endif; ?>
            <time class="{$p}-blog-card-date" data-ts="blog.post<?= \$n ?>_date"><?= esc(\$post['date']) ?></time>
          </div>
          <h3 class="{$p}-blog-card-title"><a href="<?= esc(\$post['link']) ?>" data-ts="blog.post<?= \$n ?>_title" data-ts-href="blog.post<?= \$n ?>_link"><?= esc(\$post['title']) ?></a></h3>
          <p class="{$p}-blog-card-excerpt" data-ts="blog.post<?= \$n ?>_excerpt"><?= esc(\$post['excerpt']) ?></p>
          <a href="<?= esc(\$post['link']) ?>" class="{$p}-blog-read-more" data-ts-href="blog.post<?= \$n ?>_link">Read More <i class="fas fa-arrow-right"></i></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Grid 2-Col: 2-column, larger cards ──
'grid-2col' => <<<HTML
<?php
\$blogBadge = theme_get('blog.badge', '');
\$blogTitle = theme_get('blog.title', 'Latest Articles');
\$blogSubtitle = theme_get('blog.subtitle', 'Insights, news, and updates from our team.');
\$posts = [];
for (\$i = 1; \$i <= 3; \$i++) {
    \$post = [
        'title'    => theme_get("blog.post{\$i}_title", \$i === 1 ? 'Getting Started with Our Platform' : (\$i === 2 ? 'Best Practices for Success' : 'What\'s New This Month')),
        'excerpt'  => theme_get("blog.post{\$i}_excerpt", 'Discover the latest insights and strategies to help you grow.'),
        'image'    => theme_get("blog.post{\$i}_image", ''),
        'date'     => theme_get("blog.post{\$i}_date", \$i === 1 ? 'Jan 15, 2026' : (\$i === 2 ? 'Jan 10, 2026' : 'Jan 5, 2026')),
        'link'     => theme_get("blog.post{\$i}_link", '/blog/article-' . \$i),
        'category' => theme_get("blog.post{\$i}_category", 'News'),
    ];
    if (\$post['title']) \$posts[] = \$post;
}
?>
<section class="{$p}-blog {$p}-blog--grid-2col" id="blog">
  <div class="container">
    <div class="{$p}-blog-header" data-animate="fade-up">
      <?php if (\$blogBadge): ?><span class="{$p}-blog-badge" data-ts="blog.badge"><?= esc(\$blogBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-blog-title" data-ts="blog.title"><?= esc(\$blogTitle) ?></h2>
      <?php if (\$blogSubtitle): ?><p class="{$p}-blog-subtitle" data-ts="blog.subtitle"><?= esc(\$blogSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="{$p}-blog-grid" data-animate="fade-up">
      <?php foreach (\$posts as \$idx => \$post): \$n = \$idx + 1; ?>
      <article class="{$p}-blog-card">
        <?php if (\$post['image']): ?>
          <div class="{$p}-blog-card-image">
            <a href="<?= esc(\$post['link']) ?>" data-ts-href="blog.post<?= \$n ?>_link">
              <img src="<?= esc(\$post['image']) ?>" alt="<?= esc(\$post['title']) ?>" loading="lazy" data-ts="blog.post<?= \$n ?>_image">
            </a>
          </div>
        <?php endif; ?>
        <div class="{$p}-blog-card-body">
          <div class="{$p}-blog-card-meta">
            <?php if (\$post['category']): ?><span class="{$p}-blog-card-category" data-ts="blog.post<?= \$n ?>_category"><?= esc(\$post['category']) ?></span><?php endif; ?>
            <time class="{$p}-blog-card-date" data-ts="blog.post<?= \$n ?>_date"><?= esc(\$post['date']) ?></time>
          </div>
          <h3 class="{$p}-blog-card-title"><a href="<?= esc(\$post['link']) ?>" data-ts="blog.post<?= \$n ?>_title" data-ts-href="blog.post<?= \$n ?>_link"><?= esc(\$post['title']) ?></a></h3>
          <p class="{$p}-blog-card-excerpt" data-ts="blog.post<?= \$n ?>_excerpt"><?= esc(\$post['excerpt']) ?></p>
          <a href="<?= esc(\$post['link']) ?>" class="{$p}-blog-read-more" data-ts-href="blog.post<?= \$n ?>_link">Read More <i class="fas fa-arrow-right"></i></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Grid Masonry: Masonry mixed heights ──
'grid-masonry' => <<<HTML
<?php
\$blogBadge = theme_get('blog.badge', '');
\$blogTitle = theme_get('blog.title', 'Latest Articles');
\$blogSubtitle = theme_get('blog.subtitle', 'Insights, news, and updates from our team.');
\$posts = [];
for (\$i = 1; \$i <= 3; \$i++) {
    \$post = [
        'title'    => theme_get("blog.post{\$i}_title", \$i === 1 ? 'Getting Started with Our Platform' : (\$i === 2 ? 'Best Practices for Success' : 'What\'s New This Month')),
        'excerpt'  => theme_get("blog.post{\$i}_excerpt", 'Discover the latest insights and strategies to help you grow.'),
        'image'    => theme_get("blog.post{\$i}_image", ''),
        'date'     => theme_get("blog.post{\$i}_date", \$i === 1 ? 'Jan 15, 2026' : (\$i === 2 ? 'Jan 10, 2026' : 'Jan 5, 2026')),
        'link'     => theme_get("blog.post{\$i}_link", '/blog/article-' . \$i),
        'category' => theme_get("blog.post{\$i}_category", 'News'),
    ];
    if (\$post['title']) \$posts[] = \$post;
}
?>
<section class="{$p}-blog {$p}-blog--grid-masonry" id="blog">
  <div class="container">
    <div class="{$p}-blog-header" data-animate="fade-up">
      <?php if (\$blogBadge): ?><span class="{$p}-blog-badge" data-ts="blog.badge"><?= esc(\$blogBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-blog-title" data-ts="blog.title"><?= esc(\$blogTitle) ?></h2>
      <?php if (\$blogSubtitle): ?><p class="{$p}-blog-subtitle" data-ts="blog.subtitle"><?= esc(\$blogSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="{$p}-blog-masonry" data-animate="fade-up">
      <?php foreach (\$posts as \$idx => \$post): \$n = \$idx + 1; ?>
      <article class="{$p}-blog-masonry-item">
        <?php if (\$post['image']): ?>
          <div class="{$p}-blog-masonry-image">
            <a href="<?= esc(\$post['link']) ?>" data-ts-href="blog.post<?= \$n ?>_link">
              <img src="<?= esc(\$post['image']) ?>" alt="<?= esc(\$post['title']) ?>" loading="lazy" data-ts="blog.post<?= \$n ?>_image">
            </a>
          </div>
        <?php endif; ?>
        <div class="{$p}-blog-masonry-body">
          <?php if (\$post['category']): ?><span class="{$p}-blog-card-category" data-ts="blog.post<?= \$n ?>_category"><?= esc(\$post['category']) ?></span><?php endif; ?>
          <h3 class="{$p}-blog-masonry-title"><a href="<?= esc(\$post['link']) ?>" data-ts="blog.post<?= \$n ?>_title" data-ts-href="blog.post<?= \$n ?>_link"><?= esc(\$post['title']) ?></a></h3>
          <p class="{$p}-blog-masonry-excerpt" data-ts="blog.post<?= \$n ?>_excerpt"><?= esc(\$post['excerpt']) ?></p>
          <time class="{$p}-blog-card-date" data-ts="blog.post<?= \$n ?>_date"><?= esc(\$post['date']) ?></time>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── List Horizontal: Horizontal card (image left, text right) ──
'list-horizontal' => <<<HTML
<?php
\$blogBadge = theme_get('blog.badge', '');
\$blogTitle = theme_get('blog.title', 'Latest Articles');
\$blogSubtitle = theme_get('blog.subtitle', 'Insights, news, and updates from our team.');
\$posts = [];
for (\$i = 1; \$i <= 3; \$i++) {
    \$post = [
        'title'    => theme_get("blog.post{\$i}_title", \$i === 1 ? 'Getting Started with Our Platform' : (\$i === 2 ? 'Best Practices for Success' : 'What\'s New This Month')),
        'excerpt'  => theme_get("blog.post{\$i}_excerpt", 'Discover the latest insights and strategies to help you grow.'),
        'image'    => theme_get("blog.post{\$i}_image", ''),
        'date'     => theme_get("blog.post{\$i}_date", \$i === 1 ? 'Jan 15, 2026' : (\$i === 2 ? 'Jan 10, 2026' : 'Jan 5, 2026')),
        'link'     => theme_get("blog.post{\$i}_link", '/blog/article-' . \$i),
        'category' => theme_get("blog.post{\$i}_category", 'News'),
    ];
    if (\$post['title']) \$posts[] = \$post;
}
?>
<section class="{$p}-blog {$p}-blog--list-horizontal" id="blog">
  <div class="container">
    <div class="{$p}-blog-header" data-animate="fade-up">
      <?php if (\$blogBadge): ?><span class="{$p}-blog-badge" data-ts="blog.badge"><?= esc(\$blogBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-blog-title" data-ts="blog.title"><?= esc(\$blogTitle) ?></h2>
      <?php if (\$blogSubtitle): ?><p class="{$p}-blog-subtitle" data-ts="blog.subtitle"><?= esc(\$blogSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="{$p}-blog-list" data-animate="fade-up">
      <?php foreach (\$posts as \$idx => \$post): \$n = \$idx + 1; ?>
      <article class="{$p}-blog-list-item">
        <?php if (\$post['image']): ?>
          <div class="{$p}-blog-list-image">
            <a href="<?= esc(\$post['link']) ?>" data-ts-href="blog.post<?= \$n ?>_link">
              <img src="<?= esc(\$post['image']) ?>" alt="<?= esc(\$post['title']) ?>" loading="lazy" data-ts="blog.post<?= \$n ?>_image">
            </a>
          </div>
        <?php endif; ?>
        <div class="{$p}-blog-list-content">
          <div class="{$p}-blog-card-meta">
            <?php if (\$post['category']): ?><span class="{$p}-blog-card-category" data-ts="blog.post<?= \$n ?>_category"><?= esc(\$post['category']) ?></span><?php endif; ?>
            <time class="{$p}-blog-card-date" data-ts="blog.post<?= \$n ?>_date"><?= esc(\$post['date']) ?></time>
          </div>
          <h3 class="{$p}-blog-list-title"><a href="<?= esc(\$post['link']) ?>" data-ts="blog.post<?= \$n ?>_title" data-ts-href="blog.post<?= \$n ?>_link"><?= esc(\$post['title']) ?></a></h3>
          <p class="{$p}-blog-list-excerpt" data-ts="blog.post<?= \$n ?>_excerpt"><?= esc(\$post['excerpt']) ?></p>
          <a href="<?= esc(\$post['link']) ?>" class="{$p}-blog-read-more" data-ts-href="blog.post<?= \$n ?>_link">Read More <i class="fas fa-arrow-right"></i></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── List Minimal: No images, just title + excerpt + date ──
'list-minimal' => <<<HTML
<?php
\$blogBadge = theme_get('blog.badge', '');
\$blogTitle = theme_get('blog.title', 'Latest Articles');
\$blogSubtitle = theme_get('blog.subtitle', 'Insights, news, and updates from our team.');
\$posts = [];
for (\$i = 1; \$i <= 3; \$i++) {
    \$post = [
        'title'    => theme_get("blog.post{\$i}_title", \$i === 1 ? 'Getting Started with Our Platform' : (\$i === 2 ? 'Best Practices for Success' : 'What\'s New This Month')),
        'excerpt'  => theme_get("blog.post{\$i}_excerpt", 'Discover the latest insights and strategies to help you grow.'),
        'date'     => theme_get("blog.post{\$i}_date", \$i === 1 ? 'Jan 15, 2026' : (\$i === 2 ? 'Jan 10, 2026' : 'Jan 5, 2026')),
        'link'     => theme_get("blog.post{\$i}_link", '/blog/article-' . \$i),
        'category' => theme_get("blog.post{\$i}_category", 'News'),
    ];
    if (\$post['title']) \$posts[] = \$post;
}
?>
<section class="{$p}-blog {$p}-blog--list-minimal" id="blog">
  <div class="container">
    <div class="{$p}-blog-header" data-animate="fade-up">
      <?php if (\$blogBadge): ?><span class="{$p}-blog-badge" data-ts="blog.badge"><?= esc(\$blogBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-blog-title" data-ts="blog.title"><?= esc(\$blogTitle) ?></h2>
      <?php if (\$blogSubtitle): ?><p class="{$p}-blog-subtitle" data-ts="blog.subtitle"><?= esc(\$blogSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="{$p}-blog-minimal-list" data-animate="fade-up">
      <?php foreach (\$posts as \$idx => \$post): \$n = \$idx + 1; ?>
      <article class="{$p}-blog-minimal-item">
        <div class="{$p}-blog-minimal-date">
          <time data-ts="blog.post<?= \$n ?>_date"><?= esc(\$post['date']) ?></time>
        </div>
        <div class="{$p}-blog-minimal-content">
          <?php if (\$post['category']): ?><span class="{$p}-blog-card-category" data-ts="blog.post<?= \$n ?>_category"><?= esc(\$post['category']) ?></span><?php endif; ?>
          <h3 class="{$p}-blog-minimal-title"><a href="<?= esc(\$post['link']) ?>" data-ts="blog.post<?= \$n ?>_title" data-ts-href="blog.post<?= \$n ?>_link"><?= esc(\$post['title']) ?></a></h3>
          <p class="{$p}-blog-minimal-excerpt" data-ts="blog.post<?= \$n ?>_excerpt"><?= esc(\$post['excerpt']) ?></p>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── List Featured: 1 large featured + 3 small list items ──
'list-featured' => <<<HTML
<?php
\$blogBadge = theme_get('blog.badge', '');
\$blogTitle = theme_get('blog.title', 'Latest Articles');
\$blogSubtitle = theme_get('blog.subtitle', 'Insights, news, and updates from our team.');
\$posts = [];
for (\$i = 1; \$i <= 3; \$i++) {
    \$post = [
        'title'    => theme_get("blog.post{\$i}_title", \$i === 1 ? 'Getting Started with Our Platform' : (\$i === 2 ? 'Best Practices for Success' : 'What\'s New This Month')),
        'excerpt'  => theme_get("blog.post{\$i}_excerpt", 'Discover the latest insights and strategies to help you grow.'),
        'image'    => theme_get("blog.post{\$i}_image", ''),
        'date'     => theme_get("blog.post{\$i}_date", \$i === 1 ? 'Jan 15, 2026' : (\$i === 2 ? 'Jan 10, 2026' : 'Jan 5, 2026')),
        'link'     => theme_get("blog.post{\$i}_link", '/blog/article-' . \$i),
        'category' => theme_get("blog.post{\$i}_category", 'News'),
    ];
    if (\$post['title']) \$posts[] = \$post;
}
\$featured = \$posts[0] ?? null;
\$rest = array_slice(\$posts, 1);
?>
<section class="{$p}-blog {$p}-blog--list-featured" id="blog">
  <div class="container">
    <div class="{$p}-blog-header" data-animate="fade-up">
      <?php if (\$blogBadge): ?><span class="{$p}-blog-badge" data-ts="blog.badge"><?= esc(\$blogBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-blog-title" data-ts="blog.title"><?= esc(\$blogTitle) ?></h2>
      <?php if (\$blogSubtitle): ?><p class="{$p}-blog-subtitle" data-ts="blog.subtitle"><?= esc(\$blogSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="{$p}-blog-featured-layout" data-animate="fade-up">
      <?php if (\$featured): ?>
      <article class="{$p}-blog-featured-main">
        <?php if (\$featured['image']): ?>
          <div class="{$p}-blog-featured-image">
            <a href="<?= esc(\$featured['link']) ?>" data-ts-href="blog.post1_link">
              <img src="<?= esc(\$featured['image']) ?>" alt="<?= esc(\$featured['title']) ?>" loading="lazy" data-ts="blog.post1_image">
            </a>
          </div>
        <?php endif; ?>
        <div class="{$p}-blog-featured-body">
          <div class="{$p}-blog-card-meta">
            <?php if (\$featured['category']): ?><span class="{$p}-blog-card-category" data-ts="blog.post1_category"><?= esc(\$featured['category']) ?></span><?php endif; ?>
            <time class="{$p}-blog-card-date" data-ts="blog.post1_date"><?= esc(\$featured['date']) ?></time>
          </div>
          <h3 class="{$p}-blog-featured-title"><a href="<?= esc(\$featured['link']) ?>" data-ts="blog.post1_title" data-ts-href="blog.post1_link"><?= esc(\$featured['title']) ?></a></h3>
          <p class="{$p}-blog-featured-excerpt" data-ts="blog.post1_excerpt"><?= esc(\$featured['excerpt']) ?></p>
          <a href="<?= esc(\$featured['link']) ?>" class="{$p}-blog-read-more" data-ts-href="blog.post1_link">Read More <i class="fas fa-arrow-right"></i></a>
        </div>
      </article>
      <?php endif; ?>
      <?php if (\$rest): ?>
      <div class="{$p}-blog-featured-sidebar">
        <?php foreach (\$rest as \$idx => \$post): \$n = \$idx + 2; ?>
        <article class="{$p}-blog-featured-side-item">
          <?php if (\$post['image']): ?>
            <div class="{$p}-blog-featured-side-image">
              <a href="<?= esc(\$post['link']) ?>" data-ts-href="blog.post<?= \$n ?>_link">
                <img src="<?= esc(\$post['image']) ?>" alt="<?= esc(\$post['title']) ?>" loading="lazy" data-ts="blog.post<?= \$n ?>_image">
              </a>
            </div>
          <?php endif; ?>
          <div class="{$p}-blog-featured-side-content">
            <time class="{$p}-blog-card-date" data-ts="blog.post<?= \$n ?>_date"><?= esc(\$post['date']) ?></time>
            <h4 class="{$p}-blog-featured-side-title"><a href="<?= esc(\$post['link']) ?>" data-ts="blog.post<?= \$n ?>_title" data-ts-href="blog.post<?= \$n ?>_link"><?= esc(\$post['title']) ?></a></h4>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
HTML,

// ── Creative Magazine: Magazine layout (big + small cards mixed) ──
'creative-magazine' => <<<HTML
<?php
\$blogBadge = theme_get('blog.badge', '');
\$blogTitle = theme_get('blog.title', 'Latest Articles');
\$blogSubtitle = theme_get('blog.subtitle', 'Insights, news, and updates from our team.');
\$posts = [];
for (\$i = 1; \$i <= 3; \$i++) {
    \$post = [
        'title'    => theme_get("blog.post{\$i}_title", \$i === 1 ? 'Getting Started with Our Platform' : (\$i === 2 ? 'Best Practices for Success' : 'What\'s New This Month')),
        'excerpt'  => theme_get("blog.post{\$i}_excerpt", 'Discover the latest insights and strategies to help you grow.'),
        'image'    => theme_get("blog.post{\$i}_image", ''),
        'date'     => theme_get("blog.post{\$i}_date", \$i === 1 ? 'Jan 15, 2026' : (\$i === 2 ? 'Jan 10, 2026' : 'Jan 5, 2026')),
        'link'     => theme_get("blog.post{\$i}_link", '/blog/article-' . \$i),
        'category' => theme_get("blog.post{\$i}_category", 'News'),
    ];
    if (\$post['title']) \$posts[] = \$post;
}
\$mainPost = \$posts[0] ?? null;
\$sidePosts = array_slice(\$posts, 1);
?>
<section class="{$p}-blog {$p}-blog--creative-magazine" id="blog">
  <div class="container">
    <div class="{$p}-blog-header" data-animate="fade-up">
      <?php if (\$blogBadge): ?><span class="{$p}-blog-badge" data-ts="blog.badge"><?= esc(\$blogBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-blog-title" data-ts="blog.title"><?= esc(\$blogTitle) ?></h2>
      <?php if (\$blogSubtitle): ?><p class="{$p}-blog-subtitle" data-ts="blog.subtitle"><?= esc(\$blogSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="{$p}-blog-magazine" data-animate="fade-up">
      <?php if (\$mainPost): ?>
      <article class="{$p}-blog-magazine-hero">
        <?php if (\$mainPost['image']): ?>
          <div class="{$p}-blog-magazine-hero-image">
            <a href="<?= esc(\$mainPost['link']) ?>" data-ts-href="blog.post1_link">
              <img src="<?= esc(\$mainPost['image']) ?>" alt="<?= esc(\$mainPost['title']) ?>" loading="lazy" data-ts="blog.post1_image">
            </a>
            <div class="{$p}-blog-magazine-hero-overlay">
              <div class="{$p}-blog-card-meta">
                <?php if (\$mainPost['category']): ?><span class="{$p}-blog-card-category" data-ts="blog.post1_category"><?= esc(\$mainPost['category']) ?></span><?php endif; ?>
                <time class="{$p}-blog-card-date" data-ts="blog.post1_date"><?= esc(\$mainPost['date']) ?></time>
              </div>
              <h3 class="{$p}-blog-magazine-hero-title"><a href="<?= esc(\$mainPost['link']) ?>" data-ts="blog.post1_title" data-ts-href="blog.post1_link"><?= esc(\$mainPost['title']) ?></a></h3>
              <p class="{$p}-blog-magazine-hero-excerpt" data-ts="blog.post1_excerpt"><?= esc(\$mainPost['excerpt']) ?></p>
            </div>
          </div>
        <?php endif; ?>
      </article>
      <?php endif; ?>
      <?php if (\$sidePosts): ?>
      <div class="{$p}-blog-magazine-side">
        <?php foreach (\$sidePosts as \$idx => \$post): \$n = \$idx + 2; ?>
        <article class="{$p}-blog-magazine-card">
          <?php if (\$post['image']): ?>
            <div class="{$p}-blog-magazine-card-image">
              <a href="<?= esc(\$post['link']) ?>" data-ts-href="blog.post<?= \$n ?>_link">
                <img src="<?= esc(\$post['image']) ?>" alt="<?= esc(\$post['title']) ?>" loading="lazy" data-ts="blog.post<?= \$n ?>_image">
              </a>
            </div>
          <?php endif; ?>
          <div class="{$p}-blog-magazine-card-body">
            <?php if (\$post['category']): ?><span class="{$p}-blog-card-category" data-ts="blog.post<?= \$n ?>_category"><?= esc(\$post['category']) ?></span><?php endif; ?>
            <h4 class="{$p}-blog-magazine-card-title"><a href="<?= esc(\$post['link']) ?>" data-ts="blog.post<?= \$n ?>_title" data-ts-href="blog.post<?= \$n ?>_link"><?= esc(\$post['title']) ?></a></h4>
            <time class="{$p}-blog-card-date" data-ts="blog.post<?= \$n ?>_date"><?= esc(\$post['date']) ?></time>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
HTML,

// ── Creative Carousel: Horizontal scrollable post cards ──
'creative-carousel' => <<<HTML
<?php
\$blogBadge = theme_get('blog.badge', '');
\$blogTitle = theme_get('blog.title', 'Latest Articles');
\$blogSubtitle = theme_get('blog.subtitle', 'Insights, news, and updates from our team.');
\$posts = [];
for (\$i = 1; \$i <= 3; \$i++) {
    \$post = [
        'title'    => theme_get("blog.post{\$i}_title", \$i === 1 ? 'Getting Started with Our Platform' : (\$i === 2 ? 'Best Practices for Success' : 'What\'s New This Month')),
        'excerpt'  => theme_get("blog.post{\$i}_excerpt", 'Discover the latest insights and strategies to help you grow.'),
        'image'    => theme_get("blog.post{\$i}_image", ''),
        'date'     => theme_get("blog.post{\$i}_date", \$i === 1 ? 'Jan 15, 2026' : (\$i === 2 ? 'Jan 10, 2026' : 'Jan 5, 2026')),
        'link'     => theme_get("blog.post{\$i}_link", '/blog/article-' . \$i),
        'category' => theme_get("blog.post{\$i}_category", 'News'),
    ];
    if (\$post['title']) \$posts[] = \$post;
}
?>
<section class="{$p}-blog {$p}-blog--creative-carousel" id="blog">
  <div class="container">
    <div class="{$p}-blog-header" data-animate="fade-up">
      <?php if (\$blogBadge): ?><span class="{$p}-blog-badge" data-ts="blog.badge"><?= esc(\$blogBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-blog-title" data-ts="blog.title"><?= esc(\$blogTitle) ?></h2>
      <?php if (\$blogSubtitle): ?><p class="{$p}-blog-subtitle" data-ts="blog.subtitle"><?= esc(\$blogSubtitle) ?></p><?php endif; ?>
    </div>
  </div>
  <div class="{$p}-blog-carousel" data-animate="fade-up">
    <div class="{$p}-blog-carousel-track">
      <?php foreach (\$posts as \$idx => \$post): \$n = \$idx + 1; ?>
      <article class="{$p}-blog-carousel-card">
        <?php if (\$post['image']): ?>
          <div class="{$p}-blog-carousel-image">
            <a href="<?= esc(\$post['link']) ?>" data-ts-href="blog.post<?= \$n ?>_link">
              <img src="<?= esc(\$post['image']) ?>" alt="<?= esc(\$post['title']) ?>" loading="lazy" data-ts="blog.post<?= \$n ?>_image">
            </a>
          </div>
        <?php endif; ?>
        <div class="{$p}-blog-carousel-body">
          <div class="{$p}-blog-card-meta">
            <?php if (\$post['category']): ?><span class="{$p}-blog-card-category" data-ts="blog.post<?= \$n ?>_category"><?= esc(\$post['category']) ?></span><?php endif; ?>
            <time class="{$p}-blog-card-date" data-ts="blog.post<?= \$n ?>_date"><?= esc(\$post['date']) ?></time>
          </div>
          <h3 class="{$p}-blog-carousel-title"><a href="<?= esc(\$post['link']) ?>" data-ts="blog.post<?= \$n ?>_title" data-ts-href="blog.post<?= \$n ?>_link"><?= esc(\$post['title']) ?></a></h3>
          <p class="{$p}-blog-carousel-excerpt" data-ts="blog.post<?= \$n ?>_excerpt"><?= esc(\$post['excerpt']) ?></p>
          <a href="<?= esc(\$post['link']) ?>" class="{$p}-blog-read-more" data-ts-href="blog.post<?= \$n ?>_link">Read More <i class="fas fa-arrow-right"></i></a>
        </div>
      </article>
      <?php endforeach; ?>
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
        $base = ["{$p}-blog", "{$p}-blog-header", "{$p}-blog-badge", "{$p}-blog-title",
                 "{$p}-blog-subtitle", "{$p}-blog-card-meta", "{$p}-blog-card-category",
                 "{$p}-blog-card-date", "{$p}-blog-read-more"];

        $extra = match($patternId) {
            'grid-3col', 'grid-2col' => ["{$p}-blog-grid", "{$p}-blog-card",
                                          "{$p}-blog-card-image", "{$p}-blog-card-body",
                                          "{$p}-blog-card-title", "{$p}-blog-card-excerpt"],
            'grid-masonry' => ["{$p}-blog-masonry", "{$p}-blog-masonry-item",
                               "{$p}-blog-masonry-image", "{$p}-blog-masonry-body",
                               "{$p}-blog-masonry-title", "{$p}-blog-masonry-excerpt"],
            'list-horizontal' => ["{$p}-blog-list", "{$p}-blog-list-item",
                                  "{$p}-blog-list-image", "{$p}-blog-list-content",
                                  "{$p}-blog-list-title", "{$p}-blog-list-excerpt"],
            'list-minimal' => ["{$p}-blog-minimal-list", "{$p}-blog-minimal-item",
                               "{$p}-blog-minimal-date", "{$p}-blog-minimal-content",
                               "{$p}-blog-minimal-title", "{$p}-blog-minimal-excerpt"],
            'list-featured' => ["{$p}-blog-featured-layout", "{$p}-blog-featured-main",
                                "{$p}-blog-featured-image", "{$p}-blog-featured-body",
                                "{$p}-blog-featured-title", "{$p}-blog-featured-excerpt",
                                "{$p}-blog-featured-sidebar", "{$p}-blog-featured-side-item",
                                "{$p}-blog-featured-side-image", "{$p}-blog-featured-side-content",
                                "{$p}-blog-featured-side-title"],
            'creative-magazine' => ["{$p}-blog-magazine", "{$p}-blog-magazine-hero",
                                    "{$p}-blog-magazine-hero-image", "{$p}-blog-magazine-hero-overlay",
                                    "{$p}-blog-magazine-hero-title", "{$p}-blog-magazine-hero-excerpt",
                                    "{$p}-blog-magazine-side", "{$p}-blog-magazine-card",
                                    "{$p}-blog-magazine-card-image", "{$p}-blog-magazine-card-body",
                                    "{$p}-blog-magazine-card-title"],
            'creative-carousel' => ["{$p}-blog-carousel", "{$p}-blog-carousel-track",
                                    "{$p}-blog-carousel-card", "{$p}-blog-carousel-image",
                                    "{$p}-blog-carousel-body", "{$p}-blog-carousel-title",
                                    "{$p}-blog-carousel-excerpt"],
            default => [],
        };

        return array_merge($base, $extra);
    }

    private static function buildStructuralCSS(string $cssType, string $p): string
    {
        $css = self::css_base($p);

        $typeCSS = match($cssType) {
            'grid-3col'          => self::css_grid_3col($p),
            'grid-2col'          => self::css_grid_2col($p),
            'grid-masonry'       => self::css_grid_masonry($p),
            'list-horizontal'    => self::css_list_horizontal($p),
            'list-minimal'       => self::css_list_minimal($p),
            'list-featured'      => self::css_list_featured($p),
            'creative-magazine'  => self::css_creative_magazine($p),
            'creative-carousel'  => self::css_creative_carousel($p),
            default              => self::css_grid_3col($p),
        };

        return $css . $typeCSS . self::css_responsive($p);
    }

    // --- Base (shared by all Blog patterns) ---
    private static function css_base(string $p): string
    {
        return <<<CSS

/* ═══ Blog Structural CSS (auto-generated — do not edit) ═══ */
.{$p}-blog {
  position: relative; overflow: hidden;
  padding: clamp(60px, 10vh, 120px) 0;
}
.{$p}-blog .container {
  position: relative; z-index: 2;
}
.{$p}-blog-header {
  text-align: center; max-width: 700px;
  margin: 0 auto clamp(40px, 6vw, 64px) auto;
}
.{$p}-blog-badge {
  display: inline-block;
  font-size: 0.8125rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.12em;
  padding: 6px 16px; border-radius: 100px;
  margin-bottom: 16px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.15);
  color: var(--primary, #3b82f6);
  border: 1px solid rgba(var(--primary-rgb, 42,125,225), 0.25);
}
.{$p}-blog-title {
  font-family: var(--font-heading, inherit);
  font-size: clamp(1.75rem, 4vw, 3rem);
  font-weight: 700; line-height: 1.15;
  margin: 0 0 16px 0;
  color: var(--text, #1e293b);
}
.{$p}-blog-subtitle {
  font-size: clamp(1rem, 1.5vw, 1.125rem);
  line-height: 1.7; margin: 0;
  color: var(--text-muted, #64748b);
  max-width: 50ch; margin-left: auto; margin-right: auto;
}
.{$p}-blog-card-meta {
  display: flex; align-items: center; gap: 12px;
  flex-wrap: wrap; margin-bottom: 8px;
}
.{$p}-blog-card-category {
  font-size: 0.75rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.08em;
  color: var(--primary, #3b82f6);
  background: rgba(var(--primary-rgb, 42,125,225), 0.1);
  padding: 3px 10px; border-radius: 4px;
}
.{$p}-blog-card-date {
  font-size: 0.8125rem;
  color: var(--text-muted, #64748b);
}
.{$p}-blog-read-more {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 0.875rem; font-weight: 600;
  color: var(--primary, #3b82f6);
  text-decoration: none; transition: gap 0.3s ease;
}
.{$p}-blog-read-more:hover {
  gap: 10px;
}
.{$p}-blog-read-more i {
  font-size: 0.75rem;
}

CSS;
    }

    // --- Grid 3-Col ---
    private static function css_grid_3col(string $p): string
    {
        return <<<CSS
.{$p}-blog--grid-3col .{$p}-blog-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: clamp(24px, 3vw, 32px);
}
.{$p}-blog-card {
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-blog-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.1);
}
.{$p}-blog-card-image {
  aspect-ratio: 16 / 10; overflow: hidden;
}
.{$p}-blog-card-image img {
  width: 100%; height: 100%; object-fit: cover; display: block;
  transition: transform 0.5s ease;
}
.{$p}-blog-card:hover .{$p}-blog-card-image img {
  transform: scale(1.05);
}
.{$p}-blog-card-image a {
  display: block; height: 100%;
}
.{$p}-blog-card-body {
  padding: clamp(16px, 2vw, 24px);
}
.{$p}-blog-card-title {
  font-family: var(--font-heading, inherit);
  font-size: 1.125rem; font-weight: 700;
  line-height: 1.3; margin: 0 0 8px 0;
}
.{$p}-blog-card-title a {
  color: var(--text, #1e293b); text-decoration: none;
  transition: color 0.3s ease;
}
.{$p}-blog-card-title a:hover {
  color: var(--primary, #3b82f6);
}
.{$p}-blog-card-excerpt {
  font-size: 0.875rem; color: var(--text-muted, #64748b);
  line-height: 1.6; margin: 0 0 16px 0;
  display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;
  overflow: hidden;
}

CSS;
    }

    // --- Grid 2-Col ---
    private static function css_grid_2col(string $p): string
    {
        return <<<CSS
.{$p}-blog--grid-2col .{$p}-blog-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: clamp(24px, 3vw, 32px);
}
.{$p}-blog--grid-2col .{$p}-blog-card {
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-blog--grid-2col .{$p}-blog-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.1);
}
.{$p}-blog--grid-2col .{$p}-blog-card-image {
  aspect-ratio: 16 / 9; overflow: hidden;
}
.{$p}-blog--grid-2col .{$p}-blog-card-image img {
  width: 100%; height: 100%; object-fit: cover; display: block;
  transition: transform 0.5s ease;
}
.{$p}-blog--grid-2col .{$p}-blog-card:hover .{$p}-blog-card-image img {
  transform: scale(1.05);
}
.{$p}-blog--grid-2col .{$p}-blog-card-image a {
  display: block; height: 100%;
}
.{$p}-blog--grid-2col .{$p}-blog-card-body {
  padding: clamp(20px, 3vw, 32px);
}
.{$p}-blog--grid-2col .{$p}-blog-card-title {
  font-family: var(--font-heading, inherit);
  font-size: 1.25rem; font-weight: 700;
  line-height: 1.3; margin: 0 0 10px 0;
}
.{$p}-blog--grid-2col .{$p}-blog-card-title a {
  color: var(--text, #1e293b); text-decoration: none;
  transition: color 0.3s ease;
}
.{$p}-blog--grid-2col .{$p}-blog-card-title a:hover {
  color: var(--primary, #3b82f6);
}
.{$p}-blog--grid-2col .{$p}-blog-card-excerpt {
  font-size: 0.9375rem; color: var(--text-muted, #64748b);
  line-height: 1.6; margin: 0 0 16px 0;
  display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;
  overflow: hidden;
}

CSS;
    }

    // --- Grid Masonry ---
    private static function css_grid_masonry(string $p): string
    {
        return <<<CSS
.{$p}-blog-masonry {
  columns: 3; column-gap: clamp(20px, 3vw, 28px);
}
.{$p}-blog-masonry-item {
  break-inside: avoid;
  margin-bottom: clamp(20px, 3vw, 28px);
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-blog-masonry-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.1);
}
.{$p}-blog-masonry-image {
  overflow: hidden;
}
.{$p}-blog-masonry-image img {
  width: 100%; display: block;
  transition: transform 0.5s ease;
}
.{$p}-blog-masonry-item:hover .{$p}-blog-masonry-image img {
  transform: scale(1.05);
}
.{$p}-blog-masonry-image a {
  display: block;
}
.{$p}-blog-masonry-body {
  padding: clamp(16px, 2vw, 20px);
}
.{$p}-blog-masonry-title {
  font-family: var(--font-heading, inherit);
  font-size: 1.0625rem; font-weight: 700;
  line-height: 1.3; margin: 8px 0 8px 0;
}
.{$p}-blog-masonry-title a {
  color: var(--text, #1e293b); text-decoration: none;
  transition: color 0.3s ease;
}
.{$p}-blog-masonry-title a:hover {
  color: var(--primary, #3b82f6);
}
.{$p}-blog-masonry-excerpt {
  font-size: 0.875rem; color: var(--text-muted, #64748b);
  line-height: 1.6; margin: 0 0 8px 0;
}

CSS;
    }

    // --- List Horizontal ---
    private static function css_list_horizontal(string $p): string
    {
        return <<<CSS
.{$p}-blog-list {
  display: flex; flex-direction: column;
  gap: clamp(24px, 3vw, 32px);
}
.{$p}-blog-list-item {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: clamp(20px, 3vw, 32px);
  align-items: center;
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-blog-list-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(0,0,0,0.1);
}
.{$p}-blog-list-image {
  aspect-ratio: 4 / 3; overflow: hidden;
}
.{$p}-blog-list-image img {
  width: 100%; height: 100%; object-fit: cover; display: block;
  transition: transform 0.5s ease;
}
.{$p}-blog-list-item:hover .{$p}-blog-list-image img {
  transform: scale(1.05);
}
.{$p}-blog-list-image a {
  display: block; height: 100%;
}
.{$p}-blog-list-content {
  padding: clamp(16px, 2vw, 24px) clamp(16px, 2vw, 24px) clamp(16px, 2vw, 24px) 0;
}
.{$p}-blog-list-title {
  font-family: var(--font-heading, inherit);
  font-size: 1.25rem; font-weight: 700;
  line-height: 1.3; margin: 0 0 8px 0;
}
.{$p}-blog-list-title a {
  color: var(--text, #1e293b); text-decoration: none;
  transition: color 0.3s ease;
}
.{$p}-blog-list-title a:hover {
  color: var(--primary, #3b82f6);
}
.{$p}-blog-list-excerpt {
  font-size: 0.875rem; color: var(--text-muted, #64748b);
  line-height: 1.6; margin: 0 0 16px 0;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
  overflow: hidden;
}

CSS;
    }

    // --- List Minimal ---
    private static function css_list_minimal(string $p): string
    {
        return <<<CSS
.{$p}-blog-minimal-list {
  max-width: 800px; margin: 0 auto;
  display: flex; flex-direction: column;
}
.{$p}-blog-minimal-item {
  display: grid;
  grid-template-columns: 120px 1fr;
  gap: clamp(16px, 3vw, 32px);
  padding: clamp(20px, 3vw, 32px) 0;
  border-bottom: 1px solid rgba(var(--text-rgb, 30,41,59), 0.08);
  transition: background 0.3s ease;
}
.{$p}-blog-minimal-item:last-child {
  border-bottom: none;
}
.{$p}-blog-minimal-date {
  font-size: 0.8125rem; color: var(--text-muted, #64748b);
  padding-top: 4px;
}
.{$p}-blog-minimal-title {
  font-family: var(--font-heading, inherit);
  font-size: 1.25rem; font-weight: 700;
  line-height: 1.3; margin: 8px 0 8px 0;
}
.{$p}-blog-minimal-title a {
  color: var(--text, #1e293b); text-decoration: none;
  transition: color 0.3s ease;
}
.{$p}-blog-minimal-title a:hover {
  color: var(--primary, #3b82f6);
}
.{$p}-blog-minimal-excerpt {
  font-size: 0.875rem; color: var(--text-muted, #64748b);
  line-height: 1.6; margin: 0;
}

CSS;
    }

    // --- List Featured ---
    private static function css_list_featured(string $p): string
    {
        return <<<CSS
.{$p}-blog-featured-layout {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: clamp(24px, 3vw, 40px);
  align-items: start;
}
.{$p}-blog-featured-main {
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
.{$p}-blog-featured-image {
  aspect-ratio: 16 / 10; overflow: hidden;
}
.{$p}-blog-featured-image img {
  width: 100%; height: 100%; object-fit: cover; display: block;
  transition: transform 0.5s ease;
}
.{$p}-blog-featured-main:hover .{$p}-blog-featured-image img {
  transform: scale(1.05);
}
.{$p}-blog-featured-image a {
  display: block; height: 100%;
}
.{$p}-blog-featured-body {
  padding: clamp(20px, 3vw, 32px);
}
.{$p}-blog-featured-title {
  font-family: var(--font-heading, inherit);
  font-size: 1.5rem; font-weight: 700;
  line-height: 1.3; margin: 0 0 12px 0;
}
.{$p}-blog-featured-title a {
  color: var(--text, #1e293b); text-decoration: none;
  transition: color 0.3s ease;
}
.{$p}-blog-featured-title a:hover {
  color: var(--primary, #3b82f6);
}
.{$p}-blog-featured-excerpt {
  font-size: 0.9375rem; color: var(--text-muted, #64748b);
  line-height: 1.7; margin: 0 0 16px 0;
}
.{$p}-blog-featured-sidebar {
  display: flex; flex-direction: column;
  gap: clamp(16px, 2vw, 20px);
}
.{$p}-blog-featured-side-item {
  display: grid;
  grid-template-columns: 100px 1fr;
  gap: 16px; align-items: center;
  background: var(--surface, #fff);
  border-radius: var(--radius, 8px);
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0,0,0,0.05);
  transition: transform 0.3s ease;
}
.{$p}-blog-featured-side-item:hover {
  transform: translateX(4px);
}
.{$p}-blog-featured-side-image {
  aspect-ratio: 1 / 1; overflow: hidden;
}
.{$p}-blog-featured-side-image img {
  width: 100%; height: 100%; object-fit: cover; display: block;
}
.{$p}-blog-featured-side-image a {
  display: block; height: 100%;
}
.{$p}-blog-featured-side-content {
  padding: 12px 12px 12px 0;
}
.{$p}-blog-featured-side-title {
  font-family: var(--font-heading, inherit);
  font-size: 0.9375rem; font-weight: 700;
  line-height: 1.3; margin: 6px 0 0 0;
}
.{$p}-blog-featured-side-title a {
  color: var(--text, #1e293b); text-decoration: none;
  transition: color 0.3s ease;
}
.{$p}-blog-featured-side-title a:hover {
  color: var(--primary, #3b82f6);
}

CSS;
    }

    // --- Creative Magazine ---
    private static function css_creative_magazine(string $p): string
    {
        return <<<CSS
.{$p}-blog-magazine {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: clamp(20px, 3vw, 28px);
}
.{$p}-blog-magazine-hero {
  border-radius: var(--radius, 12px); overflow: hidden;
}
.{$p}-blog-magazine-hero-image {
  position: relative; aspect-ratio: 4 / 3; overflow: hidden;
}
.{$p}-blog-magazine-hero-image img {
  width: 100%; height: 100%; object-fit: cover; display: block;
  transition: transform 0.5s ease;
}
.{$p}-blog-magazine-hero:hover .{$p}-blog-magazine-hero-image img {
  transform: scale(1.05);
}
.{$p}-blog-magazine-hero-image a {
  display: block; height: 100%;
}
.{$p}-blog-magazine-hero-overlay {
  position: absolute; bottom: 0; left: 0; right: 0;
  background: linear-gradient(0deg, rgba(0,0,0,0.8) 0%, transparent 100%);
  padding: clamp(20px, 3vw, 32px);
  color: #fff;
}
.{$p}-blog-magazine-hero-overlay .{$p}-blog-card-category {
  background: rgba(255,255,255,0.2); color: #fff;
}
.{$p}-blog-magazine-hero-overlay .{$p}-blog-card-date {
  color: rgba(255,255,255,0.7);
}
.{$p}-blog-magazine-hero-title {
  font-family: var(--font-heading, inherit);
  font-size: clamp(1.25rem, 2.5vw, 1.75rem); font-weight: 700;
  line-height: 1.3; margin: 8px 0 8px 0;
}
.{$p}-blog-magazine-hero-title a {
  color: #fff; text-decoration: none;
}
.{$p}-blog-magazine-hero-excerpt {
  font-size: 0.875rem; color: rgba(255,255,255,0.8);
  line-height: 1.5; margin: 0;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
  overflow: hidden;
}
.{$p}-blog-magazine-side {
  display: flex; flex-direction: column;
  gap: clamp(16px, 2vw, 20px);
}
.{$p}-blog-magazine-card {
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  transition: transform 0.3s ease;
}
.{$p}-blog-magazine-card:hover {
  transform: translateY(-3px);
}
.{$p}-blog-magazine-card-image {
  aspect-ratio: 16 / 9; overflow: hidden;
}
.{$p}-blog-magazine-card-image img {
  width: 100%; height: 100%; object-fit: cover; display: block;
  transition: transform 0.5s ease;
}
.{$p}-blog-magazine-card:hover .{$p}-blog-magazine-card-image img {
  transform: scale(1.05);
}
.{$p}-blog-magazine-card-image a {
  display: block; height: 100%;
}
.{$p}-blog-magazine-card-body {
  padding: clamp(12px, 2vw, 16px);
}
.{$p}-blog-magazine-card-title {
  font-family: var(--font-heading, inherit);
  font-size: 1rem; font-weight: 700;
  line-height: 1.3; margin: 8px 0 0 0;
}
.{$p}-blog-magazine-card-title a {
  color: var(--text, #1e293b); text-decoration: none;
  transition: color 0.3s ease;
}
.{$p}-blog-magazine-card-title a:hover {
  color: var(--primary, #3b82f6);
}

CSS;
    }

    // --- Creative Carousel ---
    private static function css_creative_carousel(string $p): string
    {
        return <<<CSS
.{$p}-blog-carousel {
  overflow-x: auto; overflow-y: hidden;
  scroll-snap-type: x mandatory;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
  padding: 0 clamp(16px, 4vw, 48px) 16px;
}
.{$p}-blog-carousel-track {
  display: flex;
  gap: clamp(16px, 2vw, 24px);
}
.{$p}-blog-carousel-card {
  flex: 0 0 320px; scroll-snap-align: start;
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-blog-carousel-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.1);
}
.{$p}-blog-carousel-image {
  aspect-ratio: 16 / 10; overflow: hidden;
}
.{$p}-blog-carousel-image img {
  width: 100%; height: 100%; object-fit: cover; display: block;
  transition: transform 0.5s ease;
}
.{$p}-blog-carousel-card:hover .{$p}-blog-carousel-image img {
  transform: scale(1.05);
}
.{$p}-blog-carousel-image a {
  display: block; height: 100%;
}
.{$p}-blog-carousel-body {
  padding: clamp(16px, 2vw, 20px);
}
.{$p}-blog-carousel-title {
  font-family: var(--font-heading, inherit);
  font-size: 1.0625rem; font-weight: 700;
  line-height: 1.3; margin: 0 0 8px 0;
}
.{$p}-blog-carousel-title a {
  color: var(--text, #1e293b); text-decoration: none;
  transition: color 0.3s ease;
}
.{$p}-blog-carousel-title a:hover {
  color: var(--primary, #3b82f6);
}
.{$p}-blog-carousel-excerpt {
  font-size: 0.8125rem; color: var(--text-muted, #64748b);
  line-height: 1.5; margin: 0 0 12px 0;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
  overflow: hidden;
}

CSS;
    }

    // --- Responsive ---
    private static function css_responsive(string $p): string
    {
        return <<<CSS
@media (max-width: 768px) {
  .{$p}-blog--grid-3col .{$p}-blog-grid {
    grid-template-columns: 1fr !important;
  }
  .{$p}-blog--grid-2col .{$p}-blog-grid {
    grid-template-columns: 1fr !important;
  }
  .{$p}-blog-masonry {
    columns: 1 !important;
  }
  .{$p}-blog-list-item {
    grid-template-columns: 1fr !important;
  }
  .{$p}-blog-list-content {
    padding: clamp(16px, 2vw, 24px) !important;
  }
  .{$p}-blog-minimal-item {
    grid-template-columns: 1fr !important;
  }
  .{$p}-blog-featured-layout {
    grid-template-columns: 1fr !important;
  }
  .{$p}-blog-magazine {
    grid-template-columns: 1fr !important;
  }
  .{$p}-blog-carousel-card {
    flex: 0 0 280px;
  }
}
@media (max-width: 1024px) and (min-width: 769px) {
  .{$p}-blog--grid-3col .{$p}-blog-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .{$p}-blog-masonry {
    columns: 2;
  }
}

CSS;
    }
}
