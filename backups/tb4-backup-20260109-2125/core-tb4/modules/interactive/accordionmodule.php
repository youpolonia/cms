<?php
namespace Core\TB4\Modules\Interactive;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;

/**
 * TB 4.0 Accordion Module
 * Collapsible accordion with multiple items for FAQs, expandable content sections
 */
class AccordionModule extends Module
{
    protected string $type = 'parent';
    protected ?string $child_slug = 'accordion_item';

    public function __construct()
    {
        $this->name = 'Accordion';
        $this->slug = 'accordion';
        $this->icon = 'list';
        $this->category = 'interactive';

        $this->elements = [
            'main' => '.tb4-accordion',
            'item' => '.tb4-accordion-item',
            'header' => '.tb4-accordion-header',
            'title' => '.tb4-accordion-title',
            'icon' => '.tb4-accordion-icon',
            'content' => '.tb4-accordion-content',
            'body' => '.tb4-accordion-body'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'item1_title' => [
                'label' => 'Item 1 Title',
                'type' => 'text',
                'default' => 'What is your return policy?'
            ],
            'item1_content' => [
                'label' => 'Item 1 Content',
                'type' => 'textarea',
                'default' => 'We offer a 30-day money-back guarantee on all purchases. Simply contact our support team to initiate a return.'
            ],
            'item2_title' => [
                'label' => 'Item 2 Title',
                'type' => 'text',
                'default' => 'How long does shipping take?'
            ],
            'item2_content' => [
                'label' => 'Item 2 Content',
                'type' => 'textarea',
                'default' => 'Standard shipping takes 5-7 business days. Express shipping is available for 2-3 day delivery.'
            ],
            'item3_title' => [
                'label' => 'Item 3 Title',
                'type' => 'text',
                'default' => 'Do you offer support?'
            ],
            'item3_content' => [
                'label' => 'Item 3 Content',
                'type' => 'textarea',
                'default' => 'Yes! We offer 24/7 customer support via email and live chat. Premium plans include phone support.'
            ],
            'item4_title' => [
                'label' => 'Item 4 Title',
                'type' => 'text',
                'default' => ''
            ],
            'item4_content' => [
                'label' => 'Item 4 Content',
                'type' => 'textarea',
                'default' => ''
            ],
            'item5_title' => [
                'label' => 'Item 5 Title',
                'type' => 'text',
                'default' => ''
            ],
            'item5_content' => [
                'label' => 'Item 5 Content',
                'type' => 'textarea',
                'default' => ''
            ],
            'open_first' => [
                'label' => 'Open First Item',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'toggle_behavior' => [
                'label' => 'Toggle Behavior',
                'type' => 'select',
                'options' => [
                    'single' => 'One Open at a Time',
                    'multiple' => 'Multiple Can Be Open'
                ],
                'default' => 'single'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'icon_type' => [
                'label' => 'Icon Type',
                'type' => 'select',
                'options' => [
                    'chevron' => 'Chevron (▼)',
                    'plus' => 'Plus/Minus (+/-)',
                    'arrow' => 'Arrow (→)',
                    'none' => 'No Icon'
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
            'title_font_weight' => [
                'label' => 'Title Font Weight',
                'type' => 'select',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi Bold',
                    '700' => 'Bold'
                ],
                'default' => '600'
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
            'header_bg' => [
                'label' => 'Header Background',
                'type' => 'color',
                'default' => '#f9fafb'
            ],
            'header_bg_hover' => [
                'label' => 'Header Hover BG',
                'type' => 'color',
                'default' => '#f3f4f6'
            ],
            'header_bg_active' => [
                'label' => 'Header Active BG',
                'type' => 'color',
                'default' => '#e5e7eb'
            ],
            'content_bg' => [
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
            'divider_style' => [
                'label' => 'Item Divider',
                'type' => 'select',
                'options' => [
                    'border' => 'Border',
                    'gap' => 'Gap/Space',
                    'none' => 'None'
                ],
                'default' => 'border'
            ],
            'gap_size' => [
                'label' => 'Gap Size',
                'type' => 'text',
                'default' => '8px'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    public function render(array $attrs): string
    {
        // Collect items
        $items = [];
        for ($i = 1; $i <= 5; $i++) {
            $title = $attrs['item' . $i . '_title'] ?? '';
            $itemContent = $attrs['item' . $i . '_content'] ?? '';
            if (!empty(trim($title))) {
                $items[] = ['title' => $title, 'content' => $itemContent];
            }
        }

        // If no items, use defaults
        if (empty($items)) {
            $items = [
                ['title' => 'What is your return policy?', 'content' => 'We offer a 30-day money-back guarantee on all purchases.'],
                ['title' => 'How long does shipping take?', 'content' => 'Standard shipping takes 5-7 business days.'],
                ['title' => 'Do you offer support?', 'content' => 'Yes! We offer 24/7 customer support via email and live chat.']
            ];
        }

        // Settings
        $openFirst = ($attrs['open_first'] ?? 'yes') === 'yes';
        $toggleBehavior = $attrs['toggle_behavior'] ?? 'single';
        $iconType = $attrs['icon_type'] ?? 'chevron';
        $iconPosition = $attrs['icon_position'] ?? 'right';
        $titleColor = $attrs['title_color'] ?? '#111827';
        $titleFontSize = $attrs['title_font_size'] ?? '16px';
        $titleFontWeight = $attrs['title_font_weight'] ?? '600';
        $contentColor = $attrs['content_color'] ?? '#4b5563';
        $contentFontSize = $attrs['content_font_size'] ?? '14px';
        $headerBg = $attrs['header_bg'] ?? '#f9fafb';
        $headerBgHover = $attrs['header_bg_hover'] ?? '#f3f4f6';
        $headerBgActive = $attrs['header_bg_active'] ?? '#e5e7eb';
        $contentBg = $attrs['content_bg'] ?? '#ffffff';
        $borderColor = $attrs['border_color'] ?? '#e5e7eb';
        $borderRadius = $attrs['border_radius'] ?? '8px';
        $iconColor = $attrs['icon_color'] ?? '#6b7280';
        $dividerStyle = $attrs['divider_style'] ?? 'border';
        $gapSize = $attrs['gap_size'] ?? '8px';

        // Determine icon symbol
        switch ($iconType) {
            case 'plus':
                $iconOpen = '−';
                $iconClosed = '+';
                break;
            case 'arrow':
                $iconOpen = '→';
                $iconClosed = '→';
                break;
            case 'none':
                $iconOpen = '';
                $iconClosed = '';
                break;
            default: // chevron
                $iconOpen = '▲';
                $iconClosed = '▼';
        }

        // Wrapper styles
        $wrapperStyle = 'border-radius:' . esc_attr($borderRadius) . ';overflow:hidden;';
        if ($dividerStyle === 'gap') {
            $wrapperStyle .= 'display:flex;flex-direction:column;gap:' . esc_attr($gapSize) . ';';
        } else {
            $wrapperStyle .= 'border:1px solid ' . esc_attr($borderColor) . ';';
        }

        $html = '<div class="tb4-accordion" data-behavior="' . esc_attr($toggleBehavior) . '" style="' . $wrapperStyle . '">';

        foreach ($items as $index => $item) {
            $isOpen = ($index === 0 && $openFirst);
            $activeClass = $isOpen ? ' active' : '';
            $currentHeaderBg = $isOpen ? $headerBgActive : $headerBg;
            $currentIcon = $isOpen ? $iconOpen : $iconClosed;
            $contentDisplay = $isOpen ? '' : 'display:none;';

            // Item wrapper style
            $itemStyle = '';
            if ($dividerStyle === 'border' && $index > 0) {
                $itemStyle = 'border-top:1px solid ' . esc_attr($borderColor) . ';';
            } elseif ($dividerStyle === 'gap') {
                $itemStyle = 'border:1px solid ' . esc_attr($borderColor) . ';border-radius:' . esc_attr($borderRadius) . ';overflow:hidden;';
            }

            $html .= '<div class="tb4-accordion-item' . $activeClass . '" style="' . $itemStyle . '">';

            // Header
            $headerStyle = 'display:flex;justify-content:space-between;align-items:center;padding:16px;background:' . esc_attr($currentHeaderBg) . ';cursor:pointer;transition:background 0.2s;';
            if ($iconPosition === 'left') {
                $headerStyle .= 'flex-direction:row-reverse;';
            }

            $html .= '<div class="tb4-accordion-header" style="' . $headerStyle . '">';

            // Title
            $titleStyle = 'font-size:' . esc_attr($titleFontSize) . ';font-weight:' . esc_attr($titleFontWeight) . ';color:' . esc_attr($titleColor) . ';margin:0;';
            $html .= '<span class="tb4-accordion-title" style="' . $titleStyle . '">' . esc_html($item['title']) . '</span>';

            // Icon
            if ($iconType !== 'none') {
                $iconStyle = 'color:' . esc_attr($iconColor) . ';font-size:14px;transition:transform 0.3s;';
                if ($iconType === 'chevron' && $isOpen) {
                    $iconStyle .= 'transform:rotate(180deg);';
                }
                $html .= '<span class="tb4-accordion-icon" data-open="' . esc_attr($iconOpen) . '" data-closed="' . esc_attr($iconClosed) . '" style="' . $iconStyle . '">' . $currentIcon . '</span>';
            }

            $html .= '</div>';

            // Content
            $contentWrapperStyle = $contentDisplay . 'padding:16px;background:' . esc_attr($contentBg) . ';border-top:1px solid ' . esc_attr($borderColor) . ';';
            $html .= '<div class="tb4-accordion-content" style="' . $contentWrapperStyle . '">';

            $bodyStyle = 'font-size:' . esc_attr($contentFontSize) . ';color:' . esc_attr($contentColor) . ';line-height:1.6;';
            $html .= '<div class="tb4-accordion-body" style="' . $bodyStyle . '">' . nl2br(esc_html($item['content'])) . '</div>';

            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}
