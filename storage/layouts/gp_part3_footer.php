<?php
/**
 * Golden Plate Layout - Part 3: Footer Template
 */

$footerContent = [
    'sections' => [
        [
            'id' => 'section_footer',
            'name' => 'Footer',
            'design' => [
                'background_color' => 'var(--color-surface)',
                'padding_top' => '80px',
                'padding_bottom' => '30px',
                'border_top' => '1px solid rgba(212,175,55,0.2)'
            ],
            'rows' => [
                [
                    'id' => 'row_footer_main',
                    'design' => [
                        'max_width' => '1200px',
                        'margin' => '0 auto 50px',
                        'padding' => '0 20px',
                        'gap' => '50px'
                    ],
                    'columns' => [
                        [
                            'id' => 'col_footer_brand',
                            'width' => '35%',
                            'modules' => [
                                [
                                    'id' => 'mod_footer_logo',
                                    'type' => 'heading',
                                    'content' => ['text' => 'Golden Plate', 'level' => 'h3'],
                                    'design' => [
                                        'font_size' => '28px',
                                        'text_color' => 'var(--color-accent)',
                                        'font_family' => 'Playfair Display, serif',
                                        'margin_bottom' => '20px'
                                    ]
                                ],
                                [
                                    'id' => 'mod_footer_desc',
                                    'type' => 'text',
                                    'content' => ['text' => 'Experience culinary excellence in an atmosphere of timeless elegance. Where every meal becomes a cherished memory.'],
                                    'design' => [
                                        'font_size' => '14px',
                                        'text_color' => 'var(--color-text-muted)',
                                        'line_height' => '1.7',
                                        'margin_bottom' => '25px'
                                    ]
                                ],
                                [
                                    'id' => 'mod_footer_social',
                                    'type' => 'list',
                                    'content' => [
                                        'items' => [
                                            ['icon' => 'fab fa-facebook-f', 'url' => '#'],
                                            ['icon' => 'fab fa-instagram', 'url' => '#'],
                                            ['icon' => 'fab fa-twitter', 'url' => '#']
                                        ],
                                        'type' => 'social'
                                    ],
                                    'design' => ['icon_color' => 'var(--color-accent)', 'gap' => '15px']
                                ]
                            ]
                        ],
                        [
                            'id' => 'col_footer_links',
                            'width' => '20%',
                            'modules' => [
                                [
                                    'id' => 'mod_footer_links_title',
                                    'type' => 'heading',
                                    'content' => ['text' => 'Quick Links', 'level' => 'h4'],
                                    'design' => ['font_size' => '16px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '20px']
                                ],
                                [
                                    'id' => 'mod_footer_links_list',
                                    'type' => 'list',
                                    'content' => [
                                        'items' => [
                                            ['text' => 'Home', 'url' => '/'],
                                            ['text' => 'About Us', 'url' => '/about'],
                                            ['text' => 'Our Menu', 'url' => '/menu'],
                                            ['text' => 'Gallery', 'url' => '/gallery'],
                                            ['text' => 'Contact', 'url' => '/contact']
                                        ]
                                    ],
                                    'design' => ['text_color' => 'var(--color-text-muted)', 'font_size' => '14px', 'line_height' => '2']
                                ]
                            ]
                        ],
                        [
                            'id' => 'col_footer_services',
                            'width' => '20%',
                            'modules' => [
                                [
                                    'id' => 'mod_footer_services_title',
                                    'type' => 'heading',
                                    'content' => ['text' => 'Services', 'level' => 'h4'],
                                    'design' => ['font_size' => '16px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '20px']
                                ],
                                [
                                    'id' => 'mod_footer_services_list',
                                    'type' => 'list',
                                    'content' => [
                                        'items' => [
                                            ['text' => 'Private Dining', 'url' => '#'],
                                            ['text' => 'Corporate Events', 'url' => '#'],
                                            ['text' => 'Catering', 'url' => '#'],
                                            ['text' => 'Gift Cards', 'url' => '#']
                                        ]
                                    ],
                                    'design' => ['text_color' => 'var(--color-text-muted)', 'font_size' => '14px', 'line_height' => '2']
                                ]
                            ]
                        ],
                        [
                            'id' => 'col_footer_contact',
                            'width' => '25%',
                            'modules' => [
                                [
                                    'id' => 'mod_footer_contact_title',
                                    'type' => 'heading',
                                    'content' => ['text' => 'Contact', 'level' => 'h4'],
                                    'design' => ['font_size' => '16px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '20px']
                                ],
                                [
                                    'id' => 'mod_footer_contact_list',
                                    'type' => 'list',
                                    'content' => [
                                        'items' => [
                                            ['text' => '+1 (212) 555-GOLD'],
                                            ['text' => 'reservations@goldenplate.com'],
                                            ['text' => '123 Gourmet Avenue'],
                                            ['text' => 'Manhattan, NY 10001']
                                        ]
                                    ],
                                    'design' => ['text_color' => 'var(--color-text-muted)', 'font_size' => '14px', 'line_height' => '2']
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'id' => 'row_footer_bottom',
                    'design' => [
                        'max_width' => '1200px',
                        'margin' => '0 auto',
                        'padding' => '25px 20px 0',
                        'border_top' => '1px solid rgba(255,255,255,0.1)',
                        'text_align' => 'center'
                    ],
                    'columns' => [
                        [
                            'id' => 'col_footer_copyright',
                            'width' => '100%',
                            'modules' => [
                                [
                                    'id' => 'mod_footer_copyright',
                                    'type' => 'text',
                                    'content' => ['text' => '© 2025 Golden Plate Fine Dining. All rights reserved.'],
                                    'design' => ['font_size' => '13px', 'text_color' => 'var(--color-text-muted)', 'text_align' => 'center']
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];
