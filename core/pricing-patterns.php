<?php
/**
 * Pricing Section Pattern Registry
 * 
 * Pre-built pricing HTML layouts with structural CSS.
 * AI generates only decorative CSS (colors, fonts, effects) — never the structure.
 * 
 * 10 patterns across 4 groups.
 * @since 2026-02-19
 */

class PricingPatternRegistry
{
    // ═══════════════════════════════════════
    // PATTERN DEFINITIONS
    // ═══════════════════════════════════════

    private static array $patterns = [
        // --- Columns (classic column layouts) ---
        ['id'=>'columns-3',          'group'=>'columns',     'css_type'=>'columns-3',
         'best_for'=>['saas','tech','startup','app','digital','fintech','ai','platform',
                      'hosting','cloud','vpn','email-marketing','crm']],
        ['id'=>'columns-2',          'group'=>'columns',     'css_type'=>'columns-2',
         'best_for'=>['freelance','photography','podcast','blog','newsletter','coaching',
                      'tutoring','personal-trainer','music','artist']],
        ['id'=>'columns-4',          'group'=>'columns',     'css_type'=>'columns-4',
         'best_for'=>['telecom','mobile','isp','web-design','seo','marketplace',
                      'ecommerce','retail']],

        // --- Cards (elevated / styled cards) ---
        ['id'=>'cards-elevated',     'group'=>'cards',       'css_type'=>'cards-elevated',
         'best_for'=>['agency','consulting','marketing','branding','social-media',
                      'creative-agency','design','advertising']],
        ['id'=>'cards-gradient',     'group'=>'cards',       'css_type'=>'cards-gradient',
         'best_for'=>['gaming','entertainment','streaming','nightclub','festival',
                      'concert','esports','vr']],
        ['id'=>'cards-horizontal',   'group'=>'cards',       'css_type'=>'cards-horizontal',
         'best_for'=>['legal','accounting','financial','bank','insurance','real-estate',
                      'mortgage','wealth-management']],

        // --- Comparison (table / toggle layouts) ---
        ['id'=>'comparison-table',   'group'=>'comparison',  'css_type'=>'comparison-table',
         'best_for'=>['healthcare','clinic','dental','pharmacy','hospital',
                      'manufacturing','logistics','enterprise','erp']],
        ['id'=>'comparison-toggle',  'group'=>'comparison',  'css_type'=>'comparison-toggle',
         'best_for'=>['gym','fitness','yoga','spa','salon','membership','coworking',
                      'storage','parking']],

        // --- Creative (unique layouts) ---
        ['id'=>'creative-slider',    'group'=>'creative',    'css_type'=>'creative-slider',
         'best_for'=>['restaurant','bakery','cafe','bar','hotel','resort','catering',
                      'event-planning','wedding','florist']],
        ['id'=>'creative-minimal',   'group'=>'creative',    'css_type'=>'creative-minimal',
         'best_for'=>['architecture','interior-design','gallery','museum','fashion',
                      'luxury','jewelry','art','nonprofit','charity']],
    ];

    // ═══════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════

    /**
     * Pick the best pricing pattern for an industry.
     */
    public static function pickPattern(string $industry): string
    {
        $industry = strtolower(trim($industry));
        foreach (self::$patterns as $p) {
            if (in_array($industry, $p['best_for'], true)) {
                return $p['id'];
            }
        }
        // Fallback: random from columns group (most versatile)
        $colPatterns = array_filter(self::$patterns, fn($p) => $p['group'] === 'columns');
        $colIds = array_column(array_values($colPatterns), 'id');
        return $colIds[array_rand($colIds)];
    }

    /**
     * Render a pricing pattern.
     * Returns ['html'=>..., 'structural_css'=>..., 'pattern_id'=>..., 'classes'=>[...], 'fields'=>[...]]
     */
    public static function render(string $patternId, string $prefix, array $brief): array
    {
        $def = null;
        foreach (self::$patterns as $p) {
            if ($p['id'] === $patternId) { $def = $p; break; }
        }
        if (!$def) {
            $def = self::$patterns[0]; // fallback to columns-3
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
        // Common fields all pricing sections have
        $common = [
            'title'          => ['type' => 'text',     'label' => 'Section Title'],
            'subtitle'       => ['type' => 'textarea', 'label' => 'Section Subtitle'],
            'badge'          => ['type' => 'text',     'label' => 'Badge / Label'],
        ];

        // Plan fields (repeat for 3 plans)
        $planFields = [];
        for ($i = 1; $i <= 3; $i++) {
            $planFields["plan{$i}_name"]     = ['type' => 'text', 'label' => "Plan {$i} Name"];
            $planFields["plan{$i}_price"]    = ['type' => 'text', 'label' => "Plan {$i} Price"];
            $planFields["plan{$i}_period"]   = ['type' => 'text', 'label' => "Plan {$i} Period"];
            $planFields["plan{$i}_desc"]     = ['type' => 'text', 'label' => "Plan {$i} Description"];
            $planFields["plan{$i}_features"] = ['type' => 'textarea', 'label' => "Plan {$i} Features (pipe-separated)"];
            $planFields["plan{$i}_btn_text"] = ['type' => 'text', 'label' => "Plan {$i} Button Text"];
            $planFields["plan{$i}_btn_link"] = ['type' => 'text', 'label' => "Plan {$i} Button Link"];
            $planFields["plan{$i}_featured"] = ['type' => 'text', 'label' => "Plan {$i} Featured (1=yes)"];
        }

        // Pattern-specific extras
        $extras = match($patternId) {
            'columns-4' => [
                'plan4_name'     => ['type' => 'text', 'label' => 'Plan 4 Name'],
                'plan4_price'    => ['type' => 'text', 'label' => 'Plan 4 Price'],
                'plan4_period'   => ['type' => 'text', 'label' => 'Plan 4 Period'],
                'plan4_desc'     => ['type' => 'text', 'label' => 'Plan 4 Description'],
                'plan4_features' => ['type' => 'textarea', 'label' => 'Plan 4 Features (pipe-separated)'],
                'plan4_btn_text' => ['type' => 'text', 'label' => 'Plan 4 Button Text'],
                'plan4_btn_link' => ['type' => 'text', 'label' => 'Plan 4 Button Link'],
                'plan4_featured' => ['type' => 'text', 'label' => 'Plan 4 Featured (1=yes)'],
            ],
            'comparison-toggle' => [
                'toggle_label1' => ['type' => 'text', 'label' => 'Toggle Label 1 (e.g. Monthly)'],
                'toggle_label2' => ['type' => 'text', 'label' => 'Toggle Label 2 (e.g. Yearly)'],
                'plan1_price_yearly' => ['type' => 'text', 'label' => 'Plan 1 Yearly Price'],
                'plan2_price_yearly' => ['type' => 'text', 'label' => 'Plan 2 Yearly Price'],
                'plan3_price_yearly' => ['type' => 'text', 'label' => 'Plan 3 Yearly Price'],
            ],
            default => [],
        };

        return array_merge($common, $planFields, $extras);
    }

    /**
     * Get decorative CSS guidance for a pattern (for AI CSS generation).
     * Returns hints about visual/decorative properties — NOT structural layout.
     */
    public static function getDecorativeGuide(string $patternId): string
    {
        return match($patternId) {
            'columns-3' => <<<'GUIDE'
Center/popular card visually larger: slightly scaled or extra top padding, primary border-top accent.
Popular badge uses primary bg, white text, small pill shape, positioned at card top.
Price amount uses extra-large bold font, heading color; period text small and muted.
Feature checkmark icons use primary/success color, feature list has generous line-height.
Non-featured cards have subtle border, featured card has primary border or shadow emphasis.
GUIDE,
            'columns-2' => <<<'GUIDE'
Two spacious cards with generous padding and prominent feature lists.
Featured card has primary border-left accent (4px solid) or colored top stripe.
Price uses very large bold font for impact, period text smaller and muted.
Feature list items well-spaced with checkmark icons in success/primary color.
Overall feel: spacious, comparison-focused, easy to scan side-by-side.
GUIDE,
            'columns-4' => <<<'GUIDE'
Compact cards with tighter padding and smaller font-sizes throughout.
Featured card uses primary border-top or subtle primary bg tint to stand out.
Price amount bold but not oversized — space-efficient display.
Feature text smaller (0.8125rem), compact line-height, abbreviated feature list.
Cards have thin borders, minimal shadow, efficient use of vertical space.
GUIDE,
            'cards-elevated' => <<<'GUIDE'
Heavy box-shadow on all cards (0 12px 40px rgba 0,0,0,0.1) for floating appearance.
No visible borders — shadow alone defines card edges, clean surface background.
Featured card has even deeper shadow and slight vertical offset for emphasis.
Hover: shadow transitions to gradient-tinted deeper shadow, subtle lift.
Popular badge uses primary bg pill at top of featured card.
GUIDE,
            'cards-gradient' => <<<'GUIDE'
Featured/popular card has gradient background (primary to secondary 135deg), white text throughout.
Non-featured cards use clean white/surface bg with standard dark text.
Featured card button inverted: white bg, primary text, contrasting against gradient.
Badge on featured card uses translucent white (rgba 0.2) on gradient bg.
Price on gradient card large white bold, feature checkmarks white on gradient.
GUIDE,
            'cards-horizontal' => <<<'GUIDE'
Horizontal card layout with subtle separator line between info/features/action sections.
Featured row has left border accent (4px solid primary) or subtle primary bg tint.
Price displayed prominently in action column, large bold with muted period text.
Feature list uses inline/compact styling, checkmarks in primary/success color.
Rows have subtle bottom border, hover: row bg tints very slightly.
GUIDE,
            'comparison-table' => <<<'GUIDE'
Table uses zebra striping — alternating row backgrounds (transparent / very subtle tint).
Checkmark icons use success/primary green, X icons use muted red/gray.
Featured column header has primary bg tint or bottom border accent.
Sticky header row with subtle shadow on scroll, clean border-bottom.
Table cells have comfortable padding, text centered in check columns.
GUIDE,
            'comparison-toggle' => <<<'GUIDE'
Toggle switch uses primary bg when active, muted gray when inactive, smooth slider animation.
Toggle knob is white circle that slides left/right with 0.3s ease transition.
Active period label uses primary color text, inactive label muted.
Save badge (e.g. Save 20%) uses small pill, success/accent color bg.
Price numbers animate/transition when toggling between monthly/yearly values.
GUIDE,
            'creative-slider' => <<<'GUIDE'
Swipeable cards have card-stack visual — active card elevated, adjacent cards behind and smaller.
Navigation dots below: active dot primary filled, inactive dots muted circles.
Active card has strong shadow and full opacity, side cards at 0.7 opacity and slight scale-down.
Card border-radius generous, surface bg, clean typography inside.
Overall feel: interactive, playful, one-at-a-time focus with depth effect.
GUIDE,
            'creative-minimal' => <<<'GUIDE'
Ultra-clean aesthetic: thin 1px borders only, no card backgrounds, no shadows.
Price uses very large bold font (clamp 2-3rem) as the visual anchor, heading color.
Typography-driven: plan names in caps or letter-spaced, descriptions muted.
Feature list minimal — no icons, just text with subtle indent or dash prefix.
Hover: border-color transitions to primary, no lift, no shadow — purely typographic.
GUIDE,
            default => <<<'GUIDE'
Cards use border-radius and subtle shadow for definition.
Featured/popular card has primary accent (border or badge).
Price amount large and bold, period text small and muted.
Feature checkmarks use primary or success color.
GUIDE,
        };
    }

    // ═══════════════════════════════════════
    // HTML TEMPLATES
    // ═══════════════════════════════════════

    /**
     * Replace generic placeholder defaults in pricing template with actual brief content.
     */
    private static function injectBriefContent(string $html, array $brief): string
    {
        $name = $brief['name'] ?? '';
        $industry = $brief['industry'] ?? '';

        $title = $brief['pricing_title'] ?? '';
        $subtitle = $brief['pricing_subtitle'] ?? '';
        $badge = $brief['pricing_badge'] ?? '';

        if (!$badge && $industry) {
            $badge = ucwords(str_replace('-', ' ', $industry));
        }

        $replacements = [];
        if ($title)    $replacements["theme_get('pricing.title', 'Simple, Transparent Pricing')"] = "theme_get('pricing.title', '" . addslashes($title) . "')";
        if ($subtitle) $replacements["theme_get('pricing.subtitle', 'Choose the plan that works best for you and your team.')"] = "theme_get('pricing.subtitle', '" . addslashes($subtitle) . "')";
        if ($badge)    $replacements["theme_get('pricing.badge', 'Pricing')"] = "theme_get('pricing.badge', '" . addslashes($badge) . "')";

        foreach ($replacements as $search => $replace) {
            $html = str_replace($search, $replace, $html);
        }

        return $html;
    }

    private static function buildHTML(string $patternId, string $p): string
    {
        $templates = self::getTemplates($p);
        return $templates[$patternId] ?? $templates['columns-3'];
    }

    private static function getTemplates(string $p): array
    {
        return [

// ── Columns-3: Classic 3-column pricing ──
'columns-3' => <<<HTML
<?php
\$pricingBadge = theme_get('pricing.badge', 'Pricing');
\$pricingTitle = theme_get('pricing.title', 'Simple, Transparent Pricing');
\$pricingSubtitle = theme_get('pricing.subtitle', 'Choose the plan that works best for you and your team.');

\$plan1Name = theme_get('pricing.plan1_name', 'Starter');
\$plan1Price = theme_get('pricing.plan1_price', '\$29');
\$plan1Period = theme_get('pricing.plan1_period', '/month');
\$plan1Desc = theme_get('pricing.plan1_desc', 'Perfect for individuals and small projects.');
\$plan1Features = theme_get('pricing.plan1_features', 'Up to 5 projects|10 GB storage|Basic analytics|Email support');
\$plan1BtnText = theme_get('pricing.plan1_btn_text', 'Get Started');
\$plan1BtnLink = theme_get('pricing.plan1_btn_link', '/contact');
\$plan1Featured = theme_get('pricing.plan1_featured', '');

\$plan2Name = theme_get('pricing.plan2_name', 'Professional');
\$plan2Price = theme_get('pricing.plan2_price', '\$79');
\$plan2Period = theme_get('pricing.plan2_period', '/month');
\$plan2Desc = theme_get('pricing.plan2_desc', 'Best for growing teams and businesses.');
\$plan2Features = theme_get('pricing.plan2_features', 'Unlimited projects|100 GB storage|Advanced analytics|Priority support|Team collaboration');
\$plan2BtnText = theme_get('pricing.plan2_btn_text', 'Get Started');
\$plan2BtnLink = theme_get('pricing.plan2_btn_link', '/contact');
\$plan2Featured = theme_get('pricing.plan2_featured', '1');

\$plan3Name = theme_get('pricing.plan3_name', 'Enterprise');
\$plan3Price = theme_get('pricing.plan3_price', '\$199');
\$plan3Period = theme_get('pricing.plan3_period', '/month');
\$plan3Desc = theme_get('pricing.plan3_desc', 'For large organizations with custom needs.');
\$plan3Features = theme_get('pricing.plan3_features', 'Everything in Pro|Unlimited storage|Custom integrations|Dedicated manager|SLA guarantee|SSO & audit logs');
\$plan3BtnText = theme_get('pricing.plan3_btn_text', 'Contact Sales');
\$plan3BtnLink = theme_get('pricing.plan3_btn_link', '/contact');
\$plan3Featured = theme_get('pricing.plan3_featured', '');
?>
<section class="{$p}-pricing {$p}-pricing--columns-3" id="pricing">
  <div class="container">
    <div class="{$p}-pricing-header" data-animate="fade-up">
      <?php if (\$pricingBadge): ?><span class="{$p}-pricing-badge" data-ts="pricing.badge"><?= esc(\$pricingBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-pricing-title" data-ts="pricing.title"><?= esc(\$pricingTitle) ?></h2>
      <p class="{$p}-pricing-subtitle" data-ts="pricing.subtitle"><?= esc(\$pricingSubtitle) ?></p>
    </div>
    <div class="{$p}-pricing-grid" data-animate="fade-up">
      <?php foreach ([
        ['name'=>\$plan1Name,'price'=>\$plan1Price,'period'=>\$plan1Period,'desc'=>\$plan1Desc,'features'=>\$plan1Features,'btn'=>\$plan1BtnText,'link'=>\$plan1BtnLink,'featured'=>\$plan1Featured,'n'=>'1'],
        ['name'=>\$plan2Name,'price'=>\$plan2Price,'period'=>\$plan2Period,'desc'=>\$plan2Desc,'features'=>\$plan2Features,'btn'=>\$plan2BtnText,'link'=>\$plan2BtnLink,'featured'=>\$plan2Featured,'n'=>'2'],
        ['name'=>\$plan3Name,'price'=>\$plan3Price,'period'=>\$plan3Period,'desc'=>\$plan3Desc,'features'=>\$plan3Features,'btn'=>\$plan3BtnText,'link'=>\$plan3BtnLink,'featured'=>\$plan3Featured,'n'=>'3'],
      ] as \$plan): ?>
      <div class="{$p}-pricing-plan<?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? ' {$p}-pricing-plan--featured' : '' ?>">
        <div class="{$p}-pricing-plan-header">
          <h3 class="{$p}-pricing-plan-name" data-ts="pricing.plan<?= \$plan['n'] ?>_name"><?= esc(\$plan['name']) ?></h3>
          <p class="{$p}-pricing-plan-desc" data-ts="pricing.plan<?= \$plan['n'] ?>_desc"><?= esc(\$plan['desc']) ?></p>
        </div>
        <div class="{$p}-pricing-plan-price">
          <span class="{$p}-pricing-amount" data-ts="pricing.plan<?= \$plan['n'] ?>_price"><?= esc(\$plan['price']) ?></span>
          <span class="{$p}-pricing-period" data-ts="pricing.plan<?= \$plan['n'] ?>_period"><?= esc(\$plan['period']) ?></span>
        </div>
        <ul class="{$p}-pricing-features">
          <?php foreach (explode('|', \$plan['features']) as \$feat): ?>
          <li><i class="fas fa-check"></i> <?= esc(trim(\$feat)) ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?= esc(\$plan['link']) ?>" class="{$p}-btn <?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? '{$p}-btn-primary' : '{$p}-btn-outline-pricing' ?>" data-ts="pricing.plan<?= \$plan['n'] ?>_btn_text" data-ts-href="pricing.plan<?= \$plan['n'] ?>_btn_link"><?= esc(\$plan['btn']) ?></a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Columns-2: Two plans side by side ──
'columns-2' => <<<HTML
<?php
\$pricingBadge = theme_get('pricing.badge', 'Pricing');
\$pricingTitle = theme_get('pricing.title', 'Simple, Transparent Pricing');
\$pricingSubtitle = theme_get('pricing.subtitle', 'Choose the plan that works best for you and your team.');

\$plan1Name = theme_get('pricing.plan1_name', 'Free');
\$plan1Price = theme_get('pricing.plan1_price', '\$0');
\$plan1Period = theme_get('pricing.plan1_period', '/month');
\$plan1Desc = theme_get('pricing.plan1_desc', 'Everything you need to get started.');
\$plan1Features = theme_get('pricing.plan1_features', '1 project|Basic templates|Community support|1 GB storage');
\$plan1BtnText = theme_get('pricing.plan1_btn_text', 'Start Free');
\$plan1BtnLink = theme_get('pricing.plan1_btn_link', '/signup');
\$plan1Featured = theme_get('pricing.plan1_featured', '');

\$plan2Name = theme_get('pricing.plan2_name', 'Premium');
\$plan2Price = theme_get('pricing.plan2_price', '\$49');
\$plan2Period = theme_get('pricing.plan2_period', '/month');
\$plan2Desc = theme_get('pricing.plan2_desc', 'Unlock all features and priority support.');
\$plan2Features = theme_get('pricing.plan2_features', 'Unlimited projects|All templates|Priority support|100 GB storage|Custom domain|Analytics dashboard');
\$plan2BtnText = theme_get('pricing.plan2_btn_text', 'Go Premium');
\$plan2BtnLink = theme_get('pricing.plan2_btn_link', '/contact');
\$plan2Featured = theme_get('pricing.plan2_featured', '1');
?>
<section class="{$p}-pricing {$p}-pricing--columns-2" id="pricing">
  <div class="container">
    <div class="{$p}-pricing-header" data-animate="fade-up">
      <?php if (\$pricingBadge): ?><span class="{$p}-pricing-badge" data-ts="pricing.badge"><?= esc(\$pricingBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-pricing-title" data-ts="pricing.title"><?= esc(\$pricingTitle) ?></h2>
      <p class="{$p}-pricing-subtitle" data-ts="pricing.subtitle"><?= esc(\$pricingSubtitle) ?></p>
    </div>
    <div class="{$p}-pricing-grid" data-animate="fade-up">
      <?php foreach ([
        ['name'=>\$plan1Name,'price'=>\$plan1Price,'period'=>\$plan1Period,'desc'=>\$plan1Desc,'features'=>\$plan1Features,'btn'=>\$plan1BtnText,'link'=>\$plan1BtnLink,'featured'=>\$plan1Featured,'n'=>'1'],
        ['name'=>\$plan2Name,'price'=>\$plan2Price,'period'=>\$plan2Period,'desc'=>\$plan2Desc,'features'=>\$plan2Features,'btn'=>\$plan2BtnText,'link'=>\$plan2BtnLink,'featured'=>\$plan2Featured,'n'=>'2'],
      ] as \$plan): ?>
      <div class="{$p}-pricing-plan<?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? ' {$p}-pricing-plan--featured' : '' ?>">
        <div class="{$p}-pricing-plan-header">
          <h3 class="{$p}-pricing-plan-name" data-ts="pricing.plan<?= \$plan['n'] ?>_name"><?= esc(\$plan['name']) ?></h3>
          <p class="{$p}-pricing-plan-desc" data-ts="pricing.plan<?= \$plan['n'] ?>_desc"><?= esc(\$plan['desc']) ?></p>
        </div>
        <div class="{$p}-pricing-plan-price">
          <span class="{$p}-pricing-amount" data-ts="pricing.plan<?= \$plan['n'] ?>_price"><?= esc(\$plan['price']) ?></span>
          <span class="{$p}-pricing-period" data-ts="pricing.plan<?= \$plan['n'] ?>_period"><?= esc(\$plan['period']) ?></span>
        </div>
        <ul class="{$p}-pricing-features">
          <?php foreach (explode('|', \$plan['features']) as \$feat): ?>
          <li><i class="fas fa-check"></i> <?= esc(trim(\$feat)) ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?= esc(\$plan['link']) ?>" class="{$p}-btn <?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? '{$p}-btn-primary' : '{$p}-btn-outline-pricing' ?>" data-ts="pricing.plan<?= \$plan['n'] ?>_btn_text" data-ts-href="pricing.plan<?= \$plan['n'] ?>_btn_link"><?= esc(\$plan['btn']) ?></a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Columns-4: Four tiers in a row, compact ──
'columns-4' => <<<HTML
<?php
\$pricingBadge = theme_get('pricing.badge', 'Pricing');
\$pricingTitle = theme_get('pricing.title', 'Simple, Transparent Pricing');
\$pricingSubtitle = theme_get('pricing.subtitle', 'Choose the plan that works best for you and your team.');

\$plan1Name = theme_get('pricing.plan1_name', 'Basic');
\$plan1Price = theme_get('pricing.plan1_price', '\$9');
\$plan1Period = theme_get('pricing.plan1_period', '/mo');
\$plan1Desc = theme_get('pricing.plan1_desc', 'For personal use.');
\$plan1Features = theme_get('pricing.plan1_features', '1 user|5 GB storage|Basic support');
\$plan1BtnText = theme_get('pricing.plan1_btn_text', 'Choose');
\$plan1BtnLink = theme_get('pricing.plan1_btn_link', '/contact');
\$plan1Featured = theme_get('pricing.plan1_featured', '');

\$plan2Name = theme_get('pricing.plan2_name', 'Standard');
\$plan2Price = theme_get('pricing.plan2_price', '\$29');
\$plan2Period = theme_get('pricing.plan2_period', '/mo');
\$plan2Desc = theme_get('pricing.plan2_desc', 'For small teams.');
\$plan2Features = theme_get('pricing.plan2_features', '5 users|50 GB storage|Priority support|API access');
\$plan2BtnText = theme_get('pricing.plan2_btn_text', 'Choose');
\$plan2BtnLink = theme_get('pricing.plan2_btn_link', '/contact');
\$plan2Featured = theme_get('pricing.plan2_featured', '');

\$plan3Name = theme_get('pricing.plan3_name', 'Professional');
\$plan3Price = theme_get('pricing.plan3_price', '\$59');
\$plan3Period = theme_get('pricing.plan3_period', '/mo');
\$plan3Desc = theme_get('pricing.plan3_desc', 'For growing businesses.');
\$plan3Features = theme_get('pricing.plan3_features', '25 users|200 GB storage|24/7 support|API access|Custom domain');
\$plan3BtnText = theme_get('pricing.plan3_btn_text', 'Choose');
\$plan3BtnLink = theme_get('pricing.plan3_btn_link', '/contact');
\$plan3Featured = theme_get('pricing.plan3_featured', '1');

\$plan4Name = theme_get('pricing.plan4_name', 'Enterprise');
\$plan4Price = theme_get('pricing.plan4_price', '\$149');
\$plan4Period = theme_get('pricing.plan4_period', '/mo');
\$plan4Desc = theme_get('pricing.plan4_desc', 'For large organizations.');
\$plan4Features = theme_get('pricing.plan4_features', 'Unlimited users|Unlimited storage|Dedicated support|Full API|SSO & compliance');
\$plan4BtnText = theme_get('pricing.plan4_btn_text', 'Contact Sales');
\$plan4BtnLink = theme_get('pricing.plan4_btn_link', '/contact');
\$plan4Featured = theme_get('pricing.plan4_featured', '');
?>
<section class="{$p}-pricing {$p}-pricing--columns-4" id="pricing">
  <div class="container">
    <div class="{$p}-pricing-header" data-animate="fade-up">
      <?php if (\$pricingBadge): ?><span class="{$p}-pricing-badge" data-ts="pricing.badge"><?= esc(\$pricingBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-pricing-title" data-ts="pricing.title"><?= esc(\$pricingTitle) ?></h2>
      <p class="{$p}-pricing-subtitle" data-ts="pricing.subtitle"><?= esc(\$pricingSubtitle) ?></p>
    </div>
    <div class="{$p}-pricing-grid" data-animate="fade-up">
      <?php foreach ([
        ['name'=>\$plan1Name,'price'=>\$plan1Price,'period'=>\$plan1Period,'desc'=>\$plan1Desc,'features'=>\$plan1Features,'btn'=>\$plan1BtnText,'link'=>\$plan1BtnLink,'featured'=>\$plan1Featured,'n'=>'1'],
        ['name'=>\$plan2Name,'price'=>\$plan2Price,'period'=>\$plan2Period,'desc'=>\$plan2Desc,'features'=>\$plan2Features,'btn'=>\$plan2BtnText,'link'=>\$plan2BtnLink,'featured'=>\$plan2Featured,'n'=>'2'],
        ['name'=>\$plan3Name,'price'=>\$plan3Price,'period'=>\$plan3Period,'desc'=>\$plan3Desc,'features'=>\$plan3Features,'btn'=>\$plan3BtnText,'link'=>\$plan3BtnLink,'featured'=>\$plan3Featured,'n'=>'3'],
        ['name'=>\$plan4Name,'price'=>\$plan4Price,'period'=>\$plan4Period,'desc'=>\$plan4Desc,'features'=>\$plan4Features,'btn'=>\$plan4BtnText,'link'=>\$plan4BtnLink,'featured'=>\$plan4Featured,'n'=>'4'],
      ] as \$plan): ?>
      <div class="{$p}-pricing-plan<?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? ' {$p}-pricing-plan--featured' : '' ?>">
        <div class="{$p}-pricing-plan-header">
          <h3 class="{$p}-pricing-plan-name" data-ts="pricing.plan<?= \$plan['n'] ?>_name"><?= esc(\$plan['name']) ?></h3>
          <p class="{$p}-pricing-plan-desc" data-ts="pricing.plan<?= \$plan['n'] ?>_desc"><?= esc(\$plan['desc']) ?></p>
        </div>
        <div class="{$p}-pricing-plan-price">
          <span class="{$p}-pricing-amount" data-ts="pricing.plan<?= \$plan['n'] ?>_price"><?= esc(\$plan['price']) ?></span>
          <span class="{$p}-pricing-period" data-ts="pricing.plan<?= \$plan['n'] ?>_period"><?= esc(\$plan['period']) ?></span>
        </div>
        <ul class="{$p}-pricing-features">
          <?php foreach (explode('|', \$plan['features']) as \$feat): ?>
          <li><i class="fas fa-check"></i> <?= esc(trim(\$feat)) ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?= esc(\$plan['link']) ?>" class="{$p}-btn <?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? '{$p}-btn-primary' : '{$p}-btn-outline-pricing' ?>" data-ts="pricing.plan<?= \$plan['n'] ?>_btn_text" data-ts-href="pricing.plan<?= \$plan['n'] ?>_btn_link"><?= esc(\$plan['btn']) ?></a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Cards-Elevated: Cards with shadow, featured plan raised ──
'cards-elevated' => <<<HTML
<?php
\$pricingBadge = theme_get('pricing.badge', 'Pricing');
\$pricingTitle = theme_get('pricing.title', 'Simple, Transparent Pricing');
\$pricingSubtitle = theme_get('pricing.subtitle', 'Choose the plan that works best for you and your team.');

\$plan1Name = theme_get('pricing.plan1_name', 'Starter');
\$plan1Price = theme_get('pricing.plan1_price', '\$29');
\$plan1Period = theme_get('pricing.plan1_period', '/month');
\$plan1Desc = theme_get('pricing.plan1_desc', 'Perfect for individuals and small projects.');
\$plan1Features = theme_get('pricing.plan1_features', 'Up to 5 projects|10 GB storage|Basic analytics|Email support');
\$plan1BtnText = theme_get('pricing.plan1_btn_text', 'Get Started');
\$plan1BtnLink = theme_get('pricing.plan1_btn_link', '/contact');
\$plan1Featured = theme_get('pricing.plan1_featured', '');

\$plan2Name = theme_get('pricing.plan2_name', 'Professional');
\$plan2Price = theme_get('pricing.plan2_price', '\$79');
\$plan2Period = theme_get('pricing.plan2_period', '/month');
\$plan2Desc = theme_get('pricing.plan2_desc', 'Best for growing teams and businesses.');
\$plan2Features = theme_get('pricing.plan2_features', 'Unlimited projects|100 GB storage|Advanced analytics|Priority support|Team collaboration');
\$plan2BtnText = theme_get('pricing.plan2_btn_text', 'Get Started');
\$plan2BtnLink = theme_get('pricing.plan2_btn_link', '/contact');
\$plan2Featured = theme_get('pricing.plan2_featured', '1');

\$plan3Name = theme_get('pricing.plan3_name', 'Enterprise');
\$plan3Price = theme_get('pricing.plan3_price', '\$199');
\$plan3Period = theme_get('pricing.plan3_period', '/month');
\$plan3Desc = theme_get('pricing.plan3_desc', 'For large organizations with custom needs.');
\$plan3Features = theme_get('pricing.plan3_features', 'Everything in Pro|Unlimited storage|Custom integrations|Dedicated manager|SLA guarantee|SSO & audit logs');
\$plan3BtnText = theme_get('pricing.plan3_btn_text', 'Contact Sales');
\$plan3BtnLink = theme_get('pricing.plan3_btn_link', '/contact');
\$plan3Featured = theme_get('pricing.plan3_featured', '');
?>
<section class="{$p}-pricing {$p}-pricing--cards-elevated" id="pricing">
  <div class="container">
    <div class="{$p}-pricing-header" data-animate="fade-up">
      <?php if (\$pricingBadge): ?><span class="{$p}-pricing-badge" data-ts="pricing.badge"><?= esc(\$pricingBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-pricing-title" data-ts="pricing.title"><?= esc(\$pricingTitle) ?></h2>
      <p class="{$p}-pricing-subtitle" data-ts="pricing.subtitle"><?= esc(\$pricingSubtitle) ?></p>
    </div>
    <div class="{$p}-pricing-grid" data-animate="fade-up">
      <?php foreach ([
        ['name'=>\$plan1Name,'price'=>\$plan1Price,'period'=>\$plan1Period,'desc'=>\$plan1Desc,'features'=>\$plan1Features,'btn'=>\$plan1BtnText,'link'=>\$plan1BtnLink,'featured'=>\$plan1Featured,'n'=>'1'],
        ['name'=>\$plan2Name,'price'=>\$plan2Price,'period'=>\$plan2Period,'desc'=>\$plan2Desc,'features'=>\$plan2Features,'btn'=>\$plan2BtnText,'link'=>\$plan2BtnLink,'featured'=>\$plan2Featured,'n'=>'2'],
        ['name'=>\$plan3Name,'price'=>\$plan3Price,'period'=>\$plan3Period,'desc'=>\$plan3Desc,'features'=>\$plan3Features,'btn'=>\$plan3BtnText,'link'=>\$plan3BtnLink,'featured'=>\$plan3Featured,'n'=>'3'],
      ] as \$plan): ?>
      <div class="{$p}-pricing-plan<?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? ' {$p}-pricing-plan--featured' : '' ?>">
        <?php if (\$plan['featured'] === '1' || \$plan['featured'] === 'true'): ?><div class="{$p}-pricing-plan-badge">Most Popular</div><?php endif; ?>
        <div class="{$p}-pricing-plan-header">
          <h3 class="{$p}-pricing-plan-name" data-ts="pricing.plan<?= \$plan['n'] ?>_name"><?= esc(\$plan['name']) ?></h3>
          <p class="{$p}-pricing-plan-desc" data-ts="pricing.plan<?= \$plan['n'] ?>_desc"><?= esc(\$plan['desc']) ?></p>
        </div>
        <div class="{$p}-pricing-plan-price">
          <span class="{$p}-pricing-amount" data-ts="pricing.plan<?= \$plan['n'] ?>_price"><?= esc(\$plan['price']) ?></span>
          <span class="{$p}-pricing-period" data-ts="pricing.plan<?= \$plan['n'] ?>_period"><?= esc(\$plan['period']) ?></span>
        </div>
        <ul class="{$p}-pricing-features">
          <?php foreach (explode('|', \$plan['features']) as \$feat): ?>
          <li><i class="fas fa-check"></i> <?= esc(trim(\$feat)) ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?= esc(\$plan['link']) ?>" class="{$p}-btn <?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? '{$p}-btn-primary' : '{$p}-btn-outline-pricing' ?>" data-ts="pricing.plan<?= \$plan['n'] ?>_btn_text" data-ts-href="pricing.plan<?= \$plan['n'] ?>_btn_link"><?= esc(\$plan['btn']) ?></a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Cards-Gradient: Featured card has gradient bg ──
'cards-gradient' => <<<HTML
<?php
\$pricingBadge = theme_get('pricing.badge', 'Pricing');
\$pricingTitle = theme_get('pricing.title', 'Simple, Transparent Pricing');
\$pricingSubtitle = theme_get('pricing.subtitle', 'Choose the plan that works best for you and your team.');

\$plan1Name = theme_get('pricing.plan1_name', 'Starter');
\$plan1Price = theme_get('pricing.plan1_price', '\$19');
\$plan1Period = theme_get('pricing.plan1_period', '/month');
\$plan1Desc = theme_get('pricing.plan1_desc', 'Great for getting started.');
\$plan1Features = theme_get('pricing.plan1_features', '3 projects|5 GB storage|Community support|Basic features');
\$plan1BtnText = theme_get('pricing.plan1_btn_text', 'Start Free');
\$plan1BtnLink = theme_get('pricing.plan1_btn_link', '/contact');
\$plan1Featured = theme_get('pricing.plan1_featured', '');

\$plan2Name = theme_get('pricing.plan2_name', 'Pro');
\$plan2Price = theme_get('pricing.plan2_price', '\$49');
\$plan2Period = theme_get('pricing.plan2_period', '/month');
\$plan2Desc = theme_get('pricing.plan2_desc', 'Most popular for teams.');
\$plan2Features = theme_get('pricing.plan2_features', 'Unlimited projects|50 GB storage|Priority support|Advanced features|Team tools');
\$plan2BtnText = theme_get('pricing.plan2_btn_text', 'Get Pro');
\$plan2BtnLink = theme_get('pricing.plan2_btn_link', '/contact');
\$plan2Featured = theme_get('pricing.plan2_featured', '1');

\$plan3Name = theme_get('pricing.plan3_name', 'Ultimate');
\$plan3Price = theme_get('pricing.plan3_price', '\$99');
\$plan3Period = theme_get('pricing.plan3_period', '/month');
\$plan3Desc = theme_get('pricing.plan3_desc', 'Everything, no limits.');
\$plan3Features = theme_get('pricing.plan3_features', 'Everything in Pro|Unlimited storage|White-label|Dedicated support|Custom integrations|API access');
\$plan3BtnText = theme_get('pricing.plan3_btn_text', 'Go Ultimate');
\$plan3BtnLink = theme_get('pricing.plan3_btn_link', '/contact');
\$plan3Featured = theme_get('pricing.plan3_featured', '');
?>
<section class="{$p}-pricing {$p}-pricing--cards-gradient" id="pricing">
  <div class="container">
    <div class="{$p}-pricing-header" data-animate="fade-up">
      <?php if (\$pricingBadge): ?><span class="{$p}-pricing-badge" data-ts="pricing.badge"><?= esc(\$pricingBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-pricing-title" data-ts="pricing.title"><?= esc(\$pricingTitle) ?></h2>
      <p class="{$p}-pricing-subtitle" data-ts="pricing.subtitle"><?= esc(\$pricingSubtitle) ?></p>
    </div>
    <div class="{$p}-pricing-grid" data-animate="fade-up">
      <?php foreach ([
        ['name'=>\$plan1Name,'price'=>\$plan1Price,'period'=>\$plan1Period,'desc'=>\$plan1Desc,'features'=>\$plan1Features,'btn'=>\$plan1BtnText,'link'=>\$plan1BtnLink,'featured'=>\$plan1Featured,'n'=>'1'],
        ['name'=>\$plan2Name,'price'=>\$plan2Price,'period'=>\$plan2Period,'desc'=>\$plan2Desc,'features'=>\$plan2Features,'btn'=>\$plan2BtnText,'link'=>\$plan2BtnLink,'featured'=>\$plan2Featured,'n'=>'2'],
        ['name'=>\$plan3Name,'price'=>\$plan3Price,'period'=>\$plan3Period,'desc'=>\$plan3Desc,'features'=>\$plan3Features,'btn'=>\$plan3BtnText,'link'=>\$plan3BtnLink,'featured'=>\$plan3Featured,'n'=>'3'],
      ] as \$plan): ?>
      <div class="{$p}-pricing-plan<?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? ' {$p}-pricing-plan--featured' : '' ?>">
        <?php if (\$plan['featured'] === '1' || \$plan['featured'] === 'true'): ?><div class="{$p}-pricing-plan-badge">Best Value</div><?php endif; ?>
        <div class="{$p}-pricing-plan-header">
          <h3 class="{$p}-pricing-plan-name" data-ts="pricing.plan<?= \$plan['n'] ?>_name"><?= esc(\$plan['name']) ?></h3>
          <p class="{$p}-pricing-plan-desc" data-ts="pricing.plan<?= \$plan['n'] ?>_desc"><?= esc(\$plan['desc']) ?></p>
        </div>
        <div class="{$p}-pricing-plan-price">
          <span class="{$p}-pricing-amount" data-ts="pricing.plan<?= \$plan['n'] ?>_price"><?= esc(\$plan['price']) ?></span>
          <span class="{$p}-pricing-period" data-ts="pricing.plan<?= \$plan['n'] ?>_period"><?= esc(\$plan['period']) ?></span>
        </div>
        <ul class="{$p}-pricing-features">
          <?php foreach (explode('|', \$plan['features']) as \$feat): ?>
          <li><i class="fas fa-check"></i> <?= esc(trim(\$feat)) ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?= esc(\$plan['link']) ?>" class="{$p}-btn <?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? '{$p}-btn-primary' : '{$p}-btn-outline-pricing' ?>" data-ts="pricing.plan<?= \$plan['n'] ?>_btn_text" data-ts-href="pricing.plan<?= \$plan['n'] ?>_btn_link"><?= esc(\$plan['btn']) ?></a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Cards-Horizontal: Horizontal layout per plan ──
'cards-horizontal' => <<<HTML
<?php
\$pricingBadge = theme_get('pricing.badge', 'Pricing');
\$pricingTitle = theme_get('pricing.title', 'Simple, Transparent Pricing');
\$pricingSubtitle = theme_get('pricing.subtitle', 'Choose the plan that works best for you and your team.');

\$plan1Name = theme_get('pricing.plan1_name', 'Essential');
\$plan1Price = theme_get('pricing.plan1_price', '\$49');
\$plan1Period = theme_get('pricing.plan1_period', '/month');
\$plan1Desc = theme_get('pricing.plan1_desc', 'Core features for individuals.');
\$plan1Features = theme_get('pricing.plan1_features', 'Up to 5 projects|10 GB storage|Email support|Basic reporting');
\$plan1BtnText = theme_get('pricing.plan1_btn_text', 'Choose Plan');
\$plan1BtnLink = theme_get('pricing.plan1_btn_link', '/contact');
\$plan1Featured = theme_get('pricing.plan1_featured', '');

\$plan2Name = theme_get('pricing.plan2_name', 'Business');
\$plan2Price = theme_get('pricing.plan2_price', '\$99');
\$plan2Period = theme_get('pricing.plan2_period', '/month');
\$plan2Desc = theme_get('pricing.plan2_desc', 'Advanced tools for teams.');
\$plan2Features = theme_get('pricing.plan2_features', 'Unlimited projects|100 GB storage|Priority support|Advanced reporting|Collaboration tools');
\$plan2BtnText = theme_get('pricing.plan2_btn_text', 'Choose Plan');
\$plan2BtnLink = theme_get('pricing.plan2_btn_link', '/contact');
\$plan2Featured = theme_get('pricing.plan2_featured', '1');

\$plan3Name = theme_get('pricing.plan3_name', 'Corporate');
\$plan3Price = theme_get('pricing.plan3_price', '\$249');
\$plan3Period = theme_get('pricing.plan3_period', '/month');
\$plan3Desc = theme_get('pricing.plan3_desc', 'Full suite for enterprises.');
\$plan3Features = theme_get('pricing.plan3_features', 'Everything in Business|Unlimited storage|Dedicated account manager|Custom SLA|Compliance & audit');
\$plan3BtnText = theme_get('pricing.plan3_btn_text', 'Contact Sales');
\$plan3BtnLink = theme_get('pricing.plan3_btn_link', '/contact');
\$plan3Featured = theme_get('pricing.plan3_featured', '');
?>
<section class="{$p}-pricing {$p}-pricing--cards-horizontal" id="pricing">
  <div class="container">
    <div class="{$p}-pricing-header" data-animate="fade-up">
      <?php if (\$pricingBadge): ?><span class="{$p}-pricing-badge" data-ts="pricing.badge"><?= esc(\$pricingBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-pricing-title" data-ts="pricing.title"><?= esc(\$pricingTitle) ?></h2>
      <p class="{$p}-pricing-subtitle" data-ts="pricing.subtitle"><?= esc(\$pricingSubtitle) ?></p>
    </div>
    <div class="{$p}-pricing-rows" data-animate="fade-up">
      <?php foreach ([
        ['name'=>\$plan1Name,'price'=>\$plan1Price,'period'=>\$plan1Period,'desc'=>\$plan1Desc,'features'=>\$plan1Features,'btn'=>\$plan1BtnText,'link'=>\$plan1BtnLink,'featured'=>\$plan1Featured,'n'=>'1'],
        ['name'=>\$plan2Name,'price'=>\$plan2Price,'period'=>\$plan2Period,'desc'=>\$plan2Desc,'features'=>\$plan2Features,'btn'=>\$plan2BtnText,'link'=>\$plan2BtnLink,'featured'=>\$plan2Featured,'n'=>'2'],
        ['name'=>\$plan3Name,'price'=>\$plan3Price,'period'=>\$plan3Period,'desc'=>\$plan3Desc,'features'=>\$plan3Features,'btn'=>\$plan3BtnText,'link'=>\$plan3BtnLink,'featured'=>\$plan3Featured,'n'=>'3'],
      ] as \$plan): ?>
      <div class="{$p}-pricing-row<?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? ' {$p}-pricing-row--featured' : '' ?>">
        <div class="{$p}-pricing-row-info">
          <h3 class="{$p}-pricing-plan-name" data-ts="pricing.plan<?= \$plan['n'] ?>_name"><?= esc(\$plan['name']) ?></h3>
          <p class="{$p}-pricing-plan-desc" data-ts="pricing.plan<?= \$plan['n'] ?>_desc"><?= esc(\$plan['desc']) ?></p>
        </div>
        <div class="{$p}-pricing-row-features">
          <ul class="{$p}-pricing-features">
            <?php foreach (explode('|', \$plan['features']) as \$feat): ?>
            <li><i class="fas fa-check"></i> <?= esc(trim(\$feat)) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="{$p}-pricing-row-action">
          <div class="{$p}-pricing-plan-price">
            <span class="{$p}-pricing-amount" data-ts="pricing.plan<?= \$plan['n'] ?>_price"><?= esc(\$plan['price']) ?></span>
            <span class="{$p}-pricing-period" data-ts="pricing.plan<?= \$plan['n'] ?>_period"><?= esc(\$plan['period']) ?></span>
          </div>
          <a href="<?= esc(\$plan['link']) ?>" class="{$p}-btn <?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? '{$p}-btn-primary' : '{$p}-btn-outline-pricing' ?>" data-ts="pricing.plan<?= \$plan['n'] ?>_btn_text" data-ts-href="pricing.plan<?= \$plan['n'] ?>_btn_link"><?= esc(\$plan['btn']) ?></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Comparison-Table: Full comparison table with checkmarks ──
'comparison-table' => <<<HTML
<?php
\$pricingBadge = theme_get('pricing.badge', 'Pricing');
\$pricingTitle = theme_get('pricing.title', 'Simple, Transparent Pricing');
\$pricingSubtitle = theme_get('pricing.subtitle', 'Choose the plan that works best for you and your team.');

\$plan1Name = theme_get('pricing.plan1_name', 'Basic');
\$plan1Price = theme_get('pricing.plan1_price', '\$29');
\$plan1Period = theme_get('pricing.plan1_period', '/month');
\$plan1Desc = theme_get('pricing.plan1_desc', 'For individuals.');
\$plan1Features = theme_get('pricing.plan1_features', 'Core Features|5 Projects|Email Support|Basic Analytics|Community Access');
\$plan1BtnText = theme_get('pricing.plan1_btn_text', 'Get Started');
\$plan1BtnLink = theme_get('pricing.plan1_btn_link', '/contact');
\$plan1Featured = theme_get('pricing.plan1_featured', '');

\$plan2Name = theme_get('pricing.plan2_name', 'Professional');
\$plan2Price = theme_get('pricing.plan2_price', '\$79');
\$plan2Period = theme_get('pricing.plan2_period', '/month');
\$plan2Desc = theme_get('pricing.plan2_desc', 'For teams.');
\$plan2Features = theme_get('pricing.plan2_features', 'Core Features|Unlimited Projects|Priority Support|Advanced Analytics|Community Access|API Access|Custom Branding');
\$plan2BtnText = theme_get('pricing.plan2_btn_text', 'Get Started');
\$plan2BtnLink = theme_get('pricing.plan2_btn_link', '/contact');
\$plan2Featured = theme_get('pricing.plan2_featured', '1');

\$plan3Name = theme_get('pricing.plan3_name', 'Enterprise');
\$plan3Price = theme_get('pricing.plan3_price', '\$199');
\$plan3Period = theme_get('pricing.plan3_period', '/month');
\$plan3Desc = theme_get('pricing.plan3_desc', 'For organizations.');
\$plan3Features = theme_get('pricing.plan3_features', 'Core Features|Unlimited Projects|Dedicated Support|Advanced Analytics|Community Access|API Access|Custom Branding|SSO & SAML|Dedicated Manager|SLA Guarantee');
\$plan3BtnText = theme_get('pricing.plan3_btn_text', 'Contact Sales');
\$plan3BtnLink = theme_get('pricing.plan3_btn_link', '/contact');
\$plan3Featured = theme_get('pricing.plan3_featured', '');

// Build a unified feature list from all plans
\$allFeaturesList = [];
foreach ([\$plan1Features, \$plan2Features, \$plan3Features] as \$pf) {
    foreach (explode('|', \$pf) as \$f) {
        \$ft = trim(\$f);
        if (\$ft && !in_array(\$ft, \$allFeaturesList)) {
            \$allFeaturesList[] = \$ft;
        }
    }
}
\$plan1Arr = array_map('trim', explode('|', \$plan1Features));
\$plan2Arr = array_map('trim', explode('|', \$plan2Features));
\$plan3Arr = array_map('trim', explode('|', \$plan3Features));
?>
<section class="{$p}-pricing {$p}-pricing--comparison-table" id="pricing">
  <div class="container">
    <div class="{$p}-pricing-header" data-animate="fade-up">
      <?php if (\$pricingBadge): ?><span class="{$p}-pricing-badge" data-ts="pricing.badge"><?= esc(\$pricingBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-pricing-title" data-ts="pricing.title"><?= esc(\$pricingTitle) ?></h2>
      <p class="{$p}-pricing-subtitle" data-ts="pricing.subtitle"><?= esc(\$pricingSubtitle) ?></p>
    </div>
    <!-- Desktop: Comparison table -->
    <div class="{$p}-pricing-table-wrap" data-animate="fade-up">
      <table class="{$p}-pricing-table">
        <thead>
          <tr>
            <th class="{$p}-pricing-table-feature-col">Features</th>
            <?php foreach ([
              ['name'=>\$plan1Name,'price'=>\$plan1Price,'period'=>\$plan1Period,'btn'=>\$plan1BtnText,'link'=>\$plan1BtnLink,'featured'=>\$plan1Featured,'n'=>'1'],
              ['name'=>\$plan2Name,'price'=>\$plan2Price,'period'=>\$plan2Period,'btn'=>\$plan2BtnText,'link'=>\$plan2BtnLink,'featured'=>\$plan2Featured,'n'=>'2'],
              ['name'=>\$plan3Name,'price'=>\$plan3Price,'period'=>\$plan3Period,'btn'=>\$plan3BtnText,'link'=>\$plan3BtnLink,'featured'=>\$plan3Featured,'n'=>'3'],
            ] as \$pl): ?>
            <th class="<?= (\$pl['featured'] === '1' || \$pl['featured'] === 'true') ? '{$p}-pricing-table-col--featured' : '' ?>">
              <div class="{$p}-pricing-table-plan">
                <span class="{$p}-pricing-plan-name" data-ts="pricing.plan<?= \$pl['n'] ?>_name"><?= esc(\$pl['name']) ?></span>
                <span class="{$p}-pricing-amount" data-ts="pricing.plan<?= \$pl['n'] ?>_price"><?= esc(\$pl['price']) ?></span>
                <span class="{$p}-pricing-period" data-ts="pricing.plan<?= \$pl['n'] ?>_period"><?= esc(\$pl['period']) ?></span>
              </div>
            </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach (\$allFeaturesList as \$feature): ?>
          <tr>
            <td><?= esc(\$feature) ?></td>
            <td class="{$p}-pricing-table-check"><?= in_array(\$feature, \$plan1Arr) ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' ?></td>
            <td class="{$p}-pricing-table-check <?= (\$plan2Featured === '1' || \$plan2Featured === 'true') ? '{$p}-pricing-table-col--featured' : '' ?>"><?= in_array(\$feature, \$plan2Arr) ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' ?></td>
            <td class="{$p}-pricing-table-check"><?= in_array(\$feature, \$plan3Arr) ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td></td>
            <?php foreach ([
              ['btn'=>\$plan1BtnText,'link'=>\$plan1BtnLink,'featured'=>\$plan1Featured,'n'=>'1'],
              ['btn'=>\$plan2BtnText,'link'=>\$plan2BtnLink,'featured'=>\$plan2Featured,'n'=>'2'],
              ['btn'=>\$plan3BtnText,'link'=>\$plan3BtnLink,'featured'=>\$plan3Featured,'n'=>'3'],
            ] as \$pl): ?>
            <td>
              <a href="<?= esc(\$pl['link']) ?>" class="{$p}-btn <?= (\$pl['featured'] === '1' || \$pl['featured'] === 'true') ? '{$p}-btn-primary' : '{$p}-btn-outline-pricing' ?>" data-ts="pricing.plan<?= \$pl['n'] ?>_btn_text" data-ts-href="pricing.plan<?= \$pl['n'] ?>_btn_link"><?= esc(\$pl['btn']) ?></a>
            </td>
            <?php endforeach; ?>
          </tr>
        </tfoot>
      </table>
    </div>
    <!-- Mobile: Card fallback -->
    <div class="{$p}-pricing-mobile-cards" data-animate="fade-up">
      <?php foreach ([
        ['name'=>\$plan1Name,'price'=>\$plan1Price,'period'=>\$plan1Period,'desc'=>\$plan1Desc,'features'=>\$plan1Features,'btn'=>\$plan1BtnText,'link'=>\$plan1BtnLink,'featured'=>\$plan1Featured,'n'=>'1'],
        ['name'=>\$plan2Name,'price'=>\$plan2Price,'period'=>\$plan2Period,'desc'=>\$plan2Desc,'features'=>\$plan2Features,'btn'=>\$plan2BtnText,'link'=>\$plan2BtnLink,'featured'=>\$plan2Featured,'n'=>'2'],
        ['name'=>\$plan3Name,'price'=>\$plan3Price,'period'=>\$plan3Period,'desc'=>\$plan3Desc,'features'=>\$plan3Features,'btn'=>\$plan3BtnText,'link'=>\$plan3BtnLink,'featured'=>\$plan3Featured,'n'=>'3'],
      ] as \$plan): ?>
      <div class="{$p}-pricing-plan<?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? ' {$p}-pricing-plan--featured' : '' ?>">
        <div class="{$p}-pricing-plan-header">
          <h3 class="{$p}-pricing-plan-name"><?= esc(\$plan['name']) ?></h3>
        </div>
        <div class="{$p}-pricing-plan-price">
          <span class="{$p}-pricing-amount"><?= esc(\$plan['price']) ?></span>
          <span class="{$p}-pricing-period"><?= esc(\$plan['period']) ?></span>
        </div>
        <ul class="{$p}-pricing-features">
          <?php foreach (explode('|', \$plan['features']) as \$feat): ?>
          <li><i class="fas fa-check"></i> <?= esc(trim(\$feat)) ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?= esc(\$plan['link']) ?>" class="{$p}-btn <?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? '{$p}-btn-primary' : '{$p}-btn-outline-pricing' ?>"><?= esc(\$plan['btn']) ?></a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Comparison-Toggle: Monthly/Yearly toggle with columns ──
'comparison-toggle' => <<<HTML
<?php
\$pricingBadge = theme_get('pricing.badge', 'Pricing');
\$pricingTitle = theme_get('pricing.title', 'Simple, Transparent Pricing');
\$pricingSubtitle = theme_get('pricing.subtitle', 'Choose the plan that works best for you and your team.');
\$toggleLabel1 = theme_get('pricing.toggle_label1', 'Monthly');
\$toggleLabel2 = theme_get('pricing.toggle_label2', 'Yearly');

\$plan1Name = theme_get('pricing.plan1_name', 'Starter');
\$plan1Price = theme_get('pricing.plan1_price', '\$29');
\$plan1PriceYearly = theme_get('pricing.plan1_price_yearly', '\$24');
\$plan1Period = theme_get('pricing.plan1_period', '/month');
\$plan1Desc = theme_get('pricing.plan1_desc', 'Perfect for individuals.');
\$plan1Features = theme_get('pricing.plan1_features', 'Up to 5 projects|10 GB storage|Basic analytics|Email support');
\$plan1BtnText = theme_get('pricing.plan1_btn_text', 'Get Started');
\$plan1BtnLink = theme_get('pricing.plan1_btn_link', '/contact');
\$plan1Featured = theme_get('pricing.plan1_featured', '');

\$plan2Name = theme_get('pricing.plan2_name', 'Professional');
\$plan2Price = theme_get('pricing.plan2_price', '\$59');
\$plan2PriceYearly = theme_get('pricing.plan2_price_yearly', '\$49');
\$plan2Period = theme_get('pricing.plan2_period', '/month');
\$plan2Desc = theme_get('pricing.plan2_desc', 'Best for growing teams.');
\$plan2Features = theme_get('pricing.plan2_features', 'Unlimited projects|100 GB storage|Advanced analytics|Priority support|Team tools');
\$plan2BtnText = theme_get('pricing.plan2_btn_text', 'Get Started');
\$plan2BtnLink = theme_get('pricing.plan2_btn_link', '/contact');
\$plan2Featured = theme_get('pricing.plan2_featured', '1');

\$plan3Name = theme_get('pricing.plan3_name', 'Enterprise');
\$plan3Price = theme_get('pricing.plan3_price', '\$119');
\$plan3PriceYearly = theme_get('pricing.plan3_price_yearly', '\$99');
\$plan3Period = theme_get('pricing.plan3_period', '/month');
\$plan3Desc = theme_get('pricing.plan3_desc', 'For large organizations.');
\$plan3Features = theme_get('pricing.plan3_features', 'Everything in Pro|Unlimited storage|Custom integrations|Dedicated manager|SLA guarantee');
\$plan3BtnText = theme_get('pricing.plan3_btn_text', 'Contact Sales');
\$plan3BtnLink = theme_get('pricing.plan3_btn_link', '/contact');
\$plan3Featured = theme_get('pricing.plan3_featured', '');
?>
<section class="{$p}-pricing {$p}-pricing--comparison-toggle" id="pricing">
  <div class="container">
    <div class="{$p}-pricing-header" data-animate="fade-up">
      <?php if (\$pricingBadge): ?><span class="{$p}-pricing-badge" data-ts="pricing.badge"><?= esc(\$pricingBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-pricing-title" data-ts="pricing.title"><?= esc(\$pricingTitle) ?></h2>
      <p class="{$p}-pricing-subtitle" data-ts="pricing.subtitle"><?= esc(\$pricingSubtitle) ?></p>
      <div class="{$p}-pricing-toggle">
        <span class="{$p}-pricing-toggle-label {$p}-pricing-toggle-label--active" data-ts="pricing.toggle_label1"><?= esc(\$toggleLabel1) ?></span>
        <button class="{$p}-pricing-toggle-switch" type="button" role="switch" aria-checked="false" aria-label="Toggle billing period">
          <span class="{$p}-pricing-toggle-knob"></span>
        </button>
        <span class="{$p}-pricing-toggle-label" data-ts="pricing.toggle_label2"><?= esc(\$toggleLabel2) ?></span>
        <span class="{$p}-pricing-toggle-save">Save 20%</span>
      </div>
    </div>
    <div class="{$p}-pricing-grid" data-animate="fade-up">
      <?php foreach ([
        ['name'=>\$plan1Name,'price'=>\$plan1Price,'priceY'=>\$plan1PriceYearly,'period'=>\$plan1Period,'desc'=>\$plan1Desc,'features'=>\$plan1Features,'btn'=>\$plan1BtnText,'link'=>\$plan1BtnLink,'featured'=>\$plan1Featured,'n'=>'1'],
        ['name'=>\$plan2Name,'price'=>\$plan2Price,'priceY'=>\$plan2PriceYearly,'period'=>\$plan2Period,'desc'=>\$plan2Desc,'features'=>\$plan2Features,'btn'=>\$plan2BtnText,'link'=>\$plan2BtnLink,'featured'=>\$plan2Featured,'n'=>'2'],
        ['name'=>\$plan3Name,'price'=>\$plan3Price,'priceY'=>\$plan3PriceYearly,'period'=>\$plan3Period,'desc'=>\$plan3Desc,'features'=>\$plan3Features,'btn'=>\$plan3BtnText,'link'=>\$plan3BtnLink,'featured'=>\$plan3Featured,'n'=>'3'],
      ] as \$plan): ?>
      <div class="{$p}-pricing-plan<?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? ' {$p}-pricing-plan--featured' : '' ?>">
        <?php if (\$plan['featured'] === '1' || \$plan['featured'] === 'true'): ?><div class="{$p}-pricing-plan-badge">Most Popular</div><?php endif; ?>
        <div class="{$p}-pricing-plan-header">
          <h3 class="{$p}-pricing-plan-name" data-ts="pricing.plan<?= \$plan['n'] ?>_name"><?= esc(\$plan['name']) ?></h3>
          <p class="{$p}-pricing-plan-desc" data-ts="pricing.plan<?= \$plan['n'] ?>_desc"><?= esc(\$plan['desc']) ?></p>
        </div>
        <div class="{$p}-pricing-plan-price">
          <span class="{$p}-pricing-amount {$p}-pricing-amount--monthly" data-ts="pricing.plan<?= \$plan['n'] ?>_price"><?= esc(\$plan['price']) ?></span>
          <span class="{$p}-pricing-amount {$p}-pricing-amount--yearly" style="display:none;" data-ts="pricing.plan<?= \$plan['n'] ?>_price_yearly"><?= esc(\$plan['priceY']) ?></span>
          <span class="{$p}-pricing-period" data-ts="pricing.plan<?= \$plan['n'] ?>_period"><?= esc(\$plan['period']) ?></span>
        </div>
        <ul class="{$p}-pricing-features">
          <?php foreach (explode('|', \$plan['features']) as \$feat): ?>
          <li><i class="fas fa-check"></i> <?= esc(trim(\$feat)) ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?= esc(\$plan['link']) ?>" class="{$p}-btn <?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? '{$p}-btn-primary' : '{$p}-btn-outline-pricing' ?>" data-ts="pricing.plan<?= \$plan['n'] ?>_btn_text" data-ts-href="pricing.plan<?= \$plan['n'] ?>_btn_link"><?= esc(\$plan['btn']) ?></a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <script>
  (function(){
    var section = document.querySelector('.{$p}-pricing--comparison-toggle');
    if (!section) return;
    var toggle = section.querySelector('.{$p}-pricing-toggle-switch');
    var labels = section.querySelectorAll('.{$p}-pricing-toggle-label');
    var monthly = section.querySelectorAll('.{$p}-pricing-amount--monthly');
    var yearly = section.querySelectorAll('.{$p}-pricing-amount--yearly');
    if (!toggle) return;
    toggle.addEventListener('click', function(){
      var isYearly = toggle.getAttribute('aria-checked') === 'true';
      toggle.setAttribute('aria-checked', !isYearly);
      toggle.classList.toggle('{$p}-pricing-toggle-switch--active');
      labels[0].classList.toggle('{$p}-pricing-toggle-label--active', isYearly);
      labels[1].classList.toggle('{$p}-pricing-toggle-label--active', !isYearly);
      monthly.forEach(function(el){ el.style.display = !isYearly ? 'none' : ''; });
      yearly.forEach(function(el){ el.style.display = !isYearly ? '' : 'none'; });
    });
  })();
  </script>
</section>
HTML,

// ── Creative-Slider: Horizontal scrollable pricing cards ──
'creative-slider' => <<<HTML
<?php
\$pricingBadge = theme_get('pricing.badge', 'Pricing');
\$pricingTitle = theme_get('pricing.title', 'Simple, Transparent Pricing');
\$pricingSubtitle = theme_get('pricing.subtitle', 'Choose the plan that works best for you and your team.');

\$plan1Name = theme_get('pricing.plan1_name', 'Starter');
\$plan1Price = theme_get('pricing.plan1_price', '\$29');
\$plan1Period = theme_get('pricing.plan1_period', '/month');
\$plan1Desc = theme_get('pricing.plan1_desc', 'Perfect for individuals.');
\$plan1Features = theme_get('pricing.plan1_features', 'Up to 5 projects|10 GB storage|Basic analytics|Email support');
\$plan1BtnText = theme_get('pricing.plan1_btn_text', 'Get Started');
\$plan1BtnLink = theme_get('pricing.plan1_btn_link', '/contact');
\$plan1Featured = theme_get('pricing.plan1_featured', '');

\$plan2Name = theme_get('pricing.plan2_name', 'Professional');
\$plan2Price = theme_get('pricing.plan2_price', '\$79');
\$plan2Period = theme_get('pricing.plan2_period', '/month');
\$plan2Desc = theme_get('pricing.plan2_desc', 'Best for growing teams.');
\$plan2Features = theme_get('pricing.plan2_features', 'Unlimited projects|100 GB storage|Advanced analytics|Priority support|Team collaboration');
\$plan2BtnText = theme_get('pricing.plan2_btn_text', 'Get Started');
\$plan2BtnLink = theme_get('pricing.plan2_btn_link', '/contact');
\$plan2Featured = theme_get('pricing.plan2_featured', '1');

\$plan3Name = theme_get('pricing.plan3_name', 'Enterprise');
\$plan3Price = theme_get('pricing.plan3_price', '\$199');
\$plan3Period = theme_get('pricing.plan3_period', '/month');
\$plan3Desc = theme_get('pricing.plan3_desc', 'For large organizations.');
\$plan3Features = theme_get('pricing.plan3_features', 'Everything in Pro|Unlimited storage|Custom integrations|Dedicated manager|SLA guarantee|SSO & audit logs');
\$plan3BtnText = theme_get('pricing.plan3_btn_text', 'Contact Sales');
\$plan3BtnLink = theme_get('pricing.plan3_btn_link', '/contact');
\$plan3Featured = theme_get('pricing.plan3_featured', '');
?>
<section class="{$p}-pricing {$p}-pricing--creative-slider" id="pricing">
  <div class="container">
    <div class="{$p}-pricing-header" data-animate="fade-up">
      <?php if (\$pricingBadge): ?><span class="{$p}-pricing-badge" data-ts="pricing.badge"><?= esc(\$pricingBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-pricing-title" data-ts="pricing.title"><?= esc(\$pricingTitle) ?></h2>
      <p class="{$p}-pricing-subtitle" data-ts="pricing.subtitle"><?= esc(\$pricingSubtitle) ?></p>
    </div>
  </div>
  <div class="{$p}-pricing-slider" data-animate="fade-up">
    <div class="{$p}-pricing-slider-track">
      <?php foreach ([
        ['name'=>\$plan1Name,'price'=>\$plan1Price,'period'=>\$plan1Period,'desc'=>\$plan1Desc,'features'=>\$plan1Features,'btn'=>\$plan1BtnText,'link'=>\$plan1BtnLink,'featured'=>\$plan1Featured,'n'=>'1'],
        ['name'=>\$plan2Name,'price'=>\$plan2Price,'period'=>\$plan2Period,'desc'=>\$plan2Desc,'features'=>\$plan2Features,'btn'=>\$plan2BtnText,'link'=>\$plan2BtnLink,'featured'=>\$plan2Featured,'n'=>'2'],
        ['name'=>\$plan3Name,'price'=>\$plan3Price,'period'=>\$plan3Period,'desc'=>\$plan3Desc,'features'=>\$plan3Features,'btn'=>\$plan3BtnText,'link'=>\$plan3BtnLink,'featured'=>\$plan3Featured,'n'=>'3'],
      ] as \$plan): ?>
      <div class="{$p}-pricing-slide<?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? ' {$p}-pricing-slide--featured' : '' ?>">
        <div class="{$p}-pricing-plan<?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? ' {$p}-pricing-plan--featured' : '' ?>">
          <?php if (\$plan['featured'] === '1' || \$plan['featured'] === 'true'): ?><div class="{$p}-pricing-plan-badge">Recommended</div><?php endif; ?>
          <div class="{$p}-pricing-plan-header">
            <h3 class="{$p}-pricing-plan-name" data-ts="pricing.plan<?= \$plan['n'] ?>_name"><?= esc(\$plan['name']) ?></h3>
            <p class="{$p}-pricing-plan-desc" data-ts="pricing.plan<?= \$plan['n'] ?>_desc"><?= esc(\$plan['desc']) ?></p>
          </div>
          <div class="{$p}-pricing-plan-price">
            <span class="{$p}-pricing-amount" data-ts="pricing.plan<?= \$plan['n'] ?>_price"><?= esc(\$plan['price']) ?></span>
            <span class="{$p}-pricing-period" data-ts="pricing.plan<?= \$plan['n'] ?>_period"><?= esc(\$plan['period']) ?></span>
          </div>
          <ul class="{$p}-pricing-features">
            <?php foreach (explode('|', \$plan['features']) as \$feat): ?>
            <li><i class="fas fa-check"></i> <?= esc(trim(\$feat)) ?></li>
            <?php endforeach; ?>
          </ul>
          <a href="<?= esc(\$plan['link']) ?>" class="{$p}-btn <?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? '{$p}-btn-primary' : '{$p}-btn-outline-pricing' ?>" data-ts="pricing.plan<?= \$plan['n'] ?>_btn_text" data-ts-href="pricing.plan<?= \$plan['n'] ?>_btn_link"><?= esc(\$plan['btn']) ?></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Creative-Minimal: Just price, plan name, CTA — no feature list ──
'creative-minimal' => <<<HTML
<?php
\$pricingBadge = theme_get('pricing.badge', 'Pricing');
\$pricingTitle = theme_get('pricing.title', 'Simple, Transparent Pricing');
\$pricingSubtitle = theme_get('pricing.subtitle', 'Choose the plan that works best for you and your team.');

\$plan1Name = theme_get('pricing.plan1_name', 'Basic');
\$plan1Price = theme_get('pricing.plan1_price', '\$29');
\$plan1Period = theme_get('pricing.plan1_period', '/month');
\$plan1Desc = theme_get('pricing.plan1_desc', 'Everything you need to start.');
\$plan1BtnText = theme_get('pricing.plan1_btn_text', 'Choose Basic');
\$plan1BtnLink = theme_get('pricing.plan1_btn_link', '/contact');
\$plan1Featured = theme_get('pricing.plan1_featured', '');

\$plan2Name = theme_get('pricing.plan2_name', 'Pro');
\$plan2Price = theme_get('pricing.plan2_price', '\$79');
\$plan2Period = theme_get('pricing.plan2_period', '/month');
\$plan2Desc = theme_get('pricing.plan2_desc', 'For professionals who need more.');
\$plan2BtnText = theme_get('pricing.plan2_btn_text', 'Choose Pro');
\$plan2BtnLink = theme_get('pricing.plan2_btn_link', '/contact');
\$plan2Featured = theme_get('pricing.plan2_featured', '1');

\$plan3Name = theme_get('pricing.plan3_name', 'Enterprise');
\$plan3Price = theme_get('pricing.plan3_price', '\$199');
\$plan3Period = theme_get('pricing.plan3_period', '/month');
\$plan3Desc = theme_get('pricing.plan3_desc', 'Custom solutions at scale.');
\$plan3BtnText = theme_get('pricing.plan3_btn_text', 'Contact Us');
\$plan3BtnLink = theme_get('pricing.plan3_btn_link', '/contact');
\$plan3Featured = theme_get('pricing.plan3_featured', '');
?>
<section class="{$p}-pricing {$p}-pricing--creative-minimal" id="pricing">
  <div class="container">
    <div class="{$p}-pricing-header" data-animate="fade-up">
      <?php if (\$pricingBadge): ?><span class="{$p}-pricing-badge" data-ts="pricing.badge"><?= esc(\$pricingBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-pricing-title" data-ts="pricing.title"><?= esc(\$pricingTitle) ?></h2>
      <p class="{$p}-pricing-subtitle" data-ts="pricing.subtitle"><?= esc(\$pricingSubtitle) ?></p>
    </div>
    <div class="{$p}-pricing-grid" data-animate="fade-up">
      <?php foreach ([
        ['name'=>\$plan1Name,'price'=>\$plan1Price,'period'=>\$plan1Period,'desc'=>\$plan1Desc,'btn'=>\$plan1BtnText,'link'=>\$plan1BtnLink,'featured'=>\$plan1Featured,'n'=>'1'],
        ['name'=>\$plan2Name,'price'=>\$plan2Price,'period'=>\$plan2Period,'desc'=>\$plan2Desc,'btn'=>\$plan2BtnText,'link'=>\$plan2BtnLink,'featured'=>\$plan2Featured,'n'=>'2'],
        ['name'=>\$plan3Name,'price'=>\$plan3Price,'period'=>\$plan3Period,'desc'=>\$plan3Desc,'btn'=>\$plan3BtnText,'link'=>\$plan3BtnLink,'featured'=>\$plan3Featured,'n'=>'3'],
      ] as \$plan): ?>
      <div class="{$p}-pricing-plan-minimal<?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? ' {$p}-pricing-plan-minimal--featured' : '' ?>">
        <h3 class="{$p}-pricing-plan-name" data-ts="pricing.plan<?= \$plan['n'] ?>_name"><?= esc(\$plan['name']) ?></h3>
        <div class="{$p}-pricing-plan-price">
          <span class="{$p}-pricing-amount" data-ts="pricing.plan<?= \$plan['n'] ?>_price"><?= esc(\$plan['price']) ?></span>
          <span class="{$p}-pricing-period" data-ts="pricing.plan<?= \$plan['n'] ?>_period"><?= esc(\$plan['period']) ?></span>
        </div>
        <p class="{$p}-pricing-plan-desc" data-ts="pricing.plan<?= \$plan['n'] ?>_desc"><?= esc(\$plan['desc']) ?></p>
        <a href="<?= esc(\$plan['link']) ?>" class="{$p}-btn <?= (\$plan['featured'] === '1' || \$plan['featured'] === 'true') ? '{$p}-btn-primary' : '{$p}-btn-outline-pricing' ?>" data-ts="pricing.plan<?= \$plan['n'] ?>_btn_text" data-ts-href="pricing.plan<?= \$plan['n'] ?>_btn_link"><?= esc(\$plan['btn']) ?></a>
      </div>
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
        $base = ["{$p}-pricing", "{$p}-pricing-header", "{$p}-pricing-badge",
                 "{$p}-pricing-title", "{$p}-pricing-subtitle",
                 "{$p}-pricing-grid", "{$p}-pricing-plan",
                 "{$p}-pricing-plan--featured", "{$p}-pricing-plan-header",
                 "{$p}-pricing-plan-name", "{$p}-pricing-plan-desc",
                 "{$p}-pricing-plan-price", "{$p}-pricing-amount",
                 "{$p}-pricing-period", "{$p}-pricing-features",
                 "{$p}-btn", "{$p}-btn-primary", "{$p}-btn-outline-pricing"];

        $extra = match($patternId) {
            'cards-elevated', 'cards-gradient', 'comparison-toggle', 'creative-slider' =>
                ["{$p}-pricing-plan-badge"],
            'cards-horizontal' =>
                ["{$p}-pricing-rows", "{$p}-pricing-row", "{$p}-pricing-row--featured",
                 "{$p}-pricing-row-info", "{$p}-pricing-row-features", "{$p}-pricing-row-action"],
            'comparison-table' =>
                ["{$p}-pricing-table-wrap", "{$p}-pricing-table",
                 "{$p}-pricing-table-feature-col", "{$p}-pricing-table-check",
                 "{$p}-pricing-table-col--featured", "{$p}-pricing-table-plan",
                 "{$p}-pricing-mobile-cards"],
            'comparison-toggle' =>
                ["{$p}-pricing-toggle", "{$p}-pricing-toggle-switch",
                 "{$p}-pricing-toggle-knob", "{$p}-pricing-toggle-label",
                 "{$p}-pricing-toggle-save",
                 "{$p}-pricing-amount--monthly", "{$p}-pricing-amount--yearly"],
            'creative-slider' =>
                ["{$p}-pricing-slider", "{$p}-pricing-slider-track", "{$p}-pricing-slide",
                 "{$p}-pricing-slide--featured"],
            'creative-minimal' =>
                ["{$p}-pricing-plan-minimal", "{$p}-pricing-plan-minimal--featured"],
            default => [],
        };

        return array_merge($base, $extra);
    }

    private static function buildStructuralCSS(string $cssType, string $p): string
    {
        $css = self::css_base($p);

        $typeCSS = match($cssType) {
            'columns-3'         => self::css_columns_3($p),
            'columns-2'         => self::css_columns_2($p),
            'columns-4'         => self::css_columns_4($p),
            'cards-elevated'    => self::css_cards_elevated($p),
            'cards-gradient'    => self::css_cards_gradient($p),
            'cards-horizontal'  => self::css_cards_horizontal($p),
            'comparison-table'  => self::css_comparison_table($p),
            'comparison-toggle' => self::css_comparison_toggle($p),
            'creative-slider'   => self::css_creative_slider($p),
            'creative-minimal'  => self::css_creative_minimal($p),
            default             => self::css_columns_3($p),
        };

        return $css . $typeCSS . self::css_responsive($p);
    }

    // --- Base (shared by all pricing patterns) ---
    private static function css_base(string $p): string
    {
        return <<<CSS

/* ═══ Pricing Structural CSS (auto-generated — do not edit) ═══ */
.{$p}-pricing {
  position: relative;
  padding: clamp(80px, 12vh, 140px) 0;
}
.{$p}-pricing-header {
  text-align: center;
  max-width: 700px;
  margin: 0 auto clamp(40px, 6vh, 72px);
}
.{$p}-pricing-badge {
  display: inline-block;
  font-size: 0.8125rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.12em;
  padding: 6px 16px; border-radius: 100px;
  margin-bottom: 16px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.15);
  color: var(--primary, #3b82f6);
  border: 1px solid rgba(var(--primary-rgb, 42,125,225), 0.25);
}
.{$p}-pricing-title {
  font-family: var(--font-heading, inherit);
  font-size: clamp(2rem, 4vw, 3rem);
  font-weight: 700; line-height: 1.15;
  margin: 0 0 16px 0;
  color: var(--heading, var(--text, #1e293b));
}
.{$p}-pricing-subtitle {
  font-size: clamp(1rem, 1.5vw, 1.125rem);
  line-height: 1.7;
  color: var(--text-muted, #64748b);
  margin: 0;
}
.{$p}-pricing-plan-name {
  font-family: var(--font-heading, inherit);
  font-size: 1.25rem; font-weight: 700;
  color: var(--heading, var(--text, #1e293b));
  margin: 0 0 8px 0;
}
.{$p}-pricing-plan-desc {
  font-size: 0.875rem; line-height: 1.6;
  color: var(--text-muted, #64748b);
  margin: 0;
}
.{$p}-pricing-plan-price {
  display: flex; align-items: baseline; gap: 2px;
  margin: 20px 0;
}
.{$p}-pricing-amount {
  font-family: var(--font-heading, inherit);
  font-size: clamp(2rem, 4vw, 3.5rem);
  font-weight: 800; line-height: 1;
  color: var(--primary, #3b82f6);
}
.{$p}-pricing-period {
  font-size: 0.9375rem;
  color: var(--text-muted, #64748b);
  font-weight: 500;
}
.{$p}-pricing-features {
  list-style: none; padding: 0; margin: 0 0 24px;
}
.{$p}-pricing-features li {
  display: flex; align-items: center; gap: 10px;
  padding: 8px 0; font-size: 0.9375rem;
  color: var(--text, #1e293b);
  border-bottom: 1px solid var(--border, rgba(0,0,0,0.06));
}
.{$p}-pricing-features li:last-child {
  border-bottom: none;
}
.{$p}-pricing-features li i.fa-check {
  color: var(--success, #10b981);
  font-size: 0.75rem; flex-shrink: 0;
}
.{$p}-pricing-features li i.fa-times {
  color: var(--text-muted, #94a3b8);
  font-size: 0.75rem; flex-shrink: 0;
}
.{$p}-pricing .{$p}-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  padding: 12px 28px; border-radius: 6px;
  font-weight: 600; font-size: 0.9375rem;
  text-decoration: none; transition: all 0.3s ease;
  cursor: pointer; border: 2px solid transparent;
  width: 100%;
}
.{$p}-pricing .{$p}-btn-primary {
  background: var(--primary, #3b82f6);
  color: var(--primary-contrast, #fff);
  border-color: var(--primary, #3b82f6);
}
.{$p}-pricing .{$p}-btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(var(--primary-rgb, 42,125,225), 0.35);
}
.{$p}-pricing .{$p}-btn-outline-pricing {
  background: transparent;
  color: var(--text, #1e293b);
  border-color: var(--border, #e2e8f0);
}
.{$p}-pricing .{$p}-btn-outline-pricing:hover {
  border-color: var(--primary, #3b82f6);
  color: var(--primary, #3b82f6);
  background: rgba(var(--primary-rgb, 42,125,225), 0.05);
}
.{$p}-pricing-plan-badge {
  display: inline-block;
  font-size: 0.75rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.08em;
  padding: 4px 12px; border-radius: 100px;
  background: var(--primary, #3b82f6);
  color: var(--primary-contrast, #fff);
  margin-bottom: 16px;
}

CSS;
    }

    // --- Columns-3 ---
    private static function css_columns_3(string $p): string
    {
        return <<<CSS
.{$p}-pricing--columns-3 .{$p}-pricing-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 24px; align-items: stretch;
}
.{$p}-pricing--columns-3 .{$p}-pricing-plan {
  background: var(--surface, #fff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: var(--radius, 12px);
  padding: 32px; display: flex; flex-direction: column;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-pricing--columns-3 .{$p}-pricing-plan:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.08);
}
.{$p}-pricing--columns-3 .{$p}-pricing-plan--featured {
  border-color: var(--primary, #3b82f6);
  border-width: 2px;
  transform: scale(1.03);
  box-shadow: 0 16px 48px rgba(var(--primary-rgb, 42,125,225), 0.15);
  z-index: 1;
}
.{$p}-pricing--columns-3 .{$p}-pricing-plan--featured:hover {
  transform: scale(1.03) translateY(-4px);
}
.{$p}-pricing--columns-3 .{$p}-pricing-features {
  flex: 1;
}

CSS;
    }

    // --- Columns-2 ---
    private static function css_columns_2(string $p): string
    {
        return <<<CSS
.{$p}-pricing--columns-2 .{$p}-pricing-grid {
  display: grid; grid-template-columns: repeat(2, 1fr);
  gap: 32px; align-items: stretch;
  max-width: 800px; margin: 0 auto;
}
.{$p}-pricing--columns-2 .{$p}-pricing-plan {
  background: var(--surface, #fff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: var(--radius, 12px);
  padding: 36px; display: flex; flex-direction: column;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-pricing--columns-2 .{$p}-pricing-plan:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.08);
}
.{$p}-pricing--columns-2 .{$p}-pricing-plan--featured {
  border-color: var(--primary, #3b82f6);
  border-width: 2px;
  box-shadow: 0 16px 48px rgba(var(--primary-rgb, 42,125,225), 0.15);
}
.{$p}-pricing--columns-2 .{$p}-pricing-features {
  flex: 1;
}

CSS;
    }

    // --- Columns-4 ---
    private static function css_columns_4(string $p): string
    {
        return <<<CSS
.{$p}-pricing--columns-4 .{$p}-pricing-grid {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 20px; align-items: stretch;
}
.{$p}-pricing--columns-4 .{$p}-pricing-plan {
  background: var(--surface, #fff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: var(--radius, 12px);
  padding: 28px 24px; display: flex; flex-direction: column;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-pricing--columns-4 .{$p}-pricing-plan:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.08);
}
.{$p}-pricing--columns-4 .{$p}-pricing-plan--featured {
  border-color: var(--primary, #3b82f6);
  border-width: 2px;
  transform: scale(1.02);
  box-shadow: 0 16px 48px rgba(var(--primary-rgb, 42,125,225), 0.12);
  z-index: 1;
}
.{$p}-pricing--columns-4 .{$p}-pricing-plan--featured:hover {
  transform: scale(1.02) translateY(-4px);
}
.{$p}-pricing--columns-4 .{$p}-pricing-plan-name {
  font-size: 1.1rem;
}
.{$p}-pricing--columns-4 .{$p}-pricing-amount {
  font-size: clamp(1.75rem, 3vw, 2.5rem);
}
.{$p}-pricing--columns-4 .{$p}-pricing-features {
  flex: 1;
}

CSS;
    }

    // --- Cards Elevated ---
    private static function css_cards_elevated(string $p): string
    {
        return <<<CSS
.{$p}-pricing--cards-elevated .{$p}-pricing-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 24px; align-items: end;
}
.{$p}-pricing--cards-elevated .{$p}-pricing-plan {
  background: var(--surface, #fff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: var(--radius, 16px);
  padding: 36px 32px; display: flex; flex-direction: column;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-pricing--cards-elevated .{$p}-pricing-plan:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 48px rgba(0,0,0,0.1);
}
.{$p}-pricing--cards-elevated .{$p}-pricing-plan--featured {
  border-color: var(--primary, #3b82f6);
  border-top: 4px solid var(--primary, #3b82f6);
  box-shadow: 0 20px 60px rgba(var(--primary-rgb, 42,125,225), 0.18);
  transform: translateY(-16px);
  padding: 40px 32px 36px;
  z-index: 1;
}
.{$p}-pricing--cards-elevated .{$p}-pricing-plan--featured:hover {
  transform: translateY(-22px);
}
.{$p}-pricing--cards-elevated .{$p}-pricing-features {
  flex: 1;
}

CSS;
    }

    // --- Cards Gradient ---
    private static function css_cards_gradient(string $p): string
    {
        return <<<CSS
.{$p}-pricing--cards-gradient .{$p}-pricing-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 24px; align-items: stretch;
}
.{$p}-pricing--cards-gradient .{$p}-pricing-plan {
  background: var(--surface, #fff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: var(--radius, 16px);
  padding: 36px 32px; display: flex; flex-direction: column;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-pricing--cards-gradient .{$p}-pricing-plan:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.08);
}
.{$p}-pricing--cards-gradient .{$p}-pricing-plan--featured {
  background: linear-gradient(135deg, var(--primary, #3b82f6) 0%, var(--secondary, #8b5cf6) 100%);
  border-color: transparent;
  box-shadow: 0 20px 60px rgba(var(--primary-rgb, 42,125,225), 0.25);
  transform: scale(1.03);
}
.{$p}-pricing--cards-gradient .{$p}-pricing-plan--featured:hover {
  transform: scale(1.03) translateY(-4px);
}
.{$p}-pricing--cards-gradient .{$p}-pricing-plan--featured .{$p}-pricing-plan-name,
.{$p}-pricing--cards-gradient .{$p}-pricing-plan--featured .{$p}-pricing-amount,
.{$p}-pricing--cards-gradient .{$p}-pricing-plan--featured .{$p}-pricing-features li {
  color: #fff;
}
.{$p}-pricing--cards-gradient .{$p}-pricing-plan--featured .{$p}-pricing-plan-desc,
.{$p}-pricing--cards-gradient .{$p}-pricing-plan--featured .{$p}-pricing-period {
  color: rgba(255,255,255,0.8);
}
.{$p}-pricing--cards-gradient .{$p}-pricing-plan--featured .{$p}-pricing-features li {
  border-bottom-color: rgba(255,255,255,0.15);
}
.{$p}-pricing--cards-gradient .{$p}-pricing-plan--featured .{$p}-pricing-features li i.fa-check {
  color: rgba(255,255,255,0.9);
}
.{$p}-pricing--cards-gradient .{$p}-pricing-plan--featured .{$p}-btn-primary {
  background: #fff; color: var(--primary, #3b82f6);
  border-color: #fff;
}
.{$p}-pricing--cards-gradient .{$p}-pricing-plan--featured .{$p}-pricing-plan-badge {
  background: rgba(255,255,255,0.2); color: #fff;
}
.{$p}-pricing--cards-gradient .{$p}-pricing-features {
  flex: 1;
}

CSS;
    }

    // --- Cards Horizontal ---
    private static function css_cards_horizontal(string $p): string
    {
        return <<<CSS
.{$p}-pricing--cards-horizontal .{$p}-pricing-rows {
  display: flex; flex-direction: column; gap: 16px;
  max-width: 960px; margin: 0 auto;
}
.{$p}-pricing--cards-horizontal .{$p}-pricing-row {
  display: grid; grid-template-columns: 1fr 2fr auto;
  gap: 32px; align-items: center;
  background: var(--surface, #fff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: var(--radius, 12px);
  padding: 28px 32px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-pricing--cards-horizontal .{$p}-pricing-row:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(0,0,0,0.06);
}
.{$p}-pricing--cards-horizontal .{$p}-pricing-row--featured {
  border-color: var(--primary, #3b82f6);
  border-width: 2px;
  box-shadow: 0 12px 40px rgba(var(--primary-rgb, 42,125,225), 0.12);
}
.{$p}-pricing--cards-horizontal .{$p}-pricing-row-features .{$p}-pricing-features {
  display: flex; flex-wrap: wrap; gap: 4px 20px;
  margin: 0;
}
.{$p}-pricing--cards-horizontal .{$p}-pricing-features li {
  border-bottom: none; padding: 4px 0;
  font-size: 0.875rem;
}
.{$p}-pricing--cards-horizontal .{$p}-pricing-row-action {
  text-align: center; min-width: 160px;
}
.{$p}-pricing--cards-horizontal .{$p}-pricing-row-action .{$p}-pricing-plan-price {
  justify-content: center; margin-bottom: 12px;
}

CSS;
    }

    // --- Comparison Table ---
    private static function css_comparison_table(string $p): string
    {
        return <<<CSS
.{$p}-pricing--comparison-table .{$p}-pricing-table-wrap {
  overflow-x: auto;
}
.{$p}-pricing--comparison-table .{$p}-pricing-table {
  width: 100%; border-collapse: collapse;
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
.{$p}-pricing--comparison-table .{$p}-pricing-table th,
.{$p}-pricing--comparison-table .{$p}-pricing-table td {
  padding: 16px 20px; text-align: center;
  border-bottom: 1px solid var(--border, #e2e8f0);
}
.{$p}-pricing--comparison-table .{$p}-pricing-table th:first-child,
.{$p}-pricing--comparison-table .{$p}-pricing-table td:first-child {
  text-align: left; font-weight: 500;
  color: var(--text, #1e293b);
}
.{$p}-pricing--comparison-table .{$p}-pricing-table thead th {
  background: var(--surface, #f8fafc);
  padding: 24px 20px; vertical-align: bottom;
  border-bottom: 2px solid var(--border, #e2e8f0);
}
.{$p}-pricing--comparison-table .{$p}-pricing-table-plan {
  display: flex; flex-direction: column; gap: 4px; align-items: center;
}
.{$p}-pricing--comparison-table .{$p}-pricing-table-plan .{$p}-pricing-plan-name {
  font-size: 1rem; margin: 0;
}
.{$p}-pricing--comparison-table .{$p}-pricing-table-plan .{$p}-pricing-amount {
  font-size: 1.75rem;
}
.{$p}-pricing--comparison-table .{$p}-pricing-table-plan .{$p}-pricing-period {
  font-size: 0.8125rem;
}
.{$p}-pricing--comparison-table .{$p}-pricing-table-col--featured {
  background: rgba(var(--primary-rgb, 42,125,225), 0.04);
}
.{$p}-pricing--comparison-table .{$p}-pricing-table-check i.fa-check {
  color: var(--success, #10b981); font-size: 1rem;
}
.{$p}-pricing--comparison-table .{$p}-pricing-table-check i.fa-times {
  color: var(--text-muted, #cbd5e1); font-size: 1rem;
}
.{$p}-pricing--comparison-table .{$p}-pricing-table tfoot td {
  padding: 20px; border-bottom: none;
}
.{$p}-pricing--comparison-table .{$p}-pricing-table tfoot .{$p}-btn {
  width: auto; padding: 10px 24px;
}
.{$p}-pricing--comparison-table .{$p}-pricing-mobile-cards {
  display: none;
}
.{$p}-pricing--comparison-table .{$p}-pricing-mobile-cards .{$p}-pricing-plan {
  background: var(--surface, #fff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: var(--radius, 12px);
  padding: 32px; margin-bottom: 16px;
  display: flex; flex-direction: column;
}
.{$p}-pricing--comparison-table .{$p}-pricing-mobile-cards .{$p}-pricing-plan--featured {
  border-color: var(--primary, #3b82f6);
  border-width: 2px;
}
.{$p}-pricing--comparison-table .{$p}-pricing-mobile-cards .{$p}-pricing-features {
  flex: 1;
}

CSS;
    }

    // --- Comparison Toggle ---
    private static function css_comparison_toggle(string $p): string
    {
        return <<<CSS
.{$p}-pricing--comparison-toggle .{$p}-pricing-toggle {
  display: flex; align-items: center; justify-content: center;
  gap: 12px; margin-top: 24px;
}
.{$p}-pricing--comparison-toggle .{$p}-pricing-toggle-label {
  font-size: 0.9375rem; font-weight: 500;
  color: var(--text-muted, #64748b);
  transition: color 0.3s ease;
}
.{$p}-pricing--comparison-toggle .{$p}-pricing-toggle-label--active {
  color: var(--text, #1e293b); font-weight: 600;
}
.{$p}-pricing--comparison-toggle .{$p}-pricing-toggle-switch {
  position: relative; width: 52px; height: 28px;
  background: var(--border, #e2e8f0);
  border-radius: 100px; border: none; cursor: pointer;
  transition: background 0.3s ease;
  padding: 0;
}
.{$p}-pricing--comparison-toggle .{$p}-pricing-toggle-switch--active {
  background: var(--primary, #3b82f6);
}
.{$p}-pricing--comparison-toggle .{$p}-pricing-toggle-knob {
  position: absolute; top: 3px; left: 3px;
  width: 22px; height: 22px;
  background: #fff; border-radius: 50%;
  transition: transform 0.3s ease;
  box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}
.{$p}-pricing--comparison-toggle .{$p}-pricing-toggle-switch--active .{$p}-pricing-toggle-knob {
  transform: translateX(24px);
}
.{$p}-pricing--comparison-toggle .{$p}-pricing-toggle-save {
  font-size: 0.75rem; font-weight: 600;
  color: var(--success, #10b981);
  background: rgba(16,185,129,0.1);
  padding: 2px 10px; border-radius: 100px;
}
.{$p}-pricing--comparison-toggle .{$p}-pricing-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 24px; align-items: stretch;
}
.{$p}-pricing--comparison-toggle .{$p}-pricing-plan {
  background: var(--surface, #fff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: var(--radius, 12px);
  padding: 32px; display: flex; flex-direction: column;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-pricing--comparison-toggle .{$p}-pricing-plan:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.08);
}
.{$p}-pricing--comparison-toggle .{$p}-pricing-plan--featured {
  border-color: var(--primary, #3b82f6);
  border-width: 2px;
  transform: scale(1.03);
  box-shadow: 0 16px 48px rgba(var(--primary-rgb, 42,125,225), 0.15);
  z-index: 1;
}
.{$p}-pricing--comparison-toggle .{$p}-pricing-plan--featured:hover {
  transform: scale(1.03) translateY(-4px);
}
.{$p}-pricing--comparison-toggle .{$p}-pricing-features {
  flex: 1;
}

CSS;
    }

    // --- Creative Slider ---
    private static function css_creative_slider(string $p): string
    {
        return <<<CSS
.{$p}-pricing--creative-slider .{$p}-pricing-slider {
  overflow-x: auto; overflow-y: visible;
  padding: 20px 0 40px;
  scrollbar-width: thin;
  scrollbar-color: var(--primary, #3b82f6) transparent;
  -webkit-overflow-scrolling: touch;
}
.{$p}-pricing--creative-slider .{$p}-pricing-slider::-webkit-scrollbar {
  height: 6px;
}
.{$p}-pricing--creative-slider .{$p}-pricing-slider::-webkit-scrollbar-track {
  background: transparent;
}
.{$p}-pricing--creative-slider .{$p}-pricing-slider::-webkit-scrollbar-thumb {
  background: var(--primary, #3b82f6); border-radius: 3px;
}
.{$p}-pricing--creative-slider .{$p}-pricing-slider-track {
  display: flex; gap: 24px;
  padding: 0 max(24px, calc((100vw - 1200px) / 2));
  min-width: min-content;
}
.{$p}-pricing--creative-slider .{$p}-pricing-slide {
  flex: 0 0 340px;
}
.{$p}-pricing--creative-slider .{$p}-pricing-plan {
  background: var(--surface, #fff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: var(--radius, 16px);
  padding: 36px 32px; display: flex; flex-direction: column;
  height: 100%;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-pricing--creative-slider .{$p}-pricing-plan:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 48px rgba(0,0,0,0.1);
}
.{$p}-pricing--creative-slider .{$p}-pricing-plan--featured {
  border-color: var(--primary, #3b82f6);
  border-width: 2px;
  box-shadow: 0 16px 48px rgba(var(--primary-rgb, 42,125,225), 0.15);
}
.{$p}-pricing--creative-slider .{$p}-pricing-features {
  flex: 1;
}

CSS;
    }

    // --- Creative Minimal ---
    private static function css_creative_minimal(string $p): string
    {
        return <<<CSS
.{$p}-pricing--creative-minimal .{$p}-pricing-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 1px;
  background: var(--border, #e2e8f0);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: var(--radius, 12px);
  overflow: hidden;
}
.{$p}-pricing--creative-minimal .{$p}-pricing-plan-minimal {
  background: var(--surface, #fff);
  padding: 48px 36px; text-align: center;
  display: flex; flex-direction: column; align-items: center;
  transition: background 0.3s ease;
}
.{$p}-pricing--creative-minimal .{$p}-pricing-plan-minimal:hover {
  background: var(--surface-hover, #f8fafc);
}
.{$p}-pricing--creative-minimal .{$p}-pricing-plan-minimal--featured {
  background: rgba(var(--primary-rgb, 42,125,225), 0.04);
}
.{$p}-pricing--creative-minimal .{$p}-pricing-plan-minimal--featured:hover {
  background: rgba(var(--primary-rgb, 42,125,225), 0.08);
}
.{$p}-pricing--creative-minimal .{$p}-pricing-plan-minimal .{$p}-pricing-plan-name {
  font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.1em;
  color: var(--text-muted, #64748b);
  font-weight: 600; margin-bottom: 16px;
}
.{$p}-pricing--creative-minimal .{$p}-pricing-plan-minimal .{$p}-pricing-plan-price {
  justify-content: center; margin: 0 0 12px;
}
.{$p}-pricing--creative-minimal .{$p}-pricing-plan-minimal .{$p}-pricing-amount {
  font-size: clamp(2.5rem, 5vw, 4rem);
}
.{$p}-pricing--creative-minimal .{$p}-pricing-plan-minimal .{$p}-pricing-plan-desc {
  margin-bottom: 24px; max-width: 30ch;
}
.{$p}-pricing--creative-minimal .{$p}-pricing-plan-minimal .{$p}-btn {
  width: auto; padding: 10px 28px;
}
.{$p}-pricing--creative-minimal .{$p}-pricing-plan-minimal--featured .{$p}-pricing-plan-name {
  color: var(--primary, #3b82f6);
}

CSS;
    }

    // --- Responsive ---
    private static function css_responsive(string $p): string
    {
        return <<<CSS
@media (max-width: 1024px) {
  .{$p}-pricing--columns-4 .{$p}-pricing-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 768px) {
  .{$p}-pricing--columns-3 .{$p}-pricing-grid,
  .{$p}-pricing--columns-2 .{$p}-pricing-grid,
  .{$p}-pricing--columns-4 .{$p}-pricing-grid,
  .{$p}-pricing--cards-elevated .{$p}-pricing-grid,
  .{$p}-pricing--cards-gradient .{$p}-pricing-grid,
  .{$p}-pricing--comparison-toggle .{$p}-pricing-grid,
  .{$p}-pricing--creative-minimal .{$p}-pricing-grid {
    grid-template-columns: 1fr !important;
    max-width: 420px; margin-left: auto; margin-right: auto;
  }
  .{$p}-pricing--columns-3 .{$p}-pricing-plan--featured,
  .{$p}-pricing--columns-4 .{$p}-pricing-plan--featured,
  .{$p}-pricing--cards-gradient .{$p}-pricing-plan--featured,
  .{$p}-pricing--comparison-toggle .{$p}-pricing-plan--featured {
    transform: none;
  }
  .{$p}-pricing--cards-elevated .{$p}-pricing-plan--featured {
    transform: none;
  }
  .{$p}-pricing--cards-horizontal .{$p}-pricing-row {
    grid-template-columns: 1fr;
    gap: 16px; text-align: center;
  }
  .{$p}-pricing--cards-horizontal .{$p}-pricing-row-features .{$p}-pricing-features {
    justify-content: center;
  }
  .{$p}-pricing--cards-horizontal .{$p}-pricing-row-action {
    min-width: auto;
  }
  .{$p}-pricing--comparison-table .{$p}-pricing-table-wrap {
    display: none;
  }
  .{$p}-pricing--comparison-table .{$p}-pricing-mobile-cards {
    display: block;
  }
  .{$p}-pricing--creative-slider .{$p}-pricing-slide {
    flex: 0 0 300px;
  }
  .{$p}-pricing--creative-minimal .{$p}-pricing-grid {
    gap: 0;
  }
}

CSS;
    }
}
