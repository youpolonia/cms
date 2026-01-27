<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Search Module
 * Displays a search form/bar component with customizable styling
 */
class SearchModule extends Module
{
    public function __construct()
    {
        $this->name = 'Search';
        $this->slug = 'search';
        $this->icon = 'search';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-search',
            'form' => '.tb4-search__form',
            'input' => '.tb4-search__input',
            'button' => '.tb4-search__button',
            'icon' => '.tb4-search__icon'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'placeholder' => [
                'label' => 'Placeholder Text',
                'type' => 'text',
                'default' => 'Search...',
                'description' => 'Text shown when input is empty'
            ],
            'button_text' => [
                'label' => 'Button Text',
                'type' => 'text',
                'default' => 'Search',
                'description' => 'Text for the submit button (leave empty for icon only)'
            ],
            'show_icon' => [
                'label' => 'Show Search Icon',
                'type' => 'toggle',
                'default' => true,
                'description' => 'Display search icon in button'
            ],
            'action_url' => [
                'label' => 'Action URL',
                'type' => 'text',
                'default' => '/search',
                'description' => 'Form action URL for search submission'
            ],
            'input_name' => [
                'label' => 'Input Name',
                'type' => 'text',
                'default' => 'q',
                'description' => 'Query parameter name (e.g., q, s, search)'
            ],
            'open_in_new_tab' => [
                'label' => 'Open Results in New Tab',
                'type' => 'toggle',
                'default' => false
            ],
            'method' => [
                'label' => 'Form Method',
                'type' => 'select',
                'options' => [
                    'get' => 'GET',
                    'post' => 'POST'
                ],
                'default' => 'get'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'layout' => [
                'label' => 'Layout',
                'type' => 'select',
                'options' => [
                    'inline' => 'Inline',
                    'stacked' => 'Stacked',
                    'fullwidth' => 'Full Width'
                ],
                'default' => 'inline'
            ],
            'button_style' => [
                'label' => 'Button Style',
                'type' => 'select',
                'options' => [
                    'filled' => 'Filled',
                    'outline' => 'Outline',
                    'icon-only' => 'Icon Only'
                ],
                'default' => 'filled'
            ],
            'input_bg_color' => [
                'label' => 'Input Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'input_text_color' => [
                'label' => 'Input Text Color',
                'type' => 'color',
                'default' => '#333333'
            ],
            'input_border_color' => [
                'label' => 'Input Border Color',
                'type' => 'color',
                'default' => '#cccccc'
            ],
            'input_focus_border_color' => [
                'label' => 'Input Focus Border Color',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'button_bg_color' => [
                'label' => 'Button Background',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'button_text_color' => [
                'label' => 'Button Text Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'button_hover_bg_color' => [
                'label' => 'Button Hover Background',
                'type' => 'color',
                'default' => '#1d4ed8'
            ],
            'border_radius' => [
                'label' => 'Border Radius',
                'type' => 'text',
                'default' => '8px'
            ],
            'input_padding' => [
                'label' => 'Input Padding',
                'type' => 'text',
                'default' => '12px 16px'
            ],
            'button_padding' => [
                'label' => 'Button Padding',
                'type' => 'text',
                'default' => '12px 24px'
            ],
            'font_size' => [
                'label' => 'Font Size',
                'type' => 'text',
                'default' => '16px'
            ],
            'gap' => [
                'label' => 'Gap Between Elements',
                'type' => 'text',
                'default' => '8px'
            ],
            'max_width' => [
                'label' => 'Max Width',
                'type' => 'text',
                'default' => '500px'
            ],
            'alignment' => [
                'label' => 'Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'left'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    /**
     * Get search icon SVG
     */
    private function get_search_icon_svg(string $color = 'currentColor', string $size = '18'): string
    {
        return sprintf(
            '<svg class="tb4-search__icon" xmlns="http://www.w3.org/2000/svg" width="%s" height="%s" viewBox="0 0 24 24" fill="none" stroke="%s" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/></svg>',
            esc_attr($size),
            esc_attr($size),
            esc_attr($color)
        );
    }

    public function render(array $settings): string
    {
        // Content settings
        $placeholder = $settings['placeholder'] ?? 'Search...';
        $buttonText = $settings['button_text'] ?? 'Search';
        $showIcon = $settings['show_icon'] ?? true;
        $actionUrl = $settings['action_url'] ?? '/search';
        $inputName = $settings['input_name'] ?? 'q';
        $openNewTab = $settings['open_in_new_tab'] ?? false;
        $method = $settings['method'] ?? 'get';

        // Design settings
        $layout = $settings['layout'] ?? 'inline';
        $buttonStyle = $settings['button_style'] ?? 'filled';
        $inputBgColor = $settings['input_bg_color'] ?? '#ffffff';
        $inputTextColor = $settings['input_text_color'] ?? '#333333';
        $inputBorderColor = $settings['input_border_color'] ?? '#cccccc';
        $inputFocusBorderColor = $settings['input_focus_border_color'] ?? '#2563eb';
        $buttonBgColor = $settings['button_bg_color'] ?? '#2563eb';
        $buttonTextColor = $settings['button_text_color'] ?? '#ffffff';
        $buttonHoverBgColor = $settings['button_hover_bg_color'] ?? '#1d4ed8';
        $borderRadius = $settings['border_radius'] ?? '8px';
        $inputPadding = $settings['input_padding'] ?? '12px 16px';
        $buttonPadding = $settings['button_padding'] ?? '12px 24px';
        $fontSize = $settings['font_size'] ?? '16px';
        $gap = $settings['gap'] ?? '8px';
        $maxWidth = $settings['max_width'] ?? '500px';
        $alignment = $settings['alignment'] ?? 'left';

        // Generate unique ID for scoped styles
        $uniqueId = 'tb4-search-' . uniqid();

        // Build alignment class
        $alignClass = '';
        if ($alignment === 'center') {
            $alignClass = ' align-center';
        } elseif ($alignment === 'right') {
            $alignClass = ' align-right';
        }

        // Build container styles
        $containerStyles = ['max-width:' . $maxWidth];

        // Build form styles
        $formStyles = ['display:flex', 'gap:' . $gap];
        if ($layout === 'stacked') {
            $formStyles[] = 'flex-direction:column';
        } elseif ($layout === 'fullwidth') {
            $formStyles[] = 'width:100%';
        }

        // Build input styles
        $inputStyles = [
            'flex:1',
            'min-width:0',
            'padding:' . $inputPadding,
            'font-size:' . $fontSize,
            'color:' . $inputTextColor,
            'background-color:' . $inputBgColor,
            'border:1px solid ' . $inputBorderColor,
            'border-radius:' . $borderRadius,
            'outline:none',
            'transition:border-color 0.2s, box-shadow 0.2s'
        ];

        // Build button styles
        $buttonStyles = [
            'display:inline-flex',
            'align-items:center',
            'justify-content:center',
            'gap:8px',
            'font-size:' . $fontSize,
            'border-radius:' . $borderRadius,
            'cursor:pointer',
            'transition:background-color 0.2s, transform 0.1s, border-color 0.2s'
        ];

        if ($buttonStyle === 'icon-only') {
            $buttonStyles[] = 'padding:12px';
            $buttonStyles[] = 'background-color:' . $buttonBgColor;
            $buttonStyles[] = 'color:' . $buttonTextColor;
            $buttonStyles[] = 'border:none';
        } elseif ($buttonStyle === 'outline') {
            $buttonStyles[] = 'padding:' . $buttonPadding;
            $buttonStyles[] = 'background-color:transparent';
            $buttonStyles[] = 'color:' . $buttonBgColor;
            $buttonStyles[] = 'border:2px solid ' . $buttonBgColor;
        } else {
            $buttonStyles[] = 'padding:' . $buttonPadding;
            $buttonStyles[] = 'background-color:' . $buttonBgColor;
            $buttonStyles[] = 'color:' . $buttonTextColor;
            $buttonStyles[] = 'border:none';
        }

        // Form attributes
        $targetAttr = $openNewTab ? ' target="_blank"' : '';

        // Build HTML
        $html = '<div id="' . esc_attr($uniqueId) . '" class="tb4-search' . $alignClass . '" style="' . implode(';', $containerStyles) . '">';
        $html .= '<form class="tb4-search__form layout-' . esc_attr($layout) . '" action="' . esc_attr($actionUrl) . '" method="' . esc_attr($method) . '"' . $targetAttr . ' style="' . implode(';', $formStyles) . '">';

        // Input field
        $html .= '<input type="text" name="' . esc_attr($inputName) . '" class="tb4-search__input" placeholder="' . esc_attr($placeholder) . '" style="' . implode(';', $inputStyles) . '" aria-label="' . esc_attr($placeholder) . '">';

        // Submit button
        $html .= '<button type="submit" class="tb4-search__button" style="' . implode(';', $buttonStyles) . '">';

        // Icon
        if ($showIcon) {
            $iconColor = ($buttonStyle === 'outline') ? $buttonBgColor : $buttonTextColor;
            $html .= $this->get_search_icon_svg($iconColor);
        }

        // Button text (unless icon-only)
        if ($buttonStyle !== 'icon-only' && !empty($buttonText)) {
            $html .= '<span class="tb4-search__button-text">' . esc_html($buttonText) . '</span>';
        }

        $html .= '</button>';
        $html .= '</form>';
        $html .= '</div>';

        // Add scoped CSS for hover/focus states
        $html .= $this->generate_scoped_css($uniqueId, $settings);

        return $html;
    }

    /**
     * Generate scoped CSS for interactive states
     */
    private function generate_scoped_css(string $uniqueId, array $settings): string
    {
        $selector = '#' . $uniqueId;

        $inputFocusBorderColor = $settings['input_focus_border_color'] ?? '#2563eb';
        $buttonBgColor = $settings['button_bg_color'] ?? '#2563eb';
        $buttonHoverBgColor = $settings['button_hover_bg_color'] ?? '#1d4ed8';
        $buttonTextColor = $settings['button_text_color'] ?? '#ffffff';
        $buttonStyle = $settings['button_style'] ?? 'filled';

        $css = [];

        // Input focus state
        $css[] = $selector . ' .tb4-search__input:focus {';
        $css[] = '  border-color: ' . esc_attr($inputFocusBorderColor) . ';';
        $css[] = '  box-shadow: 0 0 0 3px ' . esc_attr($inputFocusBorderColor) . '1a;';
        $css[] = '}';

        // Button hover state
        if ($buttonStyle === 'outline') {
            $css[] = $selector . ' .tb4-search__button:hover {';
            $css[] = '  background-color: ' . esc_attr($buttonBgColor) . ';';
            $css[] = '  color: ' . esc_attr($buttonTextColor) . ';';
            $css[] = '}';
            $css[] = $selector . ' .tb4-search__button:hover .tb4-search__icon {';
            $css[] = '  stroke: ' . esc_attr($buttonTextColor) . ';';
            $css[] = '}';
        } else {
            $css[] = $selector . ' .tb4-search__button:hover {';
            $css[] = '  background-color: ' . esc_attr($buttonHoverBgColor) . ';';
            $css[] = '}';
        }

        // Button active state
        $css[] = $selector . ' .tb4-search__button:active {';
        $css[] = '  transform: scale(0.98);';
        $css[] = '}';

        // Focus visible for accessibility
        $css[] = $selector . ' .tb4-search__button:focus-visible {';
        $css[] = '  outline: 2px solid ' . esc_attr($buttonBgColor) . ';';
        $css[] = '  outline-offset: 2px;';
        $css[] = '}';

        return '<style>' . implode("\n", $css) . '</style>';
    }
}
