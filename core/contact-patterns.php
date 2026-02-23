<?php
/**
 * Contact Section Pattern Registry
 * 
 * Pre-built Contact HTML layouts with structural CSS.
 * AI generates only decorative CSS (colors, fonts, effects) — never the structure.
 * 
 * 8 patterns across 3 groups.
 * @since 2026-02-19
 */

class ContactPatternRegistry
{
    // ═══════════════════════════════════════
    // PATTERN DEFINITIONS
    // ═══════════════════════════════════════

    private static array $patterns = [
        // --- Form (form-focused layouts) ---
        ['id'=>'form-centered',  'group'=>'form',     'css_type'=>'form-centered',
         'best_for'=>['agency','consulting','freelance','coaching']],
        ['id'=>'form-split',     'group'=>'form',     'css_type'=>'form-split',
         'best_for'=>['restaurant','hotel','clinic','dental']],
        ['id'=>'form-card',      'group'=>'form',     'css_type'=>'form-card',
         'best_for'=>['real-estate','construction','landscaping','law']],

        // --- Info (information-focused) ---
        ['id'=>'info-cards',     'group'=>'info',     'css_type'=>'info-cards',
         'best_for'=>['manufacturing','logistics','hvac','plumbing','electrical']],
        ['id'=>'info-map',       'group'=>'info',     'css_type'=>'info-map',
         'best_for'=>['retail','grocery','bakery','cafe','bar']],
        ['id'=>'info-minimal',   'group'=>'info',     'css_type'=>'info-minimal',
         'best_for'=>['art','gallery','museum','photography','fashion']],

        // --- Creative (unique layouts) ---
        ['id'=>'creative-split-bg',   'group'=>'creative', 'css_type'=>'creative-split-bg',
         'best_for'=>['tech','saas','startup','fintech']],
        ['id'=>'creative-faq-combo',  'group'=>'creative', 'css_type'=>'creative-faq-combo',
         'best_for'=>['ecommerce','marketplace','insurance','bank']],
    ];

    // ═══════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════

    /**
     * Pick the best contact pattern for an industry.
     */
    public static function pickPattern(string $industry): string
    {
        $industry = strtolower(trim($industry));
        foreach (self::$patterns as $p) {
            if (in_array($industry, $p['best_for'], true)) {
                return $p['id'];
            }
        }
        // Fallback: random from form group (most versatile)
        $formPatterns = array_filter(self::$patterns, fn($p) => $p['group'] === 'form');
        $formIds = array_column(array_values($formPatterns), 'id');
        return $formIds[array_rand($formIds)];
    }

    /**
     * Render a contact pattern.
     * Returns ['html'=>..., 'structural_css'=>..., 'pattern_id'=>..., 'classes'=>[...], 'fields'=>[...]]
     */
    public static function render(string $patternId, string $prefix, array $brief): array
    {
        $def = null;
        foreach (self::$patterns as $p) {
            if ($p['id'] === $patternId) { $def = $p; break; }
        }
        if (!$def) {
            $def = self::$patterns[0]; // fallback to form-centered
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
        // Common fields all contact patterns have
        $common = [
            'title'    => ['type' => 'text',     'label' => 'Contact Title'],
            'subtitle' => ['type' => 'textarea', 'label' => 'Contact Subtitle'],
            'badge'    => ['type' => 'text',     'label' => 'Badge / Label'],
            'phone'    => ['type' => 'text',     'label' => 'Phone Number'],
            'email'    => ['type' => 'text',     'label' => 'Email Address'],
            'address'  => ['type' => 'textarea', 'label' => 'Address'],
        ];

        // Pattern-specific extras
        $extras = match($patternId) {
            'form-card' => [
                'bg_image' => ['type' => 'image', 'label' => 'Background Image'],
            ],
            'info-minimal' => [
                'social_facebook'  => ['type' => 'text', 'label' => 'Facebook URL'],
                'social_instagram' => ['type' => 'text', 'label' => 'Instagram URL'],
                'social_twitter'   => ['type' => 'text', 'label' => 'Twitter / X URL'],
                'social_linkedin'  => ['type' => 'text', 'label' => 'LinkedIn URL'],
            ],
            'creative-faq-combo' => [
                'faq1_q' => ['type' => 'text',     'label' => 'FAQ Question 1'],
                'faq1_a' => ['type' => 'textarea', 'label' => 'FAQ Answer 1'],
                'faq2_q' => ['type' => 'text',     'label' => 'FAQ Question 2'],
                'faq2_a' => ['type' => 'textarea', 'label' => 'FAQ Answer 2'],
                'faq3_q' => ['type' => 'text',     'label' => 'FAQ Question 3'],
                'faq3_a' => ['type' => 'textarea', 'label' => 'FAQ Answer 3'],
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
            'form-centered' => <<<'GUIDE'
Form card: max-width: 560px centered, box-shadow: 0 8px 30px rgba(0,0,0,0.08), border-radius: var(--radius).
Inputs: border: 2px solid var(--border), border-radius: var(--radius), focus: border-color: var(--primary).
Labels: font-weight: 600, font-size: 0.875rem, above inputs.
Submit button: background: var(--primary), color: white, hover: translateY(-2px) + shadow.
GUIDE,
            'form-split' => <<<'GUIDE'
Two-column balanced: form left, contact info right.
Info icons: color: var(--primary), font-size: 1.125rem, accent colored.
Map placeholder: background: var(--surface), border-radius: var(--radius), muted icon.
Inputs: clean styling, focus ring with var(--primary).
GUIDE,
            'form-card' => <<<'GUIDE'
Floating card over contrasting bg image: box-shadow: 0 20px 60px rgba(0,0,0,0.15).
Card: background: var(--surface), border-radius: var(--radius), heavy padding.
Background overlay: linear-gradient(135deg, rgba(0,0,0,0.6), rgba(0,0,0,0.4)).
Input groups: card-like sections, clean borders, generous spacing.
GUIDE,
            'info-cards' => <<<'GUIDE'
Info cards: border: 1px solid var(--border), border-radius: var(--radius), text-align: center.
Icon circles: background: rgba(var(--primary-rgb), 0.1), color: var(--primary), 56px round.
Card hover: transform: translateY(-4px), box-shadow deepens.
Titles: font-weight: 700, info text: color: var(--text-muted).
GUIDE,
            'info-map' => <<<'GUIDE'
Map: prominent, full-width, min-height: 300px, border-radius on container.
Info overlay card: box-shadow: 0 8px 30px rgba(0,0,0,0.1), positioned over map edge.
Map placeholder: muted background var(--surface), large map icon centered.
Info items: icon left + text right, clean alignment.
GUIDE,
            'info-minimal' => <<<'GUIDE'
Minimal styling: no cards, no shadows, centered text layout.
Contact links: font-size: 1.125rem, icon color: var(--primary), hover: color transition.
Social icons: 48px circles, background: var(--surface), border: 1px solid var(--border).
Social hover: background: var(--primary), color: white, translateY(-2px).
GUIDE,
            'creative-split-bg' => <<<'GUIDE'
Dark side: background: #0f172a, text: white, info items: rgba(255,255,255,0.7).
Light side: background: var(--surface), standard form styling.
Dynamic diagonal or clean vertical split between halves.
Badge on dark: rgba(var(--primary-rgb), 0.2), border: rgba(var(--primary-rgb), 0.3).
GUIDE,
            'creative-faq-combo' => <<<'GUIDE'
Two-section layout: form left, FAQ accordion right.
Accordion: border: 1px solid var(--border), border-radius: var(--radius), smooth open/close.
Summary marker: custom +/− icon, font-weight: 600, hover: color: var(--primary).
Answer text: color: var(--text-muted), padding with smooth reveal transition.
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

        $title = $brief['contact_title'] ?? '';
        if (!$title && $name) {
            $title = "Get In Touch";
        }

        $subtitle = $brief['contact_subtitle'] ?? '';

        $badge = '';
        if ($industry) {
            $badge = ucwords(str_replace('-', ' ', $industry));
        }

        $replacements = [];
        if ($title)    $replacements["theme_get('contact.title', 'Get In Touch')"]                                                          = "theme_get('contact.title', '" . addslashes($title) . "')";
        if ($subtitle) $replacements["theme_get('contact.subtitle', 'We\\'d love to hear from you. Send us a message and we\\'ll respond as soon as possible.')"] = "theme_get('contact.subtitle', '" . addslashes($subtitle) . "')";
        if ($badge)    $replacements["theme_get('contact.badge', '')"]                                                                      = "theme_get('contact.badge', '" . addslashes($badge) . "')";

        foreach ($replacements as $search => $replace) {
            $html = str_replace($search, $replace, $html);
        }

        return $html;
    }

    private static function buildHTML(string $patternId, string $p): string
    {
        $templates = self::getTemplates($p);
        return $templates[$patternId] ?? $templates['form-centered'];
    }

    /**
     * Shared contact form HTML snippet.
     */
    private static function contactFormHTML(string $p, bool $includePhone = false): string
    {
        $phoneField = $includePhone ? <<<HTML
      <div class="{$p}-contact-field">
        <label for="contact-phone" class="{$p}-contact-label">Phone</label>
        <input type="tel" id="contact-phone" name="phone" class="{$p}-contact-input" placeholder="Your phone number">
      </div>
HTML : '';

        return <<<HTML
    <form action="/contact" method="POST" class="{$p}-contact-form">
      <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
      <div class="{$p}-contact-field">
        <label for="contact-name" class="{$p}-contact-label">Name</label>
        <input type="text" id="contact-name" name="name" required class="{$p}-contact-input" placeholder="Your name">
      </div>
      <div class="{$p}-contact-field">
        <label for="contact-email" class="{$p}-contact-label">Email</label>
        <input type="email" id="contact-email" name="email" required class="{$p}-contact-input" placeholder="Your email">
      </div>
{$phoneField}
      <div class="{$p}-contact-field">
        <label for="contact-message" class="{$p}-contact-label">Message</label>
        <textarea id="contact-message" name="message" required class="{$p}-contact-textarea" rows="5" placeholder="Your message"></textarea>
      </div>
      <div class="{$p}-contact-field">
        <button type="submit" class="{$p}-btn {$p}-btn-primary">Send Message</button>
      </div>
    </form>
HTML;
    }

    private static function getTemplates(string $p): array
    {
        $formBasic = self::contactFormHTML($p, false);
        $formWithPhone = self::contactFormHTML($p, true);

        return [

// ── Form Centered: Centered form (name, email, message, submit) ──
'form-centered' => <<<HTML
<?php
\$ctBadge = theme_get('contact.badge', '');
\$ctTitle = theme_get('contact.title', 'Get In Touch');
\$ctSubtitle = theme_get('contact.subtitle', 'We\'d love to hear from you. Send us a message and we\'ll respond as soon as possible.');
?>
<section class="{$p}-contact {$p}-contact--form-centered" id="contact">
  <div class="container">
    <div class="{$p}-contact-header" data-animate="fade-up">
      <?php if (\$ctBadge): ?><span class="{$p}-contact-badge" data-ts="contact.badge"><?= esc(\$ctBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-contact-title" data-ts="contact.title"><?= esc(\$ctTitle) ?></h2>
      <p class="{$p}-contact-subtitle" data-ts="contact.subtitle"><?= esc(\$ctSubtitle) ?></p>
    </div>
    <div class="{$p}-contact-form-wrap" data-animate="fade-up">
{$formBasic}
    </div>
  </div>
</section>
HTML,

// ── Form Split: Form left, contact info + map placeholder right ──
'form-split' => <<<HTML
<?php
\$ctBadge = theme_get('contact.badge', '');
\$ctTitle = theme_get('contact.title', 'Get In Touch');
\$ctSubtitle = theme_get('contact.subtitle', 'We\'d love to hear from you. Send us a message and we\'ll respond as soon as possible.');
\$ctPhone = theme_get('contact.phone', '');
\$ctEmail = theme_get('contact.email', '');
\$ctAddress = theme_get('contact.address', '');
?>
<section class="{$p}-contact {$p}-contact--form-split" id="contact">
  <div class="container">
    <div class="{$p}-contact-header" data-animate="fade-up">
      <?php if (\$ctBadge): ?><span class="{$p}-contact-badge" data-ts="contact.badge"><?= esc(\$ctBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-contact-title" data-ts="contact.title"><?= esc(\$ctTitle) ?></h2>
      <p class="{$p}-contact-subtitle" data-ts="contact.subtitle"><?= esc(\$ctSubtitle) ?></p>
    </div>
    <div class="{$p}-contact-split" data-animate="fade-up">
      <div class="{$p}-contact-split-form">
{$formWithPhone}
      </div>
      <div class="{$p}-contact-split-info">
        <div class="{$p}-contact-info-list">
          <?php if (\$ctPhone): ?>
          <div class="{$p}-contact-info-item">
            <i class="fas fa-phone"></i>
            <div>
              <strong>Phone</strong>
              <p data-ts="contact.phone"><?= esc(\$ctPhone) ?></p>
            </div>
          </div>
          <?php endif; ?>
          <?php if (\$ctEmail): ?>
          <div class="{$p}-contact-info-item">
            <i class="fas fa-envelope"></i>
            <div>
              <strong>Email</strong>
              <p data-ts="contact.email"><?= esc(\$ctEmail) ?></p>
            </div>
          </div>
          <?php endif; ?>
          <?php if (\$ctAddress): ?>
          <div class="{$p}-contact-info-item">
            <i class="fas fa-map-marker-alt"></i>
            <div>
              <strong>Address</strong>
              <p data-ts="contact.address"><?= esc(\$ctAddress) ?></p>
            </div>
          </div>
          <?php endif; ?>
        </div>
        <div class="{$p}-contact-map-placeholder">
          <div class="{$p}-contact-map-inner">
            <i class="fas fa-map-marked-alt"></i>
            <span>Map</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Form Card: Form inside elevated card, bg image behind ──
'form-card' => <<<HTML
<?php
\$ctBgImage = theme_get('contact.bg_image', '');
\$ctBadge = theme_get('contact.badge', '');
\$ctTitle = theme_get('contact.title', 'Get In Touch');
\$ctSubtitle = theme_get('contact.subtitle', 'We\'d love to hear from you. Send us a message and we\'ll respond as soon as possible.');
?>
<section class="{$p}-contact {$p}-contact--form-card" id="contact">
  <div class="{$p}-contact-card-bg" style="background-image: url('<?= esc(\$ctBgImage) ?>');" data-ts-bg="contact.bg_image"></div>
  <div class="{$p}-contact-card-overlay"></div>
  <div class="container">
    <div class="{$p}-contact-card" data-animate="fade-up">
      <div class="{$p}-contact-header">
        <?php if (\$ctBadge): ?><span class="{$p}-contact-badge" data-ts="contact.badge"><?= esc(\$ctBadge) ?></span><?php endif; ?>
        <h2 class="{$p}-contact-title" data-ts="contact.title"><?= esc(\$ctTitle) ?></h2>
        <p class="{$p}-contact-subtitle" data-ts="contact.subtitle"><?= esc(\$ctSubtitle) ?></p>
      </div>
{$formWithPhone}
    </div>
  </div>
</section>
HTML,

// ── Info Cards: 3 cards (phone, email, address) + form below ──
'info-cards' => <<<HTML
<?php
\$ctBadge = theme_get('contact.badge', '');
\$ctTitle = theme_get('contact.title', 'Get In Touch');
\$ctSubtitle = theme_get('contact.subtitle', 'We\'d love to hear from you. Send us a message and we\'ll respond as soon as possible.');
\$ctPhone = theme_get('contact.phone', '');
\$ctEmail = theme_get('contact.email', '');
\$ctAddress = theme_get('contact.address', '');
?>
<section class="{$p}-contact {$p}-contact--info-cards" id="contact">
  <div class="container">
    <div class="{$p}-contact-header" data-animate="fade-up">
      <?php if (\$ctBadge): ?><span class="{$p}-contact-badge" data-ts="contact.badge"><?= esc(\$ctBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-contact-title" data-ts="contact.title"><?= esc(\$ctTitle) ?></h2>
      <p class="{$p}-contact-subtitle" data-ts="contact.subtitle"><?= esc(\$ctSubtitle) ?></p>
    </div>
    <div class="{$p}-contact-cards-grid" data-animate="fade-up">
      <div class="{$p}-contact-info-card">
        <div class="{$p}-contact-info-icon"><i class="fas fa-phone"></i></div>
        <h3>Phone</h3>
        <p data-ts="contact.phone"><?= esc(\$ctPhone) ?></p>
      </div>
      <div class="{$p}-contact-info-card">
        <div class="{$p}-contact-info-icon"><i class="fas fa-envelope"></i></div>
        <h3>Email</h3>
        <p data-ts="contact.email"><?= esc(\$ctEmail) ?></p>
      </div>
      <div class="{$p}-contact-info-card">
        <div class="{$p}-contact-info-icon"><i class="fas fa-map-marker-alt"></i></div>
        <h3>Address</h3>
        <p data-ts="contact.address"><?= esc(\$ctAddress) ?></p>
      </div>
    </div>
    <div class="{$p}-contact-form-wrap" data-animate="fade-up">
{$formWithPhone}
    </div>
  </div>
</section>
HTML,

// ── Info Map: Full-width map placeholder top, info + form below ──
'info-map' => <<<HTML
<?php
\$ctBadge = theme_get('contact.badge', '');
\$ctTitle = theme_get('contact.title', 'Get In Touch');
\$ctSubtitle = theme_get('contact.subtitle', 'We\'d love to hear from you. Send us a message and we\'ll respond as soon as possible.');
\$ctPhone = theme_get('contact.phone', '');
\$ctEmail = theme_get('contact.email', '');
\$ctAddress = theme_get('contact.address', '');
?>
<section class="{$p}-contact {$p}-contact--info-map" id="contact">
  <div class="{$p}-contact-map-full">
    <div class="{$p}-contact-map-placeholder">
      <div class="{$p}-contact-map-inner">
        <i class="fas fa-map-marked-alt"></i>
        <span>Map</span>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="{$p}-contact-header" data-animate="fade-up">
      <?php if (\$ctBadge): ?><span class="{$p}-contact-badge" data-ts="contact.badge"><?= esc(\$ctBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-contact-title" data-ts="contact.title"><?= esc(\$ctTitle) ?></h2>
      <p class="{$p}-contact-subtitle" data-ts="contact.subtitle"><?= esc(\$ctSubtitle) ?></p>
    </div>
    <div class="{$p}-contact-map-bottom" data-animate="fade-up">
      <div class="{$p}-contact-map-info">
        <div class="{$p}-contact-info-list">
          <?php if (\$ctPhone): ?>
          <div class="{$p}-contact-info-item">
            <i class="fas fa-phone"></i>
            <span data-ts="contact.phone"><?= esc(\$ctPhone) ?></span>
          </div>
          <?php endif; ?>
          <?php if (\$ctEmail): ?>
          <div class="{$p}-contact-info-item">
            <i class="fas fa-envelope"></i>
            <span data-ts="contact.email"><?= esc(\$ctEmail) ?></span>
          </div>
          <?php endif; ?>
          <?php if (\$ctAddress): ?>
          <div class="{$p}-contact-info-item">
            <i class="fas fa-map-marker-alt"></i>
            <span data-ts="contact.address"><?= esc(\$ctAddress) ?></span>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="{$p}-contact-map-form">
{$formBasic}
      </div>
    </div>
  </div>
</section>
HTML,

// ── Info Minimal: Just email + phone + social links, no form ──
'info-minimal' => <<<HTML
<?php
\$ctBadge = theme_get('contact.badge', '');
\$ctTitle = theme_get('contact.title', 'Get In Touch');
\$ctSubtitle = theme_get('contact.subtitle', 'We\'d love to hear from you. Send us a message and we\'ll respond as soon as possible.');
\$ctPhone = theme_get('contact.phone', '');
\$ctEmail = theme_get('contact.email', '');
\$ctSocialFb = theme_get('contact.social_facebook', '');
\$ctSocialIg = theme_get('contact.social_instagram', '');
\$ctSocialTw = theme_get('contact.social_twitter', '');
\$ctSocialLi = theme_get('contact.social_linkedin', '');
?>
<section class="{$p}-contact {$p}-contact--info-minimal" id="contact">
  <div class="container">
    <div class="{$p}-contact-minimal-wrap" data-animate="fade-up">
      <div class="{$p}-contact-header">
        <?php if (\$ctBadge): ?><span class="{$p}-contact-badge" data-ts="contact.badge"><?= esc(\$ctBadge) ?></span><?php endif; ?>
        <h2 class="{$p}-contact-title" data-ts="contact.title"><?= esc(\$ctTitle) ?></h2>
        <p class="{$p}-contact-subtitle" data-ts="contact.subtitle"><?= esc(\$ctSubtitle) ?></p>
      </div>
      <div class="{$p}-contact-minimal-details">
        <?php if (\$ctEmail): ?>
        <a href="mailto:<?= esc(\$ctEmail) ?>" class="{$p}-contact-minimal-link" data-ts="contact.email">
          <i class="fas fa-envelope"></i> <?= esc(\$ctEmail) ?>
        </a>
        <?php endif; ?>
        <?php if (\$ctPhone): ?>
        <a href="tel:<?= esc(\$ctPhone) ?>" class="{$p}-contact-minimal-link" data-ts="contact.phone">
          <i class="fas fa-phone"></i> <?= esc(\$ctPhone) ?>
        </a>
        <?php endif; ?>
      </div>
      <div class="{$p}-contact-social">
        <?php if (\$ctSocialFb): ?><a href="<?= esc(\$ctSocialFb) ?>" class="{$p}-contact-social-link" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
        <?php if (\$ctSocialIg): ?><a href="<?= esc(\$ctSocialIg) ?>" class="{$p}-contact-social-link" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a><?php endif; ?>
        <?php if (\$ctSocialTw): ?><a href="<?= esc(\$ctSocialTw) ?>" class="{$p}-contact-social-link" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a><?php endif; ?>
        <?php if (\$ctSocialLi): ?><a href="<?= esc(\$ctSocialLi) ?>" class="{$p}-contact-social-link" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Creative Split BG: Dark/light split, form on one side ──
'creative-split-bg' => <<<HTML
<?php
\$ctBadge = theme_get('contact.badge', '');
\$ctTitle = theme_get('contact.title', 'Get In Touch');
\$ctSubtitle = theme_get('contact.subtitle', 'We\'d love to hear from you. Send us a message and we\'ll respond as soon as possible.');
\$ctPhone = theme_get('contact.phone', '');
\$ctEmail = theme_get('contact.email', '');
\$ctAddress = theme_get('contact.address', '');
?>
<section class="{$p}-contact {$p}-contact--creative-split-bg" id="contact">
  <div class="{$p}-contact-split-bg-wrap">
    <div class="{$p}-contact-split-dark">
      <div class="{$p}-contact-split-dark-content" data-animate="fade-up">
        <?php if (\$ctBadge): ?><span class="{$p}-contact-badge" data-ts="contact.badge"><?= esc(\$ctBadge) ?></span><?php endif; ?>
        <h2 class="{$p}-contact-title" data-ts="contact.title"><?= esc(\$ctTitle) ?></h2>
        <p class="{$p}-contact-subtitle" data-ts="contact.subtitle"><?= esc(\$ctSubtitle) ?></p>
        <div class="{$p}-contact-info-list">
          <?php if (\$ctPhone): ?>
          <div class="{$p}-contact-info-item">
            <i class="fas fa-phone"></i>
            <span data-ts="contact.phone"><?= esc(\$ctPhone) ?></span>
          </div>
          <?php endif; ?>
          <?php if (\$ctEmail): ?>
          <div class="{$p}-contact-info-item">
            <i class="fas fa-envelope"></i>
            <span data-ts="contact.email"><?= esc(\$ctEmail) ?></span>
          </div>
          <?php endif; ?>
          <?php if (\$ctAddress): ?>
          <div class="{$p}-contact-info-item">
            <i class="fas fa-map-marker-alt"></i>
            <span data-ts="contact.address"><?= esc(\$ctAddress) ?></span>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="{$p}-contact-split-light">
      <div class="{$p}-contact-split-light-content" data-animate="fade-up">
{$formWithPhone}
      </div>
    </div>
  </div>
</section>
HTML,

// ── Creative FAQ Combo: Contact form + mini FAQ accordion ──
'creative-faq-combo' => <<<HTML
<?php
\$ctBadge = theme_get('contact.badge', '');
\$ctTitle = theme_get('contact.title', 'Get In Touch');
\$ctSubtitle = theme_get('contact.subtitle', 'We\'d love to hear from you. Send us a message and we\'ll respond as soon as possible.');
\$faq1Q = theme_get('contact.faq1_q', 'What are your business hours?');
\$faq1A = theme_get('contact.faq1_a', 'We are open Monday to Friday, 9am to 5pm.');
\$faq2Q = theme_get('contact.faq2_q', 'How quickly do you respond?');
\$faq2A = theme_get('contact.faq2_a', 'We typically respond within 24 hours.');
\$faq3Q = theme_get('contact.faq3_q', 'Do you offer free consultations?');
\$faq3A = theme_get('contact.faq3_a', 'Yes, we offer a free initial consultation.');
?>
<section class="{$p}-contact {$p}-contact--creative-faq-combo" id="contact">
  <div class="container">
    <div class="{$p}-contact-header" data-animate="fade-up">
      <?php if (\$ctBadge): ?><span class="{$p}-contact-badge" data-ts="contact.badge"><?= esc(\$ctBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-contact-title" data-ts="contact.title"><?= esc(\$ctTitle) ?></h2>
      <p class="{$p}-contact-subtitle" data-ts="contact.subtitle"><?= esc(\$ctSubtitle) ?></p>
    </div>
    <div class="{$p}-contact-faq-layout" data-animate="fade-up">
      <div class="{$p}-contact-faq-form">
{$formWithPhone}
      </div>
      <div class="{$p}-contact-faq-side">
        <h3 class="{$p}-contact-faq-heading">Frequently Asked Questions</h3>
        <div class="{$p}-contact-faq-list">
          <details class="{$p}-contact-faq-item">
            <summary class="{$p}-contact-faq-question" data-ts="contact.faq1_q"><?= esc(\$faq1Q) ?></summary>
            <div class="{$p}-contact-faq-answer" data-ts="contact.faq1_a"><?= esc(\$faq1A) ?></div>
          </details>
          <details class="{$p}-contact-faq-item">
            <summary class="{$p}-contact-faq-question" data-ts="contact.faq2_q"><?= esc(\$faq2Q) ?></summary>
            <div class="{$p}-contact-faq-answer" data-ts="contact.faq2_a"><?= esc(\$faq2A) ?></div>
          </details>
          <details class="{$p}-contact-faq-item">
            <summary class="{$p}-contact-faq-question" data-ts="contact.faq3_q"><?= esc(\$faq3Q) ?></summary>
            <div class="{$p}-contact-faq-answer" data-ts="contact.faq3_a"><?= esc(\$faq3A) ?></div>
          </details>
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
        $base = ["{$p}-contact", "{$p}-contact-header", "{$p}-contact-badge", "{$p}-contact-title",
                 "{$p}-contact-subtitle", "{$p}-contact-form", "{$p}-contact-field",
                 "{$p}-contact-label", "{$p}-contact-input", "{$p}-contact-textarea",
                 "{$p}-btn", "{$p}-btn-primary"];

        $extra = match($patternId) {
            'form-centered' => ["{$p}-contact-form-wrap"],
            'form-split' => ["{$p}-contact-split", "{$p}-contact-split-form", "{$p}-contact-split-info",
                             "{$p}-contact-info-list", "{$p}-contact-info-item", "{$p}-contact-map-placeholder", "{$p}-contact-map-inner"],
            'form-card' => ["{$p}-contact-card", "{$p}-contact-card-bg", "{$p}-contact-card-overlay"],
            'info-cards' => ["{$p}-contact-cards-grid", "{$p}-contact-info-card", "{$p}-contact-info-icon", "{$p}-contact-form-wrap"],
            'info-map' => ["{$p}-contact-map-full", "{$p}-contact-map-placeholder", "{$p}-contact-map-inner",
                           "{$p}-contact-map-bottom", "{$p}-contact-map-info", "{$p}-contact-map-form",
                           "{$p}-contact-info-list", "{$p}-contact-info-item"],
            'info-minimal' => ["{$p}-contact-minimal-wrap", "{$p}-contact-minimal-details", "{$p}-contact-minimal-link",
                               "{$p}-contact-social", "{$p}-contact-social-link"],
            'creative-split-bg' => ["{$p}-contact-split-bg-wrap", "{$p}-contact-split-dark", "{$p}-contact-split-dark-content",
                                    "{$p}-contact-split-light", "{$p}-contact-split-light-content",
                                    "{$p}-contact-info-list", "{$p}-contact-info-item"],
            'creative-faq-combo' => ["{$p}-contact-faq-layout", "{$p}-contact-faq-form", "{$p}-contact-faq-side",
                                     "{$p}-contact-faq-heading", "{$p}-contact-faq-list", "{$p}-contact-faq-item",
                                     "{$p}-contact-faq-question", "{$p}-contact-faq-answer"],
            default => [],
        };

        return array_merge($base, $extra);
    }

    private static function buildStructuralCSS(string $cssType, string $p): string
    {
        $css = self::css_base($p);

        $typeCSS = match($cssType) {
            'form-centered'       => self::css_form_centered($p),
            'form-split'          => self::css_form_split($p),
            'form-card'           => self::css_form_card($p),
            'info-cards'          => self::css_info_cards($p),
            'info-map'            => self::css_info_map($p),
            'info-minimal'        => self::css_info_minimal($p),
            'creative-split-bg'   => self::css_creative_split_bg($p),
            'creative-faq-combo'  => self::css_creative_faq_combo($p),
            default               => self::css_form_centered($p),
        };

        return $css . $typeCSS . self::css_responsive($p);
    }

    // --- Base (shared by all contact patterns) ---
    private static function css_base(string $p): string
    {
        return <<<CSS

/* ═══ Contact Structural CSS (auto-generated — do not edit) ═══ */
.{$p}-contact {
  position: relative; overflow: hidden;
  padding: clamp(60px, 10vh, 120px) 0;
}
.{$p}-contact .container {
  position: relative; z-index: 2;
}
.{$p}-contact-header {
  text-align: center; max-width: 650px;
  margin: 0 auto clamp(32px, 5vw, 56px) auto;
}
.{$p}-contact-badge {
  display: inline-block;
  font-size: 0.8125rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.12em;
  padding: 6px 16px; border-radius: 100px;
  margin-bottom: 16px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.15);
  color: var(--primary, #3b82f6);
  border: 1px solid rgba(var(--primary-rgb, 42,125,225), 0.25);
}
.{$p}-contact-title {
  font-family: var(--font-heading, inherit);
  font-size: clamp(1.75rem, 4vw, 3rem);
  font-weight: 700; line-height: 1.15;
  margin: 0 0 16px 0;
  color: var(--text, #1e293b);
}
.{$p}-contact-subtitle {
  font-size: clamp(1rem, 1.5vw, 1.125rem);
  line-height: 1.7; margin: 0;
  color: var(--text-muted, #64748b);
  max-width: 50ch; margin-left: auto; margin-right: auto;
}
.{$p}-contact-form {
  display: flex; flex-direction: column; gap: 20px;
}
.{$p}-contact-field {
  display: flex; flex-direction: column; gap: 6px;
}
.{$p}-contact-label {
  font-size: 0.875rem; font-weight: 600;
  color: var(--text, #1e293b);
}
.{$p}-contact-input,
.{$p}-contact-textarea {
  width: 100%; padding: 12px 16px;
  border: 2px solid rgba(var(--text-rgb, 30,41,59), 0.12);
  border-radius: var(--radius, 8px);
  font-size: 0.9375rem; font-family: inherit;
  color: var(--text, #1e293b);
  background: var(--surface, #fff);
  transition: border-color 0.3s ease;
  box-sizing: border-box;
}
.{$p}-contact-input:focus,
.{$p}-contact-textarea:focus {
  outline: none;
  border-color: var(--primary, #3b82f6);
}
.{$p}-contact-textarea {
  resize: vertical; min-height: 120px;
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
.{$p}-contact-info-list {
  display: flex; flex-direction: column; gap: 20px;
}
.{$p}-contact-info-item {
  display: flex; align-items: flex-start; gap: 12px;
  color: var(--text-muted, #64748b);
}
.{$p}-contact-info-item i {
  font-size: 1.125rem;
  color: var(--primary, #3b82f6);
  margin-top: 3px;
}
.{$p}-contact-map-placeholder {
  background: var(--surface, #f1f5f9);
  border-radius: var(--radius, 8px);
  min-height: 200px; display: flex;
  align-items: center; justify-content: center;
}
.{$p}-contact-map-inner {
  display: flex; flex-direction: column;
  align-items: center; gap: 8px;
  color: var(--text-muted, #94a3b8);
  font-size: 0.875rem;
}
.{$p}-contact-map-inner i {
  font-size: 2rem;
}

CSS;
    }

    // --- Form Centered ---
    private static function css_form_centered(string $p): string
    {
        return <<<CSS
.{$p}-contact--form-centered .{$p}-contact-form-wrap {
  max-width: 560px; margin: 0 auto;
}

CSS;
    }

    // --- Form Split ---
    private static function css_form_split(string $p): string
    {
        return <<<CSS
.{$p}-contact--form-split .{$p}-contact-split {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 64px);
}
.{$p}-contact--form-split .{$p}-contact-split-info {
  display: flex; flex-direction: column; gap: 32px;
}
.{$p}-contact--form-split .{$p}-contact-map-placeholder {
  flex: 1;
}

CSS;
    }

    // --- Form Card ---
    private static function css_form_card(string $p): string
    {
        return <<<CSS
.{$p}-contact--form-card {
  padding: clamp(80px, 12vh, 160px) 0;
}
.{$p}-contact-card-bg {
  position: absolute; inset: 0;
  background-size: cover; background-position: center;
  z-index: 0;
}
.{$p}-contact-card-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(135deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.4) 100%);
  z-index: 1;
}
.{$p}-contact--form-card .{$p}-contact-card {
  max-width: 560px; margin: 0 auto;
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  padding: clamp(32px, 5vw, 48px);
  box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
.{$p}-contact--form-card .{$p}-contact-header {
  text-align: left; margin: 0 0 24px 0;
  max-width: none;
}

CSS;
    }

    // --- Info Cards ---
    private static function css_info_cards(string $p): string
    {
        return <<<CSS
.{$p}-contact--info-cards .{$p}-contact-cards-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: clamp(16px, 2vw, 24px);
  margin-bottom: clamp(32px, 5vw, 56px);
}
.{$p}-contact-info-card {
  text-align: center;
  padding: clamp(24px, 3vw, 40px);
  background: var(--surface, #f8fafc);
  border-radius: var(--radius, 12px);
  border: 1px solid rgba(var(--text-rgb, 30,41,59), 0.08);
}
.{$p}-contact-info-icon {
  width: 56px; height: 56px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 16px auto;
  background: rgba(var(--primary-rgb, 42,125,225), 0.1);
  color: var(--primary, #3b82f6);
  font-size: 1.25rem;
}
.{$p}-contact-info-card h3 {
  font-size: 1rem; font-weight: 700;
  margin: 0 0 8px 0;
  color: var(--text, #1e293b);
}
.{$p}-contact-info-card p {
  font-size: 0.9375rem; margin: 0;
  color: var(--text-muted, #64748b);
}
.{$p}-contact--info-cards .{$p}-contact-form-wrap {
  max-width: 700px; margin: 0 auto;
}

CSS;
    }

    // --- Info Map ---
    private static function css_info_map(string $p): string
    {
        return <<<CSS
.{$p}-contact--info-map {
  padding-top: 0;
}
.{$p}-contact-map-full {
  width: 100%;
}
.{$p}-contact-map-full .{$p}-contact-map-placeholder {
  min-height: 300px; border-radius: 0;
}
.{$p}-contact--info-map .{$p}-contact-header {
  margin-top: clamp(32px, 5vw, 56px);
}
.{$p}-contact--info-map .{$p}-contact-map-bottom {
  display: grid; grid-template-columns: 1fr 1.5fr;
  gap: clamp(32px, 5vw, 64px);
}
.{$p}-contact--info-map .{$p}-contact-map-info {
  display: flex; align-items: flex-start;
  padding-top: 8px;
}

CSS;
    }

    // --- Info Minimal ---
    private static function css_info_minimal(string $p): string
    {
        return <<<CSS
.{$p}-contact--info-minimal .{$p}-contact-minimal-wrap {
  text-align: center; max-width: 560px; margin: 0 auto;
}
.{$p}-contact--info-minimal .{$p}-contact-header {
  margin-bottom: 32px;
}
.{$p}-contact-minimal-details {
  display: flex; flex-direction: column; gap: 16px;
  align-items: center; margin-bottom: 32px;
}
.{$p}-contact-minimal-link {
  display: inline-flex; align-items: center; gap: 10px;
  font-size: 1.125rem; font-weight: 500;
  color: var(--text, #1e293b);
  text-decoration: none;
  transition: color 0.3s ease;
}
.{$p}-contact-minimal-link i {
  color: var(--primary, #3b82f6);
}
.{$p}-contact-minimal-link:hover {
  color: var(--primary, #3b82f6);
}
.{$p}-contact-social {
  display: flex; gap: 16px; justify-content: center;
}
.{$p}-contact-social-link {
  width: 48px; height: 48px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  background: var(--surface, #f1f5f9);
  color: var(--text-muted, #64748b);
  font-size: 1.125rem; text-decoration: none;
  transition: all 0.3s ease;
  border: 1px solid rgba(var(--text-rgb, 30,41,59), 0.08);
}
.{$p}-contact-social-link:hover {
  background: var(--primary, #3b82f6);
  color: var(--primary-contrast, #fff);
  border-color: var(--primary, #3b82f6);
  transform: translateY(-2px);
}

CSS;
    }

    // --- Creative Split BG ---
    private static function css_creative_split_bg(string $p): string
    {
        return <<<CSS
.{$p}-contact--creative-split-bg {
  padding: 0;
}
.{$p}-contact-split-bg-wrap {
  display: grid; grid-template-columns: 1fr 1fr;
  min-height: 600px;
}
.{$p}-contact-split-dark {
  background: #0f172a;
  display: flex; align-items: center;
  padding: clamp(40px, 6vw, 80px);
}
.{$p}-contact-split-dark-content {
  max-width: 480px;
}
.{$p}-contact--creative-split-bg .{$p}-contact-title {
  color: #fff;
}
.{$p}-contact--creative-split-bg .{$p}-contact-subtitle {
  color: rgba(255,255,255,0.65);
  margin-left: 0; margin-right: 0;
}
.{$p}-contact--creative-split-bg .{$p}-contact-badge {
  background: rgba(var(--primary-rgb, 42,125,225), 0.2);
  color: var(--primary, #3b82f6);
  border-color: rgba(var(--primary-rgb, 42,125,225), 0.3);
}
.{$p}-contact--creative-split-bg .{$p}-contact-info-item {
  color: rgba(255,255,255,0.7);
}
.{$p}-contact--creative-split-bg .{$p}-contact-header {
  text-align: left; margin: 0 0 32px 0;
  max-width: none;
}
.{$p}-contact-split-light {
  background: var(--surface, #f8fafc);
  display: flex; align-items: center;
  padding: clamp(40px, 6vw, 80px);
}
.{$p}-contact-split-light-content {
  width: 100%; max-width: 480px;
}

CSS;
    }

    // --- Creative FAQ Combo ---
    private static function css_creative_faq_combo(string $p): string
    {
        return <<<CSS
.{$p}-contact--creative-faq-combo .{$p}-contact-faq-layout {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 64px);
}
.{$p}-contact-faq-heading {
  font-family: var(--font-heading, inherit);
  font-size: 1.25rem; font-weight: 700;
  color: var(--text, #1e293b);
  margin: 0 0 20px 0;
}
.{$p}-contact-faq-list {
  display: flex; flex-direction: column; gap: 12px;
}
.{$p}-contact-faq-item {
  border: 1px solid rgba(var(--text-rgb, 30,41,59), 0.1);
  border-radius: var(--radius, 8px);
  overflow: hidden;
}
.{$p}-contact-faq-question {
  padding: 16px 20px;
  font-weight: 600; font-size: 0.9375rem;
  cursor: pointer;
  color: var(--text, #1e293b);
  list-style: none;
  display: flex; align-items: center;
  justify-content: space-between;
}
.{$p}-contact-faq-question::-webkit-details-marker {
  display: none;
}
.{$p}-contact-faq-question::after {
  content: '+'; font-size: 1.25rem; font-weight: 300;
  color: var(--text-muted, #64748b);
  transition: transform 0.3s ease;
}
.{$p}-contact-faq-item[open] .{$p}-contact-faq-question::after {
  content: '−';
}
.{$p}-contact-faq-answer {
  padding: 0 20px 16px 20px;
  font-size: 0.9375rem; line-height: 1.7;
  color: var(--text-muted, #64748b);
}

CSS;
    }

    // --- Responsive ---
    private static function css_responsive(string $p): string
    {
        return <<<CSS
@media (max-width: 768px) {
  .{$p}-contact--form-split .{$p}-contact-split {
    grid-template-columns: 1fr !important;
  }
  .{$p}-contact--info-cards .{$p}-contact-cards-grid {
    grid-template-columns: 1fr !important;
  }
  .{$p}-contact--info-map .{$p}-contact-map-bottom {
    grid-template-columns: 1fr !important;
  }
  .{$p}-contact-split-bg-wrap {
    grid-template-columns: 1fr !important;
  }
  .{$p}-contact-split-dark,
  .{$p}-contact-split-light {
    padding: clamp(32px, 5vw, 48px) !important;
  }
  .{$p}-contact--creative-faq-combo .{$p}-contact-faq-layout {
    grid-template-columns: 1fr !important;
  }
}

CSS;
    }
}
