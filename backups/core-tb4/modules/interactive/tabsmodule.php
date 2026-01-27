<?php
namespace Core\TB4\Modules\Interactive;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;

/**
 * TB 4.0 Tabs Module
 * Horizontal tabbed content display for organizing content into switchable panels
 */
class TabsModule extends Module
{
    protected string $type = 'parent';
    protected ?string $child_slug = 'tabs_item';

    public function __construct()
    {
        $this->name = 'Tabs';
        $this->slug = 'tabs';
        $this->icon = 'layout-list';
        $this->category = 'interactive';

        $this->elements = [
            'main' => '.tb4-tabs',
            'nav' => '.tb4-tabs-nav',
            'button' => '.tb4-tab-btn',
            'content' => '.tb4-tabs-content',
            'panel' => '.tb4-tab-panel'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'tab1_title' => [
                'label' => 'Tab 1 Title',
                'type' => 'text',
                'default' => 'Features'
            ],
            'tab1_content' => [
                'label' => 'Tab 1 Content',
                'type' => 'textarea',
                'default' => 'Discover our amazing features that help you build better websites faster than ever before.'
            ],
            'tab2_title' => [
                'label' => 'Tab 2 Title',
                'type' => 'text',
                'default' => 'Specifications'
            ],
            'tab2_content' => [
                'label' => 'Tab 2 Content',
                'type' => 'textarea',
                'default' => 'Technical specifications and requirements for optimal performance.'
            ],
            'tab3_title' => [
                'label' => 'Tab 3 Title',
                'type' => 'text',
                'default' => 'Reviews'
            ],
            'tab3_content' => [
                'label' => 'Tab 3 Content',
                'type' => 'textarea',
                'default' => 'See what our customers are saying about their experience.'
            ],
            'tab4_title' => [
                'label' => 'Tab 4 Title',
                'type' => 'text',
                'default' => ''
            ],
            'tab4_content' => [
                'label' => 'Tab 4 Content',
                'type' => 'textarea',
                'default' => ''
            ],
            'tab5_title' => [
                'label' => 'Tab 5 Title',
                'type' => 'text',
                'default' => ''
            ],
            'tab5_content' => [
                'label' => 'Tab 5 Content',
                'type' => 'textarea',
                'default' => ''
            ],
            'default_tab' => [
                'label' => 'Default Active Tab',
                'type' => 'select',
                'options' => [
                    '1' => 'Tab 1',
                    '2' => 'Tab 2',
                    '3' => 'Tab 3',
                    '4' => 'Tab 4',
                    '5' => 'Tab 5'
                ],
                'default' => '1'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'tab_layout' => [
                'label' => 'Tab Layout',
                'type' => 'select',
                'options' => [
                    'horizontal' => 'Horizontal',
                    'vertical' => 'Vertical'
                ],
                'default' => 'horizontal'
            ],
            'tab_alignment' => [
                'label' => 'Tab Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right',
                    'stretch' => 'Stretch/Full Width'
                ],
                'default' => 'left'
            ],
            'tab_bg_color' => [
                'label' => 'Tab Background',
                'type' => 'color',
                'default' => '#f3f4f6'
            ],
            'tab_active_bg' => [
                'label' => 'Active Tab Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'tab_text_color' => [
                'label' => 'Tab Text Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'tab_active_text' => [
                'label' => 'Active Tab Text',
                'type' => 'color',
                'default' => '#111827'
            ],
            'tab_font_size' => [
                'label' => 'Tab Font Size',
                'type' => 'text',
                'default' => '14px'
            ],
            'tab_font_weight' => [
                'label' => 'Tab Font Weight',
                'type' => 'select',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi Bold',
                    '700' => 'Bold'
                ],
                'default' => '500'
            ],
            'tab_padding' => [
                'label' => 'Tab Padding',
                'type' => 'text',
                'default' => '12px 24px'
            ],
            'content_bg_color' => [
                'label' => 'Content Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'content_text_color' => [
                'label' => 'Content Text Color',
                'type' => 'color',
                'default' => '#374151'
            ],
            'content_font_size' => [
                'label' => 'Content Font Size',
                'type' => 'text',
                'default' => '14px'
            ],
            'content_padding' => [
                'label' => 'Content Padding',
                'type' => 'text',
                'default' => '24px'
            ],
            'border_color' => [
                'label' => 'Border Color',
                'type' => 'color',
                'default' => '#e5e7eb'
            ],
            'border_radius' => [
                'label' => 'Border Radius',
                'type' => 'text',
                'default' => '8px'
            ],
            'active_indicator' => [
                'label' => 'Active Indicator',
                'type' => 'select',
                'options' => [
                    'bottom' => 'Bottom Border',
                    'background' => 'Background Only',
                    'top' => 'Top Border'
                ],
                'default' => 'bottom'
            ],
            'indicator_color' => [
                'label' => 'Indicator Color',
                'type' => 'color',
                'default' => '#2563eb'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    public function render(array $attrs): string
    {
        // Collect tabs from individual fields
        $tabs = [];
        for ($i = 1; $i <= 5; $i++) {
            $title = $attrs['tab' . $i . '_title'] ?? '';
            $tabContent = $attrs['tab' . $i . '_content'] ?? '';
            if (!empty(trim($title))) {
                $tabs[] = ['title' => $title, 'content' => $tabContent];
            }
        }

        // If no tabs, use defaults
        if (empty($tabs)) {
            $tabs = [
                ['title' => 'Features', 'content' => 'Discover our amazing features that help you build better websites faster than ever before.'],
                ['title' => 'Specifications', 'content' => 'Technical specifications and requirements for optimal performance.'],
                ['title' => 'Reviews', 'content' => 'See what our customers are saying about their experience.']
            ];
        }

        // Settings
        $defaultTab = (int)($attrs['default_tab'] ?? 1);
        $tabLayout = $attrs['tab_layout'] ?? 'horizontal';
        $tabAlignment = $attrs['tab_alignment'] ?? 'left';
        $tabBgColor = $attrs['tab_bg_color'] ?? '#f3f4f6';
        $tabActiveBg = $attrs['tab_active_bg'] ?? '#ffffff';
        $tabTextColor = $attrs['tab_text_color'] ?? '#6b7280';
        $tabActiveText = $attrs['tab_active_text'] ?? '#111827';
        $tabFontSize = $attrs['tab_font_size'] ?? '14px';
        $tabFontWeight = $attrs['tab_font_weight'] ?? '500';
        $tabPadding = $attrs['tab_padding'] ?? '12px 24px';
        $contentBgColor = $attrs['content_bg_color'] ?? '#ffffff';
        $contentTextColor = $attrs['content_text_color'] ?? '#374151';
        $contentFontSize = $attrs['content_font_size'] ?? '14px';
        $contentPadding = $attrs['content_padding'] ?? '24px';
        $borderColor = $attrs['border_color'] ?? '#e5e7eb';
        $borderRadius = $attrs['border_radius'] ?? '8px';
        $activeIndicator = $attrs['active_indicator'] ?? 'bottom';
        $indicatorColor = $attrs['indicator_color'] ?? '#2563eb';

        // Alignment classes
        $alignStyle = '';
        switch ($tabAlignment) {
            case 'center':
                $alignStyle = 'justify-content:center;';
                break;
            case 'right':
                $alignStyle = 'justify-content:flex-end;';
                break;
            case 'stretch':
                $alignStyle = '';
                break;
            default:
                $alignStyle = 'justify-content:flex-start;';
        }

        // Wrapper style
        $wrapperStyle = 'border:1px solid ' . esc_attr($borderColor) . ';border-radius:' . esc_attr($borderRadius) . ';overflow:hidden;';

        // Vertical layout wrapper
        if ($tabLayout === 'vertical') {
            $wrapperStyle .= 'display:flex;';
        }

        $html = '<div class="tb4-tabs" data-default="' . esc_attr($defaultTab) . '" style="' . $wrapperStyle . '">';

        // Tab navigation
        $navStyle = 'display:flex;background:' . esc_attr($tabBgColor) . ';';
        if ($tabLayout === 'horizontal') {
            $navStyle .= 'border-bottom:1px solid ' . esc_attr($borderColor) . ';' . $alignStyle;
        } else {
            $navStyle .= 'flex-direction:column;border-right:1px solid ' . esc_attr($borderColor) . ';min-width:150px;';
        }

        $html .= '<div class="tb4-tabs-nav" style="' . $navStyle . '">';

        foreach ($tabs as $index => $tab) {
            $tabIndex = $index + 1;
            $isActive = ($tabIndex === $defaultTab);
            $currentBg = $isActive ? $tabActiveBg : 'transparent';
            $currentColor = $isActive ? $tabActiveText : $tabTextColor;

            // Button style
            $btnStyle = 'padding:' . esc_attr($tabPadding) . ';';
            $btnStyle .= 'border:none;cursor:pointer;';
            $btnStyle .= 'font-size:' . esc_attr($tabFontSize) . ';font-weight:' . esc_attr($tabFontWeight) . ';';
            $btnStyle .= 'color:' . esc_attr($currentColor) . ';background:' . esc_attr($currentBg) . ';';
            $btnStyle .= 'position:relative;transition:all 0.2s;';

            if ($tabAlignment === 'stretch' && $tabLayout === 'horizontal') {
                $btnStyle .= 'flex:1;text-align:center;';
            }

            $activeClass = $isActive ? ' active' : '';

            $html .= '<button class="tb4-tab-btn' . $activeClass . '" data-tab="' . esc_attr($tabIndex) . '" style="' . $btnStyle . '">';
            $html .= esc_html($tab['title']);

            // Active indicator
            if ($isActive && $activeIndicator !== 'background') {
                $indicatorStyle = 'position:absolute;left:0;right:0;height:2px;background:' . esc_attr($indicatorColor) . ';';
                if ($activeIndicator === 'bottom') {
                    $indicatorStyle .= 'bottom:0;';
                } else {
                    $indicatorStyle .= 'top:0;';
                }
                if ($tabLayout === 'vertical') {
                    $indicatorStyle = 'position:absolute;top:0;bottom:0;width:2px;background:' . esc_attr($indicatorColor) . ';';
                    $indicatorStyle .= 'right:0;';
                }
                $html .= '<span class="tb4-tab-indicator" style="' . $indicatorStyle . '"></span>';
            }

            $html .= '</button>';
        }

        $html .= '</div>';

        // Tab content panels
        $contentWrapperStyle = 'padding:' . esc_attr($contentPadding) . ';background:' . esc_attr($contentBgColor) . ';';
        if ($tabLayout === 'vertical') {
            $contentWrapperStyle .= 'flex:1;';
        }

        $html .= '<div class="tb4-tabs-content" style="' . $contentWrapperStyle . '">';

        foreach ($tabs as $index => $tab) {
            $tabIndex = $index + 1;
            $isActive = ($tabIndex === $defaultTab);
            $panelStyle = 'color:' . esc_attr($contentTextColor) . ';font-size:' . esc_attr($contentFontSize) . ';line-height:1.6;';
            if (!$isActive) {
                $panelStyle .= 'display:none;';
            }

            $activeClass = $isActive ? ' active' : '';

            $html .= '<div class="tb4-tab-panel' . $activeClass . '" data-panel="' . esc_attr($tabIndex) . '" style="' . $panelStyle . '">';
            $html .= nl2br(esc_html($tab['content']));
            $html .= '</div>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
