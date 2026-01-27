<?php
// ============================================================================
// PAGE 2: ABOUT
// ============================================================================
$aboutPage = [
    'title' => 'About Us',
    'slug' => 'about',
    'is_homepage' => false,
    'status' => 'draft',
    'content' => [
        'sections' => [
            // PAGE HEADER
            [
                'id' => 'section_about_hero',
                'name' => 'Page Header',
                'design' => [
                    'background_color' => '#0f0f0f',
                    'background_image' => 'https://images.pexels.com/photos/2403391/pexels-photo-2403391.jpeg?auto=compress&cs=tinysrgb&w=1920',
                    'background_overlay' => 'rgba(15,15,15,0.8)',
                    'padding_top' => '160px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'row_about_hero_1',
                        'columns' => [
                            [
                                'id' => 'col_about_hero_1',
                                'width' => '100%',
                                'modules' => [
                                    [
                                        'id' => 'mod_about_hero_tagline',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'OUR HERITAGE'
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
                                        'id' => 'mod_about_hero_heading',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'About Savour',
                                            'level' => 'h1'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '64px',
                                            'font_weight' => '400',
                                            'text_color' => '#ffffff',
                                            'font_family' => 'Playfair Display, serif'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // OUR STORY
            [
                'id' => 'section_about_story',
                'name' => 'Our Story',
                'design' => [
                    'background_color' => '#0f0f0f',
                    'padding_top' => '120px',
                    'padding_bottom' => '120px'
                ],
                'rows' => [
                    [
                        'id' => 'row_about_story_1',
                        'design' => [
                            'max_width' => '1200px',
                            'margin' => '0 auto',
                            'gap' => '80px',
                            'align_items' => 'center'
                        ],
                        'columns' => [
                            [
                                'id' => 'col_about_story_img',
                                'width' => '45%',
                                'modules' => [
                                    [
                                        'id' => 'mod_about_story_img',
                                        'type' => 'image',
                                        'content' => [
                                            'src' => 'https://images.pexels.com/photos/3338497/pexels-photo-3338497.jpeg?auto=compress&cs=tinysrgb&w=800',
                                            'alt' => 'Our Founder'
                                        ],
                                        'design' => [
                                            'width' => '100%',
                                            'height' => '500px',
                                            'object_fit' => 'cover'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'col_about_story_text',
                                'width' => '55%',
                                'modules' => [
                                    [
                                        'id' => 'mod_about_story_tagline',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'SINCE 1985'
                                        ],
                                        'design' => [
                                            'font_size' => '12px',
                                            'letter_spacing' => '4px',
                                            'text_color' => '#d4af37',
                                            'margin_bottom' => '20px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_about_story_heading',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'A Legacy of Excellence',
                                            'level' => 'h2'
                                        ],
                                        'design' => [
                                            'font_size' => '42px',
                                            'font_weight' => '400',
                                            'text_color' => '#ffffff',
                                            'margin_bottom' => '28px',
                                            'font_family' => 'Playfair Display, serif'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_about_story_text1',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'Savour was born from the vision of Chef Henri Montclair, who trained in the legendary kitchens of Paris and Lyon before bringing his expertise to create something truly unique.'
                                        ],
                                        'design' => [
                                            'font_size' => '17px',
                                            'line_height' => '1.8',
                                            'text_color' => '#999999',
                                            'margin_bottom' => '20px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_about_story_text2',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'For nearly four decades, we have honored his commitment to perfection — sourcing the finest ingredients, mastering classical techniques, and continuously innovating to delight our guests.'
                                        ],
                                        'design' => [
                                            'font_size' => '17px',
                                            'line_height' => '1.8',
                                            'text_color' => '#999999'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // PHILOSOPHY
            [
                'id' => 'section_about_philosophy',
                'name' => 'Philosophy',
                'design' => [
                    'background_color' => '#1a1a1a',
                    'padding_top' => '100px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'row_about_philosophy_1',
                        'design' => [
                            'max_width' => '800px',
                            'margin' => '0 auto'
                        ],
                        'columns' => [
                            [
                                'id' => 'col_about_philosophy_1',
                                'width' => '100%',
                                'modules' => [
                                    [
                                        'id' => 'mod_about_philosophy_heading',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'Our Philosophy',
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
                                        'id' => 'mod_about_philosophy_text',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'We believe that exceptional dining is an art form. Every element — from the carefully selected ingredients to the precise techniques, from the elegant presentation to the warm service — contributes to an experience that nourishes not just the body, but the soul.'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '20px',
                                            'line_height' => '1.8',
                                            'text_color' => '#e8e8e8',
                                            'font_family' => 'Playfair Display, serif',
                                            'font_style' => 'italic'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // TEAM SECTION
            [
                'id' => 'section_about_team',
                'name' => 'Our Team',
                'design' => [
                    'background_color' => '#0f0f0f',
                    'padding_top' => '120px',
                    'padding_bottom' => '120px'
                ],
                'rows' => [
                    [
                        'id' => 'row_about_team_header',
                        'columns' => [
                            [
                                'id' => 'col_about_team_header',
                                'width' => '100%',
                                'modules' => [
                                    [
                                        'id' => 'mod_about_team_tagline',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'CULINARY MASTERS'
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
                                        'id' => 'mod_about_team_heading',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'Meet Our Team',
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
                    [
                        'id' => 'row_about_team_members',
                        'design' => [
                            'max_width' => '1100px',
                            'margin' => '0 auto',
                            'gap' => '50px'
                        ],
                        'columns' => [
                            [
                                'id' => 'col_about_team_1',
                                'width' => '33.33%',
                                'modules' => [
                                    [
                                        'id' => 'mod_about_team_1',
                                        'type' => 'team',
                                        'content' => [
                                            'name' => 'Marcus Laurent',
                                            'role' => 'Executive Chef',
                                            'bio' => 'Michelin-starred chef with 25 years of experience across Europe\'s finest restaurants.',
                                            'image' => 'https://images.pexels.com/photos/3814446/pexels-photo-3814446.jpeg?auto=compress&cs=tinysrgb&w=400',
                                            'social' => []
                                        ],
                                        'design' => [
                                            'style' => 'minimal',
                                            'image_size' => '100%',
                                            'alignment' => 'center',
                                            'name_color' => '#ffffff',
                                            'role_color' => '#d4af37',
                                            'bio_color' => '#999999'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'col_about_team_2',
                                'width' => '33.33%',
                                'modules' => [
                                    [
                                        'id' => 'mod_about_team_2',
                                        'type' => 'team',
                                        'content' => [
                                            'name' => 'Elena Rossi',
                                            'role' => 'Pastry Chef',
                                            'bio' => 'Master of French pastry arts, creating desserts that are both visual and culinary masterpieces.',
                                            'image' => 'https://images.pexels.com/photos/3771120/pexels-photo-3771120.jpeg?auto=compress&cs=tinysrgb&w=400',
                                            'social' => []
                                        ],
                                        'design' => [
                                            'style' => 'minimal',
                                            'image_size' => '100%',
                                            'alignment' => 'center',
                                            'name_color' => '#ffffff',
                                            'role_color' => '#d4af37',
                                            'bio_color' => '#999999'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'col_about_team_3',
                                'width' => '33.33%',
                                'modules' => [
                                    [
                                        'id' => 'mod_about_team_3',
                                        'type' => 'team',
                                        'content' => [
                                            'name' => 'Jacques Dubois',
                                            'role' => 'Head Sommelier',
                                            'bio' => 'World-renowned wine expert curating our collection of over 500 exceptional labels.',
                                            'image' => 'https://images.pexels.com/photos/8105118/pexels-photo-8105118.jpeg?auto=compress&cs=tinysrgb&w=400',
                                            'social' => []
                                        ],
                                        'design' => [
                                            'style' => 'minimal',
                                            'image_size' => '100%',
                                            'alignment' => 'center',
                                            'name_color' => '#ffffff',
                                            'role_color' => '#d4af37',
                                            'bio_color' => '#999999'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // AWARDS
            [
                'id' => 'section_about_awards',
                'name' => 'Awards',
                'design' => [
                    'background_color' => '#1a1a1a',
                    'padding_top' => '100px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'row_about_awards_1',
                        'design' => [
                            'max_width' => '1000px',
                            'margin' => '0 auto',
                            'gap' => '60px'
                        ],
                        'columns' => [
                            [
                                'id' => 'col_about_award_1',
                                'width' => '25%',
                                'modules' => [
                                    [
                                        'id' => 'mod_about_counter_1',
                                        'type' => 'counter',
                                        'content' => [
                                            'number' => 3,
                                            'prefix' => '',
                                            'suffix' => '',
                                            'title' => 'Michelin Stars'
                                        ],
                                        'design' => [
                                            'number_size' => '56px',
                                            'number_color' => '#d4af37',
                                            'title_size' => '14px',
                                            'title_color' => '#999999',
                                            'alignment' => 'center'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'col_about_award_2',
                                'width' => '25%',
                                'modules' => [
                                    [
                                        'id' => 'mod_about_counter_2',
                                        'type' => 'counter',
                                        'content' => [
                                            'number' => 38,
                                            'prefix' => '',
                                            'suffix' => '',
                                            'title' => 'Years of Excellence'
                                        ],
                                        'design' => [
                                            'number_size' => '56px',
                                            'number_color' => '#d4af37',
                                            'title_size' => '14px',
                                            'title_color' => '#999999',
                                            'alignment' => 'center'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'col_about_award_3',
                                'width' => '25%',
                                'modules' => [
                                    [
                                        'id' => 'mod_about_counter_3',
                                        'type' => 'counter',
                                        'content' => [
                                            'number' => 500,
                                            'prefix' => '',
                                            'suffix' => '+',
                                            'title' => 'Wine Labels'
                                        ],
                                        'design' => [
                                            'number_size' => '56px',
                                            'number_color' => '#d4af37',
                                            'title_size' => '14px',
                                            'title_color' => '#999999',
                                            'alignment' => 'center'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'col_about_award_4',
                                'width' => '25%',
                                'modules' => [
                                    [
                                        'id' => 'mod_about_counter_4',
                                        'type' => 'counter',
                                        'content' => [
                                            'number' => 50000,
                                            'prefix' => '',
                                            'suffix' => '+',
                                            'title' => 'Guests Served Yearly'
                                        ],
                                        'design' => [
                                            'number_size' => '56px',
                                            'number_color' => '#d4af37',
                                            'title_size' => '14px',
                                            'title_color' => '#999999',
                                            'alignment' => 'center'
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

// ============================================================================
// PAGE 3: MENU
// ============================================================================
$menuPage = [
    'title' => 'Menu',
    'slug' => 'menu',
    'is_homepage' => false,
    'status' => 'draft',
    'content' => [
        'sections' => [
            // PAGE HEADER
            [
                'id' => 'section_menu_hero',
                'name' => 'Page Header',
                'design' => [
                    'background_color' => '#0f0f0f',
                    'background_image' => 'https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg?auto=compress&cs=tinysrgb&w=1920',
                    'background_overlay' => 'rgba(15,15,15,0.8)',
                    'padding_top' => '160px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'row_menu_hero_1',
                        'columns' => [
                            [
                                'id' => 'col_menu_hero_1',
                                'width' => '100%',
                                'modules' => [
                                    [
                                        'id' => 'mod_menu_hero_tagline',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'CULINARY ARTISTRY'
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
                                        'id' => 'mod_menu_hero_heading',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'Our Menu',
                                            'level' => 'h1'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '64px',
                                            'font_weight' => '400',
                                            'text_color' => '#ffffff',
                                            'font_family' => 'Playfair Display, serif'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // APPETIZERS
            [
                'id' => 'section_menu_appetizers',
                'name' => 'Appetizers',
                'design' => [
                    'background_color' => '#0f0f0f',
                    'padding_top' => '100px',
                    'padding_bottom' => '60px'
                ],
                'rows' => [
                    [
                        'id' => 'row_menu_appetizers_header',
                        'columns' => [
                            [
                                'id' => 'col_menu_appetizers_header',
                                'width' => '100%',
                                'modules' => [
                                    [
                                        'id' => 'mod_menu_appetizers_heading',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'Appetizers',
                                            'level' => 'h2'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '36px',
                                            'font_weight' => '400',
                                            'text_color' => '#d4af37',
                                            'margin_bottom' => '50px',
                                            'font_family' => 'Playfair Display, serif'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'row_menu_appetizers_items',
                        'design' => [
                            'max_width' => '900px',
                            'margin' => '0 auto'
                        ],
                        'columns' => [
                            [
                                'id' => 'col_menu_appetizers_left',
                                'width' => '50%',
                                'modules' => [
                                    [
                                        'id' => 'mod_menu_app_1',
                                        'type' => 'pricing',
                                        'content' => [
                                            'title' => 'Tuna Tartare',
                                            'price' => '$28',
                                            'period' => '',
                                            'features' => ['Yellowfin tuna, avocado, sesame, wasabi aioli'],
                                            'button_text' => '',
                                            'button_url' => ''
                                        ],
                                        'design' => [
                                            'highlighted' => false,
                                            'background_color' => 'transparent',
                                            'title_color' => '#ffffff',
                                            'price_color' => '#d4af37',
                                            'feature_color' => '#999999'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_menu_app_2',
                                        'type' => 'pricing',
                                        'content' => [
                                            'title' => 'Foie Gras Torchon',
                                            'price' => '$42',
                                            'period' => '',
                                            'features' => ['House-made brioche, fig compote, Sauternes gelée'],
                                            'button_text' => '',
                                            'button_url' => ''
                                        ],
                                        'design' => [
                                            'highlighted' => false,
                                            'background_color' => 'transparent',
                                            'title_color' => '#ffffff',
                                            'price_color' => '#d4af37',
                                            'feature_color' => '#999999'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'col_menu_appetizers_right',
                                'width' => '50%',
                                'modules' => [
                                    [
                                        'id' => 'mod_menu_app_3',
                                        'type' => 'pricing',
                                        'content' => [
                                            'title' => 'Burrata Caprese',
                                            'price' => '$24',
                                            'period' => '',
                                            'features' => ['Heirloom tomatoes, aged balsamic, micro basil'],
                                            'button_text' => '',
                                            'button_url' => ''
                                        ],
                                        'design' => [
                                            'highlighted' => false,
                                            'background_color' => 'transparent',
                                            'title_color' => '#ffffff',
                                            'price_color' => '#d4af37',
                                            'feature_color' => '#999999'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_menu_app_4',
                                        'type' => 'pricing',
                                        'content' => [
                                            'title' => 'Oysters on the Half Shell',
                                            'price' => '$36',
                                            'period' => '',
                                            'features' => ['Six Kumamoto oysters, mignonette, lemon'],
                                            'button_text' => '',
                                            'button_url' => ''
                                        ],
                                        'design' => [
                                            'highlighted' => false,
                                            'background_color' => 'transparent',
                                            'title_color' => '#ffffff',
                                            'price_color' => '#d4af37',
                                            'feature_color' => '#999999'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // MAIN COURSES
            [
                'id' => 'section_menu_mains',
                'name' => 'Main Courses',
                'design' => [
                    'background_color' => '#1a1a1a',
                    'padding_top' => '80px',
                    'padding_bottom' => '80px'
                ],
                'rows' => [
                    [
                        'id' => 'row_menu_mains_header',
                        'columns' => [
                            [
                                'id' => 'col_menu_mains_header',
                                'width' => '100%',
                                'modules' => [
                                    [
                                        'id' => 'mod_menu_mains_heading',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'Main Courses',
                                            'level' => 'h2'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '36px',
                                            'font_weight' => '400',
                                            'text_color' => '#d4af37',
                                            'margin_bottom' => '50px',
                                            'font_family' => 'Playfair Display, serif'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'row_menu_mains_items',
                        'design' => [
                            'max_width' => '900px',
                            'margin' => '0 auto'
                        ],
                        'columns' => [
                            [
                                'id' => 'col_menu_mains_left',
                                'width' => '50%',
                                'modules' => [
                                    [
                                        'id' => 'mod_menu_main_1',
                                        'type' => 'pricing',
                                        'content' => [
                                            'title' => 'A5 Wagyu Ribeye',
                                            'price' => '$145',
                                            'period' => '',
                                            'features' => ['Japanese wagyu, truffle jus, bone marrow butter'],
                                            'button_text' => '',
                                            'button_url' => ''
                                        ],
                                        'design' => [
                                            'highlighted' => false,
                                            'background_color' => 'transparent',
                                            'title_color' => '#ffffff',
                                            'price_color' => '#d4af37',
                                            'feature_color' => '#999999'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_menu_main_2',
                                        'type' => 'pricing',
                                        'content' => [
                                            'title' => 'Maine Lobster',
                                            'price' => '$98',
                                            'period' => '',
                                            'features' => ['Butter-poached, champagne beurre blanc, caviar'],
                                            'button_text' => '',
                                            'button_url' => ''
                                        ],
                                        'design' => [
                                            'highlighted' => false,
                                            'background_color' => 'transparent',
                                            'title_color' => '#ffffff',
                                            'price_color' => '#d4af37',
                                            'feature_color' => '#999999'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_menu_main_3',
                                        'type' => 'pricing',
                                        'content' => [
                                            'title' => 'Rack of Lamb',
                                            'price' => '$72',
                                            'period' => '',
                                            'features' => ['Colorado lamb, herb crust, mint pesto'],
                                            'button_text' => '',
                                            'button_url' => ''
                                        ],
                                        'design' => [
                                            'highlighted' => false,
                                            'background_color' => 'transparent',
                                            'title_color' => '#ffffff',
                                            'price_color' => '#d4af37',
                                            'feature_color' => '#999999'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'col_menu_mains_right',
                                'width' => '50%',
                                'modules' => [
                                    [
                                        'id' => 'mod_menu_main_4',
                                        'type' => 'pricing',
                                        'content' => [
                                            'title' => 'Chilean Sea Bass',
                                            'price' => '$68',
                                            'period' => '',
                                            'features' => ['Miso-glazed, bok choy, ginger broth'],
                                            'button_text' => '',
                                            'button_url' => ''
                                        ],
                                        'design' => [
                                            'highlighted' => false,
                                            'background_color' => 'transparent',
                                            'title_color' => '#ffffff',
                                            'price_color' => '#d4af37',
                                            'feature_color' => '#999999'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_menu_main_5',
                                        'type' => 'pricing',
                                        'content' => [
                                            'title' => 'Duck Breast',
                                            'price' => '$58',
                                            'period' => '',
                                            'features' => ['Hudson Valley duck, cherry gastrique, foie gras'],
                                            'button_text' => '',
                                            'button_url' => ''
                                        ],
                                        'design' => [
                                            'highlighted' => false,
                                            'background_color' => 'transparent',
                                            'title_color' => '#ffffff',
                                            'price_color' => '#d4af37',
                                            'feature_color' => '#999999'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_menu_main_6',
                                        'type' => 'pricing',
                                        'content' => [
                                            'title' => 'Black Truffle Risotto',
                                            'price' => '$52',
                                            'period' => '',
                                            'features' => ['Carnaroli rice, Périgord truffle, aged parmesan'],
                                            'button_text' => '',
                                            'button_url' => ''
                                        ],
                                        'design' => [
                                            'highlighted' => false,
                                            'background_color' => 'transparent',
                                            'title_color' => '#ffffff',
                                            'price_color' => '#d4af37',
                                            'feature_color' => '#999999'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // DESSERTS
            [
                'id' => 'section_menu_desserts',
                'name' => 'Desserts',
                'design' => [
                    'background_color' => '#0f0f0f',
                    'padding_top' => '80px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'row_menu_desserts_header',
                        'columns' => [
                            [
                                'id' => 'col_menu_desserts_header',
                                'width' => '100%',
                                'modules' => [
                                    [
                                        'id' => 'mod_menu_desserts_heading',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'Desserts',
                                            'level' => 'h2'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '36px',
                                            'font_weight' => '400',
                                            'text_color' => '#d4af37',
                                            'margin_bottom' => '50px',
                                            'font_family' => 'Playfair Display, serif'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'row_menu_desserts_items',
                        'design' => [
                            'max_width' => '900px',
                            'margin' => '0 auto'
                        ],
                        'columns' => [
                            [
                                'id' => 'col_menu_desserts_left',
                                'width' => '50%',
                                'modules' => [
                                    [
                                        'id' => 'mod_menu_dessert_1',
                                        'type' => 'pricing',
                                        'content' => [
                                            'title' => 'Chocolate Soufflé',
                                            'price' => '$22',
                                            'period' => '',
                                            'features' => ['Valrhona chocolate, crème anglaise'],
                                            'button_text' => '',
                                            'button_url' => ''
                                        ],
                                        'design' => [
                                            'highlighted' => false,
                                            'background_color' => 'transparent',
                                            'title_color' => '#ffffff',
                                            'price_color' => '#d4af37',
                                            'feature_color' => '#999999'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_menu_dessert_2',
                                        'type' => 'pricing',
                                        'content' => [
                                            'title' => 'Crème Brûlée',
                                            'price' => '$18',
                                            'period' => '',
                                            'features' => ['Madagascar vanilla, caramelized sugar'],
                                            'button_text' => '',
                                            'button_url' => ''
                                        ],
                                        'design' => [
                                            'highlighted' => false,
                                            'background_color' => 'transparent',
                                            'title_color' => '#ffffff',
                                            'price_color' => '#d4af37',
                                            'feature_color' => '#999999'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'col_menu_desserts_right',
                                'width' => '50%',
                                'modules' => [
                                    [
                                        'id' => 'mod_menu_dessert_3',
                                        'type' => 'pricing',
                                        'content' => [
                                            'title' => 'Tarte Tatin',
                                            'price' => '$19',
                                            'period' => '',
                                            'features' => ['Caramelized apple, vanilla ice cream'],
                                            'button_text' => '',
                                            'button_url' => ''
                                        ],
                                        'design' => [
                                            'highlighted' => false,
                                            'background_color' => 'transparent',
                                            'title_color' => '#ffffff',
                                            'price_color' => '#d4af37',
                                            'feature_color' => '#999999'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_menu_dessert_4',
                                        'type' => 'pricing',
                                        'content' => [
                                            'title' => 'Artisan Cheese Selection',
                                            'price' => '$32',
                                            'period' => '',
                                            'features' => ['Five curated cheeses, honeycomb, fruits'],
                                            'button_text' => '',
                                            'button_url' => ''
                                        ],
                                        'design' => [
                                            'highlighted' => false,
                                            'background_color' => 'transparent',
                                            'title_color' => '#ffffff',
                                            'price_color' => '#d4af37',
                                            'feature_color' => '#999999'
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
