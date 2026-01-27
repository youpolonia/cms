<?php
/**
 * Golden Plate Layout - Part 7: Gallery Page
 */

$galleryPage = [
    'title' => 'Gallery',
    'slug' => 'gallery',
    'is_homepage' => false,
    'status' => 'draft',
    'content' => [
        'sections' => [
            // PAGE HERO
            [
                'id' => 'section_gallery_hero',
                'name' => 'Page Hero',
                'design' => ['background_color' => 'var(--color-background)', 'background_image' => img('gp-gallery1.jpg'), 'background_overlay' => 'rgba(10,10,10,0.85)', 'padding_top' => '150px', 'padding_bottom' => '80px'],
                'rows' => [[
                    'id' => 'row_gallery_hero',
                    'columns' => [[
                        'id' => 'col_gallery_hero', 'width' => '100%',
                        'modules' => [
                            ['id' => 'mod_gallery_hero_title', 'type' => 'heading', 'content' => ['text' => 'Gallery', 'level' => 'h1'], 'design' => ['text_align' => 'center', 'font_size' => '52px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '15px']],
                            ['id' => 'mod_gallery_hero_bread', 'type' => 'text', 'content' => ['text' => 'Home / Gallery'], 'design' => ['text_align' => 'center', 'font_size' => '14px', 'text_color' => 'var(--color-text-muted)']]
                        ]
                    ]]
                ]]
            ],
            // GALLERY GRID
            [
                'id' => 'section_gallery_grid',
                'name' => 'Gallery Grid',
                'design' => ['background_color' => 'var(--color-background)', 'padding_top' => '80px', 'padding_bottom' => '100px'],
                'rows' => [
                    ['id' => 'row_gallery_header', 'columns' => [['id' => 'col_gh', 'width' => '100%', 'modules' => [
                        ['id' => 'mod_gh_tag', 'type' => 'text', 'content' => ['text' => 'VISUAL JOURNEY'], 'design' => ['text_align' => 'center', 'font_size' => '12px', 'letter_spacing' => '4px', 'text_color' => 'var(--color-accent)', 'margin_bottom' => '15px']],
                        ['id' => 'mod_gh_title', 'type' => 'heading', 'content' => ['text' => 'Our Moments', 'level' => 'h2'], 'design' => ['text_align' => 'center', 'font_size' => '42px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '50px']]
                    ]]]],
                    [
                        'id' => 'row_gallery',
                        'design' => ['max_width' => '1200px', 'margin' => '0 auto'],
                        'columns' => [[
                            'id' => 'col_gallery', 'width' => '100%',
                            'modules' => [[
                                'id' => 'mod_gallery',
                                'type' => 'gallery',
                                'content' => [
                                    'images' => [
                                        ['src' => img('gp-interior.jpg'), 'alt' => 'Restaurant Interior'],
                                        ['src' => img('gp-dish1.jpg'), 'alt' => 'Wagyu Steak'],
                                        ['src' => img('gp-dish2.jpg'), 'alt' => 'Lobster'],
                                        ['src' => img('gp-dish3.jpg'), 'alt' => 'Risotto'],
                                        ['src' => img('gp-gallery1.jpg'), 'alt' => 'Dining Room'],
                                        ['src' => img('gp-dish4.jpg'), 'alt' => 'Dish'],
                                        ['src' => img('gp-gallery2.jpg'), 'alt' => 'Bar Area'],
                                        ['src' => img('gp-dish5.jpg'), 'alt' => 'Dish'],
                                        ['src' => img('gp-gallery3.jpg'), 'alt' => 'Kitchen'],
                                        ['src' => img('gp-chef.jpg'), 'alt' => 'Chef at Work'],
                                        ['src' => img('gp-dish6.jpg'), 'alt' => 'Dessert'],
                                        ['src' => img('gp-gallery4.jpg'), 'alt' => 'Private Dining']
                                    ],
                                    'columns' => 4
                                ],
                                'design' => [
                                    'gap' => '15px',
                                    'image_height' => '250px',
                                    'hover_effect' => 'zoom'
                                ]
                            ]]
                        ]]
                    ]
                ]
            ]
        ]
    ]
];
