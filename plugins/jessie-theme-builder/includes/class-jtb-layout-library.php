<?php
/**
 * Layout Library - Professional Premade Layouts
 *
 * ALL styling through attrs (editable in settings panel).
 * NO hardcoded inline styles in content.
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Layout_Library
{
    private static int $idCounter = 0;

    // =====================================================
    // DESIGN TOKENS
    // =====================================================

    private const PRIMARY = '#6366f1';
    private const PRIMARY_DARK = '#4f46e5';
    private const SECONDARY = '#0ea5e9';
    private const DARK = '#1e293b';
    private const DARKER = '#0f172a';
    private const GRAY = '#64748b';
    private const LIGHT_GRAY = '#94a3b8';
    private const LIGHT = '#f1f5f9';
    private const WHITE = '#ffffff';

    private static function id(): string
    {
        return 'jtb_' . (++self::$idCounter) . '_' . substr(md5(uniqid()), 0, 6);
    }

    // =====================================================
    // PUBLIC API
    // =====================================================

    public static function getLayouts(): array
    {
        return [
            'pages' => self::getPageLayouts(),
            'sections' => self::getSectionLayouts(),
        ];
    }

    public static function getThemeBuilderLayouts(): array
    {
        return [
            'headers' => [['id' => 'header-default', 'name' => 'Default Header', 'content' => ['version' => '1.0', 'content' => []]]],
            'footers' => [['id' => 'footer-default', 'name' => 'Default Footer', 'content' => ['version' => '1.0', 'content' => []]]],
            'body' => [['id' => 'body-default', 'name' => 'Default Page', 'content' => ['version' => '1.0', 'content' => []]]],
        ];
    }

    public static function getCategories(): array
    {
        return [
            'pages' => [
                ['id' => 'all', 'name' => 'All Pages', 'slug' => 'all'],
                ['id' => 'business', 'name' => 'Business', 'slug' => 'business'],
            ],
            'sections' => [
                ['id' => 'hero', 'name' => 'Hero', 'slug' => 'hero'],
                ['id' => 'features', 'name' => 'Features', 'slug' => 'features'],
                ['id' => 'cta', 'name' => 'Call to Action', 'slug' => 'cta'],
                ['id' => 'testimonials', 'name' => 'Testimonials', 'slug' => 'testimonials'],
                ['id' => 'pricing', 'name' => 'Pricing', 'slug' => 'pricing'],
            ],
        ];
    }

    // =====================================================
    // PAGE LAYOUTS
    // =====================================================

    public static function getPageLayouts(): array
    {
        return [
            [
                'id' => 'page-agency',
                'name' => 'Agency Home',
                'category' => 'business',
                'thumbnail' => 'https://images.pexels.com/photos/3184291/pexels-photo-3184291.jpeg?auto=compress&cs=tinysrgb&w=400',
                'content' => self::agencyPage(),
            ],
            [
                'id' => 'page-saas',
                'name' => 'SaaS Landing',
                'category' => 'business',
                'thumbnail' => 'https://images.pexels.com/photos/3182812/pexels-photo-3182812.jpeg?auto=compress&cs=tinysrgb&w=400',
                'content' => self::saasPage(),
            ],
            [
                'id' => 'page-portfolio',
                'name' => 'Portfolio',
                'category' => 'business',
                'thumbnail' => 'https://images.pexels.com/photos/3184306/pexels-photo-3184306.jpeg?auto=compress&cs=tinysrgb&w=400',
                'content' => self::portfolioPage(),
            ],
            [
                'id' => 'page-about',
                'name' => 'About Us',
                'category' => 'business',
                'thumbnail' => 'https://images.pexels.com/photos/3184339/pexels-photo-3184339.jpeg?auto=compress&cs=tinysrgb&w=400',
                'content' => self::aboutPage(),
            ],
            [
                'id' => 'page-contact',
                'name' => 'Contact',
                'category' => 'business',
                'thumbnail' => 'https://images.pexels.com/photos/3184418/pexels-photo-3184418.jpeg?auto=compress&cs=tinysrgb&w=400',
                'content' => self::contactPage(),
            ],
        ];
    }

    public static function getSectionLayouts(): array
    {
        return [
            ['id' => 'section-hero-gradient', 'name' => 'Hero Gradient', 'category' => 'hero', 'content' => self::heroGradient()],
            ['id' => 'section-hero-split', 'name' => 'Hero Split', 'category' => 'hero', 'content' => self::heroSplit()],
            ['id' => 'section-features', 'name' => 'Features Grid', 'category' => 'features', 'content' => self::featuresGrid()],
            ['id' => 'section-testimonials', 'name' => 'Testimonials', 'category' => 'testimonials', 'content' => self::testimonials()],
            ['id' => 'section-pricing', 'name' => 'Pricing', 'category' => 'pricing', 'content' => self::pricing()],
            ['id' => 'section-cta', 'name' => 'Call to Action', 'category' => 'cta', 'content' => self::ctaSection()],
        ];
    }

    // =====================================================
    // PAGES
    // =====================================================

    private static function agencyPage(): array
    {
        return ['version' => '1.0', 'content' => [
            self::heroGradient(),
            self::featuresGrid(),
            self::testimonials(),
            self::ctaSection(),
        ]];
    }

    private static function saasPage(): array
    {
        return ['version' => '1.0', 'content' => [
            self::heroSplit(),
            self::featuresGrid(),
            self::pricing(),
            self::ctaSection(),
        ]];
    }

    private static function portfolioPage(): array
    {
        return ['version' => '1.0', 'content' => [
            // Hero
            [
                'type' => 'section',
                'id' => self::id(),
                'attrs' => [
                    'background_color' => self::WHITE,
                    'padding' => ['top' => 100, 'right' => 20, 'bottom' => 100, 'left' => 20],
                ],
                'children' => [[
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1_1', 'max_width' => 800],
                    'children' => [[
                        'type' => 'column',
                        'id' => self::id(),
                        'attrs' => [],
                        'children' => [
                            [
                                'type' => 'heading',
                                'id' => self::id(),
                                'attrs' => [
                                    'text' => 'Our Work',
                                    'level' => 'h1',
                                    'font_size' => 56,
                                    'font_weight' => '800',
                                    'text_color' => self::DARK,
                                    'text_align' => 'center',
                                    'margin' => ['bottom' => 24],
                                ],
                                'children' => [],
                            ],
                            [
                                'type' => 'text',
                                'id' => self::id(),
                                'attrs' => [
                                    'content' => '<p>Explore our latest projects and see how we help businesses transform their digital presence.</p>',
                                    'font_size' => 20,
                                    'text_color' => self::GRAY,
                                    'line_height' => 1.7,
                                    'text_align' => 'center',
                                ],
                                'children' => [],
                            ],
                        ],
                    ]],
                ]],
            ],
            // Gallery
            [
                'type' => 'section',
                'id' => self::id(),
                'attrs' => [
                    'background_color' => self::LIGHT,
                    'padding' => ['top' => 80, 'right' => 20, 'bottom' => 80, 'left' => 20],
                ],
                'children' => [[
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1_1'],
                    'children' => [[
                        'type' => 'column',
                        'id' => self::id(),
                        'attrs' => [],
                        'children' => [[
                            'type' => 'gallery',
                            'id' => self::id(),
                            'attrs' => [
                                'gallery_images' => [
                                    ['url' => 'https://images.pexels.com/photos/3184465/pexels-photo-3184465.jpeg?auto=compress&cs=tinysrgb&w=600'],
                                    ['url' => 'https://images.pexels.com/photos/3184306/pexels-photo-3184306.jpeg?auto=compress&cs=tinysrgb&w=600'],
                                    ['url' => 'https://images.pexels.com/photos/3184339/pexels-photo-3184339.jpeg?auto=compress&cs=tinysrgb&w=600'],
                                    ['url' => 'https://images.pexels.com/photos/3184418/pexels-photo-3184418.jpeg?auto=compress&cs=tinysrgb&w=600'],
                                    ['url' => 'https://images.pexels.com/photos/3184291/pexels-photo-3184291.jpeg?auto=compress&cs=tinysrgb&w=600'],
                                    ['url' => 'https://images.pexels.com/photos/3182812/pexels-photo-3182812.jpeg?auto=compress&cs=tinysrgb&w=600'],
                                ],
                                'columns' => 3,
                                'gap' => 24,
                            ],
                            'children' => [],
                        ]],
                    ]],
                ]],
            ],
            self::ctaSection(),
        ]];
    }

    private static function aboutPage(): array
    {
        return ['version' => '1.0', 'content' => [
            // Hero
            [
                'type' => 'section',
                'id' => self::id(),
                'attrs' => [
                    'background_color' => self::DARKER,
                    'padding' => ['top' => 120, 'right' => 20, 'bottom' => 120, 'left' => 20],
                ],
                'children' => [[
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1_1', 'max_width' => 900],
                    'children' => [[
                        'type' => 'column',
                        'id' => self::id(),
                        'attrs' => [],
                        'children' => [
                            [
                                'type' => 'heading',
                                'id' => self::id(),
                                'attrs' => [
                                    'text' => 'About Our Company',
                                    'level' => 'h1',
                                    'font_size' => 56,
                                    'font_weight' => '800',
                                    'text_color' => self::WHITE,
                                    'text_align' => 'center',
                                    'margin' => ['bottom' => 24],
                                ],
                                'children' => [],
                            ],
                            [
                                'type' => 'text',
                                'id' => self::id(),
                                'attrs' => [
                                    'content' => '<p>We are a team of passionate designers and developers dedicated to creating exceptional digital experiences.</p>',
                                    'font_size' => 20,
                                    'text_color' => self::LIGHT_GRAY,
                                    'line_height' => 1.7,
                                    'text_align' => 'center',
                                ],
                                'children' => [],
                            ],
                        ],
                    ]],
                ]],
            ],
            // Story
            [
                'type' => 'section',
                'id' => self::id(),
                'attrs' => [
                    'background_color' => self::WHITE,
                    'padding' => ['top' => 100, 'right' => 20, 'bottom' => 100, 'left' => 20],
                ],
                'children' => [[
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1_2,1_2', 'column_gap' => 60],
                    'children' => [
                        [
                            'type' => 'column',
                            'id' => self::id(),
                            'attrs' => [],
                            'children' => [[
                                'type' => 'image',
                                'id' => self::id(),
                                'attrs' => [
                                    'src' => 'https://images.pexels.com/photos/3184291/pexels-photo-3184291.jpeg?auto=compress&cs=tinysrgb&w=800',
                                    'alt' => 'Our Story',
                                    'border_radius' => 16,
                                ],
                                'children' => [],
                            ]],
                        ],
                        [
                            'type' => 'column',
                            'id' => self::id(),
                            'attrs' => [],
                            'children' => [
                                [
                                    'type' => 'heading',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'text' => 'Building Digital Excellence Since 2015',
                                        'level' => 'h2',
                                        'font_size' => 40,
                                        'font_weight' => '700',
                                        'text_color' => self::DARK,
                                        'margin' => ['bottom' => 24],
                                        'line_height' => 1.2,
                                    ],
                                    'children' => [],
                                ],
                                [
                                    'type' => 'text',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'content' => '<p>What started as a small design studio has grown into a full-service digital agency. We combine creativity with technology to deliver solutions that drive real results for our clients.</p><p>Our team of 25+ experts works across design, development, and digital marketing to bring ambitious ideas to life.</p>',
                                        'font_size' => 17,
                                        'text_color' => self::GRAY,
                                        'line_height' => 1.8,
                                    ],
                                    'children' => [],
                                ],
                            ],
                        ],
                    ],
                ]],
            ],
            // Stats
            [
                'type' => 'section',
                'id' => self::id(),
                'attrs' => [
                    'background_color' => self::LIGHT,
                    'padding' => ['top' => 60, 'right' => 20, 'bottom' => 60, 'left' => 20],
                ],
                'children' => [[
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1_4,1_4,1_4,1_4', 'column_gap' => 30],
                    'children' => [
                        self::statCol('250+', 'Projects'),
                        self::statCol('50+', 'Clients'),
                        self::statCol('15+', 'Awards'),
                        self::statCol('8+', 'Years'),
                    ],
                ]],
            ],
            self::ctaSection(),
        ]];
    }

    private static function statCol(string $num, string $label): array
    {
        return [
            'type' => 'column',
            'id' => self::id(),
            'attrs' => [],
            'children' => [
                [
                    'type' => 'heading',
                    'id' => self::id(),
                    'attrs' => [
                        'text' => $num,
                        'level' => 'h3',
                        'font_size' => 48,
                        'font_weight' => '800',
                        'text_color' => self::PRIMARY,
                        'text_align' => 'center',
                        'margin' => ['bottom' => 8],
                    ],
                    'children' => [],
                ],
                [
                    'type' => 'text',
                    'id' => self::id(),
                    'attrs' => [
                        'content' => '<p>' . $label . '</p>',
                        'font_size' => 16,
                        'text_color' => self::GRAY,
                        'text_align' => 'center',
                    ],
                    'children' => [],
                ],
            ],
        ];
    }

    private static function contactPage(): array
    {
        return ['version' => '1.0', 'content' => [
            // Hero
            [
                'type' => 'section',
                'id' => self::id(),
                'attrs' => [
                    'background_color' => self::PRIMARY,
                    'padding' => ['top' => 100, 'right' => 20, 'bottom' => 100, 'left' => 20],
                ],
                'children' => [[
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1_1', 'max_width' => 700],
                    'children' => [[
                        'type' => 'column',
                        'id' => self::id(),
                        'attrs' => [],
                        'children' => [
                            [
                                'type' => 'heading',
                                'id' => self::id(),
                                'attrs' => [
                                    'text' => 'Get in Touch',
                                    'level' => 'h1',
                                    'font_size' => 56,
                                    'font_weight' => '800',
                                    'text_color' => self::WHITE,
                                    'text_align' => 'center',
                                    'margin' => ['bottom' => 20],
                                ],
                                'children' => [],
                            ],
                            [
                                'type' => 'text',
                                'id' => self::id(),
                                'attrs' => [
                                    'content' => '<p>Have a project in mind? We\'d love to hear from you.</p>',
                                    'font_size' => 20,
                                    'text_color' => self::WHITE,
                                    'text_align' => 'center',
                                ],
                                'children' => [],
                            ],
                        ],
                    ]],
                ]],
            ],
            // Contact Form
            [
                'type' => 'section',
                'id' => self::id(),
                'attrs' => [
                    'background_color' => self::WHITE,
                    'padding' => ['top' => 100, 'right' => 20, 'bottom' => 100, 'left' => 20],
                ],
                'children' => [[
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1_2,1_2', 'column_gap' => 80],
                    'children' => [
                        // Info
                        [
                            'type' => 'column',
                            'id' => self::id(),
                            'attrs' => [],
                            'children' => [
                                [
                                    'type' => 'heading',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'text' => 'Contact Information',
                                        'level' => 'h3',
                                        'font_size' => 28,
                                        'font_weight' => '700',
                                        'text_color' => self::DARK,
                                        'margin' => ['bottom' => 24],
                                    ],
                                    'children' => [],
                                ],
                                [
                                    'type' => 'blurb',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'title' => 'Email Us',
                                        'content' => '<p>hello@agency.com</p>',
                                        'icon' => 'mail',
                                        'icon_color' => self::PRIMARY,
                                        'title_font_size' => 18,
                                        'title_font_weight' => '700',
                                        'title_color' => self::DARK,
                                    ],
                                    'children' => [],
                                ],
                                [
                                    'type' => 'blurb',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'title' => 'Call Us',
                                        'content' => '<p>+1 (555) 123-4567</p>',
                                        'icon' => 'phone',
                                        'icon_color' => self::PRIMARY,
                                        'title_font_size' => 18,
                                        'title_font_weight' => '700',
                                        'title_color' => self::DARK,
                                    ],
                                    'children' => [],
                                ],
                                [
                                    'type' => 'blurb',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'title' => 'Visit Us',
                                        'content' => '<p>123 Design Street, Creative City</p>',
                                        'icon' => 'map-pin',
                                        'icon_color' => self::PRIMARY,
                                        'title_font_size' => 18,
                                        'title_font_weight' => '700',
                                        'title_color' => self::DARK,
                                    ],
                                    'children' => [],
                                ],
                            ],
                        ],
                        // Form
                        [
                            'type' => 'column',
                            'id' => self::id(),
                            'attrs' => [
                                'background_color' => self::LIGHT,
                                'padding' => ['top' => 40, 'right' => 40, 'bottom' => 40, 'left' => 40],
                                'border_radius' => 20,
                            ],
                            'children' => [
                                [
                                    'type' => 'heading',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'text' => 'Send a Message',
                                        'level' => 'h3',
                                        'font_size' => 24,
                                        'font_weight' => '700',
                                        'text_color' => self::DARK,
                                        'margin' => ['bottom' => 24],
                                    ],
                                    'children' => [],
                                ],
                                [
                                    'type' => 'contact_form',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'email' => 'contact@example.com',
                                        'button_text' => 'Send Message',
                                        'button_background' => self::PRIMARY,
                                        'button_text_color' => self::WHITE,
                                    ],
                                    'children' => [],
                                ],
                            ],
                        ],
                    ],
                ]],
            ],
        ]];
    }

    // =====================================================
    // SECTION: HERO GRADIENT
    // =====================================================

    private static function heroGradient(): array
    {
        return [
            'type' => 'section',
            'id' => self::id(),
            'attrs' => [
                'background_type' => 'gradient',
                'background_gradient' => 'linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)',
                'padding' => ['top' => 140, 'right' => 20, 'bottom' => 140, 'left' => 20],
            ],
            'children' => [[
                'type' => 'row',
                'id' => self::id(),
                'attrs' => ['columns' => '1_1', 'max_width' => 900],
                'children' => [[
                    'type' => 'column',
                    'id' => self::id(),
                    'attrs' => [],
                    'children' => [
                        [
                            'type' => 'heading',
                            'id' => self::id(),
                            'attrs' => [
                                'text' => 'We Build Digital Experiences That Matter',
                                'level' => 'h1',
                                'font_size' => 64,
                                'font_size_tablet' => 48,
                                'font_size_phone' => 36,
                                'font_weight' => '800',
                                'text_color' => self::WHITE,
                                'text_align' => 'center',
                                'line_height' => 1.1,
                                'margin' => ['bottom' => 28],
                            ],
                            'children' => [],
                        ],
                        [
                            'type' => 'text',
                            'id' => self::id(),
                            'attrs' => [
                                'content' => '<p>Transform your business with cutting-edge design and development. We create stunning websites and applications that drive results and delight users.</p>',
                                'font_size' => 20,
                                'text_color' => 'rgba(255, 255, 255, 0.9)',
                                'line_height' => 1.7,
                                'text_align' => 'center',
                                'margin' => ['bottom' => 40],
                            ],
                            'children' => [],
                        ],
                        [
                            'type' => 'button',
                            'id' => self::id(),
                            'attrs' => [
                                'text' => 'Start Your Project',
                                'link_url' => '#contact',
                                'background_color' => self::WHITE,
                                'text_color' => self::PRIMARY,
                                'font_size' => 17,
                                'font_weight' => '600',
                                'padding' => ['top' => 18, 'right' => 40, 'bottom' => 18, 'left' => 40],
                                'border_radius' => 50,
                                'alignment' => 'center',
                            ],
                            'children' => [],
                        ],
                    ],
                ]],
            ]],
        ];
    }

    // =====================================================
    // SECTION: HERO SPLIT
    // =====================================================

    private static function heroSplit(): array
    {
        return [
            'type' => 'section',
            'id' => self::id(),
            'attrs' => [
                'background_color' => self::WHITE,
                'padding' => ['top' => 100, 'right' => 20, 'bottom' => 100, 'left' => 20],
            ],
            'children' => [[
                'type' => 'row',
                'id' => self::id(),
                'attrs' => ['columns' => '1_2,1_2', 'column_gap' => 80],
                'children' => [
                    [
                        'type' => 'column',
                        'id' => self::id(),
                        'attrs' => [],
                        'children' => [
                            [
                                'type' => 'heading',
                                'id' => self::id(),
                                'attrs' => [
                                    'text' => 'Powerful Software for Modern Teams',
                                    'level' => 'h1',
                                    'font_size' => 52,
                                    'font_weight' => '800',
                                    'text_color' => self::DARK,
                                    'line_height' => 1.15,
                                    'margin' => ['bottom' => 24],
                                ],
                                'children' => [],
                            ],
                            [
                                'type' => 'text',
                                'id' => self::id(),
                                'attrs' => [
                                    'content' => '<p>Streamline your workflow, collaborate seamlessly, and achieve more with our all-in-one platform designed for growth-focused teams.</p>',
                                    'font_size' => 18,
                                    'text_color' => self::GRAY,
                                    'line_height' => 1.8,
                                    'margin' => ['bottom' => 32],
                                ],
                                'children' => [],
                            ],
                            [
                                'type' => 'button',
                                'id' => self::id(),
                                'attrs' => [
                                    'text' => 'Start Free Trial',
                                    'link_url' => '#',
                                    'background_color' => self::PRIMARY,
                                    'text_color' => self::WHITE,
                                    'font_size' => 16,
                                    'font_weight' => '600',
                                    'padding' => ['top' => 16, 'right' => 32, 'bottom' => 16, 'left' => 32],
                                    'border_radius' => 8,
                                ],
                                'children' => [],
                            ],
                        ],
                    ],
                    [
                        'type' => 'column',
                        'id' => self::id(),
                        'attrs' => [],
                        'children' => [[
                            'type' => 'image',
                            'id' => self::id(),
                            'attrs' => [
                                'src' => 'https://images.pexels.com/photos/3182812/pexels-photo-3182812.jpeg?auto=compress&cs=tinysrgb&w=800',
                                'alt' => 'Dashboard Preview',
                                'border_radius' => 16,
                            ],
                            'children' => [],
                        ]],
                    ],
                ],
            ]],
        ];
    }

    // =====================================================
    // SECTION: FEATURES GRID
    // =====================================================

    private static function featuresGrid(): array
    {
        return [
            'type' => 'section',
            'id' => self::id(),
            'attrs' => [
                'background_color' => self::LIGHT,
                'padding' => ['top' => 100, 'right' => 20, 'bottom' => 100, 'left' => 20],
            ],
            'children' => [
                // Header
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1_1', 'max_width' => 700, 'margin' => ['bottom' => 60]],
                    'children' => [[
                        'type' => 'column',
                        'id' => self::id(),
                        'attrs' => [],
                        'children' => [
                            [
                                'type' => 'heading',
                                'id' => self::id(),
                                'attrs' => [
                                    'text' => 'Everything You Need to Succeed',
                                    'level' => 'h2',
                                    'font_size' => 44,
                                    'font_weight' => '700',
                                    'text_color' => self::DARK,
                                    'text_align' => 'center',
                                    'margin' => ['bottom' => 20],
                                ],
                                'children' => [],
                            ],
                            [
                                'type' => 'text',
                                'id' => self::id(),
                                'attrs' => [
                                    'content' => '<p>We combine creativity, technology, and strategy to deliver solutions that drive real business results.</p>',
                                    'font_size' => 18,
                                    'text_color' => self::GRAY,
                                    'text_align' => 'center',
                                ],
                                'children' => [],
                            ],
                        ],
                    ]],
                ],
                // Features
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1_3,1_3,1_3', 'column_gap' => 30],
                    'children' => [
                        self::featureCol('Strategic Design', 'We craft beautiful, user-centered designs that engage your audience and reflect your brand identity.'),
                        self::featureCol('Modern Development', 'Built with the latest technologies for speed, security, and scalability that grows with your business.'),
                        self::featureCol('24/7 Support', 'Our dedicated team is always here to help you succeed with responsive, expert support.'),
                    ],
                ],
            ],
        ];
    }

    private static function featureCol(string $title, string $desc): array
    {
        return [
            'type' => 'column',
            'id' => self::id(),
            'attrs' => [
                'background_color' => self::WHITE,
                'padding' => ['top' => 40, 'right' => 32, 'bottom' => 40, 'left' => 32],
                'border_radius' => 16,
            ],
            'children' => [[
                'type' => 'blurb',
                'id' => self::id(),
                'attrs' => [
                    'title' => $title,
                    'content' => '<p>' . $desc . '</p>',
                    'icon' => 'check-circle',
                    'icon_color' => self::PRIMARY,
                    'title_font_size' => 22,
                    'title_font_weight' => '700',
                    'title_color' => self::DARK,
                    'content_color' => self::GRAY,
                    'content_line_height' => 1.7,
                ],
                'children' => [],
            ]],
        ];
    }

    // =====================================================
    // SECTION: TESTIMONIALS
    // =====================================================

    private static function testimonials(): array
    {
        return [
            'type' => 'section',
            'id' => self::id(),
            'attrs' => [
                'background_color' => self::WHITE,
                'padding' => ['top' => 100, 'right' => 20, 'bottom' => 100, 'left' => 20],
            ],
            'children' => [
                // Header
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1_1', 'max_width' => 600, 'margin' => ['bottom' => 60]],
                    'children' => [[
                        'type' => 'column',
                        'id' => self::id(),
                        'attrs' => [],
                        'children' => [[
                            'type' => 'heading',
                            'id' => self::id(),
                            'attrs' => [
                                'text' => 'What Our Clients Say',
                                'level' => 'h2',
                                'font_size' => 44,
                                'font_weight' => '700',
                                'text_color' => self::DARK,
                                'text_align' => 'center',
                            ],
                            'children' => [],
                        ]],
                    ]],
                ],
                // Testimonials
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1_3,1_3,1_3', 'column_gap' => 30],
                    'children' => [
                        self::testimonialCol('The team exceeded all our expectations. Our new website has significantly improved conversions.', 'Sarah Johnson', 'CEO, TechStart', 'https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?auto=compress&cs=tinysrgb&w=150'),
                        self::testimonialCol('Incredible attention to detail and exceptional communication throughout the entire project.', 'Michael Chen', 'Founder, GrowthLabs', 'https://images.pexels.com/photos/614810/pexels-photo-614810.jpeg?auto=compress&cs=tinysrgb&w=150'),
                        self::testimonialCol('They transformed our outdated platform into a modern, user-friendly experience.', 'Emily Roberts', 'Director, Innovate Co', 'https://images.pexels.com/photos/3756679/pexels-photo-3756679.jpeg?auto=compress&cs=tinysrgb&w=150'),
                    ],
                ],
            ],
        ];
    }

    private static function testimonialCol(string $quote, string $name, string $title, string $img): array
    {
        return [
            'type' => 'column',
            'id' => self::id(),
            'attrs' => [
                'background_color' => self::LIGHT,
                'padding' => ['top' => 32, 'right' => 28, 'bottom' => 32, 'left' => 28],
                'border_radius' => 16,
            ],
            'children' => [[
                'type' => 'testimonial',
                'id' => self::id(),
                'attrs' => [
                    'content' => $quote,
                    'author' => $name,
                    'job_title' => $title,
                    'portrait_url' => $img,
                    'quote_font_size' => 17,
                    'quote_color' => self::DARK,
                    'quote_line_height' => 1.7,
                    'author_font_size' => 16,
                    'author_font_weight' => '700',
                    'author_color' => self::DARK,
                    'position_font_size' => 14,
                    'position_color' => self::GRAY,
                ],
                'children' => [],
            ]],
        ];
    }

    // =====================================================
    // SECTION: PRICING
    // =====================================================

    private static function pricing(): array
    {
        return [
            'type' => 'section',
            'id' => self::id(),
            'attrs' => [
                'background_color' => self::LIGHT,
                'padding' => ['top' => 100, 'right' => 20, 'bottom' => 100, 'left' => 20],
            ],
            'children' => [
                // Header
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1_1', 'max_width' => 600, 'margin' => ['bottom' => 60]],
                    'children' => [[
                        'type' => 'column',
                        'id' => self::id(),
                        'attrs' => [],
                        'children' => [[
                            'type' => 'heading',
                            'id' => self::id(),
                            'attrs' => [
                                'text' => 'Simple, Transparent Pricing',
                                'level' => 'h2',
                                'font_size' => 44,
                                'font_weight' => '700',
                                'text_color' => self::DARK,
                                'text_align' => 'center',
                            ],
                            'children' => [],
                        ]],
                    ]],
                ],
                // Pricing Cards
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1_3,1_3,1_3', 'column_gap' => 24],
                    'children' => [
                        self::pricingCol('Starter', '29', ['5 Projects', '10GB Storage', 'Email Support'], false),
                        self::pricingCol('Professional', '79', ['25 Projects', '100GB Storage', 'Priority Support', 'API Access'], true),
                        self::pricingCol('Enterprise', '199', ['Unlimited Projects', '1TB Storage', '24/7 Support', 'Dedicated Manager'], false),
                    ],
                ],
            ],
        ];
    }

    private static function pricingCol(string $name, string $price, array $features, bool $featured): array
    {
        $bg = $featured ? self::PRIMARY : self::WHITE;
        $titleColor = $featured ? self::WHITE : self::DARK;
        $priceColor = $featured ? self::WHITE : self::DARK;
        $contentColor = $featured ? 'rgba(255,255,255,0.9)' : self::GRAY;
        $btnBg = $featured ? self::WHITE : self::PRIMARY;
        $btnText = $featured ? self::PRIMARY : self::WHITE;

        $featureList = '<ul>';
        foreach ($features as $f) {
            $featureList .= '<li>' . $f . '</li>';
        }
        $featureList .= '</ul>';

        return [
            'type' => 'column',
            'id' => self::id(),
            'attrs' => [
                'background_color' => $bg,
                'padding' => ['top' => 40, 'right' => 32, 'bottom' => 40, 'left' => 32],
                'border_radius' => 20,
            ],
            'children' => [[
                'type' => 'pricing_table',
                'id' => self::id(),
                'attrs' => [
                    'title' => $name,
                    'price' => $price,
                    'currency' => '$',
                    'per' => '/month',
                    'content' => $featureList,
                    'button_text' => 'Get Started',
                    'link_url' => '#',
                    'featured' => $featured,
                    'title_color' => $titleColor,
                    'title_font_size' => 20,
                    'title_font_weight' => '600',
                    'price_color' => $priceColor,
                    'price_font_size' => 56,
                    'price_font_weight' => '800',
                    'content_color' => $contentColor,
                    'button_background' => $btnBg,
                    'button_text_color' => $btnText,
                    'button_border_radius' => 8,
                ],
                'children' => [],
            ]],
        ];
    }

    // =====================================================
    // SECTION: CTA
    // =====================================================

    private static function ctaSection(): array
    {
        return [
            'type' => 'section',
            'id' => self::id(),
            'attrs' => [
                'background_type' => 'gradient',
                'background_gradient' => 'linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%)',
                'padding' => ['top' => 100, 'right' => 20, 'bottom' => 100, 'left' => 20],
            ],
            'children' => [[
                'type' => 'row',
                'id' => self::id(),
                'attrs' => ['columns' => '1_1', 'max_width' => 800],
                'children' => [[
                    'type' => 'column',
                    'id' => self::id(),
                    'attrs' => [],
                    'children' => [
                        [
                            'type' => 'heading',
                            'id' => self::id(),
                            'attrs' => [
                                'text' => 'Ready to Start Your Project?',
                                'level' => 'h2',
                                'font_size' => 44,
                                'font_weight' => '700',
                                'text_color' => self::WHITE,
                                'text_align' => 'center',
                                'margin' => ['bottom' => 20],
                            ],
                            'children' => [],
                        ],
                        [
                            'type' => 'text',
                            'id' => self::id(),
                            'attrs' => [
                                'content' => '<p>Let\'s work together to bring your vision to life. Contact us today for a free consultation.</p>',
                                'font_size' => 18,
                                'text_color' => 'rgba(255, 255, 255, 0.9)',
                                'text_align' => 'center',
                                'margin' => ['bottom' => 36],
                            ],
                            'children' => [],
                        ],
                        [
                            'type' => 'button',
                            'id' => self::id(),
                            'attrs' => [
                                'text' => 'Get in Touch',
                                'link_url' => '#contact',
                                'background_color' => self::WHITE,
                                'text_color' => self::PRIMARY,
                                'font_size' => 17,
                                'font_weight' => '600',
                                'padding' => ['top' => 18, 'right' => 40, 'bottom' => 18, 'left' => 40],
                                'border_radius' => 50,
                                'alignment' => 'center',
                            ],
                            'children' => [],
                        ],
                    ],
                ]],
            ]],
        ];
    }
}
