<?php
/**
 * Features Section Pattern Registry
 * 
 * Pre-built Features HTML layouts with structural CSS.
 * AI generates only decorative CSS (colors, fonts, effects) — never the structure.
 * 
 * 15 patterns across 5 groups.
 * @since 2026-02-19
 */

class FeaturesPatternRegistry
{
    // ═══════════════════════════════════════
    // PATTERN DEFINITIONS
    // ═══════════════════════════════════════

    private static array $patterns = [
        // --- Grid (columnar layouts) ---
        ['id'=>'grid-3col',           'group'=>'grid',        'css_type'=>'grid-3col',
         'best_for'=>['saas','tech','startup','fintech','ai','blockchain']],
        ['id'=>'grid-4col',           'group'=>'grid',        'css_type'=>'grid-4col',
         'best_for'=>['ecommerce','marketplace','platform','digital']],
        ['id'=>'grid-2col',           'group'=>'grid',        'css_type'=>'grid-2col',
         'best_for'=>['healthcare','clinic','hospital','dental','pharmacy']],

        // --- Cards (card-based layouts) ---
        ['id'=>'cards-elevated',      'group'=>'cards',       'css_type'=>'cards-elevated',
         'best_for'=>['agency','consulting','marketing','branding','seo']],
        ['id'=>'cards-bordered',      'group'=>'cards',       'css_type'=>'cards-bordered',
         'best_for'=>['legal','financial','accounting','insurance','bank']],
        ['id'=>'cards-glass',         'group'=>'cards',       'css_type'=>'cards-glass',
         'best_for'=>['gaming','music','entertainment','nightclub','festival']],

        // --- Alternating (left/right layouts) ---
        ['id'=>'alternating-rows',    'group'=>'alternating', 'css_type'=>'alternating-rows',
         'best_for'=>['education','university','school','coaching','nonprofit']],
        ['id'=>'alternating-zigzag',  'group'=>'alternating', 'css_type'=>'alternating-zigzag',
         'best_for'=>['construction','engineering','manufacturing','logistics']],
        ['id'=>'alternating-timeline','group'=>'alternating', 'css_type'=>'alternating-timeline',
         'best_for'=>['wedding','event-planning','catering','photography']],

        // --- Icon List (linear icon layouts) ---
        ['id'=>'icon-list-horizontal','group'=>'icon-list',   'css_type'=>'icon-list-horizontal',
         'best_for'=>['restaurant','bakery','cafe','hotel','spa']],
        ['id'=>'icon-list-numbered',  'group'=>'icon-list',   'css_type'=>'icon-list-numbered',
         'best_for'=>['fitness','sports','travel','tourism','adventure']],
        ['id'=>'icon-list-vertical',  'group'=>'icon-list',   'css_type'=>'icon-list-vertical',
         'best_for'=>['real-estate','architecture','interior-design','landscaping']],

        // --- Creative (unique/bold designs) ---
        ['id'=>'creative-bento',      'group'=>'creative',    'css_type'=>'creative-bento',
         'best_for'=>['creative-agency','design','web-design','fashion','art']],
        ['id'=>'creative-tabs',       'group'=>'creative',    'css_type'=>'creative-tabs',
         'best_for'=>['magazine','blog','media','podcast']],
        ['id'=>'creative-carousel',   'group'=>'creative',    'css_type'=>'creative-carousel',
         'best_for'=>['automotive','jewelry','luxury','gallery','museum']],
    ];

    // ═══════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════

    /**
     * Pick the best features pattern for an industry.
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
     * Render a features pattern.
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
        // Common fields all features have
        $common = [
            'badge'      => ['type' => 'text',     'label' => 'Section Badge'],
            'title'      => ['type' => 'text',     'label' => 'Section Title'],
            'subtitle'   => ['type' => 'textarea', 'label' => 'Section Subtitle'],
            'item1_icon' => ['type' => 'icon',     'label' => 'Feature 1 Icon'],
            'item1_title'=> ['type' => 'text',     'label' => 'Feature 1 Title'],
            'item1_text' => ['type' => 'textarea', 'label' => 'Feature 1 Description'],
            'item2_icon' => ['type' => 'icon',     'label' => 'Feature 2 Icon'],
            'item2_title'=> ['type' => 'text',     'label' => 'Feature 2 Title'],
            'item2_text' => ['type' => 'textarea', 'label' => 'Feature 2 Description'],
            'item3_icon' => ['type' => 'icon',     'label' => 'Feature 3 Icon'],
            'item3_title'=> ['type' => 'text',     'label' => 'Feature 3 Title'],
            'item3_text' => ['type' => 'textarea', 'label' => 'Feature 3 Description'],
            'item4_icon' => ['type' => 'icon',     'label' => 'Feature 4 Icon'],
            'item4_title'=> ['type' => 'text',     'label' => 'Feature 4 Title'],
            'item4_text' => ['type' => 'textarea', 'label' => 'Feature 4 Description'],
        ];

        // Patterns that support 6 items
        $sixItemPatterns = ['grid-3col', 'grid-4col', 'cards-elevated', 'cards-bordered',
                            'cards-glass', 'creative-bento'];
        if (in_array($patternId, $sixItemPatterns, true)) {
            $common['item5_icon']  = ['type' => 'icon',     'label' => 'Feature 5 Icon'];
            $common['item5_title'] = ['type' => 'text',     'label' => 'Feature 5 Title'];
            $common['item5_text']  = ['type' => 'textarea', 'label' => 'Feature 5 Description'];
            $common['item6_icon']  = ['type' => 'icon',     'label' => 'Feature 6 Icon'];
            $common['item6_title'] = ['type' => 'text',     'label' => 'Feature 6 Title'];
            $common['item6_text']  = ['type' => 'textarea', 'label' => 'Feature 6 Description'];
        }

        return $common;
    }

    /**
     * Get pattern-specific decorative CSS guide for Step 3 AI prompt.
     */
    public static function getDecorativeGuide(string $patternId): string
    {
        return match($patternId) {
            'grid-3col' => <<<'G'
- Standard 3-column equal cards, consistent spacing
- Subtle borders, clean hover shadow, icon circles with primary tint bg
- Even card sizing, no visual hierarchy between items
G,
            'grid-4col' => <<<'G'
- Compact 4-column, smaller icons (40px), tighter spacing
- Font-size slightly smaller for titles, concise text
- More items visible at once, compact card padding
G,
            'grid-2col' => <<<'G'
- Wide 2-column, larger icons (64px), more description visible
- Spacious layout, generous padding, icon beside text (not above)
- Larger body text, horizontal card feel
G,
            'cards-elevated' => <<<'G'
- Heavy resting shadow: 0 8px 30px rgba(0,0,0,0.08), no visible border
- Hover: shadow 0 20px 50px rgba(0,0,0,0.15), translateY(-6px)
- Floating/lifted appearance, clean white card bg
- Shadow transition 0.4s cubic-bezier
G,
            'cards-bordered' => <<<'G'
- Clean defined borders: 1px solid var(--border), NO shadow initially
- Hover: border-color var(--primary), subtle shadow appears
- Sharp, clean, corporate feel — precision over softness
- Border-left or border-top accent (3px primary) on hover
G,
            'cards-glass' => <<<'G'
- GLASSMORPHISM: backdrop-filter blur(16px), bg rgba(surface,0.6)
- Glass border: 1px solid rgba(255,255,255,0.15)
- Frosted appearance, content visible through cards
- Section needs slightly textured/gradient bg for glass effect to show
G,
            'alternating-rows' => <<<'G'
- Left/right alternating content + icon/image
- Accent bg on alternate rows: nth-child(even) bg var(--surface)
- Divider lines between rows: border-bottom 1px solid var(--border)
- Icon circles: large (64px), prominent primary bg tint
G,
            'alternating-zigzag' => <<<'G'
- Numbered steps with accent circle: bg var(--primary), color white, font-weight 700
- Step numbers: large (2rem), prominent, sequential emphasis
- Connector lines: thin (2px) between steps, primary color, dashed or solid
- Diagonal flow feel — zigzag visual path
G,
            'alternating-timeline' => <<<'G'
- Vertical timeline line: 2px solid var(--primary), continuous from top to bottom
- Connector dots: 14px circles, bg var(--primary), border 3px solid var(--background)
- Dot pulse animation: @keyframes pulse { 0%,100% { box-shadow: 0 0 0 0 rgba(primary,0.4) } 50% { box-shadow: 0 0 0 8px rgba(primary,0) } }
- Cards hanging off timeline: alternating left/right, connected to dots
G,
            'icon-list-horizontal' => <<<'G'
- Horizontal icon row, icons prominent, text minimal
- Icon circles: bg primary tint, border-radius 50%, hover scale(1.1)
- Compact spacing, single-line titles, minimal descriptions
G,
            'icon-list-numbered' => <<<'G'
- Step numbers in large accent circles: font-size 1.5rem, font-weight 700
- Number bg: var(--primary), color white, border-radius 50%
- Sequential flow emphasis, connecting visual path between numbers
- Number circles: 48-56px, prominent, eye-catching
G,
            'icon-list-vertical' => <<<'G'
- Split layout: section header left, feature list right
- Small icon circles (36px), beside each item
- List items: border-bottom subtle separators, comfortable padding
- Header stays visible alongside scrolling list feel
G,
            'creative-bento' => <<<'G'
- Bento grid: first item spans 2 columns (large), rest 1x1
- Large item: different bg shade, more padding, bigger icon
- Subtle borders between all cells: 1px solid var(--border)
- Varied visual hierarchy through size difference
G,
            'creative-tabs' => <<<'G'
- Tab buttons: pill or underline style, active bg var(--primary) color white
- Inactive tabs: bg transparent, color var(--text-muted), hover bg subtle
- Panel content: fade transition between tabs (opacity 0→1)
- Active tab indicator: bottom border or filled pill shape
G,
            'creative-carousel' => <<<'G'
- Horizontal scroll: scroll-snap-type x mandatory, snap-align start
- Fixed card width (~300px), navigation arrows on sides
- Arrow buttons: bg var(--surface-card), border-radius 50%, shadow, hover bg primary
- Scrollbar hidden: scrollbar-width none, ::-webkit-scrollbar display none
G,
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

        $title = $brief['features_title'] ?? '';
        if (!$title && $name) {
            $title = "Why Choose {$name}";
        }

        $subtitle = $brief['features_subtitle'] ?? '';
        $badge = $brief['features_badge'] ?? '';
        if (!$badge && $industry) {
            $badge = ucwords(str_replace('-', ' ', $industry));
        }

        // Replace defaults in theme_get() calls
        $replacements = [];
        if ($title)    $replacements["theme_get('features.title', 'Why Choose Us')"]                                                                              = "theme_get('features.title', '" . addslashes($title) . "')";
        if ($subtitle) $replacements["theme_get('features.subtitle', 'Everything you need to succeed, all in one place.')"]                                        = "theme_get('features.subtitle', '" . addslashes($subtitle) . "')";
        if ($badge)    $replacements["theme_get('features.badge', '')"]                                                                                            = "theme_get('features.badge', '" . addslashes($badge) . "')";

        // Inject feature items from brief
        for ($i = 1; $i <= 6; $i++) {
            $icon  = $brief["features_item{$i}_icon"]  ?? '';
            $ftitle = $brief["features_item{$i}_title"] ?? '';
            $ftext  = $brief["features_item{$i}_text"]  ?? '';
            $numWord = match($i) { 1=>'One', 2=>'Two', 3=>'Three', 4=>'Four', 5=>'Five', 6=>'Six', default=>(string)$i };
            if ($icon)   $replacements['theme_get("features.item{$i}_icon", \'fas fa-star\')']               = 'theme_get("features.item{$i}_icon", \'' . addslashes($icon) . '\')';
            if ($ftitle) $replacements['theme_get("features.item{$i}_title", \'Feature ' . $numWord . '\')'] = 'theme_get("features.item{$i}_title", \'' . addslashes($ftitle) . '\')';
            if ($ftext)  $replacements['theme_get("features.item{$i}_text", \'Description of this amazing feature and how it helps you.\')'] = 'theme_get("features.item{$i}_text", \'' . addslashes($ftext) . '\')';
        }

        foreach ($replacements as $search => $replace) {
            $html = str_replace($search, $replace, $html);
        }

        return $html;
    }

    private static function numWord(int $n): string
    {
        return match($n) {
            1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            default => (string)$n,
        };
    }

    private static function buildHTML(string $patternId, string $p): string
    {
        $templates = self::getTemplates($p);
        return $templates[$patternId] ?? $templates['grid-3col'];
    }

    private static function getTemplates(string $p): array
    {
        return [

// ── Grid 3-Column: Icon + title + desc per feature ──
'grid-3col' => <<<HTML
<?php
\$fBadge = theme_get('features.badge', '');
\$fTitle = theme_get('features.title', 'Why Choose Us');
\$fSubtitle = theme_get('features.subtitle', 'Everything you need to succeed, all in one place.');
\$features = [];
for (\$i = 1; \$i <= 6; \$i++) {
    \$_fIcon = theme_get("features.item{\$i}_icon", 'fas fa-star');
    \$_fTitle = theme_get("features.item{\$i}_title", 'Feature ' . ['One','Two','Three','Four','Five','Six'][\$i-1]);
    \$_fText = theme_get("features.item{\$i}_text", 'Description of this amazing feature and how it helps you.');
    if (\$_fTitle) \$features[] = ['icon'=>\$_fIcon, 'title'=>\$_fTitle, 'text'=>\$_fText];
}
unset(\$_fIcon, \$_fTitle, \$_fText);
?>
<section class="{$p}-features {$p}-features--grid-3col" id="features">
  <div class="container">
    <div class="{$p}-features-header" data-animate="fade-up">
      <?php if (\$fBadge): ?><span class="{$p}-features-badge" data-ts="features.badge"><?= esc(\$fBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-features-title" data-ts="features.title"><?= esc(\$fTitle) ?></h2>
      <p class="{$p}-features-subtitle" data-ts="features.subtitle"><?= esc(\$fSubtitle) ?></p>
    </div>
    <div class="{$p}-features-grid" data-animate="fade-up" data-animate-stagger>
      <?php foreach (\$features as \$idx => \$f): ?>
      <div class="{$p}-feature-item">
        <div class="{$p}-feature-icon"><i class="<?= esc(\$f['icon']) ?>" data-ts="features.item<?= \$idx+1 ?>_icon"></i></div>
        <h3 class="{$p}-feature-item-title" data-ts="features.item<?= \$idx+1 ?>_title"><?= esc(\$f['title']) ?></h3>
        <p class="{$p}-feature-item-text" data-ts="features.item<?= \$idx+1 ?>_text"><?= esc(\$f['text']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Grid 4-Column: Compact 4-col grid ──
'grid-4col' => <<<HTML
<?php
\$fBadge = theme_get('features.badge', '');
\$fTitle = theme_get('features.title', 'Why Choose Us');
\$fSubtitle = theme_get('features.subtitle', 'Everything you need to succeed, all in one place.');
\$features = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$_fIcon = theme_get("features.item{\$i}_icon", 'fas fa-star');
    \$_fTitle = theme_get("features.item{\$i}_title", 'Feature ' . ['One','Two','Three','Four'][\$i-1]);
    \$_fText = theme_get("features.item{\$i}_text", 'Description of this amazing feature and how it helps you.');
    if (\$_fTitle) \$features[] = ['icon'=>\$_fIcon, 'title'=>\$_fTitle, 'text'=>\$_fText];
}
unset(\$_fIcon, \$_fTitle, \$_fText);
?>
<section class="{$p}-features {$p}-features--grid-4col" id="features">
  <div class="container">
    <div class="{$p}-features-header" data-animate="fade-up">
      <?php if (\$fBadge): ?><span class="{$p}-features-badge" data-ts="features.badge"><?= esc(\$fBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-features-title" data-ts="features.title"><?= esc(\$fTitle) ?></h2>
      <p class="{$p}-features-subtitle" data-ts="features.subtitle"><?= esc(\$fSubtitle) ?></p>
    </div>
    <div class="{$p}-features-grid" data-animate="fade-up" data-animate-stagger>
      <?php foreach (\$features as \$idx => \$f): ?>
      <div class="{$p}-feature-item">
        <div class="{$p}-feature-icon"><i class="<?= esc(\$f['icon']) ?>" data-ts="features.item<?= \$idx+1 ?>_icon"></i></div>
        <h3 class="{$p}-feature-item-title" data-ts="features.item<?= \$idx+1 ?>_title"><?= esc(\$f['title']) ?></h3>
        <p class="{$p}-feature-item-text" data-ts="features.item<?= \$idx+1 ?>_text"><?= esc(\$f['text']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Grid 2-Column: Larger cards, 2 per row ──
'grid-2col' => <<<HTML
<?php
\$fBadge = theme_get('features.badge', '');
\$fTitle = theme_get('features.title', 'Why Choose Us');
\$fSubtitle = theme_get('features.subtitle', 'Everything you need to succeed, all in one place.');
\$features = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$_fIcon = theme_get("features.item{\$i}_icon", 'fas fa-star');
    \$_fTitle = theme_get("features.item{\$i}_title", 'Feature ' . ['One','Two','Three','Four'][\$i-1]);
    \$_fText = theme_get("features.item{\$i}_text", 'Description of this amazing feature and how it helps you.');
    if (\$_fTitle) \$features[] = ['icon'=>\$_fIcon, 'title'=>\$_fTitle, 'text'=>\$_fText];
}
unset(\$_fIcon, \$_fTitle, \$_fText);
?>
<section class="{$p}-features {$p}-features--grid-2col" id="features">
  <div class="container">
    <div class="{$p}-features-header" data-animate="fade-up">
      <?php if (\$fBadge): ?><span class="{$p}-features-badge" data-ts="features.badge"><?= esc(\$fBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-features-title" data-ts="features.title"><?= esc(\$fTitle) ?></h2>
      <p class="{$p}-features-subtitle" data-ts="features.subtitle"><?= esc(\$fSubtitle) ?></p>
    </div>
    <div class="{$p}-features-grid" data-animate="fade-up" data-animate-stagger>
      <?php foreach (\$features as \$idx => \$f): ?>
      <div class="{$p}-feature-item">
        <div class="{$p}-feature-icon-lg"><i class="<?= esc(\$f['icon']) ?>" data-ts="features.item<?= \$idx+1 ?>_icon"></i></div>
        <div class="{$p}-feature-item-body">
          <h3 class="{$p}-feature-item-title" data-ts="features.item<?= \$idx+1 ?>_title"><?= esc(\$f['title']) ?></h3>
          <p class="{$p}-feature-item-text" data-ts="features.item<?= \$idx+1 ?>_text"><?= esc(\$f['text']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Cards Elevated: Shadow cards with hover lift ──
'cards-elevated' => <<<HTML
<?php
\$fBadge = theme_get('features.badge', '');
\$fTitle = theme_get('features.title', 'Why Choose Us');
\$fSubtitle = theme_get('features.subtitle', 'Everything you need to succeed, all in one place.');
\$features = [];
for (\$i = 1; \$i <= 6; \$i++) {
    \$_fIcon = theme_get("features.item{\$i}_icon", 'fas fa-star');
    \$_fTitle = theme_get("features.item{\$i}_title", 'Feature ' . ['One','Two','Three','Four','Five','Six'][\$i-1]);
    \$_fText = theme_get("features.item{\$i}_text", 'Description of this amazing feature and how it helps you.');
    if (\$_fTitle) \$features[] = ['icon'=>\$_fIcon, 'title'=>\$_fTitle, 'text'=>\$_fText];
}
unset(\$_fIcon, \$_fTitle, \$_fText);
?>
<section class="{$p}-features {$p}-features--cards-elevated" id="features">
  <div class="container">
    <div class="{$p}-features-header" data-animate="fade-up">
      <?php if (\$fBadge): ?><span class="{$p}-features-badge" data-ts="features.badge"><?= esc(\$fBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-features-title" data-ts="features.title"><?= esc(\$fTitle) ?></h2>
      <p class="{$p}-features-subtitle" data-ts="features.subtitle"><?= esc(\$fSubtitle) ?></p>
    </div>
    <div class="{$p}-features-grid" data-animate="fade-up" data-animate-stagger>
      <?php foreach (\$features as \$idx => \$f): ?>
      <div class="{$p}-feature-card {$p}-feature-card--elevated">
        <div class="{$p}-feature-icon"><i class="<?= esc(\$f['icon']) ?>" data-ts="features.item<?= \$idx+1 ?>_icon"></i></div>
        <h3 class="{$p}-feature-item-title" data-ts="features.item<?= \$idx+1 ?>_title"><?= esc(\$f['title']) ?></h3>
        <p class="{$p}-feature-item-text" data-ts="features.item<?= \$idx+1 ?>_text"><?= esc(\$f['text']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Cards Bordered: Clean border cards ──
'cards-bordered' => <<<HTML
<?php
\$fBadge = theme_get('features.badge', '');
\$fTitle = theme_get('features.title', 'Why Choose Us');
\$fSubtitle = theme_get('features.subtitle', 'Everything you need to succeed, all in one place.');
\$features = [];
for (\$i = 1; \$i <= 6; \$i++) {
    \$_fIcon = theme_get("features.item{\$i}_icon", 'fas fa-star');
    \$_fTitle = theme_get("features.item{\$i}_title", 'Feature ' . ['One','Two','Three','Four','Five','Six'][\$i-1]);
    \$_fText = theme_get("features.item{\$i}_text", 'Description of this amazing feature and how it helps you.');
    if (\$_fTitle) \$features[] = ['icon'=>\$_fIcon, 'title'=>\$_fTitle, 'text'=>\$_fText];
}
unset(\$_fIcon, \$_fTitle, \$_fText);
?>
<section class="{$p}-features {$p}-features--cards-bordered" id="features">
  <div class="container">
    <div class="{$p}-features-header" data-animate="fade-up">
      <?php if (\$fBadge): ?><span class="{$p}-features-badge" data-ts="features.badge"><?= esc(\$fBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-features-title" data-ts="features.title"><?= esc(\$fTitle) ?></h2>
      <p class="{$p}-features-subtitle" data-ts="features.subtitle"><?= esc(\$fSubtitle) ?></p>
    </div>
    <div class="{$p}-features-grid" data-animate="fade-up" data-animate-stagger>
      <?php foreach (\$features as \$idx => \$f): ?>
      <div class="{$p}-feature-card {$p}-feature-card--bordered">
        <div class="{$p}-feature-icon"><i class="<?= esc(\$f['icon']) ?>" data-ts="features.item<?= \$idx+1 ?>_icon"></i></div>
        <h3 class="{$p}-feature-item-title" data-ts="features.item<?= \$idx+1 ?>_title"><?= esc(\$f['title']) ?></h3>
        <p class="{$p}-feature-item-text" data-ts="features.item<?= \$idx+1 ?>_text"><?= esc(\$f['text']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Cards Glass: Glassmorphism cards ──
'cards-glass' => <<<HTML
<?php
\$fBadge = theme_get('features.badge', '');
\$fTitle = theme_get('features.title', 'Why Choose Us');
\$fSubtitle = theme_get('features.subtitle', 'Everything you need to succeed, all in one place.');
\$features = [];
for (\$i = 1; \$i <= 6; \$i++) {
    \$_fIcon = theme_get("features.item{\$i}_icon", 'fas fa-star');
    \$_fTitle = theme_get("features.item{\$i}_title", 'Feature ' . ['One','Two','Three','Four','Five','Six'][\$i-1]);
    \$_fText = theme_get("features.item{\$i}_text", 'Description of this amazing feature and how it helps you.');
    if (\$_fTitle) \$features[] = ['icon'=>\$_fIcon, 'title'=>\$_fTitle, 'text'=>\$_fText];
}
unset(\$_fIcon, \$_fTitle, \$_fText);
?>
<section class="{$p}-features {$p}-features--cards-glass" id="features">
  <div class="{$p}-features-glass-bg"></div>
  <div class="container">
    <div class="{$p}-features-header" data-animate="fade-up">
      <?php if (\$fBadge): ?><span class="{$p}-features-badge" data-ts="features.badge"><?= esc(\$fBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-features-title" data-ts="features.title"><?= esc(\$fTitle) ?></h2>
      <p class="{$p}-features-subtitle" data-ts="features.subtitle"><?= esc(\$fSubtitle) ?></p>
    </div>
    <div class="{$p}-features-grid" data-animate="fade-up" data-animate-stagger>
      <?php foreach (\$features as \$idx => \$f): ?>
      <div class="{$p}-feature-card {$p}-feature-card--glass">
        <div class="{$p}-feature-icon"><i class="<?= esc(\$f['icon']) ?>" data-ts="features.item<?= \$idx+1 ?>_icon"></i></div>
        <h3 class="{$p}-feature-item-title" data-ts="features.item<?= \$idx+1 ?>_title"><?= esc(\$f['title']) ?></h3>
        <p class="{$p}-feature-item-text" data-ts="features.item<?= \$idx+1 ?>_text"><?= esc(\$f['text']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Alternating Rows: Image + text alternating left/right ──
'alternating-rows' => <<<HTML
<?php
\$fBadge = theme_get('features.badge', '');
\$fTitle = theme_get('features.title', 'Why Choose Us');
\$fSubtitle = theme_get('features.subtitle', 'Everything you need to succeed, all in one place.');
\$features = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$_fIcon = theme_get("features.item{\$i}_icon", 'fas fa-star');
    \$_fTitle = theme_get("features.item{\$i}_title", 'Feature ' . ['One','Two','Three','Four'][\$i-1]);
    \$_fText = theme_get("features.item{\$i}_text", 'Description of this amazing feature and how it helps you.');
    if (\$_fTitle) \$features[] = ['icon'=>\$_fIcon, 'title'=>\$_fTitle, 'text'=>\$_fText];
}
unset(\$_fIcon, \$_fTitle, \$_fText);
?>
<section class="{$p}-features {$p}-features--alternating-rows" id="features">
  <div class="container">
    <div class="{$p}-features-header" data-animate="fade-up">
      <?php if (\$fBadge): ?><span class="{$p}-features-badge" data-ts="features.badge"><?= esc(\$fBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-features-title" data-ts="features.title"><?= esc(\$fTitle) ?></h2>
      <p class="{$p}-features-subtitle" data-ts="features.subtitle"><?= esc(\$fSubtitle) ?></p>
    </div>
    <div class="{$p}-features-rows">
      <?php foreach (\$features as \$idx => \$f): ?>
      <div class="{$p}-feature-row <?= \$idx % 2 === 1 ? '{$p}-feature-row--reverse' : '' ?>" data-animate="fade-up">
        <div class="{$p}-feature-row-icon">
          <div class="{$p}-feature-icon-circle"><i class="<?= esc(\$f['icon']) ?>" data-ts="features.item<?= \$idx+1 ?>_icon"></i></div>
        </div>
        <div class="{$p}-feature-row-content">
          <h3 class="{$p}-feature-item-title" data-ts="features.item<?= \$idx+1 ?>_title"><?= esc(\$f['title']) ?></h3>
          <p class="{$p}-feature-item-text" data-ts="features.item<?= \$idx+1 ?>_text"><?= esc(\$f['text']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Alternating Zigzag: Zigzag with subtle alternating bg ──
'alternating-zigzag' => <<<HTML
<?php
\$fBadge = theme_get('features.badge', '');
\$fTitle = theme_get('features.title', 'Why Choose Us');
\$fSubtitle = theme_get('features.subtitle', 'Everything you need to succeed, all in one place.');
\$features = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$_fIcon = theme_get("features.item{\$i}_icon", 'fas fa-star');
    \$_fTitle = theme_get("features.item{\$i}_title", 'Feature ' . ['One','Two','Three','Four'][\$i-1]);
    \$_fText = theme_get("features.item{\$i}_text", 'Description of this amazing feature and how it helps you.');
    if (\$_fTitle) \$features[] = ['icon'=>\$_fIcon, 'title'=>\$_fTitle, 'text'=>\$_fText];
}
unset(\$_fIcon, \$_fTitle, \$_fText);
?>
<section class="{$p}-features {$p}-features--alternating-zigzag" id="features">
  <div class="container">
    <div class="{$p}-features-header" data-animate="fade-up">
      <?php if (\$fBadge): ?><span class="{$p}-features-badge" data-ts="features.badge"><?= esc(\$fBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-features-title" data-ts="features.title"><?= esc(\$fTitle) ?></h2>
      <p class="{$p}-features-subtitle" data-ts="features.subtitle"><?= esc(\$fSubtitle) ?></p>
    </div>
    <div class="{$p}-features-zigzag">
      <?php foreach (\$features as \$idx => \$f): ?>
      <div class="{$p}-feature-zigzag-row <?= \$idx % 2 === 1 ? '{$p}-feature-zigzag-row--alt' : '' ?>" data-animate="fade-up">
        <div class="{$p}-feature-zigzag-num"><span><?= str_pad(\$idx+1, 2, '0', STR_PAD_LEFT) ?></span></div>
        <div class="{$p}-feature-zigzag-icon"><i class="<?= esc(\$f['icon']) ?>" data-ts="features.item<?= \$idx+1 ?>_icon"></i></div>
        <div class="{$p}-feature-zigzag-content">
          <h3 class="{$p}-feature-item-title" data-ts="features.item<?= \$idx+1 ?>_title"><?= esc(\$f['title']) ?></h3>
          <p class="{$p}-feature-item-text" data-ts="features.item<?= \$idx+1 ?>_text"><?= esc(\$f['text']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Alternating Timeline: Vertical timeline layout ──
'alternating-timeline' => <<<HTML
<?php
\$fBadge = theme_get('features.badge', '');
\$fTitle = theme_get('features.title', 'Why Choose Us');
\$fSubtitle = theme_get('features.subtitle', 'Everything you need to succeed, all in one place.');
\$features = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$_fIcon = theme_get("features.item{\$i}_icon", 'fas fa-star');
    \$_fTitle = theme_get("features.item{\$i}_title", 'Feature ' . ['One','Two','Three','Four'][\$i-1]);
    \$_fText = theme_get("features.item{\$i}_text", 'Description of this amazing feature and how it helps you.');
    if (\$_fTitle) \$features[] = ['icon'=>\$_fIcon, 'title'=>\$_fTitle, 'text'=>\$_fText];
}
unset(\$_fIcon, \$_fTitle, \$_fText);
?>
<section class="{$p}-features {$p}-features--alternating-timeline" id="features">
  <div class="container">
    <div class="{$p}-features-header" data-animate="fade-up">
      <?php if (\$fBadge): ?><span class="{$p}-features-badge" data-ts="features.badge"><?= esc(\$fBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-features-title" data-ts="features.title"><?= esc(\$fTitle) ?></h2>
      <p class="{$p}-features-subtitle" data-ts="features.subtitle"><?= esc(\$fSubtitle) ?></p>
    </div>
    <div class="{$p}-features-timeline">
      <div class="{$p}-features-timeline-line"></div>
      <?php foreach (\$features as \$idx => \$f): ?>
      <div class="{$p}-feature-timeline-item <?= \$idx % 2 === 1 ? '{$p}-feature-timeline-item--right' : '' ?>" data-animate="fade-up">
        <div class="{$p}-feature-timeline-dot">
          <i class="<?= esc(\$f['icon']) ?>" data-ts="features.item<?= \$idx+1 ?>_icon"></i>
        </div>
        <div class="{$p}-feature-timeline-card">
          <h3 class="{$p}-feature-item-title" data-ts="features.item<?= \$idx+1 ?>_title"><?= esc(\$f['title']) ?></h3>
          <p class="{$p}-feature-item-text" data-ts="features.item<?= \$idx+1 ?>_text"><?= esc(\$f['text']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Icon List Horizontal: Icons in a row ──
'icon-list-horizontal' => <<<HTML
<?php
\$fBadge = theme_get('features.badge', '');
\$fTitle = theme_get('features.title', 'Why Choose Us');
\$fSubtitle = theme_get('features.subtitle', 'Everything you need to succeed, all in one place.');
\$features = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$_fIcon = theme_get("features.item{\$i}_icon", 'fas fa-star');
    \$_fTitle = theme_get("features.item{\$i}_title", 'Feature ' . ['One','Two','Three','Four'][\$i-1]);
    \$_fText = theme_get("features.item{\$i}_text", 'Description of this amazing feature and how it helps you.');
    if (\$_fTitle) \$features[] = ['icon'=>\$_fIcon, 'title'=>\$_fTitle, 'text'=>\$_fText];
}
unset(\$_fIcon, \$_fTitle, \$_fText);
?>
<section class="{$p}-features {$p}-features--icon-list-horizontal" id="features">
  <div class="container">
    <div class="{$p}-features-header" data-animate="fade-up">
      <?php if (\$fBadge): ?><span class="{$p}-features-badge" data-ts="features.badge"><?= esc(\$fBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-features-title" data-ts="features.title"><?= esc(\$fTitle) ?></h2>
      <p class="{$p}-features-subtitle" data-ts="features.subtitle"><?= esc(\$fSubtitle) ?></p>
    </div>
    <div class="{$p}-features-icon-row" data-animate="fade-up" data-animate-stagger>
      <?php foreach (\$features as \$idx => \$f): ?>
      <div class="{$p}-feature-icon-item">
        <div class="{$p}-feature-icon-circle"><i class="<?= esc(\$f['icon']) ?>" data-ts="features.item<?= \$idx+1 ?>_icon"></i></div>
        <h3 class="{$p}-feature-item-title" data-ts="features.item<?= \$idx+1 ?>_title"><?= esc(\$f['title']) ?></h3>
        <p class="{$p}-feature-item-text" data-ts="features.item<?= \$idx+1 ?>_text"><?= esc(\$f['text']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Icon List Numbered: Numbered steps (01, 02, 03...) ──
'icon-list-numbered' => <<<HTML
<?php
\$fBadge = theme_get('features.badge', '');
\$fTitle = theme_get('features.title', 'Why Choose Us');
\$fSubtitle = theme_get('features.subtitle', 'Everything you need to succeed, all in one place.');
\$features = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$_fIcon = theme_get("features.item{\$i}_icon", 'fas fa-star');
    \$_fTitle = theme_get("features.item{\$i}_title", 'Feature ' . ['One','Two','Three','Four'][\$i-1]);
    \$_fText = theme_get("features.item{\$i}_text", 'Description of this amazing feature and how it helps you.');
    if (\$_fTitle) \$features[] = ['icon'=>\$_fIcon, 'title'=>\$_fTitle, 'text'=>\$_fText];
}
unset(\$_fIcon, \$_fTitle, \$_fText);
?>
<section class="{$p}-features {$p}-features--icon-list-numbered" id="features">
  <div class="container">
    <div class="{$p}-features-header" data-animate="fade-up">
      <?php if (\$fBadge): ?><span class="{$p}-features-badge" data-ts="features.badge"><?= esc(\$fBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-features-title" data-ts="features.title"><?= esc(\$fTitle) ?></h2>
      <p class="{$p}-features-subtitle" data-ts="features.subtitle"><?= esc(\$fSubtitle) ?></p>
    </div>
    <div class="{$p}-features-numbered" data-animate="fade-up" data-animate-stagger>
      <?php foreach (\$features as \$idx => \$f): ?>
      <div class="{$p}-feature-numbered-item">
        <div class="{$p}-feature-number"><span><?= str_pad(\$idx+1, 2, '0', STR_PAD_LEFT) ?></span></div>
        <div class="{$p}-feature-numbered-icon"><i class="<?= esc(\$f['icon']) ?>" data-ts="features.item<?= \$idx+1 ?>_icon"></i></div>
        <div class="{$p}-feature-numbered-body">
          <h3 class="{$p}-feature-item-title" data-ts="features.item<?= \$idx+1 ?>_title"><?= esc(\$f['title']) ?></h3>
          <p class="{$p}-feature-item-text" data-ts="features.item<?= \$idx+1 ?>_text"><?= esc(\$f['text']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Icon List Vertical: Vertical stack layout ──
'icon-list-vertical' => <<<HTML
<?php
\$fBadge = theme_get('features.badge', '');
\$fTitle = theme_get('features.title', 'Why Choose Us');
\$fSubtitle = theme_get('features.subtitle', 'Everything you need to succeed, all in one place.');
\$features = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$_fIcon = theme_get("features.item{\$i}_icon", 'fas fa-star');
    \$_fTitle = theme_get("features.item{\$i}_title", 'Feature ' . ['One','Two','Three','Four'][\$i-1]);
    \$_fText = theme_get("features.item{\$i}_text", 'Description of this amazing feature and how it helps you.');
    if (\$_fTitle) \$features[] = ['icon'=>\$_fIcon, 'title'=>\$_fTitle, 'text'=>\$_fText];
}
unset(\$_fIcon, \$_fTitle, \$_fText);
?>
<section class="{$p}-features {$p}-features--icon-list-vertical" id="features">
  <div class="container">
    <div class="{$p}-features-split">
      <div class="{$p}-features-header {$p}-features-header--left" data-animate="fade-up">
        <?php if (\$fBadge): ?><span class="{$p}-features-badge" data-ts="features.badge"><?= esc(\$fBadge) ?></span><?php endif; ?>
        <h2 class="{$p}-features-title" data-ts="features.title"><?= esc(\$fTitle) ?></h2>
        <p class="{$p}-features-subtitle" data-ts="features.subtitle"><?= esc(\$fSubtitle) ?></p>
      </div>
      <div class="{$p}-features-vertical-list" data-animate="fade-up" data-animate-stagger>
        <?php foreach (\$features as \$idx => \$f): ?>
        <div class="{$p}-feature-vertical-item">
          <div class="{$p}-feature-icon-sm"><i class="<?= esc(\$f['icon']) ?>" data-ts="features.item<?= \$idx+1 ?>_icon"></i></div>
          <div class="{$p}-feature-vertical-body">
            <h3 class="{$p}-feature-item-title" data-ts="features.item<?= \$idx+1 ?>_title"><?= esc(\$f['title']) ?></h3>
            <p class="{$p}-feature-item-text" data-ts="features.item<?= \$idx+1 ?>_text"><?= esc(\$f['text']) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Creative Bento: Bento grid (1 large + smaller items) ──
'creative-bento' => <<<HTML
<?php
\$fBadge = theme_get('features.badge', '');
\$fTitle = theme_get('features.title', 'Why Choose Us');
\$fSubtitle = theme_get('features.subtitle', 'Everything you need to succeed, all in one place.');
\$features = [];
for (\$i = 1; \$i <= 5; \$i++) {
    \$_fIcon = theme_get("features.item{\$i}_icon", 'fas fa-star');
    \$_fTitle = theme_get("features.item{\$i}_title", 'Feature ' . ['One','Two','Three','Four','Five'][\$i-1]);
    \$_fText = theme_get("features.item{\$i}_text", 'Description of this amazing feature and how it helps you.');
    if (\$_fTitle) \$features[] = ['icon'=>\$_fIcon, 'title'=>\$_fTitle, 'text'=>\$_fText];
}
unset(\$_fIcon, \$_fTitle, \$_fText);
?>
<section class="{$p}-features {$p}-features--creative-bento" id="features">
  <div class="container">
    <div class="{$p}-features-header" data-animate="fade-up">
      <?php if (\$fBadge): ?><span class="{$p}-features-badge" data-ts="features.badge"><?= esc(\$fBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-features-title" data-ts="features.title"><?= esc(\$fTitle) ?></h2>
      <p class="{$p}-features-subtitle" data-ts="features.subtitle"><?= esc(\$fSubtitle) ?></p>
    </div>
    <div class="{$p}-features-bento" data-animate="fade-up">
      <?php foreach (\$features as \$idx => \$f): ?>
      <div class="{$p}-feature-bento-item <?= \$idx === 0 ? '{$p}-feature-bento-item--large' : '' ?>">
        <div class="{$p}-feature-icon"><i class="<?= esc(\$f['icon']) ?>" data-ts="features.item<?= \$idx+1 ?>_icon"></i></div>
        <h3 class="{$p}-feature-item-title" data-ts="features.item<?= \$idx+1 ?>_title"><?= esc(\$f['title']) ?></h3>
        <p class="{$p}-feature-item-text" data-ts="features.item<?= \$idx+1 ?>_text"><?= esc(\$f['text']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Creative Tabs: Tab headers with panel per feature ──
'creative-tabs' => <<<HTML
<?php
\$fBadge = theme_get('features.badge', '');
\$fTitle = theme_get('features.title', 'Why Choose Us');
\$fSubtitle = theme_get('features.subtitle', 'Everything you need to succeed, all in one place.');
\$features = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$_fIcon = theme_get("features.item{\$i}_icon", 'fas fa-star');
    \$_fTitle = theme_get("features.item{\$i}_title", 'Feature ' . ['One','Two','Three','Four'][\$i-1]);
    \$_fText = theme_get("features.item{\$i}_text", 'Description of this amazing feature and how it helps you.');
    if (\$_fTitle) \$features[] = ['icon'=>\$_fIcon, 'title'=>\$_fTitle, 'text'=>\$_fText];
}
unset(\$_fIcon, \$_fTitle, \$_fText);
\$tabId = 'feat-tabs-' . substr(md5(uniqid()), 0, 6);
?>
<section class="{$p}-features {$p}-features--creative-tabs" id="features">
  <div class="container">
    <div class="{$p}-features-header" data-animate="fade-up">
      <?php if (\$fBadge): ?><span class="{$p}-features-badge" data-ts="features.badge"><?= esc(\$fBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-features-title" data-ts="features.title"><?= esc(\$fTitle) ?></h2>
      <p class="{$p}-features-subtitle" data-ts="features.subtitle"><?= esc(\$fSubtitle) ?></p>
    </div>
    <div class="{$p}-features-tabs-wrap" data-animate="fade-up">
      <div class="{$p}-features-tab-nav" role="tablist">
        <?php foreach (\$features as \$idx => \$f): ?>
        <button class="{$p}-features-tab-btn <?= \$idx === 0 ? '{$p}-features-tab-btn--active' : '' ?>"
                role="tab" aria-selected="<?= \$idx === 0 ? 'true' : 'false' ?>"
                data-tab="<?= \$tabId ?>-<?= \$idx ?>"
                onclick="document.querySelectorAll('[data-tab-group=<?= \$tabId ?>]').forEach(p=>p.hidden=true);document.getElementById('<?= \$tabId ?>-<?= \$idx ?>').hidden=false;this.parentNode.querySelectorAll('button').forEach(b=>{b.classList.remove('{$p}-features-tab-btn--active');b.setAttribute('aria-selected','false')});this.classList.add('{$p}-features-tab-btn--active');this.setAttribute('aria-selected','true')">
          <i class="<?= esc(\$f['icon']) ?>" data-ts="features.item<?= \$idx+1 ?>_icon"></i>
          <span data-ts="features.item<?= \$idx+1 ?>_title"><?= esc(\$f['title']) ?></span>
        </button>
        <?php endforeach; ?>
      </div>
      <div class="{$p}-features-tab-panels">
        <?php foreach (\$features as \$idx => \$f): ?>
        <div class="{$p}-features-tab-panel" id="<?= \$tabId ?>-<?= \$idx ?>" data-tab-group="<?= \$tabId ?>" role="tabpanel" <?= \$idx !== 0 ? 'hidden' : '' ?>>
          <div class="{$p}-feature-tab-content">
            <div class="{$p}-feature-icon-lg"><i class="<?= esc(\$f['icon']) ?>" data-ts="features.item<?= \$idx+1 ?>_icon"></i></div>
            <h3 class="{$p}-feature-item-title" data-ts="features.item<?= \$idx+1 ?>_title"><?= esc(\$f['title']) ?></h3>
            <p class="{$p}-feature-item-text" data-ts="features.item<?= \$idx+1 ?>_text"><?= esc(\$f['text']) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Creative Carousel: Horizontal scroll cards ──
'creative-carousel' => <<<HTML
<?php
\$fBadge = theme_get('features.badge', '');
\$fTitle = theme_get('features.title', 'Why Choose Us');
\$fSubtitle = theme_get('features.subtitle', 'Everything you need to succeed, all in one place.');
\$features = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$_fIcon = theme_get("features.item{\$i}_icon", 'fas fa-star');
    \$_fTitle = theme_get("features.item{\$i}_title", 'Feature ' . ['One','Two','Three','Four'][\$i-1]);
    \$_fText = theme_get("features.item{\$i}_text", 'Description of this amazing feature and how it helps you.');
    if (\$_fTitle) \$features[] = ['icon'=>\$_fIcon, 'title'=>\$_fTitle, 'text'=>\$_fText];
}
unset(\$_fIcon, \$_fTitle, \$_fText);
?>
<section class="{$p}-features {$p}-features--creative-carousel" id="features">
  <div class="container">
    <div class="{$p}-features-header" data-animate="fade-up">
      <?php if (\$fBadge): ?><span class="{$p}-features-badge" data-ts="features.badge"><?= esc(\$fBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-features-title" data-ts="features.title"><?= esc(\$fTitle) ?></h2>
      <p class="{$p}-features-subtitle" data-ts="features.subtitle"><?= esc(\$fSubtitle) ?></p>
    </div>
  </div>
  <div class="{$p}-features-carousel-track" data-animate="fade-up">
    <?php foreach (\$features as \$idx => \$f): ?>
    <div class="{$p}-feature-carousel-card">
      <div class="{$p}-feature-icon"><i class="<?= esc(\$f['icon']) ?>" data-ts="features.item<?= \$idx+1 ?>_icon"></i></div>
      <h3 class="{$p}-feature-item-title" data-ts="features.item<?= \$idx+1 ?>_title"><?= esc(\$f['title']) ?></h3>
      <p class="{$p}-feature-item-text" data-ts="features.item<?= \$idx+1 ?>_text"><?= esc(\$f['text']) ?></p>
    </div>
    <?php endforeach; ?>
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
        $base = ["{$p}-features", "{$p}-features-header", "{$p}-features-badge",
                 "{$p}-features-title", "{$p}-features-subtitle",
                 "{$p}-feature-icon", "{$p}-feature-item-title", "{$p}-feature-item-text"];

        $extra = match($patternId) {
            'grid-3col'            => ["{$p}-features-grid", "{$p}-feature-item"],
            'grid-4col'            => ["{$p}-features-grid", "{$p}-feature-item"],
            'grid-2col'            => ["{$p}-features-grid", "{$p}-feature-item", "{$p}-feature-icon-lg", "{$p}-feature-item-body"],
            'cards-elevated'       => ["{$p}-features-grid", "{$p}-feature-card", "{$p}-feature-card--elevated"],
            'cards-bordered'       => ["{$p}-features-grid", "{$p}-feature-card", "{$p}-feature-card--bordered"],
            'cards-glass'          => ["{$p}-features-grid", "{$p}-feature-card", "{$p}-feature-card--glass", "{$p}-features-glass-bg"],
            'alternating-rows'     => ["{$p}-features-rows", "{$p}-feature-row", "{$p}-feature-row--reverse", "{$p}-feature-row-icon", "{$p}-feature-row-content", "{$p}-feature-icon-circle"],
            'alternating-zigzag'   => ["{$p}-features-zigzag", "{$p}-feature-zigzag-row", "{$p}-feature-zigzag-row--alt", "{$p}-feature-zigzag-num", "{$p}-feature-zigzag-icon", "{$p}-feature-zigzag-content"],
            'alternating-timeline' => ["{$p}-features-timeline", "{$p}-features-timeline-line", "{$p}-feature-timeline-item", "{$p}-feature-timeline-item--right", "{$p}-feature-timeline-dot", "{$p}-feature-timeline-card"],
            'icon-list-horizontal' => ["{$p}-features-icon-row", "{$p}-feature-icon-item", "{$p}-feature-icon-circle"],
            'icon-list-numbered'   => ["{$p}-features-numbered", "{$p}-feature-numbered-item", "{$p}-feature-number", "{$p}-feature-numbered-icon", "{$p}-feature-numbered-body"],
            'icon-list-vertical'   => ["{$p}-features-split", "{$p}-features-header--left", "{$p}-features-vertical-list", "{$p}-feature-vertical-item", "{$p}-feature-icon-sm", "{$p}-feature-vertical-body"],
            'creative-bento'       => ["{$p}-features-bento", "{$p}-feature-bento-item", "{$p}-feature-bento-item--large"],
            'creative-tabs'        => ["{$p}-features-tabs-wrap", "{$p}-features-tab-nav", "{$p}-features-tab-btn", "{$p}-features-tab-btn--active", "{$p}-features-tab-panels", "{$p}-features-tab-panel", "{$p}-feature-tab-content", "{$p}-feature-icon-lg"],
            'creative-carousel'    => ["{$p}-features-carousel-track", "{$p}-feature-carousel-card"],
            default => [],
        };

        return array_merge($base, $extra);
    }

    private static function buildStructuralCSS(string $cssType, string $p): string
    {
        $css = self::css_base($p);

        $typeCSS = match($cssType) {
            'grid-3col'            => self::css_grid_3col($p),
            'grid-4col'            => self::css_grid_4col($p),
            'grid-2col'            => self::css_grid_2col($p),
            'cards-elevated'       => self::css_cards_elevated($p),
            'cards-bordered'       => self::css_cards_bordered($p),
            'cards-glass'          => self::css_cards_glass($p),
            'alternating-rows'     => self::css_alternating_rows($p),
            'alternating-zigzag'   => self::css_alternating_zigzag($p),
            'alternating-timeline' => self::css_alternating_timeline($p),
            'icon-list-horizontal' => self::css_icon_list_horizontal($p),
            'icon-list-numbered'   => self::css_icon_list_numbered($p),
            'icon-list-vertical'   => self::css_icon_list_vertical($p),
            'creative-bento'       => self::css_creative_bento($p),
            'creative-tabs'        => self::css_creative_tabs($p),
            'creative-carousel'    => self::css_creative_carousel($p),
            default                => self::css_grid_3col($p),
        };

        return $css . $typeCSS . self::css_responsive($p);
    }

    // --- Base (shared by all features patterns) ---
    private static function css_base(string $p): string
    {
        return <<<CSS

/* ═══ Features Structural CSS (auto-generated — do not edit) ═══ */
.{$p}-features {
  position: relative; overflow: hidden;
  padding: clamp(60px, 10vh, 120px) 0;
}
.{$p}-features .container {
  position: relative; z-index: 2;
}
.{$p}-features-header {
  text-align: center; max-width: 700px;
  margin: 0 auto clamp(40px, 6vw, 72px) auto;
}
.{$p}-features-badge {
  display: inline-block;
  font-size: 0.8125rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.12em;
  padding: 6px 16px; border-radius: 100px;
  margin-bottom: 16px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.15);
  color: var(--primary, #3b82f6);
  border: 1px solid rgba(var(--primary-rgb, 42,125,225), 0.25);
}
.{$p}-features-title {
  font-family: var(--font-heading, inherit);
  font-size: clamp(1.75rem, 4vw, 3rem);
  font-weight: 700; line-height: 1.15;
  margin: 0 0 16px 0;
  color: var(--text, #1e293b);
}
.{$p}-features-subtitle {
  font-size: clamp(1rem, 1.5vw, 1.125rem);
  line-height: 1.7; margin: 0;
  color: var(--text-muted, #64748b);
  max-width: 50ch; margin-left: auto; margin-right: auto;
}
.{$p}-feature-icon {
  width: 56px; height: 56px;
  display: flex; align-items: center; justify-content: center;
  border-radius: var(--radius, 12px);
  font-size: 1.375rem;
  background: rgba(var(--primary-rgb, 42,125,225), 0.1);
  color: var(--primary, #3b82f6);
  margin-bottom: 20px;
  flex-shrink: 0;
}
.{$p}-feature-icon-lg {
  width: 72px; height: 72px;
  display: flex; align-items: center; justify-content: center;
  border-radius: var(--radius, 12px);
  font-size: 1.75rem;
  background: rgba(var(--primary-rgb, 42,125,225), 0.1);
  color: var(--primary, #3b82f6);
  margin-bottom: 20px;
  flex-shrink: 0;
}
.{$p}-feature-icon-sm {
  width: 44px; height: 44px;
  display: flex; align-items: center; justify-content: center;
  border-radius: var(--radius, 10px);
  font-size: 1.125rem;
  background: rgba(var(--primary-rgb, 42,125,225), 0.1);
  color: var(--primary, #3b82f6);
  flex-shrink: 0;
}
.{$p}-feature-icon-circle {
  width: 64px; height: 64px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 50%;
  font-size: 1.5rem;
  background: rgba(var(--primary-rgb, 42,125,225), 0.1);
  color: var(--primary, #3b82f6);
  flex-shrink: 0;
}
.{$p}-feature-item-title {
  font-family: var(--font-heading, inherit);
  font-size: 1.125rem; font-weight: 600;
  line-height: 1.3; margin: 0 0 8px 0;
  color: var(--text, #1e293b);
}
.{$p}-feature-item-text {
  font-size: 0.9375rem; line-height: 1.7;
  margin: 0; color: var(--text-muted, #64748b);
}

CSS;
    }

    // --- Grid 3-Column ---
    private static function css_grid_3col(string $p): string
    {
        return <<<CSS
.{$p}-features--grid-3col .{$p}-features-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: clamp(24px, 3vw, 40px);
}
.{$p}-features--grid-3col .{$p}-feature-item {
  padding: clamp(24px, 3vw, 32px);
  border-radius: var(--radius, 12px);
  background: var(--surface, #f8fafc);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-features--grid-3col .{$p}-feature-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.08);
}

CSS;
    }

    // --- Grid 4-Column ---
    private static function css_grid_4col(string $p): string
    {
        return <<<CSS
.{$p}-features--grid-4col .{$p}-features-grid {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: clamp(16px, 2vw, 32px);
}
.{$p}-features--grid-4col .{$p}-feature-item {
  text-align: center;
  padding: clamp(20px, 2.5vw, 28px);
}
.{$p}-features--grid-4col .{$p}-feature-icon {
  margin-left: auto; margin-right: auto;
}

CSS;
    }

    // --- Grid 2-Column ---
    private static function css_grid_2col(string $p): string
    {
        return <<<CSS
.{$p}-features--grid-2col .{$p}-features-grid {
  display: grid; grid-template-columns: repeat(2, 1fr);
  gap: clamp(24px, 3vw, 40px);
}
.{$p}-features--grid-2col .{$p}-feature-item {
  display: flex; gap: 20px; align-items: flex-start;
  padding: clamp(24px, 3vw, 36px);
  border-radius: var(--radius, 12px);
  background: var(--surface, #f8fafc);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-features--grid-2col .{$p}-feature-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.08);
}
.{$p}-features--grid-2col .{$p}-feature-icon-lg {
  margin-bottom: 0;
}

CSS;
    }

    // --- Cards Elevated ---
    private static function css_cards_elevated(string $p): string
    {
        return <<<CSS
.{$p}-features--cards-elevated .{$p}-features-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: clamp(24px, 3vw, 32px);
}
.{$p}-feature-card--elevated {
  padding: clamp(28px, 3vw, 40px);
  border-radius: var(--radius, 12px);
  background: var(--surface, #fff);
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-feature-card--elevated:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 48px rgba(0,0,0,0.12);
}

CSS;
    }

    // --- Cards Bordered ---
    private static function css_cards_bordered(string $p): string
    {
        return <<<CSS
.{$p}-features--cards-bordered .{$p}-features-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: clamp(24px, 3vw, 32px);
}
.{$p}-feature-card--bordered {
  padding: clamp(28px, 3vw, 40px);
  border-radius: var(--radius, 12px);
  background: transparent;
  border: 1px solid rgba(var(--text-rgb, 30,41,59), 0.12);
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-feature-card--bordered:hover {
  border-color: var(--primary, #3b82f6);
  box-shadow: 0 8px 30px rgba(var(--primary-rgb, 42,125,225), 0.1);
}

CSS;
    }

    // --- Cards Glass ---
    private static function css_cards_glass(string $p): string
    {
        return <<<CSS
.{$p}-features--cards-glass {
  background: #0f172a;
}
.{$p}-features-glass-bg {
  position: absolute; inset: 0; z-index: 0;
  background: linear-gradient(135deg, rgba(var(--primary-rgb, 42,125,225), 0.3) 0%, rgba(var(--secondary-rgb, 139,92,246), 0.3) 100%);
}
.{$p}-features--cards-glass .{$p}-features-title {
  color: #fff;
}
.{$p}-features--cards-glass .{$p}-features-subtitle {
  color: rgba(255,255,255,0.65);
}
.{$p}-features--cards-glass .{$p}-features-badge {
  background: rgba(255,255,255,0.1); color: #fff;
  border-color: rgba(255,255,255,0.2);
}
.{$p}-features--cards-glass .{$p}-features-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: clamp(24px, 3vw, 32px);
}
.{$p}-feature-card--glass {
  padding: clamp(28px, 3vw, 40px);
  border-radius: var(--radius, 16px);
  background: rgba(255,255,255,0.08);
  backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255,255,255,0.12);
  transition: transform 0.3s ease, background 0.3s ease;
}
.{$p}-feature-card--glass:hover {
  transform: translateY(-4px);
  background: rgba(255,255,255,0.14);
}
.{$p}-feature-card--glass .{$p}-feature-icon {
  background: rgba(255,255,255,0.1);
  color: var(--primary, #3b82f6);
}
.{$p}-feature-card--glass .{$p}-feature-item-title {
  color: #fff;
}
.{$p}-feature-card--glass .{$p}-feature-item-text {
  color: rgba(255,255,255,0.65);
}

CSS;
    }

    // --- Alternating Rows ---
    private static function css_alternating_rows(string $p): string
    {
        return <<<CSS
.{$p}-features-rows {
  display: flex; flex-direction: column;
  gap: clamp(32px, 4vw, 56px);
}
.{$p}-feature-row {
  display: grid; grid-template-columns: auto 1fr;
  gap: clamp(24px, 4vw, 48px); align-items: center;
}
.{$p}-feature-row--reverse {
  direction: rtl;
}
.{$p}-feature-row--reverse > * {
  direction: ltr;
}
.{$p}-feature-row-content {
  max-width: 520px;
}

CSS;
    }

    // --- Alternating Zigzag ---
    private static function css_alternating_zigzag(string $p): string
    {
        return <<<CSS
.{$p}-features-zigzag {
  display: flex; flex-direction: column;
  gap: 0;
}
.{$p}-feature-zigzag-row {
  display: grid; grid-template-columns: 80px 56px 1fr;
  gap: 20px; align-items: center;
  padding: clamp(24px, 3vw, 40px) clamp(20px, 3vw, 32px);
  border-radius: var(--radius, 12px);
  transition: background 0.3s ease;
}
.{$p}-feature-zigzag-row:hover {
  background: var(--surface, #f8fafc);
}
.{$p}-feature-zigzag-row--alt {
  direction: rtl;
}
.{$p}-feature-zigzag-row--alt > * {
  direction: ltr;
}
.{$p}-feature-zigzag-num span {
  font-family: var(--font-heading, inherit);
  font-size: clamp(2rem, 4vw, 3.5rem);
  font-weight: 800; line-height: 1;
  color: rgba(var(--primary-rgb, 42,125,225), 0.15);
}
.{$p}-feature-zigzag-icon {
  width: 56px; height: 56px;
  display: flex; align-items: center; justify-content: center;
  border-radius: var(--radius, 12px);
  font-size: 1.375rem;
  background: rgba(var(--primary-rgb, 42,125,225), 0.1);
  color: var(--primary, #3b82f6);
}

CSS;
    }

    // --- Alternating Timeline ---
    private static function css_alternating_timeline(string $p): string
    {
        return <<<CSS
.{$p}-features-timeline {
  position: relative;
  max-width: 800px; margin: 0 auto;
}
.{$p}-features-timeline-line {
  position: absolute; left: 50%; top: 0; bottom: 0;
  width: 2px; transform: translateX(-50%);
  background: rgba(var(--primary-rgb, 42,125,225), 0.2);
}
.{$p}-feature-timeline-item {
  position: relative;
  display: grid; grid-template-columns: 1fr auto 1fr;
  gap: 24px; align-items: center;
  margin-bottom: clamp(32px, 4vw, 56px);
}
.{$p}-feature-timeline-item:last-child {
  margin-bottom: 0;
}
.{$p}-feature-timeline-dot {
  grid-column: 2;
  width: 52px; height: 52px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 50%; font-size: 1.25rem;
  background: var(--primary, #3b82f6);
  color: var(--primary-contrast, #fff);
  box-shadow: 0 0 0 6px rgba(var(--primary-rgb, 42,125,225), 0.15);
  z-index: 2;
}
.{$p}-feature-timeline-card {
  padding: clamp(20px, 2.5vw, 28px);
  border-radius: var(--radius, 12px);
  background: var(--surface, #f8fafc);
  box-shadow: 0 4px 16px rgba(0,0,0,0.06);
}
.{$p}-feature-timeline-item .{$p}-feature-timeline-card {
  grid-column: 1; text-align: right;
}
.{$p}-feature-timeline-item--right .{$p}-feature-timeline-card {
  grid-column: 3; text-align: left;
}
.{$p}-feature-timeline-item::before {
  content: ''; grid-column: 3;
}
.{$p}-feature-timeline-item--right::before {
  content: ''; grid-column: 1;
}

CSS;
    }

    // --- Icon List Horizontal ---
    private static function css_icon_list_horizontal(string $p): string
    {
        return <<<CSS
.{$p}-features-icon-row {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: clamp(24px, 3vw, 40px);
  text-align: center;
}
.{$p}-feature-icon-item {
  display: flex; flex-direction: column; align-items: center;
}
.{$p}-feature-icon-item .{$p}-feature-icon-circle {
  margin-bottom: 20px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-feature-icon-item:hover .{$p}-feature-icon-circle {
  transform: scale(1.1);
  box-shadow: 0 8px 30px rgba(var(--primary-rgb, 42,125,225), 0.2);
}

CSS;
    }

    // --- Icon List Numbered ---
    private static function css_icon_list_numbered(string $p): string
    {
        return <<<CSS
.{$p}-features-numbered {
  display: flex; flex-direction: column;
  gap: clamp(16px, 2vw, 24px);
  max-width: 700px; margin: 0 auto;
}
.{$p}-feature-numbered-item {
  display: grid; grid-template-columns: 56px 48px 1fr;
  gap: 16px; align-items: center;
  padding: clamp(20px, 2.5vw, 28px);
  border-radius: var(--radius, 12px);
  background: var(--surface, #f8fafc);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-feature-numbered-item:hover {
  transform: translateX(8px);
  box-shadow: 0 8px 30px rgba(0,0,0,0.06);
}
.{$p}-feature-number span {
  font-family: var(--font-heading, inherit);
  font-size: 1.75rem; font-weight: 800;
  color: var(--primary, #3b82f6);
  opacity: 0.6;
}
.{$p}-feature-numbered-icon {
  width: 48px; height: 48px;
  display: flex; align-items: center; justify-content: center;
  border-radius: var(--radius, 10px); font-size: 1.25rem;
  background: rgba(var(--primary-rgb, 42,125,225), 0.1);
  color: var(--primary, #3b82f6);
}

CSS;
    }

    // --- Icon List Vertical ---
    private static function css_icon_list_vertical(string $p): string
    {
        return <<<CSS
.{$p}-features--icon-list-vertical {
  padding: clamp(60px, 10vh, 120px) 0;
}
.{$p}-features-split {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(40px, 6vw, 80px); align-items: start;
}
.{$p}-features-header--left {
  text-align: left; max-width: none; margin: 0;
  position: sticky; top: 120px;
}
.{$p}-features-header--left .{$p}-features-subtitle {
  margin-left: 0; margin-right: 0;
}
.{$p}-features-vertical-list {
  display: flex; flex-direction: column;
  gap: clamp(16px, 2vw, 24px);
}
.{$p}-feature-vertical-item {
  display: flex; gap: 16px; align-items: flex-start;
  padding: clamp(16px, 2vw, 24px);
  border-radius: var(--radius, 12px);
  transition: background 0.3s ease;
}
.{$p}-feature-vertical-item:hover {
  background: var(--surface, #f8fafc);
}

CSS;
    }

    // --- Creative Bento ---
    private static function css_creative_bento(string $p): string
    {
        return <<<CSS
.{$p}-features-bento {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  grid-template-rows: auto auto;
  gap: clamp(16px, 2vw, 24px);
}
.{$p}-feature-bento-item {
  padding: clamp(24px, 3vw, 36px);
  border-radius: var(--radius, 16px);
  background: var(--surface, #f8fafc);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-feature-bento-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.08);
}
.{$p}-feature-bento-item--large {
  grid-column: span 2;
  grid-row: span 2;
  display: flex; flex-direction: column; justify-content: center;
  background: var(--primary, #3b82f6);
}
.{$p}-feature-bento-item--large .{$p}-feature-icon {
  background: rgba(255,255,255,0.15);
  color: #fff;
  width: 72px; height: 72px; font-size: 1.75rem;
}
.{$p}-feature-bento-item--large .{$p}-feature-item-title {
  color: #fff; font-size: 1.375rem;
}
.{$p}-feature-bento-item--large .{$p}-feature-item-text {
  color: rgba(255,255,255,0.8);
}

CSS;
    }

    // --- Creative Tabs ---
    private static function css_creative_tabs(string $p): string
    {
        return <<<CSS
.{$p}-features-tabs-wrap {
  max-width: 800px; margin: 0 auto;
}
.{$p}-features-tab-nav {
  display: flex; gap: 4px;
  border-bottom: 2px solid rgba(var(--text-rgb, 30,41,59), 0.1);
  margin-bottom: clamp(24px, 3vw, 40px);
  overflow-x: auto;
}
.{$p}-features-tab-btn {
  display: flex; align-items: center; gap: 8px;
  padding: 14px 24px;
  border: none; background: none; cursor: pointer;
  font-size: 0.9375rem; font-weight: 600;
  color: var(--text-muted, #64748b);
  border-bottom: 2px solid transparent;
  margin-bottom: -2px;
  transition: color 0.3s ease, border-color 0.3s ease;
  white-space: nowrap;
}
.{$p}-features-tab-btn:hover {
  color: var(--text, #1e293b);
}
.{$p}-features-tab-btn--active {
  color: var(--primary, #3b82f6);
  border-bottom-color: var(--primary, #3b82f6);
}
.{$p}-features-tab-btn i {
  font-size: 1rem;
}
.{$p}-features-tab-panel {
  animation: {$p}-tab-fade-in 0.3s ease;
}
@keyframes {$p}-tab-fade-in {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
.{$p}-feature-tab-content {
  padding: clamp(24px, 3vw, 40px);
  border-radius: var(--radius, 12px);
  background: var(--surface, #f8fafc);
  text-align: center;
}
.{$p}-feature-tab-content .{$p}-feature-icon-lg {
  margin-left: auto; margin-right: auto;
}
.{$p}-feature-tab-content .{$p}-feature-item-title {
  font-size: 1.375rem;
}
.{$p}-feature-tab-content .{$p}-feature-item-text {
  max-width: 50ch; margin-left: auto; margin-right: auto;
}

CSS;
    }

    // --- Creative Carousel ---
    private static function css_creative_carousel(string $p): string
    {
        return <<<CSS
.{$p}-features-carousel-track {
  display: flex; gap: clamp(16px, 2vw, 24px);
  overflow-x: auto; scroll-snap-type: x mandatory;
  -webkit-overflow-scrolling: touch;
  padding: 0 clamp(16px, 4vw, 80px) 24px;
  scrollbar-width: thin;
}
.{$p}-features-carousel-track::-webkit-scrollbar {
  height: 6px;
}
.{$p}-features-carousel-track::-webkit-scrollbar-track {
  background: rgba(var(--text-rgb, 30,41,59), 0.05);
  border-radius: 3px;
}
.{$p}-features-carousel-track::-webkit-scrollbar-thumb {
  background: rgba(var(--primary-rgb, 42,125,225), 0.3);
  border-radius: 3px;
}
.{$p}-feature-carousel-card {
  flex: 0 0 clamp(260px, 30vw, 340px);
  scroll-snap-align: start;
  padding: clamp(28px, 3vw, 40px);
  border-radius: var(--radius, 16px);
  background: var(--surface, #f8fafc);
  border: 1px solid rgba(var(--text-rgb, 30,41,59), 0.08);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-feature-carousel-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.08);
}

CSS;
    }

    // --- Responsive ---
    private static function css_responsive(string $p): string
    {
        return <<<CSS
@media (max-width: 1024px) {
  .{$p}-features--grid-4col .{$p}-features-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .{$p}-features--cards-elevated .{$p}-features-grid,
  .{$p}-features--cards-bordered .{$p}-features-grid,
  .{$p}-features--cards-glass .{$p}-features-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .{$p}-features-bento {
    grid-template-columns: repeat(2, 1fr);
  }
  .{$p}-feature-bento-item--large {
    grid-column: span 2;
    grid-row: span 1;
  }
}
@media (max-width: 768px) {
  .{$p}-features--grid-3col .{$p}-features-grid,
  .{$p}-features--grid-4col .{$p}-features-grid,
  .{$p}-features--grid-2col .{$p}-features-grid,
  .{$p}-features--cards-elevated .{$p}-features-grid,
  .{$p}-features--cards-bordered .{$p}-features-grid,
  .{$p}-features--cards-glass .{$p}-features-grid {
    grid-template-columns: 1fr !important;
  }
  .{$p}-feature-row {
    grid-template-columns: 1fr !important;
    text-align: center;
  }
  .{$p}-feature-row--reverse {
    direction: ltr;
  }
  .{$p}-feature-row-icon {
    display: flex; justify-content: center;
  }
  .{$p}-feature-row-content {
    max-width: none;
  }
  .{$p}-feature-zigzag-row {
    grid-template-columns: 1fr !important;
    text-align: center;
  }
  .{$p}-feature-zigzag-row--alt {
    direction: ltr;
  }
  .{$p}-feature-zigzag-num,
  .{$p}-feature-zigzag-icon {
    display: flex; justify-content: center;
  }
  .{$p}-feature-zigzag-num span {
    font-size: 2rem;
  }
  .{$p}-features-timeline-line {
    left: 26px;
  }
  .{$p}-feature-timeline-item {
    grid-template-columns: auto 1fr !important;
  }
  .{$p}-feature-timeline-item .{$p}-feature-timeline-card,
  .{$p}-feature-timeline-item--right .{$p}-feature-timeline-card {
    grid-column: 2; text-align: left;
  }
  .{$p}-feature-timeline-item::before,
  .{$p}-feature-timeline-item--right::before {
    display: none;
  }
  .{$p}-feature-timeline-dot {
    grid-column: 1;
    width: 44px; height: 44px; font-size: 1rem;
  }
  .{$p}-features-icon-row {
    grid-template-columns: repeat(2, 1fr) !important;
  }
  .{$p}-feature-numbered-item {
    grid-template-columns: 40px 40px 1fr;
    gap: 12px;
  }
  .{$p}-features-split {
    grid-template-columns: 1fr !important;
  }
  .{$p}-features-header--left {
    position: static; text-align: center;
  }
  .{$p}-features-header--left .{$p}-features-subtitle {
    margin-left: auto; margin-right: auto;
  }
  .{$p}-features-bento {
    grid-template-columns: 1fr !important;
  }
  .{$p}-feature-bento-item--large {
    grid-column: span 1;
    grid-row: span 1;
  }
  .{$p}-features-tab-nav {
    flex-wrap: nowrap;
  }
  .{$p}-features-tab-btn {
    padding: 12px 16px; font-size: 0.875rem;
  }
}

CSS;
    }
}
