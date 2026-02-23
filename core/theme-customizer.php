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
        
        // Merge with schema defaults (use full schema including auto-discovered)
        $schema = theme_get_schema($themeSlug);
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
            $schema = $config['customizable'];
            // Still merge auto-discovered data-ts fields
            $additional = _theme_discover_data_attributes($themeSlug, $schema);
            foreach ($additional as $section => $sectionDef) {
                if (isset($schema[$section]) && !empty($sectionDef['fields'])) {
                    foreach ($sectionDef['fields'] as $key => $fieldDef) {
                        if (!isset($schema[$section]['fields'][$key])) {
                            $schema[$section]['fields'][$key] = $fieldDef;
                        }
                    }
                } elseif (!isset($schema[$section])) {
                    $schema[$section] = $sectionDef;
                }
            }
            // Merge fields from DB values that are missing from schema
            // (handles expanded dynamic patterns like item1_title, item2_title...)
            $schema = _theme_merge_db_fields_into_schema($schema, $themeSlug);
            return $schema;
        }
        
        // Generate default schema from existing theme.json structure
        $schema = _theme_generate_default_schema($config, $themeSlug);
        $schema = _theme_merge_db_fields_into_schema($schema, $themeSlug);
        return $schema;
    }
}

if (!function_exists('_theme_merge_db_fields_into_schema')) {
    /**
     * Auto-add fields from DB (theme_customizations) that exist in values but not in schema.
     * This handles dynamic patterns like item1_title, item2_title that PHP templates
     * generate at runtime but aren't visible in source-level parsing.
     */
    function _theme_merge_db_fields_into_schema(array $schema, string $themeSlug): array
    {
        // Use raw DB values directly — NOT theme_get_all() which calls theme_get_schema() (recursion!)
        $allValues = _theme_load_customizations($themeSlug);
        
        foreach ($allValues as $section => $fields) {
            if ($section === '_ve_styles') continue; // skip VE overrides
            if (!is_array($fields)) continue;
            
            // Ensure section exists in schema
            if (!isset($schema[$section])) {
                $schema[$section] = [
                    'label' => ucfirst(str_replace('_', ' ', $section)),
                    'fields' => [],
                ];
            }
            
            foreach ($fields as $fieldKey => $fieldValue) {
                if (isset($schema[$section]['fields'][$fieldKey])) continue;
                
                // Auto-detect type from key name and value
                $type = 'text';
                if (str_contains($fieldKey, 'image') || str_contains($fieldKey, 'avatar') || str_contains($fieldKey, 'bg_')) {
                    $type = 'image';
                } elseif (str_contains($fieldKey, 'description') || str_contains($fieldKey, 'quote') ||
                          str_contains($fieldKey, 'text') || str_contains($fieldKey, 'bio')) {
                    $type = 'textarea';
                } elseif (str_contains($fieldKey, 'color')) {
                    $type = 'color';
                } elseif (str_contains($fieldKey, 'enabled') || str_contains($fieldKey, 'show_')) {
                    $type = 'toggle';
                }
                
                $schema[$section]['fields'][$fieldKey] = [
                    'type' => $type,
                    'label' => _theme_humanize_label($fieldKey),
                    'default' => '',
                ];
            }
        }
        
        return $schema;
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
        
        // Blog theme uses --blog-primary etc., so map those too
        $blogMap = [
            '--primary' => '--blog-primary',
            '--secondary' => '--blog-primary-light',
            '--accent' => '--blog-accent',
            '--surface' => '--blog-surface',
            '--background' => '--blog-bg',
            '--text' => '--blog-text',
        ];
        
        // Also set --blog-* for surface/bg/text
        
        foreach ($colorMap as $path => $var) {
            [$section, $key] = explode('.', $path);
            if (!empty($customs[$section][$key])) {
                $css .= "    {$var}: {$customs[$section][$key]};\n";
                // Also set legacy aliases (--primary → --color-primary)
                $css .= "    --color" . substr($var, 1) . ": {$customs[$section][$key]};\n";
                // SaaS uses --color-bg (short form of --color-background)
                if ($var === '--background') {
                    $css .= "    --color-bg: {$customs[$section][$key]};\n";
                }
                // Also set theme-specific aliases (blog etc.)
                if (isset($blogMap[$var])) {
                    $css .= "    {$blogMap[$var]}: {$customs[$section][$key]};\n";
                }
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
        
        // Buttons section overrides (add px unit if missing)
        if (!empty($customs['buttons']['border_radius'])) {
            $v = $customs['buttons']['border_radius'];
            $css .= "    --btn-radius: " . (is_numeric($v) ? "{$v}px" : $v) . ";\n";
            $hasVars = true;
        }
        if (!empty($customs['buttons']['padding_x'])) {
            $v = $customs['buttons']['padding_x'];
            $css .= "    --btn-padding-x: " . (is_numeric($v) ? "{$v}px" : $v) . ";\n";
            $hasVars = true;
        }
        if (!empty($customs['buttons']['padding_y'])) {
            $v = $customs['buttons']['padding_y'];
            $css .= "    --btn-padding-y: " . (is_numeric($v) ? "{$v}px" : $v) . ";\n";
            $hasVars = true;
        }
        if (!empty($customs['buttons']['font_weight'])) {
            $css .= "    --btn-font-weight: {$customs['buttons']['font_weight']};\n";
            $hasVars = true;
        }
        
        // Layout section overrides (add px unit if missing)
        if (!empty($customs['layout']['container_width'])) {
            $v = $customs['layout']['container_width'];
            $css .= "    --container-width: " . (is_numeric($v) ? "{$v}px" : $v) . ";\n";
            $hasVars = true;
        }
        if (!empty($customs['layout']['section_spacing'])) {
            $v = $customs['layout']['section_spacing'];
            $css .= "    --section-spacing: " . (is_numeric($v) ? "{$v}px" : $v) . ";\n";
            $hasVars = true;
        }
        if (!empty($customs['layout']['border_radius'])) {
            $v = $customs['layout']['border_radius'];
            $css .= "    --border-radius: " . (is_numeric($v) ? "{$v}px" : $v) . ";\n";
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
        if (!empty($customs['effects']['gradient'])) {
            $css .= "    --gradient: {$customs['effects']['gradient']};\n";
            $hasVars = true;
        }
        if (!empty($customs['effects']['box_shadow'])) {
            $css .= "    --shadow-custom: {$customs['effects']['box_shadow']};\n";
            $hasVars = true;
        }
        
        // Spacing / Box Model
        if (!empty($customs['layout']['section_padding'])) {
            $sp = json_decode($customs['layout']['section_padding'], true);
            if (is_array($sp)) {
                $css .= "    --section-margin-top: " . (int)($sp['mt'] ?? 0) . "px;\n";
                $css .= "    --section-margin-right: " . (int)($sp['mr'] ?? 0) . "px;\n";
                $css .= "    --section-margin-bottom: " . (int)($sp['mb'] ?? 0) . "px;\n";
                $css .= "    --section-margin-left: " . (int)($sp['ml'] ?? 0) . "px;\n";
                $css .= "    --section-padding-top: " . (int)($sp['pt'] ?? 20) . "px;\n";
                $css .= "    --section-padding-right: " . (int)($sp['pr'] ?? 20) . "px;\n";
                $css .= "    --section-padding-bottom: " . (int)($sp['pb'] ?? 20) . "px;\n";
                $css .= "    --section-padding-left: " . (int)($sp['pl'] ?? 20) . "px;\n";
                $hasVars = true;
            }
        }
        
        $css .= "}\n";
        
        // Visual Editor per-element CSS overrides
        if (!empty($customs['_ve_styles'])) {
            $veCss = "\n/* Visual Editor Overrides */\n";
            $hasVeStyles = false;
            foreach ($customs['_ve_styles'] as $dataTs => $jsonVal) {
                $props = is_string($jsonVal) ? json_decode($jsonVal, true) : (is_array($jsonVal) ? $jsonVal : null);
                if (!$props || !is_array($props)) continue;
                $rules = '';
                foreach ($props as $prop => $val) {
                    $safeProp = preg_replace('/[^a-z\-]/', '', strtolower($prop));
                    // Sanitize value: allow quotes/commas for font-family, strip dangerous chars
                    $safeVal = preg_replace('/[{}<>]/', '', $val);
                    if ($safeProp && $safeVal !== '') {
                        $rules .= "    {$safeProp}: {$safeVal};\n";
                    }
                }
                if ($rules) {
                    $safeTs = htmlspecialchars($dataTs, ENT_QUOTES, 'UTF-8');
                    $veCss .= "[data-ts=\"{$safeTs}\"] {\n{$rules}}\n";
                    $hasVeStyles = true;
                }
            }
            if ($hasVeStyles) {
                $css .= $veCss;
                $hasVars = true;
            }
        }

        // Custom CSS (appended after :root block — last so it can override everything)
        if (!empty($customs['custom_css']['css_code'])) {
            $css .= "\n/* Custom CSS */\n" . $customs['custom_css']['css_code'] . "\n";
            $hasVars = true;
        }
        
        return $hasVars ? $css : '';
    }
}

if (!function_exists('ve_google_fonts_link')) {
    /**
     * Returns a <link> tag for Google Fonts used in Visual Editor overrides.
     * Call in <head> before generate_studio_css_overrides().
     */
    function ve_google_fonts_link(): string
    {
        $customs = _theme_load_customizations();
        if (empty($customs['_ve_styles'])) return '';
        $systemFonts = ['system-ui','-apple-system','sans-serif','serif','monospace','inherit','initial','georgia','courier new','arial','verdana','helvetica','times new roman'];
        $gFonts = [];
        foreach ($customs['_ve_styles'] as $_ => $jv) {
            $p = is_string($jv) ? json_decode($jv, true) : (is_array($jv) ? $jv : null);
            if (!$p || empty($p['font-family'])) continue;
            $family = preg_replace("/[\"']/", '', explode(',', $p['font-family'])[0]);
            $family = trim($family);
            if ($family && !in_array(strtolower($family), $systemFonts)) {
                $gFonts[$family] = true;
            }
        }
        if (!$gFonts) return '';
        $parts = [];
        foreach (array_keys($gFonts) as $f) {
            $parts[] = 'family=' . urlencode($f) . ':wght@300;400;500;600;700';
        }
        return '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?' . implode('&amp;', $parts) . '&amp;display=swap">';
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
                'bg_color' => ['type' => 'color', 'label' => 'Background Color', 'default' => $config['colors']['background'] ?? '#ffffff'],
                'text_color' => ['type' => 'color', 'label' => 'Text Color', 'default' => $config['colors']['text'] ?? '#1e293b'],
                'dark_color' => ['type' => 'color', 'label' => 'Surface Color', 'default' => $config['colors']['surface'] ?? '#f8fafc'],
                'favicon' => ['type' => 'image', 'label' => 'Favicon', 'default' => null],
                'og_image' => ['type' => 'image', 'label' => 'Social Share Image (OG)', 'default' => null],
            ]
        ];
        
        // Announcement Bar
        $schema['announcement'] = [
            'label' => 'Announcement Bar',
            'icon' => '📢',
            'fields' => [
                'enabled' => ['type' => 'toggle', 'label' => 'Show Announcement Bar', 'default' => false],
                'text' => ['type' => 'text', 'label' => 'Announcement Text', 'default' => ''],
                'link' => ['type' => 'text', 'label' => 'Link URL', 'default' => ''],
                'bg_color' => ['type' => 'color', 'label' => 'Background Color', 'default' => '#6366f1'],
                'text_color' => ['type' => 'color', 'label' => 'Text Color', 'default' => '#ffffff'],
            ]
        ];
        
        // Header
        $schema['header'] = [
            'label' => 'Header',
            'icon' => '📌',
            'fields' => [
                'cta_text' => ['type' => 'text', 'label' => 'CTA Button Text', 'default' => ''],
                'cta_link' => ['type' => 'text', 'label' => 'CTA Button Link', 'default' => '/contact'],
                'show_cta' => ['type' => 'toggle', 'label' => 'Show CTA Button', 'default' => true],
                'phone' => ['type' => 'text', 'label' => 'Phone Number', 'default' => ''],
                'email' => ['type' => 'text', 'label' => 'Email Address', 'default' => ''],
            ]
        ];
        
        // Hero (for themes with a hero section)
        $schema['hero'] = [
            'label' => 'Hero Section',
            'icon' => '⭐',
            'fields' => [
                'headline' => ['type' => 'text', 'label' => 'Headline', 'default' => ''],
                'tagline' => ['type' => 'text', 'label' => 'Tagline', 'default' => ''],
                'badge' => ['type' => 'text', 'label' => 'Badge', 'default' => ''],
                'label' => ['type' => 'text', 'label' => 'Label', 'default' => ''],
                'subtitle' => ['type' => 'textarea', 'label' => 'Subtitle', 'default' => ''],
                'btn_text' => ['type' => 'text', 'label' => 'Button Text', 'default' => ''],
                'btn_link' => ['type' => 'text', 'label' => 'Button Link', 'default' => '/contact'],
                'btn2_text' => ['type' => 'text', 'label' => 'Button 2 Text', 'default' => ''],
                'btn2_link' => ['type' => 'text', 'label' => 'Button 2 Link', 'default' => ''],
                'bg_image' => ['type' => 'image', 'label' => 'Background Image', 'default' => null],
            ]
        ];

        // Content sections (used by Sections tab)
        $schema['cta'] = [
            'label' => 'Call to Action',
            'icon' => '📣',
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => ''],
                'description' => ['type' => 'textarea', 'label' => 'Description', 'default' => ''],
            ]
        ];
        $schema['articles'] = [
            'label' => 'Articles',
            'icon' => '📰',
            'fields' => [
                'label' => ['type' => 'text', 'label' => 'Section Label', 'default' => ''],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => ''],
                'description' => ['type' => 'textarea', 'label' => 'Description', 'default' => ''],
                'btn_text' => ['type' => 'text', 'label' => 'Button Text', 'default' => ''],
                'btn_link' => ['type' => 'text', 'label' => 'Button Link', 'default' => '/articles'],
            ]
        ];
        $schema['pages'] = [
            'label' => 'Pages Showcase',
            'icon' => '📄',
            'fields' => [
                'label' => ['type' => 'text', 'label' => 'Section Label', 'default' => ''],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => ''],
                'description' => ['type' => 'textarea', 'label' => 'Description', 'default' => ''],
            ]
        ];
        $schema['tools'] = [
            'label' => 'Tools',
            'icon' => '🔧',
            'fields' => [
                'label' => ['type' => 'text', 'label' => 'Section Label', 'default' => ''],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => ''],
                'description' => ['type' => 'textarea', 'label' => 'Description', 'default' => ''],
            ]
        ];
        $schema['about'] = [
            'label' => 'About',
            'icon' => '👋',
            'fields' => [
                'label' => ['type' => 'text', 'label' => 'Section Label', 'default' => ''],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => ''],
                'description' => ['type' => 'textarea', 'label' => 'Description', 'default' => ''],
                'image' => ['type' => 'image', 'label' => 'Image', 'default' => null],
            ]
        ];
        $schema['parallax'] = [
            'label' => 'Parallax Quote',
            'icon' => '💬',
            'fields' => [
                'quote' => ['type' => 'textarea', 'label' => 'Quote Text', 'default' => ''],
                'citation' => ['type' => 'text', 'label' => 'Citation', 'default' => ''],
                'bg_image' => ['type' => 'image', 'label' => 'Background Image', 'default' => null],
            ]
        ];
        $schema['services'] = [
            'label' => 'Services',
            'icon' => '💼',
            'fields' => [
                'label' => ['type' => 'text', 'label' => 'Section Label', 'default' => ''],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => ''],
                'description' => ['type' => 'textarea', 'label' => 'Description', 'default' => ''],
            ]
        ];
        $schema['skills'] = [
            'label' => 'Skills',
            'icon' => '🎯',
            'fields' => [
                'label' => ['type' => 'text', 'label' => 'Section Label', 'default' => ''],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => ''],
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
                'section_padding' => [
                    'type' => 'spacing',
                    'label' => 'Section Spacing (Box Model)',
                    'default' => '',
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
                'gradient' => [
                    'type' => 'gradient',
                    'label' => 'Background Gradient',
                    'default' => '',
                ],
                'box_shadow' => [
                    'type' => 'boxshadow',
                    'label' => 'Box Shadow',
                    'default' => '',
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
        
        // Auto-discover additional sections from data-ts attributes in templates
        $additional = _theme_discover_data_attributes($themeSlug, $schema);
        foreach ($additional as $section => $sectionDef) {
            if (isset($schema[$section])) {
                // Merge new fields into existing section
                if (!empty($sectionDef['fields'])) {
                    foreach ($sectionDef['fields'] as $key => $fieldDef) {
                        if (!isset($schema[$section]['fields'][$key])) {
                            $schema[$section]['fields'][$key] = $fieldDef;
                        }
                    }
                }
            } else {
                // Insert new section before custom_css and theme_info
                $schema[$section] = $sectionDef;
            }
        }

        return $schema;
    }
}

if (!function_exists('_theme_discover_data_attributes')) {
    /**
     * Scan theme template files for data-ts attributes.
     * Returns discovered sections and fields not already in the schema.
     *
     * Scans: layout.php + templates/*.php
     * Attributes: data-ts="section.field", data-ts-bg="section.field", data-ts-href="section.field"
     *
     * @param string $themeSlug
     * @param array $existingSchema Sections already defined (won't override)
     * @return array Additional schema sections to merge
     */
    function _theme_discover_data_attributes(string $themeSlug, array $existingSchema = []): array
    {
        $themeDir = \CMS_ROOT . '/themes/' . $themeSlug;
        if (!is_dir($themeDir)) return [];

        // Collect all template files (layout + templates + sections)
        $files = [];
        $layoutFile = $themeDir . '/layout.php';
        if (file_exists($layoutFile)) {
            $files[] = $layoutFile;
        }
        foreach (['templates', 'sections'] as $subdir) {
            $dir = $themeDir . '/' . $subdir;
            if (is_dir($dir)) {
                foreach (glob($dir . '/*.php') as $f) {
                    $files[] = $f;
                }
            }
        }

        if (empty($files)) return [];

        // Parse all data-ts, data-ts-bg, data-ts-href attributes
        $discovered = []; // section => [field => ['type' => ..., 'source' => ...]]
        $pattern = '/data-ts(?:-(bg|href))?\s*=\s*"([^"]+)"/';

        foreach ($files as $filePath) {
            $content = file_get_contents($filePath);
            if ($content === false) continue;

            if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $variant = $m[1] ?? ''; // '' for data-ts, 'bg' for data-ts-bg, 'href' for data-ts-href
                    $path = $m[2];          // e.g. "about.label"

                    // Skip dynamic PHP expressions (e.g. features.item{$idx+1}_title)
                    if (str_contains($path, '<' . '?') || str_contains($path, '{$')) continue;

                    $parts = explode('.', $path, 2);
                    if (count($parts) < 2) continue;

                    [$section, $field] = $parts;

                    // Determine field type from variant and field name
                    $type = 'text';
                    if ($variant === 'bg') {
                        $type = 'image';
                    } elseif ($variant === 'href') {
                        $type = 'text'; // URL as text input
                    } elseif (
                        str_contains($field, 'description') || 
                        str_contains($field, 'quote') || 
                        str_contains($field, 'tagline') || 
                        str_contains($field, 'bio') || 
                        $field === 'text' || $field === 'content'
                    ) {
                        $type = 'textarea';
                    }

                    if (!isset($discovered[$section])) {
                        $discovered[$section] = [];
                    }
                    // Don't override if already discovered with a different type
                    if (!isset($discovered[$section][$field])) {
                        $discovered[$section][$field] = ['type' => $type];
                    }
                }
            }
        }

        // Build additional schema sections (skip those already in existingSchema)
        $additional = [];
        $sectionIcons = [
            'about' => '👤', 'pages' => '📄', 'articles' => '📰', 'parallax' => '🖼️',
            'features' => '⭐', 'services' => '🔧', 'team' => '👥', 'testimonials' => '💬',
            'pricing' => '💰', 'contact' => '📧', 'gallery' => '🎨', 'cta' => '📣',
            'stats' => '📊', 'faq' => '❓', 'newsletter' => '✉️',
        ];

        foreach ($discovered as $section => $fields) {
            // Skip sections that exist in the default schema
            if (isset($existingSchema[$section])) {
                // But add any NEW fields to existing section
                foreach ($fields as $fieldKey => $fieldDef) {
                    if (!isset($existingSchema[$section]['fields'][$fieldKey])) {
                        $additional[$section]['fields'][$fieldKey] = [
                            'type' => $fieldDef['type'],
                            'label' => _theme_humanize_label($fieldKey),
                            'default' => '',
                        ];
                    }
                }
                continue;
            }

            // New section
            $sectionFields = [];
            foreach ($fields as $fieldKey => $fieldDef) {
                $sectionFields[$fieldKey] = [
                    'type' => $fieldDef['type'],
                    'label' => _theme_humanize_label($fieldKey),
                    'default' => '',
                ];
            }

            $additional[$section] = [
                'label' => _theme_humanize_label($section),
                'icon' => $sectionIcons[$section] ?? '📋',
                'fields' => $sectionFields,
            ];
        }

        return $additional;
    }
}

if (!function_exists('_theme_humanize_label')) {
    /**
     * Convert field/section name to human-readable label.
     * "bg_image" → "Background Image", "btn_text" → "Button Text"
     */
    function _theme_humanize_label(string $name): string
    {
        $replacements = [
            'bg_' => 'Background ',
            'btn_' => 'Button ',
            'cta_' => 'CTA ',
        ];
        $label = $name;
        foreach ($replacements as $prefix => $replacement) {
            if (str_starts_with($label, $prefix)) {
                $label = $replacement . substr($label, strlen($prefix));
                break;
            }
        }
        return ucwords(str_replace('_', ' ', $label));
    }
}

if (!function_exists('theme_studio_preview_script')) {
    /**
     * Generate JS tag for Theme Studio live preview.
     * Injected into layout when THEME_STUDIO_PREVIEW is defined.
     * Loads external JS that listens for postMessage from parent
     * (Theme Studio sidebar) and applies CSS/DOM updates in real-time.
     */
    function theme_studio_preview_script(): string
    {
        return '<script src="/assets/js/theme-studio-preview.js"></script>';
    }
}

if (!function_exists('theme_render_favicon')) {
    /**
     * Render favicon link tag if set in Theme Studio.
     */
    function theme_render_favicon(): string
    {
        $favicon = theme_get('brand.favicon');
        if (!$favicon) return '';
        $type = 'image/x-icon';
        if (str_ends_with($favicon, '.png')) $type = 'image/png';
        elseif (str_ends_with($favicon, '.svg')) $type = 'image/svg+xml';
        return '<link rel="icon" type="' . $type . '" href="' . htmlspecialchars($favicon) . '">' . "\n";
    }
}

if (!function_exists('theme_render_announcement_bar')) {
    /**
     * Render announcement bar HTML if enabled in Theme Studio.
     */
    function theme_render_announcement_bar(): string
    {
        $enabled = theme_get('announcement.enabled');
        if (!$enabled || $enabled === '0' || $enabled === 'false') return '';
        $text = theme_get('announcement.text');
        if (empty($text)) return '';
        $link = theme_get('announcement.link');
        $bg = theme_get('announcement.bg_color') ?: '#6366f1';
        $color = theme_get('announcement.text_color') ?: '#ffffff';
        $html = '<div class="ts-announcement-bar" style="background:' . htmlspecialchars($bg) . ';color:' . htmlspecialchars($color) . ';text-align:center;padding:10px 20px;font-size:14px;font-weight:500;position:relative;z-index:9998">';
        if ($link) {
            $html .= '<a href="' . htmlspecialchars($link) . '" style="color:inherit;text-decoration:underline">';
        }
        $html .= htmlspecialchars($text);
        if ($link) $html .= '</a>';
        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('theme_render_og_image')) {
    /**
     * Render OG image meta tag if set in Theme Studio.
     */
    function theme_render_og_image(): string
    {
        $ogImage = theme_get('brand.og_image');
        if (!$ogImage) return '';
        return '<meta property="og:image" content="' . htmlspecialchars($ogImage) . '">' . "\n";
    }
}

if (!function_exists('theme_get_section_order')) {
    /**
     * Returns ordered section IDs for the active theme homepage.
     * Falls back to theme.json homepage_sections order if no custom order saved.
     *
     * @param string|null $themeSlug  Theme slug (defaults to active theme)
     * @return array  Ordered list of section IDs
     */
    function theme_get_section_order(?string $themeSlug = null): array
    {
        $themeSlug = $themeSlug ?? get_active_theme();
        $customs = _theme_load_customizations($themeSlug);

        // Check DB for custom order (stored as JSON in sections.order)
        if (!empty($customs['sections']['order'])) {
            $order = $customs['sections']['order'];
            if (is_string($order)) {
                $order = json_decode($order, true);
            }
            if (is_array($order) && !empty($order)) {
                return $order;
            }
        }

        // Fall back to theme.json homepage_sections default order
        $config = get_theme_config($themeSlug);
        $sections = $config['homepage_sections'] ?? [];
        return array_column($sections, 'id');
    }
}

if (!function_exists('theme_section_enabled')) {
    /**
     * Returns true if a homepage section is enabled.
     * Required sections are always enabled.
     * Default: enabled unless explicitly disabled in DB.
     *
     * @param string      $sectionId  Section identifier (e.g. 'hero', 'about')
     * @param string|null $themeSlug  Theme slug (defaults to active theme)
     * @return bool
     */
    function theme_section_enabled(string $sectionId, ?string $themeSlug = null): bool
    {
        $themeSlug = $themeSlug ?? get_active_theme();

        // Check if section is required in theme.json
        $config = get_theme_config($themeSlug);
        $sections = $config['homepage_sections'] ?? [];
        foreach ($sections as $sec) {
            if (($sec['id'] ?? '') === $sectionId && !empty($sec['required'])) {
                return true; // required sections are always enabled
            }
        }

        // Check DB for explicit enabled/disabled state
        $customs = _theme_load_customizations($themeSlug);
        $key = $sectionId . '_enabled';
        if (isset($customs['sections'][$key])) {
            $val = $customs['sections'][$key];
            // Handle both string and boolean/int values
            if ($val === false || $val === 0 || $val === '0') {
                return false;
            }
            return true;
        }

        // Default: enabled
        return true;
    }
}

