<?php
/**
 * Layout Installer: SAVOUR - Fine Dining Restaurant
 * 
 * Modern luxury restaurant template with dark theme.
 * 5 pages: Home, About, Menu, Gallery, Contact
 * 
 * Run: Navigate to this file in browser or execute via admin
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/database.php';

// Only allow in DEV_MODE
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Forbidden: DEV_MODE required');
}

$db = \core\Database::connection();

// ============================================================================
// LAYOUT METADATA
// ============================================================================
$layoutData = [
    'name' => 'Savour - Fine Dining',
    'slug' => 'savour-fine-dining',
    'description' => 'Luxurious dark-themed restaurant template with gold accents. Perfect for upscale restaurants, fine dining, wine bars, and exclusive culinary venues. Features elegant hero sections, menu displays, team showcase, and reservation system.',
    'category' => 'restaurant',
    'industry' => 'Restaurant & Hospitality',
    'style' => 'luxury',
    'page_count' => 5,
    'thumbnail' => 'https://images.pexels.com/photos/262978/pexels-photo-262978.jpeg?auto=compress&cs=tinysrgb&w=800',
    'is_premium' => 0,
    'is_ai_generated' => 0,
    'downloads' => 0,
    'rating' => null,
    'created_by' => 1
];

// ============================================================================
// DESIGN SYSTEM
// ============================================================================
$designSystem = [
    'colors' => [
        'primary_dark' => '#0f0f0f',
        'secondary_dark' => '#1a1a1a',
        'accent' => '#d4af37',
        'accent_hover' => '#c9a227',
        'cream' => '#f5f0e8',
        'white' => '#ffffff',
        'text_light' => '#e8e8e8',
        'text_muted' => '#999999',
        'overlay' => 'rgba(15,15,15,0.85)'
    ],
    'typography' => [
        'heading_font' => 'Playfair Display, serif',
        'body_font' => 'Lato, sans-serif',
        'h1_size' => '64px',
        'h2_size' => '48px',
        'h3_size' => '32px',
        'h4_size' => '24px',
        'body_size' => '18px',
        'line_height' => '1.7'
    ],
    'spacing' => [
        'section_padding' => '120px',
        'element_gap' => '40px',
        'container_max' => '1200px'
    ],
    'effects' => [
        'card_shadow' => '0 8px 32px rgba(0,0,0,0.3)',
        'glow' => '0 0 40px rgba(212,175,55,0.15)',
        'card_radius' => '0',
        'button_radius' => '0'
    ]
];

// ============================================================================
// PAGE 1: HOME
// ============================================================================
$homePage = [
    'title' => 'Home',
    'slug' => 'home',
    'is_homepage' => true,
    'status' => 'draft',
    'content' => [
        'sections' => [
            // HERO SECTION
            [
                'id' => 'section_home_hero',
                'name' => 'Hero Section',
                'design' => [
                    'background_color' => '#0f0f0f',
                    'background_image' => 'https://images.pexels.com/photos/1579739/pexels-photo-1579739.jpeg?auto=compress&cs=tinysrgb&w=1920',
                    'background_overlay' => 'rgba(15,15,15,0.75)',
                    'background_size' => 'cover',
                    'background_position' => 'center',
                    'padding_top' => '0',
                    'padding_bottom' => '0',
                    'min_height' => '100vh'
                ],
                'rows' => [
                    [
                        'id' => 'row_home_hero_1',
                        'design' => [
                            'align_items' => 'center',
                            'justify_content' => 'center',
                            'min_height' => '100vh'
                        ],
                        'columns' => [
                            [
                                'id' => 'col_home_hero_1',
                                'width' => '100%',
                                'design' => [
                                    'padding' => '40px'
                                ],
                                'modules' => [
                                    [
                                        'id' => 'mod_home_hero_tagline',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'ESTABLISHED 1985'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '14px',
                                            'letter_spacing' => '6px',
                                            'text_color' => '#d4af37',
                                            'font_weight' => '400',
                                            'margin_bottom' => '24px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_hero_heading',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'SAVOUR',
                                            'level' => 'h1'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '120px',
                                            'font_weight' => '400',
                                            'text_color' => '#ffffff',
                                            'letter_spacing' => '20px',
                                            'margin_bottom' => '16px',
                                            'font_family' => 'Playfair Display, serif'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_hero_subheading',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'Fine Dining & Culinary Excellence'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '20px',
                                            'text_color' => '#e8e8e8',
                                            'letter_spacing' => '4px',
                                            'margin_bottom' => '48px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_hero_divider',
                                        'type' => 'divider',
                                        'content' => [
                                            'show_divider' => true
                                        ],
                                        'design' => [
                                            'color' => '#d4af37',
                                            'style' => 'solid',
                                            'weight' => '1px',
                                            'width' => '80px',
                                            'margin' => '0 auto 48px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_hero_cta',
                                        'type' => 'button',
                                        'content' => [
                                            'text' => 'RESERVE A TABLE',
                                            'url' => '/contact',
                                            'target' => '_self'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'background_color' => 'transparent',
                                            'text_color' => '#d4af37',
                                            'border' => '1px solid #d4af37',
                                            'padding' => '18px 48px',
                                            'border_radius' => '0',
                                            'font_size' => '14px',
                                            'letter_spacing' => '3px',
                                            'font_weight' => '400'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // INTRODUCTION SECTION
            [
                'id' => 'section_home_intro',
                'name' => 'Introduction',
                'design' => [
                    'background_color' => '#0f0f0f',
                    'padding_top' => '120px',
                    'padding_bottom' => '120px'
                ],
                'rows' => [
                    [
                        'id' => 'row_home_intro_1',
                        'columns' => [
                            [
                                'id' => 'col_home_intro_1',
                                'width' => '100%',
                                'design' => [
                                    'max_width' => '800px',
                                    'margin' => '0 auto',
                                    'padding' => '0 20px'
                                ],
                                'modules' => [
                                    [
                                        'id' => 'mod_home_intro_tagline',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'WELCOME'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '12px',
                                            'letter_spacing' => '4px',
                                            'text_color' => '#d4af37',
                                            'margin_bottom' => '20px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_intro_heading',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'Where Every Dish Tells a Story',
                                            'level' => 'h2'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '42px',
                                            'font_weight' => '400',
                                            'text_color' => '#ffffff',
                                            'margin_bottom' => '32px',
                                            'font_family' => 'Playfair Display, serif'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_intro_text',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'At Savour, we believe dining is more than a meal — it is an experience that engages all senses. Our award-winning chefs craft each dish with locally-sourced ingredients, bringing together traditional techniques and modern innovation to create unforgettable culinary moments.'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '18px',
                                            'line_height' => '1.8',
                                            'text_color' => '#999999',
                                            'margin_bottom' => '40px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_intro_btn',
                                        'type' => 'button',
                                        'content' => [
                                            'text' => 'DISCOVER OUR STORY',
                                            'url' => '/about',
                                            'target' => '_self'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'background_color' => 'transparent',
                                            'text_color' => '#d4af37',
                                            'border' => '1px solid #d4af37',
                                            'padding' => '14px 36px',
                                            'border_radius' => '0',
                                            'font_size' => '12px',
                                            'letter_spacing' => '2px'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // FEATURES SECTION
            [
                'id' => 'section_home_features',
                'name' => 'Features',
                'design' => [
                    'background_color' => '#1a1a1a',
                    'padding_top' => '100px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'row_home_features_1',
                        'design' => [
                            'max_width' => '1200px',
                            'margin' => '0 auto',
                            'gap' => '60px'
                        ],
                        'columns' => [
                            [
                                'id' => 'col_home_feature_1',
                                'width' => '25%',
                                'modules' => [
                                    [
                                        'id' => 'mod_home_feature_1',
                                        'type' => 'blurb',
                                        'content' => [
                                            'icon' => 'fas fa-award',
                                            'use_image' => false,
                                            'title' => 'Award Winning',
                                            'text' => 'Michelin-starred excellence recognized by culinary critics worldwide.',
                                            'url' => ''
                                        ],
                                        'design' => [
                                            'alignment' => 'center',
                                            'layout' => 'top',
                                            'icon_size' => '48px',
                                            'icon_color' => '#d4af37',
                                            'title_font_size' => '20px',
                                            'title_color' => '#ffffff',
                                            'text_font_size' => '15px',
                                            'text_color' => '#999999'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'col_home_feature_2',
                                'width' => '25%',
                                'modules' => [
                                    [
                                        'id' => 'mod_home_feature_2',
                                        'type' => 'blurb',
                                        'content' => [
                                            'icon' => 'fas fa-leaf',
                                            'use_image' => false,
                                            'title' => 'Farm to Table',
                                            'text' => 'Fresh, locally-sourced ingredients delivered daily from trusted farms.',
                                            'url' => ''
                                        ],
                                        'design' => [
                                            'alignment' => 'center',
                                            'layout' => 'top',
                                            'icon_size' => '48px',
                                            'icon_color' => '#d4af37',
                                            'title_font_size' => '20px',
                                            'title_color' => '#ffffff',
                                            'text_font_size' => '15px',
                                            'text_color' => '#999999'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'col_home_feature_3',
                                'width' => '25%',
                                'modules' => [
                                    [
                                        'id' => 'mod_home_feature_3',
                                        'type' => 'blurb',
                                        'content' => [
                                            'icon' => 'fas fa-wine-glass-alt',
                                            'use_image' => false,
                                            'title' => 'Wine Cellar',
                                            'text' => 'Over 500 labels curated by our expert sommelier team.',
                                            'url' => ''
                                        ],
                                        'design' => [
                                            'alignment' => 'center',
                                            'layout' => 'top',
                                            'icon_size' => '48px',
                                            'icon_color' => '#d4af37',
                                            'title_font_size' => '20px',
                                            'title_color' => '#ffffff',
                                            'text_font_size' => '15px',
                                            'text_color' => '#999999'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'col_home_feature_4',
                                'width' => '25%',
                                'modules' => [
                                    [
                                        'id' => 'mod_home_feature_4',
                                        'type' => 'blurb',
                                        'content' => [
                                            'icon' => 'fas fa-concierge-bell',
                                            'use_image' => false,
                                            'title' => 'Private Dining',
                                            'text' => 'Exclusive rooms for intimate gatherings and special celebrations.',
                                            'url' => ''
                                        ],
                                        'design' => [
                                            'alignment' => 'center',
                                            'layout' => 'top',
                                            'icon_size' => '48px',
                                            'icon_color' => '#d4af37',
                                            'title_font_size' => '20px',
                                            'title_color' => '#ffffff',
                                            'text_font_size' => '15px',
                                            'text_color' => '#999999'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // MENU PREVIEW SECTION
            [
                'id' => 'section_home_menu',
                'name' => 'Menu Preview',
                'design' => [
                    'background_color' => '#0f0f0f',
                    'padding_top' => '120px',
                    'padding_bottom' => '120px'
                ],
                'rows' => [
                    // Header
                    [
                        'id' => 'row_home_menu_header',
                        'columns' => [
                            [
                                'id' => 'col_home_menu_header',
                                'width' => '100%',
                                'modules' => [
                                    [
                                        'id' => 'mod_home_menu_tagline',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'CULINARY EXCELLENCE'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '12px',
                                            'letter_spacing' => '4px',
                                            'text_color' => '#d4af37',
                                            'margin_bottom' => '20px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_menu_heading',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'Signature Dishes',
                                            'level' => 'h2'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '48px',
                                            'font_weight' => '400',
                                            'text_color' => '#ffffff',
                                            'margin_bottom' => '60px',
                                            'font_family' => 'Playfair Display, serif'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    // Menu Items
                    [
                        'id' => 'row_home_menu_items',
                        'design' => [
                            'max_width' => '1200px',
                            'margin' => '0 auto',
                            'gap' => '40px'
                        ],
                        'columns' => [
                            [
                                'id' => 'col_home_menu_item_1',
                                'width' => '33.33%',
                                'modules' => [
                                    [
                                        'id' => 'mod_home_menu_item_1_img',
                                        'type' => 'image',
                                        'content' => [
                                            'src' => 'https://images.pexels.com/photos/3535383/pexels-photo-3535383.jpeg?auto=compress&cs=tinysrgb&w=600',
                                            'alt' => 'Wagyu Beef Tenderloin'
                                        ],
                                        'design' => [
                                            'width' => '100%',
                                            'height' => '280px',
                                            'object_fit' => 'cover',
                                            'margin_bottom' => '24px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_menu_item_1_title',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'Wagyu Beef Tenderloin',
                                            'level' => 'h3'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '22px',
                                            'text_color' => '#ffffff',
                                            'font_weight' => '400',
                                            'margin_bottom' => '12px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_menu_item_1_desc',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'A5 Japanese wagyu, truffle jus, seasonal vegetables'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '14px',
                                            'text_color' => '#999999',
                                            'margin_bottom' => '16px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_menu_item_1_price',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => '$125'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '20px',
                                            'text_color' => '#d4af37'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'col_home_menu_item_2',
                                'width' => '33.33%',
                                'modules' => [
                                    [
                                        'id' => 'mod_home_menu_item_2_img',
                                        'type' => 'image',
                                        'content' => [
                                            'src' => 'https://images.pexels.com/photos/699953/pexels-photo-699953.jpeg?auto=compress&cs=tinysrgb&w=600',
                                            'alt' => 'Maine Lobster'
                                        ],
                                        'design' => [
                                            'width' => '100%',
                                            'height' => '280px',
                                            'object_fit' => 'cover',
                                            'margin_bottom' => '24px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_menu_item_2_title',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'Maine Lobster',
                                            'level' => 'h3'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '22px',
                                            'text_color' => '#ffffff',
                                            'font_weight' => '400',
                                            'margin_bottom' => '12px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_menu_item_2_desc',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'Butter-poached lobster, champagne beurre blanc'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '14px',
                                            'text_color' => '#999999',
                                            'margin_bottom' => '16px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_menu_item_2_price',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => '$95'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '20px',
                                            'text_color' => '#d4af37'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'col_home_menu_item_3',
                                'width' => '33.33%',
                                'modules' => [
                                    [
                                        'id' => 'mod_home_menu_item_3_img',
                                        'type' => 'image',
                                        'content' => [
                                            'src' => 'https://images.pexels.com/photos/1435904/pexels-photo-1435904.jpeg?auto=compress&cs=tinysrgb&w=600',
                                            'alt' => 'Truffle Risotto'
                                        ],
                                        'design' => [
                                            'width' => '100%',
                                            'height' => '280px',
                                            'object_fit' => 'cover',
                                            'margin_bottom' => '24px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_menu_item_3_title',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'Black Truffle Risotto',
                                            'level' => 'h3'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '22px',
                                            'text_color' => '#ffffff',
                                            'font_weight' => '400',
                                            'margin_bottom' => '12px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_menu_item_3_desc',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'Carnaroli rice, Périgord truffle, aged parmesan'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '14px',
                                            'text_color' => '#999999',
                                            'margin_bottom' => '16px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_menu_item_3_price',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => '$68'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '20px',
                                            'text_color' => '#d4af37'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    // CTA
                    [
                        'id' => 'row_home_menu_cta',
                        'design' => [
                            'margin_top' => '60px'
                        ],
                        'columns' => [
                            [
                                'id' => 'col_home_menu_cta',
                                'width' => '100%',
                                'modules' => [
                                    [
                                        'id' => 'mod_home_menu_btn',
                                        'type' => 'button',
                                        'content' => [
                                            'text' => 'VIEW FULL MENU',
                                            'url' => '/menu',
                                            'target' => '_self'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'background_color' => '#d4af37',
                                            'text_color' => '#0f0f0f',
                                            'padding' => '16px 40px',
                                            'border_radius' => '0',
                                            'font_size' => '13px',
                                            'letter_spacing' => '2px',
                                            'font_weight' => '600'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // TESTIMONIALS SECTION
            [
                'id' => 'section_home_testimonials',
                'name' => 'Testimonials',
                'design' => [
                    'background_color' => '#1a1a1a',
                    'background_image' => 'https://images.pexels.com/photos/260922/pexels-photo-260922.jpeg?auto=compress&cs=tinysrgb&w=1920',
                    'background_overlay' => 'rgba(26,26,26,0.92)',
                    'padding_top' => '120px',
                    'padding_bottom' => '120px'
                ],
                'rows' => [
                    [
                        'id' => 'row_home_testimonials_1',
                        'design' => [
                            'max_width' => '900px',
                            'margin' => '0 auto'
                        ],
                        'columns' => [
                            [
                                'id' => 'col_home_testimonials_1',
                                'width' => '100%',
                                'modules' => [
                                    [
                                        'id' => 'mod_home_testimonial_icon',
                                        'type' => 'icon',
                                        'content' => [
                                            'icon' => 'fas fa-quote-left',
                                            'size' => '40px'
                                        ],
                                        'design' => [
                                            'color' => '#d4af37',
                                            'text_align' => 'center',
                                            'margin_bottom' => '32px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_testimonial_quote',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'An extraordinary dining experience that transcends the ordinary. Every course was a masterpiece, and the attention to detail was impeccable. Savour has earned its place among the world\'s finest restaurants.'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '26px',
                                            'line_height' => '1.7',
                                            'text_color' => '#ffffff',
                                            'font_family' => 'Playfair Display, serif',
                                            'font_style' => 'italic',
                                            'margin_bottom' => '40px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_testimonial_author',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => '— The New York Times Food Review'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '14px',
                                            'letter_spacing' => '2px',
                                            'text_color' => '#d4af37'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // RESERVATION CTA SECTION
            [
                'id' => 'section_home_cta',
                'name' => 'Reservation CTA',
                'design' => [
                    'background_color' => '#0f0f0f',
                    'padding_top' => '100px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'row_home_cta_1',
                        'columns' => [
                            [
                                'id' => 'col_home_cta_1',
                                'width' => '100%',
                                'design' => [
                                    'max_width' => '700px',
                                    'margin' => '0 auto'
                                ],
                                'modules' => [
                                    [
                                        'id' => 'mod_home_cta_heading',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'Reserve Your Experience',
                                            'level' => 'h2'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '42px',
                                            'font_weight' => '400',
                                            'text_color' => '#ffffff',
                                            'margin_bottom' => '20px',
                                            'font_family' => 'Playfair Display, serif'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_cta_text',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'Join us for an unforgettable evening of culinary excellence. Reservations recommended.'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '18px',
                                            'text_color' => '#999999',
                                            'margin_bottom' => '40px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_home_cta_btn',
                                        'type' => 'button',
                                        'content' => [
                                            'text' => 'MAKE A RESERVATION',
                                            'url' => '/contact',
                                            'target' => '_self'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'background_color' => '#d4af37',
                                            'text_color' => '#0f0f0f',
                                            'padding' => '18px 48px',
                                            'border_radius' => '0',
                                            'font_size' => '14px',
                                            'letter_spacing' => '3px',
                                            'font_weight' => '600'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];
