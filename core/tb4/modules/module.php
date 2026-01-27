<?php
namespace Core\TB4\Modules;

require_once __DIR__ . '/../advanced-fields/animation.php';
require_once __DIR__ . '/../advanced-fields/typography.php';

use TB4\AdvancedFields\Animation;
use TB4\AdvancedFields\Typography;

/**
 * TB 4.0 Base Module Class
 * Enhanced with full Advanced Fields support
 *
 * Features:
 * - Responsive values (desktop/tablet/phone)
 * - Hover state support (normal/hover)
 * - Default values system
 * - CSS generation with breakpoints
 * - Content/Design/Advanced tabs
 */
abstract class Module
{
    protected string $name = "";
    protected string $slug = "";
    protected string $icon = "";
    protected string $category = "";
    protected array $elements = [];

    // Parent-child module support
    protected string $type = 'module';           // 'module', 'parent', 'child'
    protected ?string $child_slug = null;        // For parent modules - defines allowed child type
    protected ?string $parent_slug = null;       // For child modules - defines parent type
    protected ?string $child_title_var = null;   // For child modules - field used as title in editor

    /**
     * Static flag to track if keyframes have been output
     */
    protected static bool $keyframes_output = false;

    /**
     * Default values for module attributes
     */
    protected array $defaults = [];

    /**
     * Responsive breakpoints (max-width in px)
     */
    protected array $breakpoints = [
        'desktop' => null,
        'tablet' => 980,
        'phone' => 767
    ];

    /**
     * Advanced fields configuration
     * Supports: responsive, hover, type-specific options
     */
    protected array $advanced_fields = [
        'css_id' => [
            'label' => 'CSS ID',
            'type' => 'text',
            'tab' => 'advanced',
            'default' => '',
            'description' => 'Unique identifier for CSS targeting'
        ],
        'css_class' => [
            'label' => 'CSS Classes',
            'type' => 'text',
            'tab' => 'advanced',
            'default' => '',
            'description' => 'Additional CSS classes (space-separated)'
        ],
        'margin' => [
            'label' => 'Margin',
            'type' => 'spacing',
            'tab' => 'design',
            'responsive' => true,
            'default' => [
                'desktop' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
                'tablet' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
                'phone' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => '']
            ]
        ],
        'padding' => [
            'label' => 'Padding',
            'type' => 'spacing',
            'tab' => 'design',
            'responsive' => true,
            'default' => [
                'desktop' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
                'tablet' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
                'phone' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => '']
            ]
        ],
        'background' => [
            'label' => 'Background',
            'type' => 'background',
            'tab' => 'design',
            'hover' => true,
            'default' => [
                'normal' => ['color' => '', 'image' => '', 'size' => 'cover', 'position' => 'center'],
                'hover' => ['color' => '', 'image' => '', 'size' => 'cover', 'position' => 'center']
            ]
        ],
        'border' => [
            'label' => 'Border',
            'type' => 'border',
            'tab' => 'design',
            'hover' => true,
            'responsive' => true,
            'default' => [
                'normal' => [
                    'desktop' => ['width' => '', 'style' => 'solid', 'color' => '', 'radius' => ''],
                    'tablet' => ['width' => '', 'style' => 'solid', 'color' => '', 'radius' => ''],
                    'phone' => ['width' => '', 'style' => 'solid', 'color' => '', 'radius' => '']
                ],
                'hover' => [
                    'desktop' => ['width' => '', 'style' => 'solid', 'color' => '', 'radius' => ''],
                    'tablet' => ['width' => '', 'style' => 'solid', 'color' => '', 'radius' => ''],
                    'phone' => ['width' => '', 'style' => 'solid', 'color' => '', 'radius' => '']
                ]
            ]
        ],
        'box_shadow' => [
            'label' => 'Box Shadow',
            'type' => 'box_shadow',
            'tab' => 'design',
            'hover' => true,
            'default' => [
                'normal' => ['horizontal' => '', 'vertical' => '', 'blur' => '', 'spread' => '', 'color' => ''],
                'hover' => ['horizontal' => '', 'vertical' => '', 'blur' => '', 'spread' => '', 'color' => '']
            ]
        ],
        'transform' => [
            'label' => 'Transform',
            'type' => 'transform',
            'tab' => 'advanced',
            'hover' => true,
            'default' => [
                'normal' => ['scale' => '', 'rotate' => '', 'translateX' => '', 'translateY' => ''],
                'hover' => ['scale' => '', 'rotate' => '', 'translateX' => '', 'translateY' => '']
            ]
        ],
        'transition' => [
            'label' => 'Transition',
            'type' => 'transition',
            'tab' => 'advanced',
            'default' => [
                'duration' => '300',
                'timing' => 'ease',
                'delay' => '0'
            ]
        ],
        'visibility' => [
            'label' => 'Visibility',
            'type' => 'visibility',
            'tab' => 'advanced',
            'default' => [
                'desktop' => true,
                'tablet' => true,
                'phone' => true
            ]
        ],
        'z_index' => [
            'label' => 'Z-Index',
            'type' => 'number',
            'tab' => 'advanced',
            'responsive' => true,
            'default' => ''
        ],
        'overflow' => [
            'label' => 'Overflow',
            'type' => 'select',
            'tab' => 'advanced',
            'options' => ['visible' => 'Visible', 'hidden' => 'Hidden', 'scroll' => 'Scroll', 'auto' => 'Auto'],
            'default' => 'visible'
        ],
        'position' => [
            'label' => 'Position',
            'type' => 'position',
            'tab' => 'advanced',
            'responsive' => true,
            'default' => [
                'desktop' => ['type' => 'relative', 'top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
                'tablet' => ['type' => '', 'top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
                'phone' => ['type' => '', 'top' => '', 'right' => '', 'bottom' => '', 'left' => '']
            ]
        ],
        'animation' => [
            'label' => 'Animation',
            'type' => 'animation',
            'tab' => 'advanced',
            'default' => [
                'type' => '',
                'duration' => '400',
                'delay' => '0',
                'easing' => 'ease',
                'iteration' => '1',
                'direction' => 'normal',
                'fill_mode' => 'forwards',
                'trigger' => 'load',
                'scroll_offset' => '100'
            ]
        ],
        'custom_css' => [
            'label' => 'Custom CSS',
            'type' => 'custom_css',
            'tab' => 'advanced',
            'default' => []
        ]
    ];

    /**
     * Inner elements for element-level styling
     * Override in child classes
     */
    protected array $inner_elements = [];

    /**
     * Typography fields configuration per element
     * Override in child classes to define element-specific typography
     */
    protected array $typography_fields = [];

    /**
     * Custom CSS fields for per-element CSS targeting
     * Override in child classes to define module-specific CSS targets
     *
     * Structure:
     * [
     *     "element_key" => [
     *         "label" => "Display Label",
     *         "selector" => ".css-selector",
     *         "description" => "Help text for this CSS target"
     *     ]
     * ]
     *
     * Example for Blurb module:
     * [
     *     "blurb_icon" => ["label" => "Blurb Icon", "selector" => ".tb4-blurb__icon"],
     *     "blurb_title" => ["label" => "Blurb Title", "selector" => ".tb4-blurb__title"],
     *     "blurb_content" => ["label" => "Blurb Content", "selector" => ".tb4-blurb__content"]
     * ]
     */
    protected array $custom_css_fields = [];

    // =========================================================================
    // GETTERS
    // =========================================================================

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getElements(): array
    {
        return $this->elements;
    }

    public function getBreakpoints(): array
    {
        return $this->breakpoints;
    }

    public function getInnerElements(): array
    {
        return $this->inner_elements;
    }

    public function get_typography_fields(): array
    {
        return $this->typography_fields;
    }

    /**
     * Get custom CSS fields configuration
     * Returns array of element targets for user-defined CSS
     *
     * @return array Custom CSS field definitions
     */
    public function get_custom_css_fields(): array
    {
        return $this->custom_css_fields;
    }

    // Parent-child module getters
    public function getType(): string
    {
        return $this->type;
    }

    public function isParent(): bool
    {
        return $this->type === 'parent';
    }

    public function isChild(): bool
    {
        return $this->type === 'child';
    }

    public function getChildSlug(): ?string
    {
        return $this->child_slug;
    }

    public function getParentSlug(): ?string
    {
        return $this->parent_slug;
    }

    public function getChildTitleVar(): ?string
    {
        return $this->child_title_var;
    }

    // =========================================================================
    // ABSTRACT METHODS
    // =========================================================================

    /**
     * Get content tab fields (must be implemented by child)
     */
    abstract public function get_content_fields(): array;

    /**
     * Render the module HTML
     */
    abstract public function render(array $attrs): string;

    // =========================================================================
    // DEFAULT VALUES
    // =========================================================================

    /**
     * Get module defaults
     */
    public function get_defaults(): array
    {
        $defaults = $this->defaults;

        // Merge advanced field defaults
        foreach ($this->advanced_fields as $key => $field) {
            if (!isset($defaults[$key]) && isset($field['default'])) {
                $defaults[$key] = $field['default'];
            }
        }

        // Merge content field defaults
        foreach ($this->get_content_fields() as $key => $field) {
            if (!isset($defaults[$key]) && isset($field['default'])) {
                $defaults[$key] = $field['default'];
            }
        }

        return $defaults;
    }

    /**
     * Merge attributes with defaults
     */
    public function merge_with_defaults(array $attrs): array
    {
        $defaults = $this->get_defaults();
        return array_replace_recursive($defaults, $attrs);
    }

    // =========================================================================
    // ADVANCED FIELDS
    // =========================================================================

    /**
     * Get all advanced fields configuration
     */
    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    /**
     * Process advanced fields from attributes
     * Returns processed values with responsive/hover handling
     */
    public function process_advanced_fields(array $attrs): array
    {
        $processed = [];

        foreach ($this->advanced_fields as $key => $config) {
            $value = $attrs[$key] ?? $config['default'] ?? null;

            if ($value === null) {
                continue;
            }

            $processed[$key] = [
                'value' => $value,
                'type' => $config['type'] ?? 'text',
                'responsive' => $config['responsive'] ?? false,
                'hover' => $config['hover'] ?? false
            ];
        }

        return $processed;
    }

    /**
     * Generate CSS from advanced fields
     */
    public function generate_advanced_css(string $selector, array $attrs): string
    {
        $css = '';
        $desktop_css = [];
        $tablet_css = [];
        $phone_css = [];
        $hover_css = [];

        // Process each advanced field
        foreach ($this->advanced_fields as $key => $config) {
            $value = $attrs[$key] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            $type = $config['type'] ?? 'text';
            $has_responsive = $config['responsive'] ?? false;
            $has_hover = $config['hover'] ?? false;

            switch ($type) {
                case 'spacing':
                    $this->process_spacing_css($key, $value, $has_responsive, $desktop_css, $tablet_css, $phone_css);
                    break;

                case 'background':
                    $this->process_background_css($value, $has_hover, $desktop_css, $hover_css);
                    break;

                case 'border':
                    $this->process_border_css($value, $has_responsive, $has_hover, $desktop_css, $tablet_css, $phone_css, $hover_css);
                    break;

                case 'box_shadow':
                    $this->process_box_shadow_css($value, $has_hover, $desktop_css, $hover_css);
                    break;

                case 'transform':
                    $this->process_transform_css($value, $has_hover, $desktop_css, $hover_css);
                    break;

                case 'transition':
                    if (is_array($value)) {
                        $duration = ($value['duration'] ?? '300') . 'ms';
                        $timing = $value['timing'] ?? 'ease';
                        $delay = ($value['delay'] ?? '0') . 'ms';
                        $desktop_css[] = "transition: all {$duration} {$timing} {$delay}";
                    }
                    break;

                case 'position':
                    $this->process_position_css($value, $has_responsive, $desktop_css, $tablet_css, $phone_css);
                    break;
            }
        }

        // Build desktop CSS
        if (!empty($desktop_css)) {
            $css .= "{$selector} {\n  " . implode(";\n  ", $desktop_css) . ";\n}\n";
        }

        // Build hover CSS
        if (!empty($hover_css)) {
            $css .= "{$selector}:hover {\n  " . implode(";\n  ", $hover_css) . ";\n}\n";
        }

        // Build tablet CSS
        if (!empty($tablet_css)) {
            $css .= "@media (max-width: {$this->breakpoints['tablet']}px) {\n";
            $css .= "  {$selector} {\n    " . implode(";\n    ", $tablet_css) . ";\n  }\n";
            $css .= "}\n";
        }

        // Build phone CSS
        if (!empty($phone_css)) {
            $css .= "@media (max-width: {$this->breakpoints['phone']}px) {\n";
            $css .= "  {$selector} {\n    " . implode(";\n    ", $phone_css) . ";\n  }\n";
            $css .= "}\n";
        }

        return $css;
    }

    // =========================================================================
    // RESPONSIVE SUPPORT
    // =========================================================================

    /**
     * Get responsive value for a specific device
     *
     * @param array|string $values Value array with device keys or single value
     * @param string $device Device: desktop, tablet, phone
     * @return string
     */
    public function get_responsive_value(array|string $values, string $device = 'desktop'): string
    {
        // If not an array, return as-is
        if (!is_array($values)) {
            return (string)$values;
        }

        // Direct device key
        if (isset($values[$device]) && $values[$device] !== '') {
            return is_array($values[$device]) ? '' : (string)$values[$device];
        }

        // Fallback chain: phone -> tablet -> desktop
        $fallback = match ($device) {
            'phone' => ['phone', 'tablet', 'desktop'],
            'tablet' => ['tablet', 'desktop'],
            default => ['desktop']
        };

        foreach ($fallback as $fb) {
            if (isset($values[$fb]) && $values[$fb] !== '' && !is_array($values[$fb])) {
                return (string)$values[$fb];
            }
        }

        return '';
    }

    /**
     * Check if value has responsive variants
     */
    public function has_responsive_value(array $values): bool
    {
        return isset($values['desktop']) || isset($values['tablet']) || isset($values['phone']);
    }

    // =========================================================================
    // HOVER STATE SUPPORT
    // =========================================================================

    /**
     * Get hover state value
     *
     * @param array|string $values Value array with state keys or single value
     * @param string $state State: normal, hover
     * @return mixed
     */
    public function get_hover_value(array|string $values, string $state = 'normal'): mixed
    {
        // If not an array, return as-is
        if (!is_array($values)) {
            return $values;
        }

        // Direct state key
        if (isset($values[$state])) {
            return $values[$state];
        }

        // Fallback: hover -> normal
        if ($state === 'hover' && isset($values['normal'])) {
            return $values['normal'];
        }

        return $values;
    }

    /**
     * Check if value has hover state
     */
    public function has_hover_value(array $values): bool
    {
        return isset($values['normal']) || isset($values['hover']);
    }

    // =========================================================================
    // TAB METHODS
    // =========================================================================

    /**
     * Get design tab fields
     */
    public function get_design_fields(): array
    {
        $design_fields = [];

        foreach ($this->advanced_fields as $key => $field) {
            if (($field['tab'] ?? '') === 'design') {
                $design_fields[$key] = $field;
            }
        }

        return $design_fields;
    }

    /**
     * Get advanced tab fields
     */
    public function get_advanced_tab_fields(): array
    {
        $advanced_fields = [];

        foreach ($this->advanced_fields as $key => $field) {
            if (($field['tab'] ?? '') === 'advanced') {
                $advanced_fields[$key] = $field;
            }
        }

        return $advanced_fields;
    }

    /**
     * Get all fields organized by tab
     */
    public function get_fields_by_tab(): array
    {
        return [
            'content' => $this->get_content_fields(),
            'design' => $this->get_design_fields(),
            'advanced' => $this->get_advanced_tab_fields()
        ];
    }

    // =========================================================================
    // CSS GENERATION
    // =========================================================================

    /**
     * Generate complete module CSS including responsive and hover states
     */
    public function generate_module_css(string $unique_id, array $attrs): string
    {
        $selector = '#' . $unique_id;
        $css = '';

        // Generate advanced fields CSS
        $css .= $this->generate_advanced_css($selector, $attrs);

        // Generate inner elements CSS if defined
        if (!empty($this->inner_elements)) {
            $css .= $this->generate_inner_elements_css($selector, $attrs);
        }

        // Generate typography CSS for elements
        if (!empty($this->typography_fields)) {
            $css .= $this->process_typography_css($selector, $attrs);
        }

        // Generate visibility CSS
        $css .= $this->generate_visibility_css($selector, $attrs);

        // Generate animation CSS
        $css .= $this->process_animation_css($selector, $attrs);

        // Generate custom CSS for module elements
        if (!empty($this->custom_css_fields)) {
            $css .= $this->process_custom_css($unique_id, $attrs);
        }

        return $css;
    }

    /**
     * Process animation CSS using the Animation class
     *
     * @param string $selector CSS selector
     * @param array $attrs Module attributes
     * @return string Generated animation CSS
     */
    protected function process_animation_css(string $selector, array $attrs): string
    {
        $animation_values = $attrs['animation'] ?? [];

        // If no animation type set, return empty
        if (empty($animation_values) || empty($animation_values['type'])) {
            return '';
        }

        $animation = new Animation();
        return $animation->generate_css($selector, $animation_values);
    }

    /**
     * Process custom CSS from user input
     *
     * Generates scoped CSS rules for module elements based on user-defined styles.
     * Each element in $custom_css_fields defines a CSS target that users can style.
     *
     * @param string $unique_id Module unique ID for scoping
     * @param array $attrs Module attributes containing custom_css
     * @return string Generated CSS
     */
    protected function process_custom_css(string $unique_id, array $attrs): string
    {
        $custom_css = $attrs['custom_css'] ?? [];
        if (empty($custom_css) || !is_array($custom_css)) {
            return '';
        }

        $css = '';
        $selector_base = '#' . $unique_id;

        // Get custom CSS field definitions from the module
        $css_fields = $this->get_custom_css_fields();
        if (empty($css_fields)) {
            return '';
        }

        foreach ($custom_css as $element_key => $element_styles) {
            // Validate that this element exists in our definitions
            if (!isset($css_fields[$element_key]) || empty($element_styles)) {
                continue;
            }

            $element_selector = $css_fields[$element_key]['selector'] ?? '';
            if (empty($element_selector)) {
                continue;
            }

            // Build full selector
            $full_selector = $selector_base . ' ' . $element_selector;

            // Process normal state styles
            $normal_styles = $element_styles['normal'] ?? $element_styles;
            if (!empty($normal_styles) && is_array($normal_styles)) {
                $css .= $this->build_custom_css_rules($full_selector, $normal_styles);
            }

            // Process hover state styles
            $hover_styles = $element_styles['hover'] ?? [];
            if (!empty($hover_styles) && is_array($hover_styles)) {
                $css .= $this->build_custom_css_rules($full_selector . ':hover', $hover_styles);
            }

            // Process responsive styles
            foreach (['tablet', 'phone'] as $device) {
                $device_styles = $element_styles[$device] ?? [];
                if (!empty($device_styles) && is_array($device_styles)) {
                    $breakpoint = $this->breakpoints[$device] ?? null;
                    if ($breakpoint) {
                        $css .= "@media (max-width: {$breakpoint}px) {\n";
                        $css .= $this->build_custom_css_rules($full_selector, $device_styles);
                        $css .= "}\n";
                    }
                }
            }
        }

        return $css;
    }

    /**
     * Build CSS rules from style array
     *
     * @param string $selector CSS selector
     * @param array $styles Array of CSS property => value pairs
     * @return string CSS rule block
     */
    protected function build_custom_css_rules(string $selector, array $styles): string
    {
        $css_props = [];

        foreach ($styles as $property => $value) {
            if ($value === '' || $value === null) {
                continue;
            }

            // Convert property name to CSS format
            $css_property = $this->to_css_property($property);

            // Sanitize value (basic XSS prevention)
            $safe_value = preg_replace('/[<>"\']/', '', (string)$value);
            $css_props[] = "{$css_property}: {$safe_value}";
        }

        if (empty($css_props)) {
            return '';
        }

        return "{$selector} {\n  " . implode(";\n  ", $css_props) . ";\n}\n";
    }

    /**
     * Get animation keyframes CSS (call once per page)
     * Returns keyframes only on first call, empty string on subsequent calls
     *
     * @param bool $force Force output even if already called
     * @return string CSS keyframes
     */
    public static function get_animation_keyframes(bool $force = false): string
    {
        if (self::$keyframes_output && !$force) {
            return '';
        }

        self::$keyframes_output = true;

        $animation = new Animation();
        return $animation->get_keyframes();
    }

    /**
     * Reset keyframes output flag (useful for testing)
     */
    public static function reset_keyframes_flag(): void
    {
        self::$keyframes_output = false;
    }

    /**
     * Get animation data attributes for scroll trigger
     *
     * @param array $attrs Module attributes
     * @return string HTML data attributes
     */
    public function get_animation_data_attributes(array $attrs): string
    {
        $animation_values = $attrs['animation'] ?? [];

        if (empty($animation_values) || empty($animation_values['type'])) {
            return '';
        }

        $animation = new Animation();
        return $animation->get_data_attributes($animation_values);
    }

    /**
     * Generate CSS for inner elements
     */
    protected function generate_inner_elements_css(string $selector, array $attrs): string
    {
        $css = '';
        $elements_data = $attrs['elements'] ?? [];

        foreach ($this->inner_elements as $element_key => $element_selector) {
            $element_attrs = $elements_data[$element_key] ?? [];
            if (empty($element_attrs)) {
                continue;
            }

            $full_selector = "{$selector} {$element_selector}";

            // Process normal state
            $normal = $element_attrs['normal'] ?? $element_attrs;
            if (!empty($normal) && is_array($normal)) {
                $css .= $this->generate_element_state_css($full_selector, $normal);
            }

            // Process hover state
            $hover = $element_attrs['hover'] ?? [];
            if (!empty($hover) && is_array($hover)) {
                $css .= $this->generate_element_state_css("{$full_selector}:hover", $hover);
            }

            // Process active state
            $active = $element_attrs['active'] ?? [];
            if (!empty($active) && is_array($active)) {
                $css .= $this->generate_element_state_css("{$full_selector}.active", $active);
            }
        }

        return $css;
    }

    /**
     * Process typography CSS for defined typography fields
     *
     * Uses the Typography class to generate CSS for each element
     *
     * @param string $selector Base CSS selector
     * @param array $attrs Module attributes
     * @return string Generated typography CSS
     */
    protected function process_typography_css(string $selector, array $attrs): string
    {
        $css = '';
        $typography = new Typography();

        // Frontend stores typography in design as {key}_typography
        $design_data = $attrs['design'] ?? [];

        foreach ($this->typography_fields as $element_key => $config) {
            // Get element-specific typography values from design tab
            // Frontend saves as: design.title_typography, design.subtitle_typography, etc.
            $element_values = $design_data[$element_key . '_typography'] ?? [];

            // If no values set, use defaults from config
            if (empty($element_values)) {
                $element_values = $config['defaults'] ?? [];
            } else {
                // Merge with defaults
                $defaults = $config['defaults'] ?? [];
                $element_values = array_replace_recursive($defaults, $element_values);
            }

            // Skip if no values
            if (empty($element_values)) {
                continue;
            }

            // Build full selector for this element
            $element_selector = $config['selector'] ?? '';
            if (empty($element_selector)) {
                continue;
            }

            $full_selector = "{$selector} {$element_selector}";

            // Generate CSS using Typography class
            $css .= $typography->generate_css($full_selector, $element_values);
        }

        return $css;
    }

    /**
     * Generate CSS for a single element state
     */
    protected function generate_element_state_css(string $selector, array $styles): string
    {
        $css_props = [];

        foreach ($styles as $prop => $value) {
            if ($value === '' || $value === null) {
                continue;
            }

            $css_prop = $this->to_css_property($prop);
            $css_props[] = "{$css_prop}: {$value}";
        }

        if (empty($css_props)) {
            return '';
        }

        return "{$selector} {\n  " . implode(";\n  ", $css_props) . ";\n}\n";
    }

    /**
     * Generate visibility CSS for responsive hiding
     */
    protected function generate_visibility_css(string $selector, array $attrs): string
    {
        $visibility = $attrs['visibility'] ?? null;
        if (!is_array($visibility)) {
            return '';
        }

        $css = '';

        // Hide on tablet
        if (isset($visibility['tablet']) && $visibility['tablet'] === false) {
            $css .= "@media (max-width: {$this->breakpoints['tablet']}px) and (min-width: " . ($this->breakpoints['phone'] + 1) . "px) {\n";
            $css .= "  {$selector} { display: none !important; }\n";
            $css .= "}\n";
        }

        // Hide on phone
        if (isset($visibility['phone']) && $visibility['phone'] === false) {
            $css .= "@media (max-width: {$this->breakpoints['phone']}px) {\n";
            $css .= "  {$selector} { display: none !important; }\n";
            $css .= "}\n";
        }

        // Hide on desktop
        if (isset($visibility['desktop']) && $visibility['desktop'] === false) {
            $css .= "@media (min-width: " . ($this->breakpoints['tablet'] + 1) . "px) {\n";
            $css .= "  {$selector} { display: none !important; }\n";
            $css .= "}\n";
        }

        return $css;
    }

    // =========================================================================
    // CSS PROPERTY PROCESSORS
    // =========================================================================

    protected function process_spacing_css(string $type, mixed $value, bool $responsive, array &$desktop, array &$tablet, array &$phone): void
    {
        if (!is_array($value)) {
            return;
        }

        if ($responsive && $this->has_responsive_value($value)) {
            foreach (['desktop', 'tablet', 'phone'] as $device) {
                $device_value = $value[$device] ?? [];
                if (!empty($device_value) && is_array($device_value)) {
                    $spacing_css = $this->build_spacing_value($type, $device_value);
                    if ($spacing_css) {
                        match ($device) {
                            'desktop' => $desktop[] = $spacing_css,
                            'tablet' => $tablet[] = $spacing_css,
                            'phone' => $phone[] = $spacing_css
                        };
                    }
                }
            }
        } else {
            $spacing_css = $this->build_spacing_value($type, $value);
            if ($spacing_css) {
                $desktop[] = $spacing_css;
            }
        }
    }

    protected function build_spacing_value(string $type, array $value): string
    {
        $top = $value['top'] ?? '';
        $right = $value['right'] ?? '';
        $bottom = $value['bottom'] ?? '';
        $left = $value['left'] ?? '';

        if ($top === '' && $right === '' && $bottom === '' && $left === '') {
            return '';
        }

        $top = $top !== '' ? $top : '0';
        $right = $right !== '' ? $right : '0';
        $bottom = $bottom !== '' ? $bottom : '0';
        $left = $left !== '' ? $left : '0';

        return "{$type}: {$top} {$right} {$bottom} {$left}";
    }

    protected function process_background_css(mixed $value, bool $has_hover, array &$desktop, array &$hover): void
    {
        if (!is_array($value)) {
            return;
        }

        $normal = $has_hover ? ($value['normal'] ?? $value) : $value;
        $hover_val = $has_hover ? ($value['hover'] ?? []) : [];

        // Normal state
        if (is_array($normal)) {
            $bg_css = $this->build_background_value($normal);
            if ($bg_css) {
                $desktop[] = $bg_css;
            }
        }

        // Hover state
        if (is_array($hover_val) && !empty(array_filter($hover_val))) {
            $bg_css = $this->build_background_value($hover_val);
            if ($bg_css) {
                $hover[] = $bg_css;
            }
        }
    }

    protected function build_background_value(array $value): string
    {
        $color = $value['color'] ?? '';
        $image = $value['image'] ?? '';

        if ($image !== '') {
            $size = $value['size'] ?? 'cover';
            $position = $value['position'] ?? 'center';
            return "background: url('{$image}') {$position}/{$size} no-repeat";
        }

        if ($color !== '') {
            return "background-color: {$color}";
        }

        return '';
    }

    protected function process_border_css(mixed $value, bool $responsive, bool $has_hover, array &$desktop, array &$tablet, array &$phone, array &$hover): void
    {
        if (!is_array($value)) {
            return;
        }

        $normal = $has_hover ? ($value['normal'] ?? $value) : $value;
        $hover_val = $has_hover ? ($value['hover'] ?? []) : [];

        if ($responsive && $this->has_responsive_value($normal)) {
            foreach (['desktop', 'tablet', 'phone'] as $device) {
                $device_value = $normal[$device] ?? [];
                if (!empty($device_value) && is_array($device_value)) {
                    $border_css = $this->build_border_value($device_value);
                    foreach ($border_css as $css) {
                        match ($device) {
                            'desktop' => $desktop[] = $css,
                            'tablet' => $tablet[] = $css,
                            'phone' => $phone[] = $css
                        };
                    }
                }
            }
        } else if (is_array($normal)) {
            $border_css = $this->build_border_value($normal);
            foreach ($border_css as $css) {
                $desktop[] = $css;
            }
        }

        // Hover state (desktop only for simplicity)
        if (is_array($hover_val)) {
            $check = $responsive ? ($hover_val['desktop'] ?? $hover_val) : $hover_val;
            if (is_array($check)) {
                $border_css = $this->build_border_value($check);
                foreach ($border_css as $css) {
                    $hover[] = $css;
                }
            }
        }
    }

    protected function build_border_value(array $value): array
    {
        $css = [];
        $width = $value['width'] ?? '';
        $style = $value['style'] ?? 'solid';
        $color = $value['color'] ?? '';
        $radius = $value['radius'] ?? '';

        if ($width !== '' && $color !== '') {
            $css[] = "border: {$width} {$style} {$color}";
        }

        if ($radius !== '') {
            $css[] = "border-radius: {$radius}";
        }

        return $css;
    }

    protected function process_box_shadow_css(mixed $value, bool $has_hover, array &$desktop, array &$hover): void
    {
        if (!is_array($value)) {
            return;
        }

        $normal = $has_hover ? ($value['normal'] ?? $value) : $value;
        $hover_val = $has_hover ? ($value['hover'] ?? []) : [];

        if (is_array($normal)) {
            $shadow_css = $this->build_box_shadow_value($normal);
            if ($shadow_css) {
                $desktop[] = $shadow_css;
            }
        }

        if (is_array($hover_val) && !empty(array_filter($hover_val))) {
            $shadow_css = $this->build_box_shadow_value($hover_val);
            if ($shadow_css) {
                $hover[] = $shadow_css;
            }
        }
    }

    protected function build_box_shadow_value(array $value): string
    {
        $h = $value['horizontal'] ?? '';
        $v = $value['vertical'] ?? '';
        $blur = $value['blur'] ?? '';
        $spread = $value['spread'] ?? '';
        $color = $value['color'] ?? '';

        if ($h === '' && $v === '' && $blur === '' && $color === '') {
            return '';
        }

        $h = $h !== '' ? $h : '0';
        $v = $v !== '' ? $v : '0';
        $blur = $blur !== '' ? $blur : '0';
        $spread = $spread !== '' ? $spread : '0';
        $color = $color !== '' ? $color : 'rgba(0,0,0,0.1)';

        return "box-shadow: {$h} {$v} {$blur} {$spread} {$color}";
    }

    protected function process_transform_css(mixed $value, bool $has_hover, array &$desktop, array &$hover): void
    {
        if (!is_array($value)) {
            return;
        }

        $normal = $has_hover ? ($value['normal'] ?? $value) : $value;
        $hover_val = $has_hover ? ($value['hover'] ?? []) : [];

        if (is_array($normal)) {
            $transform_css = $this->build_transform_value($normal);
            if ($transform_css) {
                $desktop[] = $transform_css;
            }
        }

        if (is_array($hover_val) && !empty(array_filter($hover_val))) {
            $transform_css = $this->build_transform_value($hover_val);
            if ($transform_css) {
                $hover[] = $transform_css;
            }
        }
    }

    protected function build_transform_value(array $value): string
    {
        $transforms = [];

        if (!empty($value['scale']) && $value['scale'] !== '1') {
            $transforms[] = "scale({$value['scale']})";
        }
        if (!empty($value['rotate'])) {
            $transforms[] = "rotate({$value['rotate']})";
        }
        if (!empty($value['translateX'])) {
            $transforms[] = "translateX({$value['translateX']})";
        }
        if (!empty($value['translateY'])) {
            $transforms[] = "translateY({$value['translateY']})";
        }

        if (empty($transforms)) {
            return '';
        }

        return "transform: " . implode(' ', $transforms);
    }

    protected function process_position_css(mixed $value, bool $responsive, array &$desktop, array &$tablet, array &$phone): void
    {
        if (!is_array($value)) {
            return;
        }

        if ($responsive && $this->has_responsive_value($value)) {
            foreach (['desktop', 'tablet', 'phone'] as $device) {
                $device_value = $value[$device] ?? [];
                if (!empty($device_value) && is_array($device_value)) {
                    $position_css = $this->build_position_value($device_value);
                    foreach ($position_css as $css) {
                        match ($device) {
                            'desktop' => $desktop[] = $css,
                            'tablet' => $tablet[] = $css,
                            'phone' => $phone[] = $css
                        };
                    }
                }
            }
        } else {
            $position_css = $this->build_position_value($value);
            foreach ($position_css as $css) {
                $desktop[] = $css;
            }
        }
    }

    protected function build_position_value(array $value): array
    {
        $css = [];
        $type = $value['type'] ?? '';

        if ($type !== '' && $type !== 'relative') {
            $css[] = "position: {$type}";

            if (!empty($value['top'])) {
                $css[] = "top: {$value['top']}";
            }
            if (!empty($value['right'])) {
                $css[] = "right: {$value['right']}";
            }
            if (!empty($value['bottom'])) {
                $css[] = "bottom: {$value['bottom']}";
            }
            if (!empty($value['left'])) {
                $css[] = "left: {$value['left']}";
            }
        }

        return $css;
    }

    // =========================================================================
    // UTILITY METHODS
    // =========================================================================

    /**
     * Convert camelCase or snake_case to CSS property
     */
    protected function to_css_property(string $prop): string
    {
        // Handle common mappings
        $map = [
            'font_size' => 'font-size',
            'font_weight' => 'font-weight',
            'font_family' => 'font-family',
            'line_height' => 'line-height',
            'letter_spacing' => 'letter-spacing',
            'text_align' => 'text-align',
            'text_transform' => 'text-transform',
            'text_decoration' => 'text-decoration',
            'background_color' => 'background-color',
            'border_radius' => 'border-radius',
            'border_width' => 'border-width',
            'border_style' => 'border-style',
            'border_color' => 'border-color',
            'box_shadow' => 'box-shadow',
            'z_index' => 'z-index',
            'max_width' => 'max-width',
            'min_width' => 'min-width',
            'max_height' => 'max-height',
            'min_height' => 'min-height'
        ];

        if (isset($map[$prop])) {
            return $map[$prop];
        }

        // Convert snake_case to kebab-case
        return str_replace('_', '-', $prop);
    }

    /**
     * Get wrapper attributes for render (css_id, css_class)
     */
    public function get_wrapper_attributes(array $attrs): array
    {
        $wrapper = [];

        if (!empty($attrs['css_id'])) {
            $wrapper['id'] = $attrs['css_id'];
        }

        $classes = ['tb4-module', 'tb4-' . $this->slug];
        if (!empty($attrs['css_class'])) {
            $classes[] = $attrs['css_class'];
        }
        $wrapper['class'] = implode(' ', $classes);

        return $wrapper;
    }

    /**
     * Build wrapper opening tag
     */
    public function render_wrapper_open(array $attrs, string $tag = 'div'): string
    {
        $wrapper = $this->get_wrapper_attributes($attrs);
        $id_attr = isset($wrapper['id']) ? ' id="' . esc_attr($wrapper['id']) . '"' : '';
        $class_attr = ' class="' . esc_attr($wrapper['class']) . '"';

        // Add animation data attributes if present
        $animation_attrs = $this->get_animation_data_attributes($attrs);
        $animation_attr = $animation_attrs ? ' ' . $animation_attrs : '';

        return "<{$tag}{$id_attr}{$class_attr}{$animation_attr}>";
    }

    /**
     * Build wrapper closing tag
     */
    public function render_wrapper_close(string $tag = 'div'): string
    {
        return "</{$tag}>";
    }
}

// =========================================================================
// HELPER FUNCTIONS
// =========================================================================

/**
 * Escape attribute value
 */
function esc_attr(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape HTML content
 */
function esc_html(string $text): string
{
    return htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
}