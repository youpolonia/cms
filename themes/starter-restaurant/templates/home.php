<?php
/**
 * Starter Restaurant — Home Template
 * 
 * All editable content reads from theme_get() (Theme Studio customizations).
 * data-ts attributes provide live preview bindings.
 * Sections are loaded dynamically based on Section Manager order/enabled state.
 */

// Pre-load customized values with sensible defaults
$heroHeadline = theme_get('hero.headline', 'A Culinary Journey Through Italy');
$heroSubtitle = theme_get('hero.subtitle', 'Handmade pasta, wood-fired specialties, and an award-winning wine cellar in the heart of the city.');
$heroBtnText  = theme_get('hero.btn_text', 'Reserve a Table');
$heroBtnLink  = theme_get('hero.btn_link', '/page/reservations');
$heroBgImage  = theme_get('hero.bg_image');
$heroBadge    = theme_get('hero.badge', 'Est. 2008');

$aboutLabel   = theme_get('about.label', 'Our Story');
$aboutTitle   = theme_get('about.title', 'A Family Tradition');
$aboutDesc    = theme_get('about.description', 'For over 15 years, La Maison has been serving authentic Italian cuisine made with recipes passed down through generations.');
$aboutImage   = theme_get('about.image');

$pagesLabel   = theme_get('pages.label', 'Explore');
$pagesTitle   = theme_get('pages.title', 'Discover La Maison');
$pagesDesc    = theme_get('pages.description', 'From our seasonal menu to private events, there is always something special waiting for you.');

$articlesLabel   = theme_get('articles.label', 'From Our Kitchen');
$articlesTitle   = theme_get('articles.title', 'Stories & Recipes');
$articlesDesc    = theme_get('articles.description', 'Behind-the-scenes stories from our chef, seasonal recipes, and wine pairing guides.');
$articlesBtnText = theme_get('articles.btn_text', 'Read More Stories');
$articlesBtnLink = theme_get('articles.btn_link', '/articles');

$parallaxQuote    = theme_get('parallax.quote', 'Cooking is not just about food. It is about love, family, and the stories we share around the table.');
$parallaxCitation = theme_get('parallax.citation', 'Chef Marco Bellini, Founder');
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
