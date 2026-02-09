<?php
/**
 * Tabs Module (Parent)
 * Tabbed content container
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Tabs extends JTB_Element
{
    public string $icon = 'tabs';
    public string $category = 'interactive';
    public string $child_slug = 'tabs_item';

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
    protected string $module_prefix = 'tabs';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        // Active tab
        'active_tab_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-tab-button.jtb-active'
        ],
        'active_tab_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-tab-button.jtb-active'
        ],
        // Inactive tab
        'inactive_tab_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-tab-button'
        ],
        'inactive_tab_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-tab-button',
            'hover' => true
        ],
        // Border
        'tab_border_color' => [
            'property' => 'border-color',
            'selector' => '.jtb-tab-button, .jtb-tabs-content'
        ],
        // Body
        'body_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-tabs-content'
        ],
        // Typography
        'tab_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-tab-button',
            'unit' => 'px',
            'responsive' => true
        ]
    ];

    public function getSlug(): string
    {
        return 'tabs';
    }

    public function getName(): string
    {
        return 'Tabs';
    }

    public function getFields(): array
    {
        return [
            'active_tab_bg_color' => [
                'label' => 'Active Tab Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'active_tab_text_color' => [
                'label' => 'Active Tab Text Color',
                'type' => 'color',
                'default' => '#2ea3f2'
            ],
            'inactive_tab_bg_color' => [
                'label' => 'Inactive Tab Background',
                'type' => 'color',
                'default' => '#f4f4f4'
            ],
            'inactive_tab_text_color' => [
                'label' => 'Inactive Tab Text Color',
                'type' => 'color',
                'default' => '#666666',
                'hover' => true
            ],
            'tab_border_color' => [
                'label' => 'Tab Border Color',
                'type' => 'color',
                'default' => '#d9d9d9'
            ],
            'body_bg_color' => [
                'label' => 'Body Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'tab_font_size' => [
                'label' => 'Tab Font Size',
                'type' => 'range',
                'min' => 10,
                'max' => 30,
                'unit' => 'px',
                'default' => 14,
                'responsive' => true
            ],
            'nav_position' => [
                'label' => 'Tab Navigation Position',
                'type' => 'select',
                'options' => [
                    'top' => 'Top',
                    'left' => 'Left',
                    'bottom' => 'Bottom',
                    'right' => 'Right'
                ],
                'default' => 'top'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $navPosition = $attrs['nav_position'] ?? 'top';

        // Parse child tab items to extract nav
        // Content will be rendered by children
        $containerClass = 'jtb-tabs-container jtb-tabs-nav-' . $navPosition;

        $innerHtml = '<div class="' . $containerClass . '">';
        $innerHtml .= '<div class="jtb-tabs-nav" role="tablist"></div>'; // JS will populate this
        $innerHtml .= '<div class="jtb-tabs-content">' . $content . '</div>';
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Tabs module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Nav position special handling (changes flex direction)
        $navPosition = $attrs['nav_position'] ?? 'top';

        if ($navPosition === 'left' || $navPosition === 'right') {
            $css .= $selector . ' .jtb-tabs-container { display: flex; }' . "\n";
            if ($navPosition === 'right') {
                $css .= $selector . ' .jtb-tabs-container { flex-direction: row-reverse; }' . "\n";
            }
            $css .= $selector . ' .jtb-tabs-nav { flex-direction: column; min-width: 150px; }' . "\n";
        } else if ($navPosition === 'bottom') {
            $css .= $selector . ' .jtb-tabs-container { display: flex; flex-direction: column-reverse; }' . "\n";
        }

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('tabs', JTB_Module_Tabs::class);
