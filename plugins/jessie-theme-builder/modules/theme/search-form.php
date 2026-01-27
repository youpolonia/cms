<?php
/**
 * Search Form Module
 * Search form or icon for headers
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Search_Form extends JTB_Element
{
    public string $slug = 'search_form';
    public string $name = 'Search';
    public string $icon = 'search';
    public string $category = 'theme';

    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFields(): array
    {
        return [
            'display_type' => [
                'label' => 'Display Type',
                'type' => 'select',
                'options' => [
                    'icon_only' => 'Icon Only (Expandable)',
                    'full_form' => 'Full Search Form',
                    'minimal' => 'Minimal Input'
                ],
                'default' => 'full_form'
            ],
            'placeholder' => [
                'label' => 'Placeholder Text',
                'type' => 'text',
                'default' => 'Search...'
            ],
            'show_button' => [
                'label' => 'Show Search Button',
                'type' => 'toggle',
                'default' => true
            ],
            'button_style' => [
                'label' => 'Button Style',
                'type' => 'select',
                'options' => [
                    'icon' => 'Icon Only',
                    'text' => 'Text Only',
                    'both' => 'Icon + Text'
                ],
                'default' => 'icon'
            ],
            'button_text' => [
                'label' => 'Button Text',
                'type' => 'text',
                'default' => 'Search'
            ],
            'button_bg_color' => [
                'label' => 'Button Background',
                'type' => 'color',
                'default' => '#2ea3f2',
                'hover' => true
            ],
            'button_text_color' => [
                'label' => 'Button Text/Icon Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'input_bg_color' => [
                'label' => 'Input Background',
                'type' => 'color',
                'default' => '#f5f5f5'
            ],
            'input_text_color' => [
                'label' => 'Input Text Color',
                'type' => 'color',
                'default' => '#333333'
            ],
            'input_border_color' => [
                'label' => 'Input Border Color',
                'type' => 'color',
                'default' => '#e5e5e5'
            ],
            'input_focus_border_color' => [
                'label' => 'Input Focus Border',
                'type' => 'color',
                'default' => '#2ea3f2'
            ],
            'border_radius' => [
                'label' => 'Border Radius',
                'type' => 'range',
                'min' => 0,
                'max' => 30,
                'step' => 1,
                'default' => 4,
                'unit' => 'px'
            ],
            'input_height' => [
                'label' => 'Input Height',
                'type' => 'range',
                'min' => 32,
                'max' => 56,
                'step' => 2,
                'default' => 44,
                'unit' => 'px'
            ],
            'form_width' => [
                'label' => 'Form Width',
                'type' => 'range',
                'min' => 200,
                'max' => 600,
                'step' => 10,
                'default' => 300,
                'unit' => 'px',
                'responsive' => true
            ],
            'icon_color' => [
                'label' => 'Icon Color (Icon Only Mode)',
                'type' => 'color',
                'default' => '#333333',
                'hover' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $id = $attrs['id'] ?? 'search_' . uniqid();
        $displayType = $attrs['display_type'] ?? 'full_form';
        $placeholder = $attrs['placeholder'] ?? 'Search...';
        $showButton = $attrs['show_button'] ?? true;
        $buttonStyle = $attrs['button_style'] ?? 'icon';
        $buttonText = $attrs['button_text'] ?? 'Search';

        // SVG search icon
        $searchIcon = '<svg class="jtb-search-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>';

        $classes = ['jtb-search-form', 'jtb-search-' . $this->esc($displayType)];

        $html = '<div id="' . $this->esc($id) . '" class="' . implode(' ', $classes) . '">';

        if ($displayType === 'icon_only') {
            $html .= '<button type="button" class="jtb-search-toggle" aria-label="Toggle search">' . $searchIcon . '</button>';
        }

        $formClass = $displayType === 'icon_only' ? 'jtb-search-inner jtb-search-expandable' : 'jtb-search-inner';
        $html .= '<form action="/search" method="get" class="' . $formClass . '">';
        $html .= '<input type="search" name="q" placeholder="' . $this->esc($placeholder) . '" class="jtb-search-input" aria-label="Search">';

        if ($showButton) {
            $html .= '<button type="submit" class="jtb-search-button" aria-label="Submit search">';
            if ($buttonStyle === 'icon' || $buttonStyle === 'both') {
                $html .= $searchIcon;
            }
            if ($buttonStyle === 'text' || $buttonStyle === 'both') {
                $html .= '<span class="jtb-search-btn-text">' . $this->esc($buttonText) . '</span>';
            }
            $html .= '</button>';
        }

        $html .= '</form>';
        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);

        $displayType = $attrs['display_type'] ?? 'full_form';
        $inputBg = $attrs['input_bg_color'] ?? '#f5f5f5';
        $inputText = $attrs['input_text_color'] ?? '#333333';
        $inputBorder = $attrs['input_border_color'] ?? '#e5e5e5';
        $inputFocusBorder = $attrs['input_focus_border_color'] ?? '#2ea3f2';
        $buttonBg = $attrs['button_bg_color'] ?? '#2ea3f2';
        $buttonBgHover = $attrs['button_bg_color__hover'] ?? '#1a8cd8';
        $buttonText = $attrs['button_text_color'] ?? '#ffffff';
        $borderRadius = $attrs['border_radius'] ?? 4;
        $inputHeight = $attrs['input_height'] ?? 44;
        $formWidth = $attrs['form_width'] ?? 300;
        $iconColor = $attrs['icon_color'] ?? '#333333';
        $iconHoverColor = $attrs['icon_color__hover'] ?? '#2ea3f2';

        // Container
        $css .= $selector . ' { display: inline-flex; align-items: center; }' . "\n";

        // Search toggle (icon only mode)
        $css .= $selector . ' .jtb-search-toggle { ';
        $css .= 'background: none; ';
        $css .= 'border: none; ';
        $css .= 'padding: 8px; ';
        $css .= 'cursor: pointer; ';
        $css .= 'color: ' . $iconColor . '; ';
        $css .= 'display: flex; ';
        $css .= 'align-items: center; ';
        $css .= 'justify-content: center; ';
        $css .= 'transition: color 0.3s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-search-toggle:hover { color: ' . $iconHoverColor . '; }' . "\n";

        // Form inner
        $css .= $selector . ' .jtb-search-inner { ';
        $css .= 'display: flex; ';
        $css .= 'align-items: stretch; ';
        $css .= 'width: ' . intval($formWidth) . 'px; ';
        $css .= '}' . "\n";

        // Expandable form (hidden by default in icon_only mode)
        if ($displayType === 'icon_only') {
            $css .= $selector . ' .jtb-search-expandable { display: none; }' . "\n";
            $css .= $selector . '.jtb-search-active .jtb-search-expandable { display: flex; }' . "\n";
            $css .= $selector . '.jtb-search-active .jtb-search-toggle { display: none; }' . "\n";
        }

        // Input
        $css .= $selector . ' .jtb-search-input { ';
        $css .= 'flex: 1; ';
        $css .= 'height: ' . intval($inputHeight) . 'px; ';
        $css .= 'padding: 0 16px; ';
        $css .= 'background: ' . $inputBg . '; ';
        $css .= 'color: ' . $inputText . '; ';
        $css .= 'border: 1px solid ' . $inputBorder . '; ';
        $css .= 'border-radius: ' . intval($borderRadius) . 'px 0 0 ' . intval($borderRadius) . 'px; ';
        $css .= 'font-size: 14px; ';
        $css .= 'outline: none; ';
        $css .= 'transition: border-color 0.3s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-search-input:focus { border-color: ' . $inputFocusBorder . '; }' . "\n";

        $css .= $selector . ' .jtb-search-input::placeholder { color: #999; }' . "\n";

        // Button
        $css .= $selector . ' .jtb-search-button { ';
        $css .= 'display: flex; ';
        $css .= 'align-items: center; ';
        $css .= 'justify-content: center; ';
        $css .= 'gap: 6px; ';
        $css .= 'padding: 0 16px; ';
        $css .= 'background: ' . $buttonBg . '; ';
        $css .= 'color: ' . $buttonText . '; ';
        $css .= 'border: none; ';
        $css .= 'border-radius: 0 ' . intval($borderRadius) . 'px ' . intval($borderRadius) . 'px 0; ';
        $css .= 'cursor: pointer; ';
        $css .= 'font-size: 14px; ';
        $css .= 'font-weight: 500; ';
        $css .= 'transition: background-color 0.3s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-search-button:hover { background: ' . $buttonBgHover . '; }' . "\n";

        // Minimal style - no button
        if ($displayType === 'minimal') {
            $css .= $selector . '.jtb-search-minimal .jtb-search-input { ';
            $css .= 'border-radius: ' . intval($borderRadius) . 'px; ';
            $css .= '}' . "\n";
        }

        // Responsive
        if (!empty($attrs['form_width__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-search-inner { width: ' . intval($attrs['form_width__tablet']) . 'px; }';
            $css .= ' }' . "\n";
        }

        if (!empty($attrs['form_width__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-search-inner { width: ' . intval($attrs['form_width__phone']) . 'px; }';
            $css .= ' }' . "\n";
        }

        return $css;
    }
}

JTB_Registry::register('search_form', JTB_Module_Search_Form::class);
