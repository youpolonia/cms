<?php
/**
 * AI Theme Builder - Generates responsive HTML layouts using AI
 * Uses unified AI infrastructure from core/ai_content.php
 */

require_once __DIR__ . '/ai_content.php';

/**
 * Generate theme layout using AI or fallback templates
 *
 * @param array $params Layout parameters:
 *   - page_type: Type of page (e.g., 'homepage', 'blog', 'landing')
 *   - layout_style: Layout style (e.g., 'modern', 'classic', 'minimal')
 *   - color_palette: Color scheme (e.g., 'light', 'dark', 'vibrant')
 *   - tone: Content tone (e.g., 'professional', 'casual', 'formal')
 *   - extra_notes: Additional requirements (optional)
 *
 * @return array Result with keys:
 *   - ok: bool - Success status
 *   - html: string|null - Generated HTML
 *   - css: string|null - Generated CSS (reserved for future use)
 *   - error: string|null - Error message if failed
 */
function ai_theme_generate_layout(array $params): array {
    // Normalize input parameters
    $pageType = isset($params['page_type']) ? trim((string)$params['page_type']) : '';
    $layoutStyle = isset($params['layout_style']) ? trim((string)$params['layout_style']) : '';
    $colorPalette = isset($params['color_palette']) ? trim((string)$params['color_palette']) : '';
    $tone = isset($params['tone']) ? trim((string)$params['tone']) : '';
    $extraNotes = isset($params['extra_notes']) ? trim((string)$params['extra_notes']) : '';

    // Build human-readable topic describing the layout requirements
    $topicParts = [];
    if ($pageType !== '') {
        $topicParts[] = 'Page type: ' . $pageType;
    }
    if ($layoutStyle !== '') {
        $topicParts[] = 'Layout style: ' . $layoutStyle;
    }
    if ($colorPalette !== '') {
        $topicParts[] = 'Color palette: ' . $colorPalette;
    }
    if ($tone !== '') {
        $topicParts[] = 'Tone: ' . $tone;
    }
    if ($extraNotes !== '') {
        $topicParts[] = 'Extra notes: ' . $extraNotes;
    }

    $topic = 'Generate a complete, responsive HTML layout for a website page. Include semantic HTML5 elements, proper structure with header, main content area, and footer. Use clean, accessible markup.';
    if (count($topicParts) > 0) {
        $topic .= ' ' . implode('. ', $topicParts) . '.';
    }

    // Check if AI is configured
    $config = ai_config_load();
    $aiEnabled = !empty($config['provider']) && (!empty($config['model']) || !empty($config['base_url']));

    // Try AI generation if enabled
    if ($aiEnabled) {
        try {
            $result = ai_content_generate([
                'topic' => $topic,
                'keywords' => 'html layout responsive semantic',
                'language' => 'en',
                'tone' => $tone !== '' ? $tone : 'professional',
                'length_hint' => 'medium',
            ]);

            // AI generation succeeded
            if ($result['ok'] === true && !empty($result['content'])) {
                return [
                    'ok' => true,
                    'html' => (string)$result['content'],
                    'css' => null,
                    'error' => null,
                ];
            }

            // AI generation failed - log and fall through to fallback
            $errorMsg = $result['error'] ?? 'unknown error';
            error_log('[AI_THEME] ai_content_generate failed: ' . $errorMsg);
        } catch (\Throwable $e) {
            // Exception during AI generation - log and fall through to fallback
            error_log('[AI_THEME] Exception: ' . $e->getMessage());
        }
    }

    // Fallback: Template-based generation (AI not configured or failed)
    try {
        $staticHtml = ai_theme_generate_fallback($pageType, $layoutStyle, $colorPalette);

        return [
            'ok' => true,
            'html' => $staticHtml,
            'css' => null,
            'error' => null,
        ];
    } catch (\Throwable $e) {
        error_log('[AI_THEME] Fallback generation failed: ' . $e->getMessage());

        return [
            'ok' => false,
            'html' => null,
            'css' => null,
            'error' => 'Theme layout generation failed. Please try again later.',
        ];
    }
}

/**
 * Generate fallback layout using templates (no AI)
 *
 * @param string $pageType Page type
 * @param string $layoutStyle Layout style
 * @param string $colorPalette Color palette
 * @return string HTML markup
 */
function ai_theme_generate_fallback(string $pageType, string $layoutStyle, string $colorPalette): string {
    // Normalize inputs
    $pageType = strtolower($pageType);
    $layoutStyle = strtolower($layoutStyle);
    $colorPalette = strtolower($colorPalette);

    // Determine background and text colors based on palette
    $bgColor = '#ffffff';
    $textColor = '#333333';
    $accentColor = '#0066cc';

    if (strpos($colorPalette, 'dark') !== false) {
        $bgColor = '#1a1a1a';
        $textColor = '#e0e0e0';
        $accentColor = '#4a9eff';
    } elseif (strpos($colorPalette, 'vibrant') !== false) {
        $bgColor = '#f0f8ff';
        $textColor = '#222222';
        $accentColor = '#ff6b35';
    }

    // Build layout based on page type
    if ($pageType === 'homepage' || $pageType === 'landing') {
        return ai_theme_fallback_homepage($bgColor, $textColor, $accentColor, $layoutStyle);
    } elseif ($pageType === 'blog' || $pageType === 'article') {
        return ai_theme_fallback_blog($bgColor, $textColor, $accentColor, $layoutStyle);
    } elseif ($pageType === 'contact' || $pageType === 'form') {
        return ai_theme_fallback_contact($bgColor, $textColor, $accentColor, $layoutStyle);
    } else {
        // Generic fallback
        return ai_theme_fallback_generic($bgColor, $textColor, $accentColor, $layoutStyle);
    }
}

/**
 * Generate homepage template
 */
function ai_theme_fallback_homepage(string $bg, string $text, string $accent, string $style): string {
    $containerWidth = ($style === 'minimal') ? '800px' : '1200px';

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: {$bg}; color: {$text}; line-height: 1.6; }
        .container { max-width: {$containerWidth}; margin: 0 auto; padding: 0 20px; }
        header { padding: 40px 0; border-bottom: 1px solid rgba(128,128,128,0.2); }
        header h1 { font-size: 2.5rem; margin-bottom: 10px; color: {$accent}; }
        header p { font-size: 1.1rem; opacity: 0.8; }
        main { padding: 60px 0; }
        .hero { text-align: center; padding: 80px 0; }
        .hero h2 { font-size: 3rem; margin-bottom: 20px; }
        .hero p { font-size: 1.3rem; margin-bottom: 30px; opacity: 0.9; }
        .cta-button { display: inline-block; padding: 15px 40px; background: {$accent}; color: white; text-decoration: none; border-radius: 5px; font-size: 1.1rem; }
        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 60px; }
        .feature { padding: 30px; background: rgba(128,128,128,0.05); border-radius: 8px; }
        .feature h3 { font-size: 1.5rem; margin-bottom: 15px; color: {$accent}; }
        footer { padding: 40px 0; margin-top: 80px; border-top: 1px solid rgba(128,128,128,0.2); text-align: center; opacity: 0.7; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Your Brand</h1>
            <p>Tagline goes here</p>
        </div>
    </header>

    <main>
        <div class="container">
            <section class="hero">
                <h2>Welcome to Your Website</h2>
                <p>Create something amazing with our platform</p>
                <a href="#" class="cta-button">Get Started</a>
            </section>

            <section class="features">
                <div class="feature">
                    <h3>Feature One</h3>
                    <p>Description of your first key feature and how it helps users achieve their goals.</p>
                </div>
                <div class="feature">
                    <h3>Feature Two</h3>
                    <p>Description of your second key feature and the value it provides to your audience.</p>
                </div>
                <div class="feature">
                    <h3>Feature Three</h3>
                    <p>Description of your third key feature and why it matters to your users.</p>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Your Brand. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
HTML;
}

/**
 * Generate blog template
 */
function ai_theme_fallback_blog(string $bg, string $text, string $accent, string $style): string {
    $containerWidth = ($style === 'minimal') ? '700px' : '900px';

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Georgia, 'Times New Roman', serif; background: {$bg}; color: {$text}; line-height: 1.8; }
        .container { max-width: {$containerWidth}; margin: 0 auto; padding: 0 20px; }
        header { padding: 40px 0; border-bottom: 2px solid {$accent}; }
        header h1 { font-size: 2rem; color: {$accent}; }
        main { padding: 60px 0; }
        article { margin-bottom: 60px; }
        article h2 { font-size: 2.2rem; margin-bottom: 15px; }
        article .meta { font-size: 0.9rem; opacity: 0.6; margin-bottom: 20px; }
        article p { margin-bottom: 20px; font-size: 1.1rem; }
        .read-more { color: {$accent}; text-decoration: none; font-weight: bold; }
        aside { margin-top: 60px; padding: 30px; background: rgba(128,128,128,0.05); border-radius: 8px; }
        aside h3 { margin-bottom: 15px; color: {$accent}; }
        footer { padding: 40px 0; margin-top: 80px; border-top: 1px solid rgba(128,128,128,0.2); text-align: center; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Blog</h1>
        </div>
    </header>

    <main>
        <div class="container">
            <article>
                <h2>Article Title</h2>
                <div class="meta">Published on January 1, 2025 by Author Name</div>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                <a href="#" class="read-more">Read more &rarr;</a>
            </article>

            <article>
                <h2>Another Article</h2>
                <div class="meta">Published on December 28, 2024 by Author Name</div>
                <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
                <a href="#" class="read-more">Read more &rarr;</a>
            </article>

            <aside>
                <h3>About This Blog</h3>
                <p>This blog covers topics related to web development, design, and digital strategy.</p>
            </aside>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Blog Name. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
HTML;
}

/**
 * Generate contact page template
 */
function ai_theme_fallback_contact(string $bg, string $text, string $accent, string $style): string {
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: {$bg}; color: {$text}; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 0 20px; }
        header { padding: 40px 0; text-align: center; }
        header h1 { font-size: 2.5rem; color: {$accent}; margin-bottom: 10px; }
        main { padding: 40px 0; }
        form { background: rgba(128,128,128,0.05); padding: 40px; border-radius: 8px; }
        .form-group { margin-bottom: 25px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; }
        input, textarea { width: 100%; padding: 12px; border: 1px solid rgba(128,128,128,0.3); border-radius: 4px; font-size: 1rem; background: {$bg}; color: {$text}; }
        textarea { resize: vertical; min-height: 150px; }
        button { background: {$accent}; color: white; padding: 15px 40px; border: none; border-radius: 5px; font-size: 1.1rem; cursor: pointer; }
        button:hover { opacity: 0.9; }
        footer { padding: 40px 0; margin-top: 60px; text-align: center; opacity: 0.7; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Contact Us</h1>
            <p>We'd love to hear from you</p>
        </div>
    </header>

    <main>
        <div class="container">
            <form>
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>

                <button type="submit">Send Message</button>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Your Brand. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
HTML;
}

/**
 * Generate generic template
 */
function ai_theme_fallback_generic(string $bg, string $text, string $accent, string $style): string {
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: {$bg}; color: {$text}; line-height: 1.6; }
        .container { max-width: 1000px; margin: 0 auto; padding: 0 20px; }
        header { padding: 40px 0; border-bottom: 1px solid rgba(128,128,128,0.2); }
        header h1 { font-size: 2.5rem; color: {$accent}; }
        main { padding: 60px 0; }
        main h2 { font-size: 2rem; margin-bottom: 20px; }
        main p { margin-bottom: 20px; font-size: 1.1rem; }
        .content-section { margin-bottom: 40px; }
        footer { padding: 40px 0; margin-top: 80px; border-top: 1px solid rgba(128,128,128,0.2); text-align: center; opacity: 0.7; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Page Title</h1>
        </div>
    </header>

    <main>
        <div class="container">
            <section class="content-section">
                <h2>Section Heading</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            </section>

            <section class="content-section">
                <h2>Another Section</h2>
                <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Your Brand. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
HTML;
}
