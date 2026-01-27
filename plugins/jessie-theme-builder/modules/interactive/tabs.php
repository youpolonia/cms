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

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $navPosition = $attrs['nav_position'] ?? 'top';

        // Container flex direction based on nav position
        if ($navPosition === 'left' || $navPosition === 'right') {
            $css .= $selector . ' .jtb-tabs-container { display: flex; }' . "\n";
            if ($navPosition === 'right') {
                $css .= $selector . ' .jtb-tabs-container { flex-direction: row-reverse; }' . "\n";
            }
            $css .= $selector . ' .jtb-tabs-nav { flex-direction: column; min-width: 150px; }' . "\n";
        } else if ($navPosition === 'bottom') {
            $css .= $selector . ' .jtb-tabs-container { display: flex; flex-direction: column-reverse; }' . "\n";
        }

        // Tab navigation
        $css .= $selector . ' .jtb-tabs-nav { display: flex; list-style: none; margin: 0; padding: 0; }' . "\n";

        // Tab button
        $inactiveBg = $attrs['inactive_tab_bg_color'] ?? '#f4f4f4';
        $inactiveText = $attrs['inactive_tab_text_color'] ?? '#666666';
        $borderColor = $attrs['tab_border_color'] ?? '#d9d9d9';
        $tabFontSize = $attrs['tab_font_size'] ?? 14;

        $css .= $selector . ' .jtb-tab-button { ';
        $css .= 'background-color: ' . $inactiveBg . '; ';
        $css .= 'color: ' . $inactiveText . '; ';
        $css .= 'border: 1px solid ' . $borderColor . '; ';
        $css .= 'padding: 12px 20px; ';
        $css .= 'cursor: pointer; ';
        $css .= 'font-size: ' . $tabFontSize . 'px; ';
        $css .= 'transition: all 0.3s ease; ';
        $css .= 'border-bottom: none; ';
        $css .= 'margin-right: -1px; ';
        $css .= '}' . "\n";

        // Active tab
        $activeBg = $attrs['active_tab_bg_color'] ?? '#ffffff';
        $activeText = $attrs['active_tab_text_color'] ?? '#2ea3f2';

        $css .= $selector . ' .jtb-tab-button.jtb-active { ';
        $css .= 'background-color: ' . $activeBg . '; ';
        $css .= 'color: ' . $activeText . '; ';
        $css .= 'border-bottom-color: ' . $activeBg . '; ';
        $css .= 'position: relative; ';
        $css .= 'z-index: 1; ';
        $css .= '}' . "\n";

        // Hover for inactive
        if (!empty($attrs['inactive_tab_text_color__hover'])) {
            $css .= $selector . ' .jtb-tab-button:not(.jtb-active):hover { color: ' . $attrs['inactive_tab_text_color__hover'] . '; }' . "\n";
        }

        // Tab content area
        $bodyBg = $attrs['body_bg_color'] ?? '#ffffff';
        $css .= $selector . ' .jtb-tabs-content { ';
        $css .= 'background-color: ' . $bodyBg . '; ';
        $css .= 'border: 1px solid ' . $borderColor . '; ';
        $css .= 'padding: 20px; ';
        $css .= '}' . "\n";

        // Tab panels
        $css .= $selector . ' .jtb-tab-panel { display: none; }' . "\n";
        $css .= $selector . ' .jtb-tab-panel.jtb-active { display: block; }' . "\n";

        // Responsive font size
        if (!empty($attrs['tab_font_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-tab-button { font-size: ' . $attrs['tab_font_size__tablet'] . 'px; } }' . "\n";
        }
        if (!empty($attrs['tab_font_size__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-tab-button { font-size: ' . $attrs['tab_font_size__phone'] . 'px; } }' . "\n";
        }

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('tabs', JTB_Module_Tabs::class);
