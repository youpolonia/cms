<?php
/**
 * Clients/Logos Section Pattern Registry
 * 
 * Pre-built clients/logos HTML layouts with structural CSS.
 * AI generates only decorative CSS (colors, fonts, effects) — never the structure.
 * 
 * 8 patterns across 3 groups.
 * @since 2026-02-19
 */

class ClientsPatternRegistry
{
    // ═══════════════════════════════════════
    // PATTERN DEFINITIONS
    // ═══════════════════════════════════════

    private static array $patterns = [
        // --- Strip (horizontal logo rows) ---
        ['id'=>'strip-simple',    'group'=>'strip', 'css_type'=>'strip-simple',
         'best_for'=>['saas','tech','startup','fintech','platform','digital','ai',
                      'consulting','agency','marketing','seo','web-design']],
        ['id'=>'strip-scroll',    'group'=>'strip', 'css_type'=>'strip-scroll',
         'best_for'=>['ecommerce','marketplace','retail','fashion','beauty',
                      'entertainment','media','podcast','music','gaming']],
        ['id'=>'strip-two-rows',  'group'=>'strip', 'css_type'=>'strip-two-rows',
         'best_for'=>['enterprise','corporate','manufacturing','logistics','engineering',
                      'insurance','bank','financial','accounting','legal']],

        // --- Grid (structured logo layouts) ---
        ['id'=>'grid-bordered',   'group'=>'grid', 'css_type'=>'grid-bordered',
         'best_for'=>['healthcare','clinic','hospital','dental','pharmacy',
                      'nonprofit','charity','education','university','school']],
        ['id'=>'grid-cards',      'group'=>'grid', 'css_type'=>'grid-cards',
         'best_for'=>['real-estate','construction','architecture','interior-design',
                      'paving','roofing','plumbing','electrical','hvac','landscaping']],
        ['id'=>'grid-with-names', 'group'=>'grid', 'css_type'=>'grid-with-names',
         'best_for'=>['coaching','library','fitness','sports','gym','wellness',
                      'spa','yoga','nutrition','personal-training']],

        // --- Featured (highlight specific clients) ---
        ['id'=>'featured-with-testimonial', 'group'=>'featured', 'css_type'=>'featured-with-testimonial',
         'best_for'=>['restaurant','bakery','cafe','bar','hotel','resort','winery',
                      'brewery','fine-dining','luxury','country-club','catering']],
        ['id'=>'featured-case-study',       'group'=>'featured', 'css_type'=>'featured-case-study',
         'best_for'=>['travel','tourism','adventure','outdoor','event-planning',
                      'wedding','florist','photography','art','gallery','museum']],
    ];

    // ═══════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════

    /**
     * Pick the best clients pattern for an industry.
     */
    public static function pickPattern(string $industry): string
    {
        $industry = strtolower(trim($industry));
        foreach (self::$patterns as $p) {
            if (in_array($industry, $p['best_for'], true)) {
                return $p['id'];
            }
        }
        // Fallback: random from strip group (most versatile)
        $stripPatterns = array_filter(self::$patterns, fn($p) => $p['group'] === 'strip');
        $stripIds = array_column(array_values($stripPatterns), 'id');
        return $stripIds[array_rand($stripIds)];
    }

    /**
     * Render a clients pattern.
     * Returns ['html'=>..., 'structural_css'=>..., 'pattern_id'=>..., 'classes'=>[...], 'fields'=>[...]]
     */
    public static function render(string $patternId, string $prefix, array $brief): array
    {
        $def = null;
        foreach (self::$patterns as $p) {
            if ($p['id'] === $patternId) { $def = $p; break; }
        }
        if (!$def) {
            $def = self::$patterns[0]; // fallback to strip-simple
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
        // Common fields all clients patterns have
        $common = [
            'title'   => ['type' => 'text',     'label' => 'Section Title'],
            'subtitle'=> ['type' => 'textarea', 'label' => 'Section Subtitle'],
            'badge'   => ['type' => 'text',     'label' => 'Badge / Label'],
            'logo1'   => ['type' => 'image',    'label' => 'Client 1 Logo'],
            'name1'   => ['type' => 'text',     'label' => 'Client 1 Name'],
            'logo2'   => ['type' => 'image',    'label' => 'Client 2 Logo'],
            'name2'   => ['type' => 'text',     'label' => 'Client 2 Name'],
            'logo3'   => ['type' => 'image',    'label' => 'Client 3 Logo'],
            'name3'   => ['type' => 'text',     'label' => 'Client 3 Name'],
            'logo4'   => ['type' => 'image',    'label' => 'Client 4 Logo'],
            'name4'   => ['type' => 'text',     'label' => 'Client 4 Name'],
            'logo5'   => ['type' => 'image',    'label' => 'Client 5 Logo'],
            'name5'   => ['type' => 'text',     'label' => 'Client 5 Name'],
            'logo6'   => ['type' => 'image',    'label' => 'Client 6 Logo'],
            'name6'   => ['type' => 'text',     'label' => 'Client 6 Name'],
        ];

        // Pattern-specific extras
        $extras = match($patternId) {
            'featured-with-testimonial' => [
                'featured_quote'  => ['type' => 'textarea', 'label' => 'Featured Quote'],
                'featured_author' => ['type' => 'text',     'label' => 'Quote Author'],
                'featured_role'   => ['type' => 'text',     'label' => 'Author Role'],
            ],
            'featured-case-study' => [
                'featured_quote'  => ['type' => 'textarea', 'label' => 'Featured Quote'],
                'featured_author' => ['type' => 'text',     'label' => 'Quote Author'],
                'featured_role'   => ['type' => 'text',     'label' => 'Author Role'],
                'featured_stat1'  => ['type' => 'text',     'label' => 'Result 1'],
                'featured_stat2'  => ['type' => 'text',     'label' => 'Result 2'],
                'featured_stat3'  => ['type' => 'text',     'label' => 'Result 3'],
            ],
            default => [],
        };

        return array_merge($common, $extras);
    }

    /**
     * Get decorative CSS guide for a pattern (tells AI what visual CSS to write).
     */
    public static function getDecorativeGuide(string $patternId): string
    {
        return match($patternId) {
            'strip-simple' => <<<'GUIDE'
Logos: filter: grayscale(100%) opacity(0.5), transition: 0.3s ease.
Hover: filter: grayscale(0%) opacity(1), transform: scale(1.05).
Subtle separator gaps between logos, no visible borders.
Clean, professional feel with muted default state.
GUIDE,
            'strip-scroll' => <<<'GUIDE'
Infinite marquee: animation: scroll 30s linear infinite.
Edge fade: mask-image: linear-gradient(90deg, transparent, black 10%, black 90%, transparent).
Logos: grayscale filter, opacity 0.5, hover pauses animation.
Seamless loop by duplicating logo set.
GUIDE,
            'strip-two-rows' => <<<'GUIDE'
Two marquee rows: first scrolls left, second scrolls right (animation-direction: reverse).
Staggered animation timing: row2 slightly slower or offset.
Edge fade masks on both rows.
Logos: grayscale + reduced opacity, consistent sizing.
GUIDE,
            'grid-bordered' => <<<'GUIDE'
Grid cells: border: 1px solid var(--border), collapse borders with margin: -0.5px.
Hover: border-color transitions to var(--primary), subtle background tint.
Logos centered in cells: grayscale default, color on hover.
Clean, structured corporate look.
GUIDE,
            'grid-cards' => <<<'GUIDE'
Cards: background: var(--surface), border-radius: var(--radius), box-shadow: 0 4px 20px rgba(0,0,0,0.06).
Hover: transform: translateY(-4px), box-shadow intensifies.
Logos: grayscale default, full color on card hover.
Subtle background tint change on hover.
GUIDE,
            'grid-with-names' => <<<'GUIDE'
Logo above, company name below: name font-size: 0.8rem, uppercase, color: var(--text-muted).
Logos: grayscale + opacity 0.5, hover reveals full color.
Name: font-weight: 600, letter-spacing: 0.06em.
Hover transitions logo filter and name color simultaneously.
GUIDE,
            'featured-with-testimonial' => <<<'GUIDE'
Logo strip: grayscale, active/featured client highlighted with full color + scale.
Quote card below: font-style: italic, font-size: 1.2rem, curly quotes via ::before/::after.
Author name: font-weight: 700, role: color: var(--text-muted).
Border-bottom separator between logo strip and quote.
GUIDE,
            'featured-case-study' => <<<'GUIDE'
Case study card: background: var(--surface), border-radius: var(--radius), border: 1px solid var(--border).
Quote: italic, 1.2rem, curly quotes, author bold below.
Result stats: font-weight: 700, color: var(--primary), pill background: rgba(var(--primary-rgb), 0.08).
CTA link: color: var(--primary), hover underline or arrow animation.
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

        $title = '';
        if ($name) {
            $title = 'Trusted By Industry Leaders';
        }

        $badge = '';
        if (!empty($brief['style_preset'])) {
            $badge = ucwords(str_replace('-', ' ', $brief['style_preset']));
        } elseif ($industry) {
            $badge = ucwords(str_replace('-', ' ', $industry));
        }

        $replacements = [];
        if ($title) $replacements["theme_get('clients.title', 'Trusted By')"] = "theme_get('clients.title', '" . addslashes($title) . "')";
        if ($badge) $replacements["theme_get('clients.badge', '')"] = "theme_get('clients.badge', '" . addslashes($badge) . "')";

        foreach ($replacements as $search => $replace) {
            $html = str_replace($search, $replace, $html);
        }

        return $html;
    }

    private static function buildHTML(string $patternId, string $p): string
    {
        $templates = self::getTemplates($p);
        return $templates[$patternId] ?? $templates['strip-simple'];
    }

    private static function getTemplates(string $p): array
    {
        return [

// ── Strip Simple: Single row of logos, grayscale, color on hover ──
'strip-simple' => <<<HTML
<?php
\$clientsTitle = theme_get('clients.title', 'Trusted By');
\$clientsSubtitle = theme_get('clients.subtitle', 'Partnering with leading companies worldwide');
\$clientsBadge = theme_get('clients.badge', '');
\$logo1 = theme_get('clients.logo1', '');
\$name1 = theme_get('clients.name1', 'Company One');
\$logo2 = theme_get('clients.logo2', '');
\$name2 = theme_get('clients.name2', 'Company Two');
\$logo3 = theme_get('clients.logo3', '');
\$name3 = theme_get('clients.name3', 'Company Three');
\$logo4 = theme_get('clients.logo4', '');
\$name4 = theme_get('clients.name4', 'Company Four');
\$logo5 = theme_get('clients.logo5', '');
\$name5 = theme_get('clients.name5', 'Company Five');
\$logo6 = theme_get('clients.logo6', '');
\$name6 = theme_get('clients.name6', 'Company Six');
?>
<section class="{$p}-clients {$p}-clients--strip-simple" id="clients">
  <div class="container">
    <div class="{$p}-clients-header" data-animate="fade-up">
      <?php if (\$clientsBadge): ?><span class="{$p}-clients-badge" data-ts="clients.badge"><?= esc(\$clientsBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-clients-title" data-ts="clients.title"><?= esc(\$clientsTitle) ?></h2>
      <p class="{$p}-clients-subtitle" data-ts="clients.subtitle"><?= esc(\$clientsSubtitle) ?></p>
    </div>
    <div class="{$p}-clients-strip" data-animate="fade-up">
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo1) ?>" alt="<?= esc(\$name1) ?>" data-ts-bg="clients.logo1" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo2) ?>" alt="<?= esc(\$name2) ?>" data-ts-bg="clients.logo2" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo3) ?>" alt="<?= esc(\$name3) ?>" data-ts-bg="clients.logo3" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo4) ?>" alt="<?= esc(\$name4) ?>" data-ts-bg="clients.logo4" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo5) ?>" alt="<?= esc(\$name5) ?>" data-ts-bg="clients.logo5" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo6) ?>" alt="<?= esc(\$name6) ?>" data-ts-bg="clients.logo6" loading="lazy"></div>
    </div>
  </div>
</section>
HTML,

// ── Strip Scroll: Infinite CSS scroll animation (marquee effect) ──
'strip-scroll' => <<<HTML
<?php
\$clientsTitle = theme_get('clients.title', 'Trusted By');
\$clientsSubtitle = theme_get('clients.subtitle', 'Partnering with leading companies worldwide');
\$clientsBadge = theme_get('clients.badge', '');
\$logo1 = theme_get('clients.logo1', '');
\$name1 = theme_get('clients.name1', 'Company One');
\$logo2 = theme_get('clients.logo2', '');
\$name2 = theme_get('clients.name2', 'Company Two');
\$logo3 = theme_get('clients.logo3', '');
\$name3 = theme_get('clients.name3', 'Company Three');
\$logo4 = theme_get('clients.logo4', '');
\$name4 = theme_get('clients.name4', 'Company Four');
\$logo5 = theme_get('clients.logo5', '');
\$name5 = theme_get('clients.name5', 'Company Five');
\$logo6 = theme_get('clients.logo6', '');
\$name6 = theme_get('clients.name6', 'Company Six');
?>
<section class="{$p}-clients {$p}-clients--strip-scroll" id="clients">
  <div class="container">
    <div class="{$p}-clients-header" data-animate="fade-up">
      <?php if (\$clientsBadge): ?><span class="{$p}-clients-badge" data-ts="clients.badge"><?= esc(\$clientsBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-clients-title" data-ts="clients.title"><?= esc(\$clientsTitle) ?></h2>
      <p class="{$p}-clients-subtitle" data-ts="clients.subtitle"><?= esc(\$clientsSubtitle) ?></p>
    </div>
  </div>
  <div class="{$p}-clients-marquee">
    <div class="{$p}-clients-marquee-track">
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo1) ?>" alt="<?= esc(\$name1) ?>" data-ts-bg="clients.logo1" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo2) ?>" alt="<?= esc(\$name2) ?>" data-ts-bg="clients.logo2" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo3) ?>" alt="<?= esc(\$name3) ?>" data-ts-bg="clients.logo3" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo4) ?>" alt="<?= esc(\$name4) ?>" data-ts-bg="clients.logo4" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo5) ?>" alt="<?= esc(\$name5) ?>" data-ts-bg="clients.logo5" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo6) ?>" alt="<?= esc(\$name6) ?>" data-ts-bg="clients.logo6" loading="lazy"></div>
      <!-- Duplicate set for seamless loop -->
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo1) ?>" alt="<?= esc(\$name1) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo2) ?>" alt="<?= esc(\$name2) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo3) ?>" alt="<?= esc(\$name3) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo4) ?>" alt="<?= esc(\$name4) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo5) ?>" alt="<?= esc(\$name5) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo6) ?>" alt="<?= esc(\$name6) ?>" loading="lazy"></div>
    </div>
  </div>
</section>
HTML,

// ── Strip Two Rows: Two rows of logos scrolling in opposite directions ──
'strip-two-rows' => <<<HTML
<?php
\$clientsTitle = theme_get('clients.title', 'Trusted By');
\$clientsSubtitle = theme_get('clients.subtitle', 'Partnering with leading companies worldwide');
\$clientsBadge = theme_get('clients.badge', '');
\$logo1 = theme_get('clients.logo1', '');
\$name1 = theme_get('clients.name1', 'Company One');
\$logo2 = theme_get('clients.logo2', '');
\$name2 = theme_get('clients.name2', 'Company Two');
\$logo3 = theme_get('clients.logo3', '');
\$name3 = theme_get('clients.name3', 'Company Three');
\$logo4 = theme_get('clients.logo4', '');
\$name4 = theme_get('clients.name4', 'Company Four');
\$logo5 = theme_get('clients.logo5', '');
\$name5 = theme_get('clients.name5', 'Company Five');
\$logo6 = theme_get('clients.logo6', '');
\$name6 = theme_get('clients.name6', 'Company Six');
?>
<section class="{$p}-clients {$p}-clients--strip-two-rows" id="clients">
  <div class="container">
    <div class="{$p}-clients-header" data-animate="fade-up">
      <?php if (\$clientsBadge): ?><span class="{$p}-clients-badge" data-ts="clients.badge"><?= esc(\$clientsBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-clients-title" data-ts="clients.title"><?= esc(\$clientsTitle) ?></h2>
      <p class="{$p}-clients-subtitle" data-ts="clients.subtitle"><?= esc(\$clientsSubtitle) ?></p>
    </div>
  </div>
  <div class="{$p}-clients-marquee {$p}-clients-marquee--row1">
    <div class="{$p}-clients-marquee-track">
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo1) ?>" alt="<?= esc(\$name1) ?>" data-ts-bg="clients.logo1" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo2) ?>" alt="<?= esc(\$name2) ?>" data-ts-bg="clients.logo2" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo3) ?>" alt="<?= esc(\$name3) ?>" data-ts-bg="clients.logo3" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo4) ?>" alt="<?= esc(\$name4) ?>" data-ts-bg="clients.logo4" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo5) ?>" alt="<?= esc(\$name5) ?>" data-ts-bg="clients.logo5" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo6) ?>" alt="<?= esc(\$name6) ?>" data-ts-bg="clients.logo6" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo1) ?>" alt="<?= esc(\$name1) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo2) ?>" alt="<?= esc(\$name2) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo3) ?>" alt="<?= esc(\$name3) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo4) ?>" alt="<?= esc(\$name4) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo5) ?>" alt="<?= esc(\$name5) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo6) ?>" alt="<?= esc(\$name6) ?>" loading="lazy"></div>
    </div>
  </div>
  <div class="{$p}-clients-marquee {$p}-clients-marquee--row2">
    <div class="{$p}-clients-marquee-track {$p}-clients-marquee-track--reverse">
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo4) ?>" alt="<?= esc(\$name4) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo5) ?>" alt="<?= esc(\$name5) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo6) ?>" alt="<?= esc(\$name6) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo1) ?>" alt="<?= esc(\$name1) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo2) ?>" alt="<?= esc(\$name2) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo3) ?>" alt="<?= esc(\$name3) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo4) ?>" alt="<?= esc(\$name4) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo5) ?>" alt="<?= esc(\$name5) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo6) ?>" alt="<?= esc(\$name6) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo1) ?>" alt="<?= esc(\$name1) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo2) ?>" alt="<?= esc(\$name2) ?>" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo3) ?>" alt="<?= esc(\$name3) ?>" loading="lazy"></div>
    </div>
  </div>
</section>
HTML,

// ── Grid Bordered: Grid of logos in bordered cells ──
'grid-bordered' => <<<HTML
<?php
\$clientsTitle = theme_get('clients.title', 'Trusted By');
\$clientsSubtitle = theme_get('clients.subtitle', 'Partnering with leading companies worldwide');
\$clientsBadge = theme_get('clients.badge', '');
\$logo1 = theme_get('clients.logo1', '');
\$name1 = theme_get('clients.name1', 'Company One');
\$logo2 = theme_get('clients.logo2', '');
\$name2 = theme_get('clients.name2', 'Company Two');
\$logo3 = theme_get('clients.logo3', '');
\$name3 = theme_get('clients.name3', 'Company Three');
\$logo4 = theme_get('clients.logo4', '');
\$name4 = theme_get('clients.name4', 'Company Four');
\$logo5 = theme_get('clients.logo5', '');
\$name5 = theme_get('clients.name5', 'Company Five');
\$logo6 = theme_get('clients.logo6', '');
\$name6 = theme_get('clients.name6', 'Company Six');
?>
<section class="{$p}-clients {$p}-clients--grid-bordered" id="clients">
  <div class="container">
    <div class="{$p}-clients-header" data-animate="fade-up">
      <?php if (\$clientsBadge): ?><span class="{$p}-clients-badge" data-ts="clients.badge"><?= esc(\$clientsBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-clients-title" data-ts="clients.title"><?= esc(\$clientsTitle) ?></h2>
      <p class="{$p}-clients-subtitle" data-ts="clients.subtitle"><?= esc(\$clientsSubtitle) ?></p>
    </div>
    <div class="{$p}-clients-grid" data-animate="fade-up">
      <div class="{$p}-clients-cell"><img src="<?= esc(\$logo1) ?>" alt="<?= esc(\$name1) ?>" data-ts-bg="clients.logo1" loading="lazy"></div>
      <div class="{$p}-clients-cell"><img src="<?= esc(\$logo2) ?>" alt="<?= esc(\$name2) ?>" data-ts-bg="clients.logo2" loading="lazy"></div>
      <div class="{$p}-clients-cell"><img src="<?= esc(\$logo3) ?>" alt="<?= esc(\$name3) ?>" data-ts-bg="clients.logo3" loading="lazy"></div>
      <div class="{$p}-clients-cell"><img src="<?= esc(\$logo4) ?>" alt="<?= esc(\$name4) ?>" data-ts-bg="clients.logo4" loading="lazy"></div>
      <div class="{$p}-clients-cell"><img src="<?= esc(\$logo5) ?>" alt="<?= esc(\$name5) ?>" data-ts-bg="clients.logo5" loading="lazy"></div>
      <div class="{$p}-clients-cell"><img src="<?= esc(\$logo6) ?>" alt="<?= esc(\$name6) ?>" data-ts-bg="clients.logo6" loading="lazy"></div>
    </div>
  </div>
</section>
HTML,

// ── Grid Cards: Each logo in an elevated card ──
'grid-cards' => <<<HTML
<?php
\$clientsTitle = theme_get('clients.title', 'Trusted By');
\$clientsSubtitle = theme_get('clients.subtitle', 'Partnering with leading companies worldwide');
\$clientsBadge = theme_get('clients.badge', '');
\$logo1 = theme_get('clients.logo1', '');
\$name1 = theme_get('clients.name1', 'Company One');
\$logo2 = theme_get('clients.logo2', '');
\$name2 = theme_get('clients.name2', 'Company Two');
\$logo3 = theme_get('clients.logo3', '');
\$name3 = theme_get('clients.name3', 'Company Three');
\$logo4 = theme_get('clients.logo4', '');
\$name4 = theme_get('clients.name4', 'Company Four');
\$logo5 = theme_get('clients.logo5', '');
\$name5 = theme_get('clients.name5', 'Company Five');
\$logo6 = theme_get('clients.logo6', '');
\$name6 = theme_get('clients.name6', 'Company Six');
?>
<section class="{$p}-clients {$p}-clients--grid-cards" id="clients">
  <div class="container">
    <div class="{$p}-clients-header" data-animate="fade-up">
      <?php if (\$clientsBadge): ?><span class="{$p}-clients-badge" data-ts="clients.badge"><?= esc(\$clientsBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-clients-title" data-ts="clients.title"><?= esc(\$clientsTitle) ?></h2>
      <p class="{$p}-clients-subtitle" data-ts="clients.subtitle"><?= esc(\$clientsSubtitle) ?></p>
    </div>
    <div class="{$p}-clients-grid" data-animate="fade-up">
      <div class="{$p}-clients-card"><img src="<?= esc(\$logo1) ?>" alt="<?= esc(\$name1) ?>" data-ts-bg="clients.logo1" loading="lazy"></div>
      <div class="{$p}-clients-card"><img src="<?= esc(\$logo2) ?>" alt="<?= esc(\$name2) ?>" data-ts-bg="clients.logo2" loading="lazy"></div>
      <div class="{$p}-clients-card"><img src="<?= esc(\$logo3) ?>" alt="<?= esc(\$name3) ?>" data-ts-bg="clients.logo3" loading="lazy"></div>
      <div class="{$p}-clients-card"><img src="<?= esc(\$logo4) ?>" alt="<?= esc(\$name4) ?>" data-ts-bg="clients.logo4" loading="lazy"></div>
      <div class="{$p}-clients-card"><img src="<?= esc(\$logo5) ?>" alt="<?= esc(\$name5) ?>" data-ts-bg="clients.logo5" loading="lazy"></div>
      <div class="{$p}-clients-card"><img src="<?= esc(\$logo6) ?>" alt="<?= esc(\$name6) ?>" data-ts-bg="clients.logo6" loading="lazy"></div>
    </div>
  </div>
</section>
HTML,

// ── Grid With Names: Logo + company name below each ──
'grid-with-names' => <<<HTML
<?php
\$clientsTitle = theme_get('clients.title', 'Trusted By');
\$clientsSubtitle = theme_get('clients.subtitle', 'Partnering with leading companies worldwide');
\$clientsBadge = theme_get('clients.badge', '');
\$logo1 = theme_get('clients.logo1', '');
\$name1 = theme_get('clients.name1', 'Company One');
\$logo2 = theme_get('clients.logo2', '');
\$name2 = theme_get('clients.name2', 'Company Two');
\$logo3 = theme_get('clients.logo3', '');
\$name3 = theme_get('clients.name3', 'Company Three');
\$logo4 = theme_get('clients.logo4', '');
\$name4 = theme_get('clients.name4', 'Company Four');
\$logo5 = theme_get('clients.logo5', '');
\$name5 = theme_get('clients.name5', 'Company Five');
\$logo6 = theme_get('clients.logo6', '');
\$name6 = theme_get('clients.name6', 'Company Six');
?>
<section class="{$p}-clients {$p}-clients--grid-names" id="clients">
  <div class="container">
    <div class="{$p}-clients-header" data-animate="fade-up">
      <?php if (\$clientsBadge): ?><span class="{$p}-clients-badge" data-ts="clients.badge"><?= esc(\$clientsBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-clients-title" data-ts="clients.title"><?= esc(\$clientsTitle) ?></h2>
      <p class="{$p}-clients-subtitle" data-ts="clients.subtitle"><?= esc(\$clientsSubtitle) ?></p>
    </div>
    <div class="{$p}-clients-grid" data-animate="fade-up">
      <div class="{$p}-clients-item">
        <div class="{$p}-clients-logo-wrap"><img src="<?= esc(\$logo1) ?>" alt="<?= esc(\$name1) ?>" data-ts-bg="clients.logo1" loading="lazy"></div>
        <span class="{$p}-clients-name" data-ts="clients.name1"><?= esc(\$name1) ?></span>
      </div>
      <div class="{$p}-clients-item">
        <div class="{$p}-clients-logo-wrap"><img src="<?= esc(\$logo2) ?>" alt="<?= esc(\$name2) ?>" data-ts-bg="clients.logo2" loading="lazy"></div>
        <span class="{$p}-clients-name" data-ts="clients.name2"><?= esc(\$name2) ?></span>
      </div>
      <div class="{$p}-clients-item">
        <div class="{$p}-clients-logo-wrap"><img src="<?= esc(\$logo3) ?>" alt="<?= esc(\$name3) ?>" data-ts-bg="clients.logo3" loading="lazy"></div>
        <span class="{$p}-clients-name" data-ts="clients.name3"><?= esc(\$name3) ?></span>
      </div>
      <div class="{$p}-clients-item">
        <div class="{$p}-clients-logo-wrap"><img src="<?= esc(\$logo4) ?>" alt="<?= esc(\$name4) ?>" data-ts-bg="clients.logo4" loading="lazy"></div>
        <span class="{$p}-clients-name" data-ts="clients.name4"><?= esc(\$name4) ?></span>
      </div>
      <div class="{$p}-clients-item">
        <div class="{$p}-clients-logo-wrap"><img src="<?= esc(\$logo5) ?>" alt="<?= esc(\$name5) ?>" data-ts-bg="clients.logo5" loading="lazy"></div>
        <span class="{$p}-clients-name" data-ts="clients.name5"><?= esc(\$name5) ?></span>
      </div>
      <div class="{$p}-clients-item">
        <div class="{$p}-clients-logo-wrap"><img src="<?= esc(\$logo6) ?>" alt="<?= esc(\$name6) ?>" data-ts-bg="clients.logo6" loading="lazy"></div>
        <span class="{$p}-clients-name" data-ts="clients.name6"><?= esc(\$name6) ?></span>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Featured With Testimonial: Logo row + one featured client quote below ──
'featured-with-testimonial' => <<<HTML
<?php
\$clientsTitle = theme_get('clients.title', 'Trusted By');
\$clientsSubtitle = theme_get('clients.subtitle', 'Partnering with leading companies worldwide');
\$clientsBadge = theme_get('clients.badge', '');
\$logo1 = theme_get('clients.logo1', '');
\$name1 = theme_get('clients.name1', 'Company One');
\$logo2 = theme_get('clients.logo2', '');
\$name2 = theme_get('clients.name2', 'Company Two');
\$logo3 = theme_get('clients.logo3', '');
\$name3 = theme_get('clients.name3', 'Company Three');
\$logo4 = theme_get('clients.logo4', '');
\$name4 = theme_get('clients.name4', 'Company Four');
\$logo5 = theme_get('clients.logo5', '');
\$name5 = theme_get('clients.name5', 'Company Five');
\$logo6 = theme_get('clients.logo6', '');
\$name6 = theme_get('clients.name6', 'Company Six');
\$featuredQuote = theme_get('clients.featured_quote', 'Working with this team has been an incredible experience. They delivered outstanding results.');
\$featuredAuthor = theme_get('clients.featured_author', 'Jane Smith');
\$featuredRole = theme_get('clients.featured_role', 'CEO, Company One');
?>
<section class="{$p}-clients {$p}-clients--featured-testimonial" id="clients">
  <div class="container">
    <div class="{$p}-clients-header" data-animate="fade-up">
      <?php if (\$clientsBadge): ?><span class="{$p}-clients-badge" data-ts="clients.badge"><?= esc(\$clientsBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-clients-title" data-ts="clients.title"><?= esc(\$clientsTitle) ?></h2>
      <p class="{$p}-clients-subtitle" data-ts="clients.subtitle"><?= esc(\$clientsSubtitle) ?></p>
    </div>
    <div class="{$p}-clients-strip" data-animate="fade-up">
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo1) ?>" alt="<?= esc(\$name1) ?>" data-ts-bg="clients.logo1" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo2) ?>" alt="<?= esc(\$name2) ?>" data-ts-bg="clients.logo2" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo3) ?>" alt="<?= esc(\$name3) ?>" data-ts-bg="clients.logo3" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo4) ?>" alt="<?= esc(\$name4) ?>" data-ts-bg="clients.logo4" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo5) ?>" alt="<?= esc(\$name5) ?>" data-ts-bg="clients.logo5" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo6) ?>" alt="<?= esc(\$name6) ?>" data-ts-bg="clients.logo6" loading="lazy"></div>
    </div>
    <div class="{$p}-clients-featured" data-animate="fade-up">
      <blockquote class="{$p}-clients-quote">
        <p data-ts="clients.featured_quote"><?= esc(\$featuredQuote) ?></p>
        <footer>
          <strong data-ts="clients.featured_author"><?= esc(\$featuredAuthor) ?></strong>
          <span data-ts="clients.featured_role"><?= esc(\$featuredRole) ?></span>
        </footer>
      </blockquote>
    </div>
  </div>
</section>
HTML,

// ── Featured Case Study: Highlighted client card (logo, quote, results numbers) ──
'featured-case-study' => <<<HTML
<?php
\$clientsTitle = theme_get('clients.title', 'Trusted By');
\$clientsSubtitle = theme_get('clients.subtitle', 'Partnering with leading companies worldwide');
\$clientsBadge = theme_get('clients.badge', '');
\$logo1 = theme_get('clients.logo1', '');
\$name1 = theme_get('clients.name1', 'Company One');
\$logo2 = theme_get('clients.logo2', '');
\$name2 = theme_get('clients.name2', 'Company Two');
\$logo3 = theme_get('clients.logo3', '');
\$name3 = theme_get('clients.name3', 'Company Three');
\$logo4 = theme_get('clients.logo4', '');
\$name4 = theme_get('clients.name4', 'Company Four');
\$logo5 = theme_get('clients.logo5', '');
\$name5 = theme_get('clients.name5', 'Company Five');
\$logo6 = theme_get('clients.logo6', '');
\$name6 = theme_get('clients.name6', 'Company Six');
\$featuredQuote = theme_get('clients.featured_quote', 'The results exceeded our expectations in every measure.');
\$featuredAuthor = theme_get('clients.featured_author', 'Jane Smith');
\$featuredRole = theme_get('clients.featured_role', 'CEO, Company One');
\$featuredStat1 = theme_get('clients.featured_stat1', '+150% Revenue');
\$featuredStat2 = theme_get('clients.featured_stat2', '3x Growth');
\$featuredStat3 = theme_get('clients.featured_stat3', '99% Uptime');
?>
<section class="{$p}-clients {$p}-clients--featured-case-study" id="clients">
  <div class="container">
    <div class="{$p}-clients-header" data-animate="fade-up">
      <?php if (\$clientsBadge): ?><span class="{$p}-clients-badge" data-ts="clients.badge"><?= esc(\$clientsBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-clients-title" data-ts="clients.title"><?= esc(\$clientsTitle) ?></h2>
      <p class="{$p}-clients-subtitle" data-ts="clients.subtitle"><?= esc(\$clientsSubtitle) ?></p>
    </div>
    <div class="{$p}-clients-strip" data-animate="fade-up">
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo1) ?>" alt="<?= esc(\$name1) ?>" data-ts-bg="clients.logo1" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo2) ?>" alt="<?= esc(\$name2) ?>" data-ts-bg="clients.logo2" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo3) ?>" alt="<?= esc(\$name3) ?>" data-ts-bg="clients.logo3" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo4) ?>" alt="<?= esc(\$name4) ?>" data-ts-bg="clients.logo4" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo5) ?>" alt="<?= esc(\$name5) ?>" data-ts-bg="clients.logo5" loading="lazy"></div>
      <div class="{$p}-clients-logo"><img src="<?= esc(\$logo6) ?>" alt="<?= esc(\$name6) ?>" data-ts-bg="clients.logo6" loading="lazy"></div>
    </div>
    <div class="{$p}-clients-case-study" data-animate="fade-up">
      <div class="{$p}-clients-case-logo">
        <img src="<?= esc(\$logo1) ?>" alt="<?= esc(\$name1) ?>" loading="lazy">
      </div>
      <blockquote class="{$p}-clients-quote">
        <p data-ts="clients.featured_quote"><?= esc(\$featuredQuote) ?></p>
        <footer>
          <strong data-ts="clients.featured_author"><?= esc(\$featuredAuthor) ?></strong>
          <span data-ts="clients.featured_role"><?= esc(\$featuredRole) ?></span>
        </footer>
      </blockquote>
      <div class="{$p}-clients-case-results">
        <span class="{$p}-clients-case-stat" data-ts="clients.featured_stat1"><?= esc(\$featuredStat1) ?></span>
        <span class="{$p}-clients-case-stat" data-ts="clients.featured_stat2"><?= esc(\$featuredStat2) ?></span>
        <span class="{$p}-clients-case-stat" data-ts="clients.featured_stat3"><?= esc(\$featuredStat3) ?></span>
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
        $base = ["{$p}-clients", "{$p}-clients-header", "{$p}-clients-badge", "{$p}-clients-title",
                 "{$p}-clients-subtitle", "{$p}-clients-logo"];

        $extra = match($patternId) {
            'strip-simple' =>
                ["{$p}-clients-strip"],
            'strip-scroll' =>
                ["{$p}-clients-marquee", "{$p}-clients-marquee-track"],
            'strip-two-rows' =>
                ["{$p}-clients-marquee", "{$p}-clients-marquee-track",
                 "{$p}-clients-marquee-track--reverse"],
            'grid-bordered' =>
                ["{$p}-clients-grid", "{$p}-clients-cell"],
            'grid-cards' =>
                ["{$p}-clients-grid", "{$p}-clients-card"],
            'grid-with-names' =>
                ["{$p}-clients-grid", "{$p}-clients-item", "{$p}-clients-logo-wrap",
                 "{$p}-clients-name"],
            'featured-with-testimonial' =>
                ["{$p}-clients-strip", "{$p}-clients-featured", "{$p}-clients-quote"],
            'featured-case-study' =>
                ["{$p}-clients-strip", "{$p}-clients-case-study", "{$p}-clients-case-logo",
                 "{$p}-clients-quote", "{$p}-clients-case-results", "{$p}-clients-case-stat"],
            default => [],
        };

        return array_unique(array_merge($base, $extra));
    }

    private static function buildStructuralCSS(string $cssType, string $p): string
    {
        $css = self::css_base($p);

        $typeCSS = match($cssType) {
            'strip-simple'              => self::css_strip_simple($p),
            'strip-scroll'              => self::css_strip_scroll($p),
            'strip-two-rows'            => self::css_strip_two_rows($p),
            'grid-bordered'             => self::css_grid_bordered($p),
            'grid-cards'                => self::css_grid_cards($p),
            'grid-with-names'           => self::css_grid_with_names($p),
            'featured-with-testimonial' => self::css_featured_testimonial($p),
            'featured-case-study'       => self::css_featured_case_study($p),
            default                     => self::css_strip_simple($p),
        };

        return $css . $typeCSS . self::css_responsive($p);
    }

    // --- Base (shared by all clients patterns) ---
    private static function css_base(string $p): string
    {
        return <<<CSS

/* ═══ Clients Structural CSS (auto-generated — do not edit) ═══ */
.{$p}-clients {
  position: relative; overflow: hidden;
  padding: clamp(60px, 10vh, 120px) 0;
}
.{$p}-clients .container {
  position: relative; z-index: 2;
}
.{$p}-clients-header {
  text-align: center; margin-bottom: clamp(40px, 6vw, 64px);
  max-width: 650px; margin-left: auto; margin-right: auto;
}
.{$p}-clients-badge {
  display: inline-block;
  font-size: 0.8125rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.12em;
  padding: 6px 16px; border-radius: 100px;
  margin-bottom: 16px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.15);
  color: var(--primary, #3b82f6);
  border: 1px solid rgba(var(--primary-rgb, 42,125,225), 0.25);
}
.{$p}-clients-title {
  font-family: var(--font-heading, inherit);
  font-size: clamp(1.75rem, 4vw, 2.75rem);
  font-weight: 700; line-height: 1.2;
  margin: 0 0 16px 0;
  color: var(--text, #1e293b);
}
.{$p}-clients-subtitle {
  font-size: clamp(0.9375rem, 1.5vw, 1.125rem);
  line-height: 1.7; margin: 0;
  color: var(--text-muted, #64748b);
  max-width: 50ch; margin-left: auto; margin-right: auto;
}
.{$p}-clients-logo {
  display: flex; align-items: center; justify-content: center;
  padding: 16px;
}
.{$p}-clients-logo img {
  max-height: 48px; max-width: 140px;
  width: auto; height: auto;
  object-fit: contain;
  filter: grayscale(100%) opacity(0.5);
  transition: filter 0.3s ease, transform 0.3s ease;
}
.{$p}-clients-logo:hover img {
  filter: grayscale(0%) opacity(1);
  transform: scale(1.05);
}

CSS;
    }

    // --- Strip Simple ---
    private static function css_strip_simple(string $p): string
    {
        return <<<CSS
.{$p}-clients-strip {
  display: flex; flex-wrap: wrap;
  justify-content: center; align-items: center;
  gap: clamp(16px, 3vw, 40px);
}

CSS;
    }

    // --- Strip Scroll ---
    private static function css_strip_scroll(string $p): string
    {
        return <<<CSS
.{$p}-clients-marquee {
  overflow: hidden; width: 100%;
  -webkit-mask-image: linear-gradient(90deg, transparent, black 10%, black 90%, transparent);
  mask-image: linear-gradient(90deg, transparent, black 10%, black 90%, transparent);
}
.{$p}-clients-marquee-track {
  display: flex; width: max-content;
  animation: {$p}-marqueeScroll 30s linear infinite;
}
@keyframes {$p}-marqueeScroll {
  from { transform: translateX(0); }
  to   { transform: translateX(-50%); }
}
.{$p}-clients-marquee .{$p}-clients-logo {
  flex-shrink: 0;
  padding: 16px clamp(20px, 3vw, 40px);
}
.{$p}-clients-marquee:hover .{$p}-clients-marquee-track {
  animation-play-state: paused;
}

CSS;
    }

    // --- Strip Two Rows ---
    private static function css_strip_two_rows(string $p): string
    {
        return self::css_strip_scroll($p) . <<<CSS
.{$p}-clients-marquee--row2 {
  margin-top: 16px;
}
.{$p}-clients-marquee-track--reverse {
  animation-direction: reverse;
}

CSS;
    }

    // --- Grid Bordered ---
    private static function css_grid_bordered(string $p): string
    {
        return <<<CSS
.{$p}-clients-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
}
.{$p}-clients-cell {
  display: flex; align-items: center; justify-content: center;
  padding: clamp(24px, 3vw, 40px);
  border: 1px solid var(--border, rgba(0,0,0,0.08));
  margin: -0.5px;
  transition: background 0.3s ease;
}
.{$p}-clients-cell:hover {
  background: var(--surface, rgba(0,0,0,0.02));
}
.{$p}-clients-cell img {
  max-height: 48px; max-width: 140px;
  width: auto; height: auto;
  object-fit: contain;
  filter: grayscale(100%) opacity(0.5);
  transition: filter 0.3s ease;
}
.{$p}-clients-cell:hover img {
  filter: grayscale(0%) opacity(1);
}

CSS;
    }

    // --- Grid Cards ---
    private static function css_grid_cards(string $p): string
    {
        return <<<CSS
.{$p}-clients--grid-cards .{$p}-clients-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: clamp(16px, 2vw, 24px);
}
.{$p}-clients-card {
  display: flex; align-items: center; justify-content: center;
  padding: clamp(28px, 3vw, 44px);
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-clients-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.1);
}
.{$p}-clients-card img {
  max-height: 48px; max-width: 140px;
  width: auto; height: auto;
  object-fit: contain;
  filter: grayscale(100%) opacity(0.5);
  transition: filter 0.3s ease;
}
.{$p}-clients-card:hover img {
  filter: grayscale(0%) opacity(1);
}

CSS;
    }

    // --- Grid With Names ---
    private static function css_grid_with_names(string $p): string
    {
        return <<<CSS
.{$p}-clients--grid-names .{$p}-clients-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: clamp(20px, 3vw, 36px);
}
.{$p}-clients-item {
  text-align: center;
}
.{$p}-clients-logo-wrap {
  display: flex; align-items: center; justify-content: center;
  height: 80px; margin-bottom: 12px;
  padding: 16px;
}
.{$p}-clients-logo-wrap img {
  max-height: 48px; max-width: 140px;
  width: auto; height: auto;
  object-fit: contain;
  filter: grayscale(100%) opacity(0.5);
  transition: filter 0.3s ease;
}
.{$p}-clients-item:hover .{$p}-clients-logo-wrap img {
  filter: grayscale(0%) opacity(1);
}
.{$p}-clients-name {
  display: block;
  font-size: 0.8125rem; font-weight: 600;
  color: var(--text-muted, #64748b);
  text-transform: uppercase; letter-spacing: 0.06em;
}

CSS;
    }

    // --- Featured With Testimonial ---
    private static function css_featured_testimonial(string $p): string
    {
        return <<<CSS
.{$p}-clients--featured-testimonial .{$p}-clients-strip {
  display: flex; flex-wrap: wrap;
  justify-content: center; align-items: center;
  gap: clamp(16px, 3vw, 40px);
  padding-bottom: clamp(32px, 5vw, 56px);
  border-bottom: 1px solid var(--border, rgba(0,0,0,0.08));
}
.{$p}-clients-featured {
  max-width: 700px; margin: clamp(32px, 5vw, 56px) auto 0;
  text-align: center;
}
.{$p}-clients-quote {
  margin: 0; padding: 0;
}
.{$p}-clients-quote p {
  font-size: clamp(1.0625rem, 2vw, 1.375rem);
  font-style: italic; line-height: 1.7;
  color: var(--text, #1e293b);
  margin: 0 0 24px 0;
}
.{$p}-clients-quote p::before { content: '\201C'; }
.{$p}-clients-quote p::after  { content: '\201D'; }
.{$p}-clients-quote footer {
  display: flex; flex-direction: column; gap: 4px;
}
.{$p}-clients-quote footer strong {
  font-size: 0.9375rem; font-weight: 700;
  color: var(--text, #1e293b);
}
.{$p}-clients-quote footer span {
  font-size: 0.8125rem;
  color: var(--text-muted, #64748b);
}

CSS;
    }

    // --- Featured Case Study ---
    private static function css_featured_case_study(string $p): string
    {
        return <<<CSS
.{$p}-clients--featured-case-study .{$p}-clients-strip {
  display: flex; flex-wrap: wrap;
  justify-content: center; align-items: center;
  gap: clamp(16px, 3vw, 40px);
  margin-bottom: clamp(40px, 6vw, 64px);
}
.{$p}-clients-case-study {
  max-width: 800px; margin: 0 auto;
  padding: clamp(32px, 4vw, 56px);
  background: var(--surface, #f8fafc);
  border-radius: var(--radius, 16px);
  border: 1px solid var(--border, rgba(0,0,0,0.06));
  text-align: center;
}
.{$p}-clients-case-logo {
  margin-bottom: 24px;
}
.{$p}-clients-case-logo img {
  max-height: 48px; width: auto;
  object-fit: contain;
}
.{$p}-clients-case-study .{$p}-clients-quote {
  margin: 0; padding: 0;
}
.{$p}-clients-case-study .{$p}-clients-quote p {
  font-size: clamp(1.0625rem, 2vw, 1.375rem);
  font-style: italic; line-height: 1.7;
  color: var(--text, #1e293b);
  margin: 0 0 24px 0;
}
.{$p}-clients-case-study .{$p}-clients-quote p::before { content: '\201C'; }
.{$p}-clients-case-study .{$p}-clients-quote p::after  { content: '\201D'; }
.{$p}-clients-case-study .{$p}-clients-quote footer {
  display: flex; flex-direction: column; gap: 4px;
  margin-bottom: 28px;
}
.{$p}-clients-case-study .{$p}-clients-quote footer strong {
  font-size: 0.9375rem; font-weight: 700;
  color: var(--text, #1e293b);
}
.{$p}-clients-case-study .{$p}-clients-quote footer span {
  font-size: 0.8125rem;
  color: var(--text-muted, #64748b);
}
.{$p}-clients-case-results {
  display: flex; justify-content: center; gap: clamp(16px, 3vw, 32px);
  padding-top: 24px;
  border-top: 1px solid var(--border, rgba(0,0,0,0.08));
}
.{$p}-clients-case-stat {
  font-size: 0.9375rem; font-weight: 700;
  color: var(--primary, #3b82f6);
  padding: 8px 16px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.08);
  border-radius: 100px;
}

CSS;
    }

    // --- Responsive ---
    private static function css_responsive(string $p): string
    {
        return <<<CSS
@media (max-width: 768px) {
  .{$p}-clients-grid {
    grid-template-columns: repeat(2, 1fr) !important;
  }
  .{$p}-clients-strip {
    gap: 16px !important;
  }
  .{$p}-clients-logo img,
  .{$p}-clients-cell img,
  .{$p}-clients-card img,
  .{$p}-clients-logo-wrap img {
    max-height: 36px;
    max-width: 100px;
  }
  .{$p}-clients-case-results {
    flex-direction: column; align-items: center;
  }
}

CSS;
    }
}
