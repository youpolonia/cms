<?php
/**
 * JTB AI Schema
 * Exports module schemas for AI understanding
 * Provides complete metadata about all JTB modules, fields, and capabilities
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Schema
{
    // ========================================
    // Module Export Methods
    // ========================================

    /**
     * Ensure modules are loaded into Registry
     * This is needed when AI endpoints are called before router loads modules
     */
    private static function ensureModulesLoaded(): void
    {
        // Check if Registry already has modules
        if (count(JTB_Registry::all()) > 0) {
            return;
        }

        // Find plugin path
        $pluginPath = dirname(__DIR__, 2); // Goes up from includes/ai to plugin root
        $includesPath = $pluginPath . '/includes';

        // Load required base classes first (modules depend on these)
        if (!class_exists(__NAMESPACE__ . '\\JTB_Element')) {
            require_once $includesPath . '/class-jtb-element.php';
        }
        if (!class_exists(__NAMESPACE__ . '\\JTB_Registry')) {
            require_once $includesPath . '/class-jtb-registry.php';
        }
        if (!class_exists(__NAMESPACE__ . '\\JTB_Fields')) {
            if (file_exists($includesPath . '/class-jtb-fields.php')) {
                require_once $includesPath . '/class-jtb-fields.php';
            }
        }

        // Load modules
        $moduleCategories = ['structure', 'content', 'interactive', 'media', 'forms', 'blog', 'fullwidth', 'theme'];
        $modulesPath = $pluginPath . '/modules';

        foreach ($moduleCategories as $category) {
            $categoryPath = $modulesPath . '/' . $category;
            if (is_dir($categoryPath)) {
                foreach (glob($categoryPath . '/*.php') as $moduleFile) {
                    require_once $moduleFile;
                }
            }
        }
    }

    /**
     * Export all registered modules with their schemas
     * @return array Complete module schemas for AI context
     */
    public static function exportAllModules(): array
    {
        // Ensure modules are loaded before accessing Registry
        self::ensureModulesLoaded();

        $modules = [];
        $registry = JTB_Registry::all();

        foreach ($registry as $slug => $className) {
            $instance = JTB_Registry::get($slug);
            if ($instance) {
                $modules[$slug] = self::exportModuleSchema($slug);
            }
        }

        return $modules;
    }

    /**
     * Export single module schema
     * @param string $slug Module slug
     * @return array Module schema
     */
    public static function exportModuleSchema(string $slug): array
    {
        $instance = JTB_Registry::get($slug);
        if (!$instance) {
            return [];
        }

        return [
            'slug' => $slug,
            'name' => $instance->getName(),
            'category' => $instance->category,
            'icon' => $instance->icon,
            'is_child' => $instance->is_child,
            'child_slug' => $instance->child_slug ?: null,
            'description' => self::getModuleDescription($slug),
            'use_cases' => self::getModuleUseCases($slug),
            'fields' => self::getModuleFields($slug),
            'design_fields' => self::getModuleDesignFields($slug),
            'defaults' => self::getModuleDefaults($slug),
            'capabilities' => self::getModuleCapabilities($slug),
            'compatible_with' => self::getCompatibleModules($slug),
            'example_content' => self::getExampleContent($slug)
        ];
    }

    /**
     * Get module content fields
     * @param string $slug Module slug
     * @return array Fields definition
     */
    public static function getModuleFields(string $slug): array
    {
        $instance = JTB_Registry::get($slug);
        if (!$instance) {
            return [];
        }

        $fields = $instance->getContentFields();
        return self::formatFieldsForAI($fields);
    }

    /**
     * Get module design fields
     * @param string $slug Module slug
     * @return array Design fields
     */
    public static function getModuleDesignFields(string $slug): array
    {
        $instance = JTB_Registry::get($slug);
        if (!$instance) {
            return [];
        }

        $fields = $instance->getDesignFields();
        return self::formatFieldsForAI($fields);
    }

    /**
     * Get module default values
     * @param string $slug Module slug
     * @return array Default values for all fields
     */
    public static function getModuleDefaults(string $slug): array
    {
        $instance = JTB_Registry::get($slug);
        if (!$instance) {
            return [];
        }

        $defaults = [];
        $fields = $instance->getContentFields();

        foreach ($fields as $fieldName => $fieldConfig) {
            if (isset($fieldConfig['default'])) {
                $defaults[$fieldName] = $fieldConfig['default'];
            } elseif (isset($fieldConfig['fields'])) {
                // Group fields
                foreach ($fieldConfig['fields'] as $subName => $subConfig) {
                    if (isset($subConfig['default'])) {
                        $defaults[$subName] = $subConfig['default'];
                    }
                }
            }
        }

        // Add design field defaults
        $designFields = $instance->getDesignFields();
        foreach ($designFields as $groupName => $groupConfig) {
            if (isset($groupConfig['fields'])) {
                foreach ($groupConfig['fields'] as $fieldName => $fieldConfig) {
                    if (isset($fieldConfig['default'])) {
                        $defaults[$fieldName] = $fieldConfig['default'];
                    }
                }
            }
        }

        return $defaults;
    }

    /**
     * Get available field types
     * @return array Field type definitions
     */
    public static function getFieldTypes(): array
    {
        return [
            'text' => [
                'description' => 'Single line text input',
                'value_type' => 'string',
                'supports' => ['placeholder', 'maxlength']
            ],
            'textarea' => [
                'description' => 'Multi-line text input',
                'value_type' => 'string',
                'supports' => ['placeholder', 'rows']
            ],
            'richtext' => [
                'description' => 'WYSIWYG HTML editor',
                'value_type' => 'html_string',
                'supports' => ['toolbar']
            ],
            'select' => [
                'description' => 'Dropdown selection',
                'value_type' => 'string',
                'supports' => ['options', 'multiple']
            ],
            'toggle' => [
                'description' => 'Yes/No boolean switch',
                'value_type' => 'boolean',
                'supports' => []
            ],
            'checkbox' => [
                'description' => 'Checkbox input',
                'value_type' => 'boolean',
                'supports' => []
            ],
            'radio' => [
                'description' => 'Radio button selection',
                'value_type' => 'string',
                'supports' => ['options']
            ],
            'range' => [
                'description' => 'Numeric slider with input',
                'value_type' => 'number',
                'supports' => ['min', 'max', 'step', 'unit']
            ],
            'number' => [
                'description' => 'Numeric input',
                'value_type' => 'number',
                'supports' => ['min', 'max', 'step']
            ],
            'color' => [
                'description' => 'Color picker',
                'value_type' => 'string',
                'format' => '#RRGGBB or rgba(r,g,b,a)',
                'supports' => ['alpha']
            ],
            'upload' => [
                'description' => 'Image upload field',
                'value_type' => 'url_string',
                'supports' => ['accept', 'multiple']
            ],
            'url' => [
                'description' => 'URL input field',
                'value_type' => 'url_string',
                'supports' => ['placeholder']
            ],
            'icon' => [
                'description' => 'Icon picker',
                'value_type' => 'string',
                'format' => 'icon_name',
                'supports' => []
            ],
            'code' => [
                'description' => 'Code editor field',
                'value_type' => 'string',
                'supports' => ['language']
            ],
            'date' => [
                'description' => 'Date picker',
                'value_type' => 'string',
                'format' => 'YYYY-MM-DD',
                'supports' => ['min', 'max']
            ],
            'datetime' => [
                'description' => 'Date and time picker',
                'value_type' => 'string',
                'format' => 'YYYY-MM-DD HH:MM:SS',
                'supports' => ['min', 'max']
            ],
            'gallery' => [
                'description' => 'Multiple image selection',
                'value_type' => 'array',
                'item_type' => 'url_string',
                'supports' => ['max_images']
            ],
            'repeater' => [
                'description' => 'Repeatable field group',
                'value_type' => 'array',
                'supports' => ['fields', 'min', 'max']
            ],
            'buttonGroup' => [
                'description' => 'Button-style radio selection',
                'value_type' => 'string',
                'supports' => ['options']
            ],
            'align' => [
                'description' => 'Alignment buttons (left, center, right)',
                'value_type' => 'string',
                'format' => 'left|center|right|justify',
                'supports' => ['responsive']
            ],
            'multiSelect' => [
                'description' => 'Multiple selection checkboxes',
                'value_type' => 'array',
                'item_type' => 'string',
                'supports' => ['options']
            ],
            'gradient' => [
                'description' => 'Gradient color picker',
                'value_type' => 'string',
                'format' => 'linear-gradient(...) or radial-gradient(...)',
                'supports' => ['type', 'angle']
            ],
            'boxShadow' => [
                'description' => 'Box shadow builder',
                'value_type' => 'string',
                'format' => 'CSS box-shadow value',
                'supports' => []
            ],
            'border' => [
                'description' => 'Border controls',
                'value_type' => 'object',
                'supports' => ['width', 'style', 'color', 'radius']
            ],
            'font' => [
                'description' => 'Typography controls',
                'value_type' => 'object',
                'supports' => ['family', 'size', 'weight', 'style', 'line_height']
            ],
            'spacing' => [
                'description' => '4-side margin/padding control',
                'value_type' => 'object',
                'supports' => ['top', 'right', 'bottom', 'left', 'linked']
            ]
        ];
    }

    /**
     * Get module capabilities based on feature flags
     * @param string $slug Module slug
     * @return array Capabilities list
     */
    public static function getModuleCapabilities(string $slug): array
    {
        $instance = JTB_Registry::get($slug);
        if (!$instance) {
            return [];
        }

        $capabilities = [];

        // Check feature flags
        if ($instance->use_background) {
            $capabilities[] = 'background';
        }
        if ($instance->use_spacing) {
            $capabilities[] = 'spacing';
        }
        if ($instance->use_border) {
            $capabilities[] = 'border';
        }
        if ($instance->use_box_shadow) {
            $capabilities[] = 'box_shadow';
        }
        if ($instance->use_typography) {
            $capabilities[] = 'typography';
        }
        if ($instance->use_animation) {
            $capabilities[] = 'animation';
        }
        if ($instance->use_transform) {
            $capabilities[] = 'transform';
        }
        if ($instance->use_position) {
            $capabilities[] = 'positioning';
        }
        if ($instance->use_filters) {
            $capabilities[] = 'filters';
        }
        if ($instance->use_sizing) {
            $capabilities[] = 'sizing';
        }
        if ($instance->use_dividers) {
            $capabilities[] = 'dividers';
        }

        // Check for child module support
        if (!empty($instance->child_slug)) {
            $capabilities[] = 'has_children';
        }
        if ($instance->is_child) {
            $capabilities[] = 'is_child_module';
        }

        return $capabilities;
    }

    /**
     * Generate AI-friendly prompt context from all modules
     * @return string Formatted context for AI prompts
     */
    public static function generatePromptContext(): string
    {
        $modules = self::exportAllModules();

        $context = "# Available JTB Modules\n\n";

        // Group by category
        $byCategory = [];
        foreach ($modules as $slug => $module) {
            $category = $module['category'] ?? 'other';
            $byCategory[$category][$slug] = $module;
        }

        foreach ($byCategory as $category => $categoryModules) {
            $context .= "## {$category}\n\n";

            foreach ($categoryModules as $slug => $module) {
                $context .= "### {$module['name']} (`{$slug}`)\n";
                $context .= "{$module['description']}\n";

                // List main content fields
                if (!empty($module['fields'])) {
                    $context .= "Fields: " . implode(', ', array_keys($module['fields'])) . "\n";
                }

                $context .= "\n";
            }
        }

        return $context;
    }

    /**
     * Get column layout options
     * @return array Available column layouts
     */
    public static function getColumnLayouts(): array
    {
        return [
            '1_1' => ['label' => 'Full Width (100%)', 'columns' => 1, 'sizes' => ['100%']],
            '1_2_1_2' => ['label' => 'Two Columns (50/50)', 'columns' => 2, 'sizes' => ['50%', '50%']],
            '1_3_1_3_1_3' => ['label' => 'Three Columns (33/33/33)', 'columns' => 3, 'sizes' => ['33.33%', '33.33%', '33.33%']],
            '1_4_1_4_1_4_1_4' => ['label' => 'Four Columns (25/25/25/25)', 'columns' => 4, 'sizes' => ['25%', '25%', '25%', '25%']],
            '1_3_2_3' => ['label' => 'Sidebar Left (33/66)', 'columns' => 2, 'sizes' => ['33.33%', '66.66%']],
            '2_3_1_3' => ['label' => 'Sidebar Right (66/33)', 'columns' => 2, 'sizes' => ['66.66%', '33.33%']],
            '1_4_3_4' => ['label' => 'Narrow + Wide (25/75)', 'columns' => 2, 'sizes' => ['25%', '75%']],
            '3_4_1_4' => ['label' => 'Wide + Narrow (75/25)', 'columns' => 2, 'sizes' => ['75%', '25%']],
            '1_4_1_2_1_4' => ['label' => 'Centered (25/50/25)', 'columns' => 3, 'sizes' => ['25%', '50%', '25%']],
            '1_5_3_5_1_5' => ['label' => 'Centered Wide (20/60/20)', 'columns' => 3, 'sizes' => ['20%', '60%', '20%']],
            '1_6_1_6_1_6_1_6_1_6_1_6' => ['label' => 'Six Columns', 'columns' => 6, 'sizes' => ['16.66%', '16.66%', '16.66%', '16.66%', '16.66%', '16.66%']]
        ];
    }

    /**
     * Get available icons list
     * @return array Available icon names
     */
    public static function getAvailableIcons(): array
    {
        // Get icons from JTB_Icons class if available
        if (class_exists(__NAMESPACE__ . '\\JTB_Icons')) {
            return JTB_Icons::getAll();
        }

        // Fallback to common Feather icons
        return [
            // Navigation
            'arrow-left', 'arrow-right', 'arrow-up', 'arrow-down',
            'chevron-left', 'chevron-right', 'chevron-up', 'chevron-down',
            'menu', 'x', 'plus', 'minus',

            // Actions
            'check', 'check-circle', 'x-circle', 'alert-circle', 'info',
            'edit', 'trash', 'copy', 'download', 'upload', 'share',
            'search', 'filter', 'settings', 'sliders',

            // Media
            'image', 'video', 'film', 'camera', 'mic', 'volume-2',
            'play', 'pause', 'skip-forward', 'skip-back',

            // Communication
            'mail', 'message-circle', 'message-square', 'phone', 'at-sign',
            'send', 'inbox', 'bell',

            // Social
            'facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'github',
            'dribbble', 'figma', 'slack', 'twitch',

            // Business
            'briefcase', 'dollar-sign', 'credit-card', 'shopping-cart', 'shopping-bag',
            'tag', 'gift', 'award', 'target', 'trending-up',

            // Files
            'file', 'file-text', 'folder', 'archive', 'paperclip',
            'clipboard', 'book', 'bookmark', 'calendar',

            // Layout
            'layout', 'grid', 'list', 'sidebar', 'columns',
            'maximize', 'minimize', 'move', 'align-center',

            // People
            'user', 'users', 'user-plus', 'user-check', 'heart',
            'star', 'thumbs-up', 'thumbs-down', 'smile',

            // Tech
            'code', 'terminal', 'database', 'server', 'cloud',
            'cpu', 'hard-drive', 'wifi', 'bluetooth', 'zap',

            // Location
            'map', 'map-pin', 'navigation', 'compass', 'globe',
            'home', 'building',

            // Time
            'clock', 'watch', 'calendar', 'sunrise', 'sunset', 'moon', 'sun',

            // Other
            'eye', 'eye-off', 'lock', 'unlock', 'key', 'shield',
            'link', 'external-link', 'layers', 'box', 'package',
            'tool', 'wrench', 'hammer', 'scissors', 'coffee'
        ];
    }

    // ========================================
    // Private Helper Methods
    // ========================================

    /**
     * Format fields for AI understanding
     * @param array $fields Raw field definitions
     * @return array Formatted fields
     */
    private static function formatFieldsForAI(array $fields): array
    {
        $formatted = [];

        foreach ($fields as $fieldName => $fieldConfig) {
            if (!is_array($fieldConfig)) {
                continue;
            }

            // Handle group fields
            if (($fieldConfig['type'] ?? '') === 'group' && isset($fieldConfig['fields'])) {
                $formatted[$fieldName] = [
                    'type' => 'group',
                    'label' => $fieldConfig['label'] ?? $fieldName,
                    'toggle' => $fieldConfig['toggle'] ?? false,
                    'fields' => self::formatFieldsForAI($fieldConfig['fields'])
                ];
                continue;
            }

            $formatted[$fieldName] = [
                'type' => $fieldConfig['type'] ?? 'text',
                'label' => $fieldConfig['label'] ?? $fieldName,
                'description' => $fieldConfig['description'] ?? null,
                'default' => $fieldConfig['default'] ?? null,
                'required' => $fieldConfig['required'] ?? false,
                'responsive' => $fieldConfig['responsive'] ?? false,
                'hover' => $fieldConfig['hover'] ?? false
            ];

            // Add type-specific properties
            $type = $fieldConfig['type'] ?? 'text';

            if (isset($fieldConfig['options']) && in_array($type, ['select', 'radio', 'buttonGroup', 'multiSelect'])) {
                $formatted[$fieldName]['options'] = $fieldConfig['options'];
            }

            if (isset($fieldConfig['min'])) {
                $formatted[$fieldName]['min'] = $fieldConfig['min'];
            }
            if (isset($fieldConfig['max'])) {
                $formatted[$fieldName]['max'] = $fieldConfig['max'];
            }
            if (isset($fieldConfig['unit'])) {
                $formatted[$fieldName]['unit'] = $fieldConfig['unit'];
            }
            if (isset($fieldConfig['step'])) {
                $formatted[$fieldName]['step'] = $fieldConfig['step'];
            }
        }

        return $formatted;
    }

    /**
     * Get module description for AI context
     * @param string $slug Module slug
     * @return string Description
     */
    private static function getModuleDescription(string $slug): string
    {
        $descriptions = [
            // Structure
            'section' => 'Top-level container that holds rows. Sections stack vertically on the page.',
            'row' => 'Horizontal container inside sections that holds columns. Rows define the layout grid.',
            'column' => 'Vertical container inside rows that holds modules. Column widths can be customized.',

            // Content
            'heading' => 'Display heading text from H1 to H6. Use for titles and section headers.',
            'text' => 'Rich text content block with formatting. Use for paragraphs and body content.',
            'blurb' => 'Feature box with icon, title, and description. Perfect for features and services.',
            'button' => 'Clickable button with customizable text, link, and style.',
            'image' => 'Display a single image with optional caption and link.',
            'divider' => 'Horizontal line separator to divide content sections.',
            'icon' => 'Display a single icon from the icon library.',
            'cta' => 'Call-to-action block with heading, text, and button.',
            'testimonial' => 'Customer testimonial with quote, author name, position, and avatar.',
            'team_member' => 'Team member card with photo, name, position, and social links.',
            'pricing_table' => 'Pricing plan comparison table with features and button.',
            'pricing_table_item' => 'Child module for pricing table features.',
            'countdown' => 'Countdown timer to a specific date/time.',
            'number_counter' => 'Animated number counter for statistics.',
            'circle_counter' => 'Circular progress indicator with percentage.',
            'bar_counter' => 'Horizontal progress bar with label and percentage.',
            'code' => 'Display formatted code snippet with syntax highlighting.',
            'social_follow' => 'Social media follow buttons.',
            'social_follow_item' => 'Individual social follow link.',
            'sidebar' => 'Widget sidebar area.',
            'comments' => 'Post comments section.',
            'post_navigation' => 'Previous/next post navigation links.',
            'shop' => 'E-commerce product grid.',

            // Interactive
            'accordion' => 'Collapsible content sections. Click title to expand/collapse.',
            'accordion_item' => 'Individual accordion panel with title and content.',
            'tabs' => 'Tabbed content container. Click tab to switch content.',
            'tabs_item' => 'Individual tab panel with title and content.',
            'toggle' => 'Single toggle/collapsible content block.',

            // Media
            'gallery' => 'Image gallery with grid layout and lightbox.',
            'slider' => 'Image/content slider with navigation.',
            'slider_item' => 'Individual slide in a slider.',
            'video' => 'Embedded video player (YouTube, Vimeo, or self-hosted).',
            'video_slider' => 'Video slider carousel.',
            'video_slider_item' => 'Individual video in slider.',
            'audio' => 'Audio player for podcasts or music.',
            'map' => 'Embedded Google Map with custom markers.',
            'map_pin' => 'Map marker/pin for map module.',

            // Forms
            'contact_form' => 'Contact form with customizable fields.',
            'contact_form_field' => 'Individual form field.',
            'login' => 'User login form.',
            'signup' => 'User registration form.',
            'search' => 'Search form input.',

            // Blog
            'blog' => 'Blog post grid/list with pagination.',
            'portfolio' => 'Portfolio project showcase grid.',
            'filterable_portfolio' => 'Portfolio with category filtering.',
            'post_slider' => 'Blog post carousel slider.',

            // Fullwidth
            'fullwidth_header' => 'Full-width hero header with background, title, and CTA.',
            'fullwidth_image' => 'Full-width image display.',
            'fullwidth_slider' => 'Full-width slider/carousel.',
            'fullwidth_slider_item' => 'Slide for fullwidth slider.',
            'fullwidth_map' => 'Full-width embedded map.',
            'fullwidth_menu' => 'Full-width navigation menu.',
            'fullwidth_code' => 'Full-width code display.',
            'fullwidth_portfolio' => 'Full-width portfolio grid.',
            'fullwidth_post_slider' => 'Full-width blog post slider.',
            'fullwidth_post_title' => 'Full-width post title display.',

            // Theme modules
            'menu' => 'Navigation menu for header/footer.',
            'site_logo' => 'Site logo display.',
            'breadcrumbs' => 'Breadcrumb navigation trail.',
            'post_title' => 'Dynamic post/page title.',
            'post_content' => 'Dynamic post/page content.',
            'post_meta' => 'Post metadata (date, author, categories).',
            'post_excerpt' => 'Post excerpt/summary text.',
            'featured_image' => 'Post featured image.',
            'author_box' => 'Author information box.',
            'related_posts' => 'Related posts grid.',
            'archive_posts' => 'Archive/category post listing.',
            'archive_title' => 'Archive page title.',
            'search_form' => 'Search input form.',
            'footer_info' => 'Footer information block.',
            'footer_menu' => 'Footer navigation menu.',
            'copyright' => 'Copyright text display.',
            'header_button' => 'Header CTA button.',
            'cart_icon' => 'Shopping cart icon with count.',
            'social_icons' => 'Social media icon links.'
        ];

        return $descriptions[$slug] ?? "JTB module: {$slug}";
    }

    /**
     * Get common use cases for a module
     * @param string $slug Module slug
     * @return array Use cases
     */
    private static function getModuleUseCases(string $slug): array
    {
        $useCases = [
            'heading' => ['Page titles', 'Section headers', 'Subheadings'],
            'text' => ['Paragraphs', 'Body content', 'Descriptions', 'Introductions'],
            'blurb' => ['Features list', 'Services', 'Benefits', 'Process steps'],
            'button' => ['Call-to-action', 'Links', 'Form submit', 'Navigation'],
            'image' => ['Photos', 'Illustrations', 'Diagrams', 'Screenshots'],
            'cta' => ['Call-to-action sections', 'Newsletter signup', 'Download prompts'],
            'testimonial' => ['Customer reviews', 'Client feedback', 'Success stories'],
            'team_member' => ['Team page', 'About us', 'Staff directory'],
            'pricing_table' => ['Pricing page', 'Plan comparison', 'Subscription options'],
            'accordion' => ['FAQs', 'Feature details', 'Expandable content'],
            'tabs' => ['Product features', 'Service categories', 'Content organization'],
            'gallery' => ['Photo galleries', 'Portfolio', 'Product images'],
            'slider' => ['Hero sliders', 'Image carousels', 'Testimonial rotators'],
            'video' => ['Product demos', 'Tutorials', 'Testimonials'],
            'contact_form' => ['Contact page', 'Quote requests', 'Feedback forms'],
            'blog' => ['Blog page', 'News section', 'Article listing'],
            'portfolio' => ['Work showcase', 'Case studies', 'Projects'],
            'fullwidth_header' => ['Homepage hero', 'Landing page headers', 'Page banners'],
            'number_counter' => ['Statistics', 'Achievements', 'Metrics'],
            'countdown' => ['Launch dates', 'Event countdowns', 'Sale timers'],
            'map' => ['Contact page location', 'Store locator', 'Office address']
        ];

        return $useCases[$slug] ?? [];
    }

    /**
     * Get modules compatible with given module
     * @param string $slug Module slug
     * @return array Compatible module slugs
     */
    private static function getCompatibleModules(string $slug): array
    {
        // Modules commonly used together
        $compatibility = [
            'heading' => ['text', 'button', 'divider', 'blurb'],
            'text' => ['heading', 'button', 'image', 'divider'],
            'blurb' => ['heading', 'button', 'divider'],
            'cta' => ['heading', 'text', 'button'],
            'testimonial' => ['heading', 'slider'],
            'pricing_table' => ['heading', 'button'],
            'accordion' => ['heading', 'text'],
            'tabs' => ['heading', 'text', 'image'],
            'gallery' => ['heading', 'text'],
            'video' => ['heading', 'text', 'button'],
            'contact_form' => ['heading', 'text', 'map'],
            'blog' => ['heading', 'sidebar'],
            'fullwidth_header' => ['button']
        ];

        return $compatibility[$slug] ?? [];
    }

    /**
     * Get example content for a module
     * @param string $slug Module slug
     * @return array Example values
     */
    /**
     * Get example content for a module - DYNAMICALLY from Registry
     * Reads actual field definitions and generates sensible defaults
     * @param string $slug Module slug
     * @return array Example values using CORRECT attribute names
     */
    private static function getExampleContent(string $slug): array
    {
        try {
            $instance = JTB_Registry::get($slug);
            if (!$instance) {
                return [];
            }

            $fields = $instance->getFields();
            if (empty($fields)) {
                return [];
            }

            $example = [];
            foreach ($fields as $fieldName => $fieldDef) {
                $type = $fieldDef['type'] ?? 'text';
                $default = $fieldDef['default'] ?? null;

                // Use default value if available
                if ($default !== null && $default !== '') {
                    $example[$fieldName] = $default;
                    continue;
                }

                // Generate sensible example based on field type
                $example[$fieldName] = match ($type) {
                    'text' => 'Example ' . ucwords(str_replace('_', ' ', $fieldName)),
                    'textarea', 'richtext' => '<p>Example content for ' . str_replace('_', ' ', $fieldName) . '.</p>',
                    'url' => '#',
                    'upload' => '',
                    'color' => '#333333',
                    'number', 'range' => $fieldDef['min'] ?? 0,
                    'toggle' => false,
                    'select' => !empty($fieldDef['options']) ? array_key_first($fieldDef['options']) : '',
                    'icon' => 'star',
                    default => '',
                };
            }

            return $example;
        } catch (\Exception $e) {
            return [];
        }
    }

    // ========================================
    // Module Category Methods
    // ========================================

    /**
     * Get module category for a slug
     * @param string $slug Module slug
     * @return string Category name
     */
    public static function getModuleCategory(string $slug): string
    {
        $instance = JTB_Registry::get($slug);
        return $instance ? $instance->category : 'other';
    }

    /**
     * Get all modules by category
     * @return array Modules grouped by category
     */
    public static function getModulesByCategory(): array
    {
        $modules = self::exportAllModules();
        $byCategory = [];

        foreach ($modules as $slug => $module) {
            $category = $module['category'] ?? 'other';
            if (!isset($byCategory[$category])) {
                $byCategory[$category] = [];
            }
            $byCategory[$category][$slug] = $module;
        }

        return $byCategory;
    }

    /**
     * Get compact module list for AI (just slugs and names)
     * @return array Simple module list
     */
    public static function getCompactModuleList(): array
    {
        $list = [];
        $registry = JTB_Registry::all();

        foreach ($registry as $slug => $className) {
            $instance = JTB_Registry::get($slug);
            if ($instance) {
                $list[$slug] = [
                    'name' => $instance->getName(),
                    'category' => $instance->category,
                    'is_child' => $instance->is_child,
                    'description' => self::getModuleDescription($slug)
                ];
            }
        }

        return $list;
    }

    /**
     * Get layout-compatible modules (non-child, non-structure)
     * @return array Content modules for layouts
     */
    public static function getContentModules(): array
    {
        $modules = self::getCompactModuleList();
        $content = [];

        foreach ($modules as $slug => $module) {
            // Skip structure and child modules
            if ($module['category'] === 'structure' || $module['is_child']) {
                continue;
            }
            $content[$slug] = $module;
        }

        return $content;
    }

    /**
     * Get child modules for a parent
     * @param string $parentSlug Parent module slug
     * @return array Child module slugs
     */
    public static function getChildModules(string $parentSlug): array
    {
        $children = [];
        $registry = JTB_Registry::all();

        foreach ($registry as $slug => $className) {
            $instance = JTB_Registry::get($slug);
            if ($instance && $instance->is_child) {
                // Check if this child belongs to parent
                // Convention: child slug starts with parent slug or is defined in parent
                $parentInstance = JTB_Registry::get($parentSlug);
                if ($parentInstance && $parentInstance->child_slug === $slug) {
                    $children[] = $slug;
                }
            }
        }

        return $children;
    }

    // ========================================
    // Compact Schemas for AI Prompt
    // ========================================

    /**
     * Get compact module schemas formatted for AI prompt.
     * DYNAMICALLY reads ALL modules from JTB_Registry.
     * No hardcoded module lists - everything from Registry + getFields() + getDesignFields().
     *
     * @since 2026-02-05 - Replaced hardcoded static string with dynamic Registry export
     * @return string Formatted schemas for system prompt
     */
    public static function getCompactSchemasForAI(): string
    {
        // Ensure modules are loaded before accessing Registry
        self::ensureModulesLoaded();

        try {
            $registry = JTB_Registry::all();
        } catch (\Exception $e) {
            error_log('JTB_AI_Schema::getCompactSchemasForAI - Registry unavailable: ' . $e->getMessage());
            return "heading: text, level\ntext: content\nbutton: text, link_url\nblurb: title, content, font_icon\nimage: src, alt";
        }

        $categories = [];

        foreach ($registry as $slug => $className) {
            $instance = JTB_Registry::get($slug);
            if (!$instance) continue;

            $category = $instance->category ?? 'other';

            // Collect ALL fields: content + design
            $contentFields = $instance->getContentFields();
            $designFields = [];
            try {
                $designFields = $instance->getDesignFields();
            } catch (\Exception $e) {
                // Some modules may not have design fields
            }

            // Build compact field list from content fields
            $fieldParts = [];
            foreach ($contentFields as $fieldName => $fieldDef) {
                $fieldParts[] = self::formatFieldCompact($fieldName, $fieldDef);
            }

            // Extract key design fields (skip groups, extract inner fields)
            foreach ($designFields as $groupName => $groupDef) {
                if (($groupDef['type'] ?? '') === 'group' && !empty($groupDef['fields'])) {
                    foreach ($groupDef['fields'] as $fName => $fDef) {
                        // Only include non-generic design fields (skip common ones like padding, margin)
                        $fieldParts[] = self::formatFieldCompact($fName, $fDef);
                    }
                } else {
                    $fieldParts[] = self::formatFieldCompact($groupName, $groupDef);
                }
            }

            // Remove duplicates
            $fieldParts = array_unique($fieldParts);

            // Build line
            $childNote = '';
            if ($instance->child_slug) {
                $childNote = " [children: {$instance->child_slug}]";
            }
            if ($instance->is_child) {
                $childNote .= " [child module]";
            }

            $line = $slug . ': ' . implode(', ', $fieldParts) . $childNote;

            if (!isset($categories[$category])) {
                $categories[$category] = [];
            }
            $categories[$category][] = $line;
        }

        // Format output grouped by category
        $output = "## MODULE SCHEMAS (all attributes from Registry)\n";
        foreach ($categories as $catName => $lines) {
            $output .= "\n### " . ucfirst($catName) . "\n";
            $output .= implode("\n", $lines) . "\n";
        }

        // Add important notes
        $output .= "\nNOTES:\n";
        $output .= "- menu: Items come from DATABASE - do NOT include menu_items array!\n";
        $output .= "- social_icons: Use individual URL attrs (facebook_url, twitter_url, etc.) - NOT icons array!\n";
        $output .= "- padding/margin format: {\"top\": N, \"right\": N, \"bottom\": N, \"left\": N}\n";
        $output .= "- border_radius format: {\"top_left\": N, \"top_right\": N, \"bottom_right\": N, \"bottom_left\": N}\n";
        $output .= "- Responsive variants: add __tablet or __phone suffix (e.g. font_size__tablet)\n";

        return $output;
    }

    /**
     * Format a single field definition into compact AI-readable string
     */
    private static function formatFieldCompact(string $name, array $def): string
    {
        $type = $def['type'] ?? 'text';

        switch ($type) {
            case 'select':
            case 'buttonGroup':
            case 'radio':
                if (!empty($def['options'])) {
                    $opts = array_keys($def['options']);
                    return $name . '(' . implode('|', array_slice($opts, 0, 8)) . ')';
                }
                return $name;

            case 'toggle':
            case 'checkbox':
                return $name . '(true|false)';

            case 'richtext':
            case 'textarea':
                return $name . '(HTML)';

            case 'upload':
                return $name . '(image URL)';

            case 'url':
                return $name . '(URL)';

            case 'color':
                return $name . '(#hex)';

            case 'range':
            case 'number':
                $min = $def['min'] ?? '';
                $max = $def['max'] ?? '';
                if ($min !== '' && $max !== '') {
                    return $name . "({$min}-{$max})";
                }
                return $name;

            case 'spacing':
                return $name . '{top,right,bottom,left}';

            case 'border':
                return $name . '{width,style,color}';

            case 'boxShadow':
                return $name . '(shadow)';

            case 'gradient':
                return $name . '(gradient)';

            case 'font':
                return $name . '(font family)';

            case 'gallery':
            case 'repeater':
                return $name . '(array)';

            case 'group':
                // Skip groups in compact format
                return $name;

            default:
                return $name;
        }
    }
}
