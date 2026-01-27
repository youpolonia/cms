<?php
/**
 * Theme Integration
 * Integrates JTB templates with the frontend theme
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Theme_Integration
{
    private static bool $initialized = false;
    private static ?array $headerTemplate = null;
    private static ?array $footerTemplate = null;
    private static ?array $bodyTemplate = null;

    /**
     * Initialize theme integration
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        // Load templates for current request
        self::loadTemplates();

        self::$initialized = true;
    }

    /**
     * Load templates for current request
     */
    private static function loadTemplates(): void
    {
        self::$headerTemplate = JTB_Template_Matcher::getHeader();
        self::$footerTemplate = JTB_Template_Matcher::getFooter();
        self::$bodyTemplate = JTB_Template_Matcher::getBody();
    }

    /**
     * Check if JTB has a header template
     */
    public static function hasHeader(): bool
    {
        self::init();
        return self::$headerTemplate !== null;
    }

    /**
     * Check if JTB has a footer template
     */
    public static function hasFooter(): bool
    {
        self::init();
        return self::$footerTemplate !== null;
    }

    /**
     * Check if JTB has a body template
     */
    public static function hasBody(): bool
    {
        self::init();
        return self::$bodyTemplate !== null;
    }

    /**
     * Render header template
     */
    public static function renderHeader(): string
    {
        self::init();

        if (!self::$headerTemplate) {
            return '';
        }

        return self::renderTemplate(self::$headerTemplate);
    }

    /**
     * Render footer template
     */
    public static function renderFooter(): string
    {
        self::init();

        if (!self::$footerTemplate) {
            return '';
        }

        return self::renderTemplate(self::$footerTemplate);
    }

    /**
     * Render body template with content
     * The body template can include a {{content}} placeholder for post content
     */
    public static function renderBody(string $content = ''): string
    {
        self::init();

        if (!self::$bodyTemplate) {
            return $content;
        }

        $rendered = self::renderTemplate(self::$bodyTemplate);

        // Replace content placeholder
        if (strpos($rendered, '{{content}}') !== false) {
            $rendered = str_replace('{{content}}', $content, $rendered);
        } elseif (strpos($rendered, '{{ content }}') !== false) {
            $rendered = str_replace('{{ content }}', $content, $rendered);
        }

        return $rendered;
    }

    /**
     * Render a template
     */
    private static function renderTemplate(array $template): string
    {
        if (empty($template['content'])) {
            return '';
        }

        $content = $template['content'];
        if (is_string($content)) {
            $content = json_decode($content, true);
        }

        // Render using JTB_Renderer
        return JTB_Renderer::render($content);
    }

    /**
     * Get combined CSS for all active templates
     */
    public static function getCombinedCss(): string
    {
        self::init();

        $css = '';

        // Use cached CSS if available
        if (self::$headerTemplate && !empty(self::$headerTemplate['css_cache'])) {
            $css .= self::$headerTemplate['css_cache'];
        } elseif (self::$headerTemplate) {
            $css .= self::generateTemplateCss(self::$headerTemplate);
        }

        if (self::$footerTemplate && !empty(self::$footerTemplate['css_cache'])) {
            $css .= self::$footerTemplate['css_cache'];
        } elseif (self::$footerTemplate) {
            $css .= self::generateTemplateCss(self::$footerTemplate);
        }

        if (self::$bodyTemplate && !empty(self::$bodyTemplate['css_cache'])) {
            $css .= self::$bodyTemplate['css_cache'];
        } elseif (self::$bodyTemplate) {
            $css .= self::generateTemplateCss(self::$bodyTemplate);
        }

        return $css;
    }

    /**
     * Generate CSS for a template
     */
    private static function generateTemplateCss(array $template): string
    {
        if (empty($template['content'])) {
            return '';
        }

        $content = $template['content'];
        if (is_string($content)) {
            $content = json_decode($content, true);
        }

        return JTB_Renderer::generateCss($content);
    }

    /**
     * Get header template data
     */
    public static function getHeaderTemplate(): ?array
    {
        self::init();
        return self::$headerTemplate;
    }

    /**
     * Get footer template data
     */
    public static function getFooterTemplate(): ?array
    {
        self::init();
        return self::$footerTemplate;
    }

    /**
     * Get body template data
     */
    public static function getBodyTemplate(): ?array
    {
        self::init();
        return self::$bodyTemplate;
    }

    /**
     * Output header if JTB has one
     * Use in theme: if (JTB_Theme_Integration::outputHeader()) { // skip default header }
     */
    public static function outputHeader(): bool
    {
        if (self::hasHeader()) {
            echo self::renderHeader();
            return true;
        }
        return false;
    }

    /**
     * Output footer if JTB has one
     */
    public static function outputFooter(): bool
    {
        if (self::hasFooter()) {
            echo self::renderFooter();
            return true;
        }
        return false;
    }

    /**
     * Output body wrapper if JTB has one
     */
    public static function outputBody(string $content = ''): bool
    {
        if (self::hasBody()) {
            echo self::renderBody($content);
            return true;
        }
        return false;
    }

    /**
     * Output CSS in head
     */
    public static function outputCss(): void
    {
        $css = self::getCombinedCss();
        if (!empty($css)) {
            echo '<style id="jtb-theme-css">' . $css . '</style>';
        }
    }

    /**
     * Get full page HTML with JTB templates
     * Useful for completely overriding the theme
     */
    public static function getFullPage(string $pageContent = '', string $title = ''): string
    {
        $pluginUrl = '/plugins/jessie-theme-builder';

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="<?= $pluginUrl ?>/assets/css/frontend.css">
    <link rel="stylesheet" href="<?= $pluginUrl ?>/assets/css/animations.css">
    <?php self::outputCss(); ?>
</head>
<body class="jtb-page">
    <?php if (self::hasHeader()): ?>
    <header class="jtb-site-header">
        <?= self::renderHeader() ?>
    </header>
    <?php endif; ?>

    <main class="jtb-site-main">
        <?php if (self::hasBody()): ?>
            <?= self::renderBody($pageContent) ?>
        <?php else: ?>
            <?= $pageContent ?>
        <?php endif; ?>
    </main>

    <?php if (self::hasFooter()): ?>
    <footer class="jtb-site-footer">
        <?= self::renderFooter() ?>
    </footer>
    <?php endif; ?>
</body>
</html>
        <?php
        return ob_get_clean();
    }

    /**
     * Check if JTB should override the template
     */
    public static function shouldOverride(): bool
    {
        self::init();

        // Override if we have any active template
        return self::hasHeader() || self::hasFooter() || self::hasBody();
    }

    /**
     * Reset state (for testing)
     */
    public static function reset(): void
    {
        self::$initialized = false;
        self::$headerTemplate = null;
        self::$footerTemplate = null;
        self::$bodyTemplate = null;
        JTB_Template_Matcher::clearCache();
    }

    /**
     * Try to handle frontend request
     * Returns HTML if JTB should handle this request, null otherwise
     *
     * @param string $uri The request URI
     * @return string|null Full HTML page or null
     */
    public function tryHandle(string $uri): ?string
    {
        // Skip admin and API routes
        if (str_starts_with($uri, '/admin') || str_starts_with($uri, '/api')) {
            return null;
        }

        // Initialize and check if we have any templates
        self::init();

        // Must have at least a body template to take over
        if (!self::hasBody()) {
            return null;
        }

        // Build dynamic context
        JTB_Dynamic_Context::init();

        // Render the full page
        return $this->renderFullPageWithContext();
    }

    /**
     * Render full page with header, body, footer using dynamic context
     */
    private function renderFullPageWithContext(): string
    {
        $pluginUrl = '/plugins/jessie-theme-builder';
        $context = JTB_Dynamic_Context::get();
        $post = JTB_Dynamic_Context::getPost();
        $site = JTB_Dynamic_Context::getSite();

        // Get page title
        $pageTitle = $this->determinePageTitle($context, $post, $site);

        // Get theme settings for global CSS
        $themeSettings = [];
        if (class_exists(__NAMESPACE__ . '\\JTB_Theme_Settings')) {
            $themeSettings = JTB_Theme_Settings::getAll();
        }

        // Generate global CSS
        $globalCss = '';
        if (class_exists(__NAMESPACE__ . '\\JTB_CSS_Generator')) {
            $globalCss = JTB_CSS_Generator::generateGlobalCss($themeSettings);
        }

        // Get template CSS
        $templateCss = self::getCombinedCss();

        // Get body classes
        $bodyClasses = $this->getBodyClasses($context);

        // Get header options
        $headerOptions = $this->getHeaderOptions();

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <?php // SEO Meta ?>
    <?php if ($post && !empty($post['excerpt'])): ?>
    <meta name="description" content="<?= htmlspecialchars(substr(strip_tags($post['excerpt']), 0, 160)) ?>">
    <?php endif; ?>

    <?php // Open Graph ?>
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:type" content="<?= JTB_Dynamic_Context::isSinglePost() ? 'article' : 'website' ?>">
    <?php if ($post && !empty($post['featured_image'])): ?>
    <meta property="og:image" content="<?= htmlspecialchars($post['featured_image']) ?>">
    <?php endif; ?>

    <?php // Fonts ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php if (!empty($themeSettings['typography'])): ?>
    <?= $this->generateGoogleFontsLink($themeSettings['typography']) ?>
    <?php endif; ?>

    <?php // Stylesheets ?>
    <link rel="stylesheet" href="<?= $pluginUrl ?>/assets/css/frontend.css">
    <link rel="stylesheet" href="<?= $pluginUrl ?>/assets/css/animations.css">

    <?php // Global Theme CSS ?>
    <?php if (!empty($globalCss)): ?>
    <style id="jtb-global-css"><?= $globalCss ?></style>
    <?php endif; ?>

    <?php // Template CSS ?>
    <?php if (!empty($templateCss)): ?>
    <style id="jtb-template-css"><?= $templateCss ?></style>
    <?php endif; ?>

    <?php $this->doAction('jtb_head'); ?>
</head>
<body class="<?= htmlspecialchars($bodyClasses) ?>">
    <?php $this->doAction('jtb_body_start'); ?>

    <?php if (self::hasHeader()): ?>
    <header class="jtb-site-header <?= $this->getHeaderClasses($headerOptions) ?>"
        <?= $this->getHeaderDataAttributes($headerOptions) ?>>
        <?= self::renderHeader() ?>
    </header>
    <?php endif; ?>

    <main class="jtb-site-main" role="main">
        <?= self::renderBody($this->getPageContent($context, $post)) ?>
    </main>

    <?php if (self::hasFooter()): ?>
    <footer class="jtb-site-footer" role="contentinfo">
        <?= self::renderFooter() ?>
    </footer>
    <?php endif; ?>

    <?php $this->doAction('jtb_body_end'); ?>

    <?php // Scripts ?>
    <script src="<?= $pluginUrl ?>/assets/js/frontend.js"></script>
</body>
</html>
        <?php
        return ob_get_clean();
    }

    /**
     * Determine page title
     */
    private function determinePageTitle(array $context, ?array $post, array $site): string
    {
        $siteTitle = $site['title'] ?? 'Site';

        if ($context['is_homepage'] ?? false) {
            return !empty($site['tagline']) ? $siteTitle . ' - ' . $site['tagline'] : $siteTitle;
        }

        if ($post && !empty($post['title'])) {
            return $post['title'] . ' - ' . $siteTitle;
        }

        if (!empty($context['archive']['title'])) {
            return $context['archive']['title'] . ' - ' . $siteTitle;
        }

        if ($context['is_search'] ?? false) {
            return 'Search: ' . ($context['search_query'] ?? '') . ' - ' . $siteTitle;
        }

        if ($context['is_404'] ?? false) {
            return '404 Not Found - ' . $siteTitle;
        }

        return $siteTitle;
    }

    /**
     * Get body classes for current context
     */
    private function getBodyClasses(array $context): string
    {
        $classes = ['jtb-page'];

        if ($context['is_homepage'] ?? false) {
            $classes[] = 'jtb-home';
        }
        if ($context['is_single'] ?? false) {
            $classes[] = 'jtb-single';
            $classes[] = 'jtb-single-post';
        }
        if ($context['is_page'] ?? false) {
            $classes[] = 'jtb-single';
            $classes[] = 'jtb-single-page';
        }
        if ($context['is_archive'] ?? false) {
            $classes[] = 'jtb-archive';
            $classes[] = 'jtb-archive-' . ($context['archive']['type'] ?? 'generic');
        }
        if ($context['is_search'] ?? false) {
            $classes[] = 'jtb-search';
        }
        if ($context['is_404'] ?? false) {
            $classes[] = 'jtb-404';
        }

        return implode(' ', $classes);
    }

    /**
     * Get header options from template
     */
    private function getHeaderOptions(): array
    {
        $template = self::getHeaderTemplate();
        if (!$template) {
            return [];
        }

        // Extract options from template content if stored there
        $content = $template['content'] ?? [];
        return $content['header_options'] ?? [];
    }

    /**
     * Get header CSS classes
     */
    private function getHeaderClasses(array $options): string
    {
        $classes = [];

        if (!empty($options['sticky'])) {
            $classes[] = 'jtb-sticky';
        }
        if (!empty($options['transparent'])) {
            $classes[] = 'jtb-transparent';
        }
        if (!empty($options['shrink'])) {
            $classes[] = 'jtb-shrink-enabled';
        }

        return implode(' ', $classes);
    }

    /**
     * Get header data attributes
     */
    private function getHeaderDataAttributes(array $options): string
    {
        $attrs = [];

        if (!empty($options['sticky_offset'])) {
            $attrs[] = 'data-sticky-offset="' . intval($options['sticky_offset']) . '"';
        }
        if (!empty($options['shrink'])) {
            $attrs[] = 'data-sticky-shrink="true"';
        }

        return implode(' ', $attrs);
    }

    /**
     * Get page content for body template
     */
    private function getPageContent(array $context, ?array $post): string
    {
        // For single posts/pages, return the content
        if ($post && !empty($post['content'])) {
            return $post['content'];
        }

        return '';
    }

    /**
     * Generate Google Fonts link
     */
    private function generateGoogleFontsLink(array $typography): string
    {
        $fonts = [];

        if (!empty($typography['body_font']) && $typography['body_font'] !== 'inherit') {
            $fonts[] = urlencode($typography['body_font']) . ':wght@400;500;600;700';
        }
        if (!empty($typography['heading_font']) && $typography['heading_font'] !== 'inherit' && $typography['heading_font'] !== ($typography['body_font'] ?? '')) {
            $fonts[] = urlencode($typography['heading_font']) . ':wght@600;700;800';
        }

        if (empty($fonts)) {
            return '';
        }

        return '<link href="https://fonts.googleapis.com/css2?family=' . implode('&family=', $fonts) . '&display=swap" rel="stylesheet">';
    }

    /**
     * Execute a hook action
     */
    private function doAction(string $hook): void
    {
        if (function_exists('do_action')) {
            do_action($hook);
        }
    }
}
