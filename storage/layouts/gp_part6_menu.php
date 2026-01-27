<?php
/**
 * Golden Plate Layout - Part 6: Menu Page
 */

$menuPage = [
    'title' => 'Menu',
    'slug' => 'menu',
    'is_homepage' => false,
    'status' => 'draft',
    'content' => [
        'sections' => [
            // PAGE HERO
            [
                'id' => 'section_menu_hero',
                'name' => 'Page Hero',
                'design' => ['background_color' => 'var(--color-background)', 'background_image' => img('gp-dish1.jpg'), 'background_overlay' => 'rgba(10,10,10,0.85)', 'padding_top' => '150px', 'padding_bottom' => '80px'],
                'rows' => [[
                    'id' => 'row_menu_hero',
                    'columns' => [[
                        'id' => 'col_menu_hero', 'width' => '100%',
                        'modules' => [
                            ['id' => 'mod_menu_hero_title', 'type' => 'heading', 'content' => ['text' => 'Our Menu', 'level' => 'h1'], 'design' => ['text_align' => 'center', 'font_size' => '52px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '15px']],
                            ['id' => 'mod_menu_hero_bread', 'type' => 'text', 'content' => ['text' => 'Home / Menu'], 'design' => ['text_align' => 'center', 'font_size' => '14px', 'text_color' => 'var(--color-text-muted)']]
                        ]
                    ]]
                ]]
            ],
            // STARTERS
            [
                'id' => 'section_menu_starters',
                'name' => 'Starters',
                'design' => ['background_color' => 'var(--color-background)', 'padding_top' => '100px', 'padding_bottom' => '60px'],
                'rows' => [
                    ['id' => 'row_starters_header', 'columns' => [['id' => 'col_sh', 'width' => '100%', 'modules' => [
                        ['id' => 'mod_sh_tag', 'type' => 'text', 'content' => ['text' => 'TO BEGIN'], 'design' => ['text_align' => 'center', 'font_size' => '12px', 'letter_spacing' => '4px', 'text_color' => 'var(--color-accent)', 'margin_bottom' => '15px']],
                        ['id' => 'mod_sh_title', 'type' => 'heading', 'content' => ['text' => 'Starters', 'level' => 'h2'], 'design' => ['text_align' => 'center', 'font_size' => '36px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '50px']]
                    ]]]],
                    ['id' => 'row_starters', 'design' => ['max_width' => '1000px', 'margin' => '0 auto', 'gap' => '20px'], 'columns' => [
                        ['id' => 'col_s1', 'width' => '50%', 'modules' => [['id' => 'mod_s1', 'type' => 'text', 'content' => ['text' => '<div style="display:flex;justify-content:space-between;border-bottom:1px solid #333;padding:15px 0"><div><strong style="color:#fff">Fresh Oysters</strong><br><span style="color:#888;font-size:14px">Half dozen east coast oysters, mignonette</span></div><span style="color:#d4af37;font-size:18px">$32</span></div>'], 'design' => []]]],
                        ['id' => 'col_s2', 'width' => '50%', 'modules' => [['id' => 'mod_s2', 'type' => 'text', 'content' => ['text' => '<div style="display:flex;justify-content:space-between;border-bottom:1px solid #333;padding:15px 0"><div><strong style="color:#fff">Seared Foie Gras</strong><br><span style="color:#888;font-size:14px">Brioche, fig compote, port reduction</span></div><span style="color:#d4af37;font-size:18px">$45</span></div>'], 'design' => []]]],
                        ['id' => 'col_s3', 'width' => '50%', 'modules' => [['id' => 'mod_s3', 'type' => 'text', 'content' => ['text' => '<div style="display:flex;justify-content:space-between;border-bottom:1px solid #333;padding:15px 0"><div><strong style="color:#fff">Beef Tartare</strong><br><span style="color:#888;font-size:14px">Hand-cut beef, capers, quail egg</span></div><span style="color:#d4af37;font-size:18px">$28</span></div>'], 'design' => []]]],
                        ['id' => 'col_s4', 'width' => '50%', 'modules' => [['id' => 'mod_s4', 'type' => 'text', 'content' => ['text' => '<div style="display:flex;justify-content:space-between;border-bottom:1px solid #333;padding:15px 0"><div><strong style="color:#fff">French Onion Soup</strong><br><span style="color:#888;font-size:14px">Caramelized onions, gruyère</span></div><span style="color:#d4af37;font-size:18px">$18</span></div>'], 'design' => []]]]
                    ]]
                ]
            ],
            // MAIN COURSES
            [
                'id' => 'section_menu_mains',
                'name' => 'Main Courses',
                'design' => ['background_color' => 'var(--color-surface)', 'padding_top' => '80px', 'padding_bottom' => '60px'],
                'rows' => [
                    ['id' => 'row_mains_header', 'columns' => [['id' => 'col_mh', 'width' => '100%', 'modules' => [
                        ['id' => 'mod_mh_tag', 'type' => 'text', 'content' => ['text' => 'ENTRÉES'], 'design' => ['text_align' => 'center', 'font_size' => '12px', 'letter_spacing' => '4px', 'text_color' => 'var(--color-accent)', 'margin_bottom' => '15px']],
                        ['id' => 'mod_mh_title', 'type' => 'heading', 'content' => ['text' => 'Main Courses', 'level' => 'h2'], 'design' => ['text_align' => 'center', 'font_size' => '36px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '50px']]
                    ]]]],
                    ['id' => 'row_mains', 'design' => ['max_width' => '1000px', 'margin' => '0 auto', 'gap' => '20px'], 'columns' => [
                        ['id' => 'col_m1', 'width' => '50%', 'modules' => [['id' => 'mod_m1', 'type' => 'text', 'content' => ['text' => '<div style="display:flex;justify-content:space-between;border-bottom:1px solid #333;padding:15px 0"><div><strong style="color:#fff">A5 Wagyu Ribeye</strong><br><span style="color:#888;font-size:14px">Japanese wagyu, truffle butter</span></div><span style="color:#d4af37;font-size:18px">$128</span></div>'], 'design' => []]]],
                        ['id' => 'col_m2', 'width' => '50%', 'modules' => [['id' => 'mod_m2', 'type' => 'text', 'content' => ['text' => '<div style="display:flex;justify-content:space-between;border-bottom:1px solid #333;padding:15px 0"><div><strong style="color:#fff">Maine Lobster</strong><br><span style="color:#888;font-size:14px">Butter-poached, champagne beurre blanc</span></div><span style="color:#d4af37;font-size:18px">$95</span></div>'], 'design' => []]]],
                        ['id' => 'col_m3', 'width' => '50%', 'modules' => [['id' => 'mod_m3', 'type' => 'text', 'content' => ['text' => '<div style="display:flex;justify-content:space-between;border-bottom:1px solid #333;padding:15px 0"><div><strong style="color:#fff">Roasted Duck Breast</strong><br><span style="color:#888;font-size:14px">Cherry gastrique, parsnip purée</span></div><span style="color:#d4af37;font-size:18px">$58</span></div>'], 'design' => []]]],
                        ['id' => 'col_m4', 'width' => '50%', 'modules' => [['id' => 'mod_m4', 'type' => 'text', 'content' => ['text' => '<div style="display:flex;justify-content:space-between;border-bottom:1px solid #333;padding:15px 0"><div><strong style="color:#fff">Black Truffle Risotto</strong><br><span style="color:#888;font-size:14px">Carnaroli rice, aged parmesan</span></div><span style="color:#d4af37;font-size:18px">$68</span></div>'], 'design' => []]]]
                    ]]
                ]
            ],
            // DESSERTS
            [
                'id' => 'section_menu_desserts',
                'name' => 'Desserts',
                'design' => ['background_color' => 'var(--color-background)', 'padding_top' => '80px', 'padding_bottom' => '100px'],
                'rows' => [
                    ['id' => 'row_desserts_header', 'columns' => [['id' => 'col_dh', 'width' => '100%', 'modules' => [
                        ['id' => 'mod_dh_tag', 'type' => 'text', 'content' => ['text' => 'SWEET ENDINGS'], 'design' => ['text_align' => 'center', 'font_size' => '12px', 'letter_spacing' => '4px', 'text_color' => 'var(--color-accent)', 'margin_bottom' => '15px']],
                        ['id' => 'mod_dh_title', 'type' => 'heading', 'content' => ['text' => 'Desserts', 'level' => 'h2'], 'design' => ['text_align' => 'center', 'font_size' => '36px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '50px']]
                    ]]]],
                    ['id' => 'row_desserts', 'design' => ['max_width' => '1000px', 'margin' => '0 auto', 'gap' => '20px'], 'columns' => [
                        ['id' => 'col_d1', 'width' => '50%', 'modules' => [['id' => 'mod_d1', 'type' => 'text', 'content' => ['text' => '<div style="display:flex;justify-content:space-between;border-bottom:1px solid #333;padding:15px 0"><div><strong style="color:#fff">Grand Marnier Soufflé</strong><br><span style="color:#888;font-size:14px">Orange liqueur, crème anglaise</span></div><span style="color:#d4af37;font-size:18px">$22</span></div>'], 'design' => []]]],
                        ['id' => 'col_d2', 'width' => '50%', 'modules' => [['id' => 'mod_d2', 'type' => 'text', 'content' => ['text' => '<div style="display:flex;justify-content:space-between;border-bottom:1px solid #333;padding:15px 0"><div><strong style="color:#fff">Chocolate Fondant</strong><br><span style="color:#888;font-size:14px">Molten center, vanilla ice cream</span></div><span style="color:#d4af37;font-size:18px">$18</span></div>'], 'design' => []]]],
                        ['id' => 'col_d3', 'width' => '50%', 'modules' => [['id' => 'mod_d3', 'type' => 'text', 'content' => ['text' => '<div style="display:flex;justify-content:space-between;border-bottom:1px solid #333;padding:15px 0"><div><strong style="color:#fff">Lemon Tart</strong><br><span style="color:#888;font-size:14px">Meyer lemon curd, Italian meringue</span></div><span style="color:#d4af37;font-size:18px">$16</span></div>'], 'design' => []]]],
                        ['id' => 'col_d4', 'width' => '50%', 'modules' => [['id' => 'mod_d4', 'type' => 'text', 'content' => ['text' => '<div style="display:flex;justify-content:space-between;border-bottom:1px solid #333;padding:15px 0"><div><strong style="color:#fff">Vanilla Panna Cotta</strong><br><span style="color:#888;font-size:14px">Tahitian vanilla, fresh berries</span></div><span style="color:#d4af37;font-size:18px">$14</span></div>'], 'design' => []]]]
                    ]]
                ]
            ]
        ]
    ]
];
