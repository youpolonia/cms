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

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'accordion';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        // Icon
        'icon_color' => [
            'property' => 'color',
            'selector' => '.jtb-accordion-icon',
            'hover' => true
        ],
        'icon_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-accordion-icon',
            'unit' => 'px'
        ],
        // Open state
        'open_toggle_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-accordion-item.jtb-open .jtb-accordion-title'
        ],
        'open_toggle_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-accordion-item.jtb-open .jtb-accordion-header'
        ],
        // Closed state
        'closed_toggle_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-accordion-item:not(.jtb-open) .jtb-accordion-title'
        ],
        'closed_toggle_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-accordion-item:not(.jtb-open) .jtb-accordion-header'
        ],
        // Content
        'content_padding' => [
            'property' => 'padding',
            'selector' => '.jtb-accordion-content'
        ],
        'content_background' => [
            'property' => 'background-color',
            'selector' => '.jtb-accordion-content'
        ]
    ];

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
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

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

    /**
     * Generate CSS for Accordion module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Icon position special handling (changes flex direction)
        if (($attrs['toggle_icon_position'] ?? 'right') === 'left') {
            $css .= $selector . ' .jtb-accordion-header { flex-direction: row-reverse; justify-content: flex-end; }' . "\n";
            $css .= $selector . ' .jtb-accordion-title { margin-left: 15px; }' . "\n";
        }

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('accordion', JTB_Module_Accordion::class);
