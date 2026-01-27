<?php
/**
 * Settings Panel Renderer
 * Renders settings panel HTML for modules
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Settings
{
    /**
     * Render complete settings panel for a module
     */
    public static function renderPanel(JTB_Element $module, array $attrs = []): string
    {
        $html = '<div class="jtb-settings-panel-content" data-module="' . self::esc($module->getSlug()) . '">';

        // Header
        $html .= '<div class="jtb-settings-header">';
        $html .= '<span class="jtb-settings-icon">' . self::esc($module->icon) . '</span>';
        $html .= '<span class="jtb-settings-title">' . self::esc($module->getName()) . ' Settings</span>';
        $html .= '<button type="button" class="jtb-settings-close">&times;</button>';
        $html .= '</div>';

        // Tabs
        $html .= '<div class="jtb-settings-tabs">';
        $html .= '<button type="button" class="jtb-tab active" data-tab="content">Content</button>';
        $html .= '<button type="button" class="jtb-tab" data-tab="design">Design</button>';
        $html .= '<button type="button" class="jtb-tab" data-tab="advanced">Advanced</button>';
        $html .= '</div>';

        // Tab contents
        $html .= '<div class="jtb-settings-body">';

        // Content tab
        $html .= '<div class="jtb-tab-content active" data-tab="content">';
        $html .= self::renderContentTab($module, $attrs);
        $html .= '</div>';

        // Design tab
        $html .= '<div class="jtb-tab-content" data-tab="design">';
        $html .= self::renderDesignTab($module, $attrs);
        $html .= '</div>';

        // Advanced tab
        $html .= '<div class="jtb-tab-content" data-tab="advanced">';
        $html .= self::renderAdvancedTab($module, $attrs);
        $html .= '</div>';

        $html .= '</div>'; // .jtb-settings-body

        $html .= '</div>'; // .jtb-settings-panel-content

        return $html;
    }

    /**
     * Render content tab
     */
    public static function renderContentTab(JTB_Element $module, array $attrs): string
    {
        $fields = $module->getContentFields();
        return self::renderFields($fields, $attrs);
    }

    /**
     * Render design tab
     */
    public static function renderDesignTab(JTB_Element $module, array $attrs): string
    {
        $fields = $module->getDesignFields();
        return self::renderFields($fields, $attrs);
    }

    /**
     * Render advanced tab
     */
    public static function renderAdvancedTab(JTB_Element $module, array $attrs): string
    {
        $fields = $module->getAdvancedFields();
        return self::renderFields($fields, $attrs);
    }

    /**
     * Render a set of fields
     */
    public static function renderFields(array $fields, array $attrs): string
    {
        $html = '';

        foreach ($fields as $name => $field) {
            // Check conditions
            if (!self::checkConditions($field, $attrs)) {
                continue;
            }

            $type = $field['type'] ?? 'text';

            // Handle toggle groups
            if ($type === 'group' && !empty($field['toggle'])) {
                $html .= self::renderToggleGroup($name, $field, $attrs);
            } elseif ($type === 'group') {
                // Non-toggle group - just render fields
                $html .= '<div class="jtb-field-group">';
                if (!empty($field['label'])) {
                    $html .= '<div class="jtb-group-label">' . self::esc($field['label']) . '</div>';
                }
                $html .= self::renderFields($field['fields'] ?? [], $attrs);
                $html .= '</div>';
            } else {
                // Regular field
                $value = $attrs[$name] ?? null;
                $html .= self::renderField($name, $field, $value, $attrs);
            }
        }

        return $html;
    }

    /**
     * Render a toggle group (collapsible section)
     */
    public static function renderToggleGroup(string $name, array $group, array $attrs): string
    {
        $label = $group['label'] ?? ucfirst($name);
        $fields = $group['fields'] ?? [];

        $html = '<div class="jtb-toggle-group" data-group="' . self::esc($name) . '">';

        // Header
        $html .= '<div class="jtb-toggle-header">';
        $html .= '<span class="jtb-toggle-icon"></span>';
        $html .= '<span class="jtb-toggle-label">' . self::esc($label) . '</span>';
        $html .= '</div>';

        // Content
        $html .= '<div class="jtb-toggle-content">';
        $html .= self::renderFields($fields, $attrs);
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Render a single field
     */
    public static function renderField(string $name, array $field, $value, array $attrs): string
    {
        $label = $field['label'] ?? ucfirst(str_replace('_', ' ', $name));
        $type = $field['type'] ?? 'text';
        $description = $field['description'] ?? '';
        $responsive = !empty($field['responsive']);
        $hover = !empty($field['hover']);

        $html = '<div class="jtb-field" data-field-name="' . self::esc($name) . '"';

        // Add condition data attributes
        if (!empty($field['show_if'])) {
            $html .= ' data-show-if="' . self::esc(json_encode($field['show_if'])) . '"';
        }
        if (!empty($field['show_if_not'])) {
            $html .= ' data-show-if-not="' . self::esc(json_encode($field['show_if_not'])) . '"';
        }

        $html .= '>';

        // Field header
        $html .= '<div class="jtb-field-header">';
        $html .= '<label class="jtb-field-label">' . self::esc($label) . '</label>';

        // Toggles (responsive/hover)
        if ($responsive || $hover) {
            $html .= '<div class="jtb-field-toggles">';

            if ($responsive) {
                $html .= '<button type="button" class="jtb-responsive-toggle active" data-device="desktop" title="Desktop">&#128187;</button>';
                $html .= '<button type="button" class="jtb-responsive-toggle" data-device="tablet" title="Tablet">&#128193;</button>';
                $html .= '<button type="button" class="jtb-responsive-toggle" data-device="phone" title="Phone">&#128241;</button>';
            }

            if ($hover) {
                $html .= '<button type="button" class="jtb-hover-toggle" data-state="normal" title="Hover State">&#128065;</button>';
            }

            $html .= '</div>';
        }

        $html .= '</div>'; // .jtb-field-header

        // Field input
        $html .= '<div class="jtb-field-input">';
        $html .= JTB_Fields::render($type, $name, $field, $value);
        $html .= '</div>';

        // Description
        if ($description) {
            $html .= '<div class="jtb-field-description">' . self::esc($description) . '</div>';
        }

        $html .= '</div>'; // .jtb-field

        return $html;
    }

    /**
     * Check field conditions
     */
    private static function checkConditions(array $field, array $attrs): bool
    {
        // Check show_if conditions
        if (!empty($field['show_if'])) {
            foreach ($field['show_if'] as $condField => $condValue) {
                $actualValue = $attrs[$condField] ?? '';

                if (is_array($condValue)) {
                    if (!in_array($actualValue, $condValue)) {
                        return false;
                    }
                } else {
                    if ($actualValue != $condValue) {
                        return false;
                    }
                }
            }
        }

        // Check show_if_not conditions
        if (!empty($field['show_if_not'])) {
            foreach ($field['show_if_not'] as $condField => $condValue) {
                $actualValue = $attrs[$condField] ?? '';

                if (is_array($condValue)) {
                    if (in_array($actualValue, $condValue)) {
                        return false;
                    }
                } else {
                    if ($actualValue == $condValue) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Escape HTML entities
     */
    private static function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
