<?php
namespace Core\TB4\Modules\Interactive;

require_once __DIR__ . '/../childmodule.php';

use Core\TB4\Modules\ChildModule;

/**
 * TB 4.0 Tabs Item Module (Child Module)
 * Individual tab with title/label and content panel.
 * Must be nested inside TabsModule parent.
 */
class TabsItemModule extends ChildModule
{
    protected ?string $parent_slug = 'tabs';
    protected ?string $child_title_var = 'title';

    public function __construct()
    {
        $this->name = 'Tab Item';
        $this->slug = 'tabs_item';
        $this->icon = 'layout';
        $this->category = 'interactive';
    }

    public function get_content_fields(): array
    {
        return [
            'title' => [
                'type' => 'text',
                'label' => 'Tab Title',
                'default' => 'Tab'
            ],
            'content' => [
                'type' => 'wysiwyg',
                'label' => 'Tab Content',
                'default' => 'Tab content goes here...'
            ],
            'icon' => [
                'type' => 'select',
                'label' => 'Tab Icon (Optional)',
                'options' => [
                    'none' => 'None',
                    'home' => 'Home',
                    'user' => 'User',
                    'settings' => 'Settings',
                    'info' => 'Info',
                    'star' => 'Star',
                    'heart' => 'Heart',
                    'file' => 'File',
                    'folder' => 'Folder',
                    'image' => 'Image',
                    'video' => 'Video',
                    'music' => 'Music',
                    'mail' => 'Mail',
                    'phone' => 'Phone',
                    'map-pin' => 'Map Pin',
                    'calendar' => 'Calendar',
                    'clock' => 'Clock',
                    'check' => 'Check',
                    'x' => 'X',
                    'alert-circle' => 'Alert',
                    'help-circle' => 'Help'
                ],
                'default' => 'none'
            ],
            'icon_position' => [
                'type' => 'select',
                'label' => 'Icon Position',
                'options' => [
                    'left' => 'Left of Text',
                    'right' => 'Right of Text',
                    'top' => 'Above Text'
                ],
                'default' => 'left'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'title_font_size' => [
                'type' => 'text',
                'label' => 'Title Font Size',
                'default' => '14px'
            ],
            'title_font_weight' => [
                'type' => 'select',
                'label' => 'Title Font Weight',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi Bold',
                    '700' => 'Bold'
                ],
                'default' => '500'
            ],
            'title_color' => [
                'type' => 'color',
                'label' => 'Title Color',
                'default' => '#6b7280'
            ],
            'title_color_active' => [
                'type' => 'color',
                'label' => 'Title Color (Active)',
                'default' => '#2563eb'
            ],
            'title_color_hover' => [
                'type' => 'color',
                'label' => 'Title Color (Hover)',
                'default' => '#374151'
            ],
            'content_font_size' => [
                'type' => 'text',
                'label' => 'Content Font Size',
                'default' => '14px'
            ],
            'content_color' => [
                'type' => 'color',
                'label' => 'Content Color',
                'default' => '#4b5563'
            ],
            'content_line_height' => [
                'type' => 'text',
                'label' => 'Content Line Height',
                'default' => '1.6'
            ],
            'content_padding' => [
                'type' => 'text',
                'label' => 'Content Padding',
                'default' => '24px'
            ],
            'icon_size' => [
                'type' => 'text',
                'label' => 'Icon Size',
                'default' => '16px'
            ],
            'icon_color' => [
                'type' => 'color',
                'label' => 'Icon Color',
                'default' => '#6b7280'
            ],
            'icon_color_active' => [
                'type' => 'color',
                'label' => 'Icon Color (Active)',
                'default' => '#2563eb'
            ],
            'tab_background' => [
                'type' => 'color',
                'label' => 'Tab Background',
                'default' => 'transparent'
            ],
            'tab_background_active' => [
                'type' => 'color',
                'label' => 'Tab Background (Active)',
                'default' => '#ffffff'
            ],
            'tab_background_hover' => [
                'type' => 'color',
                'label' => 'Tab Background (Hover)',
                'default' => '#f3f4f6'
            ]
        ];
    }

    public function render(array $attrs): string
    {
        // Content fields
        $title = $attrs['title'] ?? 'Tab';
        $content = $attrs['content'] ?? 'Tab content goes here...';
        $icon = $attrs['icon'] ?? 'none';
        $iconPosition = $attrs['icon_position'] ?? 'left';

        // Design fields
        $titleFontSize = $attrs['title_font_size'] ?? '14px';
        $titleFontWeight = $attrs['title_font_weight'] ?? '500';
        $titleColor = $attrs['title_color'] ?? '#6b7280';
        $titleColorActive = $attrs['title_color_active'] ?? '#2563eb';
        $contentFontSize = $attrs['content_font_size'] ?? '14px';
        $contentColor = $attrs['content_color'] ?? '#4b5563';
        $contentLineHeight = $attrs['content_line_height'] ?? '1.6';
        $contentPadding = $attrs['content_padding'] ?? '24px';
        $iconSize = $attrs['icon_size'] ?? '16px';
        $iconColor = $attrs['icon_color'] ?? '#6b7280';
        $iconColorActive = $attrs['icon_color_active'] ?? '#2563eb';
        $tabBackground = $attrs['tab_background'] ?? 'transparent';
        $tabBackgroundActive = $attrs['tab_background_active'] ?? '#ffffff';
        $tabBackgroundHover = $attrs['tab_background_hover'] ?? '#f3f4f6';

        // Determine if this is the active tab (first item is active by default)
        $isActive = !empty($attrs['_is_active']);
        $activeClass = $isActive ? ' active' : '';
        $currentTitleColor = $isActive ? $titleColorActive : $titleColor;
        $currentIconColor = $isActive ? $iconColorActive : $iconColor;
        $currentBackground = $isActive ? $tabBackgroundActive : $tabBackground;
        $contentDisplay = $isActive ? '' : 'display:none;';

        // Build icon HTML
        $iconHtml = '';
        if ($icon !== 'none' && !empty($icon)) {
            $iconStyle = 'width:' . esc_attr($iconSize) . ';height:' . esc_attr($iconSize) . ';color:' . esc_attr($currentIconColor) . ';';
            $iconHtml = '<i data-lucide="' . esc_attr($icon) . '" style="' . $iconStyle . '"></i>';
        }

        // Tab button style
        $tabStyle = 'display:inline-flex;align-items:center;padding:10px 16px;';
        $tabStyle .= 'font-size:' . esc_attr($titleFontSize) . ';font-weight:' . esc_attr($titleFontWeight) . ';';
        $tabStyle .= 'color:' . esc_attr($currentTitleColor) . ';background:' . esc_attr($currentBackground) . ';';
        $tabStyle .= 'border:none;cursor:pointer;transition:all 0.2s;position:relative;';

        if ($iconPosition === 'top') {
            $tabStyle .= 'flex-direction:column;';
        }

        // Content panel style
        $panelStyle = $contentDisplay;
        $panelStyle .= 'padding:' . esc_attr($contentPadding) . ';';
        $panelStyle .= 'font-size:' . esc_attr($contentFontSize) . ';';
        $panelStyle .= 'color:' . esc_attr($contentColor) . ';';
        $panelStyle .= 'line-height:' . esc_attr($contentLineHeight) . ';';

        $html = '<div class="tb4-tabs-item' . $activeClass . '">';

        // Tab button
        $html .= '<button class="tb4-tab-btn' . $activeClass . '" style="' . $tabStyle . '">';

        // Icon positioning
        if ($iconPosition === 'left' || $iconPosition === 'top') {
            if ($iconHtml) {
                $marginStyle = $iconPosition === 'left' ? 'margin-right:6px;' : 'margin-bottom:4px;';
                $html .= '<span class="tb4-tab-icon" style="' . $marginStyle . '">' . $iconHtml . '</span>';
            }
            $html .= '<span class="tb4-tab-title">' . esc_html($title) . '</span>';
        } else {
            // Right position
            $html .= '<span class="tb4-tab-title">' . esc_html($title) . '</span>';
            if ($iconHtml) {
                $html .= '<span class="tb4-tab-icon" style="margin-left:6px;">' . $iconHtml . '</span>';
            }
        }

        // Active indicator
        if ($isActive) {
            $html .= '<span class="tb4-tab-indicator" style="position:absolute;bottom:0;left:0;right:0;height:2px;background:' . esc_attr($titleColorActive) . ';"></span>';
        }

        $html .= '</button>';

        // Content panel
        $html .= '<div class="tb4-tab-panel' . $activeClass . '" style="' . $panelStyle . '">';
        $html .= $content;
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }
}
