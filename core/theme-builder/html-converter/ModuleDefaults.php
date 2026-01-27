<?php
declare(strict_types=1);
/**
 * Module Defaults - Complete module structure templates
 * 
 * Provides default values for all TB 3.0 modules to ensure
 * generated content has complete, valid structure.
 *
 * @package ThemeBuilder
 * @subpackage HtmlConverter
 * @version 4.0.4
 */

namespace Core\ThemeBuilder\HtmlConverter;

class ModuleDefaults
{
    /**
     * Get complete defaults for a module type
     */
    public static function getDefaults(string $type): array
    {
        $method = 'get' . str_replace('_', '', ucwords($type, '_')) . 'Defaults';
        if (method_exists(self::class, $method)) {
            return self::$method();
        }
        return self::getGenericDefaults();
    }
    
    /**
     * Merge extracted data with defaults
     */
    public static function mergeWithDefaults(string $type, array $extracted): array
    {
        $defaults = self::getDefaults($type);
        
        return [
            'type' => $type,
            'content' => array_merge($defaults['content'] ?? [], $extracted['content'] ?? []),
            'design' => array_merge($defaults['design'] ?? [], $extracted['design'] ?? []),
            'advanced' => array_merge($defaults['advanced'] ?? [], $extracted['advanced'] ?? [])
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // CONTENT MODULES
    // ═══════════════════════════════════════════════════════════════
    
    private static function getTextDefaults(): array
    {
        return [
            'content' => ['text' => ''],
            'design' => [
                'font_size' => '16px',
                'line_height' => '1.6',
                'text_color' => '#333333',
                'text_align' => 'left',
                'font_weight' => '400',
                'font_family' => 'inherit'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getImageDefaults(): array
    {
        return [
            'content' => ['src' => '', 'alt' => '', 'title' => '', 'link' => ''],
            'design' => [
                'width' => '100%',
                'height' => 'auto',
                'max_width' => '100%',
                'border_radius' => '0',
                'object_fit' => 'cover',
                'box_shadow' => 'none'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '', 'lazy_load' => true]
        ];
    }
    
    private static function getButtonDefaults(): array
    {
        return [
            'content' => ['text' => 'Click Here', 'url' => '#', 'target' => '_self', 'icon' => ''],
            'design' => [
                'background_color' => '#0073e6',
                'text_color' => '#ffffff',
                'border_radius' => '4px',
                'padding' => '12px 24px',
                'font_size' => '16px',
                'font_weight' => '600',
                'border' => 'none',
                'hover_background' => '#005bb5',
                'hover_text_color' => '#ffffff'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getHeadingDefaults(): array
    {
        return [
            'content' => ['text' => '', 'level' => 'h2'],
            'design' => [
                'font_size' => '32px',
                'font_weight' => '700',
                'text_color' => '#222222',
                'text_align' => 'left',
                'line_height' => '1.3',
                'letter_spacing' => '0',
                'text_transform' => 'none',
                'margin_bottom' => '20px'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getDividerDefaults(): array
    {
        return [
            'content' => ['show_divider' => true],
            'design' => [
                'color' => '#dddddd',
                'style' => 'solid',
                'weight' => '1px',
                'width' => '100%',
                'margin_top' => '20px',
                'margin_bottom' => '20px'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getSpacerDefaults(): array
    {
        return [
            'content' => [],
            'design' => [
                'height' => '40px',
                'height_tablet' => '30px',
                'height_mobile' => '20px'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // MEDIA MODULES
    // ═══════════════════════════════════════════════════════════════
    
    private static function getVideoDefaults(): array
    {
        return [
            'content' => [
                'url' => '',
                'source' => 'url', // url, youtube, vimeo
                'autoplay' => false,
                'loop' => false,
                'muted' => false,
                'controls' => true,
                'poster' => ''
            ],
            'design' => [
                'aspect_ratio' => '16:9',
                'width' => '100%',
                'border_radius' => '0'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getAudioDefaults(): array
    {
        return [
            'content' => [
                'url' => '',
                'autoplay' => false,
                'loop' => false,
                'controls' => true
            ],
            'design' => ['width' => '100%'],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getGalleryDefaults(): array
    {
        return [
            'content' => [
                'images' => [], // [{src, alt, title, link}]
                'lightbox' => true
            ],
            'design' => [
                'columns' => 3,
                'columns_tablet' => 2,
                'columns_mobile' => 1,
                'gap' => '15px',
                'border_radius' => '4px',
                'hover_effect' => 'zoom'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getSliderDefaults(): array
    {
        return [
            'content' => [
                'slides' => [], // [{image, title, text, button_text, button_url}]
                'autoplay' => true,
                'loop' => true,
                'speed' => 5000
            ],
            'design' => [
                'height' => '500px',
                'navigation' => true,
                'pagination' => true,
                'pagination_style' => 'dots',
                'transition' => 'slide'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // INTERACTIVE MODULES
    // ═══════════════════════════════════════════════════════════════
    
    private static function getAccordionDefaults(): array
    {
        return [
            'content' => [
                'items' => [], // [{title, content, open}]
                'multiple_open' => false
            ],
            'design' => [
                'style' => 'default',
                'icon_position' => 'right',
                'border_color' => '#e0e0e0',
                'active_color' => '#0073e6',
                'header_background' => '#f8f9fa',
                'content_background' => '#ffffff'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getToggleDefaults(): array
    {
        return [
            'content' => ['title' => '', 'content' => '', 'open' => false],
            'design' => [
                'style' => 'default',
                'icon_position' => 'right',
                'border_color' => '#e0e0e0'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getTabsDefaults(): array
    {
        return [
            'content' => [
                'tabs' => [], // [{title, content, icon}]
                'default_tab' => 0
            ],
            'design' => [
                'style' => 'default',
                'alignment' => 'left',
                'active_color' => '#0073e6',
                'inactive_color' => '#666666',
                'border_color' => '#e0e0e0'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getFormDefaults(): array
    {
        return [
            'content' => [
                'title' => '',
                'fields' => [], // [{type, name, label, placeholder, required, options}]
                'submit_text' => 'Submit',
                'action' => '',
                'method' => 'POST',
                'success_message' => 'Thank you for your submission!',
                'error_message' => 'Please check your input and try again.'
            ],
            'design' => [
                'style' => 'default',
                'label_position' => 'top',
                'field_background' => '#ffffff',
                'field_border' => '1px solid #ddd',
                'field_border_radius' => '4px',
                'button_style' => 'filled'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getSearchDefaults(): array
    {
        return [
            'content' => [
                'placeholder' => 'Search...',
                'action' => '/search',
                'button_text' => 'Search',
                'show_button' => true
            ],
            'design' => [
                'style' => 'default',
                'width' => '100%',
                'border_radius' => '4px'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // MARKETING MODULES
    // ═══════════════════════════════════════════════════════════════
    
    private static function getBlurbDefaults(): array
    {
        return [
            'content' => [
                'icon' => '',
                'image' => '',
                'heading' => '',
                'text' => '',
                'link' => '',
                'link_text' => ''
            ],
            'design' => [
                'icon_color' => '#0073e6',
                'icon_size' => '48px',
                'icon_background' => 'transparent',
                'icon_placement' => 'top', // top, left, right
                'text_align' => 'center',
                'heading_size' => '20px',
                'content_padding' => '20px'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getTestimonialDefaults(): array
    {
        return [
            'content' => [
                'quote' => '',
                'name' => '',
                'role' => '',
                'company' => '',
                'image' => '',
                'rating' => 5
            ],
            'design' => [
                'style' => 'default', // default, card, minimal, boxed
                'quote_icon' => true,
                'quote_color' => '#0073e6',
                'text_align' => 'center',
                'image_size' => '80px',
                'image_border_radius' => '50%'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getPricingDefaults(): array
    {
        return [
            'content' => [
                'title' => '',
                'subtitle' => '',
                'price' => '',
                'currency' => '$',
                'period' => '/month',
                'features' => [], // [{text, included}]
                'button_text' => 'Get Started',
                'button_url' => '#',
                'featured' => false,
                'badge' => ''
            ],
            'design' => [
                'style' => 'default',
                'background_color' => '#ffffff',
                'featured_background' => '#f0f4ff',
                'border_color' => '#e0e0e0',
                'border_radius' => '8px',
                'price_color' => '#0073e6',
                'price_size' => '48px',
                'header_background' => '#f8f9fa'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getCtaDefaults(): array
    {
        return [
            'content' => [
                'heading' => '',
                'subheading' => '',
                'text' => '',
                'button_text' => 'Get Started',
                'button_url' => '#',
                'secondary_button_text' => '',
                'secondary_button_url' => ''
            ],
            'design' => [
                'style' => 'default',
                'background_color' => '#0073e6',
                'text_color' => '#ffffff',
                'text_align' => 'center',
                'padding' => '60px 40px',
                'border_radius' => '0'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getHeroDefaults(): array
    {
        return [
            'content' => [
                'heading' => '',
                'subheading' => '',
                'text' => '',
                'button_text' => '',
                'button_url' => '#',
                'secondary_button_text' => '',
                'secondary_button_url' => '',
                'background_image' => '',
                'video_url' => ''
            ],
            'design' => [
                'height' => '600px',
                'min_height' => '400px',
                'background_color' => '#1a1a2e',
                'overlay_color' => 'rgba(0,0,0,0.5)',
                'text_color' => '#ffffff',
                'text_align' => 'center',
                'vertical_align' => 'center',
                'content_width' => '800px',
                'parallax' => false
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getCounterDefaults(): array
    {
        return [
            'content' => [
                'number' => '0',
                'prefix' => '',
                'suffix' => '',
                'label' => '',
                'start_value' => 0
            ],
            'design' => [
                'number_size' => '48px',
                'number_color' => '#0073e6',
                'number_weight' => '700',
                'label_size' => '16px',
                'label_color' => '#666666',
                'animation_duration' => 2000,
                'text_align' => 'center'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getCountdownDefaults(): array
    {
        return [
            'content' => [
                'target_date' => '',
                'title' => '',
                'expired_message' => 'Event has ended',
                'show_days' => true,
                'show_hours' => true,
                'show_minutes' => true,
                'show_seconds' => true
            ],
            'design' => [
                'style' => 'default',
                'number_size' => '48px',
                'label_size' => '14px',
                'separator' => ':',
                'background_color' => 'transparent',
                'number_color' => '#222222',
                'label_color' => '#666666'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // NAVIGATION MODULES
    // ═══════════════════════════════════════════════════════════════
    
    private static function getMenuDefaults(): array
    {
        return [
            'content' => [
                'items' => [], // [{text, url, target, children}]
                'title' => ''
            ],
            'design' => [
                'orientation' => 'horizontal', // horizontal, vertical
                'alignment' => 'left',
                'item_spacing' => '20px',
                'link_color' => '#333333',
                'link_hover_color' => '#0073e6',
                'font_size' => '16px',
                'font_weight' => '500',
                'dropdown_background' => '#ffffff',
                'dropdown_shadow' => '0 4px 12px rgba(0,0,0,0.1)'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getLogoDefaults(): array
    {
        return [
            'content' => [
                'image' => '',
                'text' => '',
                'url' => '/',
                'alt' => ''
            ],
            'design' => [
                'max_height' => '60px',
                'max_width' => '200px'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getSocialDefaults(): array
    {
        return [
            'content' => [
                'links' => [], // [{platform, url}] or {facebook: url, twitter: url, ...}
                'show_labels' => false
            ],
            'design' => [
                'style' => 'icons', // icons, buttons, outline
                'icon_size' => '24px',
                'icon_color' => '#333333',
                'icon_hover_color' => '#0073e6',
                'spacing' => '15px',
                'alignment' => 'left'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // LAYOUT MODULES
    // ═══════════════════════════════════════════════════════════════
    
    private static function getSidebarDefaults(): array
    {
        return [
            'content' => [
                'widgets' => [] // [{title, type, content}]
            ],
            'design' => [
                'width' => '300px',
                'background' => '#f8f9fa',
                'padding' => '20px',
                'widget_spacing' => '30px'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getMapDefaults(): array
    {
        return [
            'content' => [
                'address' => '',
                'lat' => '',
                'lng' => '',
                'zoom' => 14,
                'embed_url' => '',
                'marker_title' => ''
            ],
            'design' => [
                'height' => '400px',
                'border_radius' => '0',
                'grayscale' => false
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // CONTENT DISPLAY MODULES
    // ═══════════════════════════════════════════════════════════════
    
    private static function getBlogDefaults(): array
    {
        return [
            'content' => [
                'posts' => [], // [{title, excerpt, image, link, date, author, category}]
                'posts_count' => 6,
                'category' => '',
                'show_excerpt' => true,
                'excerpt_length' => 150,
                'show_date' => true,
                'show_author' => true,
                'show_image' => true,
                'show_category' => true
            ],
            'design' => [
                'columns' => 3,
                'columns_tablet' => 2,
                'columns_mobile' => 1,
                'gap' => '30px',
                'card_style' => 'default',
                'image_ratio' => '16:9',
                'border_radius' => '8px'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getTeamDefaults(): array
    {
        return [
            'content' => [
                'members' => [], // [{name, role, bio, image, social}]
                'columns' => 4
            ],
            'design' => [
                'style' => 'default',
                'columns_tablet' => 2,
                'columns_mobile' => 1,
                'gap' => '30px',
                'image_ratio' => '1:1',
                'image_border_radius' => '8px',
                'text_align' => 'center',
                'show_social' => true
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getPortfolioDefaults(): array
    {
        return [
            'content' => [
                'items' => [], // [{image, title, category, link, description}]
                'filter' => true,
                'categories' => []
            ],
            'design' => [
                'columns' => 3,
                'columns_tablet' => 2,
                'columns_mobile' => 1,
                'gap' => '20px',
                'style' => 'grid', // grid, masonry
                'hover_effect' => 'overlay',
                'border_radius' => '8px'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // ADVANCED MODULES
    // ═══════════════════════════════════════════════════════════════
    
    private static function getBarCountersDefaults(): array
    {
        return [
            'content' => [
                'bars' => [] // [{label, value, color}]
            ],
            'design' => [
                'bar_height' => '20px',
                'bar_color' => '#0073e6',
                'bar_background' => '#e0e0e0',
                'border_radius' => '10px',
                'label_position' => 'above', // above, inside
                'show_percentage' => true,
                'animation' => true
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getCircleCounterDefaults(): array
    {
        return [
            'content' => [
                'value' => 0,
                'max_value' => 100,
                'label' => '',
                'suffix' => '%'
            ],
            'design' => [
                'size' => '150px',
                'stroke_width' => '10px',
                'stroke_color' => '#0073e6',
                'background_color' => '#e0e0e0',
                'text_color' => '#222222',
                'text_size' => '32px',
                'animation' => true
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getVideoSliderDefaults(): array
    {
        return [
            'content' => [
                'videos' => [], // [{url, title, thumbnail}]
                'autoplay' => false,
                'loop' => true
            ],
            'design' => [
                'navigation' => true,
                'pagination' => true,
                'height' => '500px'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // AUTH MODULES
    // ═══════════════════════════════════════════════════════════════
    
    private static function getLoginDefaults(): array
    {
        return [
            'content' => [
                'title' => 'Login',
                'action' => '/login',
                'submit_text' => 'Sign In',
                'show_remember' => true,
                'show_forgot' => true,
                'forgot_url' => '/forgot-password',
                'register_url' => '/register',
                'register_text' => "Don't have an account? Sign up"
            ],
            'design' => [
                'style' => 'default',
                'width' => '400px',
                'background' => '#ffffff',
                'border_radius' => '8px',
                'box_shadow' => '0 4px 20px rgba(0,0,0,0.1)'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getSignupDefaults(): array
    {
        return [
            'content' => [
                'title' => 'Create Account',
                'action' => '/register',
                'fields' => [],
                'submit_text' => 'Sign Up',
                'login_url' => '/login',
                'login_text' => 'Already have an account? Sign in',
                'terms_text' => '',
                'terms_url' => ''
            ],
            'design' => [
                'style' => 'default',
                'width' => '400px',
                'background' => '#ffffff',
                'border_radius' => '8px'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // POST/DYNAMIC MODULES
    // ═══════════════════════════════════════════════════════════════
    
    private static function getPostTitleDefaults(): array
    {
        return [
            'content' => ['title' => ''],
            'design' => [
                'font_size' => '42px',
                'font_weight' => '700',
                'text_color' => '#222222',
                'text_align' => 'left',
                'margin_bottom' => '20px'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getPostContentDefaults(): array
    {
        return [
            'content' => ['content' => ''],
            'design' => [
                'font_size' => '18px',
                'line_height' => '1.8',
                'text_color' => '#333333'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getPostsNavigationDefaults(): array
    {
        return [
            'content' => [
                'prev_text' => '← Previous',
                'next_text' => 'Next →',
                'prev_url' => '',
                'next_url' => ''
            ],
            'design' => [
                'style' => 'default',
                'show_thumbnails' => false
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getCommentsDefaults(): array
    {
        return [
            'content' => [
                'comments' => [],
                'show_form' => true,
                'require_login' => false
            ],
            'design' => [
                'style' => 'default',
                'avatar_size' => '50px'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getPostSliderDefaults(): array
    {
        return [
            'content' => [
                'posts' => [],
                'posts_count' => 5,
                'category' => '',
                'autoplay' => true
            ],
            'design' => [
                'style' => 'default',
                'show_excerpt' => true,
                'show_date' => true,
                'height' => '400px'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // UTILITY
    // ═══════════════════════════════════════════════════════════════
    
    private static function getCodeDefaults(): array
    {
        return [
            'content' => ['code' => '', 'language' => ''],
            'design' => [
                'theme' => 'dark',
                'line_numbers' => true,
                'font_size' => '14px'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getQuoteDefaults(): array
    {
        return [
            'content' => ['text' => '', 'author' => '', 'source' => ''],
            'design' => [
                'style' => 'default',
                'border_color' => '#0073e6',
                'font_size' => '20px',
                'font_style' => 'italic'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getListDefaults(): array
    {
        return [
            'content' => [
                'items' => [],
                'ordered' => false
            ],
            'design' => [
                'icon' => '',
                'icon_color' => '#0073e6',
                'item_spacing' => '10px'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    private static function getIconDefaults(): array
    {
        return [
            'content' => ['icon' => '', 'link' => ''],
            'design' => [
                'size' => '48px',
                'color' => '#0073e6',
                'background' => 'transparent',
                'border_radius' => '0',
                'padding' => '0'
            ],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // FULLWIDTH MODULES
    // ═══════════════════════════════════════════════════════════════
    
    private static function getFullwidthCodeDefaults(): array
    {
        return array_merge(self::getCodeDefaults(), ['fullwidth' => true]);
    }
    
    private static function getFullwidthImageDefaults(): array
    {
        return array_merge(self::getImageDefaults(), ['fullwidth' => true]);
    }
    
    private static function getFullwidthMapDefaults(): array
    {
        return array_merge(self::getMapDefaults(), ['fullwidth' => true]);
    }
    
    private static function getFullwidthMenuDefaults(): array
    {
        return array_merge(self::getMenuDefaults(), ['fullwidth' => true]);
    }
    
    private static function getFullwidthSliderDefaults(): array
    {
        return array_merge(self::getSliderDefaults(), ['fullwidth' => true]);
    }
    
    private static function getFullwidthHeaderDefaults(): array
    {
        return array_merge(self::getHeroDefaults(), ['fullwidth' => true]);
    }
    
    private static function getFullwidthPortfolioDefaults(): array
    {
        return array_merge(self::getPortfolioDefaults(), ['fullwidth' => true]);
    }
    
    private static function getFullwidthPostSliderDefaults(): array
    {
        return array_merge(self::getPostSliderDefaults(), ['fullwidth' => true]);
    }
    
    // ═══════════════════════════════════════════════════════════════
    // GENERIC FALLBACK
    // ═══════════════════════════════════════════════════════════════
    
    private static function getGenericDefaults(): array
    {
        return [
            'content' => [],
            'design' => [],
            'advanced' => ['css_class' => '', 'css_id' => '']
        ];
    }
}
