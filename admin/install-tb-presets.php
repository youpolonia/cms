<?php
/**
 * Theme Builder 3.0 - Install Preset Library
 *
 * Run ONCE to populate tb_preset_library with pre-built templates.
 * Location: /admin/install-tb-presets.php
 * Access: /admin/install-tb-presets.php (DEV_MODE only)
 *
 * @package ThemeBuilder
 * @version 3.0
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

// DEV_MODE gate
if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    die('Access denied. DEV_MODE required.');
}

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();

require_once CMS_ROOT . '/core/theme-builder/init.php';

// ═══════════════════════════════════════════════════════════════════════════════
// HEADER PRESETS
// ═══════════════════════════════════════════════════════════════════════════════

$headerPresets = [
    [
        'slug' => 'minimal',
        'name' => 'Minimal Header',
        'description' => 'Clean, simple header with logo and navigation',
        'tags' => 'minimal,clean,simple,modern',
        'content' => [
            'sections' => [[
                'id' => 'sec_h1',
                'settings' => ['background_color' => '#ffffff', 'padding_top' => '0', 'padding_bottom' => '0'],
                'rows' => [[
                    'id' => 'row_h1',
                    'layout' => '1_3_2_3',
                    'columns' => [
                        ['id' => 'col_h1a', 'modules' => [[
                            'id' => 'mod_logo', 'type' => 'logo',
                            'content' => ['image' => '/assets/images/logo.png', 'link_url' => '/', 'max_height' => '50px'],
                            'design' => ['alignment' => 'left', 'padding' => '15px 0']
                        ]]],
                        ['id' => 'col_h1b', 'modules' => [[
                            'id' => 'mod_menu', 'type' => 'menu',
                            'content' => ['menu_source' => 'custom', 'items' => [
                                ['label' => 'Home', 'url' => '/'],
                                ['label' => 'About', 'url' => '/about'],
                                ['label' => 'Services', 'url' => '/services'],
                                ['label' => 'Contact', 'url' => '/contact']
                            ]],
                            'design' => ['orientation' => 'horizontal', 'alignment' => 'right', 'gap' => '32px', 'font_size' => '15px', 'text_color' => '#374151', 'hover_color' => '#0073e6']
                        ]]]
                    ]
                ]]
            ]]
        ]
    ],
    [
        'slug' => 'corporate',
        'name' => 'Corporate Header',
        'description' => 'Professional header with top bar and CTA button',
        'tags' => 'corporate,business,professional,cta',
        'content' => [
            'sections' => [
                [
                    'id' => 'sec_topbar',
                    'settings' => ['background_color' => '#1e293b', 'padding_top' => '8px', 'padding_bottom' => '8px'],
                    'rows' => [[
                        'id' => 'row_top',
                        'layout' => '1_2_1_2',
                        'columns' => [
                            ['id' => 'col_contact', 'modules' => [[
                                'id' => 'mod_text', 'type' => 'text',
                                'content' => ['text' => '📞 (555) 123-4567 | ✉️ info@company.com'],
                                'design' => ['font_size' => '13px', 'text_color' => '#94a3b8']
                            ]]],
                            ['id' => 'col_social', 'modules' => [[
                                'id' => 'mod_social', 'type' => 'social',
                                'content' => ['networks' => [
                                    ['network' => 'facebook', 'url' => '#'],
                                    ['network' => 'twitter', 'url' => '#'],
                                    ['network' => 'linkedin', 'url' => '#']
                                ]],
                                'design' => ['icon_size' => '16px', 'alignment' => 'right']
                            ]]]
                        ]
                    ]]
                ],
                [
                    'id' => 'sec_main',
                    'settings' => ['background_color' => '#ffffff', 'padding_top' => '0', 'padding_bottom' => '0'],
                    'rows' => [[
                        'id' => 'row_main',
                        'layout' => '1_4_1_2_1_4',
                        'columns' => [
                            ['id' => 'col_logo', 'modules' => [[
                                'id' => 'mod_logo', 'type' => 'logo',
                                'content' => ['image' => '/assets/images/logo.png', 'link_url' => '/', 'max_height' => '50px'],
                                'design' => ['alignment' => 'left', 'padding' => '20px 0']
                            ]]],
                            ['id' => 'col_nav', 'modules' => [[
                                'id' => 'mod_menu', 'type' => 'menu',
                                'content' => ['items' => [
                                    ['label' => 'About', 'url' => '/about'],
                                    ['label' => 'Services', 'url' => '/services'],
                                    ['label' => 'Portfolio', 'url' => '/portfolio'],
                                    ['label' => 'Blog', 'url' => '/blog'],
                                    ['label' => 'Contact', 'url' => '/contact']
                                ]],
                                'design' => ['orientation' => 'horizontal', 'alignment' => 'center', 'gap' => '28px']
                            ]]],
                            ['id' => 'col_cta', 'modules' => [[
                                'id' => 'mod_btn', 'type' => 'button',
                                'content' => ['text' => 'Get a Quote', 'url' => '/quote'],
                                'design' => ['background_color' => '#0073e6', 'text_color' => '#ffffff', 'border_radius' => '6px']
                            ]]]
                        ]
                    ]]
                ]
            ]
        ]
    ],
    [
        'slug' => 'dark',
        'name' => 'Dark Header',
        'description' => 'Sleek dark header for modern sites',
        'tags' => 'dark,modern,sleek,tech',
        'content' => [
            'sections' => [[
                'id' => 'sec_h1',
                'settings' => ['background_color' => '#0f172a', 'padding_top' => '0', 'padding_bottom' => '0'],
                'rows' => [[
                    'id' => 'row_h1',
                    'layout' => '1_3_2_3',
                    'columns' => [
                        ['id' => 'col_logo', 'modules' => [[
                            'id' => 'mod_logo', 'type' => 'logo',
                            'content' => ['image' => '/assets/images/logo-light.png', 'link_url' => '/', 'max_height' => '40px'],
                            'design' => ['alignment' => 'left', 'padding' => '20px 0']
                        ]]],
                        ['id' => 'col_nav', 'modules' => [[
                            'id' => 'mod_menu', 'type' => 'menu',
                            'content' => ['items' => [
                                ['label' => 'Features', 'url' => '/features'],
                                ['label' => 'Pricing', 'url' => '/pricing'],
                                ['label' => 'Docs', 'url' => '/docs'],
                                ['label' => 'Blog', 'url' => '/blog']
                            ]],
                            'design' => ['orientation' => 'horizontal', 'alignment' => 'right', 'gap' => '32px', 'text_color' => '#e2e8f0', 'hover_color' => '#38bdf8']
                        ]]]
                    ]
                ]]
            ]]
        ]
    ]
];

// ═══════════════════════════════════════════════════════════════════════════════
// FOOTER PRESETS
// ═══════════════════════════════════════════════════════════════════════════════

$footerPresets = [
    [
        'slug' => 'simple',
        'name' => 'Simple Footer',
        'description' => 'Minimal footer with copyright and links',
        'tags' => 'simple,minimal,clean',
        'content' => [
            'sections' => [[
                'id' => 'sec_f1',
                'settings' => ['background_color' => '#1e293b', 'padding_top' => '40px', 'padding_bottom' => '40px'],
                'rows' => [[
                    'id' => 'row_f1',
                    'layout' => '1_2_1_2',
                    'columns' => [
                        ['id' => 'col_copy', 'modules' => [[
                            'id' => 'mod_text', 'type' => 'text',
                            'content' => ['text' => '© 2025 Your Company. All rights reserved.'],
                            'design' => ['font_size' => '14px', 'text_color' => '#94a3b8']
                        ]]],
                        ['id' => 'col_links', 'modules' => [[
                            'id' => 'mod_menu', 'type' => 'menu',
                            'content' => ['items' => [
                                ['label' => 'Privacy', 'url' => '/privacy'],
                                ['label' => 'Terms', 'url' => '/terms'],
                                ['label' => 'Contact', 'url' => '/contact']
                            ]],
                            'design' => ['orientation' => 'horizontal', 'alignment' => 'right', 'gap' => '24px', 'text_color' => '#94a3b8', 'hover_color' => '#ffffff']
                        ]]]
                    ]
                ]]
            ]]
        ]
    ],
    [
        'slug' => 'multi-column',
        'name' => 'Multi-Column Footer',
        'description' => 'Footer with multiple link columns',
        'tags' => 'multi-column,links,comprehensive',
        'content' => [
            'sections' => [[
                'id' => 'sec_f1',
                'settings' => ['background_color' => '#111827', 'padding_top' => '60px', 'padding_bottom' => '40px'],
                'rows' => [
                    [
                        'id' => 'row_cols',
                        'layout' => '1_4_1_4_1_4_1_4',
                        'columns' => [
                            ['id' => 'col_about', 'modules' => [[
                                'id' => 'mod_heading', 'type' => 'heading',
                                'content' => ['text' => 'About', 'level' => 'h4'],
                                'design' => ['text_color' => '#ffffff', 'font_size' => '16px']
                            ], [
                                'id' => 'mod_text', 'type' => 'text',
                                'content' => ['text' => 'Building amazing digital experiences since 2020.'],
                                'design' => ['text_color' => '#9ca3af', 'font_size' => '14px']
                            ]]],
                            ['id' => 'col_company', 'modules' => [[
                                'id' => 'mod_heading', 'type' => 'heading',
                                'content' => ['text' => 'Company', 'level' => 'h4'],
                                'design' => ['text_color' => '#ffffff', 'font_size' => '16px']
                            ], [
                                'id' => 'mod_menu', 'type' => 'menu',
                                'content' => ['items' => [['label' => 'About', 'url' => '/about'], ['label' => 'Team', 'url' => '/team'], ['label' => 'Careers', 'url' => '/careers']]],
                                'design' => ['orientation' => 'vertical', 'gap' => '12px', 'text_color' => '#9ca3af']
                            ]]],
                            ['id' => 'col_services', 'modules' => [[
                                'id' => 'mod_heading', 'type' => 'heading',
                                'content' => ['text' => 'Services', 'level' => 'h4'],
                                'design' => ['text_color' => '#ffffff', 'font_size' => '16px']
                            ], [
                                'id' => 'mod_menu', 'type' => 'menu',
                                'content' => ['items' => [['label' => 'Web Design', 'url' => '/services/web'], ['label' => 'Development', 'url' => '/services/dev'], ['label' => 'SEO', 'url' => '/services/seo']]],
                                'design' => ['orientation' => 'vertical', 'gap' => '12px', 'text_color' => '#9ca3af']
                            ]]],
                            ['id' => 'col_contact', 'modules' => [[
                                'id' => 'mod_heading', 'type' => 'heading',
                                'content' => ['text' => 'Contact', 'level' => 'h4'],
                                'design' => ['text_color' => '#ffffff', 'font_size' => '16px']
                            ], [
                                'id' => 'mod_text', 'type' => 'text',
                                'content' => ['text' => 'hello@company.com | (555) 123-4567'],
                                'design' => ['text_color' => '#9ca3af', 'font_size' => '14px']
                            ]]]
                        ]
                    ],
                    [
                        'id' => 'row_bottom',
                        'layout' => '1_1',
                        'columns' => [
                            ['id' => 'col_copy', 'modules' => [[
                                'id' => 'mod_text', 'type' => 'text',
                                'content' => ['text' => '© 2025 Company Name. All rights reserved.'],
                                'design' => ['text_align' => 'center', 'text_color' => '#6b7280', 'font_size' => '13px']
                            ]]]
                        ]
                    ]
                ]
            ]]
        ]
    ]
];

// ═══════════════════════════════════════════════════════════════════════════════
// SIDEBAR PRESETS
// ═══════════════════════════════════════════════════════════════════════════════

$sidebarPresets = [
    [
        'slug' => 'blog-sidebar',
        'name' => 'Blog Sidebar',
        'description' => 'Classic blog sidebar with search, categories, and recent posts',
        'tags' => 'blog,search,categories,recent',
        'content' => [
            'sections' => [[
                'id' => 'sec_s1',
                'settings' => ['padding_top' => '0', 'padding_bottom' => '0'],
                'rows' => [
                    ['id' => 'row_search', 'layout' => '1_1', 'columns' => [
                        ['id' => 'col_s', 'modules' => [[
                            'id' => 'mod_search', 'type' => 'search',
                            'content' => ['placeholder' => 'Search...'],
                            'design' => ['style' => 'default']
                        ]]]
                    ]],
                    ['id' => 'row_about', 'layout' => '1_1', 'columns' => [
                        ['id' => 'col_a', 'modules' => [[
                            'id' => 'mod_heading', 'type' => 'heading',
                            'content' => ['text' => 'About', 'level' => 'h4'],
                            'design' => ['font_size' => '18px']
                        ], [
                            'id' => 'mod_text', 'type' => 'text',
                            'content' => ['text' => 'Welcome to our blog! We share insights and tips.'],
                            'design' => ['font_size' => '14px']
                        ]]]
                    ]],
                    ['id' => 'row_cat', 'layout' => '1_1', 'columns' => [
                        ['id' => 'col_c', 'modules' => [[
                            'id' => 'mod_heading', 'type' => 'heading',
                            'content' => ['text' => 'Categories', 'level' => 'h4'],
                            'design' => ['font_size' => '18px']
                        ], [
                            'id' => 'mod_menu', 'type' => 'menu',
                            'content' => ['items' => [['label' => 'Technology', 'url' => '/cat/tech'], ['label' => 'Design', 'url' => '/cat/design'], ['label' => 'Business', 'url' => '/cat/business']]],
                            'design' => ['orientation' => 'vertical', 'gap' => '10px']
                        ]]]
                    ]]
                ]
            ]]
        ]
    ]
];

// ═══════════════════════════════════════════════════════════════════════════════
// 404 PRESETS
// ═══════════════════════════════════════════════════════════════════════════════

$notFoundPresets = [
    [
        'slug' => 'simple-404',
        'name' => 'Simple 404',
        'description' => 'Clean 404 page with message and home link',
        'tags' => 'simple,clean,minimal',
        'content' => [
            'sections' => [[
                'id' => 'sec_404',
                'settings' => ['background_color' => '#f8fafc', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [[
                    'id' => 'row_404',
                    'layout' => '1_1',
                    'columns' => [
                        ['id' => 'col_404', 'modules' => [[
                            'id' => 'mod_heading', 'type' => 'heading',
                            'content' => ['text' => '404', 'level' => 'h1'],
                            'design' => ['font_size' => '120px', 'text_color' => '#0073e6', 'text_align' => 'center']
                        ], [
                            'id' => 'mod_subhead', 'type' => 'heading',
                            'content' => ['text' => 'Page Not Found', 'level' => 'h2'],
                            'design' => ['font_size' => '32px', 'text_align' => 'center']
                        ], [
                            'id' => 'mod_text', 'type' => 'text',
                            'content' => ['text' => 'The page you are looking for does not exist or has been moved.'],
                            'design' => ['text_align' => 'center', 'text_color' => '#64748b']
                        ], [
                            'id' => 'mod_btn', 'type' => 'button',
                            'content' => ['text' => 'Go Home', 'url' => '/'],
                            'design' => ['background_color' => '#0073e6', 'text_color' => '#ffffff']
                        ]]]
                    ]
                ]]
            ]]
        ]
    ]
];

// ═══════════════════════════════════════════════════════════════════════════════
// ARCHIVE PRESETS
// ═══════════════════════════════════════════════════════════════════════════════

$archivePresets = [
    [
        'slug' => 'grid-archive',
        'name' => 'Grid Archive',
        'description' => 'Posts displayed in a responsive grid',
        'tags' => 'grid,cards,responsive',
        'content' => [
            'sections' => [[
                'id' => 'sec_archive',
                'settings' => ['padding_top' => '40px', 'padding_bottom' => '40px'],
                'rows' => [[
                    'id' => 'row_archive',
                    'layout' => '1_1',
                    'columns' => [
                        ['id' => 'col_archive', 'modules' => [[
                            'id' => 'mod_blog', 'type' => 'blog',
                            'content' => ['posts_count' => 9, 'show_excerpt' => true, 'show_date' => true],
                            'design' => ['columns' => 3, 'gap' => '30px', 'card_style' => 'card']
                        ]]]
                    ]
                ]]
            ]]
        ]
    ]
];

// ═══════════════════════════════════════════════════════════════════════════════
// SINGLE POST PRESETS
// ═══════════════════════════════════════════════════════════════════════════════

$singlePresets = [
    [
        'slug' => 'classic-single',
        'name' => 'Classic Single',
        'description' => 'Traditional blog post layout with sidebar',
        'tags' => 'classic,traditional,sidebar',
        'content' => [
            'sections' => [[
                'id' => 'sec_single',
                'settings' => ['padding_top' => '40px', 'padding_bottom' => '40px'],
                'rows' => [[
                    'id' => 'row_single',
                    'layout' => '2_3_1_3',
                    'columns' => [
                        ['id' => 'col_content', 'modules' => [[
                            'id' => 'mod_title', 'type' => 'post_title',
                            'content' => ['show_meta' => true, 'show_date' => true, 'show_author' => true],
                            'design' => ['title_size' => '36px']
                        ], [
                            'id' => 'mod_content', 'type' => 'post_content',
                            'content' => [],
                            'design' => ['max_width' => '100%', 'font_size' => '17px', 'line_height' => '1.8']
                        ], [
                            'id' => 'mod_nav', 'type' => 'posts_navigation',
                            'content' => ['show_title' => true],
                            'design' => ['style' => 'default']
                        ], [
                            'id' => 'mod_comments', 'type' => 'comments',
                            'content' => ['show_form' => true],
                            'design' => []
                        ]]],
                        ['id' => 'col_sidebar', 'modules' => [[
                            'id' => 'mod_sidebar', 'type' => 'sidebar',
                            'content' => ['widget_area_id' => null, 'title' => 'Sidebar'],
                            'design' => []
                        ]]]
                    ]
                ]]
            ]]
        ]
    ]
];

// ═══════════════════════════════════════════════════════════════════════════════
// INSTALL PRESETS
// ═══════════════════════════════════════════════════════════════════════════════

$installed = 0;
$errors = [];

// Install Header presets
foreach ($headerPresets as $i => $preset) {
    try {
        tb_save_preset([
            'type' => 'header',
            'name' => $preset['name'],
            'slug' => $preset['slug'],
            'description' => $preset['description'] ?? '',
            'tags' => $preset['tags'] ?? '',
            'content' => $preset['content'],
            'sort_order' => $i
        ]);
        $installed++;
    } catch (Exception $e) {
        $errors[] = "Header/{$preset['slug']}: " . $e->getMessage();
    }
}

// Install Footer presets
foreach ($footerPresets as $i => $preset) {
    try {
        tb_save_preset([
            'type' => 'footer',
            'name' => $preset['name'],
            'slug' => $preset['slug'],
            'description' => $preset['description'] ?? '',
            'tags' => $preset['tags'] ?? '',
            'content' => $preset['content'],
            'sort_order' => $i
        ]);
        $installed++;
    } catch (Exception $e) {
        $errors[] = "Footer/{$preset['slug']}: " . $e->getMessage();
    }
}

// Install Sidebar presets
foreach ($sidebarPresets as $i => $preset) {
    try {
        tb_save_preset([
            'type' => 'sidebar',
            'name' => $preset['name'],
            'slug' => $preset['slug'],
            'description' => $preset['description'] ?? '',
            'tags' => $preset['tags'] ?? '',
            'content' => $preset['content'],
            'sort_order' => $i
        ]);
        $installed++;
    } catch (Exception $e) {
        $errors[] = "Sidebar/{$preset['slug']}: " . $e->getMessage();
    }
}

// Install 404 presets
foreach ($notFoundPresets as $i => $preset) {
    try {
        tb_save_preset([
            'type' => '404',
            'name' => $preset['name'],
            'slug' => $preset['slug'],
            'description' => $preset['description'] ?? '',
            'tags' => $preset['tags'] ?? '',
            'content' => $preset['content'],
            'sort_order' => $i
        ]);
        $installed++;
    } catch (Exception $e) {
        $errors[] = "404/{$preset['slug']}: " . $e->getMessage();
    }
}

// Install Archive presets
foreach ($archivePresets as $i => $preset) {
    try {
        tb_save_preset([
            'type' => 'archive',
            'name' => $preset['name'],
            'slug' => $preset['slug'],
            'description' => $preset['description'] ?? '',
            'tags' => $preset['tags'] ?? '',
            'content' => $preset['content'],
            'sort_order' => $i
        ]);
        $installed++;
    } catch (Exception $e) {
        $errors[] = "Archive/{$preset['slug']}: " . $e->getMessage();
    }
}

// Install Single presets
foreach ($singlePresets as $i => $preset) {
    try {
        tb_save_preset([
            'type' => 'single',
            'name' => $preset['name'],
            'slug' => $preset['slug'],
            'description' => $preset['description'] ?? '',
            'tags' => $preset['tags'] ?? '',
            'content' => $preset['content'],
            'sort_order' => $i
        ]);
        $installed++;
    } catch (Exception $e) {
        $errors[] = "Single/{$preset['slug']}: " . $e->getMessage();
    }
}

// Get counts
$counts = tb_get_preset_counts();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install TB Presets</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #1e1e2e; color: #cdd6f4; margin: 0; padding: 40px; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #89b4fa; margin-bottom: 30px; }
        .card { background: #313244; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
        .success { border-left: 4px solid #a6e3a1; }
        .error { border-left: 4px solid #f38ba8; }
        .stat { display: inline-block; background: #45475a; padding: 8px 16px; border-radius: 6px; margin: 4px; }
        .stat-num { font-size: 24px; font-weight: bold; color: #89b4fa; }
        .stat-label { font-size: 12px; color: #a6adc8; }
        ul { margin: 0; padding-left: 20px; }
        li { margin: 4px 0; }
        a { color: #89b4fa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 Theme Builder Preset Library Installation</h1>

        <div class="card success">
            <h2 style="color: #a6e3a1; margin-top: 0;">✅ Installation Complete</h2>
            <p><strong><?= $installed ?></strong> presets installed successfully.</p>

            <div style="margin: 20px 0;">
                <?php foreach ($counts as $type => $count): ?>
                <div class="stat">
                    <div class="stat-num"><?= $count ?></div>
                    <div class="stat-label"><?= ucfirst($type) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="card error">
            <h3 style="color: #f38ba8; margin-top: 0;">⚠️ Errors (<?= count($errors) ?>)</h3>
            <ul>
                <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="card">
            <h3 style="margin-top: 0;">📋 Next Steps</h3>
            <ol>
                <li>Go to <a href="/admin/theme-builder/templates">Theme Builder Templates</a></li>
                <li>Edit any template</li>
                <li>Click "📚 Library" button to load a preset</li>
                <li>Customize and save!</li>
            </ol>
        </div>

        <p style="color: #6c7086; font-size: 13px;">
            This page can be safely deleted after installation.<br>
            Presets are stored in the database (tb_preset_library table).
        </p>
    </div>
</body>
</html>
