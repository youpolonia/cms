<?php
/**
 * Golden Plate Layout - Part 5: About Page
 */

$aboutPage = [
    'title' => 'About Us',
    'slug' => 'about',
    'is_homepage' => false,
    'status' => 'draft',
    'content' => [
        'sections' => [
            // PAGE HERO
            [
                'id' => 'section_about_hero',
                'name' => 'Page Hero',
                'design' => ['background_color' => 'var(--color-background)', 'background_image' => img('gp-interior.jpg'), 'background_overlay' => 'rgba(10,10,10,0.85)', 'padding_top' => '150px', 'padding_bottom' => '80px'],
                'rows' => [[
                    'id' => 'row_about_hero',
                    'columns' => [[
                        'id' => 'col_about_hero', 'width' => '100%',
                        'modules' => [
                            ['id' => 'mod_about_hero_title', 'type' => 'heading', 'content' => ['text' => 'About Us', 'level' => 'h1'], 'design' => ['text_align' => 'center', 'font_size' => '52px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '15px']],
                            ['id' => 'mod_about_hero_bread', 'type' => 'text', 'content' => ['text' => 'Home / About Us'], 'design' => ['text_align' => 'center', 'font_size' => '14px', 'text_color' => 'var(--color-text-muted)']]
                        ]
                    ]]
                ]]
            ],
            // STORY
            [
                'id' => 'section_about_story',
                'name' => 'Our Story',
                'design' => ['background_color' => 'var(--color-background)', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [[
                    'id' => 'row_story',
                    'design' => ['max_width' => '1200px', 'margin' => '0 auto', 'gap' => '60px', 'align_items' => 'center'],
                    'columns' => [
                        [
                            'id' => 'col_story_text', 'width' => '50%',
                            'modules' => [
                                ['id' => 'mod_story_tag', 'type' => 'text', 'content' => ['text' => 'SINCE 1985'], 'design' => ['font_size' => '12px', 'letter_spacing' => '4px', 'text_color' => 'var(--color-accent)', 'margin_bottom' => '15px']],
                                ['id' => 'mod_story_title', 'type' => 'heading', 'content' => ['text' => 'Our Story Begins in Paris', 'level' => 'h2'], 'design' => ['font_size' => '38px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '24px']],
                                ['id' => 'mod_story_p1', 'type' => 'text', 'content' => ['text' => 'Golden Plate was founded by Chef Marcel Dubois, who trained in the finest kitchens of Paris before bringing his passion for culinary excellence to New York.'], 'design' => ['font_size' => '16px', 'text_color' => 'var(--color-text-muted)', 'line_height' => '1.8', 'margin_bottom' => '18px']],
                                ['id' => 'mod_story_p2', 'type' => 'text', 'content' => ['text' => 'What began as a small bistro has grown into an award-winning destination for food lovers who appreciate the art of fine dining.'], 'design' => ['font_size' => '16px', 'text_color' => 'var(--color-text-muted)', 'line_height' => '1.8', 'margin_bottom' => '18px']],
                                ['id' => 'mod_story_sig', 'type' => 'text', 'content' => ['text' => '— Chef Marcel Dubois'], 'design' => ['font_size' => '22px', 'text_color' => 'var(--color-accent)', 'font_family' => 'Playfair Display, serif', 'font_style' => 'italic', 'margin_top' => '25px']]
                            ]
                        ],
                        [
                            'id' => 'col_story_img', 'width' => '50%',
                            'modules' => [['id' => 'mod_story_img', 'type' => 'image', 'content' => ['src' => img('gp-chef.jpg'), 'alt' => 'Chef Marcel'], 'design' => ['width' => '100%', 'height' => '450px', 'object_fit' => 'cover']]]
                        ]
                    ]
                ]]
            ],
            // VALUES
            [
                'id' => 'section_about_values',
                'name' => 'Values',
                'design' => ['background_color' => 'var(--color-surface)', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'row_values_header',
                        'columns' => [[
                            'id' => 'col_values_header', 'width' => '100%',
                            'modules' => [
                                ['id' => 'mod_values_tag', 'type' => 'text', 'content' => ['text' => 'WHAT WE BELIEVE'], 'design' => ['text_align' => 'center', 'font_size' => '12px', 'letter_spacing' => '4px', 'text_color' => 'var(--color-accent)', 'margin_bottom' => '15px']],
                                ['id' => 'mod_values_title', 'type' => 'heading', 'content' => ['text' => 'Our Core Values', 'level' => 'h2'], 'design' => ['text_align' => 'center', 'font_size' => '42px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '60px']]
                            ]
                        ]]
                    ],
                    [
                        'id' => 'row_values',
                        'design' => ['max_width' => '1200px', 'margin' => '0 auto', 'gap' => '30px'],
                        'columns' => [
                            ['id' => 'col_val1', 'width' => '33.33%', 'modules' => [['id' => 'mod_val1', 'type' => 'blurb', 'content' => ['icon' => 'fas fa-heart', 'title' => 'Passion', 'text' => 'Every dish prepared with love and dedication to create memorable experiences.'], 'design' => ['alignment' => 'center', 'icon_size' => '40px', 'icon_color' => 'var(--color-accent)', 'title_color' => 'var(--color-text)', 'text_color' => 'var(--color-text-muted)', 'background_color' => 'var(--color-background)', 'padding' => '40px']]]],
                            ['id' => 'col_val2', 'width' => '33.33%', 'modules' => [['id' => 'mod_val2', 'type' => 'blurb', 'content' => ['icon' => 'fas fa-gem', 'title' => 'Excellence', 'text' => 'We never compromise on quality. Excellence is our standard in everything.'], 'design' => ['alignment' => 'center', 'icon_size' => '40px', 'icon_color' => 'var(--color-accent)', 'title_color' => 'var(--color-text)', 'text_color' => 'var(--color-text-muted)', 'background_color' => 'var(--color-background)', 'padding' => '40px']]]],
                            ['id' => 'col_val3', 'width' => '33.33%', 'modules' => [['id' => 'mod_val3', 'type' => 'blurb', 'content' => ['icon' => 'fas fa-users', 'title' => 'Hospitality', 'text' => 'Our guests are family. We create an atmosphere of warmth and comfort.'], 'design' => ['alignment' => 'center', 'icon_size' => '40px', 'icon_color' => 'var(--color-accent)', 'title_color' => 'var(--color-text)', 'text_color' => 'var(--color-text-muted)', 'background_color' => 'var(--color-background)', 'padding' => '40px']]]]
                        ]
                    ]
                ]
            ],
            // TEAM
            [
                'id' => 'section_about_team',
                'name' => 'Team',
                'design' => ['background_color' => 'var(--color-background)', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [
                    [
                        'id' => 'row_team_header',
                        'columns' => [[
                            'id' => 'col_team_header', 'width' => '100%',
                            'modules' => [
                                ['id' => 'mod_team_tag', 'type' => 'text', 'content' => ['text' => 'MEET THE TEAM'], 'design' => ['text_align' => 'center', 'font_size' => '12px', 'letter_spacing' => '4px', 'text_color' => 'var(--color-accent)', 'margin_bottom' => '15px']],
                                ['id' => 'mod_team_title', 'type' => 'heading', 'content' => ['text' => 'Our Culinary Artists', 'level' => 'h2'], 'design' => ['text_align' => 'center', 'font_size' => '42px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '60px']]
                            ]
                        ]]
                    ],
                    [
                        'id' => 'row_team',
                        'design' => ['max_width' => '1200px', 'margin' => '0 auto', 'gap' => '30px'],
                        'columns' => [
                            ['id' => 'col_team1', 'width' => '33.33%', 'modules' => [
                                ['id' => 'mod_team1_img', 'type' => 'image', 'content' => ['src' => img('gp-team1.jpg'), 'alt' => 'Chef Marcel'], 'design' => ['width' => '100%', 'height' => '350px', 'object_fit' => 'cover', 'margin_bottom' => '20px']],
                                ['id' => 'mod_team1_name', 'type' => 'heading', 'content' => ['text' => 'Marcel Dubois', 'level' => 'h3'], 'design' => ['text_align' => 'center', 'font_size' => '22px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '5px']],
                                ['id' => 'mod_team1_role', 'type' => 'text', 'content' => ['text' => 'Executive Chef & Founder'], 'design' => ['text_align' => 'center', 'font_size' => '14px', 'text_color' => 'var(--color-accent)', 'letter_spacing' => '2px']]
                            ]],
                            ['id' => 'col_team2', 'width' => '33.33%', 'modules' => [
                                ['id' => 'mod_team2_img', 'type' => 'image', 'content' => ['src' => img('gp-team2.jpg'), 'alt' => 'Sofia Rosetti'], 'design' => ['width' => '100%', 'height' => '350px', 'object_fit' => 'cover', 'margin_bottom' => '20px']],
                                ['id' => 'mod_team2_name', 'type' => 'heading', 'content' => ['text' => 'Sofia Rosetti', 'level' => 'h3'], 'design' => ['text_align' => 'center', 'font_size' => '22px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '5px']],
                                ['id' => 'mod_team2_role', 'type' => 'text', 'content' => ['text' => 'Head Pastry Chef'], 'design' => ['text_align' => 'center', 'font_size' => '14px', 'text_color' => 'var(--color-accent)', 'letter_spacing' => '2px']]
                            ]],
                            ['id' => 'col_team3', 'width' => '33.33%', 'modules' => [
                                ['id' => 'mod_team3_img', 'type' => 'image', 'content' => ['src' => img('gp-team3.jpg'), 'alt' => 'Antoine Laurent'], 'design' => ['width' => '100%', 'height' => '350px', 'object_fit' => 'cover', 'margin_bottom' => '20px']],
                                ['id' => 'mod_team3_name', 'type' => 'heading', 'content' => ['text' => 'Antoine Laurent', 'level' => 'h3'], 'design' => ['text_align' => 'center', 'font_size' => '22px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '5px']],
                                ['id' => 'mod_team3_role', 'type' => 'text', 'content' => ['text' => 'Master Sommelier'], 'design' => ['text_align' => 'center', 'font_size' => '14px', 'text_color' => 'var(--color-accent)', 'letter_spacing' => '2px']]
                            ]]
                        ]
                    ]
                ]
            ]
        ]
    ]
];
