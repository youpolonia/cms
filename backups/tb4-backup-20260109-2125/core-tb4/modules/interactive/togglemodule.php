<?php
namespace Core\TB4\Modules\Interactive;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;

/**
 * TB 4.0 Toggle Module
 * Expandable/collapsible content block (like FAQ item)
 */
class ToggleModule extends Module
{
    public function __construct()
    {
        $this->name = 'Toggle';
        $this->slug = 'toggle';
        $this->icon = 'chevrons-down-up';
        $this->category = 'interactive';

        $this->elements = [
            'main' => '.tb4-toggle',
            'header' => '.tb4-toggle__header',
            'title' => '.tb4-toggle__title',
            'icon' => '.tb4-toggle__icon',
            'content' => '.tb4-toggle__content'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Click to expand'
            ],
            'content' => [
                'label' => 'Content',
                'type' => 'textarea',
                'default' => 'This is the expanded content that appears when the toggle is opened. You can add any text or information here.'
            ],
            'default_state' => [
                'label' => 'Default State',
                'type' => 'select',
                'options' => [
                    'closed' => 'Closed',
                    'open' => 'Open'
                ],
                'default' => 'closed'
            ],
            'icon_type' => [
                'label' => 'Icon Type',
                'type' => 'select',
                'options' => [
                    'chevron' => 'Chevron (arrow)',
                    'plus_minus' => 'Plus/Minus'
                ],
                'default' => 'chevron'
            ],
            'icon_position' => [
                'label' => 'Icon Position',
                'type' => 'select',
                'options' => [
                    'right' => 'Right',
                    'left' => 'Left'
                ],
                'default' => 'right'
            ],
            'title_tag' => [
                'label' => 'Title HTML Tag',
                'type' => 'select',
                'options' => [
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'Div'
                ],
                'default' => 'h4'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'text',
                'default' => '16px'
            ],
            'content_color' => [
                'label' => 'Content Color',
                'type' => 'color',
                'default' => '#4b5563'
            ],
            'content_font_size' => [
                'label' => 'Content Font Size',
                'type' => 'text',
                'default' => '14px'
            ],
            'header_bg_color' => [
                'label' => 'Header Background',
                'type' => 'color',
                'default' => '#f3f4f6'
            ],
            'header_bg_hover' => [
                'label' => 'Header Hover Background',
                'type' => 'color',
                'default' => '#e5e7eb'
            ],
            'content_bg_color' => [
                'label' => 'Content Background',
                'type' => 'color',
                'default' => '#ffffff'
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
            'icon_color' => [
                'label' => 'Icon Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'icon_size' => [
                'label' => 'Icon Size',
                'type' => 'text',
                'default' => '20px'
            ],
            'animation_speed' => [
                'label' => 'Animation Speed',
                'type' => 'select',
                'options' => [
                    '0.2s' => 'Fast',
                    '0.3s' => 'Normal',
                    '0.5s' => 'Slow'
                ],
                'default' => '0.3s'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    public function render(array $attrs): string
    {
        $title = $attrs['title'] ?? 'Click to expand';
        $content = $attrs['content'] ?? 'This is the expanded content that appears when the toggle is opened.';
        $defaultState = $attrs['default_state'] ?? 'closed';
        $iconType = $attrs['icon_type'] ?? 'chevron';
        $iconPosition = $attrs['icon_position'] ?? 'right';
        $titleTag = $attrs['title_tag'] ?? 'h4';
        $titleColor = $attrs['title_color'] ?? '#111827';
        $titleFontSize = $attrs['title_font_size'] ?? '16px';
        $contentColor = $attrs['content_color'] ?? '#4b5563';
        $contentFontSize = $attrs['content_font_size'] ?? '14px';
        $headerBgColor = $attrs['header_bg_color'] ?? '#f3f4f6';
        $headerBgHover = $attrs['header_bg_hover'] ?? '#e5e7eb';
        $contentBgColor = $attrs['content_bg_color'] ?? '#ffffff';
        $borderColor = $attrs['border_color'] ?? '#e5e7eb';
        $borderRadius = $attrs['border_radius'] ?? '8px';
        $iconColor = $attrs['icon_color'] ?? '#6b7280';
        $iconSize = $attrs['icon_size'] ?? '20px';
        $animationSpeed = $attrs['animation_speed'] ?? '0.3s';

        $isOpen = $defaultState === 'open';
        $openClass = $isOpen ? ' tb4-toggle--open' : '';
        $contentStyle = $isOpen ? '' : 'display:none;';

        // Build icon HTML
        if ($iconType === 'plus_minus') {
            $iconHtml = '<span class="tb4-toggle__icon" style="color:' . esc_attr($iconColor) . ';font-size:' . esc_attr($iconSize) . ';font-weight:bold;transition:transform ' . esc_attr($animationSpeed) . ';">' . ($isOpen ? '−' : '+') . '</span>';
        } else {
            $rotateStyle = $isOpen ? 'transform:rotate(180deg);' : '';
            $iconHtml = '<span class="tb4-toggle__icon" style="color:' . esc_attr($iconColor) . ';font-size:' . esc_attr($iconSize) . ';transition:transform ' . esc_attr($animationSpeed) . ';' . $rotateStyle . '">&#9660;</span>';
        }

        // Build header content based on icon position
        $headerInner = $iconPosition === 'left'
            ? $iconHtml . '<' . $titleTag . ' class="tb4-toggle__title" style="margin:0;color:' . esc_attr($titleColor) . ';font-size:' . esc_attr($titleFontSize) . ';font-weight:600;">' . esc_html($title) . '</' . $titleTag . '>'
            : '<' . $titleTag . ' class="tb4-toggle__title" style="margin:0;color:' . esc_attr($titleColor) . ';font-size:' . esc_attr($titleFontSize) . ';font-weight:600;">' . esc_html($title) . '</' . $titleTag . '>' . $iconHtml;

        $html = '<div class="tb4-toggle' . $openClass . '" data-animation-speed="' . esc_attr($animationSpeed) . '" data-icon-type="' . esc_attr($iconType) . '" style="border:1px solid ' . esc_attr($borderColor) . ';border-radius:' . esc_attr($borderRadius) . ';overflow:hidden;">';
        $html .= '<div class="tb4-toggle__header" style="display:flex;justify-content:space-between;align-items:center;gap:12px;padding:14px 18px;background:' . esc_attr($headerBgColor) . ';cursor:pointer;transition:background ' . esc_attr($animationSpeed) . ';" onmouseover="this.style.background=\'' . esc_attr($headerBgHover) . '\'" onmouseout="this.style.background=\'' . esc_attr($headerBgColor) . '\'">';
        $html .= $headerInner;
        $html .= '</div>';
        $html .= '<div class="tb4-toggle__content" style="padding:16px 18px;background:' . esc_attr($contentBgColor) . ';color:' . esc_attr($contentColor) . ';font-size:' . esc_attr($contentFontSize) . ';line-height:1.6;border-top:1px solid ' . esc_attr($borderColor) . ';' . $contentStyle . '">';
        $html .= esc_html($content);
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
