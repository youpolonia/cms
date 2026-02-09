<?php
/**
 * JTB Module Mapper
 * Maps HTML elements to JTB module types and extracts content-specific attributes
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Mapper
{
    /**
     * Complete mapping of all 68 JTB modules with their HTML patterns
     */
    private static array $moduleDefinitions = [
        // =============================================
        // STRUCTURE MODULES (3)
        // =============================================
        'section' => [
            'tags' => ['section', 'header', 'footer', 'main', 'article', 'aside'],
            'data_attr' => 'section',
            'classes' => ['section', 'jtb-section'],
            'category' => 'structure',
            'fields' => [
                'fullwidth' => ['type' => 'bool', 'attr' => 'data-jtb-attr-fullwidth'],
                'inner_shadow' => ['type' => 'bool', 'attr' => 'data-jtb-attr-inner-shadow'],
                'parallax' => ['type' => 'bool', 'attr' => 'data-jtb-attr-parallax'],
            ]
        ],
        'row' => [
            'tags' => ['div'],
            'data_attr' => 'row',
            'classes' => ['row', 'jtb-row'],
            'category' => 'structure',
            'detect' => 'flex_container',
            'fields' => [
                'column_structure' => ['type' => 'string', 'attr' => 'data-jtb-attr-columns'],
                'gutter_width' => ['type' => 'int', 'attr' => 'data-jtb-attr-gutter'],
                'equalheight' => ['type' => 'bool', 'attr' => 'data-jtb-attr-equalheight'],
            ]
        ],
        'column' => [
            'tags' => ['div'],
            'data_attr' => 'column',
            'classes' => ['column', 'col', 'jtb-column'],
            'category' => 'structure',
            'detect' => 'flex_item',
            'fields' => []
        ],

        // =============================================
        // CONTENT MODULES (24)
        // =============================================
        'heading' => [
            'tags' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
            'data_attr' => 'heading',
            'classes' => ['heading', 'title'],
            'category' => 'content',
            'fields' => [
                'text' => ['type' => 'text_content'],
                'level' => ['type' => 'heading_level'], // Special type that detects h1-h6
                'link_url' => ['type' => 'string', 'attr' => 'data-jtb-attr-link-url', 'selector' => 'a@href'],
                'link_target' => ['type' => 'bool', 'attr' => 'data-jtb-attr-link-target', 'selector' => 'a@target'],
            ]
        ],
        'text' => [
            'tags' => ['p', 'div', 'span', 'blockquote', 'ul', 'ol'],
            'data_attr' => 'text',
            'classes' => ['text', 'content', 'description'],
            'category' => 'content',
            'fields' => [
                'content' => ['type' => 'html_content'],
            ]
        ],
        'image' => [
            'tags' => ['img', 'figure', 'picture'],
            'data_attr' => 'image',
            'classes' => ['image', 'img', 'photo'],
            'category' => 'content',
            'fields' => [
                'src' => ['type' => 'string', 'attr' => 'src'],
                'alt' => ['type' => 'string', 'attr' => 'alt'],
                'title_text' => ['type' => 'string', 'attr' => 'title'],
                'link_url' => ['type' => 'string', 'attr' => 'data-jtb-attr-url', 'selector' => 'a@href'],
                'link_target' => ['type' => 'bool', 'selector' => 'a@target'],
                'show_in_lightbox' => ['type' => 'bool', 'attr' => 'data-jtb-attr-lightbox'],
            ]
        ],
        'button' => [
            'tags' => ['a', 'button'],
            'data_attr' => 'button',
            'classes' => ['btn', 'button', 'cta-button'],
            'category' => 'content',
            'fields' => [
                'text' => ['type' => 'text_content'],
                'link_url' => ['type' => 'string', 'attr' => 'href'],
                'link_target' => ['type' => 'bool', 'attr' => 'target'],
                'button_alignment' => ['type' => 'string', 'attr' => 'data-jtb-attr-alignment'],
                'custom_icon' => ['type' => 'string', 'attr' => 'data-jtb-attr-icon'],
                'icon_position' => ['type' => 'string', 'attr' => 'data-jtb-attr-icon-position'],
            ]
        ],
        'blurb' => [
            'tags' => ['div', 'article'],
            'data_attr' => 'blurb',
            'classes' => ['blurb', 'card', 'feature', 'feature-box', 'info-box'],
            'category' => 'content',
            'fields' => [
                'title' => ['type' => 'string', 'selector' => 'h1,h2,h3,h4,h5,h6'],
                'content' => ['type' => 'html', 'selector' => '.content,p,.description'],
                'image' => ['type' => 'string', 'selector' => 'img@src'],
                'alt' => ['type' => 'string', 'selector' => 'img@alt'],
                'use_icon' => ['type' => 'bool', 'attr' => 'data-jtb-attr-use-icon'],
                'font_icon' => ['type' => 'string', 'attr' => 'data-jtb-attr-icon'],
                'icon_color' => ['type' => 'color', 'attr' => 'data-jtb-attr-icon-color'],
                'use_circle' => ['type' => 'bool', 'attr' => 'data-jtb-attr-use-circle'],
                'image_placement' => ['type' => 'string', 'attr' => 'data-jtb-attr-placement'],
                'link_url' => ['type' => 'string', 'selector' => 'a@href'],
                'header_level' => ['type' => 'string', 'attr' => 'data-jtb-attr-header-level'],
                'text_orientation' => ['type' => 'string', 'attr' => 'data-jtb-attr-text-align'],
            ]
        ],
        'divider' => [
            'tags' => ['hr'],
            'data_attr' => 'divider',
            'classes' => ['divider', 'separator', 'hr'],
            'category' => 'content',
            'fields' => [
                'show_divider' => ['type' => 'bool', 'default' => true],
                'divider_style' => ['type' => 'string', 'attr' => 'data-jtb-attr-style'],
                'divider_weight' => ['type' => 'int', 'attr' => 'data-jtb-attr-weight'],
                'divider_color' => ['type' => 'color', 'attr' => 'data-jtb-attr-color'],
            ]
        ],
        'code' => [
            'tags' => ['pre', 'code', 'table'],
            'data_attr' => 'code',
            'classes' => ['code', 'code-block', 'syntax'],
            'category' => 'content',
            'fields' => [
                'raw_content' => ['type' => 'text_content'],
            ]
        ],
        'cta' => [
            'tags' => ['div', 'section'],
            'data_attr' => 'cta',
            'classes' => ['cta', 'call-to-action', 'cta-section', 'cta-box'],
            'category' => 'content',
            'fields' => [
                'title' => ['type' => 'string', 'selector' => 'h1,h2,h3,h4'],
                'content' => ['type' => 'html', 'selector' => 'p,.content'],
                'button_text' => ['type' => 'string', 'selector' => '.btn,button,a.button'],
                'link_url' => ['type' => 'string', 'selector' => '.btn@href,button@data-url,a.button@href'],
            ]
        ],
        'number_counter' => [
            'tags' => ['div', 'span'],
            'data_attr' => 'number_counter',
            'classes' => ['counter', 'number-counter', 'stat', 'statistic'],
            'category' => 'content',
            'fields' => [
                'title' => ['type' => 'string', 'selector' => 'h1,h2,h3,h4,h5,h6,.title'],
                'number' => ['type' => 'string', 'attr' => 'data-jtb-attr-number', 'selector' => '.number,.count'],
                'percent_sign' => ['type' => 'bool', 'attr' => 'data-jtb-attr-percent'],
            ]
        ],
        'circle_counter' => [
            'tags' => ['div'],
            'data_attr' => 'circle_counter',
            'classes' => ['circle-counter', 'progress-circle', 'radial-progress'],
            'category' => 'content',
            'fields' => [
                'title' => ['type' => 'string', 'selector' => 'h1,h2,h3,h4,h5,h6,.title'],
                'number' => ['type' => 'int', 'attr' => 'data-jtb-attr-number'],
                'bar_bg_color' => ['type' => 'color', 'attr' => 'data-jtb-attr-bar-bg'],
                'bar_color' => ['type' => 'color', 'attr' => 'data-jtb-attr-bar-color'],
            ]
        ],
        'bar_counter' => [
            'tags' => ['div'],
            'data_attr' => 'bar_counter',
            'classes' => ['bar-counter', 'progress-bar', 'progress'],
            'category' => 'content',
            'child_slug' => 'bar_counter_item',
            'fields' => [
                'layout' => ['type' => 'string', 'attr' => 'data-jtb-attr-layout'],
            ]
        ],
        'bar_counter_item' => [
            'tags' => ['div'],
            'data_attr' => 'bar_counter_item',
            'classes' => ['bar-item', 'progress-item'],
            'category' => 'content',
            'is_child' => true,
            'fields' => [
                'title' => ['type' => 'string', 'selector' => '.title,.label'],
                'percent' => ['type' => 'int', 'attr' => 'data-jtb-attr-percent'],
                'bar_color' => ['type' => 'color', 'attr' => 'data-jtb-attr-color'],
            ]
        ],
        'icon' => [
            'tags' => ['i', 'span', 'svg'],
            'data_attr' => 'icon',
            'classes' => ['icon', 'jtb-icon'],
            'category' => 'content',
            'fields' => [
                'font_icon' => ['type' => 'string', 'attr' => 'data-jtb-attr-icon', 'class_prefix' => 'icon-'],
                'icon_color' => ['type' => 'color', 'attr' => 'data-jtb-attr-color'],
                'use_circle' => ['type' => 'bool', 'attr' => 'data-jtb-attr-circle'],
                'circle_color' => ['type' => 'color', 'attr' => 'data-jtb-attr-circle-color'],
            ]
        ],
        'testimonial' => [
            'tags' => ['div', 'article', 'blockquote'],
            'data_attr' => 'testimonial',
            'classes' => ['testimonial', 'review', 'quote', 'customer-review'],
            'category' => 'content',
            'fields' => [
                'author' => ['type' => 'string', 'selector' => '.author,.name,cite'],
                'job_title' => ['type' => 'string', 'selector' => '.job,.position,.role'],
                'company' => ['type' => 'string', 'selector' => '.company,.organization'],
                'link_url' => ['type' => 'string', 'selector' => 'a@href'],
                'portrait_url' => ['type' => 'string', 'selector' => 'img@src,.avatar@src'],
                'content' => ['type' => 'html', 'selector' => '.content,.quote,p'],
                'quote_icon' => ['type' => 'string', 'attr' => 'data-jtb-attr-quote-icon'],
            ]
        ],
        'team_member' => [
            'tags' => ['div', 'article'],
            'data_attr' => 'team_member',
            'classes' => ['team-member', 'member', 'staff', 'person', 'profile'],
            'category' => 'content',
            'fields' => [
                'name' => ['type' => 'string', 'selector' => 'h1,h2,h3,h4,h5,h6,.name'],
                'position' => ['type' => 'string', 'selector' => '.position,.role,.job-title'],
                'image_url' => ['type' => 'string', 'selector' => 'img@src'],
                'content' => ['type' => 'html', 'selector' => '.bio,.content,p'],
                'facebook_url' => ['type' => 'string', 'attr' => 'data-jtb-attr-facebook'],
                'twitter_url' => ['type' => 'string', 'attr' => 'data-jtb-attr-twitter'],
                'linkedin_url' => ['type' => 'string', 'attr' => 'data-jtb-attr-linkedin'],
                'email' => ['type' => 'string', 'attr' => 'data-jtb-attr-email'],
            ]
        ],
        'pricing_table' => [
            'tags' => ['div', 'article'],
            'data_attr' => 'pricing_table',
            'classes' => ['pricing', 'pricing-table', 'pricing-card', 'price-box', 'plan'],
            'category' => 'content',
            'child_slug' => 'pricing_table_item',
            'fields' => [
                'title' => ['type' => 'string', 'selector' => 'h1,h2,h3,h4,.plan-name,.title'],
                'subtitle' => ['type' => 'string', 'selector' => '.subtitle,.tagline'],
                'currency' => ['type' => 'string', 'attr' => 'data-jtb-attr-currency'],
                'price' => ['type' => 'string', 'selector' => '.price,.amount'],
                'per' => ['type' => 'string', 'selector' => '.period,.per'],
                'content' => ['type' => 'html', 'selector' => 'ul,.features'],
                'button_text' => ['type' => 'string', 'selector' => '.btn,button,a.button'],
                'link_url' => ['type' => 'string', 'selector' => '.btn@href,a.button@href'],
                'featured' => ['type' => 'bool', 'attr' => 'data-jtb-attr-featured', 'class' => 'featured'],
                'featured_text' => ['type' => 'string', 'selector' => '.badge,.ribbon'],
            ]
        ],
        'pricing_table_item' => [
            'tags' => ['li', 'div'],
            'data_attr' => 'pricing_table_item',
            'classes' => ['pricing-feature', 'feature-item'],
            'category' => 'content',
            'is_child' => true,
            'fields' => [
                'feature' => ['type' => 'text_content'],
                'excluded' => ['type' => 'bool', 'class' => 'excluded'],
            ]
        ],
        'social_follow' => [
            'tags' => ['div', 'ul'],
            'data_attr' => 'social_follow',
            'classes' => ['social', 'social-icons', 'social-links', 'social-follow'],
            'category' => 'content',
            'child_slug' => 'social_follow_item',
            'fields' => [
                'follow_button' => ['type' => 'bool', 'attr' => 'data-jtb-attr-follow-button'],
            ]
        ],
        'social_follow_item' => [
            'tags' => ['a', 'li'],
            'data_attr' => 'social_follow_item',
            'classes' => ['social-item', 'social-link'],
            'category' => 'content',
            'is_child' => true,
            'fields' => [
                'social_network' => ['type' => 'string', 'attr' => 'data-jtb-attr-network', 'class_prefix' => 'social-'],
                'url' => ['type' => 'string', 'attr' => 'href'],
            ]
        ],
        'comments' => [
            'tags' => ['div', 'section'],
            'data_attr' => 'comments',
            'classes' => ['comments', 'comments-section', 'comment-list'],
            'category' => 'content',
            'fields' => [
                'show_avatar' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-avatar'],
                'show_reply' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-reply'],
            ]
        ],
        'countdown' => [
            'tags' => ['div'],
            'data_attr' => 'countdown',
            'classes' => ['countdown', 'timer', 'count-down'],
            'category' => 'content',
            'fields' => [
                'end_date' => ['type' => 'string', 'attr' => 'data-jtb-attr-end-date'],
                'show_days' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-days'],
                'show_hours' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-hours'],
                'show_minutes' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-minutes'],
                'show_seconds' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-seconds'],
            ]
        ],
        'sidebar' => [
            'tags' => ['aside', 'div'],
            'data_attr' => 'sidebar',
            'classes' => ['sidebar', 'widget-area'],
            'category' => 'content',
            'fields' => [
                'area' => ['type' => 'string', 'attr' => 'data-jtb-attr-area'],
            ]
        ],
        'post_navigation' => [
            'tags' => ['nav', 'div'],
            'data_attr' => 'post_navigation',
            'classes' => ['post-navigation', 'nav-links', 'post-nav'],
            'category' => 'content',
            'fields' => [
                'show_featured_image' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-image'],
                'show_title' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-title'],
            ]
        ],
        'shop' => [
            'tags' => ['div', 'section'],
            'data_attr' => 'shop',
            'classes' => ['shop', 'products', 'woocommerce', 'product-grid'],
            'category' => 'content',
            'fields' => [
                'posts_number' => ['type' => 'int', 'attr' => 'data-jtb-attr-posts-number'],
                'columns' => ['type' => 'int', 'attr' => 'data-jtb-attr-columns'],
            ]
        ],

        // =============================================
        // INTERACTIVE MODULES (5)
        // =============================================
        'accordion' => [
            'tags' => ['div'],
            'data_attr' => 'accordion',
            'classes' => ['accordion', 'faq', 'collapsible'],
            'category' => 'interactive',
            'child_slug' => 'accordion_item',
            'fields' => [
                'toggle_icon' => ['type' => 'string', 'attr' => 'data-jtb-attr-toggle-icon'],
                'toggle_icon_position' => ['type' => 'string', 'attr' => 'data-jtb-attr-icon-position'],
                'toggle_header_level' => ['type' => 'string', 'attr' => 'data-jtb-attr-header-level'],
            ]
        ],
        'accordion_item' => [
            'tags' => ['div'],
            'data_attr' => 'accordion_item',
            'classes' => ['accordion-item', 'faq-item', 'collapse-item'],
            'category' => 'interactive',
            'is_child' => true,
            'fields' => [
                'title' => ['type' => 'string', 'selector' => '.accordion-header,.faq-question,h1,h2,h3,h4,h5,h6'],
                'content' => ['type' => 'html', 'selector' => '.accordion-content,.faq-answer,.content'],
                'open' => ['type' => 'bool', 'class' => 'open', 'attr' => 'data-jtb-attr-open'],
            ]
        ],
        'tabs' => [
            'tags' => ['div'],
            'data_attr' => 'tabs',
            'classes' => ['tabs', 'tab-container', 'tabbed'],
            'category' => 'interactive',
            'child_slug' => 'tabs_item',
            'fields' => [
                'active_tab_idx' => ['type' => 'int', 'attr' => 'data-jtb-attr-active-tab'],
            ]
        ],
        'tabs_item' => [
            'tags' => ['div'],
            'data_attr' => 'tabs_item',
            'classes' => ['tab-item', 'tab-pane', 'tab-content'],
            'category' => 'interactive',
            'is_child' => true,
            'fields' => [
                'title' => ['type' => 'string', 'selector' => '.tab-title,h1,h2,h3,h4,h5,h6', 'attr' => 'data-jtb-attr-title'],
                'content' => ['type' => 'html', 'selector' => '.tab-content,.content'],
            ]
        ],
        'toggle' => [
            'tags' => ['div'],
            'data_attr' => 'toggle',
            'classes' => ['toggle', 'expandable'],
            'category' => 'interactive',
            'fields' => [
                'title' => ['type' => 'string', 'selector' => '.toggle-header,h1,h2,h3,h4,h5,h6'],
                'content' => ['type' => 'html', 'selector' => '.toggle-content,.content'],
                'open' => ['type' => 'bool', 'class' => 'open', 'attr' => 'data-jtb-attr-open'],
            ]
        ],

        // =============================================
        // MEDIA MODULES (9)
        // =============================================
        'audio' => [
            'tags' => ['audio', 'div'],
            'data_attr' => 'audio',
            'classes' => ['audio', 'audio-player', 'podcast'],
            'category' => 'media',
            'fields' => [
                'audio_url' => ['type' => 'string', 'attr' => 'src', 'selector' => 'source@src'],
            ]
        ],
        'video' => [
            'tags' => ['video', 'iframe', 'div'],
            'data_attr' => 'video',
            'classes' => ['video', 'video-player', 'embed-responsive'],
            'category' => 'media',
            'detect' => 'video_embed',
            'fields' => [
                'src' => ['type' => 'string', 'attr' => 'src'],
                'src_webm' => ['type' => 'string', 'attr' => 'data-jtb-attr-webm'],
            ]
        ],
        'gallery' => [
            'tags' => ['div', 'ul'],
            'data_attr' => 'gallery',
            'classes' => ['gallery', 'image-gallery', 'photo-gallery', 'grid-gallery'],
            'category' => 'media',
            'fields' => [
                'gallery_ids' => ['type' => 'array', 'selector' => 'img'],
                'columns' => ['type' => 'int', 'attr' => 'data-jtb-attr-columns'],
                'orientation' => ['type' => 'string', 'attr' => 'data-jtb-attr-orientation'],
                'show_title_and_caption' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-caption'],
            ]
        ],
        'slider' => [
            'tags' => ['div'],
            'data_attr' => 'slider',
            'classes' => ['slider', 'carousel', 'slideshow', 'swiper'],
            'category' => 'media',
            'child_slug' => 'slider_item',
            'fields' => [
                'show_arrows' => ['type' => 'bool', 'attr' => 'data-jtb-attr-arrows'],
                'show_dots' => ['type' => 'bool', 'attr' => 'data-jtb-attr-dots'],
                'auto' => ['type' => 'bool', 'attr' => 'data-jtb-attr-auto'],
                'auto_speed' => ['type' => 'int', 'attr' => 'data-jtb-attr-speed'],
                'loop' => ['type' => 'bool', 'attr' => 'data-jtb-attr-loop'],
                'slider_height' => ['type' => 'int', 'attr' => 'data-jtb-attr-height'],
            ]
        ],
        'slider_item' => [
            'tags' => ['div'],
            'data_attr' => 'slider_item',
            'classes' => ['slide', 'slider-item', 'carousel-item', 'swiper-slide'],
            'category' => 'media',
            'is_child' => true,
            'fields' => [
                'heading' => ['type' => 'string', 'selector' => 'h1,h2,h3,h4'],
                'content' => ['type' => 'html', 'selector' => '.content,p'],
                'image' => ['type' => 'string', 'selector' => 'img@src', 'attr' => 'data-jtb-attr-image'],
                'button_text' => ['type' => 'string', 'selector' => '.btn,button,a.button'],
                'link_url' => ['type' => 'string', 'selector' => '.btn@href,a.button@href'],
            ]
        ],
        'video_slider' => [
            'tags' => ['div'],
            'data_attr' => 'video_slider',
            'classes' => ['video-slider', 'video-carousel'],
            'category' => 'media',
            'child_slug' => 'video_slider_item',
            'fields' => [
                'show_arrows' => ['type' => 'bool', 'attr' => 'data-jtb-attr-arrows'],
                'show_dots' => ['type' => 'bool', 'attr' => 'data-jtb-attr-dots'],
            ]
        ],
        'video_slider_item' => [
            'tags' => ['div'],
            'data_attr' => 'video_slider_item',
            'classes' => ['video-slide'],
            'category' => 'media',
            'is_child' => true,
            'fields' => [
                'video_url' => ['type' => 'string', 'selector' => 'iframe@src,video@src'],
            ]
        ],
        'map' => [
            'tags' => ['div', 'iframe'],
            'data_attr' => 'map',
            'classes' => ['map', 'google-map', 'location-map'],
            'category' => 'media',
            'child_slug' => 'map_pin',
            'detect' => 'google_map',
            'fields' => [
                'address' => ['type' => 'string', 'attr' => 'data-jtb-attr-address'],
                'zoom' => ['type' => 'int', 'attr' => 'data-jtb-attr-zoom'],
                'map_height' => ['type' => 'int', 'attr' => 'data-jtb-attr-height'],
                'grayscale' => ['type' => 'bool', 'attr' => 'data-jtb-attr-grayscale'],
            ]
        ],
        'map_pin' => [
            'tags' => ['div'],
            'data_attr' => 'map_pin',
            'classes' => ['map-pin', 'marker'],
            'category' => 'media',
            'is_child' => true,
            'fields' => [
                'title' => ['type' => 'string', 'attr' => 'data-jtb-attr-title'],
                'pin_address' => ['type' => 'string', 'attr' => 'data-jtb-attr-address'],
                'pin_lat' => ['type' => 'float', 'attr' => 'data-jtb-attr-lat'],
                'pin_lng' => ['type' => 'float', 'attr' => 'data-jtb-attr-lng'],
            ]
        ],

        // =============================================
        // FORMS MODULES (4)
        // =============================================
        'contact_form' => [
            'tags' => ['form', 'div'],
            'data_attr' => 'contact_form',
            'classes' => ['contact-form', 'form', 'form-container'],
            'category' => 'forms',
            'child_slug' => 'contact_form_field',
            'detect' => 'form',
            'fields' => [
                'email' => ['type' => 'string', 'attr' => 'data-jtb-attr-email'],
                'success_message' => ['type' => 'string', 'attr' => 'data-jtb-attr-success-message'],
                'submit_button_text' => ['type' => 'string', 'selector' => 'button[type="submit"],input[type="submit"]'],
                'use_captcha' => ['type' => 'bool', 'attr' => 'data-jtb-attr-captcha'],
            ]
        ],
        'contact_form_field' => [
            'tags' => ['div', 'input', 'textarea', 'select'],
            'data_attr' => 'contact_form_field',
            'classes' => ['form-group', 'form-field', 'field'],
            'category' => 'forms',
            'is_child' => true,
            'fields' => [
                'field_type' => ['type' => 'string', 'attr' => 'type', 'detect' => 'input_type'],
                'field_id' => ['type' => 'string', 'attr' => 'name'],
                'field_title' => ['type' => 'string', 'selector' => 'label'],
                'required_mark' => ['type' => 'bool', 'attr' => 'required'],
                'placeholder' => ['type' => 'string', 'attr' => 'placeholder'],
            ]
        ],
        'login' => [
            'tags' => ['form', 'div'],
            'data_attr' => 'login',
            'classes' => ['login-form', 'signin-form', 'login'],
            'category' => 'forms',
            'detect' => 'login_form',
            'fields' => [
                'redirect_url' => ['type' => 'string', 'attr' => 'data-jtb-attr-redirect'],
                'show_remember' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-remember'],
            ]
        ],
        'signup' => [
            'tags' => ['form', 'div'],
            'data_attr' => 'signup',
            'classes' => ['signup-form', 'register-form', 'registration'],
            'category' => 'forms',
            'detect' => 'signup_form',
            'fields' => [
                'redirect_url' => ['type' => 'string', 'attr' => 'data-jtb-attr-redirect'],
            ]
        ],
        'search' => [
            'tags' => ['form', 'div'],
            'data_attr' => 'search',
            'classes' => ['search-form', 'search', 'site-search'],
            'category' => 'forms',
            'detect' => 'search_form',
            'fields' => [
                'placeholder_text' => ['type' => 'string', 'selector' => 'input[type="search"]@placeholder,input[type="text"]@placeholder'],
                'button_text' => ['type' => 'string', 'selector' => 'button'],
            ]
        ],

        // =============================================
        // BLOG MODULES (4)
        // =============================================
        'blog' => [
            'tags' => ['div', 'section'],
            'data_attr' => 'blog',
            'classes' => ['blog', 'posts', 'post-grid', 'blog-posts', 'articles'],
            'category' => 'blog',
            'fields' => [
                'posts_number' => ['type' => 'int', 'attr' => 'data-jtb-attr-posts'],
                'include_categories' => ['type' => 'string', 'attr' => 'data-jtb-attr-categories'],
                'show_thumbnail' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-thumb'],
                'show_content' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-content'],
                'show_date' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-date'],
                'show_author' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-author'],
                'show_categories' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-categories'],
                'show_pagination' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-pagination'],
                'fullwidth' => ['type' => 'bool', 'attr' => 'data-jtb-attr-fullwidth'],
            ]
        ],
        'portfolio' => [
            'tags' => ['div', 'section'],
            'data_attr' => 'portfolio',
            'classes' => ['portfolio', 'projects', 'work', 'portfolio-grid'],
            'category' => 'blog',
            'fields' => [
                'posts_number' => ['type' => 'int', 'attr' => 'data-jtb-attr-posts'],
                'include_categories' => ['type' => 'string', 'attr' => 'data-jtb-attr-categories'],
                'show_title' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-title'],
                'show_categories' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-categories'],
                'layout' => ['type' => 'string', 'attr' => 'data-jtb-attr-layout'],
                'columns' => ['type' => 'int', 'attr' => 'data-jtb-attr-columns'],
            ]
        ],
        'filterable_portfolio' => [
            'tags' => ['div', 'section'],
            'data_attr' => 'filterable_portfolio',
            'classes' => ['filterable-portfolio', 'filterable-gallery', 'isotope'],
            'category' => 'blog',
            'fields' => [
                'posts_number' => ['type' => 'int', 'attr' => 'data-jtb-attr-posts'],
                'show_categories' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-categories'],
                'layout' => ['type' => 'string', 'attr' => 'data-jtb-attr-layout'],
            ]
        ],
        'post_slider' => [
            'tags' => ['div'],
            'data_attr' => 'post_slider',
            'classes' => ['post-slider', 'posts-carousel'],
            'category' => 'blog',
            'fields' => [
                'posts_number' => ['type' => 'int', 'attr' => 'data-jtb-attr-posts'],
                'include_categories' => ['type' => 'string', 'attr' => 'data-jtb-attr-categories'],
                'show_arrows' => ['type' => 'bool', 'attr' => 'data-jtb-attr-arrows'],
                'show_pagination' => ['type' => 'bool', 'attr' => 'data-jtb-attr-pagination'],
            ]
        ],

        // =============================================
        // FULLWIDTH MODULES (10)
        // =============================================
        'fullwidth_header' => [
            'tags' => ['header', 'div', 'section'],
            'data_attr' => 'fullwidth_header',
            'classes' => ['fullwidth-header', 'hero', 'banner', 'hero-section'],
            'category' => 'fullwidth',
            'fields' => [
                'title' => ['type' => 'string', 'selector' => 'h1,h2,.hero-title'],
                'subhead' => ['type' => 'string', 'selector' => '.subtitle,.subhead,p'],
                'content' => ['type' => 'html', 'selector' => '.content'],
                'header_fullscreen' => ['type' => 'bool', 'attr' => 'data-jtb-attr-fullscreen'],
                'background_image' => ['type' => 'string', 'attr' => 'data-jtb-attr-bg-image'],
                'background_video_mp4' => ['type' => 'string', 'attr' => 'data-jtb-attr-bg-video'],
                'parallax' => ['type' => 'bool', 'attr' => 'data-jtb-attr-parallax'],
                'text_orientation' => ['type' => 'string', 'attr' => 'data-jtb-attr-text-align'],
                'button_one_text' => ['type' => 'string', 'selector' => '.btn:first-of-type,a.button:first-of-type'],
                'button_one_url' => ['type' => 'string', 'selector' => '.btn:first-of-type@href'],
                'button_two_text' => ['type' => 'string', 'selector' => '.btn:last-of-type'],
                'button_two_url' => ['type' => 'string', 'selector' => '.btn:last-of-type@href'],
                'logo_image_url' => ['type' => 'string', 'selector' => '.logo img@src'],
            ]
        ],
        'fullwidth_image' => [
            'tags' => ['div', 'figure'],
            'data_attr' => 'fullwidth_image',
            'classes' => ['fullwidth-image', 'full-image'],
            'category' => 'fullwidth',
            'fields' => [
                'src' => ['type' => 'string', 'selector' => 'img@src'],
                'alt' => ['type' => 'string', 'selector' => 'img@alt'],
                'link_url' => ['type' => 'string', 'selector' => 'a@href'],
            ]
        ],
        'fullwidth_menu' => [
            'tags' => ['nav', 'div'],
            'data_attr' => 'fullwidth_menu',
            'classes' => ['fullwidth-menu', 'main-nav'],
            'category' => 'fullwidth',
            'fields' => [
                'menu' => ['type' => 'string', 'attr' => 'data-jtb-attr-menu'],
                'logo_url' => ['type' => 'string', 'selector' => '.logo img@src'],
                'menu_style' => ['type' => 'string', 'attr' => 'data-jtb-attr-menu-style'],
            ]
        ],
        'fullwidth_slider' => [
            'tags' => ['div'],
            'data_attr' => 'fullwidth_slider',
            'classes' => ['fullwidth-slider', 'hero-slider'],
            'category' => 'fullwidth',
            'child_slug' => 'fullwidth_slider_item',
            'fields' => [
                'show_arrows' => ['type' => 'bool', 'attr' => 'data-jtb-attr-arrows'],
                'show_pagination' => ['type' => 'bool', 'attr' => 'data-jtb-attr-pagination'],
                'auto' => ['type' => 'bool', 'attr' => 'data-jtb-attr-auto'],
                'auto_speed' => ['type' => 'int', 'attr' => 'data-jtb-attr-speed'],
            ]
        ],
        'fullwidth_slider_item' => [
            'tags' => ['div'],
            'data_attr' => 'fullwidth_slider_item',
            'classes' => ['fullwidth-slide', 'hero-slide'],
            'category' => 'fullwidth',
            'is_child' => true,
            'fields' => [
                'heading' => ['type' => 'string', 'selector' => 'h1,h2,h3'],
                'subhead' => ['type' => 'string', 'selector' => '.subtitle,p'],
                'background_image' => ['type' => 'string', 'attr' => 'data-jtb-attr-bg-image'],
                'button_text' => ['type' => 'string', 'selector' => '.btn,a.button'],
                'link_url' => ['type' => 'string', 'selector' => '.btn@href'],
            ]
        ],
        'fullwidth_portfolio' => [
            'tags' => ['div', 'section'],
            'data_attr' => 'fullwidth_portfolio',
            'classes' => ['fullwidth-portfolio'],
            'category' => 'fullwidth',
            'fields' => [
                'posts_number' => ['type' => 'int', 'attr' => 'data-jtb-attr-posts'],
                'include_categories' => ['type' => 'string', 'attr' => 'data-jtb-attr-categories'],
            ]
        ],
        'fullwidth_code' => [
            'tags' => ['div'],
            'data_attr' => 'fullwidth_code',
            'classes' => ['fullwidth-code', 'embed-code'],
            'category' => 'fullwidth',
            'fields' => [
                'raw_content' => ['type' => 'html_content'],
            ]
        ],
        'fullwidth_map' => [
            'tags' => ['div', 'iframe'],
            'data_attr' => 'fullwidth_map',
            'classes' => ['fullwidth-map'],
            'category' => 'fullwidth',
            'detect' => 'google_map',
            'fields' => [
                'address' => ['type' => 'string', 'attr' => 'data-jtb-attr-address'],
                'zoom' => ['type' => 'int', 'attr' => 'data-jtb-attr-zoom'],
                'map_height' => ['type' => 'int', 'attr' => 'data-jtb-attr-height'],
            ]
        ],
        'fullwidth_post_slider' => [
            'tags' => ['div'],
            'data_attr' => 'fullwidth_post_slider',
            'classes' => ['fullwidth-post-slider'],
            'category' => 'fullwidth',
            'fields' => [
                'posts_number' => ['type' => 'int', 'attr' => 'data-jtb-attr-posts'],
                'include_categories' => ['type' => 'string', 'attr' => 'data-jtb-attr-categories'],
            ]
        ],
        'fullwidth_post_title' => [
            'tags' => ['div', 'header'],
            'data_attr' => 'fullwidth_post_title',
            'classes' => ['fullwidth-post-title', 'page-header'],
            'category' => 'fullwidth',
            'fields' => [
                'text_orientation' => ['type' => 'string', 'attr' => 'data-jtb-attr-text-align'],
                'show_meta' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-meta'],
            ]
        ],

        // =============================================
        // THEME MODULES (14)
        // =============================================
        'post_title' => [
            'tags' => ['h1', 'h2', 'div'],
            'data_attr' => 'post_title',
            'classes' => ['post-title', 'entry-title'],
            'category' => 'theme',
            'fields' => [
                'title' => ['type' => 'string', 'attr' => 'data-jtb-attr-title'],
                'link' => ['type' => 'bool', 'attr' => 'data-jtb-attr-link'],
            ]
        ],
        'post_content' => [
            'tags' => ['div', 'article'],
            'data_attr' => 'post_content',
            'classes' => ['post-content', 'entry-content'],
            'category' => 'theme',
            'fields' => []
        ],
        'site_logo' => [
            'tags' => ['a', 'div', 'img'],
            'data_attr' => 'site_logo',
            'classes' => ['site-logo', 'logo', 'brand'],
            'category' => 'theme',
            'fields' => [
                'logo_url' => ['type' => 'string', 'selector' => 'img@src'],
                'logo_alt' => ['type' => 'string', 'selector' => 'img@alt'],
                'max_height' => ['type' => 'int', 'attr' => 'data-jtb-attr-max-height'],
            ]
        ],
        'featured_image' => [
            'tags' => ['div', 'figure', 'img'],
            'data_attr' => 'featured_image',
            'classes' => ['featured-image', 'post-thumbnail', 'entry-thumbnail'],
            'category' => 'theme',
            'fields' => [
                'show_in_lightbox' => ['type' => 'bool', 'attr' => 'data-jtb-attr-lightbox'],
                'force_fullwidth' => ['type' => 'bool', 'attr' => 'data-jtb-attr-fullwidth'],
            ]
        ],
        'post_excerpt' => [
            'tags' => ['div', 'p'],
            'data_attr' => 'post_excerpt',
            'classes' => ['post-excerpt', 'entry-excerpt', 'excerpt'],
            'category' => 'theme',
            'fields' => [
                'excerpt_length' => ['type' => 'int', 'attr' => 'data-jtb-attr-length'],
            ]
        ],
        'post_meta' => [
            'tags' => ['div', 'ul', 'span'],
            'data_attr' => 'post_meta',
            'classes' => ['post-meta', 'entry-meta', 'meta'],
            'category' => 'theme',
            'fields' => [
                'show_author' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-author'],
                'show_date' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-date'],
                'show_categories' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-categories'],
                'show_comments' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-comments'],
            ]
        ],
        'author_box' => [
            'tags' => ['div', 'aside'],
            'data_attr' => 'author_box',
            'classes' => ['author-box', 'author-bio', 'about-author'],
            'category' => 'theme',
            'fields' => [
                'show_bio' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-bio'],
                'show_social' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-social'],
            ]
        ],
        'related_posts' => [
            'tags' => ['div', 'section'],
            'data_attr' => 'related_posts',
            'classes' => ['related-posts', 'related-articles'],
            'category' => 'theme',
            'fields' => [
                'posts_number' => ['type' => 'int', 'attr' => 'data-jtb-attr-posts'],
                'columns' => ['type' => 'int', 'attr' => 'data-jtb-attr-columns'],
            ]
        ],
        'archive_title' => [
            'tags' => ['h1', 'h2', 'div'],
            'data_attr' => 'archive_title',
            'classes' => ['archive-title', 'page-title'],
            'category' => 'theme',
            'fields' => []
        ],
        'breadcrumbs' => [
            'tags' => ['nav', 'div', 'ul'],
            'data_attr' => 'breadcrumbs',
            'classes' => ['breadcrumbs', 'breadcrumb', 'bread-crumbs'],
            'category' => 'theme',
            'fields' => [
                'home_text' => ['type' => 'string', 'attr' => 'data-jtb-attr-home-text'],
                'separator' => ['type' => 'string', 'attr' => 'data-jtb-attr-separator'],
            ]
        ],
        'archive_posts' => [
            'tags' => ['div', 'section'],
            'data_attr' => 'archive_posts',
            'classes' => ['archive-posts', 'post-list'],
            'category' => 'theme',
            'fields' => [
                'layout' => ['type' => 'string', 'attr' => 'data-jtb-attr-layout'],
                'columns' => ['type' => 'int', 'attr' => 'data-jtb-attr-columns'],
                'show_thumbnail' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-thumb'],
                'show_excerpt' => ['type' => 'bool', 'attr' => 'data-jtb-attr-show-excerpt'],
            ]
        ],
        'menu' => [
            'tags' => ['nav', 'ul', 'div'],
            'data_attr' => 'menu',
            'classes' => ['menu', 'nav-menu', 'navigation', 'main-menu'],
            'category' => 'theme',
            'fields' => [
                'menu' => ['type' => 'string', 'attr' => 'data-jtb-attr-menu'],
                'menu_style' => ['type' => 'string', 'attr' => 'data-jtb-attr-style'],
                'submenu_direction' => ['type' => 'string', 'attr' => 'data-jtb-attr-submenu'],
                'mobile_menu' => ['type' => 'bool', 'attr' => 'data-jtb-attr-mobile'],
            ]
        ],
        'search_form' => [
            'tags' => ['form', 'div'],
            'data_attr' => 'search_form',
            'classes' => ['search-form', 'search'],
            'category' => 'theme',
            'detect' => 'search_form',
            'fields' => [
                'placeholder_text' => ['type' => 'string', 'selector' => 'input@placeholder'],
                'button_text' => ['type' => 'string', 'selector' => 'button'],
            ]
        ],
        'social_icons' => [
            'tags' => ['div', 'ul'],
            'data_attr' => 'social_icons',
            'classes' => ['social-icons', 'social-links'],
            'category' => 'theme',
            'fields' => [
                'follow_button' => ['type' => 'bool', 'attr' => 'data-jtb-attr-follow'],
                'icon_color' => ['type' => 'color', 'attr' => 'data-jtb-attr-color'],
                'icon_shape' => ['type' => 'string', 'attr' => 'data-jtb-attr-shape'],
            ]
        ],
    ];

    /**
     * Map a DOM element to a JTB module type
     */
    public static function mapElement(\DOMElement $element): ?array
    {
        // First check for explicit data-jtb-module attribute
        if ($element->hasAttribute('data-jtb-module')) {
            $moduleType = $element->getAttribute('data-jtb-module');
            if (isset(self::$moduleDefinitions[$moduleType])) {
                return self::extractModuleData($element, $moduleType);
            }
        }

        // Try to detect module type from element characteristics
        $detectedType = self::detectModuleType($element);
        if ($detectedType) {
            return self::extractModuleData($element, $detectedType);
        }

        return null;
    }

    /**
     * Detect module type from element characteristics
     */
    private static function detectModuleType(\DOMElement $element): ?string
    {
        $tagName = strtolower($element->nodeName);
        $classes = self::getClassList($element);
        $style = $element->getAttribute('style');

        // Priority 1: Check by tag name for specific elements
        $tagBasedModules = [
            'h1' => 'heading', 'h2' => 'heading', 'h3' => 'heading',
            'h4' => 'heading', 'h5' => 'heading', 'h6' => 'heading',
            'hr' => 'divider',
            'pre' => 'code',
            'audio' => 'audio',
            'video' => 'video',
            'iframe' => null, // special handling below
        ];

        if (isset($tagBasedModules[$tagName])) {
            $type = $tagBasedModules[$tagName];
            if ($type) return $type;
        }

        // Special handling for iframe
        if ($tagName === 'iframe') {
            $src = $element->getAttribute('src');
            if (self::isVideoEmbed($src)) {
                return 'video';
            }
            if (self::isMapEmbed($src)) {
                return 'map';
            }
        }

        // Priority 2: Check by CSS classes
        foreach (self::$moduleDefinitions as $moduleType => $definition) {
            if (!empty($definition['classes'])) {
                foreach ($definition['classes'] as $moduleClass) {
                    if (in_array($moduleClass, $classes)) {
                        return $moduleType;
                    }
                }
            }
        }

        // Priority 3: Special detections
        if (self::isFormElement($element)) {
            if (self::isSearchForm($element)) return 'search';
            if (self::isLoginForm($element)) return 'login';
            if (self::isSignupForm($element)) return 'signup';
            return 'contact_form';
        }

        // Check if it's a button-like element
        if ($tagName === 'a' || $tagName === 'button') {
            if (self::looksLikeButton($element)) {
                return 'button';
            }
        }

        // Check if it's an image element
        if ($tagName === 'img') {
            return 'image';
        }

        // Check if it's a figure with image
        if ($tagName === 'figure' && $element->getElementsByTagName('img')->length > 0) {
            return 'image';
        }

        // Text content - p, blockquote, lists
        if (in_array($tagName, ['p', 'blockquote', 'ul', 'ol'])) {
            return 'text';
        }

        return null;
    }

    /**
     * Extract module data from element
     */
    private static function extractModuleData(\DOMElement $element, string $moduleType): array
    {
        $definition = self::$moduleDefinitions[$moduleType] ?? [];
        $attrs = [];

        // Extract fields defined in module definition
        if (!empty($definition['fields'])) {
            foreach ($definition['fields'] as $fieldName => $fieldDef) {
                $value = self::extractFieldValue($element, $fieldDef);
                if ($value !== null) {
                    $attrs[$fieldName] = $value;
                }
            }
        }

        // Extract data-jtb-attr-* attributes
        foreach ($element->attributes as $attr) {
            if (strpos($attr->name, 'data-jtb-attr-') === 0) {
                $attrName = substr($attr->name, 14); // Remove 'data-jtb-attr-'
                $attrName = str_replace('-', '_', $attrName);
                $attrs[$attrName] = self::parseAttributeValue($attr->value);
            }
        }

        // Extract responsive styles from data attributes
        $responsiveAttrs = self::extractResponsiveAttributes($element);
        $attrs = array_merge($attrs, $responsiveAttrs);

        return [
            'type' => $moduleType,
            'attrs' => $attrs,
            'category' => $definition['category'] ?? 'content',
            'child_slug' => $definition['child_slug'] ?? null,
            'is_child' => $definition['is_child'] ?? false,
        ];
    }

    /**
     * Extract field value based on field definition
     */
    private static function extractFieldValue(\DOMElement $element, array $fieldDef)
    {
        $type = $fieldDef['type'] ?? 'string';

        // Check for explicit attribute first
        if (!empty($fieldDef['attr'])) {
            if ($element->hasAttribute($fieldDef['attr'])) {
                $value = $element->getAttribute($fieldDef['attr']);
                return self::castValue($value, $type);
            }
        }

        // Check for selector-based extraction
        if (!empty($fieldDef['selector'])) {
            return self::extractBySelector($element, $fieldDef['selector'], $type);
        }

        // Check for class-based detection (e.g., featured class = featured: true)
        if (!empty($fieldDef['class'])) {
            $classes = self::getClassList($element);
            if (in_array($fieldDef['class'], $classes)) {
                return true;
            }
        }

        // Check for class prefix (e.g., social-facebook -> facebook)
        if (!empty($fieldDef['class_prefix'])) {
            $classes = self::getClassList($element);
            foreach ($classes as $class) {
                if (strpos($class, $fieldDef['class_prefix']) === 0) {
                    return substr($class, strlen($fieldDef['class_prefix']));
                }
            }
        }

        // Special type handlers
        switch ($type) {
            case 'text_content':
                return trim($element->textContent);

            case 'html_content':
                return self::getInnerHtml($element);

            case 'tag_name':
                return strtolower($element->nodeName);

            case 'html':
                return self::getInnerHtml($element);

            case 'heading_level':
                return self::detectHeadingLevel($element);
        }

        // Check default value
        if (isset($fieldDef['default'])) {
            return $fieldDef['default'];
        }

        return null;
    }

    /**
     * Extract value using CSS-like selector
     */
    private static function extractBySelector(\DOMElement $element, string $selector, string $type)
    {
        $selectors = explode(',', $selector);

        foreach ($selectors as $sel) {
            $sel = trim($sel);

            // Check if selector has attribute extraction (e.g., img@src)
            $attrName = null;
            if (strpos($sel, '@') !== false) {
                list($sel, $attrName) = explode('@', $sel);
            }

            // Find matching elements
            $found = self::findBySelector($element, $sel);

            if ($found) {
                if ($attrName) {
                    if ($found->hasAttribute($attrName)) {
                        return self::castValue($found->getAttribute($attrName), $type);
                    }
                } else {
                    // Return text content
                    return trim($found->textContent);
                }
            }
        }

        return null;
    }

    /**
     * Find element by simple selector
     */
    private static function findBySelector(\DOMElement $context, string $selector): ?\DOMElement
    {
        if (empty($selector)) {
            return $context;
        }

        // Tag selector (e.g., h1, img, p)
        if (preg_match('/^[a-z0-9]+$/i', $selector)) {
            $elements = $context->getElementsByTagName($selector);
            return $elements->length > 0 ? $elements->item(0) : null;
        }

        // Class selector (e.g., .title, .content)
        if (strpos($selector, '.') === 0) {
            $className = substr($selector, 1);
            return self::findByClass($context, $className);
        }

        // Pseudo selector (e.g., .btn:first-of-type)
        if (strpos($selector, ':') !== false) {
            list($baseSel, $pseudo) = explode(':', $selector);
            $elements = self::findAllBySelector($context, $baseSel);

            if (empty($elements)) return null;

            switch ($pseudo) {
                case 'first-of-type':
                case 'first-child':
                    return $elements[0];
                case 'last-of-type':
                case 'last-child':
                    return end($elements);
            }
        }

        // Combined selector (e.g., input[type="search"])
        if (preg_match('/^(\w+)\[([^=]+)="([^"]+)"\]$/', $selector, $m)) {
            $tag = $m[1];
            $attr = $m[2];
            $value = $m[3];

            foreach ($context->getElementsByTagName($tag) as $el) {
                if ($el->getAttribute($attr) === $value) {
                    return $el;
                }
            }
        }

        return null;
    }

    /**
     * Find all elements by selector
     */
    private static function findAllBySelector(\DOMElement $context, string $selector): array
    {
        $results = [];

        if (strpos($selector, '.') === 0) {
            $className = substr($selector, 1);
            return self::findAllByClass($context, $className);
        }

        if (preg_match('/^[a-z0-9]+$/i', $selector)) {
            foreach ($context->getElementsByTagName($selector) as $el) {
                $results[] = $el;
            }
        }

        return $results;
    }

    /**
     * Find element by class name
     */
    private static function findByClass(\DOMElement $context, string $className): ?\DOMElement
    {
        $elements = self::findAllByClass($context, $className);
        return !empty($elements) ? $elements[0] : null;
    }

    /**
     * Find all elements by class name
     */
    private static function findAllByClass(\DOMElement $context, string $className): array
    {
        $results = [];

        // Check direct children and descendants
        $allElements = $context->getElementsByTagName('*');
        foreach ($allElements as $el) {
            if (in_array($className, self::getClassList($el))) {
                $results[] = $el;
            }
        }

        return $results;
    }

    /**
     * Extract responsive attributes from data-jtb-tablet-style and data-jtb-phone-style
     */
    private static function extractResponsiveAttributes(\DOMElement $element): array
    {
        $attrs = [];

        // Extract tablet styles
        if ($element->hasAttribute('data-jtb-tablet-style')) {
            $tabletStyles = $element->getAttribute('data-jtb-tablet-style');
            // These will be converted by JTB_Attribute_Converter with __tablet suffix
            $attrs['_tablet_styles'] = $tabletStyles;
        }

        // Extract phone styles
        if ($element->hasAttribute('data-jtb-phone-style')) {
            $phoneStyles = $element->getAttribute('data-jtb-phone-style');
            $attrs['_phone_styles'] = $phoneStyles;
        }

        // Extract hover styles
        if ($element->hasAttribute('data-jtb-hover-style')) {
            $hoverStyles = $element->getAttribute('data-jtb-hover-style');
            $attrs['_hover_styles'] = $hoverStyles;
        }

        return $attrs;
    }

    /**
     * Cast value to appropriate type
     */
    private static function castValue($value, string $type)
    {
        switch ($type) {
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'int':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'array':
                if (is_array($value)) return $value;
                return json_decode($value, true) ?? [$value];
            case 'color':
                // Validate and return color
                return self::normalizeColor($value);
            default:
                return $value;
        }
    }

    /**
     * Parse attribute value (handles JSON and boolean strings)
     */
    private static function parseAttributeValue(string $value)
    {
        // Try JSON decode
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Handle boolean strings
        if ($value === 'true') return true;
        if ($value === 'false') return false;

        // Handle numeric strings
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float)$value : (int)$value;
        }

        return $value;
    }

    /**
     * Get class list from element
     */
    private static function getClassList(\DOMElement $element): array
    {
        $classAttr = $element->getAttribute('class');
        if (empty($classAttr)) return [];

        return array_filter(array_map('trim', preg_split('/\s+/', $classAttr)));
    }

    /**
     * Get inner HTML of element
     */
    private static function getInnerHtml(\DOMElement $element): string
    {
        $innerHTML = '';
        foreach ($element->childNodes as $child) {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }
        return trim($innerHTML);
    }

    /**
     * Check if URL is a video embed
     */
    private static function isVideoEmbed(string $url): bool
    {
        $videoPatterns = [
            'youtube.com', 'youtu.be',
            'vimeo.com',
            'dailymotion.com',
            'wistia.com',
            'vidyard.com',
        ];

        foreach ($videoPatterns as $pattern) {
            if (stripos($url, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if URL is a map embed
     */
    private static function isMapEmbed(string $url): bool
    {
        return stripos($url, 'google.com/maps') !== false ||
               stripos($url, 'maps.google.com') !== false;
    }

    /**
     * Check if element is a form
     */
    private static function isFormElement(\DOMElement $element): bool
    {
        return strtolower($element->nodeName) === 'form';
    }

    /**
     * Check if form is a search form
     */
    private static function isSearchForm(\DOMElement $element): bool
    {
        // Check for search input type
        foreach ($element->getElementsByTagName('input') as $input) {
            if ($input->getAttribute('type') === 'search') {
                return true;
            }
            if ($input->getAttribute('name') === 'q' || $input->getAttribute('name') === 's' || $input->getAttribute('name') === 'search') {
                return true;
            }
        }

        // Check role or class
        $classes = self::getClassList($element);
        return in_array('search-form', $classes) ||
               in_array('search', $classes) ||
               $element->getAttribute('role') === 'search';
    }

    /**
     * Check if form is a login form
     */
    private static function isLoginForm(\DOMElement $element): bool
    {
        $hasPassword = false;
        $hasUsername = false;

        foreach ($element->getElementsByTagName('input') as $input) {
            $type = $input->getAttribute('type');
            $name = strtolower($input->getAttribute('name'));

            if ($type === 'password') $hasPassword = true;
            if (in_array($name, ['username', 'user', 'email', 'login'])) $hasUsername = true;
        }

        // Check classes
        $classes = self::getClassList($element);
        if (array_intersect($classes, ['login-form', 'signin-form', 'login'])) {
            return true;
        }

        return $hasPassword && $hasUsername && !self::isSignupForm($element);
    }

    /**
     * Check if form is a signup form
     */
    private static function isSignupForm(\DOMElement $element): bool
    {
        // Check for multiple password fields (confirm password)
        $passwordCount = 0;
        foreach ($element->getElementsByTagName('input') as $input) {
            if ($input->getAttribute('type') === 'password') {
                $passwordCount++;
            }
        }

        if ($passwordCount >= 2) return true;

        // Check classes
        $classes = self::getClassList($element);
        return (bool)array_intersect($classes, ['signup-form', 'register-form', 'registration']);
    }

    /**
     * Check if element looks like a button
     */
    private static function looksLikeButton(\DOMElement $element): bool
    {
        $classes = self::getClassList($element);

        // Check for button-like classes
        $buttonClasses = ['btn', 'button', 'cta', 'cta-button', 'action-btn'];
        if (array_intersect($classes, $buttonClasses)) {
            return true;
        }

        // Check if it's a button element
        if (strtolower($element->nodeName) === 'button') {
            return true;
        }

        // Check for button-like inline styles
        $style = $element->getAttribute('style');
        if (stripos($style, 'display:') !== false && stripos($style, 'inline') !== false) {
            if (stripos($style, 'padding') !== false || stripos($style, 'border-radius') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect heading level from element
     * Checks: data-jtb-attr-level, tag name, inner h1-h6, defaults to h2
     */
    private static function detectHeadingLevel(\DOMElement $element): string
    {
        $validLevels = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

        // Priority 1: Check data-jtb-attr-level attribute
        if ($element->hasAttribute('data-jtb-attr-level')) {
            $level = strtolower($element->getAttribute('data-jtb-attr-level'));
            if (in_array($level, $validLevels)) {
                return $level;
            }
        }

        // Priority 2: Check if element itself is h1-h6
        $tagName = strtolower($element->nodeName);
        if (in_array($tagName, $validLevels)) {
            return $tagName;
        }

        // Priority 3: Look for h1-h6 inside the element
        foreach ($validLevels as $level) {
            $found = $element->getElementsByTagName($level);
            if ($found->length > 0) {
                return $level;
            }
        }

        // Priority 4: Try to detect from font-size in style
        $style = $element->getAttribute('style');
        if (preg_match('/font-size:\s*(\d+)(?:px)?/i', $style, $match)) {
            $fontSize = (int)$match[1];
            if ($fontSize >= 48) return 'h1';
            if ($fontSize >= 36) return 'h2';
            if ($fontSize >= 28) return 'h3';
            if ($fontSize >= 22) return 'h4';
            if ($fontSize >= 18) return 'h5';
            return 'h6';
        }

        // Default to h2
        return 'h2';
    }

    /**
     * Normalize color value
     */
    private static function normalizeColor(string $color): string
    {
        $color = trim($color);

        // Already in acceptable format
        if (preg_match('/^(#[0-9a-fA-F]{3,8}|rgba?\([^)]+\)|hsla?\([^)]+\)|[a-z]+)$/i', $color)) {
            return $color;
        }

        return $color;
    }

    /**
     * Get all available module types
     */
    public static function getAvailableModules(): array
    {
        return array_keys(self::$moduleDefinitions);
    }

    /**
     * Get module definition
     */
    public static function getModuleDefinition(string $type): ?array
    {
        return self::$moduleDefinitions[$type] ?? null;
    }

    /**
     * Check if module has children
     */
    public static function moduleHasChildren(string $type): bool
    {
        $def = self::$moduleDefinitions[$type] ?? [];
        return !empty($def['child_slug']);
    }

    /**
     * Get child module type for parent
     */
    public static function getChildModuleType(string $parentType): ?string
    {
        $def = self::$moduleDefinitions[$parentType] ?? [];
        return $def['child_slug'] ?? null;
    }

    /**
     * Check if module is a child module
     */
    public static function isChildModule(string $type): bool
    {
        $def = self::$moduleDefinitions[$type] ?? [];
        return !empty($def['is_child']);
    }

    /**
     * Get modules by category
     */
    public static function getModulesByCategory(string $category): array
    {
        $modules = [];
        foreach (self::$moduleDefinitions as $type => $def) {
            if (($def['category'] ?? 'content') === $category) {
                $modules[] = $type;
            }
        }
        return $modules;
    }
}
