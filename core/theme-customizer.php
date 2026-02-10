<?php

// Ensure Cache class is loaded
if (!class_exists('Cache') && file_exists(__DIR__ . '/cache.php')) {
    require_once __DIR__ . '/cache.php';
}
/**
 * Theme Customizer — Core Functions
 * 
 * Provides theme_get() / theme_set() for reading/writing theme customizations.
 * Used by Theme Studio UI and theme templates.
 * 
 * Usage in templates:
 *   <?= esc(theme_get('hero.headline', 'Welcome')) ?>
 *   <?= theme_get('brand.primary_color', '#7c3aed') ?>
 *   <?php if (theme_get('sections.pricing.enabled', true)): ?>
 */

if (!function_exists('theme_get')) {
    /**
     * Get a customized value for the active theme
     * 
     * Fallback chain:
     * 1. DB customization (theme_customizations table)
     * 2. theme.json customizable.{section}.fields.{key}.default
     * 3. Provided $default parameter
     * 
     * @param string $path "section.key" format (e.g., "hero.headline")
     * @param mixed $default Fallback value
     * @return mixed
     */
    function theme_get(string $path, mixed $default = null): mixed
    {
        global $_theme_mem_cache, $_theme_cache_dirty;
        
        if (!is_array($_theme_mem_cache)) $_theme_mem_cache = [];
        
        $themeSlug = get_active_theme();
        
        // Reload if cache was invalidated or not loaded
        if (!empty($_theme_cache_dirty) || !isset($_theme_mem_cache[$themeSlug])) {
            $_theme_mem_cache = [];
            $_theme_cache_dirty = false;
            $_theme_mem_cache[$themeSlug] = _theme_load_customizations($themeSlug);
        }
        
        $cache = &$_theme_mem_cache;
        
        // Parse "section.key" path
        $parts = explode('.', $path, 2);
        if (count($parts) < 2) {
            return $default;
        }
        
        [$section, $key] = $parts;
        
        // 1. Check DB customizations
        if (isset($cache[$themeSlug][$section][$key])) {
            return $cache[$themeSlug][$section][$key];
        }
        
        // 2. Check theme.json customizable defaults
        $config = get_theme_config($themeSlug);
        $schemaDefault = $config['customizable'][$section]['fields'][$key]['default'] ?? null;
        if ($schemaDefault !== null) {
            return $schemaDefault;
        }
        
        // 3. Caller default
        return $default;
    }
}

if (!function_exists('theme_get_all')) {
    /**
     * Get all customizations for a theme (merged: DB values over schema defaults)
     */
    function theme_get_all(?string $themeSlug = null): array
    {
        $themeSlug = $themeSlug ?? get_active_theme();
        $dbValues = _theme_load_customizations($themeSlug);
        
        // Merge with schema defaults
        $config = get_theme_config($themeSlug);
        $schema = $config['customizable'] ?? [];
        $merged = [];
        
        foreach ($schema as $section => $sectionDef) {
            $fields = $sectionDef['fields'] ?? [];
            foreach ($fields as $key => $fieldDef) {
                $merged[$section][$key] = $dbValues[$section][$key] 
                    ?? $fieldDef['default'] 
                    ?? null;
            }
        }
        
        // Also include any DB values not in schema (custom fields)
        foreach ($dbValues as $section => $fields) {
            foreach ($fields as $key => $value) {
                if (!isset($merged[$section][$key])) {
                    $merged[$section][$key] = $value;
                }
            }
        }
        
        return $merged;
    }
}

if (!function_exists('theme_set')) {
    /**
     * Set a customization value for a theme
     */
    function theme_set(string $themeSlug, string $section, string $key, mixed $value, string $type = 'text'): bool
    {
        $pdo = \core\Database::connection();
        $serialized = is_array($value) || is_object($value) ? json_encode($value) : (string)$value;
        
        $stmt = $pdo->prepare("
            INSERT INTO theme_customizations (theme_slug, section, field_key, field_value, field_type) 
            VALUES (?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE field_value = VALUES(field_value), field_type = VALUES(field_type), updated_at = NOW()
        ");
        $result = $stmt->execute([$themeSlug, $section, $key, $serialized, $type]);
        
        if ($result) {
            \Cache::clear('theme_custom_' . $themeSlug);
            global $_theme_cache_dirty; $_theme_cache_dirty = true;
        }
        
        return $result;
    }
}

if (!function_exists('theme_set_bulk')) {
    /**
     * Set multiple customization values at once
     * 
     * @param string $themeSlug
     * @param array $data ['section' => ['key' => 'value', ...], ...]
     * @return int Number of fields saved
     */
    function theme_set_bulk(string $themeSlug, array $data): int
    {
        $pdo = \core\Database::connection();
        $count = 0;
        
        $stmt = $pdo->prepare("
            INSERT INTO theme_customizations (theme_slug, section, field_key, field_value, field_type) 
            VALUES (?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE field_value = VALUES(field_value), field_type = VALUES(field_type), updated_at = NOW()
        ");
        
        foreach ($data as $section => $fields) {
            if (!is_array($fields)) continue;
            foreach ($fields as $key => $value) {
                $type = 'text';
                if (is_bool($value)) {
                    $type = 'toggle';
                    $value = $value ? '1' : '0';
                } elseif (is_array($value)) {
                    $type = 'json';
                    $value = json_encode($value);
                } elseif (preg_match('/^#[0-9a-fA-F]{3,8}$/', (string)$value)) {
                    $type = 'color';
                } elseif (str_starts_with((string)$value, '/uploads/') || str_starts_with((string)$value, 'http')) {
                    $type = 'image';
                }
                
                if ($stmt->execute([$themeSlug, $section, $key, (string)$value, $type])) {
                    $count++;
                }
            }
        }
        
        \Cache::clear('theme_custom_' . $themeSlug);
        global $_theme_cache_dirty; $_theme_cache_dirty = true;
        return $count;
    }
}

if (!function_exists('theme_reset')) {
    /**
     * Reset all customizations for a theme (or a specific section)
     */
    function theme_reset(string $themeSlug, ?string $section = null): bool
    {
        $pdo = \core\Database::connection();
        
        if ($section) {
            $stmt = $pdo->prepare("DELETE FROM theme_customizations WHERE theme_slug = ? AND section = ?");
            $result = $stmt->execute([$themeSlug, $section]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM theme_customizations WHERE theme_slug = ?");
            $result = $stmt->execute([$themeSlug]);
        }
        
        \Cache::clear('theme_custom_' . $themeSlug);
        global $_theme_cache_dirty; $_theme_cache_dirty = true;
        return $result;
    }
}

if (!function_exists('theme_save_snapshot')) {
    /**
     * Save current customizations as a history snapshot
     */
    function theme_save_snapshot(string $themeSlug, ?string $label = null): int
    {
        $pdo = \core\Database::connection();
        $values = _theme_load_customizations($themeSlug);
        
        $stmt = $pdo->prepare("INSERT INTO theme_customization_history (theme_slug, snapshot, label) VALUES (?, ?, ?)");
        $stmt->execute([$themeSlug, json_encode($values, JSON_UNESCAPED_UNICODE), $label]);
        
        // Keep max 50 snapshots per theme
        $stmt = $pdo->prepare("
            DELETE FROM theme_customization_history 
            WHERE theme_slug = ? AND id NOT IN (
                SELECT id FROM (
                    SELECT id FROM theme_customization_history 
                    WHERE theme_slug = ? ORDER BY created_at DESC LIMIT 50
                ) AS recent
            )
        ");
        $stmt->execute([$themeSlug, $themeSlug]);
        
        return (int)$pdo->lastInsertId();
    }
}

if (!function_exists('theme_get_history')) {
    /**
     * Get customization history for a theme
     */
    function theme_get_history(string $themeSlug, int $limit = 20): array
    {
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("
            SELECT id, label, created_at 
            FROM theme_customization_history 
            WHERE theme_slug = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$themeSlug, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

if (!function_exists('theme_restore_snapshot')) {
    /**
     * Restore customizations from a history snapshot
     */
    function theme_restore_snapshot(string $themeSlug, int $historyId): bool
    {
        $pdo = \core\Database::connection();
        
        $stmt = $pdo->prepare("SELECT snapshot FROM theme_customization_history WHERE id = ? AND theme_slug = ?");
        $stmt->execute([$historyId, $themeSlug]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$row) return false;
        
        $data = json_decode($row['snapshot'], true);
        if (!is_array($data)) return false;
        
        // Save current state as snapshot before restoring
        theme_save_snapshot($themeSlug, 'Auto-save before restore');
        
        // Clear and restore
        $pdo->prepare("DELETE FROM theme_customizations WHERE theme_slug = ?")->execute([$themeSlug]);
        theme_set_bulk($themeSlug, $data);
        
        return true;
    }
}

if (!function_exists('theme_get_schema')) {
    /**
     * Get the customizable schema for a theme.
     * If theme.json doesn't have 'customizable', generate a default schema from colors/fonts.
     */
    function theme_get_schema(?string $themeSlug = null): array
    {
        $themeSlug = $themeSlug ?? get_active_theme();
        $config = get_theme_config($themeSlug);
        
        // If theme has explicit customizable schema, use it
        if (!empty($config['customizable'])) {
            return $config['customizable'];
        }
        
        // Generate default schema from existing theme.json structure
        return _theme_generate_default_schema($config, $themeSlug);
    }
}

if (!function_exists('generate_studio_css_overrides')) {
    /**
     * Generate CSS overrides from theme customizations
     * Called in layout.php AFTER the main theme stylesheet
     */
    function generate_studio_css_overrides(): string
    {
        $themeSlug = get_active_theme();
        $customs = _theme_load_customizations($themeSlug);
        
        if (empty($customs)) return '';
        
        $css = ":root {\n";
        $hasVars = false;
        
        // Map brand colors to CSS variables
        $colorMap = [
            'brand.primary_color' => '--primary',
            'brand.secondary_color' => '--secondary',
            'brand.accent_color' => '--accent',
            'brand.dark_color' => '--surface',
            'brand.bg_color' => '--background',
            'brand.text_color' => '--text',
        ];
        
        foreach ($colorMap as $path => $var) {
            [$section, $key] = explode('.', $path);
            if (!empty($customs[$section][$key])) {
                $css .= "    {$var}: {$customs[$section][$key]};\n";
                // Also set legacy aliases
                $css .= "    --color" . substr($var, 1) . ": {$customs[$section][$key]};\n";
                $hasVars = true;
            }
        }
        
        // Custom font overrides (legacy brand section)
        if (!empty($customs['brand']['heading_font'])) {
            $css .= "    --font-heading: '{$customs['brand']['heading_font']}', sans-serif;\n";
            $hasVars = true;
        }
        if (!empty($customs['brand']['body_font'])) {
            $css .= "    --font-family: '{$customs['brand']['body_font']}', sans-serif;\n";
            $hasVars = true;
        }
        
        // Typography section overrides
        if (!empty($customs['typography']['heading_font'])) {
            $css .= "    --font-heading: '{$customs['typography']['heading_font']}', sans-serif;\n";
            $hasVars = true;
        }
        if (!empty($customs['typography']['body_font'])) {
            $css .= "    --font-family: '{$customs['typography']['body_font']}', sans-serif;\n";
            $hasVars = true;
        }
        if (!empty($customs['typography']['base_font_size'])) {
            $css .= "    --font-size-base: {$customs['typography']['base_font_size']};\n";
            $hasVars = true;
        }
        if (!empty($customs['typography']['line_height'])) {
            $css .= "    --line-height: {$customs['typography']['line_height']};\n";
            $hasVars = true;
        }
        if (!empty($customs['typography']['heading_weight'])) {
            $css .= "    --font-weight-heading: {$customs['typography']['heading_weight']};\n";
            $hasVars = true;
        }
        
        // Buttons section overrides
        if (!empty($customs['buttons']['border_radius'])) {
            $css .= "    --btn-radius: {$customs['buttons']['border_radius']};\n";
            $hasVars = true;
        }
        if (!empty($customs['buttons']['padding_x'])) {
            $css .= "    --btn-padding-x: {$customs['buttons']['padding_x']};\n";
            $hasVars = true;
        }
        if (!empty($customs['buttons']['padding_y'])) {
            $css .= "    --btn-padding-y: {$customs['buttons']['padding_y']};\n";
            $hasVars = true;
        }
        if (!empty($customs['buttons']['font_weight'])) {
            $css .= "    --btn-font-weight: {$customs['buttons']['font_weight']};\n";
            $hasVars = true;
        }
        
        // Layout section overrides
        if (!empty($customs['layout']['container_width'])) {
            $css .= "    --container-width: {$customs['layout']['container_width']};\n";
            $hasVars = true;
        }
        if (!empty($customs['layout']['section_spacing'])) {
            $css .= "    --section-spacing: {$customs['layout']['section_spacing']};\n";
            $hasVars = true;
        }
        if (!empty($customs['layout']['border_radius'])) {
            $css .= "    --border-radius: {$customs['layout']['border_radius']};\n";
            $hasVars = true;
        }
        
        // Effects section overrides
        if (isset($customs['effects']['shadow_strength']) && $customs['effects']['shadow_strength'] !== '') {
            $strength = (float)$customs['effects']['shadow_strength'];
            $opacity = round($strength / 100, 2);
            $css .= "    --shadow: 0 1px 3px rgba(0,0,0,{$opacity});\n";
            $css .= "    --shadow-lg: 0 10px 40px rgba(0,0,0,{$opacity});\n";
            $hasVars = true;
        }
        if (!empty($customs['effects']['hover_scale'])) {
            $css .= "    --hover-scale: {$customs['effects']['hover_scale']};\n";
            $hasVars = true;
        }
        if (!empty($customs['effects']['transition_speed'])) {
            $css .= "    --transition-speed: {$customs['effects']['transition_speed']};\n";
            $hasVars = true;
        }
        
        $css .= "}\n";
        
        // Custom CSS (appended after :root block)
        if (!empty($customs['custom_css']['css_code'])) {
            $css .= "\n/* Custom CSS */\n" . $customs['custom_css']['css_code'] . "\n";
            $hasVars = true;
        }
        
        return $hasVars ? $css : '';
    }
}

if (!function_exists('_theme_load_customizations')) {
    /**
     * Load all customizations from DB for a theme (with caching)
     */
    function _theme_load_customizations(string $themeSlug): array
    {
        $cacheKey = 'theme_custom_' . $themeSlug;
        $cached = \Cache::get($cacheKey);
        if (is_array($cached)) return $cached;
        
        try {
            $pdo = \core\Database::connection();
            $stmt = $pdo->prepare("SELECT section, field_key, field_value, field_type FROM theme_customizations WHERE theme_slug = ?");
            $stmt->execute([$themeSlug]);
            
            $result = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $value = $row['field_value'];
                
                // Auto-decode based on type
                switch ($row['field_type']) {
                    case 'json':
                    case 'repeatable':
                        $decoded = json_decode($value, true);
                        if ($decoded !== null) $value = $decoded;
                        break;
                    case 'toggle':
                        $value = in_array($value, ['1', 'true', 'yes'], true);
                        break;
                    case 'number':
                        $value = is_numeric($value) ? (float)$value : $value;
                        break;
                }
                
                $result[$row['section']][$row['field_key']] = $value;
            }
            
            \Cache::set($cacheKey, $result, 300); // 5 min
            return $result;
        } catch (\Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('_theme_generate_default_schema')) {
    /**
     * Auto-generate customizable schema from theme.json config
     */
    function _theme_generate_default_schema(array $config, string $themeSlug): array
    {
        $schema = [];
        
        // Brand section (always available)
        $schema['brand'] = [
            'label' => 'Brand & Colors',
            'icon' => '🎨',
            'fields' => [
                'site_name' => ['type' => 'text', 'label' => 'Site Name', 'default' => $config['name'] ?? 'My Site'],
                'tagline' => ['type' => 'text', 'label' => 'Tagline', 'default' => ''],
                'logo' => ['type' => 'image', 'label' => 'Logo', 'default' => null],
                'primary_color' => ['type' => 'color', 'label' => 'Primary Color', 'default' => $config['colors']['primary'] ?? '#3b82f6'],
                'secondary_color' => ['type' => 'color', 'label' => 'Secondary Color', 'default' => $config['colors']['secondary'] ?? '#06b6d4'],
                'accent_color' => ['type' => 'color', 'label' => 'Accent Color', 'default' => $config['colors']['accent'] ?? '#f59e0b'],
            ]
        ];
        
        // Header
        $schema['header'] = [
            'label' => 'Header',
            'icon' => '📌',
            'fields' => [
                'cta_text' => ['type' => 'text', 'label' => 'CTA Button Text', 'default' => ''],
                'cta_link' => ['type' => 'text', 'label' => 'CTA Button Link', 'default' => '/contact'],
                'show_cta' => ['type' => 'toggle', 'label' => 'Show CTA Button', 'default' => false],
            ]
        ];
        
        // Hero (for themes with a hero section)
        $schema['hero'] = [
            'label' => 'Hero Section',
            'icon' => '⭐',
            'fields' => [
                'headline' => ['type' => 'text', 'label' => 'Headline', 'default' => ''],
                'subtitle' => ['type' => 'textarea', 'label' => 'Subtitle', 'default' => ''],
                'btn_text' => ['type' => 'text', 'label' => 'Button Text', 'default' => ''],
                'btn_link' => ['type' => 'text', 'label' => 'Button Link', 'default' => '/contact'],
                'bg_image' => ['type' => 'image', 'label' => 'Background Image', 'default' => null],
            ]
        ];
        
        // Footer
        $schema['footer'] = [
            'label' => 'Footer',
            'icon' => '📝',
            'fields' => [
                'description' => ['type' => 'textarea', 'label' => 'Footer Description', 'default' => ''],
                'copyright' => ['type' => 'text', 'label' => 'Copyright Text', 'default' => ''],
                'social_twitter' => ['type' => 'text', 'label' => 'Twitter/X URL', 'default' => ''],
                'social_linkedin' => ['type' => 'text', 'label' => 'LinkedIn URL', 'default' => ''],
                'social_github' => ['type' => 'text', 'label' => 'GitHub URL', 'default' => ''],
            ]
        ];
        
        // Typography
        $schema['typography'] = [
            'label' => 'Typography',
            'icon' => '🔤',
            'fields' => [
                'heading_font' => [
                    'type' => 'select',
                    'label' => 'Heading Font',
                    'default' => $config['typography']['headingFont'] ?? 'Inter',
                    'options' => [
                        'Inter' => 'Inter',
                        'Roboto' => 'Roboto',
                        'Open Sans' => 'Open Sans',
                        'Lato' => 'Lato',
                        'Montserrat' => 'Montserrat',
                        'Poppins' => 'Poppins',
                        'Raleway' => 'Raleway',
                        'Playfair Display' => 'Playfair Display',
                        'Merriweather' => 'Merriweather',
                        'Source Sans Pro' => 'Source Sans Pro',
                        'Nunito' => 'Nunito',
                        'Work Sans' => 'Work Sans',
                        'DM Sans' => 'DM Sans',
                        'Space Grotesk' => 'Space Grotesk',
                        'Outfit' => 'Outfit',
                        'Plus Jakarta Sans' => 'Plus Jakarta Sans',
                        'Sora' => 'Sora',
                        'Manrope' => 'Manrope',
                        'Unbounded' => 'Unbounded',
                        'Clash Display' => 'Clash Display',
                    ],
                ],
                'body_font' => [
                    'type' => 'select',
                    'label' => 'Body Font',
                    'default' => $config['typography']['fontFamily'] ?? 'Inter',
                    'options' => [
                        'Inter' => 'Inter',
                        'Roboto' => 'Roboto',
                        'Open Sans' => 'Open Sans',
                        'Lato' => 'Lato',
                        'Nunito' => 'Nunito',
                        'Source Sans Pro' => 'Source Sans Pro',
                        'DM Sans' => 'DM Sans',
                        'Work Sans' => 'Work Sans',
                        'IBM Plex Sans' => 'IBM Plex Sans',
                        'Noto Sans' => 'Noto Sans',
                        'Mulish' => 'Mulish',
                        'Quicksand' => 'Quicksand',
                        'Karla' => 'Karla',
                    ],
                ],
                'base_font_size' => [
                    'type' => 'text',
                    'label' => 'Base Font Size',
                    'default' => $config['typography']['fontSize'] ?? '16px',
                ],
                'line_height' => [
                    'type' => 'text',
                    'label' => 'Line Height',
                    'default' => $config['typography']['lineHeight'] ?? '1.6',
                ],
                'heading_weight' => [
                    'type' => 'select',
                    'label' => 'Heading Weight',
                    'default' => $config['typography']['headingWeight'] ?? '700',
                    'options' => [
                        '400' => '400 — Regular',
                        '500' => '500 — Medium',
                        '600' => '600 — Semi Bold',
                        '700' => '700 — Bold',
                        '800' => '800 — Extra Bold',
                        '900' => '900 — Black',
                    ],
                ],
            ]
        ];
        
        // Buttons
        $schema['buttons'] = [
            'label' => 'Buttons',
            'icon' => '🔘',
            'fields' => [
                'border_radius' => [
                    'type' => 'text',
                    'label' => 'Border Radius',
                    'default' => $config['buttons']['borderRadius'] ?? '8px',
                ],
                'padding_x' => [
                    'type' => 'text',
                    'label' => 'Horizontal Padding',
                    'default' => $config['buttons']['paddingX'] ?? '24px',
                ],
                'padding_y' => [
                    'type' => 'text',
                    'label' => 'Vertical Padding',
                    'default' => $config['buttons']['paddingY'] ?? '12px',
                ],
                'font_weight' => [
                    'type' => 'select',
                    'label' => 'Font Weight',
                    'default' => $config['buttons']['fontWeight'] ?? '600',
                    'options' => [
                        '400' => '400 — Regular',
                        '500' => '500 — Medium',
                        '600' => '600 — Semi Bold',
                        '700' => '700 — Bold',
                    ],
                ],
                'uppercase' => [
                    'type' => 'toggle',
                    'label' => 'Uppercase Text',
                    'default' => $config['buttons']['uppercase'] ?? false,
                ],
                'shadow' => [
                    'type' => 'toggle',
                    'label' => 'Button Shadow',
                    'default' => $config['buttons']['shadow'] ?? false,
                ],
            ]
        ];
        
        // Layout
        $schema['layout'] = [
            'label' => 'Layout',
            'icon' => '📐',
            'fields' => [
                'container_width' => [
                    'type' => 'text',
                    'label' => 'Container Width',
                    'default' => $config['layout']['containerWidth'] ?? '1200px',
                ],
                'section_spacing' => [
                    'type' => 'text',
                    'label' => 'Section Spacing',
                    'default' => $config['layout']['sectionSpacing'] ?? '80px',
                ],
                'border_radius' => [
                    'type' => 'text',
                    'label' => 'Border Radius',
                    'default' => $config['layout']['borderRadius'] ?? '12px',
                ],
            ]
        ];
        
        // Effects
        $schema['effects'] = [
            'label' => 'Effects',
            'icon' => '✨',
            'fields' => [
                'shadow_strength' => [
                    'type' => 'text',
                    'label' => 'Shadow Strength (0-100)',
                    'default' => $config['effects']['shadowStrength'] ?? '20',
                ],
                'hover_scale' => [
                    'type' => 'text',
                    'label' => 'Hover Scale',
                    'default' => $config['effects']['hoverScale'] ?? '1.02',
                ],
                'transition_speed' => [
                    'type' => 'text',
                    'label' => 'Transition Speed (ms)',
                    'default' => $config['effects']['transitionSpeed'] ?? '200ms',
                ],
            ]
        ];
        
        // Custom CSS
        $schema['custom_css'] = [
            'label' => 'Custom CSS',
            'icon' => '💻',
            'fields' => [
                'css_code' => [
                    'type' => 'textarea',
                    'label' => 'CSS Code',
                    'default' => $config['customCSS'] ?? '',
                ],
            ]
        ];
        
        // Theme Info
        $schema['theme_info'] = [
            'label' => 'Theme Info',
            'icon' => 'ℹ️',
            'fields' => [
                'name' => [
                    'type' => 'text',
                    'label' => 'Theme Name',
                    'default' => $config['name'] ?? '',
                ],
                'description' => [
                    'type' => 'textarea',
                    'label' => 'Description',
                    'default' => $config['description'] ?? '',
                ],
                'version' => [
                    'type' => 'text',
                    'label' => 'Version',
                    'default' => $config['version'] ?? '1.0.0',
                ],
                'author' => [
                    'type' => 'text',
                    'label' => 'Author',
                    'default' => $config['author'] ?? '',
                ],
            ]
        ];
        
        return $schema;
    }
}
