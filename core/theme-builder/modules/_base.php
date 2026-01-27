<?php
/**
 * Theme Builder 3.0 - Module Base Functions
 *
 * Base utility functions for module handling.
 *
 * @package ThemeBuilder
 * @version 3.0
 */

/**
 * Generate a unique ID for modules and elements
 *
 * @param string $prefix Prefix for the ID (default: 'mod')
 * @return string Unique ID like 'mod_abc123'
 */
if (!function_exists('tb_generate_id')) {
    function tb_generate_id(string $prefix = 'mod'): string
    {
        $random = bin2hex(random_bytes(4));
        return $prefix . '_' . $random;
    }
}

/**
 * Get default settings for a module type
 *
 * Returns the default content, design, and advanced arrays
 * for a given module type.
 *
 * @param string $type The module type slug
 * @return array Default settings array with content/design/advanced keys
 */
function tb_get_module_defaults(string $type): array
{
    $module = tb_get_module($type);

    if (!$module) {
        return [
            'content' => [],
            'design' => [],
            'advanced' => [
                'css_class' => '',
                'css_id' => '',
            ],
        ];
    }

    return $module['defaults'] ?? [
        'content' => [],
        'design' => [],
        'advanced' => [
            'css_class' => '',
            'css_id' => '',
        ],
    ];
}

/**
 * Validate module structure
 *
 * Checks that a module array has the required keys
 * and valid structure.
 *
 * @param array $module The module array to validate
 * @return bool True if valid, false otherwise
 */
function tb_validate_module(array $module): bool
{
    // Must have a type
    if (empty($module['type'])) {
        return false;
    }

    // Type must be registered
    if (!tb_get_module($module['type'])) {
        return false;
    }

    // Must have content array (can be empty)
    if (!isset($module['content']) || !is_array($module['content'])) {
        return false;
    }

    return true;
}

/**
 * Validate section structure
 *
 * @param array $section The section array to validate
 * @return bool True if valid, false otherwise
 */
function tb_validate_section(array $section): bool
{
    // Must have rows array
    if (!isset($section['rows']) || !is_array($section['rows'])) {
        return false;
    }

    // Validate each row
    foreach ($section['rows'] as $row) {
        if (!tb_validate_row($row)) {
            return false;
        }
    }

    return true;
}

/**
 * Validate row structure
 *
 * @param array $row The row array to validate
 * @return bool True if valid, false otherwise
 */
function tb_validate_row(array $row): bool
{
    // Must have columns array
    if (!isset($row['columns']) || !is_array($row['columns'])) {
        return false;
    }

    // Validate each column
    foreach ($row['columns'] as $column) {
        if (!tb_validate_column($column)) {
            return false;
        }
    }

    return true;
}

/**
 * Validate column structure
 *
 * @param array $column The column array to validate
 * @return bool True if valid, false otherwise
 */
function tb_validate_column(array $column): bool
{
    // Must have modules array (can be empty)
    if (!isset($column['modules']) || !is_array($column['modules'])) {
        return false;
    }

    // Validate each module
    foreach ($column['modules'] as $module) {
        if (!tb_validate_module($module)) {
            return false;
        }
    }

    return true;
}

/**
 * Sanitize module content
 *
 * Sanitizes user input in module content to prevent XSS.
 *
 * @param array $content The content array to sanitize
 * @return array Sanitized content array
 */
function tb_sanitize_module_content(array $content): array
{
    $sanitized = [];

    foreach ($content as $key => $value) {
        if (is_array($value)) {
            $sanitized[$key] = tb_sanitize_module_content($value);
        } elseif (is_string($value)) {
            // Allow HTML in text fields but sanitize attributes
            if ($key === 'text' || $key === 'html') {
                $sanitized[$key] = tb_sanitize_html($value);
            } else {
                $sanitized[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        } else {
            $sanitized[$key] = $value;
        }
    }

    return $sanitized;
}

/**
 * Sanitize HTML content
 *
 * Allows safe HTML tags while removing dangerous ones.
 *
 * @param string $html The HTML to sanitize
 * @return string Sanitized HTML
 */
function tb_sanitize_html(string $html): string
{
    // Allowed tags for rich text
    $allowedTags = '<p><br><strong><b><em><i><u><s><a><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code><span><div>';

    // Strip disallowed tags
    $html = strip_tags($html, $allowedTags);

    // Remove javascript: URLs
    $html = preg_replace('/javascript\s*:/i', '', $html);

    // Remove on* event handlers
    $html = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']?/i', '', $html);

    return $html;
}

/**
 * Create a new module instance with defaults
 *
 * @param string $type The module type
 * @return array New module instance
 */
function tb_create_module(string $type): array
{
    $defaults = tb_get_module_defaults($type);

    return [
        'id' => tb_generate_id('mod'),
        'type' => $type,
        'content' => $defaults['content'] ?? [],
        'design' => $defaults['design'] ?? [],
        'advanced' => $defaults['advanced'] ?? [
            'css_class' => '',
            'css_id' => '',
        ],
    ];
}

/**
 * Create a new section with one row and one column
 *
 * @return array New section structure
 */
function tb_create_section(): array
{
    return [
        'id' => tb_generate_id('sec'),
        'type' => 'regular',
        'design' => [
            'background_color' => '',
            'padding' => '40px 0',
        ],
        'advanced' => [
            'css_class' => '',
            'css_id' => '',
        ],
        'rows' => [
            tb_create_row(),
        ],
    ];
}

/**
 * Create a new row with one column
 *
 * @return array New row structure
 */
function tb_create_row(): array
{
    return [
        'id' => tb_generate_id('row'),
        'design' => [
            'column_gap' => '20px',
        ],
        'advanced' => [
            'css_class' => '',
            'css_id' => '',
        ],
        'columns' => [
            tb_create_column(100),
        ],
    ];
}

/**
 * Create a new column
 *
 * @param float $width Column width percentage
 * @return array New column structure
 */
function tb_create_column(float $width = 100): array
{
    return [
        'id' => tb_generate_id('col'),
        'width' => $width,
        'design' => [
            'padding' => '20px',
        ],
        'advanced' => [
            'css_class' => '',
            'css_id' => '',
        ],
        'modules' => [],
    ];
}

/**
 * Deep merge arrays for module settings
 *
 * @param array $defaults Default values
 * @param array $values User values to merge
 * @return array Merged array
 */
function tb_merge_settings(array $defaults, array $values): array
{
    $result = $defaults;

    foreach ($values as $key => $value) {
        if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
            $result[$key] = tb_merge_settings($result[$key], $value);
        } else {
            $result[$key] = $value;
        }
    }

    return $result;
}
