<?php
/**
 * Divider Module
 * Horizontal divider/separator line
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Divider extends JTB_Element
{
    public string $icon = 'minus';
    public string $category = 'content';

    public bool $use_typography = false;
    public bool $use_background = false;
    public bool $use_spacing = true;
    public bool $use_border = false;
    public bool $use_box_shadow = false;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'divider';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'height' => [
            'property' => 'height',
            'selector' => '',
            'unit' => 'px',
            'responsive' => true
        ],
        'max_width' => [
            'property' => 'max-width',
            'selector' => '',
            'unit' => '%',
            'responsive' => true
        ],
        'divider_color' => [
            'property' => 'border-color',
            'selector' => '.jtb-divider-line',
            'hover' => true
        ]
    ];

    public function getSlug(): string
    {
        return 'divider';
    }

    public function getName(): string
    {
        return 'Divider';
    }

    public function getFields(): array
    {
        return [
            'show_divider' => [
                'label' => 'Show Divider',
                'type' => 'toggle',
                'default' => true
            ],
            'divider_style' => [
                'label' => 'Divider Style',
                'type' => 'select',
                'options' => [
                    'solid' => 'Solid',
                    'dashed' => 'Dashed',
                    'dotted' => 'Dotted',
                    'double' => 'Double',
                    'groove' => 'Groove',
                    'ridge' => 'Ridge'
                ],
                'default' => 'solid',
                'show_if' => ['show_divider' => true]
            ],
            'divider_position' => [
                'label' => 'Divider Position',
                'type' => 'select',
                'options' => [
                    'top' => 'Top',
                    'center' => 'Center',
                    'bottom' => 'Bottom'
                ],
                'default' => 'center',
                'show_if' => ['show_divider' => true]
            ],
            'divider_weight' => [
                'label' => 'Divider Weight',
                'type' => 'range',
                'min' => 1,
                'max' => 20,
                'unit' => 'px',
                'default' => 1,
                'show_if' => ['show_divider' => true]
            ],
            'divider_color' => [
                'label' => 'Divider Color',
                'type' => 'color',
                'default' => '#e0e0e0',
                'show_if' => ['show_divider' => true],
                'hover' => true
            ],
            'height' => [
                'label' => 'Height',
                'type' => 'range',
                'min' => 1,
                'max' => 200,
                'unit' => 'px',
                'default' => 23,
                'responsive' => true
            ],
            'max_width' => [
                'label' => 'Max Width',
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'unit' => '%',
                'default' => 100,
                'responsive' => true
            ],
            'divider_alignment' => [
                'label' => 'Module Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center',
                'responsive' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $showDivider = $attrs['show_divider'] ?? true;

        $innerHtml = '<div class="jtb-divider-inner">';
        if ($showDivider) {
            $innerHtml .= '<span class="jtb-divider-line"></span>';
        }
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Divider module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';
        $showDivider = $attrs['show_divider'] ?? true;

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Alignment (special handling)
        $alignment = $attrs['divider_alignment'] ?? 'center';
        if ($alignment === 'center') {
            $css .= $selector . ' { margin-left: auto; margin-right: auto; }' . "\n";
        } elseif ($alignment === 'right') {
            $css .= $selector . ' { margin-left: auto; margin-right: 0; }' . "\n";
        }

        if ($showDivider) {
            $style = $attrs['divider_style'] ?? 'solid';
            $weight = $attrs['divider_weight'] ?? 1;
            $color = $attrs['divider_color'] ?? '#e0e0e0';
            $position = $attrs['divider_position'] ?? 'center';

            $css .= $selector . ' .jtb-divider-inner { display: flex; height: 100%; }' . "\n";

            if ($position === 'top') {
                $css .= $selector . ' .jtb-divider-inner { align-items: flex-start; }' . "\n";
            } elseif ($position === 'bottom') {
                $css .= $selector . ' .jtb-divider-inner { align-items: flex-end; }' . "\n";
            } else {
                $css .= $selector . ' .jtb-divider-inner { align-items: center; }' . "\n";
            }

            $css .= $selector . ' .jtb-divider-line { width: 100%; border-top: ' . $weight . 'px ' . $style . ' ' . $color . '; }' . "\n";
        }

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('divider', JTB_Module_Divider::class);
