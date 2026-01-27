<?php
/**
 * Layout Library - Professional Premade Layouts
 *
 * Includes:
 * - Page Templates (full landing pages)
 * - Section Templates (reusable sections)
 * - Theme Builder Templates (header, footer, body)
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Layout_Library
{
    // =====================================================
    // INSURANCE THEME COLORS
    // =====================================================
    private static function colors(): array
    {
        return [
            'primary'    => '#024945',    // Dark teal/green
            'accent'     => '#88ffb6',    // Bright mint green
            'bg'         => '#f7f7f5',    // Off-white background
            'bg_dark'    => '#024945',    // Dark teal background
            'text'       => '#636363',    // Body text gray
            'text_dark'  => '#011f1d',    // Darker text
            'white'      => '#ffffff',
            'light'      => '#f0f5f5',    // Light teal tint
        ];
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
            'headers' => self::getHeaderLayouts(),
            'footers' => self::getFooterLayouts(),
            'body' => self::getBodyLayouts(),
        ];
    }

    public static function getBodyLayouts(): array
    {
        return [
            [
                'id' => 'body-blog-post',
                'name' => 'Blog Post Layout',
                'type' => 'body',
                'description' => 'Classic blog post with featured image, title, meta, content and author box',
                'content' => self::blogPostBody(),
            ],
            [
                'id' => 'body-page-with-sidebar',
                'name' => 'Page with Sidebar',
                'type' => 'body',
                'description' => 'Content area with sidebar for widgets',
                'content' => self::pageWithSidebarBody(),
            ],
            [
                'id' => 'body-fullwidth-page',
                'name' => 'Full Width Page',
                'type' => 'body',
                'description' => 'Full width content layout without sidebar',
                'content' => self::fullwidthPageBody(),
            ],
            [
                'id' => 'body-archive',
                'name' => 'Archive Layout',
                'type' => 'body',
                'description' => 'Archive page with post grid and pagination',
                'content' => self::archiveBody(),
            ],
        ];
    }

    public static function getPageLayouts(): array
    {
        return [
            [
                'id' => 'page-insurance-landing',
                'name' => 'Insurance Landing Page',
                'category' => 'landing',
                'thumbnail' => '/plugins/jessie-theme-builder/assets/images/thumbnails/insurance-landing.jpg',
                'content' => self::insuranceLandingPage(),
            ],
        ];
    }

    public static function getSectionLayouts(): array
    {
        return [
            [
                'id' => 'section-insurance-hero',
                'name' => 'Insurance Hero',
                'category' => 'hero',
                'content' => self::wrapSection(self::insuranceHero()),
            ],
            [
                'id' => 'section-insurance-features',
                'name' => 'Insurance Features Grid',
                'category' => 'features',
                'content' => self::wrapSection(self::insuranceFeatures()),
            ],
            [
                'id' => 'section-insurance-cta',
                'name' => 'Insurance CTA',
                'category' => 'cta',
                'content' => self::wrapSection(self::insuranceCta()),
            ],
            [
                'id' => 'section-insurance-testimonials',
                'name' => 'Insurance Testimonials',
                'category' => 'testimonials',
                'content' => self::wrapSection(self::insuranceTestimonials()),
            ],
        ];
    }

    public static function getHeaderLayouts(): array
    {
        return [
            [
                'id' => 'header-insurance',
                'name' => 'Insurance Header',
                'type' => 'header',
                'description' => 'Professional header with top bar, logo, menu, and CTA button',
                'content' => self::insuranceHeader(),
            ],
            [
                'id' => 'header-simple',
                'name' => 'Simple Header',
                'type' => 'header',
                'description' => 'Clean, minimal header with logo and navigation',
                'content' => self::simpleHeader(),
            ],
            [
                'id' => 'header-centered',
                'name' => 'Centered Header',
                'type' => 'header',
                'description' => 'Centered logo with navigation below',
                'content' => self::centeredHeader(),
            ],
        ];
    }

    public static function getFooterLayouts(): array
    {
        return [
            [
                'id' => 'footer-insurance',
                'name' => 'Insurance Footer',
                'type' => 'footer',
                'description' => '4-column footer with about, links, contact, and copyright',
                'content' => self::insuranceFooter(),
            ],
            [
                'id' => 'footer-simple',
                'name' => 'Simple Footer',
                'type' => 'footer',
                'description' => 'Clean footer with logo, links, and copyright',
                'content' => self::simpleFooter(),
            ],
            [
                'id' => 'footer-minimal',
                'name' => 'Minimal Footer',
                'type' => 'footer',
                'description' => 'Single row footer with copyright and social icons',
                'content' => self::minimalFooter(),
            ],
        ];
    }

    public static function getCategories(): array
    {
        return [
            'pages' => [
                'all' => 'All Pages',
                'landing' => 'Landing Pages',
                'business' => 'Business',
            ],
            'sections' => [
                'all' => 'All Sections',
                'hero' => 'Hero',
                'features' => 'Features',
                'cta' => 'Call to Action',
                'testimonials' => 'Testimonials',
            ],
        ];
    }

    // =====================================================
    // HELPERS
    // =====================================================

    private static function wrapSection(array $section): array
    {
        return ['version' => '1.0', 'content' => [$section]];
    }

    private static function id(): string
    {
        return 'jtb_' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
    }

    private static function spacing($top, $right = null, $bottom = null, $left = null): array
    {
        return [
            'top' => $top,
            'right' => $right ?? $top,
            'bottom' => $bottom ?? $top,
            'left' => $left ?? ($right ?? $top)
        ];
    }

    private static function radius($tl, $tr = null, $br = null, $bl = null): array
    {
        return [
            'top_left' => $tl,
            'top_right' => $tr ?? $tl,
            'bottom_right' => $br ?? ($tr ?? $tl),
            'bottom_left' => $bl ?? $tl
        ];
    }

    // =====================================================
    // INSURANCE LANDING PAGE
    // =====================================================

    private static function insuranceLandingPage(): array
    {
        return [
            'version' => '1.0',
            'content' => [
                self::insuranceHero(),
                self::insuranceLogos(),
                self::insuranceServices(),
                self::insuranceAbout(),
                self::insuranceFeatures(),
                self::insuranceProcess(),
                self::insuranceCta(),
                self::insuranceTestimonials(),
            ],
        ];
    }

    // =====================================================
    // SECTION: HERO
    // =====================================================

    private static function insuranceHero(): array
    {
        $c = self::colors();

        return [
            'type' => 'section',
            'id' => self::id(),
            'attrs' => [
                'background_type' => 'color',
                'background_color' => $c['bg'],
                'padding' => self::spacing(100, 40, 100, 40),
            ],
            'children' => [
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => [
                        'columns' => '1_2,1_2',
                        'column_gap' => '60',
                        'vertical_align' => 'center',
                    ],
                    'children' => [
                        // Left column - Content
                        [
                            'type' => 'column',
                            'id' => self::id(),
                            'attrs' => ['width' => '1_2'],
                            'children' => [
                                [
                                    'type' => 'heading',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'text' => 'Affordable Insurance Plans For Future',
                                        'level' => 'h1',
                                        'font_family' => 'Outfit',
                                        'font_size' => '56',
                                        'font_weight' => '500',
                                        'font_style' => 'italic',
                                        'line_height' => '1.15',
                                        'text_color' => $c['primary'],
                                        'margin' => self::spacing(0, 0, 30, 0),
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'content' => '<p>Protect what matters most with comprehensive coverage tailored to your needs. Our expert advisors are here to guide you every step of the way.</p>',
                                        'font_family' => 'Outfit',
                                        'font_size' => '18',
                                        'font_weight' => '300',
                                        'line_height' => '1.7',
                                        'text_color' => $c['text'],
                                        'margin' => self::spacing(0, 0, 40, 0),
                                    ],
                                ],
                                // Buttons
                                [
                                    'type' => 'row',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'columns' => '1_2,1_2',
                                        'column_gap' => '20',
                                    ],
                                    'children' => [
                                        [
                                            'type' => 'column',
                                            'id' => self::id(),
                                            'attrs' => ['width' => '1_2'],
                                            'children' => [
                                                [
                                                    'type' => 'button',
                                                    'id' => self::id(),
                                                    'attrs' => [
                                                        'text' => 'Book a Call →',
                                                        'link_url' => '#contact',
                                                        'background_type' => 'color',
                                                        'background_color' => $c['accent'],
                                                        'text_color' => $c['primary'],
                                                        'font_family' => 'Outfit',
                                                        'font_size' => '16',
                                                        'font_weight' => '500',
                                                        'padding' => self::spacing(18, 36, 18, 36),
                                                        'border_radius' => self::radius(50),
                                                    ],
                                                ],
                                            ],
                                        ],
                                        [
                                            'type' => 'column',
                                            'id' => self::id(),
                                            'attrs' => ['width' => '1_2'],
                                            'children' => [
                                                [
                                                    'type' => 'button',
                                                    'id' => self::id(),
                                                    'attrs' => [
                                                        'text' => 'Explore Plans →',
                                                        'link_url' => '#plans',
                                                        'background_type' => 'color',
                                                        'background_color' => 'transparent',
                                                        'text_color' => $c['primary'],
                                                        'font_family' => 'Outfit',
                                                        'font_size' => '16',
                                                        'font_weight' => '500',
                                                        'padding' => self::spacing(18, 36, 18, 36),
                                                        'border_radius' => self::radius(50),
                                                        'border_width' => self::spacing(2),
                                                        'border_color' => $c['primary'],
                                                        'border_style' => 'solid',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                // Trust badges
                                [
                                    'type' => 'row',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'columns' => '1_2,1_2',
                                        'column_gap' => '30',
                                        'margin' => self::spacing(50, 0, 0, 0),
                                    ],
                                    'children' => [
                                        [
                                            'type' => 'column',
                                            'id' => self::id(),
                                            'attrs' => ['width' => '1_2'],
                                            'children' => [
                                                [
                                                    'type' => 'text',
                                                    'id' => self::id(),
                                                    'attrs' => [
                                                        'content' => '<p style="text-align:center"><strong style="font-size:24px;color:#024945">#1</strong><br><span style="font-size:12px;text-transform:uppercase;letter-spacing:1px">Business Insider</span></p>',
                                                        'text_color' => $c['text'],
                                                    ],
                                                ],
                                            ],
                                        ],
                                        [
                                            'type' => 'column',
                                            'id' => self::id(),
                                            'attrs' => ['width' => '1_2'],
                                            'children' => [
                                                [
                                                    'type' => 'text',
                                                    'id' => self::id(),
                                                    'attrs' => [
                                                        'content' => '<p style="text-align:center"><strong style="font-size:14px;color:#024945">Trusted</strong><br><span style="font-size:12px">Forbes Advisor</span></p>',
                                                        'text_color' => $c['text'],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        // Right column - Image collage
                        [
                            'type' => 'column',
                            'id' => self::id(),
                            'attrs' => ['width' => '1_2'],
                            'children' => [
                                [
                                    'type' => 'image',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'src' => '/uploads/media/insurance-hero-handshake-20260125.jpg',
                                        'alt' => 'Insurance consultation meeting',
                                        'border_radius' => self::radius(20),
                                        'box_shadow_style' => 'custom',
                                        'box_shadow_horizontal' => '0',
                                        'box_shadow_vertical' => '25',
                                        'box_shadow_blur' => '50',
                                        'box_shadow_spread' => '-12',
                                        'box_shadow_color' => 'rgba(2,73,69,0.25)',
                                    ],
                                ],
                                // Stats card overlay
                                [
                                    'type' => 'blurb',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'title' => '1M+',
                                        'content' => '<p>Happy Customers</p>',
                                        'use_icon' => false,
                                        'text_orientation' => 'center',
                                        'background_type' => 'color',
                                        'background_color' => $c['primary'],
                                        'header_text_color' => $c['accent'],
                                        'header_font_size' => '32',
                                        'header_font_weight' => '700',
                                        'body_text_color' => $c['light'],
                                        'body_font_size' => '14',
                                        'padding' => self::spacing(20, 30, 20, 30),
                                        'border_radius' => self::radius(16),
                                        'margin' => self::spacing(20, 0, 0, 0),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // =====================================================
    // SECTION: LOGOS/PARTNERS
    // =====================================================

    private static function insuranceLogos(): array
    {
        $c = self::colors();

        return [
            'type' => 'section',
            'id' => self::id(),
            'attrs' => [
                'background_type' => 'color',
                'background_color' => $c['bg'],
                'padding' => self::spacing(0, 40, 80, 40),
            ],
            'children' => [
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1'],
                    'children' => [
                        [
                            'type' => 'column',
                            'id' => self::id(),
                            'attrs' => ['width' => '1'],
                            'children' => [
                                [
                                    'type' => 'heading',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'text' => 'Trusted by Industry Leaders',
                                        'level' => 'h6',
                                        'font_family' => 'Outfit',
                                        'font_size' => '14',
                                        'font_weight' => '500',
                                        'letter_spacing' => '2',
                                        'text_transform' => 'uppercase',
                                        'text_color' => $c['text'],
                                        'text_align' => 'center',
                                        'margin' => self::spacing(0, 0, 30, 0),
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'content' => '<p style="text-align:center;letter-spacing:4px;opacity:0.6;font-weight:500">ACME CORP &nbsp;•&nbsp; GLOBAL TRUST &nbsp;•&nbsp; SECURE LIFE &nbsp;•&nbsp; PREMIUM SHIELD &nbsp;•&nbsp; SAFE FUTURE</p>',
                                        'font_family' => 'Outfit',
                                        'font_size' => '13',
                                        'text_color' => $c['text'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // =====================================================
    // SECTION: SERVICES
    // =====================================================

    private static function insuranceServices(): array
    {
        $c = self::colors();

        return [
            'type' => 'section',
            'id' => self::id(),
            'attrs' => [
                'background_type' => 'color',
                'background_color' => $c['white'],
                'padding' => self::spacing(100, 40, 100, 40),
            ],
            'children' => [
                // Header
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1', 'max_width' => '700'],
                    'children' => [
                        [
                            'type' => 'column',
                            'id' => self::id(),
                            'attrs' => ['width' => '1'],
                            'children' => [
                                [
                                    'type' => 'heading',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'text' => 'Find The Right Coverage For Your Future',
                                        'level' => 'h2',
                                        'font_family' => 'Outfit',
                                        'font_size' => '42',
                                        'font_weight' => '500',
                                        'text_color' => $c['primary'],
                                        'text_align' => 'center',
                                        'margin' => self::spacing(0, 0, 20, 0),
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'content' => '<p>Choose from our comprehensive range of insurance plans designed to protect every aspect of your life.</p>',
                                        'font_family' => 'Outfit',
                                        'font_size' => '18',
                                        'text_color' => $c['text'],
                                        'text_align' => 'center',
                                        'margin' => self::spacing(0, 0, 60, 0),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // Service cards grid
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => [
                        'columns' => '1_3,1_3,1_3',
                        'column_gap' => '30',
                    ],
                    'children' => [
                        self::serviceCard('Life Insurance', 'Secure your family\'s financial future with comprehensive life coverage and flexible premium options.', 'heart', $c),
                        self::serviceCard('Home Insurance', 'Protect your most valuable asset with coverage for natural disasters, theft, and accidental damage.', 'home', $c),
                        self::serviceCard('Auto Insurance', 'Comprehensive coverage including collision, liability, and roadside assistance for peace of mind.', 'truck', $c),
                    ],
                ],
                // Second row
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => [
                        'columns' => '1_3,1_3,1_3',
                        'column_gap' => '30',
                        'margin' => self::spacing(30, 0, 0, 0),
                    ],
                    'children' => [
                        self::serviceCard('Business Insurance', 'Complete protection covering liability, property damage, and employee benefits for your company.', 'briefcase', $c),
                        self::serviceCard('Health Insurance', 'Affordable healthcare plans with extensive network coverage and wellness programs.', 'activity', $c),
                        self::serviceCard('Travel Insurance', 'Stay protected on your adventures with coverage for trips, medical emergencies, and cancellations.', 'globe', $c),
                    ],
                ],
            ],
        ];
    }

    private static function serviceCard(string $title, string $desc, string $icon, array $c): array
    {
        return [
            'type' => 'column',
            'id' => self::id(),
            'attrs' => ['width' => '1_3'],
            'children' => [
                [
                    'type' => 'blurb',
                    'id' => self::id(),
                    'attrs' => [
                        'title' => $title,
                        'content' => '<p>' . $desc . '</p>',
                        'use_icon' => true,
                        'font_icon' => $icon,
                        'icon_color' => $c['accent'],
                        'icon_font_size' => '48',
                        'icon_background_color' => $c['primary'],
                        'icon_padding' => '20',
                        'icon_border_radius' => '12',
                        'text_orientation' => 'left',
                        'header_level' => 'h4',
                        'header_font_family' => 'Outfit',
                        'header_font_size' => '22',
                        'header_font_weight' => '500',
                        'header_text_color' => $c['primary'],
                        'body_font_family' => 'Outfit',
                        'body_font_size' => '15',
                        'body_text_color' => $c['text'],
                        'body_line_height' => '1.7',
                        'background_type' => 'color',
                        'background_color' => $c['bg'],
                        'padding' => self::spacing(35),
                        'border_radius' => self::radius(16),
                    ],
                ],
            ],
        ];
    }

    // =====================================================
    // SECTION: ABOUT / WHY CHOOSE US
    // =====================================================

    private static function insuranceAbout(): array
    {
        $c = self::colors();

        return [
            'type' => 'section',
            'id' => self::id(),
            'attrs' => [
                'background_type' => 'color',
                'background_color' => $c['bg'],
                'padding' => self::spacing(100, 40, 100, 40),
            ],
            'children' => [
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => [
                        'columns' => '1_2,1_2',
                        'column_gap' => '80',
                        'vertical_align' => 'center',
                    ],
                    'children' => [
                        // Left - Image
                        [
                            'type' => 'column',
                            'id' => self::id(),
                            'attrs' => ['width' => '1_2'],
                            'children' => [
                                [
                                    'type' => 'image',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'src' => '/uploads/media/insurance-office-professional-20260125.jpg',
                                        'alt' => 'Insurance professional at work',
                                        'border_radius' => self::radius(20),
                                    ],
                                ],
                            ],
                        ],
                        // Right - Content
                        [
                            'type' => 'column',
                            'id' => self::id(),
                            'attrs' => ['width' => '1_2'],
                            'children' => [
                                [
                                    'type' => 'heading',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'text' => 'We Make Insurance Simple',
                                        'level' => 'h2',
                                        'font_family' => 'Outfit',
                                        'font_size' => '42',
                                        'font_weight' => '500',
                                        'text_color' => $c['primary'],
                                        'margin' => self::spacing(0, 0, 20, 0),
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'content' => '<p>With over 25 years of experience, we\'ve helped millions of customers find the perfect coverage. Our streamlined process ensures you get protected quickly and affordably.</p>',
                                        'font_family' => 'Outfit',
                                        'font_size' => '17',
                                        'line_height' => '1.8',
                                        'text_color' => $c['text'],
                                        'margin' => self::spacing(0, 0, 35, 0),
                                    ],
                                ],
                                // Stats row
                                [
                                    'type' => 'row',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'columns' => '1_3,1_3,1_3',
                                        'column_gap' => '20',
                                    ],
                                    'children' => [
                                        self::statItem('25+', 'Years Experience', $c),
                                        self::statItem('1M+', 'Happy Clients', $c),
                                        self::statItem('98%', 'Claims Approved', $c),
                                    ],
                                ],
                                // CTA Button
                                [
                                    'type' => 'button',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'text' => 'Learn More About Us →',
                                        'link_url' => '#about',
                                        'background_type' => 'color',
                                        'background_color' => $c['accent'],
                                        'text_color' => $c['primary'],
                                        'font_family' => 'Outfit',
                                        'font_size' => '16',
                                        'font_weight' => '500',
                                        'padding' => self::spacing(18, 36, 18, 36),
                                        'border_radius' => self::radius(50),
                                        'margin' => self::spacing(40, 0, 0, 0),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function statItem(string $number, string $label, array $c): array
    {
        return [
            'type' => 'column',
            'id' => self::id(),
            'attrs' => ['width' => '1_3'],
            'children' => [
                [
                    'type' => 'text',
                    'id' => self::id(),
                    'attrs' => [
                        'content' => '<p><strong style="font-size:36px;color:' . $c['primary'] . ';display:block;margin-bottom:5px">' . $number . '</strong><span style="font-size:14px;color:' . $c['text'] . '">' . $label . '</span></p>',
                    ],
                ],
            ],
        ];
    }

    // =====================================================
    // SECTION: FEATURES (Why Choose Us - Dark)
    // =====================================================

    private static function insuranceFeatures(): array
    {
        $c = self::colors();

        return [
            'type' => 'section',
            'id' => self::id(),
            'attrs' => [
                'background_type' => 'color',
                'background_color' => $c['primary'],
                'padding' => self::spacing(100, 40, 100, 40),
            ],
            'children' => [
                // Header
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1', 'max_width' => '600'],
                    'children' => [
                        [
                            'type' => 'column',
                            'id' => self::id(),
                            'attrs' => ['width' => '1'],
                            'children' => [
                                [
                                    'type' => 'heading',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'text' => 'Why Choose Us?',
                                        'level' => 'h2',
                                        'font_family' => 'Outfit',
                                        'font_size' => '42',
                                        'font_weight' => '500',
                                        'text_color' => $c['white'],
                                        'text_align' => 'center',
                                        'margin' => self::spacing(0, 0, 60, 0),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // Features grid
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => [
                        'columns' => '1_4,1_4,1_4,1_4',
                        'column_gap' => '30',
                    ],
                    'children' => [
                        self::featureItem('Custom Plans', 'Tailored solutions for your unique needs', 'sliders', $c),
                        self::featureItem('Expert Advice', 'Guidance from experienced professionals', 'users', $c),
                        self::featureItem('Fast Claims', 'Quick and hassle-free processing', 'zap', $c),
                        self::featureItem('24/7 Support', 'Always here when you need us', 'headphones', $c),
                    ],
                ],
            ],
        ];
    }

    private static function featureItem(string $title, string $desc, string $icon, array $c): array
    {
        return [
            'type' => 'column',
            'id' => self::id(),
            'attrs' => ['width' => '1_4'],
            'children' => [
                [
                    'type' => 'blurb',
                    'id' => self::id(),
                    'attrs' => [
                        'title' => $title,
                        'content' => '<p>' . $desc . '</p>',
                        'use_icon' => true,
                        'font_icon' => $icon,
                        'icon_color' => $c['accent'],
                        'icon_font_size' => '36',
                        'text_orientation' => 'center',
                        'header_level' => 'h4',
                        'header_font_family' => 'Outfit',
                        'header_font_size' => '20',
                        'header_font_weight' => '500',
                        'header_text_color' => $c['white'],
                        'body_font_family' => 'Outfit',
                        'body_font_size' => '15',
                        'body_text_color' => $c['light'],
                    ],
                ],
            ],
        ];
    }

    // =====================================================
    // SECTION: PROCESS / HOW IT WORKS
    // =====================================================

    private static function insuranceProcess(): array
    {
        $c = self::colors();

        return [
            'type' => 'section',
            'id' => self::id(),
            'attrs' => [
                'background_type' => 'color',
                'background_color' => $c['bg'],
                'padding' => self::spacing(100, 40, 100, 40),
            ],
            'children' => [
                // Header
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1', 'max_width' => '600'],
                    'children' => [
                        [
                            'type' => 'column',
                            'id' => self::id(),
                            'attrs' => ['width' => '1'],
                            'children' => [
                                [
                                    'type' => 'heading',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'text' => 'How It Works',
                                        'level' => 'h2',
                                        'font_family' => 'Outfit',
                                        'font_size' => '42',
                                        'font_weight' => '500',
                                        'text_color' => $c['primary'],
                                        'text_align' => 'center',
                                        'margin' => self::spacing(0, 0, 60, 0),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // Process steps
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => [
                        'columns' => '1_4,1_4,1_4,1_4',
                        'column_gap' => '30',
                    ],
                    'children' => [
                        self::processStep('01', 'Discover', 'Tell us about your insurance needs and goals.', $c),
                        self::processStep('02', 'Compare', 'We find the best plans that match your requirements.', $c),
                        self::processStep('03', 'Choose', 'Select the perfect coverage for your situation.', $c),
                        self::processStep('04', 'Relax', 'Enjoy peace of mind knowing you\'re protected.', $c),
                    ],
                ],
            ],
        ];
    }

    private static function processStep(string $num, string $title, string $desc, array $c): array
    {
        return [
            'type' => 'column',
            'id' => self::id(),
            'attrs' => ['width' => '1_4'],
            'children' => [
                [
                    'type' => 'blurb',
                    'id' => self::id(),
                    'attrs' => [
                        'title' => $title,
                        'content' => '<p>' . $desc . '</p>',
                        'use_icon' => false,
                        'text_orientation' => 'center',
                        'header_level' => 'h4',
                        'header_font_family' => 'Outfit',
                        'header_font_size' => '24',
                        'header_font_weight' => '500',
                        'header_text_color' => $c['primary'],
                        'body_font_family' => 'Outfit',
                        'body_font_size' => '15',
                        'body_text_color' => $c['text'],
                        'background_type' => 'color',
                        'background_color' => $c['white'],
                        'padding' => self::spacing(40),
                        'border_radius' => self::radius(16),
                        'box_shadow_style' => 'custom',
                        'box_shadow_horizontal' => '0',
                        'box_shadow_vertical' => '10',
                        'box_shadow_blur' => '40',
                        'box_shadow_spread' => '-10',
                        'box_shadow_color' => 'rgba(0,0,0,0.1)',
                    ],
                ],
            ],
        ];
    }

    // =====================================================
    // SECTION: CTA
    // =====================================================

    private static function insuranceCta(): array
    {
        $c = self::colors();

        return [
            'type' => 'section',
            'id' => self::id(),
            'attrs' => [
                'background_type' => 'color',
                'background_color' => $c['white'],
                'padding' => self::spacing(100, 40, 100, 40),
            ],
            'children' => [
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => [
                        'columns' => '1_2,1_2',
                        'column_gap' => '80',
                        'vertical_align' => 'center',
                    ],
                    'children' => [
                        // Left - Content
                        [
                            'type' => 'column',
                            'id' => self::id(),
                            'attrs' => ['width' => '1_2'],
                            'children' => [
                                [
                                    'type' => 'heading',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'text' => 'Ready to Secure Your Future?',
                                        'level' => 'h2',
                                        'font_family' => 'Outfit',
                                        'font_size' => '42',
                                        'font_weight' => '500',
                                        'text_color' => $c['primary'],
                                        'margin' => self::spacing(0, 0, 20, 0),
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'content' => '<p>Get a free quote today and discover how affordable comprehensive coverage can be. Our experts are standing by to help you find the perfect plan.</p>',
                                        'font_family' => 'Outfit',
                                        'font_size' => '17',
                                        'line_height' => '1.8',
                                        'text_color' => $c['text'],
                                        'margin' => self::spacing(0, 0, 35, 0),
                                    ],
                                ],
                                // Buttons
                                [
                                    'type' => 'row',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'columns' => '1_2,1_2',
                                        'column_gap' => '20',
                                    ],
                                    'children' => [
                                        [
                                            'type' => 'column',
                                            'id' => self::id(),
                                            'attrs' => ['width' => '1_2'],
                                            'children' => [
                                                [
                                                    'type' => 'button',
                                                    'id' => self::id(),
                                                    'attrs' => [
                                                        'text' => 'Get Free Quote →',
                                                        'link_url' => '#quote',
                                                        'background_type' => 'color',
                                                        'background_color' => $c['accent'],
                                                        'text_color' => $c['primary'],
                                                        'font_family' => 'Outfit',
                                                        'font_size' => '16',
                                                        'font_weight' => '500',
                                                        'padding' => self::spacing(18, 32, 18, 32),
                                                        'border_radius' => self::radius(50),
                                                    ],
                                                ],
                                            ],
                                        ],
                                        [
                                            'type' => 'column',
                                            'id' => self::id(),
                                            'attrs' => ['width' => '1_2'],
                                            'children' => [
                                                [
                                                    'type' => 'button',
                                                    'id' => self::id(),
                                                    'attrs' => [
                                                        'text' => 'Talk to Expert',
                                                        'link_url' => '#contact',
                                                        'background_type' => 'color',
                                                        'background_color' => 'transparent',
                                                        'text_color' => $c['primary'],
                                                        'font_family' => 'Outfit',
                                                        'font_size' => '16',
                                                        'font_weight' => '500',
                                                        'padding' => self::spacing(18, 32, 18, 32),
                                                        'border_radius' => self::radius(50),
                                                        'border_width' => self::spacing(2),
                                                        'border_color' => $c['primary'],
                                                        'border_style' => 'solid',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        // Right - Image
                        [
                            'type' => 'column',
                            'id' => self::id(),
                            'attrs' => ['width' => '1_2'],
                            'children' => [
                                [
                                    'type' => 'image',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'src' => '/uploads/media/insurance-contract-deal-20260125.jpg',
                                        'alt' => 'Insurance consultation',
                                        'border_radius' => self::radius(20),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // =====================================================
    // SECTION: TESTIMONIALS
    // =====================================================

    private static function insuranceTestimonials(): array
    {
        $c = self::colors();

        return [
            'type' => 'section',
            'id' => self::id(),
            'attrs' => [
                'background_type' => 'color',
                'background_color' => $c['bg'],
                'padding' => self::spacing(100, 40, 100, 40),
            ],
            'children' => [
                // Header
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => ['columns' => '1', 'max_width' => '600'],
                    'children' => [
                        [
                            'type' => 'column',
                            'id' => self::id(),
                            'attrs' => ['width' => '1'],
                            'children' => [
                                [
                                    'type' => 'heading',
                                    'id' => self::id(),
                                    'attrs' => [
                                        'text' => 'What Our Clients Say',
                                        'level' => 'h2',
                                        'font_family' => 'Outfit',
                                        'font_size' => '42',
                                        'font_weight' => '500',
                                        'text_color' => $c['primary'],
                                        'text_align' => 'center',
                                        'margin' => self::spacing(0, 0, 60, 0),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // Testimonial cards
                [
                    'type' => 'row',
                    'id' => self::id(),
                    'attrs' => [
                        'columns' => '1_3,1_3,1_3',
                        'column_gap' => '30',
                    ],
                    'children' => [
                        self::testimonialCard(
                            'The claims process was incredibly smooth. Within days, everything was handled professionally.',
                            'Michael Carter',
                            'Business Owner, New York',
                            $c['white'],
                            $c['primary'],
                            $c
                        ),
                        self::testimonialCard(
                            'Switching to this insurance company was the best decision. Their rates are competitive and service is outstanding.',
                            'Sarah Mitchell',
                            'Marketing Director, Chicago',
                            $c['primary'],
                            $c['white'],
                            $c
                        ),
                        self::testimonialCard(
                            'I feel secure knowing my family is protected. The policy options are flexible and advisors truly care.',
                            'David Thompson',
                            'Software Engineer, LA',
                            $c['white'],
                            $c['primary'],
                            $c
                        ),
                    ],
                ],
            ],
        ];
    }

    private static function testimonialCard(string $quote, string $name, string $role, string $bg, string $text, array $c): array
    {
        $bodyColor = ($bg === $c['primary']) ? $c['light'] : $c['text'];

        return [
            'type' => 'column',
            'id' => self::id(),
            'attrs' => ['width' => '1_3'],
            'children' => [
                [
                    'type' => 'testimonial',
                    'id' => self::id(),
                    'attrs' => [
                        'content' => '<p>"' . $quote . '"</p>',
                        'author' => $name,
                        'job_title' => $role,
                        'use_icon' => false,
                        'portrait_url' => '',
                        'quote_icon' => 'on',
                        'body_font_family' => 'Outfit',
                        'body_font_size' => '16',
                        'body_line_height' => '1.7',
                        'body_text_color' => $bodyColor,
                        'author_font_family' => 'Outfit',
                        'author_font_size' => '18',
                        'author_font_weight' => '500',
                        'author_text_color' => $text,
                        'position_font_family' => 'Outfit',
                        'position_font_size' => '14',
                        'position_text_color' => $bodyColor,
                        'background_type' => 'color',
                        'background_color' => $bg,
                        'padding' => self::spacing(40),
                        'border_radius' => self::radius(16),
                    ],
                ],
            ],
        ];
    }

    // =====================================================
    // THEME BUILDER: HEADER
    // =====================================================

    private static function insuranceHeader(): array
    {
        $c = self::colors();

        return [
            'version' => '1.0',
            'content' => [
                // Top bar
                [
                    'type' => 'section',
                    'id' => self::id(),
                    'attrs' => [
                        'background_type' => 'color',
                        'background_color' => $c['primary'],
                        'padding' => self::spacing(12, 40, 12, 40),
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => [
                                'columns' => '1_2,1_2',
                                'vertical_align' => 'center',
                            ],
                            'children' => [
                                // Left - Social icons
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1_2'],
                                    'children' => [
                                        [
                                            'type' => 'social_follow',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'facebook_url' => '#',
                                                'twitter_url' => '#',
                                                'linkedin_url' => '#',
                                                'icon_color' => $c['white'],
                                                'icon_size' => '16',
                                            ],
                                        ],
                                    ],
                                ],
                                // Right - Contact info
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1_2'],
                                    'children' => [
                                        [
                                            'type' => 'text',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'content' => '<p style="text-align:right"><span style="margin-right:20px">📞 +1 316 555-0116</span><span style="margin-right:20px">✉️ contact@insurex.com</span><span>📍 New York, NY</span></p>',
                                                'font_family' => 'Outfit',
                                                'font_size' => '13',
                                                'text_color' => $c['light'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // Main header
                [
                    'type' => 'section',
                    'id' => self::id(),
                    'attrs' => [
                        'background_type' => 'color',
                        'background_color' => $c['white'],
                        'padding' => self::spacing(20, 40, 20, 40),
                        'box_shadow_style' => 'custom',
                        'box_shadow_horizontal' => '0',
                        'box_shadow_vertical' => '2',
                        'box_shadow_blur' => '20',
                        'box_shadow_color' => 'rgba(0,0,0,0.08)',
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => [
                                'columns' => '1_4,1_2,1_4',
                                'vertical_align' => 'center',
                            ],
                            'children' => [
                                // Logo
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1_4'],
                                    'children' => [
                                        [
                                            'type' => 'heading',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'text' => '🛡️ Bright Path',
                                                'level' => 'h3',
                                                'font_family' => 'Outfit',
                                                'font_size' => '24',
                                                'font_weight' => '600',
                                                'text_color' => $c['primary'],
                                            ],
                                        ],
                                    ],
                                ],
                                // Menu
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1_2'],
                                    'children' => [
                                        [
                                            'type' => 'menu',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'menu_style' => 'horizontal',
                                                'menu_alignment' => 'center',
                                                'font_family' => 'Outfit',
                                                'font_size' => '15',
                                                'font_weight' => '500',
                                                'text_color' => $c['text_dark'],
                                                'text_color_hover' => $c['primary'],
                                                'link_spacing' => '30',
                                            ],
                                        ],
                                    ],
                                ],
                                // CTA Button
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1_4'],
                                    'children' => [
                                        [
                                            'type' => 'button',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'text' => 'Get Consultation →',
                                                'link_url' => '#contact',
                                                'align' => 'right',
                                                'background_type' => 'color',
                                                'background_color' => $c['accent'],
                                                'text_color' => $c['primary'],
                                                'font_family' => 'Outfit',
                                                'font_size' => '14',
                                                'font_weight' => '500',
                                                'padding' => self::spacing(14, 28, 14, 28),
                                                'border_radius' => self::radius(50),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // =====================================================
    // THEME BUILDER: FOOTER
    // =====================================================

    private static function insuranceFooter(): array
    {
        $c = self::colors();

        return [
            'version' => '1.0',
            'content' => [
                // Main footer
                [
                    'type' => 'section',
                    'id' => self::id(),
                    'attrs' => [
                        'background_type' => 'color',
                        'background_color' => $c['primary'],
                        'padding' => self::spacing(80, 40, 60, 40),
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => [
                                'columns' => '1_3,1_3,1_3',
                                'column_gap' => '60',
                            ],
                            'children' => [
                                // Column 1 - About
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1_3'],
                                    'children' => [
                                        [
                                            'type' => 'heading',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'text' => '🛡️ Bright Path',
                                                'level' => 'h4',
                                                'font_family' => 'Outfit',
                                                'font_size' => '22',
                                                'font_weight' => '600',
                                                'text_color' => $c['accent'],
                                                'margin' => self::spacing(0, 0, 20, 0),
                                            ],
                                        ],
                                        [
                                            'type' => 'text',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'content' => '<p>Insurex offers trusted and affordable insurance solutions to protect your life, home, business, and future with peace of mind.</p>',
                                                'font_family' => 'Outfit',
                                                'font_size' => '15',
                                                'line_height' => '1.7',
                                                'text_color' => $c['light'],
                                                'margin' => self::spacing(0, 0, 25, 0),
                                            ],
                                        ],
                                        [
                                            'type' => 'social_follow',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'facebook_url' => '#',
                                                'twitter_url' => '#',
                                                'linkedin_url' => '#',
                                                'youtube_url' => '#',
                                                'icon_color' => $c['white'],
                                                'icon_size' => '18',
                                            ],
                                        ],
                                    ],
                                ],
                                // Column 2 - Quick Links
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1_3'],
                                    'children' => [
                                        [
                                            'type' => 'heading',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'text' => 'Quick Links',
                                                'level' => 'h5',
                                                'font_family' => 'Outfit',
                                                'font_size' => '18',
                                                'font_weight' => '500',
                                                'text_color' => $c['white'],
                                                'margin' => self::spacing(0, 0, 25, 0),
                                            ],
                                        ],
                                        [
                                            'type' => 'text',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'content' => '<p><a href="#" style="color:#f0f5f5;text-decoration:none;display:block;padding:8px 0">Home</a><a href="#" style="color:#f0f5f5;text-decoration:none;display:block;padding:8px 0">Insurance Plans</a><a href="#" style="color:#f0f5f5;text-decoration:none;display:block;padding:8px 0">About Us</a><a href="#" style="color:#f0f5f5;text-decoration:none;display:block;padding:8px 0">Contact</a><a href="#" style="color:#f0f5f5;text-decoration:none;display:block;padding:8px 0">Blog</a></p>',
                                                'font_family' => 'Outfit',
                                                'font_size' => '15',
                                            ],
                                        ],
                                    ],
                                ],
                                // Column 3 - Contact
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1_3'],
                                    'children' => [
                                        [
                                            'type' => 'heading',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'text' => 'Contact Us',
                                                'level' => 'h5',
                                                'font_family' => 'Outfit',
                                                'font_size' => '18',
                                                'font_weight' => '500',
                                                'text_color' => $c['white'],
                                                'margin' => self::spacing(0, 0, 25, 0),
                                            ],
                                        ],
                                        [
                                            'type' => 'text',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'content' => '<p style="margin-bottom:15px"><strong style="color:#fff">Address:</strong><br>20 Cooper Square,<br>New York, NY 10003, USA</p><p style="margin-bottom:15px"><strong style="color:#fff">Phone:</strong><br>+1 316 555-0116</p><p><strong style="color:#fff">Email:</strong><br>contact@insurex.com</p>',
                                                'font_family' => 'Outfit',
                                                'font_size' => '15',
                                                'line_height' => '1.6',
                                                'text_color' => $c['light'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // Copyright bar
                [
                    'type' => 'section',
                    'id' => self::id(),
                    'attrs' => [
                        'background_type' => 'color',
                        'background_color' => $c['text_dark'],
                        'padding' => self::spacing(20, 40, 20, 40),
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => ['columns' => '1'],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1'],
                                    'children' => [
                                        [
                                            'type' => 'text',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'content' => '<p style="text-align:center">© 2026 Bright Path Insurance. All Rights Reserved.</p>',
                                                'font_family' => 'Outfit',
                                                'font_size' => '14',
                                                'text_color' => $c['light'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // =====================================================
    // ADDITIONAL HEADERS
    // =====================================================

    private static function simpleHeader(): array
    {
        $c = self::colors();

        return [
            'version' => '1.0',
            'content' => [
                [
                    'type' => 'section',
                    'id' => self::id(),
                    'attrs' => [
                        'background_type' => 'color',
                        'background_color' => $c['white'],
                        'padding' => self::spacing(20, 40, 20, 40),
                        'box_shadow_style' => 'custom',
                        'box_shadow_horizontal' => '0',
                        'box_shadow_vertical' => '2',
                        'box_shadow_blur' => '10',
                        'box_shadow_color' => 'rgba(0,0,0,0.05)',
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => [
                                'columns' => '1_3,2_3',
                                'vertical_align' => 'center',
                            ],
                            'children' => [
                                // Logo
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1_3'],
                                    'children' => [
                                        [
                                            'type' => 'heading',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'text' => 'Your Logo',
                                                'level' => 'h3',
                                                'font_family' => 'Outfit',
                                                'font_size' => '24',
                                                'font_weight' => '700',
                                                'text_color' => $c['primary'],
                                            ],
                                        ],
                                    ],
                                ],
                                // Menu
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '2_3'],
                                    'children' => [
                                        [
                                            'type' => 'menu',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'menu_style' => 'horizontal',
                                                'menu_alignment' => 'right',
                                                'font_family' => 'Outfit',
                                                'font_size' => '15',
                                                'font_weight' => '500',
                                                'text_color' => $c['text_dark'],
                                                'text_color_hover' => $c['primary'],
                                                'link_spacing' => '30',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function centeredHeader(): array
    {
        $c = self::colors();

        return [
            'version' => '1.0',
            'content' => [
                [
                    'type' => 'section',
                    'id' => self::id(),
                    'attrs' => [
                        'background_type' => 'color',
                        'background_color' => $c['white'],
                        'padding' => self::spacing(30, 40, 20, 40),
                        'border_width' => self::spacing(0, 0, 1, 0),
                        'border_color' => $c['light'],
                        'border_style' => 'solid',
                    ],
                    'children' => [
                        // Logo row
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => ['columns' => '1'],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1'],
                                    'children' => [
                                        [
                                            'type' => 'heading',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'text' => 'Your Logo',
                                                'level' => 'h2',
                                                'font_family' => 'Outfit',
                                                'font_size' => '28',
                                                'font_weight' => '700',
                                                'text_color' => $c['primary'],
                                                'text_align' => 'center',
                                                'margin' => self::spacing(0, 0, 20, 0),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        // Menu row
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => ['columns' => '1'],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1'],
                                    'children' => [
                                        [
                                            'type' => 'menu',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'menu_style' => 'horizontal',
                                                'menu_alignment' => 'center',
                                                'font_family' => 'Outfit',
                                                'font_size' => '15',
                                                'font_weight' => '500',
                                                'text_color' => $c['text_dark'],
                                                'text_color_hover' => $c['primary'],
                                                'link_spacing' => '35',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // =====================================================
    // ADDITIONAL FOOTERS
    // =====================================================

    private static function simpleFooter(): array
    {
        $c = self::colors();

        return [
            'version' => '1.0',
            'content' => [
                [
                    'type' => 'section',
                    'id' => self::id(),
                    'attrs' => [
                        'background_type' => 'color',
                        'background_color' => $c['primary'],
                        'padding' => self::spacing(60, 40, 40, 40),
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => [
                                'columns' => '1_3,1_3,1_3',
                                'column_gap' => '40',
                                'vertical_align' => 'top',
                            ],
                            'children' => [
                                // Logo & About
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1_3'],
                                    'children' => [
                                        [
                                            'type' => 'heading',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'text' => 'Your Logo',
                                                'level' => 'h4',
                                                'font_family' => 'Outfit',
                                                'font_size' => '22',
                                                'font_weight' => '600',
                                                'text_color' => $c['white'],
                                                'margin' => self::spacing(0, 0, 15, 0),
                                            ],
                                        ],
                                        [
                                            'type' => 'text',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'content' => '<p>A brief description of your company and what you do. Keep it short and meaningful.</p>',
                                                'font_family' => 'Outfit',
                                                'font_size' => '14',
                                                'line_height' => '1.7',
                                                'text_color' => $c['light'],
                                            ],
                                        ],
                                    ],
                                ],
                                // Quick Links
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1_3'],
                                    'children' => [
                                        [
                                            'type' => 'heading',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'text' => 'Quick Links',
                                                'level' => 'h5',
                                                'font_family' => 'Outfit',
                                                'font_size' => '16',
                                                'font_weight' => '600',
                                                'text_color' => $c['white'],
                                                'margin' => self::spacing(0, 0, 15, 0),
                                            ],
                                        ],
                                        [
                                            'type' => 'text',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'content' => '<p><a href="#" style="color:#f0f5f5;text-decoration:none;display:block;padding:5px 0">Home</a><a href="#" style="color:#f0f5f5;text-decoration:none;display:block;padding:5px 0">About</a><a href="#" style="color:#f0f5f5;text-decoration:none;display:block;padding:5px 0">Services</a><a href="#" style="color:#f0f5f5;text-decoration:none;display:block;padding:5px 0">Contact</a></p>',
                                                'font_family' => 'Outfit',
                                                'font_size' => '14',
                                            ],
                                        ],
                                    ],
                                ],
                                // Social
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1_3'],
                                    'children' => [
                                        [
                                            'type' => 'heading',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'text' => 'Follow Us',
                                                'level' => 'h5',
                                                'font_family' => 'Outfit',
                                                'font_size' => '16',
                                                'font_weight' => '600',
                                                'text_color' => $c['white'],
                                                'margin' => self::spacing(0, 0, 15, 0),
                                            ],
                                        ],
                                        [
                                            'type' => 'social_follow',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'facebook_url' => '#',
                                                'twitter_url' => '#',
                                                'linkedin_url' => '#',
                                                'instagram_url' => '#',
                                                'icon_color' => $c['white'],
                                                'icon_size' => '20',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        // Copyright
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => [
                                'columns' => '1',
                                'margin' => self::spacing(40, 0, 0, 0),
                                'padding' => self::spacing(20, 0, 0, 0),
                                'border_width' => self::spacing(1, 0, 0, 0),
                                'border_color' => 'rgba(255,255,255,0.1)',
                                'border_style' => 'solid',
                            ],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1'],
                                    'children' => [
                                        [
                                            'type' => 'text',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'content' => '<p style="text-align:center">© 2026 Your Company. All Rights Reserved.</p>',
                                                'font_family' => 'Outfit',
                                                'font_size' => '13',
                                                'text_color' => $c['light'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // =====================================================
    // BODY LAYOUTS
    // =====================================================

    private static function blogPostBody(): array
    {
        $c = self::colors();

        return [
            'version' => '1.0',
            'content' => [
                // Featured Image Section
                [
                    'type' => 'section',
                    'id' => self::id(),
                    'attrs' => [
                        'background_type' => 'color',
                        'background_color' => $c['bg'],
                        'padding' => self::spacing(0, 0, 0, 0),
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => ['columns' => '1'],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1'],
                                    'children' => [
                                        [
                                            'type' => 'featured_image',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'show_placeholder' => true,
                                                'border_radius' => '0',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // Post Content Section
                [
                    'type' => 'section',
                    'id' => self::id(),
                    'attrs' => [
                        'background_type' => 'color',
                        'background_color' => $c['white'],
                        'padding' => self::spacing(60, 40, 60, 40),
                        'max_width' => '800px',
                    ],
                    'children' => [
                        // Title and Meta
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => ['columns' => '1'],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1'],
                                    'children' => [
                                        [
                                            'type' => 'post_title',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'level' => 'h1',
                                                'font_family' => 'Outfit',
                                                'font_size' => '42',
                                                'font_weight' => '700',
                                                'text_color' => $c['text_dark'],
                                                'margin' => self::spacing(0, 0, 20, 0),
                                            ],
                                        ],
                                        [
                                            'type' => 'post_meta',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'show_author' => true,
                                                'show_date' => true,
                                                'show_categories' => true,
                                                'font_family' => 'Outfit',
                                                'font_size' => '14',
                                                'text_color' => $c['text'],
                                                'margin' => self::spacing(0, 0, 40, 0),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        // Content
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => ['columns' => '1'],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1'],
                                    'children' => [
                                        [
                                            'type' => 'post_content',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'font_family' => 'Outfit',
                                                'font_size' => '17',
                                                'line_height' => '1.8',
                                                'text_color' => $c['text'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        // Author Box
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => [
                                'columns' => '1',
                                'margin' => self::spacing(50, 0, 0, 0),
                            ],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1'],
                                    'children' => [
                                        [
                                            'type' => 'author_box',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'show_avatar' => true,
                                                'show_bio' => true,
                                                'show_social' => true,
                                                'background_color' => $c['bg'],
                                                'padding' => self::spacing(30, 30, 30, 30),
                                                'border_radius' => '12',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        // Post Navigation
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => [
                                'columns' => '1',
                                'margin' => self::spacing(40, 0, 0, 0),
                            ],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1'],
                                    'children' => [
                                        [
                                            'type' => 'post_navigation',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'show_thumbnails' => true,
                                                'font_family' => 'Outfit',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // Related Posts Section
                [
                    'type' => 'section',
                    'id' => self::id(),
                    'attrs' => [
                        'background_type' => 'color',
                        'background_color' => $c['bg'],
                        'padding' => self::spacing(60, 40, 60, 40),
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => ['columns' => '1'],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1'],
                                    'children' => [
                                        [
                                            'type' => 'heading',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'text' => 'Related Posts',
                                                'level' => 'h3',
                                                'font_family' => 'Outfit',
                                                'font_size' => '28',
                                                'font_weight' => '700',
                                                'text_color' => $c['text_dark'],
                                                'text_align' => 'center',
                                                'margin' => self::spacing(0, 0, 40, 0),
                                            ],
                                        ],
                                        [
                                            'type' => 'related_posts',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'posts_count' => 3,
                                                'columns' => 3,
                                                'show_image' => true,
                                                'show_date' => true,
                                                'show_excerpt' => false,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function pageWithSidebarBody(): array
    {
        $c = self::colors();

        return [
            'version' => '1.0',
            'content' => [
                // Page Header
                [
                    'type' => 'section',
                    'id' => self::id(),
                    'attrs' => [
                        'background_type' => 'color',
                        'background_color' => $c['primary'],
                        'padding' => self::spacing(60, 40, 60, 40),
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => ['columns' => '1'],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1'],
                                    'children' => [
                                        [
                                            'type' => 'breadcrumbs',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'text_color' => $c['light'],
                                                'separator_color' => $c['accent'],
                                                'margin' => self::spacing(0, 0, 20, 0),
                                            ],
                                        ],
                                        [
                                            'type' => 'post_title',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'level' => 'h1',
                                                'font_family' => 'Outfit',
                                                'font_size' => '48',
                                                'font_weight' => '700',
                                                'text_color' => $c['white'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // Content with Sidebar
                [
                    'type' => 'section',
                    'id' => self::id(),
                    'attrs' => [
                        'background_type' => 'color',
                        'background_color' => $c['white'],
                        'padding' => self::spacing(60, 40, 60, 40),
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => [
                                'columns' => '2_3,1_3',
                                'column_gap' => '40',
                            ],
                            'children' => [
                                // Main Content
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '2_3'],
                                    'children' => [
                                        [
                                            'type' => 'post_content',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'font_family' => 'Outfit',
                                                'font_size' => '16',
                                                'line_height' => '1.7',
                                                'text_color' => $c['text'],
                                            ],
                                        ],
                                    ],
                                ],
                                // Sidebar
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1_3'],
                                    'children' => [
                                        [
                                            'type' => 'sidebar',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'sidebar_id' => 'default',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function fullwidthPageBody(): array
    {
        $c = self::colors();

        return [
            'version' => '1.0',
            'content' => [
                // Hero Header
                [
                    'type' => 'section',
                    'id' => self::id(),
                    'attrs' => [
                        'background_type' => 'color',
                        'background_color' => $c['primary'],
                        'padding' => self::spacing(100, 40, 100, 40),
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => ['columns' => '1'],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1'],
                                    'children' => [
                                        [
                                            'type' => 'post_title',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'level' => 'h1',
                                                'font_family' => 'Outfit',
                                                'font_size' => '56',
                                                'font_weight' => '700',
                                                'text_color' => $c['white'],
                                                'text_align' => 'center',
                                            ],
                                        ],
                                        [
                                            'type' => 'post_excerpt',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'font_family' => 'Outfit',
                                                'font_size' => '20',
                                                'line_height' => '1.6',
                                                'text_color' => $c['light'],
                                                'text_align' => 'center',
                                                'max_width' => '700px',
                                                'margin' => self::spacing(20, 'auto', 0, 'auto'),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // Content Section
                [
                    'type' => 'section',
                    'id' => self::id(),
                    'attrs' => [
                        'background_type' => 'color',
                        'background_color' => $c['white'],
                        'padding' => self::spacing(80, 40, 80, 40),
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => ['columns' => '1', 'max_width' => '900px'],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1'],
                                    'children' => [
                                        [
                                            'type' => 'post_content',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'font_family' => 'Outfit',
                                                'font_size' => '18',
                                                'line_height' => '1.8',
                                                'text_color' => $c['text'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function archiveBody(): array
    {
        $c = self::colors();

        return [
            'version' => '1.0',
            'content' => [
                // Archive Header
                [
                    'type' => 'section',
                    'id' => self::id(),
                    'attrs' => [
                        'background_type' => 'color',
                        'background_color' => $c['primary'],
                        'padding' => self::spacing(80, 40, 80, 40),
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => ['columns' => '1'],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1'],
                                    'children' => [
                                        [
                                            'type' => 'archive_title',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'level' => 'h1',
                                                'font_family' => 'Outfit',
                                                'font_size' => '48',
                                                'font_weight' => '700',
                                                'text_color' => $c['white'],
                                                'text_align' => 'center',
                                            ],
                                        ],
                                        [
                                            'type' => 'text',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'content' => '<p style="text-align:center">Browse our latest articles and updates</p>',
                                                'font_family' => 'Outfit',
                                                'font_size' => '18',
                                                'text_color' => $c['light'],
                                                'margin' => self::spacing(15, 0, 0, 0),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // Posts Grid
                [
                    'type' => 'section',
                    'id' => self::id(),
                    'attrs' => [
                        'background_type' => 'color',
                        'background_color' => $c['bg'],
                        'padding' => self::spacing(60, 40, 60, 40),
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => ['columns' => '1'],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1'],
                                    'children' => [
                                        [
                                            'type' => 'archive_posts',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'layout' => 'grid',
                                                'columns' => 3,
                                                'posts_per_page' => 9,
                                                'show_image' => true,
                                                'show_title' => true,
                                                'show_excerpt' => true,
                                                'show_date' => true,
                                                'show_author' => false,
                                                'show_categories' => true,
                                                'excerpt_length' => 20,
                                                'card_background' => $c['white'],
                                                'card_border_radius' => '12',
                                                'card_padding' => self::spacing(0, 0, 20, 0),
                                                'gap' => '30',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function minimalFooter(): array
    {
        $c = self::colors();

        return [
            'version' => '1.0',
            'content' => [
                [
                    'type' => 'section',
                    'id' => self::id(),
                    'attrs' => [
                        'background_type' => 'color',
                        'background_color' => $c['bg'],
                        'padding' => self::spacing(30, 40, 30, 40),
                        'border_width' => self::spacing(1, 0, 0, 0),
                        'border_color' => $c['light'],
                        'border_style' => 'solid',
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'id' => self::id(),
                            'attrs' => [
                                'columns' => '1_3,1_3,1_3',
                                'vertical_align' => 'center',
                            ],
                            'children' => [
                                // Logo
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1_3'],
                                    'children' => [
                                        [
                                            'type' => 'heading',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'text' => 'Your Logo',
                                                'level' => 'h5',
                                                'font_family' => 'Outfit',
                                                'font_size' => '18',
                                                'font_weight' => '600',
                                                'text_color' => $c['primary'],
                                            ],
                                        ],
                                    ],
                                ],
                                // Copyright
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1_3'],
                                    'children' => [
                                        [
                                            'type' => 'text',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'content' => '<p style="text-align:center">© 2026 Your Company</p>',
                                                'font_family' => 'Outfit',
                                                'font_size' => '14',
                                                'text_color' => $c['text'],
                                            ],
                                        ],
                                    ],
                                ],
                                // Social
                                [
                                    'type' => 'column',
                                    'id' => self::id(),
                                    'attrs' => ['width' => '1_3'],
                                    'children' => [
                                        [
                                            'type' => 'social_follow',
                                            'id' => self::id(),
                                            'attrs' => [
                                                'facebook_url' => '#',
                                                'twitter_url' => '#',
                                                'linkedin_url' => '#',
                                                'icon_color' => $c['text'],
                                                'icon_size' => '18',
                                                'alignment' => 'right',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
