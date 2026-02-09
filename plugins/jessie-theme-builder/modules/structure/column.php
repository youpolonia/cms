<?php
/**
 * Column Module
 * Container for modules within a row
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Column extends JTB_Element
{
    public string $icon = 'column';
    public string $category = 'structure';

    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_typography = false;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'column';

    protected array $style_config = [
        'content_width' => [
            'property' => 'width',
            'selector' => '> *',
            'unit' => '%',
            'responsive' => true
        ]
    ];

    public function getSlug(): string
    {
        return 'column';
    }

    public function getName(): string
    {
        return 'Column';
    }

    public function getFields(): array
    {
        return [
            'vertical_align' => [
                'label' => 'Vertical Alignment',
                'type' => 'select',
                'options' => [
                    'default' => 'Default',
                    'flex-start' => 'Top',
                    'center' => 'Center',
                    'flex-end' => 'Bottom',
                    'stretch' => 'Stretch'
                ]
            ],
            'horizontal_align' => [
                'label' => 'Horizontal Alignment',
                'type' => 'select',
                'options' => [
                    'default' => 'Default',
                    'flex-start' => 'Left',
                    'center' => 'Center',
                    'flex-end' => 'Right'
                ]
            ],
            'content_width' => [
                'label' => 'Content Width',
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'unit' => '%',
                'responsive' => true,
                'description' => 'Width of content inside column'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $id = $attrs['_id'] ?? $this->generateId();

        // Column classes
        $classes = ['jtb-column'];

        if (!empty($attrs['css_class'])) {
            $classes[] = $this->esc($attrs['css_class']);
        }

        // Visibility classes
        if (!empty($attrs['disable_on_desktop'])) {
            $classes[] = 'jtb-hide-desktop';
        }
        if (!empty($attrs['disable_on_tablet'])) {
            $classes[] = 'jtb-hide-tablet';
        }
        if (!empty($attrs['disable_on_phone'])) {
            $classes[] = 'jtb-hide-phone';
        }

        // Animation classes
        if (!empty($attrs['animation_style']) && $attrs['animation_style'] !== 'none') {
            $classes[] = 'jtb-animated';
            $classes[] = 'jtb-animation-' . $this->esc($attrs['animation_style']);
        }

        $classStr = implode(' ', $classes);
        $idAttr = !empty($attrs['css_id']) ? $attrs['css_id'] : $id;

        $html = '<div id="' . $this->esc($idAttr) . '" class="' . $classStr . '">';
        $html .= $content;
        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';
        $rules = [];

        // Vertical alignment
        if (!empty($attrs['vertical_align']) && $attrs['vertical_align'] !== 'default') {
            $rules[] = 'display: flex';
            $rules[] = 'flex-direction: column';
            $rules[] = 'justify-content: ' . $attrs['vertical_align'];
        }

        // Horizontal alignment
        if (!empty($attrs['horizontal_align']) && $attrs['horizontal_align'] !== 'default') {
            if (empty($rules)) {
                $rules[] = 'display: flex';
                $rules[] = 'flex-direction: column';
            }
            $rules[] = 'align-items: ' . $attrs['horizontal_align'];
        }

        // Content width
        if (!empty($attrs['content_width'])) {
            $css .= $selector . ' > * { width: ' . (int) $attrs['content_width'] . '%; }' . "\n";
        }

        if (!empty($rules)) {
            $css .= $selector . ' { ' . implode('; ', $rules) . '; }' . "\n";
        }

        // Responsive content width
        if (!empty($attrs['content_width__tablet'])) {
            $css .= '@media (max-width: 980px) {' . "\n";
            $css .= '  ' . $selector . ' > * { width: ' . (int) $attrs['content_width__tablet'] . '%; }' . "\n";
            $css .= '}' . "\n";
        }

        if (!empty($attrs['content_width__phone'])) {
            $css .= '@media (max-width: 767px) {' . "\n";
            $css .= '  ' . $selector . ' > * { width: ' . (int) $attrs['content_width__phone'] . '%; }' . "\n";
            $css .= '}' . "\n";
        }

        // Parent CSS
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

// Register module
JTB_Registry::register('column', JTB_Module_Column::class);
