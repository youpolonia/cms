<?php
namespace Core\TB4\Modules\Interactive;

require_once __DIR__ . '/../childmodule.php';

use Core\TB4\Modules\ChildModule;

/**
 * TB 4.0 Accordion Item Module (Child Module)
 * Individual accordion item with title and collapsible content.
 * Must be nested inside AccordionModule parent.
 */
class AccordionItemModule extends ChildModule
{
    protected ?string $parent_slug = 'accordion';
    protected ?string $child_title_var = 'title';

    public function __construct()
    {
        $this->name = 'Accordion Item';
        $this->slug = 'accordion_item';
        $this->icon = 'chevron-down';
        $this->category = 'interactive';
    }

    public function get_content_fields(): array
    {
        return [
            'title' => [
                'type' => 'text',
                'label' => 'Title',
                'default' => 'Accordion Item'
            ],
            'content' => [
                'type' => 'wysiwyg',
                'label' => 'Content',
                'default' => 'Click to add content...'
            ],
            'open_by_default' => [
                'type' => 'toggle',
                'label' => 'Open by Default',
                'default' => false
            ],
            'icon_closed' => [
                'type' => 'select',
                'label' => 'Closed Icon',
                'options' => [
                    'plus' => 'Plus',
                    'chevron-down' => 'Chevron Down',
                    'arrow-down' => 'Arrow Down'
                ],
                'default' => 'plus'
            ],
            'icon_open' => [
                'type' => 'select',
                'label' => 'Open Icon',
                'options' => [
                    'minus' => 'Minus',
                    'chevron-up' => 'Chevron Up',
                    'arrow-up' => 'Arrow Up'
                ],
                'default' => 'minus'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'title_font_size' => [
                'type' => 'text',
                'label' => 'Title Font Size',
                'default' => '16px'
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
                'default' => '600'
            ],
            'title_color' => [
                'type' => 'color',
                'label' => 'Title Color',
                'default' => '#1f2937'
            ],
            'title_color_open' => [
                'type' => 'color',
                'label' => 'Title Color (Open)',
                'default' => '#2563eb'
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
            'icon_color' => [
                'type' => 'color',
                'label' => 'Icon Color',
                'default' => '#6b7280'
            ],
            'icon_color_open' => [
                'type' => 'color',
                'label' => 'Icon Color (Open)',
                'default' => '#2563eb'
            ],
            'background_color' => [
                'type' => 'color',
                'label' => 'Background Color',
                'default' => '#ffffff'
            ],
            'background_color_open' => [
                'type' => 'color',
                'label' => 'Background (Open)',
                'default' => '#f9fafb'
            ],
            'border_color' => [
                'type' => 'color',
                'label' => 'Border Color',
                'default' => '#e5e7eb'
            ],
            'padding' => [
                'type' => 'text',
                'label' => 'Padding',
                'default' => '16px'
            ]
        ];
    }

    public function render(array $attrs): string
    {
        // Content fields
        $title = $attrs['title'] ?? 'Accordion Item';
        $content = $attrs['content'] ?? 'Click to add content...';
        $openByDefault = !empty($attrs['open_by_default']);
        $iconClosed = $attrs['icon_closed'] ?? 'plus';
        $iconOpen = $attrs['icon_open'] ?? 'minus';

        // Design fields
        $titleFontSize = $attrs['title_font_size'] ?? '16px';
        $titleFontWeight = $attrs['title_font_weight'] ?? '600';
        $titleColor = $attrs['title_color'] ?? '#1f2937';
        $titleColorOpen = $attrs['title_color_open'] ?? '#2563eb';
        $contentFontSize = $attrs['content_font_size'] ?? '14px';
        $contentColor = $attrs['content_color'] ?? '#4b5563';
        $contentLineHeight = $attrs['content_line_height'] ?? '1.6';
        $iconColor = $attrs['icon_color'] ?? '#6b7280';
        $iconColorOpen = $attrs['icon_color_open'] ?? '#2563eb';
        $backgroundColor = $attrs['background_color'] ?? '#ffffff';
        $backgroundColorOpen = $attrs['background_color_open'] ?? '#f9fafb';
        $borderColor = $attrs['border_color'] ?? '#e5e7eb';
        $padding = $attrs['padding'] ?? '16px';

        // Determine current state
        $isOpen = $openByDefault;
        $activeClass = $isOpen ? ' active' : '';
        $currentTitleColor = $isOpen ? $titleColorOpen : $titleColor;
        $currentIconColor = $isOpen ? $iconColorOpen : $iconColor;
        $currentBackground = $isOpen ? $backgroundColorOpen : $backgroundColor;
        $contentDisplay = $isOpen ? '' : 'display:none;';

        // Get icon symbols
        $closedIcon = $this->getIconSymbol($iconClosed);
        $openIcon = $this->getIconSymbol($iconOpen);
        $currentIcon = $isOpen ? $openIcon : $closedIcon;

        // Item wrapper style
        $itemStyle = 'border:1px solid ' . esc_attr($borderColor) . ';border-radius:6px;overflow:hidden;';

        $html = '<div class="tb4-accordion-item' . $activeClass . '" style="' . $itemStyle . '">';

        // Header
        $headerStyle = 'display:flex;justify-content:space-between;align-items:center;';
        $headerStyle .= 'padding:' . esc_attr($padding) . ';';
        $headerStyle .= 'background:' . esc_attr($currentBackground) . ';';
        $headerStyle .= 'cursor:pointer;transition:background 0.2s;';

        $html .= '<div class="tb4-accordion-item-header" style="' . $headerStyle . '">';

        // Title
        $titleStyle = 'font-size:' . esc_attr($titleFontSize) . ';';
        $titleStyle .= 'font-weight:' . esc_attr($titleFontWeight) . ';';
        $titleStyle .= 'color:' . esc_attr($currentTitleColor) . ';';
        $titleStyle .= 'margin:0;';

        $html .= '<span class="tb4-accordion-item-title" style="' . $titleStyle . '">' . esc_html($title) . '</span>';

        // Icon
        $iconStyle = 'color:' . esc_attr($currentIconColor) . ';font-size:14px;transition:transform 0.3s;';
        $html .= '<span class="tb4-accordion-item-icon" data-open="' . esc_attr($openIcon) . '" data-closed="' . esc_attr($closedIcon) . '" style="' . $iconStyle . '">' . $currentIcon . '</span>';

        $html .= '</div>';

        // Content
        $contentWrapperStyle = $contentDisplay;
        $contentWrapperStyle .= 'padding:' . esc_attr($padding) . ';';
        $contentWrapperStyle .= 'background:' . esc_attr($backgroundColor) . ';';
        $contentWrapperStyle .= 'border-top:1px solid ' . esc_attr($borderColor) . ';';

        $html .= '<div class="tb4-accordion-item-content" style="' . $contentWrapperStyle . '">';

        $bodyStyle = 'font-size:' . esc_attr($contentFontSize) . ';';
        $bodyStyle .= 'color:' . esc_attr($contentColor) . ';';
        $bodyStyle .= 'line-height:' . esc_attr($contentLineHeight) . ';';

        $html .= '<div class="tb4-accordion-item-body" style="' . $bodyStyle . '">' . $content . '</div>';

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Get icon symbol from icon name
     */
    private function getIconSymbol(string $iconName): string
    {
        switch ($iconName) {
            case 'plus':
                return '+';
            case 'minus':
                return '−';
            case 'chevron-down':
                return '▼';
            case 'chevron-up':
                return '▲';
            case 'arrow-down':
                return '↓';
            case 'arrow-up':
                return '↑';
            default:
                return '+';
        }
    }
}
