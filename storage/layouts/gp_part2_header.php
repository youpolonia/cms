<?php
/**
 * Golden Plate Layout - Part 2: Header Template
 */

$headerContent = [
    'sections' => [
        [
            'id' => 'section_header',
            'name' => 'Header',
            'design' => [
                'background_color' => 'rgba(10,10,10,0.95)',
                'padding_top' => '15px',
                'padding_bottom' => '15px',
                'position' => 'fixed',
                'width' => '100%',
                'z_index' => '1000',
                'border_bottom' => '1px solid rgba(212,175,55,0.2)'
            ],
            'rows' => [
                [
                    'id' => 'row_header_main',
                    'design' => [
                        'max_width' => '1200px',
                        'margin' => '0 auto',
                        'display' => 'flex',
                        'align_items' => 'center',
                        'justify_content' => 'space-between',
                        'padding' => '0 20px'
                    ],
                    'columns' => [
                        [
                            'id' => 'col_header_logo',
                            'width' => '20%',
                            'modules' => [
                                [
                                    'id' => 'mod_header_logo',
                                    'type' => 'heading',
                                    'content' => [
                                        'text' => 'Golden Plate',
                                        'level' => 'h1'
                                    ],
                                    'design' => [
                                        'font_size' => '24px',
                                        'font_weight' => '700',
                                        'text_color' => 'var(--color-accent)',
                                        'font_family' => 'Playfair Display, serif',
                                        'margin' => '0'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'id' => 'col_header_nav',
                            'width' => '60%',
                            'modules' => [
                                [
                                    'id' => 'mod_header_nav',
                                    'type' => 'list',
                                    'content' => [
                                        'items' => [
                                            ['text' => 'HOME', 'url' => '/'],
                                            ['text' => 'ABOUT', 'url' => '/about'],
                                            ['text' => 'MENU', 'url' => '/menu'],
                                            ['text' => 'GALLERY', 'url' => '/gallery'],
                                            ['text' => 'CONTACT', 'url' => '/contact']
                                        ],
                                        'type' => 'horizontal'
                                    ],
                                    'design' => [
                                        'display' => 'flex',
                                        'gap' => '40px',
                                        'justify_content' => 'center',
                                        'text_color' => 'var(--color-text)',
                                        'font_size' => '13px',
                                        'letter_spacing' => '2px',
                                        'list_style' => 'none'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'id' => 'col_header_cta',
                            'width' => '20%',
                            'modules' => [
                                [
                                    'id' => 'mod_header_btn',
                                    'type' => 'button',
                                    'content' => [
                                        'text' => 'RESERVE',
                                        'url' => '/contact',
                                        'target' => '_self'
                                    ],
                                    'design' => [
                                        'background_color' => 'var(--color-accent)',
                                        'text_color' => 'var(--color-background)',
                                        'padding' => '10px 24px',
                                        'border_radius' => '0',
                                        'font_size' => '12px',
                                        'letter_spacing' => '2px',
                                        'font_weight' => '600',
                                        'text_align' => 'right'
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
