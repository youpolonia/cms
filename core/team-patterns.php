<?php
/**
 * Team Section Pattern Registry
 * 
 * Pre-built Team HTML layouts with structural CSS.
 * AI generates only decorative CSS (colors, fonts, effects) — never the structure.
 * 
 * 8 patterns across 3 groups.
 * @since 2026-02-19
 */

class TeamPatternRegistry
{
    // ═══════════════════════════════════════
    // PATTERN DEFINITIONS
    // ═══════════════════════════════════════

    private static array $patterns = [
        // --- Grid (card-based layouts) ---
        ['id'=>'grid-cards',          'group'=>'grid',     'css_type'=>'grid-cards',
         'best_for'=>['agency','consulting','marketing','law','accounting']],
        ['id'=>'grid-circular',       'group'=>'grid',     'css_type'=>'grid-circular',
         'best_for'=>['healthcare','clinic','dental','hospital','coaching']],
        ['id'=>'grid-minimal',        'group'=>'grid',     'css_type'=>'grid-minimal',
         'best_for'=>['tech','startup','saas','fintech','ai']],

        // --- Showcase (featured / highlight layouts) ---
        ['id'=>'showcase-featured',   'group'=>'showcase', 'css_type'=>'showcase-featured',
         'best_for'=>['restaurant','hotel','bakery','winery','brewery']],
        ['id'=>'showcase-carousel',   'group'=>'showcase', 'css_type'=>'showcase-carousel',
         'best_for'=>['sports','fitness','gym','yoga','dance']],
        ['id'=>'showcase-split',      'group'=>'showcase', 'css_type'=>'showcase-split',
         'best_for'=>['construction','engineering','manufacturing','architecture']],

        // --- Creative (unique / interactive) ---
        ['id'=>'creative-hover-reveal','group'=>'creative','css_type'=>'creative-hover-reveal',
         'best_for'=>['fashion','beauty','salon','photography','art']],
        ['id'=>'creative-org-chart',  'group'=>'creative', 'css_type'=>'creative-org-chart',
         'best_for'=>['financial','bank','insurance','real-estate','enterprise']],
    ];

    // ═══════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════

    /**
     * Pick the best Team pattern for an industry.
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
     * Render a Team pattern.
     * Returns ['html'=>..., 'structural_css'=>..., 'pattern_id'=>..., 'classes'=>[...], 'fields'=>[...]]
     */
    public static function render(string $patternId, string $prefix, array $brief): array
    {
        $def = null;
        foreach (self::$patterns as $p) {
            if ($p['id'] === $patternId) { $def = $p; break; }
        }
        if (!$def) {
            $def = self::$patterns[0]; // fallback to grid-cards
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
        // Common fields all Team patterns have
        $common = [
            'title'    => ['type' => 'text',     'label' => 'Section Title'],
            'subtitle' => ['type' => 'textarea', 'label' => 'Section Subtitle'],
            'badge'    => ['type' => 'text',     'label' => 'Badge / Label'],
        ];

        // Per-member fields (4 members)
        for ($i = 1; $i <= 4; $i++) {
            $common["member{$i}_name"]  = ['type' => 'text',     'label' => "Member {$i} Name"];
            $common["member{$i}_role"]  = ['type' => 'text',     'label' => "Member {$i} Role"];
            $common["member{$i}_bio"]   = ['type' => 'textarea', 'label' => "Member {$i} Bio"];

            // Photo field — skip for grid-minimal (no photos)
            if ($patternId !== 'grid-minimal') {
                $common["member{$i}_photo"] = ['type' => 'image', 'label' => "Member {$i} Photo"];
            }

            // Social fields
            $common["member{$i}_linkedin"] = ['type' => 'text', 'label' => "Member {$i} LinkedIn"];
            $common["member{$i}_twitter"]  = ['type' => 'text', 'label' => "Member {$i} Twitter"];
        }

        return $common;
    }

    /**
     * Get decorative CSS guide for a pattern (tells AI what visual CSS to write).
     */
    public static function getDecorativeGuide(string $patternId): string
    {
        return match($patternId) {
            'grid-cards' => <<<'GUIDE'
Cards: background: var(--surface), border-radius: var(--radius), box-shadow: 0 4px 20px rgba(0,0,0,0.06).
Hover: transform: translateY(-4px), box-shadow intensifies to 0 12px 40px rgba(0,0,0,0.1).
Photo: aspect-ratio: 1/1, object-fit: cover, overflow: hidden.
Name: font-weight: 700, role: color: var(--primary), social icons row at bottom.
GUIDE,
            'grid-circular' => <<<'GUIDE'
Photos: border-radius: 50%, width/height: 160px, border: 4px solid var(--surface).
Box-shadow on avatar: 0 8px 30px rgba(0,0,0,0.08) for depth ring.
Placeholder initials: font-size: 3rem, color: var(--primary), centered.
Name centered below, role: color: var(--primary), font-weight: 500.
GUIDE,
            'grid-minimal' => <<<'GUIDE'
No cards or shadows — clean flat design.
Left accent: border-left: 3px solid var(--primary) on each member.
Hover: subtle background tint rgba(var(--primary-rgb), 0.04).
Name: font-weight: 700, bio: color: var(--text-muted), small font.
GUIDE,
            'showcase-featured' => <<<'GUIDE'
Featured member: large card with box-shadow: 0 8px 30px rgba(0,0,0,0.08), border-radius: var(--radius).
Featured photo: large (280px), border-radius, bio visible alongside.
Rest of team: smaller cards in grid below, hover lift effect.
Role text: color: var(--primary), font-weight: 600 for featured.
GUIDE,
            'showcase-carousel' => <<<'GUIDE'
Carousel cards: scroll-snap-align: start, flex: 0 0 280px.
Cards: border-radius: var(--radius), box-shadow, hover translateY(-4px).
Photo: aspect-ratio: 3/4, overflow: hidden, object-fit: cover.
Bio text: -webkit-line-clamp: 3, overflow hidden, muted color.
GUIDE,
            'showcase-split' => <<<'GUIDE'
Photo fills left half: object-fit: cover, full height.
Right side: clean text layout, member list with border-bottom separators.
Accent bg on alternate rows or subtle hover background.
Names: font-weight: 700, roles: color: var(--primary).
GUIDE,
            'creative-hover-reveal' => <<<'GUIDE'
Photo: aspect-ratio: 3/4, overflow: hidden, border-radius: var(--radius).
Overlay: gradient from bottom rgba(0,0,0,0.8) to transparent, opacity: 0 default.
Hover: overlay opacity: 1, photo transform: scale(1.05), transition: 0.4s ease.
Overlay text: color: white, social links: background: rgba(255,255,255,0.15).
GUIDE,
            'creative-org-chart' => <<<'GUIDE'
Leader card: border: 2px solid var(--primary), larger photo (130px), centered at top.
Connector line: width: 2px, background: var(--border), vertical between leader and row.
Subordinate cards: standard size, hover lift + shadow.
Level bg variation: leader card slightly different background tint.
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
        $title = $brief['team_headline'] ?? '';
        if (!$title && $name) {
            $title = "Meet the {$name} Team";
        }

        // Subtitle from brief
        $subtitle = $brief['team_subheadline'] ?? '';

        // Badge from industry
        $badge = '';
        if ($industry) {
            $badge = ucwords(str_replace('-', ' ', $industry));
        }

        // Replace defaults in theme_get() calls
        $replacements = [];
        if ($title)    $replacements["theme_get('team.title', 'Meet Our Team')"]                                                                    = "theme_get('team.title', '" . addslashes($title) . "')";
        if ($subtitle) $replacements["theme_get('team.subtitle', 'The talented people behind our success.')"]                                        = "theme_get('team.subtitle', '" . addslashes($subtitle) . "')";
        if ($badge)    $replacements["theme_get('team.badge', '')"]                                                                                  = "theme_get('team.badge', '" . addslashes($badge) . "')";

        foreach ($replacements as $search => $replace) {
            $html = str_replace($search, $replace, $html);
        }

        return $html;
    }

    private static function buildHTML(string $patternId, string $p): string
    {
        $templates = self::getTemplates($p);
        return $templates[$patternId] ?? $templates['grid-cards'];
    }

    private static function getTemplates(string $p): array
    {
        return [

// ── Grid Cards: 3-4 column card grid with photo, name, role, social ──
'grid-cards' => <<<HTML
<?php
\$teamBadge = theme_get('team.badge', '');
\$teamTitle = theme_get('team.title', 'Meet Our Team');
\$teamSubtitle = theme_get('team.subtitle', 'The talented people behind our success.');
\$members = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$m = [
        'name'     => theme_get("team.member{\$i}_name", \$i === 1 ? 'Jane Smith' : (\$i === 2 ? 'John Doe' : (\$i === 3 ? 'Emily Chen' : 'Michael Brown'))),
        'role'     => theme_get("team.member{\$i}_role", \$i === 1 ? 'CEO' : (\$i === 2 ? 'CTO' : (\$i === 3 ? 'Designer' : 'Marketing'))),
        'photo'    => theme_get("team.member{\$i}_photo", ''),
        'bio'      => theme_get("team.member{\$i}_bio", 'Passionate professional dedicated to delivering excellence.'),
        'linkedin' => theme_get("team.member{\$i}_linkedin", ''),
        'twitter'  => theme_get("team.member{\$i}_twitter", ''),
    ];
    if (\$m['name']) \$members[] = \$m;
}
?>
<section class="{$p}-team {$p}-team--grid-cards" id="team">
  <div class="container">
    <div class="{$p}-team-header" data-animate="fade-up">
      <?php if (\$teamBadge): ?><span class="{$p}-team-badge" data-ts="team.badge"><?= esc(\$teamBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-team-title" data-ts="team.title"><?= esc(\$teamTitle) ?></h2>
      <?php if (\$teamSubtitle): ?><p class="{$p}-team-subtitle" data-ts="team.subtitle"><?= esc(\$teamSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="{$p}-team-grid" data-animate="fade-up">
      <?php foreach (\$members as \$idx => \$member): \$n = \$idx + 1; ?>
      <div class="{$p}-team-card">
        <?php if (\$member['photo']): ?>
          <div class="{$p}-team-card-photo">
            <img src="<?= esc(\$member['photo']) ?>" alt="<?= esc(\$member['name']) ?>" loading="lazy" data-ts="team.member<?= \$n ?>_photo">
          </div>
        <?php else: ?>
          <div class="{$p}-team-card-photo {$p}-team-card-photo--placeholder">
            <span><?= esc(mb_substr(\$member['name'], 0, 1)) ?></span>
          </div>
        <?php endif; ?>
        <div class="{$p}-team-card-body">
          <h3 class="{$p}-team-card-name" data-ts="team.member<?= \$n ?>_name"><?= esc(\$member['name']) ?></h3>
          <p class="{$p}-team-card-role" data-ts="team.member<?= \$n ?>_role"><?= esc(\$member['role']) ?></p>
          <div class="{$p}-team-card-social">
            <?php if (\$member['linkedin']): ?><a href="<?= esc(\$member['linkedin']) ?>" class="{$p}-team-social-link" aria-label="LinkedIn" data-ts-href="team.member<?= \$n ?>_linkedin"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
            <?php if (\$member['twitter']): ?><a href="<?= esc(\$member['twitter']) ?>" class="{$p}-team-social-link" aria-label="Twitter" data-ts-href="team.member<?= \$n ?>_twitter"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Grid Circular: Circular avatar photos in grid ──
'grid-circular' => <<<HTML
<?php
\$teamBadge = theme_get('team.badge', '');
\$teamTitle = theme_get('team.title', 'Meet Our Team');
\$teamSubtitle = theme_get('team.subtitle', 'The talented people behind our success.');
\$members = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$m = [
        'name'     => theme_get("team.member{\$i}_name", \$i === 1 ? 'Jane Smith' : (\$i === 2 ? 'John Doe' : (\$i === 3 ? 'Emily Chen' : 'Michael Brown'))),
        'role'     => theme_get("team.member{\$i}_role", \$i === 1 ? 'CEO' : (\$i === 2 ? 'CTO' : (\$i === 3 ? 'Designer' : 'Marketing'))),
        'photo'    => theme_get("team.member{\$i}_photo", ''),
        'bio'      => theme_get("team.member{\$i}_bio", 'Passionate professional dedicated to delivering excellence.'),
        'linkedin' => theme_get("team.member{\$i}_linkedin", ''),
        'twitter'  => theme_get("team.member{\$i}_twitter", ''),
    ];
    if (\$m['name']) \$members[] = \$m;
}
?>
<section class="{$p}-team {$p}-team--grid-circular" id="team">
  <div class="container">
    <div class="{$p}-team-header" data-animate="fade-up">
      <?php if (\$teamBadge): ?><span class="{$p}-team-badge" data-ts="team.badge"><?= esc(\$teamBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-team-title" data-ts="team.title"><?= esc(\$teamTitle) ?></h2>
      <?php if (\$teamSubtitle): ?><p class="{$p}-team-subtitle" data-ts="team.subtitle"><?= esc(\$teamSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="{$p}-team-grid" data-animate="fade-up">
      <?php foreach (\$members as \$idx => \$member): \$n = \$idx + 1; ?>
      <div class="{$p}-team-member">
        <div class="{$p}-team-avatar">
          <?php if (\$member['photo']): ?>
            <img src="<?= esc(\$member['photo']) ?>" alt="<?= esc(\$member['name']) ?>" loading="lazy" data-ts="team.member<?= \$n ?>_photo">
          <?php else: ?>
            <span class="{$p}-team-avatar-initial"><?= esc(mb_substr(\$member['name'], 0, 1)) ?></span>
          <?php endif; ?>
        </div>
        <h3 class="{$p}-team-member-name" data-ts="team.member<?= \$n ?>_name"><?= esc(\$member['name']) ?></h3>
        <p class="{$p}-team-member-role" data-ts="team.member<?= \$n ?>_role"><?= esc(\$member['role']) ?></p>
        <div class="{$p}-team-member-social">
          <?php if (\$member['linkedin']): ?><a href="<?= esc(\$member['linkedin']) ?>" class="{$p}-team-social-link" aria-label="LinkedIn" data-ts-href="team.member<?= \$n ?>_linkedin"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
          <?php if (\$member['twitter']): ?><a href="<?= esc(\$member['twitter']) ?>" class="{$p}-team-social-link" aria-label="Twitter" data-ts-href="team.member<?= \$n ?>_twitter"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Grid Minimal: Name + role only, no photo ──
'grid-minimal' => <<<HTML
<?php
\$teamBadge = theme_get('team.badge', '');
\$teamTitle = theme_get('team.title', 'Meet Our Team');
\$teamSubtitle = theme_get('team.subtitle', 'The talented people behind our success.');
\$members = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$m = [
        'name'     => theme_get("team.member{\$i}_name", \$i === 1 ? 'Jane Smith' : (\$i === 2 ? 'John Doe' : (\$i === 3 ? 'Emily Chen' : 'Michael Brown'))),
        'role'     => theme_get("team.member{\$i}_role", \$i === 1 ? 'CEO' : (\$i === 2 ? 'CTO' : (\$i === 3 ? 'Designer' : 'Marketing'))),
        'bio'      => theme_get("team.member{\$i}_bio", 'Passionate professional dedicated to delivering excellence.'),
        'linkedin' => theme_get("team.member{\$i}_linkedin", ''),
        'twitter'  => theme_get("team.member{\$i}_twitter", ''),
    ];
    if (\$m['name']) \$members[] = \$m;
}
?>
<section class="{$p}-team {$p}-team--grid-minimal" id="team">
  <div class="container">
    <div class="{$p}-team-header" data-animate="fade-up">
      <?php if (\$teamBadge): ?><span class="{$p}-team-badge" data-ts="team.badge"><?= esc(\$teamBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-team-title" data-ts="team.title"><?= esc(\$teamTitle) ?></h2>
      <?php if (\$teamSubtitle): ?><p class="{$p}-team-subtitle" data-ts="team.subtitle"><?= esc(\$teamSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="{$p}-team-grid" data-animate="fade-up">
      <?php foreach (\$members as \$idx => \$member): \$n = \$idx + 1; ?>
      <div class="{$p}-team-item">
        <h3 class="{$p}-team-item-name" data-ts="team.member<?= \$n ?>_name"><?= esc(\$member['name']) ?></h3>
        <p class="{$p}-team-item-role" data-ts="team.member<?= \$n ?>_role"><?= esc(\$member['role']) ?></p>
        <p class="{$p}-team-item-bio" data-ts="team.member<?= \$n ?>_bio"><?= esc(\$member['bio']) ?></p>
        <div class="{$p}-team-item-social">
          <?php if (\$member['linkedin']): ?><a href="<?= esc(\$member['linkedin']) ?>" class="{$p}-team-social-link" aria-label="LinkedIn" data-ts-href="team.member<?= \$n ?>_linkedin"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
          <?php if (\$member['twitter']): ?><a href="<?= esc(\$member['twitter']) ?>" class="{$p}-team-social-link" aria-label="Twitter" data-ts-href="team.member<?= \$n ?>_twitter"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Showcase Featured: 1 large founder card + team grid below ──
'showcase-featured' => <<<HTML
<?php
\$teamBadge = theme_get('team.badge', '');
\$teamTitle = theme_get('team.title', 'Meet Our Team');
\$teamSubtitle = theme_get('team.subtitle', 'The talented people behind our success.');
\$members = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$m = [
        'name'     => theme_get("team.member{\$i}_name", \$i === 1 ? 'Jane Smith' : (\$i === 2 ? 'John Doe' : (\$i === 3 ? 'Emily Chen' : 'Michael Brown'))),
        'role'     => theme_get("team.member{\$i}_role", \$i === 1 ? 'Founder & CEO' : (\$i === 2 ? 'Head Chef' : (\$i === 3 ? 'Sommelier' : 'Manager'))),
        'photo'    => theme_get("team.member{\$i}_photo", ''),
        'bio'      => theme_get("team.member{\$i}_bio", 'Passionate professional dedicated to delivering excellence.'),
        'linkedin' => theme_get("team.member{\$i}_linkedin", ''),
        'twitter'  => theme_get("team.member{\$i}_twitter", ''),
    ];
    if (\$m['name']) \$members[] = \$m;
}
\$founder = \$members[0] ?? null;
\$rest = array_slice(\$members, 1);
?>
<section class="{$p}-team {$p}-team--showcase-featured" id="team">
  <div class="container">
    <div class="{$p}-team-header" data-animate="fade-up">
      <?php if (\$teamBadge): ?><span class="{$p}-team-badge" data-ts="team.badge"><?= esc(\$teamBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-team-title" data-ts="team.title"><?= esc(\$teamTitle) ?></h2>
      <?php if (\$teamSubtitle): ?><p class="{$p}-team-subtitle" data-ts="team.subtitle"><?= esc(\$teamSubtitle) ?></p><?php endif; ?>
    </div>
    <?php if (\$founder): ?>
    <div class="{$p}-team-featured" data-animate="fade-up">
      <div class="{$p}-team-featured-photo">
        <?php if (\$founder['photo']): ?>
          <img src="<?= esc(\$founder['photo']) ?>" alt="<?= esc(\$founder['name']) ?>" loading="lazy" data-ts="team.member1_photo">
        <?php else: ?>
          <span class="{$p}-team-featured-initial"><?= esc(mb_substr(\$founder['name'], 0, 1)) ?></span>
        <?php endif; ?>
      </div>
      <div class="{$p}-team-featured-info">
        <h3 class="{$p}-team-featured-name" data-ts="team.member1_name"><?= esc(\$founder['name']) ?></h3>
        <p class="{$p}-team-featured-role" data-ts="team.member1_role"><?= esc(\$founder['role']) ?></p>
        <p class="{$p}-team-featured-bio" data-ts="team.member1_bio"><?= esc(\$founder['bio']) ?></p>
        <div class="{$p}-team-featured-social">
          <?php if (\$founder['linkedin']): ?><a href="<?= esc(\$founder['linkedin']) ?>" class="{$p}-team-social-link" aria-label="LinkedIn" data-ts-href="team.member1_linkedin"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
          <?php if (\$founder['twitter']): ?><a href="<?= esc(\$founder['twitter']) ?>" class="{$p}-team-social-link" aria-label="Twitter" data-ts-href="team.member1_twitter"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <?php if (\$rest): ?>
    <div class="{$p}-team-grid" data-animate="fade-up">
      <?php foreach (\$rest as \$idx => \$member): \$n = \$idx + 2; ?>
      <div class="{$p}-team-card">
        <?php if (\$member['photo']): ?>
          <div class="{$p}-team-card-photo">
            <img src="<?= esc(\$member['photo']) ?>" alt="<?= esc(\$member['name']) ?>" loading="lazy" data-ts="team.member<?= \$n ?>_photo">
          </div>
        <?php else: ?>
          <div class="{$p}-team-card-photo {$p}-team-card-photo--placeholder">
            <span><?= esc(mb_substr(\$member['name'], 0, 1)) ?></span>
          </div>
        <?php endif; ?>
        <h3 class="{$p}-team-card-name" data-ts="team.member<?= \$n ?>_name"><?= esc(\$member['name']) ?></h3>
        <p class="{$p}-team-card-role" data-ts="team.member<?= \$n ?>_role"><?= esc(\$member['role']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
HTML,

// ── Showcase Carousel: Horizontal scroll team cards ──
'showcase-carousel' => <<<HTML
<?php
\$teamBadge = theme_get('team.badge', '');
\$teamTitle = theme_get('team.title', 'Meet Our Team');
\$teamSubtitle = theme_get('team.subtitle', 'The talented people behind our success.');
\$members = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$m = [
        'name'     => theme_get("team.member{\$i}_name", \$i === 1 ? 'Jane Smith' : (\$i === 2 ? 'John Doe' : (\$i === 3 ? 'Emily Chen' : 'Michael Brown'))),
        'role'     => theme_get("team.member{\$i}_role", \$i === 1 ? 'Head Coach' : (\$i === 2 ? 'Trainer' : (\$i === 3 ? 'Nutritionist' : 'Therapist'))),
        'photo'    => theme_get("team.member{\$i}_photo", ''),
        'bio'      => theme_get("team.member{\$i}_bio", 'Passionate professional dedicated to delivering excellence.'),
        'linkedin' => theme_get("team.member{\$i}_linkedin", ''),
        'twitter'  => theme_get("team.member{\$i}_twitter", ''),
    ];
    if (\$m['name']) \$members[] = \$m;
}
?>
<section class="{$p}-team {$p}-team--showcase-carousel" id="team">
  <div class="container">
    <div class="{$p}-team-header" data-animate="fade-up">
      <?php if (\$teamBadge): ?><span class="{$p}-team-badge" data-ts="team.badge"><?= esc(\$teamBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-team-title" data-ts="team.title"><?= esc(\$teamTitle) ?></h2>
      <?php if (\$teamSubtitle): ?><p class="{$p}-team-subtitle" data-ts="team.subtitle"><?= esc(\$teamSubtitle) ?></p><?php endif; ?>
    </div>
  </div>
  <div class="{$p}-team-carousel" data-animate="fade-up">
    <div class="{$p}-team-carousel-track">
      <?php foreach (\$members as \$idx => \$member): \$n = \$idx + 1; ?>
      <div class="{$p}-team-carousel-card">
        <?php if (\$member['photo']): ?>
          <div class="{$p}-team-carousel-photo">
            <img src="<?= esc(\$member['photo']) ?>" alt="<?= esc(\$member['name']) ?>" loading="lazy" data-ts="team.member<?= \$n ?>_photo">
          </div>
        <?php else: ?>
          <div class="{$p}-team-carousel-photo {$p}-team-carousel-photo--placeholder">
            <span><?= esc(mb_substr(\$member['name'], 0, 1)) ?></span>
          </div>
        <?php endif; ?>
        <div class="{$p}-team-carousel-info">
          <h3 class="{$p}-team-carousel-name" data-ts="team.member<?= \$n ?>_name"><?= esc(\$member['name']) ?></h3>
          <p class="{$p}-team-carousel-role" data-ts="team.member<?= \$n ?>_role"><?= esc(\$member['role']) ?></p>
          <p class="{$p}-team-carousel-bio" data-ts="team.member<?= \$n ?>_bio"><?= esc(\$member['bio']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Showcase Split: Team photo left, text + names right ──
'showcase-split' => <<<HTML
<?php
\$teamBadge = theme_get('team.badge', '');
\$teamTitle = theme_get('team.title', 'Meet Our Team');
\$teamSubtitle = theme_get('team.subtitle', 'The talented people behind our success.');
\$members = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$m = [
        'name'     => theme_get("team.member{\$i}_name", \$i === 1 ? 'Jane Smith' : (\$i === 2 ? 'John Doe' : (\$i === 3 ? 'Emily Chen' : 'Michael Brown'))),
        'role'     => theme_get("team.member{\$i}_role", \$i === 1 ? 'Project Lead' : (\$i === 2 ? 'Engineer' : (\$i === 3 ? 'Architect' : 'Foreman'))),
        'photo'    => theme_get("team.member{\$i}_photo", ''),
        'bio'      => theme_get("team.member{\$i}_bio", 'Passionate professional dedicated to delivering excellence.'),
        'linkedin' => theme_get("team.member{\$i}_linkedin", ''),
        'twitter'  => theme_get("team.member{\$i}_twitter", ''),
    ];
    if (\$m['name']) \$members[] = \$m;
}
\$heroPhoto = theme_get('team.member1_photo', '');
?>
<section class="{$p}-team {$p}-team--showcase-split" id="team">
  <div class="{$p}-team-split">
    <div class="{$p}-team-split-image">
      <?php if (\$heroPhoto): ?>
        <img src="<?= esc(\$heroPhoto) ?>" alt="Our Team" loading="lazy" data-ts="team.member1_photo">
      <?php endif; ?>
    </div>
    <div class="{$p}-team-split-content" data-animate="fade-up">
      <?php if (\$teamBadge): ?><span class="{$p}-team-badge" data-ts="team.badge"><?= esc(\$teamBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-team-title" data-ts="team.title"><?= esc(\$teamTitle) ?></h2>
      <?php if (\$teamSubtitle): ?><p class="{$p}-team-subtitle" data-ts="team.subtitle"><?= esc(\$teamSubtitle) ?></p><?php endif; ?>
      <div class="{$p}-team-split-list">
        <?php foreach (\$members as \$idx => \$member): \$n = \$idx + 1; ?>
        <div class="{$p}-team-split-member">
          <h3 class="{$p}-team-split-name" data-ts="team.member<?= \$n ?>_name"><?= esc(\$member['name']) ?></h3>
          <p class="{$p}-team-split-role" data-ts="team.member<?= \$n ?>_role"><?= esc(\$member['role']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
HTML,

// ── Creative Hover Reveal: Photos with hover overlay showing bio ──
'creative-hover-reveal' => <<<HTML
<?php
\$teamBadge = theme_get('team.badge', '');
\$teamTitle = theme_get('team.title', 'Meet Our Team');
\$teamSubtitle = theme_get('team.subtitle', 'The talented people behind our success.');
\$members = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$m = [
        'name'     => theme_get("team.member{\$i}_name", \$i === 1 ? 'Jane Smith' : (\$i === 2 ? 'John Doe' : (\$i === 3 ? 'Emily Chen' : 'Michael Brown'))),
        'role'     => theme_get("team.member{\$i}_role", \$i === 1 ? 'Creative Director' : (\$i === 2 ? 'Photographer' : (\$i === 3 ? 'Stylist' : 'Art Director'))),
        'photo'    => theme_get("team.member{\$i}_photo", ''),
        'bio'      => theme_get("team.member{\$i}_bio", 'Passionate professional dedicated to delivering excellence.'),
        'linkedin' => theme_get("team.member{\$i}_linkedin", ''),
        'twitter'  => theme_get("team.member{\$i}_twitter", ''),
    ];
    if (\$m['name']) \$members[] = \$m;
}
?>
<section class="{$p}-team {$p}-team--creative-hover-reveal" id="team">
  <div class="container">
    <div class="{$p}-team-header" data-animate="fade-up">
      <?php if (\$teamBadge): ?><span class="{$p}-team-badge" data-ts="team.badge"><?= esc(\$teamBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-team-title" data-ts="team.title"><?= esc(\$teamTitle) ?></h2>
      <?php if (\$teamSubtitle): ?><p class="{$p}-team-subtitle" data-ts="team.subtitle"><?= esc(\$teamSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="{$p}-team-grid" data-animate="fade-up">
      <?php foreach (\$members as \$idx => \$member): \$n = \$idx + 1; ?>
      <div class="{$p}-team-hover-card">
        <div class="{$p}-team-hover-photo">
          <?php if (\$member['photo']): ?>
            <img src="<?= esc(\$member['photo']) ?>" alt="<?= esc(\$member['name']) ?>" loading="lazy" data-ts="team.member<?= \$n ?>_photo">
          <?php else: ?>
            <div class="{$p}-team-hover-placeholder"><span><?= esc(mb_substr(\$member['name'], 0, 1)) ?></span></div>
          <?php endif; ?>
          <div class="{$p}-team-hover-overlay">
            <p class="{$p}-team-hover-bio" data-ts="team.member<?= \$n ?>_bio"><?= esc(\$member['bio']) ?></p>
            <div class="{$p}-team-hover-social">
              <?php if (\$member['linkedin']): ?><a href="<?= esc(\$member['linkedin']) ?>" class="{$p}-team-social-link" aria-label="LinkedIn" data-ts-href="team.member<?= \$n ?>_linkedin"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
              <?php if (\$member['twitter']): ?><a href="<?= esc(\$member['twitter']) ?>" class="{$p}-team-social-link" aria-label="Twitter" data-ts-href="team.member<?= \$n ?>_twitter"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
            </div>
          </div>
        </div>
        <h3 class="{$p}-team-hover-name" data-ts="team.member<?= \$n ?>_name"><?= esc(\$member['name']) ?></h3>
        <p class="{$p}-team-hover-role" data-ts="team.member<?= \$n ?>_role"><?= esc(\$member['role']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
HTML,

// ── Creative Org Chart: Hierarchical layout (CEO top, then row) ──
'creative-org-chart' => <<<HTML
<?php
\$teamBadge = theme_get('team.badge', '');
\$teamTitle = theme_get('team.title', 'Meet Our Team');
\$teamSubtitle = theme_get('team.subtitle', 'The talented people behind our success.');
\$members = [];
for (\$i = 1; \$i <= 4; \$i++) {
    \$m = [
        'name'     => theme_get("team.member{\$i}_name", \$i === 1 ? 'Jane Smith' : (\$i === 2 ? 'John Doe' : (\$i === 3 ? 'Emily Chen' : 'Michael Brown'))),
        'role'     => theme_get("team.member{\$i}_role", \$i === 1 ? 'CEO' : (\$i === 2 ? 'VP Finance' : (\$i === 3 ? 'VP Operations' : 'VP Marketing'))),
        'photo'    => theme_get("team.member{\$i}_photo", ''),
        'bio'      => theme_get("team.member{\$i}_bio", 'Passionate professional dedicated to delivering excellence.'),
        'linkedin' => theme_get("team.member{\$i}_linkedin", ''),
        'twitter'  => theme_get("team.member{\$i}_twitter", ''),
    ];
    if (\$m['name']) \$members[] = \$m;
}
\$leader = \$members[0] ?? null;
\$rest = array_slice(\$members, 1);
?>
<section class="{$p}-team {$p}-team--creative-org-chart" id="team">
  <div class="container">
    <div class="{$p}-team-header" data-animate="fade-up">
      <?php if (\$teamBadge): ?><span class="{$p}-team-badge" data-ts="team.badge"><?= esc(\$teamBadge) ?></span><?php endif; ?>
      <h2 class="{$p}-team-title" data-ts="team.title"><?= esc(\$teamTitle) ?></h2>
      <?php if (\$teamSubtitle): ?><p class="{$p}-team-subtitle" data-ts="team.subtitle"><?= esc(\$teamSubtitle) ?></p><?php endif; ?>
    </div>
    <?php if (\$leader): ?>
    <div class="{$p}-team-org-leader" data-animate="fade-up">
      <div class="{$p}-team-org-card {$p}-team-org-card--leader">
        <div class="{$p}-team-org-photo">
          <?php if (\$leader['photo']): ?>
            <img src="<?= esc(\$leader['photo']) ?>" alt="<?= esc(\$leader['name']) ?>" loading="lazy" data-ts="team.member1_photo">
          <?php else: ?>
            <span class="{$p}-team-org-initial"><?= esc(mb_substr(\$leader['name'], 0, 1)) ?></span>
          <?php endif; ?>
        </div>
        <h3 class="{$p}-team-org-name" data-ts="team.member1_name"><?= esc(\$leader['name']) ?></h3>
        <p class="{$p}-team-org-role" data-ts="team.member1_role"><?= esc(\$leader['role']) ?></p>
        <p class="{$p}-team-org-bio" data-ts="team.member1_bio"><?= esc(\$leader['bio']) ?></p>
        <div class="{$p}-team-org-social">
          <?php if (\$leader['linkedin']): ?><a href="<?= esc(\$leader['linkedin']) ?>" class="{$p}-team-social-link" aria-label="LinkedIn" data-ts-href="team.member1_linkedin"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
          <?php if (\$leader['twitter']): ?><a href="<?= esc(\$leader['twitter']) ?>" class="{$p}-team-social-link" aria-label="Twitter" data-ts-href="team.member1_twitter"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
        </div>
      </div>
      <div class="{$p}-team-org-connector"></div>
    </div>
    <?php endif; ?>
    <?php if (\$rest): ?>
    <div class="{$p}-team-org-row" data-animate="fade-up">
      <?php foreach (\$rest as \$idx => \$member): \$n = \$idx + 2; ?>
      <div class="{$p}-team-org-card">
        <div class="{$p}-team-org-photo">
          <?php if (\$member['photo']): ?>
            <img src="<?= esc(\$member['photo']) ?>" alt="<?= esc(\$member['name']) ?>" loading="lazy" data-ts="team.member<?= \$n ?>_photo">
          <?php else: ?>
            <span class="{$p}-team-org-initial"><?= esc(mb_substr(\$member['name'], 0, 1)) ?></span>
          <?php endif; ?>
        </div>
        <h3 class="{$p}-team-org-name" data-ts="team.member<?= \$n ?>_name"><?= esc(\$member['name']) ?></h3>
        <p class="{$p}-team-org-role" data-ts="team.member<?= \$n ?>_role"><?= esc(\$member['role']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
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
        $base = ["{$p}-team", "{$p}-team-header", "{$p}-team-badge", "{$p}-team-title",
                 "{$p}-team-subtitle", "{$p}-team-social-link"];

        $extra = match($patternId) {
            'grid-cards' => ["{$p}-team-grid", "{$p}-team-card", "{$p}-team-card-photo",
                             "{$p}-team-card-body", "{$p}-team-card-name", "{$p}-team-card-role",
                             "{$p}-team-card-social"],
            'grid-circular' => ["{$p}-team-grid", "{$p}-team-member", "{$p}-team-avatar",
                                "{$p}-team-avatar-initial", "{$p}-team-member-name",
                                "{$p}-team-member-role", "{$p}-team-member-social"],
            'grid-minimal' => ["{$p}-team-grid", "{$p}-team-item", "{$p}-team-item-name",
                               "{$p}-team-item-role", "{$p}-team-item-bio", "{$p}-team-item-social"],
            'showcase-featured' => ["{$p}-team-featured", "{$p}-team-featured-photo",
                                    "{$p}-team-featured-info", "{$p}-team-featured-name",
                                    "{$p}-team-featured-role", "{$p}-team-featured-bio",
                                    "{$p}-team-featured-social", "{$p}-team-grid", "{$p}-team-card",
                                    "{$p}-team-card-photo", "{$p}-team-card-name", "{$p}-team-card-role"],
            'showcase-carousel' => ["{$p}-team-carousel", "{$p}-team-carousel-track",
                                    "{$p}-team-carousel-card", "{$p}-team-carousel-photo",
                                    "{$p}-team-carousel-info", "{$p}-team-carousel-name",
                                    "{$p}-team-carousel-role", "{$p}-team-carousel-bio"],
            'showcase-split' => ["{$p}-team-split", "{$p}-team-split-image", "{$p}-team-split-content",
                                 "{$p}-team-split-list", "{$p}-team-split-member",
                                 "{$p}-team-split-name", "{$p}-team-split-role"],
            'creative-hover-reveal' => ["{$p}-team-grid", "{$p}-team-hover-card",
                                        "{$p}-team-hover-photo", "{$p}-team-hover-overlay",
                                        "{$p}-team-hover-bio", "{$p}-team-hover-social",
                                        "{$p}-team-hover-name", "{$p}-team-hover-role"],
            'creative-org-chart' => ["{$p}-team-org-leader", "{$p}-team-org-card",
                                     "{$p}-team-org-photo", "{$p}-team-org-initial",
                                     "{$p}-team-org-name", "{$p}-team-org-role",
                                     "{$p}-team-org-bio", "{$p}-team-org-social",
                                     "{$p}-team-org-connector", "{$p}-team-org-row"],
            default => [],
        };

        return array_merge($base, $extra);
    }

    private static function buildStructuralCSS(string $cssType, string $p): string
    {
        $css = self::css_base($p);

        $typeCSS = match($cssType) {
            'grid-cards'            => self::css_grid_cards($p),
            'grid-circular'         => self::css_grid_circular($p),
            'grid-minimal'          => self::css_grid_minimal($p),
            'showcase-featured'     => self::css_showcase_featured($p),
            'showcase-carousel'     => self::css_showcase_carousel($p),
            'showcase-split'        => self::css_showcase_split($p),
            'creative-hover-reveal' => self::css_creative_hover_reveal($p),
            'creative-org-chart'    => self::css_creative_org_chart($p),
            default                 => self::css_grid_cards($p),
        };

        return $css . $typeCSS . self::css_responsive($p);
    }

    // --- Base (shared by all Team patterns) ---
    private static function css_base(string $p): string
    {
        return <<<CSS

/* ═══ Team Structural CSS (auto-generated — do not edit) ═══ */
.{$p}-team {
  position: relative; overflow: hidden;
  padding: clamp(60px, 10vh, 120px) 0;
}
.{$p}-team .container {
  position: relative; z-index: 2;
}
.{$p}-team-header {
  text-align: center; max-width: 700px;
  margin: 0 auto clamp(40px, 6vw, 64px) auto;
}
.{$p}-team-badge {
  display: inline-block;
  font-size: 0.8125rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.12em;
  padding: 6px 16px; border-radius: 100px;
  margin-bottom: 16px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.15);
  color: var(--primary, #3b82f6);
  border: 1px solid rgba(var(--primary-rgb, 42,125,225), 0.25);
}
.{$p}-team-title {
  font-family: var(--font-heading, inherit);
  font-size: clamp(1.75rem, 4vw, 3rem);
  font-weight: 700; line-height: 1.15;
  margin: 0 0 16px 0;
  color: var(--text, #1e293b);
}
.{$p}-team-subtitle {
  font-size: clamp(1rem, 1.5vw, 1.125rem);
  line-height: 1.7; margin: 0;
  color: var(--text-muted, #64748b);
  max-width: 50ch; margin-left: auto; margin-right: auto;
}
.{$p}-team-social-link {
  display: inline-flex; align-items: center; justify-content: center;
  width: 36px; height: 36px; border-radius: 50%;
  color: var(--text-muted, #64748b);
  background: rgba(var(--text-rgb, 30,41,59), 0.06);
  text-decoration: none; transition: all 0.3s ease;
  font-size: 0.875rem;
}
.{$p}-team-social-link:hover {
  background: var(--primary, #3b82f6);
  color: var(--primary-contrast, #fff);
  transform: translateY(-2px);
}

CSS;
    }

    // --- Grid Cards ---
    private static function css_grid_cards(string $p): string
    {
        return <<<CSS
.{$p}-team--grid-cards .{$p}-team-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: clamp(24px, 3vw, 32px);
}
.{$p}-team-card {
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-team-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.1);
}
.{$p}-team-card-photo {
  aspect-ratio: 1 / 1; overflow: hidden;
  background: rgba(var(--primary-rgb, 42,125,225), 0.08);
}
.{$p}-team-card-photo img {
  width: 100%; height: 100%; object-fit: cover; display: block;
}
.{$p}-team-card-photo--placeholder {
  display: flex; align-items: center; justify-content: center;
  font-size: 3rem; font-weight: 700;
  color: var(--primary, #3b82f6);
}
.{$p}-team-card-body {
  padding: clamp(16px, 2vw, 24px); text-align: center;
}
.{$p}-team-card-name {
  font-family: var(--font-heading, inherit);
  font-size: 1.125rem; font-weight: 700;
  margin: 0 0 4px 0; color: var(--text, #1e293b);
}
.{$p}-team-card-role {
  font-size: 0.875rem; color: var(--primary, #3b82f6);
  margin: 0 0 12px 0; font-weight: 500;
}
.{$p}-team-card-social {
  display: flex; gap: 8px; justify-content: center;
}

CSS;
    }

    // --- Grid Circular ---
    private static function css_grid_circular(string $p): string
    {
        return <<<CSS
.{$p}-team--grid-circular .{$p}-team-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: clamp(32px, 4vw, 48px);
}
.{$p}-team-member {
  text-align: center;
}
.{$p}-team-avatar {
  width: 160px; height: 160px;
  border-radius: 50%; overflow: hidden;
  margin: 0 auto 20px auto;
  background: rgba(var(--primary-rgb, 42,125,225), 0.08);
  border: 4px solid var(--surface, #fff);
  box-shadow: 0 8px 30px rgba(0,0,0,0.08);
  display: flex; align-items: center; justify-content: center;
}
.{$p}-team-avatar img {
  width: 100%; height: 100%; object-fit: cover; display: block;
}
.{$p}-team-avatar-initial {
  font-size: 3rem; font-weight: 700;
  color: var(--primary, #3b82f6);
}
.{$p}-team-member-name {
  font-family: var(--font-heading, inherit);
  font-size: 1.125rem; font-weight: 700;
  margin: 0 0 4px 0; color: var(--text, #1e293b);
}
.{$p}-team-member-role {
  font-size: 0.875rem; color: var(--primary, #3b82f6);
  margin: 0 0 12px 0; font-weight: 500;
}
.{$p}-team-member-social {
  display: flex; gap: 8px; justify-content: center;
}

CSS;
    }

    // --- Grid Minimal ---
    private static function css_grid_minimal(string $p): string
    {
        return <<<CSS
.{$p}-team--grid-minimal .{$p}-team-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: clamp(24px, 3vw, 32px);
}
.{$p}-team-item {
  padding: clamp(20px, 3vw, 32px);
  border-left: 3px solid var(--primary, #3b82f6);
  transition: background 0.3s ease;
}
.{$p}-team-item:hover {
  background: rgba(var(--primary-rgb, 42,125,225), 0.04);
}
.{$p}-team-item-name {
  font-family: var(--font-heading, inherit);
  font-size: 1.125rem; font-weight: 700;
  margin: 0 0 4px 0; color: var(--text, #1e293b);
}
.{$p}-team-item-role {
  font-size: 0.875rem; color: var(--primary, #3b82f6);
  margin: 0 0 12px 0; font-weight: 500;
}
.{$p}-team-item-bio {
  font-size: 0.875rem; color: var(--text-muted, #64748b);
  line-height: 1.6; margin: 0 0 12px 0;
}
.{$p}-team-item-social {
  display: flex; gap: 8px;
}

CSS;
    }

    // --- Showcase Featured ---
    private static function css_showcase_featured(string $p): string
    {
        return <<<CSS
.{$p}-team-featured {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: clamp(24px, 4vw, 48px);
  align-items: center;
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  padding: clamp(24px, 4vw, 40px);
  box-shadow: 0 8px 30px rgba(0,0,0,0.08);
  margin-bottom: clamp(32px, 5vw, 48px);
}
.{$p}-team-featured-photo {
  width: 100%; aspect-ratio: 1 / 1;
  border-radius: var(--radius, 12px); overflow: hidden;
  background: rgba(var(--primary-rgb, 42,125,225), 0.08);
  display: flex; align-items: center; justify-content: center;
}
.{$p}-team-featured-photo img {
  width: 100%; height: 100%; object-fit: cover; display: block;
}
.{$p}-team-featured-initial {
  font-size: 4rem; font-weight: 700; color: var(--primary, #3b82f6);
}
.{$p}-team-featured-name {
  font-family: var(--font-heading, inherit);
  font-size: 1.5rem; font-weight: 700;
  margin: 0 0 4px 0; color: var(--text, #1e293b);
}
.{$p}-team-featured-role {
  font-size: 1rem; color: var(--primary, #3b82f6);
  margin: 0 0 16px 0; font-weight: 600;
}
.{$p}-team-featured-bio {
  font-size: 0.9375rem; color: var(--text-muted, #64748b);
  line-height: 1.7; margin: 0 0 16px 0;
}
.{$p}-team-featured-social {
  display: flex; gap: 8px;
}
.{$p}-team--showcase-featured .{$p}-team-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: clamp(20px, 3vw, 28px);
}
.{$p}-team--showcase-featured .{$p}-team-card {
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  text-align: center;
  transition: transform 0.3s ease;
}
.{$p}-team--showcase-featured .{$p}-team-card:hover {
  transform: translateY(-4px);
}
.{$p}-team--showcase-featured .{$p}-team-card-photo {
  aspect-ratio: 1 / 1; overflow: hidden;
  background: rgba(var(--primary-rgb, 42,125,225), 0.08);
}
.{$p}-team--showcase-featured .{$p}-team-card-photo img {
  width: 100%; height: 100%; object-fit: cover; display: block;
}
.{$p}-team--showcase-featured .{$p}-team-card-photo--placeholder {
  display: flex; align-items: center; justify-content: center;
  font-size: 2.5rem; font-weight: 700; color: var(--primary, #3b82f6);
}
.{$p}-team--showcase-featured .{$p}-team-card-name {
  font-size: 1rem; font-weight: 700; margin: 12px 0 4px 0;
  color: var(--text, #1e293b);
}
.{$p}-team--showcase-featured .{$p}-team-card-role {
  font-size: 0.8125rem; color: var(--primary, #3b82f6);
  margin: 0 0 12px 0;
}

CSS;
    }

    // --- Showcase Carousel ---
    private static function css_showcase_carousel(string $p): string
    {
        return <<<CSS
.{$p}-team-carousel {
  overflow-x: auto; overflow-y: hidden;
  scroll-snap-type: x mandatory;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
  padding: 0 clamp(16px, 4vw, 48px) 16px;
}
.{$p}-team-carousel-track {
  display: flex;
  gap: clamp(16px, 2vw, 24px);
}
.{$p}-team-carousel-card {
  flex: 0 0 280px; scroll-snap-align: start;
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-team-carousel-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.1);
}
.{$p}-team-carousel-photo {
  aspect-ratio: 3 / 4; overflow: hidden;
  background: rgba(var(--primary-rgb, 42,125,225), 0.08);
}
.{$p}-team-carousel-photo img {
  width: 100%; height: 100%; object-fit: cover; display: block;
}
.{$p}-team-carousel-photo--placeholder {
  display: flex; align-items: center; justify-content: center;
  font-size: 3rem; font-weight: 700; color: var(--primary, #3b82f6);
}
.{$p}-team-carousel-info {
  padding: clamp(16px, 2vw, 20px);
}
.{$p}-team-carousel-name {
  font-family: var(--font-heading, inherit);
  font-size: 1.0625rem; font-weight: 700;
  margin: 0 0 4px 0; color: var(--text, #1e293b);
}
.{$p}-team-carousel-role {
  font-size: 0.8125rem; color: var(--primary, #3b82f6);
  margin: 0 0 8px 0; font-weight: 500;
}
.{$p}-team-carousel-bio {
  font-size: 0.8125rem; color: var(--text-muted, #64748b);
  line-height: 1.5; margin: 0;
  display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;
  overflow: hidden;
}

CSS;
    }

    // --- Showcase Split ---
    private static function css_showcase_split(string $p): string
    {
        return <<<CSS
.{$p}-team--showcase-split {
  padding: 0;
}
.{$p}-team-split {
  display: grid; grid-template-columns: 1fr 1fr;
  min-height: 500px;
}
.{$p}-team-split-image {
  position: relative; overflow: hidden;
  background: rgba(var(--primary-rgb, 42,125,225), 0.08);
}
.{$p}-team-split-image img {
  width: 100%; height: 100%; object-fit: cover; display: block;
}
.{$p}-team-split-content {
  display: flex; flex-direction: column; justify-content: center;
  padding: clamp(32px, 6vw, 80px);
}
.{$p}-team-split-list {
  margin-top: clamp(24px, 3vw, 32px);
  display: flex; flex-direction: column; gap: 16px;
}
.{$p}-team-split-member {
  padding: 16px 0;
  border-bottom: 1px solid rgba(var(--text-rgb, 30,41,59), 0.08);
}
.{$p}-team-split-member:last-child {
  border-bottom: none;
}
.{$p}-team-split-name {
  font-family: var(--font-heading, inherit);
  font-size: 1.0625rem; font-weight: 700;
  margin: 0 0 2px 0; color: var(--text, #1e293b);
}
.{$p}-team-split-role {
  font-size: 0.875rem; color: var(--primary, #3b82f6);
  margin: 0; font-weight: 500;
}

CSS;
    }

    // --- Creative Hover Reveal ---
    private static function css_creative_hover_reveal(string $p): string
    {
        return <<<CSS
.{$p}-team--creative-hover-reveal .{$p}-team-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: clamp(24px, 3vw, 32px);
}
.{$p}-team-hover-card {
  text-align: center;
}
.{$p}-team-hover-photo {
  position: relative; overflow: hidden;
  aspect-ratio: 3 / 4;
  border-radius: var(--radius, 12px);
  background: rgba(var(--primary-rgb, 42,125,225), 0.08);
}
.{$p}-team-hover-photo img {
  width: 100%; height: 100%; object-fit: cover; display: block;
  transition: transform 0.5s ease;
}
.{$p}-team-hover-placeholder {
  width: 100%; height: 100%;
  display: flex; align-items: center; justify-content: center;
  font-size: 4rem; font-weight: 700; color: var(--primary, #3b82f6);
}
.{$p}-team-hover-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(0deg, rgba(0,0,0,0.8) 0%, transparent 60%);
  display: flex; flex-direction: column; justify-content: flex-end;
  padding: clamp(16px, 2vw, 24px);
  opacity: 0; transition: opacity 0.4s ease;
}
.{$p}-team-hover-photo:hover .{$p}-team-hover-overlay {
  opacity: 1;
}
.{$p}-team-hover-photo:hover img {
  transform: scale(1.05);
}
.{$p}-team-hover-bio {
  font-size: 0.875rem; color: rgba(255,255,255,0.9);
  line-height: 1.5; margin: 0 0 12px 0;
}
.{$p}-team-hover-social {
  display: flex; gap: 8px;
}
.{$p}-team-hover-social .{$p}-team-social-link {
  background: rgba(255,255,255,0.15); color: #fff;
}
.{$p}-team-hover-social .{$p}-team-social-link:hover {
  background: var(--primary, #3b82f6);
}
.{$p}-team-hover-name {
  font-family: var(--font-heading, inherit);
  font-size: 1.125rem; font-weight: 700;
  margin: 16px 0 4px 0; color: var(--text, #1e293b);
}
.{$p}-team-hover-role {
  font-size: 0.875rem; color: var(--primary, #3b82f6);
  margin: 0; font-weight: 500;
}

CSS;
    }

    // --- Creative Org Chart ---
    private static function css_creative_org_chart(string $p): string
    {
        return <<<CSS
.{$p}-team-org-leader {
  text-align: center;
  margin-bottom: clamp(24px, 3vw, 40px);
}
.{$p}-team-org-card {
  display: inline-block; text-align: center;
  background: var(--surface, #fff);
  border-radius: var(--radius, 12px);
  padding: clamp(20px, 3vw, 32px);
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$p}-team-org-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.1);
}
.{$p}-team-org-card--leader {
  max-width: 360px;
  border: 2px solid var(--primary, #3b82f6);
}
.{$p}-team-org-photo {
  width: 100px; height: 100px;
  border-radius: 50%; overflow: hidden;
  margin: 0 auto 16px auto;
  background: rgba(var(--primary-rgb, 42,125,225), 0.08);
  display: flex; align-items: center; justify-content: center;
}
.{$p}-team-org-card--leader .{$p}-team-org-photo {
  width: 130px; height: 130px;
}
.{$p}-team-org-photo img {
  width: 100%; height: 100%; object-fit: cover; display: block;
}
.{$p}-team-org-initial {
  font-size: 2.5rem; font-weight: 700; color: var(--primary, #3b82f6);
}
.{$p}-team-org-name {
  font-family: var(--font-heading, inherit);
  font-size: 1.125rem; font-weight: 700;
  margin: 0 0 4px 0; color: var(--text, #1e293b);
}
.{$p}-team-org-role {
  font-size: 0.875rem; color: var(--primary, #3b82f6);
  margin: 0 0 8px 0; font-weight: 500;
}
.{$p}-team-org-bio {
  font-size: 0.875rem; color: var(--text-muted, #64748b);
  line-height: 1.6; margin: 0 0 12px 0;
}
.{$p}-team-org-social {
  display: flex; gap: 8px; justify-content: center;
}
.{$p}-team-org-connector {
  width: 2px; height: 40px;
  background: rgba(var(--primary-rgb, 42,125,225), 0.3);
  margin: 0 auto;
}
.{$p}-team-org-row {
  display: flex; justify-content: center;
  gap: clamp(16px, 3vw, 32px); flex-wrap: wrap;
}
.{$p}-team-org-row .{$p}-team-org-card {
  flex: 0 1 240px;
}

CSS;
    }

    // --- Responsive ---
    private static function css_responsive(string $p): string
    {
        return <<<CSS
@media (max-width: 768px) {
  .{$p}-team-featured {
    grid-template-columns: 1fr !important;
    text-align: center;
  }
  .{$p}-team-featured-photo {
    max-width: 200px; margin: 0 auto;
  }
  .{$p}-team-featured-social {
    justify-content: center;
  }
  .{$p}-team-split {
    grid-template-columns: 1fr !important;
  }
  .{$p}-team-split-image {
    min-height: 300px;
  }
  .{$p}-team-split-content {
    padding: clamp(24px, 5vw, 40px) !important;
  }
  .{$p}-team-carousel-card {
    flex: 0 0 260px;
  }
  .{$p}-team-org-row {
    flex-direction: column; align-items: center;
  }
}

CSS;
    }
}
