<?php
/**
 * Fields Renderer
 * Renders form fields for the settings panel
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Fields
{
    private static bool $initialized = false;

    /**
     * Initialize the fields system
     */
    public static function init(): void
    {
        self::$initialized = true;
    }

    /**
     * Render a field by type
     */
    public static function render(string $type, string $name, array $config, $value): string
    {
        $method = 'render' . ucfirst($type);

        if (method_exists(self::class, $method)) {
            return self::$method($name, $config, $value);
        }

        return self::renderText($name, $config, $value);
    }

    /**
     * Render text input
     */
    public static function renderText(string $name, array $config, $value): string
    {
        $placeholder = self::esc($config['placeholder'] ?? '');
        $valueAttr = self::esc($value ?? $config['default'] ?? '');

        return '<input type="text"
            class="jtb-input-text"
            name="' . self::esc($name) . '"
            value="' . $valueAttr . '"
            placeholder="' . $placeholder . '"
            data-field="' . self::esc($name) . '">';
    }

    /**
     * Render textarea
     */
    public static function renderTextarea(string $name, array $config, $value): string
    {
        $rows = (int) ($config['rows'] ?? 4);
        $placeholder = self::esc($config['placeholder'] ?? '');
        $valueContent = self::esc($value ?? $config['default'] ?? '');

        return '<textarea
            class="jtb-input-textarea"
            name="' . self::esc($name) . '"
            rows="' . $rows . '"
            placeholder="' . $placeholder . '"
            data-field="' . self::esc($name) . '">' . $valueContent . '</textarea>';
    }

    /**
     * Render richtext editor
     */
    public static function renderRichtext(string $name, array $config, $value): string
    {
        $content = $value ?? $config['default'] ?? '';

        $html = '<div class="jtb-richtext-wrapper" data-field="' . self::esc($name) . '">';

        // Toolbar
        $html .= '<div class="jtb-richtext-toolbar">';
        $html .= '<button type="button" class="jtb-richtext-btn" data-command="bold" title="Bold"><strong>B</strong></button>';
        $html .= '<button type="button" class="jtb-richtext-btn" data-command="italic" title="Italic"><em>I</em></button>';
        $html .= '<button type="button" class="jtb-richtext-btn" data-command="underline" title="Underline"><u>U</u></button>';
        $html .= '<span class="jtb-toolbar-separator"></span>';
        $html .= '<button type="button" class="jtb-richtext-btn" data-command="insertUnorderedList" title="Bullet List">&#8226;</button>';
        $html .= '<button type="button" class="jtb-richtext-btn" data-command="insertOrderedList" title="Numbered List">1.</button>';
        $html .= '<span class="jtb-toolbar-separator"></span>';
        $html .= '<button type="button" class="jtb-richtext-btn" data-command="createLink" title="Insert Link">&#128279;</button>';
        $html .= '<button type="button" class="jtb-richtext-btn" data-command="unlink" title="Remove Link">&#10060;</button>';
        $html .= '</div>';

        // Editable area
        $html .= '<div class="jtb-richtext-content" contenteditable="true">' . $content . '</div>';

        // Hidden input for form submission
        $html .= '<input type="hidden" name="' . self::esc($name) . '" value="' . self::esc($content) . '">';

        $html .= '</div>';

        return $html;
    }

    /**
     * Render select dropdown
     */
    public static function renderSelect(string $name, array $config, $value): string
    {
        $options = $config['options'] ?? [];
        $currentValue = $value ?? $config['default'] ?? '';

        $html = '<select class="jtb-input-select" name="' . self::esc($name) . '" data-field="' . self::esc($name) . '">';

        foreach ($options as $optValue => $optLabel) {
            $selected = ($optValue == $currentValue) ? ' selected' : '';
            $html .= '<option value="' . self::esc($optValue) . '"' . $selected . '>' . self::esc($optLabel) . '</option>';
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * Render toggle switch
     */
    public static function renderToggle(string $name, array $config, $value): string
    {
        $checked = !empty($value) || (!isset($value) && !empty($config['default']));
        $checkedAttr = $checked ? ' checked' : '';

        $html = '<label class="jtb-toggle-switch">';
        $html .= '<input type="checkbox"
            name="' . self::esc($name) . '"
            value="1"' . $checkedAttr . '
            data-field="' . self::esc($name) . '">';
        $html .= '<span class="jtb-toggle-slider"></span>';
        $html .= '</label>';

        return $html;
    }

    /**
     * Render range slider
     */
    public static function renderRange(string $name, array $config, $value): string
    {
        $min = $config['min'] ?? 0;
        $max = $config['max'] ?? 100;
        $step = $config['step'] ?? 1;
        $unit = $config['unit'] ?? '';
        $currentValue = $value ?? $config['default'] ?? $min;

        $html = '<div class="jtb-range-wrapper">';

        $html .= '<input type="range"
            class="jtb-input-range"
            name="' . self::esc($name) . '_range"
            min="' . $min . '"
            max="' . $max . '"
            step="' . $step . '"
            value="' . self::esc($currentValue) . '"
            data-field="' . self::esc($name) . '">';

        $html .= '<input type="number"
            class="jtb-input-number"
            name="' . self::esc($name) . '"
            min="' . $min . '"
            max="' . $max . '"
            step="' . $step . '"
            value="' . self::esc($currentValue) . '"
            data-field="' . self::esc($name) . '">';

        if ($unit) {
            $html .= '<span class="jtb-range-unit">' . self::esc($unit) . '</span>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render color picker
     */
    public static function renderColor(string $name, array $config, $value): string
    {
        $currentValue = $value ?? $config['default'] ?? '#000000';

        $html = '<div class="jtb-color-wrapper">';

        $html .= '<input type="text"
            class="jtb-input-color-text"
            name="' . self::esc($name) . '"
            value="' . self::esc($currentValue) . '"
            data-field="' . self::esc($name) . '">';

        $html .= '<input type="color"
            class="jtb-input-color"
            value="' . self::esc($currentValue) . '">';

        $html .= '<div class="jtb-color-preview" style="background-color: ' . self::esc($currentValue) . ';"></div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Render file upload
     */
    public static function renderUpload(string $name, array $config, $value): string
    {
        $accept = $config['accept'] ?? 'image/*';
        $currentValue = $value ?? '';

        $html = '<div class="jtb-upload-wrapper" data-field="' . self::esc($name) . '">';

        $html .= '<input type="hidden" name="' . self::esc($name) . '" value="' . self::esc($currentValue) . '">';

        // Preview area
        $html .= '<div class="jtb-upload-preview">';
        if ($currentValue) {
            $html .= '<img src="' . self::esc($currentValue) . '" alt="Preview">';
        } else {
            $html .= '<div class="jtb-upload-placeholder">No image selected</div>';
        }
        $html .= '</div>';

        // Buttons
        $html .= '<div class="jtb-upload-buttons">';
        $html .= '<button type="button" class="jtb-btn jtb-upload-btn" data-accept="' . self::esc($accept) . '">Choose Image</button>';
        if ($currentValue) {
            $html .= '<button type="button" class="jtb-btn jtb-upload-remove">Remove</button>';
        }
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Render spacing control (margin/padding)
     */
    public static function renderSpacing(string $name, array $config, $value): string
    {
        $sides = $config['sides'] ?? ['top', 'right', 'bottom', 'left'];
        $unit = $config['unit'] ?? 'px';
        $values = is_array($value) ? $value : [];

        $html = '<div class="jtb-spacing-wrapper" data-field="' . self::esc($name) . '">';

        $html .= '<div class="jtb-spacing-inputs">';

        foreach ($sides as $side) {
            $sideValue = $values[$side] ?? 0;
            $label = ucfirst(str_replace('_', ' ', $side));

            $html .= '<div class="jtb-spacing-input">';
            $html .= '<label>' . self::esc($label) . '</label>';
            $html .= '<input type="number"
                name="' . self::esc($name) . '[' . self::esc($side) . ']"
                value="' . self::esc($sideValue) . '"
                data-side="' . self::esc($side) . '">';
            $html .= '</div>';
        }

        $html .= '</div>';

        // Link button
        $html .= '<button type="button" class="jtb-spacing-link" title="Link values">';
        $html .= '<span class="jtb-link-icon">&#128279;</span>';
        $html .= '</button>';

        // Unit display
        $html .= '<span class="jtb-spacing-unit">' . self::esc($unit) . '</span>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Render URL input
     */
    public static function renderUrl(string $name, array $config, $value): string
    {
        $currentValue = $value ?? $config['default'] ?? '';

        $html = '<div class="jtb-url-wrapper">';

        $html .= '<input type="url"
            class="jtb-input-url"
            name="' . self::esc($name) . '"
            value="' . self::esc($currentValue) . '"
            placeholder="https://"
            data-field="' . self::esc($name) . '">';

        $html .= '<button type="button" class="jtb-url-options" title="Link Options">&#9881;</button>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Render icon picker
     */
    public static function renderIcon(string $name, array $config, $value): string
    {
        $currentValue = $value ?? '';

        $html = '<div class="jtb-icon-wrapper" data-field="' . self::esc($name) . '">';

        $html .= '<input type="hidden" name="' . self::esc($name) . '" value="' . self::esc($currentValue) . '">';

        $html .= '<div class="jtb-icon-preview">';
        if ($currentValue) {
            $html .= '<span class="jtb-icon ' . self::esc($currentValue) . '"></span>';
        } else {
            $html .= '<span class="jtb-icon-placeholder">No icon</span>';
        }
        $html .= '</div>';

        $html .= '<button type="button" class="jtb-btn jtb-icon-choose">Choose Icon</button>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Escape HTML entities
     */
    private static function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
