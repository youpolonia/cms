<?php
/**
 * AI Theme Builder — Pipeline (Pro)
 * 
 * 4-step pipeline:
 * 1. Design Brief → theme.json
 * 2. HTML Structure → header, footer, home sections (with data-ts)
 * 3. CSS Generation → style.css
 * 4. Assembly → write files to themes/{slug}/
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

    /**
     * @param array $options  Optional: provider, model, language
     */
    public function __construct(array $options = [])
    {
        $this->provider = $options['provider'] ?? '';
        $this->model    = $options['model'] ?? '';
        $this->language = $options['language'] ?? 'English';

        // Load AI Core
        $aiCorePath = CMS_ROOT . '/plugins/jessie-theme-builder/includes/ai/class-jtb-ai-core.php';
        if (!file_exists($aiCorePath)) {
            throw new \RuntimeException('AI Core not found. JTB plugin required.');
        }
        require_once $aiCorePath;
        $this->ai = \JessieThemeBuilder\JTB_AI_Core::getInstance();
        if (!$this->ai->isConfigured()) {
            throw new \RuntimeException('No AI provider configured. Add API key in Settings → AI Configuration.');
        }

        // Set provider if specified
        if (!empty($this->provider)) {
            $this->ai->setProvider($this->provider);
        }
    }

    /**
     * Run the full pipeline
     * @param array $params  prompt, industry, style, mood
     * @return array {ok, slug, theme_name, steps, timings, summary, model_used, error?}
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

        // Determine display model name for frontend
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

            // Build summary
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
            // Determine which step failed
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

    /**
     * Build query options with provider/model overrides
     */
    private function queryOptions(array $base = []): array
    {
        if (!empty($this->provider)) {
            $base['provider'] = $this->provider;
        }
        if (!empty($this->model)) {
            $base['model'] = $this->model;
        }
        return $base;
    }

    /**
     * Language instruction to prepend to system prompts
     */
    private function languageInstruction(): string
    {
        if ($this->language && strtolower($this->language) !== 'english') {
            return "IMPORTANT: Generate ALL text content (titles, descriptions, headings, paragraphs, button labels, placeholder text) in {$this->language}. Only code (HTML tags, CSS, PHP) should remain in English.\n\n";
        }
        return '';
    }

    /**
     * Step 1: Generate design brief (theme.json)
     */
    private function step1_designBrief(): array
    {
        $langInstr = $this->languageInstruction();

        $systemPrompt = <<<PROMPT
{$langInstr}You are a professional web designer. Generate a design brief (theme.json) for a website based on the user's description.

Return ONLY valid JSON with this exact structure:
{
  "name": "Theme Name",
  "description": "One-line description",
  "slug": "theme-slug-lowercase",
  "colors": {
    "primary": "#hex",
    "secondary": "#hex",
    "accent": "#hex",
    "background": "#hex",
    "surface": "#hex",
    "text": "#hex",
    "text_muted": "#hex",
    "border": "#hex"
  },
  "typography": {
    "headingFont": "Google Font Name",
    "fontFamily": "Google Font Name",
    "headingWeight": "700",
    "fontSize": "16",
    "lineHeight": "1.6"
  },
  "buttons": {
    "borderRadius": "8",
    "paddingX": "24",
    "paddingY": "12",
    "fontWeight": "600",
    "uppercase": false,
    "shadow": true
  },
  "layout": {
    "containerWidth": "1200",
    "sectionSpacing": "80",
    "borderRadius": "12"
  },
  "google_fonts_url": "https://fonts.googleapis.com/css2?family=Font+Name:wght@400;500;600;700&family=Other+Font:wght@300;400;500;600&display=swap"
}

RULES:
- slug: lowercase, only a-z and hyphens, max 30 chars
- Colors: must create a cohesive, accessible palette. Mood: {$this->mood}
- Style: {$this->style}
- Industry: {$this->industry}
- google_fonts_url: valid Google Fonts URL with the exact fonts you chose
- Choose fonts that match the industry and style
PROMPT;

        $result = $this->ai->query($this->prompt, $this->queryOptions([
            'system_prompt' => $systemPrompt,
            'max_tokens' => 1000,
            'temperature' => 0.7,
            'json_mode' => true,
        ]));

        $json = $this->extractJson($result);
        if (!$json || empty($json['colors']) || empty($json['typography'])) {
            throw new \RuntimeException('Step 1 failed: Invalid design brief from AI');
        }

        // Sanitize slug
        $json['slug'] = preg_replace('/[^a-z0-9-]/', '', strtolower($json['slug'] ?? 'ai-theme'));
        if (empty($json['slug'])) $json['slug'] = 'ai-theme-' . date('His');
        $this->slug = $json['slug'];

        return $json;
    }

    /**
     * Step 2: Generate HTML structure with data-ts attributes
     */
    private function step2_htmlStructure(array $brief): array
    {
        $briefJson = json_encode($brief, JSON_PRETTY_PRINT);
        $siteName = get_site_name();
        $langInstr = $this->languageInstruction();

        $systemPrompt = <<<PROMPT
{$langInstr}You are an expert frontend developer. Generate HTML for a website theme based on the design brief below.

DESIGN BRIEF:
{$briefJson}

USER REQUEST: {$this->prompt}
INDUSTRY: {$this->industry}
STYLE: {$this->style}

Return ONLY valid JSON with these 3 keys:
{
  "header_html": "...",
  "footer_html": "...",
  "home_html": "..."
}

CRITICAL RULES FOR header_html:
- Wrap in <header class="site-header" id="siteHeader">
- Logo: use this exact PHP: <?php if (\$tsLogo): ?><img src="<?= esc(\$tsLogo) ?>" alt="<?= esc(theme_get('brand.site_name', \$siteName)) ?>"><?php else: ?><span class="logo-text" data-ts="brand.site_name"><?= esc(theme_get('brand.site_name', \$siteName)) ?></span><?php endif; ?>
- Wrap logo in: <a href="/" class="header-logo" data-ts="brand.logo">...</a>
- Menu: <?= render_menu('header', ['class' => 'nav-links', 'link_class' => 'nav-link', 'wrap' => false]) ?>
- CTA button with data-ts="header.cta_text" data-ts-href="header.cta_link" and theme_get() for values
- Mobile toggle: <button class="mobile-toggle" id="mobileToggle" aria-label="Menu"><span></span><span></span><span></span></button>

CRITICAL RULES FOR footer_html:
- Wrap in <footer class="site-footer">
- Reuse logo pattern from header
- Footer description with data-ts="footer.description" and theme_get()
- Copyright with data-ts="footer.copyright" and theme_get()
- Footer menu: <?= render_menu('footer', ['class' => 'footer-links', 'link_class' => 'footer-link', 'wrap' => false]) ?>
- Social links placeholders

CRITICAL RULES FOR home_html:
- Hero section with: data-ts="hero.headline", data-ts="hero.subtitle", data-ts="hero.btn_text", data-ts-href="hero.btn_link", data-ts-bg="hero.bg_image"
- ALL text must use theme_get(): <?= esc(theme_get('hero.headline', 'Default Text')) ?>
- ALL images: check theme_get() and apply inline style when set
- Include 3-5 content sections appropriate for {$this->industry}
- Each section header should have data-ts attributes (e.g. data-ts="about.title")
- Use Font Awesome icons: <i class="fas fa-..."></i>
- Pages loop: <?php if (!empty(\$pages)): ?> ... <?php foreach (\$pages as \$p): ?> ... <?php endforeach; ?> ... <?php endif; ?>
- Articles loop: similar pattern with \$articles
- Use semantic, BEM-like CSS class names
- Add data-animate to animated elements

IMPORTANT: Escape all PHP properly inside JSON strings. Use \\<?php and \\?> for PHP tags.
Generate content relevant to: {$this->prompt}
PROMPT;

        $result = $this->ai->query("Generate the HTML structure for this theme", $this->queryOptions([
            'system_prompt' => $systemPrompt,
            'max_tokens' => 8000,
            'temperature' => 0.7,
            'json_mode' => true,
        ]));

        $json = $this->extractJson($result);
        if (!$json || empty($json['header_html']) || empty($json['home_html'])) {
            throw new \RuntimeException('Step 2 failed: Invalid HTML from AI');
        }

        // Unescape PHP tags that AI might have escaped
        foreach (['header_html', 'footer_html', 'home_html'] as $key) {
            if (!empty($json[$key])) {
                $json[$key] = str_replace(['\\<?php', '\\?>'], ['<?php', '?>'], $json[$key]);
            }
        }

        return $json;
    }

    /**
     * Step 3: Generate CSS
     */
    private function step3_cssGeneration(array $brief, array $html): string
    {
        $briefJson = json_encode($brief, JSON_PRETTY_PRINT);
        // Send HTML but strip PHP tags for CSS generation
        $cleanHtml = strip_tags($html['header_html'] . $html['home_html'] . $html['footer_html'], '<header><footer><section><div><nav><a><button><span><h1><h2><h3><h4><p><ul><li><img><i><form><input><blockquote><cite><main><article>');

        $systemPrompt = <<<PROMPT
You are an expert CSS developer. Generate a COMPLETE stylesheet for a website theme.

DESIGN BRIEF:
{$briefJson}

HTML STRUCTURE (classes to style):
{$cleanHtml}

USER REQUEST: {$this->prompt}
STYLE: {$this->style}
MOOD: {$this->mood}

Return ONLY the CSS code (no markdown, no backticks, no explanation).

REQUIREMENTS:
1. Start with :root {} containing CSS variables matching the design brief colors
2. Reset/base styles (box-sizing, margins, fonts)
3. Typography scale (h1-h6, p, links)
4. Container (.container) with max-width from brief
5. Header styles (.site-header) — sticky, transparent→solid on scroll, mobile responsive
6. Hero section — full viewport height or large, dramatic
7. Content sections — proper spacing, grid layouts
8. Cards — shadows, hover effects, transitions
9. Footer — dark or contrasting background
10. Buttons (.btn, .btn-primary, .btn-outline) matching the design brief
11. Mobile responsive: @media (max-width: 768px) and (max-width: 480px)
12. Navigation: .nav-links, .nav-link, mobile .open state
13. Animations: .fade-in, .fade-in-up, [data-animate], .visible
14. .prose class for article/page content
15. Dark/light appropriate for mood: {$this->mood}
16. Scrolled header: .site-header.scrolled
17. Mobile toggle: .mobile-toggle, .mobile-toggle.active spans animation

CSS must be complete and production-ready. Minimum 800 lines for a polished theme.
DO NOT use Tailwind or any framework. Pure CSS only.
PROMPT;

        $result = $this->ai->query("Generate the complete CSS stylesheet", $this->queryOptions([
            'system_prompt' => $systemPrompt,
            'max_tokens' => 16000,
            'temperature' => 0.6,
        ]));

        if (!$result['ok'] || empty($result['text'])) {
            throw new \RuntimeException('Step 3 failed: ' . ($result['error'] ?? 'No CSS generated'));
        }

        $css = $result['text'];
        // Strip markdown code fences if present
        $css = preg_replace('/^```(?:css)?\s*/m', '', $css);
        $css = preg_replace('/```\s*$/m', '', $css);
        $css = trim($css);

        if (strlen($css) < 500) {
            throw new \RuntimeException('Step 3 failed: CSS too short (' . strlen($css) . ' chars)');
        }

        return $css;
    }

    /**
     * Step 4: Assemble theme files and write to disk
     */
    private function step4_assembly(array $brief, array $html, string $css): string
    {
        $slug = $this->slug;
        $themeDir = CMS_ROOT . '/themes/' . $slug;

        // Create directories
        if (!is_dir($themeDir)) mkdir($themeDir, 0755, true);
        if (!is_dir($themeDir . '/templates')) mkdir($themeDir . '/templates', 0755, true);
        if (!is_dir($themeDir . '/assets/css')) mkdir($themeDir . '/assets/css', 0755, true);
        if (!is_dir($themeDir . '/assets/js')) mkdir($themeDir . '/assets/js', 0755, true);

        // Google Fonts link
        $fontsUrl = $brief['google_fonts_url'] ?? '';
        $fontsLink = $fontsUrl 
            ? '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="' . htmlspecialchars($fontsUrl) . '" rel="stylesheet">'
            : '';

        // 1. theme.json
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
            'colors' => $brief['colors'] ?? [],
            'typography' => $brief['typography'] ?? [],
            'buttons' => $brief['buttons'] ?? [],
            'layout' => $brief['layout'] ?? [],
        ];
        file_put_contents($themeDir . '/theme.json', json_encode($themeJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // 2. layout.php
        $layoutContent = ai_theme_build_layout(
            $fontsLink,
            $html['header_html'] ?? '',
            $html['footer_html'] ?? ''
        );
        file_put_contents($themeDir . '/layout.php', $layoutContent);

        // 3. templates/home.php
        file_put_contents($themeDir . '/templates/home.php', $html['home_html'] ?? '');

        // 4. templates/page.php
        file_put_contents($themeDir . '/templates/page.php', ai_theme_page_template());

        // 5. templates/article.php
        file_put_contents($themeDir . '/templates/article.php', ai_theme_article_template());

        // 6. templates/articles.php
        file_put_contents($themeDir . '/templates/articles.php', ai_theme_articles_template());

        // 7. templates/404.php
        file_put_contents($themeDir . '/templates/404.php', ai_theme_404_template());

        // 8. assets/css/style.css
        file_put_contents($themeDir . '/assets/css/style.css', $css);

        // 9. assets/js/main.js
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
        if (!is_dir($themeDir)) {
            return false;
        }

        // Safety: only delete AI-generated themes
        $jsonFile = $themeDir . '/theme.json';
        if (file_exists($jsonFile)) {
            $data = @json_decode(file_get_contents($jsonFile), true);
            if (($data['author'] ?? '') !== 'AI Theme Builder') {
                return false;
            }
        }

        // Check it's not the active theme
        try {
            $pdo = \core\Database::connection();
            $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'active_theme'");
            $stmt->execute();
            $active = $stmt->fetchColumn();
            if ($active === $slug) {
                return false; // Cannot delete active theme
            }
        } catch (\Throwable $e) {
            // If we can't check, refuse to delete
            return false;
        }

        // Recursively delete
        $this->deleteDir($themeDir);
        return true;
    }

    /**
     * Recursively delete a directory
     */
    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->deleteDir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    /**
     * Extract JSON from AI response
     */
    private function extractJson(array $result): ?array
    {
        if (!$result['ok']) return null;

        // Try json field first
        if (!empty($result['json']) && is_array($result['json'])) {
            return $result['json'];
        }

        // Try parsing from text
        $text = $result['text'] ?? '';
        // Strip markdown fences
        $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
        $text = preg_replace('/```\s*$/m', '', $text);

        // Find JSON object
        if (preg_match('/\{[\s\S]*\}/s', $text, $m)) {
            $parsed = json_decode($m[0], true);
            if (is_array($parsed)) return $parsed;
        }

        return null;
    }

    /**
     * Recursively set file ownership to www-data
     */
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
