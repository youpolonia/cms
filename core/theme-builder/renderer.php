<?php

// Load Database class for modules that need DB access
if (!class_exists("\core\Database")) {
    require_once dirname(__DIR__) . "/database.php";
}

// Define esc() function if not already defined
if (!function_exists('esc')) {
    function esc(?string $str): string {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Define tb_generate_id() if not already defined (from modules/_base.php)
if (!function_exists('tb_generate_id')) {
    function tb_generate_id(string $prefix = 'mod'): string {
        return $prefix . '_' . bin2hex(random_bytes(8));
    }
}
/**
 * Get theme color map (hex => CSS variable)
 * Loads colors from active theme's theme.json
 */
function tb_get_theme_color_map(): array
{
    static $colorMap = null;
    if ($colorMap !== null) {
        return $colorMap;
    }
    
    $colorMap = [];
    
    // Get active theme
    $activeTheme = 'default';
    if (file_exists(dirname(__DIR__) . '/../models/settingsmodel.php')) {
        require_once dirname(__DIR__) . '/../models/settingsmodel.php';
        if (class_exists('SettingsModel')) {
            $activeTheme = SettingsModel::getActiveTheme() ?: 'default';
        }
    }
    
    // Load theme.json
    $themePath = dirname(__DIR__) . '/../themes/' . $activeTheme . '/theme.json';
    if (file_exists($themePath)) {
        $themeData = json_decode(file_get_contents($themePath), true);
        // Support both root-level colors and config.colors (jessie theme format)
        $colors = $themeData['colors'] ?? $themeData['config']['colors'] ?? [];
        if (!empty($colors)) {
            foreach ($colors as $key => $hex) {
                $hexLower = strtolower($hex);
                $colorMap[$hexLower] = 'var(--color-' . $key . ')';
            }
        }
    }
    
    return $colorMap;
}

/**
 * Map a color value to CSS variable if it matches theme color
 */
function tb_map_color(string $color): string
{
    if (empty($color)) {
        return $color;
    }
    
    // Already a CSS variable
    if (strpos($color, 'var(') === 0) {
        return $color;
    }
    
    $colorMap = tb_get_theme_color_map();
    $colorLower = strtolower($color);
    
    if (isset($colorMap[$colorLower])) {
        return $colorMap[$colorLower];
    }
    
    return $color;
}

// ============================================
// INNER ELEMENTS STYLING SYSTEM
// ============================================

/**
 * Convert PHP property name to CSS property
 * Examples: font_size => font-size, backgroundColor => background-color
 */
function tb_to_css_property(string $prop): string
{
    // Handle snake_case
    $prop = str_replace('_', '-', $prop);
    // Handle camelCase
    $prop = preg_replace('/([a-z])([A-Z])/', '$1-$2', $prop);
    return strtolower($prop);
}

/**
 * Build composite CSS properties from individual values
 * Converts box_shadow_h, box_shadow_v etc. into proper box-shadow CSS
 */
function tb_build_composite_css(array $styles): array
{
    $result = [];
    
    // ═══════════════════════════════════════════════════════════════════════
    // BOX SHADOW: box_shadow_h, box_shadow_v, box_shadow_blur, box_shadow_spread, box_shadow_color, box_shadow_inset
    // ═══════════════════════════════════════════════════════════════════════
    if (!empty($styles['box_shadow_enabled']) || isset($styles['box_shadow_h']) || isset($styles['box_shadow_blur'])) {
        $h = $styles['box_shadow_h'] ?? 0;
        $v = $styles['box_shadow_v'] ?? 4;
        $blur = $styles['box_shadow_blur'] ?? 10;
        $spread = $styles['box_shadow_spread'] ?? 0;
        $color = $styles['box_shadow_color'] ?? 'rgba(0,0,0,0.1)';
        $inset = !empty($styles['box_shadow_inset']) ? 'inset ' : '';
        
        if (!empty($styles['box_shadow_enabled'])) {
            $result['box-shadow'] = $inset . intval($h) . 'px ' . intval($v) . 'px ' . intval($blur) . 'px ' . intval($spread) . 'px ' . $color;
        }
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // BORDER RADIUS: border_radius_tl, border_radius_tr, border_radius_br, border_radius_bl
    // ═══════════════════════════════════════════════════════════════════════
    $hasBorderRadius = isset($styles['border_radius_tl']) || isset($styles['border_radius_tr']) || 
                       isset($styles['border_radius_br']) || isset($styles['border_radius_bl']);
    if ($hasBorderRadius) {
        $tl = $styles['border_radius_tl'] ?? 0;
        $tr = $styles['border_radius_tr'] ?? 0;
        $br = $styles['border_radius_br'] ?? 0;
        $bl = $styles['border_radius_bl'] ?? 0;
        
        // Add px if numeric
        $tl = is_numeric($tl) ? intval($tl) . 'px' : $tl;
        $tr = is_numeric($tr) ? intval($tr) . 'px' : $tr;
        $br = is_numeric($br) ? intval($br) . 'px' : $br;
        $bl = is_numeric($bl) ? intval($bl) . 'px' : $bl;
        
        $result['border-radius'] = "$tl $tr $br $bl";
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // BORDER WIDTH: border_width_top, border_width_right, border_width_bottom, border_width_left
    // ═══════════════════════════════════════════════════════════════════════
    $hasBorderWidth = isset($styles['border_width_top']) || isset($styles['border_width_right']) ||
                      isset($styles['border_width_bottom']) || isset($styles['border_width_left']);
    if ($hasBorderWidth) {
        $top = $styles['border_width_top'] ?? 0;
        $right = $styles['border_width_right'] ?? 0;
        $bottom = $styles['border_width_bottom'] ?? 0;
        $left = $styles['border_width_left'] ?? 0;
        
        $top = is_numeric($top) ? intval($top) . 'px' : $top;
        $right = is_numeric($right) ? intval($right) . 'px' : $right;
        $bottom = is_numeric($bottom) ? intval($bottom) . 'px' : $bottom;
        $left = is_numeric($left) ? intval($left) . 'px' : $left;
        
        $result['border-width'] = "$top $right $bottom $left";
        $result['border-style'] = $styles['border_style'] ?? 'solid';
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // MARGIN: margin_top, margin_right, margin_bottom, margin_left
    // ═══════════════════════════════════════════════════════════════════════
    $hasMargin = isset($styles['margin_top']) || isset($styles['margin_right']) ||
                 isset($styles['margin_bottom']) || isset($styles['margin_left']);
    if ($hasMargin) {
        $top = $styles['margin_top'] ?? '0px';
        $right = $styles['margin_right'] ?? '0px';
        $bottom = $styles['margin_bottom'] ?? '0px';
        $left = $styles['margin_left'] ?? '0px';
        
        $result['margin'] = "$top $right $bottom $left";
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // PADDING: padding_top, padding_right, padding_bottom, padding_left
    // ═══════════════════════════════════════════════════════════════════════
    $hasPadding = isset($styles['padding_top']) || isset($styles['padding_right']) ||
                  isset($styles['padding_bottom']) || isset($styles['padding_left']);
    if ($hasPadding) {
        $top = $styles['padding_top'] ?? '0px';
        $right = $styles['padding_right'] ?? '0px';
        $bottom = $styles['padding_bottom'] ?? '0px';
        $left = $styles['padding_left'] ?? '0px';
        
        $result['padding'] = "$top $right $bottom $left";
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // FILTERS: filter_blur, filter_brightness, filter_contrast, etc.
    // ═══════════════════════════════════════════════════════════════════════
    $filterParts = [];
    if (!empty($styles['filter_blur'])) $filterParts[] = 'blur(' . intval($styles['filter_blur']) . 'px)';
    if (isset($styles['filter_brightness']) && $styles['filter_brightness'] != 100) $filterParts[] = 'brightness(' . intval($styles['filter_brightness']) . '%)';
    if (isset($styles['filter_contrast']) && $styles['filter_contrast'] != 100) $filterParts[] = 'contrast(' . intval($styles['filter_contrast']) . '%)';
    if (isset($styles['filter_saturation']) && $styles['filter_saturation'] != 100) $filterParts[] = 'saturate(' . intval($styles['filter_saturation']) . '%)';
    if (!empty($styles['filter_grayscale'])) $filterParts[] = 'grayscale(' . intval($styles['filter_grayscale']) . '%)';
    if (!empty($styles['filter_sepia'])) $filterParts[] = 'sepia(' . intval($styles['filter_sepia']) . '%)';
    if (!empty($styles['filter_hue_rotate'])) $filterParts[] = 'hue-rotate(' . intval($styles['filter_hue_rotate']) . 'deg)';
    if (!empty($styles['filter_invert'])) $filterParts[] = 'invert(' . intval($styles['filter_invert']) . '%)';
    
    if (!empty($filterParts)) {
        $result['filter'] = implode(' ', $filterParts);
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // TRANSFORM: transform_scale, transform_rotate, transform_translateX, transform_translateY
    // ═══════════════════════════════════════════════════════════════════════
    $transformParts = [];
    if (isset($styles['transform_scale']) && $styles['transform_scale'] != 1 && $styles['transform_scale'] != '1') {
        $transformParts[] = 'scale(' . floatval($styles['transform_scale']) . ')';
    }
    if (!empty($styles['transform_rotate'])) {
        $transformParts[] = 'rotate(' . intval($styles['transform_rotate']) . 'deg)';
    }
    if (!empty($styles['transform_translateX']) || !empty($styles['transform_translateY'])) {
        $tx = $styles['transform_translateX'] ?? 0;
        $ty = $styles['transform_translateY'] ?? 0;
        $transformParts[] = 'translate(' . intval($tx) . 'px, ' . intval($ty) . 'px)';
    }
    if (!empty($styles['transform_skewX']) || !empty($styles['transform_skewY'])) {
        $sx = $styles['transform_skewX'] ?? 0;
        $sy = $styles['transform_skewY'] ?? 0;
        $transformParts[] = 'skew(' . intval($sx) . 'deg, ' . intval($sy) . 'deg)';
    }
    
    if (!empty($transformParts)) {
        $result['transform'] = implode(' ', $transformParts);
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // Copy simple CSS properties (not composite parts)
    // ═══════════════════════════════════════════════════════════════════════
    $compositeParts = [
        'box_shadow_enabled', 'box_shadow_h', 'box_shadow_v', 'box_shadow_blur', 
        'box_shadow_spread', 'box_shadow_color', 'box_shadow_inset',
        'border_radius_tl', 'border_radius_tr', 'border_radius_br', 'border_radius_bl', 'border_radius_linked',
        'border_width_top', 'border_width_right', 'border_width_bottom', 'border_width_left', 'border_width_linked',
        'margin_top', 'margin_right', 'margin_bottom', 'margin_left', 'margin_linked',
        'padding_top', 'padding_right', 'padding_bottom', 'padding_left', 'padding_linked',
        'filter_blur', 'filter_brightness', 'filter_contrast', 'filter_saturation', 
        'filter_grayscale', 'filter_sepia', 'filter_hue_rotate', 'filter_invert', 'filter_opacity',
        'transform_scale', 'transform_rotate', 'transform_translateX', 'transform_translateY',
        'transform_skewX', 'transform_skewY', 'transform_origin',
        // Internal flags
        'hover_enabled', 'animation_enabled', 'scroll_trigger_enabled', 'scroll_trigger_point', 'scroll_animate_once',
        'background_type', 'gradient_type', 'hover_color', 'hover_border_color', 'hover_background'
    ];
    
    foreach ($styles as $prop => $val) {
        if (in_array($prop, $compositeParts)) continue;
        if ($val === '' || $val === null) continue;
        
        $cssProp = tb_to_css_property($prop);
        
        // Map colors
        if (in_array($prop, ['background', 'background_color', 'color', 'border_color'])) {
            $val = tb_map_color($val);
        }
        
        $result[$cssProp] = $val;
    }
    
    return $result;
}

/**
 * Get element map for a module type
 * Maps element names to CSS selectors
 */
function tb_get_element_map(string $type): array
{
    static $maps = [
        // === INTERACTIVE MODULES ===
        'toggle' => [
            'header' => '.tb-toggle-header',
            'content' => '.tb-toggle-content',
            'icon' => '.tb-toggle-icon',
            'item' => '.tb-toggle-item'
        ],
        'accordion' => [
            'header' => '.tb-accordion-header',
            'content' => '.tb-accordion-content',
            'icon' => '.tb-accordion-icon',
            'item' => '.tb-accordion-item'
        ],
        'tabs' => [
            'nav' => '.tb-tabs-nav',
            'tab_button' => '.tb-tab-btn',
            'content' => '.tb-tab-panel',
            'indicator' => '.tb-tabs-indicator'
        ],

        // === BUTTON & CTA ===
        'button' => [
            'button' => '.tb-button',
            'icon' => '.tb-button-icon',
            'text' => '.tb-button-text'
        ],
        'cta' => [
            'container' => '.tb-cta',
            'title' => '.tb-cta-title',
            'subtitle' => '.tb-cta-subtitle',
            'button' => '.tb-cta-button'
        ],

        // === TYPOGRAPHY ===
        'heading' => [
            'heading' => '.tb-heading',
            'underline' => '.tb-heading-underline',
            'subtitle' => '.tb-heading-subtitle'
        ],
        'text' => [
            'container' => '.tb-text',
            'paragraph' => '.tb-text p',
            'link' => '.tb-text a',
            'heading' => '.tb-text h1, .tb-text h2, .tb-text h3, .tb-text h4, .tb-text h5, .tb-text h6'
        ],
        'quote' => [
            'container' => '.tb-quote',
            'quote' => '.tb-quote-text',
            'author' => '.tb-quote-author',
            'icon' => '.tb-quote-icon'
        ],

        // === LISTS ===
        'list' => [
            'container' => '.tb-list',
            'item' => '.tb-list li',
            'icon' => '.tb-list-icon'
        ],

        // === MEDIA ===
        'image' => [
            'wrapper' => '.tb-image',
            'container' => '.tb-image-container',
            'image' => '.tb-image-img',
            'overlay' => '.tb-image-overlay',
            'caption' => '.tb-image-caption'
        ],
        'gallery' => [
            'container' => '.tb-gallery',
            'item' => '.tb-gallery-item',
            'image' => '.tb-gallery-item img',
            'overlay' => '.tb-gallery-overlay',
            'caption' => '.tb-gallery-caption'
        ],
        'video' => [
            'container' => '.tb-video',
            'wrapper' => '.tb-video-wrapper',
            'overlay' => '.tb-video-overlay',
            'play_button' => '.tb-video-play'
        ],
        'audio' => [
            'container' => '.tb-audio',
            'player' => '.tb-audio-player',
            'title' => '.tb-audio-title'
        ],

        // === SLIDERS ===
        'slider' => [
            'container' => '.tb-slider',
            'slide' => '.tb-slide',
            'title' => '.tb-slide-title',
            'text' => '.tb-slide-text',
            'button' => '.tb-slide-button',
            'arrow' => '.tb-slider-arrow',
            'dot' => '.tb-slider-dot'
        ],
        'video_slider' => [
            'container' => '.tb-video-slider',
            'slide' => '.tb-video-slide',
            'thumbnail' => '.tb-video-thumbnail',
            'title' => '.tb-video-title'
        ],
        'post_slider' => [
            'container' => '.tb-post-slider',
            'slide' => '.tb-post-slide',
            'title' => '.tb-post-title',
            'meta' => '.tb-post-meta',
            'excerpt' => '.tb-post-excerpt',
            'button' => '.tb-post-button'
        ],
        'fullwidth_slider' => [
            'container' => '.tb-fullwidth-slider',
            'slide' => '.tb-fullwidth-slide',
            'title' => '.tb-fullwidth-slide-title',
            'text' => '.tb-fullwidth-slide-text',
            'button' => '.tb-fullwidth-slide-button'
        ],
        'fullwidth_post_slider' => [
            'container' => '.tb-fullwidth-post-slider',
            'slide' => '.tb-fullwidth-post-slide',
            'title' => '.tb-fullwidth-post-title',
            'meta' => '.tb-fullwidth-post-meta'
        ],

        // === CARDS & CONTENT ===
        'blurb' => [
            'container' => '.tb-blurb',
            'icon' => '.tb-blurb-icon',
            'title' => '.tb-blurb-title',
            'text' => '.tb-blurb-text'
        ],
        'testimonial' => [
            'container' => '.tb-testimonial',
            'quote' => '.tb-testimonial-quote',
            'author' => '.tb-testimonial-author',
            'role' => '.tb-testimonial-role',
            'avatar' => '.tb-testimonial-avatar'
        ],
        'team' => [
            'container' => '.tb-team',
            'card' => '.tb-team-member',
            'avatar' => '.tb-team-avatar',
            'name' => '.tb-team-name',
            'role' => '.tb-team-role',
            'bio' => '.tb-team-bio',
            'social' => '.tb-team-social'
        ],
        'pricing' => [
            'container' => '.tb-pricing',
            'card' => '.tb-pricing-card',
            'header' => '.tb-pricing-header',
            'title' => '.tb-pricing-title',
            'price' => '.tb-pricing-price',
            'period' => '.tb-pricing-period',
            'features' => '.tb-pricing-features',
            'feature' => '.tb-pricing-feature',
            'button' => '.tb-pricing-button'
        ],

        // === HERO & HEADERS ===
        'hero' => [
            'container' => '.tb-hero',
            'wrapper' => '.tb-hero',
            'overlay' => '.tb-hero-overlay',
            'content' => '.tb-hero-content',
            'title' => '.tb-hero-title',
            'subtitle' => '.tb-hero-subtitle',
            'description' => '.tb-hero-description',
            'text' => '.tb-hero-description',
            'button' => '.tb-hero-button',
            'primary_button' => '.tb-hero-button.tb-button-primary',
            'secondary_button' => '.tb-hero-button.tb-button-secondary',
            'buttons' => '.tb-hero-buttons'
        ],
        'fullwidth_header' => [
            'container' => '.tb-fullwidth-header',
            'wrapper' => '.tb-fullwidth-header',
            'overlay' => '.tb-header-overlay',
            'title' => '.tb-header-title, .tb-fullwidth-header h1, .tb-fullwidth-header h2',
            'subtitle' => '.tb-header-subtitle, .tb-fullwidth-header h3, .tb-fullwidth-header h4',
            'description' => '.tb-header-description, .tb-fullwidth-header p',
            'text' => '.tb-fullwidth-header p',
            'button' => '.tb-header-button, .tb-fullwidth-header .tb-button',
            'primary_button' => '.tb-header-button:first-of-type, .tb-fullwidth-header .tb-button-primary',
            'secondary_button' => '.tb-header-button:last-of-type, .tb-fullwidth-header .tb-button-secondary'
        ],

        // === COUNTERS ===
        'counter' => [
            'container' => '.tb-counter',
            'number' => '.tb-counter-number',
            'title' => '.tb-counter-title',
            'icon' => '.tb-counter-icon'
        ],
        'circle_counter' => [
            'container' => '.tb-circle-counter',
            'circle' => '.tb-circle',
            'number' => '.tb-circle-number',
            'title' => '.tb-circle-title'
        ],
        'bar_counters' => [
            'container' => '.tb-bar-counters',
            'item' => '.tb-bar-item',
            'label' => '.tb-bar-label',
            'bar' => '.tb-bar',
            'fill' => '.tb-bar-fill',
            'percent' => '.tb-bar-percent'
        ],
        'countdown' => [
            'container' => '.tb-countdown',
            'item' => '.tb-countdown-item',
            'number' => '.tb-countdown-number',
            'label' => '.tb-countdown-label'
        ],

        // === SOCIAL ===
        'social' => [
            'container' => '.tb-social',
            'icon' => '.tb-social-icon',
            'link' => '.tb-social-link'
        ],
        'social_follow' => [
            'container' => '.tb-social-follow',
            'icon' => '.tb-social-follow-icon',
            'link' => '.tb-social-follow-link',
            'count' => '.tb-social-follow-count'
        ],

        // === NAVIGATION & MENUS ===
        'menu' => [
            'container' => '.tb-menu',
            'item' => '.tb-menu-item',
            'link' => '.tb-menu-link',
            'submenu' => '.tb-submenu'
        ],
        'fullwidth_menu' => [
            'container' => '.tb-fullwidth-menu',
            'item' => '.tb-menu-item',
            'link' => '.tb-menu-link',
            'submenu' => '.tb-submenu'
        ],

        // === BLOG & POSTS ===
        'blog' => [
            'container' => '.tb-blog',
            'post' => '.tb-blog-post',
            'image' => '.tb-blog-image',
            'title' => '.tb-blog-title',
            'meta' => '.tb-blog-meta',
            'excerpt' => '.tb-blog-excerpt',
            'button' => '.tb-blog-button'
        ],
        'post_title' => [
            'container' => '.tb-post-title-module',
            'title' => '.tb-post-title',
            'meta' => '.tb-post-meta'
        ],
        'post_content' => [
            'container' => '.tb-post-content',
            'paragraph' => '.tb-post-content p',
            'heading' => '.tb-post-content h1, .tb-post-content h2, .tb-post-content h3',
            'link' => '.tb-post-content a',
            'blockquote' => '.tb-post-content blockquote',
            'code' => '.tb-post-content code'
        ],
        'posts_navigation' => [
            'container' => '.tb-posts-nav',
            'prev' => '.tb-posts-nav-prev',
            'next' => '.tb-posts-nav-next',
            'label' => '.tb-posts-nav-label',
            'title' => '.tb-posts-nav-title'
        ],
        'comments' => [
            'container' => '.tb-comments',
            'comment' => '.tb-comment',
            'avatar' => '.tb-comment-avatar',
            'author' => '.tb-comment-author',
            'date' => '.tb-comment-date',
            'text' => '.tb-comment-text',
            'reply' => '.tb-comment-reply',
            'form' => '.tb-comment-form',
            'input' => '.tb-comment-input',
            'submit' => '.tb-comment-submit'
        ],

        // === FORMS ===
        'form' => [
            'container' => '.tb-form',
            'group' => '.tb-form-group',
            'label' => '.tb-form-label',
            'input' => '.tb-form-input',
            'textarea' => '.tb-form-textarea',
            'select' => '.tb-form-select',
            'checkbox' => '.tb-form-checkbox',
            'radio' => '.tb-form-radio',
            'submit' => '.tb-form-submit',
            'error' => '.tb-form-error',
            'success' => '.tb-form-success'
        ],
        'search' => [
            'container' => '.tb-search',
            'input' => '.tb-search-input',
            'button' => '.tb-search-button',
            'icon' => '.tb-search-icon'
        ],
        'login' => [
            'container' => '.tb-login',
            'title' => '.tb-login-title',
            'input' => '.tb-login-input',
            'button' => '.tb-login-button',
            'link' => '.tb-login-link'
        ],
        'signup' => [
            'container' => '.tb-signup',
            'title' => '.tb-signup-title',
            'input' => '.tb-signup-input',
            'button' => '.tb-signup-button',
            'link' => '.tb-signup-link'
        ],

        // === LAYOUT & STRUCTURE ===
        'sidebar' => [
            'container' => '.tb-sidebar',
            'widget' => '.tb-sidebar-widget',
            'title' => '.tb-sidebar-title',
            'content' => '.tb-sidebar-content'
        ],
        'divider' => [
            'container' => '.tb-divider',
            'line' => '.tb-divider-line',
            'icon' => '.tb-divider-icon'
        ],
        'spacer' => [
            'container' => '.tb-spacer'
        ],
        'icon' => [
            'container' => '.tb-icon-module',
            'icon' => '.tb-icon'
        ],
        'logo' => [
            'container' => '.tb-logo',
            'image' => '.tb-logo img',
            'text' => '.tb-logo-text'
        ],

        // === MAPS & EMBEDS ===
        'map' => [
            'container' => '.tb-map',
            'iframe' => '.tb-map iframe'
        ],
        'fullwidth_map' => [
            'container' => '.tb-fullwidth-map',
            'iframe' => '.tb-fullwidth-map iframe'
        ],

        // === CODE ===
        'code' => [
            'container' => '.tb-code',
            'pre' => '.tb-code pre',
            'code' => '.tb-code code'
        ],
        'fullwidth_code' => [
            'container' => '.tb-fullwidth-code',
            'pre' => '.tb-fullwidth-code pre',
            'code' => '.tb-fullwidth-code code'
        ],

        // === PORTFOLIO ===
        'portfolio' => [
            'container' => '.tb-portfolio',
            'item' => '.tb-portfolio-item',
            'image' => '.tb-portfolio-image',
            'overlay' => '.tb-portfolio-overlay',
            'title' => '.tb-portfolio-title',
            'category' => '.tb-portfolio-category'
        ],
        'fullwidth_portfolio' => [
            'container' => '.tb-fullwidth-portfolio',
            'item' => '.tb-portfolio-item',
            'image' => '.tb-portfolio-image',
            'overlay' => '.tb-portfolio-overlay',
            'title' => '.tb-portfolio-title'
        ],

        // === FULLWIDTH VARIANTS ===
        'fullwidth_image' => [
            'container' => '.tb-fullwidth-image',
            'image' => '.tb-fullwidth-image img',
            'overlay' => '.tb-fullwidth-image-overlay',
            'caption' => '.tb-fullwidth-image-caption'
        ]
    ];

    return $maps[$type] ?? [];
}

/**
 * Get editable elements for a module type with their labels and available properties
 */
function tb_get_element_schema(string $type): array
{
    // Common property sets for reuse
    static $commonProps = [
        'typography' => ['color', 'font_size', 'font_weight', 'line_height'],
        'spacing' => ['padding', 'margin'],
        'background' => ['background', 'border', 'border_radius', 'box_shadow'],
        'button' => ['background', 'color', 'font_size', 'font_weight', 'padding', 'border', 'border_radius', 'box_shadow']
    ];

    static $schemas = [
        // === INTERACTIVE MODULES ===
        'toggle' => [
            'header' => ['label' => 'Header', 'states' => ['normal', 'hover', 'active'], 'properties' => ['background', 'color', 'font_size', 'font_weight', 'padding', 'border_radius']],
            'content' => ['label' => 'Content', 'states' => ['normal'], 'properties' => ['background', 'color', 'font_size', 'padding', 'line_height']],
            'icon' => ['label' => 'Icon', 'states' => ['normal', 'active'], 'properties' => ['color', 'font_size']],
            'item' => ['label' => 'Item Container', 'states' => ['normal'], 'properties' => ['margin_bottom', 'border', 'border_radius']]
        ],
        'accordion' => [
            'header' => ['label' => 'Header', 'states' => ['normal', 'hover', 'active'], 'properties' => ['background', 'color', 'font_size', 'font_weight', 'padding', 'border_radius']],
            'content' => ['label' => 'Content', 'states' => ['normal'], 'properties' => ['background', 'color', 'font_size', 'padding']],
            'icon' => ['label' => 'Icon', 'states' => ['normal', 'active'], 'properties' => ['color', 'font_size']],
            'item' => ['label' => 'Item Container', 'states' => ['normal'], 'properties' => ['margin_bottom', 'border', 'border_radius']]
        ],
        'tabs' => [
            'nav' => ['label' => 'Navigation', 'states' => ['normal'], 'properties' => ['background', 'border_bottom', 'padding']],
            'tab_button' => ['label' => 'Tab Button', 'states' => ['normal', 'hover', 'active'], 'properties' => ['background', 'color', 'font_size', 'font_weight', 'padding', 'border_radius']],
            'content' => ['label' => 'Content Panel', 'states' => ['normal'], 'properties' => ['background', 'color', 'padding']]
        ],

        // === BUTTON & CTA ===
        'button' => [
            'button' => ['label' => 'Button', 'states' => ['normal', 'hover', 'active'], 'properties' => ['background', 'color', 'font_size', 'font_weight', 'padding', 'border', 'border_radius', 'box_shadow']]
        ],
        'cta' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding', 'border_radius']],
            'title' => ['label' => 'Title', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'subtitle' => ['label' => 'Subtitle', 'states' => ['normal'], 'properties' => ['color', 'font_size']],
            'button' => ['label' => 'Button', 'states' => ['normal', 'hover'], 'properties' => ['background', 'color', 'font_size', 'padding', 'border_radius']]
        ],

        // === TYPOGRAPHY ===
        'heading' => [
            'heading' => ['label' => 'Heading', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight', 'line_height']],
            'underline' => ['label' => 'Underline', 'states' => ['normal'], 'properties' => ['background', 'height', 'width']],
            'subtitle' => ['label' => 'Subtitle', 'states' => ['normal'], 'properties' => ['color', 'font_size']]
        ],
        'text' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding']],
            'paragraph' => ['label' => 'Paragraph', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'line_height']],
            'link' => ['label' => 'Link', 'states' => ['normal', 'hover'], 'properties' => ['color']]
        ],
        'quote' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding', 'border_left']],
            'quote' => ['label' => 'Quote Text', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_style']],
            'author' => ['label' => 'Author', 'states' => ['normal'], 'properties' => ['color', 'font_size']],
            'icon' => ['label' => 'Quote Icon', 'states' => ['normal'], 'properties' => ['color', 'font_size']]
        ],

        // === LISTS ===
        'list' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding']],
            'item' => ['label' => 'List Item', 'states' => ['normal', 'hover'], 'properties' => ['color', 'font_size', 'padding', 'border_bottom']],
            'icon' => ['label' => 'Icon/Bullet', 'states' => ['normal'], 'properties' => ['color', 'font_size']]
        ],

        // === MEDIA ===
        'image' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding', 'border_radius']],
            'image' => ['label' => 'Image', 'states' => ['normal', 'hover'], 'properties' => ['border_radius', 'box_shadow', 'opacity']],
            'overlay' => ['label' => 'Overlay', 'states' => ['normal', 'hover'], 'properties' => ['background', 'opacity']],
            'caption' => ['label' => 'Caption', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'background', 'padding']]
        ],
        'gallery' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding', 'gap']],
            'item' => ['label' => 'Gallery Item', 'states' => ['normal', 'hover'], 'properties' => ['border_radius', 'box_shadow']],
            'image' => ['label' => 'Image', 'states' => ['normal', 'hover'], 'properties' => ['border_radius', 'opacity']],
            'overlay' => ['label' => 'Overlay', 'states' => ['normal', 'hover'], 'properties' => ['background', 'opacity']],
            'caption' => ['label' => 'Caption', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'background']]
        ],
        'video' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'border_radius']],
            'overlay' => ['label' => 'Overlay', 'states' => ['normal'], 'properties' => ['background', 'opacity']],
            'play_button' => ['label' => 'Play Button', 'states' => ['normal', 'hover'], 'properties' => ['background', 'color', 'font_size', 'border_radius']]
        ],
        'audio' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding', 'border_radius']],
            'player' => ['label' => 'Player', 'states' => ['normal'], 'properties' => ['background']],
            'title' => ['label' => 'Title', 'states' => ['normal'], 'properties' => ['color', 'font_size']]
        ],

        // === SLIDERS ===
        'slider' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background']],
            'slide' => ['label' => 'Slide', 'states' => ['normal'], 'properties' => ['background', 'padding']],
            'title' => ['label' => 'Title', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'text' => ['label' => 'Text', 'states' => ['normal'], 'properties' => ['color', 'font_size']],
            'button' => ['label' => 'Button', 'states' => ['normal', 'hover'], 'properties' => ['background', 'color', 'padding', 'border_radius']],
            'arrow' => ['label' => 'Arrow', 'states' => ['normal', 'hover'], 'properties' => ['background', 'color', 'font_size']],
            'dot' => ['label' => 'Dot', 'states' => ['normal', 'active'], 'properties' => ['background', 'width', 'height']]
        ],
        'video_slider' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background']],
            'thumbnail' => ['label' => 'Thumbnail', 'states' => ['normal', 'hover', 'active'], 'properties' => ['border', 'opacity']],
            'title' => ['label' => 'Title', 'states' => ['normal'], 'properties' => ['color', 'font_size']]
        ],
        'post_slider' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background']],
            'slide' => ['label' => 'Slide', 'states' => ['normal'], 'properties' => ['background', 'padding']],
            'title' => ['label' => 'Title', 'states' => ['normal', 'hover'], 'properties' => ['color', 'font_size', 'font_weight']],
            'meta' => ['label' => 'Meta', 'states' => ['normal'], 'properties' => ['color', 'font_size']],
            'excerpt' => ['label' => 'Excerpt', 'states' => ['normal'], 'properties' => ['color', 'font_size']],
            'button' => ['label' => 'Button', 'states' => ['normal', 'hover'], 'properties' => ['background', 'color', 'padding', 'border_radius']]
        ],
        'fullwidth_slider' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background']],
            'title' => ['label' => 'Title', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight', 'text_shadow']],
            'text' => ['label' => 'Text', 'states' => ['normal'], 'properties' => ['color', 'font_size']],
            'button' => ['label' => 'Button', 'states' => ['normal', 'hover'], 'properties' => ['background', 'color', 'padding', 'border_radius']]
        ],
        'fullwidth_post_slider' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background']],
            'title' => ['label' => 'Title', 'states' => ['normal', 'hover'], 'properties' => ['color', 'font_size', 'font_weight']],
            'meta' => ['label' => 'Meta', 'states' => ['normal'], 'properties' => ['color', 'font_size']]
        ],

        // === CARDS & CONTENT ===
        'blurb' => [
            'container' => ['label' => 'Container', 'states' => ['normal', 'hover'], 'properties' => ['background', 'padding', 'border_radius', 'box_shadow']],
            'icon' => ['label' => 'Icon', 'states' => ['normal', 'hover'], 'properties' => ['color', 'font_size', 'background', 'border_radius']],
            'title' => ['label' => 'Title', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'text' => ['label' => 'Text', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'line_height']]
        ],
        'testimonial' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding', 'border_radius', 'box_shadow']],
            'quote' => ['label' => 'Quote', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_style', 'line_height']],
            'author' => ['label' => 'Author', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'role' => ['label' => 'Role', 'states' => ['normal'], 'properties' => ['color', 'font_size']],
            'avatar' => ['label' => 'Avatar', 'states' => ['normal'], 'properties' => ['border_radius', 'border', 'box_shadow']]
        ],
        'team' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['gap']],
            'card' => ['label' => 'Card', 'states' => ['normal', 'hover'], 'properties' => ['background', 'padding', 'border_radius', 'box_shadow']],
            'avatar' => ['label' => 'Avatar', 'states' => ['normal'], 'properties' => ['border_radius', 'border']],
            'name' => ['label' => 'Name', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'role' => ['label' => 'Role', 'states' => ['normal'], 'properties' => ['color', 'font_size']],
            'bio' => ['label' => 'Bio', 'states' => ['normal'], 'properties' => ['color', 'font_size']],
            'social' => ['label' => 'Social Icons', 'states' => ['normal', 'hover'], 'properties' => ['color', 'font_size']]
        ],
        'pricing' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['gap']],
            'card' => ['label' => 'Card', 'states' => ['normal', 'hover'], 'properties' => ['background', 'padding', 'border_radius', 'box_shadow', 'border']],
            'header' => ['label' => 'Header', 'states' => ['normal'], 'properties' => ['background', 'padding']],
            'title' => ['label' => 'Title', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'price' => ['label' => 'Price', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'period' => ['label' => 'Period', 'states' => ['normal'], 'properties' => ['color', 'font_size']],
            'feature' => ['label' => 'Feature Item', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'padding', 'border_bottom']],
            'button' => ['label' => 'Button', 'states' => ['normal', 'hover'], 'properties' => ['background', 'color', 'padding', 'border_radius']]
        ],

        // === HERO & HEADERS ===
        'hero' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding']],
            'overlay' => ['label' => 'Overlay', 'states' => ['normal'], 'properties' => ['background', 'opacity']],
            'content' => ['label' => 'Content Box', 'states' => ['normal'], 'properties' => ['background', 'padding', 'border_radius']],
            'title' => ['label' => 'Title', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight', 'text_shadow']],
            'subtitle' => ['label' => 'Subtitle', 'states' => ['normal'], 'properties' => ['color', 'font_size']],
            'button' => ['label' => 'Button', 'states' => ['normal', 'hover'], 'properties' => ['background', 'color', 'padding', 'border_radius']]
        ],
        'fullwidth_header' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding']],
            'overlay' => ['label' => 'Overlay', 'states' => ['normal'], 'properties' => ['background', 'opacity']],
            'title' => ['label' => 'Title', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'subtitle' => ['label' => 'Subtitle', 'states' => ['normal'], 'properties' => ['color', 'font_size']],
            'button' => ['label' => 'Button', 'states' => ['normal', 'hover'], 'properties' => ['background', 'color', 'padding', 'border_radius']]
        ],

        // === COUNTERS ===
        'counter' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding', 'border_radius']],
            'number' => ['label' => 'Number', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'title' => ['label' => 'Title', 'states' => ['normal'], 'properties' => ['color', 'font_size']],
            'icon' => ['label' => 'Icon', 'states' => ['normal'], 'properties' => ['color', 'font_size']]
        ],
        'circle_counter' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding']],
            'circle' => ['label' => 'Circle', 'states' => ['normal'], 'properties' => ['stroke', 'stroke_width']],
            'number' => ['label' => 'Number', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'title' => ['label' => 'Title', 'states' => ['normal'], 'properties' => ['color', 'font_size']]
        ],
        'bar_counters' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding']],
            'item' => ['label' => 'Bar Item', 'states' => ['normal'], 'properties' => ['margin_bottom']],
            'label' => ['label' => 'Label', 'states' => ['normal'], 'properties' => ['color', 'font_size']],
            'bar' => ['label' => 'Bar Background', 'states' => ['normal'], 'properties' => ['background', 'height', 'border_radius']],
            'fill' => ['label' => 'Bar Fill', 'states' => ['normal'], 'properties' => ['background']],
            'percent' => ['label' => 'Percentage', 'states' => ['normal'], 'properties' => ['color', 'font_size']]
        ],
        'countdown' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding', 'gap']],
            'item' => ['label' => 'Item Box', 'states' => ['normal'], 'properties' => ['background', 'padding', 'border_radius']],
            'number' => ['label' => 'Number', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'label' => ['label' => 'Label', 'states' => ['normal'], 'properties' => ['color', 'font_size']]
        ],

        // === SOCIAL ===
        'social' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['gap']],
            'icon' => ['label' => 'Icon', 'states' => ['normal', 'hover'], 'properties' => ['color', 'font_size', 'background', 'padding', 'border_radius']],
            'link' => ['label' => 'Link', 'states' => ['normal', 'hover'], 'properties' => ['background', 'border_radius']]
        ],
        'social_follow' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['gap']],
            'icon' => ['label' => 'Icon', 'states' => ['normal', 'hover'], 'properties' => ['color', 'font_size']],
            'link' => ['label' => 'Link', 'states' => ['normal', 'hover'], 'properties' => ['background', 'padding', 'border_radius']],
            'count' => ['label' => 'Count', 'states' => ['normal'], 'properties' => ['color', 'font_size']]
        ],

        // === NAVIGATION & MENUS ===
        'menu' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding']],
            'item' => ['label' => 'Menu Item', 'states' => ['normal'], 'properties' => ['padding']],
            'link' => ['label' => 'Link', 'states' => ['normal', 'hover', 'active'], 'properties' => ['color', 'font_size', 'font_weight', 'background', 'padding']],
            'submenu' => ['label' => 'Submenu', 'states' => ['normal'], 'properties' => ['background', 'border', 'box_shadow']]
        ],
        'fullwidth_menu' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding']],
            'link' => ['label' => 'Link', 'states' => ['normal', 'hover', 'active'], 'properties' => ['color', 'font_size', 'font_weight']]
        ],

        // === BLOG & POSTS ===
        'blog' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding', 'gap']],
            'post' => ['label' => 'Post Card', 'states' => ['normal', 'hover'], 'properties' => ['background', 'padding', 'border_radius', 'box_shadow']],
            'image' => ['label' => 'Image', 'states' => ['normal', 'hover'], 'properties' => ['border_radius', 'opacity']],
            'title' => ['label' => 'Title', 'states' => ['normal', 'hover'], 'properties' => ['color', 'font_size', 'font_weight']],
            'meta' => ['label' => 'Meta', 'states' => ['normal'], 'properties' => ['color', 'font_size']],
            'excerpt' => ['label' => 'Excerpt', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'line_height']],
            'button' => ['label' => 'Button', 'states' => ['normal', 'hover'], 'properties' => ['color', 'font_size']]
        ],
        'post_title' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['padding']],
            'title' => ['label' => 'Title', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'meta' => ['label' => 'Meta', 'states' => ['normal'], 'properties' => ['color', 'font_size']]
        ],
        'post_content' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding']],
            'paragraph' => ['label' => 'Paragraph', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'line_height']],
            'heading' => ['label' => 'Headings', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'link' => ['label' => 'Link', 'states' => ['normal', 'hover'], 'properties' => ['color']],
            'blockquote' => ['label' => 'Blockquote', 'states' => ['normal'], 'properties' => ['background', 'color', 'border_left', 'padding']],
            'code' => ['label' => 'Code', 'states' => ['normal'], 'properties' => ['background', 'color', 'font_size', 'padding']]
        ],
        'posts_navigation' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding']],
            'prev' => ['label' => 'Prev Link', 'states' => ['normal', 'hover'], 'properties' => ['background', 'padding', 'border_radius']],
            'next' => ['label' => 'Next Link', 'states' => ['normal', 'hover'], 'properties' => ['background', 'padding', 'border_radius']],
            'label' => ['label' => 'Label', 'states' => ['normal'], 'properties' => ['color', 'font_size']],
            'title' => ['label' => 'Title', 'states' => ['normal', 'hover'], 'properties' => ['color', 'font_size', 'font_weight']]
        ],
        'comments' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding']],
            'comment' => ['label' => 'Comment', 'states' => ['normal'], 'properties' => ['background', 'padding', 'border', 'border_radius']],
            'author' => ['label' => 'Author', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'date' => ['label' => 'Date', 'states' => ['normal'], 'properties' => ['color', 'font_size']],
            'text' => ['label' => 'Text', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'line_height']],
            'reply' => ['label' => 'Reply Link', 'states' => ['normal', 'hover'], 'properties' => ['color', 'font_size']],
            'input' => ['label' => 'Input', 'states' => ['normal', 'focus'], 'properties' => ['background', 'border', 'border_radius', 'padding']],
            'submit' => ['label' => 'Submit Button', 'states' => ['normal', 'hover'], 'properties' => ['background', 'color', 'padding', 'border_radius']]
        ],

        // === FORMS ===
        'form' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding', 'border_radius']],
            'label' => ['label' => 'Label', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'input' => ['label' => 'Input', 'states' => ['normal', 'focus'], 'properties' => ['background', 'color', 'border', 'border_radius', 'padding']],
            'textarea' => ['label' => 'Textarea', 'states' => ['normal', 'focus'], 'properties' => ['background', 'border', 'border_radius', 'padding']],
            'select' => ['label' => 'Select', 'states' => ['normal', 'focus'], 'properties' => ['background', 'border', 'border_radius', 'padding']],
            'submit' => ['label' => 'Submit Button', 'states' => ['normal', 'hover'], 'properties' => ['background', 'color', 'font_size', 'padding', 'border_radius']],
            'error' => ['label' => 'Error Message', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'background']],
            'success' => ['label' => 'Success Message', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'background']]
        ],
        'search' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding', 'border_radius']],
            'input' => ['label' => 'Input', 'states' => ['normal', 'focus'], 'properties' => ['background', 'color', 'border', 'padding']],
            'button' => ['label' => 'Button', 'states' => ['normal', 'hover'], 'properties' => ['background', 'color', 'padding', 'border_radius']],
            'icon' => ['label' => 'Icon', 'states' => ['normal'], 'properties' => ['color', 'font_size']]
        ],
        'login' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding', 'border_radius', 'box_shadow']],
            'title' => ['label' => 'Title', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'input' => ['label' => 'Input', 'states' => ['normal', 'focus'], 'properties' => ['background', 'border', 'border_radius', 'padding']],
            'button' => ['label' => 'Button', 'states' => ['normal', 'hover'], 'properties' => ['background', 'color', 'padding', 'border_radius']],
            'link' => ['label' => 'Link', 'states' => ['normal', 'hover'], 'properties' => ['color']]
        ],
        'signup' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding', 'border_radius', 'box_shadow']],
            'title' => ['label' => 'Title', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'input' => ['label' => 'Input', 'states' => ['normal', 'focus'], 'properties' => ['background', 'border', 'border_radius', 'padding']],
            'button' => ['label' => 'Button', 'states' => ['normal', 'hover'], 'properties' => ['background', 'color', 'padding', 'border_radius']],
            'link' => ['label' => 'Link', 'states' => ['normal', 'hover'], 'properties' => ['color']]
        ],

        // === LAYOUT & STRUCTURE ===
        'sidebar' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding']],
            'widget' => ['label' => 'Widget', 'states' => ['normal'], 'properties' => ['background', 'padding', 'margin_bottom', 'border_radius']],
            'title' => ['label' => 'Widget Title', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight', 'border_bottom']],
            'content' => ['label' => 'Widget Content', 'states' => ['normal'], 'properties' => ['color', 'font_size']]
        ],
        'divider' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['padding']],
            'line' => ['label' => 'Line', 'states' => ['normal'], 'properties' => ['background', 'height', 'width']],
            'icon' => ['label' => 'Icon', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'background', 'padding', 'border_radius']]
        ],
        'spacer' => [
            'container' => ['label' => 'Spacer', 'states' => ['normal'], 'properties' => ['height', 'background']]
        ],
        'icon' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding', 'border_radius']],
            'icon' => ['label' => 'Icon', 'states' => ['normal', 'hover'], 'properties' => ['color', 'font_size', 'background', 'padding', 'border_radius']]
        ],
        'logo' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding']],
            'image' => ['label' => 'Image', 'states' => ['normal', 'hover'], 'properties' => ['opacity']],
            'text' => ['label' => 'Text', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']]
        ],

        // === MAPS & EMBEDS ===
        'map' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['border', 'border_radius', 'box_shadow']]
        ],
        'fullwidth_map' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['border']]
        ],

        // === CODE ===
        'code' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding', 'border_radius']],
            'pre' => ['label' => 'Pre', 'states' => ['normal'], 'properties' => ['background', 'padding', 'border_radius']],
            'code' => ['label' => 'Code', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_family']]
        ],
        'fullwidth_code' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding']],
            'code' => ['label' => 'Code', 'states' => ['normal'], 'properties' => ['color', 'font_size']]
        ],

        // === PORTFOLIO ===
        'portfolio' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background', 'padding', 'gap']],
            'item' => ['label' => 'Item', 'states' => ['normal', 'hover'], 'properties' => ['border_radius', 'box_shadow']],
            'image' => ['label' => 'Image', 'states' => ['normal', 'hover'], 'properties' => ['border_radius', 'opacity']],
            'overlay' => ['label' => 'Overlay', 'states' => ['normal', 'hover'], 'properties' => ['background', 'opacity']],
            'title' => ['label' => 'Title', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'font_weight']],
            'category' => ['label' => 'Category', 'states' => ['normal'], 'properties' => ['color', 'font_size']]
        ],
        'fullwidth_portfolio' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background']],
            'item' => ['label' => 'Item', 'states' => ['normal', 'hover'], 'properties' => ['box_shadow']],
            'overlay' => ['label' => 'Overlay', 'states' => ['normal', 'hover'], 'properties' => ['background', 'opacity']],
            'title' => ['label' => 'Title', 'states' => ['normal'], 'properties' => ['color', 'font_size']]
        ],

        // === FULLWIDTH VARIANTS ===
        'fullwidth_image' => [
            'container' => ['label' => 'Container', 'states' => ['normal'], 'properties' => ['background']],
            'image' => ['label' => 'Image', 'states' => ['normal', 'hover'], 'properties' => ['opacity']],
            'overlay' => ['label' => 'Overlay', 'states' => ['normal'], 'properties' => ['background', 'opacity']],
            'caption' => ['label' => 'Caption', 'states' => ['normal'], 'properties' => ['color', 'font_size', 'background', 'padding']]
        ]
    ];

    return $schemas[$type] ?? [];
}

/**
 * Generate CSS for module inner elements
 *
 * @param array $module The module data with design.elements
 * @return string CSS rules for the module
 */
function tb_generate_module_element_css(array $module): string
{
    $moduleId = $module['id'] ?? '';
    $type = $module['type'] ?? '';
    $design = $module['design'] ?? [];
    $elements = $design['elements'] ?? [];

    if (empty($moduleId) || empty($elements)) {
        return '';
    }

    $elementMap = tb_get_element_map($type);

    // Element name aliases to map JS schema names to PHP element map names
    // This handles cases where the sidebar uses different names than the element map
    static $elementAliases = [
        'subheading' => 'subtitle',
        'subTitle' => 'subtitle',
        'desc' => 'description',
        'button_secondary' => 'secondary_button',
        'buttonSecondary' => 'secondary_button',
        'button_primary' => 'primary_button',
        'buttonPrimary' => 'primary_button',
        'background' => 'container',
        'heading' => 'title',
        'header' => 'title',
        'link' => 'button',
        'cta' => 'button',
    ];

    $css = '';

    foreach ($elements as $elementName => $states) {
        if (!is_array($states)) {
            continue;
        }

        // Handle 'wrapper' element specially - targets the module container itself
        if ($elementName === 'wrapper') {
            foreach ($states as $state => $styles) {
                if (!is_array($styles) || empty($styles)) {
                    continue;
                }

                // Build composite CSS properties (box-shadow, border-radius, etc.)
                $cssProps = tb_build_composite_css($styles);
                
                if (empty($cssProps)) {
                    continue;
                }

                $stateSelector = match($state) {
                    'hover' => ':hover',
                    'focus' => ':focus',
                    'active' => '.active, #' . $moduleId . '.open, #' . $moduleId . '[aria-expanded="true"]',
                    default => ''
                };

                if ($state === 'active') {
                    $fullSelector = '#' . $moduleId . '.active, #' . $moduleId . '.open, #' . $moduleId . '[aria-expanded="true"]';
                } else {
                    $fullSelector = '#' . $moduleId . $stateSelector;
                }

                $css .= $fullSelector . " {\n";
                foreach ($cssProps as $cssProp => $val) {
                    $css .= "    " . $cssProp . ": " . $val . " !important;\n";
                }
                $css .= "}\n";
            }
            continue;
        }

        // Try to find selector in element map (with alias fallback)
        $selector = $elementMap[$elementName] ?? null;
        if (!$selector && isset($elementAliases[$elementName])) {
            $selector = $elementMap[$elementAliases[$elementName]] ?? null;
        }

        // If still no selector, skip this element
        if (!$selector) {
            continue;
        }

        foreach ($states as $state => $styles) {
            if (!is_array($styles) || empty($styles)) {
                continue;
            }

            // Build composite CSS properties (box-shadow, border-radius, etc.)
            $cssProps = tb_build_composite_css($styles);

            if (empty($cssProps)) {
                continue;
            }

            // Determine state selector suffix
            $stateSelector = match($state) {
                'hover' => ':hover',
                'focus' => ':focus',
                default => ''
            };

            // Build CSS rule - for active state, we need multiple selectors with full prefix
            if ($state === 'active') {
                $fullSelector = '#' . $moduleId . ' ' . $selector . '.active, '
                             . '#' . $moduleId . ' ' . $selector . '.open, '
                             . '#' . $moduleId . ' ' . $selector . '[aria-expanded="true"]';
            } else {
                $fullSelector = '#' . $moduleId . ' ' . $selector . $stateSelector;
            }
            $css .= $fullSelector . " {\n";

            foreach ($cssProps as $cssProp => $val) {
                $css .= "    " . $cssProp . ": " . $val . " !important;\n";
            }

            $css .= "}\n";
        }
    }

    return $css;
}


/**
 * Render icon from format string (fa:home, material:settings, bi:house, fab:facebook, emoji)
 * 
 * @param string $format Icon format string
 * @param string $size Optional size (default: inherit)
 * @param string $color Optional color (default: inherit)
 * @return string HTML for icon
 */
function tb_render_icon_from_format(string $format, string $size = 'inherit', string $color = 'inherit'): string
{
    if (empty($format)) {
        return '';
    }

    $inlineStyle = '';
    if ($size !== 'inherit') {
        $inlineStyle .= 'font-size:' . esc($size) . ';';
    }
    if ($color !== 'inherit') {
        $inlineStyle .= 'color:' . esc($color) . ';';
    }
    $styleAttr = $inlineStyle ? ' style="' . $inlineStyle . '"' : '';

    // Handle FontAwesome space-separated format: "fas fa-utensils", "far fa-star", "fab fa-facebook"
    if (preg_match('/^(fas|far|fab|fal|fad)\s+fa-([a-z0-9-]+)$/i', $format, $m)) {
        $faPrefix = strtolower($m[1]);
        $iconName = $m[2];
        // Map old prefixes to new Font Awesome 6 classes
        $classMap = [
            'fas' => 'fa-solid',
            'far' => 'fa-regular',
            'fab' => 'fa-brands',
            'fal' => 'fa-light',
            'fad' => 'fa-duotone',
        ];
        $faClass = $classMap[$faPrefix] ?? 'fa-solid';
        return '<i class="' . $faClass . ' fa-' . esc($iconName) . '"' . $styleAttr . '></i>';
    }

    // Handle plain "fa-*" format (assume solid)
    if (preg_match('/^fa-([a-z0-9-]+)$/i', $format, $m)) {
        return '<i class="fa-solid fa-' . esc($m[1]) . '"' . $styleAttr . '></i>';
    }

    // Check if it's a formatted icon (prefix:name or prefix:style:name)
    if (strpos($format, ':') !== false) {
        $parts = explode(':', $format);
        $prefix = $parts[0];

        // Handle new fa:style:name format (fa:solid:home, fa:regular:star, fa:brands:facebook)
        if ($prefix === 'fa' && count($parts) === 3) {
            $iconStyle = $parts[1]; // solid, regular, brands
            $iconName = $parts[2];
            $faClass = $iconStyle === 'solid' ? 'fa-solid' : ($iconStyle === 'regular' ? 'fa-regular' : 'fa-brands');
            return '<i class="' . $faClass . ' fa-' . esc($iconName) . '"' . $styleAttr . '></i>';
        }

        $iconName = $parts[1] ?? '';

        switch ($prefix) {
            case 'fa':
                return '<i class="fa-solid fa-' . esc($iconName) . '"' . $styleAttr . '></i>';
            case 'far':
                return '<i class="fa-regular fa-' . esc($iconName) . '"' . $styleAttr . '></i>';
            case 'fab':
                return '<i class="fa-brands fa-' . esc($iconName) . '"' . $styleAttr . '></i>';
            case 'material':
                return '<span class="material-icons"' . $styleAttr . '>' . esc($iconName) . '</span>';
            case 'material-o':
                return '<span class="material-icons-outlined"' . $styleAttr . '>' . esc($iconName) . '</span>';
            case 'bi':
                return '<i class="bi bi-' . esc($iconName) . '"' . $styleAttr . '></i>';
            case 'lucide':
                return '<span' . $styleAttr . '>⬡</span>';
            default:
                return '<span' . $styleAttr . '>' . esc($format) . '</span>';
        }
    }

    // Emoji detection
    if (preg_match('/[\x{1F000}-\x{1F9FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $format)) {
        return '<span' . $styleAttr . '>' . $format . '</span>';
    }

    // Legacy icon names
    $legacyMap = [
        'star' => '★', 'heart' => '♥', 'check' => '✓', 'arrow-right' => '→',
        'arrow-left' => '←', 'arrow-up' => '↑', 'arrow-down' => '↓',
        'plus' => '+', 'minus' => '−', 'close' => '✕', 'menu' => '☰',
        'search' => '🔍', 'user' => '👤', 'mail' => '✉', 'phone' => '☎',
        'location' => '📍', 'calendar' => '📅', 'clock' => '🕐',
        'settings' => '⚙', 'home' => '🏠'
    ];

    $symbol = $legacyMap[$format] ?? $format;
    return '<span' . $styleAttr . '>' . $symbol . '</span>';
}

/**
 * Theme Builder 3.0 - Renderer
 *
 * Renders Theme Builder content to HTML output.
 * Handles Section→Row→Column→Module hierarchy.
 *
 * @package ThemeBuilder
 * @version 3.0
 */

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Get element ID or generate one
 */
function tb_get_element_id(array $element): string
{
    return $element['id'] ?? 'tb-' . uniqid();
}

/**
 * Build CSS classes string
 */
function tb_build_classes(string $baseClass, array $element): string
{
    $classes = [$baseClass];

    if (!empty($element['css_class'])) {
        $classes[] = $element['css_class'];
    }

    if (!empty($element['advanced']['css_class'])) {
        $classes[] = $element['advanced']['css_class'];
    }

    return implode(' ', $classes);
}

/**
 * Normalize design array keys from camelCase to snake_case
 * Handles both camelCase (from sidebar) and snake_case (from modal) keys
 */
function tb_normalize_design_keys(array $design): array
{
    $normalized = [];
    foreach ($design as $key => $value) {
        // Convert camelCase to snake_case
        $normalizedKey = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));
        $normalized[$normalizedKey] = $value;
    }
    return $normalized;
}

/**
 * Build inline styles from design array
 * Accepts both camelCase and snake_case keys (normalizes internally)
 */
function tb_build_inline_styles(array $design): string
{
    // Normalize keys to snake_case for consistent lookups
    $design = tb_normalize_design_keys($design);

    $styles = [];

    // Dimensions
    if (!empty($design['width'])) {
        $styles[] = 'width:' . $design['width'];
    }
    if (!empty($design['height'])) {
        $styles[] = 'height:' . $design['height'];
    }
    if (!empty($design['min_height'])) {
        $styles[] = 'min-height:' . $design['min_height'];
    }
    if (!empty($design['max_width'])) {
        $styles[] = 'max-width:' . $design['max_width'];
    }

    // Background
    if (!empty($design['background_color'])) {
        $styles[] = 'background-color:' . tb_map_color($design['background_color']);
    }
    if (!empty($design['background_image'])) {
        $styles[] = 'background-image:url(' . $design['background_image'] . ')';
        $styles[] = 'background-size:cover';
        $styles[] = 'background-position:center';
    }

    // Text
    if (!empty($design['text_color'])) {
        $styles[] = 'color:' . tb_map_color($design['text_color']);
    }
    if (!empty($design['font_size'])) {
        $styles[] = 'font-size:' . $design['font_size'];
    }
    if (!empty($design['font_family'])) {
        $styles[] = 'font-family:' . $design['font_family'];
    }
    if (!empty($design['font_style'])) {
        $styles[] = 'font-style:' . $design['font_style'];
    }
    if (!empty($design['text_align'])) {
        $styles[] = 'text-align:' . $design['text_align'];
    }
    if (!empty($design['font_weight'])) {
        $styles[] = 'font-weight:' . $design['font_weight'];
    }
    if (!empty($design['line_height'])) {
        $styles[] = 'line-height:' . $design['line_height'];
    }
    if (!empty($design['letter_spacing'])) {
        $styles[] = 'letter-spacing:' . $design['letter_spacing'];
    }
    if (!empty($design['text_transform'])) {
        $styles[] = 'text-transform:' . $design['text_transform'];
    }
    if (!empty($design['text_shadow'])) {
        $styles[] = 'text-shadow:' . $design['text_shadow'];
    }

    // Padding
    if (!empty($design['padding'])) {
        $styles[] = 'padding:' . $design['padding'];
    }
    if (!empty($design['padding_top'])) {
        $styles[] = 'padding-top:' . $design['padding_top'];
    }
    if (!empty($design['padding_bottom'])) {
        $styles[] = 'padding-bottom:' . $design['padding_bottom'];
    }
    if (!empty($design['padding_left'])) {
        $styles[] = 'padding-left:' . $design['padding_left'];
    }
    if (!empty($design['padding_right'])) {
        $styles[] = 'padding-right:' . $design['padding_right'];
    }

    // Margin - support both shorthand and individual sides
    if (!empty($design['margin'])) {
        $styles[] = 'margin:' . $design['margin'];
    } else {
        if (!empty($design['margin_top'])) {
            $styles[] = 'margin-top:' . $design['margin_top'];
        }
        if (!empty($design['margin_right'])) {
            $styles[] = 'margin-right:' . $design['margin_right'];
        }
        if (!empty($design['margin_bottom'])) {
            $styles[] = 'margin-bottom:' . $design['margin_bottom'];
        }
        if (!empty($design['margin_left'])) {
            $styles[] = 'margin-left:' . $design['margin_left'];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // BORDER - Support both shorthand and individual sides
    // Sidebar saves: border_width_top, border_width_right, border_width_bottom, border_width_left
    // ═══════════════════════════════════════════════════════════════════════════
    $hasBorderColor = !empty($design['border_color']);
    $borderStyle = $design['border_style'] ?? 'solid';

    // Check for individual border widths
    $bwt = $design['border_width_top'] ?? null;
    $bwr = $design['border_width_right'] ?? null;
    $bwb = $design['border_width_bottom'] ?? null;
    $bwl = $design['border_width_left'] ?? null;

    if ($hasBorderColor && ($bwt !== null || $bwr !== null || $bwb !== null || $bwl !== null)) {
        // Individual border widths
        $color = tb_map_color($design['border_color']);
        if (!empty($bwt) && $bwt !== '0px') {
            $styles[] = 'border-top:' . $bwt . ' ' . $borderStyle . ' ' . $color;
        }
        if (!empty($bwr) && $bwr !== '0px') {
            $styles[] = 'border-right:' . $bwr . ' ' . $borderStyle . ' ' . $color;
        }
        if (!empty($bwb) && $bwb !== '0px') {
            $styles[] = 'border-bottom:' . $bwb . ' ' . $borderStyle . ' ' . $color;
        }
        if (!empty($bwl) && $bwl !== '0px') {
            $styles[] = 'border-left:' . $bwl . ' ' . $borderStyle . ' ' . $color;
        }
    } elseif (!empty($design['border_width']) && $hasBorderColor) {
        // Shorthand border
        $styles[] = 'border:' . $design['border_width'] . ' ' . $borderStyle . ' ' . tb_map_color($design['border_color']);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // BORDER RADIUS - Support both shorthand and individual corners
    // Sidebar saves: border_radius_tl, border_radius_tr, border_radius_br, border_radius_bl
    // ═══════════════════════════════════════════════════════════════════════════
    $brtl = $design['border_radius_tl'] ?? null;
    $brtr = $design['border_radius_tr'] ?? null;
    $brbr = $design['border_radius_br'] ?? null;
    $brbl = $design['border_radius_bl'] ?? null;

    if ($brtl !== null || $brtr !== null || $brbr !== null || $brbl !== null) {
        // Individual corners
        $tl = $brtl ?: '0px';
        $tr = $brtr ?: '0px';
        $br = $brbr ?: '0px';
        $bl = $brbl ?: '0px';
        // Only output if not all zeros
        if ($tl !== '0px' || $tr !== '0px' || $br !== '0px' || $bl !== '0px') {
            $styles[] = 'border-radius:' . $tl . ' ' . $tr . ' ' . $br . ' ' . $bl;
        }
    } elseif (!empty($design['border_radius'])) {
        // Shorthand radius
        $styles[] = 'border-radius:' . $design['border_radius'];
    }

    // Box shadow - support both single string and individual properties
    if (!empty($design['box_shadow'])) {
        $styles[] = 'box-shadow:' . $design['box_shadow'];
    } elseif (!empty($design['box_shadow_enabled'])) {
        $h = (int)($design['box_shadow_horizontal'] ?? 0);
        $v = (int)($design['box_shadow_vertical'] ?? 4);
        $b = (int)($design['box_shadow_blur'] ?? 10);
        $s = (int)($design['box_shadow_spread'] ?? 0);
        $c = $design['box_shadow_color'] ?? 'rgba(0,0,0,0.1)';
        $inset = !empty($design['box_shadow_inset']) ? 'inset ' : '';
        $styles[] = 'box-shadow:' . $inset . $h . 'px ' . $v . 'px ' . $b . 'px ' . $s . 'px ' . $c;
    }

    // Animation - check animation_enabled flag AND animation_type
    // Editor stores: animation_enabled (bool), animation_type (string), animation_duration (seconds), animation_delay (seconds), animation_easing (string)
    $animEnabled = !empty($design['animation_enabled']);
    $animType = $design['animation_type'] ?? '';
    if ($animEnabled && !empty($animType) && $animType !== 'none') {
        $animDuration = floatval($design['animation_duration'] ?? 0.6) . 's';
        $animDelay = floatval($design['animation_delay'] ?? 0) . 's';
        $animEasing = $design['animation_easing'] ?? 'ease-out';
        $styles[] = 'animation:' . $animType . ' ' . $animDuration . ' ' . $animEasing . ' ' . $animDelay . ' both';
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // TRANSFORM - Build transform string from individual properties
    // Sidebar saves: transform_scale_x, transform_scale_y, transform_rotate_z,
    //                transform_translate_x, transform_translate_y, transform_skew_x, transform_skew_y
    // ═══════════════════════════════════════════════════════════════════════════
    $transforms = [];

    // Scale
    $scaleX = $design['transform_scale_x'] ?? null;
    $scaleY = $design['transform_scale_y'] ?? null;
    if ($scaleX !== null && $scaleX !== '' && $scaleX !== '100') {
        $transforms[] = 'scaleX(' . (floatval($scaleX) / 100) . ')';
    }
    if ($scaleY !== null && $scaleY !== '' && $scaleY !== '100') {
        $transforms[] = 'scaleY(' . (floatval($scaleY) / 100) . ')';
    }

    // Rotate
    $rotateZ = $design['transform_rotate_z'] ?? null;
    if ($rotateZ !== null && $rotateZ !== '' && $rotateZ !== '0') {
        $transforms[] = 'rotate(' . intval($rotateZ) . 'deg)';
    }

    // Translate
    $translateX = $design['transform_translate_x'] ?? null;
    $translateY = $design['transform_translate_y'] ?? null;
    if ($translateX !== null && $translateX !== '' && $translateX !== '0') {
        $transforms[] = 'translateX(' . intval($translateX) . 'px)';
    }
    if ($translateY !== null && $translateY !== '' && $translateY !== '0') {
        $transforms[] = 'translateY(' . intval($translateY) . 'px)';
    }

    // Skew
    $skewX = $design['transform_skew_x'] ?? null;
    $skewY = $design['transform_skew_y'] ?? null;
    if ($skewX !== null && $skewX !== '' && $skewX !== '0') {
        $transforms[] = 'skewX(' . intval($skewX) . 'deg)';
    }
    if ($skewY !== null && $skewY !== '' && $skewY !== '0') {
        $transforms[] = 'skewY(' . intval($skewY) . 'deg)';
    }

    if (!empty($transforms)) {
        $styles[] = 'transform:' . implode(' ', $transforms);
    }

    // Transform origin
    if (!empty($design['transform_origin']) && $design['transform_origin'] !== 'center center') {
        $styles[] = 'transform-origin:' . $design['transform_origin'];
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // FILTERS - Read from design with filter_ prefix (sidebar format)
    // Sidebar saves: filter_blur, filter_brightness, filter_contrast, filter_saturation, filter_grayscale, filter_opacity
    // ═══════════════════════════════════════════════════════════════════════════
    $filters = [];

    $blur = $design['filter_blur'] ?? null;
    if ($blur !== null && $blur !== '' && floatval($blur) > 0) {
        $filters[] = 'blur(' . floatval($blur) . 'px)';
    }

    $brightness = $design['filter_brightness'] ?? null;
    if ($brightness !== null && $brightness !== '' && floatval($brightness) != 100) {
        $filters[] = 'brightness(' . (floatval($brightness) / 100) . ')';
    }

    $contrast = $design['filter_contrast'] ?? null;
    if ($contrast !== null && $contrast !== '' && floatval($contrast) != 100) {
        $filters[] = 'contrast(' . (floatval($contrast) / 100) . ')';
    }

    $saturation = $design['filter_saturation'] ?? $design['filter_saturate'] ?? null;
    if ($saturation !== null && $saturation !== '' && floatval($saturation) != 100) {
        $filters[] = 'saturate(' . (floatval($saturation) / 100) . ')';
    }

    $grayscale = $design['filter_grayscale'] ?? null;
    if ($grayscale !== null && $grayscale !== '' && floatval($grayscale) > 0) {
        $filters[] = 'grayscale(' . floatval($grayscale) . '%)';
    }

    $opacity = $design['filter_opacity'] ?? null;
    if ($opacity !== null && $opacity !== '' && floatval($opacity) < 100) {
        $filters[] = 'opacity(' . (floatval($opacity) / 100) . ')';
    }

    if (!empty($filters)) {
        $styles[] = 'filter:' . implode(' ', $filters);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // Z-INDEX
    // ═══════════════════════════════════════════════════════════════════════════
    if (!empty($design['z_index'])) {
        $styles[] = 'z-index:' . intval($design['z_index']);
    }

    return implode(';', $styles);
}

/**
 * Build CSS filter styles from settings
 */
function tb_build_filter_styles(array $settings): string
{
    $filters = [];

    if (isset($settings['blur']) && $settings['blur'] > 0) {
        $filters[] = 'blur(' . $settings['blur'] . 'px)';
    }
    if (isset($settings['brightness']) && $settings['brightness'] != 100) {
        $filters[] = 'brightness(' . ($settings['brightness'] / 100) . ')';
    }
    if (isset($settings['contrast']) && $settings['contrast'] != 100) {
        $filters[] = 'contrast(' . ($settings['contrast'] / 100) . ')';
    }
    if (isset($settings['grayscale']) && $settings['grayscale'] > 0) {
        $filters[] = 'grayscale(' . $settings['grayscale'] . '%)';
    }
    if (isset($settings['saturate']) && $settings['saturate'] != 100) {
        $filters[] = 'saturate(' . ($settings['saturate'] / 100) . ')';
    }
    if (isset($settings['sepia']) && $settings['sepia'] > 0) {
        $filters[] = 'sepia(' . $settings['sepia'] . '%)';
    }
    if (isset($settings['opacity']) && $settings['opacity'] < 100) {
        $filters[] = 'opacity(' . ($settings['opacity'] / 100) . ')';
    }

    if (empty($filters)) {
        return '';
    }

    return 'filter:' . implode(' ', $filters);
}

/**
 * Render complete page builder content to HTML
 *
 * @param array $content The page content structure
 * @param array $options Rendering options
 * @return string Generated HTML
 */
function tb_render_page(array $content, array $options = []): string
{
    $html = '';
    $sections = $content['sections'] ?? [];

    foreach ($sections as $sIdx => $section) {
        $options['sIdx'] = $sIdx;
        $html .= tb_render_section($section, $options);
    }
    
    // Generate and append hover CSS
    $hoverCss = tb_generate_hover_css($content);
    if ($hoverCss) {
        $html .= '<style id="tb-hover-styles">' . $hoverCss . '</style>';
    }

    return $html;
}

/**
 * Render a section element
 *
 * @param array $section Section data
 * @param array $options Rendering options
 * @return string Generated HTML
 */
function tb_render_section(array $section, array $options = []): string
{
    $sIdx = $options['sIdx'] ?? 0;
    $id = tb_get_element_id($section);
    $classes = tb_build_classes('tb-section', $section);
    $design = $section['design'] ?? [];
    $styles = tb_build_inline_styles($design);
    
    // Add position relative for overlay support
    $hasOverlay = !empty($design['background_overlay']);
    if ($hasOverlay) {
        $styles .= ($styles ? ';' : '') . 'position:relative';
    }

    $html = '<section id="' . esc($id) . '" class="' . esc($classes) . '"';
    if ($styles) {
        $html .= ' style="' . esc($styles) . '"';
    }
    $html .= '>';
    
    // Add overlay div if background_overlay is set
    if ($hasOverlay) {
        $overlayColor = esc($design['background_overlay']);
        $html .= '<div class="tb-section-overlay" style="position:absolute;top:0;left:0;right:0;bottom:0;background:' . $overlayColor . ';pointer-events:none;z-index:1"></div>';
    }

    $html .= '<div class="tb-section-inner" style="position:relative;z-index:2">';

    $rows = $section['rows'] ?? [];
    foreach ($rows as $rIdx => $row) {
        $options['rIdx'] = $rIdx;
        $html .= tb_render_row($row, $options);
    }

    $html .= '</div>';
    $html .= '</section>';

    return $html;
}

/**
 * Render a row element
 *
 * @param array $row Row data
 * @param array $options Rendering options
 * @return string Generated HTML
 */
function tb_render_row(array $row, array $options = []): string
{
    $id = tb_get_element_id($row);
    $classes = tb_build_classes('tb-row', $row);
    $styles = tb_build_inline_styles($row['design'] ?? []);

    $html = '<div id="' . esc($id) . '" class="' . esc($classes) . '"';
    if ($styles) {
        $html .= ' style="' . esc($styles) . '"';
    }
    $html .= '>';

    $columns = $row['columns'] ?? [];
    $columnCount = count($columns);

    foreach ($columns as $index => $column) {
        $html .= tb_render_column($column, $columnCount, $index, $options);
    }

    $html .= '</div>';

    return $html;
}

/**
 * Render a column element
 *
 * @param array $column Column data
 * @param int $totalColumns Total columns in row
 * @param int $index Column index
 * @param array $options Rendering options
 * @return string Generated HTML
 */
function tb_render_column(array $column, int $totalColumns, int $index, array $options = []): string
{
    $id = tb_get_element_id($column);
    $width = $column['width'] ?? (100 / $totalColumns);

    // Convert to numeric if string with %
    if (is_string($width)) {
        $width = (float) str_replace('%', '', $width);
    }

    // Map width to CSS class for responsive grid
    $widthClass = '';
    if (abs($width - 100) < 1) $widthClass = 'tb-col-100';
    elseif (abs($width - 80) < 1) $widthClass = 'tb-col-80';
    elseif (abs($width - 75) < 1) $widthClass = 'tb-col-75';
    elseif (abs($width - 66.666) < 2) $widthClass = 'tb-col-66';
    elseif (abs($width - 60) < 1) $widthClass = 'tb-col-60';
    elseif (abs($width - 50) < 1) $widthClass = 'tb-col-50';
    elseif (abs($width - 40) < 1) $widthClass = 'tb-col-40';
    elseif (abs($width - 33.333) < 2) $widthClass = 'tb-col-33';
    elseif (abs($width - 25) < 1) $widthClass = 'tb-col-25';
    elseif (abs($width - 20) < 1) $widthClass = 'tb-col-20';

    $baseClasses = 'tb-column' . ($widthClass ? ' ' . $widthClass : '');
    $classes = tb_build_classes($baseClasses, $column);

    $design = $column['design'] ?? [];
    // Don't include width in inline styles - CSS classes handle it
    $styles = tb_build_inline_styles($design);

    $html = '<div id="' . esc($id) . '" class="' . esc($classes) . '"';
    if ($styles) {
        $html .= ' style="' . esc($styles) . '"';
    }
    $html .= '>';

    $modules = $column['modules'] ?? [];
    $options['cIdx'] = $index; // Pass column index
    foreach ($modules as $mIdx => $module) {
        $options['mIdx'] = $mIdx;
        $html .= tb_render_module($module, $options);
    }

    $html .= '</div>';

    return $html;
}

/**
 * Render a module element
 *
 * @param array $module Module data
 * @param array $options Rendering options
 * @return string Generated HTML
 */
function tb_render_module(array $module, array $options = []): string
{
    // ═══════════════════════════════════════════════════════════════════════════
    // GLOBAL MAPPER: AI TB 4.0 uses 'settings', TB 3.0 uses 'content'/'design'
    // This normalizes the structure BEFORE any module-specific rendering
    // ═══════════════════════════════════════════════════════════════════════════
    $settings = $module['settings'] ?? [];
    
    if (!empty($settings)) {
        // 1. If content is empty/missing, copy settings to content
        if (empty($module['content']) || !is_array($module['content'])) {
            $module['content'] = [];
        }
        
        // 2. Copy all non-design keys from settings to content
        foreach ($settings as $key => $value) {
            if ($key !== 'design' && !isset($module['content'][$key])) {
                $module['content'][$key] = $value;
            }
        }
        
        // 3. Merge settings.design into module.design
        if (isset($settings['design']) && is_array($settings['design'])) {
            $module['design'] = array_merge($module['design'] ?? [], $settings['design']);
        }

        // 4. Copy animation settings from settings to design (animation is stored directly in settings)
        $animationKeys = [
            'animation_enabled', 'animation_type', 'animation_duration', 'animation_delay', 'animation_easing',
            'scroll_trigger_enabled', 'scroll_trigger_point', 'scroll_animate_once'
        ];
        if (!isset($module['design'])) {
            $module['design'] = [];
        }
        foreach ($animationKeys as $key) {
            if (isset($settings[$key]) && !isset($module['design'][$key])) {
                $module['design'][$key] = $settings[$key];
            }
        }
    }
    
    // Normalize common key variations in content
    $content = &$module['content'];
    $moduleType = $module['type'] ?? 'text';
    if (is_array($content)) {
        // image: src vs url
        if (!isset($content['src']) && isset($content['url'])) {
            $content['src'] = $content['url'];
        }
        // menu/navigation: items normalization (handle string arrays) - ONLY for menu modules
        // Skip normalization for list, accordion, toggle, tabs modules which use different item formats
        $menuModules = ['menu', 'nav', 'navigation', 'fullwidth_menu', 'footer_menu'];
        if (in_array($moduleType, $menuModules) && isset($content['items']) && is_array($content['items'])) {
            $normalizedItems = [];
            foreach ($content['items'] as $item) {
                if (is_string($item)) {
                    $normalizedItems[] = [
                        'label' => $item,
                        'name' => $item,
                        'url' => '/' . strtolower(str_replace(' ', '-', $item)),
                        'link' => '/' . strtolower(str_replace(' ', '-', $item)),
                        'target' => '_self'
                    ];
                } elseif (is_array($item)) {
                    // Normalize object keys
                    $normalizedItems[] = [
                        'label' => $item['label'] ?? $item['name'] ?? $item['title'] ?? $item['text'] ?? 'Link',
                        'name' => $item['name'] ?? $item['label'] ?? $item['title'] ?? $item['text'] ?? 'Link',
                        'url' => $item['url'] ?? $item['link'] ?? $item['href'] ?? '#',
                        'link' => $item['link'] ?? $item['url'] ?? $item['href'] ?? '#',
                        'target' => $item['target'] ?? '_self',
                        'icon' => $item['icon'] ?? null,
                        'image' => $item['image'] ?? null,
                    ];
                }
            }
            $content['items'] = $normalizedItems;
        }
    }
    // ═══════════════════════════════════════════════════════════════════════════
    
    $type = $module['type'] ?? 'text';

    // Module type aliases (for backward compatibility and AI-generated content)
    $typeAliases = [
        'social-icons' => 'social',
        'social_icons' => 'social',
        'newsletter' => 'signup',
        'nav' => 'menu',
        'navigation' => 'menu',
        'title' => 'heading',
        'paragraph' => 'text',
        'btn' => 'button',
        'img' => 'image',
    ];
    $type = $typeAliases[$type] ?? $type;

    // ═══════════════════════════════════════════════════════════════════════════
    // FIX: Generate module ID and store it BACK into the module array
    // This ensures tb_generate_module_element_css can reference the correct ID
    // ═══════════════════════════════════════════════════════════════════════════
    $id = tb_get_element_id($module);
    $module['id'] = $id; // Store ID back into module for element CSS generation
    $classes = tb_build_classes('tb-module tb-module-' . $type, $module);
    // Merge design and settings for inline styles (TB saves spacing to settings, not design)
    $designData = array_merge($module['design'] ?? [], $module['settings'] ?? []);
    $designStyles = tb_build_inline_styles($designData);
    $filterStyles = tb_build_filter_styles($module['settings'] ?? []);
    $styles = trim($designStyles . ($filterStyles ? '; ' . $filterStyles : ''), '; ');

    // Get module path for hover CSS targeting
    $sIdx = $options['sIdx'] ?? 0;
    $rIdx = $options['rIdx'] ?? 0;
    $cIdx = $options['cIdx'] ?? 0;
    $mIdx = $options['mIdx'] ?? 0;
    $modulePath = "{$sIdx}-{$rIdx}-{$cIdx}-{$mIdx}";

    $html = '<div id="' . esc($id) . '" class="' . esc($classes) . '" data-module-path="' . esc($modulePath) . '"';

    // Add scroll trigger data attributes for frontend JS
    if (!empty($designData['scroll_trigger_enabled'])) {
        $html .= ' data-scroll-trigger="true"';
        $html .= ' data-trigger-point="' . esc($designData['scroll_trigger_point'] ?? '80') . '"';
        $html .= ' data-animate-once="' . (!empty($designData['scroll_animate_once']) ? 'true' : 'false') . '"';
    }

    if ($styles) {
        $html .= ' style="' . esc($styles) . '"';
    }
    $html .= '>';

    // Render module-specific content
    $renderFn = 'tb_render_module_' . $type;
    if (function_exists($renderFn)) {
        $html .= $renderFn($module, $options);
    } else {
        $html .= tb_render_module_fallback($module, $options);
    }

    $html .= '</div>';

    return $html;
}

/**
 * Fallback renderer for unknown module types
 */
function tb_render_module_fallback(array $module, array $options = []): string
{
    $type = $module['type'] ?? 'unknown';
    $content = $module['content'] ?? [];
    
    // Try to render something sensible from content
    if (isset($content['text'])) {
        return '<div class="tb-fallback-content">' . ($content['text'] ?? '') . '</div>';
    }
    if (isset($content['title'])) {
        return '<div class="tb-fallback-content"><h3>' . esc($content['title'] ?? '') . '</h3></div>';
    }
    
    return '<div class="tb-fallback-content" style="padding:20px;background:#f5f5f5;border:1px dashed #ccc;text-align:center;color:#666">Module: ' . esc($type) . '</div>';
}

/**
 * Render text module content
 * Applies design properties: text_color, font_size, line_height, text_align, max_width, margin
 */
function tb_render_module_text(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $d = $module['design'] ?? [];
    $text = $c['text'] ?? $c['quote'] ?? '';
    $moduleId = $module['id'] ?? 'text-' . uniqid();

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $html = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    // Build inline styles from design properties
    $styles = [];

    if (!empty($d['text_color'])) {
        $styles[] = 'color:' . esc($d['text_color']);
    }
    if (!empty($d['font_size'])) {
        $styles[] = 'font-size:' . esc($d['font_size']);
    }
    if (!empty($d['line_height'])) {
        $styles[] = 'line-height:' . esc($d['line_height']);
    }
    if (!empty($d['text_align'])) {
        $styles[] = 'text-align:' . esc($d['text_align']);
    }
    if (!empty($d['font_weight'])) {
        $styles[] = 'font-weight:' . esc($d['font_weight']);
    }
    if (!empty($d['font_family'])) {
        $styles[] = 'font-family:' . esc($d['font_family']);
    }
    if (!empty($d['font_style'])) {
        $styles[] = 'font-style:' . esc($d['font_style']);
    }
    if (!empty($d['letter_spacing'])) {
        $styles[] = 'letter-spacing:' . esc($d['letter_spacing']);
    }
    if (!empty($d['text_transform'])) {
        $styles[] = 'text-transform:' . esc($d['text_transform']);
    }
    if (!empty($d['max_width'])) {
        $styles[] = 'max-width:' . esc($d['max_width']);
    }

    // Margin (support both single value and individual sides)
    if (!empty($d['margin'])) {
        $styles[] = 'margin:' . esc($d['margin']);
    } else {
        if (!empty($d['margin_top'])) {
            $styles[] = 'margin-top:' . esc($d['margin_top']);
        }
        if (!empty($d['margin_bottom'])) {
            $styles[] = 'margin-bottom:' . esc($d['margin_bottom']);
        }
        if (!empty($d['margin_left'])) {
            $styles[] = 'margin-left:' . esc($d['margin_left']);
        }
        if (!empty($d['margin_right'])) {
            $styles[] = 'margin-right:' . esc($d['margin_right']);
        }
    }

    $styleAttr = !empty($styles) ? ' style="' . implode(';', $styles) . '"' : '';

    $html .= '<div class="tb-text" id="' . esc($moduleId) . '"' . $styleAttr . '>' . $text . '</div>';
    return $html;
}

/**
 * Render image module content
 * Applies design properties: border_radius, box_shadow, max_width
 */
function tb_render_module_image(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $d = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'image-' . uniqid();
    $src = $c['src'] ?? $c['url'] ?? '';
    $alt = $c['alt'] ?? '';
    $title = $c['title'] ?? '';
    $caption = $c['caption'] ?? '';
    $lazy = $module['advanced']['lazy_load'] ?? true;
    $showOverlay = $c['show_overlay'] ?? $d['show_overlay'] ?? false;

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    // Size and display settings - check both content and design
    $width = $c['width'] ?? $d['width'] ?? '100%';
    $maxWidth = $c['max_width'] ?? $d['max_width'] ?? '';
    $height = $c['height'] ?? $d['height'] ?? 'auto';
    $objectFit = $c['object_fit'] ?? $d['object_fit'] ?? '';
    $alignment = $c['alignment'] ?? $d['alignment'] ?? $d['text_align'] ?? 'center';
    $borderRadius = $c['border_radius'] ?? $d['border_radius'] ?? '0';

    if (empty($src)) {
        return '';
    }

    // Build img style
    $imgStyles = [];
    $imgStyles[] = 'display:block';
    $imgStyles[] = 'width:100%';
    $imgStyles[] = 'height:' . esc($height);
    if ($objectFit) $imgStyles[] = 'object-fit:' . esc($objectFit);

    // Container styles
    $containerStyles = [];
    $containerStyles[] = 'position:relative';
    $containerStyles[] = 'display:inline-block';
    $containerStyles[] = 'width:' . esc($width);
    if ($maxWidth) $containerStyles[] = 'max-width:' . esc($maxWidth);
    if ($borderRadius && $borderRadius !== '0') {
        $containerStyles[] = 'border-radius:' . esc($borderRadius);
        $containerStyles[] = 'overflow:hidden';
    }

    // Box shadow from design
    if (!empty($d['box_shadow'])) {
        $containerStyles[] = 'box-shadow:' . esc($d['box_shadow']);
    } elseif (!empty($d['box_shadow_blur'])) {
        $sh = $d['box_shadow_horizontal'] ?? '0';
        $sv = $d['box_shadow_vertical'] ?? '4';
        $sb = $d['box_shadow_blur'] ?? '10';
        $ss = $d['box_shadow_spread'] ?? '0';
        $sc = $d['box_shadow_color'] ?? 'rgba(0,0,0,0.1)';
        $containerStyles[] = 'box-shadow:' . esc($sh) . 'px ' . esc($sv) . 'px ' . esc($sb) . 'px ' . esc($ss) . 'px ' . esc($sc);
    }

    // Wrapper for alignment
    $wrapperStyle = 'text-align:' . esc($alignment);

    // Build HTML - ID is on outer wrapper from tb_render_module, don't duplicate here
    $html = '<div class="tb-image" style="' . $wrapperStyle . '">';
    $html .= '<div class="tb-image-container" style="' . implode(';', $containerStyles) . '">';

    // Image
    $html .= '<img class="tb-image-img" src="' . esc($src) . '" alt="' . esc($alt) . '" style="' . implode(';', $imgStyles) . '"';
    if ($title) $html .= ' title="' . esc($title) . '"';
    if ($lazy) $html .= ' loading="lazy"';
    $html .= '>';

    // Overlay (if enabled)
    if ($showOverlay) {
        $html .= '<div class="tb-image-overlay" style="position:absolute;inset:0;pointer-events:none"></div>';
    }

    $html .= '</div>'; // container

    // Caption (if provided)
    if (!empty($caption)) {
        $html .= '<p class="tb-image-caption" style="margin-top:8px;text-align:' . esc($alignment) . '">' . esc($caption) . '</p>';
    }

    $html .= '</div>'; // wrapper
    return $output . $html;
}

/**
 * Render button module content
 */
function tb_render_module_button(array $module, array $options = []): string
{
    $c = $module["content"] ?? [];
    $d = $module["design"] ?? [];
    $moduleId = $module['id'] ?? 'button-' . uniqid();

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $text = $c['text'] ?? 'Button';
    $url = $c['url'] ?? '#';
    $target = $c['target'] ?? '_self';
    $btnStyle = $c['style'] ?? 'primary';

    // Colors based on button style - apply style presets (use CSS variables for theme compatibility)
    $defaultColors = [
        'primary' => ['bg' => 'var(--primary, #8b5cf6)', 'text' => 'var(--text, #ffffff)', 'border' => 'var(--primary, #8b5cf6)'],
        'secondary' => ['bg' => 'var(--secondary, #6366f1)', 'text' => 'var(--text, #ffffff)', 'border' => 'var(--secondary, #6366f1)'],
        'outline' => ['bg' => 'transparent', 'text' => 'var(--primary, #8b5cf6)', 'border' => 'var(--primary, #8b5cf6)'],
        'ghost' => ['bg' => 'transparent', 'text' => 'inherit', 'border' => 'transparent'],
    ];
    $styleColors = $defaultColors[$btnStyle] ?? $defaultColors['primary'];
    $bgColor = $d['background_color'] ?? $styleColors['bg'];
    $textColor = $d['text_color'] ?? $styleColors['text'];
    if ($btnStyle === 'outline' && empty($d['border_width_top']) && empty($d['border_width'])) {
        $d['border_width'] = '2px';
        $d['border_color'] = $styleColors['border'];
    }
    
    // Border settings - use actual keys from Design Settings
    $borderWidth = $d['border_width_top'] ?? $d['border_width'] ?? '0px';
    $borderColor = $d['border_color'] ?? $bgColor;
    $borderStyle = $d['border_style'] ?? 'solid';
    
    // Border radius - check both naming conventions (tl/tr/br/bl and top_left etc)
    $brTL = $d['border_radius_tl'] ?? $d['border_radius_top_left'] ?? $d['border_radius'] ?? '4px';
    $brTR = $d['border_radius_tr'] ?? $d['border_radius_top_right'] ?? $d['border_radius'] ?? '4px';
    $brBR = $d['border_radius_br'] ?? $d['border_radius_bottom_right'] ?? $d['border_radius'] ?? '4px';
    $brBL = $d['border_radius_bl'] ?? $d['border_radius_bottom_left'] ?? $d['border_radius'] ?? '4px';
    $borderRadius = $brTL . ' ' . $brTR . ' ' . $brBR . ' ' . $brBL;
    
    // Padding
    $paddingT = $d['padding_top'] ?? '12px';
    $paddingR = $d['padding_right'] ?? '24px';
    $paddingB = $d['padding_bottom'] ?? '12px';
    $paddingL = $d['padding_left'] ?? '24px';
    
    // Margin (from spacing box)
    $marginT = $d['margin_top'] ?? '0';
    $marginR = $d['margin_right'] ?? '0';
    $marginB = $d['margin_bottom'] ?? '0';
    $marginL = $d['margin_left'] ?? '0';
    
    // Box shadow
    $shadow = '';
    if (!empty($d['box_shadow_offset_x']) || !empty($d['box_shadow_offset_y']) || !empty($d['box_shadow_blur'])) {
        $sx = $d['box_shadow_offset_x'] ?? '0px';
        $sy = $d['box_shadow_offset_y'] ?? '4px';
        $sb = $d['box_shadow_blur'] ?? '8px';
        $ss = $d['box_shadow_spread'] ?? '0px';
        $sc = $d['box_shadow_color'] ?? 'rgba(0,0,0,0.2)';
        $shadow = "box-shadow:{$sx} {$sy} {$sb} {$ss} {$sc};";
    }
    
    // Animation
    $anim = $d['animation_type'] ?? '';
    $animClass = $anim && $anim !== 'none' ? ' tb-anim-' . $anim : '';
    $animDuration = ($d['animation_duration'] ?? '') ? '--webkit-animation-duration:' . $d['animation_duration'] . 'ms;animation-duration:' . $d['animation_duration'] . 'ms;' : '';
    $animDelay = ($d['animation_delay'] ?? '') ? '--webkit-animation-delay:' . $d['animation_delay'] . 'ms;animation-delay:' . $d['animation_delay'] . 'ms;' : '';
    
    // Build margin string (handle various formats)
    $marginStr = "";
    if ($marginT !== "0" || $marginR !== "0" || $marginB !== "0" || $marginL !== "0") {
        $mT = (strpos($marginT, "px") === false && is_numeric($marginT)) ? $marginT . "px" : $marginT;
        $mR = (strpos($marginR, "px") === false && is_numeric($marginR)) ? $marginR . "px" : $marginR;
        $mB = (strpos($marginB, "px") === false && is_numeric($marginB)) ? $marginB . "px" : $marginB;
        $mL = (strpos($marginL, "px") === false && is_numeric($marginL)) ? $marginL . "px" : $marginL;
        $marginStr = "margin:" . esc($mT) . " " . esc($mR) . " " . esc($mB) . " " . esc($mL) . ";";
    }
    
    $style = "display:inline-block;background:" . tb_map_color($bgColor) . ";color:" . tb_map_color($textColor) . ";padding:" . esc($paddingT) . " " . esc($paddingR) . " " . esc($paddingB) . " " . esc($paddingL) . ";border-radius:" . esc($borderRadius) . ";border:" . esc($borderWidth) . " " . esc($borderStyle) . " " . tb_map_color($borderColor) . ";text-decoration:none;font-weight:500;transition:all 0.2s;" . $marginStr . $shadow . $animDuration . $animDelay;

    $output .= '<a href="' . esc($url) . '" target="' . esc($target) . '" id="' . esc($moduleId) . '" class="tb-button' . $animClass . '" style="' . $style . '">' . esc($text) . '</a>';
    return $output;
}

/**
 * Render heading module content
 * Applies design properties: text_color, font_size, font_weight, text_align, margin, text_shadow
 */
function tb_render_module_heading(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $d = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'heading-' . uniqid();
    $text = $c['text'] ?? $c['quote'] ?? '';
    $level = $c['level'] ?? 'h2';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    // Validate heading level
    if (!in_array($level, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
        $level = 'h2';
    }

    // Build inline styles from design properties
    $styles = [];

    if (!empty($d['text_color'])) {
        $styles[] = 'color:' . esc($d['text_color']);
    }
    if (!empty($d['font_size'])) {
        $styles[] = 'font-size:' . esc($d['font_size']);
    }
    if (!empty($d['font_weight'])) {
        $styles[] = 'font-weight:' . esc($d['font_weight']);
    }
    if (!empty($d['text_align'])) {
        $styles[] = 'text-align:' . esc($d['text_align']);
    }
    if (!empty($d['line_height'])) {
        $styles[] = 'line-height:' . esc($d['line_height']);
    }
    if (!empty($d['letter_spacing'])) {
        $styles[] = 'letter-spacing:' . esc($d['letter_spacing']);
    }
    if (!empty($d['text_transform'])) {
        $styles[] = 'text-transform:' . esc($d['text_transform']);
    }
    if (!empty($d['text_shadow'])) {
        $styles[] = 'text-shadow:' . esc($d['text_shadow']);
    }
    if (!empty($d['font_family'])) {
        $styles[] = 'font-family:' . esc($d['font_family']);
    }
    if (!empty($d['font_style'])) {
        $styles[] = 'font-style:' . esc($d['font_style']);
    }

    // Margin
    if (!empty($d['margin'])) {
        $styles[] = 'margin:' . esc($d['margin']);
    } else {
        if (!empty($d['margin_top'])) {
            $styles[] = 'margin-top:' . esc($d['margin_top']);
        }
        if (!empty($d['margin_bottom'])) {
            $styles[] = 'margin-bottom:' . esc($d['margin_bottom']);
        }
        if (!empty($d['margin_left'])) {
            $styles[] = 'margin-left:' . esc($d['margin_left']);
        }
        if (!empty($d['margin_right'])) {
            $styles[] = 'margin-right:' . esc($d['margin_right']);
        }
    }

    // Max width (for centered headings)
    if (!empty($d['max_width'])) {
        $styles[] = 'max-width:' . esc($d['max_width']);
    }

    $styleAttr = !empty($styles) ? ' style="' . implode(';', $styles) . '"' : '';

    $output .= '<' . $level . ' class="tb-heading" id="' . esc($moduleId) . '"' . $styleAttr . '>' . $text . '</' . $level . '>';
    return $output;
}

/**
 * Render divider module content
 */
function tb_render_module_divider(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $moduleId = $module['id'] ?? 'divider-' . uniqid();
    $show = $c['show_divider'] ?? true;

    if (!$show) {
        return '';
    }

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';
    $output .= '<hr class="tb-divider" id="' . esc($moduleId) . '">';
    return $output;
}

/**
 * Render spacer module content
 */
function tb_render_module_spacer(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $moduleId = $module['id'] ?? 'spacer-' . uniqid();

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';
    $output .= '<div class="tb-spacer" id="' . esc($moduleId) . '"></div>';
    return $output;
}


/**
 * Render video module content
 */
function tb_render_module_video(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $moduleId = $module['id'] ?? 'video-' . uniqid();
    $url = $c['url'] ?? '';
    $autoplay = $c['autoplay'] ?? false;
    $controls = $c['controls'] ?? true;
    $aspectRatio = $module['design']['aspect_ratio'] ?? '16:9';

    if (empty($url)) {
        return '';
    }

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    // Calculate padding for aspect ratio
    $ratioParts = explode(':', $aspectRatio);
    $paddingPercent = (count($ratioParts) === 2 && (float)$ratioParts[0] > 0)
        ? ((float)$ratioParts[1] / (float)$ratioParts[0]) * 100
        : 56.25;

    $html = '<div class="tb-video" id="' . esc($moduleId) . '"><div class="tb-video-wrapper" style="position:relative;padding-bottom:' . $paddingPercent . '%;height:0;overflow:hidden;">';

    // Detect video type
    if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $m) ||
        preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $m)) {
        $videoId = $m[1];
        $params = [];
        if ($autoplay) $params[] = 'autoplay=1';
        if (!$controls) $params[] = 'controls=0';
        $paramStr = $params ? '?' . implode('&', $params) : '';
        $html .= '<iframe src="https://www.youtube.com/embed/' . esc($videoId) . $paramStr . '" ';
        $html .= 'style="position:absolute;top:0;left:0;width:100%;height:100%;" ';
        $html .= 'frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>';
    } elseif (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
        $videoId = $m[1];
        $params = [];
        if ($autoplay) $params[] = 'autoplay=1';
        $paramStr = $params ? '?' . implode('&', $params) : '';
        $html .= '<iframe src="https://player.vimeo.com/video/' . esc($videoId) . $paramStr . '" ';
        $html .= 'style="position:absolute;top:0;left:0;width:100%;height:100%;" ';
        $html .= 'frameborder="0" allowfullscreen></iframe>';
    } else {
        // Self-hosted video
        $html .= '<video style="position:absolute;top:0;left:0;width:100%;height:100%;"';
        if ($controls) $html .= ' controls';
        if ($autoplay) $html .= ' autoplay muted';
        $html .= '><source src="' . esc($url) . '">Your browser does not support video.</video>';
    }

    $html .= '</div></div>';
    return $output . $html;
}

/**
 * Render code module content
 */
function tb_render_module_code(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $moduleId = $module['id'] ?? 'code-' . uniqid();
    $code = $c['code'] ?? '';
    $language = $c['language'] ?? 'javascript';
    $theme = $module['design']['theme'] ?? 'dark';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $themeClass = $theme === 'dark' ? 'tb-code-dark' : 'tb-code-light';

    $html = '<div class="tb-code-block ' . esc($themeClass) . '" id="' . esc($moduleId) . '">';
    $html .= '<pre><code class="language-' . esc($language) . '">' . esc($code) . '</code></pre>';
    $html .= '</div>';

    return $output . $html;
}

/**
 * Render quote module content
 */
function tb_render_module_quote(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $moduleId = $module['id'] ?? 'quote-' . uniqid();
    // Use 'quote' first (new format from Content panel), fallback to 'text' (legacy)
    $text = !empty($c['quote']) ? $c['quote'] : ($c['text'] ?? '');
    $author = $c['author'] ?? '';
    $source = $c['source'] ?? '';
    $style = $module['design']['style'] ?? 'default';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $html = '<blockquote class="tb-quote tb-quote-' . esc($style) . '" id="' . esc($moduleId) . '">';
    $html .= '<p class="tb-quote-text">' . esc($text) . '</p>';

    if ($author || $source) {
        $html .= '<footer class="tb-quote-footer">';
        if ($author) {
            $html .= '<cite class="tb-quote-author">' . esc($author) . '</cite>';
        }
        if ($source) {
            $html .= '<span class="tb-quote-source">' . esc($source) . '</span>';
        }
        $html .= '</footer>';
    }

    $html .= '</blockquote>';
    return $output . $html;
}

/**
 * Render list module content
 */
function tb_render_module_list(array $module, array $options = []): string
{
    $moduleId = $module['id'] ?? 'list-' . uniqid();

    // Get content directly, handle both formats
    $content = $module['content'] ?? [];

    // If content is not an array, normalize it
    if (!is_array($content)) {
        $content = [];
    }

    // Get items - support both 'items' key and direct array
    $items = $content['items'] ?? [];
    $type = $content['type'] ?? 'unordered';
    $icon = $module['design']['icon'] ?? 'bullet';

    // If items is empty, provide default content so module is visible
    if (empty($items)) {
        $items = ['Item 1', 'Item 2', 'Item 3'];
    }

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $tag = $type === 'ordered' ? 'ol' : 'ul';
    $html = '<' . $tag . ' class="tb-list tb-list-' . esc($icon) . '" id="' . esc($moduleId) . '" style="margin:0;padding-left:20px">';

    foreach ($items as $item) {
        // Handle both string items and object items {text: '...'}
        $itemText = is_array($item) ? ($item['text'] ?? '') : (string)$item;
        if ($itemText !== '') {
            $html .= '<li>' . esc($itemText) . '</li>';
        }
    }

    $html .= '</' . $tag . '>';
    return $output . $html;
}

/**
 * Render icon module content
 */
function tb_render_module_icon(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $moduleId = $module['id'] ?? 'icon-' . uniqid();
    $icon = $c['icon'] ?? 'star';
    $size = $c['size'] ?? '48px';
    $color = $c['color'] ?? $module['settings']['color'] ?? $module['design']['color'] ?? 'inherit';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $output .= '<div class="tb-icon" id="' . esc($moduleId) . '" style="text-align:center;line-height:1">' .
               tb_render_icon_from_format($icon, $size, $color) .
               '</div>';
    return $output;
}

/**
 * Render map module content
 */
function tb_render_module_map(array $module, array $options = []): string
{
    $content = is_array($module['content'] ?? []) && !isset($module['content'][0]) ? $module['content'] : [];
    $moduleId = $module['id'] ?? 'map-' . uniqid();
    $address = $content['address'] ?? '';
    $lat = floatval($content['lat'] ?? 0);
    $lng = floatval($content['lng'] ?? 0);
    $zoom = intval($content['zoom'] ?? 14);
    $height = $module['design']['height'] ?? '300px';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    // Priority: 1) Address, 2) Lat/Lng, 3) Default
    if (!empty($address)) {
        $encodedAddress = urlencode($address);
        $embedUrl = 'https://maps.google.com/maps?q=' . $encodedAddress . '&z=' . $zoom . '&output=embed';
    } elseif ($lat != 0 || $lng != 0) {
        $embedUrl = 'https://maps.google.com/maps?q=' . $lat . ',' . $lng . '&z=' . $zoom . '&output=embed';
    } else {
        $embedUrl = 'https://maps.google.com/maps?q=London,UK&z=12&output=embed';
    }

    $html = '<div class="tb-map" id="' . esc($moduleId) . '"><div class="tb-map-wrapper" style="height:' . esc($height) . ';">';
    $html .= '<iframe src="' . esc($embedUrl) . '" style="width:100%;height:100%;border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
    $html .= '</div></div>';

    return $output . $html;
}


/**
 * Render accordion module content
 */
function tb_render_module_accordion(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $moduleId = $module['id'] ?? 'accordion-' . uniqid();
    $items = $c['items'] ?? [];
    $style = $module['design']['style'] ?? 'default';

    if (empty($items)) {
        return '';
    }

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $html = '<div class="tb-accordion tb-accordion-' . esc($style) . '" id="' . esc($moduleId) . '">';

    foreach ($items as $idx => $item) {
        $title = $item['title'] ?? 'Item ' . ($idx + 1);
        $content = $item['content'] ?? '';
        $isFirst = $idx === 0;

        $html .= '<div class="tb-accordion-item">';
        $html .= '<button class="tb-accordion-header' . ($isFirst ? ' active' : '') . '" onclick="this.classList.toggle(\'active\');this.nextElementSibling.classList.toggle(\'open\');">';
        $html .= '<span>' . esc($title) . '</span>';
        $html .= '<span class="tb-accordion-icon">▼</span>';
        $html .= '</button>';
        $html .= '<div class="tb-accordion-content' . ($isFirst ? ' open' : '') . '">';
        $html .= '<div class="tb-accordion-content-inner">' . esc($content) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }

    $html .= '</div>';
    return $output . $html;
}

/**
 * Render tabs module content
 */
function tb_render_module_tabs(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $moduleId = $module['id'] ?? 'tabs-' . uniqid();
    $tabs = $c['tabs'] ?? [];
    $style = $module['design']['style'] ?? 'default';

    if (empty($tabs)) {
        return '';
    }

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $html = '<div class="tb-tabs tb-tabs-' . esc($style) . '" id="' . esc($moduleId) . '">';

    // Tab headers
    $html .= '<div class="tb-tabs-nav">';
    foreach ($tabs as $idx => $tab) {
        $title = $tab['title'] ?? 'Tab ' . ($idx + 1);
        $activeClass = $idx === 0 ? ' active' : '';
        $html .= '<button class="tb-tab-btn' . $activeClass . '" onclick="tbSwitchTab(this,' . $idx . ')">' . esc($title) . '</button>';
    }
    $html .= '</div>';

    // Tab content panels
    $html .= '<div class="tb-tabs-content">';
    foreach ($tabs as $idx => $tab) {
        $content = $tab['content'] ?? '';
        $activeClass = $idx === 0 ? ' active' : '';
        $html .= '<div class="tb-tab-panel' . $activeClass . '" data-tab="' . $idx . '">' . esc($content) . '</div>';
    }
    $html .= '</div>';

    $html .= '</div>';

    // Inline script for tab switching
    $html .= '<script>function tbSwitchTab(btn,idx){';
    $html .= 'var p=btn.parentElement.parentElement;';
    $html .= 'p.querySelectorAll(".tb-tab-btn").forEach(function(b){b.classList.remove("active");});';
    $html .= 'p.querySelectorAll(".tb-tab-panel").forEach(function(t){t.classList.remove("active");});';
    $html .= 'btn.classList.add("active");';
    $html .= 'p.querySelector(".tb-tab-panel[data-tab=\""+idx+"\"]").classList.add("active");';
    $html .= '}</script>';

    return $output . $html;
}

/**
 * Render Gallery module - Advanced version with lightbox, hover effects, animations
 */
function tb_render_module_gallery(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'gallery-' . uniqid();

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);

    // Support both 'images' (TB 3.0) and 'items' (AI TB 4.0)
    $images = $content['images'] ?? [];
    
    // If no images but items exist, convert items to images format
    if (empty($images) && !empty($content['items'])) {
        $images = [];
        foreach ($content['items'] as $item) {
            if (is_array($item)) {
                // AI format: {title, image: {src, alt}}
                if (isset($item['image']) && is_array($item['image'])) {
                    $images[] = [
                        'src' => $item['image']['src'] ?? $item['image']['url'] ?? '',
                        'alt' => $item['image']['alt'] ?? $item['title'] ?? '',
                        'caption' => $item['title'] ?? $item['caption'] ?? '',
                    ];
                } else {
                    // Simple format: {src, alt, caption}
                    $images[] = [
                        'src' => $item['src'] ?? $item['url'] ?? $item['image'] ?? '',
                        'alt' => $item['alt'] ?? $item['title'] ?? '',
                        'caption' => $item['caption'] ?? $item['title'] ?? '',
                    ];
                }
            } elseif (is_string($item)) {
                // Just URL string
                $images[] = ['src' => $item, 'alt' => '', 'caption' => ''];
            }
        }
    }

    if (empty($images)) {
        return '<div class="tb-gallery-empty" style="padding:40px;text-align:center;color:#64748b;background:#f1f5f9;border-radius:8px;">No images in gallery</div>';
    }

    // Content settings
    $columns = (int)($content['columns'] ?? 3);
    $columnsTablet = (int)($content['columns_tablet'] ?? 2);
    $columnsMobile = (int)($content['columns_mobile'] ?? 1);

    // Design settings
    $layout = $design['layout'] ?? 'grid';
    $gap = $design['gap'] ?? '16px';
    $gapTablet = $design['gap_tablet'] ?? '12px';
    $gapMobile = $design['gap_mobile'] ?? '8px';

    $aspectRatio = $design['aspect_ratio'] ?? 'auto';
    $objectFit = $design['object_fit'] ?? 'cover';
    $borderRadius = $design['border_radius'] ?? '8px';
    $imageShadow = $design['image_shadow'] ?? 'none';

    $hoverEffect = $design['hover_effect'] ?? 'zoom';
    $hoverZoomScale = $design['hover_zoom_scale'] ?? '1.05';
    $hoverOverlayColor = $design['hover_overlay_color'] ?? 'rgba(0,0,0,0.4)';
    $hoverIcon = $design['hover_icon'] ?? 'search';
    $hoverIconColor = $design['hover_icon_color'] ?? '#ffffff';

    $showCaptions = $design['show_captions'] ?? 'hover';
    $captionPosition = $design['caption_position'] ?? 'bottom';
    $captionBg = $design['caption_bg'] ?? 'rgba(0,0,0,0.7)';
    $captionColor = $design['caption_color'] ?? '#ffffff';
    $captionFontSize = $design['caption_font_size'] ?? '14px';

    $loadAnimation = $design['load_animation'] ?? 'fade';
    $animationDuration = $design['animation_duration'] ?? '0.4s';
    $animationStagger = $design['animation_stagger'] ?? true;
    $staggerDelay = $design['stagger_delay'] ?? '0.1s';

    $lightboxEnabled = $design['lightbox'] ?? true;
    $lightboxBg = $design['lightbox_bg'] ?? 'rgba(0,0,0,0.95)';
    $lightboxCounter = $design['lightbox_counter'] ?? true;
    $lightboxArrows = $design['lightbox_arrows'] ?? true;
    $lightboxCaptions = $design['lightbox_captions'] ?? true;
    $lightboxKeyboard = $design['lightbox_keyboard'] ?? true;

    // Use module ID for gallery
    $galleryId = $moduleId;

    // Shadow values
    $shadowMap = [
        'none' => 'none',
        'small' => '0 2px 4px rgba(0,0,0,0.1)',
        'medium' => '0 4px 12px rgba(0,0,0,0.15)',
        'large' => '0 8px 24px rgba(0,0,0,0.2)',
    ];
    $shadow = $shadowMap[$imageShadow] ?? 'none';

    // Aspect ratio conversion
    $aspectRatioCSS = $aspectRatio;
    if ($aspectRatio !== 'auto') {
        $aspectRatioCSS = str_replace(':', '/', $aspectRatio);
    }

    // Icon map
    $iconMap = [
        'search' => '&#128269;',
        'expand' => '&#10530;',
        'plus' => '+',
        'eye' => '&#128065;',
        'none' => '',
    ];
    $iconSymbol = $iconMap[$hoverIcon] ?? '&#128269;';

    // Build CSS
    $css = "
    <style>
    #{$galleryId} {
        display: grid;
        grid-template-columns: repeat({$columns}, 1fr);
        gap: {$gap};
    }
    @media (max-width: 1024px) {
        #{$galleryId} { grid-template-columns: repeat({$columnsTablet}, 1fr); gap: {$gapTablet}; }
    }
    @media (max-width: 768px) {
        #{$galleryId} { grid-template-columns: repeat({$columnsMobile}, 1fr); gap: {$gapMobile}; }
    }
    #{$galleryId} .tb-gallery__item {
        position: relative;
        overflow: hidden;
        border-radius: {$borderRadius};
        box-shadow: {$shadow};
        cursor: " . ($lightboxEnabled ? 'pointer' : 'default') . ";
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    #{$galleryId} .tb-gallery__image-wrap {
        position: relative;
        overflow: hidden;
        " . ($aspectRatio !== 'auto' ? "aspect-ratio: {$aspectRatioCSS};" : '') . "
    }
    #{$galleryId} .tb-gallery__image {
        width: 100%;
        height: 100%;
        object-fit: {$objectFit};
        display: block;
        transition: transform 0.4s ease, filter 0.4s ease;
    }
    #{$galleryId} .tb-gallery__overlay {
        position: absolute;
        inset: 0;
        background: {$hoverOverlayColor};
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    #{$galleryId} .tb-gallery__icon {
        font-size: 32px;
        color: {$hoverIconColor};
        transform: scale(0.8);
        transition: transform 0.3s ease;
    }
    #{$galleryId} .tb-gallery__item:hover .tb-gallery__overlay {
        opacity: 1;
    }
    #{$galleryId} .tb-gallery__item:hover .tb-gallery__icon {
        transform: scale(1);
    }
    ";

    // Hover effects
    if ($hoverEffect === 'zoom') {
        $css .= "#{$galleryId} .tb-gallery__item:hover .tb-gallery__image { transform: scale({$hoverZoomScale}); }";
    } elseif ($hoverEffect === 'lift') {
        $css .= "#{$galleryId} .tb-gallery__item:hover { transform: translateY(-8px); box-shadow: 0 12px 24px rgba(0,0,0,0.2); }";
    } elseif ($hoverEffect === 'grayscale') {
        $css .= "#{$galleryId} .tb-gallery__image { filter: grayscale(100%); }";
        $css .= "#{$galleryId} .tb-gallery__item:hover .tb-gallery__image { filter: grayscale(0%); }";
    } elseif ($hoverEffect === 'blur') {
        $css .= "#{$galleryId} .tb-gallery__item:hover .tb-gallery__image { filter: blur(3px); }";
    }

    // Captions
    $captionStyles = "";
    if ($captionPosition === 'bottom') {
        $captionStyles = "position:relative;";
    } elseif ($captionPosition === 'overlay-bottom') {
        $captionStyles = "position:absolute;bottom:0;left:0;right:0;";
    } elseif ($captionPosition === 'overlay-center') {
        $captionStyles = "position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;width:90%;";
    }

    $css .= "
    #{$galleryId} .tb-gallery__caption {
        {$captionStyles}
        padding: 12px;
        background: {$captionBg};
        color: {$captionColor};
        font-size: {$captionFontSize};
        " . ($showCaptions === 'hover' ? 'opacity:0;transition:opacity 0.3s ease;' : '') . "
        " . ($showCaptions === 'never' ? 'display:none;' : '') . "
    }
    ";

    if ($showCaptions === 'hover') {
        $css .= "#{$galleryId} .tb-gallery__item:hover .tb-gallery__caption { opacity: 1; }";
    }

    // Animations
    if ($loadAnimation !== 'none') {
        $animationName = 'tbGallery' . ucfirst(str_replace('-', '', $loadAnimation));
        $css .= "
        @keyframes tbGalleryFade { from { opacity: 0; } to { opacity: 1; } }
        @keyframes tbGallerySlideup { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes tbGalleryZoomin { from { opacity: 0; transform: scale(0.8); } to { opacity: 1; transform: scale(1); } }
        #{$galleryId} .tb-gallery__item {
            animation: {$animationName} {$animationDuration} ease forwards;
            opacity: 0;
        }
        ";
    }

    $css .= "</style>";

    // Build HTML - prepend element CSS for inner element styling
    $html = $elementCss ? '<style>' . $elementCss . '</style>' : '';
    $html .= $css;
    $html .= '<div class="tb-gallery" id="' . $galleryId . '" data-lightbox="' . ($lightboxEnabled ? 'true' : 'false') . '">';

    foreach ($images as $index => $image) {
        $src = esc($image['url'] ?? $image['src'] ?? '');
        $alt = esc($image['alt'] ?? '');
        $caption = esc($image['caption'] ?? '');

        $staggerStyle = '';
        if ($loadAnimation !== 'none' && $animationStagger) {
            $delay = floatval($staggerDelay) * $index;
            $staggerStyle = 'animation-delay:' . $delay . 's;';
        }

        $html .= '<div class="tb-gallery__item" data-index="' . $index . '" data-src="' . $src . '" data-caption="' . $caption . '" style="' . $staggerStyle . '">';
        $html .= '<div class="tb-gallery__image-wrap">';
        $html .= '<img class="tb-gallery__image" src="' . $src . '" alt="' . $alt . '" loading="lazy">';

        if ($hoverIcon !== 'none' && ($hoverEffect === 'overlay' || $hoverEffect === 'zoom' || $hoverEffect === 'lift')) {
            $html .= '<div class="tb-gallery__overlay"><span class="tb-gallery__icon">' . $iconSymbol . '</span></div>';
        }

        $html .= '</div>';

        if ($caption && $showCaptions !== 'never') {
            $html .= '<div class="tb-gallery__caption">' . $caption . '</div>';
        }

        $html .= '</div>';
    }

    $html .= '</div>';

    // Add lightbox
    if ($lightboxEnabled) {
        $html .= tb_render_gallery_lightbox($galleryId, $lightboxBg, $lightboxCounter, $lightboxArrows, $lightboxCaptions, $lightboxKeyboard);
    }

    return $html;
}

/**
 * Render lightbox HTML and JavaScript
 */
function tb_render_gallery_lightbox(string $galleryId, string $bgColor, bool $showCounter, bool $showArrows, bool $showCaptions, bool $keyboard): string
{
    static $lightboxInjected = false;

    $output = '';

    // Only inject lightbox container once
    if (!$lightboxInjected) {
        $lightboxInjected = true;

        $output .= '
        <style>
        .tb-lightbox {
            position: fixed;
            inset: 0;
            z-index: 999999;
            display: none;
            align-items: center;
            justify-content: center;
            background: ' . $bgColor . ';
        }
        .tb-lightbox.active { display: flex; }
        .tb-lightbox__close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            color: #fff;
            font-size: 40px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
            z-index: 10;
        }
        .tb-lightbox__close:hover { opacity: 1; }
        .tb-lightbox__nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.1);
            border: none;
            color: #fff;
            font-size: 48px;
            padding: 20px 15px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s, background 0.2s;
        }
        .tb-lightbox__nav:hover { opacity: 1; background: rgba(255,255,255,0.2); }
        .tb-lightbox__prev { left: 20px; }
        .tb-lightbox__next { right: 20px; }
        .tb-lightbox__content {
            max-width: 90vw;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .tb-lightbox__image {
            max-width: 100%;
            max-height: 75vh;
            object-fit: contain;
        }
        .tb-lightbox__caption {
            color: #fff;
            text-align: center;
            padding: 16px;
            font-size: 16px;
            max-width: 800px;
        }
        .tb-lightbox__counter {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: #fff;
            font-size: 14px;
            opacity: 0.7;
        }
        </style>

        <div class="tb-lightbox" id="tb-lightbox">
            <button class="tb-lightbox__close" onclick="tbLightbox.close()">&times;</button>
            ' . ($showArrows ? '<button class="tb-lightbox__nav tb-lightbox__prev" onclick="tbLightbox.prev()">&#8249;</button>' : '') . '
            ' . ($showArrows ? '<button class="tb-lightbox__nav tb-lightbox__next" onclick="tbLightbox.next()">&#8250;</button>' : '') . '
            <div class="tb-lightbox__content">
                <img class="tb-lightbox__image" src="" alt="">
                ' . ($showCaptions ? '<div class="tb-lightbox__caption"></div>' : '') . '
            </div>
            ' . ($showCounter ? '<div class="tb-lightbox__counter"></div>' : '') . '
        </div>

        <script>
        const tbLightbox = {
            galleries: {},
            current: null,
            index: 0,

            register(galleryId) {
                const gallery = document.getElementById(galleryId);
                if (!gallery || gallery.dataset.lightbox !== "true") return;

                const items = gallery.querySelectorAll(".tb-gallery__item");
                this.galleries[galleryId] = [];

                items.forEach((item, idx) => {
                    this.galleries[galleryId].push({
                        src: item.dataset.src,
                        caption: item.dataset.caption
                    });
                    item.addEventListener("click", () => this.open(galleryId, idx));
                });
            },

            open(galleryId, index) {
                this.current = galleryId;
                this.index = index;
                document.getElementById("tb-lightbox").classList.add("active");
                document.body.style.overflow = "hidden";
                this.update();
            },

            close() {
                document.getElementById("tb-lightbox").classList.remove("active");
                document.body.style.overflow = "";
                this.current = null;
            },

            prev() {
                if (!this.current) return;
                const items = this.galleries[this.current];
                this.index = (this.index - 1 + items.length) % items.length;
                this.update();
            },

            next() {
                if (!this.current) return;
                const items = this.galleries[this.current];
                this.index = (this.index + 1) % items.length;
                this.update();
            },

            update() {
                if (!this.current) return;
                const items = this.galleries[this.current];
                const item = items[this.index];

                const img = document.querySelector("#tb-lightbox .tb-lightbox__image");
                const caption = document.querySelector("#tb-lightbox .tb-lightbox__caption");
                const counter = document.querySelector("#tb-lightbox .tb-lightbox__counter");

                if (img) img.src = item.src;
                if (caption) caption.textContent = item.caption || "";
                if (counter) counter.textContent = (this.index + 1) + " / " + items.length;
            }
        };

        ' . ($keyboard ? '
        document.addEventListener("keydown", (e) => {
            if (!document.getElementById("tb-lightbox").classList.contains("active")) return;
            if (e.key === "Escape") tbLightbox.close();
            if (e.key === "ArrowLeft") tbLightbox.prev();
            if (e.key === "ArrowRight") tbLightbox.next();
        });
        ' : '') . '

        document.getElementById("tb-lightbox").addEventListener("click", (e) => {
            if (e.target.id === "tb-lightbox") tbLightbox.close();
        });
        </script>
        ';
    }

    // Register this gallery
    $output .= '<script>document.addEventListener("DOMContentLoaded", () => tbLightbox.register("' . $galleryId . '"));</script>';

    return $output;
}

/**
 * Render testimonial module content
 * Applies design: text_color, font_size, line_height, background_color, padding, border_radius
 */
function tb_render_module_testimonial(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $d = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'testimonial-' . uniqid();

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    // Use 'quote' first (new format from Content panel), fallback to 'text' (legacy)
    $text = !empty($c['quote']) ? $c['quote'] : ($c['text'] ?? '');
    $author = $c['author'] ?? '';
    $role = $c['role'] ?? '';
    $avatar = $c['avatar'] ?? '';
    $style = $d['style'] ?? 'card';

    // Build container styles
    $containerStyles = [];
    if (!empty($d['background_color'])) {
        $containerStyles[] = 'background-color:' . tb_map_color($d['background_color']);
    }
    if (!empty($d['padding'])) {
        $containerStyles[] = 'padding:' . esc($d['padding']);
    } else {
        $containerStyles[] = 'padding:30px';
    }
    if (!empty($d['border_radius'])) {
        $containerStyles[] = 'border-radius:' . esc($d['border_radius']);
    }
    if (!empty($d['box_shadow'])) {
        $containerStyles[] = 'box-shadow:' . esc($d['box_shadow']);
    }

    // Text styles
    $textColor = $d['text_color'] ?? '#e0e0e0';
    $fontSize = $d['font_size'] ?? '18px';
    $lineHeight = $d['line_height'] ?? '1.7';

    $containerStyleStr = !empty($containerStyles) ? ' style="' . implode(';', $containerStyles) . '"' : '';

    $html = '<div class="tb-testimonial tb-testimonial-' . esc($style) . '" id="' . esc($moduleId) . '"' . $containerStyleStr . '>';

    // Quote icon
    $html .= '<div style="font-size:48px;color:' . esc($d['quote_color'] ?? 'rgba(255,255,255,0.2)') . ';line-height:1;margin-bottom:15px">"</div>';

    if ($avatar) {
        $html .= '<div class="tb-testimonial-avatar" style="margin-bottom:15px">';
        $html .= '<img src="' . esc($avatar) . '" alt="' . esc($author) . '" loading="lazy" style="width:60px;height:60px;border-radius:50%;object-fit:cover">';
        $html .= '</div>';
    }

    $html .= '<div class="tb-testimonial-content">';
    $html .= '<blockquote class="tb-testimonial-text" style="color:' . tb_map_color($textColor) . ';font-size:' . esc($fontSize) . ';line-height:' . esc($lineHeight) . ';font-style:italic;margin:0 0 20px">' . esc($text) . '</blockquote>';

    if ($author || $role) {
        $html .= '<div class="tb-testimonial-author">';
        if ($author) {
            $authorColor = $d['author_color'] ?? '#ffffff';
            $html .= '<span class="tb-testimonial-name" style="color:' . tb_map_color($authorColor) . ';font-weight:600;display:block">' . esc($author) . '</span>';
        }
        if ($role) {
            $roleColor = $d['role_color'] ?? '#94a3b8';
            $html .= '<span class="tb-testimonial-role" style="color:' . esc($roleColor) . ';font-size:14px">' . esc($role) . '</span>';
        }
        $html .= '</div>';
    }

    $html .= '</div>';
    $html .= '</div>';

    return $output . $html;
}

/**
 * Render CTA module content
 */
function tb_render_module_cta(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $moduleId = $module['id'] ?? 'cta-' . uniqid();
    $title = $c['title'] ?? '';
    $subtitle = $c['subtitle'] ?? '';
    $buttonText = $c['button_text'] ?? 'Get Started';
    $buttonUrl = $c['button_url'] ?? '#';
    $style = $module['design']['style'] ?? 'centered';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $html = '<div class="tb-cta tb-cta-' . esc($style) . '" id="' . esc($moduleId) . '">';

    if ($title) {
        $html .= '<h2 class="tb-cta-title">' . esc($title) . '</h2>';
    }
    if ($subtitle) {
        $html .= '<p class="tb-cta-subtitle">' . esc($subtitle) . '</p>';
    }
    if ($buttonText) {
        $html .= '<a href="' . esc($buttonUrl) . '" class="tb-cta-button">' . esc($buttonText) . '</a>';
    }

    $html .= '</div>';
    return $output . $html;
}

/**
 * Render pricing module content
 */
function tb_render_module_pricing(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $moduleId = $module['id'] ?? 'pricing-' . uniqid();
    $title = $c['title'] ?? '';
    $price = $c['price'] ?? '';
    $period = $c['period'] ?? '/month';
    $features = $c['features'] ?? [];
    $buttonText = $c['button_text'] ?? 'Choose';
    $buttonUrl = $c['button_url'] ?? '#';
    $highlighted = $module['design']['highlighted'] ?? false;

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $highlightClass = $highlighted ? ' tb-pricing-highlighted' : '';
    $html = '<div class="tb-pricing' . $highlightClass . '" id="' . esc($moduleId) . '">';

    if ($highlighted) {
        $html .= '<div class="tb-pricing-badge">Popular</div>';
    }

    if ($title) {
        $html .= '<h3 class="tb-pricing-title">' . esc($title) . '</h3>';
    }

    if ($price) {
        $html .= '<div class="tb-pricing-price">';
        $html .= '<span class="tb-pricing-amount">' . esc($price) . '</span>';
        $html .= '<span class="tb-pricing-period">' . esc($period) . '</span>';
        $html .= '</div>';
    }

    if (!empty($features)) {
        $html .= '<ul class="tb-pricing-features">';
        foreach ($features as $feature) {
            $featureText = is_array($feature) ? ($feature['text'] ?? '') : $feature;
            $html .= '<li>' . esc($featureText) . '</li>';
        }
        $html .= '</ul>';
    }

    if ($buttonText) {
        $html .= '<a href="' . esc($buttonUrl) . '" class="tb-pricing-button">' . esc($buttonText) . '</a>';
    }

    $html .= '</div>';
    return $output . $html;
}

/**
 * Render form module content
 */
function tb_render_module_form(array $module, array $options = []): string
{
    $rawContent = $module['content'] ?? [];
    $content = is_array($rawContent) && !isset($rawContent[0]) ? $rawContent : [];
    $moduleId = $module['id'] ?? 'form-' . uniqid();
    $title = $content['title'] ?? 'Get In Touch';
    $submitText = $content['submit_text'] ?? 'Send Message';
    $successMessage = $content['success_message'] ?? 'Thank you! Your message has been sent.';
    $recipientEmail = $content['recipient_email'] ?? '';
    $buttonStyle = $content['button_style'] ?? 'primary';
    $buttonFullWidth = $content['button_full_width'] ?? false;
    $style = $content['style'] ?? 'stacked';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $fields = $content['fields'] ?? [
        ['type' => 'text', 'label' => 'Name', 'placeholder' => 'Your name', 'required' => true],
        ['type' => 'email', 'label' => 'Email', 'placeholder' => 'your@email.com', 'required' => true],
        ['type' => 'textarea', 'label' => 'Message', 'placeholder' => 'Your message...', 'required' => true]
    ];

    $btnClasses = 'tb-form-submit';
    if ($buttonFullWidth) {
        $btnClasses .= ' tb-form-submit-full';
    }

    $html = '<div class="tb-form-container" id="' . esc($moduleId) . '">';

    if ($title) {
        $html .= '<h3 class="tb-form-title">' . esc($title) . '</h3>';
    }

    $html .= '<form class="tb-form tb-form-' . esc($style) . '" method="post" data-success="' . esc($successMessage) . '">';

    if ($recipientEmail) {
        $html .= '<input type="hidden" name="_recipient" value="' . esc($recipientEmail) . '">';
    }

    $html .= '<div class="tb-form-fields">';

    foreach ($fields as $index => $field) {
        $fieldType = $field['type'] ?? 'text';
        $fieldLabel = $field['label'] ?? '';
        $fieldPlaceholder = $field['placeholder'] ?? '';
        $fieldRequired = $field['required'] ?? false;
        $fieldName = 'field_' . $index;

        $html .= '<div class="tb-form-field tb-form-field-' . esc($fieldType) . '">';

        if ($fieldLabel) {
            $html .= '<label class="tb-form-label" for="' . esc($fieldName) . '">';
            $html .= esc($fieldLabel);
            if ($fieldRequired) {
                $html .= '<span class="tb-form-required">*</span>';
            }
            $html .= '</label>';
        }

        if ($fieldType === 'textarea') {
            $html .= '<textarea name="' . esc($fieldName) . '" id="' . esc($fieldName) . '" class="tb-form-input tb-form-textarea" placeholder="' . esc($fieldPlaceholder) . '"';
            if ($fieldRequired) {
                $html .= ' required';
            }
            $html .= ' rows="4"></textarea>';
        } elseif ($fieldType === 'select') {
            $opts = $field['options'] ?? [];
            $html .= '<select name="' . esc($fieldName) . '" id="' . esc($fieldName) . '" class="tb-form-input tb-form-select"';
            if ($fieldRequired) {
                $html .= ' required';
            }
            $html .= '>';
            $html .= '<option value="">' . esc($fieldPlaceholder ?: 'Select...') . '</option>';
            foreach ($opts as $opt) {
                $optValue = is_array($opt) ? ($opt['value'] ?? '') : $opt;
                $optLabel = is_array($opt) ? ($opt['label'] ?? $optValue) : $opt;
                $html .= '<option value="' . esc($optValue) . '">' . esc($optLabel) . '</option>';
            }
            $html .= '</select>';
        } else {
            $html .= '<input type="' . esc($fieldType) . '" name="' . esc($fieldName) . '" id="' . esc($fieldName) . '" class="tb-form-input" placeholder="' . esc($fieldPlaceholder) . '"';
            if ($fieldRequired) {
                $html .= ' required';
            }
            $html .= '>';
        }

        $html .= '</div>';
    }

    $html .= '</div>';
    $html .= '<button type="submit" class="' . esc($btnClasses) . ' tb-form-btn-' . esc($buttonStyle) . '">' . esc($submitText) . '</button>';
    $html .= '</form>';
    $html .= '</div>';

    return $output . $html;
}

/**
 * Render fullwidth code module
 */
function tb_render_module_fullwidth_code(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'fullwidth-code-' . uniqid();
    $code = $content['code'] ?? '';
    $language = strtolower($content['language'] ?? 'javascript');

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $html = '<div class="tb-fullwidth-code" id="' . esc($moduleId) . '">';
    
    // Execute code based on language type
    switch ($language) {
        case 'javascript':
        case 'js':
            $html .= '<script>' . $code . '</script>';
            break;
        case 'css':
            $html .= '<style>' . $code . '</style>';
            break;
        case 'html':
            $html .= $code;
            break;
        default:
            // Display as code block for other languages
            $html .= '<pre><code class="language-' . esc($language) . '">' . esc($code) . '</code></pre>';
            break;
    }
    
    $html .= '</div>';

    return $output . $html;
}

/**
 * Render fullwidth image module
 */
function tb_render_module_fullwidth_image(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'fullwidth-image-' . uniqid();
    $src = $content['src'] ?? '';
    $alt = $content['alt'] ?? '';
    $link = $content['link'] ?? '';
    $height = $design['height'] ?? '400px';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $html = '<div class="tb-fullwidth-image" id="' . esc($moduleId) . '" style="height:' . esc($height) . '">';
    if ($src) {
        $img = '<img src="' . esc($src) . '" alt="' . esc($alt) . '" style="width:100%;height:100%;object-fit:cover">';
        if ($link) {
            $html .= '<a href="' . esc($link) . '">' . $img . '</a>';
        } else {
            $html .= $img;
        }
    }
    $html .= '</div>';

    return $output . $html;
}

/**
 * Render fullwidth map module
 */
function tb_render_module_fullwidth_map(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'fullwidth-map-' . uniqid();
    $address = $content['address'] ?? '';
    $zoom = $content['zoom'] ?? 14;
    $height = $design['height'] ?? '400px';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $html = '<div class="tb-fullwidth-map" id="' . esc($moduleId) . '" style="height:' . esc($height) . '">';
    if ($address) {
        $encodedAddress = urlencode($address);
        $html .= '<iframe src="https://maps.google.com/maps?q=' . $encodedAddress . '&z=' . (int)$zoom . '&output=embed" width="100%" height="100%" style="border:0" allowfullscreen loading="lazy"></iframe>';
    }
    $html .= '</div>';

    return $output . $html;
}

/**
 * Render fullwidth menu module
 */
function tb_render_module_fullwidth_menu(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'fullwidth-menu-' . uniqid();
    $logo = $content['logo'] ?? '';
    $menuItems = $content['items'] ?? $content['menu_items'] ?? [];
    $bgColor = $design['background_color'] ?? '#1e1e2e';
    $textColor = $design['text_color'] ?? '#ffffff';
    $hoverColor = $design['hover_color'] ?? 'var(--primary)';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $html = '<nav class="tb-fullwidth-menu" id="' . esc($moduleId) . '" style="background:' . tb_map_color($bgColor) . ';color:' . tb_map_color($textColor) . ';padding:16px 24px;display:flex;justify-content:space-between;align-items:center">';
    if ($logo) {
        $html .= '<img src="' . esc($logo) . '" alt="Logo" style="height:40px">';
    }
    $html .= '<ul style="display:flex;gap:24px;list-style:none;margin:0;padding:0">';
    foreach ($menuItems as $item) {
        $label = $item['label'] ?? $item['text'] ?? '';
        $url = $item['url'] ?? '#';
        $html .= '<li><a href="' . esc($url) . '" style="color:' . tb_map_color($textColor) . ';text-decoration:none">' . esc($label) . '</a></li>';
    }
    $html .= '</ul>';
    $html .= '</nav>';

    return $output . $html;
}

/**
 * Render fullwidth slider module
 */
function tb_render_module_fullwidth_slider(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'fullwidth-slider-' . uniqid();
    $slides = $content['slides'] ?? [];
    $minHeight = $design['min_height'] ?? '600px';
    $showArrows = $design['show_arrows'] ?? true;
    $showDots = $design['show_dots'] ?? true;
    $autoplay = $design['autoplay'] ?? false;
    $autoplaySpeed = $design['autoplay_speed'] ?? 5000;

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    if (empty($slides)) {
        return $output . '<div class="tb-fullwidth-slider" id="' . esc($moduleId) . '" style="min-height:' . esc($minHeight) . ';display:flex;align-items:center;justify-content:center;background:#f5f5f5;color:#999">No slides added</div>';
    }

    $sliderId = $moduleId;
    $slideCount = count($slides);

    $html = '<div class="tb-fullwidth-slider" id="' . $sliderId . '" style="position:relative;min-height:' . esc($minHeight) . ';overflow:hidden">';

    // Slides
    foreach ($slides as $i => $slide) {
        $bgImage = !empty($slide['image']) ? 'background-image:url(' . esc($slide['image']) . ');background-size:cover;background-position:center;' : 'background:linear-gradient(135deg,#667eea,#764ba2);';
        $display = $i === 0 ? 'flex' : 'none';
        $html .= '<div class="tb-slide" data-index="' . $i . '" style="position:absolute;inset:0;display:' . $display . ';align-items:center;justify-content:center;' . $bgImage . ';transition:opacity 0.5s">';
        $html .= '<div style="position:absolute;inset:0;background:rgba(0,0,0,0.4)"></div>';
        $html .= '<div style="position:relative;z-index:1;text-align:center;color:#fff;padding:20px;max-width:800px">';
        $html .= '<h2 style="font-size:clamp(24px,5vw,48px);margin:0 0 16px;font-weight:700">' . esc($slide['title'] ?? '') . '</h2>';
        $html .= '<p style="font-size:clamp(14px,2vw,20px);margin:0 0 24px;opacity:0.9">' . esc($slide['text'] ?? '') . '</p>';
        if (!empty($slide['button_text'])) {
            $html .= '<a href="' . esc($slide['button_url'] ?? '#') . '" style="display:inline-block;padding:14px 32px;background:var(--primary);color:#fff;text-decoration:none;border-radius:6px;font-weight:600;transition:opacity 0.3s" onmouseover="this.style.opacity=\'0.85\'" onmouseout="this.style.opacity=\'1\'">' . esc($slide['button_text']) . '</a>';
        }
        $html .= '</div></div>';
    }

    // Navigation Arrows
    if ($showArrows && $slideCount > 1) {
        $arrowStyle = 'position:absolute;top:50%;transform:translateY(-50%);z-index:10;width:50px;height:50px;border:none;background:rgba(255,255,255,0.9);color:#333;font-size:24px;cursor:pointer;border-radius:50%;display:flex;align-items:center;justify-content:center;transition:background 0.3s,transform 0.3s;box-shadow:0 2px 10px rgba(0,0,0,0.2)';
        $html .= '<button class="tb-slider-prev" onclick="tbSliderPrev(\'' . $sliderId . '\')" style="' . $arrowStyle . ';left:20px" onmouseover="this.style.background=\'#fff\';this.style.transform=\'translateY(-50%) scale(1.1)\'" onmouseout="this.style.background=\'rgba(255,255,255,0.9)\';this.style.transform=\'translateY(-50%) scale(1)\'">&#10094;</button>';
        $html .= '<button class="tb-slider-next" onclick="tbSliderNext(\'' . $sliderId . '\')" style="' . $arrowStyle . ';right:20px" onmouseover="this.style.background=\'#fff\';this.style.transform=\'translateY(-50%) scale(1.1)\'" onmouseout="this.style.background=\'rgba(255,255,255,0.9)\';this.style.transform=\'translateY(-50%) scale(1)\'">&#10095;</button>';
    }

    // Navigation Dots
    if ($showDots && $slideCount > 1) {
        $html .= '<div class="tb-slider-dots" style="position:absolute;bottom:20px;left:50%;transform:translateX(-50%);z-index:10;display:flex;gap:10px">';
        for ($i = 0; $i < $slideCount; $i++) {
            $activeDot = $i === 0 ? 'background:#fff' : 'background:rgba(255,255,255,0.5)';
            $html .= '<button class="tb-slider-dot" data-index="' . $i . '" onclick="tbSliderGoTo(\'' . $sliderId . '\',' . $i . ')" style="width:12px;height:12px;border-radius:50%;border:none;' . $activeDot . ';cursor:pointer;transition:background 0.3s"></button>';
        }
        $html .= '</div>';
    }

    $html .= '</div>';

    // Slider JavaScript (inline for self-contained module)
    $html .= '<script>
    (function() {
        var slider = document.getElementById("' . $sliderId . '");
        if (!slider) return;
        var slides = slider.querySelectorAll(".tb-slide");
        var dots = slider.querySelectorAll(".tb-slider-dot");
        var current = 0;
        var total = slides.length;
        
        window.tbSliderGoTo = function(id, index) {
            if (id !== "' . $sliderId . '") return;
            slides[current].style.display = "none";
            if (dots[current]) dots[current].style.background = "rgba(255,255,255,0.5)";
            current = (index + total) % total;
            slides[current].style.display = "flex";
            if (dots[current]) dots[current].style.background = "#fff";
        };
        
        window.tbSliderNext = function(id) {
            if (id !== "' . $sliderId . '") return;
            tbSliderGoTo(id, current + 1);
        };
        
        window.tbSliderPrev = function(id) {
            if (id !== "' . $sliderId . '") return;
            tbSliderGoTo(id, current - 1);
        };
        ' . ($autoplay ? '
        setInterval(function() { tbSliderNext("' . $sliderId . '"); }, ' . (int)$autoplaySpeed . ');' : '') . '
    })();
    </script>';

    return $output . $html;
}

/**
 * Render fullwidth header module
 */
function tb_render_module_fullwidth_header(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'fullwidth-header-' . uniqid();
    $title = $content['title'] ?? 'Welcome';
    $subtitle = $content['subtitle'] ?? '';
    $buttonText = $content['button_text'] ?? '';
    $buttonUrl = $content['button_url'] ?? '#';
    $bgImage = $content['background_image'] ?? '';
    $minHeight = $design['min_height'] ?? '500px';
    $textAlign = $design['text_align'] ?? 'center';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $bgStyle = $bgImage ? 'background-image:url(' . esc($bgImage) . ');background-size:cover;background-position:center;' : 'background:linear-gradient(135deg,#1e3a5f,#2d5016);';

    $html = '<header class="tb-fullwidth-header" id="' . esc($moduleId) . '" style="position:relative;min-height:' . esc($minHeight) . ';display:flex;align-items:center;justify-content:center;' . $bgStyle . '">';
    $html .= '<div style="position:absolute;inset:0;background:rgba(0,0,0,0.5)"></div>';
    $html .= '<div style="position:relative;z-index:1;text-align:' . esc($textAlign) . ';color:#fff;padding:40px;max-width:800px">';
    $html .= '<h1 style="font-size:48px;margin:0 0 16px">' . esc($title) . '</h1>';
    if ($subtitle) {
        $html .= '<p style="font-size:20px;margin:0 0 24px;opacity:0.9">' . esc($subtitle) . '</p>';
    }
    if ($buttonText) {
        $html .= '<a href="' . esc($buttonUrl) . '" style="display:inline-block;padding:14px 32px;background:var(--primary);color:#fff;text-decoration:none;border-radius:6px;font-weight:600">' . esc($buttonText) . '</a>';
    }
    $html .= '</div>';
    $html .= '</header>';

    return $output . $html;
}

/**
 * Render fullwidth portfolio module
 */
function tb_render_module_fullwidth_portfolio(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'fullwidth-portfolio-' . uniqid();
    $items = $content['items'] ?? [];
    $showFilter = $content['show_filter'] ?? true;
    $columns = $design['columns'] ?? 3;
    $gap = $design['gap'] ?? '0';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $categories = [];
    foreach ($items as $item) {
        $cat = $item['category'] ?? 'Uncategorized';
        if (!in_array($cat, $categories)) {
            $categories[] = $cat;
        }
    }

    $html = '<div class="tb-fullwidth-portfolio" id="' . esc($moduleId) . '">';

    if ($showFilter && count($categories) > 0) {
        $html .= '<div class="tb-portfolio-filter" style="text-align:center;padding:20px">';
        $html .= '<button class="tb-filter-btn active" data-filter="all" style="margin:0 8px;padding:8px 16px;border:none;background:var(--primary);color:#fff;border-radius:4px;cursor:pointer">All</button>';
        foreach ($categories as $cat) {
            $html .= '<button class="tb-filter-btn" data-filter="' . esc($cat) . '" style="margin:0 8px;padding:8px 16px;border:1px solid #e2e8f0;background:#fff;border-radius:4px;cursor:pointer">' . esc($cat) . '</button>';
        }
        $html .= '</div>';
    }

    $html .= '<div class="tb-portfolio-grid" style="display:grid;grid-template-columns:repeat(' . (int)$columns . ',1fr);gap:' . esc($gap) . '">';
    foreach ($items as $item) {
        $html .= '<div class="tb-portfolio-item" data-category="' . esc($item['category'] ?? '') . '" style="position:relative;aspect-ratio:1;overflow:hidden;background:#e2e8f0">';
        if (!empty($item['image'])) {
            $html .= '<img src="' . esc($item['image']) . '" alt="' . esc($item['title'] ?? '') . '" style="width:100%;height:100%;object-fit:cover">';
        }
        $html .= '<div class="tb-portfolio-overlay" style="position:absolute;inset:0;background:rgba(0,0,0,0.7);display:flex;align-items:center;justify-content:center;flex-direction:column;color:#fff">';
        $html .= '<h4 style="margin:0 0 4px;font-weight:600;font-size:14px">' . esc($item['title'] ?? '') . '</h4>';
        $html .= '<span style="font-size:11px;opacity:0.8">' . esc($item['category'] ?? '') . '</span>';
        $html .= '</div>';
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';

    // Add hover effect CSS
    $html .= '<style>.tb-portfolio-item:hover .tb-portfolio-overlay{opacity:1}</style>';

    return $output . $html;
}

/**
 * Render fullwidth post slider module
 */
function tb_render_module_fullwidth_post_slider(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'fullwidth-post-slider-' . uniqid();
    $postsCount = $content['posts_count'] ?? 5;
    $showExcerpt = $content['show_excerpt'] ?? true;
    $showDate = $content['show_date'] ?? true;
    $showAuthor = $content['show_author'] ?? true;
    $showReadMore = $content['show_read_more'] ?? true;
    $readMoreText = $content['read_more_text'] ?? 'Read More';
    $showArrows = $design['show_arrows'] ?? true;
    $showDots = $design['show_dots'] ?? true;
    $minHeight = $design['min_height'] ?? '500px';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    // Get recent posts from database
    $posts = [];
    try {
        $db = \core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM articles WHERE status = 'published' ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([(int)$postsCount]);
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {
        return $output;
    }

    if (empty($posts)) {
        return $output;
    }

    $sliderId = $moduleId;
    $totalSlides = count($posts);

    $html = '<div class="tb-fullwidth-post-slider" id="' . $sliderId . '" data-total="' . $totalSlides . '" style="position:relative;min-height:' . esc($minHeight) . ';overflow:hidden">';

    foreach ($posts as $i => $post) {
        $bgImage = !empty($post['featured_image']) ? 'background-image:url(' . esc($post['featured_image']) . ');background-size:cover;background-position:center;' : 'background:linear-gradient(135deg,#334155,#222);';
        $display = $i === 0 ? 'flex' : 'none';
        $html .= '<div class="tb-post-slide" data-index="' . $i . '" style="position:absolute;inset:0;display:' . $display . ';align-items:center;justify-content:center;' . $bgImage . '">';
        $html .= '<div style="position:absolute;inset:0;background:rgba(0,0,0,0.5)"></div>';
        $html .= '<div style="position:relative;z-index:1;text-align:center;color:#fff;padding:40px;max-width:800px">';
        if ($showDate) {
            $html .= '<div style="font-size:12px;opacity:0.8;margin-bottom:8px">' . date('M d, Y', strtotime($post['created_at'])) . '</div>';
        }
        $html .= '<h2 style="font-size:36px;margin:0 0 12px">' . esc($post['title']) . '</h2>';
        if ($showExcerpt && !empty($post['excerpt'])) {
            $html .= '<p style="font-size:16px;margin:0 0 16px;opacity:0.9">' . esc(substr($post['excerpt'], 0, 150)) . '...</p>';
        }
        if ($showAuthor) {
            $html .= '<div style="font-size:12px;opacity:0.7;margin-bottom:20px">By ' . esc($post['author'] ?? 'Admin') . '</div>';
        }
        // Read More button - links to /blog/{slug}
        $postSlug = $post['slug'] ?? '';
        if ($showReadMore && $postSlug) {
            $html .= '<a href="/blog/' . esc($postSlug) . '" style="display:inline-block;padding:12px 28px;background:var(--primary);color:#fff;text-decoration:none;border-radius:6px;font-weight:' . esc($fontWeight) . ';font-size:14px">' . esc($readMoreText) . '</a>';
        }
        $html .= '</div></div>';
    }


    // Navigation arrows
    if ($showArrows && $totalSlides > 1) {
        $html .= '<button class="tb-slider-prev" onclick="tbSliderPrev(\'' . $sliderId . '\')" style="position:absolute;left:20px;top:50%;transform:translateY(-50%);z-index:10;background:rgba(0,0,0,0.5);color:#fff;border:none;width:50px;height:50px;border-radius:50%;cursor:pointer;font-size:24px;display:flex;align-items:center;justify-content:center">&#10094;</button>';
        $html .= '<button class="tb-slider-next" onclick="tbSliderNext(\'' . $sliderId . '\')" style="position:absolute;right:20px;top:50%;transform:translateY(-50%);z-index:10;background:rgba(0,0,0,0.5);color:#fff;border:none;width:50px;height:50px;border-radius:50%;cursor:pointer;font-size:24px;display:flex;align-items:center;justify-content:center">&#10095;</button>';
    }

    // Dots navigation
    if ($showDots && $totalSlides > 1) {
        $html .= '<div class="tb-slider-dots" style="position:absolute;bottom:20px;left:50%;transform:translateX(-50%);z-index:10;display:flex;gap:10px">';
        for ($i = 0; $i < $totalSlides; $i++) {
            $active = $i === 0 ? 'background:#fff' : 'background:rgba(255,255,255,0.5)';
            $html .= '<button class="tb-slider-dot" data-index="' . $i . '" onclick="tbSliderGoTo(\'' . $sliderId . '\',' . $i . ')" style="width:12px;height:12px;border-radius:50%;border:none;cursor:pointer;' . $active . '"></button>';
        }
        $html .= '</div>';
    }

    $html .= '</div>';

    // Add slider JavaScript (only once per page)
    $html .= '<script>
    if (!window.tbSliderInit) {
        window.tbSliderInit = true;
        window.tbSliderCurrent = {};
        window.tbSliderNext = function(id) {
            var slider = document.getElementById(id);
            var total = parseInt(slider.dataset.total);
            var current = window.tbSliderCurrent[id] || 0;
            var next = (current + 1) % total;
            tbSliderGoTo(id, next);
        };
        window.tbSliderPrev = function(id) {
            var slider = document.getElementById(id);
            var total = parseInt(slider.dataset.total);
            var current = window.tbSliderCurrent[id] || 0;
            var prev = (current - 1 + total) % total;
            tbSliderGoTo(id, prev);
        };
        window.tbSliderGoTo = function(id, index) {
            var slider = document.getElementById(id);
            var slides = slider.querySelectorAll(".tb-post-slide, .tb-slide");
            var dots = slider.querySelectorAll(".tb-slider-dot");
            slides.forEach(function(s, i) { s.style.display = i === index ? "flex" : "none"; });
            dots.forEach(function(d, i) { d.style.background = i === index ? "#fff" : "rgba(255,255,255,0.5)"; });
            window.tbSliderCurrent[id] = index;
        };
    }
    </script>';

    return $output . $html;
}


/**
 * Fallback renderer for unknown module types
 */

// ============================================
// RENDERER: blog
// ============================================
function tb_render_module_blog(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'blog-' . uniqid();
    $postsCount = $content['posts_count'] ?? 6;
    $columns = $design['columns'] ?? 3;
    $showExcerpt = $content['show_excerpt'] ?? true;
    $showDate = $content['show_date'] ?? true;
    $showAuthor = $content['show_author'] ?? true;
    $showImage = $content['show_image'] ?? true;
    $categoryId = $content['category_id'] ?? null;

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $posts = [];
    try {
        $db = \core\Database::connection();
        $sql = "SELECT a.*, u.username as author_name FROM articles a LEFT JOIN users u ON a.author_id = u.id WHERE a.status = 'published'";
        if ($categoryId) $sql .= " AND a.category_id = " . intval($categoryId);
        $sql .= " ORDER BY a.published_at DESC, a.created_at DESC LIMIT " . intval($postsCount);
        $posts = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {
        // Table doesn't exist or other DB error - return placeholder
        return $output;
    }

    if (empty($posts)) {
        return $output;
    }

    $columnWidth = 'calc(' . (100 / $columns) . '% - 20px)';
    $html = '<div class="tb-blog" id="' . esc($moduleId) . '" style="display:flex;flex-wrap:wrap;gap:20px">';

    foreach ($posts as $post) {
        $html .= '<article style="flex:0 0 ' . $columnWidth . ';background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1)">';
        if ($showImage && !empty($post['featured_image'])) {
            $html .= '<img src="' . esc($post['featured_image']) . '" alt="' . esc($post['title']) . '" style="width:100%;height:200px;object-fit:cover">';
        }
        $html .= '<div style="padding:20px">';
        $html .= '<h3 style="margin:0 0 10px"><a href="/blog/' . esc($post['slug']) . '" style="color:#333;text-decoration:none">' . esc($post['title']) . '</a></h3>';
        if ($showDate || $showAuthor) {
            $html .= '<p style="color:#666;font-size:0.85em;margin:0 0 10px">';
            if ($showDate) $html .= date('d.m.Y', strtotime($post['created_at']));
            if ($showDate && $showAuthor) $html .= ' | ';
            if ($showAuthor) $html .= esc($post['author_name'] ?? 'Admin');
            $html .= '</p>';
        }
        if ($showExcerpt && !empty($post['excerpt'])) {
            $html .= '<p style="color:#555;line-height:1.5">' . esc($post['excerpt']) . '</p>';
        }
        $html .= '</div></article>';
    }

    $html .= '</div>';
    return $output . $html;
}

// ============================================
// RENDERER: blurb
// Applies design: background_color, box_shadow, padding, border_radius, icon_color, text_color, etc.
// ============================================
function tb_render_module_blurb(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'blurb-' . uniqid();
    $title = $content['title'] ?? $content['label'] ?? '';
    $text = $content['text'] ?? '';
    $icon = $content['icon'] ?? '';
    $image = $content['image'] ?? '';
    $useImage = $content['use_image'] ?? false;
    $url = $content['url'] ?? '';
    // Icon settings can be in content or design
    $iconColor = $content['icon_color'] ?? $design['icon_color'] ?? 'var(--primary)';
    $iconSize = $content['icon_size'] ?? $design['icon_size'] ?? '64px';
    $titleColor = $design['title_color'] ?? $design['text_color'] ?? 'inherit';
    $textColor = $design['text_color'] ?? '#666666';
    $alignment = $content['alignment'] ?? $design['text_align'] ?? $design['alignment'] ?? 'center';
    $layout = $content['layout'] ?? 'top'; // top, left, right

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    // Build container styles
    $containerStyles = [];
    $flexDirection = $layout === 'left' ? 'row' : ($layout === 'right' ? 'row-reverse' : 'column');
    $containerStyles[] = 'display:flex';
    $containerStyles[] = 'flex-direction:' . $flexDirection;
    $containerStyles[] = 'align-items:center';
    $containerStyles[] = 'text-align:' . esc($alignment);
    $containerStyles[] = 'gap:20px';

    // Padding from design or default
    $padding = $design['padding'] ?? '20px';
    $containerStyles[] = 'padding:' . esc($padding);

    // Background color
    if (!empty($design['background_color'])) {
        $containerStyles[] = 'background-color:' . tb_map_color($design['background_color']);
    }

    // Border radius
    if (!empty($design['border_radius'])) {
        $containerStyles[] = 'border-radius:' . esc($design['border_radius']);
    }

    // Box shadow - support both string and individual properties
    if (!empty($design['box_shadow'])) {
        $containerStyles[] = 'box-shadow:' . esc($design['box_shadow']);
    } elseif (!empty($design['box_shadow_enabled']) || !empty($design['box_shadow_blur'])) {
        $sh = $design['box_shadow_horizontal'] ?? '0';
        $sv = $design['box_shadow_vertical'] ?? '4';
        $sb = $design['box_shadow_blur'] ?? '15';
        $ss = $design['box_shadow_spread'] ?? '0';
        $sc = $design['box_shadow_color'] ?? 'rgba(0,0,0,0.1)';
        $containerStyles[] = 'box-shadow:' . esc($sh) . 'px ' . esc($sv) . 'px ' . esc($sb) . 'px ' . esc($ss) . 'px ' . esc($sc);
    }

    // Border
    if (!empty($design['border_width']) && !empty($design['border_color'])) {
        $borderStyle = $design['border_style'] ?? 'solid';
        $containerStyles[] = 'border:' . esc($design['border_width']) . ' ' . esc($borderStyle) . ' ' . esc($design['border_color']);
    }

    $html = '<div class="tb-blurb" id="' . esc($moduleId) . '" style="' . implode(';', $containerStyles) . '">';

    // Render icon or image
    if ($useImage && $image) {
        $html .= '<img src="' . esc($image) . '" alt="' . esc($title) . '" style="max-width:100px;margin-bottom:15px">';
    } elseif ($icon) {
        $html .= '<div class="tb-blurb-icon" style="font-size:' . esc($iconSize) . ';color:' . esc($iconColor) . ';line-height:1">' . tb_render_icon_from_format($icon, $iconSize, $iconColor) . '</div>';
    }

    $html .= '<div class="tb-blurb-content">';

    // Title with styling
    if ($title) {
        $titleStyles = ['margin:0 0 10px'];
        $titleStyles[] = 'color:' . esc($titleColor);
        if (!empty($design['title_size'])) {
            $titleStyles[] = 'font-size:' . esc($design['title_size']);
        }
        if (!empty($design['title_weight'])) {
            $titleStyles[] = 'font-weight:' . esc($design['title_weight']);
        }
        $titleHtml = '<h3 style="' . implode(';', $titleStyles) . '">' . esc($title) . '</h3>';
        $html .= $url ? '<a href="' . esc($url) . '" style="text-decoration:none">' . $titleHtml . '</a>' : $titleHtml;
    }

    // Text with styling
    if ($text) {
        $textStyles = ['margin:0', 'line-height:1.6'];
        $textStyles[] = 'color:' . tb_map_color($textColor);
        if (!empty($design['font_size'])) {
            $textStyles[] = 'font-size:' . esc($design['font_size']);
        }
        $html .= '<p style="' . implode(';', $textStyles) . '">' . esc($text) . '</p>';
    }

    $html .= '</div>'; // close tb-blurb-content
    $html .= '</div>'; // close tb-blurb
    return $output . $html;
}

// ============================================
// RENDERER: circle_counter
// ============================================
function tb_render_module_circle_counter(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'circle-counter-' . uniqid();
    $percent = min(100, max(0, intval($content['percent'] ?? 75)));
    $title = $content['title'] ?? $content['label'] ?? '';
    $circleColor = $design['circle_color'] ?? 'var(--primary)';
    $bgColor = $design['background_color'] ?? '#e0e0e0';
    $textColor = $design['text_color'] ?? 'inherit';
    $size = $design['size'] ?? 150;

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $circumference = 2 * 3.14159 * 45;
    $offset = $circumference - ($percent / 100) * $circumference;

    $html = '<div class="tb-circle-counter" id="' . esc($moduleId) . '" style="text-align:center">';
    $html .= '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 100 100">';
    $html .= '<circle cx="50" cy="50" r="45" fill="none" stroke="' . tb_map_color($bgColor) . '" stroke-width="8"/>';
    $html .= '<circle cx="50" cy="50" r="45" fill="none" stroke="' . esc($circleColor) . '" stroke-width="8" stroke-dasharray="' . $circumference . '" stroke-dashoffset="' . $offset . '" transform="rotate(-90 50 50)" style="transition:stroke-dashoffset 1s ease"/>';
    $html .= '<text x="50" y="55" text-anchor="middle" font-size="20" fill="' . tb_map_color($textColor) . '">' . $percent . '%</text>';
    $html .= '</svg>';
    if ($title) $html .= '<p style="margin:10px 0 0;color:' . tb_map_color($textColor) . '">' . esc($title) . '</p>';
    $html .= '</div>';

    return $output . $html;
}

// ============================================
// RENDERER: comments
// ============================================
function tb_render_module_comments(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'comments-' . uniqid();
    $postId = $options['post_id'] ?? $content['post_id'] ?? 0;
    $bgColor = $design['background_color'] ?? '#f9f9f9';
    $textColor = $design['text_color'] ?? 'inherit';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $html = '<div class="tb-comments" id="' . esc($moduleId) . '" style="background:' . tb_map_color($bgColor) . ';padding:30px;border-radius:8px">';
    $html .= '<h3 style="color:' . tb_map_color($textColor) . ';margin:0 0 20px">Komentarze</h3>';

    if ($postId) {
        $comments = [];
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("SELECT * FROM comments WHERE post_id = ? AND status = 'approved' ORDER BY created_at DESC");
            $stmt->execute([$postId]);
            $comments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            // Comments table not available
        }

        if (empty($comments)) {
            $html .= '<p style="color:#666">No comments yet. Be the first!</p>';
        } else {
            foreach ($comments as $comment) {
                $html .= '<div style="border-bottom:1px solid #ddd;padding:15px 0">';
                $html .= '<strong style="color:' . tb_map_color($textColor) . '">' . esc($comment['author_name']) . '</strong>';
                $html .= '<span style="color:#999;font-size:0.85em;margin-left:10px">' . date('d.m.Y H:i', strtotime($comment['created_at'])) . '</span>';
                $html .= '<p style="margin:10px 0 0;color:' . tb_map_color($textColor) . '">' . esc($comment['content']) . '</p>';
                $html .= '</div>';
            }
        }
    }

    $html .= '<form class="tb-comment-form" method="post" style="margin-top:20px">';
    $html .= '<input type="hidden" name="post_id" value="' . intval($postId) . '">';
    $html .= '<input type="text" name="author_name" placeholder="Your name" required style="width:100%;padding:10px;margin-bottom:10px;border:1px solid #ddd;border-radius:4px">';
    $html .= '<textarea name="content" placeholder="Your comment" required style="width:100%;padding:10px;min-height:100px;border:1px solid #ddd;border-radius:4px"></textarea>';
    $html .= '<button type="submit" style="margin-top:10px;padding:10px 20px;background:var(--primary);color:#fff;border:none;border-radius:4px;cursor:pointer">Add Comment</button>';
    $html .= '</form></div>';

    return $output . $html;
}

// ============================================
// RENDERER: countdown
// ============================================
function tb_render_module_countdown(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'countdown-' . uniqid();
    $targetDate = $content['target_date'] ?? date('Y-m-d', strtotime('+7 days'));
    $title = $content['title'] ?? $content['label'] ?? '';
    $bgColor = $design['background_color'] ?? '#1e1e2e';
    $textColor = $design['text_color'] ?? '#ffffff';
    $accentColor = $design['accent_color'] ?? 'var(--primary)';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $countdownId = $moduleId;

    $html = '<div class="tb-countdown" id="' . $countdownId . '" data-target="' . esc($targetDate) . '" style="background:' . tb_map_color($bgColor) . ';padding:40px;text-align:center;border-radius:8px">';
    if ($title) $html .= '<h3 style="color:' . tb_map_color($textColor) . ';margin:0 0 20px">' . esc($title) . '</h3>';
    $html .= '<div style="display:flex;justify-content:center;gap:20px">';
    foreach (['dni', 'godz', 'min', 'sek'] as $unit) {
        $html .= '<div style="text-align:center"><div class="tb-cd-' . $unit . '" style="font-size:3em;font-weight:bold;color:' . esc($accentColor) . '">00</div><div style="color:' . tb_map_color($textColor) . ';font-size:0.9em">' . $unit . '</div></div>';
    }
    $html .= '</div></div>';

    return $output . $html;
}

// ============================================
// RENDERER: counter
// ============================================
function tb_render_module_counter(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'counter-' . uniqid();
    $number = $content['number'] ?? 0;
    $title = $content['title'] ?? $content['label'] ?? '';
    $prefix = $content['prefix'] ?? '';
    $suffix = $content['suffix'] ?? '';
    $numberColor = $design['number_color'] ?? 'var(--primary)';
    $textColor = $design['text_color'] ?? 'inherit';
    $fontSize = $design['font_size'] ?? '48px';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $html = '<div class="tb-counter" id="' . esc($moduleId) . '" style="text-align:center;padding:20px">';
    $html .= '<div style="font-size:' . esc($fontSize) . ';font-weight:bold;color:' . esc($numberColor) . '">';
    $html .= esc($prefix) . '<span class="tb-counter-number" data-target="' . intval($number) . '">' . intval($number) . '</span>' . esc($suffix);
    $html .= '</div>';
    if ($title) $html .= '<p style="margin:10px 0 0;color:' . tb_map_color($textColor) . ';font-size:1.1em">' . esc($title) . '</p>';
    $html .= '</div>';

    return $output . $html;
}

// ============================================
// RENDERER: hero
// ============================================
function tb_render_module_hero(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'hero-' . uniqid();
    $title = $content['title'] ?? $content['label'] ?? '';
    $subtitle = $content['subtitle'] ?? '';
    $text = $content['text'] ?? $content['description'] ?? '';
    $buttonText = $content['button_text'] ?? $content['primary_button_text'] ?? '';
    $buttonUrl = $content['button_url'] ?? $content['primary_button_url'] ?? '#';
    $secondaryButtonText = $content['secondary_button_text'] ?? '';
    $secondaryButtonUrl = $content['secondary_button_url'] ?? '#';
    // bg_image from Content panel OR background_image from design (legacy)
    $bgImage = $content['bg_image'] ?? $content['background_image'] ?? $design['background_image'] ?? '';
    $bgColor = $content['background_color'] ?? $design['background_color'] ?? '#1e1e2e';
    $textColor = $content['text_color'] ?? $design['text_color'] ?? '#ffffff';
    $overlay = $content['overlay_opacity'] ?? $design['overlay_opacity'] ?? 0.5;
    $minHeight = $content['min_height'] ?? $design['min_height'] ?? '500px';
    $alignment = $content['alignment'] ?? $design['alignment'] ?? 'center';

    // Generate element CSS for inner elements (hover, normal states)
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $bgStyle = $bgImage ? "background-image:url('" . esc($bgImage) . "');background-size:cover;background-position:center" : "background:" . tb_map_color($bgColor);

    // ═══════════════════════════════════════════════════════════════════════════
    // FIX: Added CSS classes to all inner elements for element CSS targeting
    // Classes: tb-hero-title, tb-hero-subtitle, tb-hero-description, tb-hero-button
    // ═══════════════════════════════════════════════════════════════════════════
    $html = '<div class="tb-hero" id="' . esc($moduleId) . '" style="' . $bgStyle . ';min-height:' . esc($minHeight) . ';display:flex;align-items:center;justify-content:center;position:relative">';
    if ($bgImage) $html .= '<div class="tb-hero-overlay" style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,' . floatval($overlay) . ')"></div>';
    $html .= '<div class="tb-hero-content" style="position:relative;z-index:1;text-align:' . esc($alignment) . ';padding:40px;max-width:800px">';
    if ($title) $html .= '<h1 class="tb-hero-title" style="color:' . tb_map_color($textColor) . ';margin:0 0 15px;font-size:3em;transition:all 0.3s ease">' . esc($title) . '</h1>';
    if ($subtitle) $html .= '<h3 class="tb-hero-subtitle" style="color:' . tb_map_color($textColor) . ';opacity:0.8;margin:0 0 25px;font-size:1.1em;transition:all 0.3s ease">' . esc($subtitle) . '</h3>';
    if ($text) $html .= '<p class="tb-hero-description" style="color:' . tb_map_color($textColor) . ';margin:0 0 30px;font-size:1.2em;line-height:1.6;transition:all 0.3s ease">' . esc($text) . '</p>';

    // Buttons container
    $hasButtons = $buttonText || $secondaryButtonText;
    if ($hasButtons) {
        $html .= '<div class="tb-hero-buttons" style="display:flex;gap:15px;justify-content:' . ($alignment === 'center' ? 'center' : ($alignment === 'right' ? 'flex-end' : 'flex-start')) . '">';
        if ($buttonText) {
            $html .= '<a href="' . esc($buttonUrl) . '" class="tb-hero-button tb-button-primary" style="display:inline-block;padding:15px 35px;background:var(--primary);color:#fff;text-decoration:none;border-radius:4px;font-size:1.1em;transition:all 0.3s ease">' . esc($buttonText) . '</a>';
        }
        if ($secondaryButtonText) {
            $html .= '<a href="' . esc($secondaryButtonUrl) . '" class="tb-hero-button tb-button-secondary" style="display:inline-block;padding:15px 35px;background:transparent;border:2px solid #fff;color:#fff;text-decoration:none;border-radius:4px;font-size:1.1em;transition:all 0.3s ease">' . esc($secondaryButtonText) . '</a>';
        }
        $html .= '</div>';
    }

    $html .= '</div></div>';

    return $output . $html;
}

// ============================================
// RENDERER: login
// ============================================
function tb_render_module_login(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'login-' . uniqid();
    $title = $content['title'] ?? 'Logowanie';
    $redirectUrl = $content['redirect_url'] ?? '/';
    $showRegister = $content['show_register_link'] ?? true;
    $bgColor = $design['background_color'] ?? '#ffffff';
    $textColor = $design['text_color'] ?? 'inherit';
    $buttonColor = $design['button_color'] ?? 'var(--primary)';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $html = '<div class="tb-login" id="' . esc($moduleId) . '" style="background:' . tb_map_color($bgColor) . ';padding:40px;border-radius:8px;max-width:400px;margin:0 auto">';
    if ($title) $html .= '<h2 style="color:' . tb_map_color($textColor) . ';margin:0 0 30px;text-align:center">' . esc($title) . '</h2>';
    $html .= '<form method="post" action="/auth/login">';
    $html .= '<input type="hidden" name="redirect" value="' . esc($redirectUrl) . '">';
    $html .= '<input type="email" name="email" placeholder="Email" required style="width:100%;padding:12px;margin-bottom:15px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box">';
    $html .= '<input type="password" name="password" placeholder="Password" required style="width:100%;padding:12px;margin-bottom:20px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box">';
    $html .= '<button type="submit" style="width:100%;padding:12px;background:' . esc($buttonColor) . ';color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:1em">Log In</button>';
    $html .= '</form>';
    if ($showRegister) $html .= '<p style="text-align:center;margin:20px 0 0;color:' . tb_map_color($textColor) . '">Don\'t have an account? <a href="/register" style="color:' . esc($buttonColor) . '">Sign Up</a></p>';
    $html .= '</div>';

    return $output . $html;
}

// ============================================
// RENDERER: menu
// ============================================
function tb_render_module_menu(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $settings = $module['settings'] ?? [];
    $moduleId = $module['id'] ?? 'menu-' . uniqid();

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    // Global mapper already normalized settings→content and items
    // Just check if we have custom items (set menu_source accordingly)
    if (!empty($content['items']) && is_array($content['items'])) {
        $content['menu_source'] = 'custom';
    }
    
    $typo = $settings['typography_link'] ?? $settings['typography'] ?? [];
    
    // Settings can be in content, design, or settings - check all
    // Default to cms_menu with id 1 (header menu) if not specified
    $menuSource = $content['menu_source'] ?? 'cms_menu';
    $cmsMenuId = $content['cms_menu_id'] ?? 1;
    $orientation = $content['orientation'] ?? $design['orientation'] ?? 'horizontal';
    $alignment = $content['alignment'] ?? $design['alignment'] ?? 'center';
    $bgColor = $content['background_color'] ?? $design['background_color'] ?? 'transparent';
    $textColor = $content['text_color'] ?? $typo['color'] ?? $design['text_color'] ?? 'inherit';
    $hoverColor = $content['hover_color'] ?? $design['hover_color'] ?? 'var(--primary)';
    $gap = $content['gap'] ?? $design['gap'] ?? '24px';
    $fontSize = $content['font_size'] ?? $typo['font_size'] ?? $design['font_size'] ?? '16px';
    $fontWeight = $content['font_weight'] ?? $typo['font_weight'] ?? $design['font_weight'] ?? '500';
    $fontFamily = $typo['font_family'] ?? 'inherit';
    
    // Get menu items - always try CMS menu first
    $menuItems = [];
    
    // Try to fetch from CMS menu system
    try {
        $pdo = \core\Database::connection();
        
        // If custom items provided, use them
        if ($menuSource === 'custom' && !empty($content['items'])) {
            $menuItems = $content['items'];
        } else {
            // Fetch from CMS menu - try specified id or fallback to header location
            $stmt = $pdo->prepare("SELECT title, url, target FROM menu_items WHERE menu_id = ? AND is_active = 1 ORDER BY sort_order");
            $stmt->execute([(int)$cmsMenuId]);
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $menuItems[] = [
                    'label' => $row['title'],
                    'url' => $row['url'],
                    'target' => $row['target'] ?? '_self'
                ];
            }
            
            // If no items found and menu_id was default, try header location
            if (empty($menuItems) && $cmsMenuId == 1) {
                $stmt = $pdo->prepare("SELECT mi.title, mi.url, mi.target FROM menu_items mi JOIN menus m ON mi.menu_id = m.id WHERE m.location = 'header' AND mi.is_active = 1 ORDER BY mi.sort_order");
                $stmt->execute();
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $menuItems[] = [
                        'label' => $row['title'],
                        'url' => $row['url'],
                        'target' => $row['target'] ?? '_self'
                    ];
                }
            }
        }
    } catch (\Exception $e) {
        // Silently fail, show empty menu
    }
    
    $flexDir = $orientation === 'vertical' ? 'column' : 'row';
    $justifyMap = ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'];
    $justify = $justifyMap[$alignment] ?? 'center';

    $html = '<nav class="tb-menu tb-menu-' . esc($orientation) . '" id="' . esc($moduleId) . '">';
    $html .= '<ul style="display:flex;flex-direction:' . $flexDir . ';justify-content:' . $justify . ';gap:' . esc($gap) . ';list-style:none;margin:0;padding:0">';

    foreach ($menuItems as $item) {
        $label = $item['label'] ?? $item['text'] ?? $item['title'] ?? '';
        $url = $item['url'] ?? '#';
        $target = $item['target'] ?? '_self';
        $targetAttr = $target === '_blank' ? ' target="_blank" rel="noopener"' : '';
        $html .= '<li><a href="' . esc($url) . '"' . $targetAttr . ' style="color:' . tb_map_color($textColor) . ';font-size:' . esc($fontSize) . ';text-decoration:none;padding:10px 18px;display:inline-block;border-radius:6px;font-weight:' . esc($fontWeight) . ';transition:all 0.2s" onmouseover="this.style.color=\'' . esc($hoverColor) . '\';this.style.background=\'rgba(59,130,246,0.1)\'" onmouseout="this.style.color=\'' . tb_map_color($textColor) . '\';this.style.background=\'transparent\'">' . esc($label) . '</a></li>';
    }
    
    if (empty($menuItems)) {
        $html .= '<li style="color:' . tb_map_color($textColor) . ';opacity:0.5;font-size:' . esc($fontSize) . '">No menu items</li>';
    }

    $html .= '</ul></nav>';
    return $output . $html;
}

// ============================================
// RENDERER: portfolio
// ============================================
function tb_render_module_portfolio(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'portfolio-' . uniqid();
    $items = $content['items'] ?? [];
    $columns = $design['columns'] ?? 3;

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';
    $gap = $design['gap'] ?? '20px';
    $showTitle = $content['show_title'] ?? true;
    $showCategory = $content['show_category'] ?? true;

    $columnWidth = 'calc(' . (100 / $columns) . '% - ' . $gap . ')';

    $html = '<div class="tb-portfolio" id="' . esc($moduleId) . '" style="display:flex;flex-wrap:wrap;gap:' . esc($gap) . '">';

    foreach ($items as $item) {
        $image = $item['image'] ?? '';
        $title = $item['title'] ?? '';
        $category = $item['category'] ?? '';
        $url = $item['url'] ?? '#';

        $html .= '<div class="tb-portfolio-item" style="flex:0 0 ' . $columnWidth . ';position:relative;overflow:hidden;border-radius:8px">';
        // Check if image is valid URL
    $hasValidImage = $image && (str_starts_with($image, "http://") || str_starts_with($image, "https://") || str_starts_with($image, "/") || str_starts_with($image, "data:image/"));

    if ($hasValidImage) {
            $html .= '<a href="' . esc($url) . '">';
            $html .= '<img src="' . esc($image) . '" alt="' . esc($title) . '" style="width:100%;aspect-ratio:4/3;object-fit:cover;display:block;transition:transform 0.3s">';
            $html .= '<div style="position:absolute;bottom:0;left:0;right:0;padding:20px;background:linear-gradient(transparent,rgba(0,0,0,0.8));color:#fff">';
            if ($showTitle && $title) $html .= '<h4 style="margin:0 0 5px">' . esc($title) . '</h4>';
            if ($showCategory && $category) $html .= '<span style="opacity:0.8;font-size:0.9em">' . esc($category) . '</span>';
            $html .= '</div></a>';
        }
        $html .= '</div>';
    }

    $html .= '</div>';
    return $output . $html;
}

// ============================================
// RENDERER: post_content
// ============================================
function tb_render_module_post_content(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'post-content-' . uniqid();
    $postContent = $options['post_content'] ?? $content['content'] ?? '';
    $textColor = $design['text_color'] ?? 'inherit';
    $fontSize = $design['font_size'] ?? '16px';
    $lineHeight = $design['line_height'] ?? '1.8';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $html = '<div class="tb-post-content" id="' . esc($moduleId) . '" style="color:' . tb_map_color($textColor) . ';font-size:' . esc($fontSize) . ';line-height:' . esc($lineHeight) . '">';
    $html .= $postContent;
    $html .= '</div>';

    return $output . $html;
}

// ============================================
// RENDERER: post_slider
// ============================================
function tb_render_module_post_slider(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'post-slider-' . uniqid();
    $postsCount = $content['posts_count'] ?? 5;
    $showExcerpt = $content['show_excerpt'] ?? true;
    $showDate = $content['show_date'] ?? true;
    $showAuthor = $content['show_author'] ?? true;
    $showReadMore = $content['show_read_more'] ?? true;
    $readMoreText = $content['read_more_text'] ?? 'Czytaj wiecej';
    $categoryId = $content['category_id'] ?? null;
    $autoplay = $content['autoplay'] ?? true;
    $interval = $content['interval'] ?? 5000;

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $posts = [];
    try {
        $db = \core\Database::connection();
        $sql = "SELECT a.*, u.username as author_name FROM articles a LEFT JOIN users u ON a.author_id = u.id WHERE a.status = 'published'";
        if ($categoryId) $sql .= " AND a.category_id = " . intval($categoryId);
        $sql .= " ORDER BY a.published_at DESC, a.created_at DESC LIMIT " . intval($postsCount);
        $posts = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {
        return $output;
    }

    if (empty($posts)) {
        return $output;
    }

    $sliderId = $moduleId;

    $html = '<div class="tb-post-slider" id="' . $sliderId . '" style="position:relative;overflow:hidden">';
    $html .= '<div class="tb-post-slider-track" style="display:flex;transition:transform 0.5s ease">';

    foreach ($posts as $post) {
        $html .= '<div class="tb-post-slide" style="min-width:100%;position:relative">';
        if (!empty($post['featured_image'])) {
            $html .= '<img src="' . esc($post['featured_image']) . '" alt="' . esc($post['title']) . '" style="width:100%;height:400px;object-fit:cover">';
        }
        $html .= '<div style="position:absolute;bottom:0;left:0;right:0;padding:40px;background:linear-gradient(transparent,rgba(0,0,0,0.8));color:#fff">';
        $html .= '<h3 style="margin:0 0 10px;font-size:1.8em">' . esc($post['title']) . '</h3>';
        if ($showDate || $showAuthor) {
            $html .= '<p style="margin:0 0 10px;opacity:0.8">';
            if ($showDate) $html .= date('d.m.Y', strtotime($post['created_at']));
            if ($showDate && $showAuthor) $html .= ' | ';
            if ($showAuthor) $html .= esc($post['author_name'] ?? 'Admin');
            $html .= '</p>';
        }
        if ($showExcerpt && !empty($post['excerpt'])) {
            $html .= '<p style="margin:0 0 15px;line-height:1.5">' . esc($post['excerpt']) . '</p>';
        }
        if ($showReadMore) {
            $html .= '<a href="/post/' . esc($post['slug']) . '" style="display:inline-block;padding:10px 25px;background:var(--primary);color:#fff;text-decoration:none;border-radius:4px">' . esc($readMoreText) . '</a>';
        }
        $html .= '</div></div>';
    }

    $html .= '</div>';
    $html .= '<button class="tb-slider-prev" style="position:absolute;left:20px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.9);border:none;padding:15px 20px;cursor:pointer;font-size:18px">&lt;</button>';
    $html .= '<button class="tb-slider-next" style="position:absolute;right:20px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.9);border:none;padding:15px 20px;cursor:pointer;font-size:18px">&gt;</button>';
    $html .= '</div>';

    return $output . $html;
}

// ============================================
// RENDERER: post_title
// ============================================
function tb_render_module_post_title(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'post-title-' . uniqid();
    $title = $options['post_title'] ?? $content['title'] ?? '';
    $tag = $design['tag'] ?? 'h1';
    $textColor = $design['text_color'] ?? 'inherit';
    $fontSize = $design['font_size'] ?? '2.5em';
    $alignment = $design['alignment'] ?? 'left';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $allowedTags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span'];
    $tag = in_array($tag, $allowedTags) ? $tag : 'h1';

    $html = '<' . $tag . ' class="tb-post-title" id="' . esc($moduleId) . '" style="color:' . tb_map_color($textColor) . ';font-size:' . esc($fontSize) . ';text-align:' . esc($alignment) . ';margin:0">';
    $html .= esc($title);
    $html .= '</' . $tag . '>';

    return $output . $html;
}

// ============================================
// RENDERER: posts_navigation
// ============================================
function tb_render_module_posts_navigation(array $module, array $options = []): string
{
    $content = is_array($module['content'] ?? []) && !isset($module['content'][0]) ? $module['content'] : [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'posts-navigation-' . uniqid();
    $postId = $options['post_id'] ?? $content['post_id'] ?? 0;
    $prevText = $content['prev_text'] ?? 'Previous Post';
    $nextText = $content['next_text'] ?? 'Next Post';
    $bgColor = $design['background_color'] ?? '#f5f5f5';
    $textColor = $design['text_color'] ?? 'inherit';
    $linkColor = $design['link_color'] ?? 'var(--primary)';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    // Without post context, don't render navigation
    if (!$postId) {
        return $output;
    }

    $html = '<nav class="tb-posts-navigation" id="' . esc($moduleId) . '" style="display:flex;justify-content:space-between;padding:20px;background:' . tb_map_color($bgColor) . ';border-radius:8px">';

    if ($postId) {
        $prev = null;
        $next = null;
        try {
            $db = \core\Database::connection();

            $stmt = $db->prepare("SELECT id, title, slug FROM posts WHERE id < ? AND status = 'published' ORDER BY id DESC LIMIT 1");
            $stmt->execute([$postId]);
            $prev = $stmt->fetch(\PDO::FETCH_ASSOC);

            $stmt = $db->prepare("SELECT id, title, slug FROM posts WHERE id > ? AND status = 'published' ORDER BY id ASC LIMIT 1");
            $stmt->execute([$postId]);
            $next = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            // Posts table not available
        }

        if ($prev) {
            $html .= '<a href="/post/' . esc($prev['slug']) . '" style="color:' . esc($linkColor) . ';text-decoration:none">';
            $html .= '<span style="color:' . tb_map_color($textColor) . ';font-size:0.85em;display:block">' . esc($prevText) . '</span>';
            $html .= '<span style="font-weight:bold">' . esc($prev['title']) . '</span>';
            $html .= '</a>';
        } else {
            $html .= '<span></span>';
        }

        if ($next) {
            $html .= '<a href="/post/' . esc($next['slug']) . '" style="color:' . esc($linkColor) . ';text-decoration:none;text-align:right">';
            $html .= '<span style="color:' . tb_map_color($textColor) . ';font-size:0.85em;display:block">' . esc($nextText) . '</span>';
            $html .= '<span style="font-weight:bold">' . esc($next['title']) . '</span>';
            $html .= '</a>';
        }
    }

    $html .= '</nav>';
    return $output . $html;
}

// ============================================
// RENDERER: search
// ============================================
function tb_render_module_search(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'search-' . uniqid();
    $placeholder = $content['placeholder'] ?? 'Search...';
    $buttonText = $content['button_text'] ?? 'Search';
    $showButton = $content['show_button'] ?? true;
    $bgColor = $design['background_color'] ?? '#ffffff';
    $borderColor = $design['border_color'] ?? '#dddddd';
    $buttonColor = $design['button_color'] ?? 'var(--primary)';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $html = '<form class="tb-search" id="' . esc($moduleId) . '" action="/search" method="get" style="display:flex;gap:10px">';
    $html .= '<input type="text" name="q" placeholder="' . esc($placeholder) . '" style="flex:1;padding:12px 15px;border:1px solid ' . tb_map_color($borderColor) . ';border-radius:4px;background:' . tb_map_color($bgColor) . ';font-size:1em">';
    if ($showButton) {
        $html .= '<button type="submit" style="padding:12px 25px;background:' . esc($buttonColor) . ';color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:1em">' . esc($buttonText) . '</button>';
    }
    $html .= '</form>';

    return $output . $html;
}

// ============================================
// RENDERER: sidebar
// ============================================
function tb_render_module_sidebar(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'sidebar-' . uniqid();
    $widgets = $content['widgets'] ?? [];
    $bgColor = $design['background_color'] ?? '#f9f9f9';
    $textColor = $design['text_color'] ?? 'inherit';
    $padding = $design['padding'] ?? '20px';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $html = '<aside class="tb-sidebar" id="' . esc($moduleId) . '" style="background:' . tb_map_color($bgColor) . ';padding:' . esc($padding) . ';border-radius:8px">';

    foreach ($widgets as $widget) {
        $widgetType = $widget['type'] ?? 'text';
        $widgetTitle = $widget['title'] ?? '';

        $html .= '<div class="tb-sidebar-widget" style="margin-bottom:25px">';
        if ($widgetTitle) $html .= '<h4 style="color:' . tb_map_color($textColor) . ';margin:0 0 15px;padding-bottom:10px;border-bottom:2px solid var(--primary)">' . esc($widgetTitle) . '</h4>';

        switch ($widgetType) {
            case 'recent_posts':
                $posts = [];
                try {
                    $db = \core\Database::connection();
                    $limit = $widget['limit'] ?? 5;
                    $posts = $db->query("SELECT title, slug, created_at FROM posts WHERE status = 'published' ORDER BY created_at DESC LIMIT " . intval($limit))->fetchAll(\PDO::FETCH_ASSOC);
                } catch (\Throwable $e) {
                    $html .= '<p style="color:#999;font-size:0.9em">Recent posts unavailable</p>';
                    break;
                }
                $html .= '<ul style="list-style:none;margin:0;padding:0">';
                foreach ($posts as $post) {
                    $html .= '<li style="margin-bottom:10px"><a href="/post/' . esc($post['slug']) . '" style="color:' . tb_map_color($textColor) . ';text-decoration:none">' . esc($post['title']) . '</a></li>';
                }
                $html .= '</ul>';
                break;

            case 'categories':
                $categories = [];
                try {
                    $db = \core\Database::connection();
                    $categories = $db->query("SELECT name, slug FROM categories ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
                } catch (\Throwable $e) {
                    $html .= '<p style="color:#999;font-size:0.9em">Categories unavailable</p>';
                    break;
                }
                $html .= '<ul style="list-style:none;margin:0;padding:0">';
                foreach ($categories as $cat) {
                    $html .= '<li style="margin-bottom:8px"><a href="/category/' . esc($cat['slug']) . '" style="color:' . tb_map_color($textColor) . ';text-decoration:none">' . esc($cat['name']) . '</a></li>';
                }
                $html .= '</ul>';
                break;

            case 'text':
            default:
                $html .= '<div style="color:' . tb_map_color($textColor) . ';line-height:1.6">' . ($widget['content'] ?? '') . '</div>';
                break;
        }

        $html .= '</div>';
    }

    $html .= '</aside>';
    return $output . $html;
}

// ============================================
// RENDERER: signup
// ============================================
function tb_render_module_signup(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'signup-' . uniqid();
    $title = $content['title'] ?? 'Rejestracja';
    $redirectUrl = $content['redirect_url'] ?? '/';
    $showLogin = $content['show_login_link'] ?? true;
    $bgColor = $design['background_color'] ?? '#ffffff';
    $textColor = $design['text_color'] ?? 'inherit';
    $buttonColor = $design['button_color'] ?? 'var(--primary)';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $html = '<div class="tb-signup" id="' . esc($moduleId) . '" style="background:' . tb_map_color($bgColor) . ';padding:40px;border-radius:8px;max-width:400px;margin:0 auto">';
    if ($title) $html .= '<h2 style="color:' . tb_map_color($textColor) . ';margin:0 0 30px;text-align:center">' . esc($title) . '</h2>';
    $html .= '<form method="post" action="/auth/register">';
    $html .= '<input type="hidden" name="redirect" value="' . esc($redirectUrl) . '">';
    $html .= '<input type="text" name="name" placeholder="Full Name" required style="width:100%;padding:12px;margin-bottom:15px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box">';
    $html .= '<input type="email" name="email" placeholder="Email" required style="width:100%;padding:12px;margin-bottom:15px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box">';
    $html .= '<input type="password" name="password" placeholder="Password" required style="width:100%;padding:12px;margin-bottom:15px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box">';
    $html .= '<input type="password" name="password_confirm" placeholder="Confirm Password" required style="width:100%;padding:12px;margin-bottom:20px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box">';
    $html .= '<button type="submit" style="width:100%;padding:12px;background:' . esc($buttonColor) . ';color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:1em">Sign Up</button>';
    $html .= '</form>';
    if ($showLogin) $html .= '<p style="text-align:center;margin:20px 0 0;color:' . tb_map_color($textColor) . '">Already have an account? <a href="/login" style="color:' . esc($buttonColor) . '">Log In</a></p>';
    $html .= '</div>';

    return $output . $html;
}

// ============================================
// RENDERER: slider
// ============================================
function tb_render_module_slider(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'slider-' . uniqid();
    $slides = $content['slides'] ?? [];
    $autoplay = $content['autoplay'] ?? true;
    $interval = $content['interval'] ?? 5000;
    $showArrows = $content['show_arrows'] ?? true;
    $showDots = $content['show_dots'] ?? true;

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $sliderId = $moduleId;

    $html = '<div class="tb-slider" id="' . $sliderId . '" style="position:relative;overflow:hidden;width:100%">';
    $html .= '<div class="tb-slider-track" style="display:flex;transition:transform 0.5s ease">';

    foreach ($slides as $index => $slide) {
        $image = $slide['image'] ?? '';
        $title = $slide['title'] ?? '';
        $text = $slide['text'] ?? '';
        $buttonText = $slide['button_text'] ?? '';
        $buttonUrl = $slide['button_url'] ?? '#';

        $html .= '<div class="tb-slide" style="min-width:100%;position:relative">';
        // Check if image is valid URL
    $hasValidImage = $image && (str_starts_with($image, "http://") || str_starts_with($image, "https://") || str_starts_with($image, "/") || str_starts_with($image, "data:image/"));

    if ($hasValidImage) {
            $html .= '<img src="' . esc($image) . '" alt="' . esc($title) . '" style="width:100%;height:auto;display:block">';
        }
        if ($title || $text || $buttonText) {
            $html .= '<div class="tb-slide-content" style="position:absolute;bottom:20%;left:50%;transform:translateX(-50%);text-align:center;color:#fff;text-shadow:0 2px 4px rgba(0,0,0,0.5)">';
            if ($title) $html .= '<h2 style="margin:0 0 10px;font-size:2.5em">' . esc($title) . '</h2>';
            if ($text) $html .= '<p style="margin:0 0 20px;font-size:1.2em">' . esc($text) . '</p>';
            if ($buttonText) $html .= '<a href="' . esc($buttonUrl) . '" style="display:inline-block;padding:12px 30px;background:var(--primary);color:#fff;text-decoration:none;border-radius:4px">' . esc($buttonText) . '</a>';
            $html .= '</div>';
        }
        $html .= '</div>';
    }

    $html .= '</div>';

    if ($showArrows && count($slides) > 1) {
        $html .= '<button class="tb-slider-prev" style="position:absolute;left:20px;top:50%;transform:translateY(-50%);background:rgba(0,0,0,0.5);color:#fff;border:none;padding:15px 20px;cursor:pointer;font-size:20px">&lt;</button>';
        $html .= '<button class="tb-slider-next" style="position:absolute;right:20px;top:50%;transform:translateY(-50%);background:rgba(0,0,0,0.5);color:#fff;border:none;padding:15px 20px;cursor:pointer;font-size:20px">&gt;</button>';
    }

    if ($showDots && count($slides) > 1) {
        $html .= '<div class="tb-slider-dots" style="position:absolute;bottom:20px;left:50%;transform:translateX(-50%);display:flex;gap:10px">';
        for ($i = 0; $i < count($slides); $i++) {
            $active = $i === 0 ? '#fff' : 'rgba(255,255,255,0.5)';
            $html .= '<span style="width:12px;height:12px;border-radius:50%;background:' . $active . ';cursor:pointer"></span>';
        }
        $html .= '</div>';
    }

    $html .= '</div>';
    return $output . $html;
}

// ============================================
// RENDERER: team
// ============================================
function tb_render_module_team(array $module, array $options = []): string
{
    $c = is_array($module["content"] ?? null) && !isset($module["content"][0]) ? $module["content"] : [];
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'team-' . uniqid();

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    // Support both: array of members OR single member fields from Content panel
    $members = $content['members'] ?? [];
    if (empty($members) && !empty($content['name'])) {
        // Single member from Content panel
        $members = [[
            'name' => $content['name'] ?? '',
            'position' => $content['role'] ?? $content['position'] ?? '',
            'photo' => $content['photo'] ?? $content['image'] ?? '',
            'bio' => $content['bio'] ?? '',
            'social' => $content['social'] ?? []
        ]];
    }

    $columns = $design['columns'] ?? 3;
    $showSocial = $content['show_social'] ?? true;
    $cardBg = $design['card_background'] ?? '#ffffff';
    $textColor = $design['text_color'] ?? 'inherit';
    $gap = $design['gap'] ?? '30px';

    $columnWidth = 'calc(' . (100 / $columns) . '% - ' . $gap . ')';

    $html = '<div class="tb-team" id="' . esc($moduleId) . '" style="display:flex;flex-wrap:wrap;gap:' . esc($gap) . '">';

    foreach ($members as $member) {
        $name = $member['name'] ?? '';
        $position = $member['position'] ?? '';
        $photo = $member['photo'] ?? '';
        $bio = $member['bio'] ?? '';
        $social = $member['social'] ?? [];

        $html .= '<div class="tb-team-member" style="flex:0 0 ' . $columnWidth . ';background:' . esc($cardBg) . ';border-radius:8px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.1)">';

        if ($photo) {
            $html .= '<img src="' . esc($photo) . '" alt="' . esc($name) . '" style="width:100%;aspect-ratio:1;object-fit:cover">';
        }

        $html .= '<div style="padding:20px;text-align:center;color:' . tb_map_color($textColor) . '">';
        if ($name) $html .= '<h3 style="margin:0 0 5px;font-size:1.2em">' . esc($name) . '</h3>';
        if ($position) $html .= '<p style="margin:0 0 10px;color:#666;font-size:0.9em">' . esc($position) . '</p>';
        if ($bio) $html .= '<p style="margin:0 0 15px;font-size:0.95em;line-height:1.5">' . esc($bio) . '</p>';

        if ($showSocial && !empty($social)) {
            $html .= '<div style="display:flex;justify-content:center;gap:10px">';
            foreach ($social as $link) {
                $html .= '<a href="' . esc($link['url'] ?? '#') . '" target="_blank" style="color:#666;text-decoration:none">' . esc($link['icon'] ?? 'link') . '</a>';
            }
            $html .= '</div>';
        }

        $html .= '</div></div>';
    }

    $html .= '</div>';
    return $output . $html;
}

// ============================================
// RENDERER: toggle
// ============================================
function tb_render_module_toggle(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'toggle-' . uniqid();

    // Support both: array of items OR single toggle fields from Content panel
    $items = $content['items'] ?? [];
    if (empty($items) && !empty($content['title'])) {
        // Single toggle from Content panel
        $items = [[
            'title' => $content['title'] ?? 'Toggle Title',
            'content' => $content['content'] ?? ''
        ]];
    }

    $openFirst = $content['open_first'] ?? $content['open_by_default'] ?? false;
    $allowMultiple = $content['allow_multiple'] ?? true;

    // Design settings - fallback defaults (can be overridden by elements styling)
    $bgColor = $design['background_color'] ?? '#f5f5f5';
    $textColor = $design['text_color'] ?? '#333333';
    $borderColor = $design['border_color'] ?? '#dddddd';
    $accentColor = $design['accent_color'] ?? 'var(--primary)';
    $fontWeight = $design['font_weight'] ?? '500';

    // Generate element CSS if elements styling is defined
    $elementCss = tb_generate_module_element_css($module);
    $hasElementStyles = !empty($design['elements']);

    // Base CSS - only applied if no custom element styles
    $html = '';
    if ($elementCss) {
        $html .= '<style>' . $elementCss . '</style>';
    }

    // Base inline style for module
    $html .= '<div class="tb-toggle" id="' . esc($moduleId) . '" style="border:1px solid ' . tb_map_color($borderColor) . ';border-radius:8px;overflow:hidden">';

    foreach ($items as $index => $item) {
        $title = $item['title'] ?? 'Toggle Item';
        $body = $item['content'] ?? '';
        $isOpen = ($openFirst && $index === 0);
        $openClass = $isOpen ? ' open' : '';

        // Item container
        $html .= '<div class="tb-toggle-item' . $openClass . '" style="border-bottom:1px solid ' . tb_map_color($borderColor) . '">';

        // Header (button) - use class for styling, inline as fallback
        $headerStyle = $hasElementStyles ? '' : 'background:' . tb_map_color($bgColor) . ';color:' . tb_map_color($textColor) . ';font-weight:' . esc($fontWeight) . ';';
        $html .= '<button type="button" class="tb-toggle-header" onclick="tbToggleItem(this)" aria-expanded="' . ($isOpen ? 'true' : 'false') . '" style="width:100%;padding:15px 20px;border:none;text-align:left;cursor:pointer;display:flex;justify-content:space-between;align-items:center;font-size:1em;' . $headerStyle . '">';
        $html .= '<span class="tb-toggle-title">' . esc($title) . '</span>';
        $html .= '<span class="tb-toggle-icon" style="transition:transform 0.3s">' . ($isOpen ? '−' : '+') . '</span>';
        $html .= '</button>';

        // Content panel
        $contentStyle = $hasElementStyles ? '' : 'color:' . tb_map_color($textColor) . ';';
        $html .= '<div class="tb-toggle-content" style="padding:0 20px;max-height:' . ($isOpen ? '1000px' : '0') . ';overflow:hidden;transition:max-height 0.3s,padding 0.3s;' . ($isOpen ? 'padding:15px 20px' : '') . '">';
        $html .= '<div class="tb-toggle-body" style="line-height:1.6;' . $contentStyle . '">' . $body . '</div>';
        $html .= '</div></div>';
    }

    $html .= '</div>';

    // Add inline script for toggle functionality (only once per page)
    static $toggleScriptAdded = false;
    if (!$toggleScriptAdded) {
        $html .= '<script>function tbToggleItem(btn){';
        $html .= 'var item=btn.parentElement;var content=btn.nextElementSibling;var icon=btn.querySelector(".tb-toggle-icon");';
        $html .= 'var isOpen=btn.getAttribute("aria-expanded")==="true";';
        $html .= 'if(isOpen){content.style.maxHeight="0";content.style.padding="0 20px";icon.textContent="+";btn.setAttribute("aria-expanded","false");item.classList.remove("open");}';
        $html .= 'else{content.style.maxHeight="1000px";content.style.padding="15px 20px";icon.textContent="−";btn.setAttribute("aria-expanded","true");item.classList.add("open");}';
        $html .= '}</script>';
        $toggleScriptAdded = true;
    }

    return $html;
}

// ============================================
// RENDERER: video_slider
// ============================================
function tb_render_module_video_slider(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'video-slider-' . uniqid();
    $videos = $content['videos'] ?? [];
    $showThumbnails = $content['show_thumbnails'] ?? true;
    $autoplay = $content['autoplay'] ?? false;
    $bgColor = $design['background_color'] ?? '#000000';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $sliderId = $moduleId;

    $html = '<div class="tb-video-slider" id="' . $sliderId . '" style="background:' . tb_map_color($bgColor) . ';position:relative">';

    $html .= '<div class="tb-video-main" style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden">';

    foreach ($videos as $index => $video) {
        $src = $video['src'] ?? '';
        $type = $video['type'] ?? 'video';
        $poster = $video['poster'] ?? '';
        $display = $index === 0 ? 'block' : 'none';

        $html .= '<div class="tb-video-item" data-index="' . $index . '" style="position:absolute;top:0;left:0;width:100%;height:100%;display:' . $display . '">';

        if ($type === 'youtube') {
            preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $src, $matches);
            $youtubeId = $matches[1] ?? '';
            if ($youtubeId) {
                $html .= '<iframe src="https://www.youtube.com/embed/' . esc($youtubeId) . '" style="width:100%;height:100%;border:none" allowfullscreen></iframe>';
            }
        } elseif ($type === 'vimeo') {
            preg_match('/vimeo\.com\/(\d+)/', $src, $matches);
            $vimeoId = $matches[1] ?? '';
            if ($vimeoId) {
                $html .= '<iframe src="https://player.vimeo.com/video/' . esc($vimeoId) . '" style="width:100%;height:100%;border:none" allowfullscreen></iframe>';
            }
        } else {
            $html .= '<video src="' . esc($src) . '" poster="' . esc($poster) . '" controls style="width:100%;height:100%;object-fit:cover"' . ($autoplay && $index === 0 ? ' autoplay muted' : '') . '></video>';
        }

        $html .= '</div>';
    }

    $html .= '</div>';

    if ($showThumbnails && count($videos) > 1) {
        $html .= '<div class="tb-video-thumbnails" style="display:flex;gap:10px;padding:10px;overflow-x:auto">';
        foreach ($videos as $index => $video) {
            $poster = $video['poster'] ?? '';
            $title = $video['title'] ?? 'Video ' . ($index + 1);
            $html .= '<div style="flex:0 0 120px;cursor:pointer;opacity:' . ($index === 0 ? '1' : '0.6') . '">';
            if ($poster) {
                $html .= '<img src="' . esc($poster) . '" alt="' . esc($title) . '" style="width:100%;aspect-ratio:16/9;object-fit:cover;border-radius:4px">';
            } else {
                $html .= '<div style="width:100%;aspect-ratio:16/9;background:#333;border-radius:4px;display:flex;align-items:center;justify-content:center;color:#fff">' . esc($title) . '</div>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
    }

    $html .= '</div>';
    return $output . $html;
}

// ============================================
// RENDERER: audio
// ============================================
function tb_render_module_audio(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'audio-' . uniqid();
    $audioUrl = $content['audio_url'] ?? '';
    $title = $content['title'] ?? $content['label'] ?? '';
    $artist = $content['artist'] ?? '';
    $cover = $content['cover_image'] ?? '';
    $autoplay = $content['autoplay'] ?? false;
    $loop = $content['loop'] ?? false;
    $bgColor = $design['background_color'] ?? '#f5f5f5';
    $textColor = $design['text_color'] ?? 'inherit';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $html = '<div class="tb-audio" id="' . esc($moduleId) . '" style="background:' . tb_map_color($bgColor) . ';padding:20px;border-radius:8px;display:flex;align-items:center;gap:20px">';

    if ($cover) {
        $html .= '<img src="' . esc($cover) . '" alt="' . esc($title) . '" style="width:80px;height:80px;object-fit:cover;border-radius:4px">';
    }

    $html .= '<div style="flex:1;color:' . tb_map_color($textColor) . '">';
    if ($title) $html .= '<h3 style="margin:0 0 5px">' . esc($title) . '</h3>';
    if ($artist) $html .= '<p style="margin:0 0 10px;color:#666">' . esc($artist) . '</p>';
    $html .= '<audio controls style="width:100%"' . ($autoplay ? ' autoplay' : '') . ($loop ? ' loop' : '') . '>';
    $html .= '<source src="' . esc($audioUrl) . '" type="audio/mpeg">';
    $html .= 'Twoja przegladarka nie obsluguje elementu audio.';
    $html .= '</audio>';
    $html .= '</div></div>';

    return $output . $html;
}

// ============================================
// RENDERER: bar_counters
// ============================================
function tb_render_module_bar_counters(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'bar-counters-' . uniqid();
    $bars = $content['bars'] ?? [];
    $barColor = $design['bar_color'] ?? 'var(--primary)';
    $bgColor = $design['background_color'] ?? '#e0e0e0';
    $textColor = $design['text_color'] ?? 'inherit';
    $height = $design['bar_height'] ?? '20px';

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $html = '<div class="tb-bar-counters" id="' . esc($moduleId) . '" style="display:flex;flex-direction:column;gap:15px">';

    foreach ($bars as $bar) {
        $label = $bar['label'] ?? '';
        $percent = min(100, max(0, intval($bar['percent'] ?? 0)));

        $html .= '<div class="tb-bar-item">';
        $html .= '<div style="display:flex;justify-content:space-between;margin-bottom:5px;color:' . tb_map_color($textColor) . '">';
        $html .= '<span>' . esc($label) . '</span>';
        $html .= '<span>' . $percent . '%</span>';
        $html .= '</div>';
        $html .= '<div style="background:' . tb_map_color($bgColor) . ';border-radius:4px;overflow:hidden;height:' . esc($height) . '">';
        $html .= '<div style="width:' . $percent . '%;background:' . esc($barColor) . ';height:100%;border-radius:4px;transition:width 1s ease"></div>';
        $html .= '</div>';
        $html .= '</div>';
    }

    $html .= '</div>';
    return $output . $html;
}

// ============================================
// RENDERER: social (alias for social_follow)
// ============================================
function tb_render_module_social(array $module, array $options = []): string
{
    return tb_render_module_social_follow($module, $options);
}

// ============================================
// RENDERER: social_follow
// ============================================
function tb_render_module_social_follow(array $module, array $options = []): string
{
    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $moduleId = $module['id'] ?? 'social-follow-' . uniqid();
    $networks = $content['networks'] ?? [];
    $urlNewWindow = $content['url_new_window'] ?? true;
    $showFollowButton = $content['show_follow_button'] ?? false;

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $iconSize = $design['icon_size'] ?? '44px';
    $iconColor = $design['icon_color'] ?? ''; // empty = brand colors
    $iconShape = $design['icon_shape'] ?? 'circle';
    $gap = $design['gap'] ?? '15px';
    $alignment = $design['alignment'] ?? 'left';
    $bgStyle = $design['background_style'] ?? 'filled';
    
    // Default networks if none provided
    if (empty($networks)) {
        $networks = [
            ['network' => 'facebook', 'url' => '#', 'enabled' => true],
            ['network' => 'twitter', 'url' => '#', 'enabled' => true],
            ['network' => 'instagram', 'url' => '#', 'enabled' => true],
        ];
    }
    
    // Filter enabled networks
    $enabledNetworks = array_filter($networks, fn($n) => !empty($n['enabled']) && !empty($n['url']));
    
    if (empty($enabledNetworks)) {
        $enabledNetworks = $networks; // fallback
    }
    
    // Brand colors for social networks
    $brandColors = [
        'facebook' => '#1877f2',
        'twitter' => '#1da1f2',
        'x' => '#000000',
        'instagram' => '#e4405f',
        'linkedin' => '#0a66c2',
        'youtube' => '#ff0000',
        'pinterest' => '#bd081c',
        'tiktok' => '#000000',
        'github' => 'inherit',
        'dribbble' => '#ea4c89',
        'vimeo' => '#1ab7ea',
        'snapchat' => '#fffc00',
        'whatsapp' => '#25d366',
        'telegram' => '#0088cc',
        'reddit' => '#ff4500',
        'discord' => '#5865f2',
        'twitch' => '#9146ff',
        'spotify' => '#1db954',
        'soundcloud' => '#ff5500',
        'behance' => '#1769ff',
    ];
    
    // SVG icons for social networks
    $icons = [
        'facebook' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
        'twitter' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>',
        'x' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
        'instagram' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>',
        'linkedin' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
        'youtube' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
        'pinterest' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/></svg>',
        'tiktok' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>',
        'github' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>',
        'dribbble' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 24C5.385 24 0 18.615 0 12S5.385 0 12 0s12 5.385 12 12-5.385 12-12 12zm10.12-10.358c-.35-.11-3.17-.953-6.384-.438 1.34 3.684 1.887 6.684 1.992 7.308 2.3-1.555 3.936-4.02 4.395-6.87zm-6.115 7.808c-.153-.9-.75-4.032-2.19-7.77l-.066.02c-5.79 2.015-7.86 6.025-8.04 6.4 1.73 1.358 3.92 2.166 6.29 2.166 1.42 0 2.77-.29 4-.814zm-11.62-2.58c.232-.4 3.045-5.055 8.332-6.765.135-.045.27-.084.405-.12-.26-.585-.54-1.167-.832-1.74C7.17 11.775 2.206 11.71 1.756 11.7l-.004.312c0 2.633.998 5.037 2.634 6.855zm-2.42-8.955c.46.008 4.683.026 9.477-1.248-1.698-3.018-3.53-5.558-3.8-5.928-2.868 1.35-5.01 3.99-5.676 7.17zM9.6 2.052c.282.38 2.145 2.914 3.822 6 3.645-1.365 5.19-3.44 5.373-3.702-1.81-1.61-4.19-2.586-6.795-2.586-.825 0-1.63.1-2.4.285zm10.335 3.483c-.218.29-1.935 2.493-5.724 4.04.24.49.47.985.68 1.486.08.18.15.36.22.53 3.41-.43 6.8.26 7.14.33-.02-2.42-.88-4.64-2.31-6.38z"/></svg>',
        'vimeo' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.977 6.416c-.105 2.338-1.739 5.543-4.894 9.609-3.268 4.247-6.026 6.37-8.29 6.37-1.409 0-2.578-1.294-3.553-3.881L5.322 11.4C4.603 8.816 3.834 7.522 3.01 7.522c-.179 0-.806.378-1.881 1.132L0 7.197c1.185-1.044 2.351-2.084 3.501-3.128C5.08 2.701 6.266 1.984 7.055 1.91c1.867-.18 3.016 1.1 3.447 3.838.465 2.953.789 4.789.971 5.507.539 2.45 1.131 3.674 1.776 3.674.502 0 1.256-.796 2.265-2.385 1.004-1.589 1.54-2.797 1.612-3.628.144-1.371-.395-2.061-1.614-2.061-.574 0-1.167.121-1.777.391 1.186-3.868 3.434-5.757 6.762-5.637 2.473.06 3.628 1.664 3.493 4.797l-.013.01z"/></svg>',
        'whatsapp' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>',
        'telegram' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>',
        'reddit' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 0 1 .042.52c0 2.694-3.13 4.87-7.004 4.87-3.874 0-7.004-2.176-7.004-4.87 0-.183.015-.366.043-.534A1.748 1.748 0 0 1 4.028 12c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 0 1 .14-.197.35.35 0 0 1 .238-.042l2.906.617a1.214 1.214 0 0 1 1.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 0-.688-.561-1.249-1.249-1.249zm5.5 0c-.687 0-1.248.561-1.248 1.25 0 .687.561 1.248 1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.562-1.249-1.25-1.249zm-5.466 3.99a.327.327 0 0 0-.231.094.33.33 0 0 0 0 .463c.842.842 2.484.913 2.961.913.477 0 2.105-.056 2.961-.913a.361.361 0 0 0 .029-.463.33.33 0 0 0-.464 0c-.547.533-1.684.73-2.512.73-.828 0-1.979-.196-2.512-.73a.326.326 0 0 0-.232-.095z"/></svg>',
        'discord' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189z"/></svg>',
        'twitch' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714z"/></svg>',
        'spotify' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>',
        'soundcloud' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M1.175 12.225c-.051 0-.094.046-.101.1l-.233 2.154.233 2.105c.007.058.05.098.101.098.05 0 .09-.04.099-.098l.255-2.105-.27-2.154c-.009-.06-.052-.1-.1-.1m-.899.828c-.06 0-.091.037-.104.094L0 14.479l.165 1.308c.014.057.045.094.09.094s.089-.037.099-.094l.21-1.308-.21-1.319c-.01-.057-.045-.09-.09-.09m1.83-1.229c-.061 0-.12.045-.12.104l-.21 2.563.225 2.458c0 .06.045.12.119.12.061 0 .105-.061.121-.12l.254-2.474-.254-2.548c-.016-.06-.061-.12-.121-.12m.945-.089c-.075 0-.135.06-.15.135l-.193 2.64.21 2.544c.016.077.075.138.149.138.075 0 .135-.061.15-.138l.24-2.544-.24-2.64c-.015-.074-.074-.135-.149-.135l-.017-.001zm1.155.36c-.005-.09-.075-.149-.159-.149-.09 0-.158.06-.164.149l-.217 2.43.2 2.563c.005.09.075.157.159.157.074 0 .148-.068.148-.158l.227-2.563-.227-2.444.033.015zm.809-1.709c-.101 0-.18.09-.18.181l-.21 3.957.187 2.563c0 .09.08.164.18.164.094 0 .174-.09.18-.18l.209-2.563-.209-3.972c-.008-.104-.088-.18-.18-.18m.959-.914c-.105 0-.195.09-.203.194l-.18 4.872.165 2.548c0 .12.09.209.195.209.104 0 .193-.089.21-.209l.193-2.548-.192-4.856c-.016-.12-.105-.21-.21-.21m.989-.449c-.121 0-.211.089-.225.209l-.165 5.275.165 2.52c.014.119.104.225.225.225.119 0 .225-.105.225-.225l.195-2.52-.196-5.275c0-.12-.105-.225-.225-.225m1.245.045c0-.135-.105-.24-.24-.24-.119 0-.24.105-.24.24l-.149 5.441.149 2.503c.016.135.121.24.256.24s.24-.105.24-.24l.164-2.503-.164-5.456-.016.015zm.749-.134c-.135 0-.255.119-.255.254l-.15 5.322.15 2.473c0 .15.12.255.255.255s.255-.12.255-.27l.15-2.474-.165-5.307c0-.148-.12-.27-.255-.27m1.005.166c-.164 0-.284.135-.284.285l-.103 5.143.135 2.474c0 .149.119.277.284.277.149 0 .271-.12.284-.285l.121-2.443-.135-5.112c-.012-.164-.135-.285-.285-.285m1.184-.945c-.165 0-.301.135-.313.3l-.106 6.094.119 2.441c.015.164.15.301.313.301s.299-.135.314-.301l.136-2.458-.136-6.093c-.015-.18-.149-.3-.314-.3m1.006-.547c-.18 0-.314.149-.33.329l-.12 6.612.135 2.503c0 .164.15.314.33.314.165 0 .313-.149.313-.33l.15-2.503-.164-6.597c-.016-.179-.164-.329-.329-.329m1.021-.133c-.18 0-.33.164-.345.343l-.119 6.69.135 2.474c.015.18.164.328.344.328.164 0 .33-.149.33-.328l.149-2.474-.148-6.69c0-.18-.15-.344-.33-.344m1.185-.104c-.195 0-.344.149-.359.358l-.12 6.75.119 2.473c.016.195.165.359.361.359s.344-.164.359-.359l.135-2.473-.136-6.75c-.014-.21-.164-.359-.359-.359m1.171.015c-.21 0-.36.149-.375.359l-.105 6.69.12 2.458c.015.195.165.36.375.36.195 0 .359-.165.375-.36l.12-2.458-.135-6.69c-.015-.21-.165-.375-.375-.375m1.215-.794c-.045-.012-.09-.012-.135-.012-.164 0-.314.045-.446.119-.015-.075-.015-.135-.015-.209 0-1.666-1.352-3.012-3.018-3.012-.811 0-1.531.375-2.04 1.006-.254-.119-.57-.18-.9-.18-1.381 0-2.504 1.125-2.504 2.504 0 .136.016.255.031.374h-.021c-1.291 0-2.339 1.05-2.339 2.34 0 1.291 1.05 2.34 2.339 2.34h8.939c.949 0 1.726-.778 1.726-1.726 0-.949-.777-1.725-1.726-1.725"/></svg>',
        'behance' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.938 4.503c.702 0 1.34.06 1.92.188.577.13 1.07.33 1.485.59.414.26.733.595.96 1.006.224.41.336.908.336 1.49 0 .652-.15 1.2-.453 1.645-.3.443-.757.815-1.365 1.12.826.27 1.444.72 1.854 1.35.41.63.617 1.388.617 2.275 0 .654-.136 1.234-.408 1.737-.27.504-.636.927-1.097 1.272-.46.346-1 .607-1.618.783-.616.177-1.265.265-1.95.265H0v-13.72h6.938zm-.34 5.6c.612 0 1.102-.145 1.47-.44.367-.293.55-.71.55-1.25 0-.318-.06-.583-.174-.793-.118-.21-.28-.38-.487-.51-.21-.127-.455-.217-.74-.275-.29-.058-.596-.086-.92-.086H3.12v3.355h3.48zm.2 5.87c.354 0 .686-.04.994-.12.308-.08.576-.204.804-.373.228-.168.408-.387.54-.656.13-.27.194-.592.194-.97 0-.773-.242-1.344-.726-1.712-.486-.37-1.13-.553-1.934-.553H3.12v4.386h3.68zM21.06 13.47c0 .505-.083.97-.252 1.392-.168.423-.404.79-.71 1.103-.302.313-.66.562-1.076.746-.414.185-.865.278-1.352.278-.54 0-1.04-.107-1.5-.323-.458-.217-.854-.51-1.184-.88-.333-.37-.595-.805-.79-1.3-.195-.497-.293-1.023-.293-1.58 0-.557.09-1.075.278-1.55.19-.475.454-.886.793-1.235.34-.35.74-.623 1.206-.82.465-.198.974-.296 1.528-.296.44 0 .87.08 1.285.236.415.16.78.384 1.093.673.313.29.562.638.75 1.042.187.404.295.852.322 1.344h-5.094c.022.32.09.61.205.873.118.262.27.486.46.67.19.187.41.33.666.432.256.1.532.15.828.15.503 0 .92-.102 1.253-.307.334-.206.577-.48.73-.82h2.05c-.11.343-.266.662-.466.96-.2.3-.438.556-.713.77-.275.214-.584.382-.927.502-.343.12-.713.18-1.108.18-.52 0-1.004-.095-1.456-.283-.45-.19-.845-.458-1.183-.803-.337-.346-.603-.76-.796-1.24-.195-.48-.292-.998-.292-1.555 0-.56.104-1.08.31-1.555.207-.476.487-.887.838-1.235.353-.346.765-.62 1.237-.818.47-.198.972-.296 1.504-.296.455 0 .894.082 1.32.246.425.163.8.396 1.123.698.32.302.58.667.77 1.094.19.428.31.897.363 1.41h-5.092c0-.22.044-.423.132-.61.088-.19.214-.356.376-.5.163-.144.354-.258.573-.342.218-.086.455-.13.71-.13.27 0 .518.046.746.138.228.09.424.217.588.377.163.16.293.353.39.578.095.225.15.472.164.74h-3.188v-.005zM15.21 7.23h5.88v1.36h-5.88z"/></svg>',
    ];
    
    // Shape styles
    $shapeStyles = [
        'circle' => 'border-radius:50%',
        'rounded' => 'border-radius:8px',
        'square' => 'border-radius:0',
    ];
    
    // Alignment mapping
    $justifyMap = [
        'left' => 'flex-start',
        'center' => 'center',
        'right' => 'flex-end',
    ];
    
    $target = $urlNewWindow ? ' target="_blank" rel="noopener noreferrer"' : '';
    
    $html = '<ul class="tb-social-follow" id="' . esc($moduleId) . '" style="display:flex;justify-content:' . esc($justifyMap[$alignment] ?? 'flex-start') . ';gap:' . esc($gap) . ';list-style:none;margin:0;padding:0;flex-wrap:wrap">';
    
    foreach ($enabledNetworks as $network) {
        $networkName = $network['network'] ?? '';
        $url = $network['url'] ?? '#';
        $color = $iconColor ?: ($brandColors[$networkName] ?? 'inherit');
        $icon = $icons[$networkName] ?? '<span>' . esc(ucfirst($networkName)) . '</span>';
        $shape = $shapeStyles[$iconShape] ?? 'border-radius:50%';
        
        $bgColorStyle = '';
        $iconColorStyle = '';
        
        if ($bgStyle === 'filled') {
            $bgColorStyle = 'background:' . esc($color) . ';';
            $iconColorStyle = 'color:#fff;';
        } elseif ($bgStyle === 'outline') {
            $bgColorStyle = 'background:transparent;border:2px solid ' . esc($color) . ';';
            $iconColorStyle = 'color:' . esc($color) . ';';
        } else {
            $bgColorStyle = 'background:transparent;';
            $iconColorStyle = 'color:' . esc($color) . ';';
        }
        
        $html .= '<li class="tb-social-icon tb-social-' . esc($networkName) . '">';
        $html .= '<a href="' . esc($url) . '"' . $target . ' title="Follow on ' . esc(ucfirst($networkName)) . '" style="display:flex;align-items:center;justify-content:center;width:' . esc($iconSize) . ';height:' . esc($iconSize) . ';' . $bgColorStyle . $iconColorStyle . $shape . ';text-decoration:none;transition:opacity 0.3s,transform 0.3s" onmouseover="this.style.opacity=\'0.8\';this.style.transform=\'scale(1.1)\'" onmouseout="this.style.opacity=\'1\';this.style.transform=\'scale(1)\'">';
        $html .= '<span style="display:flex;width:60%;height:60%">' . $icon . '</span>';
        $html .= '</a>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';

    return $output . $html;
}

/**
 * Render the site footer template
 *
 * @param array $context Page context
 * @return string Rendered footer HTML
 */
function tb_render_footer(array $context = []): string
{
    return tb_render_template('footer', $context);
}

/**
 * Render a sidebar template
 *
 * @param array $context Page context
 * @return string Rendered sidebar HTML
 */
function tb_render_sidebar(array $context = []): string
{
    return tb_render_template('sidebar', $context);
}

/**
 * Render the 404 page template
 *
 * @param array $context Page context
 * @return string Rendered 404 HTML
 */
function tb_render_404(array $context = []): string
{
    return tb_render_template('404', $context);
}

/**
 * Render an archive template (for blog listing pages)
 *
 * @param array $context Page context including posts data
 * @return string Rendered archive HTML
 */
function tb_render_archive(array $context = []): string
{
    return tb_render_template('archive', $context);
}

/**
 * Render a single post template
 *
 * @param array $context Page context including post data
 * @return string Rendered single post HTML
 */
function tb_render_single(array $context = []): string
{
    return tb_render_template('single', $context);
}

/**
 * Check if a template type has an active template
 *
 * @param string $type Template type
 * @return bool True if an active template exists
 */
function tb_has_template(string $type): bool
{
    try {
        $db = \core\Database::connection();

        $stmt = $db->prepare("
            SELECT COUNT(*) FROM tb_site_templates
            WHERE type = ? AND is_active = 1
        ");
        $stmt->execute([$type]);

        return (int)$stmt->fetchColumn() > 0;
    } catch (\Throwable $e) {
        return false;
    }
}

/**
 * Get CSS for all template types (for including in <head>)
 *
 * @return string Combined CSS for theme builder templates
 */
function tb_get_template_styles(): string
{
    return '
    <style>
    /* Theme Builder Template Styles */
    .tb-template { width: 100%; }
    .tb-template-header { position: relative; z-index: 100; }
    .tb-template-footer { position: relative; z-index: 50; }
    .tb-section { position: relative; }
    .tb-section-inner { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
    .tb-row { display: flex; flex-wrap: wrap; margin: 0 -10px; }
    .tb-column { padding: 10px; box-sizing: border-box; flex-shrink: 0; }
    .tb-module { margin-bottom: 20px; }

    /* Responsive */
    @media (max-width: 768px) {
        .tb-column { width: 100% !important; }
        .tb-section-inner { padding: 0 15px; }
    }
    </style>
    ';
}
// =============================================
// RENDERER: logo
// =============================================
function tb_render_module_logo(array $module, array $options = []): string
{
    $moduleId = $module['id'] ?? 'logo-' . uniqid();

    // Generate element CSS for inner elements
    $elementCss = tb_generate_module_element_css($module);
    $output = $elementCss ? '<style>' . $elementCss . '</style>' : '';

    $content = $module['content'] ?? [];
    $design = $module['design'] ?? [];
    $advanced = $module['advanced'] ?? [];
    $settings = $module['settings'] ?? []; // AI TB 4.0 uses settings
    
    // Merge settings into content for AI TB 4.0 compatibility
    if (!empty($settings)) {
        $content = array_merge($settings, $content);
        if (isset($settings['design'])) {
            $design = array_merge($settings['design'], $design);
        }
    }
    
    $image = $content['image'] ?? '';
    $alt = esc($content['image_alt'] ?? 'Site Logo');
    $linkUrl = esc($content['link_url'] ?? '/');
    $linkTarget = esc($content['link_target'] ?? '_self');
    $maxHeight = esc($content['max_height'] ?? '60px');
    $useSiteLogo = $content['use_site_logo'] ?? true;
    
    // Get text from content/settings first (AI TB 4.0)
    $logoText = $content['text'] ?? $content['site_name'] ?? null;
    
    $alignment = esc($design['alignment'] ?? 'left');
    $padding = esc($design['padding'] ?? '10px');
    $hoverOpacity = esc($design['hover_opacity'] ?? '0.8');
    
    $cssClass = esc($advanced['css_class'] ?? '');
    $cssId = esc($advanced['css_id'] ?? '');
    $ariaLabel = esc($advanced['aria_label'] ?? 'Site Logo');
    
    // If no image set and use_site_logo is true, try to get from site settings
    if (empty($image) && $useSiteLogo) {
        try {
            $pdo = \core\Database::connection();
            // Try to get logo from theme settings or site settings
            $stmt = $pdo->query("SELECT value FROM site_settings WHERE `key` = 'site_logo' LIMIT 1");
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row && !empty($row['value'])) {
                $image = $row['value'];
            }
        } catch (\Exception $e) {
            // Silently fail
        }
    }
    
    // Get site name for text fallback (only if not provided in content)
    $siteName = $logoText ?? 'Site';
    if (!$logoText) {
        try {
            $pdo = \core\Database::connection();
            $stmt = $pdo->query("SELECT value FROM site_settings WHERE `key` = 'site_name' LIMIT 1");
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row && !empty($row['value'])) {
                $siteName = $row['value'];
            }
        } catch (\Exception $e) {
            // Use default
        }
    }
    
    $alignCss = match($alignment) {
        'center' => 'text-align:center;',
        'right' => 'text-align:right;',
        default => 'text-align:left;',
    };
    
    $classAttr = "tb-logo" . ($cssClass ? " {$cssClass}" : '');

    $html = '<div id="' . esc($moduleId) . '" class="' . $classAttr . '" style="' . $alignCss . 'padding:' . $padding . '">';
    
    // Check if image is valid URL
    $hasValidImage = $image && (str_starts_with($image, "http://") || str_starts_with($image, "https://") || str_starts_with($image, "/") || str_starts_with($image, "data:image/"));

    if ($hasValidImage) {
        $html .= "<a href=\"{$linkUrl}\" target=\"{$linkTarget}\" aria-label=\"{$ariaLabel}\" style=\"display:inline-block;transition:opacity 0.3s\" onmouseover=\"this.style.opacity={$hoverOpacity}\" onmouseout=\"this.style.opacity=1\">";
        $html .= "<img src=\"" . esc($image) . "\" alt=\"{$alt}\" style=\"max-height:{$maxHeight};width:auto;display:block\">";
        $html .= "</a>";
    } else {
        // Text logo fallback - show site name
        $html .= "<a href=\"{$linkUrl}\" target=\"{$linkTarget}\" style=\"display:inline-flex;align-items:center;font-size:1.5rem;font-weight:700;color:var(--text, #ffffff);text-decoration:none;transition:opacity 0.3s\" onmouseover=\"this.style.opacity={$hoverOpacity}\" onmouseout=\"this.style.opacity=1\">";
        $html .= esc($siteName);
        $html .= "</a>";
    }
    
    $html .= "</div>";

    return $output . $html;
}

// ═══════════════════════════════════════════════════════════════════════════
// HOVER CSS GENERATOR
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Generate CSS for hover effects on all modules
 * 
 * @param array $content Page content with sections
 * @return string CSS string
 */
function tb_generate_hover_css(array $content): string
{
    $css = '';
    $sections = $content['sections'] ?? [];
    
    foreach ($sections as $sIdx => $section) {
        $rows = $section['rows'] ?? [];
        foreach ($rows as $rIdx => $row) {
            $columns = $row['columns'] ?? [];
            foreach ($columns as $cIdx => $column) {
                $modules = $column['modules'] ?? [];
                foreach ($modules as $mIdx => $module) {
                    $moduleCss = tb_generate_module_hover_css($module, $sIdx, $rIdx, $cIdx, $mIdx);
                    if ($moduleCss) {
                        $css .= $moduleCss;
                    }
                }
            }
        }
    }
    
    return $css;
}

/**
 * Generate hover CSS for a single module
 *
 * @param array $module Module data
 * @param int $sIdx Section index
 * @param int $rIdx Row index
 * @param int $cIdx Column index
 * @param int $mIdx Module index
 * @return string CSS string
 */
function tb_generate_module_hover_css(array $module, int $sIdx, int $rIdx, int $cIdx, int $mIdx): string
{
    // ═══════════════════════════════════════════════════════════════════════════
    // FIX: Merge BOTH settings AND design for hover properties
    // Sidebar saves to mod.design, modal saves to mod.settings
    // We need to check both locations for hover properties
    // ═══════════════════════════════════════════════════════════════════════════
    $rawSettings = $module['settings'] ?? [];
    $rawDesign = $module['design'] ?? [];

    // Merge design into settings (design takes precedence as it's where sidebar saves)
    $merged = array_merge($rawSettings, $rawDesign);

    // Normalize keys to snake_case
    $settings = tb_normalize_design_keys($merged);

    // Check if hover is enabled (check both camelCase and snake_case versions)
    if (empty($settings['hover_enabled'])) {
        return '';
    }

    $selector = ".tb-module[data-module-path=\"{$sIdx}-{$rIdx}-{$cIdx}-{$mIdx}\"]";
    $duration = $settings['hover_transition_duration'] ?? '0.3';
    $easing = $settings['hover_transition_easing'] ?? 'ease';
    
    $css = '';
    
    // Base transition
    $css .= "{$selector} { transition: all {$duration}s {$easing}; }\n";
    
    // Hover state
    $hoverStyles = [];
    
    if (!empty($settings['background_color_hover'])) {
        $hoverStyles[] = 'background-color:' . esc($settings['background_color_hover']) . '!important';
    }
    if (!empty($settings['text_color_hover'])) {
        $hoverStyles[] = 'color:' . esc($settings['text_color_hover']) . '!important';
    }
    if (!empty($settings['border_color_hover'])) {
        $hoverStyles[] = 'border-color:' . esc($settings['border_color_hover']) . '!important';
    }
    if (!empty($settings['opacity_hover']) && $settings['opacity_hover'] !== '1') {
        $hoverStyles[] = 'opacity:' . esc($settings['opacity_hover']) . '!important';
    }
    
    // Transform
    $transforms = [];
    if (!empty($settings['transform_scale_x_hover']) && $settings['transform_scale_x_hover'] !== '100') {
        $scale = floatval($settings['transform_scale_x_hover']) / 100;
        $transforms[] = "scaleX({$scale})";
    }
    if (!empty($settings['transform_scale_y_hover']) && $settings['transform_scale_y_hover'] !== '100') {
        $scale = floatval($settings['transform_scale_y_hover']) / 100;
        $transforms[] = "scaleY({$scale})";
    }
    if (!empty($settings['transform_translate_y_hover']) && $settings['transform_translate_y_hover'] !== '0') {
        $translateY = intval($settings['transform_translate_y_hover']);
        $transforms[] = "translateY({$translateY}px)";
    }
    if (!empty($transforms)) {
        $hoverStyles[] = 'transform:' . implode(' ', $transforms) . '!important';
    }
    
    // Box shadow
    if (!empty($settings['box_shadow_hover_enabled'])) {
        $shV = $settings['box_shadow_hover_vertical'] ?? '8';
        $shBlur = $settings['box_shadow_hover_blur'] ?? '20';
        $shColor = $settings['box_shadow_hover_color'] ?? 'rgba(0,0,0,0.2)';
        $hoverStyles[] = "box-shadow:0 {$shV}px {$shBlur}px " . esc($shColor) . '!important';
    }
    
    if (!empty($hoverStyles)) {
        $css .= "{$selector}:hover { " . implode(';', $hoverStyles) . "; }\n";
    }
    
    return $css;
}
