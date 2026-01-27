<?php
/**
 * Theme Builder 3.0 - Module Registry
 *
 * Central registry for all available module types.
 * Modules are registered with their definitions and defaults.
 *
 * @package ThemeBuilder
 * @version 3.0
 */

/**
 * Global module registry
 *
 * Stores all registered module definitions.
 * Structure: ['slug' => ['name' => '', 'icon' => '', 'category' => '', 'defaults' => []]]
 *
 * @var array
 */
$GLOBALS['TB_MODULES'] = [];

/**
 * Register a module type
 *
 * Adds a module definition to the registry.
 *
 * @param string $slug Unique module identifier (e.g., 'text', 'image')
 * @param array $definition Module definition containing:
 *                          - name: Display name
 *                          - icon: Icon identifier
 *                          - category: Category slug
 *                          - defaults: Default content/design/advanced arrays
 * @return void
 */
function tb_register_module(string $slug, array $definition): void
{
    // Validate required fields
    if (empty($definition['name'])) {
        return;
    }

    // Set defaults
    $definition = array_merge([
        'slug' => $slug,
        'name' => $slug,
        'icon' => 'module',
        'category' => 'general',
        'defaults' => [
            'content' => [],
            'design' => [],
            'advanced' => [
                'css_class' => '',
                'css_id' => '',
            ],
        ],
    ], $definition);

    $GLOBALS['TB_MODULES'][$slug] = $definition;
}

/**
 * Get a single module definition
 *
 * @param string $slug The module slug
 * @return array|null Module definition or null if not found
 */
function tb_get_module(string $slug): ?array
{
    return $GLOBALS['TB_MODULES'][$slug] ?? null;
}

/**
 * Get all registered modules
 *
 * @return array All module definitions keyed by slug
 */
function tb_get_all_modules(): array
{
    return $GLOBALS['TB_MODULES'];
}

/**
 * Get modules filtered by category
 *
 * @param string $category The category to filter by
 * @return array Modules in the specified category
 */
function tb_get_modules_by_category(string $category): array
{
    $filtered = [];

    foreach ($GLOBALS['TB_MODULES'] as $slug => $module) {
        if ($module['category'] === $category) {
            $filtered[$slug] = $module;
        }
    }

    return $filtered;
}

/**
 * Get all module categories
 *
 * @return array Unique category slugs
 */
function tb_get_module_categories(): array
{
    $categories = [];

    foreach ($GLOBALS['TB_MODULES'] as $module) {
        $cat = $module['category'] ?? 'general';
        if (!in_array($cat, $categories)) {
            $categories[] = $cat;
        }
    }

    return $categories;
}

/**
 * Check if a module type is registered
 *
 * @param string $slug The module slug to check
 * @return bool True if registered
 */
function tb_module_exists(string $slug): bool
{
    return isset($GLOBALS['TB_MODULES'][$slug]);
}

/**
 * Unregister a module type
 *
 * @param string $slug The module slug to remove
 * @return bool True if removed, false if not found
 */
function tb_unregister_module(string $slug): bool
{
    if (!isset($GLOBALS['TB_MODULES'][$slug])) {
        return false;
    }

    unset($GLOBALS['TB_MODULES'][$slug]);
    return true;
}

/**
 * Get modules formatted for JSON output
 *
 * Returns module data suitable for frontend JavaScript.
 *
 * @return array Modules array for JSON encoding
 */
function tb_get_modules_json(): array
{
    $modules = [];

    foreach ($GLOBALS['TB_MODULES'] as $slug => $module) {
        $modules[] = [
            'slug' => $slug,
            'name' => $module['name'],
            'icon' => $module['icon'],
            'category' => $module['category'],
            'defaults' => $module['defaults'],
        ];
    }

    return $modules;
}

/**
 * Get category labels
 *
 * @return array Category slug => label mapping
 */
function tb_get_category_labels(): array
{
    return [
        'content' => 'Content',
        'layout' => 'Layout',
        'media' => 'Media',
        'forms' => 'Forms',
        'social' => 'Social',
        'dynamic' => 'Dynamic',
        'ecommerce' => 'E-Commerce',
        'fullwidth' => 'Fullwidth',
        'general' => 'General',
    ];
}
