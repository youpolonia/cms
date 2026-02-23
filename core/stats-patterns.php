<?php
/**
 * Stats Section Pattern Registry
 * 
 * Pre-built stats/counters HTML layouts with structural CSS.
 * AI generates only decorative CSS (colors, fonts, effects) — never the structure.
 * 
 * 8 patterns across 3 groups.
 * @since 2026-02-19
 */

class StatsPatternRegistry
{
    // ═══════════════════════════════════════
    // PATTERN DEFINITIONS
    // ═══════════════════════════════════════

    private static array $patterns = [
        // --- Counters (clean number displays) ---
        ['id'=>'counters-row',     'group'=>'counters', 'css_type'=>'counters-row',
         'best_for'=>['saas','tech','startup','fintech','platform','digital','ai',
                      'consulting','agency','marketing','accounting','legal','financial','bank']],
        ['id'=>'counters-icons',   'group'=>'counters', 'css_type'=>'counters-icons',
         'best_for'=>['healthcare','clinic','hospital','dental','pharmacy','nonprofit','charity',
                      'education','university','school','coaching','library']],
        ['id'=>'counters-boxed',   'group'=>'counters', 'css_type'=>'counters-boxed',
         'best_for'=>['insurance','logistics','manufacturing','engineering','construction',
                      'real-estate','paving','roofing','plumbing','electrical','hvac']],

        // --- Visual (progress bars, circles, large numbers) ---
        ['id'=>'visual-progress-bars', 'group'=>'visual', 'css_type'=>'visual-progress-bars',
         'best_for'=>['fitness','sports','gym','wellness','spa','nutrition',
                      'personal-training','yoga','crossfit','martial-arts']],
        ['id'=>'visual-circular',      'group'=>'visual', 'css_type'=>'visual-circular',
         'best_for'=>['dashboard','analytics','seo','web-design','data','blockchain',
                      'cybersecurity','it-services','cloud','devops']],
        ['id'=>'visual-large-numbers', 'group'=>'visual', 'css_type'=>'visual-large-numbers',
         'best_for'=>['architecture','interior-design','gallery','museum','photography',
                      'art','fashion','design','branding','creative-agency']],

        // --- Creative (unique layouts) ---
        ['id'=>'creative-split-bg',    'group'=>'creative', 'css_type'=>'creative-split-bg',
         'best_for'=>['restaurant','bakery','cafe','bar','hotel','resort','winery','brewery',
                      'fine-dining','luxury','country-club','catering']],
        ['id'=>'creative-with-image',  'group'=>'creative', 'css_type'=>'creative-with-image',
         'best_for'=>['travel','tourism','adventure','outdoor','landscaping',
                      'event-planning','wedding','florist','entertainment','film']],
    ];

    // ═══════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════

    /**
     * Pick the best stats pattern for an industry.
     */
    public static function pickPattern(string $industry): string
    {
        $industry = strtolower(trim($industry));
        foreach (self::$patterns as $p) {
            if (in_array($industry, $p['best_for'], true)) {
                return $p['id'];
            }
        }
        // Fallback: random from counters group (most versatile)
        $counterPatterns = array_filter(self::$patterns, fn($p) => $p['group'] === 'counters');
        $counterIds = array_column(array_values($counterPatterns), 'id');
        return $counterIds[array_rand($counterIds)];
    }

    /**
     * Render a stats pattern.
     * Returns ['html'=>..., 'structural_css'=>..., 'pattern_id'=>..., 'classes'=>[...], 'fields'=>[...]]
     */
    public static function render(string $patternId, string $prefix, array $brief): array
    {
        $def = null;
        foreach (self::$patterns as $p) {
            if ($p['id'] === $patternId) { $def = $p; break; }
        }
        if (!$def) {
            $def = self::$patterns[0]; // fallback to counters-row
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
        // Common fields all stats patterns have
        $common = [
            'title'       => ['type' => 'text',     'label' => 'Section Title'],
            'subtitle'    => ['type' => 'textarea', 'label' => 'Section Subtitle'],
            'badge'       => ['type' => 'text',     'label' => 'Badge / Label'],
            'stat1_number'=> ['type' => 'text',     'label' => 'Stat 1 Number'],
            'stat1_label' => ['type' => 'text',     'label' => 'Stat 1 Label'],
            'stat1_icon'  => ['type' => 'text',     'label' => 'Stat 1 Icon'],
            'stat2_number'=> ['type' => 'text',     'label' => 'Stat 2 Number'],
            'stat2_label' => ['type' => 'text',     'label' => 'Stat 2 Label'],
            'stat2_icon'  => ['type' => 'text',     'label' => 'Stat 2 Icon'],
            'stat3_number'=> ['type' => 'text',     'label' => 'Stat 3 Number'],
            'stat3_label' => ['type' => 'text',     'label' => 'Stat 3 Label'],
            'stat3_icon'  => ['type' => 'text',     'label' => 'Stat 3 Icon'],
            'stat4_number'=> ['type' => 'text',     'label' => 'Stat 4 Number'],
            'stat4_label' => ['type' => 'text',     'label' => 'Stat 4 Label'],
            'stat4_icon'  => ['type' => 'text',     'label' => 'Stat 4 Icon'],
        ];

        // Pattern-specific extras
        $extras = match($patternId) {
            'creative-with-image' => [
                'bg_image' => ['type' => 'image', 'label' => 'Background Image'],
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
            'counters-row' => <<<'GUIDE'
Use large, bold font-size (2.5rem+) for numbers with color: var(--primary).
Labels: small, uppercase, letter-spacing: 0.1em, color: var(--text-muted).
Add border-right: 1px solid var(--border) separators between items.
Numbers: font-weight: 800 for dramatic impact.
GUIDE,
            'counters-icons' => <<<'GUIDE'
Icon circles: background: var(--primary), color: white, border-radius: 50%.
Numbers below icons: font-weight: 700, color: var(--primary).
Labels: font-size: 0.8rem, color: var(--text-muted), uppercase.
Hover: icon circles scale(1.1) with smooth transition.
GUIDE,
            'counters-boxed' => <<<'GUIDE'
Cards: border: 1px solid var(--border), border-radius: var(--radius), background: var(--surface).
Hover: box-shadow: 0 12px 40px rgba(0,0,0,0.1), transform: translateY(-4px).
Icon bg: soft primary tint rgba(var(--primary-rgb), 0.1), border-radius matching card.
Numbers: color: var(--primary), font-weight: 700.
GUIDE,
            'visual-progress-bars' => <<<'GUIDE'
Bar fill: background: linear-gradient(90deg, var(--primary), var(--secondary)), border-radius: 100px.
Bar track: background: var(--border), border-radius: 100px, height: 10px.
Fill animation: width 0 to target over 1.5s ease-out.
Percentage labels: font-weight: 700, color: var(--primary).
GUIDE,
            'visual-circular' => <<<'GUIDE'
Circle ring: conic-gradient with var(--primary) filled, var(--border) remainder.
Center number: font-weight: 700, color: var(--primary), absolute center.
Inner cutout: background: var(--background) creates ring effect.
Reveal animation: rotate + scale entrance, staggered per circle.
GUIDE,
            'visual-large-numbers' => <<<'GUIDE'
Numbers: font-size: 5rem+, font-weight: 800, line-height: 1.
Gradient text: background: linear-gradient(135deg, var(--primary), var(--secondary)), -webkit-background-clip: text.
Labels: font-size: 1rem, letter-spacing: 0.15em, uppercase, color: var(--text-muted).
Dramatic whitespace between number and label for visual weight.
GUIDE,
            'creative-split-bg' => <<<'GUIDE'
Dark half: background: var(--dark-bg, #0f172a), text: white, badge: rgba(255,255,255,0.1).
Light half: background: var(--surface), standard text colors.
Accent divider: 3px solid var(--primary) between halves.
Stats on light side: numbers color: var(--primary), labels: var(--text-muted).
GUIDE,
            'creative-with-image' => <<<'GUIDE'
Overlay: linear-gradient(135deg, rgba(0,0,0,0.7), rgba(0,0,0,0.5)) over bg image.
All text: color: white, text-shadow: 0 2px 8px rgba(0,0,0,0.3).
Stat cards: background: rgba(255,255,255,0.1), backdrop-filter: blur(12px), border: 1px solid rgba(255,255,255,0.15).
Icons: background: rgba(255,255,255,0.15), color: white.
Card hover: background brightens to rgba(255,255,255,0.18).
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
            $title = $name . ' in Numbers';
        }

        $badge = '';
        if (!empty($brief['style_preset'])) {
            $badge = ucwords(str_replace('-', ' ', $brief['style_preset']));
        } elseif ($industry) {
            $badge = ucwords(str_replace('-', ' ', $industry));
        }

        $replacements = [];
        if ($title) $replacements["theme_get('stats.title', 'Our Impact')"] = "theme_get('stats.title', '" . addslashes($title) . "')";
        if ($badge) $replacements["theme_get('stats.badge', '')"] = "theme_get('stats.badge', '" . addslashes($badge) . "')";

        foreach ($replacements as $search => $replace) {
            $html = str_replace($search, $replace, $html);
        }

        return $html;
    }

    private static function buildHTML(string $patternId, string $p): string
    {
        $templates = self::getTemplates($p);
        return $templates[$patternId] ?? $templates['counters-row'];
    }

    private static function getTemplates(string $p): array
    {
        return [

// ── Counters Row: 4 stat counters in a row, clean ──
'counters-row' => <<<HTML
<?php
\$statsTitle = theme_get('stats.title', 'Our Impact');
\$statsSubtitle = theme_get('stats.subtitle', 'Numbers that speak for themselves');
\$statsBadge = theme_get('stats.badge', '');
\$stat1Num = theme_get('stats.stat1_number', '500+');
\$stat1Lab = theme_get('stats.stat1_label', 'Clients');
\$stat2Num = theme_get('stats.stat2_number', '1200+');
\$stat2Lab = theme_get('stats.stat2_label', 'Projects');
\$stat3Num = theme_get('stats.stat3_number', '98%');
\$stat3Lab = theme_get('stats.stat3_label', 'Satisfaction');
\$stat4Num = theme_get('stats.stat4_number', '25+');
\$stat4Lab = theme_get('stats.stat4_label', 'Years');
?>
<section class="{$p}-stats {$p}-stats--counters-row" id="stats">
  <div class="container">
    <div class="{$p}-stats-header" data-animate="fade-up">
      <?php if (\$statsBadge): ?><span class="{$p}-stats-badge" data-ts="stats.badge"><?= esc(\$statsBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-stats-title" data-ts="stats.title"><?= esc(\$statsTitle) ?></h2>
      <p class="{$p}-stats-subtitle" data-ts="stats.subtitle"><?= esc(\$statsSubtitle) ?></p>
    </div>
    <div class="{$p}-stats-grid" data-animate="fade-up">
      <div class="{$p}-stats-item">
        <span class="{$p}-stats-number" data-ts="stats.stat1_number"><?= esc(\$stat1Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat1_label"><?= esc(\$stat1Lab) ?></span>
      </div>
      <div class="{$p}-stats-item">
        <span class="{$p}-stats-number" data-ts="stats.stat2_number"><?= esc(\$stat2Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat2_label"><?= esc(\$stat2Lab) ?></span>
      </div>
      <div class="{$p}-stats-item">
        <span class="{$p}-stats-number" data-ts="stats.stat3_number"><?= esc(\$stat3Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat3_label"><?= esc(\$stat3Lab) ?></span>
      </div>
      <div class="{$p}-stats-item">
        <span class="{$p}-stats-number" data-ts="stats.stat4_number"><?= esc(\$stat4Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat4_label"><?= esc(\$stat4Lab) ?></span>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Counters Icons: Each stat has icon above, then number, then label ──
'counters-icons' => <<<HTML
<?php
\$statsTitle = theme_get('stats.title', 'Our Impact');
\$statsSubtitle = theme_get('stats.subtitle', 'Numbers that speak for themselves');
\$statsBadge = theme_get('stats.badge', '');
\$stat1Num = theme_get('stats.stat1_number', '500+');
\$stat1Lab = theme_get('stats.stat1_label', 'Clients');
\$stat1Ico = theme_get('stats.stat1_icon', 'fas fa-users');
\$stat2Num = theme_get('stats.stat2_number', '1200+');
\$stat2Lab = theme_get('stats.stat2_label', 'Projects');
\$stat2Ico = theme_get('stats.stat2_icon', 'fas fa-project-diagram');
\$stat3Num = theme_get('stats.stat3_number', '98%');
\$stat3Lab = theme_get('stats.stat3_label', 'Satisfaction');
\$stat3Ico = theme_get('stats.stat3_icon', 'fas fa-smile');
\$stat4Num = theme_get('stats.stat4_number', '25+');
\$stat4Lab = theme_get('stats.stat4_label', 'Years');
\$stat4Ico = theme_get('stats.stat4_icon', 'fas fa-calendar-alt');
?>
<section class="{$p}-stats {$p}-stats--counters-icons" id="stats">
  <div class="container">
    <div class="{$p}-stats-header" data-animate="fade-up">
      <?php if (\$statsBadge): ?><span class="{$p}-stats-badge" data-ts="stats.badge"><?= esc(\$statsBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-stats-title" data-ts="stats.title"><?= esc(\$statsTitle) ?></h2>
      <p class="{$p}-stats-subtitle" data-ts="stats.subtitle"><?= esc(\$statsSubtitle) ?></p>
    </div>
    <div class="{$p}-stats-grid" data-animate="fade-up">
      <div class="{$p}-stats-item">
        <div class="{$p}-stats-icon"><i class="<?= esc(\$stat1Ico) ?>" data-ts="stats.stat1_icon"></i></div>
        <span class="{$p}-stats-number" data-ts="stats.stat1_number"><?= esc(\$stat1Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat1_label"><?= esc(\$stat1Lab) ?></span>
      </div>
      <div class="{$p}-stats-item">
        <div class="{$p}-stats-icon"><i class="<?= esc(\$stat2Ico) ?>" data-ts="stats.stat2_icon"></i></div>
        <span class="{$p}-stats-number" data-ts="stats.stat2_number"><?= esc(\$stat2Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat2_label"><?= esc(\$stat2Lab) ?></span>
      </div>
      <div class="{$p}-stats-item">
        <div class="{$p}-stats-icon"><i class="<?= esc(\$stat3Ico) ?>" data-ts="stats.stat3_icon"></i></div>
        <span class="{$p}-stats-number" data-ts="stats.stat3_number"><?= esc(\$stat3Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat3_label"><?= esc(\$stat3Lab) ?></span>
      </div>
      <div class="{$p}-stats-item">
        <div class="{$p}-stats-icon"><i class="<?= esc(\$stat4Ico) ?>" data-ts="stats.stat4_icon"></i></div>
        <span class="{$p}-stats-number" data-ts="stats.stat4_number"><?= esc(\$stat4Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat4_label"><?= esc(\$stat4Lab) ?></span>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Counters Boxed: Stats in bordered cards/boxes ──
'counters-boxed' => <<<HTML
<?php
\$statsTitle = theme_get('stats.title', 'Our Impact');
\$statsSubtitle = theme_get('stats.subtitle', 'Numbers that speak for themselves');
\$statsBadge = theme_get('stats.badge', '');
\$stat1Num = theme_get('stats.stat1_number', '500+');
\$stat1Lab = theme_get('stats.stat1_label', 'Clients');
\$stat1Ico = theme_get('stats.stat1_icon', 'fas fa-users');
\$stat2Num = theme_get('stats.stat2_number', '1200+');
\$stat2Lab = theme_get('stats.stat2_label', 'Projects');
\$stat2Ico = theme_get('stats.stat2_icon', 'fas fa-project-diagram');
\$stat3Num = theme_get('stats.stat3_number', '98%');
\$stat3Lab = theme_get('stats.stat3_label', 'Satisfaction');
\$stat3Ico = theme_get('stats.stat3_icon', 'fas fa-smile');
\$stat4Num = theme_get('stats.stat4_number', '25+');
\$stat4Lab = theme_get('stats.stat4_label', 'Years');
\$stat4Ico = theme_get('stats.stat4_icon', 'fas fa-calendar-alt');
?>
<section class="{$p}-stats {$p}-stats--counters-boxed" id="stats">
  <div class="container">
    <div class="{$p}-stats-header" data-animate="fade-up">
      <?php if (\$statsBadge): ?><span class="{$p}-stats-badge" data-ts="stats.badge"><?= esc(\$statsBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-stats-title" data-ts="stats.title"><?= esc(\$statsTitle) ?></h2>
      <p class="{$p}-stats-subtitle" data-ts="stats.subtitle"><?= esc(\$statsSubtitle) ?></p>
    </div>
    <div class="{$p}-stats-grid" data-animate="fade-up">
      <div class="{$p}-stats-card">
        <div class="{$p}-stats-icon"><i class="<?= esc(\$stat1Ico) ?>" data-ts="stats.stat1_icon"></i></div>
        <span class="{$p}-stats-number" data-ts="stats.stat1_number"><?= esc(\$stat1Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat1_label"><?= esc(\$stat1Lab) ?></span>
      </div>
      <div class="{$p}-stats-card">
        <div class="{$p}-stats-icon"><i class="<?= esc(\$stat2Ico) ?>" data-ts="stats.stat2_icon"></i></div>
        <span class="{$p}-stats-number" data-ts="stats.stat2_number"><?= esc(\$stat2Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat2_label"><?= esc(\$stat2Lab) ?></span>
      </div>
      <div class="{$p}-stats-card">
        <div class="{$p}-stats-icon"><i class="<?= esc(\$stat3Ico) ?>" data-ts="stats.stat3_icon"></i></div>
        <span class="{$p}-stats-number" data-ts="stats.stat3_number"><?= esc(\$stat3Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat3_label"><?= esc(\$stat3Lab) ?></span>
      </div>
      <div class="{$p}-stats-card">
        <div class="{$p}-stats-icon"><i class="<?= esc(\$stat4Ico) ?>" data-ts="stats.stat4_icon"></i></div>
        <span class="{$p}-stats-number" data-ts="stats.stat4_number"><?= esc(\$stat4Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat4_label"><?= esc(\$stat4Lab) ?></span>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Visual Progress Bars: Horizontal progress bars with percentages ──
'visual-progress-bars' => <<<HTML
<?php
\$statsTitle = theme_get('stats.title', 'Our Impact');
\$statsSubtitle = theme_get('stats.subtitle', 'Numbers that speak for themselves');
\$statsBadge = theme_get('stats.badge', '');
\$stat1Num = theme_get('stats.stat1_number', '95%');
\$stat1Lab = theme_get('stats.stat1_label', 'Client Satisfaction');
\$stat2Num = theme_get('stats.stat2_number', '88%');
\$stat2Lab = theme_get('stats.stat2_label', 'Success Rate');
\$stat3Num = theme_get('stats.stat3_number', '92%');
\$stat3Lab = theme_get('stats.stat3_label', 'Retention');
\$stat4Num = theme_get('stats.stat4_number', '97%');
\$stat4Lab = theme_get('stats.stat4_label', 'On-Time Delivery');
?>
<section class="{$p}-stats {$p}-stats--progress-bars" id="stats">
  <div class="container">
    <div class="{$p}-stats-header" data-animate="fade-up">
      <?php if (\$statsBadge): ?><span class="{$p}-stats-badge" data-ts="stats.badge"><?= esc(\$statsBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-stats-title" data-ts="stats.title"><?= esc(\$statsTitle) ?></h2>
      <p class="{$p}-stats-subtitle" data-ts="stats.subtitle"><?= esc(\$statsSubtitle) ?></p>
    </div>
    <div class="{$p}-stats-bars" data-animate="fade-up">
      <div class="{$p}-stats-bar-item">
        <div class="{$p}-stats-bar-header">
          <span class="{$p}-stats-label" data-ts="stats.stat1_label"><?= esc(\$stat1Lab) ?></span>
          <span class="{$p}-stats-number" data-ts="stats.stat1_number"><?= esc(\$stat1Num) ?></span>
        </div>
        <div class="{$p}-stats-bar-track"><div class="{$p}-stats-bar-fill" style="--bar-width: <?= esc(\$stat1Num) ?>;"></div></div>
      </div>
      <div class="{$p}-stats-bar-item">
        <div class="{$p}-stats-bar-header">
          <span class="{$p}-stats-label" data-ts="stats.stat2_label"><?= esc(\$stat2Lab) ?></span>
          <span class="{$p}-stats-number" data-ts="stats.stat2_number"><?= esc(\$stat2Num) ?></span>
        </div>
        <div class="{$p}-stats-bar-track"><div class="{$p}-stats-bar-fill" style="--bar-width: <?= esc(\$stat2Num) ?>;"></div></div>
      </div>
      <div class="{$p}-stats-bar-item">
        <div class="{$p}-stats-bar-header">
          <span class="{$p}-stats-label" data-ts="stats.stat3_label"><?= esc(\$stat3Lab) ?></span>
          <span class="{$p}-stats-number" data-ts="stats.stat3_number"><?= esc(\$stat3Num) ?></span>
        </div>
        <div class="{$p}-stats-bar-track"><div class="{$p}-stats-bar-fill" style="--bar-width: <?= esc(\$stat3Num) ?>;"></div></div>
      </div>
      <div class="{$p}-stats-bar-item">
        <div class="{$p}-stats-bar-header">
          <span class="{$p}-stats-label" data-ts="stats.stat4_label"><?= esc(\$stat4Lab) ?></span>
          <span class="{$p}-stats-number" data-ts="stats.stat4_number"><?= esc(\$stat4Num) ?></span>
        </div>
        <div class="{$p}-stats-bar-track"><div class="{$p}-stats-bar-fill" style="--bar-width: <?= esc(\$stat4Num) ?>;"></div></div>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Visual Circular: Circular progress indicators (CSS conic-gradient) ──
'visual-circular' => <<<HTML
<?php
\$statsTitle = theme_get('stats.title', 'Our Impact');
\$statsSubtitle = theme_get('stats.subtitle', 'Numbers that speak for themselves');
\$statsBadge = theme_get('stats.badge', '');
\$stat1Num = theme_get('stats.stat1_number', '95%');
\$stat1Lab = theme_get('stats.stat1_label', 'Uptime');
\$stat2Num = theme_get('stats.stat2_number', '88%');
\$stat2Lab = theme_get('stats.stat2_label', 'Efficiency');
\$stat3Num = theme_get('stats.stat3_number', '92%');
\$stat3Lab = theme_get('stats.stat3_label', 'Accuracy');
\$stat4Num = theme_get('stats.stat4_number', '97%');
\$stat4Lab = theme_get('stats.stat4_label', 'Satisfaction');
?>
<section class="{$p}-stats {$p}-stats--circular" id="stats">
  <div class="container">
    <div class="{$p}-stats-header" data-animate="fade-up">
      <?php if (\$statsBadge): ?><span class="{$p}-stats-badge" data-ts="stats.badge"><?= esc(\$statsBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-stats-title" data-ts="stats.title"><?= esc(\$statsTitle) ?></h2>
      <p class="{$p}-stats-subtitle" data-ts="stats.subtitle"><?= esc(\$statsSubtitle) ?></p>
    </div>
    <div class="{$p}-stats-grid" data-animate="fade-up">
      <div class="{$p}-stats-item">
        <div class="{$p}-stats-circle" style="--circle-pct: <?= esc(\$stat1Num) ?>;">
          <span class="{$p}-stats-number" data-ts="stats.stat1_number"><?= esc(\$stat1Num) ?></span>
        </div>
        <span class="{$p}-stats-label" data-ts="stats.stat1_label"><?= esc(\$stat1Lab) ?></span>
      </div>
      <div class="{$p}-stats-item">
        <div class="{$p}-stats-circle" style="--circle-pct: <?= esc(\$stat2Num) ?>;">
          <span class="{$p}-stats-number" data-ts="stats.stat2_number"><?= esc(\$stat2Num) ?></span>
        </div>
        <span class="{$p}-stats-label" data-ts="stats.stat2_label"><?= esc(\$stat2Lab) ?></span>
      </div>
      <div class="{$p}-stats-item">
        <div class="{$p}-stats-circle" style="--circle-pct: <?= esc(\$stat3Num) ?>;">
          <span class="{$p}-stats-number" data-ts="stats.stat3_number"><?= esc(\$stat3Num) ?></span>
        </div>
        <span class="{$p}-stats-label" data-ts="stats.stat3_label"><?= esc(\$stat3Lab) ?></span>
      </div>
      <div class="{$p}-stats-item">
        <div class="{$p}-stats-circle" style="--circle-pct: <?= esc(\$stat4Num) ?>;">
          <span class="{$p}-stats-number" data-ts="stats.stat4_number"><?= esc(\$stat4Num) ?></span>
        </div>
        <span class="{$p}-stats-label" data-ts="stats.stat4_label"><?= esc(\$stat4Lab) ?></span>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Visual Large Numbers: Giant numbers (full-width), small label below ──
'visual-large-numbers' => <<<HTML
<?php
\$statsTitle = theme_get('stats.title', 'Our Impact');
\$statsSubtitle = theme_get('stats.subtitle', 'Numbers that speak for themselves');
\$statsBadge = theme_get('stats.badge', '');
\$stat1Num = theme_get('stats.stat1_number', '500+');
\$stat1Lab = theme_get('stats.stat1_label', 'Clients');
\$stat2Num = theme_get('stats.stat2_number', '1200+');
\$stat2Lab = theme_get('stats.stat2_label', 'Projects');
\$stat3Num = theme_get('stats.stat3_number', '98%');
\$stat3Lab = theme_get('stats.stat3_label', 'Satisfaction');
\$stat4Num = theme_get('stats.stat4_number', '25+');
\$stat4Lab = theme_get('stats.stat4_label', 'Years');
?>
<section class="{$p}-stats {$p}-stats--large-numbers" id="stats">
  <div class="container">
    <div class="{$p}-stats-header" data-animate="fade-up">
      <?php if (\$statsBadge): ?><span class="{$p}-stats-badge" data-ts="stats.badge"><?= esc(\$statsBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-stats-title" data-ts="stats.title"><?= esc(\$statsTitle) ?></h2>
      <p class="{$p}-stats-subtitle" data-ts="stats.subtitle"><?= esc(\$statsSubtitle) ?></p>
    </div>
    <div class="{$p}-stats-grid" data-animate="fade-up">
      <div class="{$p}-stats-item">
        <span class="{$p}-stats-number" data-ts="stats.stat1_number"><?= esc(\$stat1Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat1_label"><?= esc(\$stat1Lab) ?></span>
      </div>
      <div class="{$p}-stats-item">
        <span class="{$p}-stats-number" data-ts="stats.stat2_number"><?= esc(\$stat2Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat2_label"><?= esc(\$stat2Lab) ?></span>
      </div>
      <div class="{$p}-stats-item">
        <span class="{$p}-stats-number" data-ts="stats.stat3_number"><?= esc(\$stat3Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat3_label"><?= esc(\$stat3Lab) ?></span>
      </div>
      <div class="{$p}-stats-item">
        <span class="{$p}-stats-number" data-ts="stats.stat4_number"><?= esc(\$stat4Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat4_label"><?= esc(\$stat4Lab) ?></span>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Creative Split BG: Left half dark, right half light, stats split across ──
'creative-split-bg' => <<<HTML
<?php
\$statsTitle = theme_get('stats.title', 'Our Impact');
\$statsSubtitle = theme_get('stats.subtitle', 'Numbers that speak for themselves');
\$statsBadge = theme_get('stats.badge', '');
\$stat1Num = theme_get('stats.stat1_number', '500+');
\$stat1Lab = theme_get('stats.stat1_label', 'Clients');
\$stat1Ico = theme_get('stats.stat1_icon', 'fas fa-users');
\$stat2Num = theme_get('stats.stat2_number', '1200+');
\$stat2Lab = theme_get('stats.stat2_label', 'Projects');
\$stat2Ico = theme_get('stats.stat2_icon', 'fas fa-project-diagram');
\$stat3Num = theme_get('stats.stat3_number', '98%');
\$stat3Lab = theme_get('stats.stat3_label', 'Satisfaction');
\$stat3Ico = theme_get('stats.stat3_icon', 'fas fa-smile');
\$stat4Num = theme_get('stats.stat4_number', '25+');
\$stat4Lab = theme_get('stats.stat4_label', 'Years');
\$stat4Ico = theme_get('stats.stat4_icon', 'fas fa-calendar-alt');
?>
<section class="{$p}-stats {$p}-stats--split-bg" id="stats">
  <div class="{$p}-stats-split-dark"></div>
  <div class="{$p}-stats-split-light"></div>
  <div class="container">
    <div class="{$p}-stats-split-inner">
      <div class="{$p}-stats-split-left" data-animate="fade-right">
        <?php if (\$statsBadge): ?><span class="{$p}-stats-badge" data-ts="stats.badge"><?= esc(\$statsBadge) ?></span><?php endif; ?>
        <h2 class="{$p}-stats-title" data-ts="stats.title"><?= esc(\$statsTitle) ?></h2>
        <p class="{$p}-stats-subtitle" data-ts="stats.subtitle"><?= esc(\$statsSubtitle) ?></p>
      </div>
      <div class="{$p}-stats-split-right" data-animate="fade-left">
        <div class="{$p}-stats-grid">
          <div class="{$p}-stats-item">
            <div class="{$p}-stats-icon"><i class="<?= esc(\$stat1Ico) ?>" data-ts="stats.stat1_icon"></i></div>
            <span class="{$p}-stats-number" data-ts="stats.stat1_number"><?= esc(\$stat1Num) ?></span>
            <span class="{$p}-stats-label" data-ts="stats.stat1_label"><?= esc(\$stat1Lab) ?></span>
          </div>
          <div class="{$p}-stats-item">
            <div class="{$p}-stats-icon"><i class="<?= esc(\$stat2Ico) ?>" data-ts="stats.stat2_icon"></i></div>
            <span class="{$p}-stats-number" data-ts="stats.stat2_number"><?= esc(\$stat2Num) ?></span>
            <span class="{$p}-stats-label" data-ts="stats.stat2_label"><?= esc(\$stat2Lab) ?></span>
          </div>
          <div class="{$p}-stats-item">
            <div class="{$p}-stats-icon"><i class="<?= esc(\$stat3Ico) ?>" data-ts="stats.stat3_icon"></i></div>
            <span class="{$p}-stats-number" data-ts="stats.stat3_number"><?= esc(\$stat3Num) ?></span>
            <span class="{$p}-stats-label" data-ts="stats.stat3_label"><?= esc(\$stat3Lab) ?></span>
          </div>
          <div class="{$p}-stats-item">
            <div class="{$p}-stats-icon"><i class="<?= esc(\$stat4Ico) ?>" data-ts="stats.stat4_icon"></i></div>
            <span class="{$p}-stats-number" data-ts="stats.stat4_number"><?= esc(\$stat4Num) ?></span>
            <span class="{$p}-stats-label" data-ts="stats.stat4_label"><?= esc(\$stat4Lab) ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Creative With Image: Background image with overlay, stats floating on top ──
'creative-with-image' => <<<HTML
<?php
\$statsTitle = theme_get('stats.title', 'Our Impact');
\$statsSubtitle = theme_get('stats.subtitle', 'Numbers that speak for themselves');
\$statsBadge = theme_get('stats.badge', '');
\$statsBgImage = theme_get('stats.bg_image', '');
\$stat1Num = theme_get('stats.stat1_number', '500+');
\$stat1Lab = theme_get('stats.stat1_label', 'Clients');
\$stat1Ico = theme_get('stats.stat1_icon', 'fas fa-users');
\$stat2Num = theme_get('stats.stat2_number', '1200+');
\$stat2Lab = theme_get('stats.stat2_label', 'Projects');
\$stat2Ico = theme_get('stats.stat2_icon', 'fas fa-project-diagram');
\$stat3Num = theme_get('stats.stat3_number', '98%');
\$stat3Lab = theme_get('stats.stat3_label', 'Satisfaction');
\$stat3Ico = theme_get('stats.stat3_icon', 'fas fa-smile');
\$stat4Num = theme_get('stats.stat4_number', '25+');
\$stat4Lab = theme_get('stats.stat4_label', 'Years');
\$stat4Ico = theme_get('stats.stat4_icon', 'fas fa-calendar-alt');
?>
<section class="{$p}-stats {$p}-stats--with-image" id="stats">
  <div class="{$p}-stats-bg" style="background-image: url('<?= esc(\$statsBgImage) ?>');" data-ts-bg="stats.bg_image"></div>
  <div class="{$p}-stats-overlay"></div>
  <div class="container">
    <div class="{$p}-stats-header" data-animate="fade-up">
      <?php if (\$statsBadge): ?><span class="{$p}-stats-badge" data-ts="stats.badge"><?= esc(\$statsBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-stats-title" data-ts="stats.title"><?= esc(\$statsTitle) ?></h2>
      <p class="{$p}-stats-subtitle" data-ts="stats.subtitle"><?= esc(\$statsSubtitle) ?></p>
    </div>
    <div class="{$p}-stats-grid" data-animate="fade-up">
      <div class="{$p}-stats-card">
        <div class="{$p}-stats-icon"><i class="<?= esc(\$stat1Ico) ?>" data-ts="stats.stat1_icon"></i></div>
        <span class="{$p}-stats-number" data-ts="stats.stat1_number"><?= esc(\$stat1Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat1_label"><?= esc(\$stat1Lab) ?></span>
      </div>
      <div class="{$p}-stats-card">
        <div class="{$p}-stats-icon"><i class="<?= esc(\$stat2Ico) ?>" data-ts="stats.stat2_icon"></i></div>
        <span class="{$p}-stats-number" data-ts="stats.stat2_number"><?= esc(\$stat2Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat2_label"><?= esc(\$stat2Lab) ?></span>
      </div>
      <div class="{$p}-stats-card">
        <div class="{$p}-stats-icon"><i class="<?= esc(\$stat3Ico) ?>" data-ts="stats.stat3_icon"></i></div>
        <span class="{$p}-stats-number" data-ts="stats.stat3_number"><?= esc(\$stat3Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat3_label"><?= esc(\$stat3Lab) ?></span>
      </div>
      <div class="{$p}-stats-card">
        <div class="{$p}-stats-icon"><i class="<?= esc(\$stat4Ico) ?>" data-ts="stats.stat4_icon"></i></div>
        <span class="{$p}-stats-number" data-ts="stats.stat4_number"><?= esc(\$stat4Num) ?></span>
        <span class="{$p}-stats-label" data-ts="stats.stat4_label"><?= esc(\$stat4Lab) ?></span>
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
        $base = ["{$p}-stats", "{$p}-stats-header", "{$p}-stats-badge", "{$p}-stats-title",
                 "{$p}-stats-subtitle", "{$p}-stats-grid", "{$p}-stats-item",
                 "{$p}-stats-number", "{$p}-stats-label"];

        $extra = match($patternId) {
            'counters-icons' =>
                ["{$p}-stats-icon"],
            'counters-boxed' =>
                ["{$p}-stats-card", "{$p}-stats-icon"],
            'visual-progress-bars' =>
                ["{$p}-stats-bars", "{$p}-stats-bar-item", "{$p}-stats-bar-header",
                 "{$p}-stats-bar-track", "{$p}-stats-bar-fill"],
            'visual-circular' =>
                ["{$p}-stats-circle"],
            'creative-split-bg' =>
                ["{$p}-stats-split-dark", "{$p}-stats-split-light", "{$p}-stats-split-inner",
                 "{$p}-stats-split-left", "{$p}-stats-split-right", "{$p}-stats-icon"],
            'creative-with-image' =>
                ["{$p}-stats-bg", "{$p}-stats-overlay", "{$p}-stats-card", "{$p}-stats-icon"],
            default => [],
        };

        return array_unique(array_merge($base, $extra));
    }

    private static function buildStructuralCSS(string $cssType, string $p): string
    {
        $css = self::css_base($p);

        $typeCSS = match($cssType) {
            'counters-row'         => self::css_counters_row($p),
            'counters-icons'       => self::css_counters_icons($p),
            'counters-boxed'       => self::css_counters_boxed($p),
            'visual-progress-bars' => self::css_progress_bars($p),
            'visual-circular'      => self::css_circular($p),
            'visual-large-numbers' => self::css_large_numbers($p),
            'creative-split-bg'    => self::css_split_bg($p),
            'creative-with-image'  => self::css_with_image($p),
            default                => self::css_counters_row($p),
        };

        return $css . $typeCSS . self::css_responsive($p);
    }

    // --- Base (shared by all stats patterns) ---
    private static function css_base(string $p): string
    {
        return <<<CSS

/* ═══ Stats Structural CSS (auto-generated — do not edit) ═══ */
.{$p}-stats {
  position: relative; overflow: hidden;
  padding: clamp(60px, 10vh, 120px) 0;
}
.{$p}-stats .container {
  position: relative; z-index: 2;
}
.{$p}-stats-header {
  text-align: center; margin-bottom: clamp(40px, 6vw, 64px);
  max-width: 650px; margin-left: auto; margin-right: auto;
}
.{$p}-stats-badge {
  display: inline-block;
  font-size: 0.8125rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.12em;
  padding: 6px 16px; border-radius: 100px;
  margin-bottom: 16px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.15);
  color: var(--primary, #3b82f6);
  border: 1px solid rgba(var(--primary-rgb, 42,125,225), 0.25);
}
.{$p}-stats-title {
  font-family: var(--font-heading, inherit);
  font-size: clamp(1.75rem, 4vw, 2.75rem);
  font-weight: 700; line-height: 1.2;
  margin: 0 0 16px 0;
  color: var(--text, #1e293b);
}
.{$p}-stats-subtitle {
  font-size: clamp(0.9375rem, 1.5vw, 1.125rem);
  line-height: 1.7; margin: 0;
  color: var(--text-muted, #64748b);
  max-width: 50ch; margin-left: auto; margin-right: auto;
}
.{$p}-stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: clamp(20px, 3vw, 40px);
}
.{$p}-stats-item {
  text-align: center;
}
.{$p}-stats-number {
  display: block; font-family: var(--font-heading, inherit);
  font-size: clamp(2rem, 4vw, 3rem); font-weight: 700;
  color: var(--primary, #3b82f6);
  line-height: 1.1;
}
.{$p}-stats-label {
  display: block; font-size: 0.875rem;
  text-transform: uppercase; letter-spacing: 0.08em;
  color: var(--text-muted, #64748b);
  margin-top: 8px;
}
@keyframes {$p}-countUp {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: translateY(0); }
}
.{$p}-stats-item,
.{$p}-stats-card,
.{$p}-stats-bar-item {
  animation: {$p}-countUp 0.6s ease both;
}
.{$p}-stats-item:nth-child(2),
.{$p}-stats-card:nth-child(2),
.{$p}-stats-bar-item:nth-child(2) { animation-delay: 0.1s; }
.{$p}-stats-item:nth-child(3),
.{$p}-stats-card:nth-child(3),
.{$p}-stats-bar-item:nth-child(3) { animation-delay: 0.2s; }
.{$p}-stats-item:nth-child(4),
.{$p}-stats-card:nth-child(4),
.{$p}-stats-bar-item:nth-child(4) { animation-delay: 0.3s; }

CSS;
    }

    // --- Counters Row ---
    private static function css_counters_row(string $p): string
    {
        return <<<CSS
.{$p}-stats--counters-row .{$p}-stats-item {
  position: relative;
}
.{$p}-stats--counters-row .{$p}-stats-item + .{$p}-stats-item::before {
  content: '';
  position: absolute; left: calc(-1 * clamp(10px, 1.5vw, 20px)); top: 15%; height: 70%;
  width: 1px;
  background: var(--border, rgba(0,0,0,0.1));
}

CSS;
    }

    // --- Counters Icons ---
    private static function css_counters_icons(string $p): string
    {
        return <<<CSS
.{$p}-stats-icon {
  display: flex; align-items: center; justify-content: center;
  width: 56px; height: 56px; border-radius: 50%;
  margin: 0 auto 16px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.1);
  color: var(--primary, #3b82f6);
  font-size: 1.25rem;
  transition: transform 0.3s ease;
}
.{$p}-stats-item:hover .{$p}-stats-icon {
  transform: scale(1.1);
}

CSS;
    }

    // --- Counters Boxed ---
    private static function css_counters_boxed(string $p): string
    {
        return <<<CSS
.{$p}-stats--counters-boxed .{$p}-stats-grid {
  gap: clamp(16px, 2vw, 24px);
}
.{$p}-stats-card {
  text-align: center; padding: clamp(24px, 3vw, 40px) clamp(16px, 2vw, 24px);
  border: 1px solid var(--border, rgba(0,0,0,0.1));
  border-radius: var(--radius, 12px);
  background: var(--surface, #fff);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-stats-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.08);
}
.{$p}-stats-card .{$p}-stats-icon {
  display: flex; align-items: center; justify-content: center;
  width: 52px; height: 52px; border-radius: var(--radius, 12px);
  margin: 0 auto 16px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.1);
  color: var(--primary, #3b82f6);
  font-size: 1.125rem;
}

CSS;
    }

    // --- Progress Bars ---
    private static function css_progress_bars(string $p): string
    {
        return <<<CSS
.{$p}-stats-bars {
  max-width: 700px; margin: 0 auto;
  display: flex; flex-direction: column; gap: 28px;
}
.{$p}-stats-bar-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 10px;
}
.{$p}-stats-bar-header .{$p}-stats-label {
  margin-top: 0; text-transform: none; letter-spacing: 0;
  font-size: 0.9375rem; font-weight: 600;
  color: var(--text, #1e293b);
}
.{$p}-stats-bar-header .{$p}-stats-number {
  font-size: 0.9375rem; font-weight: 700;
  color: var(--primary, #3b82f6);
}
.{$p}-stats-bar-track {
  width: 100%; height: 10px;
  border-radius: 100px; overflow: hidden;
  background: var(--border, rgba(0,0,0,0.08));
}
.{$p}-stats-bar-fill {
  height: 100%; border-radius: 100px;
  background: linear-gradient(90deg, var(--primary, #3b82f6), var(--secondary, #8b5cf6));
  width: 0;
  animation: {$p}-barGrow 1.5s ease forwards;
}
@keyframes {$p}-barGrow {
  to { width: var(--bar-width, 80%); }
}
.{$p}-stats-bar-item:nth-child(2) .{$p}-stats-bar-fill { animation-delay: 0.15s; }
.{$p}-stats-bar-item:nth-child(3) .{$p}-stats-bar-fill { animation-delay: 0.3s; }
.{$p}-stats-bar-item:nth-child(4) .{$p}-stats-bar-fill { animation-delay: 0.45s; }

CSS;
    }

    // --- Circular ---
    private static function css_circular(string $p): string
    {
        return <<<CSS
.{$p}-stats--circular .{$p}-stats-item {
  display: flex; flex-direction: column; align-items: center;
}
.{$p}-stats-circle {
  position: relative;
  width: 130px; height: 130px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 16px;
  background: conic-gradient(
    var(--primary, #3b82f6) 0%,
    var(--primary, #3b82f6) var(--circle-pct, 75%),
    var(--border, rgba(0,0,0,0.08)) var(--circle-pct, 75%),
    var(--border, rgba(0,0,0,0.08)) 100%
  );
  animation: {$p}-circleReveal 1.2s ease both;
}
.{$p}-stats-circle::before {
  content: '';
  position: absolute;
  width: calc(100% - 12px); height: calc(100% - 12px);
  border-radius: 50%;
  background: var(--background, #fff);
}
.{$p}-stats-circle .{$p}-stats-number {
  position: relative; z-index: 1;
  font-size: 1.5rem;
}
@keyframes {$p}-circleReveal {
  from { opacity: 0; transform: scale(0.8) rotate(-90deg); }
  to   { opacity: 1; transform: scale(1) rotate(0deg); }
}
.{$p}-stats--circular .{$p}-stats-item:nth-child(2) .{$p}-stats-circle { animation-delay: 0.15s; }
.{$p}-stats--circular .{$p}-stats-item:nth-child(3) .{$p}-stats-circle { animation-delay: 0.3s; }
.{$p}-stats--circular .{$p}-stats-item:nth-child(4) .{$p}-stats-circle { animation-delay: 0.45s; }

CSS;
    }

    // --- Large Numbers ---
    private static function css_large_numbers(string $p): string
    {
        return <<<CSS
.{$p}-stats--large-numbers .{$p}-stats-number {
  font-size: clamp(3.5rem, 8vw, 6rem);
  font-weight: 800;
  line-height: 1;
  background: linear-gradient(135deg, var(--primary, #3b82f6), var(--secondary, #8b5cf6));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.{$p}-stats--large-numbers .{$p}-stats-label {
  font-size: 1rem; margin-top: 12px;
  letter-spacing: 0.15em;
}

CSS;
    }

    // --- Split BG ---
    private static function css_split_bg(string $p): string
    {
        return <<<CSS
.{$p}-stats--split-bg {
  padding: 0;
}
.{$p}-stats-split-dark,
.{$p}-stats-split-light {
  position: absolute; top: 0; bottom: 0; width: 50%;
  z-index: 0;
}
.{$p}-stats-split-dark {
  left: 0;
  background: var(--dark-bg, #0f172a);
}
.{$p}-stats-split-light {
  right: 0;
  background: var(--surface, #f8fafc);
}
.{$p}-stats-split-inner {
  display: grid; grid-template-columns: 1fr 1fr;
  min-height: 400px;
}
.{$p}-stats-split-left {
  display: flex; flex-direction: column; justify-content: center;
  padding: clamp(40px, 6vw, 80px) clamp(20px, 4vw, 60px);
}
.{$p}-stats-split-left .{$p}-stats-title {
  color: #fff;
}
.{$p}-stats-split-left .{$p}-stats-subtitle {
  color: rgba(255,255,255,0.7);
  margin-left: 0; margin-right: 0;
}
.{$p}-stats-split-left .{$p}-stats-header {
  text-align: left; margin-bottom: 0;
  margin-left: 0; margin-right: 0;
}
.{$p}-stats-split-left .{$p}-stats-badge {
  background: rgba(255,255,255,0.1);
  color: #fff;
  border-color: rgba(255,255,255,0.2);
}
.{$p}-stats-split-right {
  display: flex; align-items: center; justify-content: center;
  padding: clamp(40px, 6vw, 80px) clamp(20px, 4vw, 60px);
}
.{$p}-stats-split-right .{$p}-stats-grid {
  grid-template-columns: repeat(2, 1fr);
  gap: clamp(24px, 3vw, 40px);
}
.{$p}-stats-split-right .{$p}-stats-number {
  color: var(--primary, #3b82f6);
}
.{$p}-stats-split-right .{$p}-stats-icon {
  display: flex; align-items: center; justify-content: center;
  width: 48px; height: 48px; border-radius: 50%;
  margin: 0 auto 12px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.1);
  color: var(--primary, #3b82f6);
  font-size: 1.125rem;
}

CSS;
    }

    // --- With Image ---
    private static function css_with_image(string $p): string
    {
        return <<<CSS
.{$p}-stats--with-image {
  padding: clamp(80px, 12vh, 140px) 0;
}
.{$p}-stats-bg {
  position: absolute; inset: 0;
  background-size: cover; background-position: center;
  z-index: 0;
}
.{$p}-stats-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.5) 100%);
  z-index: 1;
}
.{$p}-stats--with-image .{$p}-stats-title {
  color: #fff;
}
.{$p}-stats--with-image .{$p}-stats-subtitle {
  color: rgba(255,255,255,0.8);
}
.{$p}-stats--with-image .{$p}-stats-badge {
  background: rgba(255,255,255,0.15);
  color: #fff;
  border-color: rgba(255,255,255,0.25);
}
.{$p}-stats--with-image .{$p}-stats-card {
  text-align: center;
  padding: clamp(24px, 3vw, 36px) clamp(16px, 2vw, 24px);
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: var(--radius, 12px);
  transition: transform 0.3s ease, background 0.3s ease;
}
.{$p}-stats--with-image .{$p}-stats-card:hover {
  transform: translateY(-4px);
  background: rgba(255,255,255,0.15);
}
.{$p}-stats--with-image .{$p}-stats-number {
  color: #fff;
}
.{$p}-stats--with-image .{$p}-stats-label {
  color: rgba(255,255,255,0.7);
}
.{$p}-stats--with-image .{$p}-stats-icon {
  display: flex; align-items: center; justify-content: center;
  width: 48px; height: 48px; border-radius: 50%;
  margin: 0 auto 12px;
  background: rgba(255,255,255,0.15);
  color: #fff;
  font-size: 1.125rem;
}

CSS;
    }

    // --- Responsive ---
    private static function css_responsive(string $p): string
    {
        return <<<CSS
@media (max-width: 768px) {
  .{$p}-stats-grid {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 24px !important;
  }
  .{$p}-stats--counters-row .{$p}-stats-item + .{$p}-stats-item::before {
    display: none;
  }
  .{$p}-stats-split-inner {
    grid-template-columns: 1fr !important;
  }
  .{$p}-stats-split-dark,
  .{$p}-stats-split-light {
    width: 100% !important;
    position: relative !important;
  }
  .{$p}-stats-split-dark { height: auto; }
  .{$p}-stats-split-light { height: auto; }
  .{$p}-stats-circle {
    width: 100px; height: 100px;
  }
}

CSS;
    }
}
