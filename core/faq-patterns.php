<?php
/**
 * FAQ Section Pattern Registry
 * 
 * Pre-built FAQ HTML layouts with structural CSS.
 * AI generates only decorative CSS (colors, fonts, effects) — never the structure.
 * 
 * 8 patterns across 3 groups.
 * @since 2026-02-19
 */

class FAQPatternRegistry
{
    // ═══════════════════════════════════════
    // PATTERN DEFINITIONS
    // ═══════════════════════════════════════

    private static array $patterns = [
        // --- Accordion (classic expand/collapse) ---
        ['id'=>'accordion-simple',     'group'=>'accordion',  'css_type'=>'accordion-simple',
         'best_for'=>['restaurant','bakery','cafe','bar','hotel','resort','spa','wedding','florist','winery',
                      'brewery','fine-dining','catering','fitness','sports','salon','barbershop','pet-care',
                      'cleaning','moving','photography','music','entertainment','event-planning']],
        ['id'=>'accordion-boxed',      'group'=>'accordion',  'css_type'=>'accordion-boxed',
         'best_for'=>['agency','consulting','marketing','seo','web-design',
                      'creative-agency','design','branding']],
        ['id'=>'accordion-numbered',   'group'=>'accordion',  'css_type'=>'accordion-numbered',
         'best_for'=>['education','university','school','library','coaching','nonprofit','charity',
                      'real-estate','construction','landscaping','architecture','interior-design']],

        // --- Columns (grid / multi-column layouts) ---
        ['id'=>'columns-2col',         'group'=>'columns',    'css_type'=>'columns-2col',
         'best_for'=>['healthcare','clinic','hospital','dental','pharmacy','insurance',
                      'manufacturing','logistics','engineering','paving','roofing']],
        ['id'=>'columns-categories',   'group'=>'columns',    'css_type'=>'columns-categories',
         'best_for'=>['legal','financial','bank','accounting','government','compliance',
                      'medical','veterinary','laboratory']],
        ['id'=>'columns-split',        'group'=>'columns',    'css_type'=>'columns-split',
         'best_for'=>['travel','tourism','adventure','outdoor','country-club','luxury',
                      'gallery','museum','fashion','art']],

        // --- Creative (unique interactive layouts) ---
        ['id'=>'creative-search',      'group'=>'creative',   'css_type'=>'creative-search',
         'best_for'=>['saas','tech','startup','app','digital','fintech','ai','blockchain','platform',
                      'hosting','cloud','devtools','analytics']],
        ['id'=>'creative-tabs',        'group'=>'creative',   'css_type'=>'creative-tabs',
         'best_for'=>['ecommerce','marketplace','subscription','membership','gaming','nightclub',
                      'festival','concert','film','podcast','influencer','content-creator','youtube',
                      'magazine','blog','news','media','social-media','plumbing','electrical','hvac']],
    ];

    // ═══════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════

    /**
     * Pick the best FAQ pattern for an industry.
     */
    public static function pickPattern(string $industry): string
    {
        $industry = strtolower(trim($industry));
        foreach (self::$patterns as $p) {
            if (in_array($industry, $p['best_for'], true)) {
                return $p['id'];
            }
        }
        // Fallback: accordion-simple (most versatile)
        return 'accordion-simple';
    }

    /**
     * Render a FAQ pattern.
     * Returns ['html'=>..., 'structural_css'=>..., 'pattern_id'=>..., 'classes'=>[...], 'fields'=>[...]]
     */
    public static function render(string $patternId, string $prefix, array $brief): array
    {
        $def = null;
        foreach (self::$patterns as $p) {
            if ($p['id'] === $patternId) { $def = $p; break; }
        }
        if (!$def) {
            $def = self::$patterns[0]; // fallback to accordion-simple
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
        // Common fields all FAQ patterns have
        $common = [
            'badge'    => ['type' => 'text',     'label' => 'Badge / Label'],
            'title'    => ['type' => 'text',     'label' => 'Section Title'],
            'subtitle' => ['type' => 'textarea', 'label' => 'Section Subtitle'],
            'q1'       => ['type' => 'text',     'label' => 'Question 1'],
            'a1'       => ['type' => 'textarea', 'label' => 'Answer 1'],
            'q2'       => ['type' => 'text',     'label' => 'Question 2'],
            'a2'       => ['type' => 'textarea', 'label' => 'Answer 2'],
            'q3'       => ['type' => 'text',     'label' => 'Question 3'],
            'a3'       => ['type' => 'textarea', 'label' => 'Answer 3'],
            'q4'       => ['type' => 'text',     'label' => 'Question 4'],
            'a4'       => ['type' => 'textarea', 'label' => 'Answer 4'],
            'q5'       => ['type' => 'text',     'label' => 'Question 5'],
            'a5'       => ['type' => 'textarea', 'label' => 'Answer 5'],
            'q6'       => ['type' => 'text',     'label' => 'Question 6'],
            'a6'       => ['type' => 'textarea', 'label' => 'Answer 6'],
        ];

        // Pattern-specific extras
        $extras = match($patternId) {
            'columns-split' => [
                'btn_text' => ['type' => 'text', 'label' => 'CTA Button Text'],
                'btn_link' => ['type' => 'text', 'label' => 'CTA Button Link'],
            ],
            'columns-categories' => [
                'cat1' => ['type' => 'text', 'label' => 'Category 1 Name'],
                'cat2' => ['type' => 'text', 'label' => 'Category 2 Name'],
                'cat3' => ['type' => 'text', 'label' => 'Category 3 Name'],
            ],
            'creative-tabs' => [
                'tab1' => ['type' => 'text', 'label' => 'Tab 1 Name'],
                'tab2' => ['type' => 'text', 'label' => 'Tab 2 Name'],
                'tab3' => ['type' => 'text', 'label' => 'Tab 3 Name'],
            ],
            'creative-search' => [
                'search_placeholder' => ['type' => 'text', 'label' => 'Search Placeholder'],
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
            'accordion-simple' => <<<'GUIDE'
Thin border-bottom dividers (1px) between items in muted/subtle color.
Chevron/plus icon rotates smoothly on open (transform rotate 0.3s ease).
Answer text fades in with opacity transition when details opens.
Question text uses heading weight (600-700), answer uses normal weight muted color.
First item gets thin border-top for complete framing of the list.
GUIDE,
            'accordion-boxed' => <<<'GUIDE'
Each FAQ item boxed with border, border-radius, and subtle surface background.
Open/active item: border-color transitions to primary accent, bg gets faint primary tint.
Active item has subtle box-shadow (0 4px 15px rgba primary 0.1) to pop forward.
Icon rotates on open state with smooth 0.3s transition.
Closed items have muted border, hover: border-color lightens toward primary.
GUIDE,
            'accordion-numbered' => <<<'GUIDE'
Number badge uses primary color, circular shape or bold inline number.
Numbers at 60% opacity when closed, 100% opacity when open for active emphasis.
Sequential numbers in heading font, large size (1.25rem), primary colored.
Answer text indented past the number column for clean alignment.
Thin border-bottom dividers between items, clean and structured feel.
GUIDE,
            'columns-2col' => <<<'GUIDE'
No accordion behavior — all questions and answers always visible.
Question text uses bold weight (600-700), answer text normal weight muted.
Cards/items have subtle border or light surface background for grouping.
Clean balanced distribution — no decorative excess, information-first.
Optional thin top-border on each card for subtle definition.
GUIDE,
            'columns-categories' => <<<'GUIDE'
Category buttons/tabs in sidebar use pill or rectangular shape with primary active state.
Active category: primary bg, white text; inactive: transparent with muted text and border.
Section dividers between category groups, clean horizontal rules.
Category badge styling: small, uppercase, letter-spaced for label emphasis.
Transition between category panels uses fade opacity 0.3s ease.
GUIDE,
            'columns-split' => <<<'GUIDE'
Left intro column has larger title, subtitle in muted color, and primary CTA button.
Right accordion column has standard FAQ styling with thin dividers.
Question font-weight bold (600-700), answer text muted color, normal weight.
CTA button uses primary bg with standard hover lift and shadow.
Overall feel: editorial split — marketing left, utility right.
GUIDE,
            'creative-search' => <<<'GUIDE'
Search input has generous padding, large border-radius, subtle border.
Focus state: input border transitions to primary, box-shadow glow (0 0 0 3px rgba primary 0.2).
Search icon inside input uses muted color, transitions to primary on focus.
Filtered/matched results stay visible, non-matching items fade out (opacity 0, height 0).
Highlight matched text in results using primary bg tint or bold weight.
GUIDE,
            'creative-tabs' => <<<'GUIDE'
Tab buttons use pill shape (border-radius 100px) or underline-style active indicator.
Active tab: primary bg with white text (pill) or primary bottom-border (underline).
Inactive tabs: transparent bg, muted text, hover transitions toward primary tint.
Tab panel content switches with fade opacity transition 0.3s ease.
Overall feel: organized, app-like categorization with clear active state.
GUIDE,
            default => <<<'GUIDE'
Question text bold weight, answer text normal weight muted color.
Expand/collapse icon rotates smoothly on toggle.
Thin border dividers between FAQ items.
Clean typography with proper hierarchy and spacing.
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

        // Inject business name into title if available
        if ($name) {
            $replacements["theme_get('faq.title', 'Frequently Asked Questions')"] =
                "theme_get('faq.title', 'Frequently Asked Questions')";
        }

        foreach ($replacements as $search => $replace) {
            $html = str_replace($search, $replace, $html);
        }

        return $html;
    }

    private static function buildHTML(string $patternId, string $p): string
    {
        $templates = self::getTemplates($p);
        return $templates[$patternId] ?? $templates['accordion-simple'];
    }

    private static function getTemplates(string $p): array
    {
        return [

// ── Accordion Simple: Clean full-width accordion ──
'accordion-simple' => <<<HTML
<?php
\$faqBadge = theme_get('faq.badge', '');
\$faqTitle = theme_get('faq.title', 'Frequently Asked Questions');
\$faqSubtitle = theme_get('faq.subtitle', 'Find answers to the most common questions about our services.');
\$q1 = theme_get('faq.q1', 'What services do you offer?');
\$a1 = theme_get('faq.a1', '<p>We offer a comprehensive range of services tailored to meet your needs. From initial consultation to final delivery, our team ensures quality at every step.</p>');
\$q2 = theme_get('faq.q2', 'How do I get started?');
\$a2 = theme_get('faq.a2', '<p>Getting started is easy. Simply reach out to us through our contact form or give us a call. We\\'ll schedule a free consultation to discuss your requirements.</p>');
\$q3 = theme_get('faq.q3', 'What are your pricing options?');
\$a3 = theme_get('faq.a3', '<p>We offer flexible pricing plans to suit different budgets and project scopes. Contact us for a detailed quote tailored to your specific needs.</p>');
\$q4 = theme_get('faq.q4', 'Do you offer support after delivery?');
\$a4 = theme_get('faq.a4', '<p>Absolutely. We provide ongoing support and maintenance to ensure everything continues running smoothly after project completion.</p>');
\$q5 = theme_get('faq.q5', 'What is your typical turnaround time?');
\$a5 = theme_get('faq.a5', '<p>Turnaround times vary depending on project complexity. Most projects are completed within 2-4 weeks. We\\'ll provide a detailed timeline during consultation.</p>');
\$q6 = theme_get('faq.q6', 'Can I request custom solutions?');
\$a6 = theme_get('faq.a6', '<p>Yes! We specialize in custom solutions. Every project is unique, and we work closely with you to deliver exactly what you need.</p>');
?>
<section class="{$p}-faq {$p}-faq--simple" id="faq">
  <div class="container">
    <div class="{$p}-faq-header" data-animate="fade-up">
      <?php if (\$faqBadge): ?><span class="{$p}-faq-badge" data-ts="faq.badge"><?= esc(\$faqBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-faq-title" data-ts="faq.title"><?= esc(\$faqTitle) ?></h2>
      <?php if (\$faqSubtitle): ?><p class="{$p}-faq-subtitle" data-ts="faq.subtitle"><?= esc(\$faqSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="{$p}-faq-list" data-animate="fade-up">
      <?php if (\$q1): ?>
      <details class="{$p}-faq-item">
        <summary class="{$p}-faq-question" data-ts="faq.q1"><?= esc(\$q1) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a1"><?= \$a1 ?></div>
      </details>
      <?php endif; ?>
      <?php if (\$q2): ?>
      <details class="{$p}-faq-item">
        <summary class="{$p}-faq-question" data-ts="faq.q2"><?= esc(\$q2) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a2"><?= \$a2 ?></div>
      </details>
      <?php endif; ?>
      <?php if (\$q3): ?>
      <details class="{$p}-faq-item">
        <summary class="{$p}-faq-question" data-ts="faq.q3"><?= esc(\$q3) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a3"><?= \$a3 ?></div>
      </details>
      <?php endif; ?>
      <?php if (\$q4): ?>
      <details class="{$p}-faq-item">
        <summary class="{$p}-faq-question" data-ts="faq.q4"><?= esc(\$q4) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a4"><?= \$a4 ?></div>
      </details>
      <?php endif; ?>
      <?php if (\$q5): ?>
      <details class="{$p}-faq-item">
        <summary class="{$p}-faq-question" data-ts="faq.q5"><?= esc(\$q5) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a5"><?= \$a5 ?></div>
      </details>
      <?php endif; ?>
      <?php if (\$q6): ?>
      <details class="{$p}-faq-item">
        <summary class="{$p}-faq-question" data-ts="faq.q6"><?= esc(\$q6) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a6"><?= \$a6 ?></div>
      </details>
      <?php endif; ?>
    </div>
  </div>
</section>
HTML,

// ── Accordion Boxed: Each FAQ in a bordered card ──
'accordion-boxed' => <<<HTML
<?php
\$faqBadge = theme_get('faq.badge', '');
\$faqTitle = theme_get('faq.title', 'Frequently Asked Questions');
\$faqSubtitle = theme_get('faq.subtitle', 'Everything you need to know about our platform and services.');
\$q1 = theme_get('faq.q1', 'What services do you offer?');
\$a1 = theme_get('faq.a1', '<p>We offer a comprehensive range of services tailored to meet your needs. From initial consultation to final delivery, our team ensures quality at every step.</p>');
\$q2 = theme_get('faq.q2', 'How do I get started?');
\$a2 = theme_get('faq.a2', '<p>Getting started is easy. Simply reach out to us through our contact form or give us a call. We\\'ll schedule a free consultation to discuss your requirements.</p>');
\$q3 = theme_get('faq.q3', 'What are your pricing options?');
\$a3 = theme_get('faq.a3', '<p>We offer flexible pricing plans to suit different budgets and project scopes. Contact us for a detailed quote tailored to your specific needs.</p>');
\$q4 = theme_get('faq.q4', 'Do you offer support after delivery?');
\$a4 = theme_get('faq.a4', '<p>Absolutely. We provide ongoing support and maintenance to ensure everything continues running smoothly after project completion.</p>');
\$q5 = theme_get('faq.q5', 'What is your typical turnaround time?');
\$a5 = theme_get('faq.a5', '<p>Turnaround times vary depending on project complexity. Most projects are completed within 2-4 weeks. We\\'ll provide a detailed timeline during consultation.</p>');
\$q6 = theme_get('faq.q6', 'Can I request custom solutions?');
\$a6 = theme_get('faq.a6', '<p>Yes! We specialize in custom solutions. Every project is unique, and we work closely with you to deliver exactly what you need.</p>');
?>
<section class="{$p}-faq {$p}-faq--boxed" id="faq">
  <div class="container">
    <div class="{$p}-faq-header" data-animate="fade-up">
      <?php if (\$faqBadge): ?><span class="{$p}-faq-badge" data-ts="faq.badge"><?= esc(\$faqBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-faq-title" data-ts="faq.title"><?= esc(\$faqTitle) ?></h2>
      <?php if (\$faqSubtitle): ?><p class="{$p}-faq-subtitle" data-ts="faq.subtitle"><?= esc(\$faqSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="{$p}-faq-list" data-animate="fade-up">
      <?php if (\$q1): ?>
      <details class="{$p}-faq-item">
        <summary class="{$p}-faq-question" data-ts="faq.q1"><?= esc(\$q1) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a1"><?= \$a1 ?></div>
      </details>
      <?php endif; ?>
      <?php if (\$q2): ?>
      <details class="{$p}-faq-item">
        <summary class="{$p}-faq-question" data-ts="faq.q2"><?= esc(\$q2) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a2"><?= \$a2 ?></div>
      </details>
      <?php endif; ?>
      <?php if (\$q3): ?>
      <details class="{$p}-faq-item">
        <summary class="{$p}-faq-question" data-ts="faq.q3"><?= esc(\$q3) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a3"><?= \$a3 ?></div>
      </details>
      <?php endif; ?>
      <?php if (\$q4): ?>
      <details class="{$p}-faq-item">
        <summary class="{$p}-faq-question" data-ts="faq.q4"><?= esc(\$q4) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a4"><?= \$a4 ?></div>
      </details>
      <?php endif; ?>
      <?php if (\$q5): ?>
      <details class="{$p}-faq-item">
        <summary class="{$p}-faq-question" data-ts="faq.q5"><?= esc(\$q5) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a5"><?= \$a5 ?></div>
      </details>
      <?php endif; ?>
      <?php if (\$q6): ?>
      <details class="{$p}-faq-item">
        <summary class="{$p}-faq-question" data-ts="faq.q6"><?= esc(\$q6) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a6"><?= \$a6 ?></div>
      </details>
      <?php endif; ?>
    </div>
  </div>
</section>
HTML,

// ── Accordion Numbered: Numbered items with expand/collapse ──
'accordion-numbered' => <<<HTML
<?php
\$faqBadge = theme_get('faq.badge', '');
\$faqTitle = theme_get('faq.title', 'Frequently Asked Questions');
\$faqSubtitle = theme_get('faq.subtitle', 'Here are answers to the questions we get asked most often.');
\$q1 = theme_get('faq.q1', 'What services do you offer?');
\$a1 = theme_get('faq.a1', '<p>We offer a comprehensive range of services tailored to meet your needs. From initial consultation to final delivery, our team ensures quality at every step.</p>');
\$q2 = theme_get('faq.q2', 'How do I get started?');
\$a2 = theme_get('faq.a2', '<p>Getting started is easy. Simply reach out to us through our contact form or give us a call. We\\'ll schedule a free consultation to discuss your requirements.</p>');
\$q3 = theme_get('faq.q3', 'What are your pricing options?');
\$a3 = theme_get('faq.a3', '<p>We offer flexible pricing plans to suit different budgets and project scopes. Contact us for a detailed quote tailored to your specific needs.</p>');
\$q4 = theme_get('faq.q4', 'Do you offer support after delivery?');
\$a4 = theme_get('faq.a4', '<p>Absolutely. We provide ongoing support and maintenance to ensure everything continues running smoothly after project completion.</p>');
\$q5 = theme_get('faq.q5', 'What is your typical turnaround time?');
\$a5 = theme_get('faq.a5', '<p>Turnaround times vary depending on project complexity. Most projects are completed within 2-4 weeks. We\\'ll provide a detailed timeline during consultation.</p>');
\$q6 = theme_get('faq.q6', 'Can I request custom solutions?');
\$a6 = theme_get('faq.a6', '<p>Yes! We specialize in custom solutions. Every project is unique, and we work closely with you to deliver exactly what you need.</p>');
\$items = [
    ['q' => \$q1, 'a' => \$a1, 'qk' => 'faq.q1', 'ak' => 'faq.a1'],
    ['q' => \$q2, 'a' => \$a2, 'qk' => 'faq.q2', 'ak' => 'faq.a2'],
    ['q' => \$q3, 'a' => \$a3, 'qk' => 'faq.q3', 'ak' => 'faq.a3'],
    ['q' => \$q4, 'a' => \$a4, 'qk' => 'faq.q4', 'ak' => 'faq.a4'],
    ['q' => \$q5, 'a' => \$a5, 'qk' => 'faq.q5', 'ak' => 'faq.a5'],
    ['q' => \$q6, 'a' => \$a6, 'qk' => 'faq.q6', 'ak' => 'faq.a6'],
];
?>
<section class="{$p}-faq {$p}-faq--numbered" id="faq">
  <div class="container">
    <div class="{$p}-faq-header" data-animate="fade-up">
      <?php if (\$faqBadge): ?><span class="{$p}-faq-badge" data-ts="faq.badge"><?= esc(\$faqBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-faq-title" data-ts="faq.title"><?= esc(\$faqTitle) ?></h2>
      <?php if (\$faqSubtitle): ?><p class="{$p}-faq-subtitle" data-ts="faq.subtitle"><?= esc(\$faqSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="{$p}-faq-list" data-animate="fade-up">
      <?php \$num = 0; foreach (\$items as \$item): if (!\$item['q']) continue; \$num++; ?>
      <details class="{$p}-faq-item">
        <summary class="{$p}-faq-question" data-ts="<?= \$item['qk'] ?>">
          <span class="{$p}-faq-number"><?= str_pad(\$num, 2, '0', STR_PAD_LEFT) ?></span>
          <?= esc(\$item['q']) ?>
          <span class="{$p}-faq-icon"></span>
        </summary>
        <div class="{$p}-faq-answer" data-ts="<?= \$item['ak'] ?>"><?= \$item['a'] ?></div>
      </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Columns 2-Col: Two-column grid, all expanded ──
'columns-2col' => <<<HTML
<?php
\$faqBadge = theme_get('faq.badge', '');
\$faqTitle = theme_get('faq.title', 'Frequently Asked Questions');
\$faqSubtitle = theme_get('faq.subtitle', 'Quick answers to help you understand our services better.');
\$q1 = theme_get('faq.q1', 'What services do you offer?');
\$a1 = theme_get('faq.a1', '<p>We offer a comprehensive range of services tailored to meet your needs. From initial consultation to final delivery, our team ensures quality at every step.</p>');
\$q2 = theme_get('faq.q2', 'How do I get started?');
\$a2 = theme_get('faq.a2', '<p>Getting started is easy. Simply reach out to us through our contact form or give us a call. We\\'ll schedule a free consultation to discuss your requirements.</p>');
\$q3 = theme_get('faq.q3', 'What are your pricing options?');
\$a3 = theme_get('faq.a3', '<p>We offer flexible pricing plans to suit different budgets and project scopes. Contact us for a detailed quote tailored to your specific needs.</p>');
\$q4 = theme_get('faq.q4', 'Do you offer support after delivery?');
\$a4 = theme_get('faq.a4', '<p>Absolutely. We provide ongoing support and maintenance to ensure everything continues running smoothly after project completion.</p>');
\$q5 = theme_get('faq.q5', 'What is your typical turnaround time?');
\$a5 = theme_get('faq.a5', '<p>Turnaround times vary depending on project complexity. Most projects are completed within 2-4 weeks. We\\'ll provide a detailed timeline during consultation.</p>');
\$q6 = theme_get('faq.q6', 'Can I request custom solutions?');
\$a6 = theme_get('faq.a6', '<p>Yes! We specialize in custom solutions. Every project is unique, and we work closely with you to deliver exactly what you need.</p>');
\$items = [
    ['q' => \$q1, 'a' => \$a1, 'qk' => 'faq.q1', 'ak' => 'faq.a1'],
    ['q' => \$q2, 'a' => \$a2, 'qk' => 'faq.q2', 'ak' => 'faq.a2'],
    ['q' => \$q3, 'a' => \$a3, 'qk' => 'faq.q3', 'ak' => 'faq.a3'],
    ['q' => \$q4, 'a' => \$a4, 'qk' => 'faq.q4', 'ak' => 'faq.a4'],
    ['q' => \$q5, 'a' => \$a5, 'qk' => 'faq.q5', 'ak' => 'faq.a5'],
    ['q' => \$q6, 'a' => \$a6, 'qk' => 'faq.q6', 'ak' => 'faq.a6'],
];
?>
<section class="{$p}-faq {$p}-faq--2col" id="faq">
  <div class="container">
    <div class="{$p}-faq-header" data-animate="fade-up">
      <?php if (\$faqBadge): ?><span class="{$p}-faq-badge" data-ts="faq.badge"><?= esc(\$faqBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-faq-title" data-ts="faq.title"><?= esc(\$faqTitle) ?></h2>
      <?php if (\$faqSubtitle): ?><p class="{$p}-faq-subtitle" data-ts="faq.subtitle"><?= esc(\$faqSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="{$p}-faq-grid" data-animate="fade-up">
      <?php foreach (\$items as \$item): if (!\$item['q']) continue; ?>
      <div class="{$p}-faq-card">
        <h3 class="{$p}-faq-question" data-ts="<?= \$item['qk'] ?>"><?= esc(\$item['q']) ?></h3>
        <div class="{$p}-faq-answer" data-ts="<?= \$item['ak'] ?>"><?= \$item['a'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Columns Categories: Sidebar categories, right side questions ──
'columns-categories' => <<<HTML
<?php
\$faqBadge = theme_get('faq.badge', '');
\$faqTitle = theme_get('faq.title', 'Frequently Asked Questions');
\$faqSubtitle = theme_get('faq.subtitle', 'Browse by category to find the answers you need.');
\$cat1 = theme_get('faq.cat1', 'General');
\$cat2 = theme_get('faq.cat2', 'Services');
\$cat3 = theme_get('faq.cat3', 'Billing');
\$q1 = theme_get('faq.q1', 'What services do you offer?');
\$a1 = theme_get('faq.a1', '<p>We offer a comprehensive range of services tailored to meet your needs. From initial consultation to final delivery, our team ensures quality at every step.</p>');
\$q2 = theme_get('faq.q2', 'How do I get started?');
\$a2 = theme_get('faq.a2', '<p>Getting started is easy. Simply reach out to us through our contact form or give us a call. We\\'ll schedule a free consultation to discuss your requirements.</p>');
\$q3 = theme_get('faq.q3', 'What are your pricing options?');
\$a3 = theme_get('faq.a3', '<p>We offer flexible pricing plans to suit different budgets and project scopes. Contact us for a detailed quote tailored to your specific needs.</p>');
\$q4 = theme_get('faq.q4', 'Do you offer support after delivery?');
\$a4 = theme_get('faq.a4', '<p>Absolutely. We provide ongoing support and maintenance to ensure everything continues running smoothly after project completion.</p>');
\$q5 = theme_get('faq.q5', 'What is your typical turnaround time?');
\$a5 = theme_get('faq.a5', '<p>Turnaround times vary depending on project complexity. Most projects are completed within 2-4 weeks. We\\'ll provide a detailed timeline during consultation.</p>');
\$q6 = theme_get('faq.q6', 'Can I request custom solutions?');
\$a6 = theme_get('faq.a6', '<p>Yes! We specialize in custom solutions. Every project is unique, and we work closely with you to deliver exactly what you need.</p>');
?>
<section class="{$p}-faq {$p}-faq--categories" id="faq">
  <div class="container">
    <div class="{$p}-faq-header" data-animate="fade-up">
      <?php if (\$faqBadge): ?><span class="{$p}-faq-badge" data-ts="faq.badge"><?= esc(\$faqBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-faq-title" data-ts="faq.title"><?= esc(\$faqTitle) ?></h2>
      <?php if (\$faqSubtitle): ?><p class="{$p}-faq-subtitle" data-ts="faq.subtitle"><?= esc(\$faqSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="{$p}-faq-columns" data-animate="fade-up">
      <nav class="{$p}-faq-sidebar">
        <input type="radio" name="{$p}-faq-cat" id="{$p}-faq-cat1" class="{$p}-faq-radio" checked>
        <label for="{$p}-faq-cat1" class="{$p}-faq-cat-btn" data-ts="faq.cat1"><?= esc(\$cat1) ?></label>
        <input type="radio" name="{$p}-faq-cat" id="{$p}-faq-cat2" class="{$p}-faq-radio">
        <label for="{$p}-faq-cat2" class="{$p}-faq-cat-btn" data-ts="faq.cat2"><?= esc(\$cat2) ?></label>
        <input type="radio" name="{$p}-faq-cat" id="{$p}-faq-cat3" class="{$p}-faq-radio">
        <label for="{$p}-faq-cat3" class="{$p}-faq-cat-btn" data-ts="faq.cat3"><?= esc(\$cat3) ?></label>
      </nav>
      <div class="{$p}-faq-panels">
        <div class="{$p}-faq-panel {$p}-faq-panel--1">
          <?php if (\$q1): ?>
          <details class="{$p}-faq-item" open>
            <summary class="{$p}-faq-question" data-ts="faq.q1"><?= esc(\$q1) ?><span class="{$p}-faq-icon"></span></summary>
            <div class="{$p}-faq-answer" data-ts="faq.a1"><?= \$a1 ?></div>
          </details>
          <?php endif; ?>
          <?php if (\$q2): ?>
          <details class="{$p}-faq-item">
            <summary class="{$p}-faq-question" data-ts="faq.q2"><?= esc(\$q2) ?><span class="{$p}-faq-icon"></span></summary>
            <div class="{$p}-faq-answer" data-ts="faq.a2"><?= \$a2 ?></div>
          </details>
          <?php endif; ?>
        </div>
        <div class="{$p}-faq-panel {$p}-faq-panel--2">
          <?php if (\$q3): ?>
          <details class="{$p}-faq-item" open>
            <summary class="{$p}-faq-question" data-ts="faq.q3"><?= esc(\$q3) ?><span class="{$p}-faq-icon"></span></summary>
            <div class="{$p}-faq-answer" data-ts="faq.a3"><?= \$a3 ?></div>
          </details>
          <?php endif; ?>
          <?php if (\$q4): ?>
          <details class="{$p}-faq-item">
            <summary class="{$p}-faq-question" data-ts="faq.q4"><?= esc(\$q4) ?><span class="{$p}-faq-icon"></span></summary>
            <div class="{$p}-faq-answer" data-ts="faq.a4"><?= \$a4 ?></div>
          </details>
          <?php endif; ?>
        </div>
        <div class="{$p}-faq-panel {$p}-faq-panel--3">
          <?php if (\$q5): ?>
          <details class="{$p}-faq-item" open>
            <summary class="{$p}-faq-question" data-ts="faq.q5"><?= esc(\$q5) ?><span class="{$p}-faq-icon"></span></summary>
            <div class="{$p}-faq-answer" data-ts="faq.a5"><?= \$a5 ?></div>
          </details>
          <?php endif; ?>
          <?php if (\$q6): ?>
          <details class="{$p}-faq-item">
            <summary class="{$p}-faq-question" data-ts="faq.q6"><?= esc(\$q6) ?><span class="{$p}-faq-icon"></span></summary>
            <div class="{$p}-faq-answer" data-ts="faq.a6"><?= \$a6 ?></div>
          </details>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Columns Split: Left title+CTA, Right accordion ──
'columns-split' => <<<HTML
<?php
\$faqBadge = theme_get('faq.badge', '');
\$faqTitle = theme_get('faq.title', 'Frequently Asked Questions');
\$faqSubtitle = theme_get('faq.subtitle', 'Can\\'t find what you\\'re looking for? Reach out to our support team for personalized help.');
\$faqBtnText = theme_get('faq.btn_text', 'Contact Support');
\$faqBtnLink = theme_get('faq.btn_link', '/contact');
\$q1 = theme_get('faq.q1', 'What services do you offer?');
\$a1 = theme_get('faq.a1', '<p>We offer a comprehensive range of services tailored to meet your needs. From initial consultation to final delivery, our team ensures quality at every step.</p>');
\$q2 = theme_get('faq.q2', 'How do I get started?');
\$a2 = theme_get('faq.a2', '<p>Getting started is easy. Simply reach out to us through our contact form or give us a call. We\\'ll schedule a free consultation to discuss your requirements.</p>');
\$q3 = theme_get('faq.q3', 'What are your pricing options?');
\$a3 = theme_get('faq.a3', '<p>We offer flexible pricing plans to suit different budgets and project scopes. Contact us for a detailed quote tailored to your specific needs.</p>');
\$q4 = theme_get('faq.q4', 'Do you offer support after delivery?');
\$a4 = theme_get('faq.a4', '<p>Absolutely. We provide ongoing support and maintenance to ensure everything continues running smoothly after project completion.</p>');
\$q5 = theme_get('faq.q5', 'What is your typical turnaround time?');
\$a5 = theme_get('faq.a5', '<p>Turnaround times vary depending on project complexity. Most projects are completed within 2-4 weeks. We\\'ll provide a detailed timeline during consultation.</p>');
\$q6 = theme_get('faq.q6', 'Can I request custom solutions?');
\$a6 = theme_get('faq.a6', '<p>Yes! We specialize in custom solutions. Every project is unique, and we work closely with you to deliver exactly what you need.</p>');
?>
<section class="{$p}-faq {$p}-faq--split" id="faq">
  <div class="container">
    <div class="{$p}-faq-columns" data-animate="fade-up">
      <div class="{$p}-faq-intro">
        <?php if (\$faqBadge): ?><span class="{$p}-faq-badge" data-ts="faq.badge"><?= esc(\$faqBadge) ?></span><?php endif; ?>
        <h2 class="{$p}-faq-title" data-ts="faq.title"><?= esc(\$faqTitle) ?></h2>
        <p class="{$p}-faq-subtitle" data-ts="faq.subtitle"><?= esc(\$faqSubtitle) ?></p>
        <?php if (\$faqBtnText): ?>
        <a href="<?= esc(\$faqBtnLink) ?>" class="{$p}-btn {$p}-btn-primary" data-ts="faq.btn_text" data-ts-href="faq.btn_link"><?= esc(\$faqBtnText) ?></a>
        <?php endif; ?>
      </div>
      <div class="{$p}-faq-list">
        <?php if (\$q1): ?>
        <details class="{$p}-faq-item" open>
          <summary class="{$p}-faq-question" data-ts="faq.q1"><?= esc(\$q1) ?><span class="{$p}-faq-icon"></span></summary>
          <div class="{$p}-faq-answer" data-ts="faq.a1"><?= \$a1 ?></div>
        </details>
        <?php endif; ?>
        <?php if (\$q2): ?>
        <details class="{$p}-faq-item">
          <summary class="{$p}-faq-question" data-ts="faq.q2"><?= esc(\$q2) ?><span class="{$p}-faq-icon"></span></summary>
          <div class="{$p}-faq-answer" data-ts="faq.a2"><?= \$a2 ?></div>
        </details>
        <?php endif; ?>
        <?php if (\$q3): ?>
        <details class="{$p}-faq-item">
          <summary class="{$p}-faq-question" data-ts="faq.q3"><?= esc(\$q3) ?><span class="{$p}-faq-icon"></span></summary>
          <div class="{$p}-faq-answer" data-ts="faq.a3"><?= \$a3 ?></div>
        </details>
        <?php endif; ?>
        <?php if (\$q4): ?>
        <details class="{$p}-faq-item">
          <summary class="{$p}-faq-question" data-ts="faq.q4"><?= esc(\$q4) ?><span class="{$p}-faq-icon"></span></summary>
          <div class="{$p}-faq-answer" data-ts="faq.a4"><?= \$a4 ?></div>
        </details>
        <?php endif; ?>
        <?php if (\$q5): ?>
        <details class="{$p}-faq-item">
          <summary class="{$p}-faq-question" data-ts="faq.q5"><?= esc(\$q5) ?><span class="{$p}-faq-icon"></span></summary>
          <div class="{$p}-faq-answer" data-ts="faq.a5"><?= \$a5 ?></div>
        </details>
        <?php endif; ?>
        <?php if (\$q6): ?>
        <details class="{$p}-faq-item">
          <summary class="{$p}-faq-question" data-ts="faq.q6"><?= esc(\$q6) ?><span class="{$p}-faq-icon"></span></summary>
          <div class="{$p}-faq-answer" data-ts="faq.a6"><?= \$a6 ?></div>
        </details>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Creative Search: Search bar with filterable FAQ ──
'creative-search' => <<<HTML
<?php
\$faqBadge = theme_get('faq.badge', '');
\$faqTitle = theme_get('faq.title', 'How can we help?');
\$faqSubtitle = theme_get('faq.subtitle', 'Search our knowledge base or browse the frequently asked questions below.');
\$faqSearchPlaceholder = theme_get('faq.search_placeholder', 'Type your question...');
\$q1 = theme_get('faq.q1', 'What services do you offer?');
\$a1 = theme_get('faq.a1', '<p>We offer a comprehensive range of services tailored to meet your needs. From initial consultation to final delivery, our team ensures quality at every step.</p>');
\$q2 = theme_get('faq.q2', 'How do I get started?');
\$a2 = theme_get('faq.a2', '<p>Getting started is easy. Simply reach out to us through our contact form or give us a call. We\\'ll schedule a free consultation to discuss your requirements.</p>');
\$q3 = theme_get('faq.q3', 'What are your pricing options?');
\$a3 = theme_get('faq.a3', '<p>We offer flexible pricing plans to suit different budgets and project scopes. Contact us for a detailed quote tailored to your specific needs.</p>');
\$q4 = theme_get('faq.q4', 'Do you offer support after delivery?');
\$a4 = theme_get('faq.a4', '<p>Absolutely. We provide ongoing support and maintenance to ensure everything continues running smoothly after project completion.</p>');
\$q5 = theme_get('faq.q5', 'What is your typical turnaround time?');
\$a5 = theme_get('faq.a5', '<p>Turnaround times vary depending on project complexity. Most projects are completed within 2-4 weeks. We\\'ll provide a detailed timeline during consultation.</p>');
\$q6 = theme_get('faq.q6', 'Can I request custom solutions?');
\$a6 = theme_get('faq.a6', '<p>Yes! We specialize in custom solutions. Every project is unique, and we work closely with you to deliver exactly what you need.</p>');
?>
<section class="{$p}-faq {$p}-faq--search" id="faq">
  <div class="container">
    <div class="{$p}-faq-header" data-animate="fade-up">
      <?php if (\$faqBadge): ?><span class="{$p}-faq-badge" data-ts="faq.badge"><?= esc(\$faqBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-faq-title" data-ts="faq.title"><?= esc(\$faqTitle) ?></h2>
      <?php if (\$faqSubtitle): ?><p class="{$p}-faq-subtitle" data-ts="faq.subtitle"><?= esc(\$faqSubtitle) ?></p><?php endif; ?>
      <div class="{$p}-faq-search-wrap">
        <i class="fas fa-search {$p}-faq-search-icon"></i>
        <input type="text" class="{$p}-faq-search" placeholder="<?= esc(\$faqSearchPlaceholder) ?>" data-ts="faq.search_placeholder" id="{$p}-faq-search-input">
      </div>
    </div>
    <div class="{$p}-faq-list" data-animate="fade-up">
      <?php if (\$q1): ?>
      <details class="{$p}-faq-item" data-search="<?= esc(strtolower(\$q1)) ?>">
        <summary class="{$p}-faq-question" data-ts="faq.q1"><?= esc(\$q1) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a1"><?= \$a1 ?></div>
      </details>
      <?php endif; ?>
      <?php if (\$q2): ?>
      <details class="{$p}-faq-item" data-search="<?= esc(strtolower(\$q2)) ?>">
        <summary class="{$p}-faq-question" data-ts="faq.q2"><?= esc(\$q2) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a2"><?= \$a2 ?></div>
      </details>
      <?php endif; ?>
      <?php if (\$q3): ?>
      <details class="{$p}-faq-item" data-search="<?= esc(strtolower(\$q3)) ?>">
        <summary class="{$p}-faq-question" data-ts="faq.q3"><?= esc(\$q3) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a3"><?= \$a3 ?></div>
      </details>
      <?php endif; ?>
      <?php if (\$q4): ?>
      <details class="{$p}-faq-item" data-search="<?= esc(strtolower(\$q4)) ?>">
        <summary class="{$p}-faq-question" data-ts="faq.q4"><?= esc(\$q4) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a4"><?= \$a4 ?></div>
      </details>
      <?php endif; ?>
      <?php if (\$q5): ?>
      <details class="{$p}-faq-item" data-search="<?= esc(strtolower(\$q5)) ?>">
        <summary class="{$p}-faq-question" data-ts="faq.q5"><?= esc(\$q5) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a5"><?= \$a5 ?></div>
      </details>
      <?php endif; ?>
      <?php if (\$q6): ?>
      <details class="{$p}-faq-item" data-search="<?= esc(strtolower(\$q6)) ?>">
        <summary class="{$p}-faq-question" data-ts="faq.q6"><?= esc(\$q6) ?><span class="{$p}-faq-icon"></span></summary>
        <div class="{$p}-faq-answer" data-ts="faq.a6"><?= \$a6 ?></div>
      </details>
      <?php endif; ?>
    </div>
  </div>
  <script>
  (function(){
    var input = document.getElementById('{$p}-faq-search-input');
    if (!input) return;
    input.addEventListener('input', function() {
      var term = this.value.toLowerCase();
      var items = this.closest('.{$p}-faq--search').querySelectorAll('.{$p}-faq-item');
      items.forEach(function(item) {
        var text = (item.getAttribute('data-search') || '') + ' ' + item.textContent.toLowerCase();
        item.style.display = (!term || text.indexOf(term) !== -1) ? '' : 'none';
      });
    });
  })();
  </script>
</section>
HTML,

// ── Creative Tabs: Tab navigation with FAQ lists per tab ──
'creative-tabs' => <<<HTML
<?php
\$faqBadge = theme_get('faq.badge', '');
\$faqTitle = theme_get('faq.title', 'Frequently Asked Questions');
\$faqSubtitle = theme_get('faq.subtitle', 'Select a category to find the answers you need.');
\$tab1 = theme_get('faq.tab1', 'General');
\$tab2 = theme_get('faq.tab2', 'Billing');
\$tab3 = theme_get('faq.tab3', 'Technical');
\$q1 = theme_get('faq.q1', 'What services do you offer?');
\$a1 = theme_get('faq.a1', '<p>We offer a comprehensive range of services tailored to meet your needs. From initial consultation to final delivery, our team ensures quality at every step.</p>');
\$q2 = theme_get('faq.q2', 'How do I get started?');
\$a2 = theme_get('faq.a2', '<p>Getting started is easy. Simply reach out to us through our contact form or give us a call. We\\'ll schedule a free consultation to discuss your requirements.</p>');
\$q3 = theme_get('faq.q3', 'What are your pricing options?');
\$a3 = theme_get('faq.a3', '<p>We offer flexible pricing plans to suit different budgets and project scopes. Contact us for a detailed quote tailored to your specific needs.</p>');
\$q4 = theme_get('faq.q4', 'Do you offer support after delivery?');
\$a4 = theme_get('faq.a4', '<p>Absolutely. We provide ongoing support and maintenance to ensure everything continues running smoothly after project completion.</p>');
\$q5 = theme_get('faq.q5', 'What is your typical turnaround time?');
\$a5 = theme_get('faq.a5', '<p>Turnaround times vary depending on project complexity. Most projects are completed within 2-4 weeks. We\\'ll provide a detailed timeline during consultation.</p>');
\$q6 = theme_get('faq.q6', 'Can I request custom solutions?');
\$a6 = theme_get('faq.a6', '<p>Yes! We specialize in custom solutions. Every project is unique, and we work closely with you to deliver exactly what you need.</p>');
?>
<section class="{$p}-faq {$p}-faq--tabs" id="faq">
  <div class="container">
    <div class="{$p}-faq-header" data-animate="fade-up">
      <?php if (\$faqBadge): ?><span class="{$p}-faq-badge" data-ts="faq.badge"><?= esc(\$faqBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-faq-title" data-ts="faq.title"><?= esc(\$faqTitle) ?></h2>
      <?php if (\$faqSubtitle): ?><p class="{$p}-faq-subtitle" data-ts="faq.subtitle"><?= esc(\$faqSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="{$p}-faq-tabs-wrap" data-animate="fade-up">
      <nav class="{$p}-faq-tab-nav">
        <input type="radio" name="{$p}-faq-tab" id="{$p}-faq-tab1" class="{$p}-faq-tab-radio" checked>
        <label for="{$p}-faq-tab1" class="{$p}-faq-tab-btn" data-ts="faq.tab1"><?= esc(\$tab1) ?></label>
        <input type="radio" name="{$p}-faq-tab" id="{$p}-faq-tab2" class="{$p}-faq-tab-radio">
        <label for="{$p}-faq-tab2" class="{$p}-faq-tab-btn" data-ts="faq.tab2"><?= esc(\$tab2) ?></label>
        <input type="radio" name="{$p}-faq-tab" id="{$p}-faq-tab3" class="{$p}-faq-tab-radio">
        <label for="{$p}-faq-tab3" class="{$p}-faq-tab-btn" data-ts="faq.tab3"><?= esc(\$tab3) ?></label>
      </nav>
      <div class="{$p}-faq-tab-panels">
        <div class="{$p}-faq-tab-panel {$p}-faq-tab-panel--1">
          <?php if (\$q1): ?>
          <details class="{$p}-faq-item" open>
            <summary class="{$p}-faq-question" data-ts="faq.q1"><?= esc(\$q1) ?><span class="{$p}-faq-icon"></span></summary>
            <div class="{$p}-faq-answer" data-ts="faq.a1"><?= \$a1 ?></div>
          </details>
          <?php endif; ?>
          <?php if (\$q2): ?>
          <details class="{$p}-faq-item">
            <summary class="{$p}-faq-question" data-ts="faq.q2"><?= esc(\$q2) ?><span class="{$p}-faq-icon"></span></summary>
            <div class="{$p}-faq-answer" data-ts="faq.a2"><?= \$a2 ?></div>
          </details>
          <?php endif; ?>
        </div>
        <div class="{$p}-faq-tab-panel {$p}-faq-tab-panel--2">
          <?php if (\$q3): ?>
          <details class="{$p}-faq-item" open>
            <summary class="{$p}-faq-question" data-ts="faq.q3"><?= esc(\$q3) ?><span class="{$p}-faq-icon"></span></summary>
            <div class="{$p}-faq-answer" data-ts="faq.a3"><?= \$a3 ?></div>
          </details>
          <?php endif; ?>
          <?php if (\$q4): ?>
          <details class="{$p}-faq-item">
            <summary class="{$p}-faq-question" data-ts="faq.q4"><?= esc(\$q4) ?><span class="{$p}-faq-icon"></span></summary>
            <div class="{$p}-faq-answer" data-ts="faq.a4"><?= \$a4 ?></div>
          </details>
          <?php endif; ?>
        </div>
        <div class="{$p}-faq-tab-panel {$p}-faq-tab-panel--3">
          <?php if (\$q5): ?>
          <details class="{$p}-faq-item" open>
            <summary class="{$p}-faq-question" data-ts="faq.q5"><?= esc(\$q5) ?><span class="{$p}-faq-icon"></span></summary>
            <div class="{$p}-faq-answer" data-ts="faq.a5"><?= \$a5 ?></div>
          </details>
          <?php endif; ?>
          <?php if (\$q6): ?>
          <details class="{$p}-faq-item">
            <summary class="{$p}-faq-question" data-ts="faq.q6"><?= esc(\$q6) ?><span class="{$p}-faq-icon"></span></summary>
            <div class="{$p}-faq-answer" data-ts="faq.a6"><?= \$a6 ?></div>
          </details>
          <?php endif; ?>
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
        $base = ["{$p}-faq", "{$p}-faq-header", "{$p}-faq-badge", "{$p}-faq-title",
                 "{$p}-faq-subtitle", "{$p}-faq-list", "{$p}-faq-item", "{$p}-faq-question",
                 "{$p}-faq-answer", "{$p}-faq-icon"];

        $extra = match($patternId) {
            'accordion-numbered' => ["{$p}-faq-number"],
            'columns-2col' => ["{$p}-faq-grid", "{$p}-faq-card"],
            'columns-categories' => ["{$p}-faq-columns", "{$p}-faq-sidebar", "{$p}-faq-cat-btn",
                                     "{$p}-faq-panels", "{$p}-faq-panel", "{$p}-faq-radio"],
            'columns-split' => ["{$p}-faq-columns", "{$p}-faq-intro", "{$p}-btn", "{$p}-btn-primary"],
            'creative-search' => ["{$p}-faq-search-wrap", "{$p}-faq-search", "{$p}-faq-search-icon"],
            'creative-tabs' => ["{$p}-faq-tabs-wrap", "{$p}-faq-tab-nav", "{$p}-faq-tab-btn",
                                "{$p}-faq-tab-panels", "{$p}-faq-tab-panel", "{$p}-faq-tab-radio"],
            default => [],
        };

        return array_merge($base, $extra);
    }

    private static function buildStructuralCSS(string $cssType, string $p): string
    {
        $css = self::css_base($p);

        $typeCSS = match($cssType) {
            'accordion-simple'     => self::css_accordion_simple($p),
            'accordion-boxed'      => self::css_accordion_boxed($p),
            'accordion-numbered'   => self::css_accordion_numbered($p),
            'columns-2col'         => self::css_columns_2col($p),
            'columns-categories'   => self::css_columns_categories($p),
            'columns-split'        => self::css_columns_split($p),
            'creative-search'      => self::css_creative_search($p),
            'creative-tabs'        => self::css_creative_tabs($p),
            default                => self::css_accordion_simple($p),
        };

        return $css . $typeCSS . self::css_responsive($p);
    }

    // --- Base (shared by all FAQ patterns) ---
    private static function css_base(string $p): string
    {
        return <<<CSS

/* ═══ FAQ Structural CSS (auto-generated — do not edit) ═══ */
.{$p}-faq {
  position: relative;
  padding: clamp(80px, 12vh, 140px) 0;
}
.{$p}-faq-header {
  text-align: center;
  max-width: 700px;
  margin: 0 auto clamp(40px, 6vh, 64px);
}
.{$p}-faq-badge {
  display: inline-block;
  font-size: 0.8125rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.12em;
  padding: 6px 16px; border-radius: 100px;
  margin-bottom: 16px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.15);
  color: var(--primary, #3b82f6);
  border: 1px solid rgba(var(--primary-rgb, 42,125,225), 0.25);
}
.{$p}-faq-title {
  font-family: var(--font-heading, inherit);
  font-size: clamp(1.75rem, 4vw, 2.75rem);
  font-weight: 700; line-height: 1.2;
  margin: 0 0 16px 0;
  color: var(--text, #fff);
}
.{$p}-faq-subtitle {
  font-size: clamp(0.9375rem, 1.5vw, 1.125rem);
  line-height: 1.7; margin: 0;
  color: var(--text-muted, rgba(255,255,255,0.7));
  max-width: 55ch;
  margin-left: auto; margin-right: auto;
}
.{$p}-faq-list {
  max-width: 800px;
  margin: 0 auto;
}
.{$p}-faq-item {
  border-bottom: 1px solid var(--border, rgba(255,255,255,0.1));
}
.{$p}-faq-item:last-child {
  border-bottom: none;
}
.{$p}-faq-question {
  display: flex; align-items: center; justify-content: space-between;
  gap: 16px;
  padding: 20px 0;
  font-size: clamp(0.9375rem, 1.5vw, 1.0625rem);
  font-weight: 600; line-height: 1.4;
  color: var(--text, #fff);
  cursor: pointer;
  list-style: none;
}
.{$p}-faq-question::-webkit-details-marker { display: none; }
.{$p}-faq-question::marker { display: none; content: ''; }
.{$p}-faq-icon {
  position: relative; flex-shrink: 0;
  width: 24px; height: 24px;
}
.{$p}-faq-icon::before,
.{$p}-faq-icon::after {
  content: '';
  position: absolute;
  top: 50%; left: 50%;
  width: 14px; height: 2px;
  background: var(--text-muted, rgba(255,255,255,0.5));
  transform: translate(-50%, -50%);
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.{$p}-faq-icon::after {
  transform: translate(-50%, -50%) rotate(90deg);
}
.{$p}-faq-item[open] .{$p}-faq-icon::after {
  transform: translate(-50%, -50%) rotate(0deg);
  opacity: 0;
}
.{$p}-faq-answer {
  padding: 0 0 20px 0;
  color: var(--text-muted, rgba(255,255,255,0.7));
  line-height: 1.7;
  font-size: 0.9375rem;
}
.{$p}-faq-answer p {
  margin: 0 0 12px 0;
}
.{$p}-faq-answer p:last-child {
  margin-bottom: 0;
}

CSS;
    }

    // --- Accordion Simple ---
    private static function css_accordion_simple(string $p): string
    {
        return <<<CSS
.{$p}-faq--simple .{$p}-faq-list {
  max-width: 800px;
  margin: 0 auto;
}
.{$p}-faq--simple .{$p}-faq-item:first-child {
  border-top: 1px solid var(--border, rgba(255,255,255,0.1));
}

CSS;
    }

    // --- Accordion Boxed ---
    private static function css_accordion_boxed(string $p): string
    {
        return <<<CSS
.{$p}-faq--boxed .{$p}-faq-list {
  max-width: 800px;
  margin: 0 auto;
  display: flex; flex-direction: column;
  gap: 12px;
}
.{$p}-faq--boxed .{$p}-faq-item {
  border: 1px solid var(--border, rgba(255,255,255,0.1));
  border-radius: var(--radius, 12px);
  background: var(--surface, rgba(255,255,255,0.03));
  padding: 0 24px;
  border-bottom: 1px solid var(--border, rgba(255,255,255,0.1));
  transition: border-color 0.3s ease, background 0.3s ease;
}
.{$p}-faq--boxed .{$p}-faq-item:last-child {
  border-bottom: 1px solid var(--border, rgba(255,255,255,0.1));
}
.{$p}-faq--boxed .{$p}-faq-item[open] {
  border-color: rgba(var(--primary-rgb, 42,125,225), 0.3);
  background: rgba(var(--primary-rgb, 42,125,225), 0.05);
}
.{$p}-faq--boxed .{$p}-faq-question {
  padding: 20px 0;
}
.{$p}-faq--boxed .{$p}-faq-answer {
  padding: 0 0 20px 0;
}

CSS;
    }

    // --- Accordion Numbered ---
    private static function css_accordion_numbered(string $p): string
    {
        return <<<CSS
.{$p}-faq--numbered .{$p}-faq-list {
  max-width: 800px;
  margin: 0 auto;
}
.{$p}-faq--numbered .{$p}-faq-question {
  gap: 20px;
}
.{$p}-faq-number {
  flex-shrink: 0;
  font-family: var(--font-heading, inherit);
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--primary, #3b82f6);
  min-width: 36px;
  opacity: 0.6;
  transition: opacity 0.3s ease;
}
.{$p}-faq--numbered .{$p}-faq-item[open] .{$p}-faq-number {
  opacity: 1;
}
.{$p}-faq--numbered .{$p}-faq-answer {
  padding-left: 56px;
}
.{$p}-faq--numbered .{$p}-faq-item:first-child {
  border-top: 1px solid var(--border, rgba(255,255,255,0.1));
}

CSS;
    }

    // --- Columns 2-Col ---
    private static function css_columns_2col(string $p): string
    {
        return <<<CSS
.{$p}-faq--2col .{$p}-faq-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 32px;
}
.{$p}-faq-card {
  padding: 28px;
  border: 1px solid var(--border, rgba(255,255,255,0.1));
  border-radius: var(--radius, 12px);
  background: var(--surface, rgba(255,255,255,0.03));
  transition: border-color 0.3s ease;
}
.{$p}-faq-card:hover {
  border-color: rgba(var(--primary-rgb, 42,125,225), 0.3);
}
.{$p}-faq-card .{$p}-faq-question {
  padding: 0 0 12px 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--text, #fff);
  cursor: default;
  display: block;
}
.{$p}-faq-card .{$p}-faq-answer {
  padding: 0;
  color: var(--text-muted, rgba(255,255,255,0.7));
  line-height: 1.7;
  font-size: 0.9375rem;
}

CSS;
    }

    // --- Columns Categories ---
    private static function css_columns_categories(string $p): string
    {
        return <<<CSS
.{$p}-faq--categories .{$p}-faq-columns {
  display: grid;
  grid-template-columns: 240px 1fr;
  gap: clamp(32px, 5vw, 64px);
  align-items: start;
}
.{$p}-faq-sidebar {
  position: sticky; top: 100px;
  display: flex; flex-direction: column; gap: 4px;
}
.{$p}-faq-radio {
  position: absolute; opacity: 0; pointer-events: none;
}
.{$p}-faq-cat-btn {
  display: block;
  padding: 12px 20px;
  font-size: 0.9375rem; font-weight: 500;
  color: var(--text-muted, rgba(255,255,255,0.6));
  border-radius: var(--radius, 8px);
  cursor: pointer;
  transition: all 0.3s ease;
  border-left: 3px solid transparent;
}
.{$p}-faq-cat-btn:hover {
  color: var(--text, #fff);
  background: rgba(var(--primary-rgb, 42,125,225), 0.05);
}
.{$p}-faq-radio:checked + .{$p}-faq-cat-btn {
  color: var(--primary, #3b82f6);
  background: rgba(var(--primary-rgb, 42,125,225), 0.1);
  border-left-color: var(--primary, #3b82f6);
  font-weight: 600;
}
/* Panel visibility via CSS sibling selectors */
.{$p}-faq-panels {
  min-height: 200px;
}
.{$p}-faq-panel {
  display: none;
}
.{$p}-faq-panel--1 { display: block; }
.{$p}-faq--categories .{$p}-faq-sidebar:has(#<?= $p ?>-faq-cat1:checked) ~ .{$p}-faq-panels .{$p}-faq-panel { display: none; }
.{$p}-faq--categories .{$p}-faq-sidebar:has(#<?= $p ?>-faq-cat1:checked) ~ .{$p}-faq-panels .{$p}-faq-panel--1 { display: block; }
.{$p}-faq--categories .{$p}-faq-sidebar:has(#<?= $p ?>-faq-cat2:checked) ~ .{$p}-faq-panels .{$p}-faq-panel { display: none; }
.{$p}-faq--categories .{$p}-faq-sidebar:has(#<?= $p ?>-faq-cat2:checked) ~ .{$p}-faq-panels .{$p}-faq-panel--2 { display: block; }
.{$p}-faq--categories .{$p}-faq-sidebar:has(#<?= $p ?>-faq-cat3:checked) ~ .{$p}-faq-panels .{$p}-faq-panel { display: none; }
.{$p}-faq--categories .{$p}-faq-sidebar:has(#<?= $p ?>-faq-cat3:checked) ~ .{$p}-faq-panels .{$p}-faq-panel--3 { display: block; }

CSS;
    }

    // --- Columns Split ---
    private static function css_columns_split(string $p): string
    {
        return <<<CSS
.{$p}-faq--split .{$p}-faq-columns {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: clamp(40px, 6vw, 80px);
  align-items: start;
}
.{$p}-faq-intro {
  position: sticky; top: 100px;
}
.{$p}-faq--split .{$p}-faq-title {
  text-align: left;
  font-size: clamp(1.75rem, 3.5vw, 2.5rem);
}
.{$p}-faq--split .{$p}-faq-subtitle {
  text-align: left;
  margin: 0 0 24px 0;
}
.{$p}-faq--split .{$p}-faq-badge {
  margin-bottom: 16px;
}
.{$p}-faq--split .{$p}-faq-list {
  max-width: none;
  margin: 0;
}
.{$p}-faq--split .{$p}-faq-item:first-child {
  border-top: 1px solid var(--border, rgba(255,255,255,0.1));
}
/* CTA Button */
.{$p}-faq--split .{$p}-btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 14px 32px; border-radius: 6px;
  font-weight: 600; font-size: 0.9375rem;
  text-decoration: none; transition: all 0.3s ease;
  cursor: pointer; border: 2px solid transparent;
}
.{$p}-faq--split .{$p}-btn-primary {
  background: var(--primary, #3b82f6);
  color: var(--primary-contrast, #fff);
  border-color: var(--primary, #3b82f6);
}
.{$p}-faq--split .{$p}-btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(var(--primary-rgb, 42,125,225), 0.35);
}

CSS;
    }

    // --- Creative Search ---
    private static function css_creative_search(string $p): string
    {
        return <<<CSS
.{$p}-faq--search .{$p}-faq-header {
  max-width: 600px;
}
.{$p}-faq-search-wrap {
  position: relative;
  max-width: 500px;
  margin: 28px auto 0;
}
.{$p}-faq-search-icon {
  position: absolute;
  left: 18px; top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted, rgba(255,255,255,0.4));
  font-size: 0.9375rem;
  pointer-events: none;
}
.{$p}-faq-search {
  width: 100%;
  padding: 16px 20px 16px 48px;
  border: 1px solid var(--border, rgba(255,255,255,0.15));
  border-radius: var(--radius, 12px);
  background: var(--surface, rgba(255,255,255,0.05));
  color: var(--text, #fff);
  font-size: 1rem;
  outline: none;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-faq-search::placeholder {
  color: var(--text-muted, rgba(255,255,255,0.4));
}
.{$p}-faq-search:focus {
  border-color: var(--primary, #3b82f6);
  box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 42,125,225), 0.15);
}
.{$p}-faq--search .{$p}-faq-list {
  max-width: 800px;
  margin: 0 auto;
}
.{$p}-faq--search .{$p}-faq-item:first-child {
  border-top: 1px solid var(--border, rgba(255,255,255,0.1));
}

CSS;
    }

    // --- Creative Tabs ---
    private static function css_creative_tabs(string $p): string
    {
        return <<<CSS
.{$p}-faq-tabs-wrap {
  max-width: 800px;
  margin: 0 auto;
}
.{$p}-faq-tab-nav {
  display: flex; justify-content: center;
  gap: 4px;
  margin-bottom: 32px;
  padding: 4px;
  background: var(--surface, rgba(255,255,255,0.05));
  border-radius: var(--radius, 12px);
  border: 1px solid var(--border, rgba(255,255,255,0.08));
}
.{$p}-faq-tab-radio {
  position: absolute; opacity: 0; pointer-events: none;
}
.{$p}-faq-tab-btn {
  padding: 12px 24px;
  font-size: 0.9375rem; font-weight: 500;
  color: var(--text-muted, rgba(255,255,255,0.6));
  border-radius: calc(var(--radius, 12px) - 4px);
  cursor: pointer;
  transition: all 0.3s ease;
  flex: 1; text-align: center;
}
.{$p}-faq-tab-btn:hover {
  color: var(--text, #fff);
}
.{$p}-faq-tab-radio:checked + .{$p}-faq-tab-btn {
  color: var(--text, #fff);
  background: rgba(var(--primary-rgb, 42,125,225), 0.15);
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.{$p}-faq-tab-panels {
  min-height: 200px;
}
.{$p}-faq-tab-panel {
  display: none;
}
.{$p}-faq-tab-panel--1 { display: block; }
.{$p}-faq-tab-nav:has(#<?= $p ?>-faq-tab1:checked) ~ .{$p}-faq-tab-panels .{$p}-faq-tab-panel { display: none; }
.{$p}-faq-tab-nav:has(#<?= $p ?>-faq-tab1:checked) ~ .{$p}-faq-tab-panels .{$p}-faq-tab-panel--1 { display: block; }
.{$p}-faq-tab-nav:has(#<?= $p ?>-faq-tab2:checked) ~ .{$p}-faq-tab-panels .{$p}-faq-tab-panel { display: none; }
.{$p}-faq-tab-nav:has(#<?= $p ?>-faq-tab2:checked) ~ .{$p}-faq-tab-panels .{$p}-faq-tab-panel--2 { display: block; }
.{$p}-faq-tab-nav:has(#<?= $p ?>-faq-tab3:checked) ~ .{$p}-faq-tab-panels .{$p}-faq-tab-panel { display: none; }
.{$p}-faq-tab-nav:has(#<?= $p ?>-faq-tab3:checked) ~ .{$p}-faq-tab-panels .{$p}-faq-tab-panel--3 { display: block; }
.{$p}-faq--tabs .{$p}-faq-item:first-child {
  border-top: 1px solid var(--border, rgba(255,255,255,0.1));
}

CSS;
    }

    // --- Responsive ---
    private static function css_responsive(string $p): string
    {
        return <<<CSS
@media (max-width: 768px) {
  .{$p}-faq--2col .{$p}-faq-grid {
    grid-template-columns: 1fr !important;
  }
  .{$p}-faq--categories .{$p}-faq-columns {
    grid-template-columns: 1fr !important;
  }
  .{$p}-faq-sidebar {
    position: static;
    flex-direction: row; flex-wrap: wrap; gap: 8px;
    margin-bottom: 24px;
  }
  .{$p}-faq-cat-btn {
    border-left: none;
    border-bottom: 2px solid transparent;
    padding: 8px 16px; font-size: 0.875rem;
  }
  .{$p}-faq-radio:checked + .{$p}-faq-cat-btn {
    border-left-color: transparent;
    border-bottom-color: var(--primary, #3b82f6);
  }
  .{$p}-faq--split .{$p}-faq-columns {
    grid-template-columns: 1fr !important;
  }
  .{$p}-faq-intro {
    position: static;
    text-align: center;
  }
  .{$p}-faq--split .{$p}-faq-title,
  .{$p}-faq--split .{$p}-faq-subtitle {
    text-align: center;
  }
  .{$p}-faq--numbered .{$p}-faq-answer {
    padding-left: 0;
  }
  .{$p}-faq-tab-nav {
    flex-direction: column;
  }
  .{$p}-faq-tab-btn {
    text-align: center;
  }
}

CSS;
    }
}
