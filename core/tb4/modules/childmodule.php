<?php
/**
 * TB4 Child Module Base Class
 *
 * Base class for all child modules (AccordionItem, TabsItem, SliderItem, etc.)
 * Child modules are nested inside parent modules and cannot be used standalone.
 *
 * @package Core\TB4\Modules
 */

namespace Core\TB4\Modules;

require_once __DIR__ . '/module.php';

abstract class ChildModule extends Module
{
    /**
     * Module type - always 'child' for child modules
     */
    protected string $type = 'child';

    /**
     * Parent module slug that this child belongs to
     * Must be set by extending classes
     */
    protected ?string $parent_slug = null;

    /**
     * Field name used as the title in collapsed view
     */
    protected ?string $child_title_var = 'title';

    /**
     * Whether this child can be reordered within the parent
     */
    protected bool $sortable = true;

    /**
     * Minimum number of child items required
     */
    protected int $min_items = 0;

    /**
     * Maximum number of child items allowed (0 = unlimited)
     */
    protected int $max_items = 0;

    // =========================================================================
    // CHILD-SPECIFIC GETTERS
    // =========================================================================

    /**
     * Get the default title for this child item
     * Used when creating new items or when title field is empty
     */
    public function getDefaultTitle(): string
    {
        return $this->name . ' Item';
    }

    /**
     * Get fields that should be shown in collapsed/preview view
     * These fields are displayed in the parent module's item list
     */
    public function getPreviewFields(): array
    {
        return [$this->child_title_var];
    }

    /**
     * Check if this child module is sortable
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * Get minimum items required
     */
    public function getMinItems(): int
    {
        return $this->min_items;
    }

    /**
     * Get maximum items allowed
     */
    public function getMaxItems(): int
    {
        return $this->max_items;
    }

    // =========================================================================
    // PREVIEW & DISPLAY METHODS
    // =========================================================================

    /**
     * Get the preview/collapsed title for a child item
     *
     * @param array $attrs The child item attributes
     * @return string The title to display
     */
    public function getItemTitle(array $attrs): string
    {
        $title_field = $this->child_title_var;

        if ($title_field !== null && !empty($attrs[$title_field])) {
            $title = $attrs[$title_field];
            // Strip HTML and truncate if needed
            $title = strip_tags($title);
            if (mb_strlen($title) > 50) {
                $title = mb_substr($title, 0, 47) . '...';
            }
            return $title;
        }

        return $this->getDefaultTitle();
    }

    /**
     * Get preview data for collapsed view
     * Returns an array of field labels and values
     *
     * @param array $attrs The child item attributes
     * @return array Preview data as ['field_name' => 'display_value']
     */
    public function getPreviewData(array $attrs): array
    {
        $preview = [];
        $fields = $this->get_content_fields();

        foreach ($this->getPreviewFields() as $field_name) {
            if (isset($attrs[$field_name]) && $attrs[$field_name] !== '') {
                $label = $fields[$field_name]['label'] ?? ucfirst($field_name);
                $value = $attrs[$field_name];

                // Format value based on field type
                if (is_array($value)) {
                    $value = implode(', ', $value);
                } else {
                    $value = strip_tags((string)$value);
                    if (mb_strlen($value) > 100) {
                        $value = mb_substr($value, 0, 97) . '...';
                    }
                }

                $preview[$label] = $value;
            }
        }

        return $preview;
    }

    // =========================================================================
    // VALIDATION METHODS
    // =========================================================================

    /**
     * Validate child item attributes
     * Override in child classes for custom validation
     *
     * @param array $attrs The child item attributes
     * @return array Array of error messages (empty if valid)
     */
    public function validate(array $attrs): array
    {
        $errors = [];
        $fields = $this->get_content_fields();

        foreach ($fields as $field_name => $field_config) {
            $required = $field_config['required'] ?? false;
            $value = $attrs[$field_name] ?? null;

            if ($required && ($value === null || $value === '')) {
                $label = $field_config['label'] ?? $field_name;
                $errors[] = "{$label} is required";
            }
        }

        return $errors;
    }

    // =========================================================================
    // RENDER HELPER METHODS
    // =========================================================================

    /**
     * Get wrapper attributes for child item render
     * Adds child-specific classes
     */
    public function get_wrapper_attributes(array $attrs): array
    {
        $wrapper = parent::get_wrapper_attributes($attrs);

        // Add child-specific classes
        $classes = explode(' ', $wrapper['class']);
        $classes[] = 'tb4-child-item';

        if (!empty($this->parent_slug)) {
            $classes[] = 'tb4-child-of-' . $this->parent_slug;
        }

        $wrapper['class'] = implode(' ', $classes);

        return $wrapper;
    }

    /**
     * Render the child item with wrapper
     * Convenience method that wraps render() output
     *
     * @param array $attrs The child item attributes
     * @param int $index The item index within the parent
     * @return string The rendered HTML
     */
    public function renderWithWrapper(array $attrs, int $index = 0): string
    {
        $wrapper = $this->get_wrapper_attributes($attrs);

        $id_attr = isset($wrapper['id']) ? ' id="' . esc_attr($wrapper['id']) . '"' : '';
        $class_attr = ' class="' . esc_attr($wrapper['class']) . '"';
        $data_index = ' data-index="' . $index . '"';

        $html = '<div' . $id_attr . $class_attr . $data_index . '>';
        $html .= $this->render($attrs);
        $html .= '</div>';

        return $html;
    }

    // =========================================================================
    // JSON EXPORT
    // =========================================================================

    /**
     * Export child module definition as array for JSON serialization
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'type' => $this->type,
            'parent_slug' => $this->parent_slug,
            'child_title_var' => $this->child_title_var,
            'sortable' => $this->sortable,
            'min_items' => $this->min_items,
            'max_items' => $this->max_items,
            'preview_fields' => $this->getPreviewFields(),
            'content_fields' => $this->get_content_fields(),
            'design_fields' => $this->get_design_fields(),
            'advanced_fields' => $this->get_advanced_tab_fields(),
            'defaults' => $this->get_defaults()
        ];
    }
}
