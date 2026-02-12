<?php
/**
 * Starter Restaurant — Home Template
 * 
 * All editable content reads from theme_get() (Theme Studio customizations).
 * data-ts attributes provide live preview bindings.
 * Sections are loaded dynamically based on Section Manager order/enabled state.
 */

// Pre-load customized values with sensible defaults
$heroHeadline = theme_get('hero.headline', get_site_name());
$heroSubtitle = theme_get('hero.subtitle', get_setting('hero_subtitle') ?: 'Experience exceptional cuisine in an unforgettable atmosphere.');
$heroBtnText  = theme_get('hero.btn_text', 'Our Stories');
$heroBtnLink  = theme_get('hero.btn_link', '/articles');
$heroBgImage  = theme_get('hero.bg_image');
$heroBadge    = get_setting('hero_badge') ?: 'Welcome';

$aboutLabel   = theme_get('about.label', 'About Us');
$aboutTitle   = theme_get('about.title', get_site_name());
$aboutDesc    = theme_get('about.description', get_setting('about_text') ?: 'Welcome to our establishment. We bring you the finest experiences with passion and dedication.');
$aboutImage   = theme_get('about.image');

$pagesLabel   = theme_get('pages.label', 'Explore');
$pagesTitle   = theme_get('pages.title', 'Our Pages');
$pagesDesc    = theme_get('pages.description', 'Discover everything we have to offer.');

$articlesLabel   = theme_get('articles.label', 'News & Stories');
$articlesTitle   = theme_get('articles.title', 'Latest Articles');
$articlesDesc    = theme_get('articles.description', 'Stay up to date with our latest news and stories.');
$articlesBtnText = theme_get('articles.btn_text', 'View All Articles');
$articlesBtnLink = theme_get('articles.btn_link', '/articles');

$parallaxQuote    = theme_get('parallax.quote', get_setting('quote_text') ?: 'Every great experience begins with passion and dedication.');
$parallaxCitation = theme_get('parallax.citation', get_site_name());
$parallaxBg       = theme_get('parallax.bg_image');

// Section ordering and visibility
$themeConfig = get_theme_config(get_active_theme());
$defaultOrder = array_column($themeConfig['homepage_sections'] ?? [], 'id');
$sectionOrder = theme_get_section_order();
if (empty($sectionOrder)) {
    $sectionOrder = $defaultOrder;
}

foreach ($sectionOrder as $sectionId) {
    if (!theme_section_enabled($sectionId)) continue;
    $sectionFile = __DIR__ . '/../sections/' . $sectionId . '.php';
    if (file_exists($sectionFile)) {
        require $sectionFile;
    }
}
?>
