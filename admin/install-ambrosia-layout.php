<?php
/**
 * Install Ambrosia Restaurant Layout to Layout Library
 * Copy this file to /admin/ and run via browser
 * 
 * NO CLI, pure PHP 8.1+, FTP-only
 */

declare(strict_types=1);
define('CMS_ROOT', dirname(__DIR__));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/core/functions.php';
require_once CMS_ROOT . '/core/session.php';

if (!defined('DEV_MODE') || !DEV_MODE) {
    die('This script requires DEV_MODE to be enabled.');
}

cms_session_start();
$db = \core\Database::connection();

$stmt = $db->prepare("SELECT id FROM tb_layout_library WHERE slug = 'ambrosia-restaurant'");
$stmt->execute();
if ($stmt->fetch()) {
    echo "<h2>Ambrosia Restaurant layout already exists!</h2>";
    echo "<p><a href='/admin/layout-library'>Go to Layout Library</a></p>";
    exit;
}

$layoutJson = [
    'pages' => [
        [
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'content' => ['sections' => [
                ['id' => 'section_hero', 'design' => ['background_color' => '#1a1a1a', 'background_image' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1920', 'background_overlay' => 'rgba(0,0,0,0.6)', 'min_height' => '100vh', 'padding' => '60px 20px', 'text_align' => 'center'], 'rows' => [[
                    'id' => 'row_hero', 'columns' => [['id' => 'col_hero', 'width' => '100%', 'modules' => [
                        ['id' => 'mod_tagline', 'type' => 'text', 'content' => ['text' => 'Est. 1987 • Fine Dining'], 'design' => ['font_size' => '14px', 'letter_spacing' => '6px', 'text_transform' => 'uppercase', 'text_color' => '#c9a962', 'margin' => '0 0 30px 0']],
                        ['id' => 'mod_title', 'type' => 'heading', 'content' => ['text' => 'Ambrosia', 'level' => 'h1'], 'design' => ['font_size' => '120px', 'font_style' => 'italic', 'text_color' => '#f5f0e8', 'font_family' => 'Cormorant Garamond, serif']],
                        ['id' => 'mod_subtitle', 'type' => 'text', 'content' => ['text' => 'CULINARY EXCELLENCE'], 'design' => ['font_size' => '18px', 'letter_spacing' => '8px', 'text_color' => '#e8d5a3', 'margin' => '15px 0 30px 0']],
                        ['id' => 'mod_desc', 'type' => 'text', 'content' => ['text' => 'Where every dish tells a story of passion, tradition, and the finest ingredients.'], 'design' => ['font_size' => '18px', 'text_color' => 'rgba(255,255,255,0.9)', 'max_width' => '600px', 'margin' => '0 auto 50px auto']],
                        ['id' => 'mod_btn', 'type' => 'button', 'content' => ['text' => 'Reserve a Table', 'url' => '#contact', 'icon' => 'fas fa-calendar-alt'], 'design' => ['background_color' => '#c9a962', 'text_color' => '#1a1a1a', 'padding' => '18px 40px']]
                    ]]]
                ]]],
                ['id' => 'section_features', 'design' => ['background_color' => '#f5f0e8', 'padding' => '140px 40px'], 'rows' => [
                    ['id' => 'row_fh', 'columns' => [['id' => 'col_fh', 'width' => '100%', 'modules' => [
                        ['id' => 'mod_fl', 'type' => 'text', 'content' => ['text' => 'Why Choose Us'], 'design' => ['font_size' => '12px', 'letter_spacing' => '4px', 'text_transform' => 'uppercase', 'text_color' => '#8b7355', 'text_align' => 'center', 'margin' => '0 0 20px 0']],
                        ['id' => 'mod_ft', 'type' => 'heading', 'content' => ['text' => 'The Ambrosia Experience', 'level' => 'h2'], 'design' => ['font_size' => '56px', 'font_family' => 'Cormorant Garamond, serif', 'text_color' => '#1a1a1a', 'text_align' => 'center', 'margin' => '0 0 80px 0']]
                    ]]]],
                    ['id' => 'row_fc', 'columns' => [
                        ['id' => 'col_f1', 'width' => '33.33%', 'modules' => [['id' => 'mod_f1', 'type' => 'blurb', 'content' => ['title' => 'Farm to Table', 'text' => 'Daily sourced from local organic farms.', 'icon' => 'fas fa-leaf'], 'design' => ['background_color' => '#ffffff', 'padding' => '50px 40px', 'text_align' => 'center', 'icon_color' => '#c9a962', 'icon_size' => '36px', 'title_color' => '#1a1a1a']]]],
                        ['id' => 'col_f2', 'width' => '33.33%', 'modules' => [['id' => 'mod_f2', 'type' => 'blurb', 'content' => ['title' => 'Award Winning', 'text' => '2 Michelin stars for excellence.', 'icon' => 'fas fa-award'], 'design' => ['background_color' => '#ffffff', 'padding' => '50px 40px', 'text_align' => 'center', 'icon_color' => '#c9a962', 'icon_size' => '36px', 'title_color' => '#1a1a1a']]]],
                        ['id' => 'col_f3', 'width' => '33.33%', 'modules' => [['id' => 'mod_f3', 'type' => 'blurb', 'content' => ['title' => 'Curated Wines', 'text' => '300+ wines from finest vineyards.', 'icon' => 'fas fa-wine-glass-alt'], 'design' => ['background_color' => '#ffffff', 'padding' => '50px 40px', 'text_align' => 'center', 'icon_color' => '#c9a962', 'icon_size' => '36px', 'title_color' => '#1a1a1a']]]]
                    ]]
                ]],
                ['id' => 'section_about', 'design' => ['background_color' => '#1a1a1a', 'padding' => '0'], 'rows' => [[
                    'id' => 'row_about', 'columns' => [
                        ['id' => 'col_ai', 'width' => '50%', 'modules' => [['id' => 'mod_ai', 'type' => 'image', 'content' => ['src' => 'https://images.unsplash.com/photo-1577219491135-ce391730fb2c?w=1000', 'alt' => 'Chef'], 'design' => ['width' => '100%', 'height' => '600px', 'object_fit' => 'cover']]]],
                        ['id' => 'col_ac', 'width' => '50%', 'design' => ['padding' => '100px 80px'], 'modules' => [
                            ['id' => 'mod_al', 'type' => 'text', 'content' => ['text' => 'Our Story'], 'design' => ['font_size' => '12px', 'letter_spacing' => '4px', 'text_transform' => 'uppercase', 'text_color' => '#c9a962', 'margin' => '0 0 20px 0']],
                            ['id' => 'mod_at', 'type' => 'heading', 'content' => ['text' => 'A Legacy of Flavor', 'level' => 'h2'], 'design' => ['font_size' => '48px', 'font_family' => 'Cormorant Garamond, serif', 'text_color' => '#ffffff', 'margin' => '0 0 30px 0']],
                            ['id' => 'mod_atx', 'type' => 'text', 'content' => ['text' => 'For over three decades, Ambrosia has been a culinary landmark, blending traditions with contemporary innovation.'], 'design' => ['font_size' => '17px', 'line_height' => '2', 'text_color' => 'rgba(255,255,255,0.85)', 'margin' => '0 0 40px 0']],
                            ['id' => 'mod_sig', 'type' => 'text', 'content' => ['text' => '— Chef Marcus Wellington'], 'design' => ['font_size' => '28px', 'font_family' => 'Cormorant Garamond, serif', 'font_style' => 'italic', 'text_color' => '#c9a962']]
                        ]]
                    ]
                ]]],
                ['id' => 'section_cta', 'design' => ['background_color' => '#c9a962', 'padding' => '160px 40px', 'text_align' => 'center'], 'rows' => [[
                    'id' => 'row_cta', 'columns' => [['id' => 'col_cta', 'width' => '100%', 'modules' => [
                        ['id' => 'mod_ct', 'type' => 'heading', 'content' => ['text' => 'Ready for an Unforgettable Evening?', 'level' => 'h2'], 'design' => ['font_size' => '60px', 'font_family' => 'Cormorant Garamond, serif', 'text_color' => '#1a1a1a', 'margin' => '0 0 30px 0']],
                        ['id' => 'mod_ctx', 'type' => 'text', 'content' => ['text' => 'Reserve your table today.'], 'design' => ['font_size' => '18px', 'text_color' => '#1a1a1a', 'margin' => '0 0 50px 0']],
                        ['id' => 'mod_cb', 'type' => 'button', 'content' => ['text' => 'Make a Reservation', 'url' => '#contact'], 'design' => ['background_color' => '#1a1a1a', 'text_color' => '#c9a962', 'padding' => '18px 40px']]
                    ]]]
                ]]]
            ]]
        ],
        [
            'title' => 'About',
            'slug' => 'about',
            'content' => ['sections' => [
                ['id' => 'section_ah', 'design' => ['background_color' => '#1a1a1a', 'background_image' => 'https://images.unsplash.com/photo-1466978913421-dad2ebd01d17?w=1920', 'background_overlay' => 'rgba(0,0,0,0.6)', 'min_height' => '60vh', 'padding' => '60px 20px', 'text_align' => 'center'], 'rows' => [[
                    'id' => 'row_ah', 'columns' => [['id' => 'col_ah', 'width' => '100%', 'modules' => [
                        ['id' => 'mod_ahl', 'type' => 'text', 'content' => ['text' => 'Our Heritage'], 'design' => ['font_size' => '13px', 'letter_spacing' => '5px', 'text_transform' => 'uppercase', 'text_color' => '#c9a962', 'margin' => '0 0 20px 0']],
                        ['id' => 'mod_aht', 'type' => 'heading', 'content' => ['text' => 'About Ambrosia', 'level' => 'h1'], 'design' => ['font_size' => '80px', 'font_family' => 'Cormorant Garamond, serif', 'font_style' => 'italic', 'text_color' => '#f5f0e8']]
                    ]]]
                ]]],
                ['id' => 'section_av', 'design' => ['background_color' => '#1a1a1a', 'padding' => '120px 40px'], 'rows' => [
                    ['id' => 'row_vh', 'columns' => [['id' => 'col_vh', 'width' => '100%', 'modules' => [
                        ['id' => 'mod_vl', 'type' => 'text', 'content' => ['text' => 'What Drives Us'], 'design' => ['font_size' => '12px', 'letter_spacing' => '4px', 'text_transform' => 'uppercase', 'text_color' => '#c9a962', 'text_align' => 'center', 'margin' => '0 0 20px 0']],
                        ['id' => 'mod_vt', 'type' => 'heading', 'content' => ['text' => 'Our Core Values', 'level' => 'h2'], 'design' => ['font_size' => '56px', 'font_family' => 'Cormorant Garamond, serif', 'text_color' => '#ffffff', 'text_align' => 'center', 'margin' => '0 0 60px 0']]
                    ]]]],
                    ['id' => 'row_vc', 'columns' => [
                        ['id' => 'col_v1', 'width' => '25%', 'modules' => [['id' => 'mod_v1', 'type' => 'blurb', 'content' => ['title' => 'Passion', 'text' => 'Every dish crafted with love.', 'icon' => 'fas fa-heart'], 'design' => ['background_color' => 'rgba(255,255,255,0.03)', 'border' => '1px solid rgba(255,255,255,0.08)', 'padding' => '50px 30px', 'text_align' => 'center', 'icon_color' => '#c9a962', 'title_color' => '#ffffff', 'text_color' => 'rgba(255,255,255,0.6)']]]],
                        ['id' => 'col_v2', 'width' => '25%', 'modules' => [['id' => 'mod_v2', 'type' => 'blurb', 'content' => ['title' => 'Sustainability', 'text' => 'Responsible sourcing.', 'icon' => 'fas fa-seedling'], 'design' => ['background_color' => 'rgba(255,255,255,0.03)', 'border' => '1px solid rgba(255,255,255,0.08)', 'padding' => '50px 30px', 'text_align' => 'center', 'icon_color' => '#c9a962', 'title_color' => '#ffffff', 'text_color' => 'rgba(255,255,255,0.6)']]]],
                        ['id' => 'col_v3', 'width' => '25%', 'modules' => [['id' => 'mod_v3', 'type' => 'blurb', 'content' => ['title' => 'Quality', 'text' => 'Finest ingredients only.', 'icon' => 'fas fa-gem'], 'design' => ['background_color' => 'rgba(255,255,255,0.03)', 'border' => '1px solid rgba(255,255,255,0.08)', 'padding' => '50px 30px', 'text_align' => 'center', 'icon_color' => '#c9a962', 'title_color' => '#ffffff', 'text_color' => 'rgba(255,255,255,0.6)']]]],
                        ['id' => 'col_v4', 'width' => '25%', 'modules' => [['id' => 'mod_v4', 'type' => 'blurb', 'content' => ['title' => 'Community', 'text' => 'Giving back locally.', 'icon' => 'fas fa-users'], 'design' => ['background_color' => 'rgba(255,255,255,0.03)', 'border' => '1px solid rgba(255,255,255,0.08)', 'padding' => '50px 30px', 'text_align' => 'center', 'icon_color' => '#c9a962', 'title_color' => '#ffffff', 'text_color' => 'rgba(255,255,255,0.6)']]]]
                    ]]
                ]],
                ['id' => 'section_stats', 'design' => ['background_color' => '#c9a962', 'padding' => '100px 40px'], 'rows' => [[
                    'id' => 'row_stats', 'columns' => [
                        ['id' => 'col_s1', 'width' => '25%', 'modules' => [['id' => 'mod_s1', 'type' => 'counter', 'content' => ['number' => '37', 'label' => 'Years'], 'design' => ['number_size' => '72px', 'number_color' => '#1a1a1a', 'text_align' => 'center']]]],
                        ['id' => 'col_s2', 'width' => '25%', 'modules' => [['id' => 'mod_s2', 'type' => 'counter', 'content' => ['number' => '2', 'label' => 'Michelin Stars'], 'design' => ['number_size' => '72px', 'number_color' => '#1a1a1a', 'text_align' => 'center']]]],
                        ['id' => 'col_s3', 'width' => '25%', 'modules' => [['id' => 'mod_s3', 'type' => 'counter', 'content' => ['number' => '50K+', 'label' => 'Guests'], 'design' => ['number_size' => '72px', 'number_color' => '#1a1a1a', 'text_align' => 'center']]]],
                        ['id' => 'col_s4', 'width' => '25%', 'modules' => [['id' => 'mod_s4', 'type' => 'counter', 'content' => ['number' => '15', 'label' => 'Awards'], 'design' => ['number_size' => '72px', 'number_color' => '#1a1a1a', 'text_align' => 'center']]]]
                    ]
                ]]]
            ]]
        ],
        [
            'title' => 'Menu',
            'slug' => 'menu',
            'content' => ['sections' => [
                ['id' => 'section_mh', 'design' => ['background_color' => '#1a1a1a', 'background_image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1920', 'background_overlay' => 'rgba(0,0,0,0.6)', 'min_height' => '60vh', 'padding' => '60px 20px', 'text_align' => 'center'], 'rows' => [[
                    'id' => 'row_mh', 'columns' => [['id' => 'col_mh', 'width' => '100%', 'modules' => [
                        ['id' => 'mod_mhl', 'type' => 'text', 'content' => ['text' => 'Culinary Creations'], 'design' => ['font_size' => '13px', 'letter_spacing' => '5px', 'text_transform' => 'uppercase', 'text_color' => '#c9a962', 'margin' => '0 0 20px 0']],
                        ['id' => 'mod_mht', 'type' => 'heading', 'content' => ['text' => 'Our Menu', 'level' => 'h1'], 'design' => ['font_size' => '80px', 'font_family' => 'Cormorant Garamond, serif', 'font_style' => 'italic', 'text_color' => '#f5f0e8']]
                    ]]]
                ]]],
                ['id' => 'section_menu', 'design' => ['background_color' => '#f5f0e8', 'padding' => '100px 40px'], 'rows' => [
                    ['id' => 'row_meh', 'columns' => [['id' => 'col_meh', 'width' => '100%', 'modules' => [
                        ['id' => 'mod_mel', 'type' => 'text', 'content' => ['text' => 'Signature Dishes'], 'design' => ['font_size' => '12px', 'letter_spacing' => '4px', 'text_transform' => 'uppercase', 'text_color' => '#8b7355', 'text_align' => 'center', 'margin' => '0 0 20px 0']],
                        ['id' => 'mod_met', 'type' => 'heading', 'content' => ['text' => 'Main Courses', 'level' => 'h2'], 'design' => ['font_size' => '56px', 'font_family' => 'Cormorant Garamond, serif', 'text_color' => '#1a1a1a', 'text_align' => 'center', 'margin' => '0 0 60px 0']]
                    ]]]],
                    ['id' => 'row_mc', 'columns' => [
                        ['id' => 'col_mc1', 'width' => '33.33%', 'modules' => [
                            ['id' => 'mod_mc1i', 'type' => 'image', 'content' => ['src' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600', 'alt' => 'Wagyu'], 'design' => ['width' => '100%', 'height' => '280px', 'object_fit' => 'cover']],
                            ['id' => 'mod_mc1t', 'type' => 'text', 'content' => ['text' => '<h3 style="font-family:Cormorant Garamond,serif;font-size:26px;color:#1a1a1a">Wagyu Tenderloin</h3><p style="color:#6b6b6b">A5 wagyu, truffle jus</p><p style="font-size:24px;color:#8b7355">$145</p>'], 'design' => ['background_color' => '#ffffff', 'padding' => '35px']]
                        ]],
                        ['id' => 'col_mc2', 'width' => '33.33%', 'modules' => [
                            ['id' => 'mod_mc2i', 'type' => 'image', 'content' => ['src' => 'https://images.unsplash.com/photo-1534080564583-6be75777b70a?w=600', 'alt' => 'Lobster'], 'design' => ['width' => '100%', 'height' => '280px', 'object_fit' => 'cover']],
                            ['id' => 'mod_mc2t', 'type' => 'text', 'content' => ['text' => '<h3 style="font-family:Cormorant Garamond,serif;font-size:26px;color:#1a1a1a">Butter Poached Lobster</h3><p style="color:#6b6b6b">Maine lobster, saffron</p><p style="font-size:24px;color:#8b7355">$98</p>'], 'design' => ['background_color' => '#ffffff', 'padding' => '35px']]
                        ]],
                        ['id' => 'col_mc3', 'width' => '33.33%', 'modules' => [
                            ['id' => 'mod_mc3i', 'type' => 'image', 'content' => ['src' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=600', 'alt' => 'Dessert'], 'design' => ['width' => '100%', 'height' => '280px', 'object_fit' => 'cover']],
                            ['id' => 'mod_mc3t', 'type' => 'text', 'content' => ['text' => '<h3 style="font-family:Cormorant Garamond,serif;font-size:26px;color:#1a1a1a">Chocolate Symphony</h3><p style="color:#6b6b6b">Valrhona chocolate</p><p style="font-size:24px;color:#8b7355">$24</p>'], 'design' => ['background_color' => '#ffffff', 'padding' => '35px']]
                        ]]
                    ]]
                ]]
            ]]
        ],
        [
            'title' => 'Gallery',
            'slug' => 'gallery',
            'content' => ['sections' => [
                ['id' => 'section_gh', 'design' => ['background_color' => '#1a1a1a', 'background_image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1920', 'background_overlay' => 'rgba(0,0,0,0.6)', 'min_height' => '50vh', 'padding' => '60px 20px', 'text_align' => 'center'], 'rows' => [[
                    'id' => 'row_gh', 'columns' => [['id' => 'col_gh', 'width' => '100%', 'modules' => [
                        ['id' => 'mod_ghl', 'type' => 'text', 'content' => ['text' => 'Visual Journey'], 'design' => ['font_size' => '13px', 'letter_spacing' => '5px', 'text_transform' => 'uppercase', 'text_color' => '#c9a962', 'margin' => '0 0 20px 0']],
                        ['id' => 'mod_ght', 'type' => 'heading', 'content' => ['text' => 'Gallery', 'level' => 'h1'], 'design' => ['font_size' => '80px', 'font_family' => 'Cormorant Garamond, serif', 'font_style' => 'italic', 'text_color' => '#f5f0e8']]
                    ]]]
                ]]],
                ['id' => 'section_gg', 'design' => ['background_color' => '#1a1a1a', 'padding' => '60px 40px'], 'rows' => [[
                    'id' => 'row_gg', 'columns' => [
                        ['id' => 'col_g1', 'width' => '25%', 'modules' => [['id' => 'mod_g1', 'type' => 'image', 'content' => ['src' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=600', 'alt' => 'Interior'], 'design' => ['width' => '100%']]]],
                        ['id' => 'col_g2', 'width' => '25%', 'modules' => [['id' => 'mod_g2', 'type' => 'image', 'content' => ['src' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600', 'alt' => 'Food'], 'design' => ['width' => '100%']]]],
                        ['id' => 'col_g3', 'width' => '25%', 'modules' => [['id' => 'mod_g3', 'type' => 'image', 'content' => ['src' => 'https://images.unsplash.com/photo-1577219491135-ce391730fb2c?w=600', 'alt' => 'Chef'], 'design' => ['width' => '100%']]]],
                        ['id' => 'col_g4', 'width' => '25%', 'modules' => [['id' => 'mod_g4', 'type' => 'image', 'content' => ['src' => 'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=600', 'alt' => 'Wine'], 'design' => ['width' => '100%']]]]
                    ]
                ]]]
            ]]
        ],
        [
            'title' => 'Contact',
            'slug' => 'contact',
            'content' => ['sections' => [
                ['id' => 'section_ch', 'design' => ['background_color' => '#1a1a1a', 'background_image' => 'https://images.unsplash.com/photo-1550966871-3ed3cdb5ed0c?w=1920', 'background_overlay' => 'rgba(0,0,0,0.6)', 'min_height' => '60vh', 'padding' => '60px 20px', 'text_align' => 'center'], 'rows' => [[
                    'id' => 'row_ch', 'columns' => [['id' => 'col_ch', 'width' => '100%', 'modules' => [
                        ['id' => 'mod_chl', 'type' => 'text', 'content' => ['text' => 'Get in Touch'], 'design' => ['font_size' => '13px', 'letter_spacing' => '5px', 'text_transform' => 'uppercase', 'text_color' => '#c9a962', 'margin' => '0 0 20px 0']],
                        ['id' => 'mod_cht', 'type' => 'heading', 'content' => ['text' => 'Contact & Reservations', 'level' => 'h1'], 'design' => ['font_size' => '70px', 'font_family' => 'Cormorant Garamond, serif', 'font_style' => 'italic', 'text_color' => '#f5f0e8']]
                    ]]]
                ]]],
                ['id' => 'section_ci', 'design' => ['background_color' => '#f5f0e8', 'padding' => '120px 40px'], 'rows' => [[
                    'id' => 'row_ci', 'columns' => [
                        ['id' => 'col_ci1', 'width' => '33.33%', 'modules' => [['id' => 'mod_ci1', 'type' => 'blurb', 'content' => ['title' => '142 Gourmet Avenue', 'text' => 'New York, NY 10012', 'icon' => 'fas fa-map-marker-alt'], 'design' => ['background_color' => '#ffffff', 'padding' => '40px', 'text_align' => 'center', 'icon_color' => '#c9a962', 'title_color' => '#1a1a1a']]]],
                        ['id' => 'col_ci2', 'width' => '33.33%', 'modules' => [['id' => 'mod_ci2', 'type' => 'blurb', 'content' => ['title' => '+1 (555) 123-4567', 'text' => 'Reservations', 'icon' => 'fas fa-phone'], 'design' => ['background_color' => '#ffffff', 'padding' => '40px', 'text_align' => 'center', 'icon_color' => '#c9a962', 'title_color' => '#1a1a1a']]]],
                        ['id' => 'col_ci3', 'width' => '33.33%', 'modules' => [['id' => 'mod_ci3', 'type' => 'blurb', 'content' => ['title' => 'hello@ambrosia.com', 'text' => 'Email Us', 'icon' => 'fas fa-envelope'], 'design' => ['background_color' => '#ffffff', 'padding' => '40px', 'text_align' => 'center', 'icon_color' => '#c9a962', 'title_color' => '#1a1a1a']]]]
                    ]
                ]]],
                ['id' => 'section_hours', 'design' => ['background_color' => '#1a1a1a', 'padding' => '120px 40px'], 'rows' => [
                    ['id' => 'row_hh', 'columns' => [['id' => 'col_hh', 'width' => '100%', 'modules' => [
                        ['id' => 'mod_hhl', 'type' => 'text', 'content' => ['text' => 'When to Visit'], 'design' => ['font_size' => '12px', 'letter_spacing' => '4px', 'text_transform' => 'uppercase', 'text_color' => '#c9a962', 'text_align' => 'center', 'margin' => '0 0 20px 0']],
                        ['id' => 'mod_hht', 'type' => 'heading', 'content' => ['text' => 'Opening Hours', 'level' => 'h2'], 'design' => ['font_size' => '56px', 'font_family' => 'Cormorant Garamond, serif', 'text_color' => '#ffffff', 'text_align' => 'center', 'margin' => '0 0 60px 0']]
                    ]]]],
                    ['id' => 'row_hc', 'columns' => [
                        ['id' => 'col_h1', 'width' => '33.33%', 'modules' => [['id' => 'mod_h1', 'type' => 'blurb', 'content' => ['title' => 'Dinner', 'text' => '5:30 PM – 11:00 PM', 'icon' => 'fas fa-utensils'], 'design' => ['background_color' => 'rgba(255,255,255,0.03)', 'border' => '1px solid rgba(255,255,255,0.08)', 'padding' => '50px 40px', 'text_align' => 'center', 'icon_color' => '#c9a962', 'title_color' => '#ffffff', 'text_color' => '#e8d5a3']]]],
                        ['id' => 'col_h2', 'width' => '33.33%', 'modules' => [['id' => 'mod_h2', 'type' => 'blurb', 'content' => ['title' => 'Bar', 'text' => '4:00 PM – 12:00 AM', 'icon' => 'fas fa-wine-glass-alt'], 'design' => ['background_color' => 'rgba(255,255,255,0.03)', 'border' => '1px solid rgba(255,255,255,0.08)', 'padding' => '50px 40px', 'text_align' => 'center', 'icon_color' => '#c9a962', 'title_color' => '#ffffff', 'text_color' => '#e8d5a3']]]],
                        ['id' => 'col_h3', 'width' => '33.33%', 'modules' => [['id' => 'mod_h3', 'type' => 'blurb', 'content' => ['title' => 'Brunch', 'text' => '11:00 AM – 3:00 PM', 'icon' => 'fas fa-sun'], 'design' => ['background_color' => 'rgba(255,255,255,0.03)', 'border' => '1px solid rgba(255,255,255,0.08)', 'padding' => '50px 40px', 'text_align' => 'center', 'icon_color' => '#c9a962', 'title_color' => '#ffffff', 'text_color' => '#e8d5a3']]]]
                    ]]
                ]],
                ['id' => 'section_ccta', 'design' => ['background_color' => '#c9a962', 'padding' => '140px 40px', 'text_align' => 'center'], 'rows' => [[
                    'id' => 'row_ccta', 'columns' => [['id' => 'col_ccta', 'width' => '100%', 'modules' => [
                        ['id' => 'mod_cct', 'type' => 'heading', 'content' => ['text' => 'Call Us Directly', 'level' => 'h2'], 'design' => ['font_size' => '60px', 'font_family' => 'Cormorant Garamond, serif', 'text_color' => '#1a1a1a', 'margin' => '0 0 20px 0']],
                        ['id' => 'mod_ccp', 'type' => 'text', 'content' => ['text' => '+1 (555) 123-4567'], 'design' => ['font_size' => '36px', 'font_family' => 'Cormorant Garamond, serif', 'text_color' => '#1a1a1a', 'margin' => '0 0 45px 0']],
                        ['id' => 'mod_ccb', 'type' => 'button', 'content' => ['text' => 'Call Now', 'url' => 'tel:+15551234567', 'icon' => 'fas fa-phone-alt'], 'design' => ['background_color' => '#1a1a1a', 'text_color' => '#c9a962', 'padding' => '18px 40px']]
                    ]]]
                ]]]
            ]]
        ]
    ]
];

try {
    $stmt = $db->prepare("INSERT INTO tb_layout_library (name, slug, description, category, industry, style, page_count, content_json, thumbnail, is_premium, is_ai_generated, downloads, rating, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        'Ambrosia Restaurant',
        'ambrosia-restaurant',
        'Elegant fine dining restaurant theme with 5 pages: Home, About, Menu, Gallery, Contact. Dark charcoal and gold color scheme.',
        'restaurant',
        'Restaurant & Food',
        'Elegant',
        5,
        json_encode($layoutJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600',
        0, 0, 0, 5.0
    ]);
    $layoutId = $db->lastInsertId();
    echo "<h2 style='color:green'>✓ Ambrosia Restaurant layout installed! ID: {$layoutId}</h2>";
    echo "<p><a href='/admin/layout-library'>Go to Layout Library</a></p>";
} catch (\PDOException $e) {
    echo "<h2 style='color:red'>Error:</h2><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
