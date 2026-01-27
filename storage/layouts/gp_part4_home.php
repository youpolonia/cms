<?php
/**
 * Golden Plate Layout - Part 4: Home Page
 */

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
                    'background_color' => 'var(--color-background)',
                    'background_image' => img('gp-hero.jpg'),
                    'background_overlay' => 'rgba(10,10,10,0.7)',
                    'background_size' => 'cover',
                    'background_position' => 'center',
                    'padding_top' => '0',
                    'padding_bottom' => '0',
                    'min_height' => '100vh'
                ],
                'rows' => [
                    [
                        'id' => 'row_home_hero',
                        'design' => ['align_items' => 'center', 'justify_content' => 'center', 'min_height' => '100vh'],
                        'columns' => [
                            [
                                'id' => 'col_home_hero',
                                'width' => '100%',
                                'design' => ['padding' => '40px', 'text_align' => 'center'],
                                'modules' => [
                                    [
                                        'id' => 'mod_home_hero_tag',
                                        'type' => 'text',
                                        'content' => ['text' => '★ MICHELIN RECOMMENDED ★'],
                                        'design' => ['text_align' => 'center', 'font_size' => '14px', 'letter_spacing' => '4px', 'text_color' => 'var(--color-accent)', 'margin_bottom' => '25px']
                                    ],
                                    [
                                        'id' => 'mod_home_hero_title',
                                        'type' => 'heading',
                                        'content' => ['text' => 'Experience Culinary Excellence', 'level' => 'h1'],
                                        'design' => ['text_align' => 'center', 'font_size' => '68px', 'font_weight' => '400', 'text_color' => 'var(--color-text)', 'letter_spacing' => '2px', 'margin_bottom' => '24px', 'font_family' => 'Playfair Display, serif']
                                    ],
                                    [
                                        'id' => 'mod_home_hero_sub',
                                        'type' => 'text',
                                        'content' => ['text' => 'Where tradition meets innovation. Indulge in exquisite flavors crafted by world-renowned chefs in an atmosphere of timeless elegance.'],
                                        'design' => ['text_align' => 'center', 'font_size' => '18px', 'text_color' => 'var(--color-text)', 'max_width' => '600px', 'margin' => '0 auto 40px']
                                    ],
                                    [
                                        'id' => 'mod_home_hero_divider',
                                        'type' => 'divider',
                                        'content' => ['show_divider' => true],
                                        'design' => ['color' => 'var(--color-accent)', 'width' => '80px', 'weight' => '1px', 'margin' => '0 auto 40px']
                                    ],
                                    [
                                        'id' => 'mod_home_hero_btn1',
                                        'type' => 'button',
                                        'content' => ['text' => 'RESERVE A TABLE', 'url' => '/contact'],
                                        'design' => ['background_color' => 'var(--color-accent)', 'text_color' => 'var(--color-background)', 'padding' => '18px 40px', 'font_size' => '14px', 'letter_spacing' => '2px', 'margin_right' => '20px']
                                    ],
                                    [
                                        'id' => 'mod_home_hero_btn2',
                                        'type' => 'button',
                                        'content' => ['text' => 'VIEW MENU', 'url' => '/menu'],
                                        'design' => ['background_color' => 'transparent', 'text_color' => 'var(--color-text)', 'border' => '1px solid var(--color-text)', 'padding' => '18px 40px', 'font_size' => '14px', 'letter_spacing' => '2px']
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
                'design' => ['background_color' => 'var(--color-surface)', 'padding_top' => '80px', 'padding_bottom' => '80px'],
                'rows' => [
                    [
                        'id' => 'row_features',
                        'design' => ['max_width' => '1200px', 'margin' => '0 auto', 'gap' => '40px'],
                        'columns' => [
                            [
                                'id' => 'col_feat1', 'width' => '25%',
                                'modules' => [[
                                    'id' => 'mod_feat1', 'type' => 'blurb',
                                    'content' => ['icon' => 'fas fa-award', 'title' => 'Award Winning', 'text' => 'Recognized by top culinary institutions worldwide'],
                                    'design' => ['alignment' => 'center', 'icon_size' => '48px', 'icon_color' => 'var(--color-accent)', 'title_color' => 'var(--color-text)', 'text_color' => 'var(--color-text-muted)']
                                ]]
                            ],
                            [
                                'id' => 'col_feat2', 'width' => '25%',
                                'modules' => [[
                                    'id' => 'mod_feat2', 'type' => 'blurb',
                                    'content' => ['icon' => 'fas fa-leaf', 'title' => 'Fresh Ingredients', 'text' => 'Locally sourced, organic produce daily'],
                                    'design' => ['alignment' => 'center', 'icon_size' => '48px', 'icon_color' => 'var(--color-accent)', 'title_color' => 'var(--color-text)', 'text_color' => 'var(--color-text-muted)']
                                ]]
                            ],
                            [
                                'id' => 'col_feat3', 'width' => '25%',
                                'modules' => [[
                                    'id' => 'mod_feat3', 'type' => 'blurb',
                                    'content' => ['icon' => 'fas fa-utensils', 'title' => 'Master Chefs', 'text' => '30+ years of combined experience'],
                                    'design' => ['alignment' => 'center', 'icon_size' => '48px', 'icon_color' => 'var(--color-accent)', 'title_color' => 'var(--color-text)', 'text_color' => 'var(--color-text-muted)']
                                ]]
                            ],
                            [
                                'id' => 'col_feat4', 'width' => '25%',
                                'modules' => [[
                                    'id' => 'mod_feat4', 'type' => 'blurb',
                                    'content' => ['icon' => 'fas fa-wine-glass-alt', 'title' => 'Fine Wines', 'text' => 'Curated selection from world vineyards'],
                                    'design' => ['alignment' => 'center', 'icon_size' => '48px', 'icon_color' => 'var(--color-accent)', 'title_color' => 'var(--color-text)', 'text_color' => 'var(--color-text-muted)']
                                ]]
                            ]
                        ]
                    ]
                ]
            ],
            // ABOUT PREVIEW
            [
                'id' => 'section_home_about',
                'name' => 'About Preview',
                'design' => ['background_color' => 'var(--color-background)', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'row_about',
                        'design' => ['max_width' => '1200px', 'margin' => '0 auto', 'gap' => '60px', 'align_items' => 'center'],
                        'columns' => [
                            [
                                'id' => 'col_about_img', 'width' => '50%',
                                'modules' => [[
                                    'id' => 'mod_about_img', 'type' => 'image',
                                    'content' => ['src' => img('gp-chef.jpg'), 'alt' => 'Chef at Work'],
                                    'design' => ['width' => '100%', 'height' => '450px', 'object_fit' => 'cover']
                                ]]
                            ],
                            [
                                'id' => 'col_about_text', 'width' => '50%',
                                'modules' => [
                                    ['id' => 'mod_about_tag', 'type' => 'text', 'content' => ['text' => 'OUR STORY'], 'design' => ['font_size' => '12px', 'letter_spacing' => '4px', 'text_color' => 'var(--color-accent)', 'margin_bottom' => '15px']],
                                    ['id' => 'mod_about_title', 'type' => 'heading', 'content' => ['text' => 'A Legacy of Culinary Mastery', 'level' => 'h2'], 'design' => ['font_size' => '42px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '24px']],
                                    ['id' => 'mod_about_text', 'type' => 'text', 'content' => ['text' => 'Since 1985, Golden Plate has been a beacon of fine dining excellence. Founded by Chef Marcel Dubois, our restaurant combines classical French techniques with modern innovation. Every dish tells a story of passion, precision, and the finest ingredients.'], 'design' => ['font_size' => '16px', 'text_color' => 'var(--color-text-muted)', 'line_height' => '1.8', 'margin_bottom' => '30px']],
                                    ['id' => 'mod_about_btn', 'type' => 'button', 'content' => ['text' => 'LEARN MORE', 'url' => '/about'], 'design' => ['background_color' => 'transparent', 'text_color' => 'var(--color-accent)', 'border' => '1px solid var(--color-accent)', 'padding' => '14px 32px', 'font_size' => '12px', 'letter_spacing' => '2px']]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // MENU PREVIEW
            [
                'id' => 'section_home_menu',
                'name' => 'Menu Preview',
                'design' => ['background_color' => 'var(--color-surface)', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'row_menu_header',
                        'columns' => [[
                            'id' => 'col_menu_header', 'width' => '100%',
                            'modules' => [
                                ['id' => 'mod_menu_tag', 'type' => 'text', 'content' => ['text' => 'CULINARY EXCELLENCE'], 'design' => ['text_align' => 'center', 'font_size' => '12px', 'letter_spacing' => '4px', 'text_color' => 'var(--color-accent)', 'margin_bottom' => '15px']],
                                ['id' => 'mod_menu_title', 'type' => 'heading', 'content' => ['text' => 'Signature Dishes', 'level' => 'h2'], 'design' => ['text_align' => 'center', 'font_size' => '48px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '60px']]
                            ]
                        ]]
                    ],
                    [
                        'id' => 'row_menu_items',
                        'design' => ['max_width' => '1200px', 'margin' => '0 auto', 'gap' => '30px'],
                        'columns' => [
                            [
                                'id' => 'col_dish1', 'width' => '33.33%',
                                'modules' => [
                                    ['id' => 'mod_dish1_img', 'type' => 'image', 'content' => ['src' => img('gp-dish1.jpg'), 'alt' => 'Wagyu'], 'design' => ['width' => '100%', 'height' => '250px', 'object_fit' => 'cover', 'margin_bottom' => '20px']],
                                    ['id' => 'mod_dish1_title', 'type' => 'heading', 'content' => ['text' => 'A5 Wagyu Ribeye', 'level' => 'h3'], 'design' => ['text_align' => 'center', 'font_size' => '22px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '10px']],
                                    ['id' => 'mod_dish1_desc', 'type' => 'text', 'content' => ['text' => 'Japanese wagyu, truffle butter, seasonal vegetables'], 'design' => ['text_align' => 'center', 'font_size' => '14px', 'text_color' => 'var(--color-text-muted)', 'margin_bottom' => '10px']],
                                    ['id' => 'mod_dish1_price', 'type' => 'text', 'content' => ['text' => '$128'], 'design' => ['text_align' => 'center', 'font_size' => '20px', 'text_color' => 'var(--color-accent)']]
                                ]
                            ],
                            [
                                'id' => 'col_dish2', 'width' => '33.33%',
                                'modules' => [
                                    ['id' => 'mod_dish2_img', 'type' => 'image', 'content' => ['src' => img('gp-dish2.jpg'), 'alt' => 'Lobster'], 'design' => ['width' => '100%', 'height' => '250px', 'object_fit' => 'cover', 'margin_bottom' => '20px']],
                                    ['id' => 'mod_dish2_title', 'type' => 'heading', 'content' => ['text' => 'Maine Lobster', 'level' => 'h3'], 'design' => ['text_align' => 'center', 'font_size' => '22px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '10px']],
                                    ['id' => 'mod_dish2_desc', 'type' => 'text', 'content' => ['text' => 'Butter-poached, champagne beurre blanc'], 'design' => ['text_align' => 'center', 'font_size' => '14px', 'text_color' => 'var(--color-text-muted)', 'margin_bottom' => '10px']],
                                    ['id' => 'mod_dish2_price', 'type' => 'text', 'content' => ['text' => '$95'], 'design' => ['text_align' => 'center', 'font_size' => '20px', 'text_color' => 'var(--color-accent)']]
                                ]
                            ],
                            [
                                'id' => 'col_dish3', 'width' => '33.33%',
                                'modules' => [
                                    ['id' => 'mod_dish3_img', 'type' => 'image', 'content' => ['src' => img('gp-dish3.jpg'), 'alt' => 'Risotto'], 'design' => ['width' => '100%', 'height' => '250px', 'object_fit' => 'cover', 'margin_bottom' => '20px']],
                                    ['id' => 'mod_dish3_title', 'type' => 'heading', 'content' => ['text' => 'Black Truffle Risotto', 'level' => 'h3'], 'design' => ['text_align' => 'center', 'font_size' => '22px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '10px']],
                                    ['id' => 'mod_dish3_desc', 'type' => 'text', 'content' => ['text' => 'Carnaroli rice, Périgord truffle, parmesan'], 'design' => ['text_align' => 'center', 'font_size' => '14px', 'text_color' => 'var(--color-text-muted)', 'margin_bottom' => '10px']],
                                    ['id' => 'mod_dish3_price', 'type' => 'text', 'content' => ['text' => '$68'], 'design' => ['text_align' => 'center', 'font_size' => '20px', 'text_color' => 'var(--color-accent)']]
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'row_menu_cta',
                        'design' => ['margin_top' => '50px'],
                        'columns' => [[
                            'id' => 'col_menu_cta', 'width' => '100%',
                            'modules' => [['id' => 'mod_menu_btn', 'type' => 'button', 'content' => ['text' => 'VIEW FULL MENU', 'url' => '/menu'], 'design' => ['text_align' => 'center', 'background_color' => 'var(--color-accent)', 'text_color' => 'var(--color-background)', 'padding' => '16px 40px', 'font_size' => '13px', 'letter_spacing' => '2px']]]
                        ]]
                    ]
                ]
            ],
            // TESTIMONIALS
            [
                'id' => 'section_home_testimonials',
                'name' => 'Testimonials',
                'design' => ['background_color' => 'var(--color-background)', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'row_test_header',
                        'columns' => [[
                            'id' => 'col_test_header', 'width' => '100%',
                            'modules' => [
                                ['id' => 'mod_test_tag', 'type' => 'text', 'content' => ['text' => 'GUEST REVIEWS'], 'design' => ['text_align' => 'center', 'font_size' => '12px', 'letter_spacing' => '4px', 'text_color' => 'var(--color-accent)', 'margin_bottom' => '15px']],
                                ['id' => 'mod_test_title', 'type' => 'heading', 'content' => ['text' => 'What People Say', 'level' => 'h2'], 'design' => ['text_align' => 'center', 'font_size' => '48px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '60px']]
                            ]
                        ]]
                    ],
                    [
                        'id' => 'row_testimonials',
                        'design' => ['max_width' => '1200px', 'margin' => '0 auto', 'gap' => '30px'],
                        'columns' => [
                            [
                                'id' => 'col_test1', 'width' => '33.33%',
                                'modules' => [[
                                    'id' => 'mod_test1', 'type' => 'testimonial',
                                    'content' => ['text' => 'An extraordinary dining experience. The wagyu was perfection, and the wine pairing elevated every course.', 'author' => 'Sarah Mitchell', 'role' => 'Food Critic, NY Times', 'avatar' => img('gp-testimonial1.jpg')],
                                    'design' => ['background_color' => 'var(--color-surface)', 'text_color' => 'var(--color-text)', 'author_color' => 'var(--color-text)', 'role_color' => 'var(--color-accent)', 'padding' => '30px']
                                ]]
                            ],
                            [
                                'id' => 'col_test2', 'width' => '33.33%',
                                'modules' => [[
                                    'id' => 'mod_test2', 'type' => 'testimonial',
                                    'content' => ['text' => 'We celebrated our anniversary here. The ambiance, service, and food were all impeccable. A night to remember.', 'author' => 'James & Emily Parker', 'role' => 'Loyal Patrons', 'avatar' => img('gp-testimonial2.jpg')],
                                    'design' => ['background_color' => 'var(--color-surface)', 'text_color' => 'var(--color-text)', 'author_color' => 'var(--color-text)', 'role_color' => 'var(--color-accent)', 'padding' => '30px']
                                ]]
                            ],
                            [
                                'id' => 'col_test3', 'width' => '33.33%',
                                'modules' => [[
                                    'id' => 'mod_test3', 'type' => 'testimonial',
                                    'content' => ['text' => 'As a chef myself, I was blown away by the technique and creativity. Chef Dubois is a true master of his craft.', 'author' => 'Marco Chen', 'role' => 'Executive Chef', 'avatar' => img('gp-testimonial3.jpg')],
                                    'design' => ['background_color' => 'var(--color-surface)', 'text_color' => 'var(--color-text)', 'author_color' => 'var(--color-text)', 'role_color' => 'var(--color-accent)', 'padding' => '30px']
                                ]]
                            ]
                        ]
                    ]
                ]
            ],
            // CTA
            [
                'id' => 'section_home_cta',
                'name' => 'Call to Action',
                'design' => ['background_color' => 'var(--color-surface)', 'background_image' => img('gp-interior.jpg'), 'background_overlay' => 'rgba(10,10,10,0.9)', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [[
                    'id' => 'row_cta',
                    'columns' => [[
                        'id' => 'col_cta', 'width' => '100%',
                        'modules' => [
                            ['id' => 'mod_cta_divider', 'type' => 'divider', 'content' => ['show_divider' => true], 'design' => ['color' => 'var(--color-accent)', 'width' => '60px', 'margin' => '0 auto 30px']],
                            ['id' => 'mod_cta_title', 'type' => 'heading', 'content' => ['text' => 'Ready for an Unforgettable Evening?', 'level' => 'h2'], 'design' => ['text_align' => 'center', 'font_size' => '42px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '20px']],
                            ['id' => 'mod_cta_text', 'type' => 'text', 'content' => ['text' => 'Reserve your table today and let us create a memorable dining experience.'], 'design' => ['text_align' => 'center', 'font_size' => '17px', 'text_color' => 'var(--color-text-muted)', 'margin_bottom' => '35px']],
                            ['id' => 'mod_cta_btn', 'type' => 'button', 'content' => ['text' => 'MAKE A RESERVATION', 'url' => '/contact'], 'design' => ['text_align' => 'center', 'background_color' => 'var(--color-accent)', 'text_color' => 'var(--color-background)', 'padding' => '18px 40px', 'font_size' => '14px', 'letter_spacing' => '2px']]
                        ]
                    ]]
                ]]
            ]
        ]
    ]
];
