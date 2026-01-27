<?php
/**
 * Accordion Module (Parent)
 * Collapsible content sections
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Accordion extends JTB_Element
{
    public string $icon = 'accordion';
    public string $category = 'interactive';
    public string $child_slug = 'accordion_item';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    public function getSlug(): string
    {
        return 'accordion';
    }

    public function getName(): string
    {
        return 'Accordion';
    }

    public function getFields(): array
    {
        return [
            'open_toggle_text_color' => [
                'label' => 'Open Title Text Color',
                'type' => 'color',
                'hover' => true
            ],
            'open_toggle_bg_color' => [
                'label' => 'Open Toggle Background',
                'type' => 'color',
                'hover' => true
            ],
            'closed_toggle_text_color' => [
                'label' => 'Closed Title Text Color',
                'type' => 'color',
                'hover' => true
            ],
            'closed_toggle_bg_color' => [
                'label' => 'Closed Toggle Background',
                'type' => 'color',
                'hover' => true
            ],
            'icon_color' => [
                'label' => 'Icon Color',
                'type' => 'color',
                'default' => '#666666',
                'hover' => true
            ],
            'icon_size' => [
                'label' => 'Icon Size',
                'type' => 'range',
                'min' => 10,
                'max' => 40,
                'unit' => 'px',
                'default' => 16
            ],
            'toggle_header_level' => [
                'label' => 'Title Heading Level',
                'type' => 'select',
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6'
                ],
                'default' => 'h5'
            ],
            'toggle_icon' => [
                'label' => 'Toggle Icon Style',
                'type' => 'select',
                'options' => [
                    'arrow' => 'Arrow',
                    'plus' => 'Plus/Minus',
                    'none' => 'None'
                ],
                'default' => 'arrow'
            ],
            'toggle_icon_position' => [
                'label' => 'Icon Position',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'right' => 'Right'
                ],
                'default' => 'right'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $iconStyle = $attrs['toggle_icon'] ?? 'arrow';
        $iconPosition = $attrs['toggle_icon_position'] ?? 'right';

        $containerClass = 'jtb-accordion-container';
        $containerClass .= ' jtb-accordion-icon-' . $iconStyle;
        $containerClass .= ' jtb-accordion-icon-pos-' . $iconPosition;

        $innerHtml = '<div class="' . $containerClass . '" data-toggle-icon="' . $this->esc($iconStyle) . '">';
        $innerHtml .= $content; // Child accordion items
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Base accordion styles
        $css .= $selector . ' .jtb-accordion-item { margin-bottom: 0; border: 1px solid #d9d9d9; }' . "\n";
        $css .= $selector . ' .jtb-accordion-item:not(:first-child) { border-top: none; }' . "\n";

        // Toggle header
        $css .= $selector . ' .jtb-accordion-header { ';
        $css .= 'display: flex; ';
        $css .= 'align-items: center; ';
        $css .= 'padding: 15px 20px; ';
        $css .= 'cursor: pointer; ';
        $css .= 'transition: all 0.3s ease; ';
        $css .= '}' . "\n";

        // Icon position
        if (($attrs['toggle_icon_position'] ?? 'right') === 'right') {
            $css .= $selector . ' .jtb-accordion-header { justify-content: space-between; }' . "\n";
        } else {
            $css .= $selector . ' .jtb-accordion-header { flex-direction: row-reverse; justify-content: flex-end; }' . "\n";
            $css .= $selector . ' .jtb-accordion-title { margin-left: 15px; }' . "\n";
        }

        // Title
        $css .= $selector . ' .jtb-accordion-title { margin: 0; flex-grow: 1; }' . "\n";

        // Icon
        $iconColor = $attrs['icon_color'] ?? '#666666';
        $iconSize = $attrs['icon_size'] ?? 16;
        $css .= $selector . ' .jtb-accordion-icon { ';
        $css .= 'color: ' . $iconColor . '; ';
        $css .= 'font-size: ' . $iconSize . 'px; ';
        $css .= 'transition: transform 0.3s ease; ';
        $css .= '}' . "\n";

        // Icon rotation when open
        $css .= $selector . ' .jtb-accordion-item.jtb-open .jtb-accordion-icon { transform: rotate(180deg); }' . "\n";
        $css .= $selector . '.jtb-accordion-icon-plus .jtb-accordion-item.jtb-open .jtb-accordion-icon { transform: rotate(45deg); }' . "\n";

        // Content area
        $css .= $selector . ' .jtb-accordion-content { ';
        $css .= 'display: none; ';
        $css .= 'padding: 20px; ';
        $css .= 'border-top: 1px solid #d9d9d9; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-accordion-item.jtb-open .jtb-accordion-content { display: block; }' . "\n";

        // Open toggle styles
        if (!empty($attrs['open_toggle_text_color'])) {
            $css .= $selector . ' .jtb-accordion-item.jtb-open .jtb-accordion-title { color: ' . $attrs['open_toggle_text_color'] . '; }' . "\n";
        }
        if (!empty($attrs['open_toggle_bg_color'])) {
            $css .= $selector . ' .jtb-accordion-item.jtb-open .jtb-accordion-header { background-color: ' . $attrs['open_toggle_bg_color'] . '; }' . "\n";
        }

        // Closed toggle styles
        if (!empty($attrs['closed_toggle_text_color'])) {
            $css .= $selector . ' .jtb-accordion-item:not(.jtb-open) .jtb-accordion-title { color: ' . $attrs['closed_toggle_text_color'] . '; }' . "\n";
        }
        if (!empty($attrs['closed_toggle_bg_color'])) {
            $css .= $selector . ' .jtb-accordion-item:not(.jtb-open) .jtb-accordion-header { background-color: ' . $attrs['closed_toggle_bg_color'] . '; }' . "\n";
        }

        // Hover states
        if (!empty($attrs['icon_color__hover'])) {
            $css .= $selector . ' .jtb-accordion-header:hover .jtb-accordion-icon { color: ' . $attrs['icon_color__hover'] . '; }' . "\n";
        }

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('accordion', JTB_Module_Accordion::class);
