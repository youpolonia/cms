<?php
/**
 * JTB Default Templates Seeder
 * Creates default header, footer templates with "All Pages" condition
 *
 * Run: echo "jaskolki" | sudo -S -u www-data php /var/www/cms/plugins/jessie-theme-builder/seed-defaults.php
 */

define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/core/database.php';

$pdo = \Core\Database::connection();

// ════════════════════════════════════════════
// 1. Default Header Template
// ════════════════════════════════════════════
$headerContent = json_encode([
    'content' => [
        [
            'id' => 'jtb_section_header_1',
            'type' => 'section',
            'attrs' => [
                'background_color' => '#ffffff',
                'padding' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
                'box_shadow' => '0 1px 3px rgba(0,0,0,0.08)',
            ],
            'rows' => [
                [
                    'id' => 'jtb_row_header_1',
                    'type' => 'row',
                    'attrs' => [
                        'columns' => '1_4,3_4',
                        'vertical_align' => 'center',
                        'padding' => ['top' => 16, 'right' => 30, 'bottom' => 16, 'left' => 30],
                    ],
                    'columns' => [
                        [
                            'id' => 'jtb_col_header_logo',
                            'type' => 'column',
                            'attrs' => [],
                            'modules' => [
                                [
                                    'id' => 'jtb_mod_site_logo',
                                    'type' => 'site_logo',
                                    'attrs' => [
                                        'logo_alt' => 'Site Logo',
                                        'logo_max_height' => '40',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'jtb_col_header_nav',
                            'type' => 'column',
                            'attrs' => [
                                'text_align' => 'right',
                            ],
                            'modules' => [
                                [
                                    'id' => 'jtb_mod_menu',
                                    'type' => 'menu',
                                    'attrs' => [
                                        'menu_style' => 'horizontal',
                                        'menu_font_size' => '15',
                                        'menu_font_weight' => '500',
                                        'menu_link_color' => '#374151',
                                        'menu_link_hover_color' => '#7c3aed',
                                        'menu_link_spacing' => '24',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
]);

// ════════════════════════════════════════════
// 2. Default Footer Template
// ════════════════════════════════════════════
$footerContent = json_encode([
    'content' => [
        [
            'id' => 'jtb_section_footer_1',
            'type' => 'section',
            'attrs' => [
                'background_color' => '#111827',
                'padding' => ['top' => 60, 'right' => 30, 'bottom' => 20, 'left' => 30],
            ],
            'rows' => [
                [
                    'id' => 'jtb_row_footer_main',
                    'type' => 'row',
                    'attrs' => [
                        'columns' => '1_3,1_3,1_3',
                        'padding' => ['top' => 0, 'right' => 0, 'bottom' => 40, 'left' => 0],
                    ],
                    'columns' => [
                        [
                            'id' => 'jtb_col_footer_about',
                            'type' => 'column',
                            'attrs' => [],
                            'modules' => [
                                [
                                    'id' => 'jtb_mod_footer_logo',
                                    'type' => 'site_logo',
                                    'attrs' => [
                                        'logo_max_height' => '32',
                                    ],
                                ],
                                [
                                    'id' => 'jtb_mod_footer_desc',
                                    'type' => 'text',
                                    'attrs' => [
                                        'content' => '<p style="color:#9ca3af;margin-top:16px;font-size:14px;">Built with Jessie CMS and Jessie Theme Builder.</p>',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'jtb_col_footer_nav',
                            'type' => 'column',
                            'attrs' => [],
                            'modules' => [
                                [
                                    'id' => 'jtb_mod_footer_heading',
                                    'type' => 'heading',
                                    'attrs' => [
                                        'text' => 'Quick Links',
                                        'level' => 'h4',
                                        'text_color' => '#f9fafb',
                                        'font_size' => '16',
                                        'font_weight' => '600',
                                    ],
                                ],
                                [
                                    'id' => 'jtb_mod_footer_menu',
                                    'type' => 'footer_menu',
                                    'attrs' => [
                                        'menu_link_color' => '#9ca3af',
                                        'menu_link_hover_color' => '#7c3aed',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'jtb_col_footer_social',
                            'type' => 'column',
                            'attrs' => [],
                            'modules' => [
                                [
                                    'id' => 'jtb_mod_footer_social_heading',
                                    'type' => 'heading',
                                    'attrs' => [
                                        'text' => 'Follow Us',
                                        'level' => 'h4',
                                        'text_color' => '#f9fafb',
                                        'font_size' => '16',
                                        'font_weight' => '600',
                                    ],
                                ],
                                [
                                    'id' => 'jtb_mod_footer_social',
                                    'type' => 'social_icons',
                                    'attrs' => [
                                        'icon_color' => '#9ca3af',
                                        'icon_hover_color' => '#7c3aed',
                                        'icon_size' => '20',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 'jtb_row_footer_copyright',
                    'type' => 'row',
                    'attrs' => [
                        'columns' => '1_1',
                        'border_top_width' => '1',
                        'border_top_color' => '#1f2937',
                        'border_top_style' => 'solid',
                        'padding' => ['top' => 20, 'right' => 0, 'bottom' => 0, 'left' => 0],
                    ],
                    'columns' => [
                        [
                            'id' => 'jtb_col_copyright',
                            'type' => 'column',
                            'attrs' => [],
                            'modules' => [
                                [
                                    'id' => 'jtb_mod_copyright',
                                    'type' => 'copyright',
                                    'attrs' => [
                                        'text_color' => '#6b7280',
                                        'font_size' => '13',
                                        'text_align' => 'center',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
]);

// ════════════════════════════════════════════
// CHECK EXISTING
// ════════════════════════════════════════════
$existing = $pdo->query("SELECT COUNT(*) FROM jtb_templates")->fetchColumn();
if ($existing > 0) {
    echo "⚠️  Already have $existing templates. Skipping seed.\n";
    echo "   To force re-seed, run: DELETE FROM jtb_templates; DELETE FROM jtb_template_conditions;\n";
    exit(0);
}

// ════════════════════════════════════════════
// INSERT TEMPLATES
// ════════════════════════════════════════════
$stmt = $pdo->prepare("
    INSERT INTO jtb_templates (name, type, content, conditions, is_active, priority, created_at)
    VALUES (?, ?, ?, ?, 1, 0, NOW())
");

// Header
$stmt->execute(['Default Header', 'header', $headerContent, json_encode([])]);
$headerId = $pdo->lastInsertId();

// Footer
$stmt->execute(['Default Footer', 'footer', $footerContent, json_encode([])]);
$footerId = $pdo->lastInsertId();

echo "✅ Created templates:\n";
echo "   Header ID: $headerId\n";
echo "   Footer ID: $footerId\n";

// ════════════════════════════════════════════
// INSERT CONDITIONS (Include: All Pages)
// ════════════════════════════════════════════
$condStmt = $pdo->prepare("
    INSERT INTO jtb_template_conditions (template_id, condition_type, page_type, object_id)
    VALUES (?, 'include', 'all', 0)
");

$condStmt->execute([$headerId]);
$condStmt->execute([$footerId]);

echo "✅ Added 'Include All' conditions for both templates\n";

// ════════════════════════════════════════════
// SEED DEFAULT THEME SETTINGS
// ════════════════════════════════════════════
$existingSettings = $pdo->query("SELECT COUNT(*) FROM jtb_theme_settings")->fetchColumn();
if ($existingSettings == 0) {
    $settingsStmt = $pdo->prepare("INSERT INTO jtb_theme_settings (setting_group, setting_key, setting_value) VALUES (?, ?, ?)");

    $defaults = [
        // Colors
        ['colors', 'primary_color', '#7c3aed'],
        ['colors', 'secondary_color', '#1e1b4b'],
        ['colors', 'accent_color', '#06b6d4'],
        ['colors', 'text_color', '#1e293b'],
        ['colors', 'text_light_color', '#64748b'],
        ['colors', 'heading_color', '#0f172a'],
        ['colors', 'link_color', '#7c3aed'],
        ['colors', 'link_hover_color', '#6d28d9'],
        ['colors', 'background_color', '#ffffff'],
        ['colors', 'surface_color', '#f8fafc'],
        ['colors', 'border_color', '#e2e8f0'],

        // Typography
        ['typography', 'body_font', 'Inter'],
        ['typography', 'body_size', '16'],
        ['typography', 'body_weight', '400'],
        ['typography', 'body_line_height', '1.6'],
        ['typography', 'heading_font', 'Inter'],
        ['typography', 'heading_weight', '700'],
        ['typography', 'h1_size', '48'],
        ['typography', 'h2_size', '36'],
        ['typography', 'h3_size', '28'],
        ['typography', 'h4_size', '22'],
        ['typography', 'h5_size', '18'],
        ['typography', 'h6_size', '16'],

        // Layout
        ['layout', 'content_width', '1200'],
        ['layout', 'gutter_width', '30'],
        ['layout', 'section_padding_top', '80'],
        ['layout', 'section_padding_bottom', '80'],

        // Buttons
        ['buttons', 'button_bg_color', '#7c3aed'],
        ['buttons', 'button_text_color', '#ffffff'],
        ['buttons', 'button_border_radius', '8'],
        ['buttons', 'button_padding_tb', '12'],
        ['buttons', 'button_padding_lr', '28'],
        ['buttons', 'button_font_weight', '600'],

        // Responsive
        ['responsive', 'tablet_breakpoint', '980'],
        ['responsive', 'phone_breakpoint', '767'],
    ];

    foreach ($defaults as [$group, $key, $value]) {
        $settingsStmt->execute([$group, $key, $value]);
    }

    echo "✅ Seeded " . count($defaults) . " theme settings\n";
} else {
    echo "⚠️  Theme settings already exist ($existingSettings). Skipping.\n";
}

echo "\n🎉 Done! Switch active theme to 'jtb' to see Theme Builder in action.\n";
echo "   UPDATE settings SET value = 'jtb' WHERE `key` = 'active_theme';\n";
