<?php
/**
 * AI Theme Builder — Pipeline (Pro)
 * 
 * 4-step pipeline:
 * 1. Design Brief → theme.json (expanded palette, homepage_sections)
 * 2. HTML Structure → header, footer, home sections (with data-ts)
 * 3. CSS Generation → style.css (production-grade, all templates)
 * 4. Assembly → write files to themes/{slug}/
 * 
 * Lessons from manual theme building applied:
 * - Extended color palette (surface-elevated, surface-card, text-dim, border-hover, etc.)
 * - Premium CSS with proper variables, animations, responsive
 * - Photo-forward card patterns (overlay + standard)
 * - Typography hierarchy (section-label → divider → title → desc)
 * - Theme Studio integration (theme_get, data-ts, generate_studio_css_overrides)
 * - Gallery template support
 */

require_once __DIR__ . '/ai-theme-templates.php';

class AiThemeBuilder
{
    private $ai;
    private string $prompt = '';
    private string $industry = 'business';
    private string $style = 'modern';
    private string $mood = 'light';
    private string $provider = '';
    private string $model = '';
    private string $language = 'English';
    private array $steps = [];
    private array $timings = [];
    private string $slug = '';
    private string $knowledgeBase = '';
    private string $existingThemesContext = '';

    public function __construct(array $options = [])
    {
        $this->provider = $options['provider'] ?? '';
        $this->model    = $options['model'] ?? '';
        $this->language = $options['language'] ?? 'English';

        // Load knowledge base
        $kbPath = CMS_ROOT . '/core/ai-theme-knowledge.md';
        if (file_exists($kbPath)) {
            $this->knowledgeBase = file_get_contents($kbPath);
        }

        $aiCorePath = CMS_ROOT . '/plugins/jessie-theme-builder/includes/ai/class-jtb-ai-core.php';
        if (!file_exists($aiCorePath)) {
            throw new \RuntimeException('AI Core not found. JTB plugin required.');
        }
        require_once $aiCorePath;
        $this->ai = \JessieThemeBuilder\JTB_AI_Core::getInstance();
        if (!$this->ai->isConfigured()) {
            throw new \RuntimeException('No AI provider configured. Add API key in Settings → AI Configuration.');
        }
        if (!empty($this->provider)) {
            $this->ai->setProvider($this->provider);
        }
    }

    /**
     * Extract a section from the knowledge base by heading number
     * e.g. getKB('2') returns "## 2. theme.json..." section
     */
    private function getKB(string ...$sections): string
    {
        if (empty($this->knowledgeBase)) return '';
        $result = [];
        foreach ($sections as $num) {
            if (preg_match('/^(## ' . preg_quote($num, '/') . '\..*?)(?=^## \d+\.|\z)/ms', $this->knowledgeBase, $m)) {
                $result[] = trim($m[1]);
            }
        }
        return implode("\n\n", $result);
    }

    /**
     * Run the full pipeline
     */
    public function generate(array $params = []): array
    {
        $this->prompt   = $params['prompt'] ?? '';
        $this->industry = $params['industry'] ?? 'portfolio';
        $this->style    = $params['style'] ?? 'minimalist';
        $this->mood     = $params['mood'] ?? 'light';

        if (empty($this->prompt)) {
            return ['ok' => false, 'error' => 'Prompt is required'];
        }

        // Scan existing themes so AI knows what NOT to copy
        $this->existingThemesContext = $this->buildExistingThemesContext();

        $modelUsed = $this->model ?: ($this->ai->getProvider() . ' default');

        try {
            // Step 1: Design Brief
            $t0 = microtime(true);
            $this->steps['brief'] = ['status' => 'running'];
            $brief = $this->step1_designBrief();
            $this->steps['brief'] = ['status' => 'done', 'data' => $brief];
            $this->timings['step1'] = (int)((microtime(true) - $t0) * 1000);

            // Step 2: HTML Structure
            $t0 = microtime(true);
            $this->steps['html'] = ['status' => 'running'];
            $html = $this->step2_htmlStructure($brief);
            $this->steps['html'] = ['status' => 'done'];
            $this->timings['step2'] = (int)((microtime(true) - $t0) * 1000);

            // Step 3: CSS Generation
            $t0 = microtime(true);
            $this->steps['css'] = ['status' => 'running'];
            $css = $this->step3_cssGeneration($brief, $html);
            $this->steps['css'] = ['status' => 'done'];
            $this->timings['step3'] = (int)((microtime(true) - $t0) * 1000);

            // Step 4: Assembly
            $t0 = microtime(true);
            $this->steps['assembly'] = ['status' => 'running'];
            $slug = $this->step4_assembly($brief, $html, $css);
            $this->steps['assembly'] = ['status' => 'done'];
            $this->timings['step4'] = (int)((microtime(true) - $t0) * 1000);

            $sectionCount = substr_count($html['home_html'] ?? '', '<section');
            $colorCount = count($brief['colors'] ?? []);
            $fontCount = 0;
            if (!empty($brief['typography']['headingFont'])) $fontCount++;
            if (!empty($brief['typography']['fontFamily'])) $fontCount++;

            return [
                'ok' => true,
                'slug' => $slug,
                'theme_name' => $brief['name'] ?? ucfirst($slug),
                'steps' => $this->steps,
                'timings' => $this->timings,
                'model_used' => $modelUsed,
                'summary' => [
                    'sections' => max($sectionCount, 1),
                    'fonts' => $fontCount,
                    'colors' => $colorCount,
                ],
            ];
        } catch (\Throwable $e) {
            $failedStep = 1;
            if (isset($this->timings['step1'])) $failedStep = 2;
            if (isset($this->timings['step2'])) $failedStep = 3;
            if (isset($this->timings['step3'])) $failedStep = 4;

            return [
                'ok' => false,
                'error' => $e->getMessage(),
                'step' => $failedStep,
                'steps' => $this->steps,
                'timings' => $this->timings,
            ];
        }
    }

    private function queryOptions(array $base = []): array
    {
        if (!empty($this->provider)) $base['provider'] = $this->provider;
        if (!empty($this->model)) $base['model'] = $this->model;

        // Cap max_tokens to model's output limit
        if (!empty($base['max_tokens'])) {
            $base['max_tokens'] = min($base['max_tokens'], $this->getModelOutputLimit());
        }

        return $base;
    }

    /**
     * Get the max output tokens for the current provider+model.
     * Read from ai_settings.json per-model config, with sensible per-provider fallbacks.
     */
    private function getModelOutputLimit(): int
    {
        // Per-provider output limits (API enforced, not context window)
        $providerDefaults = [
            'openai'    => 16000,  // most GPT models support 16k output
            'anthropic' => 16000,  // Claude supports up to 64k but 16k is safe
            'deepseek'  => 8000,   // DeepSeek API hard limit 8192
            'google'    => 8000,   // Gemini default output
            'huggingface' => 4000,
        ];

        $prov = $this->provider ?: $this->ai->getProvider();
        $model = $this->model;

        // Try to read per-model max_output_tokens from config
        $settingsPath = CMS_ROOT . '/config/ai_settings.json';
        if ($model && file_exists($settingsPath)) {
            $settings = @json_decode(file_get_contents($settingsPath), true);
            $modelConf = $settings['providers'][$prov]['models'][$model] ?? [];
            if (!empty($modelConf['max_output_tokens'])) {
                return (int)$modelConf['max_output_tokens'];
            }
            // Some configs store it as output_limit
            if (!empty($modelConf['output_limit'])) {
                return (int)$modelConf['output_limit'];
            }
        }

        return $providerDefaults[$prov] ?? 8000;
    }

    private function languageInstruction(): string
    {
        if ($this->language && strtolower($this->language) !== 'english') {
            return "IMPORTANT: Generate ALL text content (titles, descriptions, headings, paragraphs, button labels) in {$this->language}. Code (HTML tags, CSS, PHP) stays in English.\n\n";
        }
        return '';
    }

    /**
     * Scan existing themes to build "do NOT copy" context
     */
    private function buildExistingThemesContext(): string
    {
        $themesDir = CMS_ROOT . '/themes';
        if (!is_dir($themesDir)) return '';

        $existing = [];
        foreach (glob($themesDir . '/*/theme.json') as $jsonFile) {
            $data = @json_decode(file_get_contents($jsonFile), true);
            if (!$data) continue;
            $slug = basename(dirname($jsonFile));
            $colors = $data['colors'] ?? [];
            $typo = $data['typography'] ?? [];
            $sections = array_column($data['homepage_sections'] ?? [], 'id');
            $existing[] = sprintf(
                '- %s (%s): primary=%s, secondary=%s, bg=%s, fonts=%s/%s, sections=[%s]',
                $data['name'] ?? $slug,
                $slug,
                $colors['primary'] ?? '?',
                $colors['secondary'] ?? '?',
                $colors['background'] ?? '?',
                $typo['headingFont'] ?? '?',
                $typo['fontFamily'] ?? '?',
                implode(',', $sections)
            );
        }

        if (empty($existing)) return '';

        return "EXISTING THEMES (DO NOT duplicate these — use DIFFERENT colors, fonts, layout, sections):\n"
            . implode("\n", $existing) . "\n";
    }

    /* ═══════════════════════════════════════════════════════
       Step 1: Design Brief — Extended palette + sections
       ═══════════════════════════════════════════════════════ */
    private function step1_designBrief(): array
    {
        $langInstr = $this->languageInstruction();
        $kbSchema = $this->getKB('2', '14');
        $existing = $this->existingThemesContext;

        $systemPrompt = <<<PROMPT
{$langInstr}You are a professional web designer. Generate a design system (theme.json) for a website.

REFERENCE — follow this schema exactly:
{$kbSchema}

Return ONLY valid JSON matching the theme.json schema above, PLUS these additional keys:
- "slug": lowercase a-z and hyphens, max 30 chars
- "google_fonts_url": valid Google Fonts URL with both heading and body fonts

CONTEXT:
- Industry: {$this->industry}
- Style: {$this->style}
  minimalist=whitespace+clean, bold=strong contrasts+big type, elegant=serif+refined, playful=rounded+fun colors,
  corporate=structured+professional, brutalist=raw+stark+monospace, retro=vintage textures+nostalgic,
  futuristic=gradients+glassmorphism+neon, organic=curves+natural textures, artdeco=geometric+gold+luxury,
  glassmorphism=frosted glass+blur+transparency, neubrutalism=thick borders+bright fills+quirky,
  editorial=magazine-like typography+whitespace, geometric=bold shapes+patterns+angles
- Mood: {$this->mood}
  light=white/cream bg, dark=near-black bg, colorful=vibrant+saturated, monochrome=grayscale+single accent,
  warm=amber/orange/red tones, cool=blue/teal/cyan tones, pastel=soft muted colors, neon=electric bright on dark,
  earth=browns/greens/natural, luxury=dark+gold/champagne accents

{$existing}
RULES:
- homepage_sections: 4-6 sections for this industry. Always hero (required) + about + articles. Add industry-specific ones.
- Colors: cohesive, accessible. Dark mood → light text (#e-f range), dark bg (#0-2 range). Light → opposite.
- Choose fonts that NO existing theme uses. Pick from the full Google Fonts catalog — be creative.
- Pick a completely DIFFERENT color palette from existing themes. If an existing theme uses warm/amber, use cool/teal. If dark, try light. Be deliberately distinct.
- Create a UNIQUE name and slug — not a variation of existing names.
PROMPT;

        $result = $this->ai->query($this->prompt, $this->queryOptions([
            'system_prompt' => $systemPrompt,
            'max_tokens' => 1500,
            'temperature' => 0.7,
            'json_mode' => true,
        ]));

        $json = $this->extractJson($result);
        if (!$json || empty($json['colors']) || empty($json['typography'])) {
            throw new \RuntimeException('Step 1 failed: Invalid design brief from AI');
        }

        $json['slug'] = preg_replace('/[^a-z0-9-]/', '', strtolower($json['slug'] ?? 'ai-theme'));
        if (empty($json['slug'])) $json['slug'] = 'ai-theme-' . date('His');
        $this->slug = $json['slug'];

        // Ensure homepage_sections exists with at least hero
        if (empty($json['homepage_sections'])) {
            $json['homepage_sections'] = [
                ['id' => 'hero', 'label' => 'Hero', 'icon' => '⭐', 'required' => true],
                ['id' => 'about', 'label' => 'About', 'icon' => '📖'],
                ['id' => 'pages', 'label' => 'Pages', 'icon' => '📋'],
                ['id' => 'articles', 'label' => 'Articles', 'icon' => '📰'],
                ['id' => 'cta', 'label' => 'CTA', 'icon' => '🎯'],
            ];
        }

        return $json;
    }

    /* ═══════════════════════════════════════════════════════
       Step 2: HTML Structure — Delimiter-based (no JSON)
       ═══════════════════════════════════════════════════════ */
    private function step2_htmlStructure(array $brief): array
    {
        $briefJson = json_encode($brief, JSON_PRETTY_PRINT);
        $langInstr = $this->languageInstruction();
        $sectionIds = array_column($brief['homepage_sections'] ?? [], 'id');
        $sectionsDesc = implode(', ', $sectionIds);

        // Load relevant KB sections: PHP functions, variables, data-ts, header, footer, home, section pattern, icons
        $kbRef = $this->getKB('3', '4', '5', '7', '8', '10', '13');

        $existing = $this->existingThemesContext;

        $systemPrompt = <<<PROMPT
{$langInstr}You are an expert frontend developer generating PHP/HTML for a CMS theme.

DESIGN BRIEF:
{$briefJson}

USER REQUEST: {$this->prompt}
INDUSTRY: {$this->industry} | STYLE: {$this->style}

{$existing}
KNOWLEDGE BASE — follow these structural patterns (IDs, classes, PHP functions) EXACTLY, but invent your OWN visual layout:
{$kbRef}

OUTPUT FORMAT — use these exact delimiters (each on its own line):
===HEADER===
(header HTML/PHP code here)
===FOOTER===
(footer HTML/PHP code here)
===HOME===
(home sections HTML/PHP code here)
===END===

TASK:
1. HEADER — Follow section 7 structure exactly. Keep required IDs and classes.
2. FOOTER — Follow section 8 structure. Use 3-4 columns appropriate for {$this->industry}. Include social links.
3. HOME — Generate sections for: {$sectionsDesc}
   - Each section: follow the Section PHP Pattern (section 10)
   - Hero: .hero with .hero-bg, .hero-overlay, .hero-content, data-ts bindings (section 5)
   - Pages loop + Articles loop: follow exact PHP patterns from section 10
   - ALL text: theme_get() + data-ts attributes
   - Add data-animate to animated elements
   - Use Font Awesome icons (section 13)

CRITICAL:
- Output raw PHP/HTML code between delimiters — NO JSON wrapping, NO markdown backticks
- Write real PHP: <?php ... ?> (no escaping needed — this is raw code output)
- NO inline styles except dynamic background images

UNIQUENESS — THIS IS THE MOST IMPORTANT RULE:
- DO NOT copy or mimic existing themes listed above. Your layout MUST be visibly different.
- Vary the hero style: split-screen, video bg, animated text, minimal center, asymmetric, illustrated
- Vary card patterns: standard grid, masonry, overlapping, horizontal cards, list view, magazine layout
- Vary the page rhythm: alternating left-right, full-bleed sections, narrow centered, mixed widths
- Use creative section dividers: SVG waves, diagonal clips, gradient fades, geometric shapes
- Each section needs a DISTINCTIVE visual approach — not just "container > heading > grid > cards"
PROMPT;

        $result = $this->ai->query("Generate the HTML structure", $this->queryOptions([
            'system_prompt' => $systemPrompt,
            'max_tokens' => 8000,
            'temperature' => 0.7,
        ]));

        if (!$result['ok'] || empty($result['text'])) {
            throw new \RuntimeException('Step 2 failed: AI returned no output — ' . ($result['error'] ?? 'unknown'));
        }

        $parsed = $this->extractDelimited($result['text'], ['HEADER', 'FOOTER', 'HOME']);

        if (empty($parsed['HEADER']) || empty($parsed['HOME'])) {
            // Log raw response for debugging
            $debugPath = '/tmp/aitb-step2-debug-' . date('His') . '.txt';
            @file_put_contents($debugPath, $result['text']);
            throw new \RuntimeException('Step 2 failed: Could not parse HTML sections from AI response. Raw saved to ' . $debugPath);
        }

        return [
            'header_html' => $parsed['HEADER'],
            'footer_html' => $parsed['FOOTER'] ?? '',
            'home_html'   => $parsed['HOME'],
        ];
    }

    /**
     * Parse delimiter-separated output: ===SECTION=== ... ===NEXT=== ... ===END===
     */
    private function extractDelimited(string $text, array $sections): array
    {
        $result = [];

        // Strip markdown code fences if AI wrapped everything
        $text = preg_replace('/^```(?:php|html)?\s*$/m', '', $text);

        for ($i = 0; $i < count($sections); $i++) {
            $start = $sections[$i];
            $end = $sections[$i + 1] ?? 'END';

            // Match ===SECTION=== ... up to ===NEXT=== (flexible whitespace)
            $pattern = '/===\s*' . preg_quote($start, '/') . '\s*===\s*\n(.*?)(?=\n===\s*' . preg_quote($end, '/') . '\s*===|\z)/s';
            if (preg_match($pattern, $text, $m)) {
                $result[$start] = trim($m[1]);
            }
        }

        return $result;
    }

    /* ═══════════════════════════════════════════════════════
       Step 3: CSS Generation — Knowledge-base driven
       ═══════════════════════════════════════════════════════ */
    private function step3_cssGeneration(array $brief, array $html): string
    {
        $briefJson = json_encode($brief, JSON_PRETTY_PRINT);
        $cleanHtml = strip_tags(
            ($html['header_html'] ?? '') . ($html['home_html'] ?? '') . ($html['footer_html'] ?? ''),
            '<header><footer><section><div><nav><a><button><span><h1><h2><h3><h4><p><ul><li><img><i><form><input><blockquote><cite><main><article>'
        );

        // Load CSS requirements + quality standards from KB
        $kbCss = $this->getKB('11', '14');

        $systemPrompt = <<<PROMPT
You are a senior CSS developer. Generate a COMPLETE, production-grade stylesheet.

DESIGN BRIEF:
{$briefJson}

HTML TO STYLE:
{$cleanHtml}

STYLE: {$this->style} | MOOD: {$this->mood}

CSS REQUIREMENTS — follow ALL rules from this reference:
{$kbCss}

Return ONLY CSS code. No markdown, no backticks, no explanation.

ADDITIONAL RULES:
- Style ALL classes from the HTML above — every class must have CSS rules
- Create a UNIQUE visual identity — not generic Bootstrap-like. Match {$this->style} + {$this->mood}.
- Minimum 600 lines of production-ready CSS
- Test mentally: would this look premium on a real website?
PROMPT;

        $result = $this->ai->query("Generate the complete CSS", $this->queryOptions([
            'system_prompt' => $systemPrompt,
            'max_tokens' => 16000,
            'temperature' => 0.5,
        ]));

        if (!$result['ok'] || empty($result['text'])) {
            throw new \RuntimeException('Step 3 failed: ' . ($result['error'] ?? 'No CSS generated'));
        }

        $css = $result['text'];
        $css = preg_replace('/^```(?:css)?\s*/m', '', $css);
        $css = preg_replace('/```\s*$/m', '', $css);
        $css = trim($css);

        if (strlen($css) < 500) {
            throw new \RuntimeException('Step 3 failed: CSS too short (' . strlen($css) . ' chars)');
        }

        return $css;
    }

    /* ═══════════════════════════════════════════════════════
       Step 4: Assembly — Write theme files
       ═══════════════════════════════════════════════════════ */
    private function step4_assembly(array $brief, array $html, string $css): string
    {
        $slug = $this->slug;
        $themeDir = CMS_ROOT . '/themes/' . $slug;

        // Create directories
        foreach (['', '/templates', '/assets/css', '/assets/js', '/content'] as $dir) {
            $path = $themeDir . $dir;
            if (!is_dir($path)) mkdir($path, 0755, true);
        }

        // Google Fonts link
        $fontsUrl = $brief['google_fonts_url'] ?? '';
        $fontsLink = '';
        if ($fontsUrl) {
            $fontsLink = '<link rel="preconnect" href="https://fonts.googleapis.com">'
                . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
                . '<link href="' . htmlspecialchars($fontsUrl, ENT_QUOTES) . '" rel="stylesheet">';
        }

        // 1. theme.json — with homepage_sections
        $themeJson = [
            'name' => $brief['name'] ?? ucfirst($slug),
            'description' => $brief['description'] ?? 'AI-generated theme',
            'version' => '1.0.0',
            'author' => 'AI Theme Builder',
            'supports' => [
                'theme-builder' => true,
                'custom-header' => true,
                'custom-footer' => true,
                'custom-colors' => true,
            ],
            'options' => [
                'show_header' => true,
                'show_footer' => true,
                'body_background' => $brief['colors']['background'] ?? '#ffffff',
                'preload_fonts' => true,
            ],
            'colors' => $brief['colors'] ?? [],
            'typography' => $brief['typography'] ?? [],
            'buttons' => $brief['buttons'] ?? [],
            'layout' => $brief['layout'] ?? [],
            'homepage_sections' => $brief['homepage_sections'] ?? [
                ['id' => 'hero', 'label' => 'Hero', 'icon' => '⭐', 'required' => true],
                ['id' => 'about', 'label' => 'About', 'icon' => '📖'],
                ['id' => 'pages', 'label' => 'Pages', 'icon' => '📋'],
                ['id' => 'articles', 'label' => 'Articles', 'icon' => '📰'],
                ['id' => 'cta', 'label' => 'CTA', 'icon' => '🎯'],
            ],
        ];
        file_put_contents($themeDir . '/theme.json', json_encode($themeJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // 2. layout.php
        file_put_contents(
            $themeDir . '/layout.php',
            ai_theme_build_layout($fontsLink, $html['header_html'] ?? '', $html['footer_html'] ?? '')
        );

        // 3. templates/home.php — AI-generated content
        file_put_contents($themeDir . '/templates/home.php', $html['home_html'] ?? '');

        // 4. templates/page.php
        file_put_contents($themeDir . '/templates/page.php', ai_theme_page_template());

        // 5. templates/article.php
        file_put_contents($themeDir . '/templates/article.php', ai_theme_article_template());

        // 6. templates/articles.php
        file_put_contents($themeDir . '/templates/articles.php', ai_theme_articles_template());

        // 7. templates/gallery.php
        file_put_contents($themeDir . '/templates/gallery.php', ai_theme_gallery_template());

        // 8. templates/404.php
        file_put_contents($themeDir . '/templates/404.php', ai_theme_404_template());

        // 9. assets/css/style.css
        file_put_contents($themeDir . '/assets/css/style.css', $css);

        // 10. assets/js/main.js
        file_put_contents($themeDir . '/assets/js/main.js', ai_theme_main_js());

        // Set ownership
        $this->chownRecursive($themeDir);

        return $slug;
    }

    /**
     * Activate a theme by slug
     */
    public function activateTheme(string $slug): bool
    {
        $themeDir = CMS_ROOT . '/themes/' . $slug;
        if (!is_dir($themeDir) || !file_exists($themeDir . '/theme.json')) {
            return false;
        }

        try {
            $pdo = \core\Database::connection();
            $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE `key` = 'active_theme'");
            $stmt->execute([$slug]);
            if ($stmt->rowCount() === 0) {
                $stmt = $pdo->prepare("INSERT INTO settings (`key`, value) VALUES ('active_theme', ?)");
                $stmt->execute([$slug]);
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Delete a generated theme by slug
     */
    public function deleteTheme(string $slug): bool
    {
        $themeDir = CMS_ROOT . '/themes/' . $slug;
        if (!is_dir($themeDir)) return false;

        $jsonFile = $themeDir . '/theme.json';
        if (file_exists($jsonFile)) {
            $data = @json_decode(file_get_contents($jsonFile), true);
            if (($data['author'] ?? '') !== 'AI Theme Builder') return false;
        }

        try {
            $pdo = \core\Database::connection();
            $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'active_theme'");
            $stmt->execute();
            if ($stmt->fetchColumn() === $slug) return false;
        } catch (\Throwable $e) {
            return false;
        }

        $this->deleteDir($themeDir);
        return true;
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->deleteDir($path) : @unlink($path);
        }
        @rmdir($dir);
    }

    private function extractJson(array $result): ?array
    {
        if (!$result['ok']) return null;

        if (!empty($result['json']) && is_array($result['json'])) return $result['json'];

        $text = $result['text'] ?? '';
        $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
        $text = preg_replace('/```\s*$/m', '', $text);

        if (preg_match('/\{[\s\S]*\}/s', $text, $m)) {
            $parsed = json_decode($m[0], true);
            if (is_array($parsed)) return $parsed;
        }

        return null;
    }

    private function chownRecursive(string $path): void
    {
        @chown($path, 'www-data');
        @chgrp($path, 'www-data');
        if (is_dir($path)) {
            foreach (scandir($path) as $item) {
                if ($item === '.' || $item === '..') continue;
                $this->chownRecursive($path . '/' . $item);
            }
        }
    }
}
