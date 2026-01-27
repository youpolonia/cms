<?php
declare(strict_types=1);
/**
 * Theme Builder 4.0 - Section Templates Library
 * 
 * Pre-built JSON templates for all section types with multiple variants.
 * Used by AI Theme Builder and Layout Library.
 *
 * @package ThemeBuilder
 * @subpackage Templates
 * @version 4.0
 */

namespace Core\ThemeBuilder;

class SectionTemplates
{
    /**
     * Get all available section types
     */
    public static function getTypes(): array
    {
        return [
            'hero', 'features', 'about', 'services', 'testimonials', 
            'cta', 'contact', 'gallery', 'pricing', 'team', 
            'faq', 'stats', 'blog', 'portfolio', 'partners'
        ];
    }
    
    /**
     * Get all variants for a section type
     */
    public static function getVariants(string $type): array
    {
        $method = 'get' . ucfirst($type) . 'Variants';
        if (method_exists(self::class, $method)) {
            return self::$method();
        }
        return [];
    }
    
    /**
     * Get a specific template by type and variant
     */
    public static function getTemplate(string $type, string $variant, array $params = []): array
    {
        $variants = self::getVariants($type);
        if (!isset($variants[$variant])) {
            $variant = array_key_first($variants);
        }
        
        $template = $variants[$variant] ?? [];
        return self::applyParams($template, $params);
    }
    
    /**
     * Apply dynamic parameters to template
     */
    private static function applyParams(array $template, array $params): array
    {
        $json = json_encode($template);
        
        foreach ($params as $key => $value) {
            $json = str_replace('{{' . $key . '}}', $value, $json);
        }
        
        // Set default placeholders if not provided
        $defaults = [
            'business_name' => 'Your Business',
            'hero_title' => 'Welcome to Excellence',
            'hero_subtitle' => 'We deliver exceptional results',
            'cta_text' => 'Get Started',
            'primary_color' => '#6366f1',
            'secondary_color' => '#8b5cf6',
            'dark_color' => '#1a1a2e',
            'light_color' => '#f8fafc'
        ];
        
        foreach ($defaults as $key => $value) {
            $json = str_replace('{{' . $key . '}}', $value, $json);
        }
        
        return json_decode($json, true);
    }
    
    // ═══════════════════════════════════════════════════════════════
    // HERO SECTION VARIANTS
    // ═══════════════════════════════════════════════════════════════
    
    private static function getHeroVariants(): array
    {
        return [
            'split_right' => [
                'id' => 'hero_split_right',
                'name' => 'Hero',
                'design' => [
                    'background_color' => '{{dark_color}}',
                    'padding_top' => '100px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'hero_row_1',
                        'columns' => [
                            [
                                'id' => 'hero_col_1',
                                'width' => '55%',
                                'modules' => [
                                    ['type' => 'text', 'content' => ['text' => '{{hero_subtitle}}'], 'design' => ['text_color' => '{{primary_color}}', 'font_size' => '14px', 'font_weight' => '600', 'text_transform' => 'uppercase', 'letter_spacing' => '2px']],
                                    ['type' => 'heading', 'content' => ['text' => '{{hero_title}}', 'level' => 'h1'], 'design' => ['text_color' => '#ffffff', 'font_size' => '64px', 'font_weight' => '800', 'line_height' => '1.1', 'margin_bottom' => '24px']],
                                    ['type' => 'text', 'content' => ['text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'], 'design' => ['text_color' => '#94a3b8', 'font_size' => '18px', 'line_height' => '1.7', 'margin_bottom' => '32px']],
                                    ['type' => 'button', 'content' => ['text' => '{{cta_text}}', 'url' => '#contact'], 'design' => ['background_color' => '{{primary_color}}', 'text_color' => '#ffffff', 'padding' => '16px 32px', 'border_radius' => '8px', 'font_weight' => '600']]
                                ]
                            ],
                            [
                                'id' => 'hero_col_2',
                                'width' => '45%',
                                'modules' => [
                                    ['type' => 'image', 'content' => ['src' => 'hero-image', 'alt' => 'Hero image'], 'design' => ['border_radius' => '16px', 'box_shadow' => '0 25px 50px -12px rgba(0,0,0,0.25)']]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            'split_left' => [
                'id' => 'hero_split_left',
                'name' => 'Hero',
                'design' => [
                    'background_color' => '{{light_color}}',
                    'padding_top' => '100px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'hero_row_1',
                        'columns' => [
                            [
                                'id' => 'hero_col_1',
                                'width' => '45%',
                                'modules' => [
                                    ['type' => 'image', 'content' => ['src' => 'hero-image', 'alt' => 'Hero image'], 'design' => ['border_radius' => '16px']]
                                ]
                            ],
                            [
                                'id' => 'hero_col_2',
                                'width' => '55%',
                                'modules' => [
                                    ['type' => 'heading', 'content' => ['text' => '{{hero_title}}', 'level' => 'h1'], 'design' => ['text_color' => '{{dark_color}}', 'font_size' => '56px', 'font_weight' => '700']],
                                    ['type' => 'text', 'content' => ['text' => 'Description text goes here with compelling copy about your business.'], 'design' => ['text_color' => '#64748b', 'font_size' => '18px', 'margin_bottom' => '32px']],
                                    ['type' => 'button', 'content' => ['text' => '{{cta_text}}', 'url' => '#'], 'design' => ['background_color' => '{{primary_color}}', 'text_color' => '#ffffff', 'padding' => '14px 28px', 'border_radius' => '6px']]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            'centered' => [
                'id' => 'hero_centered',
                'name' => 'Hero',
                'design' => [
                    'background_color' => '{{dark_color}}',
                    'background_image' => '',
                    'padding_top' => '140px',
                    'padding_bottom' => '140px',
                    'text_align' => 'center'
                ],
                'rows' => [
                    [
                        'id' => 'hero_row_1',
                        'columns' => [
                            [
                                'id' => 'hero_col_1',
                                'width' => '100%',
                                'modules' => [
                                    ['type' => 'heading', 'content' => ['text' => '{{hero_title}}', 'level' => 'h1'], 'design' => ['text_color' => '#ffffff', 'font_size' => '72px', 'font_weight' => '800', 'text_align' => 'center', 'max_width' => '900px', 'margin' => '0 auto 24px']],
                                    ['type' => 'text', 'content' => ['text' => '{{hero_subtitle}}'], 'design' => ['text_color' => '#94a3b8', 'font_size' => '20px', 'text_align' => 'center', 'max_width' => '600px', 'margin' => '0 auto 40px']],
                                    ['type' => 'button', 'content' => ['text' => '{{cta_text}}', 'url' => '#'], 'design' => ['background_color' => '{{primary_color}}', 'text_color' => '#ffffff', 'padding' => '18px 40px', 'border_radius' => '8px', 'font_size' => '18px']]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            'video_background' => [
                'id' => 'hero_video',
                'name' => 'Hero',
                'design' => [
                    'background_color' => '#000000',
                    'background_video' => '',
                    'overlay_color' => 'rgba(0,0,0,0.6)',
                    'min_height' => '100vh',
                    'padding_top' => '0',
                    'padding_bottom' => '0',
                    'text_align' => 'center',
                    'vertical_align' => 'center'
                ],
                'rows' => [
                    [
                        'id' => 'hero_row_1',
                        'columns' => [
                            [
                                'id' => 'hero_col_1',
                                'width' => '100%',
                                'modules' => [
                                    ['type' => 'heading', 'content' => ['text' => '{{hero_title}}', 'level' => 'h1'], 'design' => ['text_color' => '#ffffff', 'font_size' => '80px', 'font_weight' => '700', 'text_align' => 'center']],
                                    ['type' => 'divider', 'content' => [], 'design' => ['width' => '80px', 'color' => '{{primary_color}}', 'weight' => '3px', 'margin' => '30px auto']],
                                    ['type' => 'text', 'content' => ['text' => '{{hero_subtitle}}'], 'design' => ['text_color' => '#ffffff', 'font_size' => '22px', 'text_align' => 'center', 'margin_bottom' => '40px']],
                                    ['type' => 'button', 'content' => ['text' => '{{cta_text}}', 'url' => '#'], 'design' => ['background_color' => 'transparent', 'text_color' => '#ffffff', 'border' => '2px solid #ffffff', 'padding' => '16px 40px', 'border_radius' => '0']]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            'minimal' => [
                'id' => 'hero_minimal',
                'name' => 'Hero',
                'design' => [
                    'background_color' => '#ffffff',
                    'padding_top' => '180px',
                    'padding_bottom' => '180px',
                    'text_align' => 'center'
                ],
                'rows' => [
                    [
                        'id' => 'hero_row_1',
                        'columns' => [
                            [
                                'id' => 'hero_col_1',
                                'width' => '100%',
                                'modules' => [
                                    ['type' => 'heading', 'content' => ['text' => '{{hero_title}}', 'level' => 'h1'], 'design' => ['text_color' => '#18181b', 'font_size' => '64px', 'font_weight' => '500', 'text_align' => 'center', 'letter_spacing' => '-2px']],
                                    ['type' => 'button', 'content' => ['text' => '{{cta_text}}', 'url' => '#'], 'design' => ['background_color' => '#18181b', 'text_color' => '#ffffff', 'padding' => '14px 32px', 'border_radius' => '0', 'margin_top' => '40px']]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            'gradient' => [
                'id' => 'hero_gradient',
                'name' => 'Hero',
                'design' => [
                    'background' => 'linear-gradient(135deg, {{primary_color}} 0%, {{secondary_color}} 100%)',
                    'padding_top' => '120px',
                    'padding_bottom' => '120px',
                    'text_align' => 'center'
                ],
                'rows' => [
                    [
                        'id' => 'hero_row_1',
                        'columns' => [
                            [
                                'id' => 'hero_col_1',
                                'width' => '100%',
                                'modules' => [
                                    ['type' => 'text', 'content' => ['text' => 'Welcome to'], 'design' => ['text_color' => 'rgba(255,255,255,0.8)', 'font_size' => '18px', 'text_align' => 'center', 'margin_bottom' => '16px']],
                                    ['type' => 'heading', 'content' => ['text' => '{{business_name}}', 'level' => 'h1'], 'design' => ['text_color' => '#ffffff', 'font_size' => '72px', 'font_weight' => '800', 'text_align' => 'center']],
                                    ['type' => 'text', 'content' => ['text' => '{{hero_subtitle}}'], 'design' => ['text_color' => 'rgba(255,255,255,0.9)', 'font_size' => '20px', 'text_align' => 'center', 'max_width' => '600px', 'margin' => '24px auto 40px']],
                                    ['type' => 'button', 'content' => ['text' => '{{cta_text}}', 'url' => '#'], 'design' => ['background_color' => '#ffffff', 'text_color' => '{{primary_color}}', 'padding' => '16px 36px', 'border_radius' => '50px', 'font_weight' => '600']]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // FEATURES SECTION VARIANTS
    // ═══════════════════════════════════════════════════════════════
    
    private static function getFeaturesVariants(): array
    {
        return [
            'grid_3col' => [
                'id' => 'features_grid_3',
                'name' => 'Features',
                'design' => [
                    'background_color' => '{{light_color}}',
                    'padding_top' => '100px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'features_header',
                        'columns' => [
                            [
                                'id' => 'features_header_col',
                                'width' => '100%',
                                'modules' => [
                                    ['type' => 'text', 'content' => ['text' => 'Why Choose Us'], 'design' => ['text_color' => '{{primary_color}}', 'font_size' => '14px', 'font_weight' => '600', 'text_transform' => 'uppercase', 'letter_spacing' => '2px', 'text_align' => 'center']],
                                    ['type' => 'heading', 'content' => ['text' => 'Our Features', 'level' => 'h2'], 'design' => ['text_color' => '{{dark_color}}', 'font_size' => '42px', 'font_weight' => '700', 'text_align' => 'center', 'margin_bottom' => '60px']]
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'features_grid',
                        'columns' => [
                            [
                                'id' => 'feature_1',
                                'width' => '33.33%',
                                'modules' => [
                                    ['type' => 'blurb', 'content' => ['icon' => 'fas fa-rocket', 'title' => 'Fast Performance', 'text' => 'Lightning fast loading times and optimized performance.'], 'design' => ['icon_color' => '{{primary_color}}', 'icon_size' => '48px', 'text_align' => 'center']]
                                ]
                            ],
                            [
                                'id' => 'feature_2',
                                'width' => '33.33%',
                                'modules' => [
                                    ['type' => 'blurb', 'content' => ['icon' => 'fas fa-shield-alt', 'title' => 'Secure & Safe', 'text' => 'Enterprise-grade security to protect your data.'], 'design' => ['icon_color' => '{{primary_color}}', 'icon_size' => '48px', 'text_align' => 'center']]
                                ]
                            ],
                            [
                                'id' => 'feature_3',
                                'width' => '33.33%',
                                'modules' => [
                                    ['type' => 'blurb', 'content' => ['icon' => 'fas fa-headset', 'title' => '24/7 Support', 'text' => 'Round-the-clock support from our expert team.'], 'design' => ['icon_color' => '{{primary_color}}', 'icon_size' => '48px', 'text_align' => 'center']]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            'grid_4col' => [
                'id' => 'features_grid_4',
                'name' => 'Features',
                'design' => [
                    'background_color' => '#ffffff',
                    'padding_top' => '100px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'features_header',
                        'columns' => [
                            [
                                'id' => 'features_header_col',
                                'width' => '100%',
                                'modules' => [
                                    ['type' => 'heading', 'content' => ['text' => 'What We Offer', 'level' => 'h2'], 'design' => ['text_color' => '{{dark_color}}', 'font_size' => '40px', 'font_weight' => '700', 'text_align' => 'center', 'margin_bottom' => '16px']],
                                    ['type' => 'text', 'content' => ['text' => 'Comprehensive solutions for your business needs'], 'design' => ['text_color' => '#64748b', 'font_size' => '18px', 'text_align' => 'center', 'margin_bottom' => '50px']]
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'features_grid',
                        'columns' => [
                            ['id' => 'f1', 'width' => '25%', 'modules' => [['type' => 'blurb', 'content' => ['icon' => 'fas fa-bolt', 'title' => 'Fast', 'text' => 'Quick delivery'], 'design' => ['icon_color' => '{{primary_color}}', 'text_align' => 'center']]]],
                            ['id' => 'f2', 'width' => '25%', 'modules' => [['type' => 'blurb', 'content' => ['icon' => 'fas fa-lock', 'title' => 'Secure', 'text' => 'Protected data'], 'design' => ['icon_color' => '{{primary_color}}', 'text_align' => 'center']]]],
                            ['id' => 'f3', 'width' => '25%', 'modules' => [['type' => 'blurb', 'content' => ['icon' => 'fas fa-sync', 'title' => 'Reliable', 'text' => '99.9% uptime'], 'design' => ['icon_color' => '{{primary_color}}', 'text_align' => 'center']]]],
                            ['id' => 'f4', 'width' => '25%', 'modules' => [['type' => 'blurb', 'content' => ['icon' => 'fas fa-heart', 'title' => 'Loved', 'text' => 'Customer first'], 'design' => ['icon_color' => '{{primary_color}}', 'text_align' => 'center']]]]
                        ]
                    ]
                ]
            ],
            
            'bento' => [
                'id' => 'features_bento',
                'name' => 'Features',
                'design' => [
                    'background_color' => '{{dark_color}}',
                    'padding_top' => '100px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'bento_row_1',
                        'columns' => [
                            [
                                'id' => 'bento_large',
                                'width' => '50%',
                                'modules' => [
                                    ['type' => 'blurb', 'content' => ['icon' => 'fas fa-star', 'title' => 'Premium Quality', 'text' => 'We deliver excellence in every project. Our commitment to quality sets us apart from the competition.'], 'design' => ['background_color' => 'rgba(255,255,255,0.1)', 'padding' => '40px', 'border_radius' => '16px', 'icon_color' => '{{primary_color}}', 'icon_size' => '56px']]
                                ]
                            ],
                            [
                                'id' => 'bento_small_col',
                                'width' => '50%',
                                'modules' => [
                                    ['type' => 'blurb', 'content' => ['icon' => 'fas fa-clock', 'title' => 'Fast Delivery', 'text' => 'On-time, every time.'], 'design' => ['background_color' => 'rgba(255,255,255,0.05)', 'padding' => '24px', 'border_radius' => '12px', 'icon_color' => '{{secondary_color}}', 'margin_bottom' => '20px']],
                                    ['type' => 'blurb', 'content' => ['icon' => 'fas fa-users', 'title' => 'Expert Team', 'text' => 'Skilled professionals.'], 'design' => ['background_color' => 'rgba(255,255,255,0.05)', 'padding' => '24px', 'border_radius' => '12px', 'icon_color' => '{{secondary_color}}']]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            'alternating' => [
                'id' => 'features_alternating',
                'name' => 'Features',
                'design' => [
                    'background_color' => '#ffffff',
                    'padding_top' => '100px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'alt_row_1',
                        'columns' => [
                            ['id' => 'alt_img_1', 'width' => '50%', 'modules' => [['type' => 'image', 'content' => ['src' => 'feature-1', 'alt' => 'Feature 1'], 'design' => ['border_radius' => '12px']]]],
                            ['id' => 'alt_text_1', 'width' => '50%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => 'Feature One', 'level' => 'h3'], 'design' => ['font_size' => '32px', 'margin_bottom' => '16px']],
                                ['type' => 'text', 'content' => ['text' => 'Detailed description of this amazing feature and how it benefits your customers.'], 'design' => ['color' => '#64748b', 'line_height' => '1.7']]
                            ]]
                        ]
                    ],
                    [
                        'id' => 'alt_row_2',
                        'columns' => [
                            ['id' => 'alt_text_2', 'width' => '50%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => 'Feature Two', 'level' => 'h3'], 'design' => ['font_size' => '32px', 'margin_bottom' => '16px']],
                                ['type' => 'text', 'content' => ['text' => 'Another compelling feature that makes your service stand out from competitors.'], 'design' => ['color' => '#64748b', 'line_height' => '1.7']]
                            ]],
                            ['id' => 'alt_img_2', 'width' => '50%', 'modules' => [['type' => 'image', 'content' => ['src' => 'feature-2', 'alt' => 'Feature 2'], 'design' => ['border_radius' => '12px']]]]
                        ]
                    ]
                ]
            ],
            
            'icon_cards' => [
                'id' => 'features_icon_cards',
                'name' => 'Features',
                'design' => [
                    'background_color' => '{{light_color}}',
                    'padding_top' => '100px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'icon_cards_row',
                        'columns' => [
                            ['id' => 'ic1', 'width' => '33.33%', 'modules' => [['type' => 'blurb', 'content' => ['icon' => 'fas fa-palette', 'title' => 'Beautiful Design', 'text' => 'Stunning visuals that capture attention and convert visitors into customers.'], 'design' => ['background_color' => '#ffffff', 'padding' => '40px', 'border_radius' => '16px', 'box_shadow' => '0 4px 20px rgba(0,0,0,0.08)', 'icon_color' => '{{primary_color}}', 'icon_size' => '48px', 'text_align' => 'center']]]],
                            ['id' => 'ic2', 'width' => '33.33%', 'modules' => [['type' => 'blurb', 'content' => ['icon' => 'fas fa-code', 'title' => 'Clean Code', 'text' => 'Well-structured, maintainable code that scales with your business.'], 'design' => ['background_color' => '#ffffff', 'padding' => '40px', 'border_radius' => '16px', 'box_shadow' => '0 4px 20px rgba(0,0,0,0.08)', 'icon_color' => '{{primary_color}}', 'icon_size' => '48px', 'text_align' => 'center']]]],
                            ['id' => 'ic3', 'width' => '33.33%', 'modules' => [['type' => 'blurb', 'content' => ['icon' => 'fas fa-chart-line', 'title' => 'Analytics', 'text' => 'Data-driven insights to help you make better business decisions.'], 'design' => ['background_color' => '#ffffff', 'padding' => '40px', 'border_radius' => '16px', 'box_shadow' => '0 4px 20px rgba(0,0,0,0.08)', 'icon_color' => '{{primary_color}}', 'icon_size' => '48px', 'text_align' => 'center']]]]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // ABOUT SECTION VARIANTS
    // ═══════════════════════════════════════════════════════════════
    
    private static function getAboutVariants(): array
    {
        return [
            'split_image' => [
                'id' => 'about_split',
                'name' => 'About Us',
                'design' => ['background_color' => '#ffffff', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'about_row',
                        'columns' => [
                            ['id' => 'about_img', 'width' => '50%', 'modules' => [['type' => 'image', 'content' => ['src' => 'about-image', 'alt' => 'About us'], 'design' => ['border_radius' => '16px']]]],
                            ['id' => 'about_text', 'width' => '50%', 'modules' => [
                                ['type' => 'text', 'content' => ['text' => 'About Us'], 'design' => ['text_color' => '{{primary_color}}', 'font_size' => '14px', 'font_weight' => '600', 'text_transform' => 'uppercase', 'letter_spacing' => '2px']],
                                ['type' => 'heading', 'content' => ['text' => 'Our Story', 'level' => 'h2'], 'design' => ['font_size' => '40px', 'margin_bottom' => '24px']],
                                ['type' => 'text', 'content' => ['text' => 'We started with a simple mission: to deliver exceptional quality and service. Today, we continue that commitment with every project we undertake.'], 'design' => ['color' => '#64748b', 'font_size' => '18px', 'line_height' => '1.8', 'margin_bottom' => '32px']],
                                ['type' => 'button', 'content' => ['text' => 'Learn More', 'url' => '/about'], 'design' => ['background_color' => '{{primary_color}}', 'text_color' => '#ffffff', 'padding' => '14px 28px', 'border_radius' => '8px']]
                            ]]
                        ]
                    ]
                ]
            ],
            
            'stats' => [
                'id' => 'about_stats',
                'name' => 'About Us',
                'design' => ['background_color' => '{{dark_color}}', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'about_header',
                        'columns' => [
                            ['id' => 'about_h', 'width' => '60%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => 'Why Work With Us', 'level' => 'h2'], 'design' => ['text_color' => '#ffffff', 'font_size' => '48px', 'margin_bottom' => '24px']],
                                ['type' => 'text', 'content' => ['text' => 'Years of experience and thousands of satisfied customers speak for our commitment to excellence.'], 'design' => ['text_color' => '#94a3b8', 'font_size' => '18px']]
                            ]],
                            ['id' => 'about_space', 'width' => '40%', 'modules' => []]
                        ]
                    ],
                    [
                        'id' => 'stats_row',
                        'columns' => [
                            ['id' => 's1', 'width' => '25%', 'modules' => [['type' => 'counter', 'content' => ['number' => '15', 'suffix' => '+', 'label' => 'Years Experience'], 'design' => ['number_color' => '{{primary_color}}', 'number_size' => '56px', 'label_color' => '#94a3b8']]]],
                            ['id' => 's2', 'width' => '25%', 'modules' => [['type' => 'counter', 'content' => ['number' => '500', 'suffix' => '+', 'label' => 'Projects Completed'], 'design' => ['number_color' => '{{primary_color}}', 'number_size' => '56px', 'label_color' => '#94a3b8']]]],
                            ['id' => 's3', 'width' => '25%', 'modules' => [['type' => 'counter', 'content' => ['number' => '50', 'suffix' => '+', 'label' => 'Team Members'], 'design' => ['number_color' => '{{primary_color}}', 'number_size' => '56px', 'label_color' => '#94a3b8']]]],
                            ['id' => 's4', 'width' => '25%', 'modules' => [['type' => 'counter', 'content' => ['number' => '99', 'suffix' => '%', 'label' => 'Client Satisfaction'], 'design' => ['number_color' => '{{primary_color}}', 'number_size' => '56px', 'label_color' => '#94a3b8']]]]
                        ]
                    ]
                ]
            ],
            
            'timeline' => [
                'id' => 'about_timeline',
                'name' => 'Our Journey',
                'design' => ['background_color' => '{{light_color}}', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'timeline_header',
                        'columns' => [
                            ['id' => 't_h', 'width' => '100%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => 'Our Journey', 'level' => 'h2'], 'design' => ['font_size' => '42px', 'text_align' => 'center', 'margin_bottom' => '60px']]
                            ]]
                        ]
                    ],
                    [
                        'id' => 'timeline_items',
                        'columns' => [
                            ['id' => 't1', 'width' => '33.33%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => '2010', 'level' => 'h3'], 'design' => ['text_color' => '{{primary_color}}', 'font_size' => '24px']],
                                ['type' => 'heading', 'content' => ['text' => 'Founded', 'level' => 'h4'], 'design' => ['font_size' => '20px', 'margin_bottom' => '12px']],
                                ['type' => 'text', 'content' => ['text' => 'Started with a vision to transform the industry.'], 'design' => ['color' => '#64748b']]
                            ]],
                            ['id' => 't2', 'width' => '33.33%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => '2015', 'level' => 'h3'], 'design' => ['text_color' => '{{primary_color}}', 'font_size' => '24px']],
                                ['type' => 'heading', 'content' => ['text' => 'Expansion', 'level' => 'h4'], 'design' => ['font_size' => '20px', 'margin_bottom' => '12px']],
                                ['type' => 'text', 'content' => ['text' => 'Grew to serve clients nationwide.'], 'design' => ['color' => '#64748b']]
                            ]],
                            ['id' => 't3', 'width' => '33.33%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => '2024', 'level' => 'h3'], 'design' => ['text_color' => '{{primary_color}}', 'font_size' => '24px']],
                                ['type' => 'heading', 'content' => ['text' => 'Today', 'level' => 'h4'], 'design' => ['font_size' => '20px', 'margin_bottom' => '12px']],
                                ['type' => 'text', 'content' => ['text' => 'Industry leader with global reach.'], 'design' => ['color' => '#64748b']]
                            ]]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // TESTIMONIALS SECTION VARIANTS
    // ═══════════════════════════════════════════════════════════════
    
    private static function getTestimonialsVariants(): array
    {
        return [
            'grid' => [
                'id' => 'testimonials_grid',
                'name' => 'Testimonials',
                'design' => ['background_color' => '{{light_color}}', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'test_header',
                        'columns' => [
                            ['id' => 'test_h', 'width' => '100%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => 'What Our Clients Say', 'level' => 'h2'], 'design' => ['font_size' => '42px', 'text_align' => 'center', 'margin_bottom' => '50px']]
                            ]]
                        ]
                    ],
                    [
                        'id' => 'test_grid',
                        'columns' => [
                            ['id' => 'test1', 'width' => '33.33%', 'modules' => [['type' => 'testimonial', 'content' => ['quote' => 'Exceptional service and outstanding results. Highly recommended!', 'name' => 'John Smith', 'role' => 'CEO, TechCorp'], 'design' => ['background_color' => '#ffffff', 'padding' => '32px', 'border_radius' => '12px', 'box_shadow' => '0 4px 20px rgba(0,0,0,0.08)']]]],
                            ['id' => 'test2', 'width' => '33.33%', 'modules' => [['type' => 'testimonial', 'content' => ['quote' => 'They transformed our business. Professional team and amazing work.', 'name' => 'Sarah Johnson', 'role' => 'Founder, StartupXYZ'], 'design' => ['background_color' => '#ffffff', 'padding' => '32px', 'border_radius' => '12px', 'box_shadow' => '0 4px 20px rgba(0,0,0,0.08)']]]],
                            ['id' => 'test3', 'width' => '33.33%', 'modules' => [['type' => 'testimonial', 'content' => ['quote' => 'Best decision we made. Quality and reliability at its finest.', 'name' => 'Mike Davis', 'role' => 'Director, AgencyCo'], 'design' => ['background_color' => '#ffffff', 'padding' => '32px', 'border_radius' => '12px', 'box_shadow' => '0 4px 20px rgba(0,0,0,0.08)']]]]
                        ]
                    ]
                ]
            ],
            
            'featured' => [
                'id' => 'testimonials_featured',
                'name' => 'Testimonials',
                'design' => ['background_color' => '{{dark_color}}', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'featured_test',
                        'columns' => [
                            ['id' => 'ft', 'width' => '100%', 'modules' => [
                                ['type' => 'icon', 'content' => ['icon' => 'fas fa-quote-left'], 'design' => ['color' => '{{primary_color}}', 'font_size' => '48px', 'text_align' => 'center', 'margin_bottom' => '24px']],
                                ['type' => 'text', 'content' => ['text' => '"Working with this team was an absolute pleasure. They exceeded every expectation and delivered a product that transformed our business. I cannot recommend them highly enough."'], 'design' => ['text_color' => '#ffffff', 'font_size' => '28px', 'font_style' => 'italic', 'text_align' => 'center', 'max_width' => '800px', 'margin' => '0 auto 32px', 'line_height' => '1.6']],
                                ['type' => 'image', 'content' => ['src' => 'avatar', 'alt' => 'Client'], 'design' => ['width' => '80px', 'height' => '80px', 'border_radius' => '50%', 'margin' => '0 auto 16px']],
                                ['type' => 'heading', 'content' => ['text' => 'Amanda Chen', 'level' => 'h4'], 'design' => ['text_color' => '#ffffff', 'font_size' => '20px', 'text_align' => 'center']],
                                ['type' => 'text', 'content' => ['text' => 'VP of Marketing, GlobalTech'], 'design' => ['text_color' => '#94a3b8', 'text_align' => 'center']]
                            ]]
                        ]
                    ]
                ]
            ],
            
            'logo_bar' => [
                'id' => 'testimonials_logos',
                'name' => 'Trusted By',
                'design' => ['background_color' => '#ffffff', 'padding_top' => '60px', 'padding_bottom' => '60px'],
                'rows' => [
                    [
                        'id' => 'logos_header',
                        'columns' => [
                            ['id' => 'l_h', 'width' => '100%', 'modules' => [
                                ['type' => 'text', 'content' => ['text' => 'Trusted by leading companies'], 'design' => ['text_color' => '#94a3b8', 'font_size' => '14px', 'text_transform' => 'uppercase', 'letter_spacing' => '2px', 'text_align' => 'center', 'margin_bottom' => '40px']]
                            ]]
                        ]
                    ],
                    [
                        'id' => 'logos_row',
                        'columns' => [
                            ['id' => 'logo1', 'width' => '20%', 'modules' => [['type' => 'image', 'content' => ['src' => 'client-logo-1', 'alt' => 'Client 1'], 'design' => ['opacity' => '0.5', 'max_height' => '40px']]]],
                            ['id' => 'logo2', 'width' => '20%', 'modules' => [['type' => 'image', 'content' => ['src' => 'client-logo-2', 'alt' => 'Client 2'], 'design' => ['opacity' => '0.5', 'max_height' => '40px']]]],
                            ['id' => 'logo3', 'width' => '20%', 'modules' => [['type' => 'image', 'content' => ['src' => 'client-logo-3', 'alt' => 'Client 3'], 'design' => ['opacity' => '0.5', 'max_height' => '40px']]]],
                            ['id' => 'logo4', 'width' => '20%', 'modules' => [['type' => 'image', 'content' => ['src' => 'client-logo-4', 'alt' => 'Client 4'], 'design' => ['opacity' => '0.5', 'max_height' => '40px']]]],
                            ['id' => 'logo5', 'width' => '20%', 'modules' => [['type' => 'image', 'content' => ['src' => 'client-logo-5', 'alt' => 'Client 5'], 'design' => ['opacity' => '0.5', 'max_height' => '40px']]]]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // CTA SECTION VARIANTS
    // ═══════════════════════════════════════════════════════════════
    
    private static function getCtaVariants(): array
    {
        return [
            'centered' => [
                'id' => 'cta_centered',
                'name' => 'Call to Action',
                'design' => ['background_color' => '{{primary_color}}', 'padding_top' => '80px', 'padding_bottom' => '80px'],
                'rows' => [
                    [
                        'id' => 'cta_row',
                        'columns' => [
                            ['id' => 'cta_col', 'width' => '100%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => 'Ready to Get Started?', 'level' => 'h2'], 'design' => ['text_color' => '#ffffff', 'font_size' => '42px', 'text_align' => 'center', 'margin_bottom' => '16px']],
                                ['type' => 'text', 'content' => ['text' => 'Contact us today and let\'s discuss how we can help you achieve your goals.'], 'design' => ['text_color' => 'rgba(255,255,255,0.9)', 'font_size' => '18px', 'text_align' => 'center', 'margin_bottom' => '32px']],
                                ['type' => 'button', 'content' => ['text' => 'Contact Us', 'url' => '#contact'], 'design' => ['background_color' => '#ffffff', 'text_color' => '{{primary_color}}', 'padding' => '16px 40px', 'border_radius' => '8px', 'font_weight' => '600']]
                            ]]
                        ]
                    ]
                ]
            ],
            
            'gradient' => [
                'id' => 'cta_gradient',
                'name' => 'Call to Action',
                'design' => ['background' => 'linear-gradient(135deg, {{primary_color}} 0%, {{secondary_color}} 100%)', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'cta_row',
                        'columns' => [
                            ['id' => 'cta_col', 'width' => '100%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => 'Let\'s Create Something Amazing', 'level' => 'h2'], 'design' => ['text_color' => '#ffffff', 'font_size' => '48px', 'text_align' => 'center', 'margin_bottom' => '24px']],
                                ['type' => 'text', 'content' => ['text' => 'Your success story starts here. Take the first step today.'], 'design' => ['text_color' => 'rgba(255,255,255,0.9)', 'font_size' => '20px', 'text_align' => 'center', 'margin_bottom' => '40px']],
                                ['type' => 'button', 'content' => ['text' => 'Start Your Project', 'url' => '#contact'], 'design' => ['background_color' => '#ffffff', 'text_color' => '{{primary_color}}', 'padding' => '18px 48px', 'border_radius' => '50px', 'font_size' => '18px', 'font_weight' => '600']]
                            ]]
                        ]
                    ]
                ]
            ],
            
            'split' => [
                'id' => 'cta_split',
                'name' => 'Call to Action',
                'design' => ['background_color' => '{{dark_color}}', 'padding_top' => '80px', 'padding_bottom' => '80px'],
                'rows' => [
                    [
                        'id' => 'cta_row',
                        'columns' => [
                            ['id' => 'cta_text', 'width' => '60%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => 'Ready to Transform Your Business?', 'level' => 'h2'], 'design' => ['text_color' => '#ffffff', 'font_size' => '36px', 'margin_bottom' => '16px']],
                                ['type' => 'text', 'content' => ['text' => 'Join hundreds of satisfied clients who have already taken their business to the next level.'], 'design' => ['text_color' => '#94a3b8', 'font_size' => '18px']]
                            ]],
                            ['id' => 'cta_btn', 'width' => '40%', 'modules' => [
                                ['type' => 'button', 'content' => ['text' => 'Get Free Consultation', 'url' => '#contact'], 'design' => ['background_color' => '{{primary_color}}', 'text_color' => '#ffffff', 'padding' => '18px 32px', 'border_radius' => '8px', 'width' => '100%']]
                            ]]
                        ]
                    ]
                ]
            ],
            
            'minimal' => [
                'id' => 'cta_minimal',
                'name' => 'Call to Action',
                'design' => ['background_color' => '#ffffff', 'padding_top' => '60px', 'padding_bottom' => '60px', 'border_top' => '1px solid #e5e7eb', 'border_bottom' => '1px solid #e5e7eb'],
                'rows' => [
                    [
                        'id' => 'cta_row',
                        'columns' => [
                            ['id' => 'cta_col', 'width' => '100%', 'modules' => [
                                ['type' => 'text', 'content' => ['text' => 'Interested in working together?'], 'design' => ['text_color' => '#64748b', 'font_size' => '18px', 'text_align' => 'center', 'margin_bottom' => '16px']],
                                ['type' => 'button', 'content' => ['text' => 'Get in Touch →', 'url' => '#contact'], 'design' => ['background_color' => 'transparent', 'text_color' => '{{dark_color}}', 'font_size' => '18px', 'font_weight' => '600', 'text_decoration' => 'underline']]
                            ]]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // CONTACT SECTION VARIANTS  
    // ═══════════════════════════════════════════════════════════════
    
    private static function getContactVariants(): array
    {
        return [
            'split_form' => [
                'id' => 'contact_split',
                'name' => 'Contact',
                'design' => ['background_color' => '{{light_color}}', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'contact_row',
                        'columns' => [
                            ['id' => 'contact_info', 'width' => '40%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => 'Get in Touch', 'level' => 'h2'], 'design' => ['font_size' => '36px', 'margin_bottom' => '24px']],
                                ['type' => 'text', 'content' => ['text' => 'Have a question or want to work together? We\'d love to hear from you.'], 'design' => ['color' => '#64748b', 'margin_bottom' => '32px']],
                                ['type' => 'blurb', 'content' => ['icon' => 'fas fa-map-marker-alt', 'title' => 'Address', 'text' => '123 Business Street, City, ST 12345'], 'design' => ['icon_color' => '{{primary_color}}', 'margin_bottom' => '20px']],
                                ['type' => 'blurb', 'content' => ['icon' => 'fas fa-phone', 'title' => 'Phone', 'text' => '(555) 123-4567'], 'design' => ['icon_color' => '{{primary_color}}', 'margin_bottom' => '20px']],
                                ['type' => 'blurb', 'content' => ['icon' => 'fas fa-envelope', 'title' => 'Email', 'text' => 'hello@example.com'], 'design' => ['icon_color' => '{{primary_color}}']]
                            ]],
                            ['id' => 'contact_form', 'width' => '60%', 'modules' => [
                                ['type' => 'form', 'content' => [
                                    'fields' => [
                                        ['type' => 'text', 'name' => 'name', 'label' => 'Your Name', 'placeholder' => 'John Doe', 'required' => true],
                                        ['type' => 'email', 'name' => 'email', 'label' => 'Email Address', 'placeholder' => 'john@example.com', 'required' => true],
                                        ['type' => 'text', 'name' => 'subject', 'label' => 'Subject', 'placeholder' => 'How can we help?'],
                                        ['type' => 'textarea', 'name' => 'message', 'label' => 'Message', 'placeholder' => 'Your message...', 'required' => true]
                                    ],
                                    'submit_text' => 'Send Message'
                                ], 'design' => ['background_color' => '#ffffff', 'padding' => '40px', 'border_radius' => '16px', 'box_shadow' => '0 4px 20px rgba(0,0,0,0.08)']]
                            ]]
                        ]
                    ]
                ]
            ],
            
            'centered_form' => [
                'id' => 'contact_centered',
                'name' => 'Contact',
                'design' => ['background_color' => '#ffffff', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'contact_header',
                        'columns' => [
                            ['id' => 'ch', 'width' => '100%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => 'Contact Us', 'level' => 'h2'], 'design' => ['font_size' => '42px', 'text_align' => 'center', 'margin_bottom' => '16px']],
                                ['type' => 'text', 'content' => ['text' => 'Fill out the form below and we\'ll get back to you within 24 hours.'], 'design' => ['color' => '#64748b', 'text_align' => 'center', 'margin_bottom' => '40px']]
                            ]]
                        ]
                    ],
                    [
                        'id' => 'contact_form_row',
                        'columns' => [
                            ['id' => 'form_col', 'width' => '100%', 'modules' => [
                                ['type' => 'form', 'content' => [
                                    'fields' => [
                                        ['type' => 'text', 'name' => 'name', 'label' => 'Name', 'required' => true],
                                        ['type' => 'email', 'name' => 'email', 'label' => 'Email', 'required' => true],
                                        ['type' => 'textarea', 'name' => 'message', 'label' => 'Message', 'required' => true]
                                    ],
                                    'submit_text' => 'Submit'
                                ], 'design' => ['max_width' => '600px', 'margin' => '0 auto']]
                            ]]
                        ]
                    ]
                ]
            ],
            
            'map_split' => [
                'id' => 'contact_map',
                'name' => 'Contact',
                'design' => ['background_color' => '{{light_color}}', 'padding_top' => '0', 'padding_bottom' => '0'],
                'rows' => [
                    [
                        'id' => 'contact_row',
                        'columns' => [
                            ['id' => 'map_col', 'width' => '50%', 'modules' => [
                                ['type' => 'map', 'content' => ['address' => '123 Business St, City', 'zoom' => 14], 'design' => ['height' => '500px']]
                            ]],
                            ['id' => 'form_col', 'width' => '50%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => 'Send Us a Message', 'level' => 'h2'], 'design' => ['font_size' => '32px', 'margin_bottom' => '24px', 'padding_top' => '60px']],
                                ['type' => 'form', 'content' => [
                                    'fields' => [
                                        ['type' => 'text', 'name' => 'name', 'label' => 'Name', 'required' => true],
                                        ['type' => 'email', 'name' => 'email', 'label' => 'Email', 'required' => true],
                                        ['type' => 'textarea', 'name' => 'message', 'label' => 'Message', 'required' => true]
                                    ],
                                    'submit_text' => 'Send'
                                ], 'design' => ['padding_right' => '60px', 'padding_bottom' => '60px']]
                            ]]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // PRICING SECTION VARIANTS
    // ═══════════════════════════════════════════════════════════════
    
    private static function getPricingVariants(): array
    {
        return [
            'cards_3col' => [
                'id' => 'pricing_3col',
                'name' => 'Pricing',
                'design' => ['background_color' => '{{light_color}}', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'pricing_header',
                        'columns' => [
                            ['id' => 'ph', 'width' => '100%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => 'Simple, Transparent Pricing', 'level' => 'h2'], 'design' => ['font_size' => '42px', 'text_align' => 'center', 'margin_bottom' => '16px']],
                                ['type' => 'text', 'content' => ['text' => 'Choose the plan that\'s right for you'], 'design' => ['color' => '#64748b', 'text_align' => 'center', 'margin_bottom' => '50px']]
                            ]]
                        ]
                    ],
                    [
                        'id' => 'pricing_cards',
                        'columns' => [
                            ['id' => 'p1', 'width' => '33.33%', 'modules' => [
                                ['type' => 'pricing', 'content' => ['title' => 'Starter', 'price' => '29', 'period' => '/month', 'features' => ['5 Projects', '10GB Storage', 'Email Support', 'Basic Analytics'], 'button_text' => 'Get Started', 'button_url' => '#'], 'design' => ['background_color' => '#ffffff', 'padding' => '40px', 'border_radius' => '16px', 'box_shadow' => '0 4px 20px rgba(0,0,0,0.08)']]
                            ]],
                            ['id' => 'p2', 'width' => '33.33%', 'modules' => [
                                ['type' => 'pricing', 'content' => ['title' => 'Professional', 'price' => '79', 'period' => '/month', 'features' => ['Unlimited Projects', '100GB Storage', 'Priority Support', 'Advanced Analytics', 'Custom Domain'], 'button_text' => 'Get Started', 'button_url' => '#', 'featured' => true, 'badge' => 'Most Popular'], 'design' => ['background_color' => '{{primary_color}}', 'text_color' => '#ffffff', 'padding' => '40px', 'border_radius' => '16px', 'transform' => 'scale(1.05)']]
                            ]],
                            ['id' => 'p3', 'width' => '33.33%', 'modules' => [
                                ['type' => 'pricing', 'content' => ['title' => 'Enterprise', 'price' => '199', 'period' => '/month', 'features' => ['Everything in Pro', 'Unlimited Storage', '24/7 Support', 'Custom Integration', 'SLA Guarantee'], 'button_text' => 'Contact Sales', 'button_url' => '#contact'], 'design' => ['background_color' => '#ffffff', 'padding' => '40px', 'border_radius' => '16px', 'box_shadow' => '0 4px 20px rgba(0,0,0,0.08)']]
                            ]]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // TEAM SECTION VARIANTS
    // ═══════════════════════════════════════════════════════════════
    
    private static function getTeamVariants(): array
    {
        return [
            'grid_4col' => [
                'id' => 'team_grid',
                'name' => 'Our Team',
                'design' => ['background_color' => '#ffffff', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'team_header',
                        'columns' => [
                            ['id' => 'th', 'width' => '100%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => 'Meet Our Team', 'level' => 'h2'], 'design' => ['font_size' => '42px', 'text_align' => 'center', 'margin_bottom' => '16px']],
                                ['type' => 'text', 'content' => ['text' => 'The talented people behind our success'], 'design' => ['color' => '#64748b', 'text_align' => 'center', 'margin_bottom' => '50px']]
                            ]]
                        ]
                    ],
                    [
                        'id' => 'team_members',
                        'columns' => [
                            ['id' => 't1', 'width' => '25%', 'modules' => [
                                ['type' => 'image', 'content' => ['src' => 'team-1', 'alt' => 'Team member'], 'design' => ['border_radius' => '12px', 'margin_bottom' => '16px']],
                                ['type' => 'heading', 'content' => ['text' => 'John Smith', 'level' => 'h4'], 'design' => ['font_size' => '20px', 'text_align' => 'center']],
                                ['type' => 'text', 'content' => ['text' => 'CEO & Founder'], 'design' => ['color' => '{{primary_color}}', 'text_align' => 'center']],
                                ['type' => 'social', 'content' => ['links' => ['linkedin' => '#', 'twitter' => '#']], 'design' => ['alignment' => 'center', 'margin_top' => '12px']]
                            ]],
                            ['id' => 't2', 'width' => '25%', 'modules' => [
                                ['type' => 'image', 'content' => ['src' => 'team-2', 'alt' => 'Team member'], 'design' => ['border_radius' => '12px', 'margin_bottom' => '16px']],
                                ['type' => 'heading', 'content' => ['text' => 'Sarah Johnson', 'level' => 'h4'], 'design' => ['font_size' => '20px', 'text_align' => 'center']],
                                ['type' => 'text', 'content' => ['text' => 'Creative Director'], 'design' => ['color' => '{{primary_color}}', 'text_align' => 'center']],
                                ['type' => 'social', 'content' => ['links' => ['linkedin' => '#', 'twitter' => '#']], 'design' => ['alignment' => 'center', 'margin_top' => '12px']]
                            ]],
                            ['id' => 't3', 'width' => '25%', 'modules' => [
                                ['type' => 'image', 'content' => ['src' => 'team-3', 'alt' => 'Team member'], 'design' => ['border_radius' => '12px', 'margin_bottom' => '16px']],
                                ['type' => 'heading', 'content' => ['text' => 'Mike Davis', 'level' => 'h4'], 'design' => ['font_size' => '20px', 'text_align' => 'center']],
                                ['type' => 'text', 'content' => ['text' => 'Lead Developer'], 'design' => ['color' => '{{primary_color}}', 'text_align' => 'center']],
                                ['type' => 'social', 'content' => ['links' => ['linkedin' => '#', 'github' => '#']], 'design' => ['alignment' => 'center', 'margin_top' => '12px']]
                            ]],
                            ['id' => 't4', 'width' => '25%', 'modules' => [
                                ['type' => 'image', 'content' => ['src' => 'team-4', 'alt' => 'Team member'], 'design' => ['border_radius' => '12px', 'margin_bottom' => '16px']],
                                ['type' => 'heading', 'content' => ['text' => 'Emily Brown', 'level' => 'h4'], 'design' => ['font_size' => '20px', 'text_align' => 'center']],
                                ['type' => 'text', 'content' => ['text' => 'Marketing Lead'], 'design' => ['color' => '{{primary_color}}', 'text_align' => 'center']],
                                ['type' => 'social', 'content' => ['links' => ['linkedin' => '#', 'twitter' => '#']], 'design' => ['alignment' => 'center', 'margin_top' => '12px']]
                            ]]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // SERVICES SECTION VARIANTS
    // ═══════════════════════════════════════════════════════════════
    
    private static function getServicesVariants(): array
    {
        return [
            'card_grid' => [
                'id' => 'services_grid',
                'name' => 'Our Services',
                'design' => ['background_color' => '#ffffff', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'services_header',
                        'columns' => [
                            ['id' => 'sh', 'width' => '100%', 'modules' => [
                                ['type' => 'text', 'content' => ['text' => 'What We Do'], 'design' => ['text_color' => '{{primary_color}}', 'font_size' => '14px', 'font_weight' => '600', 'text_transform' => 'uppercase', 'letter_spacing' => '2px', 'text_align' => 'center']],
                                ['type' => 'heading', 'content' => ['text' => 'Our Services', 'level' => 'h2'], 'design' => ['font_size' => '42px', 'text_align' => 'center', 'margin_bottom' => '50px']]
                            ]]
                        ]
                    ],
                    [
                        'id' => 'services_cards',
                        'columns' => [
                            ['id' => 's1', 'width' => '33.33%', 'modules' => [
                                ['type' => 'image', 'content' => ['src' => 'service-1', 'alt' => 'Service 1'], 'design' => ['border_radius' => '12px 12px 0 0', 'height' => '200px', 'object_fit' => 'cover']],
                                ['type' => 'heading', 'content' => ['text' => 'Web Design', 'level' => 'h3'], 'design' => ['font_size' => '24px', 'padding' => '24px 24px 0']],
                                ['type' => 'text', 'content' => ['text' => 'Beautiful, responsive websites that convert visitors into customers.'], 'design' => ['color' => '#64748b', 'padding' => '12px 24px 24px']],
                                ['type' => 'button', 'content' => ['text' => 'Learn More →', 'url' => '#'], 'design' => ['background_color' => 'transparent', 'text_color' => '{{primary_color}}', 'padding' => '0 24px 24px']]
                            ]],
                            ['id' => 's2', 'width' => '33.33%', 'modules' => [
                                ['type' => 'image', 'content' => ['src' => 'service-2', 'alt' => 'Service 2'], 'design' => ['border_radius' => '12px 12px 0 0', 'height' => '200px', 'object_fit' => 'cover']],
                                ['type' => 'heading', 'content' => ['text' => 'Development', 'level' => 'h3'], 'design' => ['font_size' => '24px', 'padding' => '24px 24px 0']],
                                ['type' => 'text', 'content' => ['text' => 'Custom solutions built with modern technologies for performance.'], 'design' => ['color' => '#64748b', 'padding' => '12px 24px 24px']],
                                ['type' => 'button', 'content' => ['text' => 'Learn More →', 'url' => '#'], 'design' => ['background_color' => 'transparent', 'text_color' => '{{primary_color}}', 'padding' => '0 24px 24px']]
                            ]],
                            ['id' => 's3', 'width' => '33.33%', 'modules' => [
                                ['type' => 'image', 'content' => ['src' => 'service-3', 'alt' => 'Service 3'], 'design' => ['border_radius' => '12px 12px 0 0', 'height' => '200px', 'object_fit' => 'cover']],
                                ['type' => 'heading', 'content' => ['text' => 'Marketing', 'level' => 'h3'], 'design' => ['font_size' => '24px', 'padding' => '24px 24px 0']],
                                ['type' => 'text', 'content' => ['text' => 'Strategic digital marketing to grow your online presence.'], 'design' => ['color' => '#64748b', 'padding' => '12px 24px 24px']],
                                ['type' => 'button', 'content' => ['text' => 'Learn More →', 'url' => '#'], 'design' => ['background_color' => 'transparent', 'text_color' => '{{primary_color}}', 'padding' => '0 24px 24px']]
                            ]]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // FAQ SECTION VARIANTS
    // ═══════════════════════════════════════════════════════════════
    
    private static function getFaqVariants(): array
    {
        return [
            'accordion' => [
                'id' => 'faq_accordion',
                'name' => 'FAQ',
                'design' => ['background_color' => '{{light_color}}', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'faq_header',
                        'columns' => [
                            ['id' => 'fh', 'width' => '100%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => 'Frequently Asked Questions', 'level' => 'h2'], 'design' => ['font_size' => '42px', 'text_align' => 'center', 'margin_bottom' => '50px']]
                            ]]
                        ]
                    ],
                    [
                        'id' => 'faq_content',
                        'columns' => [
                            ['id' => 'faq_col', 'width' => '100%', 'modules' => [
                                ['type' => 'accordion', 'content' => [
                                    'items' => [
                                        ['title' => 'What services do you offer?', 'content' => 'We offer a comprehensive range of services including web design, development, and digital marketing solutions tailored to your needs.'],
                                        ['title' => 'How long does a typical project take?', 'content' => 'Project timelines vary based on scope and complexity. A typical website project takes 4-8 weeks from start to finish.'],
                                        ['title' => 'What is your pricing structure?', 'content' => 'We offer flexible pricing options including fixed-price projects and hourly rates. Contact us for a custom quote.'],
                                        ['title' => 'Do you offer ongoing support?', 'content' => 'Yes! We provide ongoing maintenance and support packages to keep your website running smoothly.'],
                                        ['title' => 'How do I get started?', 'content' => 'Simply contact us through our form or give us a call. We\'ll schedule a free consultation to discuss your project.']
                                    ]
                                ], 'design' => ['max_width' => '800px', 'margin' => '0 auto', 'border_color' => '#e5e7eb']]
                            ]]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // GALLERY SECTION VARIANTS
    // ═══════════════════════════════════════════════════════════════
    
    private static function getGalleryVariants(): array
    {
        return [
            'grid' => [
                'id' => 'gallery_grid',
                'name' => 'Gallery',
                'design' => ['background_color' => '#ffffff', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'gallery_header',
                        'columns' => [
                            ['id' => 'gh', 'width' => '100%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => 'Our Work', 'level' => 'h2'], 'design' => ['font_size' => '42px', 'text_align' => 'center', 'margin_bottom' => '50px']]
                            ]]
                        ]
                    ],
                    [
                        'id' => 'gallery_images',
                        'columns' => [
                            ['id' => 'gallery_col', 'width' => '100%', 'modules' => [
                                ['type' => 'gallery', 'content' => ['images' => [
                                    ['src' => 'gallery-1', 'alt' => 'Project 1'],
                                    ['src' => 'gallery-2', 'alt' => 'Project 2'],
                                    ['src' => 'gallery-3', 'alt' => 'Project 3'],
                                    ['src' => 'gallery-4', 'alt' => 'Project 4'],
                                    ['src' => 'gallery-5', 'alt' => 'Project 5'],
                                    ['src' => 'gallery-6', 'alt' => 'Project 6']
                                ], 'lightbox' => true], 'design' => ['columns' => 3, 'gap' => '20px', 'border_radius' => '12px']]
                            ]]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // STATS SECTION VARIANTS
    // ═══════════════════════════════════════════════════════════════
    
    private static function getStatsVariants(): array
    {
        return [
            'counters' => [
                'id' => 'stats_counters',
                'name' => 'Stats',
                'design' => ['background_color' => '{{primary_color}}', 'padding_top' => '80px', 'padding_bottom' => '80px'],
                'rows' => [
                    [
                        'id' => 'stats_row',
                        'columns' => [
                            ['id' => 'st1', 'width' => '25%', 'modules' => [['type' => 'counter', 'content' => ['number' => '500', 'suffix' => '+', 'label' => 'Projects Completed'], 'design' => ['number_color' => '#ffffff', 'number_size' => '56px', 'label_color' => 'rgba(255,255,255,0.8)', 'text_align' => 'center']]]],
                            ['id' => 'st2', 'width' => '25%', 'modules' => [['type' => 'counter', 'content' => ['number' => '50', 'suffix' => '+', 'label' => 'Team Members'], 'design' => ['number_color' => '#ffffff', 'number_size' => '56px', 'label_color' => 'rgba(255,255,255,0.8)', 'text_align' => 'center']]]],
                            ['id' => 'st3', 'width' => '25%', 'modules' => [['type' => 'counter', 'content' => ['number' => '15', 'suffix' => '+', 'label' => 'Years Experience'], 'design' => ['number_color' => '#ffffff', 'number_size' => '56px', 'label_color' => 'rgba(255,255,255,0.8)', 'text_align' => 'center']]]],
                            ['id' => 'st4', 'width' => '25%', 'modules' => [['type' => 'counter', 'content' => ['number' => '99', 'suffix' => '%', 'label' => 'Client Satisfaction'], 'design' => ['number_color' => '#ffffff', 'number_size' => '56px', 'label_color' => 'rgba(255,255,255,0.8)', 'text_align' => 'center']]]]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // BLOG SECTION VARIANTS
    // ═══════════════════════════════════════════════════════════════
    
    private static function getBlogVariants(): array
    {
        return [
            'grid_3col' => [
                'id' => 'blog_grid',
                'name' => 'Latest Articles',
                'design' => ['background_color' => '{{light_color}}', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'blog_header',
                        'columns' => [
                            ['id' => 'bh', 'width' => '100%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => 'Latest from Our Blog', 'level' => 'h2'], 'design' => ['font_size' => '42px', 'text_align' => 'center', 'margin_bottom' => '50px']]
                            ]]
                        ]
                    ],
                    [
                        'id' => 'blog_posts',
                        'columns' => [
                            ['id' => 'blog_col', 'width' => '100%', 'modules' => [
                                ['type' => 'blog', 'content' => ['posts_count' => 3, 'show_image' => true, 'show_excerpt' => true, 'show_date' => true], 'design' => ['columns' => 3, 'gap' => '30px', 'card_style' => 'default']]
                            ]]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // PORTFOLIO SECTION VARIANTS
    // ═══════════════════════════════════════════════════════════════
    
    private static function getPortfolioVariants(): array
    {
        return [
            'filterable' => [
                'id' => 'portfolio_filter',
                'name' => 'Portfolio',
                'design' => ['background_color' => '#ffffff', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'portfolio_header',
                        'columns' => [
                            ['id' => 'ph', 'width' => '100%', 'modules' => [
                                ['type' => 'heading', 'content' => ['text' => 'Our Portfolio', 'level' => 'h2'], 'design' => ['font_size' => '42px', 'text_align' => 'center', 'margin_bottom' => '50px']]
                            ]]
                        ]
                    ],
                    [
                        'id' => 'portfolio_items',
                        'columns' => [
                            ['id' => 'portfolio_col', 'width' => '100%', 'modules' => [
                                ['type' => 'portfolio', 'content' => ['items' => [
                                    ['image' => 'portfolio-1', 'title' => 'Project One', 'category' => 'Web Design'],
                                    ['image' => 'portfolio-2', 'title' => 'Project Two', 'category' => 'Branding'],
                                    ['image' => 'portfolio-3', 'title' => 'Project Three', 'category' => 'Web Design'],
                                    ['image' => 'portfolio-4', 'title' => 'Project Four', 'category' => 'Development'],
                                    ['image' => 'portfolio-5', 'title' => 'Project Five', 'category' => 'Branding'],
                                    ['image' => 'portfolio-6', 'title' => 'Project Six', 'category' => 'Development']
                                ], 'filter' => true], 'design' => ['columns' => 3, 'gap' => '20px', 'hover_effect' => 'overlay']]
                            ]]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    // ═══════════════════════════════════════════════════════════════
    // PARTNERS SECTION VARIANTS
    // ═══════════════════════════════════════════════════════════════
    
    private static function getPartnersVariants(): array
    {
        return [
            'logo_row' => [
                'id' => 'partners_logos',
                'name' => 'Our Partners',
                'design' => ['background_color' => '#ffffff', 'padding_top' => '60px', 'padding_bottom' => '60px'],
                'rows' => [
                    [
                        'id' => 'partners_header',
                        'columns' => [
                            ['id' => 'parh', 'width' => '100%', 'modules' => [
                                ['type' => 'text', 'content' => ['text' => 'Trusted by Industry Leaders'], 'design' => ['color' => '#94a3b8', 'font_size' => '14px', 'text_transform' => 'uppercase', 'letter_spacing' => '2px', 'text_align' => 'center', 'margin_bottom' => '30px']]
                            ]]
                        ]
                    ],
                    [
                        'id' => 'partners_logos_row',
                        'columns' => [
                            ['id' => 'pl1', 'width' => '16.66%', 'modules' => [['type' => 'image', 'content' => ['src' => 'partner-1', 'alt' => 'Partner 1'], 'design' => ['opacity' => '0.6', 'max_height' => '50px', 'filter' => 'grayscale(100%)']]]],
                            ['id' => 'pl2', 'width' => '16.66%', 'modules' => [['type' => 'image', 'content' => ['src' => 'partner-2', 'alt' => 'Partner 2'], 'design' => ['opacity' => '0.6', 'max_height' => '50px', 'filter' => 'grayscale(100%)']]]],
                            ['id' => 'pl3', 'width' => '16.66%', 'modules' => [['type' => 'image', 'content' => ['src' => 'partner-3', 'alt' => 'Partner 3'], 'design' => ['opacity' => '0.6', 'max_height' => '50px', 'filter' => 'grayscale(100%)']]]],
                            ['id' => 'pl4', 'width' => '16.66%', 'modules' => [['type' => 'image', 'content' => ['src' => 'partner-4', 'alt' => 'Partner 4'], 'design' => ['opacity' => '0.6', 'max_height' => '50px', 'filter' => 'grayscale(100%)']]]],
                            ['id' => 'pl5', 'width' => '16.66%', 'modules' => [['type' => 'image', 'content' => ['src' => 'partner-5', 'alt' => 'Partner 5'], 'design' => ['opacity' => '0.6', 'max_height' => '50px', 'filter' => 'grayscale(100%)']]]],
                            ['id' => 'pl6', 'width' => '16.66%', 'modules' => [['type' => 'image', 'content' => ['src' => 'partner-6', 'alt' => 'Partner 6'], 'design' => ['opacity' => '0.6', 'max_height' => '50px', 'filter' => 'grayscale(100%)']]]]
                        ]
                    ]
                ]
            ]
        ];
    }
}
