<?php
/**
 * JTB Theme Settings Class
 * Manages global theme settings for colors, typography, layout, buttons, etc.
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Theme_Settings
{
    private static ?array $cache = null;

    /**
     * Default settings structure
     */
    private static array $defaults = [
        'colors' => [
            'primary_color' => '#7c3aed',
            'secondary_color' => '#1e1b4b',
            'accent_color' => '#10b981',
            'text_color' => '#1f2937',
            'text_light_color' => '#6b7280',
            'heading_color' => '#111827',
            'link_color' => '#7c3aed',
            'link_hover_color' => '#5b21b6',
            'background_color' => '#ffffff',
            'surface_color' => '#f9fafb',
            'border_color' => '#e5e7eb',
            'success_color' => '#10b981',
            'warning_color' => '#f59e0b',
            'error_color' => '#ef4444',
            'info_color' => '#3b82f6'
        ],
        'typography' => [
            'body_font' => 'Inter',
            'heading_font' => 'Inter',
            'body_size' => '16',
            'body_size_unit' => 'px',
            'body_weight' => '400',
            'body_line_height' => '1.6',
            'heading_weight' => '700',
            'heading_line_height' => '1.2',
            'heading_letter_spacing' => '-0.02',
            'h1_size' => '48',
            'h1_size_unit' => 'px',
            'h2_size' => '36',
            'h2_size_unit' => 'px',
            'h3_size' => '28',
            'h3_size_unit' => 'px',
            'h4_size' => '24',
            'h4_size_unit' => 'px',
            'h5_size' => '20',
            'h5_size_unit' => 'px',
            'h6_size' => '18',
            'h6_size_unit' => 'px'
        ],
        'layout' => [
            'content_width' => '1200',
            'content_width_unit' => 'px',
            'gutter_width' => '30',
            'gutter_width_unit' => 'px',
            'section_padding_top' => '80',
            'section_padding_top_unit' => 'px',
            'section_padding_bottom' => '80',
            'section_padding_bottom_unit' => 'px',
            'row_gap' => '30',
            'row_gap_unit' => 'px',
            'column_gap' => '30',
            'column_gap_unit' => 'px'
        ],
        'buttons' => [
            'button_bg_color' => '#7c3aed',
            'button_text_color' => '#ffffff',
            'button_border_width' => '0',
            'button_border_color' => '#7c3aed',
            'button_border_radius' => '8',
            'button_border_radius_unit' => 'px',
            'button_padding_tb' => '12',
            'button_padding_lr' => '24',
            'button_padding_unit' => 'px',
            'button_font_size' => '16',
            'button_font_size_unit' => 'px',
            'button_font_weight' => '600',
            'button_letter_spacing' => '0',
            'button_text_transform' => 'none',
            'button_hover_bg' => '#5b21b6',
            'button_hover_text' => '#ffffff',
            'button_hover_border' => '#5b21b6',
            'button_transition' => '0.2'
        ],
        'forms' => [
            'input_bg_color' => '#ffffff',
            'input_text_color' => '#1f2937',
            'input_border_color' => '#d1d5db',
            'input_border_width' => '1',
            'input_border_radius' => '6',
            'input_padding_tb' => '10',
            'input_padding_lr' => '14',
            'input_font_size' => '16',
            'input_focus_border_color' => '#7c3aed',
            'input_focus_shadow' => '0 0 0 3px rgba(124, 58, 237, 0.1)',
            'placeholder_color' => '#9ca3af',
            'label_color' => '#374151',
            'label_font_size' => '14',
            'label_font_weight' => '500',
            'label_margin_bottom' => '6'
        ],
        'header' => [
            'header_bg_color' => '#ffffff',
            'header_text_color' => '#1f2937',
            'header_height' => '80',
            'header_height_unit' => 'px',
            'header_padding_tb' => '0',
            'header_padding_lr' => '30',
            'header_shadow' => '0 1px 3px rgba(0,0,0,0.1)',
            'header_sticky' => false,
            'header_sticky_bg' => '#ffffff',
            'header_sticky_shadow' => '0 2px 10px rgba(0,0,0,0.1)',
            'header_transparent' => false,
            'header_transparent_text' => '#ffffff',
            'logo_height' => '50',
            'logo_height_unit' => 'px',
            'logo_height_sticky' => '40'
        ],
        'menu' => [
            'menu_font_family' => 'inherit',
            'menu_font_size' => '16',
            'menu_font_weight' => '500',
            'menu_text_transform' => 'none',
            'menu_letter_spacing' => '0',
            'menu_link_color' => '#1f2937',
            'menu_link_hover_color' => '#7c3aed',
            'menu_link_active_color' => '#7c3aed',
            'menu_link_padding_tb' => '10',
            'menu_link_padding_lr' => '16',
            'dropdown_bg_color' => '#ffffff',
            'dropdown_text_color' => '#1f2937',
            'dropdown_hover_bg' => '#f3f4f6',
            'dropdown_border_radius' => '8',
            'dropdown_shadow' => '0 10px 40px rgba(0,0,0,0.15)',
            'mobile_breakpoint' => '980',
            'mobile_menu_bg' => '#ffffff',
            'mobile_menu_text' => '#1f2937',
            'hamburger_color' => '#1f2937'
        ],
        'footer' => [
            'footer_bg_color' => '#1f2937',
            'footer_text_color' => '#d1d5db',
            'footer_heading_color' => '#ffffff',
            'footer_link_color' => '#d1d5db',
            'footer_link_hover_color' => '#ffffff',
            'footer_padding_top' => '60',
            'footer_padding_bottom' => '60',
            'footer_columns' => '4',
            'copyright_bg_color' => '#111827',
            'copyright_text_color' => '#9ca3af',
            'copyright_padding_tb' => '20',
            'copyright_text' => '© {year} {site_name}. All rights reserved.'
        ],
        'blog' => [
            'blog_layout' => 'grid',
            'blog_columns' => '3',
            'blog_gap' => '30',
            'post_card_bg' => '#ffffff',
            'post_card_border_radius' => '12',
            'post_card_shadow' => '0 4px 6px rgba(0,0,0,0.07)',
            'post_card_hover_shadow' => '0 10px 40px rgba(0,0,0,0.12)',
            'show_featured_image' => true,
            'show_date' => true,
            'show_author' => true,
            'show_categories' => true,
            'show_excerpt' => true,
            'excerpt_length' => '150',
            'show_read_more' => true,
            'read_more_text' => 'Read More',
            'pagination_style' => 'numbers'
        ],
        'responsive' => [
            'tablet_breakpoint' => '980',
            'phone_breakpoint' => '767',
            'h1_size_tablet' => '36',
            'h1_size_phone' => '28',
            'h2_size_tablet' => '28',
            'h2_size_phone' => '24',
            'body_size_tablet' => '15',
            'body_size_phone' => '14',
            'section_padding_tablet' => '60',
            'section_padding_phone' => '40',
            'content_width_tablet' => '100%',
            'content_width_phone' => '100%'
        ]
    ];

    /**
     * Create settings table if not exists
     */
    public static function createTable(): void
    {
        $db = \core\Database::connection();

        $db->exec("
            CREATE TABLE IF NOT EXISTS jtb_theme_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_group VARCHAR(50) NOT NULL,
                setting_key VARCHAR(100) NOT NULL,
                setting_value TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_setting (setting_group, setting_key),
                INDEX idx_group (setting_group)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Get all settings merged with defaults
     */
    public static function getAll(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $settings = self::$defaults;

        try {
            $db = \core\Database::connection();
            $stmt = $db->query("SELECT setting_group, setting_key, setting_value FROM jtb_theme_settings");
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $group = $row['setting_group'];
                $key = $row['setting_key'];
                $value = $row['setting_value'];

                // Try to decode JSON, otherwise use as string
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }

                if (isset($settings[$group])) {
                    $settings[$group][$key] = $value;
                }
            }
        } catch (\Exception $e) {
            // Table might not exist yet, return defaults
            error_log('JTB Theme Settings: ' . $e->getMessage());
        }

        self::$cache = $settings;
        return $settings;
    }

    /**
     * Get settings for a specific group
     */
    public static function getGroup(string $group): array
    {
        $all = self::getAll();
        return $all[$group] ?? [];
    }

    /**
     * Get a single setting value
     */
    public static function get(string $group, string $key, $default = null)
    {
        $all = self::getAll();
        return $all[$group][$key] ?? $default ?? (self::$defaults[$group][$key] ?? null);
    }

    /**
     * Save a single setting
     */
    public static function set(string $group, string $key, $value): bool
    {
        try {
            $db = \core\Database::connection();

            // Encode arrays/objects as JSON
            $valueStr = is_array($value) || is_object($value) ? json_encode($value) : (string) $value;

            $stmt = $db->prepare("
                INSERT INTO jtb_theme_settings (setting_group, setting_key, setting_value)
                VALUES (:group, :key, :value)
                ON DUPLICATE KEY UPDATE setting_value = :value2, updated_at = NOW()
            ");

            $stmt->execute([
                ':group' => $group,
                ':key' => $key,
                ':value' => $valueStr,
                ':value2' => $valueStr
            ]);

            // Clear cache
            self::$cache = null;

            return true;
        } catch (\Exception $e) {
            error_log('JTB Theme Settings save error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Save multiple settings at once
     */
    public static function saveAll(array $settings): bool
    {
        try {
            $db = \core\Database::connection();
            $db->beginTransaction();

            foreach ($settings as $group => $groupSettings) {
                if (!is_array($groupSettings)) {
                    continue;
                }

                foreach ($groupSettings as $key => $value) {
                    self::set($group, $key, $value);
                }
            }

            $db->commit();
            self::$cache = null;

            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            error_log('JTB Theme Settings saveAll error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Save a group of settings
     */
    public static function saveGroup(string $group, array $settings): bool
    {
        return self::saveAll([$group => $settings]);
    }

    /**
     * Reset a group to defaults
     */
    public static function resetGroup(string $group): bool
    {
        if (!isset(self::$defaults[$group])) {
            return false;
        }

        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("DELETE FROM jtb_theme_settings WHERE setting_group = :group");
            $stmt->execute([':group' => $group]);

            self::$cache = null;
            return true;
        } catch (\Exception $e) {
            error_log('JTB Theme Settings reset error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reset all settings to defaults
     */
    public static function resetAll(): bool
    {
        try {
            $db = \core\Database::connection();
            $db->exec("TRUNCATE TABLE jtb_theme_settings");
            self::$cache = null;
            return true;
        } catch (\Exception $e) {
            error_log('JTB Theme Settings resetAll error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get default settings
     */
    public static function getDefaults(): array
    {
        return self::$defaults;
    }

    /**
     * Get default value for specific setting
     */
    public static function getDefault(string $group, string $key)
    {
        return self::$defaults[$group][$key] ?? null;
    }

    /**
     * Get all setting groups
     */
    public static function getGroups(): array
    {
        return array_keys(self::$defaults);
    }

    /**
     * Get group labels for UI
     */
    public static function getGroupLabels(): array
    {
        return [
            'colors' => 'Colors',
            'typography' => 'Typography',
            'layout' => 'Layout',
            'buttons' => 'Buttons',
            'forms' => 'Forms',
            'header' => 'Header',
            'menu' => 'Menu',
            'footer' => 'Footer',
            'blog' => 'Blog',
            'responsive' => 'Responsive'
        ];
    }

    /**
     * Get Google Fonts used in typography settings
     */
    public static function getUsedFonts(): array
    {
        $settings = self::getAll();
        $fonts = [];

        $typography = $settings['typography'] ?? [];

        if (!empty($typography['body_font']) && $typography['body_font'] !== 'inherit') {
            $fonts[] = $typography['body_font'];
        }

        if (!empty($typography['heading_font']) && $typography['heading_font'] !== 'inherit') {
            $fonts[] = $typography['heading_font'];
        }

        $menu = $settings['menu'] ?? [];
        if (!empty($menu['menu_font_family']) && $menu['menu_font_family'] !== 'inherit') {
            $fonts[] = $menu['menu_font_family'];
        }

        return array_unique($fonts);
    }

    /**
     * Export settings as JSON
     */
    public static function export(): string
    {
        return json_encode(self::getAll(), JSON_PRETTY_PRINT);
    }

    /**
     * Import settings from JSON
     */
    public static function import(string $json): bool
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return false;
        }

        return self::saveAll($data);
    }

    /**
     * Clear cache (useful after direct DB changes)
     */
    public static function clearCache(): void
    {
        self::$cache = null;
    }
}
